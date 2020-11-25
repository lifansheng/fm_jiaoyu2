<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'allcamera';
$this1             = 'no1';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,videoname FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'post'){
	if (!(IsHasQx($tid_global,1000302,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE id = :id", array(':id' => $id));
        if(empty($item)){
            $this->imessage('抱歉，不存在或是已经删除！', referer(), 'error');
        }
    }
	$payweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");
    $banji  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
    $uniarr = explode(',', $item['bj_id']);
    $kc  = pdo_fetchall("SELECT * FROM " . GetTableName('tcourse') . " where weid = :weid And schoolid = :schoolid ORDER BY id DESC", array(':weid' => $weid, ':schoolid' => $schoolid));
    $kcuniarr = explode(',', $item['kcidstr']);
    if(checksubmit('submit')){
        if($_W['schooltype']){
            if($_GPC['arr']){
                $kcidstr = implode(',', $_GPC['arr']);
            }else{
                $kcidstr = '';
            }
        }else{
            if($_GPC['arr']){
                $bj_id = implode(',', $_GPC['arr']);
            }else{
                $bj_id = '';
            }
        }
        
		$starttime1 = -1 ;
		$endtime1	= -1 ;
		$starttime2 = -1 ;
		$endtime2	= -1 ;
		$starttime3 = -1 ;
		$endtime3	= -1 ;
		
		if( $_GPC['starttime1'] != $_GPC['endtime1'] ){
			$starttime1 = $_GPC['starttime1'];
			$endtime1	= $_GPC['endtime1'];
		}
		if( $_GPC['starttime2'] != $_GPC['endtime2'] ){
			$starttime2 = $_GPC['starttime2'];
			$endtime2	= $_GPC['endtime2'];
		}
		if( $_GPC['starttime3'] != $_GPC['endtime3'] ){
			$starttime3 = $_GPC['starttime3'];
			$endtime3	= $_GPC['endtime3'];
		}
		$notunique = 0 ;
		if($starttime1 != -1 ){
				if($starttime1 <= $endtime2 && $starttime1 >= $starttime2 ){
				$notunique = 1;
			}
			if($starttime1 <= $endtime3 && $starttime1 >= $starttime3 ){
				$notunique = 2;
			}
		}
		
		if($starttime2 != -1){
				if($starttime2 <= $endtime1 && $starttime2 >= $starttime1 )
			{
				$notunique = 3;
			}
			if($starttime2 <= $endtime3 && $starttime2 >= $starttime3 )
			{
				$notunique = 4;
			}
		}
		if($starttime3 != -1 ){
				if($starttime3 <= $endtime1 && $starttime3 >= $starttime1 )
			{
				$notunique = 5;
			} 
			if($starttime3 <= $endtime2 && $starttime3 >= $starttime2 )
			{
				$notunique = 6;
			}
		}
		
		//die();
        $data = array(
            'weid'       => $weid,
            'schoolid'   => $schoolid,
            'name'       => trim($_GPC['name']),
            'videopic'   => trim($_GPC['videopic']),
            'videourl'   => trim($_GPC['videourl']),
            'starttime1'  => $starttime1,
            'endtime1'    => $endtime1,
			'starttime2'  => $starttime2,
            'endtime2'    => $endtime2,
			'starttime3'  => $starttime3,
            'endtime3'    => $endtime3,
            'click'      => trim($_GPC['click']),
            'allowpy'    => trim($_GPC['allowpy']),
            'bj_id'      => $bj_id,
            'kcid'      => $_GPC['kcid'],
            'kcidstr'      => $_GPC['kcid'],
            'videotype'  => trim($_GPC['videotype']),
            'conet'      => trim($_GPC['conet']),
            'type'       => 1,
            'createtime' => time(),
			'days' => intval($_GPC['days']),
			'is_pay' => intval($_GPC['is_pay']),
			'price_one' => trim($_GPC['price_one']),
			'price_one_cun' => trim($_GPC['price_one_cun']),
			'price_all' => trim($_GPC['price_all']),
			'price_all_cun' => trim($_GPC['price_all_cun']),
			'is_try' => intval($_GPC['is_try']),
			'try_time' => intval($_GPC['try_time']),
			'payweid'      => empty($_GPC['payweid']) ? $weid : $_GPC['payweid'],
            'ssort'      => intval($_GPC['ssort']),
            'ispx'      => $_W['schooltype'] ? 1 : 0
        );
		if($notunique != 0 ){
			//var_dump($notunique);
			//die();
			$this->imessage('监控可用时段有重叠!！', referer(), 'error');
		}
		if($_GPC['is_pay'] == 1 ){
			if(empty($_GPC['price_one']) && empty($_GPC['price_all'])){
				$this->imessage('启用单独收费模式请至少设置一种付费方式!！', referer(), 'error');
			}		
		}
        if(empty($data['name'])){
            $this->imessage('请输入名称!！', referer(), 'error');
        }
        if(!$_W['schooltype']){
            if($starttime1 == -1 &&  $starttime2 == -1 &&  $starttime3  == -1){
                $this->imessage('请设置每日观看开始时间', referer(), 'error');
            }
            if( $endtime1  == -1 &&  $endtime2  == -1 && $endtime3 == -1){
                $this->imessage('请设置每日观看结束时间', referer(), 'error');
            }
        }
        
        if(empty($id)){
            pdo_insert($this->table_allcamera, $data);
        }else{
            unset($data['dateline']);
            pdo_update($this->table_allcamera, $data, array('id' => $id));
        }

        $this->imessage('操作成功！', $this->createWebUrl('allcamera', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'display'){

    $addr = pdo_fetchall("SELECT video,sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'addr' And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");

	if (!(IsHasQx($tid_global,1000301,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 15;
    $condition = '';
    if(!empty($_GPC['keyword'])){
        $condition .= " AND name LIKE '%{$_GPC['keyword']}%'";
    }
    if($_W['schooltype']){
        $condition .= " AND ispx = '1'";
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY ssort DESC , id DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $key => $value){
        $plsl                  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$value['id']}' And type = 2");
        $dzsl                  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$value['id']}' And type = 1");
        if($value['is_pay'] ==1){
			$payorder = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_order) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND vodid = '{$value['id']}' And status = 2");
			$list[$key]['paycunt']    = $payorder;
		}
		$list[$key]['plsl']    = $plsl;
        $list[$key]['dianzan'] = $dzsl;
		
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition");

    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id  = intval($_GPC['id']);
    $row = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE id = :id", array(':id' => $id));
    if(empty($row)){
        $this->imessage('抱歉，不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_allcamera, array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'pllist'){
    load()->func('tpl');
    $pindex  = max(1, intval($_GPC['page']));
    $psize   = 20;
    $videoid = intval($_GPC['id']);
    $allpl   = pdo_fetchall("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' AND type = 2 ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($allpl as $key => $row){
        $user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
        if($user['pard'] == 0){
            $teacher             = pdo_fetch("SELECT tname,thumb FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $user['tid']));
            $allpl[$key]['name'] = $teacher['tname'] . "老师";
        }else{
            $studen = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where id = :id ", array(':id' => $user['sid']));
            if($user['pard'] == 4){
                $allpl[$key]['name'] = $studen['s_name'];
            }else{
                $item                = pdo_fetch("SELECT avatar FROM " . tablename('mc_members') . " where uniacid = :uniacid AND uid=:uid ", array(':uid' => $user['uid'], ':uniacid' => $weid));
                $allpl[$key]['icon'] = $item['avatar'];
                if($user['pard'] == 2){
                    $allpl[$key]['name'] = $studen['s_name'] . "妈妈";
                }
                if($user['pard'] == 3){
                    $allpl[$key]['name'] = $studen['s_name'] . "爸爸";
                }
                if($user['pard'] == 4){
                    $allpl[$key]['name'] = $studen['s_name'] . "家长";
                }
            }
        }
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$videoid}' AND type = 2 ");
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $item = pdo_fetch("SELECT * FROM " . tablename($this->table_camerapl) . " WHERE id = :id", array(':id' => $id));
            if(empty($item)){
                $notrowcount++;
                continue;
            }
            pdo_delete($this->table_camerapl, array('id' => $id));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
    $data ['result'] = true;
    $data ['msg'] = $message;
    die (json_encode($data));
}elseif($operation == 'setAddr'){
    $addrData = explode('|',$_GPC['addrData']);
    foreach ($addrData as $key => $value) {
        $data = explode(',',$value);
        if($data){
            pdo_update(GetTableName('classify',false),array('video'=>$data['1']),array('sid'=>$data['0']));
        }
    }
    $result['msg'] = '设置成功';
    $result['result'] = 'true';
    die(json_encode($result));
}elseif($operation == 'showcarmera'){
    $video = pdo_fetch("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE id = '{$_GPC['id']}' AND schoolid = '{$schoolid}' ");
    $isonline = true;
    if($video['videotype'] != 2 && $_W['schooltype']){ //培训视频指定课程
        //查询当前课程是否正在上课(或准备开课)
        $video = getAllKcKs($schoolid,$video['kcidstr']);
        if(!empty($video)){
            $isonline = true;
        }else{
            $isonline = false;
        }
    }
    if(!$_W['schooltype']){
        $bjidnum = count(explode(',',$video['bj_id']));
        $video['bjidnum'] = $bjidnum;
    }
    include $this->template('web/carmera/carmera');
    die();
}
include $this->template('web/allcamera');
?>