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
$thisleave = pdo_fetch("SELECT userid,touserid,openid,leaveid FROM " . GetTableName('psychology') . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $id));
$GLid = $thisleave['leaveid'];
$toopenid = $thisleave['openid'];
$teachers = pdo_fetch("SELECT thumb FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
if(!empty($it)){
	$img_url = array();
    $iii = 0 ;	
    $list = pdo_fetchall("SELECT * FROM " . GetTableName('psychology') . " where weid = :weid AND schoolid = :schoolid AND leaveid = :leaveid ORDER BY createtime ASC ", array(':weid' => $weid, ':schoolid' => $schoolid, ':leaveid' => $GLid));
	foreach ($list as $k => $v) {
		if(!empty($v['picurl'])){
			$img_url[$iii] = tomedia($v['picurl']);
			$iii = $iii + 1 ;
		}
		if($v['touserid'] == $it['id']){
			$users = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $v['touserid']));
			if($v['isread'] ==1){
				pdo_update(GetTableName('psychology',false), array('isread' =>  2), array('id' =>  $v['id']));
			}
		}	
		
		$teacher = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $users['tid']));
		$list[$k]['time'] = sub_day($v['createtime']);
		if (!empty($v['userid'])){
            $list[$k]['name'] = $teacher['tname']." 老师";
			$list[$k]['icon'] =  empty($teacher['thumb']) ? $school['tpic'] : $teacher['thumb'];	
		}else{
			$fans_info = mc_fansinfo($toopenid);
            $name = $fans_info['nickname'];
            $icon = $fans_info['headimgurl'];
			$list[$k]['name'] = $name;
			$list[$k]['icon'] = $icon;						
		}
		if(!empty($v['audio'])){
			$audios = iunserializer($v['audio']);
			$list[$k]['audios'] = $audios['audio'][0];
			$list[$k]['audioTime'] = $audios['audioTime'][0];
		}				
	}
	$img_url_de = json_encode($img_url);
    $lasttime = pdo_fetch("SELECT id,createtime FROM " . GetTableName('psychology') . " where weid = :weid AND schoolid = :schoolid AND leaveid = :leaveid ORDER BY createtime DESC ", array(':weid' => $weid, ':schoolid' => $schoolid, ':leaveid' => $thisleave['leaveid']));
	include $this->template(''.$school['style3'].'/psychology/tfanspsychology');
}else{
	session_destroy();
	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
	header("location:$stopurl");
}
