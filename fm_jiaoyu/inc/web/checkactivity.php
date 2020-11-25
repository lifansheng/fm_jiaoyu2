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
$tid_global = $_W['tid'];
// if (!(IsHasQx($tid_global,1001701,1,$schoolid))){
// 	$this->imessage('非法访问，您无权操作该页面','','error');	
// }
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

    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_groupactivity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 $condition ORDER BY createtime DESC,ssort DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach( $list as $key => $value )
    {
    	$signup = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_groupsign) . " WHERE gaid = '{$value['id']}'");
    	$list[$key]['signcount'] =$signup;
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_groupactivity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 ");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
    if (!(IsHasQx($tid_global,1001702,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');	
    }
    load()->func('tpl');
    $id = intval($_GPC['id']);
    $starttime = time();
    $endtime =$starttime + 86400;
   

    if(!empty($id)){
        $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_groupactivity) . " WHERE id = '{$id}'");
        if(empty($item1)){
			$this->imessage('抱歉，本条信息不存在在或是已经删除！', $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
        }
        $starttime = $item1['starttime'];
        $endtime = $item1['endtime'];
        $groupsign =  pdo_fetchAll("SELECT sid FROM " . tablename($this->table_groupsign) . " where :gaid = gaid  ", array( ':gaid' => $id ));
        foreach ($groupsign as $key => $value) {
            $student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $groupsign[$key]['s_name'] = $student['s_name'];
        }
        // var_dump($groupsign);die;
        $bannerOutarr = iunserializer($item1['banner']);
    }

    $banji  = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
    $uniarr = explode(',', $item1['bjarray']);
    
    if(checksubmit('submit')){
        // var_dump($_GPC);die;
		$bannerarr = iserializer($_GPC['banners']);
		$starttime = strtotime($_GPC['timerange']['start']);
		$endtime = strtotime($_GPC['timerange']['end']) + 86399;
		
		if(empty($_GPC['gtitle'])){
			 $this->imessage('请输入活动标题！', '', 'error');
		}
		if(empty($_GPC['thumb'])){
			 $this->imessage('请选择活动缩略图！', '', 'error');
		}
		if(empty($_GPC['banners'])){
			 $this->imessage('请选择活动幻灯片！', '', 'error');
		}
		if(empty($_GPC['arr'])){
			 $this->imessage('请选择允许报名班级！', '', 'error');
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
            'banner'	   => $bannerarr,
            'ssort'		   => $_GPC['gsort'],
            'starttime'	   => $starttime,
            'endtime'	   => $endtime,		
            'bjarray'	   =>  implode(',', $_GPC['arr']),
            'cost'		   => $_GPC['bmfy'],
            'type'         =>  1 ,
            
        );
        $allchecked = $_GPC['allchecked'];
        if($allchecked == 'allc'){
	        $data['isall'] = 1 ;
        }else{
	         $data['isall'] = 0 ;
        }
        $iiii = 0 ;
        $is_allsend = $_GPC['is_allsend'];
     
		
		$gaid = 0 ;
        if(!empty($id)){
            pdo_update($this->table_groupactivity, $data, array('id' => $id));
              $gaid = $id;
        }else{
	        $data['createtime'] =time();
	        
            pdo_insert($this->table_groupactivity, $data);
            $gaid = pdo_insertid();
        }
        //TODO:bjarr班级ID，班级下的所有学生。sidarr指定个别学生--进行报名操作(两个值分别不为空，才进行添加)
        if(keep_MC()){
            //通过指定学生报名
            // var_dump($_GPC['sidarr']);die;
            if(!empty($_GPC['sidarr'])){
                foreach ($_GPC['sidarr'] as $value) {
                    if($value !== 0){
                        //通过sid去查询userid
                        $user = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where sid = :sid ", array( ':sid'=>$value ));
                        //查询学生是否报名
                        $checksign =  pdo_fetch("SELECT id FROM " . tablename($this->table_groupsign) . " where :schoolid = schoolid And :sid = sid  And  :gaid = gaid  ", array( ':schoolid' => $_GPC['schoolid'], ':sid'=>$value, ':gaid' => $gaid ));
                        if(empty($checksign)){
                            $temp = array(
                                'schoolid'   =>$_GPC['schoolid'],
                                'weid'       =>$weid,
                                'gaid'       =>$gaid,
                                'userid'	 =>$user['id'],
                                'sid'        =>$value,
                                'createtime' => TIMESTAMP,
                                'type'       => 1 
                            );
                            pdo_insert($this->table_groupsign, $temp);
                        }
                    }
                }
            }
            //通过班级报名
            if(!empty($_GPC['bjarr'])){
                foreach ($_GPC['bjarr'] as $value) {
                    //通过sid去查询userid
                    $bjstudents = pdo_fetchAll("SELECT id FROM " . tablename($this->table_students) . " where bj_id = :bj_id ", array( ':bj_id'=>$value ));
                    foreach ($bjstudents as $val) {
                        $user = pdo_fetch("SELECT id FROM " . tablename($this->table_user) . " where sid = :sid ", array( ':sid'=>$val['id'] ));
                        //查询学生是否报名
                        $checksign =  pdo_fetch("SELECT id FROM " . tablename($this->table_groupsign) . " where :schoolid = schoolid And :sid = sid And :gaid = gaid  ", array( ':schoolid' => $_GPC['schoolid'], ':sid'=>$val['id'], ':gaid' => $gaid ));
                        if(empty($checksign)){
                            $temp = array(
                                'schoolid'   =>$_GPC['schoolid'],
                                'weid'       =>$weid,
                                'gaid'       =>$gaid,
                                'userid'	 =>$user['id'],
                                'sid'        =>$val['id'],
                                'createtime' => TIMESTAMP,
                                'type'       => 1 
                            );
                            pdo_insert($this->table_groupsign, $temp);
                        }
                    }
                }
            }
        }

        //群发操作
		// gaid,bjarray,groupid(?),
   		if($is_allsend == 1 ){
	        $bjarray = $_GPC['arr'];
	        //foreach( $bjarray as $key => $value )
	        //{
	        //	$studentsarray = pdo_fetchall("SELECT id,s_name FROM " . tablename($this->table_students) . " where weid = :weid And schoolid = :schoolid And bj_id = :bjid ORDER BY id DESC", array(':weid' => $weid, ':bjid' => $value, ':schoolid' => $schoolid));
	        //	foreach( $studentsarray as $key1 => $value1 )
	        //	{
	        //		$userarray = pdo_fetchall("SELECT id,openid FROM " . tablename($this->table_user) . " where weid = :weid And schoolid = :schoolid And sid = :sid AND tid = :tid ORDER BY id DESC", array(':weid' => $weid, ':sid' => $value1['id'], ':tid'=>0,':schoolid' => $schoolid));
	        //		foreach( $userarray as $key2 => $value2 )
	        //		{
	        //			var_dump('群发消息');
	        //		}
	        //	}
	        //}
	         //$this->imessage('开始群发班级通知,请勿执行任何操作！', $this->createWebUrl('BjtzMsg', array('notice_id' => $gaid, 'schoolid' => $schoolid, 'weid' => $weid, 'tname' => $teacher['tname'], 'bj_id' => $bjarray)), 'success');
	          header("location:".$this->createWebUrl('sendmsg_muti', array('notice_id' => $gaid, 'schoolid' => $schoolid, 'weid' => $weid, 'tname' =>"管理员", 'bj_id' => $bjarray,'type'=>1,'from'=>"group"))); 
	         
	        // var_dump($bjarray);
	        //die();
        }
        


        //结束群发操作
        $this->imessage('操作成功', $this->createWebUrl('groupactivity', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_groupactivity) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，集体活动不存在或是已经被删除！', $this->createWebUrl('groupactivity', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_groupactivity, array('id' => $id));
    pdo_delete($this->table_groupsign, array('gaid' => $id));
    $this->imessage('集体活动删除成功！', $this->createWebUrl('groupactivity', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'GetBjStudent'){
    $bjid = $_GPC['bjid'];
    $students = pdo_fetchAll("SELECT id,s_name FROM " . GetTableName('students') . " WHERE FIND_IN_SET(bj_id,'{$bjid}')");
    $result ['data'] = $students;
    die ( json_encode ( $result ) );
}
include $this->template('web/checkmeeting');
?>