<?php
/**
 * 微教育模块
 * www.daren007.com
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'article';
$this1             = 'no3';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$action1           = 'wenzhang';
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$allSchool         = pdo_fetchAll("SELECT id,title FROM " . tablename($this->table_index) . " WHERE weid = '{$weid}' AND id != '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$ismaster = pdo_fetch("SELECT status FROM ".GetTableName('teachers')." WHERE id = '{$tid_global}' ");
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1001421,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = '';
    $params    = array();
    if(!empty($_GPC['keyword'])){
        $condition          .= " AND n.title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    $list  = pdo_fetchall("SELECT n.*,i.title as name FROM " . tablename($this->table_news) . " as n LEFT JOIN " . tablename($this->table_index) . " as i ON i.id = n.schoolid WHERE n.weid = '{$weid}' And (n.schoolid = '{$schoolid}' OR FIND_IN_SET('{$schoolid}',n.schoolidstr)) And n.type = 'wenzhang' $condition ORDER BY n.displayorder DESC, n.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_news) . " WHERE weid = '{$weid}'And (schoolid = '{$schoolid}' OR FIND_IN_SET('{$schoolid}',schoolidstr)) And type = 'wenzhang'");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1001421,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_news) . " WHERE id = '{$id}'");
        $uniarr = explode(',',$item1['schoolidstr']); 
        if(empty($item1)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
    }
    if(checksubmit('submit')){

        $data = array(
            'weid'         => $weid,
            'schoolid'     => $_GPC['schoolid'],
            'title'        => trim($_GPC['title']),
            'content'      => trim($_GPC['content']),
            'thumb'        => trim($_GPC['thumb']),
            'description'  => trim($_GPC['description']),
            'author'       => trim($_GPC['author']),
            'is_display'   => 1,
            'is_show_home' => 1,
            'click'        => intval($_GPC['click']),
            'dianzan'      => intval($_GPC['dianzan']),
            'displayorder' => intval($_GPC['displayorder']),
            'type'         => 'wenzhang',
            'isopenpl'         => $_GPC['isopenpl'],
            'defaultshow'  => $_GPC['isopenpl'] == 1 ? $_GPC['defaultshow'] : 2,
            'isopendz'         => $_GPC['isopendz'],
            // 'isshow'         => ($_W['isfounder'] || $_W['role'] == 'owner' || $ismaster['status'] == 2) ? 1 : 2,
            'createtime'   => time(),
        );
        if($_W['isfounder'] || $_W['role'] == 'owner'){
            $data['schoolidstr'] = arrayToString($_GPC['schoolidstr']);
        }
        if(!empty($id)){
            pdo_update($this->table_news, $data, array('id' => $id));
        }else{
            pdo_insert($this->table_news, $data);
        }
        $this->imessage('发布成功！', $this->createWebUrl('wenzhang', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id       = intval($_GPC['id']);
    $wenzhang = pdo_fetch("SELECT id FROM " . tablename($this->table_news) . " WHERE id = '{$id}'");
    if(empty($wenzhang)){
        $this->imessage('抱歉，文章不存在或是已经被删除！', $this->createWebUrl('wenzhang', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_news, array('id' => $id), 'OR');
    $this->imessage('文章删除成功！', $this->createWebUrl('wenzhang', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'change'){
    // $isshow = $_GPC['isshow'];
    // $id = $_GPC['id'];
    // $res = pdo_fetch("SELECT * FROM ".GetTableName('news')." WHERE id = '{$id}' ");
    // if(!empty($res)){
    //     pdo_update(GetTableName('news',false),array('isshow' => $isshow),array('id'=>$id));
    //     if($isshow == 1){
    //         $data['msg'] = '已开启';
    //     }else{
    //         $data['msg'] = '已关闭';
    //     }
    //     $data['status'] = true;
    // }else{
    //     $data['msg'] = '未检测到数据';
    //     $data['status'] = false;
    // }
    // die(json_encode($data));
}
include $this->template('web/wenzhang');
?>
