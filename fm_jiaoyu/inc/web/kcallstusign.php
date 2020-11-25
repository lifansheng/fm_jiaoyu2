<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_GPC, $_W;
        
        $weid = $_W['uniacid'];
        $action = 'kecheng';
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	    $schoolid = intval($_GPC['schoolid']);
		$kcid1 = intval($_GPC['kcid']);
		$ksid1 = intval($_GPC['ksid']);
		$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ORDER BY ssort DESC", array(':id' => $schoolid));
		
		$kecheng = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where id = :id", array(':id' => $kcid1));
		$keshi = pdo_fetch("SELECT nub FROM " . tablename($this->table_kcbiao) . " where id = :id", array(':id' => $ksid1));
		
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        $tid_global = $_W['tid'];
		if ($operation == 'display') {
			if (!(IsHasQx($tid_global,1000941,1,$schoolid))){
				$this->imessage('非法访问，您无权操作该页面','','error');	
			}
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 1 AND status = 2 and kcid = '{$kcid1}' AND sid != 0 GROUP BY kcid,sid ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
            //var_dump($list);
				foreach($list as $index => $row){
							$kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
							$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
							$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE id = :id ", array(':id' => $row['userid']));
							$buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
							$hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid and status = 2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
							$check = pdo_fetch("SELECT id,createtime,status,qrtid FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and ksid=:ksid AND schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':ksid'=>$ksid1, ':schoolid'=> $schoolid));
							if($check['qrtid'] > 0){
								$qrtea =pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id ", array(':id' => $check['qrtid'])); 
							}elseif($check['qrtid'] == '-1'){
								$qrtea['tname'] = '系统管理员';
							}elseif($check['qrtid'] == '-2'){
								$qrtea['tname'] = '管理员';
							}
							$list[$index]['restnum'] = $buycourse - $hasSign;
							$list[$index]['buycourse'] = $buycourse;
							$list[$index]['hasSign'] = $hasSign;
							$list[$index]['kcnanme'] = $kc['name'];
							$list[$index]['s_name'] = $student['s_name'];
							$list[$index]['userinfo'] = $user['userinfo'];
							$list[$index]['pard'] = $user['pard'];
							$list[$index]['check'] = $check['id'];
							$list[$index]['signtime'] = $check['createtime'];
							$list[$index]['status'] = $check['status'];
							$list[$index]['qrtea'] = $qrtea['tname'];	
				}
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 1 AND status = 2 and kcid = '{$kcid1}'  ");
            $pager = pagination($total, $pindex, $psize);
			
        } elseif ($operation == 'sign') {
			$checkks = pdo_fetch("select id,costnum FROM ".tablename($this->table_kcbiao)." WHERE weid='{$weid}' And schoolid='{$schoolid}' And  id = '{$ksid1}'");
         	if($_W['tid'] == 'founder'){
	            $tid = '-2' ;
            }elseif($_W['tid'] == 'owner'){
	            $tid = '-1' ;
            }else{
	            $tid = $_W['tid']; 
            }
            $sid = intval($_GPC['sid']);
            if (empty($sid)) {
                $this->imessage('抱歉，该学生报名信息不存在或是已删除！', referer(), 'error');
            }
            if(!empty($_GPC['checkid'])){
             	pdo_update($this->table_kcsign, array('status'=> 2,'qrtid'=>$tid ), array('id' =>$_GPC['checkid']));
            }elseif(empty($_GPC['checkid'])){
	            $temp = array(
					'weid'=> $weid,
					'schoolid' => $schoolid,
					'kcid' => $kcid1,
					'ksid' => $ksid1,
					'sid' => $sid,
					'createtime' => time(),
					'status' => 2,
					'qrtid' => $tid,
					'costnum' => $checkks['costnum']
	            );
	            pdo_insert($this->table_kcsign, $temp);
            }
            $this->imessage('签到学生成功！', referer(), 'success');
        } elseif ($operation == 'unsign') {
            $sid = intval($_GPC['sid']);
            if (empty($sid)) {
                $this->imessage('抱歉，该学生报名信息不存在或是已删除！', referer(), 'error');
            }
            pdo_delete($this->table_kcsign, array('id' =>$_GPC['checkid']));
            $this->imessage('修改学生未签到成功！', referer(), 'success');
        }elseif ($operation == 'leave') {
          	if($_W['tid'] == 'founder'){
	            $tid = '-2' ;
            }elseif($_W['tid'] == 'owner'){
	            $tid = '-1' ;
            }else{
	            $tid = $_W['tid']; 
            }
            $sid = intval($_GPC['sid']);
            if (empty($sid)) {
                $this->imessage('抱歉，该学生报名信息不存在或是已删除！', referer(), 'error');
            }
            if(!empty($_GPC['checkid'])){
             	pdo_update($this->table_kcsign, array('status'=> 3,'qrtid'=>$tid ), array('id' =>$_GPC['checkid']));
            }elseif(empty($_GPC['checkid'])){
	          
	            $temp = array(
					'weid'=> $weid,
					'schoolid' => $schoolid,
					'kcid' => $kcid1,
					'ksid' => $ksid1,
					'sid' => $sid,
					'createtime' => time(),
					'status' => 3,
					'qrtid' => $tid
	            );
	            pdo_insert($this->table_kcsign, $temp);
            }
            $this->imessage('学生请假成功！', referer(), 'success');
        } elseif ($operation == 'signall') {
          	if($_W['tid'] == 'founder'){
	            $tid = '-2' ;
            }elseif($_W['tid'] == 'owner'){
	            $tid = '-1' ;
            }else{
	            $tid = $_W['tid']; 
            }
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $sid) {
                $sid = intval($sid);
                if (!empty($sid)) {
                    $check = pdo_fetch("SELECT id FROM " . tablename($this->table_kcsign) . " WHERE sid ='{$sid}' AND kcid='{$kcid1}' AND ksid ='{$ksid1}' ");
                    if (empty($check)) {
                     	$temp = array(
							'weid'=> $weid,
							'schoolid' => $schoolid,
							'kcid' => $kcid1,
							'ksid' => $ksid1,
							'sid' => $sid,
							'createtime' => time(),
							'status' => 2,
							'qrtid' => $tid
	            		);
            			pdo_insert($this->table_kcsign, $temp);
                    }elseif(!empty($check)){
	                    pdo_update($this->table_kcsign, array('status'=> 2,'qrtid'=>$tid ), array('id' =>$check['id']));
                    }
                    $rowcount++;
                }
            }			
			$message = "操作成功！共签到{$rowcount}名学生!";
			$data ['result'] = true;
			$data ['msg'] = $message;
			die (json_encode($data));
        }elseif ($operation == 'unsignall') {
          	
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $sid) {
                $sid = intval($sid);
                if (!empty($sid)) {
                    $check = pdo_fetch("SELECT id FROM " . tablename($this->table_kcsign) . " WHERE sid ='{$sid}' AND kcid='{$kcid1}' AND ksid ='{$ksid1}' ");
                    if (empty($check)) {
                     	 $rowcount++;
                    }elseif(!empty($check)){
	                    pdo_delete($this->table_kcsign,array('id' =>$check['id']));
                  		$rowcount++;
                    }
                }
            }			
			$message = "操作成功！{$rowcount}名学生状态修改为未签到，{$notrowcount}名学生操作失败！";
			$data ['result'] = true;
			$data ['msg'] = $message;
			die (json_encode($data));
        }elseif ($operation == 'leaveall') {
 			// $message =$_GPC['idArr'];
			//$data ['result'] = true;
			//$data ['msg'] = $message;
			//die (json_encode($data));
          	if($_W['tid'] == 'founder'){
	            $tid = '-2' ;
            }elseif($_W['tid'] == 'owner'){
	            $tid = '-1' ;
            }else{
	            $tid = $_W['tid']; 
            }
   //          $message =$tid;
			//$data ['result'] = true;
			//$data ['msg'] = $message;
			//die (json_encode($data));
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $sid) {
                $sid = intval($sid);
                if (!empty($sid)) {
                    $check = pdo_fetch("SELECT id FROM " . tablename($this->table_kcsign) . " WHERE sid ='{$sid}' AND kcid='{$kcid1}' AND ksid ='{$ksid1}' ");
                    if (empty($check)) {
                     	$temp = array(
							'weid'=> $weid,
							'schoolid' => $schoolid,
							'kcid' => $kcid1,
							'ksid' => $ksid1,
							'sid' => $sid,
							'createtime' => time(),
							'status' => 3,
							'qrtid' => $tid
	            		);
            			pdo_insert($this->table_kcsign, $temp);
                    }elseif(!empty($check)){
	                    pdo_update($this->table_kcsign, array('status'=> 3,'qrtid'=>$tid), array('id' =>$check['id']));
	                    $rowcount++;
                    }
                   
                }
            }			
			$message = "操作成功！共请假{$rowcount}名学生!";
			$data ['result'] = true;
			$data ['msg'] = $message;
			die (json_encode($data));
        }	
        include $this->template ( 'web/kcallstusign' );
?>