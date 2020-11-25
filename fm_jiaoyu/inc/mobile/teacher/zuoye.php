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
  		$ZY_contents = GetZyContent($leaveid,$schoolid,$weid);
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$leave = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $leaveid));
		$anstype = iunserializer($leave['anstype']);
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		if($schooltype){
			$kcinfo =  pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " WHERE id =:id ", array(':id' => $leave['kc_id']));
			$kc_id = $leave['kc_id'];
			$allstud = pdo_fetchall("SELECT students.id,students.s_name,students.icon FROM " . GetTableName('order') . " as stuorder , " . GetTableName('students') . " as students where stuorder.schoolid = '{$schoolid}' And stuorder.kcid = '{$kc_id}' and stuorder.status = 2 and stuorder.type = 1 and stuorder.sid = students.id group by students.id  ORDER BY students.id DESC ");
		}else{
			$nowbj =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid =:sid ", array(':sid' => $leave['bj_id']));
			$nowkm =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid =:sid ", array(':sid' => $leave['km_id']));	
			$allstud = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':schoolid' => $schoolid, ':bj_id' => $leave['bj_id']));
		}
        if(!empty($userid['id'])){
			$teacher = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where id = :id", array(':id' => $it['tid']));	
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
			include $this->template(''.$school['style3'].'/zuoye');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>