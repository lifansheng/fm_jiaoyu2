<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        $schooltype  = $_W['schooltype'];
		$senceid = $_GPC['senceid'];
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
		$senceinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_upsence) . " where weid = '{$weid}' AND id= '{$senceid}'");
		$fzinfo = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND sid= '{$senceinfo['qxfzid']}'"); 
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
		$school = pdo_fetch("SELECT is_fbnew,style3,title,txid,txms,is_fbnew,is_fbvocie FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($userid['id'])){
			$time = time();
			$nowtiem = date('m月d日',$time);
			$nowday = $nowtiem.'('.get_week($time).')';
			$bjlist = get_mylist($schoolid,$it['tid'],'teacher');
			include $this->template(''.$school['style3'].'/tuploadsence');
		}else{
	        session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
		}        
?>