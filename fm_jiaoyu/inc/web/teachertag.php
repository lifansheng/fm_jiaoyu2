<?php
/**
 * 微教育模块
 *
 * @author hannibal
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'teachertag';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$checkDefalut = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and addedinfo = 'default' and type = 'teatag' ");
if(empty($checkDefalut)){
    $InsertData = array(
        0 => '班主任',
        1 => '配备',
        2 => '生活服务'
    );
    foreach($InsertData as $vi){
        $I = array(
            'schoolid' => $schoolid,
            'weid' => $weid,
            'sname' => $vi,
            'type' => 'teatag',
            'addedinfo' => 'default'
        );
        pdo_insert(GetTableName('classify',false),$I);
    }
}
if($operation == 'display'){
    $teatag = pdo_fetchall("SELECT sname,sid,ssort,addedinfo FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'teatag' ORDER BY ssort DESC , sid ASC ");  
}elseif($operation == 'post'){
	// if (!(IsHasQx($tid_global,1000262,1,$schoolid))){
	// 	$this->imessage('非法访问，您无权操作该页面','','error');	
	// }
    $sid      = intval($_GPC['sid']);
    if(!empty($sid)){
        $taginfo = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}' ");
    }

    if(checksubmit('submit')){
        // if(!empty($sid)){
        if(!empty($_GPC['old'])){
            if(empty($_GPC['catename'])){
                $this->imessage('抱歉，请输入科目名称！', referer(), 'error');
            }
            $data = array(
                'weid'     => $weid,
                'schoolid' => $_GPC['schoolid'],
                'sname'    => $_GPC['catename'],
                'ssort'    => intval($_GPC['ssort']),
                'type'     => 'teatag',
            );
            pdo_update($this->table_classify, $data, array('sid' => $sid));
            $edittitle = '成功修改标签名称为：'.$_GPC['catename'];
        }

        if(!empty($_GPC['new'])){
                $f_count = 0;
				foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $subcount += $f_count + 1;
                        $subname = '有【'.$subcount.'】条标签名称未填写';
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
            			'type'     => 'teatag',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下标签:';
                        $msg .= $name.'|';
                    }
				}
            $msg = rtrim($msg, "|");
            $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$subname.'</span>';
            $this->imessage("$message",  $this->createWebUrl('teachertag', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
        }
    // }else{
	// 		if(!empty($_GPC['new'])){
    //             $f_count = 0;
	// 			foreach($_GPC['new'] as $key => $name){
	// 				$name = trim($_GPC['catename_new'][$key]);
	// 				if(empty($name)){
    //                     $subcount += $f_count + 1;
    //                     $subname = '有【'.$subcount.'】条科目名称未填写';
	// 				}
	// 				$data = array(
						
	// 				   	'weid'     => $weid,
    //         			'schoolid' => $_GPC['schoolid'],
    //    				 	'sname'    => $name,
    //    				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
    //    				 	'icon'     => $_GPC['icon_new'][$key],
    //         			'type'     => 'subject',
            			
	// 				);
    //                 if(!empty($name)){
    //                     pdo_insert($this->table_classify, $data);
    //                     $success = '成功添加以下科目:';
    //                     $msg .= $name.'|';
    //                 }
	// 			}
    //             $msg = rtrim($msg, "|");
    //             $message =  $success.$msg.'<br/><span style="color:red;">'.$subname.'</span>';
    //             $this->imessage("$message",  $this->createWebUrl('subject', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
	// 		}			 
	// 	}
        $this->imessage('更新科目成功！',$this->createWebUrl('teachertag', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }

}elseif($operation == 'delete'){

    /** 删除科目 */
    $sid     = intval($_GPC['sid']);
    $subject = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($subject)){
        $this->imessage('抱歉，科目不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('科目删除成功！', referer(), 'success');

}
include $this->template('web/teachertag');
