<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'chongzhi';
$this1             = 'no4';
$action            = 'chongzhi';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];




if($operation =='display' && !(IsHasQx($tid_global,1002201,1,$schoolid))){
	$operation = 'chongzhilog';
	$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl('chongzhi', array('schoolid' => $schoolid,'op'=>$operation));
	header("location:$stopurl");
}
if($operation =='chongzhilog' && !(IsHasQx($tid_global,1002211,1,$schoolid))){
	$operation = 'cardchongzhi';
	$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl('chongzhi', array('schoolid' => $schoolid,'op'=>$operation));
	header("location:$stopurl");
}
if($operation =='cardchongzhi' && !(IsHasQx($tid_global,1002221,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}





if($operation == "DownExcel"){
 
	if($_GPC['isDetail'] == 'isDetail'){
		$datalist = [];
		$starttime = strtotime($_GPC['down_time']['start']);
		$endtime = strtotime($_GPC['down_time']['end']) + 86399;
		$condition .= " AND createtime >= '{$starttime}' AND createtime <= '{$endtime}'";
		$list = pdo_fetchall(" SELECT * FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and yue_type = 2 and cost_type = 3 $condition ORDER BY createtime DESC  " );
		$ii = 0;
		foreach($list as $key => $row){
			$student = pdo_fetch("SELECT id,icon,s_name,bj_id,xq_id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['sid']));
			$datalist[$ii]['s_name'] = $student['s_name'];

			$bj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['bj_id']));
			$nj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['xq_id']));
			$datalist[$ii]['bj_name'] = $bj_name['sname'];
			$datalist[$ii]['nj_name'] = $nj_name['sname'];
			$datalist[$ii]['money'] = $row['cost'];
			$datalist[$ii]['time'] = date("Y-m-d H:i",$row['createtime']);
			if($row['cztid'] == -1){
				$datalist[$ii]['cztname'] = "管理员";
			}else{
				$cztea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$row['cztid']}' ");
				$datalist[$ii]['cztname'] = $cztea['tname'];
			}
			$ii++;
		}

	
		$ExcelTitle = "充值详情表";
		$this->exportexcel($datalist, array('学生姓名','年级','班级','充值金额','时间' ,'操作员'), $ExcelTitle);
		exit(); 
	}

	$starttime = strtotime($_GPC['down_time']['start']);
	$endtime = strtotime($_GPC['down_time']['end']) + 86399;


	mload()->model('excel');

	$title = $logo['title'];

	$LoopBeginTime = strtotime(date("Y-m-d",$starttime));
	$datalist = [];
	;

	$bet =($endtime + 1 - $LoopBeginTime)/86400;
	

	for($ii = 0;$ii < $bet ;$ii++){
		
		$SingleBeginTime = $LoopBeginTime + $ii * 86400;
		$SingleEndTime = $SingleBeginTime + 86400;

	
	
		$ButieAll = pdo_fetchcolumn("SELECT sum(cost) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and cost_type = 1 and yue_type = 1 and createtime >= '{$SingleBeginTime}' and createtime <'{$SingleEndTime}' ");
		$Chongzhi = pdo_fetchcolumn("SELECT sum(cost) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and cost_type = 1 and yue_type = 2 and on_offline = 1 and createtime >= '{$SingleBeginTime}' and createtime <'{$SingleEndTime}' ");
		$AdminChongzhi_1 = pdo_fetchcolumn("SELECT sum(cost) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and cost_type = 3 and yue_type = 2  and createtime >= '{$SingleBeginTime}' and createtime <'{$SingleEndTime}' ");
		$OfflineStuChongzhi = pdo_fetchcolumn("SELECT sum(cost) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and cost_type = 1 and yue_type = 2 and on_offline = 2 and createtime >= '{$SingleBeginTime}' and createtime <'{$SingleEndTime}' ");
		$AdminChongzhi = $AdminChongzhi_1 + $OfflineStuChongzhi;
		$ReFound = pdo_fetchcolumn("SELECT sum(cost) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and cost_type = 4 and yue_type = 2  and createtime >= '{$SingleBeginTime}' and createtime <'{$SingleEndTime}' ");
		$datalist[$ii]['time'] = date("Y-m-d",$SingleBeginTime);
		$datalist[$ii]['butie'] = $ButieAll ? round(100,2): 0 ;
		$datalist[$ii]['houtaichongzhi'] = $AdminChongzhi ? round($AdminChongzhi,2) : 0 ;
		$datalist[$ii]['xianshangchongzhi'] = $Chongzhi ? round($Chongzhi,2) : 0 ;
		$datalist[$ii]['tuikuan'] = $ReFound ? round($ReFound,2): 0; 
		$datalist[$ii]['shishou'] = $AdminChongzhi + $Chongzhi - $ReFound ? round($AdminChongzhi + $Chongzhi - $ReFound,2)  : 0 ;
		
	}
 
 


	$data = array(
		'title'=>$title,
		'TimeRange'=> date("Y年m月d日",$starttime)." 至 ".date("Y年m月d日",$endtime),
		'data' => $datalist
	);
	 $Excelname = "chongzhilog.xml";
	 CreateModalExcel($data,$Excelname);
	 exit();

}

