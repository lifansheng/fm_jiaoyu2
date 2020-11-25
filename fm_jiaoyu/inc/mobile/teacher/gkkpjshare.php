<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$school = pdo_fetch("SELECT style3,title,spic,tpic,title,headcolor,thumb FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$gkkid = $_GPC['gkkid'];
$userid = $_GPC['userid']; //此处userid为评价人的userid,不是登陆人的userid
$gkkinfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " where id = :id And schoolid = :schoolid ", array(':id' => $gkkid,':schoolid' => $schoolid));
//查询是否用户登录
$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $user['id']));
if(!empty($it)){




    $pjtid = pdo_fetch("SELECT tid FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid));
    $pj_tid = $pjtid['tid'];
    $pj_tea =  pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id And schoolid = :schoolid ", array(':id' => $pj_tid,':schoolid' => $schoolid));



    $mypl = pdo_fetch("SELECT content FROM " . tablename($this->table_gkkpj) . " where tid = :tid And gkkid = :gkkid And userid = :userid And type = :type ", array(':tid' => $gkkinfo['tid'],':gkkid' => $gkkid,':userid' => $userid,':type' => 1));
    $teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where id = :id And schoolid = :schoolid ", array(':id' => $gkkinfo['tid'],':schoolid' => $schoolid));
    $list1 = pdo_fetchall("SELECT iconid,iconlevel FROM " . tablename($this->table_gkkpj) . " where userid = :userid And gkkid = :gkkid And type = :type  ORDER BY iconid ASC", array(
        ':userid' => $userid,
        ':gkkid' => $gkkid,
        ':type' => 2
    ));
    foreach($list1 as $key => $row){
        $scicon = pdo_fetch("SELECT * FROM " . tablename($this->table_gkkpjk) . " where id = :id ", array(':id' => $row['iconid']));
        $list1[$key]['title'] = $scicon['title'];
        if ($row['iconlevel'] == 1){
            $list1[$key]['icontitle'] = $scicon['icon1title'];
            $list1[$key]['icon'] = $scicon['icon1'];
        }
        if ($row['iconlevel'] == 2){
            $list1[$key]['icontitle'] = $scicon['icon2title'];
            $list1[$key]['icon'] = $scicon['icon2'];
        }
        if ($row['iconlevel'] == 3){
            $list1[$key]['icontitle'] = $scicon['icon3title'];
            $list1[$key]['icon'] = $scicon['icon3'];
        }
        if ($row['iconlevel'] == 4){
            $list1[$key]['icontitle'] = $scicon['icon4title'];
            $list1[$key]['icon'] = $scicon['icon4'];
        }
        if ($row['iconlevel'] == 5){
            $list1[$key]['icontitle'] = $scicon['icon5title'];
            $list1[$key]['icon'] = $scicon['icon5'];
        }
    }
    include $this->template(''.$school['style3'].'/gkkpjshare');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}
?>