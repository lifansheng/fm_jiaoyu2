<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'houseorder';
$this1             = 'no3';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1001801,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    
    $params    = array();
    if(!empty($_GPC['gctype'])){
	    $gctype   = $_GPC['gctype'];
	    $condition   .= " AND type  = {$gctype}";
       
    }
    if(!empty($_GPC['group_name'])){
	    $group_name   = $_GPC['group_name'];
	    $condition   .= " AND title LIKE '%{$group_name}%'";
       
    }
   
    if(!empty($_GPC['searchtime'])){
	    $searchStime  = strtotime($_GPC['searchtime']['start']);
      	$searchEtime  = strtotime($_GPC['searchtime']['end']) + 86399 ;
      	  if($searchStime != '-28800' && $searchEtime != '-28800')
  	 	$condition  .= " AND starttime >= {$searchStime} AND endtime <= {$searchEtime}";
      	
    }

    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_groupactivity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type != 1 $condition ORDER BY ssort DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach( $list as $key => $value )
    {
    	$signup = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_groupsign) . " WHERE gaid = '{$value['id']}'");
    	$list[$key]['signcount'] =$signup;
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_groupactivity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type != 1 ");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1001802,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    load()->func('tpl');
    $id = intval($_GPC['id']);
    $starttime = time();
    $endtime =$starttime + 86400;
   

    if(!empty($id)){
        $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_groupactivity) . " WHERE id = '{$id}'");
        if(empty($item1)){
			$this->imessage('抱歉，该项目不存在在或是已经删除！', $this->createWebUrl('houseorder', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
        }
        $starttime = $item1['starttime'];
        $endtime = $item1['endtime'];
    }
    if(checksubmit('submit')){
		$starttime = strtotime($_GPC['timerange']['start']);
		$endtime = strtotime($_GPC['timerange']['end']) + 86399;
		
		if(empty($_GPC['gtitle'])){
			 $this->imessage('请输入项目标题！', '', 'error');
		}
		if(empty($_GPC['gctype'])){
			 $this->imessage('请选择项目类型！', '', 'error');
		}
		if(empty($_GPC['thumb'])){
			 $this->imessage('请选择项目缩略图！', '', 'error');
		}
		
        if($starttime > $endtime){
            $this->imessage('时间范围设置错误,开始时间不能大于结束时间！', '', 'error');
        }
        
        $data = array(
            'weid'         => $weid,
            'schoolid'     => $_GPC['schoolid'],
            'title'        => trim($_GPC['gtitle']),
            'content'      => trim($_GPC['content']),
            'thumb'        => trim($_GPC['thumb']),
            'ssort'		   => $_GPC['gsort'],
            'starttime'	   => $starttime,
            'endtime'	   => $endtime,		
            'type'         =>  intval($_GPC['gctype']) ,
        );

	
        if(!empty($id)){
            pdo_update($this->table_groupactivity, $data, array('id' => $id));
        }else{
	        $data['createtime'] =time();
            pdo_insert($this->table_groupactivity, $data);
        }

        $this->imessage('操作成功', $this->createWebUrl('houseorder', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_groupactivity) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，该项目不存在或是已经被删除！', $this->createWebUrl('houseorder', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_groupactivity, array('id' => $id));
    $this->imessage('服务项目删除成功！', $this->createWebUrl('houseorder', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/houseorder');
?>