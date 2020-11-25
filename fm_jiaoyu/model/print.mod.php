<?php
/**
 * 微教育模块
 *QQ：332035136
 * @author 高贵血迹
 */
defined('IN_IA') or die('Access Denied');
load()->func('communication');

/**
 * 根据订单类型获取小票打印机数组
 *
 * @param int $ordertype
 * @param int $schoolid
 * @param int $orderid
 * @return void Array Or boolean
 */
function GetPrinterArrByOrderType($ordertype, $schoolid, $orderid)
{
	$printset = pdo_fetch("SELECT * FROM " . tablename('wx_school_printset') . " where ordertype = :ordertype And schoolid = :schoolid ", array(':ordertype' => $ordertype, ':schoolid' => $schoolid));
	$order = pdo_fetch("SELECT kcid,costid,signid FROM " . tablename('wx_school_order') . " WHERE id = :id ", array(':id' => $orderid));
	if ($ordertype == 1) {
		$kc = pdo_fetch("SELECT printarr FROM " . tablename('wx_school_tcourse') . " WHERE id = :id And is_print = :is_print ", array(':id' => $order['kcid'], ':is_print' => 1));
		$printset['printarr'] = $kc['printarr'];
	}
	if ($ordertype == 3) {
		$ob = pdo_fetch("SELECT printarr FROM " . tablename('wx_school_cost') . " WHERE id = :id And is_print = :is_print ", array(':id' => $order['costid'], ':is_print' => 1));
		$printset['printarr'] = $ob['printarr'];
	}
	if ($printset && $printset['status'] == 1) {
		return $printset;
	} else {
		return false;
	}
}

/**
 * 获取小票打印机设置
 *
 * @param int $id
 * @return void Array or Boolean
 */
function GetPrinterSet($id)
{
	$print = pdo_fetch("SELECT * FROM " . tablename('wx_school_printer') . " where id = :id And status = :status ", array(':id' => $id, ':status' => 1));
	if ($print) {
		return $print;
	} else {
		return false;
	}
}

/**
 * ordertype 转换文字
 *
 * @param int $ordertype
 * @return void
 */
function GetOrderType($ordertype)
{
	if ($ordertype == 1) {
		return '课程课时购买';
	}
	if ($ordertype == 3) {
		return '校园缴费项目';
	}
	if ($ordertype == 4) {
		return '报名缴费';
	}
	if ($ordertype == 5) {
		return '考勤卡费';
	}
	if ($ordertype == 6) {
		return '商城订单';
	}
	if ($ordertype == 7) {
		return '监控直播';
	}
	if ($ordertype == 8) {
		return '余额充值';
	}
}

/**
 * order pay type 转换文字
 *
 * @param int $paytype
 * @return void
 */
function GetOrderPayType($paytype)
{
	if ($paytype == 'wechat') {
		return '微信支付';
	}
	if ($paytype == 'alipay') {
		return '支付宝支付';
	}
	if ($paytype == 'baifubao') {
		return '百付宝支付';
	}
	if ($paytype == 'unionpay') {
		return '银联支付';
	}
	if ($paytype == 'cash') {
		return '现金支付';
	}
	if ($paytype == 'credit' || $paytype == 'chongzhi') {
		return '余额支付';
	}
	if ($paytype == 'wxapp') {
		return '小程序内支付';
	}
}

/**
 * 班牌消课小票打印
 * @author Hannibal·Lee
 * 
 * @param int $signid
 * @param int $schoolid
 * @param int $weid
 * @return void
 */
