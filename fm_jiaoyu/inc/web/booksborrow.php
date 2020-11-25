<?php
  global $_GPC, $_W;
   
	$weid = $_W['uniacid'];
	$action = 'booksborrow';
	$this1 = 'no8';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	$schoolid = intval($_GPC['schoolid']);
	$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));			
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$apartmentlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
	$bj   = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));

	
	$tid_global = $_W['tid'];
	if (!(IsHasQx($tid_global,1003601,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	} 
	if ($operation == 'bot') {	
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		if(!empty($_GPC['stuCard'])){
			$stu_id = pdo_fetch('SELECT sid FROM ' . tablename($this->table_idcard) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and idcard = '{$_GPC['stuCard']}' ");
			$students = pdo_fetch('SELECT id,s_name,icon,xq_id,bj_id,roomid FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id='{$stu_id['sid']}' ");
			if(empty($students)){
				$is_find = false;
			}else{
				$is_find = true;
				$students['njname'] = pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and type='semester' and sid = '{$students['xq_id']}'")['sname'];  
				$students['bjname'] = pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and type='theclass' and sid = '{$students['bj_id']}'")['sname'];  
				 
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_booksborrow) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and  sid = '{$students['id']}' order by createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			 
				$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_booksborrow) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and  sid = '{$students['id']}'");
				$pager = pagination($total, $pindex, $psize);					
			}
			
		}
		include $this->template ( 'web/booksborrow_bot' );	
	}elseif($operation == 'newborrow'){

		$bookname  = $_GPC['bookName'];
		$bookworth = $_GPC['bookworth'];
		$stuid     = $_GPC['stuid'];
		$data = array(
			'weid'=>$weid,
			'schoolid'=>$schoolid,
			'sid' =>$stuid,
			'bookname' =>$bookname,
			'worth' =>$bookworth,
			'borrowtime'=>time(),
			'createtime'=>time(),
			'status' => 1 
		);
		pdo_insert($this->table_booksborrow,$data);
		$pdoid = pdo_insertid();
		if($pdoid){
			die(json_encode(array(
				'result' => true,
				'msg' => '借阅成功！！',
			)));
		}else{
			die(json_encode(array(
				'result' => false,
				'msg' => '借阅失败，请刷新并重试！',
			)));
			
		}
		
	}elseif ($operation == 'returnbooks') {
		$id = $_GPC['id'];
		$schoolid = $_GPC['schoolid'];
		$check = pdo_fetch('SELECT * FROM ' . tablename($this->table_booksborrow) . " WHERE schoolid = '{$schoolid}' and id = '{$id}'"); 
		if(empty($check)){
			die(json_encode(array(
				'msg' => '归还失败，未查询到该条借阅信息'
			)));
		}else{
			$time = time();
			pdo_update($this->table_booksborrow,array('status'=>2,'returntime'=>$time),array('id'=>$id));
			die(json_encode(array(
				'msg' => '归还成功!!'
			)));
		}
	}
	elseif ($operation == 'delete') {
		$id = $_GPC['id'];
		$schoolid = $_GPC['schoolid'];
		$check = pdo_fetch('SELECT * FROM ' . tablename($this->table_booksborrow) . " WHERE schoolid = '{$schoolid}' and id = '{$id}'"); 
		if(empty($check)){
			die(json_encode(array(
				'msg' => '删除失败，未查询到该条借阅信息'
			)));
		}else{
			pdo_delete($this->table_booksborrow,array('id'=>$id));
			die(json_encode(array(
				'msg' => '删除成功!!'
			)));
		}  
			  
			  
	}elseif ($operation == 'display') {
		include $this->template ( 'web/booksborrow' );	 
	}	  

   
?>