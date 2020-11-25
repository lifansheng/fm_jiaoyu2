<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-03-17 16:39:35
 * @LastEditTime: 2020-10-20 07:20:33
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$this1             = 'no14';
$action            = 'manualmuban';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
mload()->model('photo');
$logo = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");

$bjlist = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type='theclass' AND is_over != 2 ORDER BY ssort DESC");
if($_GPC['bjid']){
   $bjid =  $_GPC['bjid'];
}else{
   $bjid =  $bjlist[0]['sid'];
}
$school = pdo_fetch("SELECT spic,style2,title,qroce,logo FROM " . tablename($this->table_index) . " where id=:id", array(':id' => $schoolid));

if($_GPC['op'] == 'display'){
    $schoolid          = intval($_GPC['schoolid']);
    $title = pdo_fetch("SELECT title FROM ".GetTableName('growupfile')." WHERE id = '{$_GPC['id']}' ")['title'];
    $list = pdo_fetchall("SELECT * FROM ".GetTableName('growuppage')." WHERE gid = 1 AND schoolid = '{$schoolid}' ");
    foreach ($list as $key => $value) {
        $mubanpage = pdo_fetch("SELECT * FROM ".GetTableName('mubanpage')." WHERE id = '{$value['pid']}' ");
        $list['bgimg'] = tomedia($mubanpage['bgimg']);
    }
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
    include $this->template('web/manualgrowpage');
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
        $list[$key]['picurl'] = $value['is_video'] == 1 ? PosterQrcode(tomedia($value['video'])) : $value['picurl'];
        if($value['is_video'] == 1){
            $list[$key]['video'] = tomedia($value['video']);
        }
    }
    
    $result['result'] = true;
    $result['data'] = $list;
    die(json_encode($result));
}elseif($_GPC['op'] == 'GetXcVideo'){ //获取相册中的图片
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
        $list[$key]['picurl'] = $value['is_video'] == 1 ? PosterQrcode(tomedia($value['video'])) : $value['picurl'];
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
}elseif($_GPC['op'] == 'GetFirstPage'){
    if($_GPC['id']){
        $list = pdo_fetchAll("SELECT g.content_data,g.ssort as gssort,g.auth as gauth,g.id as nowid,g.content_data as gcontainer,m.* FROM ".GetTableName('growuppage')." as g LEFT JOIN " . GetTableName('mubanpage') . " as m on m.id = g.pid WHERE g.gid = '{$_GPC['id']}' AND g.sid=0 ORDER BY g.ssort ");
        foreach ($list as $key => $value) {
            $item[$key]['id'] = intval($value['nowid']);
            $item[$key]['auth'] = intval($value['gauth']);
            $item[$key]['thumb'] = $value['thumb'];
            $item[$key]['title'] = $value['title'];
            $item[$key]['smallimg'] = tomedia($value['thumb']);
            $item[$key]['order'] = intval($value['gssort']);
            $item[$key]['tagid'] = intval($_GPC['id'].$value['gssort']);
            $item[$key]['IsStart'] = $value['pagetype'] == 1 ? true : false;
            $item[$key]['IsEnd'] = $value['pagetype'] == 2 ? true : false;
            $item[$key]['data']['backimgurl'] = tomedia($value['bgimg']);
            $item[$key]['data']['pageData'] = json_decode($value['gcontainer'],true);
        }
        $result['result'] = true;
        $result['data'] = $item;
        $result['msg'] = '获取成功';
    }else{
        $result['result'] = false;
        $result['msg'] = '新增没有数据';
    }
    die(json_encode($result));
}elseif($_GPC['op'] == 'SavePage'){
    $PageList = $_GPC['PageList'];
    foreach ($_GPC['DelArrId'] as $v) {
        pdo_delete(GetTableName('growuppage',false),array('id'=>$v));
    } 
    foreach ($PageList as $key => $value) {
        
        if($value['id']){ //修改或删除数据
            $data = array(
                'content_data' => json_encode($value['data']['pageData']),
                'auth' => $value['auth'],
                'ssort' => $value['order'],
            );
            pdo_update(GetTableName('growuppage',false),$data,array('id'=>$value['id']));
        }else{
            $data = array(
                'weid' => $_GPC['weid'],
                'schoolid' => $_GPC['schoolid'],
                'auth' => $value['auth'],
                'isok' => $value['auth']==1 ? 1 : 0,
                'title' => $value['title'],
                'pid' => $value['pid'],
                'gid' => $_GPC['gid'],
                'ssort' => $value['order'],
                'createtime' => time(),
                'content_data' => json_encode($value['data']['pageData']),
            );
            pdo_insert(GetTableName('growuppage',false),$data);
        }
    }
    $result['result'] = true;
    $result['msg'] = '保存成功';
    die(json_encode($result));
}