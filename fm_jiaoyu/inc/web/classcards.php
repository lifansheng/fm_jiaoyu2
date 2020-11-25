<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'classcards';
$this1             = 'no10';
//var_dump($_W);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,gonggao FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$schooltype = $_W['schooltype'];

mload()->model('wt');

/*
mload()->model('wt');
$res = GetToken($schoolid,$weid,time());
var_dump($res);
die();*/

if(!empty($_W['setting']['remote']['type'])){
    $urls = $_W['attachurl'];
}else{
    $urls = $_W['siteroot'] . 'attachment/';
}
if($operation == 'display'){
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_mac) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC");
    foreach($list as $key => $row){
        $macset  = unserialize($row['macset']);  
    }
}elseif($operation == 'post'){
    $class = pdo_fetchall("SELECT sid as id, sname as classes, schoolid as sid, ssort as score, tid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$schoolid} ORDER BY ssort DESC");
    $id     = intval($_GPC['id']);
    $item   = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_mac) . " WHERE id = {$id} ");
     $bg = $item['bg'];
    $bg1 = $item['bg1'];
    $set    = unserialize($item['macset']);
    if(checksubmit('submit')){
        $bgarr = iserializer($_GPC['bg']);
        $bg1arr = iserializer($_GPC['bg1']);
        $data = array(
            'weid'       => $weid,
            'schoolid'   => $schoolid,
            'name'       => $_GPC['name'],
			'macid'       => trim($_GPC['macid']),
            'bj_id'		 =>intval($_GPC['bj_id']),
            'bg' => trim($_GPC['bg']),
            'bg1' => trim($_GPC['bg1']),
			'createtime' => time()
        );
		$temp = array(
            'welcome'      => $_GPC['welcome'],
            'password'      => $_GPC['password'],
            'starttime'      => $_GPC['starttime'],
            'shutdowntime'      => $_GPC['shutdowntime']
			
		);
		$data['macset'] = serialize($temp);
        
		
        if(!empty($id)){
            unset($data['createtime']);
            $data['lastedittime'] = time();
            pdo_update($this->table_classcard_mac, $data, array('id' => $id));
            
        }else{
            
                $mactype = 5;
                $posturl = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&m=fm_jiaoyu';
                
                $addmac = opreatmac($data['macid'],$mactype,$posturl,'add',$logo['title']);
			
                $respoed = json_decode($addmac,true);
				$respoed['result'] =1;
                if($respoed['result'] !=0){
                    if($respoed['result'] == 1){
                        $data['lastedittime'] = time();
                        pdo_insert($this->table_classcard_mac, $data);
                        
                    }
                    if($respoed['result'] == 4){
                        $this->imessage($respoed['info'], $this->createWebUrl('classcards', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
                    }
                }else{
                    $this->imessage($respoed['info'], $this->createWebUrl('classcards', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
                }
          
        }
        $resMsg = '操作成功！';
        if(!empty($back_msg)){
            $urlMsg .= '<br/>附加信息：<br/>';
            $urlMsg .= $back_msg;
        }
        $this->imessage('操作成功！', $this->createWebUrl('classcards', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'posta'){
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id,exam_plan FROM " . tablename($this->table_classcard_mac) . " WHERE id = '{$id}' ");
    $exam_plan = unserialize($item['exam_plan']);
    $lastkey = count($exam_plan) +1;
    $allkm = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'subject' And schoolid = {$schoolid} ORDER BY ssort DESC");
    $allteacher = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = {$schoolid} ");
    if(checksubmit('submit')){
        if(!empty($_GPC['new'])){
            $exam = array();
            //print_r($_GPC['exam_course']);
            $i = 0;
            foreach($_GPC['new'] as $key =>$val){
                $data[$i]['exam_course'] = $_GPC['exam_course'][$key];
                $data[$i]['exam_start_time'] = $_GPC['exam_start_time'][$key];
                $data[$i]['exam_end_time'] = $_GPC['exam_end_time'][$key];
                $data[$i]['exam_teacher1'] = $_GPC['exam_teacher1'][$key];
                $data[$i]['exam_teacher2'] = $_GPC['exam_teacher2'][$key];
                $i++;
            }

            $exam = serialize($data);
            pdo_update($this->table_classcard_mac, array('exam_plan' => $exam,'lastedittime'=>time()), array('id' => $id));
        }
        $this->imessage('操作成功！', $this->createWebUrl('classcards', array('op' => 'posta','id' => $id, 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id,macid FROM " . tablename($this->table_classcard_mac) . " WHERE id = '{$id}' ");
    if(empty($item)){
        $this->imessage('抱歉，不存在或是已经被删除！', $this->createWebUrl('classcards', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    $mactype = 5;
        opreatmac($item['macid'],$mactype,$posturl,'del',$logo['title']);
        pdo_delete($this->table_classcard_mac, array('id' => $id));
    

    $this->imessage('删除成功！', $this->createWebUrl('classcards', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'change'){
    $id    = intval($_GPC['id']);
    $is_on = intval($_GPC['is_on']);
    $data = array('is_on' => $is_on);
    $data['lastedittime'] = time();
	print_r($data);
    pdo_update($this->table_classcard_mac, $data, array('id' => $id));
	$this->imessage('请求方式不存在');
}else{
    $this->imessage('请求方式不存在');
}
include $this->template('web/classcards');
?>