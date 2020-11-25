<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'coursetype';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000241,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $coursetype = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'coursetype' And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000242,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $sid = intval($_GPC['sid']);
    if(!empty($sid)){
        $coursetype = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    }else{
        $coursetype = array(
            'ssort' => 0,
        );
    }

    if(checksubmit('submit')){
	    if(!empty($sid)){
			if(!empty($_GPC['old'])){
		        if(empty($_GPC['cTypeName'])){
		            $this->imessage('抱歉，请输入课程类型名称！', referer(), 'error');
		        }
	        $data = array(
	            'weid'     => $weid,
	            'schoolid' => $_GPC['schoolid'],
	            'sname'    => $_GPC['cTypeName'],
	            'ssort'    => intval($_GPC['ssort']),
	            'type'     => 'coursetype',
	        );
           	pdo_update($this->table_classify, $data, array('sid' => $sid));
            $edittitle = '成功修改名称为：'.$_GPC['cTypeName'];
        	}

	        if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $value){
					$name = trim($_GPC['cTypeName_new'][$key]);
					if(empty($name)){
                        $kccount += $f_count + 1;
                        $kcname = '有【'.$kccount.'】条课程类型名称未填写';
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'coursetype',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下课程:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$kcname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('coursetype', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
    	}else{
			if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $value){
					$name = trim($_GPC['cTypeName_new'][$key]);
					if(empty($name)){
                        $kccount += $f_count + 1;
                        $kcname = '有【'.$kccount.'】条课程类型名称未填写';
					}
					$data = array(
						'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'coursetype',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下课程:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $success.$msg.'<br/><span style="color:red;">'.$kcname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('coursetype', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
		}
       $this->imessage('更新课程类型成功！',$this->createWebUrl('coursetype', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid       = intval($_GPC['sid']);
    $timeframe = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($timeframe)){
        $this->imessage('抱歉，课程类型不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('课程类型删除成功！', referer(), 'success');
}
include $this->template('web/coursetype');
?>