<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-03-03 17:28:37
 * @LastEditTime: 2020-03-10 11:04:48
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
if(!empty($it)){
	if($op == 'GetHealth'){
		//传递过来的日期
		$date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
		$istrue = (strtotime($date)+86399) < time() ? true : false; //是否在今天(在今天不允许点击后一天)
		$time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
		//当前日期对开始与结束
		$start = strtotime($date);
		$end = $start + 86399;
		$condition = " AND schoolid = '{$schoolid}' AND createtime BETWEEN '{$start}' AND '{$end}' ";
		/********************************************当前学校共有数据(全部)*********************************************/
		$AllStu = pdo_fetchcolumn("SELECT count(st.id) FROM ".GetTableName('students')." as st , ".GetTableName('classify'). " as cl WHERE  st.bj_id = cl.sid AND st.weid = '{$weid}' AND st.schoolid = '{$schoolid}' AND cl.type = 'theclass' AND cl.is_over != 2"); //总人数
		$AllTea = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' "); //总人数
		$AllNum = intval($AllStu) + intval($AllTea);
		if($_GPC['leixing'] == 1 || $_GPC['leixing'] == 2){
			$AllCheckupStu = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND sid != 0 $condition"); //检测人数
			$AllCheckupTea = pdo_fetchcolumn("SELECT count(DISTINCT tid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND tid != 0 $condition"); //检测人数
			$AllCheckup = intval($AllCheckupStu) + intval($AllCheckupTea);
			$AbNormalStu = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND temperature > 37.5 AND sid != 0 $condition"); //发热数量
			$AbNormalTea = pdo_fetchcolumn("SELECT count(DISTINCT tid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND temperature > 37.5 AND tid != 0 $condition"); //发热数量
			$AbNormal = intval($AbNormalStu) + intval($AbNormalTea);
		}elseif($_GPC['leixing'] == 3){
			mload()->model('mc');
			$Reoort = GetHealReport($schoolid,$date);
			$AllCheckup = intval($Reoort['Checkup']);
			$AbNormal = $Reoort['CheckupIsNormal']['abnormal']; //检测率
		}
		$qj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假'  then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end");//请假数量

		$teaqj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假'  then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE sid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end");//请假数量
		/********************************************当前学校共有数据(全部)*********************************************/
	
		// 获取当前学校的年级
		$njlist = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'semester' And schoolid = '{$schoolid}' AND is_over = 1 ORDER BY sid ASC, ssort DESC");
		foreach ($njlist as $key => $value) {
			//查询当前年级下的所有班级id
			$njstucount = 0; //年级学生人数
			$StuCheck = 0; //当日检测人数
			$heat = 0; //发热人数
			$bjlist = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " WHERE parentid = '{$value['sid']}' AND is_over = 1");
			if(!empty($bjlist)){
				$bjstr = arrayToString(array_column($bjlist,'sid')); //当前年级下的所有班级id
				$njstucount = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('students')." WHERE FIND_IN_SET(bj_id,'{$bjstr}') "); //获取当前年级下所有班级的学生总数
				if($_GPC['leixing'] == 1 || $_GPC['leixing'] == 2){
					$Checkup = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND FIND_IN_SET(bj_id,'{$bjstr}') $condition"); //当前检测人数
					$abnormal = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = '{$_GPC['leixing']}' AND temperature > 37.5 AND FIND_IN_SET(bj_id,'{$bjstr}') $condition"); //发热数量
				}elseif($_GPC['leixing'] == 3){
					$Reoort2 = GetHealReport($schoolid,$date,$bjstr);
					$Checkup = $Reoort2['Checkup'];
					$abnormal = $Reoort2['CheckupIsNormal']['abnormal'];
				}
				$njqj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假' then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE tid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end AND FIND_IN_SET(bj_id,'{$bjstr}')");//请假数量
			}
			$njlist[$key]['njstucount'] = $njstucount;
			$njlist[$key]['Checkup'] = $Checkup;
			$njlist[$key]['abnormal'] = $abnormal;
			$njlist[$key]['sj'] = $njqj['sj'];
			$njlist[$key]['bj'] = $njqj['bj'];
		}
		include $this->template('comtool/tempcenter');
		exit;
	}elseif($op == 'display'){
		include $this->template(''.$school['style3'].'/tempcenter');
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");	
}
				     
?>