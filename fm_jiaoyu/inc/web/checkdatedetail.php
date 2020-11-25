<?php

/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'checkdateset';
$this1             = 'no5';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$checkdatesetid    = $_GPC['id'];
$tid_global = $_W['tid'];
if($operation == 'display'){

}elseif($operation == 'post'){

	
	$banji  = pdo_fetchall("SELECT sid,sname,datesetid FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
	
    $id = intval($_GPC['id']);
    if(!empty($id)){
       $item = pdo_fetch("SELECT * FROM " . tablename($this->table_checkdateset) . " WHERE id = '{$id}'"); 
    }

    if(checksubmit('submit')){
		$data = array(
			'weid'     => $weid,
			'schoolid' => $_GPC['schoolid'],
			'name'    => $_GPC['name'],
			'friday'    => intval($_GPC['fridayset']),
			'saturday'    => intval($_GPC['saturdayset']),
			'sunday'    => intval($_GPC['sundayset']),
		);
		if(!empty($id)){
           	pdo_update($this->table_checkdateset, $data, array('id' => $id));
			$bj_old =  pdo_fetchall("SELECT sid FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type and datesetid = :datesetid ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid,':datesetid'=>$id));
			foreach($bj_old as $key_o => $value_o){
				pdo_update($this->table_classify,array('datesetid'=>0),array('sid'=>$value_o['sid']));  
			}
			$datesetid = $id;
    	}else{
			pdo_insert($this->table_checkdateset, $data);
			$datesetid = pdo_insertid();
		}
		$bjarray = $_GPC['arr'];
		foreach($bjarray as $key=>$value){
			$data_bj = array(
				'datesetid' => $datesetid
			);
			pdo_update($this->table_classify,$data_bj,array('sid'=>$value));	
		}
		//var_dump($datesetid);
      $this->imessage('更新日期安排成功！',$this->createWebUrl('checkdateset', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id       = intval($_GPC['id']);
    $timeframe = pdo_fetch("SELECT id FROM " . tablename($this->table_checkdateset) . " WHERE id = '{$id}'");
    if(empty($timeframe)){
        $this->imessage('抱歉，该日期安排不存在或是已删除！', referer(), 'error');
    }
    pdo_delete($this->table_classify, array('id' => $id), 'OR');
    $this->imessage('日期安排删除成功！', referer(), 'success');
}
include $this->template('web/checkdatedetail');
?>