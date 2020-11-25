<?php

/**
 * By 高贵血迹
 */

global $_GPC, $_W;

$weid = $_GPC['i'];
$schoolid = $_GPC['schoolid'];
$macid = $_GPC['macid'];
$cardid = $_GPC['sKH'];
$pic = trim($_GPC['sImg']);
$time = $_GPC['sDateYMDHMS'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
$xk_type = pdo_fetch("SELECT * FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' ");
$school['xk_type'] = $xk_type['xk_type'];
$ckmac = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE macid = '{$macid}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
$ckuser = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$cardid}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
if ($school['is_recordmac'] == 1) {
	if ($ckmac['is_on'] == 1) {
		if (!empty($ckuser)) {
			$times = TIMESTAMP;
			$nowtime = date('H:i', $times);
			if (empty($_GPC['jx_type'])) {
				if ($ckmac['type'] != 0) {
					include 'checktime2.php';
				} else {
					include 'checktime.php';
				}
			} else {
				if ($_GPC['jx_type'] == 1) {
					$type = "进校";
				}
				if ($_GPC['jx_type'] == 2) {
					$type = "离校";
				}
			}
			$bj = pdo_fetch("SELECT bj_id,isopen FROM " . tablename($this->table_students) . " WHERE id = {$ckuser['sid']} ");
			$picurl = "";
			if (!empty($ckuser['sid'])) {
				if ($school['is_cardpay'] == 1) {
					$data = array(
						'weid' => $weid,
						'schoolid' => $schoolid,
						'macid' => $ckmac['id'],
						'cardid' => $cardid,
						'sid' => $ckuser['sid'],
						'bj_id' => $bj['bj_id'],
						'type' => $type,
						'pic' => $pic,
						'leixing' => empty($_GPC['jx_type']) ? $leixing : trim($_GPC['jx_type']),
						'pard' => $ckuser['pard'],
						'createtime' => time(),
						'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
					);
					if ($school['xk_type'] == '1') {
						mload()->model('kc');
						$back = GetnearksBySid($schoolid, $weid, $ckuser['sid'], $signTime, true);
					}
					if (!empty($back['sendMsgArr'])) {
						foreach ($back['sendMsgArr'] as $row) {
							$this->sendMobileXsqrqdtz($row, $schoolid, $weid);
						}
					}
					pdo_insert($this->table_checklog, $data);
					$checkid = pdo_insertid();
					if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
						if ($school['send_overtime'] == -1) {
							if (is_showyl()) {
								$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
							} else {
								$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
							}
						} else {
							$overtime = $school['send_overtime'] * 60;
							$timecha = $times - $signTime;
							if ($overtime >= $timecha) {
								if (is_showyl()) {
									$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
								} else {
									$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
								}
							}
						}
						$arr = "Check OK!";
					} else {
						$arr = "This card severtime is out!";
					}
				} else {
					$data = array(
						'weid' => $weid,
						'schoolid' => $schoolid,
						'macid' => $ckmac['id'],
						'cardid' => $cardid,
						'sid' => $ckuser['sid'],
						'bj_id' => $bj['bj_id'],
						'type' => $type,
						'pic' => $pic,
						'leixing' => empty($_GPC['jx_type']) ? $leixing : trim($_GPC['jx_type']),
						'pard' => $ckuser['pard'],
						'createtime' => time(),
						'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
					);

					if ($school['xk_type'] == '1') {
						mload()->model('kc');
						$back = GetnearksBySid($schoolid, $weid, $ckuser['sid'], $signTime, true);
					}
					if (!empty($back['sendMsgArr'])) {
						foreach ($back['sendMsgArr'] as $row) {
							$this->sendMobileXsqrqdtz($row, $schoolid, $weid);
						}
					}
					pdo_insert($this->table_checklog, $data);
					$checkid = pdo_insertid();
					if ($ckuser['severend'] > $times && $bj['isopen'] == 1) {
						if ($school['send_overtime'] == -1) {
							if (is_showyl()) {
								$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
							} else {
								$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
							}
						} else {
							$overtime = $school['send_overtime'] * 60;
							$timecha = $times - $signTime;
							if ($overtime >= $timecha) {
								if (is_showyl()) {
									$this->sendMobileJxlxtz_yl($schoolid, $weid, $ckuser['sid'], $checkid, $ckmac['id']);
								} else {
									$this->sendMobileJxlxtz($schoolid, $weid, $bj['bj_id'], $ckuser['sid'], $type, $leixing, $checkid, $ckuser['pard']);
								}
							}
						}
					}
					$arr = "Check OK!";
				}
			}
			if (!empty($ckuser['tid'])) {
				$data = array(
					'weid' => $weid,
					'schoolid' => $schoolid,
					'macid' => $macid,
					'cardid' => $cardid,
					'pic' => $pic,
					'tid' => $ckuser['tid'],
					'type' => $type,
					'leixing' => empty($_GPC['jx_type']) ? $leixing : trim($_GPC['jx_type']),
					'pard' => 1,
					'createtime' => time(),
					'surestatus'  => $xk_type['is_sure_kq'] == 1 ? 0 : 1,
				);
				pdo_insert($this->table_checklog, $data);
				$arr = "Check OK!";
			}
		} else {
			$arr = "No this card!";
		}
	} else {
		$arr = "No this mac!";
	}
} else {
	$arr = "This mac is closed!";
}
$fmdata = array(
	"Upload" => $arr,
);
echo json_encode($fmdata);
exit();
