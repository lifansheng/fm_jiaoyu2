<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$this1             = 'no1';
$action            = 'semester';
$action1           = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,is_qx FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$schooltype = $_W['schooltype'];
$tid_global = $_W['tid'];
mload()->model('xzf');
if($tid_global !='founder' && $tid_global != 'owner'){
	$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
	$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
	$toPage = 'semester';
	if( !(strstr($qxarr,'1000211'))){
		$toPage = 'theclass';
	}
	if(!(strstr($qxarr,'1000221')) && $toPage == 'theclass'){
		$toPage = 'score';
	}
	if(!(strstr($qxarr,'1000231')) && $toPage == 'score'){
		$toPage = 'coursetype';
	}
	if(!(strstr($qxarr,'1000241')) && $toPage == 'coursetype'){
		$toPage = 'editaddr';
	}
	if(!(strstr($qxarr,'1000251')) && $toPage == 'editaddr'){
		$toPage = 'subject';
	}
	if(!(strstr($qxarr,'1000261')) && $toPage == 'subject'){
		$toPage = 'timeframe';
	}
	if(!(strstr($qxarr,'1000271')) && $toPage == 'timeframe'){
		$toPage = 'week';
	}
	if(!(strstr($qxarr,'1000281')) && $toPage == 'week'){
		$toPage = 'tscoreobject';
	}
	if(!(strstr($qxarr,'1000291')) && $toPage == 'tscoreobject'){
		$toPage = 'jsfz';
	}
	if($_W['role'] != 'manager' && $toPage == 'jsfz'){
		$toPage = 'NoAccess';
	}

	if($toPage != 'semester' && $toPage != 'NoAccess' ){
		$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl($toPage, array('schoolid' => $schoolid,'op'=>'display'));
		header("location:$stopurl");
	}elseif($toPage == 'NoAccess' ){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
}

			
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000211,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    if(!empty($_GPC['ssort'])){
        foreach($_GPC['ssort'] as $sid => $ssort){
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', referer(), 'success');
    }
    $children = array();
    $semester = pdo_fetchall("SELECT * FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'semester' And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
    foreach($semester as $index => $row){
        if(!empty($row['parentid'])){
            $children[$row['parentid']][] = $row;
            unset($semester[$index]);
        }
        $teacher    = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $row['tid']));
		if($schooltype){
			$bjsm     = pdo_fetchcolumn("SELECT COUNT(*) FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And xq_id = '{$row['sid']}' ");
			$suntype    = pdo_fetchcolumn("SELECT COUNT(sid) FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '{$row['sid']}' And type = 'kcclass' ");
			$semester[$index]['suntype']  = $suntype;
		}else{
			$renshu                     = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_students) . " WHERE schoolid = :schoolid And xq_id = :xq_id", array(':schoolid' => $schoolid, ':xq_id' => $row['sid']));
			$bjsm                     = pdo_fetchcolumn("SELECT COUNT(*) FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '{$row['sid']}' And type = 'theclass'");
		}
		
        $semester[$index]['tname']  = $teacher['tname'];
		$semester[$index]['bjsm']  = $bjsm;
        $semester[$index]['renshu'] = $renshu;
	}
}elseif($operation == 'getsunTypelist'){		
	$sid      = intval($_GPC['sid']);
	$kcclass = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '$sid' And type = 'kcclass' ");
	die(json_encode($kcclass));
}elseif($operation == 'type_info'){	
	$sid      = intval($_GPC['sid']);
	$kcclass = pdo_fetchall("SELECT * FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '$sid' And type = 'kcclass' ");

	include $this->template('web/kc/kc_type');
	return;
	
}elseif($operation == 'del_suntype'){		
	$sid = $_GPC['sid'];
	$checktype = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE  sid = '{$sid}'  ");
	if(!empty($checktype)){
		pdo_delete(GetTableName('classify',false),array('sid'=>$sid));
		$result['msg'] = "删除成功";
		$result['result'] = true;
	}else{
		$result['msg'] = "操作失败,本条信息不存在或已被删除";
		$result['result'] = false;
	}
	die(json_encode($result));
}elseif($operation == 'add_suntype'){
	$sid = $_GPC['sid'];
	$catenamet_new = $_GPC['catenamet_new'];
	$parentid = $_GPC['parentid'];
	$f_count = 0;;$suc_nub=0;
	if(!empty($sid)){
		foreach($sid as $k => $sname){
			$checktype = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE  sid = '{$_GPC['sid'][$k]}'  ");
			if(!empty($checktype)){
				$sname = trim($_GPC['catename'][$k]);
				if(empty($sname)){
					$f_count ++;
				}else{
					$data = array(
						'weid'     => $weid,
						'schoolid' => $schoolid,
						'sname'    => $sname,
						'parentid'   => $parentid,
						'type'   => 'kcclass',
						'ssort'    => intval($_GPC['ssort'][$k]),
					);
					pdo_update(GetTableName('classify',false),$data,array('sid'=>$_GPC['sid'][$k]));
					$suc_nub++;
				}
			}else{
				$f_count++;	
			}
		}
	}
	if(!empty($catenamet_new)){
		foreach($catenamet_new as $key => $name){
			$name = trim($catenamet_new[$key]);
			if(empty($name)){
				$f_count ++;
			}else{
				$data = array(
					'weid'     => $weid,
					'schoolid' => $schoolid,
					'sname'    => $name,
					'parentid'   => $parentid,
					'type'   => 'kcclass',
					'ssort'    => intval($_GPC['ssort_new'][$key]),
				);
				pdo_insert(GetTableName('classify',false), $data);
				$suc_nub++;
			}
		}
	}
	$result['msg'] = "操作成功".$suc_nub."条，失败".$f_count."条";
	$result['result'] = true;
	die(json_encode($result));
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000212,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $sid      = intval($_GPC['sid']);
    $allls    = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid", array(':schoolid' => $schoolid));
    if(!empty($sid)){
		$semester = pdo_fetch("SELECT * FROM " . GetTableName('classify') . " WHERE sid = '$sid'");
		$tname = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $semester['tid']));
    }
    if(checksubmit('submit')){
		if(!empty($sid)){
			if(!empty($_GPC['old'])){
				if(empty($_GPC['catename'])){
					$this->imessage('抱歉，年级名称不能为空！', referer(), 'error');
				}	
				$old_data = pdo_fetch("SELECT tid FROM " . GetTableName('classify') . " WHERE sid = '$sid'");
				$data = array(
					'weid'     => $weid,
					'schoolid' => $_GPC['schoolid'],
					'sname'    => $_GPC['catename'],
					'ssort'    => intval($_GPC['ssort']),
					'type'     => 'semester',
				);	
				if($_GPC['tid']){
					$data['tid'] = trim($_GPC['tid']);
				}
				if(keep_Blacklist()){
					$data['njabbr'] = strtoupper($_GPC['njabbr']);
				}
				if($semester['sname'] != $data['sname']){
					setXzfNeedsync($sid,'classify');
				}
				pdo_update($this->table_classify, $data, array('sid' => $sid));
				if(!empty($_GPC['tid'])){
					pdo_update($this->table_teachers, array('status' => 3), array('id' => $_GPC['tid']));
				}
				if(!is_njzr($old_data['tid'])){
					pdo_update($this->table_teachers, array('status' => 1), array('id' => $old_data['tid']));
				}
                $edittitle = '成功修改名称为：'.trim($_GPC['catename']);
			}
            if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $njcount += $f_count + 1;
                        $njname = '有【'.$njcount.'】条年级名称未填写';
					}
					$data = array(
						'weid'     => $weid,
						'schoolid' => $_GPC['schoolid'],
						'sname'    => $name,
						'ssort'    => intval($_GPC['ssort_new'][$key]),
						'type'     => 'semester',
					);
					if($_GPC['tid_new'][$key]){
						$data['tid'] = trim($_GPC['tid_new'][$key]);
					}
					if(keep_Blacklist()){
						$data['njabbr'] = strtoupper($_GPC['njabbr_new'][$key]);
					}

                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下年级:';
                        $msg .= $name.'|';
                    }
					if(!empty($_GPC['tid'][$key])){
						pdo_update($this->table_teachers, array('status' => 3), array('id' => $_GPC['tid'][$key]));
					}	
					
				}
                $msg = rtrim($msg, "|");
                $message = $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$njname.'<br/></span>';
                $this->imessage("$message", referer(), 'success');
			}
		}else{
			if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $njcount += $f_count + 1;
                        $njname = '有【'.$njcount.'】条年级名称未填写';
					}
					$data = array(
						'weid'     => $weid,
						'schoolid' => $_GPC['schoolid'],
						'sname'    => $name,
						'tid'      => trim($_GPC['tid_new'][$key]),
						'ssort'    => intval($_GPC['ssort_new'][$key]),
						'type'     => 'semester',
					);
					if(keep_Blacklist()){
						$data['njabbr'] = strtoupper($_GPC['njabbr_new'][$key]);
					}
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下年级:';
                        $msg .= $name.'|';
                    }
					if(!empty($_GPC['tid'][$key])){
						pdo_update($this->table_teachers, array('status' => 3), array('id' => $_GPC['tid'][$key]));
					}
				}
                $msg = rtrim($msg, "|");
                $message = $success.$msg.'<br/><span style="color:red;">'.$njname.'<br/></span>';
                $this->imessage("$message", $this->createWebUrl('semester', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}			 
		}
        $this->imessage('更新年级成功！', $this->createWebUrl('semester', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid      = intval($_GPC['sid']);
    $semester = pdo_fetch("SELECT sid FROM " . GetTableName('classify') . " WHERE sid = '$sid'");
	$checkbj = pdo_fetch("SELECT * FROM " . GetTableName('classify') . " WHERE parentid = '$sid'");
    if(empty($semester)){
        $this->imessage('抱歉，年级不存在或是已经被删除！', referer(), 'error');
    }
    if(!empty($checkbj)){
        $this->imessage('抱歉，本年级仍有下属班级或分类，请先删除或转移！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('年级删除成功！', referer(), 'success');
}elseif($operation == 'change_over'){
	$id    = intval($_GPC['id']);
	$is_over = intval($_GPC['is_over']);
	$data1 = array('is_over' => $is_over);
	$classlist = pdo_fetchall("SELECT sid FROM " . GetTableName('classify') . " WHERE parentid = '{$id}' ");
	foreach($classlist as $key_c=>$value_c){
		pdo_update($this->table_classify, $data1, array('sid' => $value_c['sid']));	
	}
	pdo_update($this->table_classify, $data1, array('sid' => $id));	
}
if($operation == 'syncgrade'){
	mload()->model('xzf'); 
	$res = SyncGrade($schoolid);
	die(json_encode($res));
}
include $this->template('web/semester');
?>