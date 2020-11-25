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
		$logid = trim($_GPC['logid']);
		if (!empty($_GPC['userid'])){
			$_SESSION['user'] = $_GPC['userid'];
		}
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id And openid = :openid", array(':id' => $_SESSION['user'],':openid' => $openid));
		$schoolset = pdo_fetch("SELECT twqswitch FROM " . tablename($this->table_schoolset) . " where weid = :weid AND schoolid=:schoolid", array(':weid' => $weid, ':schoolid' => $schoolid));
		$is_sure_kq = pdo_fetch("SELECT is_sure_kq FROM " . tablename($this->table_schoolset) . " where weid = :weid AND schoolid=:schoolid AND is_sure_kq = :is_sure_kq", array(':weid' => $weid, ':schoolid' => $schoolid, ':is_sure_kq' => 1));
		$school = pdo_fetch("SELECT title,logo,style2,spic,is_showad FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($it)){
			$student = pdo_fetch("SELECT icon,s_name FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));
			$log = pdo_fetch("SELECT * FROM " . tablename($this->table_checklog) . " where id = :id AND schoolid = :schoolid ", array(':id' => $logid, ':schoolid' => $schoolid));
			pdo_update($this->table_checklog, array('isread' => 2), array('id' => $logid));	
			$classid = $log['bj_id'];
			$class= pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :id AND schoolid = :schoolid ", array(':id' => $classid, ':schoolid' => $schoolid));
			$mac = pdo_fetch("SELECT name FROM " . tablename($this->table_checkmac) . " WHERE id = {$log['macid']} ");
			if (empty($_W['setting']['remote']['type'])) { 
				$urls = "http://severwm.oss-cn-shenzhen.aliyuncs.com/"; 
			} else {
				$urls = "http://severwm.oss-cn-shenzhen.aliyuncs.com/"; 
			}
			include $this->template(''.$school['style2'].'/checklogdetail');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>