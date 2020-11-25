<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-03-17 16:39:35
 * @LastEditTime: 2020-08-20 10:19:17
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$this1             = 'no14';
$action            = 'manualmuban';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
mload()->model('photo');
$bjid =  $_GPC['bjid'];
$logo = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");

//获取当前班级的学生
$manual = pdo_fetch("SELECT title FROM ".GetTableName('growupfile')." WHERE id = '{$_GPC['id']}' ")['title'];
$bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['bjid']}' ")['sname'];
$title = $manual.' - '.$bjname;
$students = pdo_fetchall("SELECT s.id,s.s_name, IFNULL(SUM(case when (g.auth !=1 AND g.isok = 1) then 1 else 0 end),0) as wcnum, IFNULL(SUM(case when g.auth !=1 then 1 else 0 end),0) as allnum FROM ".GetTableName('students')." s LEFT JOIN ". GetTableName('growuppage') ." g on g.sid = s.id WHERE s.schoolid = '{$schoolid}' AND s.bj_id = '{$bjid}' AND g.gid = '{$_GPC['id']}' AND g.is_send = 0 GROUP BY sid ORDER BY id DESC");
if($_GPC['op'] == 'display'){
    $bjxc = GetPhotoType($weid,$schoolid,$bjid,2,'',true);
    foreach ($bjxc as $key => $value) {
        $bjxc[$key]['type'] = 2;
    }
    $grxc = GetPhotoType($weid,$schoolid,$_GPC['bjid'],1,'',true);
    foreach ($grxc as $key => $value) {
        $grxc[$key]['type'] = 1;
    }
    $xc = array_merge($bjxc,$grxc);
    array_pop($xc);
    include $this->template('web/manualbjgrowpage');
}elseif($_GPC['op'] == 'GetBjXc'){ //获取班级相册
    $bjxc = GetPhotoType($weid,$schoolid,$_GPC['bjid'],2,'',true);
    foreach ($bjxc as $key => $value) {
        $bjxc[$key]['type'] = 2;
    }
    $grxc = GetPhotoType($weid,$schoolid,$_GPC['bjid'],1,'',true);
    foreach ($grxc as $key => $value) {
        $grxc[$key]['type'] = 1;
    }
    $xc = array_merge($bjxc,$grxc);
    array_pop($xc);
    $result['result'] = true;
    $result['data'] = $xc;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetXcImg'){ //获取相册中的图片
    $bjid = $_GPC['bjid'];
    $condition .= " AND bj_id1 = '{$bjid}' AND is_video = '{$_GPC['is_video']}' ";
    if($_GPC['type'] == 2){
        $condition .= " AND type = '{$_GPC['type']}' AND sid = 0 AND ctype = '{$_GPC['ctype']}'";
    }elseif($_GPC['type'] == 1){
        if($_GPC['ctype'] == 0){
            $condition .= " AND type = '{$_GPC['type']}' AND sid != 0 AND ctype = '{$_GPC['ctype']}' ";
        }else{
            $condition .= " AND type = '{$_GPC['type']}' AND ctype = '{$_GPC['ctype']}' ";
        }
    }
    $list = pdo_fetchall("SELECT id,picurl,is_video,video,videoqrcode FROM " . tablename($this->table_media) . " where schoolid = '{$_GPC['schoolid']}' $condition ORDER BY id DESC ");

    foreach ($list as $key => $value) {
        mload()->model('kc');
        $list[$key]['img'] = tomedia($value['picurl']);
        $list[$key]['picurl'] = $value['is_video'] == 1 ? PosterQrcode($value['video']) : $value['picurl'];
        if($value['is_video'] == 1){
            $list[$key]['video'] = tomedia($value['video']);
        }
    }
    
    $result['result'] = true;
    $result['data'] = $list;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetXcVideo'){ //获取相册中的视频
    $bjid = $_GPC['bjid'];
    $condition .= " AND bj_id1 = '{$bjid}' AND is_video = '{$_GPC['is_video']}' ";
    if($_GPC['type'] == 2){
        $condition .= " AND type = '{$_GPC['type']}' AND sid = 0 AND ctype = '{$_GPC['ctype']}'";
    }elseif($_GPC['type'] == 1){
        if($_GPC['ctype'] == 0){
            $condition .= " AND type = '{$_GPC['type']}' AND sid != 0 AND ctype = '{$_GPC['ctype']}' ";
        }else{
            $condition .= " AND type = '{$_GPC['type']}' AND ctype = '{$_GPC['ctype']}' ";
        }
    }
    $list = pdo_fetchall("SELECT id,picurl,is_video,video,videoqrcode FROM " . tablename($this->table_media) . " where schoolid = '{$_GPC['schoolid']}' $condition ORDER BY id DESC ");

    foreach ($list as $key => $value) {
        mload()->model('kc');
        $list[$key]['img'] = tomedia($value['picurl']);
        $list[$key]['picurl'] = $value['is_video'] == 1 ? PosterQrcode($value['video']) : $value['picurl'];
        if($value['is_video'] == 1){
            $list[$key]['video'] = tomedia($value['video']);
        }
    }
    
    $result['result'] = true;
    $result['data'] = $list;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetComment'){ //获取评语库分类
    $sid = $_GPC['typeid'];
    $list = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = '{$_GPC['schoolid']}' AND FIND_IN_SET(sid,'{$sid}') ORDER BY sid DESC ");
    $result['result'] = true;
    $result['data'] = $list;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetCommentTxt'){ //获取评语内容
    $sid = $_GPC['sid'];
    $list = pdo_fetchall("SELECT id,title FROM " . GetTableName('shoucepyk') . " where schoolid = '{$_GPC['schoolid']}' AND sid='{$sid}' ORDER BY ssort DESC ");
    $result['result'] = true;
    $result['data'] = $list;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetMuBanType'){ //获取模板类型
    $list = pdo_fetchall("SELECT id,title FROM " . GetTableName('muban') . " where schoolid = '{$_GPC['schoolid']}' ORDER BY id DESC ");
    $result['result'] = true;
    $result['data'] = $list;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetMuBanPage'){ //获取模板页面
    $list = pdo_fetchall("SELECT id,title,thumb FROM " . GetTableName('mubanpage') . " where schoolid = '{$_GPC['schoolid']}' AND mid = '{$_GPC['id']}' ORDER BY ssort ");
    foreach ($list as $key => $value) {
        $list[$key]['thumb'] = tomedia($value['thumb']);
    }
    $result['result'] = true;
    $result['data'] = $list;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetPageData'){ //获取page页面详细数据
    $id = arrayToString($_GPC['id']);
    $list = pdo_fetchall("SELECT * FROM " . GetTableName('mubanpage') . " where schoolid = '{$_GPC['schoolid']}' AND FIND_IN_SET(id,'{$id}') ORDER BY ssort ");
    foreach ($list as $key => $value) {
        $item[$key]['pid'] = intval($value['id']);
        $item[$key]['auth'] = intval($value['auth']);
        $item[$key]['title'] = $value['title'];
        $item[$key]['thumb'] = $value['thumb'];
        $item[$key]['smallimg'] = tomedia($value['thumb']);
        $item[$key]['order'] = $value['ssort'] == 0 ? 1 : intval($value['ssort']);
        $item[$key]['tagid'] = intval($id.$value['ssort']);
        $item[$key]['IsStart']  = false;
        $item[$key]['IsEnd']  = false;
        $item[$key]['data']['backimgurl']  = tomedia($value['bgimg']);
        $item[$key]['data']['pageData']  = json_decode($value['container'],true);
    }
    $result['result'] = true;
    $result['data'] = $item;
    die(json_encode($result));
}elseif($_GPC['op'] == 'uploadPhotos'){ //上传相册图片
    $photoarr = $_GPC['photoarr'];
    $type = $_GPC['type'];
    $id = $_GPC['xcid'];
    $bj_id = $_GPC['bj_id'];
    $ctype = $_GPC['ctype'];
    if(!empty($photoarr)){
        foreach( $photoarr as $key => $value )
        {
            $data = array(
                'weid' =>  $_GPC['weid'],
                'schoolid' => $_GPC['schoolid'],
                'picurl' => $value,
                'bj_id1' =>  $_GPC['bj_id'],
                'uid' => $_W['tid'],
                'ctype' => $_GPC['ctype'],
                'order'=>$key+1,
                'createtime' => time(),
                'type'=> $_GPC['type'],
            );
            pdo_insert($this->table_media, $data);
        }
    }
    $result['result'] = true;
    $result['msg'] = '上传成功';
    die(json_encode($result));
}elseif($_GPC['op'] == 'uploadVideo'){ //上传相册图片
    mload()->model('kc');
    $qrcode = PosterQrcode(tomedia($_GPC['video']));
    $vdata = array(
        'weid' =>  $_GPC['weid'],
        'schoolid' => $_GPC['schoolid'],
        'bj_id1' => $_GPC['bj_id'],
        'uid' => $tid_global,
        'ctype' => $_GPC['ctype'],
        'video'=>$_GPC['video'],
        'videoqrcode'=>$qrcode,
        'picurl'=>$_GPC['videoimg'],
        'is_video'=>1,
        'createtime' => time(),
        'type'=>$_GPC['type'],
    );
    pdo_insert($this->table_media, $vdata);
    $result['result'] = true;
    $result['msg'] = '上传成功';
    die(json_encode($result));
}elseif($_GPC['op'] == 'SavePage'){
    $PageList = $_GPC['PageList'];
    foreach ($PageList as $key => $value) {
        //判断是否有修改权限
        $checkauth = pdo_fetch("SELECT auth,ssort,id FROM ".GetTableName('growuppage')." WHERE schoolid = '{$schoolid}' AND bjid = '{$value['bjid']}' AND gid = '{$value['gid']}' AND sid = '{$value['sid']}' AND ssort = '{$value['order']}' ");
        if($checkauth['auth'] != $value['auth']){
            $res = pdo_update(GetTableName('growuppage',false),array('auth'=>$value['auth']),array('bjid'=>$value['bjid'],'gid'=>$value['gid'],'ssort'=>$value['order']));
        }
        $data = array(
            'content_data' => json_encode($value['data']['pageData']),
            'auth' => $value['auth'],
            'isok' => $value['is_ok'] === 'true' ? 1 : 0,
            'ssort' => $value['order'],
        );
        pdo_update(GetTableName('growuppage',false),$data,array('id'=>$value['id']));
    }
    $result['result'] = true;
    $result['msg'] = '保存成功';
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetStuPage'){
    mload()->model('manual');
    $item = GetStuManualPage($schoolid, $_GPC['id'], $_GPC['gid'], 2);
    $IsShowSave = pdo_fetch("SELECT pdffile FROM ".GetTableName('growuppage')." WHERE schoolid = '{$schoolid}' AND sid = '{$_GPC['id']}' AND gid = '{$_GPC['gid']}' ")['pdffile'];
    $result['IsShowSave'] = $IsShowSave;
    $result['data'] = $item;
    die(json_encode($result));
}elseif($_GPC['op'] == 'Send'){
    //获取需要发送的对象
    $list = pdo_fetchall("SELECT * FROM ".GetTableName('growuppage')." WHERE schoolid = '{$_GPC['schoolid']}' AND bjid = '{$_GPC['bjid']}' AND gid = '{$_GPC['gid']}' AND is_send = 0 AND isok = 1 AND auth = 2 GROUP BY sid ORDER BY id DESC ");
    foreach ($list as $key => $value) {
        // 修改发送状态 
        pdo_update(GetTableName('growuppage',false),array('is_send'=>1),array('gid'=>$_GPC['gid'],'sid'=>$value['sid']));
        //发送模板
        $this->sendMobileManual($value['sid'],$_GPC['gid'], $_GPC['schoolid'], $_GPC['weid']);
    }
    $result['data'] = $send;
    $result['result'] = true;
    $result['msg'] = '发送成功';
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetIsSend'){
    $students = pdo_fetchall("SELECT s.id,s.s_name, IFNULL(SUM(case when (g.auth !=1 AND g.isok = 1) then 1 else 0 end),0) as wcnum, IFNULL(SUM(case when g.auth !=1 then 1 else 0 end),0) as allnum FROM ".GetTableName('students')." s LEFT JOIN ". GetTableName('growuppage') ." g on g.sid = s.id WHERE s.schoolid = '{$_GPC['schoolid']}' AND s.bj_id = '{$_GPC['bjid']}' AND g.gid = '{$_GPC['gid']}' AND g.is_send = '{$_GPC['is_send']}' GROUP BY sid ORDER BY id DESC");
    $result['data'] = $students;
    die(json_encode($result));
}