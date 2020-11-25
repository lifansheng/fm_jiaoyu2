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
		$userss = !empty($_GPC['userid']) ? intval($_GPC['userid']) : 1;
		$id = intval($_GPC['id']);
		$scid = $_GPC['scid'];
		$setid = $_GPC['setid'];		
		$obid = 2;
		$gkkid = $_GPC['gkkid'];
		$userid = $_GPC['userid'];

		$gkkinfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " where id = :id And schoolid = :schoolid ", array(':id' => $gkkid,':schoolid' => $schoolid));
		
        //查询是否用户登录
		mload()->model('user');
		$_SESSION['user'] = check_userlogin_all($weid,$schoolid,$openid,$userss);
		if ($_SESSION['user'] ==2){
			include $this->template('bangding');
		}

		
		$alluser = get_myalluser($weid,$openid,$schoolid);
		//var_dump($alluser);
		$myname = array();
		foreach($alluser as $key=>$row)
		{
			if($row['id'] == $userid){
				$myname['type'] = $row['type'];
				if($row['type'] == 1)
				{
					$myname['name'] = $row['s_name'];
					
				}else{
					$myname['name'] = $row['tname'];
				}
			}
		}
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And openid = :openid AND id=:id ", array(':schoolid' => $schoolid,':openid' => $openid, ':id' => $userid));
		$pard = get_guanxi($it['pard']);
		if(!$pard){
			$pard = '本人';
		}
		
		
		$it = pdo_fetch("SELECT id,sid FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));	
		$school = pdo_fetch("SELECT style1,title,spic,tpic,title,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));





		$student = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where id = :id", array(':id' => $it['sid']));
        if(!empty($it)){


			include $this->template(''.$school['style1'].'/goodstemp');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>