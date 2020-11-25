<?php
 /**
  * 公物领用
  * @copyright 2019 微美科技
  * @author Hannibal·Lee <No@email.com>
  */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . GetTableName('user'). " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid_global = $it['tid'];
		 
		$school = pdo_fetch("SELECT * FROM " . GetTableName('index') . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));  
        if(!empty($userid['id'])){
             
			include $this->template(''.$school['style3'].'/assetstake');
        }else{
			$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>