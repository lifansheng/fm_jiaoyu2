<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$this1             = 'no2';
$action            = 'uploadsence';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];



if($tid_global !='founder' && $tid_global != 'owner'){
			$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
			$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
			$toPage = 'uploadsence';
			if( !(strstr($qxarr,'1003801'))){
				$toPage = 'upsencerecord';
			}
			if(!(strstr($qxarr,'1003811')) && $toPage == 'upsencerecord'){
				$toPage = 'uploadsence';
			}

			if($toPage != 'uploadsence'){
				$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl($toPage, array('schoolid' => $schoolid,'op'=>'display'));
				header("location:$stopurl");
			}
		}



$jsfzlist    = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz'  And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
if($tid_global !='founder' && $tid_global !='owner' && $tid_global !='vice_founder'){
	$this_teacher = pdo_fetch("SELECT fz_id FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id = '{$tid_global}' ");
	$this_fz = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz'  And schoolid = '{$schoolid}' and sid ='{$this_teacher['fz_id']}' ");  
}
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1003801,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 10;
	
	$total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_upsence) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY id DESC");

	$pager = pagination($total, $pindex, $psize);
    $sencelist = pdo_fetchall("SELECT * FROM " . tablename($this->table_upsence) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY id DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach( $sencelist as $key=>$value){
		$fz = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz'  And schoolid = '{$schoolid}' and sid ='{$value['qxfzid']}' "); 
		$sencelist[$key]['fzname'] = $fz['sname'];		
	}

}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1003802,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $id = intval($_GPC['id']);
	$senceinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_upsence) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' and id ='{$id}' ");
	$this_fz_post = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'jsfz'  And schoolid = '{$schoolid}' and sid ='{$senceinfo['qxfzid']}' ");  
   
    if(checksubmit('submit')){
        if(empty($_GPC['sence_name'])){
            $this->imessage('抱歉，请输入场景名称！', referer(), 'error');
        }
		if(empty($_GPC['sence_fzid'])){
            $this->imessage('抱歉，请选择权限部门！', referer(), 'error');
        }

		   $data = array(
				'weid'		=> $weid,
				'schoolid'	=> $_GPC['schoolid'],
				'name'		=> $_GPC['sence_name'],
				'qxfzid'	=> $_GPC['sence_fzid'],
				'sencetime'	=> strtotime($_GPC['sence_date']),
				'createtime'=> time(),
			);

        if(!empty($id)){
            pdo_update($this->table_upsence, $data, array('id' => $id));
        }else{
            pdo_insert($this->table_upsence, $data);
        }
        $this->imessage('更新场景成功！', $this->createWebUrl('uploadsence', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id   = intval($_GPC['id']);
    $score = pdo_fetch("SELECT id FROM " . tablename($this->table_upsence) . " WHERE id = '{$id}'");
    if(empty($score)){
        $this->imessage('抱歉，场景不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete($this->table_upsence, array('id' => $id), 'OR');
    $this->imessage('场景删除成功！', referer(), 'success');
}
include $this->template('web/uploadsence');
?>