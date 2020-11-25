<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

/**
 * 获取所有教师分组
 *
 * @param [type] $schoolid
 * @param [type] $is_over
 * @param [type] $schooltype
 *
 * @return void
 */
function getalljsfzinfo($schoolid,$is_over,$schooltype){
	$condition = '';
	if($is_over){
		$condition .= " AND is_over = '{$is_over}' ";
	}
	// $datas = pdo_fetchall("SELECT sid,sname FROM ".tablename('wx_school_classify')." WHERE  type ='jsfz' And schoolid='{$schoolid}' $condition ORDER BY ssort DESC ");
	$datas = pdo_fetchall("SELECT sid,sname FROM ".tablename('wx_school_classify')." WHERE  type ='jsfz' And schoolid='{$schoolid}' AND is_show = 1 $condition ORDER BY ssort DESC ");
	foreach($datas as $key => $row){
		$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_teachers')." WHERE fz_id = '{$row['sid']}' ");
		$datas[$key]['sname'] = $row['sname'].'('.$total.'人)';
	}
	return $datas;
}

/**
 * 获取所有教师分组下的教师信息
 *
 * @param [type] $schoolid
 * @param [type] $is_over
 * @param [type] $schooltype
 *
 * @return void
 */
function getalljsfzallteainfo($schoolid,$is_over,$schooltype){
	$condition = '';
	if($is_over){
		$condition .= " AND is_over = '{$is_over}' ";
	}
	$school = pdo_fetch("SELECT tpic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
	$fzlist = pdo_fetchall("SELECT sid,sname FROM ".tablename('wx_school_classify')." WHERE  type ='jsfz' And schoolid='{$schoolid}' $condition ORDER BY ssort DESC ");
	foreach($fzlist as $key => $item){
		$alltea = pdo_fetchall("SELECT id,tname,thumb FROM ".tablename('wx_school_teachers')." WHERE schoolid = '{$schoolid}' And fz_id ='{$item['sid']}' ORDER BY id ASC ");
		$fzlist[$key]['sname'] = $item['sname'].'('.count($alltea).'人)';
		foreach($alltea as $k =>$i){
			if($i['thumb']){
				$alltea[$k]['icon'] = tomedia($i['thumb']);
			}else{
				$alltea[$k]['icon'] = tomedia($school['tpic']);
			}
		}
		$fzlist[$key]['alltea'] = $alltea;
	}
	return $fzlist;
}

 
function TeaInfoByclassArr($staff_jsfz,$schoolid){
	if(is_array($staff_jsfz)){
		$teaarr = array();
		foreach($staff_jsfz as $row){
			$alltea = pdo_fetchall("SELECT id FROM ".tablename('wx_school_teachers')." WHERE  fz_id = '{$row}' And schoolid = '{$schoolid}' ORDER BY id ASC ");
			if($alltea){
				foreach($alltea as $r){
					$teaarr[] = intval($r['id']);
				}
			}
		}
	}
	return $teaarr;		
}


function TeaInfoByclassArr_BothWay($staff_jsfz,$schoolid){
	if(is_array($staff_jsfz)){
		$teaarr = array();
		foreach($staff_jsfz as $row){
			$alltea = pdo_fetchall("SELECT id FROM ".tablename('wx_school_teachers')." WHERE  fz_id = '{$row}' And schoolid = '{$schoolid}' ORDER BY id ASC ");
			$alltea_byfz_temp = pdo_fetch("SELECT tidarr FROM ".tablename('wx_school_classify')." WHERE  sid = '{$row}' And schoolid = '{$schoolid}' ");
			$alltea_byfz = explode(",",$alltea_byfz_temp['tidarr']);
			if($alltea){
				foreach($alltea as $r){
					if(!in_array($r['id'],$teaarr)){
						$teaarr[] = intval($r['id']);
					}
					
				}
			}
			if($alltea_byfz){
				foreach($alltea_byfz as $r_f){
					if(!in_array($r_f,$teaarr)){
						$teaarr[] = intval($r_f);
					}
					
				}
			}
		}
		
	}
	return $teaarr;		
}

function getalljsfzallteainfo_nofz($schoolid,$schooltype){ //查询未分配分组教师	
	$school = pdo_fetch("SELECT tpic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
	$alltea = pdo_fetchall("SELECT id,tname,thumb FROM ".tablename('wx_school_teachers')." WHERE schoolid = '{$schoolid}' And fz_id = 0 ORDER BY id ASC ");
	foreach($alltea as $k =>$i){
		if($i['thumb']){
			$alltea[$k]['icon'] = tomedia($i['thumb']);
		}else{
			$alltea[$k]['icon'] = tomedia($school['tpic']);
		}
	}
	return $alltea;		
}

function GetFzInfoByArr($fz_arr,$schooltype,$schoolid){//根据已知分组ID数组查询分组名称
	if(is_array($fz_arr)){
		$fzlist = array();
		foreach($fz_arr as $row){
			if($row == 0){
				$fzlist[]['name'] = '未分组';
			}else{
				$fzinfo = pdo_fetch("SELECT sname as name FROM " . tablename('wx_school_classify') . " WHERE sid = '{$row}' And schoolid = '{$schoolid}' ");
				if($fzinfo){
					$fzlist[] = $fzinfo;
				}
			}
		}	
	}	
	return $fzlist;
}

function GetTeaInfoByArr($tea_arr,$schooltype,$schoolid){//根据已知老师数组查询老师姓名和头像
	if(is_array($tea_arr)){
		$alltea = array();
		$school = pdo_fetch("SELECT tpic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
		foreach($tea_arr as $row){
			if($row == 0 || $row != ""){
				$tea = pdo_fetch("SELECT tname as name,thumb FROM " . tablename('wx_school_teachers') . " WHERE id = '{$row}' And schoolid = '{$schoolid}' ");
				if($tea){
					if($tea['thumb']){
						$tea['icon'] = tomedia($tea['thumb']);
					}else{
						$tea['icon'] = tomedia($school['tpic']);
					}
					$alltea[] = $tea;
				}
			}
		}	
	}	
	return $alltea;
}
/**
 * 根据已知道老师查询可管辖班级情况 (授课班级 加上 可管辖班级)
 *
 * @param [type] $schoolid
 * @param [type] $is_over
 * @param [type] $schooltype
 * @param [type] $tid
 *
 * @return void
 */
function GetAllClassInfoByTid($schoolid,$is_over,$schooltype,$tid){ 
	$mynjlist = is_njzr($tid);

	$nowtime = time();
	if($schooltype){//查询授课班级或课程(不区分身份，后台安排的授课班级或课程)
		$kclist = pdo_fetchall("select id as sid ,name as sname, end,kc_type FROM ".tablename('wx_school_tcourse')." WHERE schoolid = '{$schoolid}'  and (tid like '%,{$tid},%'  or tid like '%,{$tid}' or tid like '{$tid},%' or tid ='{$tid}') ORDER BY end DESC , ssort DESC ");
		foreach($kclist as $key =>$row){
			$kclist[$key]['is_over'] = 1;
			$total = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$row['sid']}' And is_change != 1  "); //已报
			$kclist[$key]['sname'] = $row['sname'].'('.$total.'人)';
            $kclist[$key]['old_sname'] =$row['sname']; //只有名字，没有其他信息
			if($row['end'] < $nowtime){
				$kclist[$key]['info'] = '--(已结课)';
				$kclist[$key]['is_over'] = 2;
			}
		} 
	}else{
		$bjlists = get_myskbj($tid);//默认取未毕业班级
		foreach($bjlists as $i => $v){
			$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$v['bj_id']}' ");
			$bjinfo = pdo_fetch("SELECT is_over,sname FROM " . tablename('wx_school_classify') . " where sid = :sid", array(':sid' => $v['bj_id']));
			$bjlists[$i]['sname'] = $bjinfo['sname'].'('.$total.'人)';
			$bjlists[$i]['old_sname'] = $bjinfo['sname'];//只有名字，没有其他信息
			$bjlists[$i]['sid'] = $v['bj_id'];
			$bjlists[$i]['bj_id'] = $v['bj_id'];
			$bjlists[$i]['ssort'] = $v['ssort'];
			$bjlists[$i]['is_over'] = $bjinfo['is_over'];
			if($bjinfo['is_over'] == 2){
				$bjlists[$i]['info'] = '--(已毕业)';
				if($is_over == 1){
					unset($bjlists[$i]);
				}
			}
		}	
		$bjlists = array_merge($bjlists);	
	}	
	if($mynjlist){

		$getallnj = getallnj($tid);
       
		if($schooltype){//年级主任取管辖班级和授课班级
			$datas = array();
			$datas = array_merge($datas,$kclist);
			foreach($getallnj as $val){
				$str_nj .=$val['sid'].',';					
			}
			$str_nj = trim($str_nj,',');
			$kclist_nj = pdo_fetchall("select id as sid ,name as sname, end,kc_type FROM ".tablename('wx_school_tcourse')." WHERE schoolid = :schoolid and FIND_IN_SET(xq_id,:str_nj) ORDER BY end DESC , ssort DESC ",array(':schoolid'=>$schoolid,':str_nj'=>$str_nj));
			if(!empty($kclist_nj)){

			 
				foreach($kclist_nj as $key =>$row){
					$kclist_nj[$key]['is_over'] = 1;
					$total = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$row['sid']}' And is_change != 1  "); //已报
					$kclist_nj[$key]['sname'] = $row['sname'].'('.$total.'人)';
                    $kclist_nj[$key]['old_sname'] =$row['sname']; //只有名字，没有其他信息
					if($row['end'] < $nowtime){
						$kclist_nj[$key]['info'] = '--(已结课)';
						$kclist_nj[$key]['is_over'] = 2;
					}
				}	
				$datas = array_merge($datas,$kclist_nj);
			}
		}else{
			$condition = '';
			if($is_over == 1){
				//$condition .= " AND is_over = '{$is_over}' ";
			}			
			$datas = array();
			$datas = array_merge($datas,$bjlists);
			foreach($getallnj as $val){
				$classify = pdo_fetchall("SELECT sid,sname,is_over,ssort FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' And parentid='{$val['sid']}' $condition ORDER BY ssort DESC ");
				foreach($classify as $key => $row){
					$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
					$classify[$key]['sname'] = $row['sname'].'('.$total.'人)';
					$classify[$key]['old_sname'] = $row['sname'];
					if($row['is_over'] == 2){
						$classify[$key]['info'] = '--(已毕业)';
						if($is_over == 1){
							unset($classify[$key]);
						}
					}
				}
				$classify = array_merge($classify);
				$datas = array_merge($datas,$classify);
			}
		}
	}else{
		if(is_xiaozhang($tid) || $tid == 'founder' || $tid == 'owner'){//校长  取全校数据
			if($schooltype){//培训
				$condition = '';
				$nowtime = time();
				if($is_over == 1){
					//$condition .= " AND end >= $nowtime ";
				}		
				$datas = pdo_fetchall("SELECT id as sid,name as sname,end,kc_type FROM " . tablename('wx_school_tcourse') . " WHERE schoolid='{$schoolid}' $condition  ORDER BY end DESC, ssort DESC");
				foreach($datas as $key =>$row){
					$datas[$key]['is_over'] = 1;
					$total = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$row['sid']}' And is_change != 1  "); //已报
					$datas[$key]['sname'] = $row['sname'].'('.$total.'人)';
                    $datas[$key]['old_sname'] = $row['sname'];
					if($row['end'] < $nowtime){
						$datas[$key]['info'] = '--(已结课)';
						$datas[$key]['is_over'] = 2;
					}
				} 
				

			}else{//公立
				$condition = '';
				if($is_over == 1){
					//$condition .= " AND is_over = '{$is_over}' ";
				}
				$datas = pdo_fetchall("SELECT sid,sname,is_over,ssort FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' $condition ORDER BY ssort DESC ");
				foreach($datas as $key => $row){
					$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
					$datas[$key]['sname'] = $row['sname'].'('.$total.'人)';
					$datas[$key]['old_sname'] = $row['sname'];
					$datas[$key]['bj_id'] = $row['sid'];
					if($row['is_over'] == 2){
						$datas[$key]['info'] = '--(已毕业)';
						if($is_over == 1){
							unset($datas[$key]);
						}
					}
				}
				$datas = array_merge($datas);
			}
		}else{ //普通老师取授课班级
			if($schooltype){//年级主任取管辖班级和授课班级
				$datas = $kclist;
			}else{
				$datas = $bjlists;//默认取未毕业班级
			}	
		}
	}
	$datas = array_sorts($datas,'ssort','DESC');
	return $datas;	
}

