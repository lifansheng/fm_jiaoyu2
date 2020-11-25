<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$this1             = 'no10';
$action            = 'classcardset';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,is_openht FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");

load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'post';
//权限控制所需
$tid = $_W['tid'];
if($tid !='founder' && $tid != 'owner'){
	$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid));
	$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
}



if($operation == 'post'){
	mload()->model('tea');
    $list = getalljsfzallteainfo($schoolid,0,$schooltype);
	//设置权限标识
	
		$tab_basic_li  = 1 ; 
		$tab_gongn_li  = 1 ;
		$tab_baom_li   = 1 ;
		$tab_shid_li   = 1 ;
		$tab_sms_li    = 1 ;  
		$tab_ten_li = 1;
		$level = 'tab_basic' ;
	

    $id      = intval($_GPC['schoolid']);
    $reply   = pdo_fetch("select * from " . tablename($this->table_index) . " where id=:id and weid =:weid", array(':id' => $schoolid, ':weid' => $weid));
    $ccset   = pdo_fetch("select * from " . tablename($this->table_classcard_set) . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
    
    if(checksubmit('submit')){
		
		if(!empty($_GPC['img'])){
			
			$img=serialize($_GPC['img']);
			
			}else{
				$img='';
				}
        

        $data = array(
            'weid'                   => intval($weid),
			'schoolid'               => intval($schoolid),
			'starttime'              => $_GPC['starttime'],
			'endtime'                => $_GPC['endtime'],
			'accessKeyID'            => $_GPC['accessKeyID'],
			'accessKeySecret'        => $_GPC['accessKeySecret'],
			// 'roleArn'              => $_GPC['roleArn'],
			'bucket'              => $_GPC['bucket'],
			'endpoint'              => $_GPC['endpoint'],
			'tappId'              => $_GPC['tappId'],
			'tappKey'              => $_GPC['tappKey'],
			'img'              => $img
            
    	);
        if(!empty($ccset)){
   
            pdo_update($this->table_classcard_set, $data, array('schoolid' => $id, 'weid' => $weid));
        }else{
           pdo_insert($this->table_classcard_set, $data);
        }
        $this->imessage('操作成功!', referer(), 'success');
    }
}
include $this->template('web/classcardset');
?>