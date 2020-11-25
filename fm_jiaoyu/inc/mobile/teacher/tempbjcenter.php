<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-03-05 17:06:27
 * @LastEditTime: 2020-03-07 15:00:06
 */
/*
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$njid = intval($_GPC['njid']); 
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid,':schoolid' => $schoolid,':openid' => $openid,':sid' => 0));
$school = pdo_fetch("SELECT title,style3,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$op = $_GPC['op'] ? $_GPC['op'] : 'display';	
mload()->model('mc');
if(!empty($it)){
	if($op == 'GetHealth'){
        $njid = "{$_GPC['njid']}";
		//传递过来的日期
		$date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
		$istrue = (strtotime($date)+86399) < time() ? true : false; //是否在今天(在今天不允许点击后一天)
		$time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
		//当前日期对开始与结束
		$start = strtotime($date);
		$end = $start + 86399;
		$condition = " AND schoolid = '{$schoolid}' AND createtime BETWEEN '{$start}' AND '{$end}' ";
        /********************************************当前班级共有数据(全部)*********************************************/
        $bjlist = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$schoolid}' AND is_over = 1 AND parentid = '{$njid}' ORDER BY sid ASC, ssort DESC");
        $bjstr = arrayToString(array_column($bjlist,'sid'));
        $condition1 .= " AND FIND_IN_SET(bj_id,'{$bjstr}')";
		$AllStu = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE schoolid='{$schoolid}' $condition1"); //总人数
		if($_GPC['leixing'] == 1 || $_GPC['leixing'] == 2){
			$AllCheckup = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' $condition1 $condition"); //检测人数
			$AbNormal = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND temperature > 37.5 $condition1 $condition"); //发热数量
		}elseif($_GPC['leixing'] == 3){
			$Reoort = GetHealReport($schoolid,$date,$bjstr);
			$AllCheckup = $Reoort['Checkup'];
			$AbNormal = $Reoort['CheckupIsNormal']['abnormal'];
        }
		$qj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假'  then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE tid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end $condition1");//请假数量
		/********************************************当前学校共有数据(全部)*********************************************/
	
		// 获取当前班级数据
		foreach ($bjlist as $key => $value) {
			//查询当前年级下的所有班级id
            $stucount = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE bj_id = '{$value['sid']}' "); //获取当前年级下所有班级的学生总数
            if($_GPC['leixing'] == 1 || $_GPC['leixing'] == 2){
                $Checkup = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND bj_id = '{$value['sid']}' $condition"); //当前检测人数
                $heat = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND temperature > 37.5 AND bj_id = '{$value['sid']}' $condition"); //发热数量
            }elseif($_GPC['leixing'] == 3){
				$Reoort2 = GetHealReport($schoolid,$date,'',$value['sid']);
				$Checkup = $Reoort['Checkup'];
				$heat = $Reoort2['CheckupIsNormal']['abnormal'];
            }
            $bjqj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假' then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE tid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end AND bj_id = '{$value['sid']}'");//请假数量
			$bjlist[$key]['stucount'] = $stucount;
			$bjlist[$key]['Checkup'] = $Checkup;
			$bjlist[$key]['heat'] = $heat;
			$bjlist[$key]['sj'] = $bjqj['sj'];
			$bjlist[$key]['bj'] = $bjqj['bj'];
		}
		include $this->template('comtool/tempbjcenter');
		exit;
	}elseif($op == 'display'){
		include $this->template(''.$school['style3'].'/tempbjcenter');
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");	
}
				     
?>