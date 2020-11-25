<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$openid = $_W['openid'];	
$schoolid = $_GPC['schoolid'];
$userss = !empty($_GPC['userid']) ? intval($_GPC['userid']) : 1;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if(!empty($_GPC['userid'])){
    mload()->model('user');
    $_SESSION['user'] = check_userlogin($weid,$schoolid,$openid,$userss);
    if ($_SESSION['user'] == 2){
        include $this->template('bangding');
    }
}
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id And openid = :openid", array(':id' => $_SESSION['user'],':openid' => $openid));

if(!empty($it)){
    $student = pdo_fetch("SELECT id,s_name,icon,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $it['sid']));
    $class = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' and schoolid = '{$schoolid}'");
    $student['icon'] = tomedia($student['icon']);
    $student['bjname'] = $class['sname'];
    $bj_id = $student['bj_id'];

    if($op == 'display'){
        $list = pdo_fetchall("SELECT id,title,thumb,createtime,is_cose FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET('{$bj_id}',bjarr)  ORDER BY id DESC LIMIT 0,10 ");
        foreach($list as $k=>$v) {
            $AllowShare = pdo_fetch("SELECT id,pdffile FROM ".GetTableName('growuppage')." WHERE sid = '{$it['sid']}' AND gid = '{$v['id']}' AND schoolid = '{$schoolid}' AND isallok = 1 ");
            $list[$k]['thumb'] =  !empty($v['thumb']) ? tomedia($v['thumb']) : tomedia($school['logo']);
            $list[$k]['createtime'] =  date("Y-m-d",$v['createtime']);
            $list[$k]['AllowShare'] = $AllowShare ? true : false;
            $list[$k]['pdffile'] = tomedia($AllowShare['pdffile']);
        }
        include $this->template(''.$school['style2'].'/manual/smanuallist');
    }elseif($op == 'scroll_more'){
        $idL = $_GPC['LiData']['tagid'];
        $list = pdo_fetchall("SELECT id,title,thumb,createtime,is_cose FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET('{$bj_id}',bjarr) and id < '{$idL}'  ORDER BY id DESC LIMIT 0,10 ");
        foreach($list as $k=>$v) {
            $AllowShare = pdo_fetch("SELECT id,pdffile FROM ".GetTableName('growuppage')." WHERE sid = '{$it['sid']}' AND gid = '{$v['id']}' AND schoolid = '{$schoolid}' AND isallok = 1 ");
            $list[$k]['AllowShare'] = $AllowShare ? true : false;
            $list[$k]['thumb'] =  !empty($v['thumb']) ? tomedia($v['thumb']) : tomedia($school['logo']);
            $list[$k]['createtime'] =  date("Y-m-d",$v['createtime']);
            $list[$k]['pdffile'] = tomedia($AllowShare['pdffile']);
        }
        include $this->template(''.$school['style2'].'/manual/smanuallist_bot');
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}     