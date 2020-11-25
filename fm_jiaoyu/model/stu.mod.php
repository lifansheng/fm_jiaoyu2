<?php 
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');


//获取本校所有班级信息 
//公立is_over1 未毕业 is_over2 毕业 is_over0 所有班级
//培训is_over1 未结束课程 is_over2已结束课程
function getallclassinfo($schoolid,$is_over,$schooltype){
	if($schooltype){//培训
		$condition = '';
		$nowtime = time();
		if($is_over == 1){
			//$condition .= " AND end >= $nowtime ";
		}		
		$datas = pdo_fetchall("SELECT id as sid,name as sname,end FROM " . tablename('wx_school_tcourse') . " WHERE schoolid='{$schoolid}' $condition  ORDER BY end DESC, ssort DESC");
		foreach($datas as $key =>$row){
			if($row['end'] < $nowtime){
				$datas[$key]['info'] = '--(已结课)';
				$datas[$key]['is_over'] = 2; 
			}else{
				$datas[$key]['is_over'] = 1; 
			}
			$total =  pdo_fetchall("SELECT distinct sid as id  FROM " . tablename('wx_school_order') . " WHERE schoolid='{$schoolid}' And kcid = '{$row['sid']}' And type = 1 And status = 2 And sid != 0  ");
			$datas[$key]['sname'] = $row['sname'].'('.count($total).'人)';			
		} 
	}else{//公立
		$condition = '';
		if($is_over){
			//$condition .= " AND is_over = '{$is_over}' ";
		}
		$datas = pdo_fetchall("SELECT sid,sname,is_over FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' $condition ORDER BY parentid ASC ");
		foreach($datas as $key => $row){
			$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
			$datas[$key]['sname'] = $row['sname'].'('.$total.'人)';
			if($row['is_over'] == 2){
				$datas[$key]['info'] = '--(已毕业)';
				if($is_over == 1){
					unset($datas[$key]);
				}
			}			
		}
	}
	$datas = array_sorts($datas,'is_over','asc');
	return $datas;
}

