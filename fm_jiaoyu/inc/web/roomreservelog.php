<?php
  	global $_GPC, $_W;
	$weid = $_W['uniacid'];
	$action = 'roomreservelog';
	$this1 = 'no9';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	$schoolid = intval($_GPC['schoolid']);
	$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));			
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$tid_global = $_W['tid'];
	if ($operation == 'display') {	
		if (!(IsHasQx($tid_global,1004201,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$status = $_GPC['status'] ? $_GPC['status'] : -1 ;
		$condition = '';
		if($status != -1){
			$condition .= "and status = '{$status}' ";
		}
		if(!empty($_GPC['roomname'])){
			 $searchList = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE sname like '%{$_GPC['roomname']}%' ");
			 $liststr = '';
			 foreach ($searchList as $key_s => $value_s) {
				 $liststr .= $value_s['sid'].',';
			 }
			 $liststr = trim($liststr,',');
			 $condition .= " AND FIND_IN_SET(roomid,'{$liststr}') ";
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
		$list = pdo_fetchall(" SELECT * FROM ".GetTableName('roomreserve')." WHERE schoolid = '{$schoolid}' $condition  ORDER BY createtime DESC LIMIT ".($pindex - 1)*$psize." ,".$psize);
		foreach ($list as $key => $value) {
			$roominfo = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE sid = '{$value['roomid']}' ");
			$teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['tid']}' ");
			$list[$key]['roomname'] = $roominfo['sname'];
			$list[$key]['tname'] = $teacher['tname'];
			if($value['status'] != 1){
				$cltea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['cltid']}' ");
			}
			$list[$key]['cltname'] = $cltea['tname'] ? $cltea['tname'] : "管理员" ;	 
		}
		$total =pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('roomreserve')." WHERE schoolid = '{$schoolid}' $condition  ORDER BY createtime DESC ");
		$pager = pagination($total, $pindex, $psize);	
	}elseif ($operation == 'pass') { //通过
		$id = $_GPC['id'];
		$checkIsHave = pdo_fetch("SELECT * FROM ".GetTableName('roomreserve')." WHERE id='{$id}' ");	
		if(!empty($checkIsHave)){
			$starttime = $thisinfo['starttime'];
			$endtime = $thisinfo['endtime'];
			$addrid = $thisinfo['roomid'];
			$condition = " and  ( ( starttime < '{$starttime}' and endtime > '{$starttime}' ) or ( starttime < '{$endtime}' and endtime > '{$endtime}'  )  or ( starttime > '{$starttime}' and endtime < '{$endtime}'  ) )  ";
			$check = pdo_fetch("SELECT * FROM ".GetTableName('roomreserve')." WHERE schoolid = '{$schoolid}' and roomid = '{$addrid}' {$condition} and status = 2  ");
			if(!empty($check)){
			 
				$this->imessage('当前场室在当前时间段已被预约', referer(), 'error');
			}else{
				$update_data = array(
					'cltid'  => $tid,
					'status' => 2,
					'cltime' => time()
				);
				pdo_update(GetTableName('roomreserve',false),$update_data,array('id'=>$id));
				$res = $this->sendMobileTeaRoomReserve($id,$schoolid,$weid);
				$this->imessage('修改状态成功', $this->createWebUrl('roomreservelog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
			}
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');

		}
	}elseif($operation == 'refuse') { //拒绝
		
		$id = $_GPC['id'];
		$checkIsHave = pdo_fetch("SELECT * FROM ".GetTableName('roomreserve')." WHERE id='{$id}' ");	
		if(!empty($checkIsHave)){
 
				$update_data = array(
					'cltid'  => $tid,
					'status' => 2,
					'cltime' => time()
				);
				pdo_update(GetTableName('roomreserve',false),$update_data,array('id'=>$id));
				$res = $this->sendMobileTeaRoomReserve($id,$schoolid,$weid);
				$this->imessage('修改状态成功', $this->createWebUrl('roomreservelog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
			 
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');

		}
	}elseif($operation == 'delete'){ //删除
		$id = $_GPC['id'];
		if(!empty($id)){
			$check = pdo_fetch("SELECT id FROM ".GetTableName('roomreserve')." WHERE id = '{$id}' ");
			if(!empty($check)){
				pdo_delete(GetTableName('roomreserve',false),array('id'=>$id));
				$this->imessage('删除公物成功！', $this->createWebUrl('roomreservelog', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
			}else{
				$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
			}
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
		}
	}elseif($operation == 'deleteall') { //批量删除
		$rowcount = 0;
		$notrowcount = 0;
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				$check = pdo_fetch("SELECT * FROM " . GetTableName('roomreserve'). " WHERE id = :id", array(':id' => $id));
				if (empty($check)) {
					$notrowcount++;
					continue;
				}
				pdo_delete(GetTableName('roomreserve',false), array('id' => $id));
				$rowcount++;
			}
		}
		$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
		$data ['result'] = true;
		$data ['msg'] = $message;
		die (json_encode($data));
	}	

  // include $this->template ( 'web/aproomset' );
  include $this->template ( 'web/roomreservelog' );
?>