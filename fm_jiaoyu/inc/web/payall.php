<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$action = 'payall';
$this1 = 'no4';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$schoolid = intval($_GPC['schoolid']);
$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
$NjList = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND type = 'semester' ");
$BjList = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND type = 'theclass' ");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1002101,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if ($operation == 'post') {
	load()->func('tpl');
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE id = :id ", array(':id' => $id));
		if (empty($item)) {   
			$this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
		}
	}
} elseif ($operation == 'display') {
	$allkc = pdo_fetchall("SELECT id,name FROM " . tablename($this->table_tcourse) . " WHERE weid = :weid And schoolid = :schoolid ", array(
			':weid' => $weid,
			':schoolid' => $schoolid
			));
	$allob = pdo_fetchall("SELECT id,name FROM " . tablename($this->table_cost) . " WHERE weid = :weid And schoolid = :schoolid ", array(
			':weid' => $weid,
			':schoolid' => $schoolid
			));
	$allvod = pdo_fetchall("SELECT id,name FROM " . tablename($this->table_allcamera) . " WHERE weid = :weid And schoolid = :schoolid And is_pay = :is_pay", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':is_pay' => 1
			));					
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	$condition = '';
	if (!empty($_GPC['number'])) {
		$condition .= " AND id = '{$_GPC['number']}'";
	}
	if (!empty($_GPC['uniontid'])) {
		$condition .= " AND uniontid = '{$_GPC['uniontid']}'";
	}			
	if (!empty($_GPC['type'])) {
		$type = intval($_GPC['type']);
		$condition .= " AND type = '{$type}' ";
	}
	if (!empty($_GPC['paytype'])) {
		$paytype = intval($_GPC['paytype']);
		$condition .= " AND paytype = '{$paytype}' ";
	}								
	if (!empty($_GPC['obid'])) {
		$obid = intval($_GPC['obid']);
		$condition .= " AND costid = '{$obid}'";
	}
	if (!empty($_GPC['kcid'])) {
		$condition .= " AND kcid = '{$_GPC['kcid']}'";
	}
	if (!empty($_GPC['vodid'])) {
		$condition .= " AND vodid = '{$_GPC['vodid']}'";
	}			
	if (!empty($_GPC['costid'])) {
		$condition .= " AND costid = '{$_GPC['costid']}'";
	}			
	$is_pay = isset($_GPC['is_pay']) ? intval($_GPC['is_pay']) : -1;
	if($is_pay >= 0) {
		$condition .= " AND status = '{$is_pay}'";
		$params[':is_pay'] = $is_pay;
	}			
	if(!empty($_GPC['createtime'])) {
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
	} else {
		$starttime = strtotime('-600 day');
		$endtime = TIMESTAMP;
	}
	if (!empty($_GPC['keyword'])) {
		$stuid = '';
		$students = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ", array(':schoolid' => $schoolid,':s_name' => trim($_GPC['keyword'])));
		
		$numofstudent = count($students);
		if($numofstudent == 1 && !empty($students) ){
			$stuid = $students[0]['id'];
				$condition_t = " AND sid = '{$stuid}'";
		}elseif($numofstudent > 1){
			$temp = '';
			for( $i=0;$i<$numofstudent;$i++ )
			{
				$temp .="{$students[$i]['id']}";
				if($i != $numofstudent -1 ){
					$temp .=',';
				}
			}
			$condition_t = " AND sid in ({$temp})";
			
		}
		
		if(!empty($students))
		{
			$condition .= $condition_t;
		}elseif(empty($students))
		{
			$tea = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And tname = :tname ", array(':schoolid' => $schoolid,':tname' => $_GPC['keyword']));
			$teaUser = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " WHERE schoolid = :schoolid And tid = :tid ", array(':schoolid' => $schoolid,':tid' => $tea['id']));
			if(!empty($tea))
			{
					$condition .= " AND userid = '{$teaUser['id']}'";
			}
		}
		
	}	
	$search_nj = $_GPC['nj'] ? $_GPC['nj'] : 0 ;
	$search_bj = $_GPC['bj'] ? $_GPC['bj'] : 0 ;
	$condition_cell = '';
	if($search_nj != 0 ){
		$condition_cell .= " and xq_id = '{$search_nj}' ";
	}
	if($search_bj != 0 ){
		$condition_cell .= " and bj_id = '{$search_bj}' ";
	}

	if(!empty($condition_cell)){
		$condition .= " and sid IN ( SELECT id FROM ".GetTableName('students')." WHERE schoolid ='{$schoolid}' {$condition_cell} ) ";
	}
	$params[':start'] = $starttime;
	$params[':end'] = $endtime;
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY id DESC,paytime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $index => $row){
		$kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
		$student = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
		
		$bjinfo = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid ", array(':sid' => $student['bj_id']));
		if(!empty($row['tempsid']) && empty($row['sid'])){
			$student = pdo_fetch("SELECT sname as s_name FROM ".GetTableName('tempstudent')." WHERE  id = '{$row['tempsid']}'  ");
			$bjinfo = array(
				'sname' => '临时学生'
			);

		}
		$user = pdo_fetch("SELECT realname,mobile,pard,tid,sid FROM " . tablename($this->table_user) . " WHERE id = :id ", array(':id' => $row['userid']));
		$ob = pdo_fetch("SELECT name FROM " . tablename($this->table_cost) . " WHERE id = :id ", array(':id' => $row['costid']));
		$signup = pdo_fetch("SELECT name,pard,mobile FROM " . tablename($this->table_signup) . " WHERE orderid = :orderid ", array(':orderid' => $row['id']));
		if($row['pay_type'] != 'wxapp'){
			$payweid = pdo_fetch("SELECT name FROM " . tablename('account_wechats') . " where uniacid = :uniacid ", array(':uniacid' => $row['payweid']));
		}else{
			$payweid = pdo_fetch("SELECT name FROM " . tablename('account_wxapp') . " where uniacid = :uniacid ", array(':uniacid' => $row['payweid']));
		}
		$list[$index]['kcname'] = $kc['name'];
		$list[$index]['s_name'] = $student['s_name'];
		if(!empty($user)){
			$list[$index]['mobile'] = $user['mobile'];
			$list[$index]['realname'] = $user['realname'];
		}else{
			if($list[$index]['tid'] == -1) {
				$list[$index]['who'] = "管理员";
			}elseif($list[$index]['tid'] != -1){
				if($list[$index]['tid'] =='founder'){
					$list[$index]['who'] = '站长';
				}elseif($list[$index]['tid'] =='owner'){
					$list[$index]['who'] = '主管理员';
				}else{
					$list[$index]['who'] = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $list[$index]['tid']))['tname'];
				}
			}
		}
		if($list[$index]['type'] == 6 ){
			$mallorderid = $row['morderid'];
			$mallorderinfo = pdo_fetch("SELECT * FROM ".GetTableName('mallorder')." WHERE id = '{$mallorderid}' ");
			if($mallorderinfo['tid'] != 0 && $mallorderinfo['sid'] == 0){
				$teacher = pdo_fetch("SELECT tname,mobile FROM " . tablename($this->table_teachers) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $mallorderinfo['tid'],':weid' => $weid,':schoolid' => $schoolid));
				$list[$index]['t_name'] = $teacher['tname'];
				$list[$index]['s_name'] = $teacher['tname'];
				$list[$index]['t_phone'] = $teacher['mobile'];
				$list[$index]['bjname'] = '教师';
			}elseif($mallorderinfo['tid'] == 0 && $mallorderinfo['sid'] != 0 && $mallorderinfo['userid'] != 0 ){
				$student_mall = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $mallorderinfo['sid'],':weid' => $weid,':schoolid' => $schoolid));
				if(!empty($student_mall)){
					$list[$index]['s_name'] = $student_mall['s_name'];
					$bjinfos = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid ", array(':sid' => $student_mall['bj_id']));
					$list[$index]['bjname'] = $bjinfos['sname'];
				}else{
					$list[$index]['bjname'] = $bjinfo['sname'];
				}
			}								
		}
		if($list[$index]['type'] == 7 ){
			$vod = pdo_fetch("SELECT name FROM " . tablename($this->table_allcamera) . " WHERE id = :id ", array(':id' => $row['vodid']));
			$list[$index]['vodname'] = $vod['name'];
		}

		if($list[$index]['type'] == 8 ){
			$taocan = pdo_fetch("SELECT chongzhi FROM " . tablename($this->table_chongzhi) . " WHERE id = :id ", array(':id' => $row['taocanid']));
			$list[$index]['chongzhi'] = $taocan['chongzhi'];
		}
		$list[$index]['pard'] = $user['pard'];
		$list[$index]['obname'] = $ob['name'];
		$list[$index]['signname'] = $signup['name'];
		$list[$index]['signpard'] = $signup['pard'];
		$list[$index]['signmob'] = $signup['mobile'];
		if($row['type'] != 6 )
		{
			
			$list[$index]['bjname'] = $bjinfo['sname'];
		}
		
		if($list[$index]['type'] == 20 ){
			$file = pdo_fetch("SELECT title FROM " . GetTableName('growupfile') . " WHERE id = :id ", array(':id' => $row['extra_info']));
			$list[$index]['growuptitle'] = $file['title'];
		}

		$list[$index]['payweidname'] = $payweid['name'];

		if($row['redpacketlogid']){// 红包
			$redpacket = pdo_fetch("SELECT w.maxrange,w.cose FROM ".GetTableName('welfare')." as w RIGHT JOIN " . GetTableName('welfarelog') . " as wf ON wf.welfareid = w.id WHERE wf.id = '{$row['redpacketlogid']}' ");
			$list[$index]['redpacketinfo'] = "满{$redpacket['maxrange']}减{$redpacket['cose']}";
			// var_dump("SELECT w.maxrange,w.cose FROM ".GetTableName('welfare')." as w RIGHT JOIN " . GetTableName('welfarelog') . " as wf ON wf.welfareid = w.id WHERE wf.id = '{$row['redpacketlogid']}' ");die;
		}

		if($row['couponlogid']){// 红包
			$coupon = pdo_fetch("SELECT w.maxrange,w.cose FROM ".GetTableName('welfare')." as w RIGHT JOIN " . GetTableName('welfarelog') . " as wf ON wf.welfareid = w.id WHERE wf.id = '{$row['couponlogid']}' ");
			$list[$index]['couponinfo'] = "满{$coupon['maxrange']}减{$coupon['cose']}";
		}

	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");

	$pager = pagination($total, $pindex, $psize);			
	/*导出数据*/
	if ($_GPC['out_put'] == 'output') {
		$list = pdo_fetchall("SELECT * FROM " . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY id DESC");
		$ii = 0;
		foreach($list as $index => $row){
			$kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
			$student = pdo_fetch("SELECT s_name,bj_id,numberid FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
			$bjinfo = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid ", array(':sid' => $student['bj_id']));
			$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE id = :id ", array(':id' => $row['userid']));
			$ob = pdo_fetch("SELECT name FROM " . tablename($this->table_cost) . " WHERE id = :id ", array(':id' => $row['costid']));
			$signup = pdo_fetch("SELECT * FROM " . tablename($this->table_signup) . " WHERE orderid = :orderid ", array(':orderid' => $row['id']));
			if($list[$index]['type'] == 7 ){
				$vod = pdo_fetch("SELECT name FROM " . tablename($this->table_allcamera) . " WHERE id = :id ", array(':id' => $row['vodid']));
				$list[$index]['vodname'] = $vod['name'];
			}							
			$arr[$ii]['id'] = $row['id'];
			if($row['type']==4){
				$kcname = '报名费';
			}else if($row['type']==5){
				$kcname = '考勤卡费';
			}else if($row['type']==6){
				$kcname = '商城订单';
			}else if($row['type']==8){
				$kcname = '余额充值';
			}else if($row['type']==9){
				$kcname = '充电桩充次';
			}else if($row['type']==7){
				$vod = pdo_fetch("SELECT name FROM " . tablename($this->table_allcamera) . " WHERE id = :id ", array(':id' => $row['vodid']));						
				$kcname = $vod['name'];
			}else{
				$kcname = $ob['name'].$kc['name'];
			}

			$arr[$ii]['project'] = $kcname;
			$arr[$ii]['student'] = ($row['type']!=4)?$student['s_name']:$signup['name'];
			$arr[$ii]['xuehao'] = !empty($student['numberid'])? $student['numberid']:'未填写';
			$arr[$ii]['classname'] = $bjinfo['sname'];
			if($row['type'] == 6 ){
				$mallorderid = $row['morderid'];
				$mallorderinfo = pdo_fetch("SELECT * FROM ".GetTableName('mallorder')." WHERE id = '{$mallorderid}' ");
				if($mallorderinfo['tid'] != 0 && $mallorderinfo['sid'] == 0){
					$teacher = pdo_fetch("SELECT tname,mobile FROM " . tablename($this->table_teachers) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $mallorderinfo['tid'],':weid' => $weid,':schoolid' => $schoolid));
					$arr[$ii]['student'] = $teacher['tname'];
					$arr[$ii]['classname'] = '教师';
				}elseif($mallorderinfo['tid'] == 0 && $mallorderinfo['sid'] != 0 && $mallorderinfo['userid'] != 0 ){
					$student_mall = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $mallorderinfo['sid'],':weid' => $weid,':schoolid' => $schoolid));
					if(!empty($student_mall)){
						$arr[$ii]['student'] = $student_mall['s_name'];
						$bjinfos = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid ", array(':sid' => $student_mall['bj_id']));
						$arr[$ii]['classname'] = $bjinfos['sname'];
					}else{
						$arr[$ii]['classname'] = $bjinfo['sname'];
					}
				}
			}
			if(is_array($myuser)){
				$arr[$ii]['signpard'] = !empty($user['realname'])? $user['realname']:'未填写';
				$arr[$ii]['mobile'] = !empty($user['mobile'])? $user['mobile']:'未填写';
			}else{
				$arr[$ii]['signpard'] = !empty($user['realname'])? $user['realname']:'未填写';
				$arr[$ii]['mobile'] = !empty($user['mobile'])? $user['mobile']:'未填写';								
			}
			$arr[$ii]['paytime'] = date('Y年m月d日 h:i:sa',$row['paytime']);
			$arr[$ii]['cose'] = $row['cose'];
			if($row['status']=='1'){
				$status = '未支付';
			}
			else if($row['status']=='2'){
				$status = '已支付';
			}
			else if($row['status']=='3'){
				$status = '已退款';
			}
			$arr[$ii]['status'] = $status;
			if($row['pay_type']=='wechat'){
				$pay_type = '微信支付';
			}
			else if($row['pay_type']=='alipay'){
				$pay_type = '支付宝';
			}
			else if($row['pay_type']=='baifubao'){
				$pay_type = '百付宝';
			}
			else if($row['pay_type']=='unionpay'){
				$pay_type = '银联';
			}
			else if($row['pay_type']=='cash'){
				$pay_type = '现金支付';
			}
			else if($row['pay_type']=='credit'){
				$pay_type = '余额支付';
			}
			$arr[$ii]['pay_type'] = $pay_type;
			if($row['paytype']=='1'){
				$paytype = '在线支付';
			}
			else if($row['paytype']=='2'){
				$paytype = '现金支付';
			}
			$arr[$ii]['paytype'] = $paytype;
			if ($_W['isfounder'] || $_W['role'] == 'owner'){
				$payweid = pdo_fetch("SELECT name FROM " . tablename('account_wechats') . " where uniacid = :uniacid ", array(':uniacid' => $row['payweid']));
				$arr[$ii]['payacuont'] = $payweid['name'];
			}
			$arr[$ii]['ksnum'] = $row['ksnum'];
			$ii++;
		}
		if ($_W['isfounder'] || $_W['role'] == 'owner'){
			$this->exportexcel($arr, array('订单号','项目名','学生','学号','班级','缴费人','手机号','付费时间','金额','支付状态','支付方式','支付类型','收款公众号','包含课时节数'), time());
		}else{
			$this->exportexcel($arr, array('订单号','项目名','学生','学号','班级','缴费人','手机号','付费时间','金额','支付状态','支付方式','支付类型','包含课时节数'), time());
		}
		exit();
	}
} elseif ($operation == 'tuifei') {
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE id = {$id}");
	if (empty($item)) {
		$this->imessage('抱歉，本条信息不存在在或是已经被删除！');
	}
	if ($item['status'] == 3) {
		$this->imessage('抱歉，本订单已退费，无需重复操作');
	}
	$refund = get_schoolset($schoolid,'refund');
	if($item['uniontid'] && ($item['pay_type'] == 'wxapp' || $item['pay_type'] == 'wechat') && $refund == 1){
		mload()->model('wxpay');
		$payweid = empty($item['payweid'])?$item['weid']:$item['payweid'];
		$wxPay = new WxpayService($payweid,$item['pay_type']);
		$result = $wxPay->doRefund($item['cose'], $item['cose'], $item['uniontid'],$orderNo,$payweid);
		if($result===true){
			$data = array('status' => 3,'refundid'=>$item['id'],'tuitime'=>time()); 
			pdo_update($this->table_order, $data, array('id' => $id));
			$this->imessage('退费成功,改订单费用已原路退回买家付款微信帐号！', referer(), 'success');
			if($item['team_id']){
				pdo_update(GetTableName('sale_team',false), array('tfuser' => $tid_global,'tuifei' => 3), array('id' => $item['team_id']));
			}
			if($item['spoint'] > 0 && $item['sid'] > 0){//返还积分
				$stu =  pdo_fetch("SELECT points FROM " . GetTableName('order') . " WHERE id = {$item['sid']}");
				pdo_update(GetTableName('students',false), array('points' => $stu['points'] + $item['spoint']), array('id' => $item['sid']));
			}
		}else{
			$this->imessage('退费失败', referer(), 'error');
		}
	}else{
		$data = array('status' => 3, 'tuitime'=>time()); 
		pdo_update($this->table_order, $data, array('id' => $id));
		$stu =  pdo_fetch("SELECT points,chongzhi FROM " . GetTableName('order') . " WHERE id = {$item['sid']}");
		if($item['team_id']){
			pdo_update(GetTableName('sale_team',false), array('tfuser' => $tid_global,'tuifei' => 3), array('id' => $item['team_id']));
		}
		if($item['pay_type'] == 'credit'){
			pdo_update(GetTableName('students',false), array('chongzhi' => $stu['chongzhi'] + $item['cose']), array('id' => $item['sid']));
		}
		if($item['spoint'] > 0 && $item['sid'] > 0){//返还积分
			pdo_update(GetTableName('students',false), array('points' => $stu['points'] + $item['spoint']), array('id' => $item['sid']));
		}
		$this->imessage('操作成功！', referer(), 'success');
	}
} elseif ($operation == 'uniontid') {
	$id = intval($_GPC['id']);
	$order = pdo_fetch("SELECT id,payweid,wxpayid,pay_type FROM " . GetTableName('order') . " WHERE id = '{$id}'");
	$core_paylog = pdo_fetch("SELECT plid,uniontid,tag,type,uniacid FROM " . tablename('core_paylog') . " where tid = :tid ", array(':tid' => $order['wxpayid']));
	if(!empty($core_paylog)){
		if($order['pay_type'] == 'wechat' || $order['pay_type'] == 'wxapp'){
			mload()->model('wxpay');
			$payweid = empty($core_paylog['uniacid'])?$order['payweid']:$core_paylog['uniacid'];
			$wxPay = new WxpayService($payweid,$order['pay_type']);
			$query = json_decode(json_encode($wxPay->orderquery($core_paylog['uniontid'])),true);
			if(!empty($query['transaction_id'])){
				$result['result'] = true;
				$result['uniontid'] = $query['transaction_id'];
				pdo_update(GetTableName('order',false), array('uniontid' => $query['transaction_id']), array('id' => $id));
			}else{
				$result['result'] = false;
				$result['msg'] = '无法查询到真实订单,微信支付未返回结果';	
			}
			$result['query'] = $query;
		}else{
			$core_paylog = iunserializer($core_paylog['tag']);
			if($core_paylog['transaction_id']){
				$result['result'] = true;
				$result['uniontid'] = $core_paylog['transaction_id'];
				pdo_update(GetTableName('order',false), array('uniontid' => $core_paylog['transaction_id']), array('id' => $id));
			}else{
				$result['result'] = false;
				$result['msg'] = '无法查询到真实订单,支付商未返回结果';	
			}
		}
	}else{
		$result['result'] = false;
		$result['msg'] = '该订单无效或已被删除';
	}
	die(json_encode($result));
} elseif ($operation == 'print') {
	$id = intval($_GPC['id']);
	mload()->model('print');
	$item = pdo_fetch("SELECT type FROM " . GetTableName('order') . " WHERE id = {$id}");
	if (empty($item)) {
		$this->imessage('抱歉，本条信息不存在在或是已经被删除！');
	}
	$isset = GetPrinterArrByOrderType($item['type'],$schoolid,$id);
	if (empty($isset['printarr'])) {
		$this->imessage('抱歉，本类型订单未设置打印规则或未启用打印设备！');
	}
	$status = order_print($id, true);
	if(is_error($status)) {
			$this->imessage($status['message']);
	}
	$this->imessage('成功发送打印命令！', referer(), 'success');
} elseif ($operation == 'pay') {
	$id = intval($_GPC['id']);
	if (empty($id)){
		$this->imessage('抱歉，本条信息不存在在或是已经被删除！');
	}
	$data = array('status' => 2,'paytime' => time(),'paytype' => 2,'tid' => $tid_global,'pay_type' => 'cash'); 
	pdo_update($this->table_order, $data, array('id' => $id));
	$temporder = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} And id = {$id}");
	mload()->model('pay');
	if(!empty($temporder['morderid']))
	{
		//商城订单OK
		DealMallPayResult($temporder);
	}
	//课程订单暂时未处理
	if($temporder['type'] == 1){
		DealKcPayResult($temporder);
		// if($temporder['tempsid'] != 0){
		// 	$tempstu = pdo_fetch("SELECT * FROM " . tablename($this->table_tempstudent) . " where :id = id", array(':id' => $temporder['tempsid']));
		// 	$randStr = str_shuffle('123456789');
		// 	$rand = substr($randStr,0,6);	
		// 	$tempstudata = array(
		// 		'schoolid' => $tempstu['schoolid'],
		// 		'bj_id'=> $tempstu['bj_id'],
		// 		'sex' => $tempstu['sex'],
		// 		'createdate'=> time(),
		// 		'seffectivetime' => time(),
		// 		'code' => $rand,
		// 		's_name' => $tempstu['sname'],
		// 		'mobile'=> $tempstu['mobile'],
		// 		'area_addr'=> $tempstu['adde'],
		// 		'weid' => $tempstu['weid'],
		// 	);
		// 	pdo_insert($this->table_students,$tempstudata);
		// 	$sid = pdo_insertid();
		// 	pdo_update($this->table_students,array('keyid'=> $sid),array('id'=>$sid));
		// 	$userinsert = array(
		// 		'sid' => $sid,
		// 		'weid' => $tempstu['weid'],
		// 		'schoolid' => $tempstu['schoolid'],
		// 		'uid' => $tempstu['uid'],
		// 		'openid' => $tempstu['openid'],
		// 		'pard' => $tempstu['pard'],
		// 	);
		// 	pdo_insert($this->table_user,$userinsert);
		// 	$userid_tostu = pdo_insertid();
		// 	$into_stu = array();
		// 	if($tempstu['pard'] == 2){
		// 		$into_stu['mom'] = $tempstu['openid'];
		// 		$into_stu['muserid'] = $userid_tostu;
		// 		$into_stu['muid'] = $tempstu['uid']; 
		// 	}
		// 	if($tempstu['pard'] == 3){
		// 		$into_stu['dad'] = $tempstu['openid'];
		// 		$into_stu['duserid'] = $userid_tostu;
		// 		$into_stu['duid'] = $tempstu['uid']; 
		// 	}
		// 	if($tempstu['pard'] == 4){
		// 		$into_stu['own'] = $tempstu['openid'];
		// 		$into_stu['ouserid'] = $userid_tostu;
		// 		$into_stu['ouid'] = $tempstu['uid']; 
		// 	}
		// 	if($tempstu['pard'] == 5){
		// 		$into_stu['other'] = $tempstu['openid'];
		// 		$into_stu['otheruserid'] = $userid_tostu;
		// 		$into_stu['otheruid'] = $tempstu['uid']; 
		// 	}
		// 	pdo_update($this->table_students,$into_stu,array('id'=>$sid));
		// 	$into_order = array(
		// 		'userid' => $userid_tostu,
		// 		'sid' => $sid
		// 	);
		// 	pdo_update($this->table_order,$into_order,array('id'=>$temporder['id']));
		// 	$temporder = pdo_fetch("SELECT * FROM " . GetTableName('order') . " where id = :id ", array(':id' =>$temporder['id']));
		// }
		// if(!empty($temporder['ksnum'])){
		// 	$kcinfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where :id = id", array(':id' => $temporder['kcid']));
		// 	$userinfo = pdo_fetch("SELECT sid FROM " . tablename($this->table_user) . " where :id = id", array(':id' => $temporder['userid']));
		// 	$ygks = pdo_fetch("SELECT ksnum,id FROM " . tablename($this->table_coursebuy) . " where kcid=:kcid AND :sid = sid", array(':kcid' => $temporder['kcid'],':sid'=>$userinfo['sid']));
		//     $overday = $kcinfo['overtimeday'];
		//     $overtime = 0;
		//     if($overday != 0 ){
		//         $overtime = strtotime(date("Y-m-d",time())) + 86399 + 86400*$overday;
		//     }

		// 	if(!empty($ygks)){
		// 		$newksnum = $ygks['ksnum'] + $temporder['ksnum'];
		// 		$data_coursebuy = array(
		// 			'ksnum'      => $newksnum,
		//             'overtime'  => $overtime
		// 		);
		// 		pdo_update($this->table_coursebuy,$data_coursebuy,array('id' => $ygks['id']));
		// 	}elseif(empty($ygks)){
		// 			$userinfo = pdo_fetch("SELECT sid FROM " . tablename($this->table_user) . " where :id = id", array(':id' => $temporder['userid']));
		// 		$data_coursebuy = array(
		// 			'weid'       => $temporder['weid'],
		// 			'schoolid'   => $temporder['schoolid'],
		// 			'userid'     => $temporder['userid'],
		// 			'sid'        => $userinfo['sid'],
		// 			'kcid'       => $temporder['kcid'],
		// 			'orderid'    => $temporder['id'],
		// 			'ksnum'      => $kcinfo['FirstNum'],
		// 			'createtime' => time(),
		//             'overtime'  => $overtime
		// 		);
		// 		pdo_insert($this->table_coursebuy,$data_coursebuy);
		// 	}
		// }
		// $shareset_t = pdo_fetch("SELECT shareset FROM " . tablename($this->table_index) . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $temporder['schoolid'],':weid'=>$temporder['weid'] ));
		// $shareset = unserialize($shareset_t['shareset']);
		
		// if($shareset['is_share'] != 0 ){
			
		//  	if($temporder['shareuserid'] != 0){
		// 		$sharesid = pdo_fetch("SELECT sid FROM " . tablename($this->table_user) . " where :id = id", array(':id' => $temporder['shareuserid']));
		//  		$student_share = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where :id = id", array(':id' => $sharesid['sid']));
		// 		$temp_student = array();
		// 		//给分享源用户新增积分、余额、课时
		// 		if($shareset['is_share'] == 1){
		// 			//新增积分
		// 			$AddJF = $shareset['addJF'];
		// 			$oldJF = $student_share['points'];
		// 			$newJF = $AddJF + $oldJF;
		// 			//$temp_student['points'] = $newJF;
		// 			pdo_update($this->table_students, array('points' => $newJF ), array('id' => $sharesid['sid']));
		// 		}elseif($shareset['is_share'] == 2){
		// 			//新增余额
		// 			$AddYE = $shareset['addYE'];
		// 			$oldYE = $student_share['chongzhi'];
		// 			$newYE = $AddYE + $oldYE;
		// 			//$temp_student['chongzhi'] = $newYE;
		// 			pdo_update($this->table_students, array('chongzhi' => $newYE ), array('id' => $sharesid['sid']));
		// 		}elseif($shareset['is_share'] == 3){
		// 			//新增课时
		// 			$AddKC = $temporder['kcid'];
		// 			$AddKS = $shareset['addKS'];
		// 			$kcinfo_share =  pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where :id = id", array(':id' => $AddKC));
		// 			$coursebuy =  pdo_fetch("SELECT ksnum,id FROM " . tablename($this->table_coursebuy) . " where kcid=:kcid AND :sid = sid", array(':kcid' => $AddKC,':sid'=>$sharesid['sid']));
		// 			if(!empty($coursebuy)){
		// 				$newksnum = $coursebuy['ksnum'] + $AddKS;
		// 				if($newksnum > $kcinfo_share['AllNum']){
		// 					$newksnum = $kcinfo_share['AllNum'];
		// 				}
		// 				pdo_update($this->table_coursebuy, array('ksnum' => $newksnum ),  array('id' => $coursebuy['id']));
		// 			}
		// 		} 
		// 	} 
		// }
	}elseif($temporder['type'] == 8){
		//充值订单OK
		DealCzPayResult($temporder);
		$this->sendMobileJfjgtz($temporder['id']);
	}elseif($temporder['type'] == 9){
		//充电桩充值OK
		DealCdzCzPayResult($temporder);
	}elseif($temporder['type'] == 5){
		//考勤卡续费OK
		DealKqkfPayResult($temporder);
		$this->sendMobileJfjgtz($temporder['id']);
	}elseif($temporder['type'] == 4){
		//报名付费OK
		$sign = pdo_fetch("SELECT name,nj_id FROM " . GetTableName('signup') . " where :id = id", array(':id' => $temporder['signid']));
		$njinfo = pdo_fetch("SELECT tid FROM " . GetTableName('classify') . " WHERE :sid = sid ", array(':sid' => $sign['nj_id']));
		$njzr = pdo_fetch("SELECT openid FROM " . GetTableName('teachers') . " WHERE :id = id ", array(':id' => $njinfo['tid']));
		if(!empty($njzr)){
			$this->sendMobileBmshtz($temporder['signid'], $temporder['schoolid'], $temporder['weid'], $njzr['openid'], $sign['name']);
		}		
	}
	else{
		//考勤消费处理
		if($temporder['type'] == 3){ //功能消费
			$hasKqXf = pdo_fetch("SELECT id,serverend FROM ".GetTableName('cost')." WHERE id = '{$temporder['costid']}' ");
			if(!empty($hasKqXf) && $hasKqXf['serverend']>0){ //考勤卡消费，开通考勤消费通知
				pdo_update(GetTableName('students',false),array('isopen'=>1),array('id'=>$temporder['sid'])); 
				pdo_update(GetTableName('idcard',false),array('severend'=>$hasKqXf['serverend']),array('sid'=>$temporder['sid'],'pard'=>1)); 
			}
		}
		$this->sendMobileJfjgtz($temporder['id']);
	}
	
	$this->imessage('操作成功！', referer(), 'success');
} elseif ($operation == 'unpay') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		$this->imessage('抱歉，本条信息不存在在或是已经被删除！');
	}
	$data = array('status' => 1,'paytime' => '','paytype' => 3,'pay_type' => 'no'); 
	pdo_update($this->table_order, $data, array('id' => $id));
	pdo_delete(core_paylog,array('tid' => $id));
	$temporder = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} And id = {$id}");
	$Morder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} And id = {$temporder['morderid']}");
	$Mdata = array('status' => 1);
		if(!empty($Morder))
	{
			pdo_update($this->table_mallorder, $Mdata, array('id' => $Morder['id']));
	}
	$this->imessage('操作成功！', referer(), 'success');
} elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		$this->imessage('抱歉，本条信息不存在在或是已经被删除！');
	}
	$temporder = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} And id = {$id}");
	if(!empty($temporder['morderid']))
	{
		pdo_delete($this->table_mallorder, array('id' => $temporder['morderid']));
	}
	if(!empty($temporder['ksnum']) &&$temporder['status'] == 2){
		$kcinfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where :id = id", array(':id' => $temporder['kcid']));
		$userinfo = pdo_fetch("SELECT sid FROM " . tablename($this->table_user) . " where :id = id", array(':id' => $temporder['userid']));
		$ygks = pdo_fetch("SELECT ksnum,id FROM " . tablename($this->table_coursebuy) . " where kcid=:kcid AND :sid = sid", array(':kcid' => $temporder['kcid'],':sid'=>$userinfo['sid']));
		$newksnum = $ygks['ksnum'] - $temporder['ksnum'];
		if($newksnum != 0){
				$data_coursebuy = array(
					'ksnum'      => $newksnum,
				);
				pdo_update($this->table_coursebuy,$data_coursebuy,array('id' => $ygks['id']));
			}elseif($newksnum == 0){
				pdo_delete($this->table_coursebuy,array('id' => $ygks['id']));
			}
	}

	pdo_delete($this->table_order, array('id' => $id));
	pdo_delete(core_paylog,array('tid' => $id));
	if($_GPC['form'] == 'kcinfo'){
		$data ['result'] = true;
		$data ['msg'] = $message;
		die (json_encode($data));
	}else{
		$this->imessage('操作成功！', referer(), 'success');
	}
} elseif ($operation == 'payallorder') {
	$rowcount = 0;
	$notrowcount = 0;
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		if (!empty($id)) {
			$goods = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE id = :id", array(':id' => $id));
			if (empty($goods)) {
				$notrowcount++;
				continue;
			}
			$data = array('status' => 2,'paytime' => time(),'paytype' => 2,'pay_type' => 'cash');
			pdo_update($this->table_order, $data, array('id' => $id));
			if(!empty($goods['morderid'])){
				$Mdata= array('status' => 2 );
				pdo_update($this->table_mallorder, $Mdata, array('id' => $goods['morderid']));
			}
			$rowcount++;
		}
	}
	message("操作成功！");
} elseif ($operation == 'deleteall') {
	$rowcount = 0;
	$notrowcount = 0;
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		if (!empty($id)) {
			$goods = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE id = :id", array(':id' => $id));
			if (empty($goods)) {
				$notrowcount++;
				continue;
			}
			pdo_delete($this->table_order, array('id' => $id));
			pdo_delete(core_paylog,array('tid' => $id));
			if(!empty($goods['morderid'])){
				pdo_delete($this->table_mallorder, array('id' => $goods['morderid']));
			}
			$rowcount++;
		}
	}
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

	$data ['result'] = true;

	$data ['msg'] = $message;

	die (json_encode($data));
}elseif ($operation == 'getorderpayinfo') {
	$orderid = $_GPC['orderid'];
	$order = pdo_fetch("SELECT * FROM " . GetTableName('order') . " WHERE id = :id", array(':id' => $orderid));
	$user = pdo_fetch("SELECT userinfo,pard,tid,sid FROM " . tablename($this->table_user) . " WHERE id = :id ", array(':id' => $order['userid']));
	$student = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $user['sid']));
	$bjinfo = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid ", array(':sid' => $student['bj_id']));
	$orderName = '';
	$this_name = $student['s_name'];
	$addinfo = $bjinfo['sname'];
		
	if($order['type'] == 8){
		$taocan = pdo_fetch("SELECT chongzhi FROM " . tablename($this->table_chongzhi) . " WHERE id = :id ", array(':id' => $order['taocanid']));
		$orderName = '余额充值【'.$taocan['chongzhi'].'】元';
	}elseif($order['type'] == 9){
		$orderName = '充电桩充次'.$order['ksnum'].'】元';
	}elseif($order['type'] ==4){
		$orderName = '报名费';
	}
	elseif($order['type'] ==5){
		$orderName = '考勤卡费';
	}
	elseif($order['type'] ==6){
		$orderName = '商城订单';
		if(!empty($user['tid']) && empty($user['sid'])){
			$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $user['tid'],':weid' => $weid,':schoolid' => $schoolid));
			if(!empty($teacher)){
				$this_name = $teacher['tname'];
				$addinfo = '教师';
			}
		}
	}
	elseif($order['type'] ==7){
		$vod = pdo_fetch("SELECT name FROM " . tablename($this->table_allcamera) . " WHERE id = :id ", array(':id' => $order['vodid']));
		$orderName = $vod['name'];
	}elseif($order['type'] ==1){
		$kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $order['kcid']));
		$orderName = $kc['name'];
		if ($order['ksnum'] != 0){
			$orderName .='【含'.$order['ksnum'].'课时】';
		}
	}
	else{
		$ob = pdo_fetch("SELECT name FROM " . tablename($this->table_cost) . " WHERE id = :id ", array(':id' => $order['costid']));
		$orderName = $ob['name'];
	}
	$result['status'] = true;
	$result['order'] = $order;
	$result['ordername'] = $orderName;
	$result['this_name'] = $this_name.'-'.$addinfo;
	
	die(json_encode($result));
}elseif ($operation == 'changePay') {
	$orderid = $_GPC['orderid'];
	$newcost = $_GPC['newcost'];
	pdo_update($this->table_order, array('cose'=>$newcost), array('id' => $orderid));
	$result['status'] = true;
	$result['msg'] = "修改成功";
	die(json_encode($result));
}	
include $this->template ( 'web/payall' );
