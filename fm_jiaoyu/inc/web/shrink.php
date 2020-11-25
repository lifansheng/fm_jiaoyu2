<?php
/*
 * @Discription: 心理咨询管理页
 * @Author: Hannibal·Lee
 * @Date: 2020-05-19 10:46:27
 * @LastEditTime: 2020-06-04 15:15:55
 */
    global $_GPC, $_W;
    $weid              = $_W['uniacid'];
    $action            = 'shrink';
    $this1             = 'no15';
    $GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
    $schoolid          = intval($_GPC['schoolid']);
    $logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
    $tid_global = $_W['tid'];
    $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
    $teacherlist = pdo_fetchall("SELECT t.id,t.tname,s.tid FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('shrink')." as s  ON  s.tid = t.id    WHERE    t.schoolid = '{$schoolid}' and s.tid IS NULL  ");
    if ($operation == 'display') {
        $pindex = max(1, intval($_GPC['page']));
        $psize  = 10;

        $condition = '';
        if(!empty($_GPC['teaname'])){
            $condition = "and t.tname like '%{$_GPC['teaname']}%' ";
        }

        $list = pdo_fetchall("SELECT s.tid,t.tname,t.thumb,t.sex,t.mobile,s.description,s.id FROM ".GetTableName('shrink')." as s LEFT JOIN ".GetTableName('teachers')." as t ON s.tid = t.id  WHERE s.schoolid = '{$schoolid}' {$condition} ORDER BY s.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        // var_dump($list);
        $total = pdo_fetchcolumn("SELECT count(s.id) FROM ".GetTableName('shrink')." as s LEFT JOIN ".GetTableName('teachers')." as t ON s.tid = t.id  WHERE s.schoolid = '{$schoolid}' {$condition}   " );
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/shrink');
    }
    if($operation == 'editshrink'){
        if(!empty($_GPC['id'])){ //编辑
            $check = pdo_fetch("SELECT id,tid FROM ".GetTableName('shrink')." WHERE id = '{$_GPC['id']}' ");
            if(empty($check)){
                $result['status'] = false;
                $result['msg'] = '心理咨询师已被移除或是不存在';
            }else{
                $checktea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$check['tid']}' ");
                if(empty($checktea)){
                    $result['status'] = false;
                    $result['msg'] = '教师资料已被移除或是不存在';
                }else{
                    $desc = $_GPC['description'];
                    pdo_update(GetTableName('shrink',false),array('description'=>$desc),array('id'=>$_GPC['id']));
                    $result['status'] = true;
                    $result['msg'] = '修改咨询师简介成功';
                }
            }
        }else{
            $insertData = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'tid' => $_GPC['tid'],
                'description' => $_GPC['description']
            );
            pdo_insert(GetTableName('shrink',false),$insertData);
            $result['status'] = true;
            $result['msg'] = '新增咨询师成功';
        }
        die(json_encode($result));
    }

    if($operation == "GetShrinkInfo"){
        $id = $_GPC['id'];
        $Info = pdo_fetch("SELECT tid,description FROM ".GetTableName('shrink')." WHERE id = '{$id}' ");
        $teaname = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$Info['tid']}' ");
        $Info['tname'] = $teaname['tname'];
        $result['status'] = true;
        $result['msg'] = '获取咨询师信息成功';
        $result['data'] = $Info;
        die(json_encode($result));
    }

    if($operation == 'delete'){
        $id = $_GPC['id'];
        $CheckShrink = pdo_fetch("SELECT id FROM ".GetTableName('shrink')." WHERE id = '{$id}' ");
        if(!empty($CheckShrink)){
            pdo_delete(GetTableName('shrink',false),array('id'=>$id));
            pdo_delete(GetTableName('psychology',false),array('tid'=>$id));
            $this->imessage('删除成功！', referer(), 'success');
        }else {
            $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
        }
    }
