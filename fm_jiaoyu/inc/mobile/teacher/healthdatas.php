<?php
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
mload()->model('mc');
if(!empty($it)){
	if($op == 'GetHealth'){
		//传递过来的日期
		$date = $_GPC['date'] ? date("Y-m-d",(strtotime($_GPC['date']) + $_GPC['selectday'] * 86400)) :date('Y-m-d',TIMESTAMP); 
		$istrue = (strtotime($date)+86399) < time() ? true : false; //是否在今天(在今天不允许点击后一天)
		$time = $istrue ? '截止23:59:59' : '截止'.date('H:i:s',TIMESTAMP); //只用作前端显示
		//当前日期对开始与结束
		$start = strtotime($date);
		$end = $start + 86399;
		if(keep_MC()){
			$condition = " AND is_mc = 1 AND createdate = '{$start}'";
		}else{
			$condition = " AND is_mc = 0 AND createtime BETWEEN '{$start}' AND '{$end}' ";
		}
		//获取学校总人数
		$AllStu = pdo_fetchcolumn("SELECT count(st.id) FROM ".GetTableName('students')." as st , ".GetTableName('classify'). " as cl WHERE  st.bj_id = cl.sid AND st.weid = '{$weid}' AND st.schoolid = '{$schoolid}' AND cl.type = 'theclass' AND cl.is_over != 2");
		$AllTea = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' ");
		$AllNum = intval($AllStu) + intval($AllTea);
		/*********************************************每日概览*******************************************/
		if(keep_MC()){
			$Checkup = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE  schoolid = '{$schoolid}' $condition"); //检测人数
			$CheckupIsNormal = pdo_fetch("SELECT IFNULL(SUM(case when (tiwen > 37.5 OR mouth = 2) then 1 else 0 end),0) as abnormal,IFNULL(SUM(case when ((tiwen BETWEEN 35.5 AND 37.5) AND mouth = 1) then 1 else 0 end),0) as normal,IFNULL(SUM(case when tiwen > 37.5 then 1 else 0 end),0) as heat FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' $condition"); //正常与异常人数
		}else{
			$Checkup = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' $condition"); //检测人数
			$CheckupIsNormal = pdo_fetch("SELECT IFNULL(SUM(case when (tiwen > 37.5 OR cough = 1 OR vomit = 1 OR trauma = 1 OR diarrhea = 1 OR cold = 1 OR headache = 1) then 1 else 0 end),0) as abnormal,IFNULL(SUM(case when ((tiwen BETWEEN 35.5 AND 37.5) AND cough = 2 AND vomit = 2 AND trauma = 2 AND diarrhea = 2 AND cold = 2 AND headache = 2) then 1 else 0 end),0) as normal,IFNULL(SUM(case when tiwen > 37.5 then 1 else 0 end),0) as heat FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' $condition"); //正常与异常人数
			// 症状图表
			$symptomchart = pdo_fetch("SELECT IFNULL(SUM(case when cough = 1 then 1 else 0 end),0) as cough,IFNULL(SUM(case when vomit = 1 then 1 else 0 end),0) as vomit,IFNULL(SUM(case when trauma = 1 then 1 else 0 end),0) as trauma,IFNULL(SUM(case when diarrhea = 1 then 1 else 0 end),0) as diarrhea,IFNULL(SUM(case when cold = 1 then 1 else 0 end),0) as cold,IFNULL(SUM(case when headache = 1 then 1 else 0 end),0) as headache FROM ".GetTableName('morningcheck')." WHERE schoolid = '{$schoolid}' $condition"); //正常与异常人数
		}
		$NoCheckup = intval($AllStu) - intval($Checkup); //未检测人数
		$qj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假'  then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end");//请假数量
		/*********************************************每日概览*******************************************/
		include $this->template('comtool/healthdatas');
		exit;
	}elseif($op == 'getechardate'){
		$start = strtotime($_GPC['date']);
		$end = $start + 86399;
		if(keep_MC()){
			$return_data['title'] = ['咳嗽','呕吐','外伤','腹泻','感冒','头痛','口腔异常'];
		}else{
			$return_data['title'] = ['咳嗽','呕吐','外伤','腹泻','感冒','头痛'];
		}
		$symptomchart = GetHealCharts($schoolid,$_GPC['date']);
		$return_data['data'] = array_values($symptomchart['symptomchart']);
		die(json_encode($return_data));
	}elseif($op == 'GetSymptom'){
		$data = GetSymptom($schoolid,$_GPC['day']);
		die ( json_encode ( $data ) );
	}elseif($op == 'GetTrend'){
		$data = GetTrend($schoolid,$_GPC['day']);
		die ( json_encode ( $data ) );
	}elseif($op == 'GetSick'){
		$nowday = strtotime(date("Y-m-d",time()));
		$AllStu = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' ");
		for($i=$_GPC['day'];$i>=0;$i--){
			$first= $nowday-$i*86400;
			$last = $nowday-$i*86400 + 86399;
			$qj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假'  then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE tid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $first && endtime1 >= $last");//请假数量
			$sj[$i] = $qj['sj'];
			$bj[$i] = $qj['bj'];
			$zc[$i] = intval($AllStu) - intval($qj['sj']) - intval($qj['bj']);
			$createtime[$i] = date("m-d",$first);
		}
		$return_data['createtime'] = array_values($createtime);
		$return_data['sj'] = array_values($sj);
		$return_data['bj'] = array_values($bj);
		die ( json_encode ( $return_data ) );
	}elseif($op == 'GetTeaSick'){
		$nowday = strtotime(date("Y-m-d",time()));
		for($i=$_GPC['day'];$i>=0;$i--){
			$first= $nowday-$i*86400;
			$last = $nowday-$i*86400 + 86399;
			$qj = pdo_fetch("SELECT IFNULL(SUM(case when type = '事假' then 1 else 0 end),0) as sj,IFNULL(SUM(case when type = '病假'  then 1 else 0 end),0) as bj FROM ".GetTableName('leave')." WHERE sid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $first && endtime1 >= $last");//请假数量
			$sj[$i] = $qj['sj'];
			$bj[$i] = $qj['bj'];
			$createtime[$i] = date("m-d",$first);
		}
		$return_data['createtime'] = array_values($createtime);
		$return_data['sj'] = array_values($sj);
		$return_data['bj'] = array_values($bj);
		die ( json_encode ( $return_data ) );
	}elseif($op == 'display'){
		include $this->template(''.$school['style3'].'/healthdatas');
	}
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");	
}
				     
?>