<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-03-03 16:50:12
 * @LastEditTime: 2020-03-10 11:03:16
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
		//传递过来的日期
		$date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
		$istrue = (strtotime($date)+86399) < time() ? true : false; //是否在今天(在今天不允许点击后一天)
		$time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
		//当前日期对开始与结束
		$start = strtotime($date);
		$end = $start + 86399;
		$condition = " AND schoolid = '{$schoolid}' AND createtime BETWEEN '{$start}' AND '{$end}' ";

		/*********************************************进校数据*******************************************/
		//获取学校总人数
		$AllStu = pdo_fetchcolumn("SELECT count(st.id) FROM ".GetTableName('students')." as st , ".GetTableName('classify'). " as cl WHERE  st.bj_id = cl.sid AND st.weid = '{$weid}' AND st.schoolid = '{$schoolid}' AND cl.type = 'theclass' AND cl.is_over != 2");//所有学生
		$AllTea = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}'");//所有老师
		$AllNum = intval($AllStu) + intval($AllTea);
		$InToSchoolCheckStu = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = 1 AND sid != 0 $condition"); //检测学生人数
		$InToSchoolCheckTea = pdo_fetchcolumn("SELECT count(DISTINCT tid) FROM ".GetTableName('checklog')." WHERE leixing = 1 AND tid != 0 $condition"); //检测老师人数
		$InToSchoolCheck = intval($InToSchoolCheckStu) + intval($InToSchoolCheckTea);
		$InToSchoolNoCheck = intval($AllNum) - intval($InToSchoolCheck); //未检测人数
		$InToSchoolCheckRate = round(intval($InToSchoolCheck) / intval($AllNum) * 100,2).'%'; //检测率
		$InToSchoolCheckIsNormal = pdo_fetch("SELECT IFNULL(SUM(case when temperature > 37.5 then 1 else 0 end),0) as abnormal,IFNULL(SUM(case when temperature BETWEEN 35.5 AND 37.5 then 1 else 0 end),0) as normal FROM ".GetTableName('checklog')." WHERE leixing = 1 $condition"); //正常与异常人数
		/*********************************************进校数据*******************************************/
		
		/*********************************************体检数据*******************************************/
		mload()->model('mc');
		$Reoort = GetHealReport($schoolid,$date);
		$NoCheckup = intval($AllStu) - intval($Reoort['Checkup']); //未检测人数
		$CheckupRate = round(intval($Reoort['Checkup']) / intval($AllStu) * 100,2).'%'; //检测率
		/*********************************************体检数据*******************************************/

		/*********************************************离校数据*******************************************/
		$LeaveSchoolCheckStu = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('checklog')." WHERE leixing = 2 AND sid != 0 $condition"); //检测学生人数
		$LeaveSchoolCheckTea = pdo_fetchcolumn("SELECT count(DISTINCT tid) FROM ".GetTableName('checklog')." WHERE leixing = 2 AND tid != 0 $condition"); //检测老师人数
		$LeaveSchoolCheck = intval($LeaveSchoolCheckStu) + intval($LeaveSchoolCheckTea);
		$LeaveSchoolNoCheck = intval($AllNum) - intval($LeaveSchoolCheck); //未检测人数
		$LeaveSchoolCheckRate = round(intval($LeaveSchoolCheck) / intval($AllNum) * 100,2).'%'; //未检测概率
		$LeaveSchoolIsNormal = pdo_fetch("SELECT IFNULL(SUM(case when temperature > 37.5 then 1 else 0 end),0) as abnormal,IFNULL(SUM(case when temperature BETWEEN 35.5 AND 37.5 then 1 else 0 end),0) as normal FROM ".GetTableName('checklog')." WHERE leixing = 2 $condition"); //正常与异常人数
		/*********************************************离校数据*******************************************/
		include $this->template('comtool/healthcenter');
		exit;
	}elseif($op == 'display'){
		include $this->template(''.$school['style3'].'/healthcenter');
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");	
}
				     
?>