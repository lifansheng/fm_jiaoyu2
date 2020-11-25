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
		$leaveid = intval($_GPC['id']);
		$record_id = intval($_GPC['record_id']);
		
		mload()->model('que');
        //查询是否用户登录		
		$userid1 = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid1['id']));
		$tid_global = $it['tid'];
		$userid= $userid1['id'];
		$leave = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $leaveid));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$ZY_contents = GetZyContent($leaveid,$schoolid,$weid);
        if(!empty($userid1['id'])){
			$teacher = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
			$picarr = iunserializer($leave['picarr']);
			$thisteacher = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $leave['tid']));
			$recode = pdo_fetch("SELECT readtime,id FROM " . tablename($this->table_record) . " where schoolid = :schoolid And noticeid = :noticeid And tid = :tid And userid = :userid", array(':schoolid' => $schoolid,':noticeid' => $leaveid,':tid' => $it['tid'],':userid' => $it['id']));
			if ($recode){
				if($recode['readtime'] == 0){
					$date = array(
						'readtime' =>time()
					);
					pdo_update($this->table_record, $date, array('id' => $recode['id']));				
				}			
			}else{
				$data = array(
					'weid' =>  $weid,
					'schoolid' => $schoolid,
					'noticeid' => $leaveid,
					'tid' => $it['tid'],
					'userid' => $it['id'],
					'openid' => $openid,
					'type' => $leave['type'],
					'createtime' => $leave['createtime'],
					'readtime' =>time()
				);
				pdo_insert($this->table_record, $data);		
			}
			$userdatas = explode(';',$leave['userdatas']);
			$dataarr = array();
			foreach($userdatas as $row){
				if($row == 0 || $row != ""){
					$dataarr[] = intval($row);
				}	
			}			
			if($leave['usertype'] == 'send_class'){
				mload()->model('stu');
				$arr = GetClassInfoByArr($dataarr,$_W['schooltype'],$schoolid);
			}	
			if($leave['usertype'] == 'student'){
				mload()->model('stu');
				$arr = GetStuInfoByArr($dataarr,$_W['schooltype'],$schoolid);
			}
			if($leave['usertype'] == 'staff_jsfz'){
				mload()->model('tea');
				$arr = GetFzInfoByArr($dataarr,$_W['schooltype'],$schoolid);
			}
			if($leave['usertype'] == 'staff'){
				mload()->model('tea');
				$arr = GetTeaInfoByArr($dataarr,$_W['schooltype'],$schoolid);	
			}	
			$testAA = GetMyAnswerAll_tea($it['tid'] ,$leaveid,$schoolid,$weid);
		    include $this->template(''.$school['style3'].'/mnotice');
        }else{
            session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>