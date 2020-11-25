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
		if (!empty($_GPC['userid'])){
			$_SESSION['user'] = $_GPC['userid'];
		}
		//查询是否用户登录		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($it)){
            $leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where schoolid = :schoolid AND sid = :sid And tid = 0 And isliuyan = 0 ORDER BY id DESC", array(
				':schoolid' => $schoolid,
				':sid' => $it['sid']
            ));

			$thisid = 1;
			foreach($leave as $key => $row){
                $leave[$key]['isshowopera'] = false;
				// $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where uid = :uid And openid = :openid And sid = :sid ", array(':uid' => $row['uid'],':openid' => $row['openid'],':sid' => $row['sid']));
				$user = pdo_fetch("SELECT pard FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
				$student = pdo_fetch("SELECT * FROM " . tablename ($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $weid, ':id' => $row['sid']));
				if($user['pard'] ==2){
					$leave[$key]['guanxi'] = "妈妈";
				}
				if($user['pard'] ==3){
					$leave[$key]['guanxi'] = "爸爸";
				}
				if($user['pard'] ==4){
                    $leave[$key]['isshowopera'] = true;
					$leave[$key]['guanxi'] = "本人";
				}
				if($user['pard'] ==5){
					$leave[$key]['guanxi'] = "家长";
				}
				if(!$row['cltid']){
					$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $bzr['tid'], ':schoolid' => $schoolid));
					$leave[$key]['tname'] = $teacher['tname'];
					$leave[$key]['thumb'] = $teacher['thumb'];					
				}else{
					$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id AND schoolid = :schoolid ", array(':id' => $row['cltid'], ':schoolid' => $schoolid));
					$leave[$key]['tname'] = $teacher['tname'];
					$leave[$key]['thumb'] = $teacher['thumb'];
				}
				$leave[$key]['s_name'] = $student['s_name'];
				$leave[$key]['key'] = $thisid;
				$thisid ++;
			}
			if (empty($_W['setting']['remote']['type'])) {
				$urls = "http://severwm.oss-cn-shenzhen.aliyuncs.com/"; 
			} else {
				$urls = "http://severwm.oss-cn-shenzhen.aliyuncs.com/"; 
            }
			include $this->template(''.$school['style2'].'/smssagetopard');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>