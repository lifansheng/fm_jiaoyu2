<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'groupactivity';
$this1             = 'no3';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$gaid			   = $_GPC['gaid'];
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1001703,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    
    $params    = array();
  

    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_groupsign) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And gaid='{$gaid}' And type = 1  ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach( $list as $key => $value )
    {
    	$userinfo =  pdo_fetch('SELECT userinfo,pard FROM ' . tablename($this->table_user) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['userid']}' ");
    	$pard = get_guanxi($userinfo['pard']);
		if(!$pard){
			$pard = '本人';
		}
    	$usertemp = unserialize($userinfo['userinfo']);
    	$list[$key]['username'] = $usertemp['name'];
    	$list[$key]['phone'] = $usertemp['mobile'];
    	$list[$key]['pard'] = $pard;
   		$student = pdo_fetch('SELECT s_name,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['sid']}' ");
   		$bjname =  pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And sid='{$student['bj_id']}' ");
   		$list[$key]['sname'] = $student['s_name'];
   		$list[$key]['sbj'] = $bjname['sname']; 
   		
    }
    //var_dump($list);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_groupsign) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And gaid='{$gaid}' And type = 1 ");
    $pager = pagination($total, $pindex, $psize);
    //////////////////////////////////导出报名记录
}elseif($operation == 'out_putcode'){
	$listss = pdo_fetchall("SELECT * FROM " . tablename($this->table_groupsign) .  " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And gaid='{$gaid}' And type = 1 ORDER BY createtime DESC");
	    $ii = 0 ;
	 foreach( $listss as $key => $value )
    {
    	$userinfo =  pdo_fetch('SELECT userinfo,pard FROM ' . tablename($this->table_user) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['userid']}' ");
    	$pard = get_guanxi($userinfo['pard']);
		if(!$pard){
			$pard = '本人';
		}
    	$usertemp = unserialize($userinfo['userinfo']);
    	
   		$student = pdo_fetch('SELECT s_name,bj_id FROM ' . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$value['sid']}' ");
   		$bjname =  pdo_fetch('SELECT sname FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And sid='{$student['bj_id']}' ");
   		$arr[$ii]['sname'] = $student['s_name'];
   		$arr[$ii]['sbj'] = $bjname['sname'];
   		$arr[$ii]['username'] = $usertemp['name'];
   		$arr[$ii]['pard'] = $pard;
    	$arr[$ii]['phone'] = $usertemp['mobile'];
    	$arr[$ii]['signtime'] = date('Y-m-d h:i:s',$value['createtime']);
    	$ii++;
    }
	$ganame = pdo_fetch('SELECT title FROM ' . tablename($this->table_groupactivity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$gaid}' ");
	$title=$ganame['title']."报名情况";
	$this->exportexcel($arr, array('学生姓名','所属班级','家长姓名','关系','手机','报名时间'), $title );
    exit();
			
}elseif($operation == 'delete'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_groupsign) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，报名信息不存在或是已经被删除！', $this->createWebUrl('gasignup', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_groupsign, array('id' => $id), 'OR');
    $this->imessage('报名信息删除成功！', $this->createWebUrl('gasignup', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/gasignup');
?>