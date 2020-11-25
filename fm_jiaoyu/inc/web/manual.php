<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-15 11:03:14
 * @LastEditTime: 2020-08-20 09:23:35
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'manual';
$this1             = 'no14';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
mload()->model('manual');
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
//班级
$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
// 期号
$qh = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score'  AND is_show_qh = 1 ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
//评语类型
$commenttype = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'comment'", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
$muban = pdo_fetchall("SELECT id,title FROM " . GetTableName('muban') . " where weid = '{$weid}' AND schoolid ={$schoolid}  ORDER BY id DESC", array(':weid' => $weid,':schoolid' => $schoolid));
$tid_global = $_W['tid'];
$ismaster = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE id = '{$tid_global}' AND status = 2");
if(is_numeric($tid_global) && is_numeric($tid_global) != 0){ //普通老师
    $ismanager = false;
    $tid = $tid_global;
}else{
    $ismanager = true;
    $teachers = pdo_fetchall("SELECT id,tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}'  ORDER BY convert(tname using gbk) ASC");
    $tid = $_GPC['tid'] ? $_GPC['tid'] : $teachers[0]['id'];
}
if($operation == 'display'){ //显示校长填写列表
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    if(!empty($_GPC['xctype'])){
        $condition .= " AND gf.type = '{$_GPC['xctype']}'";
    }
    if(!empty($_GPC['createtime'])){
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime   = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND gf.createtime BETWEEN '{$starttime}' AND '{$endtime}'";
    }else{
        $starttime = strtotime('-30 day');
        $endtime   = TIMESTAMP;
    }
    $list = pdo_fetchall("SELECT gf.*,c.sname FROM " . GetTableName('growupfile') . " gf LEFT JOIN " . GetTableName('classify') . " c on gf.qhid = c.sid  WHERE gf.weid = '{$weid}' AND gf.schoolid = '{$schoolid}' $condition ORDER BY gf.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('growupfile') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){ //删除档案
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete(GetTableName('growupfile',false), array('id' => $id));
    pdo_delete(GetTableName('growuppage',false), array('gid' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'AddMuban'){
    $data = array(
        'weid' => $weid,
        'schoolid' => $schoolid,
        'title' => $_GPC['title'],
        'img' => $_GPC['img'],
        'type' => $_GPC['type'],
        'description' => $_GPC['description'],
    );
    pdo_insert(GetTableName('muban',false),$data);
    $result['status'] = true;
    $result['msg'] = '操作成功';
    die(json_encode($result));
}elseif($operation == 'AddMubanPage'){
    $mubancode=str_replace('{OSSURL}',"{$_W['siteroot']}addons/fm_jiaoyu/",file_get_contents($_W['siteroot'].'addons/fm_jiaoyu/template/mubanpage/'.$_GPC['mobile'].'.html'));
    $data = array(
        'weid' => $weid,
        'schoolid' => $schoolid,
        'title' => $_GPC['title'],
        'mid' => $_GPC['mid'],
        'pc' => 'mubanpage/'.$_GPC['pc'],
        'mobile' => 'mubanpage/'.$_GPC['mobile'],
        'mubancode' => htmlspecialchars($mubancode),
        'thumb' => $_GPC['thumb'],
        'pagetype' => $_GPC['pagetype'],
    );
    pdo_insert(GetTableName('mubanpage',false),$data);
    $result['status'] = true;
    $result['msg'] = '操作成功';
    die(json_encode($result));
}elseif($operation == 'GetMuBan'){
    $res = pdo_fetchall("SELECT title,img,id FROM ".GetTableName('muban')." WHERE schoolid='{$_GPC['schoolid']}' AND weid='{$_GPC['weid']}' AND type = '{$_GPC['type']}'" );
    foreach ($res as $key => $value) {
        $res[$key]['img'] = tomedia($value['img']);
    }
    $result['status'] = true;
    $result['data'] = $res;
    die(json_encode($result));
}elseif($operation == 'LookMuBan'){
    $res = pdo_fetchall("SELECT title,thumb,id FROM ".GetTableName('mubanpage')." WHERE schoolid='{$_GPC['schoolid']}' AND weid='{$_GPC['weid']}' AND mid = '{$_GPC['id']}' ORDER BY ssort ASC " );
    foreach ($res as $key => $value) {
        $res[$key]['thumb'] = tomedia($value['thumb']);
    }
    $result['status'] = true;
    $result['data'] = $res;
    die(json_encode($result));
}elseif($operation == 'MakeMuBan'){
    //档案增加一条数据
    $data = array(
        'weid'=>$_GPC['weid'],
        'schoolid'=>$_GPC['schoolid'],
        'title'=>$_GPC['title'],
        'qhid'=>$_GPC['qh'],
        'is_cose'=>$_GPC['is_cose'],
        'cose'=>$_GPC['cose'],
        'thumb'=>$_GPC['thumb'],
        'poster'=>$_GPC['poster'],
        'audio'=>$_GPC['audio'],
        'type'=>$_GPC['type'],
        'createtime'=>time(),
    );
    pdo_insert(GetTableName('growupfile',false),$data);
    $gid = pdo_insertid();

    $mubanpage = pdo_fetchall("SELECT title,auth,id,ssort,container FROM ".GetTableName('mubanpage')." WHERE schoolid='{$_GPC['schoolid']}' AND weid='{$_GPC['weid']}' AND mid = '{$_GPC['id']}' ORDER BY ssort" );
    foreach ($mubanpage as $key => $value) {
        $pageinfo = array(
            'weid'=>$_GPC['weid'],
            'schoolid'=>$_GPC['schoolid'],
            'title'=>$value['title'],
            'auth'=>$value['auth'],
            'gid'=>$gid,
            'pid'=>$value['id'],
            'content_data'=>$value['container'],
            'ssort'=>$value['ssort'],
            'createtime'=>time(),
        );
        pdo_insert(GetTableName('growuppage',false),$pageinfo);
    }
    $result['status'] = true;
    $result['msg'] = '创建成功';
    $result['gid'] = $gid;
    die(json_encode($result));
}elseif($operation == 'GetGrowUpFile'){
    $id = $_GPC['id'];
    $schoolid = $_GPC['schoolid'];
    $file = pdo_fetch("SELECT * FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' AND id ='{$id}' ");
    $file['pic'] = tomedia($file['thumb']);
    $file['posterimg'] = tomedia($file['poster']);
    $result['data'] = $file;
    die(json_encode($result));
}elseif($operation == 'EditMuBan'){
    if($_GPC['id']){
        $data = array(
            'title'=>$_GPC['title'],
            'qhid'=>$_GPC['qh'],
            'is_cose'=>$_GPC['is_cose'],
            'cose'=>$_GPC['cose'],
            'thumb'=>$_GPC['thumb'],
            'poster'=>$_GPC['poster'],
            'audio'=>$_GPC['audio'],
            'createtime'=>time(),
        );
        pdo_update(GetTableName('growupfile',false),$data,array('id'=>$_GPC['id']));
        $result['msg'] = '修改成功';
    }else{
        $result['msg'] = '修改失败';
    }
    die(json_encode($result));
}elseif($operation == 'Enable'){
    $growup = pdo_fetch("SELECT id,bjarr FROM ".GetTableName('growupfile')." WHERE id = '{$_GPC['id']}'" );
    //获取已启用的班级
    $Enable = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE FIND_IN_SET(sid,'{$growup['bjarr']}') ");
    $AbEnable = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid ='{$_GPC['schoolid']}' AND type = 'theclass' AND NOT FIND_IN_SET(sid,'{$growup['bjarr']}') ORDER BY ssort DESC");
    $data['Enable'] = $Enable;
    $data['AbEnable'] = $AbEnable;
    $result['status'] = true;
    $result['data'] = $data;
    die(json_encode($result));
}elseif($operation == 'AddEnable'){
    //查出已启动的班级在追加班级进去
    $growup = pdo_fetch("SELECT bjarr FROM ".GetTableName('growupfile')." WHERE id = '{$_GPC['id']}'" );
    $bjstr = trim($growup['bjarr'].','.$_GPC['bjarr'],',');
    pdo_update(GetTableName('growupfile',false),array('bjarr'=>$bjstr,'is_use'=>1),array('id'=>$_GPC['id']));
    //获取档案页面
    $growuppage = pdo_fetchAll("SELECT * FROM ".GetTableName('growuppage')." WHERE gid = '{$_GPC['id']}' AND status = 0" );
    foreach ($growuppage as $key => $value) {
        $bj = trim($_GPC['bjarr'],',');
        //获取班级学生
        $students = pdo_fetchall("SELECT id,bj_id FROM ".GetTableName('students')." WHERE FIND_IN_SET(bj_id,'{$bj}') ");
        foreach ($students as $ks => $vs) {
            $data = array(
                'weid' => $value['weid'],
                'schoolid' => $value['schoolid'],
                'title' => $value['title'],
                'auth' => $value['auth'],
                'isok' => $value['auth'] == 1 ? 1 : 0,
                'sid' => $vs['id'],
                'gid' => $value['gid'],
                'pid' => $value['pid'],
                'bjid' => $vs['bj_id'],
                'status' => 1,
                'ssort' => $value['ssort'],
                'createtime' => $value['createtime'],
                'content_html' => $value['content_html'],
                'content_data' => $value['content_data'],
            );
            pdo_insert(GetTableName('growuppage',false),$data);
        }
    }
    $result['status'] = true;
    $result['msg'] = '设置完成';
    die(json_encode($result));
}elseif($operation == 'look'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    if(!empty($_GPC['bjid'])){
        $bjid = $_GPC['bjid'];
    }else{
        $first = pdo_fetch("SELECT bjid FROM ".GetTableName('growuppage')." WHERE id = '{$_GPC['id']}' AND bjid !=0 ");
        $bjid = $first['bjid'];  
    }
    $condition .= " AND g.bjid = '{$bjid}'";
    
    if(!empty($_GPC['sname'])){
        $condition .= " AND s.s_name like '%{$_GPC['sname']}'";
    }
    $bjinfo = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$bjid}' ");
    $list = pdo_fetchall("SELECT g.pid,g.isallok,g.bjid,g.sid,g.gid,g.auth,s.s_name,s.mobile,IFNULL(SUM(CASE WHEN g.auth = 1 AND g.isok = 1 then 1 else 0 end),0 ) as yzok,IFNULL(SUM(CASE WHEN g.auth = 1 then 1 else 0 end),0 ) as yznum,IFNULL(SUM(CASE WHEN g.auth = 2 AND g.isok = 1 then 1 else 0 end),0 ) as jsok,IFNULL(SUM(CASE WHEN g.auth = 2 then 1 else 0 end),0 ) as jsnum,IFNULL(SUM(CASE WHEN g.auth = 3 AND g.isok = 1 then 1 else 0 end),0 ) as jzok,IFNULL(SUM(CASE WHEN g.auth = 3 then 1 else 0 end),0 ) as jznum FROM ".GetTableName('growuppage')." g LEFT JOIN " . GetTableName('students') . " s on s.id = g.sid WHERE s.schoolid = '{$schoolid}' AND g.gid = '{$_GPC['id']}' $condition GROUP BY g.sid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . GetTableName('students') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND bj_id = '{$bjid}'");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'AddCommentType'){
    $data = array(
        'weid'=>$weid,
        'schoolid'=>$_GPC['schoolid'],
        'sname'=>$_GPC['sname'],
        'type'=>'comment',
    );
    pdo_insert(GetTableName('classify',false),$data);
    $result['status'] = true;
    $result['msg'] = '添加成功';
    die(json_encode($result));
}elseif($operation == 'AddComment'){
    $data = array(
        'weid'       => $weid,
        'schoolid'   => $_GPC['schoolid'],
        'title'      => trim($_GPC['comment']),
        'sid'      => trim($_GPC['commentid']),
        'ssort'      => intval($_GPC['ssort']),
        'createtime' => time()
    );
    pdo_insert($this->table_scpy, $data);
    $result['status'] = true;
    $result['msg'] = '添加成功';
    die(json_encode($result));
}elseif($operation == 'teawrite'){
    mload()->model('tea');
    $bjlist = get_myskbj($tid);
    $bjidstr = arrayToString($bjlist);
    $list = pdo_fetchall("SELECT gid,bjid,createtime FROM ".GetTableName('growuppage')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(bjid,'{$bjidstr}') GROUP BY bjid,gid");
    foreach ($list as $key => $value) {
        //获取档案基本信息
        $file = pdo_fetch("SELECT * FROM ".GetTableName('growupfile')." WHERE id = '{$value['gid']}'");
        //获取班级信息
        $class = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$value['bjid']}'");
        //已发送人数
        $sendnum = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('growuppage')." WHERE gid = '{$value['gid']}' AND bjid = '{$value['bjid']}' AND auth != 1 AND is_send = 1 ");
        //总共需要发送人数
        $sendcount = pdo_fetchcolumn("SELECT count(DISTINCT sid) FROM ".GetTableName('growuppage')." WHERE gid = '{$value['gid']}' AND bjid = '{$value['bjid']}' AND auth != 1");
        //获取购买人数
        $ordernum = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('order')." WHERE extra_info = '{$value['gid']}' AND status = 2 AND type = 20 ");
        $list[$key]['id'] = $value['gid'];
        $list[$key]['createtime'] = date("Y-m-d H:i:s",$value['createtime']);
        $list[$key]['title'] = $file['title'];
        $list[$key]['bjname'] = $class['sname'];
        $list[$key]['ordernum'] = $ordernum;
        $list[$key]['sendnum'] = $sendnum;
        $list[$key]['sendcount'] = $sendcount;
        $list[$key]['type'] = $file['type'] == 1 ? '成长手册': '毕业纪念册';
    }
}elseif($operation == 'ChangePDF'){
    pdo_update(GetTableName('growuppage',false),array('isallok'=>1),array('gid'=>$_GPC['gid'],'sid'=>$_GPC['sid']));
    $item = GetStuManualPage($_GPC['schoolid'], $_GPC['sid'], $_GPC['gid']);
    $result['data'] = $item;
    die(json_encode($result));
}elseif($operation == 'MakePDF'){
    $pid = pdo_fetch("SELECT pid FROM ".GetTableName('growuppage')." WHERE id = '{$_GPC['pageid']}'")['pid'];
    $bgimg = pdo_fetch("SELECT bgimg FROM ".GetTableName('mubanpage')." WHERE id = '{$pid}'")['bgimg'];
    $data = $_GPC['SinglePageItemList'];
    $pic = setComPoster(tomedia($bgimg),$data);
    $qrcode_name = 'images/fm_jiaoyu/'.random(30).'.png';
    $filename = IA_ROOT .'/attachment/'. $qrcode_name;
    $path = "images/fm_jiaoyu/img/";
    if (file_exists($picurl)) {
        unlink($picurl);
    }
    
    $PosterQrcode = createComPoster($pic,$filename);
    // var_dump($_GPC['pageid']);
    // die;
    // return $PosterQrcode;
    pdo_update(GetTableName('growuppage',false),array('pdfimg'=>$PosterQrcode,'pdfimgurl'=>$qrcode_name),array('id'=>$_GPC['pageid']));
    $result['result'] = true;
    die(json_encode($result));
}elseif($operation == 'CreatePDFFile'){
    $page = pdo_fetchall("SELECT * FROM ".GetTableName('growuppage')." WHERE schoolid = '{$_GPC['schoolid']}' AND sid = '{$_GPC['sid']}' AND gid = '{$_GPC['gid']}' ORDER BY ssort");
    $file = pdo_fetch("SELECT * FROM ".GetTableName('growupfile')." WHERE schoolid = '{$_GPC['schoolid']}' AND id = '{$_GPC['gid']}' ");
    $student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE schoolid = '{$_GPC['schoolid']}' AND id = '{$_GPC['sid']}' ");
    $sch = pdo_fetch("SELECT title FROM ".GetTableName('index')." WHERE id = '{$_GPC['schoolid']}'");

    // $content = array_column($page,'pdfimgurl');
    $content = array_column($page,'pdfimg');
    $txtname = "{$student['s_name']}的{$file['title']}.pdf";
    $txtpath_name = $_SERVER['DOCUMENT_ROOT'] . '/attachment/down/'.$_GPC['sid'].$_GPC['gid'].'.pdf';
    $savepath = 'down/'.$_GPC['sid'].$_GPC['gid'].'.pdf';
    $info =  array(
            'user'=>"{$sch['title']}" ,
            'title'=>"{$student['s_name']}的{$file['title']}" ,
            'keywords'=>$file['type'] == 1 ? '成长手册' : '毕业纪念册' ,
            'content'=>$content,
            'path'=>$txtpath_name,
            'filename' => $txtname
        );
    $PDF = new pdf();
    $aa = $PDF->createPDF($info);
    // var_dump($aa);die;
    $res = pdo_update(GetTableName('growuppage',false),array('pdffile'=>$savepath),array('sid'=>$_GPC['sid'],'gid'=>$_GPC['gid']));
    $result['info'] = $res;
    die(json_encode($result));
}elseif($operation == 'PDFDown'){
    DownloadFileNoDel($_GPC['schoolid'],$_GPC['sid'],$_GPC['gid']);
    die;
}
include $this->template('web/manual');
?>