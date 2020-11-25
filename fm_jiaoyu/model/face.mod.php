<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-01-14 11:22:05
 * @LastEditTime: 2020-02-26 17:50:14
 */
/**
 *  和校园 mod
 * 
 */


/**
 * 发送 post 请求 function
 *
 * @param [type] $url
 * @param [type] $jsonStr
 *
 * @return void
 */
function CheckFace($weid,$picurl)
{
    $set = pdo_fetch("SELECT facesecret,faceid FROM " . GetTableName('set') . " WHERE weid = :weid ",array(':weid' => $weid));
    $url = 'https://aip.baidubce.com/oauth/2.0/token';
    $param = array(
        'grant_type' => 'client_credentials',
        'client_id' => "{$set['faceid']}",
        'client_secret' => "{$set['facesecret']}",
    );
    $o = "";
    foreach ( $param as $k => $v ) 
    {
    	$o.= "$k=" . urlencode( $v ). "&" ;
    }
    $post_data = substr($o,0,-1);
    $facetoken = ihttp_post($url, $post_data);
    $access_token = json_decode($facetoken['content'],true);
    $url = 'https://aip.baidubce.com/rest/2.0/face/v3/detect?access_token=' . $access_token['access_token'];
    $face = array(
        'image' => tomedia($picurl),
        'image_type' => 'URL',
        'face_field' => 'faceshape,facetype,quality',
    );
    $bodys = json_encode($face);
    $isface = ihttp_post($url, $bodys);
    $result = json_decode($isface['content'],true);
 

    if( $result['error_code'] == 0 && $result['result']['face_num'] == 1 && $result['result']['face_list'][0]['face_probability'] != 0 && $result['result']['face_list'][0]['face_type']['type'] == 'human' && $result['result']['face_list'][0]['quality']['blur'] <= '1' && $result['result']['face_list'][0]['quality']['completeness'] == '1'){
        $data['result'] = true;
    }else{
        file_remote_delete($picurl);
        $data['result'] = false;
        $data['code'] = $result['error_code'];
    }
    return $data;
}


function GetFaceError($code){
	$return = [];
	$return['222201'] = '服务端请求失败';
	$return['222202'] = '图片中没有人脸';
	$return['222203'] = '检查图片质量';
	$return['223113'] = '人脸有被遮挡';
	$return['223114'] = '人脸模糊';
	$return['223115'] = '人脸光照不好';
	$return['223116'] = '人脸不完整';
	$return['223121'] = '请勿遮挡左眼';
	$return['223122'] = '请勿遮挡右眼';
	$return['223123'] = '请勿遮挡左脸颊';
	$return['223124'] = '请勿遮挡右脸颊';
	$return['223125'] = '请勿遮挡下巴';
	$return['223126'] = '请勿遮挡鼻子';
	$return['223127'] = '请勿遮挡嘴巴';
	$return['222204'] = '请确认url可公网访问';
	if(!empty($return[$code])){
		return $return[$code].'请重新上传';
	}else{
		return '未知错误！人脸检测失败，请重新上传';
	}
}
// /**
//  * 获取百度人脸token
//  */
// function GetFaceToken($url,$param){
	
// }