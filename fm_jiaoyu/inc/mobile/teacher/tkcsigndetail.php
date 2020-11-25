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
    $obid = 1;
    $checktid = $_GPC['id'];
    //var_dump($_GPC);
    //查询是否用户登录		
	$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
	$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
	$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));		
	$tea_show = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $checktid));	
	$gxnj = getallnj($teachers['id']);
	$nj_temp = '';
	foreach( $gxnj as $key_n => $value_n )
	{
		$nj_temp .= $value_n['sid'].",";
	}
	$nj_after =trim($nj_temp,","); 
	
	
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
			
			
	if(!empty($_GPC['limit'])){
		$limit = trim($_GPC['limit']);
		$page_start = $limit + 1 ;
		if($teachers['status'] != 2){
		   	$kclist = pdo_fetchall("SELECT id,name,tid,start,end,adrr,bj_id,xq_id,OldOrNew FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And( tid like '%,{$checktid},%' or  tid like '{$checktid},%' or tid like '%,{$checktid}' or tid = {$checktid})  And FIND_IN_SET(xq_id,'{$nj_after}') and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}') ORDER BY end DESC LIMIT ".$page_start.",5");
	   }elseif($teachers['status'] ==2){
	    	$kclist = pdo_fetchall("SELECT id,name,tid,start,end,adrr,bj_id,xq_id,OldOrNew FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And( tid like '%,{$checktid},%' or  tid like '{$checktid},%' or tid like '%,{$checktid}' or tid = {$checktid}) and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}')  ORDER BY end DESC LIMIT ".$page_start.",5");
	   }
	   	
		foreach( $kclist as $key => $value )
		{
			$signNum = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_kcsign) . " WHERE schoolid = {$schoolid} And weid = {$weid} And tid={$checktid} and kcid={$value['id']}");
			$kclist[$key]['signNum'] = $signNum;
			$kclist[$key]['localtion'] = $page_start + $key;
			$t_array = explode(',',$value['tid']);
			$tname_array = ' ';
			foreach( $t_array as $key_t => $value_t )
			{
				$teacher_all =  pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid,':id' => $value_t));	
				$tname_array.=$teacher_all['tname']."/";
			}
			$kclist[$key]['alltname'] = trim($tname_array,"/");
		}
		include $this->template('comtool/tkcsigndetail'); 
	}elseif(empty($_GPC['limit'])){
			if($teachers['status'] != 2){
		   	$kclist = pdo_fetchall("SELECT id,name,tid,start,end,adrr,bj_id,xq_id,OldOrNew FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And( tid like '%,{$checktid},%' or  tid like '{$checktid},%' or tid like '%,{$checktid}' or tid = {$checktid}) And FIND_IN_SET(xq_id,'{$nj_after}') and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}') ORDER BY end DESC LIMIT 0,5");
		   }elseif($teachers['status'] ==2){
		    	$kclist = pdo_fetchall("SELECT id,name,tid,start,end,adrr,bj_id,xq_id,OldOrNew FROM " . tablename($this->table_tcourse) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And( tid like '%,{$checktid},%' or  tid like '{$checktid},%' or tid like '%,{$checktid}' or tid = {$checktid}) and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}')   ORDER BY end DESC LIMIT 0,5");
		    	//var_dump($kclist);
		   }
			
		foreach( $kclist as $key => $value )
		{
			$signNum = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_kcsign) . " WHERE schoolid = {$schoolid} And weid = {$weid} And tid={$checktid} and kcid={$value['id']}");
			$kclist[$key]['signNum'] = $signNum;
			$t_array = explode(',',$value['tid']);
			$tname_array = ' ';
			foreach( $t_array as $key_t => $value_t )
			{
				$teacher_all =  pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid,':id' => $value_t));	
				$tname_array.=$teacher_all['tname']."/";
			}
			$kclist[$key]['alltname'] = trim($tname_array,"/");
		}
		include $this->template(''.$school['style3'].'/tkcsigndetail');	
	}	
	if(empty($it)){
		session_destroy();
	    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
    }       
?>