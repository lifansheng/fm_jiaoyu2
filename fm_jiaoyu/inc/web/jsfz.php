<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid = $_W['uniacid'];
$action1 = 'jsfz';
$this1 = 'no1';
$action = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid = intval($_GPC['schoolid']);
$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
//$schoolset = pdo_fetch("SELECT * FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($_W['role'] != 'manager' && !$_W['isfounder'] && $_W['role'] != 'owner') {
    $this->imessage('非法访问，您无权操作该页面', '', 'error');
}
if ($operation == 'display') {
    if (!empty($_GPC['ssort'])) {
        foreach ($_GPC['ssort'] as $sid => $ssort) {
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', referer(), 'success');
    }
    $children = array();
    $jsfz = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz' And schoolid = {$schoolid} ORDER BY sid ASC, ssort DESC", array(), 'sid');
    foreach ($jsfz as $index => $row) {
        $jsfz[$index]['renshu'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . GetTableName('teachers') . " WHERE schoolid = :schoolid And fz_id = :fz_id", array(':schoolid' => $schoolid, ':fz_id' => $row['sid']));
        if (!empty($row['parentid'])) {
            $children[$row['parentid']][] = $row;
            unset($jsfz[$index]);
        }
    }
} elseif ($operation == 'setdown_fztidarr') {
    $tidarr = $_GPC['tidarr'];
    $arr_str = '';
    foreach ($tidarr as $value) {
        $arr_str .= $value . ',';
    }
    $arr_str = trim($arr_str, ',');

    $sid = $_GPC['sid'];
    if (pdo_update($this->table_classify, array('tidarr' => $arr_str), array('sid' => $sid))) {
        die(json_encode(array(
            'result' => true,
            'msg' => '设置成功！',
        )));
    }

} elseif ($operation == 'get_fztid') {
    $fzid = $_GPC['sid'];
    $schoolid = $_GPC['schoolid'];
    $teachers_list = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " where weid = :weid And schoolid = :schoolid and fz_id != '{$fzid}' ORDER BY  CONVERT(tname USING gbk)  ASC ", array(':weid' => $weid, ':schoolid' => $schoolid));
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id={$schoolid}");
    $fztid = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid={$fzid} and schoolid={$schoolid} And type='jsfz' ");
    $qx = array();
    $fztidarr = explode(',', $fztid['tidarr']);
    include $this->template('web/fztidarr');
    exit();
} elseif ($operation == 'post') {
    $parentid = intval($_GPC['parentid']);
    $sid = intval($_GPC['sid']);
    if (!empty($sid)) {
        $jsfz = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '$sid'");
    } else {
        $jsfz = array(
            'ssort' => 0,
        );
    }

    if (checksubmit('submit')) {
        if (!empty($sid)) {
            if (!empty($_GPC['old'])) {
                if (empty($_GPC['catename'])) {
                    $this->imessage('抱歉，请输入分组名称！', referer(), 'error');
                }
                if (empty($_GPC['pname'])) {
                    $this->imessage('抱歉，请输入称谓！', referer(), 'error');
                }

                $data = array(
                    'weid' => $weid,
                    'schoolid' => $_GPC['schoolid'],
                    'sname' => $_GPC['catename'],
                    'pname' => trim($_GPC['pname']),
                    'ssort' => intval($_GPC['ssort']),
                    'type' => 'jsfz',

                );

                pdo_update($this->table_classify, $data, array('sid' => $sid));
                $edittitle = '成功修改名称为：' . $_GPC['catename'];

            }

            if (!empty($_GPC['new'])) {
                $f_count = 0;
                foreach ($_GPC['new'] as $key => $name) {
                    $name = trim($_GPC['catename_new'][$key]);
                    $pname = trim($_GPC['pname_new'][$key]);
                    if (empty($name)) {
                        $fzcount += $f_count + 1;
                        $fzname = '有【' . $fzcount . '】条分组名称未填写';
                    }
                    if (empty($pname)) {
                        $cwcount += $f_count + 1;
                        $cwname = '有【' . $cwcount . '】条分组名称未填写';
                    }
                    $data = array(
                        'weid' => $weid,
                        'schoolid' => $_GPC['schoolid'],
                        'sname' => $name,
                        'pname' => $pname,
                        'ssort' => intval($_GPC['ssort_new'][$key]),
                        'type' => 'jsfz',
                    );
                    if (!empty($name) && !empty($pname)) {
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下分组:';
                        $msg .= $name . '|';
                    }
                }
                $msg = rtrim($msg, "|");
                $message = $edittitle . '<br/>' . $success . $msg . '<br/><span style="color:red;">' . $fzname . '<br/>' . $cwname . '<br/></span>';
                $this->imessage("$message", $this->createWebUrl('jsfz', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
            }
        } else {
            if (!empty($_GPC['new'])) {
                $f_count = 0;
                foreach ($_GPC['new'] as $key => $name) {
                    $name = trim($_GPC['catename_new'][$key]);
                    $pname = trim($_GPC['pname_new'][$key]);
                    if (empty($name)) {
                        $fzcount += $f_count + 1;
                        $fzname = '有【' . $fzcount . '】条分组名称未填写';
                    }
                    if (empty($pname)) {
                        $cwcount += $f_count + 1;
                        $cwname = '有【' . $cwcount . '】条分组名称未填写';
                    }
                    $data = array(
                        'weid' => $weid,
                        'schoolid' => $_GPC['schoolid'],
                        'sname' => $name,
                        'pname' => $pname,
                        'ssort' => intval($_GPC['ssort_new'][$key]),
                        'type' => 'jsfz',
                    );
                    if (!empty($name) && !empty($pname)) {
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下分组:';
                        $msg .= $name . '|';
                    }
                }
                $msg = rtrim($msg, "|");
                $message = $success . $msg . '<br/><span style="color:red;">' . $fzname . '<br/>' . $cwname . '<br/></span>';
                $this->imessage("$message", $this->createWebUrl('jsfz', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
            }
        }
        $this->imessage('更新分组成功！', $this->createWebUrl('jsfz', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
} elseif ($operation == 'delete') {
    $sid = intval($_GPC['sid']);
    $jsfz = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '$sid'");
    if (empty($jsfz)) {
        $this->imessage('抱歉，分组不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('分组删除成功！', referer(), 'success');
} elseif ($operation == 'get_fz') {
    $fzid = $_GPC['sid'];
    $schoolid = $_GPC['schoolid'];
    $schooltype = $_GPC['schooltype'];
    $school = pdo_fetch("SELECT tpic FROM " . tablename($this->table_index) . " where id={$schoolid}");
    /**************当前分组及老师*****************/
    $nowfz = pdo_fetch("SELECT sid,sname FROM " . tablename('wx_school_classify') . " WHERE type ='jsfz' AND schoolid='{$schoolid}' AND sid = '{$fzid}'");
    $nowfztea = pdo_fetchall("SELECT id,tname,thumb FROM " . tablename('wx_school_teachers') . " WHERE schoolid = '{$schoolid}' And fz_id ='{$fzid}' ORDER BY id ASC ");
    foreach ($nowfztea as $k => $i) {
        if ($i['thumb']) {
            $nowfztea[$k]['icon'] = tomedia($i['thumb']);
        } else {
            $nowfztea[$k]['icon'] = tomedia($school['tpic']);
        }
    }
    /**************当前分组及老师*****************/

    /**************非当前分组及老师*****************/
    $nonowfz = pdo_fetchall("SELECT sid,sname FROM " . tablename('wx_school_classify') . " WHERE type ='jsfz' And schoolid='{$schoolid}' AND sid != '{$fzid}' ORDER BY parentid ASC ");
    foreach ($nonowfz as $key => $item) {
        $nonowfztea = pdo_fetchall("SELECT id,tname,thumb FROM " . tablename('wx_school_teachers') . " WHERE schoolid = '{$schoolid}' And fz_id ='{$item['sid']}' ORDER BY id ASC ");
        $nonowfz[$key]['sname'] = $item['sname'] . '(' . count($nonowfztea) . '人)';
        foreach ($nonowfztea as $k => $i) {
            if ($i['thumb']) {
                $nonowfztea[$k]['icon'] = tomedia($i['thumb']);
            } else {
                $nonowfztea[$k]['icon'] = tomedia($school['tpic']);
            }
        }
        $nonowfz[$key]['nonowfztea'] = $nonowfztea;
    }
    /**************非当前分组及老师*****************/

    /**************未分组老师*****************/
    $nofz = pdo_fetchall("SELECT id,tname,thumb FROM " . tablename('wx_school_teachers') . " WHERE schoolid = '{$schoolid}' And fz_id = 0 ORDER BY id ASC ");
    foreach ($nofz as $k => $i) {
        if ($i['thumb']) {
            $nofz[$k]['icon'] = tomedia($i['thumb']);
        } else {
            $nofz[$k]['icon'] = tomedia($school['tpic']);
        }
    }
    /**************未分组老师*****************/
    include $this->template('web/fzinfo');
    exit();
} elseif ($operation == 'AddJsFz') {
    $fzid = $_GPC['fzid'];
    $tidArr = $_GPC['tidArr'];
    $delTid = $_GPC['delTid'];
    if (!empty($tidArr)) {
        foreach ($tidArr as $id) {
            pdo_update(GetTableName('teachers', false), array('fz_id' => $fzid), array('id' => $id));
        }
    }
    if (!empty($delTid)) {
        foreach ($delTid as $id) {
            pdo_update(GetTableName('teachers', false), array('fz_id' => 0), array('id' => $id));
        }
    }
} elseif ($operation == 'ChangeShow') {
    $check = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' and sid = '{$_GPC['sid']}' ");
    if (!empty($check)) {
        pdo_update(GetTableName('classify', false), array('is_show' => $_GPC['is_show']), array('sid' => $_GPC['sid']));
    }
}
include $this->template('web/jsfz');
