<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $schoolid = intval($_GPC['schoolid']);
        
       	$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $_SESSION['user']));	
		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
		}
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id= :id", array(':weid' => $weid, ':id' => $schoolid));
        $title = $school['title'];

		
        if (empty($school)) {
	       
            message('参数错误');
        }
        include $this->template(''.$school['style1'].'/yzxx');
?>