<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
    global $_GPC, $_W;

    $weid = $_W['uniacid'];
    $action = 'kecheng';
    $GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
    $schoolid = intval($_GPC['schoolid']);
    $kcid1 = intval($_GPC['kcid']);
    $logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ORDER BY ssort DESC", array(':id' => $schoolid));
    $is_pay = ($_GPC['is_pay']) ? intval($_GPC['is_pay']) : -1;
    $kecheng = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where id = :id", array(':id' => $kcid1));
    if($_W['schooltype']){
        $AllBj = pdo_fetchAll("SELECT id,title FROM " . GetTableName('class') . " WHERE schoolid = '{$schoolid}' AND weid = '{$weid}'");
    }
    $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
    $tid_global = $_W['tid'];
    if ($operation == 'display') {
        $condition = '';
		$pindex    = max(1, intval($_GPC['page']));
		$psize     = 10;
        if (!empty($_GPC['kcid'])) {
            $kcid = intval($_GPC['kcid']);
            $condition .= " AND kcid = '{$kcid}'";
        }
        if(!empty($_GPC['kcname'])){
            $kcname = trim($_GPC['kcname']);
            $kcsearch = pdo_fetchall("SELECT id FROM " . tablename($this->table_tcourse) . " WHERE weid='{$weid}' AND schoolid='{$schoolid}' and name LIKE '%$kcname%' ");
            $kcid_temp = '';
            if(!empty($kcsearch)){
                foreach( $kcsearch as $key => $value )
                {
                    $kcid_temp .=$value['id'].",";
                }
                $kcid_str = trim($kcid_temp,",");
                $condition .= " AND  FIND_IN_SET (kcid,{$kcid_str}) ";
            }
            else{
                 $condition .= " AND kcid =0 ";
            }
        }

        if($is_pay > 0) {
            $condition .= " AND status = '{$is_pay}'";
            $params[':is_pay'] = $is_pay;
        }
        if(!empty($_GPC['createtime']) || $_GPC['start'] || $_GPC['end']) {
			if($_GPC['start'] && $_GPC['end']){
				$starttime = strtotime($_GPC['start']);
				$endtime = strtotime($_GPC['end']) + 86399;
			}else{
				$starttime = strtotime($_GPC['createtime']['start']);
				$endtime = strtotime($_GPC['createtime']['end']) + 86399;
			}
            $condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
        } else {
            $starttime = strtotime('-200 day');
            $endtime = time();
        }
        $params[':start'] = $starttime;
        $params[':end'] = $endtime;
        mload()->model('kc');
        $ismoreguige = pdo_fetch("SELECT id FROM " . GetTableName('tcourse') . " WHERE id = :id AND guigetype = :guigetype", array(':id' => $kcid, 'guigetype' =>1));
        $list = pdo_fetchall("SELECT * FROM " . GetTableName('order') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 1 $condition GROUP BY kcid,sid ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		foreach($list as $index => $row){
            $kcname = pdo_fetch("SELECT name FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $row['kcid']));
            if($ismoreguige){ //表示多规格
                $guige = pdo_fetch("SELECT name FROM ".GetTableName('guige')." WHERE id = '{$row['guigeid']}' ");
                $list[$index]['guige'] = $guige['name'];
            }
			$student = pdo_fetch("SELECT * FROM " . GetTableName('students') . " WHERE id = :id ", array(':id' => $row['sid']));
			$user = pdo_fetch("SELECT * FROM " . GetTableName('user') . " WHERE id = :id ", array(':id' => $row['userid']));
			$buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . GetTableName('coursebuy') . " WHERE sid = :sid AND kcid=:kcid ", array(':sid' => $row['sid'],':kcid'=> $row['kcid']));
			$hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . GetTableName('kcsign') . " WHERE sid = :sid AND kcid=:kcid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid']));
			if(keep_sk77()){
				$list[$index]['kcstatus'] = GetStuKcStatus($schoolid,$weid,$row['sid'],$row['kcid']);
			}
			$list[$index]['restnum'] = $buycourse - $hasSign;
			$list[$index]['buycourse'] = $buycourse;
			$list[$index]['hasSign'] = $hasSign;
			$list[$index]['kcnanme'] = $kcname['name'];
			$list[$index]['s_name'] = $student['s_name'];
			$list[$index]['realname'] = $user['realname'];
			$list[$index]['mobile'] = $user['mobile'];
			$list[$index]['pard'] = $user['pard'];
			$list[$index]['pkuser'] = CheckPkUser($row['tid']);
		}
        if (!empty($_GPC['kcid'])) {
			$total = pdo_fetchcolumn('SELECT count(distinct sid) FROM ' . GetTableName('order') . " where weid = '{$weid}' AND schoolid = '{$schoolid}' AND kcid = '{$_GPC['kcid']}' AND type = 1 $condition ");
			$pager = paginations($total, $pindex, $psize,'',array('before' => 0, 'after' => 0, 'ajaxcallback' => true, 'callbackfuncname' => 'page_bmlist'));
			$kcinfo = pdo_fetch("SELECT kc_type FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $_GPC['kcid']));
			$optype = 'bmlist';
			include $this->template ( 'public/kc_bm_list' );
			die();
        }
	} elseif ($operation == 'stu_list') {//查询一个课程的正式学员
		mload()->model('kc');
		$kcid = $_GPC['kcid'];
        $nowtime = time();
        $optype = 'stu_list';
		$condition = array('page' =>$_GPC['page'],'ks_type' => $_GPC['ks_type'],'s_name' => $_GPC['s_name'],'mobile' => $_GPC['mobile']);
        $kcinfo = pdo_fetch("SELECT kc_type,name FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $_GPC['kcid']));
        //查处所有课程，排除当前课程
        $checkNowTime = strtotime(date("Y-m-d",time()));
        $list = GetOneKcStuList($kcid,$condition);
		// var_dump($list);
        if(is_array($list['list']) && $kcinfo['kc_type'] == 0){
			$AllKc = pdo_fetchAll("SELECT id,name FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' AND NOT FIND_IN_SET(id,'{$_GPC['kcid']}') AND end > '{$checkNowTime}'");
            foreach ($list['list'] as $key => $value) {
                $grant = pdo_fetchcolumn("SELECT SUM(ksnum) FROM ".GetTableName('freekslog')." WHERE kcid = '{$_GPC['kcid']}' AND sid = '{$value['sid']}' ");
                $stuover = pdo_fetch("SELECT overtime,bjid FROM " . tablename($this->table_coursebuy) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$value['sid']}' AND kcid = '{$kcid}' ");
                $bjname = pdo_fetch("SELECT title FROM " . GetTableName('class') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND id = '{$stuover['bjid']}'")['title'];
                $list['list'][$key]['isover'] = ($stuover['overtime'] <= $nowtime && $stuover['overtime'] != 0 ) ? '过期': '';
                $list['list'][$key]['grantks'] = $grant ? $grant : 0;
                $list['list'][$key]['bjname'] = $bjname ? $bjname : '未分班';
                $list['list'][$key]['bjid'] = $stuover['bjid'];
            }
        }
		include $this->template ( 'public/kc_bm_list' );
		die();
	} elseif ($operation == 'sign_list') {//查询一个课程的点名签到情况
		mload()->model('kc');
		$kcid = $_GPC['kcid'];
		$optype = 'sign_list';
		$condition = array('page' =>$_GPC['page'],'ksid' => $_GPC['ksid'],'sign_porsen' => $_GPC['sign_porsen'],'qr_tid' => $_GPC['qr_tid'],'sign_type' => $_GPC['sign_type']);
		$kcinfo = pdo_fetch("SELECT kc_type,allow_menu FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $_GPC['kcid']));
		$list = GetOneKcSignList($kcid,$kcinfo['kc_type'],$condition);
		//print_r($list);
		include $this->template ( 'public/kc_bm_list' );
		die();
	} elseif ($operation == 'qr_onesign') {//手动确认一条签到
		$signid = $_GPC['id'];
		$kcsign = pdo_fetch("SELECT id,weid,schoolid FROM " . GetTableName('kcsign') . " WHERE id = :id ", array(':id' => $signid));
		if($kcsign){
			$signdata = array(
				'status'	  => 2,
				'qrtid'       => is_int($tid_global)? $tid_global : -1,
				'signtime'    => time()
			);
			pdo_update(GetTableName('kcsign',false),$signdata, array('id' => $signid));
			$this->sendMobileXsqrqdtz($signid, $kcsign['schoolid'], $kcsign['weid']);
		}
        $data ['result'] = true;
        die (json_encode($data));	
	} elseif ($operation == 'del_kssign') {//删除一条签到记录
		$signid = $_GPC['id'];
		$kcsign = pdo_fetch("SELECT * FROM " . GetTableName('kcsign') . " WHERE id = :id ", array(':id' => $signid));
		if($kcsign){
			pdo_delete(GetTableName('kcsign',false), array('id' => $signid));
		}
        $data ['result'] = true;
        die (json_encode($data));	
	} elseif ($operation == 'del_cursbuy') {//移除这个课程下的一个学员
		$courid = $_GPC['id'];
		$course = pdo_fetch("SELECT * FROM " . GetTableName('coursebuy') . " WHERE id = :id ", array(':id' => $courid));
		if($course){
			$kcsign = pdo_fetch("SELECT * FROM " . GetTableName('kcsign') . " WHERE sid = :sid And kcid = :kcid ", array(':sid' => $course['sid'],':kcid' => $course['kcid']));
			if($kcsign){
				pdo_delete(GetTableName('kcsign',false), array('sid' => $course['sid'],'kcid' => $course['kcid']));
			}
			if($course['orderid'] >= 0){
				$order = pdo_fetch("SELECT team_id FROM " . GetTableName('order') . " WHERE id = :id ", array(':id' => $course['orderid']));
				if($order){
					if($order['team_id'] >= 0){
						pdo_delete(GetTableName('sale_team',false), array('id' => $order['team_id']));
					}
					pdo_delete(GetTableName('order',false), array('id' => $course['orderid']));
				}
			}
			pdo_delete(GetTableName('coursebuy',false), array('id' => $courid));
		}
        $data ['result'] = true;
        die (json_encode($data));
    } elseif ($operation == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $this->imessage('抱歉，本条信息不存在在或是已经被删除！', referer(), 'error');
        }
        pdo_delete($this->table_order, array('id' => $id));
        $this->imessage('删除成功！', referer(), 'success');
    } elseif ($operation == 'tuifei') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
        }
        $data = array('status' => 3);
        pdo_update($this->table_order, $data, array('id' => $id));
        $this->imessage('删除成功！', referer(), 'success');
    } elseif ($operation == 'deleteall') {
        $rowcount = 0;
        $notrowcount = 0;
        foreach ($_GPC['idArr'] as $k => $id) {
            $id = intval($id);
            if (!empty($id)) {
                $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id", array(':id' => $id));
                if (empty($goods)) {
                    $notrowcount++;
                    continue;
                }
                pdo_delete($this->table_order, array('id' => $id, 'weid' => $weid));
                $rowcount++;
            }
        }
        $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
        $data ['result'] = true;
        $data ['msg'] = $message;
        die (json_encode($data));
    } elseif($operation == 'GrantKs'){
        $data = array(
            'weid' => $weid,
            'schoolid' => $_GPC['schoolid'],
            'sid' => $_GPC['sid'],
            'kcid' => $_GPC['kcid'],
            'ksnum' => $_GPC['ksnum'],
            'beizhu' => $_GPC['beizhu'],
            'createtime' => time(),
            'tid' => intval($tid_global) != 0 ? $tid_global : '-1',
        );
        pdo_insert(GetTableName('freekslog',false),$data);
        $stucourse = pdo_fetch("SELECT ksnum FROM ".GetTableName('coursebuy')." WHERE sid = '{$_GPC['sid']}' AND kcid = '{$_GPC['kcid']}' ");
        $ksnum = $stucourse['ksnum']+$_GPC['ksnum'];
        pdo_update(GetTableName('coursebuy',false),array('ksnum'=>$ksnum),array('sid'=>$_GPC['sid'],'kcid'=>$_GPC['kcid']));
        $result ['msg'] = '操作成功';
        die (json_encode($result));
    } elseif($operation == 'GetGrantKs'){
        $AllGrantKs = pdo_fetchall("SELECT * FROM ".GetTableName('freekslog')." WHERE sid = '{$_GPC['sid']}' AND kcid = '{$_GPC['kcid']}' ");
        $stu = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id = '{$_GPC['sid']}' ");
        $kc = pdo_fetch("SELECT name FROM ".GetTableName('tcourse')." WHERE id = '{$_GPC['kcid']}' ");
        foreach ($AllGrantKs as $key => $value) {
            $AllGrantKs[$key]['s_name'] = $stu['s_name'];
            $AllGrantKs[$key]['kcname'] = $kc['name'];
            $AllGrantKs[$key]['createtime'] = date("Y-m-d H:i",$value['createtime']);
            if($value['tid'] != -1){
                $tea = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE id = '{$value['tid']}' ");
                $AllGrantKs[$key]['czy'] = $tea['tname'];
            }else{
                $AllGrantKs[$key]['czy'] = '管理员';
            }
        }
        if($AllGrantKs){
            $result ['status'] = true;
            $result ['data'] = $AllGrantKs;

        }else{
            $result ['status'] = false;
            $result ['msg'] = '没有赠送记录';
        }
        die (json_encode($result));
    } elseif($operation == 'ZhuanKc'){
        $sid = $_GPC['sid'];
        $schoolid = $_GPC['schoolid'];
        $oldkcid = $_GPC['oldkcid']; //转班前的课程
        $newkcid = $_GPC['newkcid'];
        //获取转班前的课程信息
        $checkcoursebuy = pdo_fetch("SELECT * FROM " .GetTableName('coursebuy') . " WHERE kcid = '{$oldkcid}' and sid = '{$sid}' ");

        //学生在之前班级消耗的课时
        $stusign = pdo_fetchcolumn("SELECT SUM(costnum) FROM " .GetTableName('kcsign') . " WHERE kcid = '{$oldkcid}' and sid = {$sid} and status = 2 "); 

        $insertKcBuyData = array(
            'weid' =>$weid,
            'schoolid' =>$schoolid,
            'userid' => $checkcoursebuy['userid'],
            'sid' => $sid,
            'kcid' => $newkcid,
            'ksnum' => $checkcoursebuy['ksnum'] - $stusign,
            'createtime' => time(),
            'overtime' => $checkcoursebuy['overtime'],
            'is_change' => 2,
            'change_id'=>$oldkcid
        );

        $insertOrderData = array(
            'weid' => $weid,
            'schoolid' =>$schoolid,
            'sid' => $sid,
            'kcid' => $newkcid,
            'status' => 2,
            'type' => 1,
            'createtime' => time(),
            'ksnum' =>  $checkcoursebuy['ksnum'] - $stusign,
        );
        //是否存在过班级
        $checkIsBuy = pdo_fetch("SELECT id,is_change FROM " .GetTableName('coursebuy') . " WHERE kcid = '{$newkcid}' and sid = '{$sid}' ");
        if(!empty($checkIsBuy)){ //如果目标班级是已经出现过的
            if($checkIsBuy['is_change'] == 1){ //转回来
                //学生在新的班级消耗的课时
                $targetUsedKsnum = pdo_fetchcolumn("SELECT SUM(costnum) FROM " .GetTableName('kcsign') . " WHERE kcid = '{$newkcid}' and sid = {$sid} and status = 2  "); 
                $ChangeData = array(
                    'ksnum' => $checkcoursebuy['ksnum'] - $stusign + $targetUsedKsnum,
                    'is_change' => 0
                );
            }else{ //合并
                //学生在新的班级消耗的课时
                $targetUsedKsnum = pdo_fetchcolumn("SELECT ksnum FROM " .GetTableName('coursebuy') . " WHERE kcid = '{$newkcid}' and sid = {$sid}"); 
                $ChangeData = array(
                    'ksnum' => $checkcoursebuy['ksnum'] - $stusign + $targetUsedKsnum,
                    'is_change' => 0
                );
            }
            pdo_update(GetTableName('coursebuy',false),$ChangeData,array('id'=>$checkIsBuy['id']));
            pdo_update(GetTableName('coursebuy',false),array('ksnum'=>0,'is_change' => 1,'change_id'=>$newkcid),array('id'=>$checkcoursebuy['id']));
            $BeforeKcName = pdo_fetch("SELECT name FROM " .GetTableName('tcourse') . " WHERE id = :id", array(':id' => $checkcoursebuy['kcid']));
            $this->sendMobileZbtz($sid,$newkcid,$BeforeKcName['name'],$checkcoursebuy['schoolid'], $checkcoursebuy['weid']);
        }else{ //如果目标班级未出现过
            pdo_insert(GetTableName('order',false),$insertOrderData);
            pdo_insert(GetTableName('coursebuy',false),$insertKcBuyData);
            pdo_update(GetTableName('coursebuy',false),array('ksnum'=>0,'is_change' => 1,'change_id'=>$newkcid),array('id'=>$checkcoursebuy['id']));
            //发送模版消息
            $BeforeKcName = pdo_fetch("SELECT name FROM " .GetTableName('tcourse') . " WHERE id = :id", array(':id' => $checkcoursebuy['kcid']));
            $this->sendMobileZbtz($sid,$newkcid,$BeforeKcName['name'],$checkcoursebuy['schoolid'], $checkcoursebuy['weid']);
        }
        //新增转班记录
        $changbjlog = array(
            'weid' => $weid,
            'schoolid' =>$schoolid,
            'sid' => $sid,
            'beforekcid' => $oldkcid,
            'afterkcid' => $newkcid,
            'createtime' => time(),
            'tid' =>  intval($tid_global) != 0 ? $tid_global : '-1',
        );
        pdo_insert(GetTableName('changbjlog',false),$changbjlog);

        $result['msg'] = '转课程成功';
        die (json_encode($result));
    } elseif($operation == 'ZhuanBj'){
        $sid = $_GPC['sid'];
        $schoolid = $_GPC['schoolid'];
        $kcid = $_GPC['kcid']; 
        $bjid = $_GPC['bjid'];
        //获取转班前的课程信息
        $hascoursebuy = pdo_fetch("SELECT id FROM " .GetTableName('coursebuy') . " WHERE kcid = '{$kcid}' and sid = '{$sid}' ");
        if($hascoursebuy){
            pdo_update(GetTableName('coursebuy',false),array('bjid'=>$bjid),array('id'=>$hascoursebuy['id']));
            $result['msg'] = '转班成功';
            $result['retult'] = true;
        }else{
            $result['msg'] = '数据有无,无购买课程记录';
            $result['retult'] = false;
        }
        die (json_encode($result));
    } 
    include $this->template ( 'web/baoming' );
?>