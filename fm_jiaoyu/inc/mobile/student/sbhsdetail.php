<?php
    /**
     * 微教育模块
     *
     * @author 高贵血迹
     */
    global $_W, $_GPC;
    $weid = $_W['uniacid'];
    $schoolid = intval($_GPC['schoolid']);
    $userss = !empty($_GPC['userid']) ? intval($_GPC['userid']) : 1;
    $openid = $_W['openid'];
    $obid = 2;

    //查询是否用户登录
    if(empty($_SESSION['user'])){
        mload()->model('user');
        $_SESSION['user'] = check_userlogin($weid,$schoolid,$openid,$userss);
        if ($_SESSION['user'] == 2){
            include $this->template('bangding');
        }
    }
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
    $it = pdo_fetch("SELECT id,sid,is_allowmsg FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));

    $qh = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['qhid']}' ");
    $teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$_GPC['tid']}' ");
    $sid = $it['sid'];
    $student = pdo_fetch("SELECT s_name,icon FROM ".GetTableName('students')." WHERE id = '{$sid}' ");
    $student['icon'] =  $student['icon'] ? $student['icon'] : $school['spic']; 
    $BHSlist = pdo_fetchall("SELECT bhs.score,bhs.word,c.sname FROM ".GetTableName('behaviorscorelog')." as bhs , ".GetTableName('classify')." as c WHERE bhs.schoolid = '{$schoolid}' and bhs.bhsid = c.sid and bhs.sid = '{$sid}' and bhs.qhid = '{$_GPC['qhid']}' and bhs.tid = '{$_GPC['tid']}' order by c.ssort DESC ");
    if(!empty($it)){

        include $this->template(''.$school['style2'].'/sbhsdetail');
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
        exit;
    } 
       
?>