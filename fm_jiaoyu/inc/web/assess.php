<?php
/**
 * 微教育模块
 * @author 高贵血迹
 */
global $_GPC, $_W;
$action            = 'assess';
$this1             = 'no2';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$weid              = $_W['uniacid'];
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
mload()->model('xzf');
$AllTag = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type ='teatag' ");
if(empty($schoolid)){
	$this->imessage('非法操作!', referer(), 'error');
}
if($operation == 'post'){
	if (!(IsHasQx($tid_global,1000602,1,$schoolid))){
		$this->imessage('非法访问，您无权操作此功能','','error');	
	}
	$id = intval($_GPC['id']);
	$fz = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'jsfz', ':schoolid' => $schoolid));
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$km = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
	$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	$lastid = pdo_fetch("SELECT id FROM " . tablename($this->table_class) . " ORDER by id DESC LIMIT 0,1");
	$lastids = empty($lastid['id']) ? 1000 :$lastid['id'];
	if(!empty($id)){
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $id));
		$item['otherinfo'] = unserialize($item['otherinfo']);
		$bjlists = get_mylist($schoolid,$item['id'],'teacher',1);
		//var_dump($bjlists);
		if(empty($item)){
			$this->imessage('抱歉，教师不存在或是已经删除！', referer(), 'error');
		}
	}
}elseif($operation == 'tea_info'){		
	if (!(IsHasQx($tid_global,1000602,1,$schoolid))){
		$this->imessage('非法访问，您无权操作此功能','','error');	
	}
	$id = intval($_GPC['id']);
	$fz = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'jsfz', ':schoolid' => $schoolid));
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$km = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
	$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	$lastid = pdo_fetch("SELECT id FROM " . tablename($this->table_class) . " ORDER by id DESC LIMIT 0,1");
	$lastids = empty($lastid['id']) ? 1000 :$lastid['id'];
	if(!empty($id)){
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $id));
		$item['otherinfo'] = unserialize($item['otherinfo']);
		$bjlists = get_mylist($schoolid,$item['id'],'teacher',1);
		//var_dump($bjlists);
		if(empty($item)){
			$this->imessage('抱歉，教师不存在或是已经删除！', referer(), 'error');
		}
	}
	include $this->template('web/tea/edite');
	die();
}elseif($operation == 'edit_tea'){	
	load()->func('tpl');
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $id));
		$item['otherinfo'] = unserialize($item['otherinfo']);
		$bjlists = get_mylist($schoolid,$item['id'],'teacher',1);
		//var_dump($bjlists);
		if(empty($item)){
			$this->imessage('抱歉，教师不存在或是已经删除！', referer(), 'error');
		}
	}		
	if($item['code'] == 0){
		$randStr = str_shuffle('123456789');
		$rand    = substr($randStr, 0, 6);
	}else{
		$rand = $item['code'];
	}
	if(!empty($_GPC['code'])){
		$rand = $_GPC['code'];
	}

	$data = array(
		'weid'      => $weid,
		'schoolid'  => $schoolid,
		'tname'     => trim($_GPC['tname']),
		'birthdate' => strtotime($_GPC['birthdate']),
		'tel'       => trim($_GPC['tel']),
		'mobile'    => trim($_GPC['mobile']),
		'email'     => trim($_GPC['email']),
		'thumb'     => trim($_GPC['thumb']),
		'jiontime'  => strtotime($_GPC['jiontime']),
		'sex'       => intval($_GPC['sex']),
		'is_show'   => intval($_GPC['is_show']),
		'status'    => intval($_GPC['status']),
		'sort'      => intval($_GPC['sort']),
		'fz_id'     => trim($_GPC['fz_id']),
		'headinfo'  => trim($_GPC['headinfo']),
		'jinyan'    => trim($_GPC['jinyan']),
		'info'      => htmlspecialchars_decode($_GPC['info']),
		'code'      => $rand,
		'idcard'    => $_GPC['idcard'],
		'jiguan'    => $_GPC['jiguan'],
		'minzu'     => $_GPC['minzu'],
		'zzmianmao' => $_GPC['zzmianmao'],
		'address'   => $_GPC['address']
	);
	// var_dump($data);die;
	if(keep_MC()){
		$data['tagid'] = $_GPC['tea_tagid'];
	}

	if(!empty($id)){
		if($_GPC['thumb'] != $item['thumb']){
			$allcard = pdo_fetchall("SELECT id,idcard,tpic FROM " . GetTableName('idcard') . " WHERE :tid = tid", array(':tid' => $id));
			$newpic = $_GPC['thumb'];
			if($allcard){
				foreach($allcard as $row){
					$path = "images/fm_jiaoyu/cardthumb/".$schoolid."/";
					// $picurl2 = $path.trim($row['idcard']).".jpg";
					$picurl2 = $path.random(30) .".jpg";
					$image_file = file_get_contents(ATTACHMENT_ROOT.$_GPC['thumb']);
					file_write($picurl2,$image_file);
					if (!empty($_W['setting']['remote']['type'])) {
						$remotestatus = file_remote_upload($picurl2);
					}
					pdo_update(GetTableName('idcard',false), array('tpic' => $picurl2), array('id' => $row['id']));
				$newpic = $picurl2;
				}
				pdo_update(GetTableName('schoolset',false), array('top'=>1), array('schoolid' => $_GPC['schoolid'], 'weid'=>$_GPC['weid']));
			}
		}
	}
	
	if(is_showZB()){
		$data['plate_num'] = $_GPC['plate_num'];
	}

	if(keep_sk77()){
		$data['is_sell'] = $_GPC['is_sell'];
	}

	if (is_showpf()){		
		$otherinfo = array(
			'first_xl'     	=> $_GPC['first_xl'],
			'first_zy'     	=> $_GPC['first_zy'],
			'first_yx'     	=> $_GPC['first_yx'],
			'first_bytime' 	=> $_GPC['first_bytime'],
			'top_xl'       	=> $_GPC['top_xl'],
			'top_zy'       	=> $_GPC['top_zy'],
			'top_yx'       	=> $_GPC['top_yx'],
			'top_bytime'   	=> $_GPC['top_bytime'],
			'main_study_jl' => $_GPC['main_study_jl'],
			'time2work' 	=> $_GPC['time2work'],
			'tea_subject' 	=> $_GPC['tea_subject'],
			'zhicheng' 		=> $_GPC['zhicheng'],
			'zc_pstime' 	=> $_GPC['zc_pstime'],
			'zc_prtime' 	=> $_GPC['zc_prtime'],
			'zjzhiwu' 		=> $_GPC['zjzhiwu'],
			'zjzw_pstime' 	=> $_GPC['zjzw_pstime'],
			'zjzw_prtime' 	=> $_GPC['zjzw_prtime'],
			'main_work_jl' 	=> $_GPC['main_work_jl'],
			'jszg_type' 	=> $_GPC['jszg_type'],
			'jszgzs_num'	=> $_GPC['jszgzs_num'],
			'pth_level' 	=> $_GPC['pth_level'],
			'pthzs_num' 	=> $_GPC['pthzs_num'],
			'yzk1_level' 	=> $_GPC['yzk1_level'],
			'yzk1_rank' 	=> $_GPC['yzk1_rank'],
			'yzk1_org' 		=> $_GPC['yzk1_org'],
			'yzk2_level' 	=> $_GPC['yzk2_level'],
			'yzk2_rank' 	=> $_GPC['yzk2_rank'],
			'yzk2_org' 		=> $_GPC['yzk2_org'],
			'zhbz1_level' 	=> $_GPC['zhbz1_level'],
			'zhbz1_rank' 	=> $_GPC['zhbz1_rank'],
			'zhbz1_org' 	=> $_GPC['zhbz1_org'],
			'zhbz2_level' 	=> $_GPC['zhbz2_level'],
			'zhbz2_rank' 	=> $_GPC['zhbz2_rank'],
			'zhbz2_org' 	=> $_GPC['zhbz2_org'],
			'jky1_level' 	=> $_GPC['jky1_level'],
			'jky1_rank' 	=> $_GPC['jky1_rank'],
			'jky1_org' 		=> $_GPC['jky1_org'],
			'jky2_level' 	=> $_GPC['jky2_level'],
			'jky2_rank' 	=> $_GPC['jky2_rank'],
			'jky2_org' 		=> $_GPC['jky2_org'],
			'qtzs1_level' 	=> $_GPC['qtzs1_level'],
			'qtzs1_rank' 	=> $_GPC['qtzs1_rank'],
			'qtzs1_org' 	=> $_GPC['qtzs1_org'],
			'qtzs2_level' 	=> $_GPC['qtzs2_level'],
			'qtzs2_rank' 	=> $_GPC['qtzs2_rank'],
			'qtzs2_org' 	=> $_GPC['qtzs2_org'],
			'qtzs3_level' 	=> $_GPC['qtzs3_level'],
			'qtzs3_rank' 	=> $_GPC['qtzs3_rank'],
			'qtzs3_org' 	=> $_GPC['qtzs3_org'],
		);
		$otherinfo_temp = serialize($otherinfo);
		$data['otherinfo'] = $otherinfo_temp;
	}

	
	if(empty($data['tname'])){
		$result['result'] = false;
		$result['msg'] = '请输入教师姓名！';
		die(json_encode($result));
	}
	if(!empty($data['tname'])){
		if(ischeckName($data['tname']) == false){
			$result['result'] = false;
			$result['msg'] = "禁止使用'测试、test'等字眼作为教师姓名";
			die(json_encode($result));
		}
	}								
	$urlss = getoauthurl();
	if($urlss == 'manger.weimeizhan.com'){
		if(!$_W['isfounder']){
			if($id == 13 || $id == 448 || $id == 627){
				$this->imessage('抱歉，你无权编辑此教师（此老师是管理员绑定的），请另行添加或解绑其他老师！');
			}
		}
	}	
	if(empty($id)){
		pdo_insert($this->table_teachers, $data);
		$thistid = pdo_insertid();
	}else{
		unset($data['dateline']);
		$chekOldTname = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and id = '{$id}' ");
		pdo_update($this->table_teachers, $data, array('id' => $id));

		if($item['tname'] != $data['tname'] || $item['idcard'] != $data['idcard'] || $item['mobile'] != $data['mobile']){
			setXzfNeedsync($id,'teachers');
		}

		//讯贞触发
		$checkTC = pdo_fetch("SELECT idcard FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and tid = '{$id}' ");
		if(!empty($checkTC) &&  $chekOldTname['tname'] != $data['tname'] ){
			//教师有卡，且名字变了
			xzTriggerCommon($schoolid,$checkTC['idcard'],'update');
		}

		$thistid = $id;
	}

	if(!empty($_GPC['thisid'])){
		if(!empty($_GPC['old'])){
			foreach($_GPC['thisid'] as $key => $thisid){
				if(!empty($thisid)){
					$data1  = array(
						'bj_id' 	 => trim($_GPC['bj_id'][$key]),
						'km_id' 	 => trim($_GPC['km_id'][$key]),
					);
					pdo_update($this->table_class, $data1, array('id' => $thisid));
				}
			}
		}
		if(!empty($_GPC['new'])){
			foreach($_GPC['new'] as $key => $title){
				$data2  = array(
					'weid'       => $weid,
					'schoolid'   => $schoolid,
					'tid'      	 => $thistid,
					'bj_id' 	 => trim($_GPC['bj_id_new'][$key]),
					'km_id' 	 => trim($_GPC['km_id_new'][$key]),
					'type'       => 1
				);
				pdo_insert($this->table_class, $data2);
			}
		}
	}else{
		if(!empty($_GPC['new'])){
			foreach($_GPC['new'] as $key => $title){
				$data3  = array(
					'weid'       => $weid,
					'schoolid'   => $schoolid,
					'tid'      	 => $thistid,
					'bj_id' 	 => trim($_GPC['bj_id_new'][$key]),
					'km_id' 	 => trim($_GPC['km_id_new'][$key]),
					'type'       => 1
				);
				pdo_insert($this->table_class, $data3);
			}
		}
	}
	$result['thistid'] = $thistid;
	$result['result'] = true;
	$result['msg'] = '操作成功！';
	die(json_encode($result));
}elseif($operation == 'changebjdata'){
	$oldbjdata = pdo_fetchall("SELECT * FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} ORDER BY id DESC");
	foreach($oldbjdata as $index => $row){
		if($row['bj_id1']){
			$data1 = array(
				'weid'     => $weid,
				'schoolid' => $schoolid,
				'tid'      => $row['id'],
				'bj_id'    => $row['bj_id1'],
				'km_id'    => $row['km_id1'],
				'type'     => 1,
			);
			pdo_insert($this->table_class, $data1);
		}
		if($row['bj_id2']){
			$data2 = array(
				'weid'     => $weid,
				'schoolid' => $schoolid,
				'tid'      => $row['id'],
				'bj_id'    => $row['bj_id2'],
				'km_id'    => $row['km_id2'],
				'type'     => 1,
			);
			pdo_insert($this->table_class, $data2);						
		}
		if($row['bj_id3']){
			$data2 = array(
				'weid'     => $weid,
				'schoolid' => $schoolid,
				'tid'      => $row['id'],
				'bj_id'    => $row['bj_id3'],
				'km_id'    => $row['km_id3'],
				'type'     => 1,
			);
			pdo_insert($this->table_class, $data2);
		}					
	}
	$this->imessage('操作成功', $this->createWebUrl('assess', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'display'){
	if (!(IsHasQx($tid_global,1000601,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	if (checksubmit('submit')) { //排序
		if (is_array($_GPC['sort'])) {
			foreach ($_GPC['sort'] as $id => $val) {
				$data = array('sort' => intval($_GPC['sort'][$id]));
				pdo_update($this->table_teachers, $data, array('id' => $id));
			}
		}
		$this->imessage('操作成功', $this->createWebUrl('assess', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
	}
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 8;
	$condition = '';
	if(!empty($_GPC['keyword'])){
		$condition .= " AND tname LIKE '%{$_GPC['keyword']}%'";
	}
	if(!empty($_GPC['one_tid'])){ //只取一行数据
		$condition .= " And id = '{$_GPC['one_tid']}'";
	}
	if(!empty($_GPC['bd_type'])){
		if($_GPC['bd_type'] == 1){
			$condition .= " AND uid != '' ";
		}
		if($_GPC['bd_type'] == 2){
			$condition .= " AND uid = '' ";
		}				
	}
	
	if(!empty($_GPC['bj_id'])){
		$bj_id     = $_GPC['bj_id'];
		$condition .= " AND (bj_id1 = '{$bj_id}' or bj_id2 = '{$bj_id}' or bj_id3 = '{$bj_id}')";
	}

	if(!empty($_GPC['km_id'])){
		$km_id     = $_GPC['km_id'];
		$condition .= " AND (km_id1 = '{$km_id}' or km_id2 = '{$km_id}' or km_id3 = '{$km_id}')";
	}
	$checkbjold = pdo_fetch("SELECT * FROM " . tablename($this->table_class) . " WHERE schoolid = :schoolid And type = :type ", array(':schoolid' => $schoolid,':type' => 1));
	//////////导出数据/////////////////
	if($_GPC['out_putbjlist'] == 'out_putbjlist'){
		$listss = pdo_fetchall("SELECT tname,id FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} ORDER BY id DESC");
		$ii   = 0;
		foreach($listss as $index => $row){
			$arr[$ii]['id'] = $row['id'];
			$arr[$ii]['tname']  = $row['tname'];
			$ii++;
		}
		$this->exportexcel($arr, array('ID','姓名','班级ID','科目ID'), 'example_bjlist');
		exit();
	}
	////////////////////////////////			
	//////////导出数据/////////////////
	if($_GPC['out_putcode'] == 'out_putcode'){
		$listss = pdo_fetchall("SELECT tname,code,mobile FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC");
		$ii   = 0;
		foreach($listss as $index => $row){
			$arr[$ii]['tname'] = $row['tname'];
			$arr[$ii]['code']  = $row['code'];
			$arr[$ii]['mobile']= $row['mobile'];
			$ii++;
		}
		$this->exportexcel($arr, array('教师','绑定码','手机'), '教师绑定码');
		exit();
	}
	
	///////////////////导出教师信息////////////////////////////////
	if($_GPC['out_putTeaInfo'] == 'out_putTeaInfo'){
		$listss = pdo_fetchall("SELECT * FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC");
		$ii   = 0;
		foreach($listss as $index => $row){
			$arr[$ii]['tname'] = $row['tname'];
			if($row['sex'] == 1){
				$arr[$ii]['sex'] = '男';
			}elseif($row['sex'] == 0 ){
				$arr[$ii]['sex'] = '女';
			}
			$arr[$ii]['birthdate'] = date("Y-m-d",$row['birthdate']);
			$arr[$ii]['idcard'] = $row['idcard'];
			$arr[$ii]['jiguan'] = $row['jiguan'];
			$arr[$ii]['minzu'] = $row['minzu'];
			$arr[$ii]['zzmianmao'] = $row['zzmianmao'];
			$arr[$ii]['address'] = $row['address'];
			$arr[$ii]['email'] = $row['email'];
			$arr[$ii]['mobile'] = $row['mobile'];
			$this_otherinfo = unserialize($row['otherinfo']);
			//第一学历
			
			$arr[$ii]['firstxl'] = "【学历】".($this_otherinfo['first_xl']?$this_otherinfo['first_xl']:"未填写")."【专业】".($this_otherinfo['first_zy']?$this_otherinfo['first_zy']:"未填写")."【毕业院校】".($this_otherinfo['first_yx']?$this_otherinfo['first_yx']:"未填写")."【毕业时间】".($this_otherinfo['first_bytime'] != '1970-01-01' && !empty($this_otherinfo['first_bytime']) ? $this_otherinfo['first_bytime']:"未填写");
			//最高学历
			$arr[$ii]['topxl'] = "【学历】".($this_otherinfo['top_xl']?$this_otherinfo['top_xl']:"未填写")."【专业】".($this_otherinfo['top_zy']?$this_otherinfo['top_zy']:"未填写")."【毕业院校】".($this_otherinfo['top_yx']?$this_otherinfo['top_yx']:"未填写")."【毕业时间】".($this_otherinfo['top_bytime'] != '1970-01-01' && !empty($this_otherinfo['top_bytime']) ? $this_otherinfo['top_bytime']:"未填写");
			//主要学习简历
			$arr[$ii]['main_study_jl'] =  strip_tags(htmlspecialchars_decode($this_otherinfo['main_study_jl']));
			//参加工作
			$arr[$ii]['time2work'] = "【参加工作时间】".($this_otherinfo['time2work'] != '1970-01-01' && !empty($this_otherinfo['time2work']) ? $this_otherinfo['time2work']:"未填写")."【任教学科】".($this_otherinfo['tea_subject']?$this_otherinfo['tea_subject']:"未填写");
			//职称
			$arr[$ii]['zhicheng'] = "【职称】".($this_otherinfo['zhicheng']?$this_otherinfo['zhicheng']:"未填写")."【评审时间】".($this_otherinfo['zc_pstime'] != '1970-01-01' && !empty($this_otherinfo['zc_pstime']) ? $this_otherinfo['zc_pstime']:"未填写")."【聘任时间】".($this_otherinfo['zc_prtime'] != '1970-01-01' && !empty($this_otherinfo['zc_prtime']) ? $this_otherinfo['zc_prtime']:"未填写");
			//专业技术职务
			$arr[$ii]['zjzhiwu'] = "【职务】".($this_otherinfo['zjzhiwu']?$this_otherinfo['zjzhiwu']:"未填写")."【评审时间】".($this_otherinfo['zjzw_pstime'] != '1970-01-01' && !empty($this_otherinfo['zjzw_pstime']) ? $this_otherinfo['zjzw_pstime']:"未填写")."【聘任时间】".($this_otherinfo['zjzw_prtime'] != '1970-01-01' && !empty($this_otherinfo['zjzw_prtime']) ? $this_otherinfo['zjzw_prtime']:"未填写");
			//主要工作简历
			$arr[$ii]['main_work_jl'] =  strip_tags(htmlspecialchars_decode($this_otherinfo['main_work_jl']));
			//教师资格证
			$arr[$ii]['jszgz'] = "【资格证类型】".($this_otherinfo['jszg_type']?$this_otherinfo['jszg_type']:"未填写")."【证书编号】".($this_otherinfo['jszgzs_num'] ? $this_otherinfo['jszgzs_num']:"未填写");
			//普通话证
			$arr[$ii]['pth'] = "【级别】".($this_otherinfo['pth_level']?$this_otherinfo['pth_level']:"未填写")."【证书编号】".($this_otherinfo['pthzs_num'] ? $this_otherinfo['pthzs_num']:"未填写");
			//优质课一
			$arr[$ii]['yzk1'] = "【级别】".($this_otherinfo['yzk1_level']?$this_otherinfo['yzk1_level']:"未填写")."【等次】".($this_otherinfo['yzk1_rank'] ? $this_otherinfo['yzk1_rank']:"未填写")."【发证单位】".($this_otherinfo['yzk1_org'] ? $this_otherinfo['yzk1_org']:"未填写");
			//优质课二
			$arr[$ii]['yzk2'] = "【级别】".($this_otherinfo['yzk2_level']?$this_otherinfo['yzk2_level']:"未填写")."【等次】".($this_otherinfo['yzk2_rank'] ? $this_otherinfo['yzk2_rank']:"未填写")."【发证单位】".($this_otherinfo['yzk2_org'] ? $this_otherinfo['yzk2_org']:"未填写");
			//综合表彰一
			$arr[$ii]['zhbz1'] = "【级别】".($this_otherinfo['zhbz1_level']?$this_otherinfo['zhbz1_level']:"未填写")."【等次】".($this_otherinfo['zhbz1_rank'] ? $this_otherinfo['zhbz1_rank']:"未填写")."【发证单位】".($this_otherinfo['zhbz1_org'] ? $this_otherinfo['zhbz1_org']:"未填写");
			//综合表彰二
			$arr[$ii]['zhbz2'] = "【级别】".($this_otherinfo['zhbz2_level']?$this_otherinfo['zhbz2_level']:"未填写")."【等次】".($this_otherinfo['zhbz2_rank'] ? $this_otherinfo['zhbz2_rank']:"未填写")."【发证单位】".($this_otherinfo['zhbz2_org'] ? $this_otherinfo['zhbz2_org']:"未填写");
			//教科研一
			$arr[$ii]['jky1'] = "【级别】".($this_otherinfo['jky1_level']?$this_otherinfo['jky1_level']:"未填写")."【等次】".($this_otherinfo['jky1_rank'] ? $this_otherinfo['jky1_rank']:"未填写")."【发证单位】".($this_otherinfo['jky1_org'] ? $this_otherinfo['jky1_org']:"未填写");
			//教科研二
			$arr[$ii]['jky2'] = "【级别】".($this_otherinfo['jky2_level']?$this_otherinfo['jky2_level']:"未填写")."【等次】".($this_otherinfo['jky2_rank'] ? $this_otherinfo['jky2_rank']:"未填写")."【发证单位】".($this_otherinfo['jky2_org'] ? $this_otherinfo['jky2_org']:"未填写");
			//证书一
			$arr[$ii]['qtzs1'] = "【级别】".($this_otherinfo['qtzs1_level']?$this_otherinfo['qtzs1_level']:"未填写")."【等次】".($this_otherinfo['qtzs1_rank'] ? $this_otherinfo['qtzs1_rank']:"未填写")."【发证单位】".($this_otherinfo['qtzs1_org'] ? $this_otherinfo['qtzs1_org']:"未填写");
			//证书二
			$arr[$ii]['qtzs2'] = "【级别】".($this_otherinfo['qtzs2_level']?$this_otherinfo['qtzs2_level']:"未填写")."【等次】".($this_otherinfo['qtzs2_rank'] ? $this_otherinfo['qtzs2_rank']:"未填写")."【发证单位】".($this_otherinfo['qtzs2_org'] ? $this_otherinfo['qtzs2_org']:"未填写");
			//证书三
			$arr[$ii]['qtzs3'] = "【级别】".($this_otherinfo['qtzs3_level']?$this_otherinfo['qtzs3_level']:"未填写")."【等次】".($this_otherinfo['qtzs3_rank'] ? $this_otherinfo['qtzs3_rank']:"未填写")."【发证单位】".($this_otherinfo['qtzs3_org'] ? $this_otherinfo['qtzs3_org']:"未填写");
			$ii++;
		}
		$this->exportexcel($arr, array('教师','性别','出生年月日','身份证号码','籍贯','民族','政治面貌','现住址','邮箱','手机','第一学历','最高学历','主要学习简历','参加工作时间','职称','专业技术职务','主要工作简历','教师资格证','普通话证','优质课一','优质课二','综合表彰一','综合表彰二','教科研一','教科研二','其他证书一','其他证书二','其他证书三'), '教师详细信息');
		exit();
	}
	
	////////////////////////////////
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY status DESC, sort DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $key => $value){
		if($value['userid']){
			$user = pdo_fetch("SELECT openid FROM " . tablename($this->table_user) . " WHERE id = '{$value['userid']}' ");
			$member = GetWeFans($weid,$user['openid']);
		}else{
			$uid = $value['uid'];
			$member = pdo_fetch("SELECT nickname,avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W ['uniacid'], ':uid' => $uid));
		}
		$kcnum = pdo_fetchall("select id,name,OldOrNew FROM ".tablename($this->table_tcourse)." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and (tid like '%,{$value['id']},%'  or tid like '%,{$value['id']}' or tid like '{$value['id']},%' or tid ='{$value['id']}') ");
		if(!empty($kcnum)){
			foreach($kcnum as $key_k=>$value_k){
				$ksnum_k = pdo_fetchcolumn("select sum(costnum) FROM ".tablename($this->table_kcbiao)." WHERE tid = '".$value['id']."' and kcid = '{$value_k['id']}'");
				$kcnum[$key_k]['ksnum'] = $ksnum_k;
				$ksnum_yq = pdo_fetchcolumn("select sum(costnum) FROM ".tablename($this->table_kcsign)." WHERE tid = '".$value['id']."' and kcid = '{$value_k['id']}'");
				$kcnum[$key_k]['ksnum_yq'] = $ksnum_yq ? $ksnum_yq : 0;
			}	
		}
		$nowtime = time();
		$zks = pdo_fetchcolumn("select sum(costnum) FROM ".tablename($this->table_kcbiao)." WHERE tid = '".$value['id']."'");
		$wwks = pdo_fetchcolumn("select sum(costnum) FROM ".tablename($this->table_kcbiao)." WHERE date > '".$nowtime."' And  tid = '".$value['id']."'");
		$ywks = pdo_fetchcolumn("select sum(costnum) FROM ".tablename($this->table_kcbiao)." WHERE date < '".$nowtime."' And  tid = '".$value['id']."'");
		$list[$key]['nickname'] = $member['nickname'];
		$list[$key]['avatar']   = $member['avatar']?$member['avatar']:tomedia($school['tpic']);
		$list[$key]['kcnum'] = count($kcnum);
		$list[$key]['kclist'] = $kcnum;
		//var_dump($kcnum);
		$list[$key]['Ttitle'] = GetTeacherTitle($value['status'],$value['fz_id']);
		$bjlists = get_mylist($schoolid,$value['id'],'teacher',1);
		$list[$key]['bjlist'] = $bjlists;
		$list[$key]['zks'] = $zks;
		$list[$key]['wwks'] = $wwks;
		$list[$key]['ywks'] = $ywks;
	}
	if(!empty($_GPC['one_tid'])){ //只取一行数据
		include $this->template('web/tea/one_tea');
		die();
	}	
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
	$pager = pagination($total, $pindex, $psize);
	$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$endtime = $starttime + 86399;
	$zrstarttime = $starttime - 86399;
	$conditions = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
	$conditionss = " AND createtime > '{$zrstarttime}' AND createtime < '{$starttime}'";
	$jrbd  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_user)." WHERE schoolid = '{$schoolid}' And sid = 0 $conditions ");
	$zrbd  = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_user)." WHERE schoolid = '{$schoolid}' And sid = 0 $conditionss ");				
}elseif($operation == 'delete'){
	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $id));
	if(empty($row)){
		$result['msg'] = '抱歉，教师不存在或是已经被删除！';
		$result['result'] = false;
		die(json_encode($result));
	}
	if(!empty($row['openid'])){
		$result['msg'] = '请先解绑该教师的微信！';
		$result['result'] = false;
		die(json_encode($result));
	}
	$urlss = getoauthurl();
	if($urlss == 'manger.weimeizhan.com'){
		if(!$_W['isfounder']){
			if($id == 13 || $id == 448 || $id == 627){
				$result['msg'] = '抱歉，你无权编辑此教师（此老师是管理员绑定的），请另行添加或解绑其他老师！';
				$result['result'] = false;
				die(json_encode($result));
			}				
		}
	}			
	if(!empty($row['thumb'])){
		load()->func('file');
		file_delete($row['thumb']);
	}
	if(!empty($row['userid'])){
		pdo_delete($this->table_user, array('id' => $row['userid']));
	}else{
		pdo_delete($this->table_user, array('tid' => $id, 'sid' => 0));
	}
	//讯贞触发
	xzTriggerCommon($schoolid,0,'delete_tea',$id);

	//校智付删除老师操作
	setXzfExtra($id,3);

	pdo_delete($this->table_teachers, array('id' => $id));


	/*  if(keep_wt() && CheckWtOn($schoolid)) {
		mload()->model('wt');
		//人员删除
		$param['guid'] = $row['guid'];
		$result = personAction($schoolid, $weid, time(), $param, 'delete');
		if($result['result'] != '1'){
			$back_msg = CheckWtReturnCode($result['code']);
		}
	} */


	DeleteTeacher($id);
	pdo_delete($this->table_class, array('tid' => $id));
	$result['msg'] = '删除成功！';
	$result['result'] = true;
	die(json_encode($result));
}elseif($operation == 'jiebang'){
	$id  = intval($_GPC['id']);
	$row = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $id));
	if(empty($row)){
		$this->imessage('抱歉，教师不存在或是已经被删除！', referer(), 'error');
	}
	$urlss = getoauthurl();
	if($urlss == 'manger.weimeizhan.com'){
		if(!$_W['isfounder']){
			if($id == 13 || $id == 448 || $id == 627){
				$this->imessage('抱歉，你无权编辑此教师（此老师是管理员绑定的），请另行添加或解绑其他老师！');
			}
		}
	}			
	$temp = array(
		'openid' => '',
		'userid' => 0,
		'uid'    => 0
	);
	if(!empty($row['userid'])){
		pdo_delete($this->table_user, array('id' => $row['userid']));
	}else{
		pdo_delete($this->table_user, array('tid' => $id, 'openid' => $row['openid'], 'sid' => 0, 'pard' => 0));
	}
	pdo_update($this->table_teachers, $temp, array('id' => $id));
	$this->imessage('解绑成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
	$rowcount    = 0;
	$notrowcount = 0;
	foreach($_GPC['idArr'] as $k => $id){
		$id = intval($id);
		if(!empty($id)){
			$assess = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $id));
			if(!empty($assess['uid'])){
				$message = '批量删除失败，老师' . $assess['tname'] . '微信未解绑！';

				die (json_encode(array(
					'result' => false,
					'msg'    => $message
				)));
			}
			if(empty($assess)){
				$notrowcount++;
				continue;
			}
			//讯贞触发
			xzTriggerCommon($schoolid, 0, 'delete_tea', $id);
			//校智付删除老师操作
			setXzfExtra($id,3);
			pdo_delete($this->table_teachers, array('id' => $id, 'weid' => $weid));
			/* if(keep_wt() && CheckWtOn($schoolid)) {
				mload()->model('wt');
				//人员删除
				$param['guid'] = $assess['guid'];
				personAction($schoolid, $weid, time(), $param, 'delete');
			} */
			DeleteTeacher($id);
			pdo_delete($this->table_class, array('tid' => $id));
			$rowcount++;
		}
	}
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	$data ['result'] = true;
	$data ['msg'] = $message;
	die (json_encode($data));
}elseif($operation == 'delclass'){
	$id      = intval($_GPC['id']);
	pdo_delete($this->table_class, array('id' => $id,'schoolid' => $schoolid));
}elseif($operation == 'clear'){
	pdo_delete($this->table_teachers, array('birthdate' => 0, 'sex' => 0, 'jiontime' => 0, 'weid' => $weid, 'schoolid' => $schoolid));
	$this->imessage('清理成功', $this->createWebUrl('assess', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'OneKeyUpPeople'){
	mload()->model('wt');
	$schoolid = $_GPC['schoolid'];
	$tealist = pdo_fetchall("SELECT id,tname,thumb,guid,photo_guid  FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' and weid = '{$weid}' ");

	foreach ($tealist as $key => $value){
		if(empty($value['guid'])){
			//未录入  人员录入
			$param['idcardNo'] = $value['id'];
			$param['name']     = $value['tname'];
			$result_guid       = personAction($schoolid, $weid, time(), $param, 'insert','tea');
			if($result_guid['result'] == '1'){
				$guid_insert =$result_guid['data']['guid'];
				$result_device =  People2Device($schoolid, $weid, time(),$guid_insert);
				$imgurl = tomedia($value['thumb']);
				$result_face = PersonFace($schoolid, $weid, time(),$guid_insert,$imgurl);
				if($result_face['result'] == '1'){
					$photo_guid_insert = $result_face['data']['guid'];
				}
				pdo_update(GetTableName('teachers',false),array('guid'=>$guid_insert,'photo_guid'=>$result_face['data']['guid']),array('id'=>$value['id']));
			}
		}else{
			//已录入 更新照片
			$param['guid'] = $value['guid'];
			$param['idcardNo'] = $value['id'];
			$param['name'] = $value['tname'];
			$result = personAction($schoolid, $weid, time(), $param, 'update','tea');
			$imgurl = tomedia($value['thumb']);
			//上传新的
			$result_face = PersonFace($schoolid, $weid, time(),$value['guid'],$imgurl);
			if($result_face['result'] == '1'){
				//删除旧的
				DeleteFace($schoolid, $weid, time(),$value['guid'],$value['photo_guid']);
				pdo_update(GetTableName('teachers',false),array('photo_guid'=>$result_face['data']['guid']),array('id'=>$value['id']));
			}
		}
	}
	$this->imessage('同步成功！', referer(), 'success');
}

if($operation == 'syncteainfo'){
	mload()->model('xzf'); 
	$total = $_GPC['total'];
	$page = $_GPC['page'];
	$res = SyncTeachers($schoolid,$total,$page);
	die(json_encode($res));
}
include $this->template('web/assess');
?>