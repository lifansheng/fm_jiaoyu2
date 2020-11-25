<?php
/*
 * @Discription: 心理咨询管理页
 * @Author: Hannibal·Lee
 * @Date: 2020-05-19 10:46:27
 * @LastEditTime: 2020-05-21 18:09:54
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'psychology';
$this1             = 'no15';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$teacherlist = pdo_fetchall("SELECT t.id,t.tname,s.tid FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('shrink') . " as s  ON  s.tid = t.id    WHERE    t.schoolid = '{$schoolid}' and s.tid IS NULL  ");
if ($operation == 'display') {
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 15;
    $condition = '';
    if(!empty($_GPC['tname'])){
        $tealist = pdo_fetchall("SELECT id FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' AND tname LIKE :tname ",array(':tname'=>"%{$_GPC['tname']}%"));
        $tidstr = arrayToString($tealist);
        $users = pdo_fetchall("SELECT id FROM ".GetTableName('user')." WHERE schoolid = :schoolid AND FIND_IN_SET(tid,:tid)",array(':schoolid'=>$schoolid,':tid'=>$tidstr));
        $useridstr = arrayToString($users);
        $condition .= " AND (FIND_IN_SET(touserid,'{$useridstr}') OR FIND_IN_SET(userid,'{$useridstr}'))";
    }
    $list = pdo_fetchall("SELECT leaveid,sendtype,openid,toopenid,userid,touserid FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' $condition GROUP BY leaveid ORDER BY createtime ASC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach ($list as $key => $value) {
        //取出最小和最大的时间
        $mintime = pdo_fetch("SELECT createtime FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' AND leaveid = '{$value['leaveid']}' ORDER BY createtime ASC")['createtime'];
        $maxtime = pdo_fetch("SELECT createtime FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' AND leaveid = '{$value['leaveid']}' ORDER BY createtime DESC")['createtime'];
        $list[$key]['mintime'] = date("Y-m-d H:i:s", $mintime);
        $list[$key]['maxtime'] = date("Y-m-d H:i:s", $maxtime);

        if ($value['sendtype'] == 'fanstotea' || $value['sendtype'] == 'teatofans') { //粉丝身份对话
            $fans_info = mc_fansinfo($value['openid']);
            $list[$key]['sname'] = $fans_info['nickname'];
            $list[$key]['icon'] = $fans_info['headimgurl'];
            $tid = pdo_fetch("SELECT tid FROM " . GetTableName('user') . " WHERE id = '{$value['touserid']}' ")['tid'];
            $tea = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id='{$tid}' ");
            $list[$key]['tname'] = $tea['tname'];
            $list[$key]['thumb'] = tomedia($tea['thumb']);
            $list[$key]['isfans'] = true;
        } else {
            $sid = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " WHERE id = '{$value['userid']}' ")['sid'];
            $stu = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id='{$sid}' ");
            $tid = pdo_fetch("SELECT tid FROM " . GetTableName('user') . " WHERE id = '{$value['touserid']}' ")['tid'];
            $tea = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id='{$tid}' ");
            $list[$key]['tname'] = $tea['tname'];
            $list[$key]['thumb'] = tomedia($tea['thumb']);
            $list[$key]['sname'] = $stu['s_name'];
            $list[$key]['icon'] = tomedia($stu['icon']);
            $list[$key]['isfans'] = false;
        }
    }
    $total = pdo_fetchcolumn("SELECT count(id) FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' GROUP BY leaveid ");
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/psychology');
}elseif($operation == 'delete'){
    $id  = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM " . GetTableName('psychology') . " WHERE leaveid = :leaveid", array(':leaveid' => $id));
    if(empty($row)){
        $this->imessage('抱歉，不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete(GetTableName('psychology',false), array('leaveid' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'getPsychology'){ //查看评价列表
	$leaveid = $_GPC['leaveid'];
    $item = pdo_fetch("SELECT leaveid,sendtype,openid,toopenid,userid,touserid FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' AND leaveid = '{$leaveid}' ORDER BY createtime ASC  ");
    if($item['sendtype'] == 'fanstotea'){ //粉丝对话老师
        $list = pdo_fetchAll("SELECT * FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' AND leaveid = '{$leaveid}' ORDER BY createtime ASC  ");
        foreach ($list as $key => $value) {
            if($value['sendtype'] == 'fanstotea'){
                //粉丝信息
                $fans_info = mc_fansinfo($value['openid']);
                $list[$key]['name'] = $fans_info['nickname'];
                $list[$key]['icon'] = $fans_info['headimgurl'];
                $list[$key]['istea'] = false;
            }else{
                //老师信息
                $tid = pdo_fetch("SELECT tid FROM " . GetTableName('user') . " WHERE id = '{$value['userid']}' ")['tid'];
                $tea = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id='{$tid}' ");
                $list[$key]['name'] = $tea['tname'];
                $list[$key]['icon'] = tomedia($tea['thumb']);
                $list[$key]['istea'] = true;
            }
            $audio = unserialize($value['audio']);
            $list[$key]['audio'] = tomedia($audio['audio'][0]);
            $list[$key]['picurl'] = tomedia($value['picurl']);
        }
	}else{
		$list = pdo_fetchAll("SELECT * FROM " . GetTableName('psychology') . " WHERE schoolid = '{$schoolid}' AND leaveid = '{$leaveid}' ORDER BY createtime ASC  ");
        foreach ($list as $key => $value) {
            if($value['sendtype'] == 'stutotea'){
                //学生信息
                $sid = pdo_fetch("SELECT sid FROM " . GetTableName('user') . " WHERE id = '{$value['userid']}' ")['sid'];
                $stu = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE id='{$sid}' ");
                $list[$key]['name'] = $stu['s_name'];
                $list[$key]['icon'] = tomedia($stu['icon']);
                $list[$key]['istea'] = false;
            }else{
                //老师信息
                $tid = pdo_fetch("SELECT tid FROM " . GetTableName('user') . " WHERE id = '{$value['userid']}' ")['tid'];
                $tea = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE id='{$tid}' ");
                $list[$key]['name'] = $tea['tname'];
                $list[$key]['icon'] = tomedia($tea['thumb']);
                $list[$key]['istea'] = true;
            }
            $audio = unserialize($value['audio']);
            $list[$key]['audio'] = tomedia($audio['audio'][0]);
            $list[$key]['picurl'] = tomedia($value['picurl']);
        }
	}
    include $this->template('public/psychology');
	die;
}
