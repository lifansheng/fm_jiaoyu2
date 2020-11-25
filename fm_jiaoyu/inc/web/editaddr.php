<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'editaddr';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000251,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $addr = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'addr' And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1000252,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $sid = intval($_GPC['sid']);
    if(!empty($sid)){
        $addr = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    }else{
        $addr = array(
            'ssort' => 0,
        );
    }

    if(checksubmit('submit')){
	    if(!empty($sid)){
			if(!empty($_GPC['old'])){
		        if(empty($_GPC['AddrName'])){
		            $this->imessage('抱歉，请输入地址名称！', referer(), 'error');
		        }
	        $data = array(
	            'weid'     => $weid,
	            'schoolid' => $_GPC['schoolid'],
	            'sname'    => $_GPC['AddrName'],
				'icon'     => $_GPC['icon'],
	            'ssort'    => intval($_GPC['ssort']),
	            'type'     => 'addr',
	        );
           	pdo_update($this->table_classify, $data, array('sid' => $sid));
            $edittitle = '成功修改地址为：'.$_GPC['AddrName'];
        	}

	        if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $value){
					$name = trim($_GPC['AddrName_new'][$key]);
					if(empty($name)){
                        $addrcount += $f_count + 1;
                        $addrname = '有【'.$addrcount.'】条地址名称未填写';
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
						'icon'     => $_GPC['icon_new'][$key],
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'addr',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下地址:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$addrname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('editaddr', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
    	}else{
			if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $value){
					$name = trim($_GPC['AddrName_new'][$key]);
					if(empty($name)){
                        $addrcount += $f_count + 1;
                        $addrname = '有【'.$addrcount.'】条地址名称未填写';
					}
					$data = array(
						'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
						'icon'     => $_GPC['icon_new'][$key],
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'addr',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下地址:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $success.$msg.'<br/><span style="color:red;">'.$addrname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('editaddr', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}			 
		}
       $this->imessage('更新地址成功！',$this->createWebUrl('editaddr', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid       = intval($_GPC['sid']);
    $timeframe = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($timeframe)){
        $this->imessage('抱歉，地址不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('地址删除成功！', referer(), 'success');
}
include $this->template('web/editaddr');
?>