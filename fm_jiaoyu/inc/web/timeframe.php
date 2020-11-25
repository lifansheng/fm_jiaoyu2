<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'timeframe';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000271,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    if(!empty($_GPC['ssort'])){
        foreach($_GPC['ssort'] as $sid => $ssort){
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', referer(), 'success');
    }
    $children  = array();
    $timeframe = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'timeframe' And schoolid = '{$schoolid}' ORDER BY ssort DESC, sid ASC");
    foreach($timeframe as $index => $row){
        if(!empty($row['parentid'])){
            $children[$row['parentid']][] = $row;
            unset($timeframe[$index]);
        }
    }
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000272,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $parentid = intval($_GPC['parentid']);
    $sid      = intval($_GPC['sid']);
    if(!empty($sid)){
        $timeframe = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '$sid'");
    }else{
        $timeframe = array(
            'ssort' => 0,
        );
    }

    if(checksubmit('submit')){
		if(!empty($sid)){
				if(!empty($_GPC['old'])){
			if(empty($_GPC['catename'])){
				$this->imessage('抱歉，请输入时段名称！', referer(), 'error');
			}

			$data = array(
				'weid'     => $weid,
				'schoolid' => $_GPC['schoolid'],
				'sname'    => $_GPC['catename'],
				'ssort'    => intval($_GPC['ssort']),
				'type'     => 'timeframe',
				'sd_start' => strtotime($_GPC['sd_start']),
				'sd_end'   => strtotime($_GPC['sd_end']),
			);
			  pdo_update($this->table_classify, $data, array('sid' => $sid));
              $edittitle = '成功修改名称为：'.$_GPC['catename'];
			}

			if(!empty($_GPC['new'])){
                $f_count = 0;
					foreach($_GPC['new'] as $key => $name){
						$name = trim($_GPC['catename_new'][$key]);
						if(empty($name)){
                            $timecount += $f_count + 1;
                            $timename = '有【'.$timecount.'】条课程类型名称未填写';
						}
						$data = array(
							'weid'     => $weid,
							'schoolid' => $_GPC['schoolid'],
							'sname'    => $name,
							'ssort'    => intval($_GPC['ssort_new'][$key]),
							'type'     => 'timeframe',
							'sd_start' => strtotime($_GPC['sd_start_new'][$key]),
							'sd_end'   => strtotime($_GPC['sd_end_new'][$key]),

						);
                        if(!empty($name)){
                            pdo_insert($this->table_classify, $data);
                            $success = '成功添加以下时段:';
                            $msg .= $name.'|';
                        }
					}
                $msg = rtrim($msg, "|");
                $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$timename.'</span>';
                $this->imessage("$message",  $this->createWebUrl('timeframe', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
				}
				
		}else{
				if(!empty($_GPC['new'])){
                    $f_count = 0;
					foreach($_GPC['new'] as $key => $name){
						$name = trim($_GPC['catename_new'][$key]);
						if(empty($name)){
                            $timecount += $f_count + 1;
                            $timename = '有【'.$timecount.'】条课程类型名称未填写';
						}
						$data = array(
							'weid'     => $weid,
							'schoolid' => $_GPC['schoolid'],
							'sname'    => $name,
							'ssort'    => intval($_GPC['ssort_new'][$key]),
							'type'     => 'timeframe',
							'sd_start' => strtotime($_GPC['sd_start_new'][$key]),
							'sd_end'   => strtotime($_GPC['sd_end_new'][$key]),
						);
                        if(!empty($name)){
                            pdo_insert($this->table_classify, $data);
                            $success = '成功添加以下时段:';
                            $msg .= $name.'|';
                        }
					}
                    $msg = rtrim($msg, "|");
                    $message =  $success.$msg.'<br/><span style="color:red;">'.$timename.'</span>';
                    $this->imessage("$message",  $this->createWebUrl('timeframe', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
				}			 
		}
       $this->imessage('更新时段成功！',$this->createWebUrl('timeframe', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid       = intval($_GPC['sid']);
    $timeframe = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($timeframe)){
        $this->imessage('抱歉，时段不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('时段删除成功！', referer(), 'success');
}
include $this->template('web/timeframe');
?>