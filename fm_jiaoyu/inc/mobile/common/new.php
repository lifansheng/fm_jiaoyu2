<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $this->weid;
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$id = $_GPC['id'];
//查询是否用户登录		
$item = pdo_fetch("SELECT * FROM " . tablename($this->table_news) . " where :id = id", array(':id' => $id));
//获取用户点赞信息
if($item['isopendz'] == 1){
	$articledz = pdo_fetchAll("SELECT openid,id FROM ".GetTableName('articledz')." WHERE a_id='{$id}' AND status = 1");
	foreach ($articledz as $key => $value) {
		$articledz[$key] =  mc_fansinfo($value['openid']);
		$articledz[$key]['id'] =  $value['id'];
	}
}
if($item['isopenpl'] == 1){
	$articlepl = pdo_fetchAll("SELECT id,content,createtime,openid,status FROM ".GetTableName('articlepl')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND a_id = '{$id}' AND status = 1 ORDER BY createtime DESC");
	foreach ($articlepl as $key => $value) {
		$mc_fansinfo =  mc_fansinfo($value['openid']);
		$articlepl[$key]['nickname'] =  $mc_fansinfo['nickname'];
		$articlepl[$key]['avatar'] =  $mc_fansinfo['avatar'];
		$articlepl[$key]['id'] =  $value['id'];
		$articlepl[$key]['content'] =  $value['content'];
		$articlepl[$key]['status'] =  $value['status'];
		$articlepl[$key]['createtime'] =  $value['createtime'];
	}
}

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$click =$item['click'] + 1;
$temp = array(
	'click' => $click
);
$sharetitle = $item['title'];
$sharedesc = $item['description'];
$shareimgUrl = tomedia($item['thumb']);
$links = $_W['siteroot'] .'app/'.$this->createMobileUrl('new', array('schoolid' => $schoolid,'id' => $item['id']));
pdo_update($this->table_news, $temp, array('id' => $item['id']));			
$picarr = iunserializer($item['picarr']);
include $this->template(''.$school['style1'].'/new');       
?>