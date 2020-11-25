<?php

/**

 * By 高贵血迹

 */
$leixing = 3;
if($ckmac['type'] ==1){
	$leixing = 1;
	$lx = "进校";
	if($ckmac['apid']){
		$lx = "归寝";
	}
}else{
	$leixing = 2;
	$lx = "离校";
	if($ckmac['apid']){
		$lx = "离寝";
	}
}
$type = "异常".$lx;
$now = str_replace(":",".",$nowtime);
$jxstart = str_replace(":",".",$school['jxstart']);
$lxstart = str_replace(":",".",$school['lxstart']);
$jxstart1 = str_replace(":",".",$school['jxstart1']);
$lxstart1 = str_replace(":",".",$school['lxstart1']);
$jxstart2 = str_replace(":",".",$school['jxstart2']);
$lxstart2 = str_replace(":",".",$school['lxstart2']);
$jxend = str_replace(":",".",$school['jxend']);
$lxend = str_replace(":",".",$school['lxend']);
$jxend1 = str_replace(":",".",$school['jxend1']);
$lxend1 = str_replace(":",".",$school['lxend1']);
$jxend2 = str_replace(":",".",$school['jxend2']);
$lxend2 = str_replace(":",".",$school['lxend2']);
if ($jxstart <= $now & $now <= $jxend){
    $type = "早上".$lx;
}
if ($lxstart <= $now & $now <= $lxend){
    $type = "下午".$lx;
}
if ($jxstart1 <= $now & $now <= $jxend1){
    $type = "午间".$lx;
}
if ($lxstart1 <= $now & $now <= $lxend1){
    $type = "午间".$lx;
}
if ($jxstart2 <= $now & $now <= $jxend2){
    $type = "晚间".$lx;
}
if ($lxstart2 <= $now & $now <= $lxend2){
    $type = "晚间".$lx;
}
?>