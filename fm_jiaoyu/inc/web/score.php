<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'score';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000231,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    if(!empty($_GPC['ssort'])){
        foreach($_GPC['ssort'] as $sid => $ssort){
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', referer(), 'success');
    }
    $children = array();
    $score    = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And (type = 'score' or  type = 'xq_score') And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
    foreach($score as $index => $row){
        if(!empty($row['parentid'])){
            $children[$row['parentid']][] = $row;
            unset($score[$index]);
        }
    }
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000232,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $sid      = intval($_GPC['sid']);
	if($_GPC['qhtype'] == 2){
		$bj_id = implode(',', $_GPC['arr']);
	}else{
		$bj_id = '';
	}	
    if(!empty($sid)){
        $score = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '$sid' ");
    }else{
        $score = array(
            'ssort' => 0,
        );
    }
    $banji  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	$uniarr = explode(',', $score['qh_bjlist']);
	
	$subject  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type  ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
	$this_addinfo = json_decode($score['addedinfo'],true);
	
	if(!empty($this_addinfo['sub_arr'])){
		$this_subjectarr = explode(',', $this_addinfo['sub_arr']);
	}else{
		$this_subjectarr = array();
	}

    if(checksubmit('submit')){
        if(empty($_GPC['catename'])){
            $this->imessage('抱歉，请输入名称！', referer(), 'error');
        }

		if($_GPC['scoretype'] == 'score'){
            $data = array(
				'weid'     => $weid,
				'schoolid' => $_GPC['schoolid'],
				'sname'    => $_GPC['catename'],
				'ssort'    => intval($_GPC['ssort']),
				'qh_bjlist'     => $bj_id,
				'qhtype'     => $_GPC['qhtype'],
				'type'     => 'score',
                'sd_start' => 0,
                'sd_end' => 0,
			);
            if (is_showpf()) {
                $is_review         = $_GPC['is_review'];
                $data['is_review'] = $is_review;
                if ($is_review == 1) {
                    $addinfo['review_per'] = $_GPC['review_p'];
                    $sub_arr               = implode(',', $_GPC['sub_arr']);
                    $addinfo['sub_arr']    = $sub_arr;
                    $data['addedinfo']     = json_encode($addinfo);
                } else {
                    $data['addedinfo'] = '';
                }
            }
		}elseif($_GPC['scoretype'] == 'xq_score'){
			$start_t =strtotime($_GPC['start']);
			$firstlast =strtotime($_GPC['firstlast']);
			$laststart =strtotime($_GPC['laststart']);
			$end_t = strtotime($_GPC['end']);
			$scoreyear = $_GPC['scoreyear'];
			if($start_t >=$end_t){
				$this->imessage('抱歉，开始日期必须小于结束日期！', referer(), 'error');
			}
			$check = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE type='xq_score' and schoolid = '{$schoolid}' and weid = '{$weid}' and (( sd_start < '{$start_t}' and sd_end > '{$start_t}') or (sd_start < '{$end_t}' and sd_end > '{$end_t}') or (sd_start > '{$start_t}' and sd_start < '{$end_t}')) ");
			
			if(!empty($check)){
				$this->imessage("抱歉，时段有重复，请重新设置{$check['sid']}！", referer(), 'error');
			}
            
			$data = array(
				'weid'     => $weid,
				'schoolid' => $_GPC['schoolid'],
				'sname'    => $_GPC['catename'],
				'ssort'    => intval($_GPC['ssort']),
				'type'     => 'xq_score',
				'is_show_qh'     => 'is_show_qh',
				'sd_start' => $start_t,
                'sd_end' => $end_t,
            );
		}
        $addedinfo = '';
        if(keep_MC()){
            if(empty($_GPC['othertip'])){
                $this->imessage("抱歉，请填写提示文字！", referer(), 'error');
            }else {
                $addedinfo = json_encode(array(
                    'othertip' => $_GPC['othertip']
                ));
            }
            $data['addedinfo'] = $addedinfo;
            $data['firstlast'] = $firstlast;
            $data['laststart'] = $laststart;
            $data['scoreyear'] = $scoreyear;
            
        }
        if(!empty($sid)){
            unset($data['parentid']);
            pdo_update($this->table_classify, $data, array('sid' => $sid));
        }else{
            pdo_insert($this->table_classify, $data);
            $sid = pdo_insertid();
        }
        $ext = '';
        if($_GPC['scoretype'] == 'xq_score' && keep_MC()){
            mload()->model('znl');
            $rr = znlSyncSemester($sid,$schoolid);
            if($rr['status']==false){
                $ext = $rr['msg'];
            }
        }
        $this->imessage('更新成绩成功！'.$ext,$this->createWebUrl('score', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'ChangeShow') {
    $check = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$_GPC['sid']}' ");
    if(!empty($check)){
        pdo_update(GetTableName('classify',false),array('is_show_qh' =>$_GPC['is_show']),array('sid'=>$_GPC['sid']));
    }
}
elseif($operation == 'delete'){
    $sid   = intval($_GPC['sid']);
    $score = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '$sid'");
    if(empty($score)){
        $this->imessage('抱歉，成绩不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('成绩删除成功！', referer(), 'success');
}

elseif($operation == 'SyncYear'){
    mload()->model('znl');
    $id = $_GPC['id'];
    $rr = znlSyncSemester($id,$schoolid);
    die($rr);
}
include $this->template('web/score');
?>