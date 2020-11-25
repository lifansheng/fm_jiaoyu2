<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'kecheng';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,shoucename FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($tid_global !='founder' && $tid_global != 'owner'){
	$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
	$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
}
if (!(IsHasQx($tid_global,1000957,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
if($operation == 'display'){
	
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    $sclist    = pdo_fetchall("SELECT * FROM " . tablename($this->table_gkkpjbz) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ORDER BY id ASC, ssort DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($sclist as $key => $value){
        $gz = pdo_fetch("SELECT id FROM " . tablename($this->table_gkkpjk) . " WHERE schoolid = :schoolid ", array(':schoolid' => $schoolid,));
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_gkkpjbz) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'setiocn'){
    load()->func('tpl');
    $bzid = intval($_GPC['bzid']);

        $item = pdo_fetchall("SELECT * FROM " . tablename($this->table_gkkpjk) . " WHERE  schoolid = '{$schoolid}' And weid = '{$weid}' and bzid ='{$bzid}' ORDER BY ssort ASC");
        if(checksubmit('submit')){
            if(!empty($_GPC['thisid'])){
                if(!empty($_GPC['old'])){
                    foreach($_GPC['old'] as $key => $thisid){
                        if(!empty($thisid)){
                            $thisid = trim($_GPC['thisid'][$key]);
                            $title  = trim($_GPC['custom_title'][$key]);
                            $ssort  = trim($_GPC['custom_ssort'][$key]);
                            $data   = array(
                                'weid'       => $weid,
                                'schoolid'   => $schoolid,
                                'bzid'       => $bzid,
                                'title'      => $title,
                                'icon1title' => trim($_GPC['custom_title1'][$key]),
                                'icon2title' => trim($_GPC['custom_title2'][$key]),
                                'icon3title' => trim($_GPC['custom_title3'][$key]),
                                'icon4title' => trim($_GPC['custom_title4'][$key]),
                                'icon5title' => trim($_GPC['custom_title5'][$key]),
                                'icon1'      => trim($_GPC['custom_icon1'][$key]),
                                'icon2'      => trim($_GPC['custom_icon2'][$key]),
                                'icon3'      => trim($_GPC['custom_icon3'][$key]),
                                'icon4'      => trim($_GPC['custom_icon4'][$key]),
                                'icon5'      => trim($_GPC['custom_icon5'][$key]),
                                'ssort'      => $ssort,
                                'type'       => 1
                            );
                            pdo_update($this->table_gkkpjk, $data, array('id' => $thisid));
                        }
                    }
                }
                if(!empty($_GPC['new'])){
                    foreach($_GPC['new'] as $key => $title){
                        $title = trim($_GPC['custom_title-new'][$key]);
                        $data  = array(
                            'weid'       => $weid,
                            'schoolid'   => $schoolid,
                             'bzid'       => $bzid,
                            'title'      => $title,
                            'icon1title' => trim($_GPC['custom_title1-new'][$key]),
                            'icon2title' => trim($_GPC['custom_title2-new'][$key]),
                            'icon3title' => trim($_GPC['custom_title3-new'][$key]),
                            'icon4title' => trim($_GPC['custom_title4-new'][$key]),
                            'icon5title' => trim($_GPC['custom_title5-new'][$key]),
                            'icon1'      => trim($_GPC['custom_icon1-new'][$key]),
                            'icon2'      => trim($_GPC['custom_icon2-new'][$key]),
                            'icon3'      => trim($_GPC['custom_icon3-new'][$key]),
                            'icon4'      => trim($_GPC['custom_icon4-new'][$key]),
                            'icon5'      => trim($_GPC['custom_icon5-new'][$key]),
                            'ssort'      => trim($_GPC['custom_ssort-new'][$key]),
                            'type'       => 1
                        );
                        pdo_insert($this->table_gkkpjk, $data);
                    }
                }
            }else{
                foreach($_GPC['custom_title-new'] as $key => $title){
                    $title = trim($title);
                    $data  = array(
                        'weid'       => $weid,
                        'schoolid'   => $schoolid,
                         'bzid'       => $bzid,
                        'title'      => $title,
                        'icon1title' => trim($_GPC['custom_title1-new'][$key]),
                        'icon2title' => trim($_GPC['custom_title2-new'][$key]),
                        'icon3title' => trim($_GPC['custom_title3-new'][$key]),
                        'icon4title' => trim($_GPC['custom_title4-new'][$key]),
                        'icon5title' => trim($_GPC['custom_title5-new'][$key]),
                        'icon1'      => trim($_GPC['custom_icon1-new'][$key]),
                        'icon2'      => trim($_GPC['custom_icon2-new'][$key]),
                        'icon3'      => trim($_GPC['custom_icon3-new'][$key]),
                        'icon4'      => trim($_GPC['custom_icon4-new'][$key]),
                        'icon5'      => trim($_GPC['custom_icon5-new'][$key]),
                        'ssort'      => trim($_GPC['custom_ssort-new'][$key]),
                        'type'       => 1
                    );
                    pdo_insert($this->table_gkkpjk, $data);
                }
            }
            $this->imessage('操作成功!', $this->createWebUrl('gkkpjxt', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
        }

}elseif($operation == 'delitemiconset'){
    $id = intval($_GPC['id']);
    pdo_delete($this->table_gkkpjk, array('id' => $id));
    $data ['result'] = true;
    $data ['msg']    = '删除成功！';
    die (json_encode($data));
}elseif($operation == 'post'){
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_gkkpjbz) . " WHERE id = '$id'");
    }else{
        $item = array(
            'ssort' => 0,
        );
    }
    if(checksubmit('submit')){
        if(empty($_GPC['title'])){
            $this->imessage('抱歉，请输入名称！');
        }
        $data = array(
            'weid'       => $weid,
            'schoolid'   => $_GPC['schoolid'],
            'title'      => $_GPC['title'],
            'ssort'      => intval($_GPC['ssort']),
           
        );
        if(!empty($id)){
            pdo_update($this->table_gkkpjbz, $data, array('id' => $id));
        }else{
            pdo_insert($this->table_gkkpjbz, $data);
        }
        $this->imessage('操作成功！!!', $this->createWebUrl('gkkpjxt', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id       = intval($_GPC['id']);
    $theclass = pdo_fetch("SELECT id FROM " . tablename($this->table_gkkpjbz) . " WHERE id = '$id'");
    if(empty($theclass['id'])){
        message('抱歉，本规则不存在或是已经被删除！', $this->createWebUrl('gkkpjxt', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_gkkpjbz, array('id' => $id));
    $this->imessage('删除成功！', $this->createWebUrl('gkkpjxt', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/gkkpjxt');
?>