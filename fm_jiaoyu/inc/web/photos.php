<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'photos';
$this1             = 'no3';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school            = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ORDER BY ssort DESC", array(':id' => $schoolid));

$bj     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort ASC LIMIT 0,1 ", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type AND is_over = :is_over ORDER BY CONVERT(sname USING gbk) ASC  ", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid, ':is_over'=>1));
$kclist = pdo_fetchall("select id,name FROM ".tablename('wx_school_tcourse')." WHERE schoolid = '{$schoolid}' ORDER BY ssort DESC ");

$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));

$xcname = $_W['schooltype'] ? '公共相册': '班级相册';
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1001601,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if($operation == 'post'){
    $id = intval($_GPC['id']);
    // if (!empty($id)) {
    // $item = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " WHERE id = :id ", array(':id' => $id));
    // if (empty($item)) {
    // message('抱歉，本条信息不存在在或是已经删除！', '', 'error');
    // }
    // }

    $students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where id = :id", array(':id' => $id));
    $list1    = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 And sid = '{$id}' ORDER BY id DESC");
    $classify = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $students['bj_id']));
}elseif($operation == 'uploadPhotos'){
	$ack = 0 ;
	$bj_id= $_GPC['bj_up'];
	$photoarr = $_GPC['photoarr'];
	foreach( $photoarr as $key => $value )
	{
	 	$data = array(
			'weid' =>  $weid,
			'schoolid' => $schoolid,
			'picurl' => $value,
			'bj_id1' => $bj_id,
			'order'=>$key+1,
			'createtime' => time(),
			'type'=>2,
	   	);
	   	if(pdo_insert($this->table_media, $data)){
		   		$ack = 1;
	   	}
	   
	}
	$data['result'] = $ack;
	$data['msg'] = '上传成功！';
	die(json_encode($data));	
}elseif($operation == 'display'){

    $pindex = max(1, intval($_GPC['page']));
    $psize  = 10;
    //$condition = " And (bj_id1 = '{$bj['sid']}' or bj_id2 = '{$bj['sid']}' or bj_id3 = '{$bj['sid']}')";
    if(!empty($_GPC['bj_id'])){
        $bj_id     = intval($_GPC['bj_id']);
        $class     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $bj_id));
        $condition .= " And (bj_id1 = '{$bj_id}' or bj_id2 = '{$bj_id}' or bj_id3 = '{$bj_id}')";
        $bjqbjid   = $bj_id;
    }else{
        $condition = " And (bj_id1 = '{$bj['sid']}' or bj_id2 = '{$bj['sid']}' or bj_id3 = '{$bj['sid']}')";
        $class     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $bj['sid']));
        $bjqbjid   = $bj['sid'];
    }

    $cfrist = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 2 $condition ORDER BY id DESC LIMIT 0,1");

    $ctotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 2 $condition ");

    $xclist = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 AND isfm= 1 $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

    foreach($xclist as $key => $value){
        $students                 = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id = :id", array(
            ':weid'     => $weid,
            ':schoolid' => $schoolid,
            ':id'       => $value['sid'],
        ));
        $stotal                   = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' ");
        $xclist[$key]['tag']      = $students['s_name'];
        $xclist[$key]['wlzytype'] = $value['sid'];
        $xclist[$key]['total']    = $stotal;
        $xclist[$key]['tagid']    = $value['uid'];
        $xclist[$key]['picurl']   = tomedia($value['fmpicurl']);
    }

    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type= 1 AND isfm= 1 $condition");

    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete($this->table_media, array('sid' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteone'){
    $pid  = intval($_GPC['photoid']);
    $item = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " WHERE id = :id ", array(':id' => $pid));
    if(empty($pid)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    if($item['isfm'] == 1){
        $this->imessage('此照片为本学生封面照片，不能删除，如需删除，请先删除其他的照片，再点击删除所有按钮！');
    }
    pdo_delete($this->table_media, array('id' => $pid));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'posta'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 14;
    $bj_id     = intval($_GPC['bj_id']);
    $classify  = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $bj_id));
    $condition .= " And (bj_id1 = '{$bj_id}' or bj_id2 = '{$bj_id}' or bj_id3 = '{$bj_id}')";
    $list2     = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type = 2 $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total     = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type= 2 $condition");
    $pager     = pagination($total, $pindex, $psize);

}elseif($operation == 'basic'){
    
    mload()->model('photo');
    if($_W['schooltype']){ //培训模式
        //获取课程
        // $kclist = pdo_fetchall("select id,name FROM ".tablename('wx_school_tcourse')." WHERE schoolid = '{$schoolid}' ORDER BY ssort DESC ");
        if(!empty($_GPC['kcid'])){
            $kcid     = intval($_GPC['kcid']);
        }else{
            $kcid = $kclist[0]['id'];
        }
        $bjphototype = GetPxPhotoType($weid,$schoolid,$kcid,2,'');
    }else{
        if(!empty($_GPC['bj_id'])){
            $bj_id     = intval($_GPC['bj_id']);
        }else{
            $bj_id = $bj['sid'];
        }
        $bjphototype = GetPhotoType($weid,$schoolid,$bj_id,2,'');
    }
}elseif($operation == 'AddPhotoType'){
    $data = array(
        'weid' =>  $weid,
        'schoolid' => $schoolid,
        'sname' => trim($_GPC['sname']),
        'is_upload' => $_GPC['is_upload'],
        'phototype' => $_GPC['phototype'],
        'bjid' => $_GPC['bj_id'],
        'kcid' => $_GPC['kcid'],
        'type'=>'phototype'
    );
    if($_GPC['sid']){
        pdo_update(GetTableName('classify',false),$data,array('sid'=>$_GPC['sid']));
    }else{
        pdo_insert(GetTableName('classify',false), $data);
    }
	$data['result'] = true;
	$data['msg'] = '操作成功！';
	die(json_encode($data));	
}elseif($operation == 'GetPhotoData'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 15;
    $bj_id = $_GPC['bj_id'];
    $kcid = $_GPC['kcid'];
    $sid = $_GPC['sid'];
    if(!empty($bj_id)){
        $condition .= " AND bj_id1 = '{$bj_id}' ";
    }elseif(!empty($kcid)){
        $condition .= " AND kcid = '{$kcid}'";
    }
    if($_GPC['type'] == 2){
        if($_GPC['ctype'] == '0'){
            $condition .= " AND type = '{$_GPC['type']}' AND (ctype = '' || ctype ='0')";

        }else{
            $condition .= " AND type = '{$_GPC['type']}' AND ctype = '{$_GPC['ctype']}'";
        }
        if(!is_numeric($_GPC['ctype']) && !empty($_GPC['ctype'])){
            if(!empty($bj_id)){
                if($_GPC['ctype'] == 'teaactivity'){
                    $groupactivity = pdo_fetchAll("SELECT title,id FROM " . tablename($this->table_groupactivity) . " WHERE schoolid = '{$schoolid}' AND FIND_IN_SET($bj_id,bjarray)");
                }
            }
            mload()->model('photo');
            $com['sname']  = GetphotoTypeName($_GPC['ctype']);
        }else{
            if($_GPC['ctype'] != '0'){
                $com  = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $_GPC['ctype']));
            }else{
                $com  = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $bj_id));
            }
        }
    }elseif($_GPC['type'] == 1){
        if($_GPC['ctype'] == '0'){
            $condition .= " AND type = '{$_GPC['type']}' AND sid = '{$sid}' AND (ctype = '' || ctype='0') ";
        }else{
            $condition .= " AND type = '{$_GPC['type']}' AND sid = '{$sid}' AND ctype = '{$_GPC['ctype']}' ";
        }
        $com = pdo_fetch("SELECT s_name as sname FROM " . tablename($this->table_students) . " where id = :id", array(':id' => $sid));
    }
    $list2     = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total     = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager     = pagination($total, $pindex, $psize);
}elseif($operation == 'GetPhotoType'){
    $phototype = pdo_fetch("SELECT bjid,kcid,sname,is_upload,phototype FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['id']}' ");
	$data['data'] = $phototype;
	die(json_encode($data));	
}elseif($operation == 'DeleteType'){
    $haspic = pdo_fetch("SELECT id FROM ".GetTableName('media')." WHERE ctype = '{$_GPC['id']}' ");
    if(!empty($haspic)){
        $data['result'] = false;
        $data['msg'] = '无法删除！当前相册下有照片，请先删除照片';
    }else{
        pdo_delete(GetTableName('classify',false),array('sid'=>$_GPC['id']));
        $data['result'] = true;
        $data['msg'] = '删除成功';
    }
	die(json_encode($data));	
}elseif($operation == 'delAll'){
    $res = pdo_delete(GetTableName('media',false),array('ctype'=>$_GPC['ctype'],'bj_id1'=>$_GPC['bjid']));
    $data['result'] = true;
    $data['msg'] = '删除成功';
	die(json_encode($data));	
}elseif($operation == 'NewuploadPhotos'){
    $photoarr = $_GPC['photoarr'];
    $type = $_GPC['type'];
    $id = $_GPC['id'];
    $bj_id = $_GPC['bj_id'];
    $kcid = $_GPC['kcid'];
    $sid = $_GPC['sid'];
    $ctype = $_GPC['ctype'];
    $video = $_GPC['video'];
    if(!empty($photoarr)){
        foreach( $photoarr as $key => $value )
        {
            $data = array(
                'weid' =>  $weid,
                'schoolid' => $schoolid,
                'picurl' => $value,
                'bj_id1' => $bj_id,
                'kcid' => $kcid,
                'sid' => $sid,
                'uid' => $tid_global,
                'jthdid' => $_GPC['jthdid'],
                'ctype' => $ctype,
                'order'=>$key+1,
                'createtime' => time(),
                'type'=>$type,
            );
            pdo_insert($this->table_media, $data);
        }
    }
    $school = pdo_fetch("SELECT spic FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
    mload()->model('kc');
    $qrcode = PosterQrcode(tomedia($video));
    $vdata = array(
        'weid' =>  $weid,
        'schoolid' => $schoolid,
        'bj_id1' => $bj_id,
        'kcid' => $kcid,
        'sid' => $sid,
        'uid' => $tid_global,
        'ctype' => $ctype,
        'video'=>$video,
        'videoqrcode'=>$qrcode,
        'picurl'=>$_GPC['videoimg'] ? $_GPC['videoimg'] : $school['spic'],
        'jthdid' => $_GPC['jthdid'],
        'is_video'=>1,
        'createtime' => time(),
        'type'=>$type,
    );
    if(!empty($video)){
        pdo_insert($this->table_media, $vdata);
    }

	$data['result'] = true;
	$data['msg'] = '上传成功！';
	die(json_encode($data));	
}elseif($operation == 'private'){
    mload()->model('photo');
    if($_W['schooltype']){ //培训模式
        //获取课程
        // $kclist = pdo_fetchall("select id,name FROM ".tablename('wx_school_tcourse')." WHERE schoolid = '{$schoolid}' ORDER BY ssort DESC ");
        if(!empty($_GPC['kcid'])){
            $kcid     = intval($_GPC['kcid']);
        }else{
            $kcid = $kclist[0]['id'];
        }
        $stuphototype = GetPxPhotoType($weid,$schoolid,$kcid,1,'');
    }else{
        // 获取学生相册分类
        if(!empty($_GPC['bj_id'])){
            $bj_id     = intval($_GPC['bj_id']);
        }else{
            $bj_id = $bj['sid'];
        }
        $stuphototype = GetPhotoType($weid,$schoolid,$bj_id,1,'');
    }
    
}elseif($operation == 'GetStuList'){
    $pindex = max(1, intval($_GPC['page']));
    $psize  = 10;
    if(!$_W['schooltype']){
        if(!empty($_GPC['bj_id'])){
            $bj_id     = intval($_GPC['bj_id']);
        }else{
            $bj_id = $bj['sid'];
        }
        $condition .= " And bj_id1 = '{$bj_id}' ";
    }else{
        if(!empty($_GPC['kcid'])){
            $kcid     = intval($_GPC['kcid']);
        }else{
            $kcid = $kclist[0]['id'];
        }
        $condition .= " And (kcid = '{$kcid}' OR kcid = '0') AND type = '{$_GPC['type']}' ";
        if($_GPC['ctype'] == 0){
            $condition .= " AND (ctype = '0' || ctype = '')";
        }else{
            $condition .= " AND ctype = '{$_GPC['ctype']}'";
        }
    }
    
    if(!$_W['schooltype']){
        if($_GPC['ctype'] == 0){
            $condition .= " AND (ctype = '0' || ctype = '') GROUP BY sid";
            $condition2 .= " AND (ctype = '0' || ctype = '')";
        }else{
            $condition .= " AND ctype = '{$_GPC['ctype']}' GROUP BY sid ";
            $condition2 .= " AND ctype = '{$_GPC['ctype']}'";
        }
    }
    
            
    if(!empty($_GPC['kcid'])){

        $xclist = pdo_fetchall("SELECT * FROM " . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kcid = '{$kcid}' ORDER BY kcid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        foreach ($xclist as $key => $value) {
            $students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id=:id", array(
                ':weid' => $weid,
                ':schoolid' => $schoolid,
                ':id' => $value['sid']
                ));
            $stotal = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' $condition ");
            $media = pdo_fetch("SELECT picurl FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND sid = '{$value['sid']}' $condition");
            $xclist[$key]['sname'] = $students['s_name'];	
            $xclist[$key]['picurl'] = tomedia($media['picurl']);	
            $xclist[$key]['total'] = $stotal;
        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kcid = '{$kcid}' ");
        //未分配学生和课程和班级
        $nokcmedia = pdo_fetch("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND kcid = '0' AND sid = '0' AND bj_id1 = '0'");
        $nokctotal = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND kcid = '0' AND sid = '0' AND bj_id1 = '0'");
    }else{
        $xclist = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        foreach($xclist as $key => $value){
            $students                 = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id = :id", array(
                ':weid'     => $weid,
                ':schoolid' => $schoolid,
                ':id'       => $value['sid'],
            ));
            $stotal                   = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And type = 1 And sid = '{$value['sid']}' AND bj_id1 = '{$bj_id}' $condition2");
            $xclist[$key]['sname']      = $students['s_name'];
            $xclist[$key]['sid'] = $value['sid'];
            $xclist[$key]['total']    = $stotal;
            $xclist[$key]['picurl']   = tomedia($value['picurl']);
            $xclist[$key]['bj_id']   = $bj_id;
        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_media) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type= 1 AND isfm= 1 $condition");
    }
    $pager = pagination($total, $pindex, $psize);
}
// if(keep_MC()){
    include $this->template('web/newphotos');
// }else{
    // include $this->template('web/photos');
// }
?>