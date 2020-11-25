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
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		//获取基本信息
		if(empty($_GPC['userid'])){
			$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		}elseif(!empty($_GPC['userid'])){
			$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_GPC['userid']));
		}
				
$thistime = trim($_GPC['limit']);
		if($thistime){
			$condition = " AND createtime < '{$thistime}'";	
		    $leave1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And type=1 AND fromuserid = '{$it['id']}' {$condition} ORDER BY createtime DESC LIMIT 0,5 ");			
			foreach($leave1 as $key =>$row){
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
				$leave1[$key]['sname'] = $students['s_name'].$guanxi;
				$leave1[$key]['tname'] = $teach['tname'];
				$leave1[$key]['thumb'] = $teach['thumb'];

			}
			include $this->template('comtool/syzxx'); 
		}else{
		    $leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And type=1  AND fromuserid = '{$it['id']}' ORDER BY createtime DESC LIMIT 0,5 ");
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
				$leave[$key]['sname'] = $students['s_name'].$guanxi;
				$leave[$key]['tname'] = $teach['tname'];
				$leave[$key]['thumb'] = $teach['thumb'];
				
			} 
			include $this->template(''.$school['style2'].'/syzxx');	
		}	
?>

