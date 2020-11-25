<?php
/**
 * 微教育模块定义
 *
 * @author 高贵血迹
 * @url http://www.daren007.com
 */
if (!defined('IN_IA')) { exit('Access Denied');}
global $_W, $_GPC;
load()->func('communication');
$schoolid          = intval($_GPC['schoolid']);
if($schoolid){
	$GLOBALS['frames'] = $this->getNaveMenu($schoolid, $action);
}
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
define('FM_JIAOYU_AUTH_URL', 'https%3a%2f%2fmac.weimeizhan.com%2fapi%2fhelp.php');
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$oauthurl = getoauthurl();
$versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/version.php';
$versionfile2 = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
require $versionfile;
require $versionfile2;
if ($_W['isfounder'] || $_W['role'] == 'owner'){
	$user = 'admin';
}else{
	$user = 'user';
}
if ($operation == 'display') {
	$updatedate = date('Y-m-d H:i', filemtime($versionfile));
    $version = FM_JIAOYU_VERSION;
	$locahelp = pdo_fetchall("SELECT * FROM " . tablename($this->table_help) . " WHERE is_share = 2 ORDER BY lasttime DESC ");	
	$mycoludhelp = pdo_fetchall("SELECT * FROM " . tablename($this->table_help) . " WHERE is_share = 1 ");
	foreach($mycoludhelp as $key => $row){
		$resp = ihttp_post(urldecode(FM_JIAOYU_AUTH_URL), array(
			'type' => 'checkhelpopen',
			'user' => $user,
			'ip' => $_W['clientip'],
			'oauthurl' => $oauthurl,
			'oauthurl2' => FM_JIAOYU_HOST,
			'version' => $version,
			'couldhelpid'=> $row['could_id']
		));
		$ret = @json_decode($resp['content'], true);	
		$mycoludhelp[$key]['open'] = $ret['is_open'];	
	}
	$localastime = empty($locahelp[0]['lasttime'])? '无内容' : date('Y-m-d H:i',$locahelp[0]['lasttime']);	
}elseif ($operation == 'check') {
    $version = defined('FM_JIAOYU_VERSION') ? FM_JIAOYU_VERSION : '1.0';
    $resp = ihttp_post(urldecode(FM_JIAOYU_AUTH_URL), array(
        'type' => 'check',
		'user' => $user,
        'ip' => $_W['clientip'],
        'oauthurl' => $oauthurl,
		'oauthurl2' => FM_JIAOYU_HOST,
        'version' => $version,
        'manual'=>1
    ));
    $ret = @json_decode($resp['content'], true);
    if (is_array($ret)) {
	    $fmdata = array(
			'result' => $ret['result'],
			'm' => $ret['m'],
		);
		echo json_encode($fmdata);
		exit; 
    }
    die(json_encode(array('result' => 0, 'message' =>$ret['m'])));
}elseif ($operation == 'checktype') {
    $version = defined('FM_JIAOYU_VERSION') ? FM_JIAOYU_VERSION : '1.0';
    $resp = ihttp_post(urldecode(FM_JIAOYU_AUTH_URL), array(
        'type' => 'checktype',
		'user' => $user,
        'ip' => $_W['clientip'],
        'oauthurl' => $oauthurl,
		'oauthurl2' => FM_JIAOYU_HOST,
        'version' => $version,
        'manual'=>1
    ));
    $ret = @json_decode($resp['content'], true);
    if (is_array($ret)) {
	    $fmdata = array(
			'result' => $ret['result'],
			'm' => $ret['m'],
		);
		echo json_encode($fmdata);
		exit; 
    }
    die(json_encode(array('result' => 0, 'message' =>$ret['m'])));	
}elseif($operation == 'post') {
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_help) . " WHERE id = '{$id}'");
		if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
		if($item['could_id']){
			$version = defined('FM_JIAOYU_VERSION') ? FM_JIAOYU_VERSION : '1.0';
			$resp = ihttp_post(urldecode(FM_JIAOYU_AUTH_URL), array(
				'type' 			=> 'getcouldcontent',
				'user' 			=> $user,
				'ip' 			=> $_W['clientip'],
				'oauthurl' 		=> $oauthurl,
				'oauthurl2' 	=> FM_JIAOYU_HOST,
				'version' 		=> $version,
				'couldhelpid'   => $item['could_id'],
			));
			$ret = @json_decode($resp['content'], true);
			if($item['lasttime'] > $ret['lasttime']){
				$content = $item['content'];
			}else{
				$content = $ret['could_content'];
			}
		}else{
			$content = $item['content'];
		}
    }
	if (checksubmit('submit')) {
		$data = array(
			'type'   		=> trim($_GPC['type']),
			'displayorder'  => trim($_GPC['displayorder']),
			'is_share'  	=> trim($_GPC['is_share']),
			'title'   		=> trim($_GPC['title']),
			'author'  		=> trim($_GPC['author']),
			'content'       => trim($_GPC['content']),
			'lasttime' 		=> time(),
			'createtime' 	=> time()
		);
		if($data['is_share'] == 1){
			$version = defined('FM_JIAOYU_VERSION') ? FM_JIAOYU_VERSION : '1.0';
			$resp = ihttp_post(urldecode(FM_JIAOYU_AUTH_URL), array(
				'type' 			=> 'sendhelp',
				'user' 			=> $user,
				'ip' 			=> $_W['clientip'],
				'oauthurl' 		=> $oauthurl,
				'oauthurl2' 	=> FM_JIAOYU_HOST,
				'version' 		=> $version,
				'helptype'   	=> trim($_GPC['type']),
				'title'   		=> trim($_GPC['title']),
				'author'  		=> trim($_GPC['author']),
				'content'       => trim($_GPC['content']),
			));
			$ret = @json_decode($resp['content'], true);
			$data['could_id'] = $ret['could_id'];
		}
		if(!empty($id)){
			unset($data['createtime']);
			pdo_update($this->table_help, $data, array('id' => $id));
			message('更新成功', $this->createWebUrl('help', array('op' => 'display','schoolid' => $schoolid)), 'success');
		}else{
			if(empty($data['author'])){
				$data['author'] = '管理员';
			}
			pdo_insert($this->table_help, $data);
			if($data['is_share'] == 1){
				message('提交共享教程成功,请联系客服审核审核后方可显示', $this->createWebUrl('help', array('op' => 'display','schoolid' => $schoolid)), 'success');
			}else{
				message('新增教程成功', $this->createWebUrl('help', array('op' => 'display','schoolid' => $schoolid)), 'success');
			}
		}
	}	
}elseif($operation == 'detail') {
    if(!empty($_GPC['id'])){
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_help) . " WHERE id = '{$_GPC['id']}'");
		if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
		if($item['could_id']){
			$version = defined('FM_JIAOYU_VERSION') ? FM_JIAOYU_VERSION : '1.0';
			$resp = ihttp_post(urldecode(FM_JIAOYU_AUTH_URL), array(
				'type' 			=> 'getcontent',
				'user' 			=> $user,
				'ip' 			=> $_W['clientip'],
				'oauthurl' 		=> $oauthurl,
				'oauthurl2' 	=> FM_JIAOYU_HOST,
				'version' 		=> $version,
				'couldhelpid'   => $item['could_id'],
			));
			$ret = @json_decode($resp['content'], true);
			$coulditme = $ret['item'];
			if($item['lasttime'] < $coulditme['lasttime']){
				unset($item);
				$item = $coulditme;				
			}
		}else{
			$content = $item['content'];
		}
    }
    if(!empty($_GPC['helpid'])){
		$version = defined('FM_JIAOYU_VERSION') ? FM_JIAOYU_VERSION : '1.0';
		$resp = ihttp_post(urldecode(FM_JIAOYU_AUTH_URL), array(
			'type' 			=> 'getcontent',
			'user' 			=> $user,
			'ip' 			=> $_W['clientip'],
			'oauthurl' 		=> $oauthurl,
			'oauthurl2' 	=> FM_JIAOYU_HOST,
			'version' 		=> $version,
			'couldhelpid'   => $_GPC['helpid'],
		));
		$ret = @json_decode($resp['content'], true);
		$coulditme = $ret['item'];
		$item = $coulditme;				
    }
} elseif ($operation == 'delete') {	
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM " . tablename($this->table_help) . " WHERE id = {$id} ");
	if(!empty($item)){
		pdo_delete($this->table_help, array('id' => $id));
		message('删除教程成功',referer(), 'success');
	}else{
		message('本数据不存在或已被删除',referer(), 'success');
	}	
}
include $this->template('web/help');
