<?php
/*
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2020-05-29 14:18:15
 * @LastEditTime: 2020-08-11 14:58:19
 */
mload()->model('xzf');
global $_GPC,$_W;
$op = $_GPC['op'] ? $_GPC['op'] : 'default' ;
$schoolid = $_GPC['schoolid'];
$weid = $_GPC['i'];

if($op == 'sync'){
    $res = [];
    $CheckGrade = pdo_fetch("SELECT sid  FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and type='semester' and xzf_needsync = 1  ");
    if(!empty($CheckGrade)){
       $res['grade'] = SyncGrade($schoolid,true);
    }
    $CheckClass = pdo_fetch("SELECT sid  FROM ".GetTableName('classify')." WHERE  schoolid = '{$schoolid}' and type='theclass' and xzf_needsync = 1  ");
    if(!empty($CheckClass)){
       $res['class'] = SyncClass($schoolid,true);
    }
    $CheckStu = pdo_fetch("SELECT id FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and xzf_needsync = 1 ");
    if(!empty($CheckStu)){
       $res['stu'] = SyncStudent($schoolid,-1,1,true);
    }

    $CheckTea = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and xzf_needsync = 1 ");
    if(!empty($CheckTea)){
       $res['tea'] = SyncTeachers($schoolid,-1,1,true);
    }
    $res['card'] = SyncCard($schoolid,-1,1);
}


var_dump($res);


function WriteGlobalLogznl($GetData){
    $txtname = 'GobalLogznl.txt';
    $txtpath_name = IA_ROOT . '/attachment/down/' . $txtname;
	ob_start(); //打开缓冲区
	var_dump($GetData);
	$a = ob_get_contents(); //输出缓冲区内容到$a,相当于赋值给$a
	ob_clean();   //这里清除缓冲区内容

	$fp = fopen($txtpath_name,"a");//打开文件资源通道 不存在则自动创建
    fwrite($fp,date("Y-m-d H:i:s").'   '.$a."\r\n");//写入文件
    fclose($fp);//关闭资源通道
}
