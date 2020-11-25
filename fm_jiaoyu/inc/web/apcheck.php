<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'apcheck';
$this1             = 'no7';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
load()->func('tpl');
load()->func('file');
load()->func('communication');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
 if (!(IsHasQx($tid_global,1003401,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	} 
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    if($_GPC['type'] == 1){
        $type      = intval($_GPC['type']);
        $condition .= " AND ap_type = '{$type}' ";
    }
    if($_GPC['type'] == 2){
        $type      = intval($_GPC['type']);
        $condition .= " AND ap_type = '{$type}' ";
    }

 
    if(!empty($_GPC['bj_id'])){
        $condition .= " AND bj_id = '{$_GPC['bj_id']}' ";
    }
	
	if(!empty($_GPC['nj_id']) && empty($_GPC['bj_id']) ){
		 $bjidlist = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  AND type = 'theclass' and parentid = '{$_GPC['nj_id']}'  ORDER BY CONVERT(sname USING gbk) ASC");
		 $bjid_str = '';
		 foreach($bjidlist as $key_k => $value_k){
			 $bjid_str .= $value_k['sid'].","; 
		 }
		 $bjid_str = trim($bjid_str,",");
        $condition .= " AND FIND_IN_SET(bj_id,'{$bjid_str}') ";
    }
	
	
	if(!empty($_GPC['ap_id'])){
        $condition .= " AND apid = '{$_GPC['ap_id']}' ";
    }
	
	if(!empty($_GPC['room_id'])){
        $condition .= " AND roomid = '{$_GPC['room_id']}' ";
    }
	
	
	
	
	if(!empty($_GPC['createtime'])){
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime   = strtotime($_GPC['createtime']['end']) + 86399;
		$condition .= " AND createtime <= '{$endtime}'  AND createtime >= '{$starttime}'";
    }else{
        $starttime = strtotime('-30 day');
        $endtime   = TIMESTAMP;
    }

    if(!empty($_GPC['stu_name'])){
        $students  = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And s_name = :s_name ", array(':schoolid' => $schoolid, ':s_name' => $_GPC['stu_name']));
        $condition .= " AND sid = '{$students['id']}'";
    }

	$allnj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  AND type = 'semester' ORDER BY CONVERT(sname USING gbk) ASC");
    $allbj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  AND type = 'theclass' ORDER BY CONVERT(sname USING gbk) ASC");
	$allAp =  pdo_fetchall("SELECT id,name FROM " . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC");
	$allRoom =  pdo_fetchall("SELECT id,name FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC");
	
	
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_checklog) . " WHERE weid = '{$weid}' and sc_ap = 1 AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$checkpic = pdo_fetchall("SELECT id FROM " . tablename($this->table_checklog) . " WHERE weid = '{$weid}' and sc_ap = 1 AND schoolid = '{$schoolid}' AND (pic LIKE '%wmpickq%' Or pic2 LIKE '%wmpickq%')");
	$checkpicsl = count($checkpic);
    foreach($list as $index => $row){
		$student   = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " WHERE id = '{$row['sid']}' ");
		if($student['bj_id'] == $row['bj_id']){
			$teacher   = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['tid']}' ");
			$qdtid     = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['qdtid']}' ");
			$idcard    = pdo_fetch("SELECT pname FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$row['cardid']}' And schoolid = '{$schoolid}' ");
			$mac       = pdo_fetch("SELECT name FROM " . tablename($this->table_checkmac) . " WHERE schoolid = '{$schoolid}' And id = '{$row['macid']}' ");
			$banji     = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$row['bj_id']}' ");
			
			$apatrment =  pdo_fetch("SELECT id,name FROM " . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$row['apid']}' ");
			$aproom =  pdo_fetch("SELECT id,name FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$row['roomid']}'");
			
			
			
			$list[$index]['s_name']  = $student['s_name'];
			$list[$index]['tname']   = $teacher['tname'];
			$list[$index]['qdtname'] = $qdtid['tname'];
			$list[$index]['mac']     = $mac['name'];
			$list[$index]['pname']   = $idcard['pname'];
			$list[$index]['bj_name'] = $banji['sname'];
			$list[$index]['ap_name'] = $apatrment['name'];
			$list[$index]['room_name'] = $aproom['name'];
			if (preg_match('/(http:\/\/)|(https:\/\/)/i', $row['pic'])) {
				if (preg_match('/wmpickq/i', $row['pic']) || preg_match('/kaoqin/i', $row['pic'])) {
					if (preg_match('/wmpickq/i', $row['pic'])) {
						$img = getImg($row['pic']);
						if(!empty($img)){
							$path = "images/fm_jiaoyu/check_pic/". date('Y/m/d/');
							if (!is_dir(IA_ROOT."/attachment/". $path)) {
								mkdirs(IA_ROOT."/attachment/". $path, "0777");
							}
							$picurl = $path.random(30).".jpg";
							file_write($picurl,$img);
								if (!empty($_W['setting']['remote']['type'])) { // 
									$remotestatus = file_remote_upload($picurl); //
									if (is_error($remotestatus)) {
										message('远程附件上传失败，请检查配置并重新上传');
									}
								}
						}
						pdo_update($this->table_checklog, array('pic' => $picurl), array('id' => $row['id']));
						$list[$index]['img1']     = $picurl;					
					}
					if (preg_match('/kaoqin/i', $row['pic'])) {
						$list[$index]['img1'] = $row['pic'];	
					}
				}else{
					$path = "images/fm_jiaoyu/check/". date('Y/m/d/');
					if (!is_dir(IA_ROOT."/attachment/". $path)) {
						mkdirs(IA_ROOT."/attachment/". $path, "0777");
					}
					$picurl = $path.random(30) .".jpg";
					$pic_data = getimg_form_oss($row['pic']);
					file_write($picurl,$pic_data);
					if (!empty($_W['setting']['remote']['type'])) {
						$remotestatus = file_remote_upload($picurl);
						if (is_error($remotestatus)) {
							message('远程附件上传失败，请检查配置并重新上传');
						}
					}
					pdo_update($this->table_checklog, array('pic' => $picurl), array('id' => $row['id']));
					$list[$index]['img1']     = $picurl;				
				}			
			}else{
				$list[$index]['img1']     = $row['pic'];
			}
			if (preg_match('/(http:\/\/)|(https:\/\/)/i', $row['pic2'])) {
				if (preg_match('/wmpickq/i', $row['pic2']) || preg_match('/kaoqin/i', $row['pic2'])) {
					if (preg_match('/wmpickq/i', $row['pic2'])) {
						$img = getImg($row['pic2']);
						if(!empty($img)){
							$path = "images/fm_jiaoyu/check_pic/". date('Y/m/d/');
							if (!is_dir(IA_ROOT."/attachment/". $path)) {
								mkdirs(IA_ROOT."/attachment/". $path, "0777");
							}
							$picurl2 = $path.random(30).".jpg";
							file_write($picurl2,$img);
								if (!empty($_W['setting']['remote']['type'])) {  
									$remotestatus = file_remote_upload($picurl2); 
									if (is_error($remotestatus)) {
										message('远程附件上传失败，请检查配置并重新上传');
									}
								}
						}
						pdo_update($this->table_checklog, array('pic2' => $picurl2), array('id' => $row['id']));
						$list[$index]['img2']     = $picurl2;					
					}
					if (preg_match('/kaoqin/i', $row['pic2'])) {
						$list[$index]['img2'] = $row['pic2'];
					}				
				}else{
					$path = "images/fm_jiaoyu/check/". date('Y/m/d/');
					if (!is_dir(IA_ROOT."/attachment/". $path)) {
						mkdirs(IA_ROOT."/attachment/". $path, "0777");
					}
					$picurl2 = $path.random(30) .".jpg";
					$pic_data = getimg_form_oss($row['pic2']);
					file_write($picurl2,$pic_data);
					if (!empty($_W['setting']['remote']['type'])) {
						$remotestatus = file_remote_upload($picurl2);
						if (is_error($remotestatus)) {
							message('远程附件上传失败，请检查配置并重新上传');
						}
					}
					pdo_update($this->table_checklog, array('pic2' => $picurl2), array('id' => $row['id']));
					$list[$index]['img2']     = $picurl2;				
				}			
			}else{
				$list[$index]['img2']     = $row['pic2'];
			}
		}else{
			unset($list[$index]);
		}
        
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*)  FROM ' . tablename($this->table_checklog) . " WHERE weid = '{$weid}' and sc_ap = 1 AND schoolid ={$schoolid} $condition");

    $pager = pagination($total, $pindex, $psize);

}elseif($operation == 'tongji'){
	
	$pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;

	$time = time();

	if(!empty($_GPC['checkpoint'])){
		$time = strtotime($_GPC['checkpoint']);
	}

	if(!empty($_GPC['createtime'])){
		$starttime = strtotime($_GPC['createtime']['start']);
		$endtime   = strtotime($_GPC['createtime']['end']) + 86399;
		
    }else{
        $starttime = strtotime('-30 day');
        $endtime   = TIMESTAMP;
    }
  
	if(!empty($_GPC['ap_id'])){
		$condition .= " AND apid = '{$_GPC['ap_id']}' ";
		$allRoom =  pdo_fetchall("SELECT id,name FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and apid = '{$_GPC['ap_id']}' ORDER BY CONVERT(name USING gbk) ASC");
    }else {
		$allRoom =  pdo_fetchall("SELECT id,name FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC");
	}
	
	if(!empty($_GPC['room_id'])){
        $condition .= " AND id = '{$_GPC['room_id']}' ";
    }

	

	$StuList= [];
	$apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
	$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  $condition  ORDER BY CONVERT(name USING gbk) ASC "); //寝室列表
	$roomstr = '';
	foreach($roomlist as $value_room){
		$roomstr .= $value_room['id'].",";
	}
	$roomstr = trim($roomstr,',');
	$list = pdo_fetchall(" SELECT * FROM ".GetTableName('roomcheck')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and FIND_IN_SET(roomid,'{$roomstr}') and date >= '{$starttime}' and date <='{$endtime}' LIMIT ".($pindex - 1) * $psize .", ".$psize);
	$ShowList = [];
	foreach($list as $key_l =>$value_l){
		$room = pdo_fetch('SELECT * FROM ' . GetTableName('aproom') . " WHERE  id = '{$value_l['roomid']}' ");
		$Apartment = pdo_fetch('SELECT * FROM ' . GetTableName('apartment') . " WHERE  id = '{$room['apid']}' ");
		$student =  pdo_fetch('SELECT * FROM ' . GetTableName('students') . " WHERE id = '{$value_l['sid']}' ");
		$bjName = pdo_fetch("SELECT sname,parentid FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");
		$NjName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$bjName['parentid']}' ");
		$list[$key_l]['stuName'] = $student['s_name'];
		$list[$key_l]['roomName'] = $room['name'];
		$list[$key_l]['apName'] = $Apartment['name'];
		$list[$key_l]['bjName'] = $bjName['sname'];
		$list[$key_l]['NjName'] = $NjName['sname'];
		$list[$key_l]['datestr'] = date("Y-m-d",$value_l['date']);
	}
	//var_dump($list);
	if($_GPC['OutExcel'] == 'OutExcel'){
		$Out_list = pdo_fetchall(" SELECT * FROM ".GetTableName('roomcheck')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and FIND_IN_SET(roomid,'{$roomstr}') and date >= '{$starttime}' and date <='{$endtime}'  ");
		$ExcelArr = [];
		$ii = 0;
		foreach($Out_list as $key_o =>$value_o){
			$room = pdo_fetch('SELECT * FROM ' . GetTableName('aproom') . " WHERE  id = '{$value_o['roomid']}' ");
			$Apartment = pdo_fetch('SELECT * FROM ' . GetTableName('apartment') . " WHERE  id = '{$room['apid']}' ");
			$student =  pdo_fetch('SELECT * FROM ' . GetTableName('students') . " WHERE id = '{$value_o['sid']}' ");
			$bjName = pdo_fetch("SELECT sname,parentid FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");
			$NjName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$bjName['parentid']}' ");
			$ExcelArr[$ii]['stuName'] = $student['s_name'];
			$ExcelArr[$ii]['roomName'] =$Apartment['name']."-".$room['name'];
			$ExcelArr[$ii]['apName'] = $Apartment['name'];
			$ExcelArr[$ii]['bjName'] = $bjName['sname'];
			$ExcelArr[$ii]['NjName'] = $NjName['sname'];
			$ExcelArr[$ii]['datestr'] = date("Y-m-d",$value_o['date']);
			$ExcelArr[$ii]['type'] = $value_o['type'] == 1 ? "中午" : "晚上";
			$ii++;
		}
		$ExcelTitle =date("Y年m月d日",$starttime)." - ". date("Y年m月d日",$endtime)."未按时归寝情况检查表";
		$this->exportexcel($ExcelArr, array('学生', '寝室','楼栋','班级','年级','日期','中午/晚上'), $ExcelTitle);
		exit();

	}
 
	//var_dump($Stulist_show);
	
	$total = pdo_fetchcolumn(" SELECT count(*) FROM ".GetTableName('roomcheck')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and FIND_IN_SET(roomid,'{$roomstr}') and date >= '{$starttime}' and date <='{$endtime}'  ");
	$pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id       = intval($_GPC['id']);
    $checklog = pdo_fetch("SELECT id  FROM " . tablename($this->table_checklog) . " WHERE id = '$id' ");
    if(empty($checklog)){
        $this->imessage('抱歉，不存在或是已经被删除！', $this->createWebUrl('checklog', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_checklog, array('id' => $id));
    $this->imessage('删除成功！', $this->createWebUrl('checklog', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_checklog) . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }else{
				pdo_delete($this->table_checklog, array('id' => $id));
				$rowcount++;
			}
        }
    }
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
	$data ['result'] = true;
	$data ['msg'] = $message;
	die (json_encode($data));
}elseif ($operation == 'getroomlist')  {
		if (! $_GPC ['schoolid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
	    }else{
			$data = array();
			$roomlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_aproom) . " where schoolid = '{$_GPC['schoolid']}' And apid = '{$_GPC['gradeId']}'  ORDER BY CONVERT(name USING gbk) ASC");
   			$data ['roomlist'] = $roomlist;
			$data ['result'] = true;
			$data ['msg'] = '成功获取！';
         die ( json_encode ( $data ) );
		}
}elseif($operation == 'checksturoomdaily'){
	$roomlist = pdo_fetchall('SELECT * FROM ' . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  ORDER BY CONVERT(name USING gbk) ASC "); //寝室列表
	
	foreach($roomlist as $key_r =>$value_r){
		$students =  pdo_fetchall('SELECT id,s_name,icon,xq_id,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and roomid = '{$value_r['id']}' "); //寝室下的所有学生
	
		foreach($students as $key_s =>$value_s){
		 
				$date = date("Y-m-d",time());
				$noon_start     = strtotime($date.' '.$value_r['noon_start']);
				$noon_deadline  = strtotime($date.' '.$value_r['noon_deadline']);
				$night_start    = strtotime($date.' '.$value_r['night_start']);
				$night_deadline = strtotime($date.' '.$value_r['night_deadline']);
				$condition_noon = " createtime > '{$noon_start}' and createtime < '{$noon_deadline}' and sc_ap = 1  ORDER BY createtime DESC LIMIT 0 ,1 "; //今天午间归寝时间内的记录 取一条的话就是最近的一次记录
				$condition_night = " createtime > '{$night_start}' and createtime < '{$night_deadline}' and sc_ap = 1 ORDER BY createtime DESC LIMIT 0 ,1 "; //今天晚间归寝时间内的记录 取一条的话就是最近的一次记录
				$check_noon  = pdo_fetch(" SELECT * FROM ".GetTableName('checklog')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and $condition_noon ");
				$check_night = pdo_fetch(" SELECT * FROM ".GetTableName('checklog')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and $condition_night ");
				if(!empty($check_noon) || $check_noon['ap_type'] != 1 ){ //如果取不到中午的寝室考勤记录，或者中午的考勤记录 不是回寝室，就说明学生没有按时归寝
					$insert_data = array(
						'sid' =>$value_s['id'],
						'roomid' => $value_r['id'],
						'date'=>strtotime($date),
						'weid' => $weid,
						'schoolid' => $schoolid,
						'type' => '1'
					);
					$check = pdo_fetch("SELECT * FROM ".GetTableName('roomcheck')." WHERE sid = '{$value_s['id']}' and roomid = '{$value_r['id']}' and date = '{$insert_data['date']}' and type = '1'  ");
					if(empty($check)){
						pdo_insert(GetTableName('roomcheck',false),$insert_data);
					}
					// $StuList[] = $insert_data;
				};
				if(!empty($check_night) || $check_night['ap_type'] != 1 ){ //晚上同理。
					$insert_data = array(
						'sid' =>$value_s['id'],
						'roomid' => $value_r['id'],
						'date'=>strtotime($date),
						'weid' => $weid,
						'schoolid' => $schoolid,
						'type' => '2'
					);
					$check = pdo_fetch("SELECT * FROM ".GetTableName('roomcheck')." WHERE sid = '{$value_s['id']}' and roomid = '{$value_r['id']}' and date = '{$insert_data['date']}' and type = '2'  ");
					if(empty($check)){
						pdo_insert(GetTableName('roomcheck',false),$insert_data);
					}
					// $StuList[] = $insert_data;
				};
		}
		// var_dump($StuList);
		
	}
	die();
}else{
    $this->imessage('请求方式不存在');
}
include $this->template('web/apcheck');
?>