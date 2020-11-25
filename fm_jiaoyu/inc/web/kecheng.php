<?php
/**
* 微教育模块
*
* @author 高贵血迹
*/
global $_GPC, $_W;

$weid = $_W['uniacid'];
$action = 'kecheng';
$this1 = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$schoolid = intval($_GPC['schoolid']);
$is_start = !empty($_GPC['is_start'])?$_GPC['is_start'] : -1 ;
$logo = pdo_fetch("SELECT logo,title FROM " . GetTableName('index') . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT * FROM " . GetTableName('index') . " where id = :id ", array(':id' => $schoolid));
if($_W['schooltype']){
	$AllBj = pdo_fetchAll("SELECT id,title FROM " . GetTableName('class') . " WHERE schoolid = '{$schoolid}' AND weid = '{$weid}'");
}
$xueqi = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
$sunxueqi = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'kcclass', ':schoolid' => $schoolid));
	
$sd = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$schooltype = $_W['schooltype'];
$remindday = pdo_fetch("select id,remindday,tx_pay from " . GetTableName('schoolset') . " where schoolid=:schoolid And weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
$tuan = check_app('tuan',$weid,$schoolid);$tuiguang = check_app('tuiguang',$weid,$schoolid);$zhuli = check_app('zhuli',$weid,$schoolid);
$sale =false;
if($zhuli || $tuan){ $sale = true; }
if($tid_global !='founder' && $tid_global != 'owner'){
	$loginTeaFzid =  pdo_fetch("SELECT fz_id,is_sell FROM " . GetTableName ('teachers') . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
	$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
	$toPage = 'kecheng';
	if( !(strstr($qxarr,'1000901'))){
		$toPage = 'kcbiao';
	}
	if(!(strstr($qxarr,'1000921')) && $toPage == 'kcbiao'){
		$toPage = 'kcsign';
	}
	if(!(strstr($qxarr,'1000941')) && $toPage == 'kcsign'){
		$toPage = 'gongkaike';
	}
	if(!(strstr($qxarr,'1000951')) && $toPage == 'gongkaike'){
		$toPage = 'NoAccess';
	}
	if($toPage != 'kecheng' && $toPage != 'NoAccess'  ){
		$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl($toPage, array('schoolid' => $schoolid,'op'=>'display'));
		header("location:$stopurl");
	}elseif($toPage == 'NoAccess' ){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
}
if ($operation == 'post') {
	if (!(IsHasQx($tid_global,1000902,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$fz = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'jsfz', ':schoolid' => $schoolid));
	$km = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
	$bj = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	load()->func('tpl');
	$id = intval($_GPC['id']);
	$addr =  pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = '{$weid}' And schoolid ='{$schoolid}' And type='addr' ORDER BY ssort DESC ");
	$payweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");
	$teachers = pdo_fetchall("SELECT id,tname FROM " . GetTableName ('teachers') . " where weid = :weid And schoolid = :schoolid ORDER BY  CONVERT(tname USING gbk)  ASC ", array(
			':weid' => $weid,
			':schoolid' => $schoolid
		) );
	$countSelectedTea = 0;
	mload()->model('print');
	$printers = printers($schoolid);
	$printer_name = printer_name();
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $id));
		$saleset = pdo_fetch("SELECT * FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $item['sale_id']));
		$tgset = pdo_fetch("SELECT * FROM " . GetTableName('kc_promote') . " WHERE id = :id ", array(':id' => $item['tg_id']));
		$promote_team = pdo_fetchall("SELECT tid FROM " . GetTableName('promote_team') . " WHERE kcid = :kcid ", array(':kcid' => $id));
		$guige = pdo_fetchall("SELECT * FROM " . GetTableName('guige') . " WHERE kcid = :kcid ", array(':kcid' => $id));
		$signset = pdo_fetch("SELECT * FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $item['sign_pl_set']));
		if(!empty($item)){
			$nowxq = pdo_fetch("SELECT sid,type,parentid FROM ".GetTableName('classify')." WHERE  sid =  '{$item['xq_id']}' ");
			if($nowxq['type'] == 'kcclass'){
				$parentxq =$nowxq['parentid'];
			}else{
				$parentxq =$item['xq_id'];
			}
		}
		$sh_tealist ='';
		$sh_teaid ='';
		$sh_signteaid ='';
		$sh_signtealist ='';
		if($promote_team){
			foreach($promote_team as $row){
				$teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $row['tid']));
				$sh_tealist .= $teacher['tname'].',';
				$sh_teaid .= $row['tid'].',';
			}
			$sh_teaid = rtrim($sh_teaid,',');
			$sh_tealist = rtrim($sh_tealist,',');
		}
		if($signset['sh_tea_teacherids']){
			$sh_tea_teacherids = explode(',', $signset['sh_tea_teacherids']);
			foreach($sh_tea_teacherids as $ite){
				$teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $ite));
				$sh_signtealist .= $teacher['tname'].',';
				$sh_signteaid .= $ite.',';
			}
			$sh_signteaid = rtrim($sh_signteaid,',');
			$sh_signtealist = rtrim($sh_signtealist,',');
		}
		$menu_list = pdo_fetchall("SELECT id,name FROM " . GetTableName('kc_menu') . " WHERE kcid = :kcid And schoolid = :schoolid ", array(':kcid' => $id,':schoolid' => $schoolid));
		$uniarr = explode(',', $item['tid']);
		$nowprints = explode(',', $item['printarr']);
		$countSelectedTea = count($uniarr)?count($uniarr): 0;
		$thisTealist = pdo_fetchall("SELECT id,tname FROM " . GetTableName ('teachers') . " where weid = :weid And schoolid = :schoolid And FIND_IN_SET(id,:tidarr) ORDER BY  CONVERT(tname USING gbk)  ASC ", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':tidarr'=>$item['tid']
		) );
		if($item['tea_getcash'] == 1){
			$txset = pdo_fetch("SELECT * FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $item['id']));
		}
		if (empty($item)) {
			$this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
		}
	}
	include $this->template ( 'public/add_kc' );
	die();
} elseif ($operation == 'add_new') {//添加或修改课程
	load()->func('tpl');
	$id = intval($_GPC['id']);
	if(empty($_GPC['tidarr'])){
		$result['result'] = false;
		$result['msg'] = '请至少选择一位授课老师！';
		die ( json_encode ( $result ) );
	}
	$checkkc = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $id));
	$tidarray = implode(',', $_GPC['tidarr']);
	$AllNum   = 0;
	$FirstNum = 0;
	$ReNum    = 0;
	$RePrice  = 0;
	$signTime = 0;
	$isSign = !empty($_GPC['is_sign'])?$_GPC['is_sign']:$checkkc['isSign'];
	if($isSign == 1  ){
		$signTime = $_GPC['signTime'];
	}
	
	$AllNum   = intval($_GPC['AllNum']);
	$FirstNum = intval($_GPC['FirstNum']);
	$ReNum    = intval($_GPC['ReNum']);
	$RePrice  = $_GPC['RePrice'];
	if($_GPC['OldOrNew'] == 1 ){
		$isSign   = 1;
	}
	if(empty($_GPC['kcthumb'])){
		$result['result'] = false;
		$result['msg'] = '请设置课程图片！';
		die ( json_encode ( $result ) );
	}
	if(empty($_GPC['xq'])){
		$result['result'] = false;
		$result['msg'] = '请设置本课程归属分类！';
		die ( json_encode ( $result ) );
	}
	if($_GPC['is_tx'] == 1){
		if(empty($_GPC['txtime'])){
			$result['result'] = false;
			$result['msg'] = '抱歉，开启定时上课提醒必须设置提前提醒时间';
			die ( json_encode ( $result ) );
		}
	}
	if(empty($_GPC['km']) && $_GPC['kc_type'] == 0){
		$result['result'] = false;
		$result['msg'] = '线下模式，请设置科目！';
		die ( json_encode ( $result ) );
	}
	if($AllNum == 0 && $_GPC['kc_type'] == 0){
		$result['result'] = false;
		$result['msg'] = '线下模式，总课时不能为空！';
		die ( json_encode ( $result ) );
	}
	if($AllNum == 0 && $_GPC['kc_type'] == 0){
		$result['result'] = false;
		$result['msg'] = '线下模式，首购课时不能为空！';
		die ( json_encode ( $result ) );
	}
	if($_GPC['guigetype'] != 1){
		if($_GPC['sale_type'] == 1){//检查团购价格设置
			if($_GPC['tuan_price'] >= $_GPC['cose']){
				$result['result'] = false;
				$result['msg'] = '抱歉团购优惠不能大于课程原价';
				die ( json_encode ( $result ) );
			}
			if(($_GPC['tuan_price'] + $_GPC['tuan_tzyh'])  >= $_GPC['cose']){
				$result['result'] = false;
				$result['msg'] = '抱歉团购优惠+团长优惠不能大于课程原价';
				die ( json_encode ( $result ) );
			}
		}
	}
	if($_GPC['guigetype'] != 1){
		if($_GPC['sale_type'] == 2){//检查助力价格设置
			if($_GPC['zhuli_price'] >= $_GPC['cose']){
				$result['result'] = false;
				$result['msg'] = '抱歉助力优惠不能大于课程原价';
				die ( json_encode ( $result ) );
			}
		}
	}
	
	if(count( $_GPC['tidarr']) > 1){
		if(! empty($_GPC['maintid'])){
			$maintid = $_GPC['maintid'];
		}else{
			$result['result'] = false;
			$result['msg'] = '主讲老师不能为空！';
			die ( json_encode ( $result ) );
		}
	}else{
		$maintid = $tidarray;
	}
	$data  = array(
		'weid'     => $weid,
		'schoolid' => $schoolid,
		'tid'      => $tidarray,
		'xq_id'    => !empty($_GPC['sun_xq']) ?intval($_GPC['sun_xq']) :intval($_GPC['xq']),
		'km_id'    => trim($_GPC['km']),
		'bj_id'    => trim($_GPC['bj']),
		'name'     => trim($_GPC['name']),
		'minge'    => trim($_GPC['minge']),
		'yibao'    => trim($_GPC['yibao']),
		'cose'     => trim($_GPC['cose']),
		'dagang'   => urldecode($_GPC['dagang']),
		'adrr'     => trim($_GPC['adrr']),
		'is_dm'    => intval($_GPC['is_dm']),
		'is_tx'    => intval($_GPC['is_tx']),
		'txtime'   => intval($_GPC['txtime']),
		'is_hot'   => intval($_GPC['is_hot']),
		'is_show'  => intval($_GPC['is_show']),
		'ssort'    => intval($_GPC['ssort']),
		'start'    => strtotime($_GPC['start']),
		'end'      => strtotime($_GPC['end']),
		'printarr' => !empty($_GPC['printarr'])?implode(',', $_GPC['printarr']):'',
		'is_print' => intval($_GPC['is_print']),
		'payweid'  => empty($_GPC['payweid']) ? $weid : $_GPC['payweid'],
		'signTime' => $signTime,
		'isSign'   => $isSign,
		'AllNum'   => $AllNum,
		'FirstNum' => $FirstNum,
		'ReNum'    => $ReNum,
		'RePrice'  => $RePrice,
		'thumb'	   => $_GPC['kcthumb'],
		'bigimg'   => $_GPC['bigimg'],
		'Ctype'    => $_GPC['Ctype'],
		'maintid'  => $maintid,
		'Point2Cost'=> intval($_GPC['Point2Cost']),
		'MinPoint' => intval($_GPC['MinPoint']),
		'MaxPoint' => intval($_GPC['MaxPoint']),
		'yytid'	   =>$_GPC['yytid'],
		'is_tuijian' => $_GPC['is_tuijian'],
		'rechecktime' => $_GPC['rechecktime'],
		'is_print_xk' => $_GPC['is_print_xk'],
		'kc_type' => $_GPC['kc_type'],
		'is_try' => $_GPC['is_try'],
		'allow_pl' => $_GPC['allow_pl'],
		'sale_type' => $_GPC['sale_type'],
		'allow_menu' => $_GPC['allow_menu'],
		'allow_tuiguang' => $_GPC['allow_tuiguang'],
		'pkuser' => $_W['tid'],
		'coupon' => $_GPC['redpacket'], 
		'redpacket' => $_GPC['redpacket'], 
		'tea_getcash' => $_GPC['tea_getcash'], 
	);
	if($_GPC['kc_type'] == 1){//如果前端选择在线课程得话，课程模式unset
		unset($data['is_try']);
	}

	if(keep_mutikc()){
		$data['guigetype'] = $_GPC['guigetype']; //规格1多规格，0单规格
	}

	if(keep_Blacklist()){
		// $xq = pdo_fetch("SELECT njabbr FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['xq']}' ");
		// $kccode = strtoupper($_GPC['kcabbr']).$xq['njabbr'].date("Ym",time());
		// $tcourse = pdo_fetch("SELECT kccode,kcabbr FROM ".GetTableName('tcourse')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kccode LIKE '{$kccode}%' ORDER BY id DESC");
		// if(empty($tcourse)){
		// 	$newkccode = $kccode.'01'; //新增
		// }else{ //修改
		// 	for($i=0;$i<strlen($tcourse['kccode']);$i++){ // 取出数据库中kccode字段的数字
		// 		if(is_numeric($tcourse['kccode'][$i])){
		// 			$selectkccode.=$tcourse['kccode'][$i];
		// 		}
		// 	}
		// 	if($checkkc['kcabbr'] == $_GPC['kcabbr']){
		// 		$code = $selectkccode;
		// 	}else{
		// 		$code = $selectkccode+1; //最大值+1
		// 	}
		// 	$newkccode = strtoupper($_GPC['kcabbr']).$xq['njabbr'].$code;
		// }
		$data['kcabbr'] = strtoupper($_GPC['kcabbr']);
		$data['kcnumber'] = $_GPC['kcnumber'];
		// $data['kccode'] = $newkccode;
	}
	if(keep_sk77()){
		$data['overtimeday'] = $_GPC['OverTimeDay'];
		$data['remindday'] = $remindday['reminday'];
	}
	if(!empty($_GPC['OldOrNew'])){
		$data['OldOrNew'] = intval($_GPC['OldOrNew']);
	}
	if (empty($id)) {
		pdo_insert($this->table_tcourse, $data);
		$kcid = pdo_insertid();
		$result['result'] = true;
		$result['type'] = 'new';
		$result['msg'] = '新增课程成功';
	} else {
		pdo_update($this->table_tcourse, $data, array('id' => $id));
		$kcid = $id;
		$result['type'] = 'old';
		$result['result'] = true;
		$result['msg'] = '修改课程成功';
	}
	if(keep_Blacklist()){
		$tcourse = pdo_fetch("SELECT kcabbr,kcnumber,start,xq_id FROM ".GetTableName('tcourse')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND id = '{$kcid}'");
		$xq = pdo_fetch("SELECT njabbr FROM ".GetTableName('classify')." WHERE sid = '{$tcourse['xq_id']}' ");
		$kccode = $tcourse['kcabbr'].$tcourse['kcnumber'].date("Ym",$tcourse['start']).$xq['njabbr'].$kcid;
		pdo_update(GetTableName('tcourse',false),array('kccode'=>$kccode),array('id'=>$kcid));
	}
	/**************多规格操作****************/ 
	if($_GPC['guigetype'] == 1){
		if(!empty($_GPC['new'])){
			$f_count = 0;
			foreach($_GPC['new'] as $key => $value){
				$guigename_new = trim($_GPC['guigename_new'][$key]);
				$guigeprice_new = $_GPC['guigeprice_new'][$key];
				$guigeksnum_new = trim($_GPC['guigeksnum_new'][$key]);
				if(empty($guigename_new) || empty($guigeprice_new) || empty($guigeksnum_new)){
					$guigecount += $f_count + 1;
					$ggname = '有【'.$guigecount.'】条信息未完善';
				}
				$data_new = array(
					'weid'     => $weid,
					'schoolid' => $_GPC['schoolid'],
					'name'    => $guigename_new,
					'price'    => $guigeprice_new,
					'ksnum'    => intval($guigeksnum_new),
					'createtime'     => time(),
					'kcid' => $kcid,
				);
				if(!empty($guigename_new) && !empty($guigeprice_new) && !empty($guigeksnum_new)){
					pdo_insert(GetTableName('guige',false), $data_new);
					$success = '--成功添加以下规格:';
					$msg .= $guigename_new.'|';
				}
			}
			$message =  $success.$msg.$ggname;
			$result['msg'] .= $message;
		}elseif(!empty($_GPC['old'])){
			$f_count = 0;
			foreach($_GPC['old'] as $key => $value){
				$guigename_old = trim($_GPC['guigename_old'][$key]);
				$guigeprice_old = $_GPC['guigeprice_old'][$key];
				$guigeksnum_old = trim($_GPC['guigeksnum_old'][$key]);
				$guigeid_old = trim($_GPC['guigeid_old'][$key]);
				if(empty($guigename_old) || empty($guigeprice_old) || empty($guigeksnum_old)){
					$guigecount += $f_count + 1;
					$ggname = '有【'.$guigecount.'】条信息未完善';
				}
				$data_old = array(
					'name'    => $guigename_old,
					'price'    => $guigeprice_old,
					'ksnum'    => intval($guigeksnum_old),
					'createtime'     => time(),
				);
				if(!empty($guigename_old) && !empty($guigeprice_old) && !empty($guigeksnum_old)){
					pdo_update(GetTableName('guige',false), $data_old,array('id'=>$guigeid_old));
				}
			}
		}
		if(!empty($_GPC['deletearr'])){
			$deletearr = explode(',',$_GPC['deletearr']);
			foreach ($deletearr as $key => $value) {
				pdo_delete(GetTableName('guige',false),array('id'=>$value));
			}
		}
	}
	/**************多规格操作****************/ 

	if($_GPC['allow_menu'] == 1){//启用章节
		$menulist = $_GPC['menu_name'];
		$meunids = $_GPC['meunid'];
		foreach($menulist as $i => $r){
			$menudata = array(
				'weid'     		=> $weid,
				'schoolid' 		=> $schoolid,
				'kcid'     		=> $kcid,
				'name'     		=> $r,
				'createtime' 	=> time()
			);
			if(!empty($meunids[$i])){
				pdo_update(GetTableName('kc_menu',false), $menudata, array('id' => $meunids[$i]));
			}else{
				unset($menudata['createtime']);
				pdo_insert(GetTableName('kc_menu',false), $menudata);
			}
		}
	}
	//处理营销和推广
	if($_GPC['sale_type'] != 0){
		if($_GPC['sale_type'] == 1){//团购设置
			$salearray = array(
				'weid'     		=> $weid,
				'schoolid' 		=> $schoolid,
				'kcid'     		=> $kcid,
				'name'     		=> trim($_GPC['name']).'(团购设置)',
				'price' 		=> $_GPC['tuan_price'],
				'tuanz_price' 	=> $_GPC['tuan_tzyh'],
				'suc_munber' 	=> $_GPC['tuan_number'],
				'overtimeset' 	=> $_GPC['tuan_over_set'],
				'overtime' 		=> $_GPC['tuan_over_time'],
				'endtime'       => strtotime($_GPC['tuan_endtime']),
				'allow_again' 	=> $_GPC['fail_tuan'],
				'allow_help' 	=> $_GPC['tuan_xn'],
				'use_pop'		=> $_GPC['use_pop_tuan'],
				'pop_id' 		=> $_GPC['tuan_mb_id'],
				'pop_img' 		=> trim($_GPC['tuan_bg']),
				'share_title' 	=> $_GPC['tuan_share_title'],
				'share_word' 	=> $_GPC['tuan_wenan'],
				'rule_word' 	=> $_GPC['tuan_guize'],
				'type' 			=> $_GPC['sale_type'],
				'createtime' 	=> time()
			);
			if($_GPC['tuan_price'] > $_GPC['cose']){
				
			}
		}
		if($_GPC['sale_type'] == 2){//助力设置
			$salearray = array(
				'weid'     => $weid,
				'schoolid' => $schoolid,
				'kcid'     => $kcid,
				'name'     => trim($_GPC['name']).'(助力设置)',
				'price' 		=> $_GPC['zhuli_price'],
				'suc_munber' 	=> $_GPC['zhuli_number'],
				'overtimeset' 	=> $_GPC['zhuli_over_set'],
				'overtime' 		=> $_GPC['zhuli_over_time'],
				'endtime'       => strtotime($_GPC['zhuli_endtime']),
				'allow_again' 	=> $_GPC['fail_zhuli'],
				'allow_help' 	=> $_GPC['zhuli_xn'],
				'use_pop'		=> $_GPC['use_pop_zl'],
				'pop_id' 		=> $_GPC['zhuli_mb_id'],
				'pop_img' 		=> trim($_GPC['zhuli_bg']),
				'share_title' 	=> $_GPC['zhuli_share_title'],
				'share_word' 	=> $_GPC['zhuli_wenan'],
				'rule_word' 	=> $_GPC['zhuli_guize'],
				'type' 			=> $_GPC['sale_type'],
				'createtime' 	=> time()
			);
		}
		if (empty($id)) { //处理储存营销规则
			pdo_insert(GetTableName('kc_saleset',false), $salearray);
			$sale_id = pdo_insertid();
		}else{
			if($checkkc['sale_id']){
				pdo_update(GetTableName('kc_saleset',false), $salearray, array('id' => $checkkc['sale_id']));
				$sale_id = $checkkc['sale_id'];
			}else{
				pdo_insert(GetTableName('kc_saleset',false), $salearray);
				$sale_id = pdo_insertid();
			}
		}
	}
	if($_GPC['allow_tuiguang'] == 1){
		$tgarray = array(
			'weid'     => $weid,
			'schoolid' => $schoolid,
			'kcid'     => $kcid,
			'name'     => trim($_GPC['name']).'(推广设置)',
			'allow_normal' 	=> $_GPC['allow_normal'],
			'team' 			=> rtrim($_GPC['sh_teacherids'],','),
			'tg_number' 	=> $_GPC['tg_number'],
			'show_ranking' 	=> $_GPC['show_ranking'],
			'is_royalty' 	=> $_GPC['is_royalty'],
			'need_done' 	=> $_GPC['need_done'],
			'royalty' 		=> $_GPC['royalty'],
			'xg_royalty' 	=> $_GPC['xg_royalty'],
			'mobile_sign' 	=> $_GPC['mobile_sign'],
			'mobile_sign_fp'=> $_GPC['mobile_sign_fp'],
			'use_pop'		=> $_GPC['use_pop_tg'],
			'pop_id'		=> $_GPC['tuiguang_mb_id'],
			'pop_img'		=> trim($_GPC['tuiguang_bg']),
			'share_title'	=> $_GPC['tuiguang_share_title'],
			'share_word'	=> $_GPC['tuiguang_wenan'],
			'count_menber'	=> $_GPC['count_menber'],
			'type' 			=> 1,
			'createtime' 	=> time()
		);
		$promote_team = pdo_fetchall("SELECT id,tid FROM " . GetTableName('promote_team') . " WHERE kcid = :kcid ", array(':kcid' => $kcid));
		$team = rtrim($_GPC['sh_teacherids'],',');
		$gpcteam = explode(',',$team);
		foreach($promote_team as $key => $row){
			if(!in_array($row['tid'],$gpcteam)){//不在前端选择范围内的则删除
				pdo_delete(GetTableName('promote_team',false), array('id' => $row['id']));
			}
		}
		$promote = array(
			'weid'     => $weid,
			'schoolid' => $schoolid,
			'kcid'     => $kcid,
			'createtime' 	=> time()
		);
		foreach($gpcteam as $r){
			$checkin = pdo_fetch("SELECT id FROM " . GetTableName('promote_team') . " WHERE kcid = :kcid And tid = :tid", array(':kcid' => $kcid,':tid' => $r));
			if(empty($checkin)){ //未写入的写入
				$promote['tid'] = $r;
				pdo_insert(GetTableName('promote_team',false), $promote);
			}
		}
		if (empty($id)) { //处理储存推广规则
			pdo_insert(GetTableName('kc_promote',false), $tgarray);
			$tg_id = pdo_insertid();
		}else{
			if($checkkc['tg_id']){
				pdo_update(GetTableName('kc_promote',false), $tgarray, array('id' => $checkkc['tg_id']));
				$tg_id = $checkkc['tg_id'];
			}else{
				pdo_insert(GetTableName('kc_promote',false), $tgarray);
				$tg_id = pdo_insertid();
			}
		}
	}
	//评论和签到设置
	if($isSign == 1){
		$signset = array(
			'weid'     => $weid,
			'schoolid' => $schoolid,
			'kcid' => $kcid,
			'tea_sign_confirm' => $_GPC['tea_sign_confirm'],
			'stu_sign_confirm' => $_GPC['stu_sign_confirm'],
			'more_tea_sign' => $_GPC['more_tea_sign'],
			'sh_tea_teacherids' => rtrim($_GPC['sh_tea_teacherids'],','),
			'tea_change_stutype' => $_GPC['tea_change_stutype'],
			'tea_sign_fuke' => $_GPC['tea_sign_fuke'],
			'tea_sign_old' => $_GPC['tea_sign_old'],
			'tea_no_myks' => $_GPC['tea_no_myks'],
			'allow_ksdf' => $_GPC['allow_ksdf'],
			'tea_add_ks' => $_GPC['tea_add_ks'],
			'tea_edit_ks' => $_GPC['tea_edit_ks'],
			'tea_mobile_pk' => $_GPC['tea_mobile_pk'],
			'allow_kspl' => $_GPC['allow_kspl'],
			'allow_shera_pl' => $_GPC['allow_shera_pl'],
			'createtime' => time()
		);
		// CommonWriteLog($signset,'kc');
		if (empty($id)) { //处理储存营销规则
			pdo_insert(GetTableName('kc_signset',false), $signset);
			$sign_id = pdo_insertid();
		}else{
			if($checkkc['sign_pl_set']){
				pdo_update(GetTableName('kc_signset',false), $signset, array('id' => $checkkc['sign_pl_set']));
				$sign_id = $checkkc['sign_pl_set'];
			}else{
				pdo_insert(GetTableName('kc_signset',false), $signset);
				$sign_id = pdo_insertid();
			}
		}
	}
	//提现设置
	if($_GPC['tea_getcash'] == 1){
		$txset = pdo_fetch("SELECT id FROM " . GetTableName('kc_getcashrule') . " WHERE kcid = :kcid ", array(':kcid' => $kcid));
		$txsetdata = array(
			'weid'     => $weid,
			'schoolid' => $schoolid,
			'kcid' => $kcid,
			'payweid'  => empty($_GPC['tx_payweid']) ? $weid : $_GPC['tx_payweid'],
			'is_needsh' => $_GPC['is_tx_shenhe'],
			'fee1' => $_GPC['fee1'],
			'fee2' => $_GPC['fee2'],
			'min_ksnumber' => $_GPC['min_ksnumber'],
			'max_ksnumber' => $_GPC['max_ksnumber'],
			'type' => $_GPC['txtype'],
		);
		if (empty($txset)) {
			pdo_insert(GetTableName('kc_getcashrule',false), $txsetdata);
		}else{
			pdo_update(GetTableName('kc_getcashrule',false), $txsetdata, array('id' => $txset['id']));
		}
	}
	
	pdo_update($this->table_tcourse, array('sale_id' => $sale_id,'tg_id' => $tg_id,'sign_pl_set' => $sign_id), array('id' => $kcid));
	if($_GPC['is_tx']){//处理课时开课提醒
		$task = pdo_fetch("SELECT * FROM " . GetTableName('task') . " WHERE kcid = '{$kcid}' And type = 1 ");
		$temp = array(
			'weid'     		=> $weid,
			'schoolid' 		=> $schoolid,
			'kcid'     		=> $kcid,
			'status'  		=> $_GPC['is_tx'],
			'type'     		=> 1,
			'createtime'    => time()
		);
		if(empty($task)){
			pdo_insert(GetTableName('task',false), $temp);
		}else{
			unset($temp['createtime']);
			pdo_update(GetTableName('task',false), $temp, array('id' => $task['id']));
		}
	}
	$result['tg_id'] = $tg_id;
	$result['tgarray'] = $tgarray;
	$result['kcid'] = $kcid;
	die ( json_encode ( $result ) );
} elseif ($operation == 'display') {//加载课程列表
	if (!(IsHasQx($tid_global,1000901,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));	
	$km = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));

	
	if (empty($_GPC['kcid'])) {
		if (checksubmit('submit')) { //排序
			if (is_array($_GPC['ssort'])) {
				foreach ($_GPC['ssort'] as $id => $val) {
					$data = array('ssort' => intval($_GPC['ssort'][$id]));
					pdo_update($this->table_tcourse, $data, array('id' => $id));
				}
			}
			$this->imessage('批量修排序成功!', $url);
		}
	}
	mload()->model('tea');	
	$teacher_list = getalljsfzallteainfo($schoolid,0,$schooltype);
	$teacher_list2 = getalljsfzallteainfo_nofz($schoolid,$schooltype);
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 10;
	$condition = '';
	$time = time();
	$kc_type = !empty($_GPC['kc_type'])?$_GPC['kc_type'] : -1 ;
	$is_start = !empty($_GPC['is_start'])?$_GPC['is_start'] : -1 ;
	switch ( $is_start ){
		case -1 :
			break;
		case 1 :
			$condition .= "And start > {$time}";
			break;
		case 2 :
			$condition .= "And start <= {$time} And end >= {$time}";
			break;
		case 3 :
			$condition .= " And end < {$time}";
			break;		
		default:
			break;
	}
	switch ( $kc_type ){
		case -1 :
			break;
		case 1 :
			$condition .= " And kc_type != 1" ;
			break;
		case 2 :
			$condition .= " And kc_type = 1 ";
			break;
		case 3 :
			$condition .= " And sale_type = 1 ";
			break;
		case 4 :
			$condition .= " And sale_type = 2 ";
			break;
		case 5 :
			$condition .= " And allow_tuiguang = 1 ";
		case 6 :
			$condition .= " And is_try = 1 ";
		case 7 :
			$condition .= " And is_try != 1 ";	
			break;	
		default:
			break;
	}
	if (!empty($_GPC['kcid'])) {
		$kcid = intval($_GPC['kcid']);
		$condition .= " And id = '{$kcid}'";
	}else{
		if (!empty($_GPC['name'])) {
			$condition .= " And name LIKE '%{$_GPC['name']}%' ";
		}
		if (!empty($_GPC['tname'])) {
			$tname = trim($_GPC['tname']);
			$tid = pdo_fetch("SELECT id FROM " . GetTableName ('teachers') . " where weid='{$weid}' And schoolid='{$schoolid}' And tname LIKE '%$tname%' ");
			$condition .= "And (tid like '{$tid['id']},%' OR tid like '%,{$tid['id']}' OR tid like '%,{$tid['id']},%' OR tid='{$tid['id']}') ";
		}
		$childInfo = pdo_fetchAll("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '{$_GPC['nj_id']}' And type = 'kcclass' ");
		if($_GPC['childId']){
			$condition .= " And xq_id = '{$_GPC['childId']}'";
		}elseif(!empty($_GPC['nj_id'])){
			$chlid = pdo_fetchAll("SELECT sid FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '{$_GPC['nj_id']}'");
			$chlidstr = arrayToString($chlid);
			$cid = intval($_GPC['nj_id']);
			$condition .= " And (xq_id = '{$cid}' OR FIND_IN_SET(xq_id,'{$chlidstr}'))";
		}	
		if (!empty($_GPC['km_id'])) {
			$cid = intval($_GPC['km_id']);
			$condition .= " And km_id = '{$cid}'";
		}
	}
	// $bj_str = '';
	// foreach($bj as $key=>$value){
	// 	$bj_str .=$value['sid'].","; 
	// }	
	// $bj_str_f = trim($bj_str,",");
	mload()->model('kc');
	/*****************************导出数据*********************************/
	if(keep_Blacklist()){
		if($_GPC['out_putcode'] == 'out_putcode'){
			$listss = pdo_fetchall("SELECT id,xq_id,km_id,adrr,schoolid,tid,kccode,name,end,AllNum FROM " . GetTableName('tcourse') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY id DESC  ");
			$ii   = 0;
			foreach($listss as $index => $row){
				$nj = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$row['xq_id']}' ");
				$km = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$row['km_id']}' ");
				$adrr = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$row['adrr']}' ");
				$schindex = pdo_fetch("SELECT title FROM ".GetTableName('index')." WHERE id = '{$row['schoolid']}' ");
				$teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$row['tid']}' ");
				$yb = GetKcSiguNub($row['id']);
				$arr[$ii]['kccode'] = $row['kccode'];
				$arr[$ii]['name']  = $row['name'];
				$arr[$ii]['njname']= $nj['sname']."({$schindex['title']})";
				$arr[$ii]['teacher']= $teacher['tname'];
				$arr[$ii]['end']= date("Y-m-d",$row['end']);
				$arr[$ii]['kmjs']= $km['sname'].'/'.$adrr['sname'];
				$arr[$ii]['bmqk']= $yb;
				$arr[$ii]['allks']= $row['AllNum'];
				$ii++;
			}
			$this->exportexcel($arr, array('课程编码','课程名称','年级（校区)','授课老师','有效日期','科目/教室','报名人数','总课时'), '课程信息');
			exit();
		}
	}
	/*****************************导出数据*********************************/

	$list = pdo_fetchall("SELECT * FROM " . GetTableName('tcourse') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		$listAll = pdo_fetchall("SELECT id,name,end FROM " . GetTableName('tcourse') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY id DESC " );
		foreach($list as $key => $row){
			$tidarray =  explode(',', $row['tid']);
			$yb = GetKcSiguNub($row['id']);
			$allks = pdo_fetchcolumn("select count(*) FROM ".GetTableName('kcbiao')." WHERE kcid = '".$row['id']."'");
			$fristks = pdo_fetch("select id FROM ".GetTableName('kcbiao')." WHERE kcid = '{$row['id']}' And schoolid = '{$schoolid}' ORDER BY date ASC ");
			$njs = pdo_fetch("SELECT sname FROM " . GetTableName ('classify') . " where sid = :sid ", array(':sid' => $row['xq_id']));
			$kms = pdo_fetch("SELECT sname FROM " . GetTableName ('classify') . " where sid = :sid ", array(':sid' => $row['km_id']));
			$adrrs = pdo_fetch("SELECT sname FROM " . GetTableName ('classify') . " where sid = :sid ", array(':sid' => $row['adrr']));

			//规格
			if($row['guigetype'] == 1){
				$guige = pdo_fetch("SELECT price,ksnum FROM " . GetTableName ('guige') . " where kcid = :kcid ", array(':kcid' => $row['id']));
				$list[$key]['guige'] = $guige;
			}
			$list[$key]['fristks'] = $fristks['id'];
			$list[$key]['njname'] = $njs['sname'];
			$list[$key]['kmname'] = $kms['sname'];
			$list[$key]['adrrname'] = $adrrs['sname'];
			$list[$key]['allks'] = $allks;
			$list[$key]['tid'] =  explode(',', $row['tid']);
			$list[$key]['yib'] = $yb;
			if($row['allow_tuiguang'] == 1){
				$jindu = GetKcTgProcess($row['id'],0);
				$list[$key]['bili'] = $jindu['bili'];
				$list[$key]['minge'] = $jindu['count'];
			}else{
				$list[$key]['minge'] = $row['minge'];
				$list[$key]['bili'] = intval(100*($yb/$row['minge']));
			}
			if($list[$key]['bili'] >=100){
				$list[$key]['mission'] = 'success';
			}
			if($row['end'] > $time){
				if($list[$key]['bili'] < 100){
					$list[$key]['mission'] = 'active';
				}
			}else{
				if($list[$key]['bili'] < 100){
					$list[$key]['mission'] = 'exception';
				}
			}
			foreach( $tidarray as $key1 => $value ){
				$teacher = pdo_fetch("SELECT * FROM " . GetTableName ('teachers') . " where id = :id ", array(':id' => $value));
				$allks = pdo_fetchcolumn("select count(*) FROM ".GetTableName('kcbiao')." WHERE kcid = '".$row['id']."'");
				$list[$key]['tname'][$key1]['tname'] = $teacher['tname'];
				$list[$key]['tname'][$key1]['tid'] = $teacher['id'];
			}
		}
	if (!empty($_GPC['kcid'])) { //只显示单独一个课程
		include $this->template ( 'public/one_kc' );
		die();
	}else{
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('tcourse') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition");
		$pager = pagination($total, $pindex, $psize);
	}
} elseif ($operation == 'pktemplet') {//加载排课弹框模板
	$kcid = intval($_GPC['id']);
	$kcinfo = pdo_fetch('SELECT name,is_try,tid,kc_type,allow_menu,adrr FROM ' . GetTableName('tcourse') . " WHERE id = '{$kcid}' And schoolid = '{$schoolid}'");
	$allks = pdo_fetchcolumn("select count(*) FROM ".GetTableName('kcbiao')." WHERE kcid = '".$kcid."'");
	$tidarray = explode(',', $kcinfo['tid']);
	foreach( $tidarray as $key => $value ){
		$teachers[$key] = pdo_fetch("SELECT id,tname FROM " . GetTableName ('teachers') . " where id = :id ", array(':id' => $value));		
	}
	if($kcinfo['kc_type'] == 0){
		$addr =  pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = '{$weid}' And schoolid ='{$schoolid}' And type='addr' ORDER BY ssort DESC ");
	}
	if($kcinfo['kc_type'] == 1){
		$allmenu =  pdo_fetchall("SELECT * FROM " . GetTableName('kc_menu') . " where schoolid ='{$schoolid}' And kcid ='{$kcid}' ORDER BY id ASC");
	}
	include $this->template ( 'public/paike' );
	die();
} elseif ($operation == 'team_list') {//team列表页
	mload()->model('kc');
	$kcid = intval($_GPC['kcid']);
	$kcinfo = pdo_fetch('SELECT * FROM ' . GetTableName('tcourse') . " WHERE id = '{$kcid}' And schoolid = '{$schoolid}'");
	$saleset = pdo_fetch("SELECT suc_munber FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $kcinfo['sale_id']));
	$allteam = GetAllTeamByKcid($kcid,false);
	$optype = 'teamlist';
	include $this->template ( 'public/kc_bm_list' );
	die();
} elseif ($operation == 'kc_pingjia') {//课程评价盒子
	$optype = 'kcpingjia';
	$kcid = intval($_GPC['kcid']);
	mload()->model('kc');
	$list = GetKcComment($kcid,'web');
	include $this->template ( 'public/kc_bm_list' );
	die();
} elseif ($operation == 'opt_pl') {//设置单个评论状态
	$optype = 'kcpingjia';
	$plid = intval($_GPC['id']);
	$type = $_GPC['type'];
	mload()->model('kc');
	$data = SetKcComment($plid,$type);
	die( json_encode($data) );
} elseif ($operation == 'get_kc_radar') {//课程招生雷达图
	$kcid = intval($_GPC['kcid']);
	mload()->model('kc');
	$data = GetKcRadar($kcid,$schoolid);
	die( json_encode($data) );
} elseif ($operation == 'kc_info') {//课程管理页
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));		
	$bj = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	$allsd =  pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid ='{$schoolid}' And type='timeframe' ORDER BY ssort DESC ");
	$nowtiem = time();
	$kcid = intval($_GPC['id']);
	$kcinfo = pdo_fetch('SELECT * FROM ' . GetTableName('tcourse') . " WHERE id = '{$kcid}' And schoolid = '{$schoolid}'"); //
	$kslist = GetOneKcKsOrder($kcid,0);
	$ysk = pdo_fetchcolumn("select COUNT(distinct ksid ) FROM ".GetTableName('kcsign')." WHERE kcid = '{$kcid}' And schoolid = '{$schoolid}'  "); //只取签到的课程
	$ybinfo = pdo_fetchall("SELECT id,sid FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$kcid}' And is_change != 1  "); //已报
	$yb = GetKcSiguNub($kcid);
	// foreach($ybinfo as $v){//需要排除已删除的学生
		// $sinfo = pdo_fetch("select s_name FROM ".GetTableName('students')." WHERE id = '{$v['sid']}' ");
		// if($sinfo['s_name']){
			// $yb++;
		// }else{//查不到学生直接删除购买记录
			// pdo_delete(GetTableName('coursebuy',false), array(':id' => $v['id']));
		// }
	// }
	$allstuks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$kcid}' And is_change != 1  "); //当前课程所有学生已购买课程数
	$allstuksxh = pdo_fetchcolumn("SELECT sum(costnum) FROM " . GetTableName('kcsign') . " WHERE kcid = '{$kcid}' And status = 2 And sid > 0 And costnum > 0 ");//排除老师的签到记录
	$allcose = pdo_fetchcolumn("select sum(cose) FROM ".GetTableName('order')." WHERE kcid = '{$kcid}' And status = 2 "); //当前课程所有购买记录金额总和
	$allpl = pdo_fetchcolumn('SELECT count(id) FROM ' . GetTableName('kcpingjia') . " WHERE kcid = '{$kcid}' And type = 2  ");
	$allhb = pdo_fetchcolumn('SELECT count(id) FROM ' . GetTableName('promote_pop') . " WHERE kcid = '{$kcid}' ");
	$pkteahcer = array();
	$pkteahcer['tname'] = '管理员';
	if($kcinfo['pkuser']>0){
		$pkteahcer = pdo_fetch("select tname FROM ".GetTableName('teachers')." WHERE id = '{$kcinfo['pkuser']}' ");
	}
	$fristks = pdo_fetch("select id FROM ".GetTableName('kcbiao')." WHERE kcid = '{$kcid}' And schoolid = '{$schoolid}' ORDER BY date ASC ");
	$allksnub = count($kslist);
	$tidarray = explode(',', $kcinfo['tid']);
	$teachers = array();
	foreach( $tidarray as $key => $value ){
		$tea = pdo_fetch("SELECT id,tname,thumb,mobile FROM " . GetTableName ('teachers') . " where id = :id ", array(':id' => $value));
		$maintid = 2;
		if($kcinfo['maintid'] == $tea['id']){
			$maintid = 1;
		}
		$tea_pk = pdo_fetchcolumn("select count(id) FROM ".GetTableName('kcbiao')." WHERE kcid = '{$kcid}' And tid = '{$tea['id']}' ");
		if($tea_pk == 0){$tea_pk=1;}
		// $tea_sg = pdo_fetchcolumn("select count(id) FROM ".GetTableName('kcsign')." WHERE kcid = '{$kcid}' And tid = '{$tea['id']}' And status = 2 ");
		// $tea_jd = $tea_sg/$tea_pk*100;
		//主讲签到次数
		$zjsignnum = pdo_fetchcolumn("select count(id) FROM ".GetTableName('kcsign')." WHERE kcid = '{$kcid}' And tid = '{$tea['id']}' And status = 2 AND ismaster_tid = 1");
		$fdsignnum = pdo_fetchcolumn("select count(id) FROM ".GetTableName('kcsign')." WHERE kcid = '{$kcid}' And tid = '{$tea['id']}' And status = 2 AND ismaster_tid = 2");
		$teachers[] = array(
			'id' 		=> $tea['id'],
			'maintid' 	=> $maintid,
			'fdsignnum' 	=> $fdsignnum,
			'zjsignnum' 	=> $zjsignnum,
			'tname' 	=> $tea['tname'],
			'mobile' 	=> $tea['mobile'],
			'thumb' 	=> !empty($tea['thumb'])?tomedia($tea['thumb']):tomedia($school['tpic'])
		);
	}
	$sktdnumb = count($teachers);
	$teachers = array_sorts($teachers,'maintid','asc');//主讲前面
	if($kcinfo['allow_tuiguang'] == 1){
		mload()->model('kc');
		$tgset = pdo_fetch("SELECT * FROM " . GetTableName('kc_promote') . " WHERE id = :id ", array(':id' => $kcinfo['tg_id']));
		$team = array();
		$jindu = GetKcTgProcess($kcid,0);
		$minge = $jindu['count'];
		$promote_team = pdo_fetchall("SELECT id,tid FROM " . GetTableName('promote_team') . " WHERE kcid = :kcid ", array(':kcid' => $kcid));
		if($tgset['team']){
			foreach($promote_team as $row){
				$tid = $row['tid'];
				$teacher = pdo_fetch("SELECT tname,thumb,mobile,sex FROM " . GetTableName('teachers') . " WHERE id = :id ", array(':id' => $tid));
				$onejindu = GetKcTgProcess($kcid,$tid);
				if($onejindu['mybilis'] >=100){
					$mission = 'success';
				}
				if($kcinfo['end'] > $time){
					if($onejindu['mybilis'] < 100){
						$mission = 'active';
					}
				}else{
					if($onejindu['mybilis'] < 100){
						$mission = 'exception';
					}
				}
				$team[] = array(
					'tname'    => $teacher['tname'],
					'oneminge' => $onejindu['count'],
					'mobile'   => $teacher['mobile'],
					'sex'      => $teacher['sex'],
					'allfans'  => $onejindu['myfans'],
					'onebili'  => $onejindu['mybilis'],
					'mission'  => $mission,
					'sucnuber'  => $onejindu['sucnumber'],
					'thumb'    => !empty($teacher['thumb'])?tomedia($teacher['thumb']):tomedia($school['tpic'])
				);
			}
		}
	}else{
		$minge = $kcinfo['minge'];
	}
	if($kcinfo['sale_type'] != 0){
		$allteam = pdo_fetchall("SELECT * FROM " . GetTableName('sale_team') . " where kcid = '{$kcid}' And ismaster = 1 ");
		$teamnumbers = count($allteam);
	}
	if($kcinfo['kc_type'] == 0){
		$nowweek = $_GPC['dtweek'] ? $_GPC['dtweek'] :str_pad(date("W"),1,"0",STR_PAD_LEFT);//动态周期
		$addr =  pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where sid = '{$kcinfo['adrr']}' ");
		$alladdr =  pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = '{$schoolid}' And type = 'addr' ");
	}
	if($kcinfo['kc_type'] == 1){
		$allmenu =  pdo_fetchall("SELECT * FROM " . GetTableName('kc_menu') . " where schoolid ='{$schoolid}' And kcid ='{$kcid}' ORDER BY id ASC");
		$menunub = count($allmenu);
	}
} elseif ($operation == 'xu_zd') {	//虚拟拼团 组队
	mload()->model('kc');
	$uidarray = $_GPC['uidarray'];
	$teamid = $_GPC['teamid'];
	$pkuser = $_W['tid'];
	$team = pdo_fetch("SELECT kcid FROM " . GetTableName('sale_team') . " WHERE id = {$teamid}");
	$kcinfo = pdo_fetch("SELECT sale_type FROM " . GetTableName('tcourse') . " WHERE id = {$team['kcid']}");
	$result = SetXnFans($teamid,$uidarray,$pkuser);//调用虚拟插入粉丝方法
	foreach($uidarray as $openid){
		if($kcinfo['sale_type'] == 1){
			$this->sendMobilePttz($teamid,$openid);
		}
		if($kcinfo['sale_type'] == 2){
			$this->sendMobileZltz($teamid,$openid);
		}
	}
	die ( json_encode($result) );
} elseif ($operation == 'team_info') {	//队伍详情
	mload()->model('kc');
	$teamid = $_GPC['teamid'];
	$type = $operation;
	$list = GetTeamList($teamid,false);
	include $this->template('public/anyfans_list');
	die();
} elseif ($operation == 'clearallpop') {	//清理所有海报文件
	$kcid = $_GPC['kcid'];
	load()->func('file');
	$allpop = pdo_fetchall("SELECT * FROM " . GetTableName('promote_pop') . " WHERE kcid = {$kcid}");
	if($allpop){
		$nub = 0;
		foreach($allpop as $row){
			if($row['pop_url']){
				file_delete($row['pop_url']);
				pdo_delete( GetTableName('promote_pop',false),array('id' => $row['id']));
				$nub++;
			}
		}
		$result['result'] = true;
		$result['msg'] = "成功清理!".$nub."张已生成得海报";
	}else{
		$result['result'] = false;
		$result['msg'] = "本课暂未有人生成过海报";
	}
	die ( json_encode($result) );	
} elseif ($operation == 'del_xnfans') {	//移除一个虚拟粉丝
	mload()->model('kc');
	$id = $_GPC['id'];
	$result = DelOneFans($id);
	die ( json_encode($result) );
} elseif ($operation == 'rebackorder') {//拼团失败的队伍单击退费操作
	mload()->model('kc');
	$teamid = intval($_GPC['id']);
	$team = pdo_fetch("SELECT orderid FROM " . GetTableName('sale_team') . " WHERE id = {$teamid}");
	$item = pdo_fetch("SELECT status,uniontid,pay_type,payweid,weid,cose,uniontid,id,spoint FROM " . GetTableName('order') . " WHERE id = {$team['orderid']}");//查询订单
	if (empty($item)) {
		$result['result'] = false;
		$result['msg'] = "未查询到订单!";
	}else{
		if ($item['status'] == 3) {
			$result['result'] = false;
			$result['msg'] = "抱歉，本订单已退费，无需重复操作!";
		}else{
			$stu =  pdo_fetch("SELECT points,chongzhi FROM " . GetTableName('order') . " WHERE id = {$item['sid']}");
			$refund = get_schoolset($schoolid,'refund');
			if($item['uniontid'] && ($item['pay_type'] == 'wxapp' || $item['pay_type'] == 'wechat') && $refund == 1){
				mload()->model('wxpay');
				$payweid = empty($item['payweid'])?$item['weid']:$item['payweid'];
				$wxPay = new WxpayService($payweid,$item['pay_type']);
				$result = $wxPay->doRefund($item['cose'], $item['cose'], $item['uniontid'],$orderNo,$payweid);
				if($result === true){
					$data = array('status' => 3,'refundid'=>$item['id'],'tuitime'=>time()); 
					pdo_update(GetTableName('order',false), $data, array('id' => $item['id']));
					$result['result'] = true;
					$result['msg'] = "退费成功,该订单费用已原路退回买家付款微信帐号！";
				}else{
					$result['result'] = false;
					$result['msg'] = "退费失败,发起微信退费错误";
				}
			}else{
				$data = array('status' => 3,'tuitime'=>time()); 
				pdo_update(GetTableName('order',false), $data, array('id' => $item['id']));
				pdo_update(GetTableName('sale_team',false), array('tfuser' => $tid_global,'tuifei' => 3), array('id' => $teamid));
				if($item['spoint'] > 0 && $item['sid'] > 0){//返还积分
					pdo_update(GetTableName('students',false), array('points' => $stu['points'] + $item['spoint']), array('id' => $item['sid']));
				}
				if($item['pay_type'] == 'credit'){//饭还余额
					pdo_update(GetTableName('students',false), array('chongzhi' => $stu['chongzhi'] + $item['cose']), array('id' => $item['sid']));
				}
				$result['result'] = true;
				$result['msg'] = "已修改订单状态为退费,未真实退款,本校未设置退费配置,或本订单非微信支付";
			}
		}
	}
	die ( json_encode($result) );
} elseif ($operation == 'del_menu') {
	$id = intval($_GPC['menuid']);
	$menu = pdo_fetch("SELECT * FROM " .GetTableName('kc_menu') . " WHERE id = :id", array(':id' => $id));
	if (empty($menu)) {
		$data['result'] = false;
		$data['msg'] = "抱歉，本条章节不存在或已删除！!";
	}else{
		pdo_delete(GetTableName('kc_menu',false), array('id' => $id));
		$data['result'] = true;
		$data['msg'] = "删除成功!";
	}
	die(json_encode($data));
} elseif ($operation == 'delete') {
	$id = intval($_GPC['kcid']);
	$goods = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id", array(':id' => $id));
	if (empty($goods)) {
		$data['result'] = false;
		$data['msg'] = "抱歉，本条信息不存在或已删除！!";
	}else{
		pdo_delete(GetTableName('kc_menu',false), array('kcid' => $id));
		pdo_delete(GetTableName('kcbiao',false), array('kcid' => $id));
		pdo_delete(GetTableName('kcsign',false), array('kcid' => $id));
		pdo_delete(GetTableName('coursebuy',false), array('kcid' => $id));
		pdo_delete(GetTableName('order',false), array('kcid' => $id));
		pdo_delete(GetTableName('tcourse',false), array('id' => $id));
		$data['result'] = true;
		$data['msg'] = "删除成功!";
	}
	die(json_encode($data));
} elseif ($operation == 'deleteall') {
	$rowcount = 0;
	$notrowcount = 0;
	$success_id = array();
	foreach ($_GPC['idArr'] as $k => $id) {
		$id = intval($id);
		if (!empty($id)){
			$goods = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id", array(':id' => $id));
			if (empty($goods)) {
				$notrowcount++;
				continue;
			}
			pdo_delete($this->table_tcourse, array('id' => $id, 'weid' => $weid));
			$success_id[] = $id;
			$rowcount++;
		}
	}
	$data['result'] = true;
	$data['idarr'] = $success_id;
	$data['msg'] = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	die(json_encode($data));
} elseif ($operation == 'add_newks') {
	$kcid = intval($_GPC['kcid']);
	$kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id", array(':id' => $kcid));
	if (empty($kcinfo)) {
		$data['result'] = false;
		$data['msg'] = "抱歉，课程不存在或是已经删除！";
	}
	$dataarray = array(
		'weid' => $weid,
		'schoolid' => $schoolid,
		'tid' => intval($_GPC['tid']),
		'kcid' => $kcid,
		'km_id' => $kcinfo['km_id'],
		'bj_id' => $kcinfo['bj_id'],					
		'sd_id' => intval($_GPC['sd_id']),
		'xq_id' => $kcinfo['xq_id'],
		'costnum' => intval($_GPC['costnum']),
		'content' => trim($_GPC['content']),
		'addr_id'=> $_GPC['adrr'],
		'pkuser' => $_W['tid'],
		'createtime'=> time()
	);
	if($_GPC['pkmode'] == 'guize' || $_GPC['pkmode'] == 'rili'){
		if($_GPC['re_type'] == 1 || $_GPC['re_type'] == 2 || $_GPC['re_type'] == 3){
			$dataarray['rulsetid'] = getRandomString(10);
			$sdinfo = pdo_fetch("SELECT sd_start,sd_end,sname FROM " . GetTableName('classify') . " where sid = :sid ", array(':sid' => $_GPC['sd_id']));
			$weekarray = $_GPC['week'];
			$start = strtotime(trim($_GPC['start']));
			$end = strtotime(trim($_GPC['end']));
			$starttime = mktime(0,0,0,date('m',$start),date('d',$start),date('Y',$start));
			$endtime = mktime(0,0,0,date('m',$end),date('d',$end)+1,date('Y',$end))-1;
			$j = count_days($starttime,$endtime);
			$dayarray = array();		
			if($_GPC['re_type'] == 1){
				$dataarray['re_type'] =1;//每周
				$datearray = array();
				for($i=0;$i<$j;$i++){
					$datearray[] = array(
						'time' => $starttime+$i*86400,//每隔一天赋值给数组
						'date' => date('Y-m-d',$starttime+$i*86400),
						'day' => $i +1,
						'week' => date('w',$starttime+$i*86400) %7==0?7:date('w',$starttime+$i*86400)//处理w=0为7
					);
				}
				foreach($datearray as $key => $row){
					if(in_array($row['week'],$weekarray)){
						$dayarray[] = array(
							'date' => $row['date'],
							'time' => $row['time'],
							'week' => $row['week']
						);
					}
				}
			}
			if($_GPC['re_type'] == 3){//日历
				$dataarray['re_type'] =3;
				$gpcdayarray = $_GPC['dataarry'];
				foreach($gpcdayarray as $k => $d){
					$dayarray[] = array(
						'date' => $d,
						'time' => strtotime($d.' 00:00:00')
					);
				}
			}
			if($_GPC['re_type'] == 2){
				$dataarray['re_type'] =2;//隔周
				if($j >25){
					$startweek = date('w',$start) %7==0?7:date('w',$start);
					foreach($weekarray as $key => $row){
						if($row >= $startweek){
							$firstKCDate = $start + ($row - $startweek )* 86400;
						}else{
							$firstKCDate = $start + ($row - $startweek + 7)* 86400;//处理w=0为7
						}
						$dayarray[] = array(
							'time' => $firstKCDate,
							'date' => date('Y-m-d',$firstKCDate),
						);
						$inputdate = $firstKCDate;
						while (1){
							$inputdate = $inputdate + 14*86400;
							if($inputdate <= $end){
								$dayarray[] = array(
									'time' => $inputdate,
									'date' => date('Y-m-d',$inputdate),
								);
							}else{
								break;
							}
						}
					}
				}else{
					$data['result'] = false;
					$data['msg'] = "抱歉,你所选的日期范围太小无法执行隔周排课,请至少涵盖25天以上";
				}
			}
			if(count($dayarray) < 1){
				$data['dayarray'] = $dayarray;
				$data['result'] = false;
				$data['msg'] = "抱歉，你选择的日期范围和周几设置无法排课,请检查设置！";
			}else{
				$sucks = 0;
				$defks = 0;
				$defaddr = '';
				$defakc = '';
				$defatea = '';
				foreach($dayarray as $k => $r){
					$lasttime = $r['date'].date(" H:i",$sdinfo['sd_start']);
					$dataarray['date'] = strtotime($lasttime)+12;
					$check_start = strtotime($r['date'].date(" H:i",$sdinfo['sd_start']));
					$check_end   = strtotime($r['date'].date(" H:i",$sdinfo['sd_end']));
					if(intval($_GPC['adrr']) > 0){
						$checkaddr =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where addr_id = '{$_GPC['adrr']}' And date >= '{$check_start}' And date < '{$check_end}' ");
						if(empty($checkaddr)){
							$checkkc =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where kcid = '{$kcid}' And date >= '{$check_start}' And date < '{$check_end}' ");
							if(empty($checkkc)){
								$checktea =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where tid ='{$dataarray['tid']}' And date >= '{$check_start}' And date < '{$check_end}' ");
								if(empty($checktea)){
									pdo_insert(GetTableName('kcbiao',false),$dataarray);
									$sucks ++;
								}else{
									$defatea .= ",".$r['date'];
									$defks ++;
								}
							}else{
								$defakc .= ",".$r['date'];
								$defks ++;
							}
						}else{
							$defaddr .= ",".$r['date'];
							$defks ++;
						}
						$data['OK'] = 1;
						$data['sucks'] = $sucks;
						$data['result'] = true;
						$data['msg'] = "成功排课".$sucks."节";
						if($defakc != '' || $defaddr != '' || $defatea != ''){
							if($defakc != ''){
								$data['msg'] .= ",本课程以下日期排课冲突".$defakc;
							}
							if($defaddr != ''){
								$data['msg'] .= ",本教室以下日期排课冲突".$defaddr;
							}
							if($defatea != ''){
								$data['msg'] .= ",该老师以下日期排课冲突".$defatea;
							}
						}
					}else{
						$checkkc =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where kcid = '{$kcid}' And date >= '{$check_start}' And date < '{$check_end}' ");
						if(empty($checkkc)){
							$checktea =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where tid = '{$dataarray['tid']}' And date >= '{$check_start}' And date < '{$check_end}' ");
							if(empty($checktea)){
								pdo_insert(GetTableName('kcbiao',false),$dataarray);
								$sucks ++;
							}else{
								$defatea .= ",".$r['date'];
								$defks ++;
							}
						}else{
							$defakc .= ",".$r['date'];
							$defks ++;
						}
						$data['OK'] = 2;
						$data['sucks'] = $sucks;
						$data['result'] = true;
						$data['msg'] = "成功排课".$sucks."节";
						if($defakc != '' || $defatea != ''){
							if($defakc != ''){
								$data['msg'] .= ",本课程以下日期排课冲突".$defakc;
							}
							if($defatea != ''){
								$data['msg'] .= ",该老师以下日期排课冲突".$defatea;
							}
						}
					}
				}
			}
		}
	}
	if($_GPC['pkmode'] == 'online'){
		$sucks = 0;
		$unsetname = 0;
		$unsetnames = '';
		$errotime = 0;
		$errotimes = '';
		foreach($_GPC['name'] as $k => $row){
			if(!$row){
				$unsetname ++;
				$kes = $k+1;
				$unsetnames .= $kes.',';
			}
			if($_GPC['content_type'][$k] == 1){
				if(strtotime($_GPC['sk_start'][$k]) >= strtotime($_GPC['sk_end'][$k])){
					$et = $k+1;
					$errotimes .= $et.',';
					$errotime ++;
				}
			}
		}
		if($errotime>0){
			$data['result'] = false;
			$data['msg'] = '错误,请为第'.rtrim($errotimes,',').'个课时设置直播开播起始时间且结束时间不能小于开始时间';	
			die(json_encode($data));
		}
		if($unsetname>0){
			$data['result'] = false;
			$data['msg'] = '错误,第'.rtrim($unsetnames,',').'个课时未设置名称';	
			die(json_encode($data));
		}
		if($unsetname>0){
			$data['result'] = false;
			$data['msg'] = '错误,第'.rtrim($unsetnames,',').'个课时未设置名称';	
			die(json_encode($data));
		}
		$unsetmus = 0;
		$unsetmenu = '';
		if($kcinfo['allow_menu'] == 1){
			foreach($_GPC['menuid'] as $k => $row){
				if(!$row){
					$unsetmus ++;
					$kes = $k+1;
					$unsetmenu .= $kes.',';
				}
			}
			if($unsetmus>0){
				$data['result'] = false;
				$data['msg'] = '错误,第'.rtrim($unsetmenu,',').'个课时未选择归属章节';	
				die(json_encode($data));
			}
		}
		$unsettype = 0;
		$unsettypes = '';
		foreach($_GPC['content_type'] as $i => $r){
			if($r != '-1'){
			}else{
				$unsettype ++;
				$ke = $i+1;
				$unsettypes .= $ke.',';
			}
		}
		if($unsettype>0){
			$data['result'] = false;
			$data['msg'] = '错误,第'.rtrim($unsettypes,',').'个课时未选择内容类型';
			die(json_encode($data));
		}
		$unsetcont = 0;
		$unsetconts = '';
		foreach($_GPC['conment'] as $s => $c){
			if(!$c){
				$unsetcont ++;
				$key = $s+1;
				$unsetconts .= $key.',';
			}
		}
		if($unsetcont>0){
			$data['result'] = false;
			$data['msg'] = '错误,第'.rtrim($unsetconts,',').'个课时未设置课时内容';
			die(json_encode($data));
		}
		foreach($_GPC['name'] as $key => $item){
			$dataarray = array(
				'weid' 			=> $weid,
				'schoolid' 		=> $schoolid,
				'tid' 			=> intval($_GPC['tid'][$key]),
				'name' 			=> $_GPC['name'][$key],
				'kcid' 			=> $kcid,
				'is_allow_reply'=> $_GPC['is_allow_reply'][$key],
				'is_allow_ykreply'=> $_GPC['is_allow_ykreply'][$key],
				'sk_end'        => strtotime($_GPC['sk_end'][$key]),
				'sk_start'   	=> strtotime($_GPC['sk_start'][$key]),
				'menu_id' 		=> $_GPC['menuid'][$key],
				'is_try_see' 	=> $_GPC['is_try_see'][$key],
				'ssort' 		=> $_GPC['ssort'][$key],
				'content_type' 	=> $_GPC['content_type'][$key],
				'createtime' 	=> time()
			);
			if($_GPC['content_type'][$key] == 0){
				$dataarray['content'] = htmlspecialchars_decode($_GPC['conment'][$key]);
			}else{
				$dataarray['content'] = $_GPC['conment'][$key];
			}
			if($_GPC['ksid'][$key] > 0){//处理通过弹框单个修改的线上课时
				unset($dataarray['is_try_see']);
				unset($dataarray['createtime']);
				pdo_update(GetTableName('kcbiao',false), $dataarray, array('id' => intval($_GPC['ksid'][$key])));
			}else{
				pdo_insert(GetTableName('kcbiao',false), $dataarray );
			}
			$sucks++;
		}
		$data['conment'] = $_GPC['conment'];
		$data['result'] = true;
		$data['sucks'] = $sucks;
		$data['msg'] = "成功排课".$sucks."节";
	}
	die(json_encode($data));
} elseif ($operation == 'add') {
	 if (!(IsHasQx($tid_global,1000922,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	 $addr =  pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where weid = '{$weid}' And schoolid ='{$schoolid}' And type='addr' ORDER BY ssort DESC ");
	load()->func('tpl');
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id", array(':id' => $id));	
		$tidarray = explode(',', $item['tid']);
		foreach( $tidarray as $key => $value )
		{
			$teachers[$key] = pdo_fetch("SELECT id,tname FROM " . GetTableName ('teachers') . " where id = :id ", array(':id' => $value));		
		}
		if (empty($item)) {
			$this->imessage('抱歉，课程不存在或是已经删除！', '', 'error');
		}
	}
	if (checksubmit('submit')) {
		$wrong_back = '';
		if(!empty($_GPC['new'])){
			foreach($_GPC['new'] as $key => $name){
				$sdinfo = pdo_fetch("SELECT sd_start,sd_end FROM " . GetTableName('classify') . " where sid = :sid ", array(':sid' => $_GPC['sd_new'][$key]));
			
				$lasttime =$_GPC['date_new'][$key].date(" H:i",$sdinfo['sd_start']);
				$check_start = strtotime($_GPC['date_new'][$key].date(" H:i",$sdinfo['sd_start']));
				$check_end   = strtotime($_GPC['date_new'][$key].date(" H:i",$sdinfo['sd_end']));
				$check =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where addr_id=:addr_id And date>=:start And date<= :end ", array(':addr_id' => $_GPC['skaddr_new'][$key],':start'=>$check_start,':end'=>$check_end));
				$data = array(
					'weid' => $weid,
					'schoolid' => $schoolid,
					'tid' => intval($_GPC['sktid_new'][$key]),
					'kcid' => trim($_GPC['kcid']),
					'bj_id' => trim($_GPC['bj_id']),
					'km_id' => trim($_GPC['km_id']),					
					'sd_id' => trim($_GPC['sd_new'][$key]),
					'xq_id' => trim($_GPC['xq']),					
					'nub' => trim($_GPC['nub_new'][$key]),
					'isxiangqing' => trim($_GPC['isxiangqing']),
					'content' => trim($_GPC['content']),
					'date' => strtotime($lasttime),
					'addr_id'=> $_GPC['skaddr_new'][$key],
				);
				if(keep_sk77()){
					$data['costnum'] = $_GPC['costnum_new'][$key]? $_GPC['costnum_new'][$key]:1;
				}
				if(empty($_GPC['sktid_new'][$key])){
					$this->imessage('授课老师不能为空！', '', 'referer');
				}
				if (istrlen($_GPC['nub_new'][$key]) == 0) {
					$this->imessage('没有输入编号.', '', 'error');
				}
				if(!empty($check)){
					$chongtukc = pdo_fetch("SELECT kcid,nub FROM " . GetTableName('kcbiao') . " where id='{$check['id']}' ");
					$chongtukecheng = pdo_fetch("SELECT name FROM " . GetTableName('tcourse') . " where id='{$chongtukc['kcid']}' ");
					$wrong_back .="第".$_GPC['nub_new'][$key]."课 与 ".$chongtukecheng['name']."【第".$chongtukc['nub']."课时】冲突";
				}else{	
					pdo_insert($this->table_kcbiao, $data);
				}
			}
			$back_str = '操作成功！';
			if($wrong_back != ''){
				$back_str .='</br>以下课时教室冲突，新增失败:'.$wrong_back;
			}
			$this->imessage($back_str, $this->createWebUrl('kecheng', array('op' => 'display', 'schoolid' => $schoolid)), 'success');  
		}   
	}
} elseif ($operation == 'del_gg'){
	$schoolid = $_GPC['schoolid'];
	$id = $_GPC['id'];
	$res = pdo_fetch("SELECT id FROM ".GetTableName('order')." WHERE schoolid = '{$schoolid}' AND guigeid = '{$id}' ");
	if($res){
		$data['result'] = true;
		$data['msg'] = "当前规格已有学员购买，无法删除当前规格！";
	}else{
		$data['result'] = false;
		$data['msg'] = "删除成功";
	}
	die(json_encode($data));
} elseif ($operation == 'GetStuListByKc'){
	mload()->model('kc');
	mload()->model('excel');
	$kcid = $_GPC['kcid'];
	$nowtime = time();
	$optype = 'stu_list';
	$condition = array('page' =>$_GPC['page'],'ks_type' => $_GPC['ks_type'],'s_name' => $_GPC['s_name'],'mobile' => $_GPC['mobile']);
	$kcinfo = pdo_fetch("SELECT kc_type,name FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $_GPC['kcid']));
	//查处所有课程，排除当前课程
	$checkNowTime = strtotime(date("Y-m-d",time()));
	$list = GetOneKcStuList($kcid,$condition,true);
	$OutArr = [];
	if(is_array($list['list'])){
		$AllKc = pdo_fetchAll("SELECT id,name FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' AND NOT FIND_IN_SET(id,'{$_GPC['kcid']}') AND end > '{$checkNowTime}'");
		foreach ($list['list'] as $key => $value) {
			// $grant = pdo_fetchcolumn("SELECT SUM(ksnum) FROM ".GetTableName('freekslog')." WHERE kcid = '{$_GPC['kcid']}' AND sid = '{$value['sid']}' ");
			// $stuover = pdo_fetch("SELECT overtime FROM " . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$value['sid']}' AND kcid = '{$kcid}' ");
			// $list['list'][$key]['isover'] = ($stuover['overtime'] <= $nowtime && $stuover['overtime'] != 0 ) ? '过期': '';
			// $list['list'][$key]['grantks'] = $grant ? $grant : 0;
			$OutArr[] = array(
				'sid'=>$value['sid'],
				's_name'=>$value['s_name'],
				'ksnum'=>$value['ksnum'],
				'rest'=>$value['ksnum'] - $value['hasSign'],
				'free' => 0 ,
				'beizhu' => '',
				'kcname' => $kcinfo['name']
			);
			
		}
	}
	$TitleArr = array(
		0 => array('sid','学生ID'),
		1 => array('s_name','学生姓名'),
		2 => array('kcname','课程名称'),
		3 => array('ksnum','总课时'),
		4 => array('rest','剩余课时'),
		5 => array('free','赠送课时'),
		6 => array('beizhu','赠送备注'),
	);

	//var_dump($list['list']);
	$fileTitle = $kcinfo['name']."学员列表";
	ReExportExcel($fileTitle,$TitleArr,$OutArr);
	exit();
} elseif ($operation == 'OutExcel'){
	$kcinfo = pdo_fetch('SELECT * FROM ' . GetTableName('tcourse') . " WHERE id = '{$_GPC['kcid']}' And schoolid = '{$_GPC['schoolid']}'");
	$tidarray = explode(',', $kcinfo['tid']);
	$ii = 0;
	$ExcelArr = [];
	foreach( $tidarray as $key => $value ){
		$maintid = 2;
		if($kcinfo['maintid'] == $tea['id']){
			$maintid = 1;
		}
		$tea = pdo_fetch("SELECT id,tname,mobile FROM " . GetTableName ('teachers') . " where id = :id ", array(':id' => $value));
		//主讲签到次数
		$zjsignnum = pdo_fetchcolumn("select count(id) FROM ".GetTableName('kcsign')." WHERE kcid = '{$_GPC['kcid']}' And tid = '{$tea['id']}' And status = 2 AND ismaster_tid = 1");
		$fdsignnum = pdo_fetchcolumn("select count(id) FROM ".GetTableName('kcsign')." WHERE kcid = '{$_GPC['kcid']}' And tid = '{$tea['id']}' And status = 2 AND ismaster_tid = 2");
		$ExcelArr[$ii]['tname'] = $tea['tname'];
		$ExcelArr[$ii]['mobile'] = $tea['mobile'];
		$ExcelArr[$ii]['zjsignnum'] = $zjsignnum.'课';
		$ExcelArr[$ii]['fdsignnum'] = $fdsignnum.'课';
		$ExcelArr[$ii]['maintid'] = $maintid;
		$ii++;
	}
	$ExcelArr = array_sorts($ExcelArr,'maintid','asc');//主讲前面
	foreach ($ExcelArr as $key => $value) {
		unset($ExcelArr[$key]['maintid']);
	}
	$ExcelTitle ="老师代签记录";
	$this->exportexcel($ExcelArr, array('老师姓名', '手机号', '主讲签到','助教签到'), $ExcelTitle);
	exit();
} elseif ($operation == 'getChildType'){
	$nj_id = $_GPC['nj_id'];
	$kcclass = pdo_fetchall("SELECT * FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '{$nj_id}' And type = 'kcclass' ");
	if($kcclass){
		$result['data'] = $kcclass;
		$result['result'] = true;
	}else{
		$result['result'] = false;
	}
	die(json_encode($result));
}




function count_days($a,$b){
	$a_dt=getdate($a);
	$b_dt=getdate($b);
	$a_new=mktime(12,0,0,$a_dt['mon'],$a_dt['mday'],$a_dt['year']);
	$b_new=mktime(12,0,0,$b_dt['mon'],$b_dt['mday'],$b_dt['year']);
	return round(abs($a_new-$b_new)/86400)+1;
}


include $this->template ( 'web/kecheng' );
?>