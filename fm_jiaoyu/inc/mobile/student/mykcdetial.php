<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid']; 
		$openid = $_W['openid'];
        $id = intval($_GPC['id']);
        $schoolid = intval($_GPC['schoolid']);
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_kcbiao) . " where schoolid = :schoolid AND id=:id", array(':schoolid' => $schoolid, ':id' => $id));
		$kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where schoolid = :schoolid AND id=:id", array(':schoolid' => $schoolid, ':id' => $item['kcid']));
        $school = pdo_fetch("SELECT title,style2 FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $item['schoolid'])); 
		$type = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where schoolid = :schoolid AND sid = :sid", array(':schoolid' => $schoolid, ':sid' => $item['sd_id']));	
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));	        
		$weekarray=array("日","一","二","三","四","五","六"); //先定义一个数组
		$nowweek = $weekarray[date("w",$item['date'])];
		if(!empty($it)){
            include $this->template(''.$school['style2'].'/mykcdetial');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }
		
?>