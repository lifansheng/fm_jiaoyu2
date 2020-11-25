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
        $studentsid = intval($_GPC['sid']);
		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $_SESSION['user']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($it)){            
			$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $it['sid']));
			$qrinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_qrinfo) . " where id = :id", array(':id' => $students['qrcode_id']));
			//print_r($students);
			$overtime = true;
			$restday = floor(($qrinfo['expire']-time())/86400);
			if(time() > $qrinfo['expire']){
				$overtime = false;
			}
			$item = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid=:uid", array(':uid' => $it['uid'], ':uniacid' => $_W ['uniacid'])); 
       		$category = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid =:schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');
			$nowbj =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid AND schoolid =:schoolid", array(':sid' => $students['bj_id'], ':schoolid' => $schoolid));
       		if($students['keyid'] != 0 ){
				$bjlist = pdo_fetchall("SELECT bj_id FROM " . tablename($this->table_students) . " where keyid = :keyid And schoolid = :schoolid ORDER BY id ASC", array(':keyid' =>$students['keyid'], ':schoolid' => $schoolid));
				foreach($bjlist as $key => $vel){
					$bj =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid AND schoolid =:schoolid", array(':sid' => $vel['bj_id'], ':schoolid' => $schoolid));
					$bjlist[$key]['bjname'] = $bj['sname'];
				}
			}
		    include $this->template(''.$school['style2'].'/myinfo');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>