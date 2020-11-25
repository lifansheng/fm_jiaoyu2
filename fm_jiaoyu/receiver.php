<?php
/**
 * 微教育模块订阅器
 *
 * @author 高贵血迹
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class Fm_jiaoyuModuleReceiver extends WeModuleReceiver {

	public $table_qrinfo = 'wx_school_qrcode_info';
	public $table_qrstat = 'wx_school_qrcode_statinfo';
	public $table_gongkaike = 'wx_school_gongkaike';
	public $table_group = 'wx_school_fans_group';
	public $table_students = 'wx_school_students';
	public $table_classify = 'wx_school_classify';

	public function receive() {
		global $_W;
		load()->func('logging');		 
		WeUtility::logging('fm_jiaoyu_messagelog', $this->message);
		if ($this->message['event'] == 'subscribe' && !empty($this->message['ticket'])) {
			$sceneid = $this->message['scene'];
			$row = pdo_fetch("SELECT * FROM " . tablename($this->table_qrinfo) . " WHERE qrcid = '{$sceneid}' and weid = '{$_W['uniacid']}' ORDER BY id DESC");
			if (!empty($row)){
				//type 1学校永久独立二维码 2公开课临时 3学生绑定临时 4班级邀请临时
				if ($row['type'] == 1 || $row['type'] == 3){
				    $insert = array(
						'weid' => $_W['uniacid'],
						'qid' => $row['id'],
						'openid' => $this->message['from'],
						'type' => 1,
						'qrcid' => $sceneid,
						'name' => $row['name'],
						'group_id' => $row['group_id'],
						'createtime' => TIMESTAMP
					);
					$openid = array();
					$openid = $this->message['from'];
					if($row['type'] == 1){
						$group_id = $row['group_id'];
						$rid 	  = $row['rid'];
						$this->toSendCustomNotice($openid,$rid);
					}
					if($row['type'] == 3){
						$zhugroup = pdo_fetch("SELECT group_id FROM " . tablename($this->table_group) . " WHERE weid = '{$_W['uniacid']}' And schoolid = '{$row['schoolid']}' And is_zhu = 1 ");
						$group_id = $zhugroup['group_id'];
						$this->toSendCustomNoticeGkk($openid,$row['id'],'user',$row['schoolid'],$row['expire']);
					}		
					$weixindata = array('openid_list' => $openid,'tagid' => $group_id);		
					$this->weixin_fans_group($weixindata);
					pdo_insert($this->table_qrstat, $insert);
					pdo_update($this->table_qrinfo, array('subnum' => $row['subnum'] + 1), array('id' => $row['id']));
				}	
				if($row['type'] == 5){
					$openid = array();
					$openid = $this->message['from'];
					$this->toSendCustomNoticeGkk($openid,$row['id'],'bjbangding',$row['schoolid'],$row['expire']);
				}				
			}
		} else if($this->message['event'] == 'SCAN') {
			$sceneid = $this->message['scene'];
			$row = pdo_fetch("SELECT * FROM " . tablename($this->table_qrinfo) . " WHERE qrcid = '{$sceneid}' and weid = '{$_W['uniacid']}' ORDER BY id DESC");
			if (!empty($row)){
				if ($row['type'] == 1 || $row['type'] == 3){
					$statinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_qrstat) . " WHERE openid = '{$this->message['from']}' and weid= '{$_W['uniacid']}' ");
						$insert = array(
							'weid' => $_W['uniacid'],
							'qid' => $row['id'],
							'qrcid' => $sceneid,
							'group_id' => $row['group_id'],
							'name' => $row['name'],
							'createtime' => time()
						);		
					$openid = array();
					$openid = $this->message['from'];
					if($row['type'] == 1){
						$group_id = $row['group_id'];
						$rid 	  = $row['rid'];
						$this->toSendCustomNotice($openid,$rid);
					}
					if($row['type'] == 3){
						$zhugroup = pdo_fetch("SELECT group_id FROM " . tablename($this->table_group) . " WHERE weid = '{$_W['uniacid']}' And schoolid = '{$row['schoolid']}' And is_zhu = 1 ");
						$group_id = $zhugroup['group_id'];
						$this->toSendCustomNoticeGkk($openid,$row['id'],'user',$row['schoolid'],$row['expire']);
					}
					$weixindata = array('openid_list' => $openid,'tagid' => $group_id);			
					$this->weixin_fans_group($weixindata);
					pdo_update($this->table_qrinfo, array('subnum' => $row['subnum'] + 1), array('id' => $row['id']));
					pdo_update($this->table_qrstat, $insert, array('id' => $statinfo['id']));
				}
				if ($row['type'] == 2 ){
					$statinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_qrstat) . " WHERE openid = '{$this->message['from']}' and weid= '{$_W['uniacid']}' ");
					$insert = array(
						'weid' => $_W['uniacid'],
						'qid' => $row['id'],
						'qrcid' => $sceneid,
						'group_id' => $row['group_id'],
						'name' => $row['name'],
						'createtime' => time()
					);
					$openid = $this->message['from'];
					if($row['id']){
						$this->toSendCustomNoticeGkk($openid,$row['id'],'gkk',0,$row['expire']);	
					}
					pdo_update($this->table_qrinfo, array('subnum' => $row['subnum'] + 1), array('id' => $row['id']));
					pdo_update($this->table_qrstat, $insert, array('id' => $statinfo['id']));
				}
				if($row['type'] == 5){
					$openid = array();
					$openid = $this->message['from'];
					$this->toSendCustomNoticeGkk($openid,$row['id'],'bjbangding',$row['schoolid'],$row['expire']);
				}
			}
		} else if($this->message['event'] == 'unsubscribe') {
			$openid = $this->message['from'];
		}
	}

	public function weixin_fans_group($weixindata){
		global $_W, $_GPC;
		load()->func('logging');
		$weid = $_W['uniacid'];
		load()->classs('weixin.account');
		$accObj = WeixinAccount::create($weid);
		$access_token = WeAccount::token();
		$url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token={$access_token}";
		$url = sprintf($url);
		logging_run("$url===$weixindata");
		load()->func('communication');
		$weixindata = json_encode($weixindata);
		$response = ihttp_request($url, $weixindata);
		if (is_error($response)) {
			logging_run("访问公众平台接口失败, 错误: {$response['message']},$weixindata");
		}
		$result = @json_decode($response['content'], true);
		if (empty($result)) {
			logging_run("服务器没有返回");
		} elseif (!empty($result['errcode'])) {
			logging_run("访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},$weixindata");
		} else {
			logging_run('weixin_fans_group接口调用成功');
		}
		return $result;
	}

    public function toSendCustomNotice($openid,$srid) {
		global $_W, $_GPC;
		load()->model('material');
		$acc = WeAccount::create($acid);
		$send['touser'] = trim($openid);
		$send['msgtype'] = 'news';
		$rid = intval($srid);
		$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE rid = :id AND parent_id = -1 ORDER BY displayorder DESC, id ASC LIMIT 8";
		$commends = pdo_fetchall($sql, array(':id' => $rid));
		if (empty($commends)) {
			$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE rid = :id AND parent_id = 0 ORDER BY RAND()";
			$main = pdo_fetch($sql, array(':id' => $rid));
			if(empty($main['id'])) {
				return false;
			}
			$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE id = :id OR parent_id = :parent_id ORDER BY parent_id ASC, displayorder DESC, id ASC LIMIT 8";
			$commends = pdo_fetchall($sql, array(':id'=>$main['id'], ':parent_id'=>$main['id']));
		}
		
		if(empty($commends)) {
			$idata = array();
			$send['news'] = '';
		}else{
			$news = array();
			foreach($commends as $commend) {
				$row = array();
				$sql = "SELECT * FROM " . tablename('wechat_news') . " WHERE displayorder = :displayorder AND title = :title AND attach_id = :attach_id";
				$r = pdo_fetch($sql, array(':displayorder'=>$commend['displayorder'], ':title'=>$commend['title'], ':attach_id'=>$commend['media_id']));
				$row['title'] = empty($r['title']) ? urlencode($commend['title']) : urlencode($r['title']);
				$row['description'] = empty($r['description']) ? urlencode($commend['description']) : urlencode($r['description']);
				$row['picurl'] = !empty($r['thumb_url']) ? tomedia($r['thumb_url']) : tomedia($commend['thumb_url']);
				$row['url'] = !empty($r['content_source_url']) ? $r['content_source_url'] : $commend['url'];
				$news[] = $row;
			}
			$send['news']['articles'] = $news;
		}
        $acc->sendCustomNotice($send);
    }
	
	public function toSendCustomNoticeGkk($openid,$qrid,$type,$schoolid,$expire){
		global $_W, $_GPC;
		load()->model('material');
		$acc = WeAccount::create($acid);
		$send['touser'] = trim($openid);
		$send['msgtype'] = 'news';
		if($type == 'gkk'){
			$gkkinfo = pdo_fetch("SELECT * FROM " .tablename($this->table_gongkaike)."WHERE qrid = :qrid", array(':qrid' => $qrid));
			$tempbak_title ="点击评价公开课【".$gkkinfo['name']."】";
			$tempbak['title'] = urlencode($tempbak_title);
			$tempbak['url'] = $_W['siteroot']. 'app/' . $this->createMobileUrl('gkkpingjia', array('gkkid' => $gkkinfo['id'],'schoolid' =>$gkkinfo['schoolid'],'op'=>'edite'), true);
		}
		if($type == 'user'){
			$student = pdo_fetch("SELECT id,icon,s_name FROM " .tablename($this->table_students)."WHERE schoolid = :schoolid And qrcode_id = :qrcode_id", array(':qrcode_id' => $qrid,':schoolid' => $schoolid));
			$school = pdo_fetch("SELECT thumb,spic FROM " .tablename('wx_school_index')."WHERE id = :id ", array(':id' => $schoolid));
			$logo = !empty($school['thumb']) ? tomedia($school['thumb']) : tomedia($school['spic']);
			$tempbak['picurl'] = !empty($student['icon']) ? tomedia($student['icon']) : tomedia($logo);
			$overtime = date('m月d日',$expire);
			$tempbak['title'] = urlencode("【".$student['s_name']."】现在可以开始绑定学生了");
			$tempbak['description'] = urlencode("点击绑定【".$student['s_name']."】,本链接将于".$overtime."失效！");
			$tempbak['url'] = $_W['siteroot']. 'app/' . $this->createMobileUrl('qkbinding', array('sid' => $student['id'],'schoolid' =>$schoolid,'type'=>'student','expire'=>$expire), true);	
		}

		if($type == 'bjbangding'){
			$classify = pdo_fetch("SELECT sname,sid FROM " .tablename($this->table_classify)."WHERE schoolid = :schoolid And qrcode_id = :qrcode_id", array(':qrcode_id' => $qrid,':schoolid' => $schoolid));
			$school = pdo_fetch("SELECT thumb,spic FROM " .tablename('wx_school_index')."WHERE id = :id ", array(':id' => $schoolid));
			$logo = !empty($school['thumb']) ? tomedia($school['thumb']) : tomedia($school['spic']);
			$tempbak['picurl'] = tomedia($logo);
			$overtime = date('m月d日',$expire);
			$tempbak['title'] = urlencode("【".$classify['sname']."】现在可以开始绑定了");
			$tempbak['description'] = urlencode("点击绑定【".$classify['sname']."】,本链接将于".$overtime."失效！");
			$tempbak['url'] = $_W['siteroot']. 'app/' . $this->createMobileUrl('bjbangding', array('bjid' => $classify['sid'],'schoolid' =>$schoolid,'expire'=>$expire), true);	
		}
		$news[] =$tempbak; 
		$send['news']['articles'] = $news;	
        $acc->sendCustomNotice($send);
    }

	public function sendCustomNotice($data) {
		if(empty($data)) {
			return error(-1, '参数错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		$response = ihttp_request($url, urldecode(json_encode($data)));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}	
}