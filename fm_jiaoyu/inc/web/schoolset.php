<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$this1             = 'no1';
$action            = 'schoolset';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,is_openht FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$city              = pdo_fetchall("SELECT * FROM " . tablename($this->table_area) . " where weid = '{$weid}' And type = 'city' ORDER BY ssort DESC");
$area              = pdo_fetchall("SELECT * FROM " . tablename($this->table_area) . " where weid = '{$weid}' And type = '' ORDER BY ssort DESC");
$schooltype        = pdo_fetchall("SELECT * FROM " . tablename($this->table_type) . " where weid = '{$weid}' ORDER BY ssort DESC");
$is_xz = pdo_fetch("SELECT id FROM " . tablename($this->table_checkmac) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND macname = 2");
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'post';

//权限控制所需
$tid = $_W['tid'];
if($tid !='founder' && $tid != 'owner'){
	$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid));
	$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
}
$CheckSchoolDefault = pdo_fetch("SELECT id FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");
if(empty($CheckSchoolDefault)){
    $datadefault['schoolid'] = trim($schoolid);
    $datadefault['weid'] = trim($weid);
    pdo_insert(GetTableName('schoolset',false), $datadefault);
}
//if(strstr($qxarr,'100010')){
//	echo "yes";
//}
//die();

if(keep_DD()){
    $doorman = pdo_fetchAll("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' AND lxdoorman= 1 ");
    $doorman = arrayToString($doorman);
}
if($operation == 'post'){
	mload()->model('tea');
    $list = getalljsfzallteainfo($schoolid,0,$schooltype);
    $list2 = getalljsfzallteainfo_nofz($schoolid,$schooltype);
	//设置权限标识
	if (!empty($_W['tid']) && !($_W['isfounder'] || $_W['role'] == 'owner')){
		$level = 'not' ;
		if (IsHasQx($tid,1000101,1,$schoolid)){
			$tab_basic_li = 1;
			if ($level == 'not'){
				$level = 'tab_basic' ;
			}
		}
		if (IsHasQx($tid,1000103,1,$schoolid)){
			$tab_gongn_li = 1 ;
			if ($level == 'not'){
				$level = 'tab_gongn' ;
			}
		}
		if (IsHasQx($tid,1000105,1,$schoolid)){
			$tab_baom_li = 1 ;
			if ($level == 'not'){
				$level = 'tab_baom' ;
			}
		}
		if (IsHasQx($tid,1000107,1,$schoolid)){
			$tab_shid_li = 1 ;
			if ($level == 'not'){
				$level = 'tab_shid' ;
			}
		}
		if (IsHasQx($tid,1000109,1,$schoolid)){
			$tab_sms_li = 1 ;
			if ($level == 'not'){
				$level = 'tab_sms' ;
			}
		}
		if (IsHasQx($tid,1000110,1,$schoolid)){
			$tab_pic_li = 1 ;
			if ($level == 'not'){
				$level = 'tab_pic' ;
			}
		}
	}elseif($_W['isfounder'] || $_W['role'] == 'owner'){
		$tab_basic_li  = 1 ;
		$tab_gongn_li  = 1 ;
		$tab_baom_li   = 1 ;
		$tab_shid_li   = 1 ;
		$tab_pic_li   = 1 ; //新增
		$tab_sms_li    = 1 ;
		$level = 'tab_basic' ;
	}

    $id      = intval($_GPC['schoolid']);
    $reply   = pdo_fetch("select * from " . tablename($this->table_index) . " where id=:id and weid =:weid", array(':id' => $schoolid, ':weid' => $weid));
    $alydb   = pdo_fetch("select * from " . tablename($this->table_schoolset) . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
 
    $shareinfo = unserialize($alydb['shareinfo']);
    /**********定制，病因填写字段相关处理*************/
    if(keep_Blacklist()){
        $ByData = unserialize($alydb['bingyincontent']);
    }
    /**********定制，病因填写字段相关处理*************/
    $alydb['fxtitle'] = $shareinfo['fxtitle'];
    $alydb['fxdescription'] = $shareinfo['fxdescription'];
    $alydb['fxpic'] = $shareinfo['fxpic'];
    if(assets()){
        $gwtidnamelist = '';
        $gwtidarr= explode(',',$alydb['gwtidarr']);
        foreach($gwtidarr as $row){
            $teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$row}'");
            if($teacher){
                $gwtidnamelist .= $teacher['tname'].',';
            }
        }
        $gwtidnamelist = rtrim($gwtidnamelist,',');

        $csydtidnamelist = '';
        $csydtidarr= explode(',',$alydb['csydtidarr']);
        foreach($csydtidarr as $row){
            $teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$row}'");
            if($teacher){
                $csydtidnamelist .= $teacher['tname'].',';
            }
        }
        $csydtidnamelist = rtrim($csydtidnamelist,',');

    }

    $zyvideolimit = pdo_fetch("select id,zyvideolimit,remindday from " . tablename($this->table_schoolset) . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
    $reply['zyvideolimit'] = $zyvideolimit['zyvideolimit'];
    $reply['remindday'] = $zyvideolimit['remindday'];
    $mallset = unserialize($reply['mallsetinfo']);
	$picarrSet_out = unserialize($reply['picarrset']);
	$textarrSet_out = unserialize($reply['textarrset']);
	$shareset_from =  unserialize($reply['shareset']);
    $reply['is_shangcheng'] = $mallset['isShow'];
    $reply['mall_is_Auto'] = $mallset['isAuto'];
    $reply['mallpayweid'] = $mallset['payweid'];
    if(is_showgkk())
        $fxlocation = json_decode($reply['fxlocation'],true);
    //var_dump($fxlocation);
    $groups = pdo_fetchall("SELECT id, name FROM ".tablename('users_group')." ORDER BY id ASC");
    $quyu    = pdo_fetch("select name from " . tablename($this->table_area) . " where id=:id and weid =:weid", array(':id' => $reply['areaid'], ':weid' => $weid));
    $payweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");
    $teachers = pdo_fetchall("SELECT id,tname,status FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid ORDER BY  CONVERT(tname USING gbk) ASC ", array(':weid' => $weid,':schoolid' => $schoolid));
    if(keep_hxy()){
        $typt_admin = pdo_fetchall("SELECT id,tname,status FROM " . GetTableName('teachers') . " where weid = :weid And schoolid = :schoolid and typt_is_admin = 1 ORDER BY  CONVERT(tname USING gbk) ASC ", array(':weid' => $weid,':schoolid' => $schoolid));
    }

    $user    = pdo_fetchall("SELECT * FROM " . tablename('users') . " where status = 2 ORDER BY uid DESC");
    $icon    = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = 1 ORDER by ssort ASC", array(':weid' => $weid, ':schoolid' => $schoolid));
    $sign    = unserialize($reply['signset']);
    $card    = unserialize($reply['cardset']);
    if(!empty($reply['checksendset'])){
        $checksendset =unserialize($reply['checksendset']);
    }else{
        $checksendset = array('students','parents','head_teacher');
    }
    /*** 新增相册分类*/
    if(!empty($alydb['teapictype'])){
        $teapictype =unserialize($alydb['teapictype']);
    }else{
        $teapictype = array('teaactivity','tealife','teaworks');
    }
    if(!empty($alydb['stupictype'])){
        $stupictype =unserialize($alydb['stupictype']);
    }else{
        $stupictype = array('stuworks','stulife','stumyinschool','stumyinhome');
    }
    /*** 新增相册分类*/

	$sms_set = get_school_sms_set($schoolid);
	$this_chrgesetInfo = unserialize($reply['chargesetinfo']);
	$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid,':is_over'=>"2"));
	$nj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid,':is_over'=>"2"));
	$bj_str_temp = '0,';
	foreach($bj as $key_b=>$value_b){
		$bj_str_temp .=$value_b['sid'].",";
	}
	$bj_str = trim($bj_str_temp,",");
	$nj_str_temp = '0,';
	foreach($nj as $key_n=>$value_n){
		$nj_str_temp .=$value_n['sid'].",";
	}
	$nj_str = trim($nj_str_temp,",");
    $kclist = pdo_fetchall("SELECT id,name FROM " . tablename($this->table_tcourse) . " WHERE weid = :weid AND schoolid =:schoolid AND is_show = :is_show and FIND_IN_SET(bj_id,:bj_str) and FIND_IN_SET(xq_id,:nj_str)   ORDER BY end DESC", array(':weid' => $_W['uniacid'], ':schoolid' => $schoolid, ':is_show' => 1,':bj_str'=>$bj_str,':nj_str'=>$nj_str));
    // ****************************** 保存数据 *******************************
    if(checksubmit('submit')){
        if(is_showgkk())
        {

            if($_GPC['wxsignrange'] != 0 && $_GPC['wxsignrange'] <= 199){
                $this->imessage('微信签到范围不正确，请输入0或不小于200的数值', referer(), 'error');
            }
        }
        if($_GPC['Is_star'] == 1 ){
            $Is_star = 1;
        }elseif($_GPC['Is_star'] == null ){
             $Is_star = 0;
        }
        if($_GPC['Is_chongzhi'] == 1 ){
            $Is_chongzhi = 1;
        }elseif($_GPC['Is_chongzhi'] == null ){
             $Is_chongzhi = 0;
        }
        if($_GPC['is_stuewcode'] == 1 ){
            $is_stuewcode = 1;
        }elseif($_GPC['is_stuewcode'] == null ){
            $is_stuewcode = 2;
        }
        if($_GPC['is_fbnew'] == 1 ){
            $is_fbnew = 1;
        }elseif($_GPC['is_fbnew'] == null ){
            $is_fbnew = 2;
        }
        if($_GPC['is_shoufei'] == 1 ){
            $is_shoufei = 1;
        }elseif($_GPC['is_shoufei'] == null ){
            $is_shoufei = 0;
        }
         if($_GPC['is_fbnew'] == 1 ){
            $is_fbnew = 1;
        }elseif($_GPC['is_fbnew'] == null ){
            $is_fbnew = 2;
        }
        $kcshare = $_GPC['kcshare'];
        $shareset = array();
        if($kcshare == 0){
            $shareset['is_share'] = 0;
        }elseif($kcshare == 1){
            $shareset['is_share'] = 1;
            $shareset['addJF'] = $_GPC['shareAddJF'];
        }elseif($kcshare == 2){
            $shareset['is_share'] = 2;
            $shareset['addYE'] = $_GPC['shareAddYE'];
        }elseif($kcshare == 3){
            $shareset['is_share'] = 3;
            $shareset['addKC'] = 0;
            $shareset['addKS'] = $_GPC['shareAddKS'];
        }
        $shareset_str = serialize($shareset);
        $picarrset = array();
        $textarrset = array();
        $piclen = 0;
        $textlen = 0 ;
        if($_GPC['is_picarr1'] == 1 ){
            $picarrset['is_picarr1'] = 1;
            $piclen = 1;
            $picarrset['picarr1_name'] = $_GPC['picarr1_name'];
            if($_GPC['is_picarr1_must'] == 1 ){
                $picarrset['is_picarr1_must'] = 1;
            }elseif($_GPC['is_picarr1_must'] == null ){
                $picarrset['is_picarr1_must'] = 0;
            }
        }elseif($_GPC['is_picarr1'] == null ){
            $picarrset['is_picarr1'] = 0;
        }

        if($_GPC['is_picarr2'] == 1 ){
            $picarrset['is_picarr2'] = 1;
            $piclen = 1;
            $picarrset['picarr2_name'] = $_GPC['picarr2_name'];
            if($_GPC['is_picarr2_must'] == 1 ){
                $picarrset['is_picarr2_must'] = 1;
            }elseif($_GPC['is_picarr2_must'] == null ){
                $picarrset['is_picarr2_must'] = 0;
            }
        }elseif($_GPC['is_picarr2'] == null ){
            $picarrset['is_picarr2'] = 0;
        }

        if($_GPC['is_picarr3'] == 1 ){
            $picarrset['is_picarr3'] = 1;
            $piclen = 1;
            $picarrset['picarr3_name'] = $_GPC['picarr3_name'];
            if($_GPC['is_picarr3_must'] == 1 ){
                $picarrset['is_picarr3_must'] = 1;
            }elseif($_GPC['is_picarr3_must'] == null ){
                $picarrset['is_picarr3_must'] = 0;
            }
        }elseif($_GPC['is_picarr3'] == null ){
            $picarrset['is_picarr3'] = 0;
        }

        if($_GPC['is_picarr4'] == 1 ){
            $picarrset['is_picarr4'] = 1;
            $piclen = 1;
            $picarrset['picarr4_name'] = $_GPC['picarr4_name'];
            if($_GPC['is_picarr4_must'] == 1 ){
                $picarrset['is_picarr4_must'] = 1;
            }elseif($_GPC['is_picarr4_must'] == null ){
                $picarrset['is_picarr4_must'] = 0;
            }
        }elseif($_GPC['is_picarr4'] == null ){
            $picarrset['is_picarr4'] = 0;
        }
         if($_GPC['is_picarr5'] == 1 ){
            $picarrset['is_picarr5'] = 1;
            $piclen = 1;
            $picarrset['picarr5_name'] = $_GPC['picarr5_name'];
            if($_GPC['is_picarr5_must'] == 1 ){
                $picarrset['is_picarr5_must'] = 1;
            }elseif($_GPC['is_picarr5_must'] == null ){
                $picarrset['is_picarr5_must'] = 0;
            }
        }elseif($_GPC['is_picarr5'] == null ){
            $picarrset['is_picarr5'] = 0;
        }

        if($_GPC['is_textarr1'] == 1 ){
            $textarrset['is_textarr1'] = 1;
            $textlen = 1;
            $textarrset['textarr1_name'] = $_GPC['textarr1_name'];
            $textarrset['textarr1_length'] = $_GPC['textarr1_length']? $_GPC['textarr1_length']:50;
            if($_GPC['is_textarr1_must'] == 1 ){
                 $textarrset['is_textarr1_must'] = 1;
            }elseif($_GPC['is_textarr1_must'] == null ){
                 $textarrset['is_textarr1_must'] = 0;
            }
        }elseif($_GPC['is_textarr1'] == null ){
            $textarrset['is_textarr1'] = 0;
        }

        if($_GPC['is_textarr2'] == 1 ){
            $textarrset['is_textarr2'] = 1;
            $textlen = 1;
            $textarrset['textarr2_name'] = $_GPC['textarr2_name'];
            $textarrset['textarr2_length'] = $_GPC['textarr2_length']? $_GPC['textarr2_length']:50;
            if($_GPC['is_textarr2_must'] == 1 ){
                $textarrset['is_textarr2_must'] = 1;
            }elseif($_GPC['is_textarr2_must'] == null ){
                 $textarrset['is_textarr2_must'] = 0;
            }
        }elseif($_GPC['is_textarr2'] == null ){
            $textarrset['is_textarr2'] = 0;
        }

        if($_GPC['is_textarr3'] == 1 ){
            $textarrset['is_textarr3'] = 1;
            $textlen = 1;
            $textarrset['textarr3_name'] = $_GPC['textarr3_name'];
            $textarrset['textarr3_length'] = $_GPC['textarr3_length']? $_GPC['textarr3_length']:50;
            if($_GPC['is_textarr3_must'] == 1 ){
                 $textarrset['is_textarr3_must'] = 1;
            }elseif($_GPC['is_textarr3_must'] == null ){
                 $textarrset['is_textarr3_must'] = 0;
            }
        }elseif($_GPC['is_textarr3'] == null ){
            $textarrset['is_textarr3'] = 0;
        }

        if($_GPC['is_textarr4'] == 1 ){
            $textarrset['is_textarr4'] = 1;
            $textlen = 1;
            $textarrset['textarr4_name'] = $_GPC['textarr4_name'];
            $textarrset['textarr4_length'] = $_GPC['textarr4_length']? $_GPC['textarr4_length']:50;
            if($_GPC['is_textarr4_must'] == 1 ){
                 $textarrset['is_textarr4_must'] = 1;
            }elseif($_GPC['is_textarr4_must'] == null ){
                 $textarrset['is_textarr4_must'] = 0;
            }
        }elseif($_GPC['is_textarr4'] == null ){
            $textarrset['is_textarr4'] = 0;
        }

        if($_GPC['is_textarr5'] == 1 ){
            $textarrset['is_textarr5'] = 1;
            $textlen = 1;
            $textarrset['textarr5_name'] = $_GPC['textarr5_name'];
            $textarrset['textarr5_length'] = $_GPC['textarr5_length']? $_GPC['textarr5_length']:50;
            if($_GPC['is_textarr5_must'] == 1 ){
                 $textarrset['is_textarr5_must'] = 1;
            }elseif($_GPC['is_textarr5_must'] == null ){
                 $textarrset['is_textarr5_must'] = 0;
            }
        }elseif($_GPC['is_textarr5'] == null ){
            $textarrset['is_textarr5'] = 0;
        }
        if($_GPC['is_textarr6'] == 1 ){
            $textarrset['is_textarr6'] = 1;
            $textlen = 1;
            $textarrset['textarr6_name'] = $_GPC['textarr6_name'];
            $textarrset['textarr6_length'] = $_GPC['textarr6_length']? $_GPC['textarr6_length']:50;
            if($_GPC['is_textarr6_must'] == 1 ){
                $textarrset['is_textarr6_must'] = 1;
            }elseif($_GPC['is_textarr6_must'] == null ){
                $textarrset['is_textarr6_must'] = 0;
            }
        }elseif($_GPC['is_textarr6'] == null ){
            $textarrset['is_textarr6'] = 0;
        }

         if($_GPC['is_textarr7'] == 1 ){
            $textarrset['is_textarr7'] = 1;
            $textlen = 1;
            $textarrset['textarr7_name'] = $_GPC['textarr7_name'];
            $textarrset['textarr7_length'] = $_GPC['textarr7_length']? $_GPC['textarr7_length']:50;
            if($_GPC['is_textarr7_must'] == 1 ){
                $textarrset['is_textarr7_must'] = 1;
            }elseif($_GPC['is_textarr7_must'] == null ){
                $textarrset['is_textarr7_must'] = 0;
            }
        }elseif($_GPC['is_textarr7'] == null ){
            $textarrset['is_textarr7'] = 0;
        }
         if($_GPC['is_textarr8'] == 1 ){
            $textarrset['is_textarr8'] = 1;
            $textlen = 1;
            $textarrset['textarr8_name'] = $_GPC['textarr8_name'];
            $textarrset['textarr8_length'] = $_GPC['textarr8_length']? $_GPC['textarr8_length']:50;
            if($_GPC['is_textarr8_must'] == 1 ){
                $textarrset['is_textarr8_must'] = 1;
            }elseif($_GPC['is_textarr8_must'] == null ){
                $textarrset['is_textarr8_must'] = 0;
            }
        }elseif($_GPC['is_textarr8'] == null ){
            $textarrset['is_textarr8'] = 0;
        }

        if($_GPC['is_textarr9'] == 1 ){
            $textarrset['is_textarr9'] = 1;
            $textlen = 1;
            $textarrset['textarr9_name'] = $_GPC['textarr9_name'];
            $textarrset['textarr9_length'] = $_GPC['textarr9_length']? $_GPC['textarr9_length']:50;
            if($_GPC['is_textarr9_must'] == 1 ){
                $textarrset['is_textarr9_must'] = 1;
            }elseif($_GPC['is_textarr9_must'] == null ){
                $textarrset['is_textarr9_must'] = 0;
            }
        }elseif($_GPC['is_textarr9'] == null ){
            $textarrset['is_textarr9'] = 0;
        }
        if($_GPC['is_textarr10'] == 1 ){
            $textarrset['is_textarr10'] = 1;
            $textlen = 1;
            $textarrset['textarr10_name'] = $_GPC['textarr10_name'];
            $textarrset['textarr10_length'] = $_GPC['textarr10_length']? $_GPC['textarr10_length']:50;
            if($_GPC['is_textarr10_must'] == 1 ){
                $textarrset['is_textarr10_must'] = 1;
            }elseif($_GPC['is_textarr10_must'] == null ){
                $textarrset['is_textarr10_must'] = 0;
            }
        }elseif($_GPC['is_textarr10'] == null ){
            $textarrset['is_textarr10'] = 0;
        }
        if($_GPC['Is_charge'] == 1 ){
            $Is_charge = 1;
        }elseif($_GPC['Is_charge'] == null ){
             $Is_charge = 0;
        }
        $out_t = serialize($textarrset);
        $out_p = serialize($picarrset);
        $checkarr = $_GPC['checkarr'];
        if(!empty($checkarr)){
            $checksendset_in = serialize($checkarr);
        }else{
            $checkarr = array('parents','head_teacher');
            $checksendset_in = serialize($checkarr);
        }
        $data = array(
            'weid'              => intval($weid),
            'uid'               => intval($_GPC['uid']),
            'cityid'            => intval($_GPC['cityid']),
            'areaid'            => intval($_GPC['area']),
            'typeid'            => intval($_GPC['type']),
            'title'             => trim($_GPC['title']),
            'info'              => trim($_GPC['info']),
            'content'           => trim($_GPC['content']),
            'zhaosheng'         => trim($_GPC['zhaosheng']),
            'tel'               => trim($_GPC['tel']),
            'gonggao'           => trim($_GPC['gonggao']),
            'logo'              => trim($_GPC['logo']),
            'thumb'             => trim($_GPC['thumb']),
            'qroce'             => trim($_GPC['qroce']),
            'address'           => trim($_GPC['address']),
            'copyright'         => trim($_GPC['copyright']),
            'copyrighturl'         => trim($_GPC['copyrighturl']),
            'location_p'        => trim($_GPC['location_p']),
            'location_c'        => trim($_GPC['location_c']),
            'location_a'        => trim($_GPC['location_a']),
            'lng'               => trim($_GPC['baidumap']['lng']),
            'lat'               => trim($_GPC['baidumap']['lat']),
            'is_show'           => intval($_GPC['is_show']),
            'is_showew'         => intval($_GPC['is_showew']),
            'is_openht'         => intval($_GPC['is_openht']),
            'is_zjh'            => intval($_GPC['is_zjh']),
            'is_rest'           => intval($_GPC['is_rest']),
            'is_sms'            => intval($_GPC['is_sms']),
            'is_hot'            => intval($_GPC['is_hot']),
            'is_cost'           => intval($_GPC['is_cost']),
            'is_kb'             => intval($_GPC['is_kb']),
            'send_overtime'     => empty($_GPC['send_overtime']) ? -1 : intval($_GPC['send_overtime']),
            'is_video'          => intval($_GPC['is_video']),
            'is_fbvocie'        => intval($_GPC['is_fbvocie']),
            'is_fbnew'          => intval($is_fbnew),
            //'txid'              => trim($_GPC['txid']),
            //'txms'              => trim($_GPC['txms']),
            'savevideoto'       => intval($_GPC['savevideoto']),
            'is_sign'           => intval($_GPC['is_sign']),
            'is_cardpay'        => intval($_GPC['is_cardpay']),
            'is_cardlist'       => intval($_GPC['is_cardlist']),
            'is_recordmac'      => intval($_GPC['is_recordmac']),
            'is_wxsign'         => intval($_GPC['is_wxsign']),
            'is_printer'        => intval($_GPC['is_printer']),
            'is_signneedcomfim' => intval($_GPC['is_signneedcomfim']),
            'sms_rest_times'    => intval($_GPC['sms_rest_times']),
            'bd_type'           => intval($_GPC['bd_type']),
            'is_stuewcode'      => intval($is_stuewcode),
            'wqgroupid'         => intval($_GPC['wqgroupid']),
            'isopen'            => intval($_GPC['isopen']),
            'shoucename'        => trim($_GPC['shoucename']),
            'videopic'          => trim($_GPC['videopic']),
            'videoname'         => trim($_GPC['videoname']),
            'jxstart'           => trim($_GPC['jxstart']),
            'jxend'             => trim($_GPC['jxend']),
            'lxstart'           => trim($_GPC['lxstart']),
            'lxend'             => trim($_GPC['lxend']),
            'jxstart1'          => trim($_GPC['jxstart1']),
            'jxend1'            => trim($_GPC['jxend1']),
            'lxstart1'          => trim($_GPC['lxstart1']),
            'lxend1'            => trim($_GPC['lxend1']),
            'jxstart2'          => trim($_GPC['jxstart2']),
            'jxend2'            => trim($_GPC['jxend2']),
            'lxstart2'          => trim($_GPC['lxstart2']),
            'lxend2'            => trim($_GPC['lxend2']),
            'ssort'             => intval($_GPC['ssort']),
            'tpic'              => trim($_GPC['tpic']),
            'spic'              => trim($_GPC['spic']),
            'dateline'          => time(),
            'comtid'            => $_GPC['comtid'],
            'yzxxtid'           => $_GPC['yzxxtid'],
            'is_star'           => $Is_star,
            'is_chongzhi'       => $Is_chongzhi,
            'chongzhiweid'      => $_GPC['chongzhiweid'],
            'is_shoufei'        => $is_shoufei,
            'is_qx'             => 1,
            'picarrset'         => $out_p,
            'is_picarr'         => $piclen,
            'is_textarr'        => $textlen,
            'textarrset'        => $out_t,
            'shareset'          => $shareset_str,
            'checksendset'      => $checksendset_in
        );
        $data2 = array(
            'alivodappid'       => trim($_GPC['alivodappid']),
            'alivodkey'         => trim($_GPC['alivodkey']),
            'xk_type'           => $_GPC['xk_type'],
            'is_dybp'        	=> $_GPC['is_dybp'],
			'is_show_pm'        => $_GPC['is_show_pm'],
            'is_unbind'         => $_GPC['is_unbind'],
        );
        // TODO:keep_Ls()定制功能
        if(keep_Ls()){
            $data2['addedinfo'] = $_GPC['addedinfo'];
            $data2['is_allow_send_voice'] = $_GPC['is_allow_send_voice'];
        }
        if(is_TestFz()){
            $shareinfo = array(
                'fxtitle' => $_GPC['fxtitle'],
                'fxdescription' => $_GPC['fxdescription'],
                'fxpic' => $_GPC['fxpic'],
            );
            $sharestr = serialize($shareinfo);
            $data2['is_teatotea'] = $_GPC['is_teatotea'];
            $data2['is_stutostu'] = $_GPC['is_stutostu'];
            $data2['is_teatostu'] = $_GPC['is_teatostu'];
            $data2['shareinfo'] = $sharestr;

        }
        if(keep_wt()){
            $data2['is_wtcheck'] = $_GPC['is_wtcheck'];
            if($_GPC['is_wtcheck'] == 1){
                $data2['wt_appid'] = $_GPC['wt_appid'];
                $data2['wt_appkey'] = $_GPC['wt_appkey'];
                $data2['wt_appsecret'] = $_GPC['wt_appsecret'];
                $data2['wt_version'] = $_GPC['wt_version'];
            }else{
                $data2['wt_appid'] = '';
                $data2['wt_appkey'] = '';
                $data2['wt_appsecret'] = '';
                $data2['wt_version'] = '';
                $data2['wt_token'] = '';
                $data2['wt_token_time'] = 0;
            }
        }

        if(keep_Blacklist()){
            $data2['doctorid'] = $_GPC['doctorid'];
        }
		if(anhui()){
            $data2['ah_appid'] = $_GPC['ah_appid'];
			$data2['ah_secret'] = $_GPC['ah_secret'];
        }
        if(is_TestFz()){
            $data2['no_ks_num'] = $_GPC['no_ks_num'];
			$data2['no_kcsign_num'] = $_GPC['no_kcsign_num'];
        }
            $data2['no_ks_num'] = $_GPC['no_ks_num'];
        if(keep_sk77()){
            $data2['zyvideolimit'] = $_GPC['zyvideolimit'];
            $data2['remindday'] = $_GPC['remindday'];
        }
        if(is_showpf()){
            $data2['review_type'] = $_GPC['review_type'];
        }
        if(assets()){
            $data2['is_gw'] = $_GPC['is_gw'];
            $data2['is_csyd'] = $_GPC['is_csyd'];
            $data2['gwtidarr'] = trim($_GPC['gw_tidarr'],',');
            $data2['csydtidarr'] = trim($_GPC['csyd_tidarr'],',');
        }
        if(keep_hxy()){
            $data2['typt_admin_tid'] = $_GPC['typt_admin_tid'];
            $data2['msgsendtype'] = $_GPC['msgsendtype'];
        }
        if(keep_Blacklist()){
            if($_GPC['Is_bingyin'] == 1){
                $data2['is_bingyin']  = 1;
            }else{
                $data2['is_bingyin']  = 0;
            }
        }
        /*** 添加相册分类*/
        $teapictype = $_GPC['teapictype'];
        $teapictype_in = serialize($teapictype);
        $data2['teapictype'] = $teapictype_in;
        $stupictype = $_GPC['stupictype'];
        $stupictype_in = serialize($stupictype);
        $data2['stupictype'] = $stupictype_in;
        $data2['isallowup'] = $_GPC['isallowup'];

        /*** 添加相册分类*/
        /******新增参数 */
        $data2['is_sure_kq'] = $_GPC['is_sure_kq'];
        $data2['twqswitch'] = $_GPC['twqswitch'];
        /******新增参数 */
        
        if(CheckXZF($schoolid)){
            $data2['xzf_scid'] = $_GPC['xzf_scid'];
        }

        if(keep_MC()){
            $data2['znl_appid'] = $_GPC['znl_appid'];
            $data2['znl_appsecret'] = $_GPC['znl_appsecret'];

        }

        if(keep_DD()){
            $data2['senddoor'] = $_GPC['senddoor'];
            $data2['priority'] = $_GPC['priority'];
            $data2['dd_limit_time'] = $_GPC['dd_limit_time'];
        }

        if(keep_ZHXZY()){ //上课提醒老师
            $data2['is_tx_tea'] = $_GPC['is_tx_tea'];
        }

        if($is_xz){ //上课提醒老师
            $data2['is_limit_send'] = $_GPC['is_limit_send'];
            $data2['is_repet'] = $_GPC['is_repet'];
        }
        pdo_update(GetTableName('schoolset',false), $data2, array('id' => $alydb['id']));
       
        if(is_showgkk())
        {
            $data['wxsignrange']  = intval($_GPC['wxsignrange']);
            $data['fxlocation'] = json_encode($_GPC['baidumap1']);
            //var_dump($data['fxlocation']);
            //die();
        }
        if(keep_hxy()){
            $data['typt_school_id'] =  $_GPC['typt_school_id'];
            $data['typt_ec_code'] =  trim($_GPC['typt_ec_code']);
        }
        if($_GPC['Is_point'] == 0){
            $data['Is_point']  = 0;
            $data['Cost2Point'] =0;
        }elseif($_GPC['Is_point'] ==1){
            $data['Is_point']  = 1;
            $data['Cost2Point'] =$_GPC['Cost2Point'];
        }
        if($_GPC['Is_buzhu'] == 1 ){
            $data['is_buzhu']  = 1;
        }elseif($_GPC['Is_buzhu'] == null || $_GPC['Is_buzhu'] ==0 ){
              $data['is_buzhu']  = 0;
        }

        if($_GPC['Is_ap'] == 1 ){
            $data['is_ap']  = 1;
        }elseif($_GPC['Is_ap'] == null || $_GPC['Is_ap'] ==0 ){
            $data['is_ap']  = 0;
        }
        if($_GPC['Is_book'] == 1 ){
            $data['is_book']  = 1;
        }elseif($_GPC['Is_book'] == null || $_GPC['Is_book'] ==0 ){
            $data['is_book']  = 0;
        }
        $sms_set = array(
            'code'  	 => trim($_GPC['code']),
            'signup'  	 => trim($_GPC['signup']),
            'xsqingjia'  => trim($_GPC['xsqingjia']),
            'xsqjsh'     => trim($_GPC['xsqjsh']),
            'jsqingjia'  => trim($_GPC['jsqingjia']),
            'jsqjsh'     => trim($_GPC['jsqjsh']),
            'xxtongzhi'  => trim($_GPC['xxtongzhi']),
            'liuyan'     => trim($_GPC['liuyan']),
            'liuyanhf'   => trim($_GPC['liuyanhf']),
            'zuoye'      => trim($_GPC['zuoye']),
            'bjtz'       => trim($_GPC['bjtz']),
            'bjqshjg'    => trim($_GPC['bjqshjg']),
            'bjqshtz'    => trim($_GPC['bjqshtz']),
            'jxlxtx'     => trim($_GPC['jxlxtx']),
            'jfjgtz'     => trim($_GPC['jfjgtz']),
            'sykstx'     => trim($_GPC['sykstx']),
            'kcqdtx'     => trim($_GPC['kcqdtx']),
            'kcyytx'     => trim($_GPC['kcyytx']),
            'sktxls'     => trim($_GPC['sktxls']),
            'kcjstz'     => trim($_GPC['kcjstz']),
        );
        $old = pdo_fetch("select mallsetinfo from " . tablename($this->table_index) . " where id=:id and weid =:weid", array(':id' => $id, ':weid' => $weid));
        $un = iunserializer($old['mallsetinfo']);

		$un['payweid'] = empty($_GPC['mallpayweid']) ? $weid : intval($_GPC['mallpayweid']);

		$data['sms_set'] = serialize($sms_set);
		$data['mallsetinfo'] = serialize($un);

		if($Is_charge != 0){
			$chargeset = array(
				'is_charge' => 1,
				'min_num' 	=> $_GPC['min_num'],
				'price_once' => $_GPC['price_once'],
				'chargepayweid' => $_GPC['chargepayweid']?$_GPC['chargepayweid']:$weid,
			);
		}else{
			$chargeset = array(
				'is_charge' => 0,
			);
		}
		$data['chargesetinfo'] = serialize($chargeset);



        if(istrlen($data['title']) == 0){
            $this->imessage('没有输入标题.', referer(), 'error');
        }
        if(istrlen($data['title']) > 30){
            $this->imessage('标题不能多于30个字。', referer(), 'error');
        }
        if($_GPC['is_stuewcode'] == 1){
			if(checkvers() == 1){
			   if(!$fenzu){
					$this->imessage('抱歉,启用学生二维码必须前往 平台管理 为本校设置一个主分组,即将为您跳转', $this->createWebUrl('manager', array('op' => 'display')), 'error');
				}
			}
		}
        if($_GPC['is_rest']['0'] == 1){
            if(!$_GPC['shoucename']){
                $this->imessage('启用评价系统时，必须为评价系统命名', referer(), 'error');
            }
        }
        if($_GPC['is_fbnew'] == 1){
            if(!$_GPC['alivodappid'] || !$_GPC['alivodkey']){
                $this->imessage('启用视频必须设置阿里云appid与密钥!', referer(), 'error');
            }
        }
        if($_GPC['is_video']['0'] == 1){
            if(!$_GPC['videoname']){
                $this->imessage('启用直播和监控系统时，必须为该系统命名', referer(), 'error');
            }
        }

        if($_GPC['is_sign'] == 1){
            $temp            = array(
                'is_idcard' => $_GPC['is_idcard'],
                'is_nj'     => $_GPC['is_nj'],
                'is_bj'     => $_GPC['is_bj'],
                'is_bir'    => $_GPC['is_bir'],
                'is_bd'     => $_GPC['is_bd'],
				'is_sms'    => $_GPC['is_sms'],
				'is_head'   => $_GPC['is_head'],
                'payweid'   => empty($_GPC['bmpayweid']) ? $weid : $_GPC['bmpayweid']
            );
            $data['signset'] = serialize($temp);
        }else{
            $data['signset'] = '';
        }
        if($_GPC['is_cardpay'] == 1){
            $temp1           = array(
                'cardcost' => $_GPC['cardcost'],
                'cardtime' => $_GPC['cardtime'],
                'endtime1' => intval($_GPC['endtime1']),
                'endtime2' => strtotime($_GPC['endtime2']),
                'payweid'  => empty($_GPC['kqpayweid']) ? $weid : $_GPC['kqpayweid']
            );
            $data['cardset'] = serialize($temp1);
        }else{
            $data['cardset'] = '';
        }
        if(!empty($id)){
            unset($data['dateline']);

            pdo_update($this->table_index, $data, array('id' => $id, 'weid' => $weid));
        }else{
           pdo_insert($this->table_index, $data);
        }
        $this->imessage('操作成功!', referer(), 'success');
    }
}elseif($operation == 'changemall'){

    $is_show = intval($_GPC['is_shangcheng']);
 	$old_mall = pdo_fetch("select mallsetinfo from " . tablename($this->table_index) . " where id=:id and weid =:weid", array(':id' => $schoolid, ':weid' => $weid));
 	$un_mall = unserialize($old_mall['mallsetinfo']);
 	$un_mall['isShow'] = $is_show;

 	$new_mall = serialize($un_mall);
    $data1 = array('mallsetinfo' => $new_mall);

    pdo_update($this->table_index, $data1, array('id' => $schoolid));
}
elseif($operation == 'changeauto'){
    $is_Auto = intval($_GPC['mall_is_Auto']);
	$old_mall = pdo_fetch("select mallsetinfo from " . tablename($this->table_index) . " where id=:id and weid =:weid", array(':id' => $schoolid, ':weid' => $weid));
 	$un_mall = iunserializer($old_mall['mallsetinfo']);
 	$un_mall['isAuto'] = $is_Auto;
 	$new_mall = iserializer($un_mall);
    $data = array('mallsetinfo' => $new_mall);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}