function KsCheck_print($signid, $schoolid, $weid)
{
	global $_W;
	$school = pdo_fetch("SELECT title FROM " . tablename('wx_school_index') . " where id = :id ", array(':id' => $schoolid));
	$sign = pdo_fetch("SELECT kcid,ksid,sid,type,createtime,id FROM " . GetTableName('kcsign') . " WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $weid, ':id' => $signid, ':schoolid' => $schoolid));
	$kcinfo = pdo_fetch("SELECT name,printarr FROM " . GetTableName('tcourse') . " WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $weid, ':id' => $sign['kcid'], ':schoolid' => $schoolid));
	$student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " where weid = :weid And schoolid = :schoolid And id = :id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $sign['sid']));
	if (empty($sign)) {
		return error(-1, '签到记录不存在');
	}
	if (empty($kcinfo['printarr'])) {
		return error(-1, '没有有效的打印机');
	}
	$num = 0;
	$printset = pdo_fetch("SELECT * FROM " . tablename('wx_school_printset') . " where ordertype = :ordertype And schoolid = :schoolid ", array(':ordertype' => 1, ':schoolid' => $schoolid));
	if (strpos($kcinfo['printarr'], ',')) { //如果不止一个，则转换
		$printarrs = explode(',', $kcinfo['printarr']);
		foreach ($printarrs as $row) {
			$printes = GetPrinterSet($row);
			if (!empty($printes['print_no'])) {
				$content = array('title' => "<CB>{$school['title']}</CB>");
				if (!empty($printset['print_header'])) {
					$content[] = $printset['print_header'];
				}
				$student = pdo_fetch("SELECT s_name,bj_id FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $sign['sid']));
				$content[] = '******************************';
				$rest = 0;   //剩余课时数
				$signtime = date("Y-m-d H:i:s",time());
				if ($sign['type'] == 1) { //自由课程
					$count_done = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('kcsign') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid And status=:status", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid'], ':status' => 2));
					$count_all = pdo_fetchcolumn("SELECT ksnum FROM " . GetTableName('coursebuy') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid ", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid']));
					$rest = $count_all - $count_done;
				} elseif ($sign['type'] == 0) { //固定课表
					$count_done = pdo_fetchcolumn("SELECT sum(costnum) FROM " . GetTableName('kcsign') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid And status=:status", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid'], ':status' => 2));
					$count_all = pdo_fetchcolumn("SELECT ksnum FROM " . GetTableName('coursebuy') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid ", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid']));
					$rest = $count_all - $count_done;
				}
				$content[] = "课程名称:{$kcinfo['name']}";
				$content[] = "签到学生:{$student['s_name']}";
				$content[] = "已签课时:{$count_done}节";
				$content[] = "剩余课时:{$rest}节";
				$content[] = "签到时间:{$signtime}";
				$content[] = '******************************';
				if (!empty($printset['print_footer'])) {
					$content[] = $printset['print_footer'];
				}
				if (!empty($printset['qrcode_link'])) {
					$content['qrcode'] = "<QR>{$printset['qrcode_link']}</QR>";
				}
				if (($printes['type'] == 'feiyin' || $printes['type'] == 'AiPrint') && $printset['print_nums'] > 0) {
					for ($i = 0; $i < $printset['print_nums']; $i++) {
						$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printset['print_nums'], $sign['id'] . random(10, true));
						if (!is_error($status)) {
							$num++;
							$data = array('weid' => $weid, 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => 0, 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
							pdo_insert('wx_school_print_log', $data);
						}
					}
				} else {
					$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printset['print_nums'], $sign['id'] . random(10, true));
					if (!is_error($status)) {
						$num++;
						$data = array('weid' => $weid, 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => 0, 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
						pdo_insert('wx_school_print_log', $data);
					}
				}
			}
		}
	} else { //如果只有一个设备，则不需要转换，直接取 
		$printes = GetPrinterSet($kcinfo['printarr']);
		if (!empty($printes['print_no'])) {
			$content = array('title' => "<CB>{$school['title']}</CB>");
			if (!empty($printset['print_header'])) {
				$content[] = $printset['print_header'];
			}
			$student = pdo_fetch("SELECT s_name,bj_id FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $sign['sid']));
			$content[] = '******************************';
			$rest = 0;
			$signtime = '';
			if ($sign['type'] == 1) {
				$count_done = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('kcsign') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid And status=:status", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid'], ':status' => 2));
				$count_all = pdo_fetchcolumn("SELECT ksnum FROM " . GetTableName('coursebuy') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid ", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid']));
				$rest = $count_all - $count_done;
				$signtime = date("Y-m-d H:i:s", $sign['createtime']);
			} elseif ($sign['type'] == 0) {
				$count_done = pdo_fetchcolumn("SELECT sum(costnum) FROM " . GetTableName('kcsign') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid And status=:status", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid'], ':status' => 2));
				$count_all = pdo_fetchcolumn("SELECT ksnum FROM " . GetTableName('coursebuy') . " WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid ", array(':weid' => $weid, ':sid' => $sign['sid'], ':schoolid' => $schoolid, ':kcid' => $sign['kcid']));
				$rest = $count_all - $count_done;
				$signtime = date("Y-m-d H:i:s", $sign['createtime']);
			}
			$signtime = date("Y-m-d H:i:s",time());

			$content[] = "课程名称:{$kcinfo['name']}";
			$content[] = "签到学生:{$student['s_name']}";
			$content[] = "已签课时:{$count_done}节";
			$content[] = "剩余课时:{$rest}节";
			$content[] = "签到时间:{$signtime}";
			$content[] = '******************************';
			if (!empty($printset['print_footer'])) {
				$content[] = $printset['print_footer'];
			}
			if (!empty($printset['qrcode_link'])) {
				$content['qrcode'] = "<QR>{$printset['qrcode_link']}</QR>";
			}
			if (($printes['type'] == 'feiyin' || $printes['type'] == 'AiPrint') && $printset['print_nums'] > 0) {
				for ($i = 0; $i < $printset['print_nums']; $i++) {
					$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printset['print_nums'], $sign['id'] . random(10, true));
					if (!is_error($status)) {
						$num++;
						$data = array('weid' => $weid, 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => 0, 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
						pdo_insert('wx_school_print_log', $data);
					}
				}
			} else {
				$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printset['print_nums'], $sign['id'] . random(10, true));
				if (!is_error($status)) {
					$num++;
					$data = array('weid' => $weid, 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => 0, 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
					pdo_insert('wx_school_print_log', $data);
				}
			}
		}
	}
	if ($num = 0) {
		return error(-1, '发送打印指令失败。没有有效的打印机或没有开启打印机');
	}
	return true;
}

function order_print($id)
{
	global $_W;
	$order = pdo_fetch("SELECT * FROM " . tablename('wx_school_order') . " WHERE id = :id ", array(':id' => $id));
	if (empty($order)) {
		return error(-1, '订单不存在');
	}
	$schoolid = intval($order['schoolid']);
	$ordertype = $order['type'];
	$school = pdo_fetch("SELECT title,videoname FROM " . tablename('wx_school_index') . " where id = :id ", array(':id' => $schoolid));
	$printrule = GetPrinterArrByOrderType($ordertype, $schoolid, $id);
	if (empty($printrule['printarr'])) {
		return error(-1, '没有有效的打印机');
	}
	$num = 0;
	if (strpos($printrule['printarr'], ',')) {
		$printarrs = explode(',', $printrule['printarr']);
		foreach ($printarrs as $row) {
			$printes = GetPrinterSet($row);
			if (!empty($printes['print_no'])) {
				$content = array('title' => "<CB>{$school['title']}</CB>");
				if (!empty($printrule['print_header'])) {
					$content[] = $printrule['print_header'];
				}
				$studetn = pdo_fetch("SELECT s_name,bj_id,mobile FROM " . tablename('wx_school_students') . " WHERE id = :id ", array(':id' => $order['sid']));
				$teacher = pdo_fetch("SELECT tname FROM " . tablename('wx_school_teachers') . " WHERE id = :id ", array(':id' => $order['tid']));
				$content[] = '******************************';
				if ($ordertype == 7) {
					$ordertypename = empty($school['videoname']) ? $school['videoname'] : '直播监控';
					$content[] = "订单类型:{$ordertypename}";
				} else {
					$ordertypename = GetOrderType($ordertype);
					$content[] = "订单类型:{$ordertypename}";
				}
				$content[] = "订单　号:{$id}";
				$paytype = GetOrderPayType($order['pay_type']);
				$content[] = "支付方式:{$paytype}";
				if ($ordertype == 1) {
					$kc = pdo_fetch("SELECT name FROM " . tablename('wx_school_tcourse') . " WHERE id = :id ", array(':id' => $order['kcid']));
					$content[] = "课    程:{$kc['name']}";
					if ($order['xufeitype'] == 1) {
						$content[] = "购买课时:{$order['ksnum']}节";
					}
					if ($order['xufeitype'] == 2) {
						if ($order['ksnum'] != 0) {
							$content[] = "包含课时:{$order['ksnum']}节";
						}
					}
				}
				if ($ordertype == 3) {
					$ob = pdo_fetch("SELECT name FROM " . tablename('wx_school_cost') . " WHERE id = :id ", array(':id' => $order['costid']));
					$content[] = "项    目:{$ob['name']}";
				}
				if ($ordertype == 4) {
					$signup = pdo_fetch("SELECT name,bj_id,nj_id FROM " . tablename('wx_school_signup') . " WHERE id = :id ", array(':id' => $order['signid']));
					$signbj = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $signup['bj_id']));
					$signnj = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $signup['nj_id']));
					$content[] = "报名  人:{$signup['name']}";
					$content[] = "报名年级:{$signnj['sname']}";
					$content[] = "报名班级:{$signbj['sname']}";
				}
				if ($ordertype == 5) {
					$card = pdo_fetch("SELECT idcard,pard,pname,severend FROM " . tablename('wx_school_idcard') . " WHERE :id = id", array(':id' => $order['bdcardid']));
					$guanxi = getpardforkqj($card['pard']);
					$content[] = "学    生:{$studetn['s_name']}";
					$content[] = "卡    号:{$card['idcard']}";
					$content[] = "绑卡关系:{$guanxi}";
					$content[] = "持卡  人:{$card['pname']}";
					$content[] = "到期时间:" . date('Y-m-d H:i', $card['severend']);
				}
				if ($ordertype == 6) {
					$morder = pdo_fetch("SELECT goodsid,tname,tphone,addressid,count,sid,tid,taddress FROM " . tablename('wx_school_mallorder') . " WHERE :id = id", array(':id' => $order['morderid']));
					$good = pdo_fetch("SELECT title,new_price,old_price FROM " . tablename('wx_school_mall') . " WHERE :id = id", array(':id' => $morder['goodsid']));
					$content[] = "商    品:{$good['title']}";
					$content[] = "单    价:{$good['new_price']}元";
					$content[] = "数    量:{$morder['count']}";
					if ($morder['sid'] != 0) {
						$paystu = pdo_fetch("SELECT s_name,bj_id FROM " . tablename('wx_school_students') . " WHERE id = :id ", array(':id' => $morder['sid']));
						$paybj = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $paystu['bj_id']));
						$content[] = "学    生:{$paystu['s_name']}";
						$content[] = "班    级:{$paybj['sname']}";
					}
					if ($morder['tid'] != 0) {
						$paytea = pdo_fetch("SELECT tname FROM " . tablename('wx_school_teachers') . " WHERE id = :id ", array(':id' => $morder['tid']));
						$content[] = "老    师:{$paytea['tname']}";
					}
					$content[] = "下单  人:{$morder['tname']}";
					$content[] = "下单电话:{$morder['tphone']}";
					$content[] = "**********收货信息************";
					if ($morder['taddress'] == '到校自取') {
						$content[] = "到校自取";
					} else {
						$address = pdo_fetch("SELECT name,phone FROM " . tablename('wx_school_address') . " WHERE id = :id ", array(':id' => $morder['addressid']));
						$content[] = "下单  人:{$address['name']}";
						$content[] = "下单电话:{$address['phone']}";
					}
				}
				if ($ordertype == 7) {
					$vod = pdo_fetch("SELECT name FROM " . tablename('wx_school_allcamera') . " WHERE id = :id ", array(':id' => $order['vodid']));
					$content[] = "学    生:{$studetn['s_name']}";
					$content[] = "{$ordertypename}:{$vod['name']}";
					$content[] = "单    价:{$order['cose']}元";
					if ($order['vodtype'] == 'one') {
						$content[] = "购买类型:单帐号观看";
					} else {
						$content[] = "购买类型:全家共享";
					}
				}
				if ($ordertype == 8) {
					$taocan = pdo_fetch("SELECT chongzhi,cost FROM " . tablename('wx_school_chongzhi') . " WHERE id = :id ", array(':id' => $order['taocanid']));
					if ($order['sid'] != 0) {
						$content[] = "学    生:{$studetn['s_name']}";
					}
					if ($order['tid'] != 0) {
						$content[] = "老    师:{$teacher['tname']}";
					}
					$content[] = $taocan['chongzhi'];
					$content[] = "单    价:{$taocan['cost']}元";
				}
				$content[] = "合　　计:{$order['cose']}元";
				$content[] = '**********支付者信息**********';
				if($order['userid']){
					$user = pdo_fetch("SELECT mobile,realname,pard,tid,sid FROM " . tablename('wx_school_user') . " WHERE id = :id ", array(':id' => $order['userid']));
					$classinfo = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $studetn['bj_id']));
					if ($user['sid'] != 0) {
						$nguanxi = get_guanxi($user['pard']);
						$content[] = "学    生:{$studetn['s_name']}";
						if ($ordertype != 1) {
							$content[] = "关    系:{$nguanxi}";
						}
						$content[] = "姓    名:{$user['realname']}";
						$content[] = "电    话:{$user['mobile']}";
					}else{
						if ($order['sid'] != 0) {//防止解绑后查不到信息
							$content[] = "学    生:{$studetn['s_name']}";
							if($classinfo && !$_W['schooltype']){
								$content[] = "班    级:{$classinfo['sname']}";
							}
							$content[] = "电    话:{$studetn['mobile']}";
						}
					}
					if ($user['tid'] != 0) {
						$content[] = "老    师:{$teacher['tname']}";
					}
				}else{
					if ($order['sid'] != 0) {
						$classinfo = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $studetn['bj_id']));
						$content[] = "学    生:{$studetn['s_name']}";
						if($classinfo && !$_W['schooltype']){
							$content[] = "班    级:{$classinfo['sname']}";
						}
						$content[] = "电    话:{$studetn['mobile']}";
					}
					if ($user['tid'] != 0) {
						$content[] = "老    师:{$teacher['tname']}";
					}					
				}
				$content[] = "下单时间:" . date('Y-m-d H:i', $order['createtime']);
				$content[] = "支付时间:" . date('Y-m-d H:i', $order['paytime']);
				$content[] = '******************************';
				if (!empty($printrule['print_footer'])) {
					$content[] = $printrule['print_footer'];
				}
				if (!empty($printrule['qrcode_link'])) {
					$content['qrcode'] = "<QR>{$printrule['qrcode_link']}</QR>";
				}
				if (($printes['type'] == 'feiyin' || $printes['type'] == 'AiPrint') && $printrule['print_nums'] > 0) {
					for ($i = 0; $i < $printrule['print_nums']; $i++) {
						$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printrule['print_nums'], $order['id'] . random(10, true));
						if (!is_error($status)) {
							$num++;
							$data = array('weid' => $order['weid'], 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => $order['id'], 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
							pdo_insert('wx_school_print_log', $data);
						}
					}
				} else {
					$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printrule['print_nums'], $order['id'] . random(10, true));
					if (!is_error($status)) {
						$num++;
						$data = array('weid' => $order['weid'], 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => $order['id'], 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
						pdo_insert('wx_school_print_log', $data);
					}
				}
			}
		}
	} else {
		$printes = GetPrinterSet($printrule['printarr']);
		if (!empty($printes['print_no'])) {
			$content = array('title' => "<CB>{$school['title']}</CB>");
			if (!empty($printrule['print_header'])) {
				$content[] = $printrule['print_header'];
			}
			$studetn = pdo_fetch("SELECT s_name,bj_id,mobile FROM " . tablename('wx_school_students') . " WHERE id = :id ", array(':id' => $order['sid']));
			$teacher = pdo_fetch("SELECT tname FROM " . tablename('wx_school_teachers') . " WHERE id = :id ", array(':id' => $order['tid']));
			$content[] = '******************************';
			if ($ordertype == 7) {
				$ordertypename = empty($school['videoname']) ? $school['videoname'] : '直播监控';
				$content[] = "订单类型:{$ordertypename}";
			} else {
				$ordertypename = GetOrderType($ordertype);
				$content[] = "订单类型:{$ordertypename}";
			}
			$content[] = "订单　号:{$id}";
			$paytype = GetOrderPayType($order['pay_type']);
			$content[] = "支付方式:{$paytype}";
			if ($ordertype == 1) {
				$kc = pdo_fetch("SELECT name FROM " . tablename('wx_school_tcourse') . " WHERE id = :id ", array(':id' => $order['kcid']));
				$content[] = "课    程:{$kc['name']}";
				if ($order['xufeitype'] == 1) {
					$content[] = "购买课时:{$order['ksnum']}节";
				}
				if ($order['xufeitype'] == 2) {
					if ($order['ksnum'] != 0) {
						$content[] = "包含课时:{$order['ksnum']}节";
					}
				}
			}
			if ($ordertype == 3) {
				$ob = pdo_fetch("SELECT name FROM " . tablename('wx_school_cost') . " WHERE id = :id ", array(':id' => $order['costid']));
				$content[] = "项    目:{$ob['name']}";
			}
			if ($ordertype == 4) {
				$signup = pdo_fetch("SELECT name,bj_id,nj_id FROM " . tablename('wx_school_signup') . " WHERE id = :id ", array(':id' => $order['signid']));
				$signbj = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $signup['bj_id']));
				$signnj = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $signup['nj_id']));
				$content[] = "报名  人:{$signup['name']}";
				$content[] = "报名年级:{$signnj['sname']}";
				$content[] = "报名班级:{$signbj['sname']}";
			}
			if ($ordertype == 5) {
				$card = pdo_fetch("SELECT idcard,pard,pname,severend FROM " . tablename('wx_school_idcard') . " WHERE :id = id", array(':id' => $order['bdcardid']));
				$guanxi = getpardforkqj($card['pard']);
				$content[] = "学    生:{$studetn['s_name']}";
				$content[] = "卡    号:{$card['idcard']}";
				$content[] = "绑卡关系:{$guanxi}";
				$content[] = "持卡  人:{$card['pname']}";
				$content[] = "到期时间:" . date('Y-m-d H:i', $card['severend']);
			}
			if ($ordertype == 6) {
				$morder = pdo_fetch("SELECT goodsid,tname,tphone,addressid,count,sid,tid,taddress FROM " . tablename('wx_school_mallorder') . " WHERE :id = id", array(':id' => $order['morderid']));
				$good = pdo_fetch("SELECT title,new_price,old_price FROM " . tablename('wx_school_mall') . " WHERE :id = id", array(':id' => $morder['goodsid']));
				$content[] = "商    品:{$good['title']}";
				$content[] = "单    价:{$good['new_price']}元";
				$content[] = "数    量:{$morder['count']}";
				if ($morder['sid'] != 0) {
					$paystu = pdo_fetch("SELECT s_name,bj_id FROM " . tablename('wx_school_students') . " WHERE id = :id ", array(':id' => $morder['sid']));
					$paybj = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $paystu['bj_id']));
					$content[] = "学    生:{$paystu['s_name']}";
					$content[] = "班    级:{$paybj['sname']}";
				}
				if ($morder['tid'] != 0) {
					$paytea = pdo_fetch("SELECT tname FROM " . tablename('wx_school_teachers') . " WHERE id = :id ", array(':id' => $morder['tid']));
					$content[] = "老    师:{$paytea['tname']}";
				}
				$content[] = "下单  人:{$morder['tname']}";
				$content[] = "下单电话:{$morder['tphone']}";
				$content[] = "**********收货信息************";
				if ($morder['taddress'] == '到校自取') {
					$content[] = "到校自取";
				} else {
					$address = pdo_fetch("SELECT name,phone FROM " . tablename('wx_school_address') . " WHERE id = :id ", array(':id' => $morder['addressid']));
					$content[] = "下单  人:{$address['name']}";
					$content[] = "下单电话:{$address['phone']}";
				}
			}
			if ($ordertype == 7) {
				$vod = pdo_fetch("SELECT name FROM " . tablename('wx_school_allcamera') . " WHERE id = :id ", array(':id' => $order['vodid']));
				$content[] = "学    生:{$studetn['s_name']}";
				$content[] = "{$ordertypename}:{$vod['name']}";
				$content[] = "单    价:{$order['cose']}元";
				if ($order['vodtype'] == 'one') {
					$content[] = "购买类型:单帐号观看";
				} else {
					$content[] = "购买类型:全家共享";
				}
			}
			if ($ordertype == 8) {
				$taocan = pdo_fetch("SELECT chongzhi,cost FROM " . tablename('wx_school_chongzhi') . " WHERE id = :id ", array(':id' => $order['taocanid']));
				if ($order['sid'] != 0) {
					$content[] = "学    生:{$studetn['s_name']}";
				}
				if ($order['tid'] != 0) {
					$content[] = "老    师:{$teacher['tname']}";
				}
				$content[] = $taocan['chongzhi'];
				$content[] = "单    价:{$taocan['cost']}元";
			}
			$content[] = "合　　计:{$order['cose']}元";
			$content[] = '**********支付者信息**********';
			if($order['userid']){
				$user = pdo_fetch("SELECT realname,mobile,pard,tid,sid FROM " . tablename('wx_school_user') . " WHERE id = :id ", array(':id' => $order['userid']));
				$classinfo = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $studetn['bj_id']));
				if ($user['sid'] != 0) {
					$nguanxi = get_guanxi($user['pard']);
					$content[] = "学    生:{$studetn['s_name']}";
					if ($ordertype != 1) {
						$content[] = "班    级:{$classinfo['sname']}";
						$content[] = "关    系:{$nguanxi}";
					}
					$content[] = "姓    名:{$user['realname']}";
					$content[] = "电    话:{$user['mobile']}";
				}else{
					if ($order['sid'] != 0) {//防止解绑后查不到信息
						$content[] = "学    生:{$studetn['s_name']}";
						if($classinfo && !$_W['schooltype']){
							$content[] = "班    级:{$classinfo['sname']}";
						}
						$content[] = "电    话:{$studetn['mobile']}";
					}
				}
				if ($user['tid'] != 0) {
					$content[] = "老    师:{$teacher['tname']}";
				}
			}else{
				if ($order['sid'] != 0) {
					$classinfo = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " WHERE sid = :sid ", array(':sid' => $studetn['bj_id']));
					$content[] = "学    生:{$studetn['s_name']}";
					if($classinfo && !$_W['schooltype']){
						$content[] = "班    级:{$classinfo['sname']}";
					}
					$content[] = "电    话:{$studetn['mobile']}";
				}
				if ($user['tid'] != 0) {
					$content[] = "老    师:{$teacher['tname']}";
				}					
			}
			$content[] = "下单时间:" . date('Y-m-d H:i', $order['createtime']);
			$content[] = "支付时间:" . date('Y-m-d H:i', $order['paytime']);
			$content[] = '******************************';
			if (!empty($printrule['print_footer'])) {
				$content[] = $printrule['print_footer'];
			}
			if (!empty($printrule['qrcode_link'])) {
				$content['qrcode'] = "<QR>{$printrule['qrcode_link']}</QR>";
			}
			if (($printes['type'] == 'feiyin' || $printes['type'] == 'AiPrint') && $printrule['print_nums'] > 0) {
				for ($i = 0; $i < $printrule['print_nums']; $i++) {
					$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printrule['print_nums'], $order['id'] . random(10, true));
					if (!is_error($status)) {
						$num++;
						$data = array('weid' => $order['weid'], 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => $order['id'], 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
						pdo_insert('wx_school_print_log', $data);
					}
				}
			} else {
				$status = print_add_order($printes['type'], $printes['print_no'], $printes['key'], $printes['member_code'], $printes['api_key'], $content, $printrule['print_nums'], $order['id'] . random(10, true));
				if (!is_error($status)) {
					$num++;
					$data = array('weid' => $order['weid'], 'schoolid' => $schoolid, 'pid' => $printes['id'], 'oid' => $order['id'], 'status' => 2, 'foid' => $status, 'printer_type' => $printes['type'], 'createtime' => TIMESTAMP);
					pdo_insert('wx_school_print_log', $data);
				}
			}
		}
	}
	if ($num > 0) {
		pdo_query('UPDATE ' . tablename('wx_school_order') . " SET print_nums = print_nums + {$num} WHERE schoolid = {$schoolid} AND id = {$order['id']}");
	} else {
		return error(-1, '发送打印指令失败。没有有效的打印机或没有开启打印机');
	}
	return true;
}
/**
 * Undocumented function
 *
 * @param string $printer_type   打印机类型
 * @param [type] $deviceno 设备no
 * @param [type] $key   设备Key
 * @param [type] $member_code  商户编号
 * @param [type] $api_key  ？？？
 * @param [type] $content  打印内容
 * @param integer $times  次数
 * @param integer $orderindex
 * @return void
 */
