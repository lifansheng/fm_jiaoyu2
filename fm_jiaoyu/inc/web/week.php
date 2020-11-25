<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'week';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000281,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    if(!empty($_GPC['ssort'])){
        foreach($_GPC['ssort'] as $sid => $ssort){
            pdo_update($this->table_classify, array('ssort' => $ssort), array('sid' => $sid));
        }
        $this->imessage('批量更新排序成功', referer(), 'success');
    }
    $children = array();
    $week     = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'week' And schoolid = {$schoolid} ORDER BY sid ASC, ssort DESC");
    foreach($week as $index => $row){
        if(!empty($row['parentid'])){
            $children[$row['parentid']][] = $row;
            unset($week[$index]);
        }
    }
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000282,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $parentid = intval($_GPC['parentid']);
    /** 前端传sid 这里接收 并且查询出所有数据, 然后返回给前端遍历出sid 和其他信息*/
    $sid = intval($_GPC['sid']);
    if(!empty($sid)){
        $week = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '$sid'");
    }else{
        $week = array(
            'ssort' => 0,
        );
    }

    if(checksubmit('submit')){
        if(!empty($sid)){
			if(!empty($_GPC['old'])){
        if(empty($_GPC['catename'])){
            $this->imessage('抱歉，请输入星期名称！', referer(), 'error');
        }

        $data = array(
            'weid'     => $weid,
            'schoolid' => $_GPC['schoolid'],
            'sname'    => $_GPC['catename'],
            'ssort'    => intval($_GPC['ssort']),
            'type'     => 'week',
            
        );
           pdo_update($this->table_classify, $data, array('sid' => $sid));
           $edittitle = '成功修改名称为：'.$_GPC['catename'];
        }

        if(!empty($_GPC['new'])){
            $f_count = 0;
			    foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $datecount += $f_count + 1;
                        $datename = '有【'.$datecount.'】条星期名称未填写';
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'week',

					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下星期:';
                        $msg .= $name.'|';
                    }
									
				}
            $msg = rtrim($msg, "|");
            $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$datename.'</span>';
            $this->imessage("$message",  $this->createWebUrl('week', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
    }else{
			if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $datecount += $f_count + 1;
                        $datename = '有【'.$datecount.'】条星期名称未填写';
					}
					$data = array(
						'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'week',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下星期:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $success.$msg.'<br/><span style="color:red;">'.$datename.'</span>';
                $this->imessage("$message",  $this->createWebUrl('week', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}			 
		}
        $this->imessage('更新星期成功！', $this->createWebUrl('week', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid  = intval($_GPC['sid']);
    $week = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($week)){
        $this->imessage('抱歉，星期不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('星期删除成功！', referer(), 'success');
}
include $this->template('web/week');
?>