//获取所有班级(课程)数组的学生信息
function getallclassstuinfo($schoolid,$is_over,$schooltype){
	$nowtime = time();
	if($schooltype){//培训
		$condition = '';
		if($is_over == 1){
			//$condition .= " AND end >= $nowtime ";
		}
		
		$school = pdo_fetch("SELECT spic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
		$bjlist = pdo_fetchall("SELECT id as sid,name as sname,end FROM " . tablename('wx_school_tcourse') . " WHERE schoolid='{$schoolid}' $condition  ORDER BY end DESC, ssort DESC");
		foreach($bjlist as $key => $item){
			if($item['end'] < $nowtime){
				$bjlist[$key]['info'] = '--(已结课)';
				$bjlist[$key]['is_over'] = 2; 
			}else{
				$bjlist[$key]['is_over'] = 1; 
			}
			$stulist =  pdo_fetchall("SELECT distinct sid as id  FROM " . tablename('wx_school_order') . " WHERE schoolid='{$schoolid}'  and kcid = '{$item['sid']}' and type = 1 and status = 2 and sid != 0  ");
			$bjlist[$key]['sname'] = $item['sname'].'('.count($stulist).'人)';
			foreach($stulist as $keys => $values){
				$stuinfo =  pdo_fetch("SELECT s_name,icon FROM ".tablename('wx_school_students')." WHERE id = '{$values['id']}' ");
				if($stuinfo['icon']){
					$stulist[$keys]['icon'] = tomedia($stuinfo['icon']);
				}else{
					$stulist[$keys]['icon'] = tomedia($school['spic']);
				}
				$stulist[$keys]['s_name'] = $stuinfo['s_name'];
			}
			$bjlist[$key]['allstu'] = $stulist;
		}	
	}else{//公立
		$condition = '';
		if($is_over){
			//$condition .= " AND is_over = '{$is_over}' ";
		}
		$school = pdo_fetch("SELECT spic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
		$bjlist = pdo_fetchall("SELECT sid,sname,is_over FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' $condition ORDER BY parentid ASC ");
		foreach($bjlist as $key => $item){
			$allstu = pdo_fetchall("SELECT id,s_name,icon FROM ".tablename('wx_school_students')." WHERE schoolid = '{$schoolid}' And bj_id ='{$item['sid']}' ORDER BY id ASC ");
			$bjlist[$key]['sname'] = $item['sname'].'('.count($allstu).'人)';
			foreach($allstu as $k =>$i){
				if($i['icon']){
					$allstu[$k]['icon'] = tomedia($i['icon']);
				}else{
					$allstu[$k]['icon'] = tomedia($school['spic']);
				}
			}
			if($item['is_over'] == 2){
				$bjlist[$key]['info'] = '--(已毕业)';
				if($is_over == 1){
					unset($bjlist[$key]);
				}
			}			
			$bjlist[$key]['allstu'] = $allstu;			
		}
	}
	$bjlist = array_sorts($bjlist,'is_over','asc');
	return $bjlist;
}

//根据班级（课程）ID获取学生id //暂时未用
function StuInfoByclassArr($send_class,$schoolid,$schooltype){
	if($schooltype){//培训
		if(is_array($send_class)){
			$stuarr = array();
			foreach($send_class as $row){
				$stulist =  pdo_fetchall("SELECT distinct sid as id  FROM " . tablename('wx_school_order') . " WHERE schoolid='{$schoolid}'  and kcid = '{$row}' and type = 1 and status = 2 and sid != 0  ");
				if($stulist){
					foreach($stulist as $r){
						$stuarr[] = intval($r['id']);
					}
				}	
			}	
		}
	}else{//公立
		if(is_array($send_class)){
			$stuarr = array();
			foreach($send_class as $row){
				$allstu = pdo_fetchall("SELECT id FROM ".tablename('wx_school_students')." WHERE  bj_id = '{$row}' And schoolid = '{$schoolid}' ORDER BY id ASC ");
				if($allstu){
					foreach($allstu as $r){
						$stuarr[] = intval($r['id']);
					}
				}
			}
		}
	}
	return $stuarr;	 
}

//根据班级（课程）ID获取学生id //针对培训机构新增班级使用
function PxStuInfoByclassArr($send_class,$schoolid){
	if(is_array($send_class)){
		$stuarr = array();
		foreach($send_class as $key=>$row){
			$bj_id = trim($row,',');
			$stulist = pdo_fetchall("SELECT sid FROM ". GetTableName('coursebuy') ." where schoolid = '{$schoolid}' and FIND_IN_SET(bjid,'{$bj_id}') AND kcid = '{$key}' ");	
			if($stulist){
				foreach($stulist as $r){
					$stuarr[] = intval($r['sid']);
				}
			}	
		}	
	}
	return $stuarr;	 
}

//根据班级（课程）获取学生id
function StuInfoByclassId($bj_id,$schoolid,$schooltype){
	if($schooltype){//培训
		$stuarr = array();
		$stulist =  pdo_fetchall("SELECT distinct sid as id  FROM " . tablename('wx_school_order') . " WHERE schoolid='{$schoolid}'  and kcid = '{$bj_id}' and type = 1 and status = 2 and sid != 0  ");
		if($stulist){
			foreach($stulist as $r){
				$stuarr[] = intval($r['id']);
			}
		}	
	}else{//公立
		$stuarr = array();
		$allstu = pdo_fetchall("SELECT id FROM ".tablename('wx_school_students')." WHERE  bj_id = '{$bj_id}' And schoolid = '{$schoolid}' ORDER BY id ASC ");
		if($allstu){
			foreach($allstu as $r){
				$stuarr[] = intval($r['id']);
			}
		}		
	}
	return $stuarr;	 
}

function getallclassstuinfo_nobj($schoolid,$schooltype){ //查询未分配班级或课程的学生
	if($schooltype){//培训
		$school = pdo_fetch("SELECT spic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
		$allstu = pdo_fetchall("SELECT stu.id,stu.s_name,stu.icon FROM ".tablename('wx_school_students')." as stu , ".tablename('wx_school_order')." as orders  WHERE orders.schoolid = '{$schoolid}' And orders.kcid != 0 and orders.type = 1 and orders.status = 2 and orders.sid != 0 and stu.id != orders.sid ORDER BY stu.id ASC ");
		foreach($allstu as $k =>$i){
			if($i['icon']){
				$allstu[$k]['icon'] = tomedia($i['icon']);
			}else{
				$allstu[$k]['icon'] = tomedia($school['spic']);
			}
		}	
	}else{//公立	
		$school = pdo_fetch("SELECT spic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
		$allstu = pdo_fetchall("SELECT id,s_name,icon FROM ".tablename('wx_school_students')." WHERE schoolid = '{$schoolid}' And bj_id = 0 ORDER BY id ASC ");
		foreach($allstu as $k =>$i){
			if($i['icon']){
				$allstu[$k]['icon'] = tomedia($i['icon']);
			}else{
				$allstu[$k]['icon'] = tomedia($school['spic']);
			}
		}
	}
	return $allstu;		
}

function GetClassInfoByArr($send_class,$schooltype,$schoolid){//根据已知班级或课程数组查询班级课程名称
	if(is_array($send_class)){
		$bjlist = array();
		foreach($send_class as $row){
			if($row == 0 || $row != ""){
				if($row == 0){
					$bjlist[]['name'] = '未分班';
				}else{
					if($schooltype){//培训
						$kcinfo = pdo_fetch("SELECT name FROM " . tablename('wx_school_tcourse') . " WHERE id = '{$row}' And schoolid = '{$schoolid}' ");
						if($kcinfo){
							$bjlist[] = $kcinfo;
						}
					}else{//公立
						$bjinfo = pdo_fetch("SELECT sname as name FROM ".tablename('wx_school_classify')." WHERE  sid = '{$row}' And schoolid='{$schoolid}' ");
						if($bjinfo){
							$bjlist[] = $bjinfo;
						}
					}	
				}	
			}
		}	
	}	
	return $bjlist;
}

function GetStuInfoByArr($stu_arr,$schooltype,$schoolid){//根据已知学生数组查询学生姓名和头像
	if(is_array($stu_arr)){
		$allstu = array();
		$school = pdo_fetch("SELECT spic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
		foreach($stu_arr as $row){
			if($row == 0 || $row != ""){
				$stu = pdo_fetch("SELECT s_name as name,icon FROM " . tablename('wx_school_students') . " WHERE id = '{$row}' And schoolid = '{$schoolid}' ");
				if($stu){
					if($stu['icon']){
						$stu['icon'] = tomedia($stu['icon']);
					}else{
						$stu['icon'] = tomedia($school['spic']);
					}					
					$allstu[] = $stu;
				}
			}
		}	
	}	
	return $allstu;
}

function get_myallclass_inschool($weid,$openid,$schoolid){ //查询绑定学生所有班级信息（本学校）
	$user = pdo_fetchall("SELECT id,sid,pard,mobile FROM " . tablename('wx_school_user') . " where :weid = weid And :openid = openid And :schoolid = schoolid And :tid = tid", array(
			':weid' => $weid,
			':openid' => $openid,
			':schoolid' => $schoolid,
			':tid' => 0
	));
	if($user){
		foreach($user as $key => $row){
			$student = pdo_fetch("SELECT id,s_name,schoolid,bj_id,sex,icon,mobile FROM " . tablename('wx_school_students') . " where id=:id ", array(':id' => $row['sid']));
			$bajinam = pdo_fetch("SELECT sname FROM " . tablename('wx_school_classify') . " where sid = :sid ", array(':sid' => $student['bj_id']));
			$school = pdo_fetch("SELECT spic FROM " . tablename('wx_school_index') . " where :id = id", array(':id' => $student['schoolid']));
			$user[$key]['s_name'] = $student['s_name'];
			$user[$key]['bjname'] = $bajinam['sname'];
			$user[$key]['sid'] = $student['id'];
			$user[$key]['mobile'] = $student['mobile'] ? $student['mobile'] : $row['mobile'];
			$user[$key]['schoolid'] = $student['schoolid'];
			$user[$key]['sex'] = $student['sex'];
			$user[$key]['pard'] = get_guanxi($row['pard']);
			$user[$key]['icon'] = empty($student['icon'])?$school['spic']:$student['icon'];
		}
		return $user;
	}else{
		return false;
	}
}

function CheckStuNameForFans($sname,$schoolid,$openid){//判断分数是否添加了同名学生
	$result = false;
	$alluser = pdo_fetchall("SELECT sid FROM " . GetTableName('user') . " where :openid = openid And :schoolid = schoolid And :tid = tid", array(':openid' => $openid,':schoolid' => $schoolid,':tid' => 0));
	if(!empty($alluser)){
		foreach($alluser as $row){
			$stuinfo = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " where id=:id ", array(':id' => $row['sid']));
			if($stuinfo['s_name'] == $sname){
				$result = true;
				break;
			}
		}
	}
	return $result;
}

function GetClassNameByBjId($bj_id,$schooltype){//根据班级ID查询班级名称
	if($bj_id){
		//if($schooltype){//培训
			//$kcinfo = pdo_fetch("SELECT name FROM " . tablename('wx_school_tcourse') . " WHERE id = '{$row}' And schoolid = '{$schoolid}' ");
		//}else{//公立
			$bjinfo = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE  sid = '{$bj_id}' ");
			$classname = $bjinfo['sname'];
		//}	
	}else{
		$classname = '未分班';
	}
	return $classname;
}

//根据班级ID获取学生信息
function GetBjStuList($bj_id,$schoolid){
	$allstu = pdo_fetchall("SELECT id,s_name,icon,bj_id FROM ".tablename('wx_school_students')." WHERE  bj_id = '{$bj_id}' And schoolid = '{$schoolid}' ORDER BY id DESC ");
	return $allstu;	 
}
//获取我的授课老师及班主任(公立学校)
function getMyTea($schoolid,$sid){
	$tid_arr = pdo_fetchall("SELECT DISTINCT c.tid FROM " . GetTableName('user_class') . " as c RIGHT JOIN " . GetTableName('students') . " as s ON s.bj_id = c.bj_id where s.schoolid ='{$schoolid}' AND s.id = '{$sid}' ");
	$tealist = [];
	if(!empty($tid_arr)){
		foreach($tid_arr as $key=>$value){
			$tealist[$key] = pdo_fetch("SELECT tname,id FROM " . GetTableName('teachers') . " WHERE id = :id", array(':id' => $value['tid']));
			if(!$tealist[$key]){
				unset($tealist[$key]);
			}
		}
	}
	$tealist = array_values($tealist);
	return $tealist;
}

function getOverClass($schoolid,$schooltype){
	if($schooltype){
		$condition = '';
		$nowtime = time();
		$condition .= " AND end <= '{$nowtime}' ";
		$datas = pdo_fetchall("SELECT id as sid,name as sname,end FROM " . tablename('wx_school_tcourse') . " WHERE schoolid='{$schoolid}' $condition  ORDER BY end DESC, ssort DESC");
		foreach($datas as $key =>$row){
			if($row['end'] < $nowtime){
				$datas[$key]['info'] = '--(已结课)';
				$datas[$key]['is_over'] = 2; 
			}else{
				$datas[$key]['is_over'] = 1; 
			}
			$total =  pdo_fetchall("SELECT distinct sid as id  FROM " . tablename('wx_school_order') . " WHERE schoolid='{$schoolid}' And kcid = '{$row['sid']}' And type = 1 And status = 2 And sid != 0  ");
			$datas[$key]['sname'] = $row['sname'].'('.count($total).'人)';			
		} 
	}else{
		$datas = pdo_fetchall("SELECT sid,sname,is_over FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' AND is_over = 2 ORDER BY parentid ASC ");
		foreach($datas as $key => $row){
			$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
			$datas[$key]['sname'] = $row['sname'].'('.$total.'人)';
			if($row['is_over'] == 2){
				$datas[$key]['info'] = '--(已毕业)';
				if($is_over == 1){
					unset($datas[$key]);
				}
			}			
		}
	}
	$datas = array_sorts($datas,'is_over','asc');
	return $datas;
}
//获取学生所报名的所有课程(排除课时用完的课程)
function getHasSyksStu($schoolid,$sid){
	$stuKcList = pdo_fetchall("SELECT * FROM " . GetTableName('coursebuy') . " WHERE schoolid = :schoolid And sid = :sid and is_change != :is_change", array( ':schoolid' => $schoolid, ':sid' => $sid, 'is_change' => 1 ));
	return $stuKcList;
}
//获取培训下当前课程下的学生
function getPxBjstu($schoolid,$kcid,$bjid){
	$condition = " AND FIND_IN_SET(c.bjid,'{$bjid}') ";
	if($bjid){
		$condition = " AND FIND_IN_SET(c.bjid,'{$bjid}') ";
	}
	$stulist = pdo_fetchall("SELECT s.id,s.s_name,c.bjid,c.kcid FROM ".GetTableName('students')." as s RIGHT JOIN " . GetTableName('coursebuy') . " as c ON c.sid = s.id where c.schoolid = :schoolid AND c.kcid = :kcid AND c.is_change != 1 $condition ORDER BY s.id DESC ",array(':schoolid'=>$schoolid, ':kcid'=>$kcid));
	return $stulist;
}
/**
 * 学生转班，改变视频购买记录
 *
 * @return void
 */
function ChangeVideoWithBj($schoolid,$sid,$targetId){
	// 原视频id
	$oldvideo = pdo_fetchAll("SELECT a.id,s.bj_id FROM ".GetTableName('students')." as s LEFT JOIN " . GetTableName('allcamera') . " as a ON FIND_IN_SET(s.bj_id,a.bj_id) WHERE s.id = '{$sid}' AND s.schoolid = '{$schoolid}' ");
	if(in_array($targetId,array_column($oldvideo,'bj_id')) ){
		return;
	}
	$videostr = arrayToString(array_column($oldvideo,'id'));
	//新班级视频ID
	$videoid = pdo_fetch("SELECT id FROM ".GetTableName('allcamera')." WHERE schoolid='{$schoolid}' AND FIND_IN_SET('{$targetId}',bj_id) AND is_pay = 1")['id'];
	//之前已经购买的视频
	$hasVideo = pdo_fetchAll("SELECT id FROM ".GetTableName('order')." WHERE  type ='7' And schoolid='{$schoolid}' AND FIND_IN_SET(vodid,'{$videostr}') AND sid = '{$sid}' ORDER BY id DESC");
	foreach ($hasVideo as $key => $value) {
		$newvideoid = $videoid;
		//$newvideoid = $newvideo[$key]['id'];
		if(!empty($newvideoid)){
			pdo_update(GetTableName('order',false),array('vodid'=>$newvideoid),array('id'=>$value['id']));
		}else{
			break;
		}
		
	}
}

?>