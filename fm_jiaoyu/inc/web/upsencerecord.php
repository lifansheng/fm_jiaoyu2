<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$this1             = 'no2';
$action            = 'uploadsence';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global        = $_W['tid'];

$jsfzlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz'  And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
$teachers = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " where weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY  CONVERT(tname USING gbk)  ASC ");
$sencelist = pdo_fetchall("SELECT * FROM " . tablename($this->table_upsence) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' ORDER BY id DESC");


if ($operation == 'display') {
    if (!(IsHasQx($tid_global, 1003811, 1, $schoolid))) {
        $this->imessage('非法访问，您无权操作该页面', '', 'error');
    }
    if ($tid_global != 'founder' && $tid_global != 'owner' && $tid_global != 'vice_founder') {
        $is_admin = false;
        $this_teacher = pdo_fetch("SELECT fz_id,status,tname,id FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id = '{$tid_global}' ");
        if ($this_teacher['status'] == 2) {
            $is_admin = true;
        }

    } else {
        $is_admin = true;

    }
    if ($is_admin == true) {
        $condition = '';

    } elseif ($is_admin == false) {
        $this_fz = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz'  And schoolid = '{$schoolid}' and sid ='{$this_teacher['fz_id']}' ");
        $allsencelist = pdo_fetchall("SELECT * FROM " . tablename($this->table_upsence) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and qxfzid ='{$this_fz['sid']}' ");
        if (!empty($allsencelist)) {
            $is_qx = true;
        } else {
            $is_qx = false;

        }
        if ($is_qx == true) {
            $sencelist_str = '';
            foreach ($allsencelist as $key_a => $value_a) {
                $sencelist_str .= $value_a['id'] . ',';
            }
            $sencelist_str = trim($sencelist_str, ',');
            $condition = " and tid = '{$tid_global}' or  FIND_IN_SET(senceid,'{$sencelist_str}') ";
        } elseif ($is_qx == false) {
            $condition = " and tid = '{$tid_global}'";
        }

    }

    if (!empty($_GPC['uptime'])) {
        $starttime = strtotime($_GPC['uptime']['start']);
        $endtime   = strtotime($_GPC['uptime']['end']) + 86399;
        $condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    } else {
        $starttime = strtotime('-200 day');
        $endtime   = TIMESTAMP;
    }


    if (!empty($_GPC['search_tname'])) {
        $tea = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And tname = :tname ", array(':schoolid' => $schoolid, ':tname' => $_GPC['search_tname']));
        if (!empty($tea)) {
            $condition .= " AND tid = '{$tea['id']}'";
        } elseif (empty($tea)) {
            $condition .= " AND tid = 0 ";
        }
    }


    if (!empty($_GPC['search_senceid'])) {
        $condition .= " AND senceid = '{$_GPC['search_senceid']}' ";
    }

    if (!empty($_GPC['search_fzid'])) {
        $allsencelist_search = pdo_fetchall("SELECT * FROM " . tablename($this->table_upsence) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and qxfzid ='{$_GPC['search_fzid']}' ");
        $sencelist_str_search = '';
        foreach ($allsencelist_search as $key_a => $value_a) {
            $sencelist_str_search .= $value_a['id'] . ',';
        }
        $sencelist_str_search = trim($sencelist_str_search, ',');
        $condition .= " AND FIND_IN_SET(senceid,'{$sencelist_str_search}') ";
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize  = 10;

    $total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_teasencefiles) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY id DESC");
    $pager = pagination($total, $pindex, $psize);
    $sencefilelist = pdo_fetchall("SELECT * FROM " . tablename($this->table_teasencefiles) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY id DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach ($sencefilelist as $key => $value) {
        $sence = pdo_fetch("SELECT id,name,qxfzid FROM " . tablename($this->table_upsence) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id ='{$value['senceid']}' ");
        $sencefz = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and type='jsfz' and sid ='{$sence['qxfzid']}' ");
        $this_tname = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE  id='{$value['tid']}'");


        $sencefilelist[$key]['sencename'] = $sence['name'];
        $sencefilelist[$key]['fzname']    = $sencefz['sname'];
        $sencefilelist[$key]['tname']     = $this_tname['tname'];
    }

} elseif ($operation == 'upsence_form') {
    $tid       = $_GPC['up_tid'];
    $senceid   = $_GPC['up_senceid'];
    $wordinfo  = $_GPC['up_word'];
    $imgsarr   = $_GPC['up_imgs'];
    $audioinfo = $_GPC['up_audio'];
    $videoinfo = $_GPC['up_video'];

    if (empty($tid)) {
        $result['status'] = false;
        $result['msg'] = "请选择老师";
        die(json_encode($result));
    }
    if (empty($senceid)) {
        $result['status'] = false;
        $result['msg'] = "请选择上传场景";
        die(json_encode($result));
    }
    if (empty($wordinfo) && empty($imgsarr) && empty($audioinfo) && empty($videoinfo)) {
        $result['status'] = false;
        $result['msg'] = "请至少上传一项内容";
        die(json_encode($result));
    }
    if (!empty($imgsarr)) {
        $imgs_into = json_encode($imgsarr);
    }

    $into_data = array(
        'weid'       => $weid,
        'schoolid'   => $schoolid,
        'tid'        => $tid,
        'senceid'    => $senceid,
        'up_word'    => $wordinfo,
        'up_imgs'    => $imgs_into,
        'up_audio'   => $audioinfo,
        'up_video'   => $videoinfo,
        'createtime' => time()
    );
    $check = pdo_fetch("SELECT id FROM " . tablename($this->table_teasencefiles) . " WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and tid = '{$tid}' and senceid = '{$senceid}' ");
    if (!empty($check)) {
        pdo_update($this->table_teasencefiles, $into_data, array('id' => $check['id']));
    } else {
        pdo_insert($this->table_teasencefiles, $into_data);
    }
    $result['status'] = true;
    $result['msg']    = "上传成功";
    die(json_encode($result));
} elseif ($operation == 'down_file') {
    $id = intval($_GPC['tid_down']);
    $check = pdo_fetch("SELECT * FROM " . tablename($this->table_teasencefiles) . " WHERE  id='{$id}' ");
    $teacher_name = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE  id='{$check['tid']}' ");
    $sence_name = pdo_fetch("SELECT name FROM " . tablename($this->table_upsence) . " WHERE  id='{$check['senceid']}' ");
    $file_arr = array();
    if (!empty($check['up_word']) && $check['up_word'] != null) {
        $txtname = time() . '-' . $check['senceid'] . '-' . $check['tid'] . '.txt';
        $txtpath_name = IA_ROOT . '/attachment/down/' . $txtname;

        $file = fopen($txtpath_name, "w");
        fwrite($file, $check['up_word']);
        fclose($file);
        $file_arr[] = $txtpath_name;
    }

    if (!empty($check['up_imgs']) && $check['up_imgs'] != 'null') {
        $imgsarr = json_decode($check['up_imgs']);
        if (!empty($imgsarr)) {
            foreach ($imgsarr as $row) {
                if (!empty($row)) {
                    $file_arr[] = IA_ROOT . '/attachment/' . $row;
                }

            }
        }
    }
    if (!empty($check['up_audio']) && $check['up_audio'] != null) {
        $start_this_audio = strstr($check['up_audio'], 'http');
        if ($start_this_audio === false) {
            $file_arr[] = IA_ROOT . '/attachment/' . $check['up_audio'];
        } else {
            $file_arr[] = $check['up_audio'];
        }

    }
    if (!empty($check['up_video']) && $check['up_video'] != null) {
        $start_this_video = strstr($check['up_video'], 'http://');
        $start_this_video_s = strstr($check['up_video'], 'https://');
        if ($start_this_video === false && $start_this_video_s === false) {
            $file_arr[] = IA_ROOT . '/attachment/' . $check['up_video'];
        } else {
            $save_dir      = IA_ROOT . '/attachment/down/';
            $filename_this = basename($check['up_video']);
            $res           = getFile($check['up_video'], $save_dir, $filename_this, 1);
            $this_allpath  = $save_dir . $filename_this;
            $file_arr[]    = $this_allpath;
        }

    }

    $zipname = $teacher_name['tname'] . '-' . $sence_name['name'] . '.zip';
    $res = CreateZipAndDownload($zipname, $file_arr);
    if ($res == "finish") {
        if (!empty($txtpath_name)) {
            $res = unlink($txtpath_name);
        }
        if (!empty($this_allpath)) {
            $res1 = unlink($this_allpath);
        }
    }
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $score = pdo_fetch("SELECT id,ali_vod_id FROM " . tablename($this->table_teasencefiles) . " WHERE id = '{$id}'");
    if (empty($score)) {
        $this->imessage('抱歉，上传记录不存在或是已经被删除！', referer(), 'error');
    }
    if($score['ali_vod_id']){
        mload()->model('ali');
        $aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
        $appid = $aliyun['alivodappid'];
        $key = $aliyun['alivodkey'];
        DelAlivod($appid,$key,$score['ali_vod_id']);
    }
    pdo_delete($this->table_upsence, array('id' => $id), 'OR');
    $this->imessage('上传记录删除成功！', referer(), 'success');
}
include $this->template('web/upsencerecord');
?>