function GetAllClassStuInfoByTid($schoolid,$is_over,$schooltype,$tid){ //根据已知道老师查询可管辖班级情况 及学生头像姓名
	$mynjlist = is_njzr($tid);
	$nowtime = time();
	$school = pdo_fetch("SELECT spic FROM ".tablename('wx_school_index')." WHERE id = '{$schoolid}' ");
	if($schooltype){//查询授课班级或课程(不区分身份，后台安排的授课班级或课程)
		$kclist = pdo_fetchall("select id as sid ,name as sname, end FROM ".tablename('wx_school_tcourse')." WHERE schoolid = '{$schoolid}'  and (tid like '%,{$tid},%'  or tid like '%,{$tid}' or tid like '{$tid},%' or tid ='{$tid}') ORDER BY end DESC , ssort DESC ");
		foreach($kclist as $key =>$row){
			$allstu = pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM ".tablename('wx_school_students')." as students ,".tablename('wx_school_order')." as orders  WHERE orders.schoolid = '{$schoolid}' And orders.kcid = '{$row['sid']}' and orders.type = 1 and  orders.status = 2 and students.id = orders.sid GROUP BY students.id ORDER BY students.id  ASC ");
			foreach($allstu as $k =>$vs){
				if($vs['icon']){
					$allstu[$k]['icon'] = tomedia($vs['icon']);
				}else{
					$allstu[$k]['icon'] = tomedia($school['spic']);
				}
			}
			
			$kclist[$key]['allstu'] = $allstu;
			$kclist[$key]['is_over'] = 1;
			$total = pdo_fetchcolumn("select COUNT(distinct sid) FROM ".tablename('wx_school_order')." WHERE kcid = '{$row['sid']}' and type = 1 and status = 2 and sid != 0  ");
			$kclist[$key]['sname'] = $row['sname'].'('.$total.'人)';
			if($row['end'] < $nowtime){
				$kclist[$key]['info'] = '--(已结课)';
				$kclist[$key]['is_over'] = 2;
			}
		} 
	}else{
		$get_myskbj = get_myskbj($tid);//默认取未毕业班级
		$bjlists = array();
		foreach($get_myskbj as $i => $v){
			$allstu = pdo_fetchall("SELECT id,s_name,icon FROM ".tablename('wx_school_students')." WHERE schoolid = '{$schoolid}' And bj_id ='{$v['bj_id']}' ORDER BY id ASC ");
			foreach($allstu as $k =>$vs){
				if($vs['icon']){
					$allstu[$k]['icon'] = tomedia($vs['icon']);
				}else{
					$allstu[$k]['icon'] = tomedia($school['spic']);
				}
			}
			$bjlists[$i]['allstu'] = $allstu;
			$bjinfo = pdo_fetch("SELECT is_over,sname FROM " . tablename('wx_school_classify') . " where sid = :sid", array(':sid' => $v['bj_id']));
			$bjlists[$i]['sname'] = $bjinfo['sname'].'('.count($allstu).'人)';
			$bjlists[$i]['sid'] = $v['bj_id'];
			$bjlists[$i]['ssort'] = $v['ssort'];
			$bjlists[$i]['is_over'] = $bjinfo['is_over'];
			if($bjinfo['is_over'] == 2){
				$bjlists[$i]['info'] = '--(已毕业)';
				if($is_over == 1){
					unset($bjlists[$i]);
				}
			}
		}		
	}	
	if($mynjlist){ 
		$getallnj = getallnj($tid);
		if($schooltype){//年级主任取管辖班级和授课班级
			$datas = array();
			$datas = array_merge($datas,$kclist);
			foreach($getallnj as $val){
				$str_nj .=$val['sid'].',';					
			}
			$str_nj = trim($str_nj,',');
			$kclist_nj = pdo_fetchall("select id as sid ,name as sname, end FROM ".tablename('wx_school_tcourse')." WHERE schoolid = :schoolid and FIND_IN_SET(xq_id,:str_nj) ORDER BY end DESC , ssort DESC ",array(':schoolid'=>$schoolid,':str_nj'=>$str_nj));
			if(!empty($kclist_nj)){
				
			
				foreach($kclist_nj as $key =>$row){
					$allstu = pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM ".tablename('wx_school_students')." as students ,".tablename('wx_school_order')." as orders  WHERE orders.schoolid = '{$schoolid}' And orders.kcid = '{$row['sid']}' and orders.type = 1 and orders.status = 2 and students.id = orders.sid GROUP BY students.id ORDER BY students.id ASC ");
					foreach($allstu as $k =>$vs){
						if($vs['icon']){
							$allstu[$k]['icon'] = tomedia($vs['icon']);
						}else{
							$allstu[$k]['icon'] = tomedia($school['spic']);
						}
					}
					$kclist_nj[$key]['allstu'] = $allstu;
					$kclist_nj[$key]['is_over'] = 1;
					$total = pdo_fetchcolumn("select COUNT(distinct sid) FROM ".tablename('wx_school_order')." WHERE kcid = '{$row['sid']}' and type = 1 and status = 2 and sid != 0  ");
					$kclist_nj[$key]['sname'] = $row['sname'].'('.$total.'人)';
					if($row['end'] < $nowtime){
						$kclist_nj[$key]['info'] = '--(已结课)';
						$kclist_nj[$key]['is_over'] = 2;
					}
				}	
				$datas = array_merge($datas,$kclist_nj);
			}
		}else{
			$condition = '';
			if($is_over == 1){
				//$condition .= " AND is_over = '{$is_over}' ";
			}			
			$datas = array();
			$datas = array_merge($datas,$bjlists);
			foreach($getallnj as $val){
				$classify = pdo_fetchall("SELECT sid,sname,is_over,ssort FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' And parentid='{$val['sid']}' $condition ORDER BY ssort DESC ");
				foreach($classify as $key => $row){					
					$allstus = pdo_fetchall("SELECT id,s_name,icon FROM ".tablename('wx_school_students')." WHERE schoolid = '{$schoolid}' And bj_id ='{$row['sid']}' ORDER BY id ASC ");
					foreach($allstus as $ks =>$is){
						if($is['icon']){
							$allstus[$ks]['icon'] = tomedia($is['icon']);
						}else{
							$allstus[$ks]['icon'] = tomedia($school['spic']);
						}
					}
					$classify[$key]['allstu'] = $allstus;
					$classify[$key]['sname'] = $row['sname'].'('.count($allstus).'人)';
					if($row['is_over'] == 2){
						$classify[$key]['info'] = '--(已毕业)';
						if($is_over == 1){
							unset($classify[$key]);
						}
					}
				}
				$datas = array_merge($datas,$classify);
			}
		}
	}else{
		if(is_xiaozhang($tid)){//校长  取全校数据
			if($schooltype){//培训
				$condition = '';
				$nowtime = time();
				if($is_over == 1){
					//$condition .= " AND end >= $nowtime "; 
				}		
				$datas = pdo_fetchall("SELECT id as sid,name as sname,end FROM " . tablename('wx_school_tcourse') . " WHERE schoolid='{$schoolid}' $condition  ORDER BY end DESC, ssort DESC");
				foreach($datas as $key =>$row){
					$allstu = pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM ".tablename('wx_school_students')." as students ,".tablename('wx_school_order')." as orders  WHERE orders.schoolid = '{$schoolid}' And orders.kcid = '{$row['sid']}' and orders.type = 1 and orders.status = 2 and students.id = orders.sid GROUP BY students.id ORDER BY students.id ASC ");
					foreach($allstu as $k =>$vs){
						if($vs['icon']){
							$allstu[$k]['icon'] = tomedia($vs['icon']);
						}else{
							$allstu[$k]['icon'] = tomedia($school['spic']);
						}
					}
					$datas[$key]['allstu'] = $allstu;
					$datas[$key]['is_over'] = 1;
					$total = pdo_fetchcolumn("select COUNT(distinct sid) FROM ".tablename('wx_school_order')." WHERE kcid = '{$row['sid']}' and type = 1 and status = 2 and sid != 0  ");
					$datas[$key]['sname'] = $row['sname'].'('.$total.'人)';
					if($row['end'] < $nowtime){
						$datas[$key]['info'] = '--(已结课)';
						$datas[$key]['is_over'] = 2;
					}
				} 
			}else{//公立
				$condition = '';
				if($is_over == 1){
					$condition .= " AND is_over = '{$is_over}' ";
				}
				$datas = pdo_fetchall("SELECT sid,sname,is_over,ssort FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' $condition ORDER BY ssort DESC ");
				foreach($datas as $key => $row){
					$allstus = pdo_fetchall("SELECT id,s_name,icon FROM ".tablename('wx_school_students')." WHERE schoolid = '{$schoolid}' And bj_id ='{$row['sid']}' ORDER BY id ASC ");
					foreach($allstus as $ks =>$is){
						if($is['icon']){
							$allstus[$ks]['icon'] = tomedia($is['icon']);
						}else{
							$allstus[$ks]['icon'] = tomedia($school['spic']);
						}
					}
					$datas[$key]['allstu'] = $allstus;
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
		}else{ //普通老师取授课班级
			if($schooltype){//年级主任取管辖班级和授课班级
				$datas = $kclist;
			}else{
				$datas = $bjlists;//默认取未毕业班级
			}	
		}
	}
	$datas = array_sorts($datas,'ssort','DESC');
	return $datas;
}

function get_myskbj($tid){ //根据tid查询授课班级包含毕业班
	$bjlist = pdo_fetchall("SELECT distinct(bj_id)  FROM ".tablename('wx_school_user_class')." WHERE tid = :tid  ", array(':tid' => $tid));
	foreach($bjlist as $key =>$row){
		$bjinfo = pdo_fetch("SELECT sid,ssort FROM ".tablename('wx_school_classify')." WHERE  sid ='{$row['bj_id']}'  ");
		if(empty($bjinfo)){
			unset($bjlist[$key]);
		}
	}
	return $bjlist;	
}



function get_MyBjAndKm($tid,$schoolid,$weid){ //根据tid查询授课情况（班级，科目）包含毕业班 仅返回id
    $bjlist = pdo_fetchall("SELECT bj_id,km_id  FROM ".tablename('wx_school_user_class')." WHERE tid = :tid  ", array(':tid' => $tid));
    foreach($bjlist as $key =>$row){
        $bjinfo = pdo_fetch("SELECT sid FROM ".tablename('wx_school_classify')." WHERE  sid ='{$row['bj_id']}'  ");
        if(empty($bjinfo)){
            unset($bjlist[$key]);
        }
    }
    return $bjlist;
}


/**
 * 根据tid判断是否校长
 *
 * @param [type] $tid
 *
 * @return bool
 */
function is_xiaozhang($tid){
	 $teacher = pdo_fetch("SELECT status FROM " . tablename('wx_school_teachers') . " where id = :id", array(':id' => $tid));
	if($teacher['status'] == 2){
		return true;
	}else{
		return false;
	}	
}

/**
 * 查看老师可查看的成绩
 *
 * @param [type] $tid
 * @param [type] $schoolid
 * @param [type] $qhid
 * @param [type] $bjid
 * @param [type] $weid
 *
 * @return void
 */
function GetStuScore($tid,$schoolid,$qhid,$bjid,$weid){
	$teacherinfo = pdo_fetch("SELECT status FROM " . GetTableName('teachers') . " where weid = '{$weid}' AND schoolid ={$schoolid} and id='{$tid}'  ");
	$xq_id = pdo_fetch("SELECT parentid FROM " . GetTableName('classify') . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$bjid}'  ");
	//如果是校长/年级主任/班主任/或者是有权限的人  可查看所有科目成绩
    if($teacherinfo['status'] == 2 || $teacherinfo['status'] == 3 || IsHasQx($tid,2002402,2,$schoolid) || is_bzr($tid,$bjid,$schoolid,$weid)){
		$cjlist = pdo_fetchall("SELECT score.* ,student.s_name FROM " . tablename('wx_school_score') . " as score ,". tablename('wx_school_students') ." as student where score.weid = '{$weid}' AND score.schoolid ={$schoolid} and score.bj_id='{$bjid}' and score.sid = student.id and score.qh_id = '{$qhid}'   ORDER BY CONVERT(student.s_name USING gbk)  ASC  ");
    }else{
		//普通老师只能查看授课科目成绩
        $kmlist = pdo_fetchall("SELECT km_id  FROM ".tablename('wx_school_user_class')." WHERE tid ='{$tid}' and bj_id = '{$bjid}'  ");
        $km_str = '';
        foreach ($kmlist as $row){
            $km_str .= $row['km_id'].',';
        }
        $km_str = trim($km_str,',');
        $cjlist = pdo_fetchall("SELECT score.* ,student.s_name FROM " . tablename('wx_school_score') . " as score ,". tablename('wx_school_students') ." as student where score.weid = '{$weid}' AND score.schoolid ={$schoolid} and score.bj_id='{$bjid}' and score.sid = student.id and score.qh_id = '{$qhid}' and FIND_IN_SET(score.km_id,'{$km_str}')  ORDER BY CONVERT(student.s_name USING gbk)  ASC  ");
    }

    $back_data_temp = array();
    foreach ($cjlist as $key=>$value){
        $student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " where weid = '{$weid}' AND schoolid ={$schoolid} and id='{$value['sid']}'  ");
        $kminfo = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$value['km_id']}'  ");
		//班级排名 单科
        $count_before = pdo_fetchall(" select distinct sid  FROM " . GetTableName('score') . "  where  bj_id = '{$value['bj_id']}' and qh_id = '{$value['qh_id']}' and km_id = '{$value['km_id']}'   AND schoolid = '{$schoolid}' and my_score +0  >{$value['my_score']}+0   " );
		$rank = count($count_before)+1; 
		//年级排名 单科
		$count_xq_before = pdo_fetchall(" select distinct sid  FROM " . GetTableName('score') . "  where  xq_id = '{$value['xq_id']}' and qh_id = '{$value['qh_id']}' and km_id = '{$value['km_id']}'   AND schoolid = '{$schoolid}' and my_score +0  >{$value['my_score']}+0   " );
        $rank_xq = count($count_xq_before)+1; 

        $back_data_temp["{$value['sid']}"]['s_name'] =$student['s_name'];
        $back_data_temp["{$value['sid']}"]['data'][$value['km_id']]['score'] =$value['is_absent'] == 1 ? "缺考" : $value['my_score'];
		$back_data_temp["{$value['sid']}"]['data'][$value['km_id']]['rank'] = $rank;
		$back_data_temp["{$value['sid']}"]['data'][$value['km_id']]['xq_rank'] = $rank_xq;

        $back_data_temp["{$value['sid']}"]['data'][$value['km_id']]['km_name'] = $kminfo['sname'];
        $back_data_temp["{$value['sid']}"]['data'][$value['km_id']]['xq_id'] = $value['xq_id'];
		$back_data_temp["{$value['sid']}"]['rowcount'] =  $back_data_temp["{$value['sid']}"]['rowcount'] +1;
        rsort($back_data_temp["{$value['sid']}"]['data']);
    }

	 

    $back_data = array();
    foreach ($back_data_temp as $key => $value_t){
		if ($teacherinfo['status'] == 2 || $teacherinfo['status'] == 3 || IsHasQx($tid, 2002402, 2, $schoolid) || is_bzr($tid, $bjid, $schoolid, $weid)) {
			$sid = $key;
			//班级排名 总分
			$student_allscore = pdo_fetchcolumn("select SUM(my_score) FROM " . GetTableName('score') . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and qh_id = '{$qhid}'  and sid = '{$sid}' ");
			$count_before_all = pdo_fetchall(" select distinct(sid)  FROM " . GetTableName('score') . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' and qh_id = '{$qhid}' and bj_id = '{$value['bj_id']}'  group by sid   HAVING SUM(my_score)+0>{$student_allscore} +0  " );
			$rank_all = count($count_before_all) +1;

			//年级排名 总分
			$count_before_all_xq = pdo_fetchall(" select distinct(sid)  FROM " . GetTableName('score') . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' and qh_id = '{$qhid}' and xq_id = '{$xq_id['parentid']}'  group by sid   HAVING SUM(my_score)+0>{$student_allscore} +0  " );
			$rank_all_xq = count($count_before_all_xq) +1;

			$back_data_temp[$key]['data']['all']['km_name'] = '总分';
			$back_data_temp[$key]['data']['all']['score'] =$student_allscore;
			$back_data_temp[$key]['data']['all']['rank'] = $rank_all;
			$back_data_temp[$key]['data']['all']['xq_rank'] = $rank_all_xq;
			$back_data_temp[$key]['rowcount'] =  $value_t['rowcount'] +1;
		}
        $back_data[] = $back_data_temp[$key];
    }
 
    $back_result['status'] = true;
    $back_result['data'] = $back_data;
return $back_result;

}

/**
 * 判断是否班主任
 *
 * @param [type] $tid
 * @param [type] $bj_id
 * @param [type] $schoolid
 * @param [type] $weid
 *
 * @return bool
 */
function is_bzr($tid,$bj_id,$schoolid,$weid){
    $check = pdo_fetch("SELECT * FROM " . tablename('wx_school_classify') . " where weid = '{$weid}' AND schoolid ={$schoolid} and tid = '{$tid}' and sid = '{$bj_id}' ");
    if(!empty($check)){
        return true;
    }else{
        return false;
    }
}


/**
 * 获取班级量化评分
 *
 * @param [type] $schoolid
 * @param [type] $weid
 * @param [type] $scoretime
 *
 * @return void
 */
function GetBjScore($schoolid,$weid,$scoretime){
	$condition = "";
    $condition .= " and scoretime = '{$scoretime}' ";
    //获取当前学期
    $score_xueqi = pdo_fetch("select * FROM " . tablename('wx_school_classify') . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sd_start <='{$scoretime}' and sd_end >='{$scoretime}'  ");
    //获得当前月初始时间与结束时间
    $nowmonthstart = mktime(0, 0 , 0,date("m",$scoretime),1,date("Y",$scoretime));
    $nowmonthend = mktime(23,59,59,date("m",$scoretime),date("t",$scoretime),date("Y",$scoretime));
    //获取当前周初始时间与结束时间
    $nowweekstart = mktime(0,0,0,date("m",$scoretime),date("d",$scoretime)-date("w",$scoretime)+1,date("Y",$scoretime));
    $nowweekend = mktime(23,59,59,date("m",$scoretime),date("d",$scoretime)-date("w",$scoretime)+7,date("Y",$scoretime));
    $list = pdo_fetchall("SELECT id,score,bj_id FROM " . tablename('wx_school_teascore') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 2 ORDER BY score DESC  " );

    foreach($list as $key => $row){
        $bj_name = pdo_fetch("select sname FROM " . tablename('wx_school_classify') . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$row['bj_id']}' ");


        $list[$key]['bj_name'] = $bj_name['sname'];


        //学期得分与排名
        $all_score_xueqi = pdo_fetchcolumn("select SUM(score) FROM " . tablename('wx_school_teascore') . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}' ");
        $count_before = pdo_fetchall(" select bj_id ,SUM(score) FROM " . tablename('wx_school_teascore') . "  where   scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0 > {$all_score_xueqi}+0   " );
        $xueqi_rank = count($count_before)+1; //学期排名
        $list[$key]['xueqi_score'] = $all_score_xueqi;
        $list[$key]['xueqi_rank'] = $xueqi_rank;



        //月得分与排名
        $all_score_month = pdo_fetchcolumn("select SUM(score) FROM " . tablename('wx_school_teascore') . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$nowmonthstart}' AND scoretime <='{$nowmonthend}' ");
        $count_before_month = pdo_fetchall(" select bj_id  FROM " . tablename('wx_school_teascore') . "  where   scoretime >='{$nowmonthstart}' AND scoretime <='{$nowmonthend}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0>{$all_score_month} +0   " );

        $month_rank = count($count_before_month)+1; //月排名
        $list[$key]['month_score'] = $all_score_month;
        $list[$key]['month_rank'] = $month_rank;



        //周得分与排名
        $all_score_week = pdo_fetchcolumn("select SUM(score) FROM " . tablename('wx_school_teascore') . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$nowweekstart}' AND scoretime <='{$nowweekend}' ");
        $count_before_week = pdo_fetchall(" select bj_id  FROM " . tablename('wx_school_teascore') . "  where   scoretime >='{$nowweekstart}' AND scoretime <='{$nowweekend}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0>{$all_score_week} +0   " );
        $week_rank = count($count_before_week)+1; //周排名
        $list[$key]['week_score'] = $all_score_week;
        $list[$key]['week_rank'] = $week_rank;

        //日得分与排名
        $count_before_date = pdo_fetchall(" select bj_id  FROM " . tablename('wx_school_teascore') . "  where   scoretime ='{$scoretime}'  and type='2'  AND schoolid = '{$schoolid}'  and score>'{$row['score']}' + 0   " );
        $list[$key]['date_rank'] = count($count_before_date)+1;



    }
	if(!empty($list)){
		$result['data'] = $list;
		$result['bjcount'] = count($list);
		$result['status'] = true;
		$result['date'] = date("Y年m月d日",$scoretime);
	}else{
		$result['status'] = false;
	}

	return $result;

}




/**
 * 获取老师管辖班级（仅获取可管辖的，即 班主任  年级主任 校长，不带授课班级）
 *
 * @param [type] $weid
 * @param [type] $schoolid
 * @param [type] $tid
 *
 * @return array
 */
function GetTeaManagerBjlist($weid,$schoolid,$tid){
	$teacher = pdo_fetch("SELECT * FROM ".GetTableName('teachers')." WHERE id = '{$tid}' and schoolid = '{$schoolid}' and weid = '{$weid}' ");
	//如果是校长，可查看全校所有班级
	if($teacher['status'] == 2){
		$ManaNjlist = pdo_fetchall("SELECT * FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid ='{$schoolid}'  and type = 'semester' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC ");
	//如果不是校长 
	}elseif($teacher['status'] == 3){
		$ManaNjlist = pdo_fetchall("SELECT * FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid ='{$schoolid}' and tid = '{$tid}' and type = 'semester' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC ");
	}
	$NjlistPureArr = [];
	$njlist = [];
	if(is_array($ManaNjlist)){
		foreach($ManaNjlist as $key => $value){
			if(!in_array($value['sid'],$NjlistPureArr)){
				$NjlistPureArr[] = $value['sid'];
				$cellData = [];
				$bjlist = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE parentid = '{$value['sid']}' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC");
				$cellData['sname']  = $value['sname'];
				$cellData['sid']    = $value['sid'];
				$cellData['bjlist'] = $bjlist;
				$njlist[] = $cellData;
			}
		}
	}


	/* $bzrlist = pdo_fetchall(" SELECT * FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'theclass' and tid = '{$tid}' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC");
	
	foreach($bzrlist as $key_b=>$value_b){
		if(!in_array($value_b['parentid'],$NjlistPureArr)){
			$NjlistPureArr[] = $value_b['parentid'];
			$njinfo = pdo_fetch("SELECT sid,sname FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and sid = '{$value_b['parentid']}' ");
			$cellData_b = $njinfo;
			$cellData_b['bjlist'][] = array(
				'sid' => $value_b['sid'],
				'sname' => $value_b['sname']
			);
			$cellData_b['bjlistpure'][] = $value_b['sid'];
			$njlist[] = $cellData_b;
		}
	} */

	$bzrnjlist = pdo_fetchall(" SELECT distinct parentid FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'theclass' and tid = '{$tid}' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC");
	foreach($bzrnjlist as $key_bn =>$value_bn){
		if(!in_array($value_bn['parentid'],$NjlistPureArr)){
			$NjlistPureArr[] = $value_bn['parentid'];
			$cellData = [];
			$bjlist = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE parentid = '{$value_bn['parentid']}' and tid = '{$tid}' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC");
			$njinfo = pdo_fetch(" SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$value_bn['parentid']}' and weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'semester' ");
			$cellData['sname']  = $njinfo['sname'];
			$cellData['sid']    = $value_bn['parentid'];
			$cellData['bjlist'] = $bjlist;
			$njlist[] = $cellData; 
		}
	}
	return $njlist;	
}
/**
 * 按当前课程查询出所有班级及班级下的学生(按照班级划分)
 */
function GetNowKcStu($schoolid,$kcid){
	$stuList = pdo_fetchall("SELECT distinct bjid FROM ".GetTableName('coursebuy')." WHERE kcid = :kcid AND schoolid = :schoolid AND is_change != :is_change ORDER BY bjid DESC",array(':kcid'=>$kcid,':schoolid'=>$schoolid,':is_change'=>1));
	$kcallstu = 0;
	foreach ($stuList as $key => $value) {
		$students = pdo_fetchall("SELECT s.id as sid,s.s_name,c.bjid FROM " . GetTableName('coursebuy') . " c , " . GetTableName('students') . " s WHERE c.sid=.s.id AND c.bjid = '{$value['bjid']}' AND kcid = '{$kcid}' AND c.is_change != 1 ORDER BY c.sid DESC");
		$bjname = pdo_fetch("SELECT title FROM " . GetTableName('class') . " WHERE schoolid = :schoolid AND id = :id ", array(':schoolid' => $schoolid, ':id' => $value['bjid']))['title'];
		$stuList[$key]['bjname'] = intval($value['bjid']) == 0 ? '未分班' : $bjname;
		$stuList[$key]['stulist'] = $students;
		$stuList[$key]['count'] = count($students);
		$kcallstu += count($students);
	}
	$result['stulist'] = $stuList;
	$result['kcallstu'] = $kcallstu;
	return $result;
}

/**
 * 按当前课程查询出所有班级及班级下的学生(不分班级)
 */
function GetKcStu($schoolid,$kcid){
	$stuList = pdo_fetchall("SELECT distinct sid FROM ".GetTableName('coursebuy')." WHERE kcid = :kcid AND schoolid = :schoolid AND is_change != :is_change ORDER BY id DESC",array(':kcid'=>$kcid,':schoolid'=>$schoolid,':is_change'=>1));
	$result['stulist'] = $stuList;
	$result['kcallstu'] = count($stuList);
	return $result;
}

/**
 * 根据已知道老师查询可管辖班级情况 (授课班级 加上 可管辖班级-----新版-培训学校有班级)
 *
 * @param [type] $schoolid
 * @param [type] $is_over
 * @param [type] $schooltype
 * @param [type] $tid
 *
 * @return void
 */
function GetNewAllClassInfoByTid($schoolid,$is_over,$schooltype,$tid){ 
	$mynjlist = is_njzr($tid);

	$nowtime = time();
	if($schooltype){//查询授课班级或课程(不区分身份，后台安排的授课班级或课程)
		/*****************普通教师*****************/
		$kclist = pdo_fetchall("select id as sid ,name as sname, end FROM ". GetTableName('tcourse') ." WHERE schoolid = '{$schoolid}'  and (tid like '%,{$tid},%'  or tid like '%,{$tid}' or tid like '{$tid},%' or tid ='{$tid}') ORDER BY end DESC , ssort DESC ");
		foreach ($kclist as $index => $row) {
			//TODO:将这3种身份写成一个model在调用
			$result1 = GetNowKcStu($schoolid,$row['sid']);
			$kclist[$index]['sname'] = $row['sname'].'('.$result1['kcallstu'].'人)';
			$stulist = $result1['stulist'];
			if($row['end'] < $nowtime){
				$kclist[$index]['sname'] .= '--(已结课)';
			}

			foreach ($stulist as $key => $value) {
				$kclist[$index]['stulist'][$key]['bjname'] = $value['bjname'];
				$kclist[$index]['stulist'][$key]['bjid'] = $value['bjid'];
				$kclist[$index]['stulist'][$key]['count'] = $value['count'];
				foreach ($value['stulist'] as $key2 => $value2) {
					$stuinfo = pdo_fetchall("SELECT realname,mobile,pard,id,uid,is_allowmsg,sid FROM " . GetTableName('user') . " WHERE schoolid = :schoolid AND sid = :sid ", array(
						':schoolid' => $schoolid,
						':sid' => $value2['sid']
					));
					foreach ($stuinfo as $key3 => $value3) {
						$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uid = :uid ", array( ':uid' => $value3['uid']));
						$kclist[$index]['stulist'][$key]['sid'][$key2]['avatar'] = $member['avatar'];
						$kclist[$index]['stulist'][$key]['sid'][$key2]['realname'] = $value3['realname'];
						$kclist[$index]['stulist'][$key]['sid'][$key2]['mobile'] = $value3['mobile'];
						$kclist[$index]['stulist'][$key]['sid'][$key2]['pard'] = $value3['pard'];
						$kclist[$index]['stulist'][$key]['sid'][$key2]['id'] = $value3['id'];
						$kclist[$index]['stulist'][$key]['sid'][$key2]['is_allowmsg'] = $value3['is_allowmsg'];
						$kclist[$index]['stulist'][$key]['sid'][$key2]['sid'] = $value3['sid'];
						$kclist[$index]['stulist'][$key]['sid'][$key2]['uid'] = $value3['uid'];
					}
				}
			}
			$kclist[$index]['rs'] = $result1['kcallstu'];
		}
	}else{
		$bjlists = get_myskbj($tid);//默认取未毕业班级
		foreach($bjlists as $i => $v){
			$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$v['bj_id']}' ");
			$bjinfo = pdo_fetch("SELECT is_over,sname FROM " . tablename('wx_school_classify') . " where sid = :sid", array(':sid' => $v['bj_id']));
			$bjlists[$i]['sname'] = $bjinfo['sname'].'('.$total.'人)';
			$bjlists[$i]['old_sname'] = $bjinfo['sname'];//只有名字，没有其他信息
			$bjlists[$i]['sid'] = $v['bj_id'];
			$bjlists[$i]['bj_id'] = $v['bj_id'];
			$bjlists[$i]['ssort'] = $v['ssort'];
			$bjlists[$i]['is_over'] = $bjinfo['is_over'];
			if($bjinfo['is_over'] == 2){
				$bjlists[$i]['info'] = '--(已毕业)';
				if($is_over == 1){
					unset($bjlists[$i]);
				}
			}
		}		
	}	
	if($mynjlist){
		$getallnj = getallnj($tid);
		if($schooltype){ //年级主任取管辖班级和授课班级
			$datas = array();
			$datas = array_merge($datas,$kclist);
			foreach($getallnj as $val){
				$str_nj .=$val['sid'].',';					
			}
			$str_nj = trim($str_nj,',');
			$kclist_nj = pdo_fetchall("select id as sid ,name as sname, end FROM ".tablename('wx_school_tcourse')." WHERE schoolid = :schoolid and FIND_IN_SET(xq_id,:str_nj) ORDER BY end DESC , ssort DESC ",array(':schoolid'=>$schoolid,':str_nj'=>$str_nj));
			if(!empty($kclist_nj)){
				foreach ($kclist_nj as $index => $row) {
					$result1 = GetNowKcStu($schoolid,$row['sid']);
					$stulist = $result1['stulist'];
					$kclist_nj[$index]['sname'] = $row['sname'].'('.$result1['kcallstu'].'人)';
					if($row['end'] < $nowtime){
						$kclist_nj[$index]['bjname'] .= '--(已结课)';
					}
					foreach ($stulist as $key => $value) {
						$kclist_nj[$index]['stulist'][$key]['bjname'] = $value['bjname'];
						$kclist_nj[$index]['stulist'][$key]['bjid'] = $value['bjid'];
						$kclist_nj[$index]['stulist'][$key]['count'] = $value['count'];
						foreach ($value['stulist'] as $key2 => $value2) {
							$stuinfo = pdo_fetchall("SELECT realname,mobile,pard,id,uid,is_allowmsg,sid FROM " . GetTableName('user') . " WHERE schoolid = :schoolid AND sid = :sid ", array(
								':schoolid' => $schoolid,
								':sid' => $value2['sid']
							));
							foreach ($stuinfo as $key3 => $value3) {
								$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uid = :uid ", array( ':uid' => $value3['uid']));
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['avatar'] = $member['avatar'];
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['realname'] = $value3['realname'];
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['mobile'] = $value3['mobile'];
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['pard'] = $value3['pard'];
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['id'] = $value3['id'];
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['is_allowmsg'] = $value3['is_allowmsg'];
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['sid'] = $value3['sid'];
								$kclist_nj[$index]['stulist'][$key]['sid'][$key2]['uid'] = $value3['uid'];
							}
						}
					}
					$kclist[$index]['rs'] = $result1['kcallstu'];
				}

				$datas = array_merge($kclist,$kclist_nj);
			}
		}else{
			$condition = '';		
			$datas = array();
			$datas = array_merge($datas,$bjlists);
			foreach($getallnj as $val){
				$classify = pdo_fetchall("SELECT sid,sname,is_over,ssort FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' And parentid='{$val['sid']}' $condition ORDER BY ssort DESC ");
				foreach($classify as $key => $row){
					$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
					$classify[$key]['sname'] = $row['sname'].'('.$total.'人)';
					$classify[$key]['old_sname'] = $row['sname'];
					if($row['is_over'] == 2){
						$classify[$key]['info'] = '--(已毕业)';
						if($is_over == 1){
							unset($classify[$key]);
						}
					}
				}
				$datas = array_merge($datas,$classify);
			}
		}
	}else{
		if(is_xiaozhang($tid) || $tid == 'founder' || $tid == 'owner'){//校长  取全校数据
			if($schooltype){//培训
				$condition = '';
				$nowtime = time();	
				$datas =  pdo_fetchall("select id as sid ,name as sname, end FROM ". GetTableName('tcourse') ." WHERE schoolid = '{$schoolid}' ORDER BY end DESC , ssort DESC ");	
				foreach ($datas as $index => $row) {
					if($row['end'] < $nowtime){
						$datas[$index]['sname'] .= '--(已结课)';
					}
					$result1 = GetNowKcStu($schoolid,$row['sid']);
					$datas[$index]['sname'] = $row['sname'].'('.$result1['kcallstu'].'人)';
					$stulist = $result1['stulist'];
					foreach ($stulist as $key => $value) {
						$datas[$index]['stulist'][$key]['bjname'] = $value['bjname'];
						$datas[$index]['stulist'][$key]['bjid'] = $value['bjid'];
						$datas[$index]['stulist'][$key]['count'] = $value['count'];
						foreach ($value['stulist'] as $key2 => $value2) {
							$stuinfo = pdo_fetchall("SELECT realname,mobile,pard,id,uid,is_allowmsg,sid FROM " . GetTableName('user') . " WHERE schoolid = :schoolid AND sid = :sid ", array(
								':schoolid' => $schoolid,
								':sid' => $value2['sid']
							));
							foreach ($stuinfo as $key3 => $value3) {
								$member = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uid = :uid ", array( ':uid' => $value3['uid']));
								$datas[$index]['stulist'][$key]['sid'][$key2]['avatar'] = $member['avatar'];
								$datas[$index]['stulist'][$key]['sid'][$key2]['realname'] = $value3['realname'];
								$datas[$index]['stulist'][$key]['sid'][$key2]['mobile'] = $value3['mobile'];
								$datas[$index]['stulist'][$key]['sid'][$key2]['pard'] = $value3['pard'];
								$datas[$index]['stulist'][$key]['sid'][$key2]['id'] = $value3['id'];
								$datas[$index]['stulist'][$key]['sid'][$key2]['is_allowmsg'] = $value3['is_allowmsg'];
								$datas[$index]['stulist'][$key]['sid'][$key2]['sid'] = $value3['sid'];
								$datas[$index]['stulist'][$key]['sid'][$key2]['uid'] = $value3['uid'];
							}
						}
					}
					$datas[$index]['rs'] = $result1['kcallstu'];			
				}

			}else{//公立
				$datas = pdo_fetchall("SELECT sid,sname,is_over,ssort FROM ".tablename('wx_school_classify')." WHERE  type ='theclass' And schoolid='{$schoolid}' ORDER BY ssort DESC ");
				foreach($datas as $key => $row){
					$total = pdo_fetchcolumn("select COUNT(id) FROM ".tablename('wx_school_students')." WHERE bj_id = '{$row['sid']}' ");
					$datas[$key]['sname'] = $row['sname'].'('.$total.'人)';
					$datas[$key]['old_sname'] = $row['sname'];
					$datas[$key]['bj_id'] = $row['sid'];
					if($row['is_over'] == 2){
						$datas[$key]['info'] = '--(已毕业)';
						if($is_over == 1){
							unset($datas[$key]);
						}
					}
				}
			}
		}else{ //普通老师取授课班级
			if($schooltype){//年级主任取管辖班级和授课班级
				$datas = $kclist;
			}else{
				$datas = $bjlists;//默认取未毕业班级
			}	
		}
	}
	$datas = array_sorts($datas,'ssort','DESC');
	// var_dump($datas);die;
	return $datas;	
}


/**
 * 获取当前学校的所有年级，以及年级下的班级(TODO:keep_Ls())
 *
 * @param [type] $weid
 * @param [type] $schoolid
 * @param [type] $tid
 *
 * @return array
 */
function getSchoolBjlist($weid,$schoolid){
	$ManaNjlist = pdo_fetchall("SELECT * FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid ='{$schoolid}'  and type = 'semester' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC ");
	$NjlistPureArr = [];
	$njlist = [];
	if(is_array($ManaNjlist)){
		foreach($ManaNjlist as $key => $value){
			if(!in_array($value['sid'],$NjlistPureArr)){
				$NjlistPureArr[] = $value['sid'];
				$cellData = [];
				$bjlist = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE parentid = '{$value['sid']}' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}' ORDER BY  is_over ASC , CONVERT(sname USING gbk) ASC");
				$cellData['sname']  = $value['sname'];
				$cellData['sid']    = $value['sid'];
				$cellData['bjlist'] = $bjlist;
				$njlist[] = $cellData;
			}
		}
	}
	return $njlist;	
}
function teaIsBangDing($tid){
	$isBangding = pdo_fetch("SELECT id FROM ".GetTableName('user')." WHERE tid = '{$tid}' ");
	if($isBangding){
		return true;
	}else{
		return false;
	}
}
//当前老师授课课程
function myKcList($schoolid,$tid){
	$list = pdo_fetchall("SELECT * FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' AND FIND_IN_SET($tid,tid) AND kc_type != 1 ORDER BY end DESC");
	return $list;
}
?>