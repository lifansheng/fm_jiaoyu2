<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
mload()->model('que');
$schooltype  = $_W['schooltype'];
$lastid = pdo_fetch("SELECT id FROM " . tablename($this->table_class) . " ORDER by id DESC LIMIT 0,1");
$lastids = empty($lastid['id']) ? 1000 :$lastid['id'];
$weid              = $_W['uniacid'];
$this1             = 'no3';
$action            = 'notice';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,spic,tpic FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$bjlist            = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid And type = :type ORDER BY sid ASC, ssort DESC", array(
    ':weid'     => $weid,
    ':schoolid' => $schoolid,
    ':type'     => 'theclass'
));

$fzlist            = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid And type = :type ORDER BY sid ASC, ssort DESC", array(
    ':weid'     => $weid,
    ':schoolid' => $schoolid,
    ':type'     => 'jsfz'
));
$fzlist[] = array(
    'sid' => 0,
    'sname' => '未分组'
);
$techerlist        = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " WHERE weid = :weid AND schoolid = :schoolid ORDER BY CONVERT(tname USING gbk)  ASC", array(
    ':weid'     => $weid,
    ':schoolid' => $schoolid,
));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    $params    = array();
    if(!empty($_GPC['keyword'])){
        $condition          .= " AND title LIKE '%{$_GPC['keyword']}%'";
    }
    if(!empty($_GPC['bj_id'])){
		if($schooltype){
			$condition .= " AND kc_id = '{$_GPC['bj_id']}'";
		}else{
			$condition .= " AND bj_id = '{$_GPC['bj_id']}'";
		}
    }
	//获取内容
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_notice) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 5 $condition ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $key => $row){
        if($row['usertype'] == 'school'){
            $list[$key]['sendtype'] = '全校师生';
        }elseif($row['usertype'] == 'alltea'){
            $list[$key]['sendtype'] = '全体老师';
        }elseif($row['usertype'] == 'allstu'){
            $list[$key]['sendtype'] = '全体家长';
        }elseif($row['usertype'] == 'bj'){
            $bjlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE FIND_IN_SET(sid,'{$row['userdatas']}') ");
            
            $list[$key]['sendtype'] = arrayToString($bjlist);
        }elseif($row['usertype'] == 'jsfz'){
            $fzlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE FIND_IN_SET(sid,'{$row['userdatas']}') ");
            if(in_array('0',explode(',',$row['userdatas']))){
                $fzlist[] = array(
                    'sname' => '未分组'
                );
            }
            $list[$key]['sendtype'] = arrayToString($fzlist);
        }
    }
    //获取总个数
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_notice) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 $condition");
    $pager = pagination($total, $pindex, $psize);

}elseif($operation == 'post'){
    if(checksubmit('submit')){
        if(empty($_GPC['title'])){
            $this->imessage('请输入标题！');
        }
        if(empty($_GPC['tid'])){
            $this->imessage('请选择发送者老师');
        }
        $teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = '{$_GPC['tid']}'");
        $temp = array(
            'weid'       => $weid,
            'schoolid'   => $schoolid,
            'tid'        => $_GPC['tid'],
            'tname'      => $teacher['tname'],
            'title'      => $_GPC['title'],
            'content'    => htmlspecialchars_decode($_GPC['content']),
            'createtime' => time(),
            'starttime' => strtotime($_GPC['timerange']['start']),
            'endtime' => strtotime($_GPC['timerange']['end'])+86399,
            'type'       => 5,
            'is_research'=> 1,
            'ismobile'   => 1,
        );

        if ($_GPC['sendtype'] == 1) {
            $temp['usertype'] = 'school';
        }
        if ($_GPC['sendtype'] == 2) {
            $temp['usertype'] = 'alltea';
        }
        if ($_GPC['sendtype'] == 3) {
            $temp['usertype'] = 'allstu';
        }
        if ($_GPC['sendtype'] == 4) {
            $temp['usertype'] = 'jsfz';
            $temp['userdatas'] = arrayToString($_GPC['send_id']);
        }
        if ($_GPC['sendtype'] == 5) {
            $temp['usertype'] = 'bj';
            $temp['userdatas'] = arrayToString($_GPC['send_id']);
        }
        $is_send = $_GPC['is_send'];
        pdo_insert($this->table_notice, $temp);
        $notice_id = pdo_insertid();
        // 如果开启了模板通知
        
            // (问卷写入)
            $qorder = 1;
            $contents = $_GPC['contents'];
            $ques_title = $_GPC['ques_title'];
            $contentss = $_GPC['contentsss'];
            $ques_type = $_GPC['ques_type'];
            $ques_anspoint = $_GPC['ques_anspoint'];
            if(!empty($ques_title)){
                foreach($ques_title as $key=>$value){
                    $WJ_temp = array(
                        'weid'     => $weid,
                        'schoolid' => $schoolid,
                        'tid'      => $_GPC['tid'],
                        'zyid'     => $notice_id,
                        'qorder'   => $qorder,
                        'title'    => $ques_title[$key],
                        'type'     => $ques_type[$key]
                    );
                    //单选
                    if($ques_type[$key] == 1 ){
                        $i=1;
                        $temp1=[];
                        foreach ($contents[$key] as $keys=>$values){
                            $temp1[$i] = array(
                                'title' =>$values);
                            $temp1[$i]['is_answer'] ='No';
                            foreach($contentss[$key] as $keyss=>$valuess){
                                if($valuess == $i){ //输出答案
                                    $temp1[$i]['is_answer'] = 'Yes';
                                }
                            }
                            $i++;
                        }

                        $temp11 = iserializer($temp1);
                        $WJ_temp['content'] = $temp11;
                        //多选
                    }elseif($ques_type[$key] == 2){
                        $i=1;

                        $temp2;
                        foreach ($contents[$key] as $keys=>$values){
                            foreach($contentss[$key] as $keyss=>$valuess){
                                if($valuess == $i)
                                {
                                }else{
                                    //echo "</br>";
                                }
                            }
                            $temp2[$i] = array(
                                'title' =>$values);
                            $temp2[$i]['is_answer'] ='No';
                            foreach($contentss[$key] as $keyss=>$valuess){
                                if($valuess == $i){
                                    //echo "\n答案";
                                    $temp2[$i]['is_answer'] = 'Yes';
                                }
                            }
                            $i++;
                        }
                        $temp22 = iserializer($temp2);
                        $WJ_temp['content'] = $temp22;
                    }elseif($ques_type[$key] == 3){	//问答题
                        $anspoint =  $ques_anspoint[$key];
                        $WJ_temp['content'] = $anspoint;
                    }
                    pdo_insert($this->table_questions,$WJ_temp);
                    $qorder = $qorder + 1 ;
                }
            }
            $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
            $data = array(
                'notice_id' => $notice_id,
                'schoolid' => $schoolid,
                'weid' => $weid,
                'tname' => $teacher['tname'],
                'schooltype' => $schooltype,
                'op' => 'sendMobileQuesForm',
            );
            if($is_send == 1){
                $res = timeOutPost($url, $data);
                // dd($res);
            }
            $this->imessage('发送成功！', $this->createWebUrl('createquesform', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'deleteallrecord'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $item = pdo_fetch("SELECT * FROM " . tablename($this->table_record) . " WHERE id = :id", array(':id' => $id));
            if(empty($item)){
                $notrowcount++;
                continue;
            }
            pdo_delete($this->table_record, array('id' => $id));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}elseif($operation == 'lookInfo'){

    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item   = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " WHERE id = '{$id}'");
        $bj     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid ", array(':sid' => $item['bj_id']));
        $anstype = iunserializer($item['anstype']);
        $ZY_contents = GetZyContent($id,$schoolid,$weid);
		$userdatas = explode(',',$item['userdatas']);
		$dataarr = array();
		foreach($userdatas as $row){
			if($row == 0 || $row != ""){
				$dataarr[] = intval($row);
			}	
		}			
		if($item['usertype'] == 'bj'){
			mload()->model('stu');
			$arr = GetClassInfoByArr($dataarr,$_W['schooltype'],$schoolid);
        }	
		if($item['usertype'] == 'jsfz'){
			mload()->model('tea');
			$arr = GetFzInfoByArr($dataarr,$_W['schooltype'],$schoolid);
		}	
        if(empty($item)){
            $this->imessage('抱歉，本信息不存在在或是已经删除！', '', 'error');
        }
    }
}elseif($operation == 'quesInfo'){
    $fzlist = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = :schoolid AND type = :type ",array(':schoolid'=>$schoolid,':type'=>'jsfz'));
    $bjlist = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = :schoolid AND type = :type ",array(':schoolid'=>$schoolid,':type'=>'theclass'));
    $pindex    = max(1, intval($_GPC['page']));
    $notice_id = intval($_GPC['notice_id']);
    $notice    = pdo_fetch("SELECT title,usertype,userdatas,createtime FROM " . tablename($this->table_notice) . " where id = :id ", array(':id' => $notice_id));
    $psize     = 100;
    $condition = '';
    $is_pay = isset($_GPC['is_pay']) ? intval($_GPC['is_pay']) : -1;
    if($is_pay >= 0){
        if($is_pay == 2){
            $nowtime   = TIMESTAMP;
            $condition .= " HAVING createtime > 0";
        }else if($is_pay == 1){
            $condition .= " HAVING createtime <= 0 ";
        }
    }
    if($_GPC['shenfen'] == 1){ //老师
        if($notice['usertype'] == 'alltea' || $notice['usertype'] == 'jsfz' || $notice['usertype'] == 'school'){
            $condition .= " AND t.fz_id = '{$_GPC['fz_id']}' ";
        }else{
            $condition .= " AND 1 != 1 ";
        }
    }
    if($_GPC['shenfen'] == 2){ //老师
        if($notice['usertype'] == 'allstu' || $notice['usertype'] == 'bj' || $notice['usertype'] == 'school'){
            $condition .= " AND s.bj_id = '{$_GPC['bj_id']}' ";
        }else{
            $condition .= " AND 1 != 1 ";
        }
    }
    // dd($condition);
    // if($_GPC['bj_id'] && ($notice['usertype'] != 'alltea' && $notice['usertype'] != 'jsfz')){
    //     $condition .= " AND s.bj_id = '{$_GPC['bj_id']}' ";
    // }
    // if($_GPC['fz_id'] && ($notice['usertype'] != 'allstu' && $notice['usertype'] != 'bj')){
    //     $condition .= " AND t.fz_id = '{$_GPC['fz_id']}' ";
    // }
    // $testAA = GetMyAnswerAll($it['sid'] ,$leaveid,$schoolid,$weid);
    // $testAA = GetMyAnswerAll_tea($it['tid'] ,$leaveid,$schoolid,$weid);
    // pdo_fetchall("SELECT * FROM " . tablename('wx_school_answers') . " where tid= :tid AND zyid = :zyid  ORDER by tmid  ", array( ':tid' =>$tid, ':zyid' => $zyid));
    if($notice['usertype'] == 'school'){
        $teachers = "SELECT t.id,t.tname as name,t.thumb as icon,c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$notice_id}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}' $condition ORDER BY t.id DESC";

        $students = "SELECT s.id,s.s_name as name,s.icon as icon,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$notice_id}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' $condition ORDER BY s.id DESC";
        $sql = " SELECT * FROM ( {$teachers} ) as t union ( {$students} ) ";
    }elseif($notice['usertype'] == 'alltea'){
        $sql = "SELECT t.id,t.tname as name,t.thumb as icon,c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$notice_id}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}' $condition";
    }elseif($notice['usertype'] == 'allstu'){
        $sql = "SELECT s.id,s.s_name as name,s.icon as icon,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$notice_id}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' $condition";
    }elseif($notice['usertype'] == 'bj'){
        $sql = "SELECT s.id,s.s_name as name,s.icon as icon,c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$notice_id}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}' AND FIND_IN_SET(s.bj_id,'{$notice['userdatas']}') $condition";
    }elseif($notice['usertype'] == 'jsfz'){
        $sql = "SELECT t.id,t.tname as name,t.thumb as icon,c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$notice_id}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}' AND FIND_IN_SET(t.fz_id,'{$notice['userdatas']}') $condition";
    }
    $list = pdo_fetchall($sql." ORDER BY usertype DESC,CONVERT(name USING gbk) ASC LIMIT ".($pindex - 1) * $psize . ',' . $psize);
    foreach($list as &$row_l) {
        if(empty($row_l['icon'])){
            $row_l['icon'] = $row_l['usertype'] == 1 ? $logo['tpic'] : $logo['spic'];
        }
    }
    $total = pdo_fetchall($sql);
    $pager = pagination(count($total), $pindex, $psize);
}elseif($operation == 'delete1'){
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id FROM " . tablename($this->table_notice) . " WHERE id = '$id'");
    if(empty($item)){
        $this->imessage('抱歉，不存在或是已经被删除！', 'error');
    }
    pdo_delete($this->table_notice, array('id' => $id));
    pdo_delete($this->table_record, array('noticeid' => $id));
    pdo_delete($this->table_questions, array('zyid' => $id));
    pdo_delete($this->table_answers, array('zyid' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
	$rowcount    = 0;
	$notrowcount = 0;
	foreach($_GPC['idArr'] as $k => $id){
		$id = intval($id);
		if(!empty($id)){
			$item = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " WHERE id = :id", array(':id' => $id));
			if(empty($item)){
				$notrowcount++;
				continue;
			}
			pdo_delete($this->table_record, array('noticeid' => $id));
			pdo_delete($this->table_notice, array('id' => $id));
		  	pdo_delete($this->table_questions, array('zyid' => $id));
    		pdo_delete($this->table_answers, array('zyid' => $id));
			$rowcount++;
		}
	}
	$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

	$data ['result'] = true;

	$data ['msg'] = $message;

	die (json_encode($data));
}elseif($operation == 'lookQuesInfo'){
	$notice_id = intval($_GPC['notice_id']);
	$id = intval($_GPC['id']);
    $usertype = intval($_GPC['usertype']);
    $title = $_GPC['title'];
    if($_GPC['usertype'] == 1){
        $testAA = GetMyAnswerAll_tea($id,$notice_id,$schoolid,$weid);
    }elseif($_GPC['usertype'] == 0){
        $testAA = GetMyAnswerAll($id,$notice_id,$schoolid,$weid);
    }
    $ZY_contents = GetZyContent($notice_id,$schoolid,$weid);
	include $this->template('web/quesform_bot');
	die();
}


include $this->template('web/createquesform');
