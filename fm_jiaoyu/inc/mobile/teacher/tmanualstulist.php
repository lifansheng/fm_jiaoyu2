<!--
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2020-04-20 18:14:24
 * @LastEditTime: 2020-04-21 10:17:56
 -->
 <?php
/*
 * @Discription:  教师成长手册列表页
 * @Author: Hannibal·Lee
 * @Date: 2020-04-20 10:27:44
 * @LastEditTime: 2020-04-22 13:29:58
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$userss = intval($_GPC['userid']);



$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
//老师登陆
$school = pdo_fetch("SELECT style3,headcolor,logo,spic FROM ".GetTableName('index')." WHERE id = {$schoolid} ");
$userid = pdo_fetch("SELECT id,tid FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
if(!empty($userid)){ //是老师登录
    $tid = $userid['tid'];
    $teacherinfo = pdo_fetch("SELECT tname,thumb FROM ".GetTableName('teachers')." WHERE id = '{$tid}' ");
    $teacherinfo['thumb'] = !empty($teacherinfo['thumb']) ? tomedia($teacherinfo['thumb']) : tomedia($school['tpic']);

    $fileid = $_GPC['fileid'];
    $bj_id = $_GPC['bj_id'];

    if($op == 'display'){
        $fileInfo = pdo_fetch("SELECT * FROM ".GetTableName('growupfile')." WHERE id = '{$fileid}' ");
        $bjstulist = pdo_fetchall("SELECT  id,s_name FROM ".GetTableName('students')." WHERE bj_id = '{$bj_id}' ");
        $StuTable = GetTableName('students');
        $PageTable = GetTableName('growuppage');
        $sql  = <<<EOF
            SELECT
            COUNT(CASE WHEN p.isok = 1 AND p.auth =2 THEN 1 ELSE NULL END) AS done,
            COUNT(CASE WHEN p.auth = 2 THEN 1 ELSE NULL END) as allc,
            max(p.is_send) as is_send,
            p.isok,p.sid,s.s_name,s.icon
            FROM {$PageTable} as p, {$StuTable} as s 
            WHERE
            p.gid = '{$fileid}' and p.sid = s.id and s.bj_id = '{$bj_id}' group by p.sid
EOF;
$list = pdo_fetchall($sql);




        include $this->template(''.$school['style3'].'/manual/tmanualstulist');
    }elseif($op == 'scroll_more'){
       $idL = $_GPC['LiData']['tagid'];
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET('{$bj_id}',bjarr) and id < '{$idL}'  ORDER BY id DESC LIMIT 0,10 ");
        foreach($list as $k=>$v) {
            $list[$k]['thumb'] =  !empty($v['thumb']) ? tomedia($v['thumb']) : tomedia($school['logo']);
            $list[$k]['qhname'] =  pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$v['qhid']}' ")['sname'];
            $list[$k]['createtime'] =  date("Y-m-d",$v['createtime']);
        }
        include $this->template(''.$school['style3'].'/manual/tmanuallist_bot');
    }elseif($op == 'sendManual'){
        //获取需要发送的对象
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('growuppage')." WHERE schoolid = '{$_GPC['schoolid']}' AND bjid = '{$_GPC['bjid']}' AND gid = '{$_GPC['gid']}' AND is_send = 0 AND isok = 1 AND auth = 2 GROUP BY sid ORDER BY id DESC ");
        foreach ($list as $key => $value) {
            //发送模板
            $this->sendMobileManual($value['sid'],$_GPC['gid'], $_GPC['schoolid'], $_GPC['weid']);
            // 修改发送状态 
            pdo_update(GetTableName('growuppage',false),array('is_send'=>1),array('gid'=>$_GPC['gid'],'sid'=>$value['sid']));
        }
        $result['result'] = true;
        $result['msg'] = '发送成功';
        die(json_encode($result));
    }
}

?>