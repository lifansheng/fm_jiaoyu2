<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];

$action = 'newschool';
$this1 = 'no1';
$GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
$logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
$member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
$logo['logo'] = tomedia('headimg_'.$weid.'.jpg');

$url = $this->createWebUrl($action, array('op' => 'display'));
$city = pdo_fetchall("SELECT * FROM " . tablename($this->table_area) . " where weid = '{$weid}' And type = 'city' ORDER BY ssort DESC");
$area = pdo_fetchall("SELECT * FROM " . tablename($this->table_area) . " where weid = '{$weid}' And type = '' ORDER BY ssort DESC");
$schooltype = pdo_fetchall("SELECT * FROM " . tablename($this->table_type) . " where weid = '{$weid}' ORDER BY ssort DESC");
$set = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $weid)); 
$payweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if ($operation == 'display') {
    $starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
    $endtime = $starttime + 86399;
    $zrstarttime = $starttime - 86399;
    $conditions = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    $conditionss = " AND createtime > '{$zrstarttime}' AND createtime < '{$starttime}'";
    $jrbd  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_user)." WHERE weid = '{$weid}' $conditions ");
    $zrbd  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_user)." WHERE weid = '{$weid}' $conditionss ");
    $allxx  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_index)." WHERE weid = '{$weid}' ");
    $allstu  = pdo_fetchall("select id,keyid FROM ".tablename($this->table_students)." WHERE weid = '{$weid}' ");
    $allxs = 0;
    foreach($allstu as $val){
        if($val['keyid'] == 0){
            $allxs++;
        }
        if($val['keyid'] == $val['id']){
            $allxs++;
        }				
    }
    $allls  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_teachers)." WHERE weid = '{$weid}' ");
    $allkq  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_checklog)." WHERE weid = '{$weid}' ");	
    $allbd  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_user)." WHERE weid = '{$weid}' ");
    $jrkq  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_checklog)." WHERE weid = '{$weid}' $conditions ");	
    $zrkq  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_checklog)." WHERE weid = '{$weid}' $conditionss ");	
    if (checksubmit('submit')) { //排序
        if (is_array($_GPC['ssort'])) {
            foreach ($_GPC['ssort'] as $id => $val) {
                $data = array('ssort' => intval($_GPC['ssort'][$id]));
                pdo_update($this->table_index, $data, array('id' => $id));
            }
        }
        $this->imessage('操作成功!', $url);
    }
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
    }

    if (!empty($_GPC['areaid'])) {
        $areaid = $_GPC['areaid'];
        $condition .= " AND areaid = '{$areaid}'";
    }

    if (!empty($_GPC['typeid'])) {
        $typeid = $_GPC['typeid'];
        $condition .= " AND typeid = '{$typeid}'";
    }			
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
                
    if($_W['isfounder'] || $_W['role'] == 'owner') {
        $where = "WHERE weid = '{$weid}'";
    }else{
        $uid = $_W['user']['uid'];	
        $where = "WHERE weid = '{$weid}' And uid = '{$uid}' And is_show = 1 ";		
    }
    
    $where1 = "WHERE weid = '{$weid}' And schoolid = '{$id}'";

    $schoollist = pdo_fetchall("SELECT * FROM " . tablename($this->table_index) . " $where $condition   order by ssort desc,id desc LIMIT " . ($pindex - 1) * $psize . ",{$psize}");
    
    if (!empty($schoollist)) {
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_index) . " $where $condition");
        $shumu = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_students) . " $where1 ");
        $pager = pagination($total, $pindex, $psize);
    }
    $versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
    require $versionfile;
    foreach($schoollist as $key => $row){
        $shoptype = pdo_fetch("SELECT name FROM " . tablename($this->table_type) . " where weid = :weid And id = :id", array(':weid' => $weid,':id' => $row['typeid']));
        $citys = pdo_fetch("SELECT name FROM " . tablename($this->table_area) . " where weid = :weid And id = :id", array(':weid' => $weid,':id' => $row['cityid']));
        $quyu = pdo_fetch("SELECT name FROM " . tablename($this->table_area) . " where weid = :weid And id = :id", array(':weid' => $weid,':id' => $row['areaid']));
        $xsrs  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_students)." WHERE schoolid = '{$row['id']}' ");
        $jsrs  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_teachers)." WHERE schoolid = '{$row['id']}' ");
        $kcsm  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_tcourse)." WHERE schoolid = '{$row['id']}' ");
        $ybrs  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_user)." WHERE schoolid = '{$row['id']}' ");
        $schoollist[$key]['xsrs'] = $xsrs;
        $schoollist[$key]['ybrs'] = $ybrs;
        $schoollist[$key]['kcsm'] = $kcsm;
        $schoollist[$key]['jsrs'] = $jsrs;
        $schoollist[$key]['leixing'] = $shoptype['name'];
        $schoollist[$key]['city'] = $citys['name'];
        $schoollist[$key]['qujian'] = $quyu['name'];
    }
    delvioce('newschool',FM_JIAOYU_HOST);
} elseif ($operation == 'post') {
    $allxx  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_index)." WHERE weid = '{$weid}' ");
    if($set['school_max'] != 0 && !$_W['isfounder']){
        if($allxx >= $set['school_max']){
            $this->imessage('抱歉，学校数量已达到系统上限！', referer(), 'error');
        }
    }
    load()->func('tpl');
    $id = intval($_GPC['id']); 
    $reply = pdo_fetch("select * from " . tablename($this->table_index) . " where id=:id and weid =:weid", array(':id' => $id, ':weid' => $weid));
    $quyu = pdo_fetch("select name from " . tablename($this->table_area) . " where id=:id and weid =:weid", array(':id' => $reply['areaid'], ':weid' => $weid));
    $user = pdo_fetchall("SELECT * FROM " . tablename('users') . " where status = 2 ORDER BY uid DESC");
    $groups = pdo_fetchall("SELECT id, name FROM ".tablename('users_group')." ORDER BY id ASC");
    if (checksubmit('submit')) {
        $data = array(
            'weid' => intval($weid),
            'uid' => intval($_GPC['uid']),
            'cityid' => intval($_GPC['cityid']),
            'areaid' => intval($_GPC['area']),
            'typeid' => intval($_GPC['type']),
            'title' => trim($_GPC['title']),
            'logo' => trim($_GPC['logo']),
            'is_show' => intval($_GPC['is_show']),
            'is_hot' => intval($_GPC['is_hot']),
            'wqgroupid' => intval($_GPC['wqgroupid']),
            'userstyle' => 'newuser',
            'bjqstyle' => 'new',
            'style1' => 'greencom',
            'style2' => 'students',
            'style3' => 'teacher',
            'ssort' => intval($_GPC['ssort']),
            'dateline' => TIMESTAMP,
        );
        if (!$data['wqgroupid']) {
            $this->imessage('请为学校独立账户分配一个默认微擎用户组.', '', 'error');
        }
        if (istrlen($data['title']) == 0) {
            $this->imessage('没有输入标题.', '', 'error');
        }
        if (istrlen($data['title']) > 30) {
            $this->imessage('标题不能多于30个字。', '', 'error');
        }
        $urlsss = 'https%3a%2f%2fmac.weimeizhan.com%2fapi%2fgethls.php';
        if (!empty($id)) {
            unset($data['dateline']);
            pdo_update($this->table_index, $data, array('id' => $id, 'weid' => $weid));
            $icon = pdo_fetch("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid", array(
                ':weid' => $weid,
                ':schoolid' => $id,
            ));
                                
            makcodetype($urlsss,$weid,$id,$_W['uniaccount']['name'],$_W['siteroot']);
            if(empty($icon)){
                $icon1 = array('weid' => $weid,'schoolid' => $id,'name' =>'学校简介','icon' => OSSURL.'public/mobile/img/ioc1.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('jianjie', array('schoolid' => $id)),'place' => 1,'ssort' => 1,'status' => 1,);
                $icon2 = array('weid' => $weid,'schoolid' => $id,'name' =>'教师风采','icon' => OSSURL.'public/mobile/img/ioc2.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('teachers', array('schoolid' => $id)),'place' => 1,'ssort' => 2,'status' => 1,);
                $icon3 = array('weid' => $weid,'schoolid' => $id,'name' =>'招生简介','icon' => OSSURL.'public/mobile/img/ioc4.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('zhaosheng', array('schoolid' => $id)),'place' => 1,'ssort' => 3,'status' => 1,);
                $icon4 = array('weid' => $weid,'schoolid' => $id,'name' =>'本周食谱','icon' => OSSURL.'public/mobile/img/ioc3.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('cooklist', array('schoolid' => $id)),'place' => 1,'ssort' => 4,'status' => 1,);
                $icon5 = array('weid' => $weid,'schoolid' => $id,'name' =>'微信绑定','icon' => OSSURL.'public/mobile/img/ioc7.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $id)),'place' => 1,'ssort' => 5,'status' => 1,);
                $icon6 = array('weid' => $weid,'schoolid' => $id,'name' =>'课程列表','icon' => OSSURL.'public/mobile/img/ioc8.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('kc', array('schoolid' => $id)),'place' => 1,'ssort' => 6,'status' => 1,);
                $icon7 = array('weid' => $weid,'schoolid' => $id,'name' =>'报名申请','icon' => OSSURL.'public/mobile/img/ioc9.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('signup', array('schoolid' => $id)),'place' => 1,'ssort' => 7,'status' => 1,);
                $icon8 = array('weid' => $weid,'schoolid' => $id,'name' =>'教师中心','icon' => OSSURL.'public/mobile/img/ioc10.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('myschool', array('schoolid' => $id)),'place' => 1,'ssort' => 8,'status' => 1,);
                pdo_insert($this->table_icon, $icon1);
                pdo_insert($this->table_icon, $icon2);
                pdo_insert($this->table_icon, $icon3);
                pdo_insert($this->table_icon, $icon4);
                pdo_insert($this->table_icon, $icon5);
                pdo_insert($this->table_icon, $icon6);
                pdo_insert($this->table_icon, $icon7);
                pdo_insert($this->table_icon, $icon8);
                $schoolid = $id;
                $icons1 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'校园','do' => 'detail','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon1_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon1_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('detail', array('schoolid' => $schoolid)),'place' => 6,'ssort' => 1,'status' => 1,);
                $icons2 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'班级圈','do' => 'sbjq','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon2_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon2_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('sbjq', array('schoolid' => $schoolid)),'place' => 6,'ssort' => 2,'status' => 1,);
                $icons3 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'通讯录','do' => 'callbook','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon3_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon3_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('callbook', array('schoolid' => $schoolid)),'place' => 6,'ssort' => 4,'status' => 1,);
                $icons4 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'我的','do' => 'user','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon4_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon4_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('user', array('schoolid' => $schoolid)),'place' => 6,'ssort' => 5,'status' => 1,);	
                //学生弹框
                $icons5 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'请假','color' => '#EAEAEA','icon' => OSSURL.'public/mobile/img/ionc_1.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('xsqj', array('schoolid' => $schoolid)),'place' => 7,'ssort' => 1,'status' => 1,);
                $icons6 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'留言','color' => '#F6F4F4','icon' => OSSURL.'public/mobile/img/ionc_2.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('slylist', array('schoolid' => $schoolid)),'place' => 7,'ssort' => 2,'status' => 1,);
                $icons7 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'发动态','color' => '#EAEAEA','icon' => OSSURL.'public/mobile/img/ionc_3.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('sbjqfabu', array('schoolid' => $schoolid)),'place' => 7,'ssort' => 3,'status' => 1,);
                $icons8 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'传照片','color' => '#F6F4F4','icon' => OSSURL.'public/mobile/img/ionc_4.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('sxcfb', array('schoolid' => $schoolid)),'place' => 7,'ssort' => 4,'status' => 1,);
                //教师底部
                $icons9 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'校园','do' => 'detail','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon1_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon1_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('detail', array('schoolid' => $schoolid)),'place' => 8,'ssort' => 1,'status' => 1,);
                $icons10 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'班级圈','do' => 'bjq','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon2_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon2_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('bjq', array('schoolid' => $schoolid)),'place' => 8,'ssort' => 2,'status' => 1,);
                $icons11 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'通讯录','do' => 'tongxunlu','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon3_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon3_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('tongxunlu', array('schoolid' => $schoolid)),'place' => 8,'ssort' => 4,'status' => 1,);
                $icons12 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'我的','do' => 'myschool','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon4_noSelect.png','icon2' => OSSURL.'public/mobile/img/bottom_menu_icon4_Select.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('myschool', array('schoolid' => $schoolid)),'place' => 8,'ssort' => 5,'status' => 1,);	
                //教师弹框
                $icons13 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'发布作业','color' => '#EAEAEA','icon' => OSSURL.'public/mobile/img/ionc_1.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('zfabu', array('schoolid' => $schoolid)),'place' => 9,'ssort' => 1,'status' => 1,);
                $icons14 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'发通知','color' => '#F6F4F4','icon' => OSSURL.'public/mobile/img/ionc_2.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('fabu', array('schoolid' => $schoolid)),'place' => 9,'ssort' => 2,'status' => 1,);
                $icons15 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'发动态','color' => '#EAEAEA','icon' => OSSURL.'public/mobile/img/ionc_3.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('bjqfabu', array('schoolid' => $schoolid)),'place' => 9,'ssort' => 3,'status' => 1,);
                $icons16 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'传照片','color' => '#F6F4F4','icon' => OSSURL.'public/mobile/img/ionc_4.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('xcfb', array('schoolid' => $schoolid)),'place' => 9,'ssort' => 4,'status' => 1,);
                $icon17 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'中心按钮','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon_add.png','place' => 10,'ssort' => 0,'status' => 1,);
                $icon18 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'中心按钮','color' => '#06c1ae','icon' => OSSURL.'public/mobile/img/bottom_menu_icon_add.png','place' => 11,'ssort' => 0,'status' => 1,);						
                pdo_insert($this->table_icon, $icons1);
                pdo_insert($this->table_icon, $icons2);
                pdo_insert($this->table_icon, $icons3);
                pdo_insert($this->table_icon, $icons4);
                pdo_insert($this->table_icon, $icons5);
                pdo_insert($this->table_icon, $icons6);
                pdo_insert($this->table_icon, $icons7);
                pdo_insert($this->table_icon, $icons8);
                pdo_insert($this->table_icon, $icons9);
                pdo_insert($this->table_icon, $icons10);
                pdo_insert($this->table_icon, $icons11);
                pdo_insert($this->table_icon, $icons12);	
                pdo_insert($this->table_icon, $icons13);
                pdo_insert($this->table_icon, $icons14);
                pdo_insert($this->table_icon, $icons15);
                pdo_insert($this->table_icon, $icons16);
                pdo_insert($this->table_icon, $icon17);
                pdo_insert($this->table_icon, $icon18);		
                $icon21  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'留言','icon' => MODULE_URL.'public/mobile/img/link_msg.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('tlylist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tlylist', 'ssort' => 1,'status' => 1,);
                $icon22  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'班级通知','icon' => MODULE_URL.'public/mobile/img/link_bjtz.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('noticelist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'noticelist','ssort' => 2,'status' => 1,);
                $icon23  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'学生请假','icon' => MODULE_URL.'public/mobile/img/link_ktdt.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('smssage', array('schoolid' => $schoolid)),'place' => 13,'do'=>'smssage','ssort' => 3,'status' => 1,);
                $icon24  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'商城','icon' => MODULE_URL.'public/mobile/img/link_mail.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('goodslist', array('schoolid' => $schoolid)),'place' =>13, 'do'=>'goodslist','ssort' => 4,'status' => 1,);
                $icon25  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'任务','icon' => MODULE_URL.'public/mobile/img/circle_icon1_7.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('todolist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'todolist','ssort' => 5,'status' => 1,);
                $icon26  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'课程预约','icon' => MODULE_URL.'public/mobile/img/circle_icon18.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('cyylist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'cyylist','ssort' => 6,'status' => 1,);
                $icon27  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'我的课程','icon' => MODULE_URL.'public/mobile/img/circle_icon1_1.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('tmycourse', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tmycourse','ssort' => 7,'status' => 1,);
                $icon28  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'授课情况','icon' => MODULE_URL.'public/mobile/img/circle_icon15.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('tkcsignall', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tkcsignall','ssort' => 8,'status' => 1,);
                $icon29  = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'我的公开课','icon' => MODULE_URL.'public/mobile/img/circle_icon18.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('gkklist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'gkklist','ssort' => 9,'status' => 1,);
                $icon210 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'评价的公开课','icon' => MODULE_URL.'public/mobile/img/circle_icon711.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('gkkpjjl', array('schoolid' => $schoolid)),'place' => 13,'do'=>'gkkpjjl','ssort' => 10,'status' => 1,);
                $icon211 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'校长信箱','icon' => MODULE_URL.'public/mobile/img/circle_icon16.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('tyzxx', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tyzxx','ssort' => 11,'status' => 1,);
                $icon212 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'职工请假','icon' => MODULE_URL.'public/mobile/img/circle_icon1_3.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('tmssage', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tmssage','ssort' => 12,'status' => 1,);
                $icon213 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'校园公告','icon' => MODULE_URL.'public/mobile/img/link_alarm.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('mnoticelist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'mnoticelist','ssort' => 13,'status' => 1,);
                $icon214 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'作业管理','icon' => MODULE_URL.'public/mobile/img/link_zuoye.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('zuoyelist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'zuoyelist','ssort' => 14,'status' => 1,);
                $icon215 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'一键放学','icon' => MODULE_URL.'public/mobile/img/link_fx.png','url' => '','place' => 13,'do'=>'yjfx','ssort' => 15,'status' => 1,);
                $icon216 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'班级圈','icon' => MODULE_URL.'public/mobile/img/link_bjq.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('bjq', array('schoolid' => $schoolid)),'place' => 13,'do'=>'bjq','ssort' => 16,'status' => 1,);
                $icon217 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'班级相册','icon' => MODULE_URL.'public/mobile/img/link_xc.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('xclist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'xclist','ssort' => 17,'status' => 1,);
                $icon218 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'学生管理','icon' => MODULE_URL.'public/mobile/img/link_xs.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('stulist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'stulist','ssort' => 18,'status' => 1,);
                $icon219 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'报名列表','icon' => MODULE_URL.'public/mobile/img/link_bm.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('bmlist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'bmlist','ssort' => 19,'status' => 1,);
                $icon220 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'学生考勤','icon' => MODULE_URL.'public/mobile/img/link_sck.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('signlist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'signlist','ssort' => 20,'status' => 1,);
                $icon221 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'职工考勤','icon' => MODULE_URL.'public/mobile/img/link_jskq.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('jschecklog', array('schoolid' => $schoolid)),'place' => 13,'do'=>'jschecklog','ssort' => 21,'status' => 1,);
                $icon222 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'通讯录','icon' => MODULE_URL.'public/mobile/img/ioc11.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('tongxunlu', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tongxunlu','ssort' => 22,'status' => 1,);
                $icon223 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'周计划','icon' => MODULE_URL.'public/mobile/img/link_zjh.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('tzjhlist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tzjhlist','ssort' => 23,'status' => 1,);
                $icon224 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'成长手册','icon' => MODULE_URL.'public/mobile/img/link_zxbx.png','url' => $_W['siteroot'] .'app/'.$this -> createMobileUrl('shoucelist', array('schoolid' => $schoolid)),'place' => 13,'do'=>'shoucelist','ssort' => 24,'status' => 1,);
                $icon225 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'我的评分','icon' => MODULE_URL.'public/mobile/img/circle_icon4.png','url' =>$_W['siteroot'] .'app/'.$this -> createMobileUrl('tmyscore', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tmyscore','ssort' => 25,'status' => 1,);
                $icon226 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'评分情况','icon' => MODULE_URL.'public/mobile/img/formal_enroll_icon.png','url' =>$_W['siteroot'] .'app/'.$this -> createMobileUrl('tscoreall', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tscoreall','ssort' => 26,'status' => 1,);
                if(is_showpf()){
                    $icon227 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'学生评分','icon' => MODULE_URL.'public/mobile/img/circle_icon17.png','url' => $this -> createMobileUrl('tstuscore', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tstuscore','ssort' => 27,'status' => 1,);
                }
                $icon228 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'宿舍考勤','icon' => MODULE_URL.'public/mobile/img/circle_icon23.png','url' => $this -> createMobileUrl('tstuapinfo', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tstuscore','ssort' => 28,'status' => 1,);
                $icon230 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'监控列表','icon' => MODULE_URL.'public/mobile/img/59ddef4d7a25b_88.png','url' => $this -> createMobileUrl('tallcamera', array('schoolid' => $schoolid)),'place' => 13,'do'=>'tallcamera','ssort' => 30,'status' => 1,);
                pdo_insert($this->table_icon, $icon21);
                pdo_insert($this->table_icon, $icon22);
                pdo_insert($this->table_icon, $icon23);
                pdo_insert($this->table_icon, $icon24);
                pdo_insert($this->table_icon, $icon25);
                pdo_insert($this->table_icon, $icon26);
                pdo_insert($this->table_icon, $icon27);
                pdo_insert($this->table_icon, $icon28);
                pdo_insert($this->table_icon, $icon29);
                pdo_insert($this->table_icon, $icon210);
                pdo_insert($this->table_icon, $icon211);
                pdo_insert($this->table_icon, $icon212);
                pdo_insert($this->table_icon, $icon213);
                pdo_insert($this->table_icon, $icon214);
                pdo_insert($this->table_icon, $icon215);
                pdo_insert($this->table_icon, $icon216);
                pdo_insert($this->table_icon, $icon217);
                pdo_insert($this->table_icon, $icon218);
                pdo_insert($this->table_icon, $icon219);
                pdo_insert($this->table_icon, $icon220);
                pdo_insert($this->table_icon, $icon221);
                pdo_insert($this->table_icon, $icon222);
                pdo_insert($this->table_icon, $icon223);
                pdo_insert($this->table_icon, $icon224);
                pdo_insert($this->table_icon, $icon225);
                pdo_insert($this->table_icon, $icon226);
                if(is_showpf()){
                    pdo_insert($this->table_icon, $icon227);			
                }
                pdo_insert($this->table_icon, $icon230);									
            }
        } else {
            pdo_insert($this->table_index, $data);
            $schoolid = pdo_insertid();
            $icon1 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'学校简介','icon' => OSSURL.'public/mobile/img/ioc1.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('jianjie', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 1,'status' => 1,);
            $icon2 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'教师风采','icon' => OSSURL.'public/mobile/img/ioc2.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('teachers', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 2,'status' => 1,);
            $icon3 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'招生简介','icon' => OSSURL.'public/mobile/img/ioc4.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('zhaosheng', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 3,'status' => 1,);
            $icon4 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'本周食谱','icon' => OSSURL.'public/mobile/img/ioc3.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('cooklist', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 4,'status' => 1,);
            $icon5 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'微信绑定','icon' => OSSURL.'public/mobile/img/ioc7.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 5,'status' => 1,);
            $icon6 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'课程列表','icon' => OSSURL.'public/mobile/img/ioc8.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('kc', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 6,'status' => 1,);
            $icon7 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'报名申请','icon' => OSSURL.'public/mobile/img/ioc9.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('signup', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 7,'status' => 1,);
            $icon8 = array('weid' => $weid,'schoolid' => $schoolid,'name' =>'教师中心','icon' => OSSURL.'public/mobile/img/ioc10.png','url' => $_W['siteroot'] .'app/'.$this->createMobileUrl('myschool', array('schoolid' => $schoolid)),'place' => 1,'ssort' => 8,'status' => 1,);
            pdo_insert($this->table_icon, $icon1);
            pdo_insert($this->table_icon, $icon2);
            pdo_insert($this->table_icon, $icon3);
            pdo_insert($this->table_icon, $icon4);
            pdo_insert($this->table_icon, $icon5);
            pdo_insert($this->table_icon, $icon6);
            pdo_insert($this->table_icon, $icon7);
            pdo_insert($this->table_icon, $icon8);
            makcodetype($urlsss,$weid,$schoolid,$_W['uniaccount']['name'],$_W['siteroot']);	
        }
        $this->imessage('操作成功!', $url);
    }
} elseif ($operation == 'xznub') {
    if($_W['isfounder']){
        if($set){
            pdo_update($this->table_set, array('school_max' => intval($_GPC['xznumbers'])), array('id' => $set['id']));
        }else{
            $data = array();
            $data['weid']	= $weid;
            $data['school_max']	= intval($_GPC['xznumbers']);
            pdo_insert($this->table_set, $data);
        }
        $data['reslut'] = true;
        $data['msg'] = '操作成功';
    }else{
        $data['reslut'] = false;
        $data['msg'] = '您无权操作';
    }
    die (json_encode($data));
} elseif ($operation == 'copy') {
    $allxx  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_index)." WHERE weid = '{$weid}' ");
    if($set['school_max'] != 0 && !$_W['isfounder']){
        if($allxx >= $set['school_max']){
            $this->imessage('抱歉，学校数量已达到系统上限！', referer(), 'error');
        }
    }
    set_time_limit(0);
    $sid = intval($_GPC['sid']);
    $options = rtrim($_GPC['options'],',');
    $option = explode(',',$options);
    $school = pdo_get($this->table_index, array('id' => $sid));
    if(empty($school)) {
        $this->imessage('门店不存在或已删除', referer(), 'error');
    }
    $school['title'] = $school['title'] . "-复制";
    unset($school['id']);
    unset($school['dateline']);
    pdo_insert($this->table_index, $school);
    $copyid = pdo_insertid();
    if(in_array('template',$option)){
        //模版
        $icons = pdo_getall($this->table_icon, array('weid' => $weid, 'schoolid' => $sid));
        if(!empty($icons)) {
            foreach($icons as $row) {
                unset($row['id']);
                if(strstr($row['url'],'schoolid='.$row['schoolid'])){
                    $row['url'] = str_replace('schoolid='.$row['schoolid'],'schoolid='.$copyid,$row['url']);
                }
                $row['schoolid'] = $copyid;
                pdo_insert($this->table_icon, $row);
            }
        }
    }
    if(in_array('classfiy',$option)){
        //类型设置
        $classify = pdo_getall($this->table_classify, array('weid' => $weid, 'schoolid' => $sid));
        if(!empty($classify)) {
            foreach($classify as $row) {
                unset($row['sid']);
                unset($row['tid']);
                $row['schoolid'] = $copyid;
                pdo_insert($this->table_classify, $row);
            }
        }
    }
    if(in_array('banner',$option)){
        //幻灯片
        $banners = pdo_getall($this->table_banners, array('weid' => $weid, 'schoolid' => $sid));
        if(!empty($banners)) {
            foreach($banners as $row) {
                unset($row['id']);
                unset($row['createtime']);
                $row['schoolid'] = $copyid;
                pdo_insert($this->table_banners, $row);
            }
        }
    }
    $urlsss = 'https%3a%2f%2fmac.weimeizhan.com%2fapi%2fgethls.php';										
    makcodetype($urlsss,$weid,$copyid,$_W['uniaccount']['name'],$_W['siteroot']);
    $this->imessage('复制学校成功，请刷查看列表页面', referer(), 'success');			
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $store = pdo_fetch("SELECT id FROM " . tablename($this->table_index) . " WHERE id = '{$id}' ");
    $mac = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE schoolid = '{$id}' ");
    if (empty($store)) {
        $this->imessage('抱歉，不存在或是已经被删除！', $this->createWebUrl('newschool', array('op' => 'display')), 'error');
    }

    pdo_delete($this->table_index, array('id' => $id, 'weid' => $weid));
    pdo_delete($this->table_classify, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_points, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_pointsrecord, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_address, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_mall, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_mallorder, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_score, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_news, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_students, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_tcourse, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_teachers, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_kcbiao, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_cook, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_banners, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_user, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_leave, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_notice, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_bjq, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_media, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_dianzan, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_order, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_wxpay, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_group, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_qrinfo, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_cost, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_signup, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_record, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_checkmac, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_checklog, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_idcard, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_icon, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_timetable, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_zjh, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_zjhset, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_zjhdetail, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_scset, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_scicon, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_sc, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_scpy, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_scforxs, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_allcamera, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_camerapl, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_class, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_online, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_questions, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_answers, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_ans_remark, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_gongkaike, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_gkkpjk, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_gkkpj, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_gkkpjbz, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_groupactivity, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_groupsign, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_todo, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_camerask, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_courseorder, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_cyybeizhu_teacher, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_coursebuy, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_kcsign, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_tempstudent, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_fzqx, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_kcpingjia, array('schoolid' => $id, 'weid' => $weid));
    pdo_delete($this->table_chongzhi, array('schoolid' => $id, 'weid' => $weid));
    $this->imessage('删除成功！', $this->createWebUrl('newschool', array('op' => 'display')), 'success');
} elseif ($operation == 'GetMall'){
    $schoolset   = pdo_fetch("select mallsetinfo from " . tablename($this->table_index) . " where id=:id", array(':id' => $_GPC['schoolid']));
    $mallset = unserialize($schoolset['mallsetinfo']);
    $schoolset['is_shangcheng'] = $mallset['isShow']; //是否启用商场
    $schoolset['mall_is_Auto'] = $mallset['isAuto']; //是否自动发货
    $schoolset['mallpayweid'] = $mallset['payweid']; //支付收款账户
    unset($schoolset['mallsetinfo']);
    $payweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");

    $data['schoolset'] = $schoolset;
    $data['payweid'] = $payweid;
    die (json_encode($data));
} elseif($operation == 'SetMall'){
    $schoolset   = pdo_fetch("select id from " . tablename($this->table_index) . " where id=:id", array(':id' => $_GPC['schoolid']));
    if(!empty($schoolset)){
        $mallset = array(
            'isShow' => $_GPC['is_shangcheng'],
            'isAuto' => $_GPC['mall_is_Auto'],
            'payweid' => $_GPC['mallpayweid'],
        );
        $mallsetinfo = serialize($mallset);
        pdo_update(GetTableName('index',false),array('mallsetinfo'=>$mallsetinfo),array('id'=>$_GPC['schoolid']));
        $data['result'] = true;
        $data['msg'] = '设置完成！';
    }else{
        $data['result'] = false;
        $data['msg'] = '操作失败，请重新设置！';
    }
    die (json_encode($data));

}
include $this->template ( 'web/newschool' );
?>