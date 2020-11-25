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
$njid = $_GPC['njid'];
//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid_global = $it['tid'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
mload()->model('tea');
$qh = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$qhid}' ");
$njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$njid}'")['sname'];
$bjlist = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and parentid = '{$njid}' ORDER BY sid DESC");
$date = strtotime(date("Y-m-d",time()));
foreach ($bjlist as $key => $value) {
    $IsDone = pdo_fetch("SELECT id FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND bjid = '{$value['sid']}' AND date = '{$date}' ");
    $bjlist[$key]['IsDone'] = $IsDone ? true : false;
}
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
if($op == 'getscorelist'){
    $bjid = $_GPC['bjid'];
    $date = strtotime(date("Y-m-d",time()));
    $list = pdo_fetchAll("SELECT id,type,title,addition FROM " . GetTableName('ddscorecategory') .  " WHERE schoolid = :schoolid and FIND_IN_SET('{$tid_global}',tid)  ORDER BY addition,ssort DESC", array(':schoolid' => $_GPC['schoolid']));
    $BjList = [];
    $Bzrist = [];

    foreach ($list as $key => &$value) {
        $score = pdo_fetch("SELECT score,remark FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND bjid = '{$bjid}' AND date = '{$date}' AND cid = '{$value['id']}' ");
        if(!empty($score)){
            $value['score'] = $score['score'];
        }else{
            $value['score'] = 0;
        }
        $value['remark'] = $score['remark'];
        if($value['type'] == 2){ 
            $Bzrist[] = $value;
        }else {
            $BjList[] = $value;
        }
    }
    $bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND sid = '{$bjid}'")['sname'];
    $IsDone = false;
    if(!empty($score)){
        $IsDone = true;
    }
    include $this->template(''.$school['style3'].'/ddscoredetail_bot');
    die();
}elseif($op == 'display'){
    if(!empty($userid['id'])){
        include $this->template(''.$school['style3'].'/tddscoredetail');
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
    }   
}elseif($op == 'save'){
    $Score = $_GPC['score'];
	$bjid = $_GPC['bjid'];
    $date = strtotime(date("Y-m-d",time())); 
	foreach($Score as $cid => $value_s){
		$insertData = array(
			'weid' => $_GPC['weid'],
			'schoolid' => $_GPC['schoolid'],
			'bjid' => $bjid,
			'score' => $value_s,
			'remark' => $_GPC['remark'][$cid],
			'cid' => $cid,
			'createtime' => time(),
			'date' => $date,
			'tid' => intval($tid_global) <= 0 ? -1 : $tid_global 
		);
		$check = pdo_fetch("SELECT id FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' and bjid = '{$bjid}' and date = '{$date}' and cid = '{$cid}'");
		if(!empty($check)){
			pdo_update(GetTableName('ddscorelog',false),$insertData,array('id' => $check['id']));
		}else{
			pdo_insert(GetTableName('ddscorelog',false),$insertData);
		}
    }
    die(json_encode(array(
        'status' => true,
        'msg' => '提交成功'
    )));
}
            
?>