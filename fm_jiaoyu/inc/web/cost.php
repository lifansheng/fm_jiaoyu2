<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'cost';
$this1             = 'no4';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,is_cost,is_printer FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$gongneng          = pdo_fetchall("SELECT * FROM " . tablename($this->table_object) . " ");

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($tid_global != 'founder' && $tid_global != 'owner'){
	if (!(IsHasQx($tid_global,1002001,1,$schoolid)) || $logo['is_cost'] == 2){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
}
if($operation == 'post'){
    load()->func('tpl');
    $id      = intval($_GPC['id']);
    $payweid = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " where level = 4 ORDER BY acid ASC");
    if(!empty($id)){
        $item = pdo_fetch("SELECT * FROM " . tablename($this->table_cost) . " WHERE id = :id", array(':id' => $id));
        $uniarr = explode(',', $item['bj_id']);
        if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
    }
    mload()->model('print');
    $banji = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$_GPC['schoolid']}' AND type='theclass' ORDER BY ssort DESC");
   

	$nowprints = explode(',', $item['printarr']);
	$printers = printers($schoolid);
	$printer_name = printer_name();
    if(checksubmit('submit')){
        $data = array(
            'weid'         => intval($weid),
            'schoolid'     => $schoolid,
            'name'         => $_GPC['name'],
            'cost'         => $_GPC['cost'],
            'dataline'     => intval($_GPC['dataline']),
            'icon'         => $_GPC['icon'],
            'is_sys'       => intval($_GPC['is_sys']),
            'is_time'      => intval($_GPC['is_time']),
            'starttime'    => strtotime($_GPC['starttime']),
            'endtime'      => strtotime($_GPC['endtime']),
            'serverend'      => strtotime($_GPC['serverend']),
            'about'        => intval($_GPC['about']),
            'bj_id'        => implode(',', $_GPC['arr']),
			'printarr'     => implode(',', $_GPC['printarr']),
			'is_print'     => intval($_GPC['is_print']),
            'createtime'   => time(),
            'payweid'      => empty($_GPC['payweid']) ? $weid : intval($_GPC['payweid']),
            'description'  => trim($_GPC['description']),
            'displayorder' => intval($_GPC['displayorder'])
        );

        // if(empty($_GPC['dataline']) & empty($_GPC['starttime']) & empty($_GPC['endtiem'])){
        //     $this->imessage('你必须设置一项时间范围设置方式！', '', 'error');
        // }
        if($_GPC['is_print'] == 1  && empty($_GPC['printarr'])){
            $this->imessage('启用打印情况下必须选择至少一台打印设备,如已添加设备,本页未出现,请千万打印设备设置页查看是否启用！', '', 'error');
        }
        // if(!empty($_GPC['starttime']) || !empty($_GPC['endtiem'])){
        //     if(strtotime($_GPC['starttime']) > strtotime($_GPC['endtime'])){
        //         $this->imessage('时间范围设置错误,开始时间不能大于结束时间！', '', 'error');
        //     }
        // }

        if(empty($id)){
            if(!empty($_GPC['about'])){
                $about = pdo_fetch("SELECT * FROM " . tablename($this->table_cost) . " WHERE weid = :weid And schoolid = :schoolid And about = :about And is_on = :is_on", array(
                    ':weid'     => $weid,
                    ':schoolid' => $schoolid,
                    ':about'    => $_GPC['about'],
                    ':is_on'    => 1
                ));
                if(!empty($about) && $about['serverend'] = 0){
                    $this->imessage('你选择的关联功能已经其他收费项目中存在,或先停止使用之前已经关联过改功能的收费项目！', '', 'error');
                }else{
                    pdo_insert($this->table_cost, $data);
                }
            }else{
                pdo_insert($this->table_cost, $data);
            }
        }else{
            $cost = pdo_fetch("SELECT * FROM " . tablename($this->table_cost) . " WHERE id = :id", array(':id' => $id));
            if($cost['about'] != intval($_GPC['about'])){
                $this->imessage('错误,缴费项目的关联功能不可更改！', '', 'error');
            }else{
                pdo_update($this->table_cost, $data, array('id' => $id));
            }
        }
        $this->imessage('创建付费项目成功！', $this->createWebUrl('cost', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'display'){

    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';

    if(!empty($_GPC['name'])){
        $condition .= " AND name LIKE '%{$_GPC['name']}%' ";
    }

    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_cost) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $index => $row){
        $object                       = pdo_fetch("SELECT * FROM " . tablename($this->table_object) . " where id = :id ", array(':id' => $row['about']));
		$ordercount = pdo_fetchcolumn("select count(*) FROM ".tablename($this->table_order)." WHERE costid = '{$row['id']}' And type = 3 And status = 2 ");
        $list[$index]['displayorder'] = $object['displayorder'];
		$list[$index]['ordercount'] = $ordercount;
		
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_cost) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");

    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
	$checkorder = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE costid = :costid And status = :status", array(':costid' => $id,':status' => 2));
    if(!empty($checkorder)){
        $this->imessage('抱歉,本项目尚有已付费订单,如需删除,请先清空本项目所有订单！');
    }	
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete($this->table_cost, array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'clearorder'){
    $costid = intval($_GPC['id']);
    pdo_delete($this->table_order, array('costid' => $id));
    $this->imessage('已情况所有本项目生成的订单！', referer(), 'success');	
}elseif($operation == 'change'){
    $id    = intval($_GPC['id']);
    $is_on = intval($_GPC['is_on']);
    $data = array('is_on' => $is_on);
    pdo_update($this->table_cost, $data, array('id' => $id));
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_cost) . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete($this->table_cost, array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $this->imessage("操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!", '', 0);
}elseif($operation == 'getBjList'){
    //当前考勤项目分班过后的班级
    $time = time();
    $hasbj = pdo_fetchall("SELECT bj_id FROM ".GetTableName('cost')." WHERE is_on = 1 AND endtime >= '{$time}' AND serverend > 0");
    $bjstr = arrayToString($hasbj);
    $costbj = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$_GPC['schoolid']}' AND type='theclass' AND NOT FIND_IN_SET(sid,'{$bjstr}') ORDER BY ssort DESC");


    $result['data'] = $costbj;
    die(json_encode($result));
}
include $this->template('web/cost');
?>