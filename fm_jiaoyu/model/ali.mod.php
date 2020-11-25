<?php
/**
 * 封装阿里云点播
 * 成都市微美网络科技有限公司
 * www.daren007.com
 */
defined('IN_IA') or exit('Access Denied');
require IA_ROOT.'/addons/fm_jiaoyu/inc/func/aliyun-php-sdk/ali_vod_api.php';
function GetAliVideoUrl($appid,$key,$vidoeid){
	try {
		$client = initVodClient($appid, $key,'');
		$playInfo = getPlayInfo($client, $vidoeid);
		$nowinfo = object_array($playInfo->PlayInfoList->PlayInfo);
		$result['PlayURL'] = $nowinfo[0]['PlayURL'];
		$result['info'] = "获取播放地址成功";
		$result['result'] = true;
	} catch (Exception $e) {
		$result['msg'] = $e->getMessage()."\n";
		$result['info'] = "获取播放地址失败";
		$result['result'] = false;
	}
	return $result;
}

function GetAliVideoCover($appid,$key,$vidoeid){
	try {
		$client = initVodClient($appid, $key,'');
		$videoInfo = getVideoInfo($client, $vidoeid);
		$videoInfo = object_array($videoInfo);
		$result['CoverURL'] = $videoInfo['Video']['CoverURL'];
		$result['info'] = "获取封面成功";
		$result['result'] = true;
	} catch (Exception $e) {
		$result['msg'] = $e->getMessage()."\n";
		$result['info'] = "获取封面失败";
		$result['result'] = false;
	}
	return $result;
}

function AddCategorys($appid, $key, $schoolid,$weid) {
	$schoolset = pdo_fetch("SELECT id,alivodcate FROM " . tablename('wx_school_schoolset') . " WHERE :weid = weid And :schoolid = schoolid", array(':weid' => $weid,':schoolid' => $schoolid));
	$school = pdo_fetch("SELECT title FROM " . tablename('wx_school_index') . " WHERE :id = id", array(':id' => $schoolid));
	if($schoolset){
		$checkfl = GetCategoriess($appid,$key,$schoolset['alivodcate']);
		if($checkfl['CateId'] != $schoolset['alivodcate']){
			try {
				$client = initVodClient($appid, $key,'');
				$addRes = addCategory($client, $school['title']);
				$videoInfo = object_array($addRes);
				$result['CateId'] = $videoInfo['Category']['CateId'];
				$result['info'] = "创建分类成功";
				$result['result'] = true;
				pdo_update('wx_school_schoolset',  array ('alivodcate' => $result['CateId']), array ('id' => $schoolset['id']) );
			} catch (Exception $e) {
				$result['msg'] = $e->getMessage()."\n";
				$result['info'] = "创建分类失败";
				$result['result'] = false;
				
			}
		}else{
			$result['CateId'] = $schoolset['alivodcate'];
			$result['info'] = "获取分类ID";
			$result['result'] = true;
		}
	}else{
		try {
			$client = initVodClient($appid, $key,'');
			$addRes = addCategory($client, $school['title']);
			$videoInfo = object_array($addRes);
			$result['CateId'] = $videoInfo['Category']['CateId'];
			$result['info'] = "创建分类成功";
			$result['result'] = true;
			pdo_insert('wx_school_schoolset', array ('alivodcate' => $result['CateId'],'weid' => $weid,'schoolid' => $schoolid));
		} catch (Exception $e) {
			$result['msg'] = $e->getMessage()."\n";
			$result['info'] = "创建分类失败";
			$result['result'] = false;
		}
	}
	return $result;
}

function GetCategoriess($appid,$key,$cateId) {
	try {
		$client = initVodClient($appid, $key,'');
		$getRes = getCategories($client, $cateId);
		$videoInfo = object_array($getRes);
		$result['CateId'] = $videoInfo['Category']['CateId'];
		$result['info'] = "查询分类成功";
		$result['result'] = true;
	} catch (Exception $e) {
		$result['msg'] = $e->getMessage()."\n";
		$result['info'] = "查询分类失败";
		$result['result'] = false;
	}
	return $result;
}
function DelAlivod($appid,$key,$vidoeid) {
	try {
		$client = initVodClient($appid, $key,'');
		$delInfo = deleteVideos($client, $vidoeid);
		$videoInfo = object_array($delInfo);
		$result['info'] = "删除视频成功";
		$result['result'] = true;
	} catch (Exception $e) {
		$result['msg'] = $e->getMessage()."\n";
		$result['info'] = "删除视频失败";
		$result['result'] = false;
	}
	return $result;
}

function GetAliApp($weid,$schoolid) {
	$schoolset = pdo_fetch("SELECT id,alivodappid,alivodkey FROM " . tablename('wx_school_schoolset') . " WHERE :weid = weid And :schoolid = schoolid", array(':weid' => $weid,':schoolid' => $schoolid));
	if($schoolset && $schoolset['alivodappid'] && $schoolset['alivodkey']){
		$result['alivodappid'] = $schoolset['alivodappid'];
		$result['alivodkey'] = $schoolset['alivodkey'];
		$result['result'] = true;
	}else{
		$result['info'] = "未填写";
		$result['result'] = false;
	}
	return $result;
}
