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
$id = intval($_GPC['id']);

//查询是否用户登录
$it = pdo_fetch("SELECT id,tid FROM " . tablename($this->table_user) . " where  weid = :weid And schoolid = :schoolid And openid = :openid And sid = :sid ", array(
			':weid' => $weid,
			':schoolid' => $schoolid,
			':openid' => $openid,
			':sid' => 0
));	
$school = pdo_fetch("SELECT style3,spic,tpic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$thisleave = pdo_fetch("SELECT userid,touserid FROM " . tablename($this->table_leave) . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $id));
$teachers = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
// p($thisleave);
if(!empty($it)){
	$img_url = array();
	$iii = 0 ;
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where weid = :weid AND leaveid = :leaveid ORDER BY createtime ASC ", array(':weid' => $weid, ':leaveid' => $id));	
	foreach ($list as $k => $v) {
		
		if(!empty($v['picurl'])){
			$img_url[$iii] = tomedia($v['picurl']);
			$iii = $iii + 1 ;
		}
		if($v['userid'] == $it['id']){
			$users = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $v['touserid']));
		}
		if($v['touserid'] == $it['id']){
			$users = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $v['userid']));
			if($v['isread'] ==1){
				pdo_update($this->table_leave, array('isread' =>  2), array('id' =>  $v['id']));
			}
		}	
		$students = pdo_fetch("SELECT s_name,icon,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $users['sid']));
		$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $users['tid']));
		mload()->model('user');
		$gx = check_gx($users['pard']);
		if($users['userinfo']){
			$userinfo = iunserializer($users['userinfo']);
			$name = $userinfo['name'];
			$guanxi = empty($gx)?'':$gx.$name;
		}
		//p($students);
		$list[$k]['time'] = sub_day($v['createtime']);
		if ($users['sid']){
			$nowbji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid", array(':sid' => $students['bj_id']));
			$list[$k]['name'] = $students['s_name'].$guanxi.'('.$nowbji['sname'].')';
			$list[$k]['icon'] = empty($students['icon']) ? $school['spic'] : $students['icon'];					
		}else{
			$list[$k]['name'] = $teacher['tname']." 老师";
			$list[$k]['icon'] =  empty($teacher['thumb']) ? $school['tpic'] : $teacher['thumb'];					
		}
		if(!empty($v['audio'])){
			$audios = iunserializer($v['audio']);
			$list[$k]['audios'] = $audios['audio'][0];
			$list[$k]['audioTime'] = $audios['audioTime'][0];
		}				
	}
	$img_url_de = json_encode($img_url);
	$lasttime = pdo_fetch("SELECT id,createtime FROM " . tablename($this->table_leave) . " where weid = :weid AND leaveid = :leaveid ORDER BY createtime DESC ", array(':weid' => $weid, ':leaveid' => $id));			
	include $this->template(''.$school['style3'].'/tduihua');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
