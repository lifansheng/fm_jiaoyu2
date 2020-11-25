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
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$allSchool         = pdo_fetchAll("SELECT id,title FROM " . tablename($this->table_index) . " WHERE weid = '{$weid}' AND id != '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$ismaster = pdo_fetch("SELECT status FROM ".GetTableName('teachers')." WHERE id = '{$tid_global}' ");
$toPage = 'article';
if(!(IsHasQx($tid_global,1001401,1,$schoolid)) && $toPage == 'article'){
    $toPage = 'news';
}
if(!(IsHasQx($tid_global,1001411,1,$schoolid)) && $toPage == 'news'){
    $toPage = 'wenzhang';
}
if(!(IsHasQx($tid_global,1001421,1,$schoolid)) && $toPage == 'wenzhang'){
    $toPage = 'article';
}
if($toPage != 'article'){
    $stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl($toPage, array('schoolid' => $schoolid,'op'=>'display'));
    header("location:$stopurl");
}


if($operation == 'display'){
	if (!(IsHasQx($tid_global,1001401,1,$schoolid))){
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

    $list  = pdo_fetchall("SELECT n.*,i.title as name FROM " . tablename($this->table_news) . " as n LEFT JOIN " . tablename($this->table_index) . " as i ON i.id = n.schoolid WHERE n.weid = '{$weid}' And (n.schoolid = '{$schoolid}' OR FIND_IN_SET('{$schoolid}',n.schoolidstr)) And n.type = 'article' $condition ORDER BY n.displayorder DESC, n.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_news) . " WHERE weid = '{$weid}'And (schoolid = '{$schoolid}' OR FIND_IN_SET('{$schoolid}',schoolidstr)) And type = 'article'");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){
	if (!(IsHasQx($tid_global,1001401,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    load()->func('tpl');
    $id = intval($_GPC['id']);

    if(!empty($id)){
        $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_news) . " WHERE id = '{$id}'");
        $uniarr = explode(',',$item1['schoolidstr']); 
        if(empty($item1)){
			$this->imessage('抱歉，本条信息不存在在或是已经删除！', $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
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
            'type'         => 'article',
            'isopenpl'     => $_GPC['isopenpl'],
            'defaultshow'  => $_GPC['isopenpl'] == 1 ? $_GPC['defaultshow'] : 2,
            'isopendz'     => $_GPC['isopendz'],
            // 'isshow'       => ($_W['isfounder'] || $_W['role'] == 'owner' || $ismaster['status'] == 2) ? 1 : 2,
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
        $this->imessage('操作成功', $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_news) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，文章不存在或是已经被删除！', $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_news, array('id' => $id), 'OR');
    $this->imessage('文章删除成功！', $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
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
}elseif($operation == 'look'){
    $id = intval($_GPC['id']);
    $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_news) . " WHERE id = '{$id}'");
    if($item1['type'] == 'article'){
        $action = 'article';
    }elseif($item1['type'] == 'news'){
        $action = 'news';
    }elseif($item1['type'] == 'wenzhang'){
        $action = 'wenzhang';
    }
    $articledz = pdo_fetchAll("SELECT openid FROM " . GetTableName('articledz') . " WHERE a_id = '{$id}'");
    foreach ($articledz as $key => $value) {
        $articledz[$key] = mc_fansinfo($value['openid']);
    }
    $articlepl = pdo_fetchAll("SELECT id,openid,content,status,createtime FROM " . GetTableName('articlepl') . " WHERE a_id = '{$id}' ORDER BY id DESC");
    foreach ($articlepl as $key => $value) {
        $articlepl[$key] = mc_fansinfo($value['openid']);
        $articlepl[$key]['id'] = $value['id'];
        $articlepl[$key]['content'] = $value['content'];
        $articlepl[$key]['status'] = $value['status'];
        $articlepl[$key]['createtime'] = date('m-d H:i', $value['createtime']) ;
    }
}
include $this->template('web/article');
?>