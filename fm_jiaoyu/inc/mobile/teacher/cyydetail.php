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
		$cyyid = intval($_GPC['id']);
		$record_id = intval($_GPC['record_id']);
		mload()->model('que');
        
        //查询是否用户登录		
		$userid1 = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid1['id']));
		$tid_global = $it['tid'];
		$userid= $userid1['id'];
		$cyyinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_courseorder) . " where :id = id", array(':id' => $cyyid));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		if(!empty($cyyinfo['kcid'])){
			$course = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where :id = id", array(':id' => $cyyinfo['kcid'])); 
		}else{
			$course['name'] = "未指定课程";
		}
		//教师跟进情况
		$jsgj = pdo_fetchall("SELECT * FROM " . tablename($this->table_cyybeizhu_teacher) . " where weid = :weid AND schoolid=:schoolid  and cyyid = :cyyid ORDER BY createtime ASC ", array(':weid' => $weid, ':schoolid' => $schoolid,':cyyid'=>$cyyid));
		foreach( $jsgj as $key => $value )
		{
			$teacherinfo = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $value['tid']));
			$jsgj[$key]['tname'] = $teacherinfo['tname'];
		}
        if(!empty($userid1['id'])){
			$teacher = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
			$picarr = iunserializer($leave['picarr']);
			$thisteacher = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $leave['tid']));
		    include $this->template(''.$school['style3'].'/cyydetail');
        }else{
            session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>