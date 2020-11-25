<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'behaviorscore';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$km    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'subject' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
$xq    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'week' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'week', ':schoolid' => $schoolid));
$sd    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'timeframe' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));
$qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'score' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
 

// if($tid_global == 'founder' || $tid_global == 'owner'){
// 	$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
// 	$scoreObPa = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
	
// 	$limit = 0 ;
// }else{
// 	$teacher = pdo_fetch("SELECT status,fz_id FROM " . tablename($this->table_teachers) . " where id = '{$tid_global}' ");
// 	if($teacher['status'] == 2){
// 		$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
// 		$scoreObPa  = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
// 		$limit = 0 ;
// 	}else{
// 		$scoreOb  = pdo_fetchall("SELECT sname,sid,parentid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid != 0 and fzid = '{$teacher['fz_id']}'  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
// 		$scoreObPa  = pdo_fetchall("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'tscoreobject' And schoolid = {$schoolid} And parentid = 0 and fzid = '{$teacher['fz_id']}'  ORDER BY CONVERT(sname USING gbk) ASC",array(),'sid');
// 		$limit = 1 ;
// 	}
	
// }
if($operation == 'post'){
    $banji  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type   ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
    $sid = intval($_GPC['sid']);
    $uniarr = array();
    $wordlist = array(0=>'');
    if(!empty($sid)){
        $item = pdo_fetch("SELECT sname,ssort,qh_bjlist,addedinfo FROM ".GetTableName('classify')." WHERE sid = '{$sid}' ");
        $uniarr = explode(',', $item['qh_bjlist']);
        $wordlist = json_decode($item['addedinfo']);
    }
    if(checksubmit('submit')){
        $words = [];
        if($_GPC['word']){
            foreach ($_GPC['word'] as $key => $value) {
                if($value){
                    $words[$key] = $value;
                }
            }
        }
        $newword = array_values($words);
        $name  = $_GPC['name'];
        $bjarr = implode(',',$_GPC['arr']) ;
        $word  = json_encode($newword);
        $data = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
			'sname'     => $name,
			'addedinfo' => $word,
			'qh_bjlist' => $bjarr,
			'ssort'     => $_GPC['ssort'],
			'type'      => 'behaviorscore'
        );

        if(empty($sid)){
           pdo_insert(GetTableName('classify',false),$data);
        }else{
            pdo_update(GetTableName('classify',false), $data, array('sid' => $sid));
        }
        $this->imessage('编辑行为评测分类成功', $this->createWebUrl('behaviormanager', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $sid = intval($_GPC['sid']);
    if(empty($sid)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete(GetTableName('classify',false), array('sid' => $sid));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_teascore) . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete($this->table_teascore, array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}elseif($operation == 'display'){
    $condition = '';
    $search_bj_id = $_GPC['search_bj_id'];
    $search_name = $_GPC['search_name'];
    if(!empty($search_name)){
        $condition .= " and sname like '%{$search_name}%' ";
    }
    if(!empty($search_bj_id)){
        $condition .= " and  FIND_IN_SET('{$search_bj_id}',qh_bjlist) ";

    }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $banji  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type   ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
    $behaviorlist = pdo_fetchall("SELECT sname,sid,qh_bjlist,addedinfo FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'behaviorscore' {$condition} ORDER BY ssort DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($behaviorlist as $key => $value){
        $word = json_decode($value['addedinfo']);
        $behaviorlist[$key]['word'] = $word;
        $bjlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET(sid,'{$value['qh_bjlist']}')  ORDER BY CONVERT(sname USING gbk) ASC ");
        $behaviorlist[$key]['bjlist'] = $bjlist;
    }
}
include $this->template('web/behaviormanager');
?>