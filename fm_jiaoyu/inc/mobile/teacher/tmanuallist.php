<?php
/*
 * @Discription:  教师成长手册列表页
 * @Author: Hannibal·Lee
 * @Date: 2020-04-20 10:27:44
 * @LastEditTime: 2020-04-21 10:12:51
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$userss = intval($_GPC['userid']);

$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
//老师登陆
$school = pdo_fetch("SELECT style3,headcolor,logo,tpic FROM ".GetTableName('index')." WHERE id = {$schoolid} ");
$userid = pdo_fetch("SELECT id,tid FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
if(!empty($userid)){ //是老师登录
    $tid = $userid['tid'];
    $teacherinfo = pdo_fetch("SELECT tname,thumb FROM ".GetTableName('teachers')." WHERE id = '{$tid}' ");
    $teacherinfo['thumb'] = !empty($teacherinfo['thumb']) ? tomedia($teacherinfo['thumb']) : tomedia($school['tpic']);
    $classlist = pdo_fetchall("SELECT s.sname,s.sid FROM ".GetTableName('classify')." as s LEFT JOIN ".GetTableName('user_class')." as c ON c.bj_id = s.sid  WHERE c.tid = '{$tid}' and c.schoolid = '{$schoolid}' group by c.bj_id ");

    if(!empty($_GPC['bj_id'])){
        $bj_id = $_GPC['bj_id'];
    }else {
        $bj_id = $classlist[0]['sid'];
    }

    if($op == 'display'){
       
        foreach($classlist as $v){
            if($v['sid'] == $bj_id){
                $bjName = $v['sname'];
            }
        }
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET('{$bj_id}',bjarr)  ORDER BY id DESC LIMIT 0,10 ");
        foreach($list as $k=>$v) {

            //获取需要发送的总数
            $send = pdo_fetch("SELECT COUNT(DISTINCT sid) as allc,IFNULL(COUNT( DISTINCT CASE WHEN is_send = 1 then sid end),0) as done FROM ".GetTableName('growuppage')." WHERE bjid='{$bj_id}' AND gid = '{$v['id']}' ");
            $list[$k]['thumb'] =  !empty($v['thumb']) ? tomedia($v['thumb']) : tomedia($school['logo']);
            $list[$k]['qhname'] =  pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$v['qhid']}' ")['sname'];
            $list[$k]['createtime'] =  date("Y-m-d",$v['createtime']);
            $list[$k]['done'] =  $send['done'];
            $list[$k]['allc'] =  $send['allc'];
        }
        include $this->template(''.$school['style3'].'/manual/tmanuallist');
    }elseif($op == 'scroll_more'){
       $idL = $_GPC['LiData']['tagid'];
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET('{$bj_id}',bjarr) and id < '{$idL}'  ORDER BY id DESC LIMIT 0,10 ");
        foreach($list as $k=>$v) {
            $list[$k]['thumb'] =  !empty($v['thumb']) ? tomedia($v['thumb']) : tomedia($school['logo']);
            $list[$k]['qhname'] =  pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$v['qhid']}' ")['sname'];
            $list[$k]['createtime'] =  date("Y-m-d",$v['createtime']);
        }
        include $this->template(''.$school['style3'].'/manual/tmanuallist_bot');
    }
}
 
?>