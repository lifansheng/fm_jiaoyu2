<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-21 10:44:06
 * @LastEditTime: 2020-02-22 17:55:16
 */
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'yqdkgather';
$this1             = 'no13';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
mload()->model('kc');
if($operation == 'display'){
    $AllSelect = yqselect();
    //获取所有年级
    $AllNj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 'semester' AND is_over = 1 ORDER BY ssort DESC ,sid DESC ");
    //获取所有年级
    // 获取当前年级下的所有班级
    $nowbj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND parentid = '{$AllNj[0]['sid']}' ORDER BY ssort DESC");
    if(!empty($nowbj)){
        foreach ($nowbj as $key => $value) {
            //查询当前班级所有学生人数
            $studentsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE bj_id = '{$value['sid']}' ");
            //已检测的学生人数
            $mcsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('morningcheck')." WHERE bj_id = '{$value['sid']}' AND createdate = '{$nowday}'");
            //未检测是学生人数
            $nomcsum = intval($studentsum) - intval($mcsum);
            $nowbj[$key]['mcsum'] = $mcsum;
            $nowbj[$key]['nomcsum'] = $nomcsum;
        }
    }
    
    $AllBj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 'theclass' AND is_over = 1 ORDER BY ssort DESC ,sid DESC ");
    // 获取当前年级下的所有班级
    include $this->template('web/yqdkgather');
}elseif($operation == 'dknum'){
    $nowday = strtotime(date("Y-m-d",time()));
    $dknum = [];
    for($i=13;$i>=0;$i--){
        $first= $nowday-$i*86400;
        $last = $nowday-$i*86400 + 86399;
        $dkcount = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$first}' AND '{$last}')");
        //当前学校所有数量
        $studentsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}'");
        $dknum[$i]['createdate'] = date("m-d",$first);
        $dknum[$i]['dkcount'] = $dkcount;
        $dknum[$i]['nodkcount'] = $studentsum - $dkcount;
    }
    $data['createdate'] = array_values(array_column($dknum,'createdate'));//每天的日期
	$data['dkcount'] = array_values(array_column($dknum,'dkcount'));//每天打卡总人数
	$data['nodkcount'] = array_values(array_column($dknum,'nodkcount'));//当前班级学生总数
	die ( json_encode ( $data ) );
}elseif($operation == 'tiwennum'){
    $nowday = strtotime(date("Y-m-d",time()));
    $tiwennum = [];
    for($i=13;$i>=0;$i--){
        $first= $nowday-$i*86400;
        $last = $nowday-$i*86400 + 86399;
        //当日体温测量人数
        $AllCount = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$first}' AND '{$last}')");
        //当日体温正常人数
        $normal = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$first}' AND '{$last}') AND (tiwen BETWEEN '35.5' AND '37.5')");
        $tiwennum[$i]['createdate'] = date("m-d",$first);
        $tiwennum[$i]['normal'] = $normal;
    }
    $data['createdate'] = array_values(array_column($tiwennum,'createdate'));//每天的日期
	$data['normal'] = array_values(array_column($tiwennum,'normal'));//每天打卡总人数
	die ( json_encode ( $data ) );
}elseif($operation == 'notiwennum'){
    $nowday = strtotime(date("Y-m-d",time()));
    $tiwennum = [];
    $condition = " AND bj_id = '{$bj_id}' ";
    for($i=13;$i>=0;$i--){
        $first= $nowday-$i*86400;
        $last = $nowday-$i*86400 + 86399;
        //当日体温测量人数
        $AllCount = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$first}' AND '{$last}')");

        //当日体温正常人数
        $normal = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$first}' AND '{$last}') AND (tiwen BETWEEN '35.5' AND '37.5')");
        // 不正常人数
        $nonormal = intval($AllCount) - intval($normal);
        $tiwennum[$i]['createdate'] = date("m-d",$first);
        $tiwennum[$i]['nonormal'] = $nonormal;
    }
    $data['createdate'] = array_values(array_column($tiwennum,'createdate'));//每天的日期
	$data['nonormal'] = array_values(array_column($tiwennum,'nonormal'));//当前班级学生总数
    
	die ( json_encode ( $data ) );
}elseif($operation == 'a'){
    $start = strtotime(date("Y-m-d",time())) - ($_GPC['day'] * 86400);
    $end = strtotime(date("Y-m-d",time())) + 86399;
    $hasradio = yqselect();
    $raidoarr = [];
    foreach ($hasradio as $k => $v) {
        if($v['type'] == 'radio'){
            $raidoarr[$k]['data'] = array();
            $raidoarr[$k]['title'] = $v['title'];
            foreach ($v['data'] as $k_d => $v_d) {
                $raidoarr[$k]['name'][] = $v_d;
                $raidoarr[$k]['data'][$k_d] = array('value' => 0, 'name' => $v_d);
            }
        }
    }
    $yqdklist = pdo_fetchAll("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$start}' AND '{$end}') ORDER BY createtime DESC");
    foreach ($yqdklist as $key => $value) {
        $content = unserialize($value['content']);
        foreach ($content as $key2 => $value2) {
            if(yqselect($key2,'type') == 'radio'){
                $raidoarr[$key2]['data'][$value2]['value'] += 1;
            }
        }
    }
    $data['data'] = $raidoarr ;
	die ( json_encode ( $data ) );
}elseif($operation == 'b'){
    $start = strtotime(date("Y-m-d",time())) - ($_GPC['day'] * 86400);
    $end = strtotime(date("Y-m-d",time())) + 86399;
    $hasradio = yqselect();
    $raidoarr = [];
    foreach ($hasradio as $k => $v) {
        if($v['type'] == 'radio'){
            $raidoarr[$k]['data'] = array();
            $raidoarr[$k]['title'] = $v['title'];
            foreach ($v['data'] as $k_d => $v_d) {
                $raidoarr[$k]['name'][] = $v_d;
                $raidoarr[$k]['data'][$k_d] = array('value' => 0, 'name' => $v_d);
            }
        }
    }
    $yqdklist = pdo_fetchAll("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$start}' AND '{$end}') ORDER BY createtime DESC");
    foreach ($yqdklist as $key => $value) {
        $content = unserialize($value['content']);
        foreach ($content as $key2 => $value2) {
            if(yqselect($key2,'type') == 'radio'){
                $raidoarr[$key2]['data'][$value2]['value'] += 1;
            }
        }
    }
    $data['data'] = $raidoarr ;
    // var_dump($raidoarr);
	die ( json_encode ( $data ) );
}elseif($operation == 'c'){
    $start = strtotime(date("Y-m-d",time())) - ($_GPC['day'] * 86400);
    $end = strtotime(date("Y-m-d",time())) + 86399;
    $hasradio = yqselect();
    $raidoarr = [];
    foreach ($hasradio as $k => $v) {
        if($v['type'] == 'radio'){
            $raidoarr[$k]['data'] = array();
            $raidoarr[$k]['title'] = $v['title'];
            foreach ($v['data'] as $k_d => $v_d) {
                $raidoarr[$k]['name'][] = $v_d;
                $raidoarr[$k]['data'][$k_d] = array('value' => 0, 'name' => $v_d);
            }
        }
    }
    $yqdklist = pdo_fetchAll("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$start}' AND '{$end}')  ORDER BY createtime DESC");
    foreach ($yqdklist as $key => $value) {
        $content = unserialize($value['content']);
        foreach ($content as $key2 => $value2) {
            if(yqselect($key2,'type') == 'radio'){
                $raidoarr[$key2]['data'][$value2]['value'] += 1;
            }
        }
    }
    $data['data'] = $raidoarr ;
    // var_dump($raidoarr);
	die ( json_encode ( $data ) );
}elseif($operation == 'd'){
    $start = strtotime(date("Y-m-d",time())) - ($_GPC['day'] * 86400);
    $end = strtotime(date("Y-m-d",time())) + 86399;
    $yqdklist = pdo_fetchAll("SELECT * FROM " . GetTableName('yqdk') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND (createtime BETWEEN '{$start}' AND '{$end}') ORDER BY createtime DESC");
    $con_d = [];
    $count = [];
    $hascheck = yqselect();
    $checkarr = [];
    foreach ($hascheck as $k => $v) {
        if($v['type'] == 'checkbox'){
            $checkarr[$k]['data'] = array();
            $checkarr[$k]['title'] = $v['data'];
            foreach ($v['data'] as $k_d => $v_d) {
                $checkarr[$k]['data'][$k_d] = array('value' => 0, 'name' => $v_d);
            }
        }
    }
    foreach ($yqdklist as $key => $value) {
        $content = unserialize($value['content']);
        foreach ($content as $key2 => $value2) {
            if(yqselect($key2,'type') == 'checkbox'){
                foreach ($value2 as $key3 => $value3) {
                    $inv = intval($value3);
                    $checkarr[$key2]['data'][$inv]['value'] += 1;
                }
            }
        }
    }
    
    $data['data'] = $checkarr ;
    die ( json_encode ( $data ) );
		
}elseif($operation == 'GetBjYqdkList'){
    $start = strtotime(date("Y-m-d",time()));
    $end = strtotime(date("Y-m-d",time())) + 86399;
	if($_GPC['type'] == 'yqdk'){
		// 获取当前年级下的所有班级
		$nowbj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE schoolid = '{$_GPC ['schoolid']}' AND parentid = '{$_GPC['nj_id']}' ORDER BY ssort DESC");
		if(!empty($nowbj)){
			foreach ($nowbj as $key => $value) {
				//查询当前班级所有学生人数
				$studentsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE bj_id = '{$value['sid']}' ");
				//已检测的学生人数
				$mcsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('yqdk')." WHERE bj_id = '{$value['sid']}' AND createtime BETWEEN '{$start}' AND '{$end}' ");
				//未检测是学生人数
				$nomcsum = intval($studentsum) - intval($mcsum);
				$nowbj[$key]['mcsum'] = $mcsum;
				$nowbj[$key]['nomcsum'] = $nomcsum;
			}
		}
		$result['data'] = $nowbj;
		$result['result'] = true;
    }elseif($_GPC['type'] == 'yqtiwen'){
        $nowbj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND parentid = '{$_GPC['nj_id']}' ORDER BY ssort DESC");
		if(!empty($nowbj)){
			foreach ($nowbj as $key => $value) {
				//查询当前班级所有学生人数
				$studentsum = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('students')." WHERE bj_id = '{$value['sid']}' ");
				//体温正常的学生人数
                $normal = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('yqdk')." WHERE bj_id = '{$value['sid']}' AND createtime BETWEEN '{$start}' AND '{$end}' AND tiwen BETWEEN 35.5 and 37.5 ");
				$nonormal = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('yqdk')." WHERE bj_id = '{$value['sid']}' AND createtime BETWEEN '{$start}' AND '{$end}' AND (tiwen <35.5 OR tiwen >37.5)");
				//体温不正常的学生人数
				$noexamine = intval($studentsum) - intval($normal) - intval($nonormal);
				$nowbj[$key]['normal'] = $normal;
				$nowbj[$key]['nonormal'] = $nonormal;
				$nowbj[$key]['noexamine'] = $noexamine;
			}
        }
        $result['data'] = $nowbj;
		$result['result'] = true;
    }
    die ( json_encode ( $result ) );
		
}
?>