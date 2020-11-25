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
if (!(IsHasQx($tid_global,1001804,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    
    $params    = array();

    if(!empty($_GPC['searchtime'])){
	    $searchStime  = strtotime($_GPC['searchtime']['start']);
      	$searchEtime  = strtotime($_GPC['searchtime']['end']) + 86399 ;
      	  if($searchStime != '-28800' && $searchEtime != '-28800')
  	 	$condition  .= " AND servetime >= {$searchStime} AND servetime <= {$searchEtime}";
     
    }

    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_groupsign) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And gaid='{$gaid}' And type != 1  $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    
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
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_groupsign) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And gaid='{$gaid}' And type != 1 ");
    $pager = pagination($total, $pindex, $psize);
    //////////////////////////////////导出报名记录
}elseif($operation == 'out_putcode'){
	 $condition = '';
 if(!empty($_GPC['searchtime'])){
	    $searchStime  = strtotime($_GPC['searchtime']['start']);
      	$searchEtime  = strtotime($_GPC['searchtime']['end']) + 86399 ;
      	  if($searchStime != '-28800' && $searchEtime != '-28800')
  	 	$condition  .= " AND servetime >= {$searchStime} AND servetime <= {$searchEtime}";
     
    }
	$listss = pdo_fetchall("SELECT * FROM " . tablename($this->table_groupsign) .  " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And gaid='{$gaid}' And type != 1 $condition ORDER BY createtime DESC");
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
    	$arr[$ii]['servetime'] = date('Y-m-d h:i:s',$value['servetime']);
    	$arr[$ii]['signtime'] = date('Y-m-d h:i:s',$value['createtime']);
    	$ii++;
    }
	$ganame = pdo_fetch('SELECT title FROM ' . tablename($this->table_groupactivity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  And id='{$gaid}' ");
	if(!empty($_GPC['searchtime'])){
	    $searchStime  = strtotime($_GPC['searchtime']['start']);
      	$searchEtime  = strtotime($_GPC['searchtime']['end']) + 86399 ;
      	  if($searchStime != '-28800' && $searchEtime != '-28800')
  	 	$condition = " {$_GPC['searchtime']['start']} 至 {$_GPC['searchtime']['start']}";
     
    }
	$title=$ganame['title'].$condition."预约情况";
	$this->exportexcel($arr, array('学生姓名','所属班级','家长姓名','关系','手机','上门服务时间', '预约时间'), $title );
    exit();
			
}elseif($operation == 'delete'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_groupsign) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，报名信息不存在或是已经被删除！', $this->createWebUrl('gasignup', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_groupsign, array('id' => $id));
    $this->imessage('报名信息删除成功！', $this->createWebUrl('gasignup', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/horecord');
?>