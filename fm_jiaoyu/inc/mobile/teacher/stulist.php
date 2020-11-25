<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid,':schoolid' => $schoolid,':openid' => $openid,':sid' => 0));	
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000501,2,$schoolid))){
			message('您无权查看本页面');
		}
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));		
		$bjlists = get_mylist($schoolid,$it['tid'],'teacher');	
		$ertype = true;
		if($school['is_stuewcode'] == 2){
			$ertype = false;
		}
		//var_dump($schoolid);
		//获取未毕业的班级/年级
		$bj_t = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid,':is_over'=>"2"));
		$nj_t = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid,':is_over'=>"2"));
		$bj_str_temp = '0,';
		foreach($bj_t as $key_b=>$value_b){
			$bj_str_temp .=$value_b['sid'].",";
		}
		$bj_str = trim($bj_str_temp,",");
		$nj_str_temp = '0,';
		foreach($nj_t as $key_n=>$value_n){
			$nj_str_temp .=$value_n['sid'].",";
		}
		$nj_str = trim($nj_str_temp,",");
		//var_dump(GetSchoolType($schoolid,$weid));
		if($_W['schooltype']){
			mload()->model('kc');
			//培训机构模式
			$nowtime = time();
			//获取首次进入时的课程 
			if(is_njzr($teachers['id'])){
				$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' And type = 'semester' and is_over != 2");
				$firstKC =  pdo_fetch("SELECT id  FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And xq_id = '{$myfisrtnj['sid']}' ORDER BY end  DESC  ");
				
				$mynjlist = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' and is_over != 2 ORDER BY ssort DESC");
				$nj_str_temp = '';
				foreach($mynjlist as $key =>$row){
					$nj_str_temp .=$row['sid'].",";
				}
				$nj_str_temp = trim($nj_str_temp,",");
				$MyKcList =  pdo_fetchall("SELECT id,name  FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' AND ( maintid = '{$it['tid']}'  And  FIND_IN_SET(xq_id,'{$nj_str}') OR  FIND_IN_SET(xq_id,'{$nj_str_temp}') ) ORDER BY end  DESC ");
			}elseif($teachers['status'] == 2){
				$firstKC =  pdo_fetch("SELECT id  FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY end  DESC  ");
				$MyKcList =  pdo_fetchall("SELECT id,name  FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY end  DESC  ");
			}else{
				$firstKC =  pdo_fetch("SELECT id  FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' and maintid = '{$it['tid']}' ORDER BY end  DESC ");
				$MyKcList = pdo_fetchall("select * FROM ".tablename($this->table_tcourse)." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and maintid = '{$it['tid']}'  And  FIND_IN_SET(xq_id,'{$nj_str}') ORDER BY end  DESC ");				
			}	
			if(!empty($_GPC['kc_id'])){
				$kc_id = intval($_GPC['kc_id']);			
			}else{
				$kc_id = $firstKC['id'];
			}
		
			$nowKC = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $kc_id));
			$title_name = $nowKC['name'];
			$thisKcStu = pdo_fetchall("SELECT distinct sid FROM " . tablename($this->table_coursebuy) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And kcid = '{$kc_id}' And is_change != 1 ORDER BY id DESC ");
			$bjallrs = count($thisKcStu);
			$bjxs = 0;
			$alljz =0;
			$Stu_str_temp = '';
			foreach($thisKcStu as $u){
				$Stu_str_temp .=$u['sid'].",";
				$checkuser = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where sid = :sid ", array(':sid' => $u['sid']));
				if($checkuser){
					$checkusers = pdo_fetchall("SELECT id FROM " . tablename($this->table_user) . " where sid = :sid ", array(':sid' => $u['sid']));
					$alljz = $alljz + count($checkusers);
					$bjxs++;
				}
			}
			$stu_str = trim($Stu_str_temp,",");
			$thistime = trim($_GPC['limit']);
			// var_dump($stu_str);
			if($thistime){
				$condition = " AND id < '{$thistime}'";	
				$leave1 = pdo_fetchall("SELECT id,s_name,numberid,qrcode_id,bj_id,sex,icon FROM " . tablename($this->table_students) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And FIND_IN_SET(id,'{$stu_str}') $condition ORDER BY id DESC LIMIT 0,10 ");
				foreach($leave1 as $key =>$row){
					$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
					$leave1[$key]['banji'] = $banji['sname'];
					$leave1[$key]['pard'] = pdo_fetchall("SELECT pard FROM ".tablename($this->table_user)." WHERE schoolid = '{$schoolid}' And sid = '{$row['id']}' ");
					$kcksinfo = GetRestKsBySid($kc_id,$row['id']);
					$leave1[$key]['yq'] = $kcksinfo['hasSign'] ? $kcksinfo['hasSign'] : 0;
					$leave1[$key]['buy'] = $kcksinfo['buycourse'] ? $kcksinfo['buycourse'] : 0;
					$leave1[$key]['rest'] = $kcksinfo['restnumber']> 0 ? $kcksinfo['restnumber'] : 0;
					if($leave1[$key]['pard']){
						foreach($leave1[$key]['pard'] as $k => $v){
							$leave1[$key]['pard'][$k]['pardid'] = $v['pard'];
							if($v['pard'] == 4){
								$leave1[$key]['pard'][$k]['guanxi'] = "本人";
							}else{
								$leave1[$key]['pard'][$k]['guanxi'] = get_guanxi($v['pard']);
							}
						}
					}
				}
				include $this->template('comtool/stulist'); 
			}else{
				$leave = pdo_fetchall("SELECT id,s_name,numberid,qrcode_id,bj_id,sex,icon FROM " . tablename($this->table_students) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And FIND_IN_SET(id,'{$stu_str}') ORDER BY id DESC LIMIT 0,10 ");
				
				foreach($leave as $key =>$row){
					$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
					$kcksinfo = GetRestKsBySid($kc_id,$row['id']);
					$leave[$key]['banji'] = $banji['sname'];
					$leave[$key]['pard'] = pdo_fetchall("SELECT pard FROM ".tablename($this->table_user)." WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And sid = '{$row['id']}' ");
					$leave[$key]['yq'] = $kcksinfo['hasSign'] ? $kcksinfo['hasSign'] : 0;
					$leave[$key]['buy'] = $kcksinfo['buycourse'] ? $kcksinfo['buycourse'] : 0;
					$leave[$key]['rest'] = $kcksinfo['restnumber']> 0 ? $kcksinfo['restnumber'] : 0;
					if($leave[$key]['pard']){
						foreach($leave[$key]['pard'] as $k => $v){
							$leave[$key]['pard'][$k]['pardid'] = $v['pard'];
							if($v['pard'] == 4){
								$leave[$key]['pard'][$k]['guanxi'] = "本人";
							}else{
								$leave[$key]['pard'][$k]['guanxi'] = get_guanxi($v['pard']);
							}
						}
					}
				}
				include $this->template(''.$school['style3'].'/stulist');	
			}
			
		}else{
		//公立学校模式
			
			//获取首次进入时的班级
			if(is_njzr($teachers['id'])){
				$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' And type = 'semester' and is_over != 2");
				$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And parentid = '{$myfisrtnj['sid']}' and is_over != 2");
			}else{
				$fisrtbj =  pdo_fetch("SELECT bj_id FROM " . tablename($this->table_class) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And tid = {$it['tid']} and FIND_IN_SET(bj_id,'{$bj_str}') ");
				if($teachers['status'] == 2){
					$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = 'theclass' and is_over != 2 ");
				}			
			}
			
			//接收选择的班级id
			if(!empty($_GPC['bj_id'])){
				$bj_id = intval($_GPC['bj_id']);			
			}else{
				$bj_id = $fisrtbj['bj_id'];
			}
			$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
			$title_name = $nowbj['sname'];
			if(is_njzr($teachers['id'])){
				$mynjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' and is_over != 2 ORDER BY ssort DESC");
				foreach($mynjlist as $key =>$row){
					$mynjlist[$key]['bjlist'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$row['sid']}' And type = 'theclass' and is_over != 2 ORDER BY sid ASC, ssort DESC");
					foreach($mynjlist[$key]['bjlist'] as $k => $v){

					}
				}
			}else{
				if($teachers['status'] == 2){
					$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' and is_over != 2  ORDER BY sid ASC, ssort DESC");
				}			
			}
			$thisbj = pdo_fetchall("SELECT id FROM " . tablename($this->table_students) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$bj_id}' ORDER BY id DESC ");
			$bjallrs = count($thisbj);
			$bjxs = 0;
			$alljz =0;
			foreach($thisbj as $u){
				$checkuser = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where sid = :sid ", array(':sid' => $u['id']));
				if($checkuser){
					$checkusers = pdo_fetchall("SELECT id FROM " . tablename($this->table_user) . " where sid = :sid ", array(':sid' => $u['id']));
					$alljz = $alljz + count($checkusers);
					$bjxs++;
				}
			}
			$thistime = trim($_GPC['limit']);
			if($thistime){
				$condition = " AND id < '{$thistime}'";	
				$leave1 = pdo_fetchall("SELECT id,s_name,numberid,qrcode_id,bj_id,sex,icon FROM " . tablename($this->table_students) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$bj_id}' $condition ORDER BY id DESC LIMIT 0,10 ");
				foreach($leave1 as $key =>$row){
					$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
					$leave1[$key]['banji'] = $banji['sname'];
					$leave1[$key]['pard'] = pdo_fetchall("SELECT pard FROM ".tablename($this->table_user)." WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And sid = '{$row['id']}' ");
					if($leave1[$key]['pard']){
						foreach($leave1[$key]['pard'] as $k => $v){
							$leave1[$key]['pard'][$k]['pardid'] = $v['pard'];
							if($v['pard'] == 4){
								$leave1[$key]['pard'][$k]['guanxi'] = "本人";
							}else{
								$leave1[$key]['pard'][$k]['guanxi'] = get_guanxi($v['pard']);
							}
						}
					}
				}
				include $this->template('comtool/stulist'); 
			}else{
				$leave = pdo_fetchall("SELECT id,s_name,numberid,qrcode_id,bj_id,sex,icon FROM " . tablename($this->table_students) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$bj_id}' ORDER BY id DESC LIMIT 0,10 ");
				foreach($leave as $key =>$row){
					$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
					$leave[$key]['banji'] = $banji['sname'];
					$leave[$key]['pard'] = pdo_fetchall("SELECT pard FROM ".tablename($this->table_user)." WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And sid = '{$row['id']}' ");
					if($leave[$key]['pard']){
						foreach($leave[$key]['pard'] as $k => $v){
							$leave[$key]['pard'][$k]['pardid'] = $v['pard'];
							if($v['pard'] == 4){
								$leave[$key]['pard'][$k]['guanxi'] = "本人";
							}else{
								$leave[$key]['pard'][$k]['guanxi'] = get_guanxi($v['pard']);
							}
						}
					}
				}
				include $this->template(''.$school['style3'].'/stulist');	
			}
		}		
        if(empty($it)){
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>