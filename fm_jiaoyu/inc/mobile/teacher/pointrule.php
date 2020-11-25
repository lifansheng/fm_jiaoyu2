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
        $type = intval($_GPC['op']);
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid = $it['tid'];
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));		
	    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_points) . " where weid = :weid AND schoolid=:schoolid  And type = :type And is_on = '1' ORDER BY id ASC " , array(':weid' => $weid, ':schoolid' => $schoolid,':type'=>$type));
	    if($type == 2 )
	    {
		    foreach( $list as $key => $value )
		    {
		    	$back = CheckMissionFinished($it['tid'],$value['id']);
		    	$list[$key]['back'] = $back;
		    }
	    }
			
	    include $this->template(''.$school['style3'].'/pointrule');	
			
		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }       








?>