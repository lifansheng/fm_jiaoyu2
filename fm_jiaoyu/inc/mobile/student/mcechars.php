<?php
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$sid = $_GPC['sid'];
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$student = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND id = '{$sid}' ");
$school = pdo_fetch("SELECT style2 FROM ".GetTableName('index')." WHERE id = '{$schoolid}'");
if(!empty($student)){
    if($operation == 'display'){
        // 期号
        $qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' AND is_show_qh = 1 ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
    }elseif($operation == 'GetStuMcData'){
        $qh_id = $_GPC['qh_id'];
        $sid = $_GPC['sid'];
        mload()->model('mc');
        $return_data = GetStuMcData($sid,$qh_id);
    }
    include $this->template(''.$school['style2'].'/mcechars');
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}        
?>