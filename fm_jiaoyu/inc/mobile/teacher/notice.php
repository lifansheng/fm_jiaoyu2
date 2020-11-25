<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $schooltype = $_W['schooltype'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$leaveid = $_GPC['id'];
		mload()->model('que');
        $userid = $it['id'];
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$school = pdo_fetch("SELECT style3,title,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$ZY_contents = GetZyContent($leaveid,$schoolid,$weid);	
			$tid_global = $it['tid'];	
        if(!empty($userid['id'])){
			$teacher = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where id = :id", array(':id' => $it['tid']));	
			$leave = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $leaveid));
			$is_njzr = check_bj($it['tid'],$leave['bj_id']);           
			$picarr = iunserializer($leave['picarr']);
			if($leave['usertype'] == 'send_class'){
				$userdatas = explode(',',$leave['userdatas']);
				$dataarr = array();
				foreach($userdatas as $row){
					if($row == 0 || $row != ""){
						$dataarr[] = intval($row);
					}	
				}
				mload()->model('stu');
				$arr = GetClassInfoByArr($dataarr,$schooltype,$schoolid);
			}
			if($leave['usertype'] == 'student'){
				mload()->model('stu');
				$datass = str_replace('&quot;','"',$leave['userdatas']);
				$userdatas = json_decode($datass,true);
				$stuarr = array();
				foreach($userdatas as $key => $row){
					$vals = explode(',',$row);
					if($schooltype){
						$bjinfo = pdo_fetch("SELECT name as sname FROM ".tablename($this->table_tcourse)." WHERE  id = '{$key}' And schoolid='{$schoolid}' ");
					}else{
						$bjinfo = pdo_fetch("SELECT sname FROM ".tablename($this->table_classify)." WHERE  sid = '{$key}' And schoolid='{$schoolid}' ");
					}
					$stuarr[$key]['bjname'] = $bjinfo['sname'];
					$stuarr[$key]['stulist'] = GetStuInfoByArr($vals,$schooltype,$schoolid);
				}
				$arr = $stuarr;
			}
			include $this->template(''.$school['style3'].'/notice');
		}else{
			session_destroy();
			$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
		}        
?>