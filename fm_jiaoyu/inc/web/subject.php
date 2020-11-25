<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'subject';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if ($operation == 'display') {
    if (!(IsHasQx($tid_global, 1000261, 1, $schoolid))) {
        $this->imessage('非法访问，您无权操作该页面', '', 'error');
    }
    if (!empty($_GPC['ssort'])) {
        foreach ($_GPC['ssort'] as $sid => $ssort) {
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', referer(), 'success');
    }
    $children = array();
    $subject  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'subject' And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
    foreach ($subject as $index => $row) {
        $addedinfo = json_decode($row['addedinfo'],true);
        $subject[$index]['abbrevia'] = $addedinfo['abbrevia'];
        $subject[$index]['color'] = $addedinfo['color'];
        if (!empty($row['parentid'])) {
            $children[$row['parentid']][] = $row;
            unset($subject[$index]);
        }
    }
} elseif ($operation == 'post') {
    if (!(IsHasQx($tid_global, 1000262, 1, $schoolid))) {
        $this->imessage('非法访问，您无权操作该页面', '', 'error');
    }
    $parentid = intval($_GPC['parentid']);
    $sid      = intval($_GPC['sid']);
    if (!empty($sid)) {
        $subject = pdo_fetch("SELECT sid,ssort,sname,icon,addedinfo FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
        $addedinfo = json_decode($subject['addedinfo'],true);
        $subject['abbrevia'] = $addedinfo['abbrevia'];
        $subject['color'] = $addedinfo['color'];
    } else {
        $subject = array(
            'ssort' => 0,
        );
    }

    if (checksubmit('submit')) {
        if (!empty($sid)) {
            if (!empty($_GPC['old'])) {
                if (empty($_GPC['catename'])) {
                    $this->imessage('抱歉，请输入科目名称！', referer(), 'error');
                }
                $abbrevia = $_GPC['abbrevia'] ? $_GPC['abbrevia'] : mb_substr($_GPC['catename'], 0, 1, 'utf-8');
                $addedinfo = json_encode(array('abbrevia' => $abbrevia, 'color' => $_GPC['color']));
                $data = array(
                    'weid'     => $weid,
                    'schoolid' => $_GPC['schoolid'],
                    'sname'    => $_GPC['catename'],
                    'ssort'    => intval($_GPC['ssort']),
                    'icon'     => $_GPC['icon'],
                    'type'     => 'subject',
                    'addedinfo' => $addedinfo,
                );
                pdo_update($this->table_classify, $data, array('sid' => $sid));
                $edittitle = '成功修改名称为：' . $_GPC['catename'];
            }

            if (!empty($_GPC['new'])) {
                $f_count = 0;
                foreach ($_GPC['new'] as $key => $name) {
                    $name = trim($_GPC['catename_new'][$key]);
                    if (empty($name)) {
                        $subcount += $f_count + 1;
                        $subname = '有【' . $subcount . '】条科目名称未填写';
                    }
                    $abbrevia = $_GPC['abbrevia_new'][$key] ? $_GPC['abbrevia_new'][$key] : mb_substr($_GPC['catename_new'][$key], 0, 1, 'utf-8');
                    $addedinfo = json_encode(array('abbrevia' => $abbrevia, 'color' => $_GPC['color_new'][$key]));
                    $data = array(
                        'weid'     => $weid,
                        'schoolid' => $_GPC['schoolid'],
                        'sname'    => $name,
                        'ssort'    => intval($_GPC['ssort_new'][$key]),
                        'icon'     => $_GPC['icon_new'][$key],
                        'type'     => 'subject',
                        'addedinfo' => $addedinfo,
                    );
                    if (!empty($name)) {
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下科目:';
                        $msg .= $name . '|';
                    }
                }
                $msg = rtrim($msg, "|");
                $message =  $edittitle . '<br/>' . $success . $msg . '<br/><span style="color:red;">' . $subname . '</span>';
                $this->imessage("$message",  $this->createWebUrl('subject', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
            }
        } else {
            if (!empty($_GPC['new'])) {
                $f_count = 0;
                foreach ($_GPC['new'] as $key => $name) {
                    $name = trim($_GPC['catename_new'][$key]);
                    if (empty($name)) {
                        $subcount += $f_count + 1;
                        $subname = '有【' . $subcount . '】条科目名称未填写';
                    }
                    $data = array(

                        'weid'     => $weid,
                        'schoolid' => $_GPC['schoolid'],
                        'sname'    => $name,
                        'ssort'    => intval($_GPC['ssort_new'][$key]),
                        'icon'     => $_GPC['icon_new'][$key],
                        'type'     => 'subject',

                    );
                    if (!empty($name)) {
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下科目:';
                        $msg .= $name . '|';
                    }
                }
                $msg = rtrim($msg, "|");
                $message =  $success . $msg . '<br/><span style="color:red;">' . $subname . '</span>';
                $this->imessage("$message",  $this->createWebUrl('subject', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
            }
        }
        $this->imessage('更新科目成功！', $this->createWebUrl('subject', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
} elseif ($operation == 'delete') {

    /** 删除科目 */
    $sid     = intval($_GPC['sid']);
    $subject = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if (empty($subject)) {
        $this->imessage('抱歉，科目不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('科目删除成功！', referer(), 'success');
}
include $this->template('web/subject');
