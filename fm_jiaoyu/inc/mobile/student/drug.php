<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
load()->func('tpl');
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
// 查询学校
$school = pdo_fetch("SELECT style2,title,spic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
if(!empty($it)){
    $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
    if($operation == 'display'){
        include $this->template(''.$school['style2'].'/drug');       
        exit;
    }
    elseif($operation=='GetList'){
        //总数
        $total1 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND sid = '{$it['sid']}'");

        //未处理
        $total2 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND sid = '{$it['sid']}' AND status = 0");

        //已通过
        $total3 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND sid = '{$it['sid']}' AND status = 1");

        //已拒绝
        $total4 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND sid = '{$it['sid']}' AND status = 2");

        $list = pdo_fetchall("SELECT * FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND sid = '{$it['sid']}' ORDER BY id LIMIT 0,10 ");
        foreach ($list as $key => $value) {
            $student = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $list[$key]['icon'] = tomedia($student['icon']);
            $list[$key]['sname'] = $student['s_name'];
            $list[$key]['starttime'] = date("Y-m-d",$value['starttime']);
            $list[$key]['endtime'] = date("Y-m-d",$value['endtime']);
            $datetime = unserialize($value['datetime']);
            $list[$key]['datetime'] = arrayToString($datetime);
        }
        include $this->template(''.$school['style2'].'/druglist');       
        exit;
    }
    elseif($operation=='DrugShow'){
        $drugid = $_GPC['drugid'];
        //TODO:喂药详细列表
        // $list = pdo_fetchall("SELECT * FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' ");
        // foreach ($list as $key => $value) {
        //     $student = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
        //     $list[$key]['icon'] = tomedia($student['icon']);
        //     $list[$key]['sname'] = $student['s_name'];
        //     $list[$key]['starttime'] = date("Y-m-d",$value['starttime']);
        //     $list[$key]['endtime'] = date("Y-m-d",$value['endtime']);
        //     $datetime = unserialize($value['datetime']);
        //     $list[$key]['datetime'] = arrayToString($datetime);
        // }
        include $this->template(''.$school['style2'].'/drugshow');       
        exit;
    }elseif($operation == 'scroll_more'){
        $time = $_GPC['LiData']['time'];
        $limit_start = $time + 1;
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('drug')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND sid = '{$it['sid']}' ORDER BY id LIMIT {$limit_start},10 ");

        foreach ($list as $key => $value) {
            $student = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $list[$key]['icon'] = tomedia($student['icon']);
            $list[$key]['sname'] = $student['s_name'];
            $list[$key]['starttime'] = date("Y-m-d",$value['starttime']);
            $list[$key]['endtime'] = date("Y-m-d",$value['endtime']);
            $datetime = unserialize($value['datetime']);
            $list[$key]['datetime'] = arrayToString($datetime);
            $list[$key]['location'] = $key + $limit_start;
        }
        include $this->template('comtool/druglist');
        exit;
    }elseif($operation == 'GetDrugInfo'){
        $id = $_GPC['id'];
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['weid'];
        $data = pdo_fetch("SELECT * FROM ".GetTableName('drug')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND id = '{$id}' ");

        $student = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$data['sid']}' ");
        $data['sname'] = $student['s_name'];

        $data['headimg'] = unserialize($data['headimg']);
        $data['datetime'] = arrayToString(unserialize($data['datetime']));
        include $this->template('comtool/druginfo');
        exit;
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}

?>