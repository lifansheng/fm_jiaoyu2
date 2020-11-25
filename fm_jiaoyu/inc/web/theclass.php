<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'theclass';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$alltag = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'teatag' ");
mload()->model('xzf');
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000221,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 20;	
    if(!empty($_GPC['ssort'])){
        foreach($_GPC['ssort'] as $sid => $ssort){
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', $this->createWebUrl('theclass', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
    $theclass = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = '{$schoolid}' ORDER BY  ssort DESC , sid ASC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);	
    foreach($theclass as $key => $row){
        $teacher                  = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $row['tid']));
        $xueqi                    = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $row['parentid']));
        $renshu                   = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And bj_id = :bj_id", array(':schoolid' => $schoolid, ':bj_id' => $row['sid']));
		$bjqsm                   = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_bjq) . " WHERE schoolid = {$schoolid} And (bj_id1 = '{$row['sid']}' or bj_id2 = '{$row['sid']}' or bj_id3 = '{$row['sid']}')");
		$theclass[$key]['bjqsm']   = $bjqsm;
        $theclass[$key]['name']   = $teacher['tname'];
        $theclass[$key]['xueqi']  = $xueqi['sname'];
        $theclass[$key]['renshu'] = $renshu;
    }
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And type = 'theclass'");
	$pager = pagination($total, $pindex, $psize);
	//////////导出班级数据/////////////////
	if($_GPC['out_putcode'] == 'out_putcode'){
		$listss = pdo_fetchall("SELECT sid,parentid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And type = 'theclass' ORDER BY ssort DESC");
		$ii   = 0;
		foreach($listss as $index => $row){
			$njinfo = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$row['parentid']}'");
			$arr[$ii]['sname'] = $row['sname'];
			$arr[$ii]['sid']  = $row['sid'];
			$arr[$ii]['sname2']= $njinfo['sname'];
			$ii++;
		}
		$this->exportexcel($arr, array('班级名称','班级ID','所属年级'), '班级ID信息对照表');
		exit();
	}
	//////////导出科目数据/////////////////
	if($_GPC['out_putsub'] == 'out_putsub'){
		$listss = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' And type = 'subject' ORDER BY ssort DESC");
		$ii   = 0;
		foreach($listss as $index => $row){
			$arr[$ii]['sname'] = $row['sname'];
			$arr[$ii]['sid']  = $row['sid'];
			$ii++;
		}
		$this->exportexcel($arr, array('科目名称','科目ID'), '科目ID信息对照表');
		exit();
	}
	////////////////////////////////	
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000222,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    load()->func('tpl');
    $parentid = intval($_GPC['parentid']);
    $sid      = intval($_GPC['sid']);
	$xueqi             = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
    $allls    = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid", array(':schoolid' => $schoolid));
    if(!empty($sid)){
        $theclass = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '$sid'");
		$tname = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$theclass['tid']}'");
    }
    if(checksubmit('submit')){
        $lastedittime = time();
        if(!empty($sid)){
			if(!empty($_GPC['old'])){
				$checname    = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sname = :sname And type = :type", array(':schoolid' => $schoolid,':sname' => trim($_GPC['catename']),':type' => 'theclass'));
				
				if(!$_GPC['parentid']){
					$this->imessage('抱歉，请选择所属年级！', referer(), 'error');
				}
				$data = array(
					'weid'       => $weid,
					'schoolid'   => $schoolid,
					'sname'      => trim($_GPC['catename']),
					'ssort'      => intval($_GPC['ssort']),
					'type'       => 'theclass',
					'erwei'      => trim($_GPC['erwei']),
					'qun'        => trim($_GPC['qun']),
					'cost'       => trim($_GPC['cost']),
					'class_device'       => trim($_GPC['class_device']),
					'video'      => trim($_GPC['video']),
					'video1'     => trim($_GPC['video1']),
					'videostart' => trim($_GPC['videostart']),
					'videoend'   => trim($_GPC['videoend']),
					'allowpy'    => trim($_GPC['allowpy']),
					'parentid'   => intval($parentid),
                    'lastedittime' => $lastedittime,
				);
				if($_GPC['tid']){
					$data['tid'] = trim($_GPC['tid']);
                }
                if($checname['sname'] != $checname['sname'] || $checname['parentid'] != $parentid){
					setXzfNeedsync($sid,'classify');
				}
				pdo_update($this->table_classify, $data, array('sid' => $sid));
				$edittitle = '成功修改名称为：'.trim($_GPC['catename']);
			}
			if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					$checkname    = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sname = :sname And type = :type", array(':schoolid' => $schoolid,':sname' => $name,':type' => 'theclass'));
                    if(!empty($checkname)){
                        $ckcount += $f_count + 1;
                        $ckname = '有【'.$ckcount.'】条重复班级名称';
                    }
                    if(empty($name)){
                        $bjcount += $f_count + 1;
                        $bjname = '有【'.$bjcount.'】条班级名称未填写';
                    }
                    if($_GPC['parentid_new'][$key] == '所属年级'){
                        $njcount += $f_count + 1;
                        $njname = '有【'.$njcount.'】条未选择所属年级';
                    }
					$data = array(
						'weid'     => $weid,
						'schoolid' => $_GPC['schoolid'],
						'sname'    => $name,
						'tid'      => trim($_GPC['tid_new'][$key]),
						'ssort'    => intval($_GPC['ssort_new'][$key]),
						'cost'     => trim($_GPC['cost_new'][$key]),
						'class_device'       => trim($_GPC['class_device']),
						'parentid' => intval($_GPC['parentid_new'][$key]),
						'type'     => 'theclass',
                        'lastedittime' => $lastedittime,
					);
                    if(empty($checkname) && !empty($name) && $_GPC['parentid_new'][$key] != '所属年级' ){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下班级:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message = $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$bjname.'<br/>'.$njname.'<br/>'.$ckname.'</span>';
                $this->imessage("$message", referer(), 'success');
			}
			if(is_showZB()){
            CreateHBtodo_ZB($schoolid,$weid,$lastedittime,15);
	    }
			$this->imessage('更新班级成功！', $this->createWebUrl('theclass', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
		}else{
            if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					$checkname    = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE schoolid = :schoolid And sname = :sname And type = :type", array(':schoolid' => $schoolid,':sname' => $name,':type' => 'theclass'));
					if(!empty($checkname)){
                        $ckcount += $f_count + 1;
                        $ckname = '有【'.$ckcount.'】条重复班级名称';
					}
					if(empty($name)){
						$bjcount += $f_count + 1;
						$bjname = '有【'.$bjcount.'】条班级名称未填写';
					}
					if($_GPC['parentid_new'][$key] == '所属年级'){
                        $njcount += $f_count + 1;
                        $njname = '有【'.$njcount.'】条未选择所属年级';
					}
					$data = array(
						'weid'     => $weid,
						'schoolid' => $_GPC['schoolid'],
						'sname'    => $name,
						'tid'      => trim($_GPC['tid_new'][$key]),
						'ssort'    => intval($_GPC['ssort_new'][$key]),
						'cost'     => trim($_GPC['cost_new'][$key]),
						'class_device'       => trim($_GPC['class_device']),
						'parentid' => intval($_GPC['parentid_new'][$key]),
						'type'     => 'theclass',
                        'lastedittime' => $lastedittime,
					);
					if(empty($checkname) && !empty($name) && $_GPC['parentid_new'][$key] != '所属年级' ){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下班级:';
                        $msg .= $name.'|';
                    }
                }
                $msg = rtrim($msg, "|");
                $message = $success.$msg.'<br/><span style="color:red;">'.$bjname.'<br/>'.$njname.'<br/>'.$ckname.'</span>';
               if(is_showZB()){
	        CreateHBtodo_ZB($schoolid,$weid,$lastedittime,15);
		}
				$this->imessage("$message", $this->createWebUrl('theclass', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
		}
    }
}elseif($operation == 'change'){
	$id    = intval($_GPC['id']);
	$is_on = intval($_GPC['is_on']);
	$data = array('is_bjzx' => $is_on);
	pdo_update($this->table_classify, $data, array('sid' => $id));	
}elseif($operation == 'change_over'){
	$id    = intval($_GPC['id']);
	$is_over = intval($_GPC['is_over']);
	$data1 = array('is_over' => $is_over);
	pdo_update($this->table_classify, $data1, array('sid' => $id));	
}
elseif($operation == 'delete'){
    $sid      = intval($_GPC['sid']);
    $theclass = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
	$checkstud = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE bj_id = '{$sid}'");
    if(empty($theclass)){
        $this->imessage('抱歉，班级不存在或是已经被删除！', referer(), 'error');
    }
    if(!empty($checkstud)){
        $this->imessage('抱歉，本班仍有学生,请尝试转移后删除本！', referer(), 'error');
    }
    //校智付删除班级操作
	setXzfExtra($sid,1);
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
	pdo_delete($this->table_bjq, array('bj_id1' => $sid));
	pdo_delete($this->table_bjq, array('bj_id2' => $sid));
	pdo_delete($this->table_bjq, array('bj_id3' => $sid));
	pdo_delete($this->table_media, array('bj_id1' => $sid));
	pdo_delete($this->table_media, array('bj_id2' => $sid));
    pdo_delete($this->table_media, array('bj_id3' => $sid));
    pdo_delete(GetTableName('user_class',false),array('bj_id'=>$sid));	
    $this->imessage('班级删除成功！', $this->createWebUrl('theclass', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}

elseif($operation == 'setstarname'){
    $bj_id = $_GPC['bj_id'];
    $star_nam1 = $_GPC['star_name1']?$_GPC['star_name1']:'';
    $star_nam2 = $_GPC['star_name2']?$_GPC['star_name2']:'';
    $star_nam3 = $_GPC['star_name3']?$_GPC['star_name3']:'';
    $star_nam4 = $_GPC['star_name4']?$_GPC['star_name4']:'';
    $input_data = array(
       'star_name1' => $star_nam1,
       'star_name2' => $star_nam2,
       'star_name3' => $star_nam3,
       'star_name4' => $star_nam4,
    );
    $data = json_encode($input_data);
    pdo_update($this->table_classify, array('addedinfo'=>$data),array('sid' => $bj_id));
    $result['msg'] = '设置成功';
    $result['status'] = 1;
    die(json_encode($result));
}
elseif($operation == 'getstarname'){
    $bj_id = $_GPC['bj_id'];
    $bjstarname = pdo_fetch("SELECT addedinfo,sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$bj_id}'");
    if(!empty($bjstarname['addedinfo']))
        $starname = json_decode($bjstarname['addedinfo'],true);
    elseif (empty($bjstarname['addedinfo']))
        $starname = array();
    $result['bjname'] = $bjstarname['sname'];
    $result['star_name1'] = $starname['star_name1'];
    $result['star_name2'] = $starname['star_name2'];
    $result['star_name3'] = $starname['star_name3'];
    $result['star_name4'] = $starname['star_name4'];
    die(json_encode($result));
}elseif ($operation == 'getchecksendset') {
    $schoolid = $_GPC['schoolid'];
    $bjid = $_GPC['bjid'];
    $bjinfo =  pdo_fetch("select checksendset,sname from " . tablename($this->table_classify) . " where sid=:sid and weid =:weid", array(':sid' => $bjid, ':weid' => $weid));

    if(!empty($bjinfo['checksendset'])){
        $checksendset =unserialize($bjinfo['checksendset']);
    }else{
        $schoolinfo = pdo_fetch("select checksendset from " . tablename($this->table_index) . " where id=:id and weid =:weid", array(':id' => $schoolid, ':weid' => $weid));
        if(!empty($schoolinfo)){
            $checksendset =unserialize($schoolinfo['checksendset']);
        }
        else{
            $checksendset = array('students','parents','head_teacher');
        }
    }
    include $this->template('web/checksend_add');
    die();
} elseif ($operation == 'setchecksendset') {
    $schoolid = $_GPC['schoolid'];
    $bjid = $_GPC['bjid'];
    $stu = $_GPC['stu'];
    $pare = $_GPC['pare'];
    $ht = $_GPC['ht'];
    $rt = $_GPC['rt'];
    $sendarr = array();
    if($stu == 'true'){
        $sendarr[]='students';
    }
    if($pare == 'true'){
        $sendarr[]='parents';
    }
    if($ht == 'true'){
        $sendarr[]='head_teacher';
    }
    if($rt == 'true'){
        $sendarr[]='rest_teacher';
    }
    if(!empty($sendarr)){
        $checksendset_in = serialize($sendarr);
    }else{
        $checksendset_in = '';
    }

    pdo_update($this->table_classify, array('checksendset'=>$checksendset_in),array('sid' => $bjid));
    $result['status'] = true;
    $result['msg'] = '修改成功！';

    die(json_encode($result));
}
elseif($operation == 'getclassteacher'){
    $bj_id = $_GPC['bj_id'];
    $tpic = pdo_fetch("SELECT tpic FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
    $AllTea = pdo_fetchall("SELECT t.tname,t.thumb,c.km_id,t.id as tid ,t.tagid  FROM ".GetTableName('teachers')." as t , ".GetTableName('user_class')." as c    WHERE  c.bj_id = '{$bj_id}' and  t.id = c.tid and c.schoolid = '{$schoolid}'  group by c.tid   ");
    $bjinfo = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$bj_id}' ");
    foreach($AllTea as $key=> $value){
        $SubStr = '';
        $AllSub = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$value['km_id']}' ");
        foreach($AllSub as $vs){
            $SubStr .= $vs['sname'].',';
        }
        $SubStr = rtrim($SubStr,',');
        $AllTea[$key]['sub'] = $SubStr;
        $tagname = $value['tagid'] ? pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$value['tagid']}' ")['sname'] : '未设置标签';
        $AllTea[$key]['tagname'] = $tagname;
    }
    include $this->template('web/classteacher_bot');
    die();
}elseif($operation == 'chageteatag'){
    $tagid = $_GPC['tagid'];
    $tid = $_GPC['tid'];
    if($tagid == -1){
        $ntname = $_GPC['ntname'];
        $ins = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'sname' => $ntname,
            'type' => 'teatag'
        );
        pdo_insert(GetTableName('classify',false),$ins);
        $tagid = pdo_insertid();
    }
    pdo_update(GetTableName('teachers',false),array('tagid' => $tagid),array('id'=>$tid));
    die(json_encode(array(
        'status' => true,
        'msg' => '变更标签成功'
    )));
}elseif($operation == 'gettaglist'){
    $list = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'teatag' ");
    die(json_encode($list));
}elseif($operation == 'Synchronize'){
    mload()->model('znl');
    $bjlist = znlMutiStuInfoByClass($_GPC['sid']);
    die(json_encode($bjlist));
}elseif ($operation == 'getRate') {
    $schoolid = $_GPC['schoolid'];
    $bjid = $_GPC['bjid'];
    $rate =  pdo_fetch("select bjrate,bzrrate from " . tablename($this->table_classify) . " where sid=:sid and schoolid =:schoolid", array(':sid' => $bjid, ':schoolid' => $schoolid));
    $result['bjrate'] = $rate['bjrate'];
    $result['bzrrate'] = $rate['bzrrate'];
    die(json_encode($result));
}elseif ($operation == 'setRate') {
    $schoolid = $_GPC['schoolid'];
    $bjid = $_GPC['bjid'];
    $bjrate = $_GPC['bjrate'];
    $bzrrate = $_GPC['bzrrate'];
    if(!empty($bjid)){
        pdo_update(GetTableName('classify',false),array('bjrate'=>$bjrate,'bzrrate'=>$bzrrate),array('sid'=>$bjid,'schoolid'=>$schoolid));
    }
    $result['result'] = true;
    $result['msg'] = '设置成功';
    die(json_encode($result));
}

if($operation == 'syncclass'){
    mload()->model('xzf'); 
	$res = SyncClass($schoolid);
	die(json_encode($res));
}
include $this->template('web/theclass');
?>
