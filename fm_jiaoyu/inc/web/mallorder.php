<?php
  global $_GPC, $_W;
       
        $weid = $_W['uniacid'];
        $action = 'mallorder';
		$this1 = 'no6';
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	    $schoolid = intval($_GPC['schoolid']);
		$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
		
		$NjList = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND type = 'semester' ");
		$BjList = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND type = 'theclass' ");

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$tid_global = $_W['tid'];
        if ($operation == 'display') {
			if (!(IsHasQx($tid_global,1002701,1,$schoolid))){
				$this->imessage('非法访问，您无权操作该页面','','error');	
			}	
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
			$condition = '';
			
            if (!empty($_GPC['number'])) {
                $condition .= " AND torderid = '{$_GPC['number']}'";	
            }
			$status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
			if($status > 0) {
				$condition .= " AND status = '{$status}'";
				$params[':status'] = $status;
			}
			$cop = isset($_GPC['cop']) ? intval($_GPC['cop']) : -1;	
			if($cop > 0) {
				$condition .= " AND cop = '{$cop}'";
				$params[':cop'] = $cop;
			}		
			if(!empty($_GPC['createtime'])) {
				$starttime = strtotime($_GPC['createtime']['start']);
				$endtime = strtotime($_GPC['createtime']['end']) + 86399;
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			} else {
				$starttime = strtotime('-200 day');
				$endtime = TIMESTAMP;
			}

            if (!empty($_GPC['keyword'])) {
	            if(!empty($_GPC['showtype'])){
					if($_GPC['showtype'] == 1){
						$tea = pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And tname = :tname ", array(':schoolid' => $schoolid,':tname' => $_GPC['keyword']));
						if(!empty($tea)){
							$condition .= " AND tid = '{$tea['id']}'";
						}elseif(empty($tea)){
							$condition .= " AND tid = 0 ";
						}
					}elseif($_GPC['showtype'] == 2){
						$student_id = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ", array(':schoolid' => $schoolid,':s_name' => $_GPC['keyword']));
						if(!empty($student_id)){
							$condition .= " AND sid = '{$student_id['id']}'";
						}
						if(empty($student_id))
						{
							$condition .= "  AND sid = 0 ";
						}
					}
				}
			}

			$search_nj = $_GPC['nj'] ? $_GPC['nj'] : 0 ;
			$search_bj = $_GPC['bj'] ? $_GPC['bj'] : 0 ;
			$condition_cell = '';
			if($search_nj != 0 ){
				$condition_cell .= " and xq_id = '{$search_nj}' ";
			}
			if($search_bj != 0 ){
				$condition_cell .= " and bj_id = '{$search_bj}' ";
			}

			if(!empty($condition_cell)){
				$condition .= " and sid IN ( SELECT id FROM ".GetTableName('students')." WHERE schoolid ='{$schoolid}' {$condition_cell} ) ";
			}


			
			if(!empty($_GPC['showtype'])){
				if($_GPC['showtype'] == 1){
					$condition .= " AND sid = 0 ";
				}elseif($_GPC['showtype'] == 2){
					$condition .= " AND tid = 0 ";
				}
				
			}
			if(!empty($_GPC['receive']))
			{
				 $condition .= " AND tname = '{$_GPC['receive']}' ";
			}

			//var_dump($condition);
			/////////////////////////////////////////////////////////导出订单记录
            if($_GPC['out_putcode'] == 'out_putcode'){
				$listss = pdo_fetchall("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' {$condition} ORDER BY createtime DESC");
				$ii   = 0;
				foreach($listss as $index => $row){
					$good = pdo_fetch("SELECT title,new_price,points FROM " . tablename($this->table_mall) . " WHERE id = :id ", array(':id' => $row['goodsid']));
					$arr[$ii]['orderid'] = $row['torderid'];
					$arr[$ii]['goodname']  = $good['title'];
					if($row['tid'] != 0 && $row['sid'] == 0){
						$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $row['tid'],':weid' => $weid,':schoolid' => $schoolid));
						$arr[$ii]['username'] = $teacher['tname'];
						$arr[$ii]['bjname'] = "老师";
						$arr[$ii]['leixing'] = "教师";
					}elseif($row['tid'] == 0 && $row['sid'] != 0 && $row['userid'] != 0 ){
						$usertemp = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $row['userid'],':weid' => $weid,':schoolid' => $schoolid));

						$userinfo = unserialize($usertemp['userinfo']);
						if(!empty($usertemp['pard'])){
			                $pard = $usertemp['pard'];
			                $guanxi = get_guanxi($pard);
			              
			            }
						$student = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $row['sid'],':weid' => $weid,':schoolid' => $schoolid));
						$bjName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");
						$arr[$ii]['username'] = $userinfo['name'];
						$arr[$ii]['bjname'] = $bjName['sname'];

						$arr[$ii]['leixing'] = "学生"; 
					}
					$arr[$ii]['shname'] = $row['tname'];
					$arr[$ii]['shphone'] = $row['tphone'];
					$arr[$ii]['address'] = $row['taddress'];
					$arr[$ii]['beizhu'] = $row['beizhu'];
					
					$arr[$ii]['count'] = $row['count'];
					$arr[$ii]['cost'] = $good['new_price'] * intval($row['count']);
					$arr[$ii]['costp'] = $row['allpoint'];
					if($row['status'] == 1){
						$arr[$ii]['status'] = "未支付";
					}elseif($row['status'] == 2){
						$arr[$ii]['status'] = "待发货";
					}elseif($row['status'] == 3){
						$arr[$ii]['status'] = "已发货";
					}elseif($row['status'] == 4){
						$arr[$ii]['status'] = "已完成";
					}
					
					$arr[$ii]['sktime']  = date('Y年m月d日 h:i:s', $row['createtime']);
					$ii++;
				}
				$nowtime = date('Y年m月d日',TIMESTAMP);
				$exceltitle = '商城订单记录表'.$nowtime.'导出';
				$this->exportexcel($arr, array('订单号','商品名','姓名','班级','老师/学生','收货人姓名','联系方式','收货地址','备注','数量','总金额','总积分','状态','下单时间'), $exceltitle);
                exit();
			}   
            			
			$params[':start'] = $starttime;
			$params[':end'] = $endtime;
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			foreach($list as $index => $row){
				$good = pdo_fetch("SELECT * FROM " . tablename($this->table_mall) . " WHERE id = :id ", array(':id' => $row['goodsid']));
				if($row['tid'] != 0 && $row['sid'] == 0){
					$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $row['tid'],':weid' => $weid,':schoolid' => $schoolid));
					$list[$index]['userleixing'] = "教师";
					$list[$index]['teaname'] = $teacher['tname'];
				}elseif($row['tid'] == 0 && $row['sid'] != 0 && $row['userid'] != 0 ){
					$student = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $row['sid'],':weid' => $weid,':schoolid' => $schoolid));
					$usertemp = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " WHERE id = :id And weid = :weid And schoolid = :schoolid", array(':id' => $row['userid'],':weid' => $weid,':schoolid' => $schoolid));
					$bjName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");

					if(!empty($usertemp['pard'])){
						$pard = $usertemp['pard'];
						$guanxi = get_guanxi($pard);
						$list[$index]['sf'] = $guanxi;
					}
					$userinfo = unserialize($usertemp['userinfo']);
					//var_dump($userinfo);
					$list[$index]['userleixing'] = "学生";
					$list[$index]['bjname'] = $bjName['sname'];
					$list[$index]['teaname'] = $userinfo['name'];
					$list[$index]['stuname'] = $student['s_name'];
				}
				$order = pdo_fetch("SELECT cose FROM " . tablename($this->table_order) . " WHERE id = :id ", array(':id' => $row['torderid']));	
				
				$list[$index]['goodname'] = $good['title'];
				$list[$index]['cost'] = $good['new_price'] * intval($row['count']);
				$list[$index]['costp'] = $row['allpoint'];
				$list[$index]['ordertime'] = date("Y-m-d H:i",$row['createtime']);
			}
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_mallorder) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");

            $pager = pagination($total, $pindex, $psize);			
						

        }
        elseif ($operation == 'unpay') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			
			$Torder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id = :id ", array(':id' => $id));
			if (empty($Torder)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			$Torderid = $Torder['torderid'];
			$data = array('status' => 1);
			$odata = array('status' => 1,'paytime' => '','paytype' => 3,'pay_type' => 'no');
            pdo_update($this->table_mallorder, $data, array('id' => $id));
         	pdo_update($this->table_order, $odata, array('id' => $Torderid));
         	pdo_delete(core_paylog,array('tid' => $Torderid));
            $this->imessage('操作成功！', referer(), 'success');

        }elseif ($operation == 'unsend') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			$Torder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id = :id ", array(':id' => $id));
			if (empty($Torder)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			$Torderid = $Torder['torderid'];
			$data = array('status' => 2);
            $odata = array('status' => 2,'paytime' => TIMESTAMP,'paytype' => 2,'pay_type' => 'cash'); 
           	if(!empty($Torder['tid']) && empty($Torder['sid'])){
				$teacher = pdo_fetch("SELECT point FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $Torder['tid']));
				if($teacher['point'] == $Torder['allpoint']){
					$new_point = 0 ;
				}elseif($teacher['point'] > $Torder['allpoint']){
					$new_point = intval($teacher['point']) - intval($Torder['allpoint']);
					pdo_update($this->table_teachers, array('point' => $new_point ), array('id' => $Torder['tid']));
				}elseif($teacher['point'] < $Torder['allpoint']){
					$this->imessage('抱歉，该教师剩余积分不足！');
				}
			}elseif(empty($Torder['tid'] ) && !empty($Torder['sid'])){
				$JFinfo =  pdo_fetch("SELECT Is_point,Cost2Point FROM " . tablename($this->table_index) . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $Torder['schoolid'],':weid'=>$Torder['weid'] ));
				if($JFinfo['Is_point'] ==1){ 
					$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $Torder['sid']));
					$money = $Torder['allcash'];
					$Cost2Point = $JFinfo['Cost2Point'];
					$addpoint = intval($money * $Cost2Point);
					if($students['points'] == $Torder['allpoint']){
						$new_point = 0 + $addpoint;
					}elseif($students['points'] > $Torder['allpoint']){
						$new_point = intval($students['points']) - intval($Torder['allpoint']) + $addpoint;
						pdo_update($this->table_students, array('points' => $new_point ), array('id' => $Torder['sid']));
					}elseif($students['points'] < $Torder['allpoint']){
						$this->imessage('抱歉，该学生师剩余积分不足！');
					}
				}
			}
		 	pdo_update($this->table_mallorder, $data, array('id' => $id));
         	pdo_update($this->table_order, $odata, array('id' => $Torderid));
         	if($Torder['tid'] == 0 && $Torder['sid'] != 0 ){
			 	$this->sendMobileJfjgtz($Torderid);
			}
		 	$this->imessage('操作成功！', referer(), 'success');
           
        } elseif ($operation == 'send') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			
			$Torder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id = :id ", array(':id' => $id));
			if (empty($Torder)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			$Torderid = $Torder['torderid'];
			
			$data = array('status' => 3);
            $odata = array('status' => 2,'paytime' => time(),'paytype' => 2,'pay_type' => 'cash');
            if($Torder['status'] ==1){
            	if(!empty($Torder['tid']) && empty($Torder['sid'])){
					$teacher = pdo_fetch("SELECT point FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $Torder['tid']));
					if($teacher['point'] == $Torder['allpoint']){
						$new_point = 0 ;
					}elseif($teacher['point'] > $Torder['allpoint']){
						$new_point = intval($teacher['point']) - intval($Torder['allpoint']);
						pdo_update($this->table_teachers, array('point' => $new_point ), array('id' => $Torder['tid']));
					}elseif($teacher['point'] < $Torder['allpoint']){
						$this->imessage('抱歉，该教师剩余积分不足！');
					}
				}elseif(empty($Torder['tid'] ) && !empty($Torder['sid'])){
					$JFinfo =  pdo_fetch("SELECT Is_point,Cost2Point FROM " . tablename($this->table_index) . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $Torder['schoolid'],':weid'=>$Torder['weid'] ));
					if($JFinfo['Is_point'] ==1){ 
						$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $Torder['sid']));
						$money = $Torder['allcash'];
						$Cost2Point = $JFinfo['Cost2Point'];
						$addpoint = intval($money * $Cost2Point);
						if($students['points'] == $Torder['allpoint']){
							$new_point = 0 + $addpoint;
						}elseif($students['points'] > $Torder['allpoint']){
							$new_point = intval($students['points']) - intval($Torder['allpoint']) + $addpoint;
							pdo_update($this->table_students, array('points' => $new_point ), array('id' => $Torder['sid']));
						}elseif($students['points'] < $Torder['allpoint']){
							$this->imessage('抱歉，该学生师剩余积分不足！');
						}
					}
				}
			}
            pdo_update($this->table_mallorder, $data, array('id' => $id));
         	pdo_update($this->table_order, $odata, array('id' => $Torderid)); 
			$this->imessage('操作成功！', referer(), 'success');
           
        } elseif ($operation == 'finish') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			 
			$Torder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id = :id ", array(':id' => $id));
			if (empty($Torder)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			$Torderid = $Torder['torderid'];
			$data = array('status' => 4);
            $odata = array('status' => 2,'paytime' => time(),'paytype' => 2,'pay_type' => 'cash');
		 	pdo_update($this->table_mallorder, $data, array('id' => $id));
         	pdo_update($this->table_order, $odata, array('id' => $Torderid)); 
			$this->imessage('操作成功！', referer(), 'success');
           
        }
        elseif ($operation == 'finish') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			 
			$Torder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id = :id ", array(':id' => $id));
			if (empty($Torder)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			$Torderid = $Torder['torderid'];
			$data = array('status' => 4);
            $odata = array('status' => 2,'paytime' => time(),'paytype' => 2,'pay_type' => 'cash');
		 	pdo_update($this->table_mallorder, $data, array('id' => $id));
         	pdo_update($this->table_order, $odata, array('id' => $Torderid)); 
			$this->imessage('操作成功！', referer(), 'success');
           
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			 
			$Torder = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id = :id ", array(':id' => $id));
			if (empty($Torder)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
			$Torderid = $Torder['torderid'];
			
		 	pdo_delete($this->table_mallorder,array('id'=>$id));
         	pdo_delete($this->table_order,  array('id' => $Torderid)); 
         	pdo_delete(core_paylog,array('tid' => $Torderid));
			$this->imessage('操作成功！', referer(), 'success');
           
        }elseif ($operation == 'deleteall') {
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                if (!empty($id)) {
                    $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE id = :id", array(':id' => $id));
                    if (empty($goods)) {
                        $notrowcount++;
                        continue;
                    }
                    pdo_delete($this->table_mallorder, array('id' => $id));
                    pdo_delete($this->table_order, array('id' => $goods['torderid']));
                    pdo_delete(core_paylog,array('tid' => $goods['torderid']));
                    
                    $rowcount++;
                }
            }
			$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

			$data ['result'] = true;

			$data ['msg'] = $message;

			die (json_encode($data));
        }	  

   include $this->template ( 'web/mallorder' );
?>