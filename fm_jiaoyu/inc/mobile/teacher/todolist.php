<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $this->weid;
        $from_user = $this->_fromuser;
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');$usertemp = 'http://wmeiapi-session.stor.sinaapp.com';$MODOLE_URL = $usertemp;
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$tid_global = $it['tid'];
		$category = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid =  '{$_W['uniacid']}' AND schoolid ={$schoolid} ORDER BY sid ASC, ssort DESC", array(':weid' => $_W['uniacid'], ':schoolid' => $schoolid), 'sid');
		
        if (!empty($category)) {
            $children = [];
            foreach ($category as $cid => $cate) {
                if (!empty($cate['parentid'])) {
					if(count($children[$cate['parentid']]) == 0){
						$children[$cate['parentid']] = [];
					}
                    $children[$cate['parentid']][$cate['sid']] = array($cate['sid'], $cate['sname']);
                }
            }
        }
        if(!empty($userid['id'])){
            $isxz = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
            $is_njzr = is_njzr($it['tid']);
          	if($_GPC['op'] == 'scroll'){
	          	$thistime  = $_GPC['limit'];
	          	$condition = " AND acttime < '{$thistime}'";
	          	$leave1    = pdo_fetchall("SELECT * FROM " . tablename($this->table_todo) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And ((fsid = ' {$it['tid']}' ) or (  jsid = ' {$it['tid']}' ) or (  zjid = ' {$it['tid']}' ) ) $condition  ORDER BY acttime DESC  LIMIT 0,3");
	          	$xzlist    = pdo_fetchall("SELECT tname,openid,id FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid ", array(':weid' => $weid, ':schoolid' => $schoolid));
          		if(!$is_njzr && $isxz['status'] == 2){
	          		$leave1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_todo) . " where schoolid = '{$schoolid}' And weid = '{$weid}' $condition  ORDER BY acttime DESC  LIMIT 0,3 ");
	            }
	           	foreach($leave1 as $key => $row){
				   $teacher   = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['jsid']));
				   $fsteacher = pdo_fetch("SELECT thumb,tname,status FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['fsid']));
				   $leave1[$key]['photoarray'] = unserialize($row['picurls']);
				   if(!empty($teacher['thumb'])){
					   $leave1[$key]['jsicon']  = $teacher['thumb'];
				    }else{
					   $leave1[$key]['jsicon']  = $school['tpic'];
				    }
			    	$leave1[$key]['avatar'] = $school['logo'];
				   	$leave1[$key]['jstname'] = $teacher['tname'];
				   	$leave1[$key]['tname']   = $fsteacher['tname'];
				    $is_njzr = is_njzr($row['fsid']);
				    if($fsteacher['status'] == 2  && !$is_njzr ){
					    $leave1[$key]['is_xz'] ='校长'; 
				    }elseif($is_njzr && $fsteacher['status'] != 2){
					    $leave1[$key]['is_xz'] ='年级主任';  
				    }
				   if(!empty($row['zjid'])){
					    $teacher_zj = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['zjid']));
					    if(!empty($teacher['thumb'])){
					   		$leave1[$key]['zjicon'] = $teacher_zj['thumb'];
						}else{
							$leave1[$key]['clicon'] = $school['tpic'];
				    	}
					    $leave1[$key]['zjtname'] = $teacher_zj['tname'];
				   	}
			   	}		
		 		include $this->template('comtool/todolist');	
          	}else{
		          	$leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_todo) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And ((fsid = ' {$it['tid']}' ) or (  jsid = ' {$it['tid']}' ) or (  zjid = ' {$it['tid']}' ) )  ORDER BY acttime DESC  LIMIT 0,3");
			    	$xzlist = pdo_fetchall("SELECT tname,openid,id FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid ", array(':weid' => $weid, ':schoolid' => $schoolid));
				  	if(!$is_njzr && $isxz['status'] == 2){
			             $leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_todo) . " where schoolid = '{$schoolid}' And weid = '{$weid}'   ORDER BY acttime DESC LIMIT 0,3");
		            }
	           foreach($leave as $key => $row){
				   $teacher = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['jsid']));
				   $fsteacher =  pdo_fetch("SELECT thumb,tname,status FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['fsid']));
				   $leave[$key]['photoarray'] = unserialize($row['picurls']);
				   if(!empty($teacher['thumb'])){
					   $leave[$key]['jsicon'] = $teacher['thumb'];
				   	}else{
					   $leave[$key]['jsicon'] = $school['tpic'];
				   	}
				   	$leave[$key]['avatar'] = $school['logo'];
				    $leave[$key]['jstname'] = $teacher['tname'];
				    $leave[$key]['tname'] = $fsteacher['tname'];
				    $is_njzr = is_njzr($row['fsid']);
				    if($fsteacher['status'] == 2  && !$is_njzr ){
					    $leave[$key]['is_xz'] ='校长'; 
				    }elseif($is_njzr && $fsteacher['status'] != 2){
					    $leave[$key]['is_xz'] ='年级主任';  
				    }
				   if(!empty($row['zjid'])){
					    $teacher_zj = pdo_fetch("SELECT thumb,tname FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id = :id ", array(':schoolid' => $schoolid,':id' => $row['zjid']));
					    if(!empty($teacher['thumb'])){
					   		$leave[$key]['zjicon'] = $teacher_zj['thumb'];
						}else{
							$leave[$key]['clicon'] = $school['tpic'];
				    	}
					    $leave[$key]['zjtname'] = $teacher_zj['tname'];
				   	}
			   	}		
		 		include $this->template(''.$school['style3'].'/todolist');
          	}
      	}else{
         	session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
      	}        
?>