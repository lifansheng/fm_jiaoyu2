<?php
    global $_W, $_GPC;
    $operation = in_array ( $_GPC ['op'], array ('default', 'addShareRecord','addForm') ) ? $_GPC ['op'] : 'default';
    if ($operation == 'default') {
        die ( json_encode ( array (
                'result' => false,
                'msg' => '参数错误'
            ) ) );
    }
    if ($operation == 'addShareRecord') {
        if (!$_GPC ['schoolid']) {
            die (json_encode(array('result' => false, 'msg' => '非法请求')));
        }
        if($_GPC['type'] == 'sp'){ // 专题分享
            $condition = " AND spid = '{$_GPC['id']}' AND fxzopenid = '{$_GPC['fxzopenid']}'";
        }else{ //课程分享
            $condition = " AND kcid = '{$_GPC['id']}' AND fxzopenid = '{$_GPC['fxzopenid']}'";
        }
        
        $item = pdo_fetch("SELECT id,shtime FROM ".GetTableName('sharerecord')." WHERE schoolid = '{$_GPC['schoolid']}' $condition ");
        if($item){
            $data = array(
                'shtime' => $item['shtime'] + 1,
            );
            pdo_update(GetTableName('sharerecord',false),$data,array('id'=>$item['id']));
        }else{
            $data = array(
                'weid' => $_GPC['weid'],
                'schoolid' => $_GPC['schoolid'],
                'spid' => $_GPC['type'] == 'sp' ? $_GPC['id'] : 0,
                'kcid' => $_GPC['type'] == 'kc' ? $_GPC['id'] : 0,
                'fxzopenid' => $_GPC['fxzopenid'],
                'type' => $_GPC['type'],
                'shtime' => 1,
                'createtime' => time(),
            );
            pdo_insert(GetTableName('sharerecord',false),$data);
        }
        $result['result'] = true; 
        $result['msg'] = '分享成功!';
        die (json_encode($result));
    }
    if ($operation == 'addForm'){
        $item = pdo_fetch("SELECT content FROM " . GetTableName('special') . " where :id = id", array(':id' => $_GPC['id']));
        $content = json_decode($item['content'],true);
        $insertData = [];
        foreach ($content as $key => $value) {
            if($value['type'] == 5){
                foreach ($value['formData'] as $k => $v) {
                    $insertData[$k]['title'][] = $_GPC["{$v['type']}".$k.'title'];
                    $insertData[$k]['content'][] = $_GPC["{$v['type']}".$k];
                }
            }
        }
        $data = array(
            'schoolid' => $_GPC['schoolid'],
            'weid' => $_GPC['weid'],
            'spid' => $_GPC['id'],
            'openid' => $_GPC['openid'],
            'content' => serialize($insertData),
            'createtime' => time(),
        );
        pdo_insert(GetTableName('saleform',false),$data);
        $result['result'] = true; 
        $result['msg'] = '提交完成,请勿重新提交!';
        die (json_encode($result));
    }
?>