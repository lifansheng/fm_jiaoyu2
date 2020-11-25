<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
	global $_GPC, $_W;
	$weid = $_W['uniacid'];
	load()->func('tpl');
	$state = uni_permission($_W['uid'], $uniacid);
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display1';
	if ($operation == 'display1') {
		$pindex    = max(1, intval($_GPC['page']));
		$psize     = 20;			
		$schoolist = pdo_fetchall("SELECT id,title,logo FROM " . tablename($this->table_index) . " WHERE weid = '{$weid}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		foreach($schoolist as $key =>$row){
			$refund = pdo_fetch('SELECT refund,tx_pay FROM ' . tablename($this->table_schoolset) . " WHERE weid = '{$weid}' And schoolid = '{$row['id']}' ");
			$schoolist[$key]['refund'] = 0;
			if($refund && $refund['refund'] == 1){
				$schoolist[$key]['refund'] = 1;
			}
			$schoolist[$key]['pay'] = 2;
			if($refund && $refund['tx_pay'] == 1){
				$schoolist[$key]['pay'] = 1;
			}
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_index) . " WHERE weid = '{$weid}' $condition ");
		$pager = pagination($total, $pindex, $psize);
	} elseif ($operation == 'display2'){		
		$wechats = pdo_fetchall("SELECT acid,uniacid,name FROM " . tablename('account_wechats') . " WHERE level = 4 ORDER BY acid ASC ");
		foreach($wechats as $key =>$row){
			$wechats[$key]['cert'] = IA_ROOT.'/addons/fm_jiaoyu/public/cert/'.$row['uniacid'].'/apiclient_cert.pem';
			$wechats[$key]['key'] = IA_ROOT.'/addons/fm_jiaoyu/public/cert/'.$row['uniacid'].'/apiclient_key.pem';
			$wechats[$key]['is_wxapp'] = false;
			$wechats[$key]['certs'] = false;
			if(file_exists($wechats[$key]['cert']) && file_exists($wechats[$key]['key'])){
				$wechats[$key]['certs'] = true;
			}
		}
		
		$wxapps = pdo_fetchall("SELECT acid,uniacid,name FROM " . tablename('account_wxapp') . " WHERE level = 1 ORDER BY acid ASC ");
		foreach($wxapps as $key =>$row){
			$wxapps[$key]['cert'] = IA_ROOT.'/addons/fm_jiaoyu/public/cert/'.$row['uniacid'].'/apiclient_cert.pem';
			$wxapps[$key]['key'] = IA_ROOT.'/addons/fm_jiaoyu/public/cert/'.$row['uniacid'].'/apiclient_key.pem';
			$wxapps[$key]['is_wxapp'] = true;
			$wxapps[$key]['certs'] = false;
			if(file_exists($wxapps[$key]['cert']) && file_exists($wxapps[$key]['key'])){
				$wxapps[$key]['certs'] = true;
			}
		}
		$datas = array_merge($wechats,$wxapps);
	} elseif ($operation == 'change'){
		$set = pdo_fetch('SELECT id,refund FROM ' . tablename($this->table_schoolset) . " WHERE weid = '{$weid}' And schoolid = '{$_GPC['id']}' ");
		$data = array(
			'weid' => $weid,
			'schoolid' => $_GPC['id'],
			'refund' => intval($_GPC['is_on'])
		);
		if($set){
			pdo_update($this->table_schoolset,$data,array('id' => $set['id']));
		}else{
			pdo_insert($this->table_schoolset,$data);
		}
		die;
	} elseif ($operation == 'pay'){
		$set = pdo_fetch('SELECT id,tx_pay FROM ' . tablename($this->table_schoolset) . " WHERE weid = '{$weid}' And schoolid = '{$_GPC['id']}' ");
		$data = array(
			'weid' => $weid,
			'schoolid' => $_GPC['id'],
			'tx_pay' => intval($_GPC['tx_pay'])
		);
		if($set){
			pdo_update($this->table_schoolset,$data,array('id' => $set['id']));
		}else{
			pdo_insert($this->table_schoolset,$data);
		}
		die;
	} elseif ($operation == 'upcert'){
		if(checksubmit('submit')){
			load()->func('file');
			$acid = $_GPC['acid'];
			if($_FILES['cert'] && $_FILES['key']){
				if ($_FILES['cert']['type'] != 'application/octet-stream' || $_FILES['key']['type'] != 'application/octet-stream') {
					itoast('文件类型错误', '', 'error');
				}
				if (!is_dir(IA_ROOT."/addons/fm_jiaoyu/public/cert/". $acid."/")) {
					mkdirs(IA_ROOT."/addons/fm_jiaoyu/public/cert/". $acid."/", "0777");
				}
				$cert = file_get_contents($_FILES['cert']['tmp_name']);
				$cert_name = 'apiclient_cert.pem';
				if ($cert_name != $_FILES['cert']['name']) {
					itoast('上传文件不合法,请重新上传', '', 'error');
				}
				file_put_contents(IA_ROOT. "/addons/fm_jiaoyu/public/cert/".$acid."/". $_FILES['cert']['name'], $cert);
				$key = file_get_contents($_FILES['key']['tmp_name']);
				$key_name = 'apiclient_key.pem';
				if ($key_name != $_FILES['key']['name']) {
					itoast('上传文件不合法,请重新上传', '', 'error');
				}
				file_put_contents(IA_ROOT. "/addons/fm_jiaoyu/public/cert/".$acid."/". $_FILES['key']['name'], $key);
				message('上传成功');
			}else{
				itoast('请选择文件', '', 'error');
			}
			
		}
	} else{
		message('操作失败, 非法访问.');
	}
        
   include $this->template ( 'web/refund' );
?>