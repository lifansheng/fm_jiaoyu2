<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-03-03 16:51:24
 * @LastEditTime: 2020-03-10 11:07:19
 */
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid,':schoolid' => $schoolid,':openid' => $openid,':sid' => 0));
$school = pdo_fetch("SELECT title,style3,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$op = $_GPC['op'] ? $_GPC['op'] : 'display';	
if(!empty($it)){
	if($op == 'GetHealth'){
		$bjid = "{$_GPC['bjid']}";
		//获取班级
		$bj = pdo_fetch("SELECT sname,parentid FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$schoolid}' AND sid = '{$bjid}'");
		//获取年级
		$nj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$schoolid}' AND sid = '{$bj['parentid']}'");
		if($_GPC['leixing'] == 1){
			$type = '进校';
		}elseif($_GPC['leixing'] == 2){
			$type = '离校';
		}elseif($_GPC['leixing'] == 3){
			$type = '体检';
		}
		$title = $nj['sname'].$bj['sname'].$type.'检测';
		//传递过来的日期
		$date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
		$istrue = (strtotime($date)+86399) < time() ? true : false; //是否在今天(在今天不允许点击后一天)
		$time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
		//当前日期对开始与结束
		$start = strtotime($date);
		$end = $start + 86399;
		$condition = " AND schoolid = '{$schoolid}' AND createtime BETWEEN '{$start}' AND '{$end}' ";
        /********************************************当前班级学生症状数据*********************************************/
    
		if($_GPC['leixing'] == 1 || $_GPC['leixing'] == 2){
			if($_GPC['istea']){
				$heatlist = pdo_fetchAll("SELECT c.temperature,t.tname as s_name,t.thumb as icon FROM " .GetTableName('teachers'). "as t RIGHT JOIN " .GetTableName('checklog'). " as c on c.tid = t.id WHERE c.schoolid = '{$schoolid}' AND c.createtime BETWEEN '{$start}' AND '{$end}' AND c.temperature > 37.5 AND c.leixing = '{$_GPC['leixing']}'"); //发热和异常
				$sickleave = pdo_fetchall("SELECT t.tname as s_name,t.thumb as icon FROM ".GetTableName('teachers')." t RIGHT JOIN ".GetTableName('leave')." l on t.id = l.tid WHERE t.schoolid = '{$schoolid}' AND l.isliuyan = 0 AND l.ksid = 0 AND l.kcid = 0 AND l.type='病假' AND l.startime1 <= $start AND l.endtime1 >= $end "); //病假
			}else{
				$heatlist = pdo_fetchAll("SELECT c.temperature,s.s_name,s.icon FROM " .GetTableName('students'). "as s RIGHT JOIN " .GetTableName('checklog'). " as c on c.sid = s.id WHERE c.schoolid = '{$schoolid}' AND c.createtime BETWEEN '{$start}' AND '{$end}' AND c.temperature > 37.5 AND c.leixing = '{$_GPC['leixing']}' AND s.bj_id = '{$_GPC['bjid']}'"); //发热和异常
				$sickleave = pdo_fetchall("SELECT s.s_name,s.icon FROM ".GetTableName('students')." s RIGHT JOIN ".GetTableName('leave')." l on s.id = l.sid WHERE s.schoolid = '{$schoolid}' AND l.isliuyan = 0 AND l.ksid = 0 AND l.kcid = 0 AND l.type='病假' AND l.startime1 <= $start AND l.endtime1 >= $end AND s.bj_id = '{$_GPC['bjid']}'"); //病假
			}
		}elseif($_GPC['leixing'] == 3){
			if(keep_MC()){
				$CheckStu = pdo_fetchAll("SELECT m.id,m.tiwen,m.mouth,s.s_name,s.icon FROM " .GetTableName('students'). "as s RIGHT JOIN " .GetTableName('morningcheck'). " as m on m.sid = s.id WHERE m.is_mc = 1 AND m.schoolid = '{$schoolid}' AND m.createdate = $start AND (tiwen > 37.5 OR mouth = 2 OR cough = 1 OR vomit = 1 OR trauma = 1 OR diarrhea = 1 OR cold = 1 OR headache = 1) AND s.bj_id = '{$_GPC['bjid']}'"); //发热和异常
				foreach ($CheckStu as $key => $value) {
					if($value['tiwen'] > 37.5){
						$heatlist[$key] = $CheckStu[$key]; //发热
					}else{
						$abnormal[$key] = $CheckStu[$key]; //异常
					}
				}
			}else{
				$CheckStu = pdo_fetchAll("SELECT m.id,m.tiwen,m.mouth,s.s_name,s.icon FROM " .GetTableName('students'). "as s RIGHT JOIN " .GetTableName('morningcheck'). " as m on m.sid = s.id WHERE m.is_mc = 0 AND m.schoolid = '{$schoolid}' AND m.createtime BETWEEN '{$start}' AND '{$end}' AND (tiwen > 37.5 OR cough = 1 OR vomit = 1 OR trauma = 1 OR diarrhea = 1 OR cold = 1 OR headache = 1) AND s.bj_id = '{$_GPC['bjid']}'"); //发热和异常
				foreach ($CheckStu as $key => $value) {
					if($value['tiwen'] > 37.5){
						$heatlist[$key] = $CheckStu[$key]; //异常
					}else{
						$abnormal[$key] = $CheckStu[$key]; //发热
					}
				}
			}
        }
		
		include $this->template('comtool/tempforclass');
		exit;
	}elseif($op == 'display'){
		include $this->template(''.$school['style3'].'/tempforclass');
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");	
}
				     
?>