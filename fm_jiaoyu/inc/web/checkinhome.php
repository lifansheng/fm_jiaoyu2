<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'checkinhome';
$this1             = 'no5';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title,spic FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1005101,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;

    $AllNj = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'semester' ");
    $AllBj = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'theclass' ");

    $condition = '';
    if(!empty($_GPC['nj_id'])){
        $condition .= " and s.xq_id = '{$_GPC['nj_id']}' ";
    }
    if(!empty($_GPC['bj_id'])){
        $condition .= " and s.bj_id = '{$_GPC['bj_id']}' ";
    }
    if(!empty($_GPC['sname'])){
        $S_NAME = trim($_GPC['sname']);
        $condition .= " and s.s_name like '%{$S_NAME}%' ";
    }
    if(!empty($_GPC['createtime'])){
        $starttime = strtotime($_GPC['createtime']['start']);
        $endtime = strtotime($_GPC['createtime']['end']) + 86399;
    }else{
        $starttime = strtotime('-150 day');
        $endtime = time();
    }
    $condition .= " and c.createtime BETWEEN '{$starttime}' AND '{$endtime}'  ";

    $list = pdo_fetchall("SELECT c.*,u.openid,u.pard,s.icon,s.s_name,s.bj_id as s_bj_id FROM ".GetTableName('checkinhome')." as c LEFT JOIN ".GetTableName('user')." as u ON u.id = c.userid  LEFT JOIN ".GetTableName('students')." as s ON s.id = c.sid WHERE c.weid = '{$weid}' and c.schoolid = '{$schoolid}' {$condition} ORDER BY c.createtime DESC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as &$value){
        $value['fansinfo'] = GetWeFans($weid,$value['openid']);
        switch ($value['pard']) {
            case '2':
                $value['gx'] = "父亲";
                break;
            case '3':
                $value['gx'] = "母亲";
                break;
            case '4':
                $value['gx'] = "本人";
                break;
            case '5':
                $value['gx'] = "家长";
                break;
            default:
                $value['gx'] = "家长";
                break;
        }
        $bjinfo = pdo_fetch("SELECT bj.sname as bjname,nj.sname as njname FROM ".GetTableName('classify')." as bj LEFT JOIN ".GetTableName('classify')." as nj ON bj.parentid = nj.sid  WHERE bj.sid = '{$value['s_bj_id']}' ");
        $value['bjname'] = $bjinfo['bjname'];
        $value['njname'] = $bjinfo['njname'];
        $value['icon'] = !empty($value['icon']) ? tomedia($value['icon']) : tomedia($logo['spic']);
    }
    $total = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('checkinhome')." as c LEFT JOIN ".GetTableName('user')." as u ON u.id = c.userid  LEFT JOIN ".GetTableName('students')." as s ON s.id = c.sid WHERE c.weid = '{$weid}' and c.schoolid = '{$schoolid}' ORDER BY c.createtime DESC");
    $pager = pagination($total, $pindex, $psize);

}elseif($operation == 'delete'){
    $id     = intval($_GPC['id']);
    
    $check = pdo_fetch("SELECT id FROM ".GetTableName('checkinhome')." WHERE id = '{$id}' ");
    
    if(empty($check)){
        $this->imessage('抱歉，记录不存在或是已经被删除！', referer(), 'error');
    }
    pdo_delete(GetTableName('checkinhome',false), array('id' => $id));
    $this->imessage('记录删除成功!！', referer(), 'success');
}else{
    $this->imessage('请求方式不存在');
}
include $this->template('web/checkinhome');
?>