<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'classcardactivity';
$this1             = 'no10';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1001701,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    
    $params    = array();
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

    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_activity) . " WHERE cate=1 and weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 $condition ORDER BY createtime DESC,ssort DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

    foreach( $list as $key => $value )
    {
        $signup = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_classcard_activity_result) . " WHERE activity_id = '{$value['id']}'");
    	$list[$key]['signcount'] =$signup;
    }

    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_activity) . " WHERE cate=1 and weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 ");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){

if (!(IsHasQx($tid_global,1001702,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
    load()->func('tpl');
    $id = intval($_GPC['id']);
    $starttime = time();
    $endtime =$starttime + 86400;
    $starttime1 = $endtime+86400;
    $endtime1 =$starttime1 + 86400;

    if(!empty($id)){
        $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_activity) . " WHERE id = '{$id}'");
        if(empty($item1)){
			$this->imessage('抱歉，本条信息不存在在或是已经删除！', $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
        }
        $starttime = $item1['starttime'];
        $endtime = $item1['endtime'];

        $starttime1 = $item1['starttime1'];
        $endtime1 = $item1['endtime1'];

    }

    $banji  = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
    $uniarr = explode(',', $item1['bjarray']);
    
    if(checksubmit('submit')){

		$starttime = strtotime($_GPC['timerange']['start']);
		$endtime = strtotime($_GPC['timerange']['end']) + 86399;

        $starttime1 = strtotime($_GPC['timerange1']['start']);
        $endtime1 = strtotime($_GPC['timerange1']['end']) + 86399;
		if(empty($_GPC['gtitle'])){
			 $this->imessage('请输入活动标题！', '', 'error');
		}
        if(empty($_GPC['addr'])){
            $this->imessage('请输入活动地点！', '', 'error');
        }

		if(empty($_GPC['arr'])){
			 $this->imessage('请选择允许报名班级！', '', 'error');
		}
        if(empty($_GPC['cate'] )){
            $this->imessage('请选活动类型！', '', 'error');
        }
        if($starttime > $endtime){
            $this->imessage('时间范围设置错误,开始时间不能大于结束时间！', '', 'error');
        }
        if($starttime1 > $endtime1){
            $this->imessage('时间范围设置错误,开始时间不能大于结束时间！', '', 'error');
        }
        $data = array(
            'weid'         => $weid,
            'schoolid'     => $_GPC['schoolid'],
            'title'        => trim($_GPC['gtitle']),
            'content'      => trim($_GPC['content']),
            'addr'        => trim($_GPC['addr']),
            'ssort'		   => $_GPC['gsort'],
            'starttime'	   => $starttime,
            'endtime'	   => $endtime,
            'starttime1'	   => $starttime1,
            'endtime1'	   => $endtime1,
            'bjarray'	   =>  implode(',', $_GPC['arr']),
            'type'         =>  1 ,
            'cate'         =>  $_GPC['cate'] ,
        );
        $allchecked = $_GPC['allchecked'];
        if($allchecked == 'allc'){
	        $data['isall'] = 1 ;
        }else{
	         $data['isall'] = 0 ;
        }
        $iiii = 0 ;

		$gaid = 0 ;
        if(!empty($id)){
            pdo_update($this->table_classcard_activity, $data, array('id' => $id));
              $gaid = $id;
        }else{
	        $data['createtime'] =time();
	        
            pdo_insert($this->table_classcard_activity, $data);
            $gaid = pdo_insertid();
        }

        //结束群发操作
        $this->imessage('操作成功', $this->createWebUrl('classcardactivity', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_classcard_activity) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，报名活动不存在或是已经被删除！', $this->createWebUrl('classcardactivity', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_classcard_activity, array('id' => $id));
    $this->imessage('报名活动删除成功！', $this->createWebUrl('classcardactivity', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'display1') {

    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';

    $params    = array();
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

    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_activity) . " WHERE cate=2 and weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 $condition ORDER BY createtime DESC,ssort DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

    foreach( $list as $key => $value )
    {
        $signup = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_classcard_activity_result) . " WHERE activity_id = '{$value['id']}'");
        $list[$key]['signcount'] =$signup;
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_activity) . " WHERE cate=1 and weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 ");
    $pager = pagination($total, $pindex, $psize);


}elseif($operation == 'post1'){

    if (!(IsHasQx($tid_global,1001702,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
    load()->func('tpl');
    $id = intval($_GPC['id']);
    $starttime = time();
    $endtime =$starttime + 86400;

    if(!empty($id)){
        $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_activity) . " WHERE id = '{$id}'");
        if(empty($item1)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
        }
        $starttime = $item1['starttime'];
        $endtime = $item1['endtime'];
        //$bannerOutarr = iunserializer($item1['banner']);
    }

    $banji  = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
    $uniarr = explode(',', $item1['bjarray']);

    if(checksubmit('submit')){
        /***
         * cate: 2
        gtitle: 33
        gsort: 333
        timerange[start]: 2020-02-13
        timerange[end]: 2020-02-14
        banners[]: images/89/2019/12/ddB9di6Ggdj61NfZ06bHmGSgjz6N66.jpg
        content: wwww
        total_count: 11
        attr: a
        attr: cv
        attr: vv
        submit: 提交
        token: 14c908dd
         */
        // var_dump(iserializer($_GPC['gtitle']));var_dump();die;die;
        //$bannerarr = iserializer($_GPC['banners']);
        $starttime = strtotime($_GPC['timerange']['start']);
        $endtime = strtotime($_GPC['timerange']['end']) + 86399;
        $attr = iserializer($_GPC['attr']);

        if(empty($_GPC['gtitle'])){
            $this->imessage('请输入活动标题！', '', 'error');
        }
        if(empty($_GPC['content'])){
            $this->imessage('请输入投票描述！', '', 'error');
        }
//        if(empty($_GPC['banners'])){
//            $this->imessage('请选择活动幻灯片！', '', 'error');
//        }
        if(empty($_GPC['attr'])){
            $this->imessage('请选择投票选项！', '', 'error');
        }
        if(empty($_GPC['total_count'])){
            $this->imessage('请选择最多投票数！', '', 'error');
        }
        if($starttime > $endtime){
            $this->imessage('时间范围设置错误,开始时间不能大于结束时间！', '', 'error');
        }
        $data = array(
            'weid'         => $weid,
            'schoolid'     => $_GPC['schoolid'],
            'title'        => trim($_GPC['gtitle']),
            'content'      => trim($_GPC['content']),
            //'banner'	   => $bannerarr,
            'ssort'		   => $_GPC['gsort'],
            'starttime'	   => $starttime,
            'endtime'	   => $endtime,
            'bjarray'	   =>  implode(',', $_GPC['arr']),
            'type'         =>  1 ,
            'cate'         =>  $_GPC['cate'] ,
            'total_count'  =>  $_GPC['total_count'] ,
            'attr'         => $attr,
        );
        $allchecked = $_GPC['allchecked'];
        if($allchecked == 'allc'){
            $data['isall'] = 1 ;
        }else{
            $data['isall'] = 0 ;
        }
        $iiii = 0 ;

        $gaid = 0 ;
        if(!empty($id)){
            pdo_update($this->table_classcard_activity, $data, array('id' => $id));
            $gaid = $id;
        }else{
            $data['createtime'] =time();

            pdo_insert($this->table_classcard_activity, $data);
            $gaid = pdo_insertid();
        }

        //结束群发操作
        $this->imessage('操作成功', $this->createWebUrl('classcardactivity', array('op' => 'display1', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete1'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_classcard_activity) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，投票活动不存在或是已经被删除！', $this->createWebUrl('classcardactivity', array('op' => 'display1', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_classcard_activity, array('id' => $id));
    $this->imessage('投票活动删除成功！', $this->createWebUrl('classcardactivity', array('op' => 'display1', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/classcardactivity');
?>