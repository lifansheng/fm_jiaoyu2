<?php
global $_GPC,$_W;
$op = $_GPC['op'] ? $_GPC['op'] : 'default' ;

if($op == 'default'){
    $result['resultCode'] = "42";
    $result['resultStr'] = "方法不存在";
    die(json_encode($result));
}

if($op == 'addXzfOrder'){
    $GetData = $_GPC['__input'];
    //  $GetData = $_GPC;
    $SchoolInfo = pdo_fetch("SELECT weid,schoolid FROM ".GetTableName('schoolset')." WHERE xzf_scid = '{$GetData['scid']}' ");
    $weid = $SchoolInfo['weid'];
    $schoolid = $SchoolInfo['schoolid'];
    $preg = '/(\d+)\.(\d+)/is';
    preg_match_all($preg,$GetData['amount'],$arr);
    $amount = $arr[0][0];
    $data = array(
        'weid' => $weid, 
        'schoolid' => $schoolid, 
        'pushtype' => $GetData['pushtype'],
        'usertype' => $GetData['usertype'],
        'sid' => $GetData['userid'],
        'content' => $GetData['content'],
        'address' => $GetData['address'],
        'project' => $GetData['project'],
        'datetime' => strtotime($GetData['datetime']),
        'amount' =>  $amount,
        'paytype' => $GetData['paytype'],
        'appid' => $GetData['appid'],
        'createtime' => time(),
    );
    if(empty($GetData['userid'])){
        $result['ack'] = "34";
        $result['msg'] = "请求参数错误";
        die(json_encode($result));
    }
    pdo_insert(GetTableName('xzforder',false),$data);
    WriteGlobalLogXZF('获取校智付data');
    WriteGlobalLogXZF($data);
    $insertid = pdo_insertid();
    $url = $_W['siteroot'] .'app/'.$this->createMobileUrl('xzfport', array('weid' => $weid,'schoolid' => $schoolid,'op'=>'addXzf'));
    $aaa = ihttp_post($url,$insertid);
    $result['ack'] = "0";
    $result['msg'] = "success";
    die(json_encode($result));

}elseif($op == 'addXzf'){
    $id = json_decode(file_get_contents('php://input'),true);
    WriteGlobalLogXZF('发送推送消息orderid');
    WriteGlobalLogXZF($id);
    $this->sendMobileXzfSale($_GPC['weid'],$_GPC['schoolid'],$id);
}



function WriteGlobalLogXZF($GetData){
    $txtname = 'GobalLogxzf.txt';
    $txtpath_name = IA_ROOT . '/attachment/down/' . $txtname;
	ob_start(); //打开缓冲区
	var_dump($GetData);
	$a = ob_get_contents(); //输出缓冲区内容到$a,相当于赋值给$a
	ob_clean();   //这里清除缓冲区内容

	$fp = fopen($txtpath_name,"a");//打开文件资源通道 不存在则自动创建
    fwrite($fp,date("Y-m-d H:i:s").'   '.$a."\r\n");//写入文件
    fclose($fp);//关闭资源通道
}
