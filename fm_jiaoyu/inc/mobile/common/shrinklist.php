<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-05-21 18:04:52
 * @LastEditTime: 2020-06-04 15:23:53
 */ 
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
if(!empty($_SESSION['user'])){
    $UID = $_SESSION['user'];
}
//教师列表按教师入职时间先后顺序排列，先入职再前
$list = pdo_fetchall("SELECT t.*,s.description FROM ".GetTableName('shrink')." as s LEFT JOIN ".GetTableName('teachers')." as t ON s.tid = t.id  WHERE s.schoolid = '{$schoolid}' ORDER BY s.id DESC");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
mload()->model('stu');
$user =  get_myallclass_inschool($weid,$openid,$schoolid);
$HasUid = 0;
foreach($user as $k => $v){
    if($v['id'] == $UID){
        $HasUid = $UID;
    }
}
include $this->template(''.$school['style1'].'/shrinklist');
?>