<?php
/**
 * 封装阿里云点播
 * 成都市微美网络科技有限公司
 * www.daren007.com
 */
require_once IA_ROOT.'/addons/fm_jiaoyu/inc/func/aliyun-php-sdk/aliyun-php-sdk-core/Config.php';
use vod\Request\V20170321 as vod;
function initVodClient($accessKeyId, $accessKeySecret, $securityToken) {
    $regionId = 'cn-shanghai';
    $profile = DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret, $securityToken);
    return new DefaultAcsClient($profile);
}
function createUploadVideo($client,$fileinfo) {
	$request = new vod\CreateUploadVideoRequest();
	$request->setTitle($fileinfo['title']);        
	$request->setFileName($fileinfo['filename']); 
	$request->setDescription($fileinfo['description']);
	$request->setCoverURL($fileinfo['coverurl']); 
	$request->setTags($fileinfo['tag']);
	$request->setCateId($fileinfo['Category']);
	$request->setAcceptFormat('JSON');
	return $client->getAcsResponse($request);
}
function refreshUploadVideo($client, $videoId) {
    $request = new vod\RefreshUploadVideoRequest();
    $request->setVideoId($videoId);
    $request->setAcceptFormat('JSON');
    return $client->getAcsResponse($request);
}
function getPlayInfo($client, $videoId) {
    $request = new vod\GetPlayInfoRequest();
    $request->setVideoId($videoId);
    $request->setAuthTimeout(3600*24);
	$request->setFormats('mp4');
    $request->setAcceptFormat('JSON');
    return $client->getAcsResponse($request);
}
function getVideoInfo($client, $videoId) {
    $request = new vod\GetVideoInfoRequest();
    $request->setVideoId($videoId);
    $request->setAcceptFormat('JSON');
    return $client->getAcsResponse($request);
}
function addCategory($client, $cateName, $parentId=-1) {
    $request = new vod\AddCategoryRequest();
    $request->setCateName($cateName); 
    $request->setParentId($parentId);
    $request->setAcceptFormat('JSON');
    return $client->getAcsResponse($request);
}
function getCategories($client, $cateId=-1, $pageNo=1, $pageSize=10) {
    $request = new vod\GetCategoriesRequest();
    $request->setCateId($cateId);   // 分类ID，默认为根节点分类ID即-1
    $request->setPageNo($pageNo);
    $request->setPageSize($pageSize);
    $request->setAcceptFormat('JSON');
    return $client->getAcsResponse($request);
}
function deleteVideos($client, $videoIds) {
    $request = new vod\DeleteVideoRequest();
    $request->setVideoIds($videoIds);   // 支持批量删除视频；videoIds为传入的视频ID列表，多个用逗号分隔
    $request->setAcceptFormat('JSON');
    return $client->getAcsResponse($request);
}
function object_array($array){
    if(is_object($array)){
        $array = (array)$array;
    } if(is_array($array)){
        foreach($array as $key=>$value) { 
            $array[$key] = object_array($value);  
        } 
    }  
    return $array;  
}