function print_add_order($printer_type, $deviceno, $key, $member_code, $api_key, $content, $times = 1, $orderindex = 0)
{
	if ($printer_type == 'feie') {
		$postdata = array('sn' => $deviceno, 'key' => $key, 'printContent' => implode('<BR>', $content), 'times' => $times);
		$posturl = 'http://api163.feieyun.com/FeieServer/printOrderAction';
	} else if ($printer_type == 'feiyin') {
		$qrcode = str_replace(array('<QR>', '</QR>'), array('', ''), $content['qrcode']);
		$content['title'] = str_replace(array('<CB>', '</CB>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['title']);
		$content['pay'] = str_replace(array('<CB>', '</CB>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['pay']);
		$content['store'] = str_replace(array('<C>', '</C>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['store']);
		$content['print_header'] = str_replace(array('<C>', '</C>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['print_header']);
		$content['username'] = str_replace(array('<L>', '</L>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['username']);
		$content['mobile'] = str_replace(array('<L>', '</L>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['mobile']);
		$content['address'] = str_replace(array('<L>', '</L>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['address']);
		$content['note'] = str_replace(array('<L>', '</L>'), array('<Font# Bold=0 Width=2 Height=2>', '</Font#>'), $content['note']);

		if ($content['final_fee']) {
			$content['final_fee'] = '<Font# Bold=0 Width=2 Height=2>' . $content['final_fee'] . '</Font#>';
		} else {
			$content['total_fee'] = '<Font# Bold=0 Width=2 Height=2>' . $content['total_fee'] . '</Font#>';
		}

		$content = implode("\n", $content);
		$content = str_replace(array('<CB>', '</CB>'), array('', ''), $content);
		$content = str_replace(array('<C>', '</C>'), array('', ''), $content);
		$content = str_replace(array('<L>', '</L>'), array('<Font# Bold=1 Width=1 Height=1>', '</Font#>'), $content);
		$postdata = array('memberCode' => $member_code, 'deviceNo' => $deviceno, 'reqTime' => number_format(1000 * time(), 0, '', ''), 'msgDetail' => $content, 'mode' => 2, 'msgNo' => $orderindex);
		$securityCode = $member_code . $content . $deviceno . $orderindex . $postdata['reqTime'] . $key;
		$postdata['securityCode'] = md5($securityCode);
		$posturl = 'http://my.feyin.net:80/api/sendMsg';
	} else if ($printer_type == 'AiPrint') {
		unset($content['qrcode']);
		$content = implode("\n", $content);
		$content = str_replace(array('<CB>', '</CB>'), array('', ''), $content);
		$content = str_replace(array('<C>', '</C>'), array('', ''), $content);
		$content = str_replace(array('<L>', '</L>'), array('', ''), $content);
		$postdata = array('memberCode' => $member_code, 'deviceNo' => $deviceno, 'reqTime' => number_format(1000 * time(), 0, '', ''), 'msgDetail' => $content, 'mode' => 2, 'msgNo' => $orderindex);
		$securityCode = $member_code . $content . $deviceno . $orderindex . $postdata['reqTime'] . $key;
		$postdata['securityCode'] = md5($securityCode);
		$posturl = 'http://iprint.ieeoo.com/porderPrint';
	} else if ($printer_type == '365') {
		if (substr($deviceno, 0, 4) == 'kdt1') {
			$qrcode = str_replace(array('<QR>', '</QR>'), array('', ''), $content['qrcode']);
			$qrlength = chr(strlen($qrcode));
			$content['qrcode'] = '^Q' . $qrlength . $qrcode;
			array_unshift($content, '^N' . $times . '^F1');
			$content = str_replace(array('<CB>', '</CB>'), array('^H2', ''), $content);
			$content = str_replace(array('<C>', '</C>'), array('^H3', ''), $content);
		}


		$content = implode("\n", $content);
		$content = str_replace(array('<L>', '</L>'), array('', ''), $content);
		$postdata = array('deviceNo' => $deviceno, 'key' => $key, 'printContent' => $content, 'times' => $times);
		$posturl = 'http://open.printcenter.cn:8080/addOrder';
	} else if ($printer_type == 'yilianyun') {
		array_unshift($content, '**' . $times);
		$content['title'] = str_replace(array('<CB>', '</CB>'), array('<center>', '</center>'), $content['title']);
		$content['store'] = str_replace(array('<C>', '</C>'), array('<center>', '</center>'), $content['store']);
		$content = implode("\n", $content);
		$content = str_replace(array('<QR>', '</QR>'), array('<q>', '</q>'), $content);
		$content = str_replace(array('<L>', '</L>'), array('', ''), $content);
		$time = time();
		$sign = strtoupper(md5($api_key . 'machine_code' . $deviceno . 'partner' . $member_code . 'time' . $time . $key));
		$postdata = array('partner' => $member_code, 'machine_code' => $deviceno, 'sign' => $sign, 'content' => $content, 'time' => $time);
		$postdata = http_build_query($postdata);
		$posturl = 'http://open.10ss.net:8888';
	} else if ($printer_type == 'qiyun') {
		$content = str_replace(array('<C>', '</C>'), array('', ''), $content);
		$content = str_replace(array('<CB>', '</CB>'), array('', ''), $content);
		$content = str_replace(array('<L>', '</L>'), array('', ''), $content);
		$content = str_replace(array('<N>', '</N>'), array('', ''), $content);
		$content = implode("\r\n", $content) . "\r\n\r\n\r\n\r\n\r\n";
		$time = time();
		$sign = strtoupper(md5($api_key . 'machine_code' . $deviceno . 'partner' . $member_code . 'time' . $time . $key));
		$postdata = array('partner' => $member_code, 'machine_code' => $deviceno, 'sign' => $sign, 'content' => $content, 'time' => $time);
		$postdata = http_build_query($postdata);
		$posturl = 'http://openapi.qiyunkuailian.com';
	} else if ($printer_type == 'xixun') {
		$content = str_replace(array('<CB>', '</CB>'), array('', ''), $content);
		$content = str_replace(array('<C>', '</C>'), array(' ', ' '), $content);
		$content = str_replace(array('<L>', '</L>'), array('', ''), $content);
		$content = str_replace(array('<QR>', '</QR>'), array('', ''), $content);
		$content['title'] = '<1D2101><1B6101>' . $content['title'];
		$content['store'] = '<1D2100><1B6101>' . $content['store'];

		if (!(empty($content['print_header']))) {
			$content['print_header'] = '<1D2100><1B6101>' . $content['print_header'];
		}


		$content['pay'] = '<1D2101><1B6101>' . $content['pay'];

		if (!(empty($content['note']))) {
			$content['note'] = '<1D2101><1B6100>' . $content['note'];
		}


		$content['username'] = '<1D2110><1B6100>' . $content['username'];
		$content['mobile'] = '<1D2110><1B6100>' . $content['mobile'];
		$content['address'] = '<1D2110><1B6100>' . $content['address'];

		if (!(empty($content['print_footer']))) {
			$content['print_footer'] = '<1D2100><1B6101>' . $content['print_footer'];
		}


		if (!(empty($content['qrcode']))) {
			$content['qrcode'] = '<1B2A>' . $content['qrcode'] . '<1B2A>';
			$content['end'] = '<1B2AD><1D2110><1B6101>' . $content['end'] . '<0D0A><0D0A><0D0A><0D0A><0D0A><0D0A><1D5642000A0A><1B2AD>';
		} else {
			$content['end'] = '<1D2110><1B6101>' . $content['end'] . '<0D0A><0D0A><0D0A><0D0A><0D0A><0D0A><1D5642000A0A>';
		}

		foreach ($content as $key => &$v) {
			if (strexists($key, 'goods_item_') || in_array($key, array('title', 'store', 'print_header', 'pay', 'username', 'mobile', 'address', 'print_footer', 'note', 'end', 'qrcode'))) {
				continue;
			}


			$v = '<1D2100><1B6100>' . $v;
		}

		$content = implode('<0D0A>', $content);
		$content = '<1B40><1B40><1B40>' . $content;
		$posturl = 'http://115.28.15.113:61111';
		$postdata = array('dingdan' => $content, 'dingdanID' => substr($orderindex, 0, 18), 'dayinjisn' => $deviceno, 'pages' => 1, 'replyURL' => '', 'indexID' => 10000, 'dayinflag' => 1);
	}


	if (($printer_type == 'feiyin') || ($printer_type == 'AiPrint')) {
		$response = ihttp_post($posturl, $postdata);

		if (is_error($response)) {
			return error(-1, '错误: ' . $response['message']);
		}


		$result['responseCode'] = intval($response['content']);
		$result['orderindex'] = $orderindex;

		if ($result['responseCode'] == 0) {
			return $result['orderindex'];
		}


		$errors = print_code_msg();
		return error(-1, $errors[$printer_type]['printorder'][$result['responseCode']]);
	}


	if ($printer_type == 'xixun') {
		$postdata = http_build_query($postdata);
		$url = 'http://115.28.15.113:61111?' . $postdata;
		$response = ihttp_get($url);

		if ($response['content'] != 'OK') {
			return error(-1, $response['content']);
		}


		return $response['content'];
	}


	$response = ihttp_post($posturl, $postdata);

	if (is_error($response)) {
		return error(-1, '错误: ' . $response['message']);
	}


	if (in_array($printer_type, array('feie', '365'))) {
		$result = @json_decode($response['content'], true);
	} else if ($printer_type == 'qiyun') {
		$result = @json_decode($response['content'], true);

		if ($result['code'] == 200) {
			$result['responseCode'] = 0;
		} else {
			$result['responseCode'] = $result['code'];
			$result['responseMsg'] = $result['msg'];
		}
	} else if ($printer_type == 'yilianyun') {
		$result = @json_decode($response['content'], true);

		if ($result['state'] == 1) {
			$result['responseCode'] = 0;
			$result['orderindex'] = $result['id'];
		} else {
			$result['responseCode'] = $result['state'];
		}
	} else {
		$result['responseCode'] = intval($response['content']);
		$result['orderindex'] = $orderindex;
	}

	if (($result['responseCode'] == 0) || (($printer_type == '365') && ($result['responseCode'] == 1))) {
		return $result['orderindex'];
	}


	if (!(empty($result['responseMsg']))) {
		return error(-1, $result['responseMsg']);
	}


	$errors = print_code_msg();
	return error(-1, $errors[$printer_type]['printorder'][$result['responseCode']]);
}

function print_query_order_status($printer_type, $deviceno, $key, $member_code, $orderindex)
{
	if ($printer_type == 'feie') {
		$postdata = array('sn' => $deviceno, 'key' => $key, 'index' => $orderindex);
		$http = print_feie_url($deviceno);
		$posturl = 'http://api163.feieyun.com/FeieServer/queryOrderStateAction';
		$response = ihttp_post($posturl, $postdata);
	} else if ($printer_type == 'feiyin') {
		$postdata = array('memberCode' => $member_code, 'key' => $key, 'msgNo' => $orderindex, 'reqTime' => number_format(1000 * time(), 0, '', ''));
		$securityCode = $member_code . $postdata['reqTime'] . $key . $orderindex;
		$postdata['securityCode'] = md5($securityCode);
		$posturl = 'http://my.feyin.net/api/queryState?' . http_build_query($postdata);
		$response = ihttp_get($posturl);
	} else if ($printer_type == 'AiPrint') {
		$postdata = array('memberCode' => $member_code, 'msgNo' => $orderindex, 'reqTime' => number_format(1000 * time(), 0, '', ''));
		$securityCode = $member_code . $postdata['reqTime'] . $key . $orderindex;
		$postdata['securityCode'] = md5($securityCode);
		$posturl = 'http://iprint.ieeoo.com/porderqueryState?' . http_build_query($postdata);
		$response = ihttp_get($posturl);
	} else if ($printer_type == '365') {
		$postdata = array('deviceNo' => $deviceno, 'key' => $key, 'orderindex' => $orderindex);
		$posturl = 'http://open.printcenter.cn:8080/queryOrder';
		$response = ihttp_post($posturl, $postdata);
	}


	if (is_error($response)) {
		return error(-1, '错误: ' . $response['message']);
	}


	if (in_array($printer_type, array('feie', '365'))) {
		$result = json_decode($response['content'], true);
	} else {
		$result['responseCode'] = intval($response['content']);
	}

	$status = 2;

	if (in_array($printer_type, array('feie', '365'))) {
		if ($result['responseCode'] == 0) {
			if ($printer_type == 'feie') {
				$status = (($result['msg'] == '已打印' ? 1 : 2));
			} else {
				$status = 1;
			}
		}
	} else if ($result['responseCode'] == 1) {
		$status = 1;
	}


	return $status;
}

function print_query_printer_status($printer_type, $deviceno, $key, $member_code)
{
	if ($printer_type == 'feie') {
		$postdata = array('sn' => $deviceno, 'key' => $key);
		$http = print_feie_url($deviceno);
		$posturl = 'http://api163.feieyun.com/FeieServer/queryPrinterStatusAction';
		$response = ihttp_post($posturl, $postdata);
	} else if ($printer_type == 'feiyin') {
		$postdata = array('memberCode' => $member_code, 'reqTime' => number_format(1000 * time(), 0, '', ''));
		$securityCode = $member_code . $postdata['reqTime'] . $key;
		$postdata['securityCode'] = md5($securityCode);
		$posturl = 'http://my.feyin.net/api/listDevice?' . http_build_query($postdata);
		$response = ihttp_get($posturl);
	} else if ($printer_type == '365') {
		$postdata = array('deviceNo' => $deviceno, 'key' => $key);
		$posturl = 'http://open.printcenter.cn:8080/queryPrinterStatus';
		$response = ihttp_post($posturl, $postdata);
	}


	if (is_error($response)) {
		return error(-1, '错误: ' . $response['message']);
	}


	if (in_array($printer_type, array('feie', '365'))) {
		$result = @json_decode($response['content'], true);
	} else {
		$result = intval($response['content']);
		if (is_numeric($result) && ($result < 0)) {
			$errors = print_code_msg();
			return $errors[$printer_type]['qureystate'][$result];
		}


		$result = isimplexml_load_string($response['content']);
		$result = json_decode(json_encode($result), true);
		return $result['device']['deviceStatus'] . ',纸张状态:' . $result['device']['paperStatus'];
	}

	$errors = print_code_msg();
	if (($printer_type == 'feiyin') || ($printer_type == '365')) {
		return $errors[$printer_type]['qureystate'][$result['responseCode']];
	}


	return $result['msg'];
}

function print_code_msg()
{
	$data = array(
		'feie'      => array(
			'printorder' => array('服务器接收订单成功', '打印机编号错误', '服务器处理订单失败', '打印内容太长', '请求参数错误'),
			'qureyorder' => array('已打印/未打印', '请求参数错误', '服务器处理订单失败', '没有找到该索引的订单'),
			'qureystate' => array()
		),
		'feiyin'    => array(
			'printorder' => array(0 => '正常', -1 => 'IP地址不允许', -2 => '关键参数为空或请求方式不对', -3 => '客户编码不对', -4 => '安全校验码不正确', -5 => '请求时间失效', -6 => '订单内容格式不对', -7 => '重复的消息 （ msgNo 的值重复）', -8 => '消息模式不对', -9 => '服务器错误', -10 => '服务器内部错误', -111 => '打印终端不属于该账户'),
			'qureyorder' => array(0 => '打印请求/任务中队列中，等待打印', 1 => '打印任务已完成/请求数据已打印', 2 => '打印任务/请求失败', 9 => '打印任务/请求已发送', -1 => 'IP地址不允许', -2 => '关键参数为空或请求方式不对', -3 => '客户编码不正确', -4 => '安全校验码不正确', -5 => '请求时间失效。请求时间和请求到达飞印API的时间长超出安全范围。', -6 => '订单编号错误或者不存在'),
			'qureystate' => array(-1 => 'IP地址不允许', -2 => '关键参数为空或请求方式不对', -3 => '客户编码不正确', -4 => '安全校验码不正确', -5 => ' 同步应用服务器时间 了解更多飞印API的时间安全设置。')
		),
		365         => array(
			'printorder' => array(0 => '正常', 2 => '订单添加成功，但是打印机缺纸，无法打印', 3 => '订单添加成功，但是打印机不在线', 10 => '内部服务器错误', 11 => '参数不正确', 12 => '打印机未添加到服务器', 13 => '未添加为订单服务器', 14 => '订单服务器和打印机不在同一个组', 15 => '订单已经存在，不能再次打印'),
			'qureyorder' => array(0 => '打印成功', 1 => '正在打印中', 2 => '打印机缺纸', 3 => '打印机下线', 16 => '订单不存在'),
			'qureystate' => array(1 => '打印机正常在线', 2 => '打印机缺纸', 3 => '打印机下线')
		),
		'yilianyun' => array(
			'printorder' => array(1 => '数据提交成功', 2 => '提交时间超时。验证你所提交的时间戳超过3分钟后拒绝接受', 3 => '参数有误', 4 => 'sign加密验证失败'),
			'qureyorder' => array('已打印/未打印', '请求参数错误', '服务器处理订单失败', '没有找到该索引的订单'),
			'qureystate' => array()
		)
	);
	return $data;
}
function print_printer_types()
{
	return array('feie' => array('text' => '飞鹅打印机', 'css' => 'label label-success'), 'feiyin' => array('text' => '飞印打印机', 'css' => 'label label-danger'), '365' => array('text' => '365打印机', 'css' => 'label label-warning'), 'AiPrint' => array('text' => 'AiPrint打印机', 'css' => 'label label-info'), 'yilianyun' => array('text' => '易联云打印机', 'css' => 'label label-primary'));
}
function printer_name()
{
	return array('feie' => array('text' => '飞鹅'), 'feiyin' => array('text' => '飞印'), '365' => array('text' => '365'), 'AiPrint' => array('text' => 'AiPrint'), 'yilianyun' => array('text' => '易联云'));
}
function print_feie_url($deviceno)
{
	$number = substr($deviceno, 2, 1);
	$data = array('5' => 'http://dzp.feieyun.com', '6' => 'http://api163.feieyun.com', '7' => 'http://api174.feieyun.com');
	return $data[$number];
}
function printers($schoolid)
{
	$list = pdo_fetchall("SELECT id,name,type FROM " . tablename('wx_school_printer') . " WHERE  schoolid = '{$schoolid}' And status = 1 ORDER BY createtime DESC ");
	return $list;
}
