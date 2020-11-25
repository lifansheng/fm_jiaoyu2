<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$openid = $_W['openid'];
$schoolid = intval($_GPC['schoolid']);
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
    $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
    if(!is_TestFz()){
        message('您无权查看本页面');
    }
    if($it){

        
        $schoolset = pdo_fetch("SELECT shareinfo FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}'");
        $shareinfo = unserialize($schoolset['shareinfo']);
        $shareinfo['fxpic'] = tomedia($shareinfo['fxpic']);
        $links = $_W['siteroot'] .'app/'.$this->createMobileUrl('mysharelist', array('schoolid' => $schoolid,'op'=>'qzkh','shareid' => $it['sid']));

        //我的分享
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('qzkh')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND shareid = '{$it['sid']}'");



        $All = count($list);
       // $HasDeal = pdo_fetchall("SELECT * FROM ".GetTableName('qzkh')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND shareid = '{$it['sid']}' and status = 2");
        $HasDeal = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('qzkh')." WHERE  schoolid = '{$schoolid}' AND weid = '{$weid}' AND shareid = '{$it['sid']}' and status = 2  ");
        $NoDeal = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('qzkh')." WHERE  schoolid = '{$schoolid}' AND weid = '{$weid}' AND shareid = '{$it['sid']}' and status = 1  ");

        include $this->template(''.$school['style2'].'/mysharelist');
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
        exit;
    }
}
elseif($operation == 'qzkh'){ //被分享人填写信息页面
    $shareid = $_GPC['shareid'];
    include $this->template(''.$school['style2'].'/qzkh');

}
?>