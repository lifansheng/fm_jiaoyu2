<?php
    global $_W, $_GPC;
    $weid = $this->weid;
    $from_user = $this->_fromuser;
    $schoolid = intval($_GPC['schoolid']);
    $openid = $_W['openid'];
    $op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
    //查询是否用户登录		
    $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
    $it = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['tid']));	
    if(!empty($it)){
        if($op == 'display'){
            $leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :tid = tid And :isliuyan = isliuyan AND status = :status ORDER BY id DESC LIMIT 0,10", array(
                ':weid' => $weid,
                ':schoolid' => $schoolid,
                ':tid' => 0,
                ':isliuyan' => 0,
                ':status' => 1
                ));
            foreach ($leave as $index => $row) {
                $member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
                $student = pdo_fetch("SELECT * FROM " . tablename ($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $weid, ':id' => $row['sid']));
                $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where uid = :uid And openid = :openid And sid = :sid ", array(':uid' => $row['uid'],':openid' => $row['openid'],':sid' => $row['sid']));
                if($user['pard'] ==2){
                    $leave[$index]['guanxi'] = "妈妈";
                }
                if($user['pard'] ==3){
                    $leave[$index]['guanxi'] = "爸爸";
                }
                if($user['pard'] ==4){
                    $leave[$index]['guanxi'] = "本人";
                }
                if($user['pard'] ==5){
                    $leave[$index]['guanxi'] = "家长";
                }
                $teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $row['cltid'], ':schoolid' => $schoolid));
                $leave[$index]['tname'] = $teacher['tname'];
                $leave[$index]['thumb'] = $teacher['thumb'];
                $leave[$index]['s_name'] = $student['s_name'];
            }	
            include $this->template(''.$school['style3'].'/leavetodoorlist');	   
        }elseif($op == 'scroll_more'){
            $id = $_GPC['LiData']['id'];
            $leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where :schoolid = schoolid And :weid = weid And :tid = tid And :isliuyan = isliuyan AND status = :status AND id < {$id} ORDER BY id DESC LIMIT 0,10", array(
                ':weid' => $weid,
                ':schoolid' => $schoolid,
                ':tid' => 0,
                ':isliuyan' => 0,
                ':status' => 1
                ));
            foreach ($leave as $index => $row) {
                $member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $weid, ':uid' => $row['uid']));
                $student = pdo_fetch("SELECT * FROM " . tablename ($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $weid, ':id' => $row['sid']));
                $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where uid = :uid And openid = :openid And sid = :sid ", array(':uid' => $row['uid'],':openid' => $row['openid'],':sid' => $row['sid']));
                if($user['pard'] ==2){
                    $leave[$index]['guanxi'] = "妈妈";
                }
                if($user['pard'] ==3){
                    $leave[$index]['guanxi'] = "爸爸";
                }
                if($user['pard'] ==4){
                    $leave[$index]['guanxi'] = "本人";
                }
                if($user['pard'] ==5){
                    $leave[$index]['guanxi'] = "家长";
                }
                $teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $row['cltid'], ':schoolid' => $schoolid));
                $leave[$index]['tname'] = $teacher['tname'];
                $leave[$index]['thumb'] = $teacher['thumb'];
                $leave[$index]['s_name'] = $student['s_name'];
            }	
            include $this->template('comtool/leavetodoorlist');
        }
        
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
    } 	
    