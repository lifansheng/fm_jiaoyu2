<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'wuliu';
$this1             = 'no6';
$action            = 'wuliu';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];

load()->func('tpl');
load()->func('file');
load()->func('communication');
if($operation =='post' && !(IsHasQx($tid_global,1002801,1,$schoolid))){
	$operation = 'display';
	$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl('wuliu', array('schoolid' => $schoolid,'op'=>$operation));
	header("location:$stopurl");
}
if($operation =='display' && !(IsHasQx($tid_global,1002811,1,$schoolid))){
	$operation = 'post';
	$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl('wuliu', array('schoolid' => $schoolid,'op'=>$operation));
	header("location:$stopurl");
}

if($operation == 'display'){
	if (!(IsHasQx($tid_global,1002811,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 20;	
    $allact = pdo_fetchall("SELECT * FROM " . tablename($this->table_points) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' ORDER BY id ASC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);	
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_points) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ");
    $pager = pagination($total, $pindex, $psize);
}
elseif ($operation == 'posta' )
{
	if (!(IsHasQx($tid_global,1002812,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$actid = $_GPC['actid'];
	if(!empty($actid))
	{
		$act = pdo_fetch("SELECT * FROM " . tablename($this->table_points) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' And id='{$actid}' " );	
		
	};
	
	if(checksubmit('submit')){
		if(empty( $_GPC['dailytime'])){
			$this->imessage('抱歉，每日操作次数不能为空！', referer(), 'error');
		};
		if(empty( $_GPC['adpoints'])){
			$this->imessage('抱歉，每次增加积分不能为空！', referer(), 'error');
		};
		if(empty( $_GPC['opname'])){
			$this->imessage('抱歉，规则名不能为空！', referer(), 'error');
		};
		if(empty($actid))
		{
			
		
		if(empty( $_GPC['chooseOP'])){
			$this->imessage('抱歉，操作不能为空！', referer(), 'error');
		};
		};
		
	
		$chooseOP = $_GPC['chooseOP'];
		if(intval($_GPC['chooseType']) == 1 ){
			$type = 1 ;
		};
		if(intval($_GPC['chooseType']) == 2 ){
			$type = 2 ;
		}
		$check = pdo_fetch("SELECT * FROM " . tablename($this->table_points) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and op = '{$chooseOP}' And type='1' " );
		$check1 = pdo_fetch("SELECT * FROM " . tablename($this->table_points) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and op = '{$chooseOP}' And type='2' " );
		if($type == 1 )
		{
			if(!empty($check)  && empty($actid))
			{
				$this->imessage('抱歉，该动作已存在积分规则！', referer(), 'error');
			}
		}
		if($type == 2 )
		{
			if(!empty($check1)  && empty($actid))
			{
				$this->imessage('抱歉，该动作已存在积分任务！', referer(), 'error');
			}
		}	
		
		
		$temp = array(
			'weid'       => $weid,
			'schoolid'   => $schoolid,
			'op'      => trim($_GPC['chooseOP']),
			'name' => trim($_GPC['opname']),
			'dailytime'       => intval($_GPC['dailytime']),
			'adpoint'      => intval($_GPC['adpoints']),
			'is_on'    => 1,
			'type'    => $type
		);
		if(empty($actid))
		{
			pdo_insert($this->table_points, $temp);
		}
		if(!empty($actid))
		{
			pdo_update($this->table_points, array('name' => trim($_GPC['opname']),'dailytime'=> intval($_GPC['dailytime']),'adpoint'=>intval($_GPC['adpoints'])), array('id' => $actid));
		}
		 
	
	  $this->imessage('更新积分动作成功！', $this->createWebUrl('wuliu', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
		 
		
	 };
}
elseif ($operation == 'post' )
{
	if (!(IsHasQx($tid_global,1002801,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 20;
	$condition = '';
    if (!empty($_GPC['keyword'])) {
	    $tnameser = trim($_GPC['keyword']);
		$teacher =  pdo_fetch("SELECT id FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' And tname = '{$tnameser}'  ");
        $condition .= " AND tid = '{$teacher['id']}'";
		
    }
    if (!empty($_GPC['receive'])) {
	    $pnameser = trim($_GPC['receive']);
		$pointop =  pdo_fetch("SELECT id FROM " . tablename($this->table_points) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' And name = '{$pnameser}'  ");
        $condition .= " AND pid = '{$pointop['id']}'";
		
    }
    if(!empty($_GPC['optime'])) {
				$starttime = strtotime($_GPC['optime']['start']);
				$endtime = strtotime($_GPC['optime']['end']) + 86399;
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
				
			} else {
				$starttime = strtotime('-200 day');
				$endtime = TIMESTAMP;
			}	
    $allact = pdo_fetchall("SELECT * FROM " . tablename($this->table_pointsrecord) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' $condition ORDER BY createtime DESC , id ASC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach( $allact as $key => $value )
    {
    	$op =  pdo_fetch("SELECT name,adpoint,dailytime FROM " . tablename($this->table_points) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' And id = '{$value['pid']}'  ");
    	$allact[$key]['name'] = $op['name'];
    	$allact[$key]['max']  = $op['dailytime'];
    	$allact[$key]['adpoint'] = $op['adpoint'];
    	$tname =  pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' And id = '{$value['tid']}'  ");
    	$allact[$key]['tname'] = $tname['tname'];
    	$time = date("Y-m-d H:i",$value['createtime']);
    	$allact[$key]['time'] =$time;
    }
	
	    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_pointsrecord) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid}  $condition ");

    $pager = pagination($total, $pindex, $psize);
}
elseif($operation == 'change'){
	$is_on = $_GPC['is_on'];
	$id = $_GPC['id'];
	$data = array('is_on' => $is_on);

	pdo_update($this->table_points, $data, array('id' => $id));
	
}elseif($operation == 'delete'){
	$actid = $_GPC['actid'];
	$checkd = pdo_fetch("SELECT * FROM " . tablename($this->table_points) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' And id='{$actid}' " );	
	if(empty($checkd))
	{
		$this->imessage('抱歉，该动作不存在！', referer(), 'error');
	}else if(!empty($checkd))
	{
		pdo_delete($this->table_points,array('id'=>$actid));
		pdo_delete($this->table_pointsrecord,array('pid'=>$actid));
		 $this->imessage('删除动作成功！', $this->createWebUrl('points', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
	};
	
}
elseif($operation == 'deletere'){
	$actid = $_GPC['actid'];
	$checkd = pdo_fetch("SELECT * FROM " . tablename($this->table_pointsrecord) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' And id='{$actid}' " );	
	if(empty($checkd))
	{
		$this->imessage('抱歉，该动作不存在！', referer(), 'error');
	}else if(!empty($checkd))
	{
		pdo_delete($this->table_pointsrecord,array('id'=>$actid));
		 $this->imessage('删除动作成功！', $this->createWebUrl('wuliu', array('op' => 'post','schoolid' => $schoolid,'weid' => $weid )), 'success');
	};
	
}
elseif ($operation == 'deleteall') {
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                if (!empty($id)) {
                    $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_pointsrecord) . " WHERE id = :id", array(':id' => $id));
                    if (empty($goods)) {
                        $notrowcount++;
                        continue;
                    }
                    pdo_delete($this->table_pointsrecord, array('id' => $id));
                   
                    
                    $rowcount++;
                }
            }
			$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

			$data ['result'] = true;

			$data ['msg'] = $message;

			die (json_encode($data));
        }	  
include $this->template('web/points');


	
?>
