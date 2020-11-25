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
        $getMy = $_GPC['getMy'];
   		if(!empty($_SESSION['user'])){
       		$userss = $_SESSION['user'];
   		}
   		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where openid = :openid AND schoolid=:schoolid   AND weid=:weid", array(':openid' => $openid,':schoolid'=> $schoolid,':weid'=> $weid ));
   		if(!empty($it)){
	   		
	   	
   		}else{
	       	session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
       		}
	       
     
        //查询是否用户登录	
       	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$thistime = trim($_GPC['limit']);
		if($thistime){
			$condition = " AND createtime < '{$thistime}'";	
			$mygalist1 = array();
			//var_dump($condition);
			$galisttemp =  pdo_fetchall("SELECT * FROM " . tablename($this->table_groupactivity) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  AND type !=1  $condition  ORDER BY createtime DESC LIMIT 0,10 ");
	
	      		$mygalist1 = $galisttemp;
			include $this->template('comtool/horder');	 
		}else{

			$mygalist = array();
			$iii = 0 ;
			$galisttemp =  pdo_fetchall("SELECT * FROM " . tablename($this->table_groupactivity) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  AND type !=1  ORDER BY createtime DESC LIMIT 0,10 ");
		
	      		$mygalist = $galisttemp;
			include $this->template(''.$school['style1'].'/horder');	
		}				        		
           
?>