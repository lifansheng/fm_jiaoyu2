<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid = $_W['uniacid'];
$action = 'newmanager';
$this1 = 'no1';
$GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
$logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
$member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
$logo['logo'] = tomedia('headimg_'.$weid.'.jpg');
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$state = uni_permission($_W['uid'], $uniacid);
$versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
require $versionfile;
delvioce('newmanager',FM_JIAOYU_HOST);
$set = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $weid)); 
if ($operation == 'post') {
        $fansgroup = pdo_fetchall("SELECT * FROM " . tablename($this->table_group) . " WHERE weid = '{$weid}'");
        $group = pdo_fetch("select * from " . tablename($this->table_group) . " where id=:id and weid =:weid", array(':id' => $_GPC['group_id'], ':weid' => $weid));
        $qrinfo = pdo_fetch("select * from " . tablename($this->table_qrinfo) . " where group_id=:group_id and weid =:weid", array(':group_id' => $group['group_id'], ':weid' => $weid));	
        $info = pdo_fetch("SELECT rid FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$_GPC['id']}'");
        $keyword = pdo_fetch('SELECT content FROM ' . tablename('rule_keyword') . " WHERE uniacid = :uniacid And rid = :rid ", array(':uniacid' => $weid,':rid' => $info['rid']));	
    if (checksubmit('submit')) {
        load()->func('file');
        $barcode = array(
        'expire_seconds' => '',
        'action_name' => '',
        'action_info' => array(
                        'scene' => array(
                                'scene_id' => ''
                                ),
                            ),
                        );
        $uniacccount = WeAccount::create($weid);
        $id = intval($_GPC['id']);
        if (!empty($id)) {
                $qrcrow = pdo_fetch("SELECT * FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$id}'");
                $keyword = pdo_fetch('SELECT content FROM ' . tablename('rule_keyword') . " WHERE uniacid = :uniacid And rid = :rid ", array(':uniacid' => $weid,':rid' => $qrcrow['rid']));					
                $update = array(
                    'keyword' => $group['name'],
                    'name' => $group['name'],
                    'gpid' => $_GPC['group_id'],
                    'rid' => trim($_GPC['ruleid']),
                    'status' => '1',
                    );
                $barcode['action_info']['scene']['scene_id'] = $qrcrow['qrcid'];
                $barcode['action_name'] = 'QR_LIMIT_SCENE';
                $result = $uniacccount->barCodeCreateFixed($barcode);
                if (is_error($result)) {
                    $this->imessage($result['$this->imessage'], referer(), 'fail');
                }
                $update['ticket'] = $result['ticket'];
                $showurl = $this->createImageUrlCenter("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $group['schoolid']);
                $update['group_id'] = $group['group_id'];
                $urlarr = explode('/',$showurl);
                $update['show_url'] = "images/".$urlarr['3'];
                $update['createtime'] = time();
                    $temp1 = array(
                        'type' => 1
                        );
                        
                pdo_update($this->table_group, $temp1, array('id' => $_GPC['group_id']));
                pdo_update($this->table_qrinfo, $update, array('id' => $id));
                $qrurl = pdo_fetch("SELECT * FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$id}'");
                $arr = explode('/',$qrurl['show_url']);
                $pathname = "images/".$arr['1'];
                if (!empty($_W['setting']['remote']['type'])) { // 
                    $remotestatus = file_remote_upload($pathname); //
                    if (is_error($remotestatus)) {
                        $this->imessage('远程附件上传失败，'.$pathname.'请检查配置并重新上传');
                    }
                }						
                $this->imessage('恭喜，更新带参数二维码成功！', $this->createWebUrl('newmanager'), 'success');
        }
        $qrcid = pdo_fetchcolumn("SELECT qrcid FROM " . tablename($this->table_qrinfo) . " WHERE model = '2' ORDER BY qrcid DESC");
        $barcode['action_info']['scene']['scene_id'] = !empty($qrcid) ? ($qrcid + 1) : 1;
        if ($barcode['action_info']['scene']['scene_id'] > 10000000000) {
            $this->imessage('抱歉，永久二维码已经生成最大数量，请先删除一些。');
        }
        if (!empty($qrinfo)) {
            $this->imessage('抱歉，该学校分组已经拥有独立二维码');
        }
        $barcode['action_name'] = 'QR_LIMIT_SCENE';
        $result = $uniacccount->barCodeCreateFixed($barcode);
        if (is_error($result)) {
        $this->imessage($result['$this->imessage'], referer(), 'fail');
        }
        
        if (!is_error($result)) {
            $showurl = $this->createImageUrlCenter("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $group['schoolid']);
            $urlarr = explode('/',$showurl);
            $qrurls = "images/".$urlarr['3'];
            $insert = array(
            'weid' => $_W['weid'], 
            'qrcid' => $barcode['action_info']['scene']['scene_id'], 
            'keyword' => $group['name'],
            'gpid' => $_GPC['group_id'],
            'name' => $group['name'], 
            'model' => 2,
            'group_id' => $group['group_id'], 
            'rid' => trim($_GPC['ruleid']),
            'ticket' => $result['ticket'], 
            'show_url' => $qrurls,
            'expire' => $result['expire_seconds'], 
            'createtime' => time(),
            'status' => '1',
            );
            $temp2 = array(
            'type' => 1
            );
            pdo_update($this->table_group, $temp2, array('id' => $_GPC['group_id']));
            pdo_insert($this->table_qrinfo, $insert);
            $qrid = pdo_insertid();
            $qrurl = pdo_fetch("SELECT * FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$qrid}'");
            $arr = explode('/',$qrurl['show_url']);
            $pathname = "images/".$arr['1'];
                if (!empty($_W['setting']['remote']['type'])) { // 
                    $remotestatus = file_remote_upload($pathname); //
                        if (is_error($remotestatus)) {
                            $this->imessage('远程附件上传失败，'.$pathname.'请检查配置并重新上传');
                        }
                }				
            $this->imessage('恭喜，生成带参数二维码成功！', $this->createWebUrl('newmanager'), 'success');	
        } else {
            $this->imessage("公众平台返回接口错误. <br />错误代码为: {$result['errorcode']} <br />错误信息为: {$result['$this->imessage']}");
        }
    }
    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT * FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$id}'");
} elseif ($operation == 'display') {
    
    $pindex = max(1, intval($_GPC['page']));
    $psize = 12;

    $condition = '';
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND keyword LIKE '%{$_GPC['keyword']}%'";
    }
    
    $where = "WHERE weid = '{$weid}' and type = 1 ";
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_qrinfo) . " {$where} $condition ORDER BY qrcid DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}");
    if (!empty($list)) {
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_qrinfo) . " $where");
        $pager = pagination($total, $pindex, $psize);  				
    }			
    
} elseif ($operation == 'delete') {

    $id = $_GPC['id'];
        $temp3 = array(
            'type' => 0
            );
    pdo_update($this->table_group, $temp3, array('id' => $_GPC['gpid']));			
    pdo_delete($this->table_qrinfo, array('id' => $id));
    //pdo_delete('qrcode_statinfo', array('qid' => $id));
    $this->imessage('删除成功', $this->createWebUrl('newmanager'), 'success');

} elseif ($operation == 'keyword') {
    if($_W['isajax']) {
        $condition = '';
        $key_word = trim($_GPC['key_word']);
        if(!empty($key_word)) {
            $condition = " AND content LIKE '%{$key_word}%' AND (module = 'news' OR module = 'cover' OR module = 'reply')";
        } else {
            $condition = " AND (module = 'news' OR module = 'cover' OR module = 'reply')";
        }

        $data = pdo_fetchall('SELECT content, module, rid FROM ' . tablename('rule_keyword') . " WHERE uniacid = :uniacid AND status != 0 " . $condition . ' ORDER BY uniacid DESC,displayorder DESC LIMIT 100', array(':uniacid' => $_W['uniacid']));
        $exit_da = array();
        if(!empty($data)) {
            foreach($data as $da) {
                $exit_da[] = array('content' => $da['content'], 'rid' => $da['rid']);
            }
        }
        exit(json_encode($exit_da));
    }	
    exit('error');
}

include $this->template ( 'web/newmanager' );
?>