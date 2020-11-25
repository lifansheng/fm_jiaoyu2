<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'visireason';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1004101,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    if(!empty($_GPC['ssort'])){
        foreach($_GPC['ssort'] as $sid => $ssort){
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', referer(), 'success');
    }
    $visireason     = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'visireason' And schoolid = {$schoolid} ORDER BY sid ASC, ssort DESC");
  
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1004102,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $parentid = intval($_GPC['parentid']);
    /** 前端传sid 这里接收 并且查询出所有数据, 然后返回给前端遍历出sid 和其他信息*/
    $sid = intval($_GPC['sid']);
    if(!empty($sid)){
        $visireason = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '$sid'");
    }else{
        $visireason = array(
            'ssort' => 0,
        );
    }
    if(checksubmit('submit')){
        if(!empty($sid)){
			if(!empty($_GPC['old'])){
				if(empty($_GPC['catename'])){
					$this->imessage('抱歉，请输入事由名称！', referer(), 'error');
				}
				$data = array(
					'weid'     => $weid,
					'schoolid' => $_GPC['schoolid'],
					'sname'    => $_GPC['catename'],
					'ssort'    => intval($_GPC['ssort']),
					'type'     => 'visireason',
				);
				pdo_update($this->table_classify, $data, array('sid' => $sid));
                $edittitle = '成功修改名称为：'.$_GPC['catename'];
        }
        if(!empty($_GPC['new'])){
                $f_count = 0;
			    foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $viscount += $f_count + 1;
                        $visname = '有【'.$viscount.'】条事由名称未填写';
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'visireason',

					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下事由:';
                        $msg .= $name.'|';
                    }
				}
            $msg = rtrim($msg, "|");
            $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$visname.'</span>';
            $this->imessage("$message",  $this->createWebUrl('visireason', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
    }else{
			if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $viscount += $f_count + 1;
                        $visname = '有【'.$viscount.'】条事由名称未填写';
					}
					$data = array(
						'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'visireason',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下事由:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $success.$msg.'<br/><span style="color:red;">'.$visname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('visireason', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}			 
		}
        $this->imessage('更新事由成功！', $this->createWebUrl('visireason', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid  = intval($_GPC['sid']);
    $visireason = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($visireason)){
        $this->imessage('抱歉，事由不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('事由删除成功！', referer(), 'success');
}
include $this->template('web/visireason');
?>