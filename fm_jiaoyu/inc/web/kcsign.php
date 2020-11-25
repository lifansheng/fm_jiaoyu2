<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
	global $_GPC, $_W;

	$weid              = $_W['uniacid'];
	$action            = 'kecheng';
	$this1             = 'no2';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
	$schoolid          = intval($_GPC['schoolid']);
	$logo              = pdo_fetch("SELECT logo,title,is_kb FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");

	/** 学期? */
	$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));

	/** 科目? */
	$km = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));

	/** 班级? */
	$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));

	/** 星期? */
	$xq = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'week', ':schoolid' => $schoolid));

	/** 时段? */
	$sd = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));

	/** 期号 */
	$qh = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));

	$category = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');

	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$tid_global = $_W['tid'];
	if($tid_global !='founder' && $tid_global != 'owner'){
		$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
		$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
}
	if($operation == 'display'){
	 	if (!(IsHasQx($tid_global,1000941,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		}
		$out_excel = $_GPC['out_excel'];
	    $pindex    = max(1, intval($_GPC['page']));
	    $psize     = 20;
	    $condition = '';
	    $time = time();
		$TorS = !empty($_GPC['TorS'])?$_GPC['TorS'] : -1 ;
		switch ( $TorS )
		{
			case -1 :
				break;
			case 1 :
				$condition .= "AND sid = 0 ";
				break;
			case 2 :
				$condition .= "AND tid = 0 ";
				break;	
			default:
				break;
		}
		$is_confirm = !empty($_GPC['is_confirm'])?$_GPC['is_confirm'] : -1 ;
		switch ( $is_confirm )
		{
			case -1 :
				break;
			case 1 :
				$condition .= "AND status = 2 ";
				break;
			case 2 :
				$condition .= "AND status = 1 ";
				break;	
			default:
				break;
		}
	  
	    if(!empty($_GPC['kcname'])){
		     $kcname = trim($_GPC['kcname']);
		    $kcsearch = pdo_fetchall("SELECT id FROM " . tablename($this->table_tcourse) . " WHERE weid='{$weid}' AND schoolid='{$schoolid}' and name LIKE '%$kcname%' ");
		    $kcid_temp = '';
		    if(!empty($kcsearch)){
			    foreach( $kcsearch as $key => $value )
			    {
			    	$kcid_temp .=$value['id'].",";
			    }
			    $kcid_str = trim($kcid_temp,",");
		        $condition .= " AND kcid in ({$kcid_str}) ";
	        }
	        else{
		         $condition .= " AND kcid = 0 ";
	        }
	    }
	   
		if (!empty($_GPC['tname'])) {
		            $tname = trim($_GPC['tname']);
		            $tid = pdo_fetch("SELECT id FROM " . tablename ($this->table_teachers) . " where weid='{$weid}' AND schoolid='{$schoolid}' AND tname ='{$tname}'");
	                $condition .= "AND tid ='{$tid['id']}' ";
	    }

	    if (!empty($_GPC['sname'])) {
		            $sname = trim($_GPC['sname']);
		            $sid =pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " where weid='{$weid}' AND schoolid='{$schoolid}' AND s_name ='{$sname}'");
	                $condition .= "AND sid ='{$sid['id']}' ";
	    }
	   
	    if(!empty($_GPC['createtime'])) {
					$starttime = strtotime($_GPC['createtime']['start']);
					$endtime = strtotime($_GPC['createtime']['end']) + 86399;
					$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
				} else {
					$starttime = strtotime('-600 day');
					$endtime = TIMESTAMP;
				}
	 	if($out_excel == "Yes"){
		 	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_kcsign) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC " );
		 	$ii = 0;
		 	$array_out = array();
		 	  foreach($list as $key => $row){
			 	   if(!empty($row['sid']) && empty($row['tid'])){
			        $useName = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $row['sid']));
			        $array_out[$ii]['username'] = $useName['s_name'];
			        $array_out[$ii]['TorS'] = "学生";
		        }elseif(empty($row['sid']) && !empty($row['tid'])){
			        $useName = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
			        $array_out[$ii]['username'] = $useName['tname'];
			        $array_out[$ii]['TorS'] = "老师";
		        }
		        $kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $row['kcid']));
		        $array_out[$ii]['kcname'] =$kc['name'];
			   
		        if(!empty($row['ksid'])){
					$ks = pdo_fetch("SELECT * FROM " . tablename($this->table_kcbiao) . " where id = :id ", array(':id' => $row['ksid']));
					$checknubs = GetOneKcKsOrder($row['kcid'],$row['ksid']);
			        $array_out[$ii]['ksname'] ="第".$checknubs['nuber']."课";
		        }elseif(empty($row['ksid'])){
			        $array_out[$ii]['ksname']= date("m月d日课",$row['createtime']);
		        }
		       	$array_out[$ii]['createtime'] = date("Y-m-d H:i:s",$row['createtime']);
		        if($row['status'] ==1){
			        $array_out[$ii]['status'] = "未确认";
		        }elseif($row['status'] ==2){
			        $array_out[$ii]['status'] = "已确认";
		        }
		        $ii++;
		    }
		    $title="签到信息-".date("Y年m月d日导出",time());
		    $this->exportexcel($array_out, array('签到人','教师/学生','课程名称','课时','签到时间','状态' ), $title);
			exit();
	 	}else{
		    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_kcsign) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		    foreach($list as $key => $row){
		        $kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $row['kcid']));
		        $list[$key]['kcname'] = $kc['name'];
		       
		        if(!empty($row['ksid'])){
			        $ks = pdo_fetch("SELECT * FROM " . tablename($this->table_kcbiao) . " where id = :id ", array(':id' => $row['ksid']));
					$checknubs = GetOneKcKsOrder($row['kcid'],$row['ksid']);
					$list[$key]['ksname'] ="第".$checknubs['nuber']."课";
		        }elseif(empty($row['ksid'])){
			         $list[$key]['ksname']= date("m月d日课",$row['createtime']);
		        }
		        if(!empty($row['sid']) && empty($row['tid'])){
			        $useName = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $row['sid']));
			        $list[$key]['username'] = $useName['s_name'];
			        $list[$key]['TorS'] = "S";
		        }elseif(empty($row['sid']) && !empty($row['tid'])){
			        $useName = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
			        $list[$key]['username'] = $useName['tname'];
			        $list[$key]['TorS'] = "T";
		        }
		    }
		    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_kcsign) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");

		    $pager = pagination($total, $pindex, $psize);
	    }
	}elseif($operation == 'post'){
        $id = intval($_GPC['id']);

        $condition = '';
        $SignDetail =  pdo_fetch("SELECT * FROM " . GetTableName('kcsign') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$id}' ");

        if(!empty($SignDetail['sid']) && empty($SignDetail['tid'])){
            $student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$SignDetail['sid']}' ");
            $condition .= " and sid = '{$SignDetail['sid']}' ";
        }elseif(empty($SignDetail['sid']) && !empty($SignDetail['tid'])){
            $teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$SignDetail['tid']}' ");
            $condition .= " and tid = '{$SignDetail['tid']}' ";
        }
        $kclist =  pdo_fetchall("SELECT distinct (orders.kcid) , tcourse.name ,tcourse.id FROM " . GetTableName('order') . " as orders , " . GetTableName('tcourse') . " as tcourse WHERE orders.weid = '{$weid}' AND orders.schoolid = '{$schoolid}' and orders.sid = '{$SignDetail['sid']}' and orders.type = '1' and orders.status = 2 and orders.kcid = tcourse.id order by tcourse.id ");
		$kslist =  pdo_fetchall("SELECT * FROM " . GetTableName('kcbiao') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and kcid = '{$SignDetail['kcid']}' ");
		foreach($kslist as $kk => $vv){
			$checknubs = GetOneKcKsOrder($vv['kcid'],$vv['id']);
			$kslist[$kk]['nub'] = $checknubs['nuber'];
		};
        //确认修改签到信息
        if(checksubmit('submit')){
            $id = intval($_GPC['id']);
            $kcid = $_GPC['signkc'];
            $ksid = $_GPC['signks'];
            $signtime =strtotime($_GPC['signtime']);
            $checktype =  pdo_fetch("SELECT OldOrNew FROM " . GetTableName('tcourse') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$kcid}' ");
            $checksNum =  pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$ksid}' ");

            if($checktype['OldOrNew'] == 0){
                $data = array(
                    'kcid'=>$kcid,
                    'ksid'=>$ksid,
                    'type'=>$checktype['OldOrNew'],
                    'createtime' => $signtime,
                    'costnum' => $checksNum['costnum'],
                    'status' => 2
                );
            }elseif($checktype['OldOrNew'] == 1){
                $data = array(
                    'kcid'=>$kcid,
                    'ksid'=>0,
                    'type'=>$checktype['OldOrNew'],
                    'createtime' => $signtime,
                    'signtime' => $signtime,
                    'costnum' => 1,
                    'status' => 2
                );
            }
            if($checktype['OldOrNew'] == 0){
                $checkIsSign = pdo_fetch("SELECT * FROM " . GetTableName('kcsign') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and ksid = '{$ksid}'  and kcid = '{$kcid}' $condition  ");
                if(!empty($checkIsSign) && $checkIsSign['id'] != $id ){
                    $this->imessage('修改失败，当前老师/学生已签到该课时', referer(), 'error');
                    die();
                }
            }
            if(!empty($SignDetail['sid']) && empty($SignDetail['tid'])){
                $count_done = pdo_fetchcolumn("SELECT sum(costnum) FROM ".tablename($this->table_kcsign)." WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid And status=:status", array(':weid' => $weid, ':sid' => $SignDetail['sid'], ':schoolid' => $schoolid,':kcid'=>$kcid,':status'=>2));
                $count_all = pdo_fetchcolumn("SELECT ksnum FROM ".tablename($this->table_coursebuy)." WHERE :weid = weid AND :sid = sid AND :schoolid = schoolid and kcid=:kcid ", array(':weid' => $weid, ':sid' => $SignDetail['sid'], ':schoolid' => $schoolid,':kcid'=>$kcid));
                $rest = $count_all - $count_done - $checksNum['costnum'];
                /*var_dump($rest);
                die();*/

                if($rest < 0){
                    $this->imessage('修改失败，该学生当前课程剩余课时不足', referer(), 'error');
                }else{
                    pdo_update($this->table_kcsign,$data,array('id'=>$id));
                    if(empty($checkIsSign)){
                        $this->sendMobileXsqrqdtz($id, $_GPC['schoolid'], $_W['uniacid']);
                    }
                }
            }else{
                pdo_update($this->table_kcsign,$data,array('id'=>$id));
            }
            $this->imessage('修改成功', $this->createWebUrl('kcsign',array('op'=>'display','schoolid'=>$schoolid)));

        }
    }elseif($operation == 'GetKsDetail'){
        $kcid = intval($_GPC['kcid']);
        $checktype =  pdo_fetch("SELECT OldOrNew FROM " . GetTableName('tcourse') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$kcid}' ")['OldOrNew'];
        if($checktype == 0) {
            $kslist = pdo_fetchall("SELECT nub,id,kcid FROM " . GetTableName('kcbiao') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and kcid = '{$kcid}' ");
            $result['datatype'] = 0;
            $result['data'] = $kslist;
        }else{
            $result['datatype'] = 1;
        }
        die(json_encode($result));
    }elseif($operation == 'delete'){
	    $id = intval($_GPC['id']);
	    if(empty($id)){
	        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
	    }
	    pdo_delete($this->table_kcsign, array('id' => $id));
	    $this->imessage('删除成功！', referer(), 'success');
	}elseif($operation == 'queren'){
	    $id = intval($_GPC['id']);
	    if(empty($id)){
	        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
	    }
	    pdo_update($this->table_kcsign,array('status'=> 2),array('id' => $id));
	    $this->imessage('确认签到成功！', referer(), 'success');
	}
	
	include $this->template('web/kcsign');
?>