elseif($operation == 'change8'){
    $is_kb = intval($_GPC['is_kb']);

    $data = array('is_kb' => $is_kb);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}elseif($operation == 'change7'){
    $is_zjh = intval($_GPC['is_zjh']);

    $data = array('is_zjh' => $is_zjh);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}elseif($operation == 'change6'){
    $is_showew = intval($_GPC['is_showew']);

    $data = array('is_showew' => $is_showew);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}elseif($operation == 'change5'){
    $status = intval($_GPC['status']);
    $id     = intval($_GPC['id']);
    $data   = array('status' => $status);
    pdo_update($this->table_icon, $data, array('id' => $id));
}elseif($operation == 'change4'){
    $isopen = intval($_GPC['isopen']);

    $data = array('isopen' => $isopen);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}elseif($operation == 'change3'){
    $is_rest = intval($_GPC['is_rest']);

    $data = array('is_rest' => $is_rest);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}elseif($operation == 'change2'){
    $is_hot = intval($_GPC['is_hot']);

    $data = array('is_hot' => $is_hot);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}elseif($operation == 'change1'){
    $is_video = intval($_GPC['is_video']);

    $data = array('is_video' => $is_video);

    pdo_update($this->table_index, $data, array('id' => $schoolid));
}


if($operation == 'bootSwitchChange'){
    $status = $_GPC['status'];
    $Param = $_GPC['param'];
    $data = array("{$Param}" => $status);
    pdo_update(GetTableName('schoolset',false), $data, array('schoolid' => $schoolid));
    die();
}

if($operation == 'allsync'){
    mload()->model('xzf'); 
    if($_GPC['act'] == 'grade'){
	    $res = SyncGrade($schoolid);
        die(json_encode($res));
    }
    if($_GPC['act'] == 'class'){
        $res = SyncClass($schoolid);
        die(json_encode($res));
    }

    if($_GPC['act'] == 'stu'){
        $total = $_GPC['total'];
        $page = $_GPC['page'];
        $res = SyncStudent($schoolid,$total,$page);
        die(json_encode($res));
    }

    if($_GPC['act'] == 'tea'){
        $total = $_GPC['total'];
        $page = $_GPC['page'];
        $res = SyncTeachers($schoolid,$total,$page);
        die(json_encode($res));
    }

    if($_GPC['act'] == 'card'){
        $total = $_GPC['total'];
        $page = $_GPC['page'];
        $res = SyncCard($schoolid,$total,$page);
        die(json_encode($res));
    }

    $res['msg'] = "非法操作";
    $res['status'] = false;
	die(json_encode($res));
}

include $this->template('web/schoolset');
?>