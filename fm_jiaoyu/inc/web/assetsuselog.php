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
		if (!(IsHasQx($tid_global,1004311,1,$schoolid))){
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
		if(!empty($_GPC['tname'])){
			$searchTList = pdo_fetchall("SELECT id FROM ".GetTableName('teachers')." WHERE tname like '%{$_GPC['tname']}%' ");
			$Tliststr = '';
			foreach ($searchTList as $key_s => $value_s) {
				$Tliststr .= $value_s['id'].',';
			}
			$Tliststr = trim($Tliststr,',');
			$condition .= " AND FIND_IN_SET(tid,'{$Tliststr}') ";
		}

		if(!empty($_GPC['borrowtime'])) {
			$starttime = strtotime($_GPC['borrowtime']['start']);
			$endtime = strtotime($_GPC['borrowtime']['end']) + 86399;
			
		} else {
			$starttime = strtotime('-600 day');
			$endtime = TIMESTAMP;
		}
		$condition .= " AND createtime >= '{$starttime}' AND createtime <= '{$endtime}'";
		$list = pdo_fetchall(" SELECT * FROM ".GetTableName('assetstake')." WHERE schoolid = '{$schoolid}' $condition  ORDER BY createtime DESC LIMIT ".($pindex - 1)*$psize." ,".$psize);
		foreach ($list as $key => $value) {
			$gwinfo  = pdo_fetch("SELECT * FROM ".GetTableName('assets')." WHERE id = '{$value['gwid']}' ");
			$teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['tid']}' ");
			if($value['status'] != 1){
				$cltea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['cltid']}' ");
			}
			if($value['status'] == 4){
				$returntea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['returntid']}' ");
			}
			$list[$key]['gwname'] = $gwinfo['name'];
			$list[$key]['gwtype'] = $gwinfo['type'];
			$list[$key]['tname'] = $teacher['tname'];
			$list[$key]['cltname'] = $cltea['tname'] ? $cltea['tname'] : "管理员" ;	 
			$list[$key]['returntname'] = $returntea['tname'] ? $returntea['tname'] : "管理员" ;	 
		}
		$total =pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('assetstake')." WHERE schoolid = '{$schoolid}' $condition ");
		$pager = pagination($total, $pindex, $psize);	
	}elseif ($operation == 'pass') { //通过
		$id = $_GPC['id'];
		$check = pdo_fetch("SELECT * FROM ".GetTableName('assetstake')." WHERE id='{$id}' ");	
		if(!empty($check)){
			$data = array(
				'status' => 2,
				'cltime' => time(),
				'cltid' => intval($tid_global) == 0 ? -1 :$tid_global,
			);
			pdo_update(GetTableName('assetstake',false),$data,array('id'=>$id));
		 	 
			$res = $this->sendMobileTeaAssetsShenHe($id,$schoolid,$weid); 
			$this->imessage('修改状态成功', $this->createWebUrl('assetsuselog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');

		}
	}elseif ($operation == 'refuse') { //拒绝
		$id = $_GPC['id'];
		$check = pdo_fetch("SELECT * FROM ".GetTableName('assetstake')." WHERE id='{$id}' ");	
		if(!empty($check)){
			$data = array(
				'status' => 3,
				'cltime' => time(),
				'cltid' => intval($tid_global) == 0 ? -1 :$tid_global,
			);
			pdo_update(GetTableName('assetstake',false),$data,array('id'=>$id));
		 
		 	$res = $this->sendMobileTeaAssetsShenHe($id,$schoolid,$weid); 
			 $this->imessage('修改状态成功', $this->createWebUrl('assetsuselog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
		
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');

		}
	}elseif ($operation == 'return') { //归还
		$id = $_GPC['id'];
		$check = pdo_fetch("SELECT * FROM ".GetTableName('assetstake')." WHERE id='{$id}' ");	
		if(!empty($check)){
			$data = array(
				'status' => 4,
				'returntime' => time(),
				'returntid' => intval($tid_global) == 0 ? -1 :$tid_global,
			);
			pdo_update(GetTableName('assetstake',false),$data,array('id'=>$id));
		 
		 	$res = $this->sendMobileTeaAssetsShenHe($id,$schoolid,$weid); 
			$this->imessage('修改状态成功', $this->createWebUrl('assetsuselog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');

		}
	}elseif($operation == 'delete'){ //删除
		$id = $_GPC['id'];
		if(!empty($id)){
			$check = pdo_fetch("SELECT id FROM ".GetTableName('assetstake')." WHERE id = '{$id}' ");
			if(!empty($check)){
				pdo_delete(GetTableName('assetstake',false),array('id'=>$id));
				$this->imessage('删除公物成功！', $this->createWebUrl('assetsuselog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
			}else{
				$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
			}
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
		}
	}elseif ($operation == 'deleteall') { //批量删除
		$rowcount = 0;
		$notrowcount = 0;
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				$check = pdo_fetch("SELECT * FROM " . GetTableName('assetstake'). " WHERE id = :id", array(':id' => $id));
				if (empty($check)) {
					$notrowcount++;
					continue;
				}
				pdo_delete(GetTableName('assetstake',false), array('id' => $id));
				$rowcount++;
			}
		}
		$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
		$data ['result'] = true;
		$data ['msg'] = $message;
		die (json_encode($data));
	}	

  // include $this->template ( 'web/aproomset' );
  include $this->template ( 'web/assetsuselog' );
?>