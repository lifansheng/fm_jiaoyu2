<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'pfxm';
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
    $pfxm = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'pfxm' And schoolid = '{$schoolid}' ORDER BY ssort DESC");
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000242,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $sid = intval($_GPC['sid']);
    if(!empty($sid)){
        $pfxm = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    }else{
        $pfxm = array(
            'ssort' => 0,
        );
    }

    if(checksubmit('submit')){
	    if(!empty($sid)){
			if(!empty($_GPC['old'])){
		        if(empty($_GPC['cTypeName'])){
		            $this->imessage('抱歉，请输入评分项目名称！', referer(), 'error');
		        }
	        $data = array(
	            'weid'     => $weid,
	            'schoolid' => $_GPC['schoolid'],
	            'sname'    => $_GPC['cTypeName'],
	            'ssort'    => intval($_GPC['ssort']),
	            'type'     => 'pfxm',
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
                        $kcname = '有【'.$kccount.'】条评分项目名称未填写';
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'pfxm',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下评分项:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$kcname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('pfxm', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
    	}else{
			if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $value){
					$name = trim($_GPC['cTypeName_new'][$key]);
					if(empty($name)){
                        $kccount += $f_count + 1;
                        $kcname = '有【'.$kccount.'】条评分项目名称未填写';
					}
					$data = array(
						'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'pfxm',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下评分项:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $success.$msg.'<br/><span style="color:red;">'.$kcname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('pfxm', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
		}
       $this->imessage('更新评分项目成功！',$this->createWebUrl('pfxm', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid       = intval($_GPC['sid']);
    $timeframe = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($timeframe)){
        $this->imessage('抱歉，评分项目不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('评分项目删除成功！', referer(), 'success');
}
include $this->template('web/pfxm');
?>