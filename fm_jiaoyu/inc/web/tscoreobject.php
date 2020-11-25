<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'tscoreobject';
$this1             = 'no1';
$action            = 'semester';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$jsfz     = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz' And schoolid = {$schoolid} ORDER BY CONVERT(sname USING gbk) ASC");

$scoreOb     = pdo_fetchall("SELECT classify.sid,classify.sname,jsfz.sname as fzname,jsfz.sid as Obfzid FROM " . tablename($this->table_classify) . " as  classify , " . tablename($this->table_classify) . " as  jsfz WHERE classify.weid = '{$weid}' And classify.type = 'tscoreobject' And classify.schoolid = {$schoolid} And classify.parentid = 0 And  classify.fzid = jsfz.sid     ORDER BY CONVERT(classify.sname USING gbk) ASC");
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1000291,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	} 
	$tscoreobject = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
 
    foreach($tscoreobject as $index => $row){
        $tscoreobject[$index]['fzname'] =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz' And schoolid = {$schoolid} and sid = '{$row['fzid']}' ")['sname'];
		if($row['parentid'] != 0 ){
			  $tscoreobject[$index]['Paname'] =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} and sid = '{$row['parentid']}' ")['sname'];
			
			
			
		}
    }
}elseif($operation == 'post'){
	 if (!(IsHasQx($tid_global,1000292,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	} 
    $sid      = intval($_GPC['sid']);
    if(!empty($sid)){
        $theobject = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    }else{
        $subject = array(
            'ssort' => 0,
        );
    }

    if(checksubmit('submit')){
        if(!empty($sid)){
			if(!empty($_GPC['old'])){
				if(empty($_GPC['catename'])){
					$this->imessage('抱歉，请输入项目名称！', referer(), 'error');
				}

				$data = array(
					'weid'     => $weid,
					'schoolid' => $_GPC['schoolid'],
					'sname'    => $_GPC['catename'],
					'ssort'    => intval($_GPC['ssort']),
					'icon'     => $_GPC['icon'],
					'fzid'     => $_GPC['teafenzu'],
					'parentid' => $_GPC['teapa'],
					'type'     => 'tscoreobject',
					
				);
				$child_ob = pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " WHERE type='tscoreobject' and parentid = '{$sid}' ");

				foreach($child_ob as $key_c=>$value_c){
					pdo_update($this->table_classify, array('fzid'=>$data['fzid']), array('sid' => $value_c['sid']));
				}
				pdo_update($this->table_classify, $data, array('sid' => $sid));
                $edittitle = '成功修改名称为：'.$_GPC['catename'];
            }

			if(!empty($_GPC['new'])){
                $f_count = 0;
                foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $obcount += $f_count + 1;
                        $obname = '有【'.$obcount.'】条项目名称未填写';
					}
					$data = array(
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
       				 	'icon'     => $_GPC['icon_new'][$key],
						'fzid'     => $_GPC['teafenzu_new'][$key],
						'parentid' => $_GPC['teapa_new'][$key],
            			'type'     => 'tscoreobject',
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下项目:';
                        $msg .= $name.'|';
                    }
				}
                $msg = rtrim($msg, "|");
                $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$obname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('tscoreobject', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
		}else{
			if(!empty($_GPC['new'])){
                $f_count = 0;
                foreach($_GPC['new'] as $key => $name){
					$name = trim($_GPC['catename_new'][$key]);
					if(empty($name)){
                        $obcount += $f_count + 1;
                        $obname = '有【'.$obcount.'】条项目名称未填写';
					}
					$data = array(
						
					   	'weid'     => $weid,
            			'schoolid' => $_GPC['schoolid'],
       				 	'sname'    => $name,
       				 	'ssort'    => intval($_GPC['ssort_new'][$key]),
       				 	'icon'     => $_GPC['icon_new'][$key],
						'fzid'     => $_GPC['teafenzu_new'][$key],
						'parentid' => $_GPC['teapa_new'][$key],
            			'type'     => 'tscoreobject',
            			
					);
                    if(!empty($name)){
                        pdo_insert($this->table_classify, $data);
                        $success = '成功添加以下项目:';
                        $msg .= $name.'|';
                    }
									
				}
                $msg = rtrim($msg, "|");
                $message =  $edittitle.'<br/>'.$success.$msg.'<br/><span style="color:red;">'.$obname.'</span>';
                $this->imessage("$message",  $this->createWebUrl('tscoreobject', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}			 
		}
        $this->imessage('更新项目成功！',$this->createWebUrl('tscoreobject', array('op' => 'display', 'schoolid' => $schoolid)), 'success');

    }

}elseif($operation == 'delete'){
    $sid     = intval($_GPC['sid']);
    $subject = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$sid}'");
    if(empty($subject)){
        $this->imessage('抱歉，项目不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('sid' => $sid), 'OR');
    $this->imessage('项目删除成功！', referer(), 'success');

}
include $this->template('web/tscoreobject');
?>