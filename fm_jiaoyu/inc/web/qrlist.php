<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school            = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ORDER BY ssort DESC", array(':id' => $schoolid));

$bj = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
	$list = pdo_fetchall("SELECT s_name,qrcode_id,bj_id FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC");
	foreach($list as $key => $value){				
		$bj                = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$value['bj_id']}'");		
		if($value['qrcode_id']){
			$qrimg = pdo_fetch("SELECT show_url,expire,subnum FROM " . tablename($this->table_qrinfo) . " where  id = '{$value['qrcode_id']}' ");
			$list[$key]['img_qr'] = tomedia($qrimg['show_url']);
			$list[$key]['bjname'] = $bj['sname'];
			$list[$key]['overtime'] = true;
			if($qrimg['expire'] < time()){
				$list[$key]['overtime'] = false;
			}
		}
	}
}elseif($operation == 'choose'){
	$bj_id = $_GPC['bj_id_choose'];
	
	$list1 = pdo_fetchall("SELECT s_name,qrcode_id,bj_id FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id='{$bj_id}' ORDER BY id DESC");

	foreach($list1 as $key => $value){				
		$bj                = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = '{$value['bj_id']}'");		
		if($value['qrcode_id']){
			$qrimg = pdo_fetch("SELECT show_url,expire,subnum FROM " . tablename($this->table_qrinfo) . " where  id = '{$value['qrcode_id']}' ");
			$list1[$key]['img_qr'] = tomedia($qrimg['show_url']);
			$list1[$key]['bjname'] = $bj['sname'];
			$list1[$key]['overtime'] = true;
			if($qrimg['expire'] < time()){
				$list1[$key]['overtime'] = false;
			}
		}
	}
	
}
include $this->template('web/qrlist');
?>