<?php

			
mload()->model('hxy');
  
$op = $_GPC['op'] ? $_GPC['op'] : 'display' ;

if($op == 'display'){
	$appid = "111111";
	$ec_code = "1233";
	$op_time = date("Y-m-d H:i:s",time());
	$checkid  = 1478; 
	$sid = 3261;
	$macid = 2 ;
	$cost = 10;
	$aaa = SendHXYPaySms($appid,$op_time,$ec_code,$cost,$macid,$sid,$paytype = 1,$cost_type = 2,$is_yue = 1);
	var_dump($aaa);
}

 
 

/* global $_GPC,$_W;

$url = "http://manger.daren007.com/app/index.php?i=3&c=entry&schoolid=4&m=fm_jiaoyu&do=hxyport&op=BorrowBooks";

$postdata = array(
	'ss' => "asdadsd总 i 微博",
	'ddd' => 222
);

 mload()->model('hxy');
 

$aaa = json_decode(hxy_http_post_json($url, $postdata,true),true);
var_dump($aaa); */





// $appid = "111111";
// $ec_code = "1233";
// $op_time = date("Y-m-d H:i:s",time());
// $sendid = "41512523";
// $receivers = array(
// 	0 => array(
// 		'receiverid' => "41513791"
// 	),
// 	1 => array(
// 		'receiverid' => "41512855"
// 	),
// 	2 => array(
// 		'receiverid' => "41512947"
// 	)
// );   
// $smscontent = "测试消息发送";
 
// $aaa = SyncAllTea('240069',$appid);
// var_dump($aaa);	

/* global $_GPC,$_W;

$schoolid = $_GPC['schoolid'];
$weid = $_W['uniacid'];

$timeDeead =  1568599200;
$opp = $_GPC['op'];
if($opp == 'display'){
	 
}elseif($opp == 'more'){
	$waitList = pdo_fetchall("SELECT * FROM ".GetTableName('yuecostlog')." WHERE  schoolid = '{$schoolid}' and weid = '{$weid}' and on_offline = 2 and costtime > '{$timeDeead}' ORDER BY costtime DESC  LIMIT 0,2000 ");
	$last = '';
	$count = 0;
	foreach($waitList as $key=> $v){
		$cost_o = $v['cost'];
		if($v['cost_type'] == 1 ){ //充值
			$New_cost = $cost_o;
		}elseif($v['cost_type'] == 2 ){
			$New_cost = 0 - $cost_o ;
		}
		$student = pdo_fetch("SELECT id,chongzhi FROM ".GetTableName('students')." WHERE id = '{$v['sid']}' and schoolid = '{$schoolid}' and weid = '{$weid}' ");
		if(!empty($student)){
			$new_chongzhi = $student['chongzhi'] - $New_cost;
			 

			pdo_update(GetTableName('students',false),array('chongzhi' => $new_chongzhi),array('id'=>$v['sid']));
			pdo_delete(GetTableName('yuecostlog',false),array('id'=>$v['id']));
			$last  = date('Y-m-d H:i:s',$v['costtime']);
			$count  = $count + 1 ;
		}
		
	}
	$MSG = "当前进度时间：".$last.";条数:".$count;
	var_dump($MSG);

} */

?>