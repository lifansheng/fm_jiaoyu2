<?php
  	global $_GPC, $_W;
	$weid = $_W['uniacid'];
	$action = 'assetsmanager';
	$this1 = 'no9';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	$schoolid = intval($_GPC['schoolid']);
	$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));			
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$tid_global = $_W['tid'];
	 
	if ($operation == 'display') {	
		if (!(IsHasQx($tid_global,1004321,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$status = $_GPC['status'] ? $_GPC['status'] : -1 ;
		$condition = '';
		if($status != -1){
			$condition .= "and status = '{$status}' ";
		}
		if(!empty($_GPC['assetsname'])){
			 $searchList = pdo_fetchall("SELECT id FROM ".GetTableName('assets')." WHERE name like '%{$_GPC['assetsname']}%' ");
			 $liststr = '';
			 foreach ($searchList as $key_s => $value_s) {
				 $liststr .= $value_s['id'].',';
			 }
			 $liststr = trim($liststr,',');
			 $condition .= " AND FIND_IN_SET(gwid,'{$liststr}') ";
		}

		if(!empty($_GPC['sqtname'])){
			$searchTList = pdo_fetchall("SELECT id FROM ".GetTableName('teachers')." WHERE tname like '%{$_GPC['sqtname']}%' ");
			$Tliststr = '';
			foreach ($searchTList as $key_s => $value_s) {
				$Tliststr .= $value_s['id'].',';
			}
			$Tliststr = trim($Tliststr,',');
			$condition .= " AND FIND_IN_SET(tid,'{$Tliststr}') ";
		}

		if(!empty($_GPC['cltname'])){
			if($_GPC['cltname'] != "管理员"){
				$searchTList_cl = pdo_fetchall("SELECT id FROM ".GetTableName('teachers')." WHERE tname like '%{$_GPC['cltname']}%' ");
				$Tliststr_cl = '';
				foreach ($searchTList_cl as $key_s_cl => $value_s_cl) {
					$Tliststr_cl .= $value_s_cl['id'].',';
				}
				$Tliststr_cl = trim($Tliststr_cl,',');
				$condition .= " AND FIND_IN_SET(cltid,'{$Tliststr_cl}') ";
			}else{
				$condition .= " AND cltid = -1 ";
			}
		}
		if(!empty($_GPC['shenqingtime'])) {
			$starttime = strtotime($_GPC['shenqingtime']['start']);
			$endtime = strtotime($_GPC['shenqingtime']['end']) + 86399;
		} else {
			$starttime = strtotime('-600 day');
			$endtime = TIMESTAMP;
		}
		$condition .= " AND createtime >= '{$starttime}' AND createtime <= '{$endtime}'";
		$list = pdo_fetchall(" SELECT * FROM ".GetTableName('assetsfix')." WHERE schoolid = '{$schoolid}' $condition  ORDER BY createtime DESC LIMIT ".($pindex - 1)*$psize." ,".$psize);
		foreach ($list as $key => $value) {
			$gwinfo  = pdo_fetch("SELECT * FROM ".GetTableName('assets')." WHERE id = '{$value['gwid']}' ");
			$teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['tid']}' ");
			if($value['status'] != 1){
				$cltea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['cltid']}' ");
			}
			 
			$list[$key]['gwname'] = $gwinfo['name'] ? $gwinfo['name'] : $value['gwname'];
			$list[$key]['gwtype'] = $gwinfo['type'];
			$list[$key]['tname'] = $teacher['tname'];
			$list[$key]['cltname'] = $cltea['tname'] ? $cltea['tname'] : "管理员" ;	 
			$list[$key]['returntname'] = $returntea['tname'] ? $returntea['tname'] : "管理员" ;	 
		}
		$total =pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('assetsfix')." WHERE schoolid = '{$schoolid}' $condition ORDER BY createtime DESC ");
		$pager = pagination($total, $pindex, $psize);	
	}elseif ($operation == 'GetSqInfo') {
		$id = $_GPC['id'];
		$check = pdo_fetch("SELECT * FROM ".GetTableName('assetsfix')." WHERE id='{$id}' ");	
		if(!empty($check)){
			$gwinfo  = pdo_fetch("SELECT * FROM ".GetTableName('assets')." WHERE id = '{$check['gwid']}' ");
			$teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$check['tid']}' ");
			if($check['status'] != 1){
				$cltea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$check['cltid']}' ");
			}
			$check['gwname'] = $gwinfo['name'] ? $gwinfo['name'] : $check['gwname'];
			$check['gwtype'] = $gwinfo['type'];
			$check['tname'] = $teacher['tname'];
			$check['cltname'] = $cltea['tname'] ? $cltea['tname'] : "管理员" ;	 
			$check['sqtime'] = date("Y-m-d H:i",$check['createtime']);
			$result['status'] = true;
			$result['data'] = $check;
		}else{
			$result['status'] = false;
		}
		die(json_encode($result));
	}elseif($operation == 'dealshenqing'){
		$id       = $_GPC['id'];
		$dealtype = $_GPC['dealtype'];
		$cltext   = $_GPC['cltext'];
		$check    = pdo_fetch("SELECT * FROM ".GetTableName('assetsfix')." WHERE id='{$id}' ");
		if(!empty($check)){
			$data = array(
				'status' => $dealtype,
				'cltime' => time(),
				'cltid'  => intval($tid_global) == 0 ? -1 :$tid_global,
				'cltext' => $cltext
			);
			pdo_update(GetTableName('assetsfix',false),$data,array('id'=>$id));


			$FixData = pdo_fetch("SELECT * FROM ".GetTableName('assetsfix')." WHERE id = '{$id}'  ");
			if($FixData['gwid'] != -1){
				$checkGw = pdo_fetch("SELECT * FROM ".GetTableName('assets')." WHERE id = '{$FixData['gwid']}' ");
				if(!empty($checkGw)){
					if($dealtype == 4){
						$wasteNumNew = $checkGw['wastenum'] + 1 ;
						pdo_update(GetTableName('assets',false),array('wastenum'=>$wasteNumNew),array('id'=>$FixData['gwid']));
					}
				}
			}


			$res = $this->sendMobileTeaAssetsFix($id,$schoolid,$weid);  
			$result['status'] = true ;
			$result['msg'] = "修改状态成功" ;
		}else{
			$result['status'] = false ;
			$result['msg'] = "该条记录不存在或是已删除！" ;
		}
		die(json_encode($result));
	}elseif($operation == 'delete'){
		$id = $_GPC['id'];
		if(!empty($id)){
			$check = pdo_fetch("SELECT id FROM ".GetTableName('assetsfix')." WHERE id = '{$id}' ");
			if(!empty($check)){
				pdo_delete(GetTableName('assetsfix',false),array('id'=>$id));
				$this->imessage('删除维修记录成功！', $this->createWebUrl('assetsfixlog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
			}else{
				$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
			}
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
		}
	}elseif ($operation == 'deleteall') {
		$rowcount = 0;
		$notrowcount = 0;
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				$check = pdo_fetch("SELECT * FROM " . GetTableName('assetsfix'). " WHERE id = :id", array(':id' => $id));
				if (empty($goocheckds)) {
					$notrowcount++;
					continue;
				}
				pdo_delete(GetTableName('assetsfix',false), array('id' => $id));
				$rowcount++;
			}
		}
		$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
		$data ['result'] = true;
		$data ['msg'] = $message;
		die (json_encode($data));
	}		
  include $this->template ( 'web/assetsfixlog' );
?>