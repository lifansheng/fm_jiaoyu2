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
        $obid = 1;
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT title,style3,spic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));		
		$thistime = trim($_GPC['limit']);
		$tid_global = $it['tid'];
		if($thistime){
			$condition = " AND createtime < '{$thistime}'";	
		    $leave1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And type=1 {$condition} ORDER BY createtime DESC LIMIT 0,5 ");			
			foreach($leave1 as $key =>$row){
				$teach = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['totid']));
				$stu= pdo_fetch("SELECT sid,pard FROM ".tablename($this->table_user)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $weid, ':id' => $row['fromuserid'], ':schoolid' => $schoolid));
				$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $stu['sid']));
				$guanxi = "本人";
				if($stu['pard'] == 2){
					$guanxi = "妈妈";
				}else if($stu['pard'] == 3) {
					$guanxi = "爸爸";
				}else if($stu['pard'] == 5) {
					$guanxi = "家长";
				}
				$leave1[$key]['tname'] = $teach['tname'];
				$leave1[$key]['sname'] = $students['s_name'].$guanxi;
				$leave1[$key]['s_icon'] = !empty($students['icon'])?$students['icon']:$school['spic'];
				$leave1[$key]['time'] = date('Y-m-d H:i', $row['createtime']);	
			}
			include $this->template('comtool/tyzxx'); 
		}else{
		    $leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And type=1 ORDER BY createtime DESC LIMIT 0,5 ");
			foreach($leave as $key =>$row){
				$teach = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['totid']));
				$stu= pdo_fetch("SELECT sid,pard FROM ".tablename($this->table_user)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $weid, ':id' => $row['fromuserid'], ':schoolid' => $schoolid));
				$students = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $stu['sid']));
				$guanxi = "本人";
				if($stu['pard'] == 2){
					$guanxi = "妈妈";
				}else if($stu['pard'] == 3) {
					$guanxi = "爸爸";
				}else if($stu['pard'] == 5) {
					$guanxi = "家长";
				}
				$leave[$key]['tname'] = $teach['tname'];
				$leave[$key]['sname'] = $students['s_name'].$guanxi;
				$leave[$key]['s_icon'] = !empty($students['icon'])?$students['icon']:$school['spic'];
				$leave[$key]['time'] = date('Y-m-d H:i', $row['createtime']);	
			} 
			include $this->template(''.$school['style3'].'/tyzxx');	
		}	
		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }       








?>