if($operation == 'display'){
 	if (!(IsHasQx($tid_global,1002201,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	} 
    $list     = pdo_fetchall("SELECT * FROM " . tablename($this->table_chongzhi) . " WHERE weid = '{$weid}'  And schoolid = {$schoolid} ORDER BY ssort DESC,id ASC");
}elseif($operation == 'post'){
    
    if (!(IsHasQx($tid_global,1002202,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	} 
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $chongzhi = pdo_fetch("SELECT * FROM " . tablename($this->table_chongzhi) . " WHERE id = '{$id}'");
    }else{
        $chongzhi = array(
            'ssort' => 0,
        );
    }

    if(checksubmit('submit')){
        if(!empty($id)){
			if(!empty($_GPC['old'])){
        if(empty($_GPC['should_pay'])){
            $this->imessage('抱歉，请输入应付金额！', referer(), 'error');
        }
        if(empty($_GPC['addNum'])){
            $this->imessage('抱歉，请输入增加余额！', referer(), 'error');
        }

        $data = array(
            'weid'      => $weid,
            'schoolid'  => $_GPC['schoolid'],
            'cost'      => $_GPC['should_pay'],
            'chongzhi'  => $_GPC['addNum'],
            'ssort'     => intval($_GPC['ssort']),
            'createtime' => time()
        );
        pdo_update($this->table_chongzhi, $data, array('id' => $id));
        }

        if(!empty($_GPC['new'])){
				foreach($_GPC['new'] as $key => $value){
					$should_pay = $_GPC['should_pay_new'][$key];
					if(empty($should_pay)){
						$this->imessage('抱歉，请输入应付金额！', referer(), 'error');
					}
					$addNum = $_GPC['addNum_new'][$key];
					if(empty($addNum)){
						$this->imessage('抱歉，请输入增加余额！', referer(), 'error');
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'cost'    => $should_pay,
       				 	'chongzhi'    => $addNum,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'createtime' => time()
					);	
					pdo_insert($this->table_chongzhi, $data);
									
				}
			}
    }else{
	      	if(!empty($_GPC['new'])){
				foreach($_GPC['new'] as $key => $value){
					$should_pay = $_GPC['should_pay_new'][$key];
					if(empty($should_pay)){
						$this->imessage('抱歉，请输入应付金额！', referer(), 'error');
					}
					$addNum = $_GPC['addNum_new'][$key];
					if(empty($addNum)){
						$this->imessage('抱歉，请输入增加余额！', referer(), 'error');
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'cost'    => $should_pay,
       				 	'chongzhi'    => $addNum,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'createtime' => time()
					);	
					pdo_insert($this->table_chongzhi, $data);

				}
			}			 
		}
        $this->imessage('更新套餐成功！', $this->createWebUrl('chongzhi', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'chongzhilog'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    if(!empty($_GPC['stuname'])){
		$student = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ORDER BY id DESC LIMIT 1", array(':schoolid' => $schoolid,':s_name' => $_GPC['stuname']));
		$condition .= " AND sid = '{$student['id']}'";		
    }
	if(!empty($_GPC['createtime'])) {
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime   = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
	} else {
		$starttime = strtotime('-600 day');
		$endtime = TIMESTAMP;
	}


	$actype = $_GPC['add_cut_type'];
	if($actype > 0 ){
		$condition .= " AND cost > 0 ";
	}elseif($actype < 0 ){
		$condition .= " AND cost < 0 ";
	}

	$list = pdo_fetchall(" SELECT * FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and yue_type = 2 and cost_type = 3 $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$AllCountPage = 0;
	$AllCount = pdo_fetchcolumn(" SELECT sum(cost) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and yue_type = 2 and cost_type = 3 $condition ORDER BY createtime DESC ");
	foreach($list as $key => $row){
		$AllCountPage +=$row["cost"];
		$student = pdo_fetch("SELECT id,icon,s_name,bj_id,xq_id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['sid']));
		if(!empty($student['icon'])){
			$list[$key]['sicon'] = $student['icon'];
		}else{
			$list[$key]['sicon'] = $logo['spic']; 
		}
		if($row['cost_type'] == 3){
			if($row['cztid'] == -1){
				$list[$key]['cztname'] = "管理员";
			}else{
				$cztea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$row['cztid']}' ");
				$list[$key]['cztname'] = $cztea['tname'];
			}
		}
		$bj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['bj_id']));
		$nj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $student['xq_id']));
		$list[$key]['bj_name'] = $bj_name['sname'];
		$list[$key]['nj_name'] = $nj_name['sname'];	
		$list[$key]['s_name'] = $student['s_name'];
	}
	$total = pdo_fetchcolumn(" SELECT count(*) FROM ".GetTableName('yuecostlog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and yue_type = 2 and cost_type = 3  $condition ORDER BY createtime DESC ");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'downloadexcel'){
	$stulist = pdo_fetchall(" SELECT * FROM ".GetTableName('students')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' ORDER BY id DESC ");
	$ii = 0;
	$OutArr = [];
	foreach($stulist as $key_o => $row_o){
		$bj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $row_o['bj_id']));
		$nj_name = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sid = :sid ", array(':schoolid' => $schoolid,':sid' => $row_o['xq_id']));
		$OutArr[$ii]['sid']    = $row_o['id'];
		$OutArr[$ii]['sName']  = $row_o['s_name'];
		$OutArr[$ii]['NjName'] = $nj_name['sname'];
		$OutArr[$ii]['BjName'] = $bj_name['sname'];
		$OutArr[$ii]['yue'] = $row_o['chongzhi'];
		$OutArr[$ii]['addyue'] = 0;
		$OutArr[$ii]['cutyue'] = 0;
		$ii++;		
	}
	$ExcelTitle = "学生余额表";
	$this->exportexcel($OutArr, array('学生id','学生姓名' ,'年级','班级','当前余额','增加余额' ,'减少余额'), $ExcelTitle);
	exit(); 
}


elseif($operation == 'downloadchongzhilog'){

}


elseif($operation == 'card_bot'){
	$card = $_GPC['stuCard'];
	$is_find = false;
	$card = pdo_fetch("SELECT * FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and  idcard = '{$card}' ");
	$students = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$card['sid']}' ");
	if(!empty($students)){
		$is_find = true;
		$students['njname'] = pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and type='semester' and sid = '{$students['xq_id']}'")['sname'];  
		$students['bjname'] = pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and type='theclass' and sid = '{$students['bj_id']}'")['sname'];  
	}

	include $this->template ( 'web/cardchongzhi_bot' );	
	die();
}elseif($operation == 'changechongzhi'){
	$changechongzhi = $_GPC['changechongzhi'];
	$stuid = $_GPC['stuid'];
	$op    = $_GPC['dealop'];
	$student = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$stuid}' ");
	$old_chongzhi = $student['chongzhi'];

	if($op == 'cut'){ //减少余额
		$new_chongzhi = $old_chongzhi - $changechongzhi;
		$cost = 0 - $changechongzhi;
	}
	if($op == 'add'){ //增加余额
		$new_chongzhi = $old_chongzhi + $changechongzhi;
		$cost = $changechongzhi;

	}

	$yuelogdata = array(
		'schoolid' => $schoolid,
		'weid' => $weid,
		'sid' => $stuid,
		'yue_type' => 2,
		'cost' => $cost,
		'cost_type' => 3,
		'createtime' => time(),
		'cztid' => intval($tid_global) >0 ? $tid_global : -1 
	);
	pdo_insert(GetTableName('yuecostlog',false),$yuelogdata);
	pdo_update(GetTableName('students',false),array('chongzhi'=>$new_chongzhi),array('id'=>$stuid));
	$result['result'] = true;
	$result['msg'] = "操作学生余额成功！";
	die(json_encode($result));
}
elseif($operation == 'delete'){
    $id  = intval($_GPC['id']);
    $week = pdo_fetch("SELECT id FROM " . tablename($this->table_chongzhi) . " WHERE id = '{$id}'");
    if(empty($week)){
        $this->imessage('抱歉，套餐不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_chongzhi, array('id' => $id), 'OR');
    $this->imessage('套餐删除成功！', referer(), 'success');
}

elseif($operation == 'deletechongzhilog'){
    $id  = intval($_GPC['id']);
    $week = pdo_fetch("SELECT id FROM " . GetTableName('yuecostlog') . " WHERE id = '{$id}'");
    if(empty($week)){
        $this->imessage('抱歉，充值记录不存在或是已经被删除！', referer(), 'error');
    }
	pdo_delete(GetTableName('yuecostlog',false), array('id' => $id), 'OR');
    $this->imessage('充值记录删除成功！', referer(), 'success');
}
elseif($operation == "deleteallchongzhilog"){
 
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('yuecostlog') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('yuecostlog',false), array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));



}


include $this->template('web/chongzhi');
?>