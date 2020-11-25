<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

//获取业务员,$type =0 获取所有参与销售的老师，$type=1仅获取业务员，$type=2仅获取销售经理
//$search 为筛选条件字符串，格式同$conditon
function GetAllSellTea($schoolid,$weid,$type = 0,$search = ''){
    $conditon = '';
    if($type == 0){
        $conditon = ' and is_sell != 0 ';
    }elseif($type != 0){
        $conditon = " and is_sell = {$type} ";
    }
    $list = pdo_fetchall("SELECT id,tname FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' $conditon $search ");
    return $list;
}


function GetSellTeaByStuId($schoolid,$weid,$sid){
    $student =  pdo_fetch("SELECT s_name,sellteaid FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and id = '{$sid}' ");
    $selltea =  pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and id = '{$student['sellteaid']}' and is_sell != 0 ");
    $data = [];
    if(!empty($selltea) && !empty($student['sellteaid'])){
        $data['status'] = true; //找到业务员
        $data['sellteaname'] = $selltea['tname'];
    }else{
        $data['status'] = false; //未找到业务员
    }
    $data['sname'] = $student['s_name'];
    return $data;
}




?>