<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'ddscorelog';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$fzlist    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'jsfz', ':schoolid' => $schoolid));
$fzlist[] = array(
    'sid' => 0,
    'sname' => '未分组'
);
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$banji  = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid And schoolid = :schoolid And type = :type ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
// $teachers  = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " where weid = :weid And schoolid = :schoolid ORDER BY CONVERT(tname USING gbk) ASC", array(':weid' => $weid, ':schoolid' => $schoolid));
if($operation == 'post'){
    $id = intval($_GPC['id']);
    $uniarr = array();
    if(!empty($id)){
        $item = pdo_fetch("SELECT * FROM ".GetTableName('ddscorecategory')." WHERE id = '{$id}' ");
        $tealist = pdo_fetchall("SELECT tname,id FROM ".GetTableName('teachers')." WHERE schoolid='{$schoolid}' AND FIND_IN_SET(id,'{$item['tid']}') ");
        $uniarr = explode(',', $item['bjidstr']);
        $specialuniarr = explode(',', $item['specialbjidstr']);
    }
    if(checksubmit('submit')){
        $bjidstr = implode(',',$_GPC['bjidarr']) ;
        $data = array(
            'weid'          => $weid,
            'schoolid'      => $schoolid,
			'ssort'         => $_GPC['ssort'],
			'title'         => $_GPC['title'],
			'bjidstr'       => $bjidstr,
			'type'          => $_GPC['type'],
			'addition'      => $_GPC['addition'],
			'tid'           => arrayToString($_GPC['tid']),
			'score'         => $_GPC['score'],
			'isspecial'     => $_GPC['isspecial'],
			'specialscore'  => $_GPC['specialscore'],
			'specialbjidstr' => $specialbjidstr,
			'createtime'    => time(),
        );
        if(empty($id)){
            pdo_insert(GetTableName('ddscorecategory',false),$data);
        }else{
            pdo_update(GetTableName('ddscorecategory',false), $data, array('id' => $id));
        }
        $this->imessage('评分项操作成功', $this->createWebUrl('ddscorecategory', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete(GetTableName('ddscorecategory',false), array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . GetTableName('ddscorecategory') . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete(GetTableName('ddscorecategory',false), array('id' => $id));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}elseif($operation == 'display'){
    $condition = '';
    $bjid = $_GPC['bjid'];
    $title = $_GPC['title'];
    if(!empty($title)){
        $condition .= " and title like '%{$title}%' ";
    }
    if(!empty($bjid)){
        $condition .= " AND FIND_IN_SET('{$bjid}',bjidstr) ";

    }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $list = pdo_fetchall("SELECT * FROM ".GetTableName('ddscorecategory')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' {$condition} ORDER BY ssort DESC,ssort DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    foreach($list as $key => $row){
        $bjlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET(sid,'{$row['bjidstr']}') ORDER BY CONVERT(sname USING gbk) ASC ");
        $list[$key]['bjlist'] = $bjlist;

        $specialbjlist = pdo_fetchall("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET(sid,'{$row['specialbjidstr']}') ORDER BY CONVERT(sname USING gbk) ASC ");
        $list[$key]['specialbjlist'] = $specialbjlist;
    }
    $total = pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('ddscorecategory')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}'  {$condition} ");
    $pager = pagination($total, $pindex, $psize);
    // var_dump($psize);

}elseif($operation == 'getTeaList'){
    $teachers  = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " where weid = :weid And schoolid = :schoolid AND fz_id = '{$_GPC['fzid']}' ORDER BY CONVERT(tname USING gbk) ASC", array(':weid' => $weid, ':schoolid' => $schoolid));
    $result['data'] = $teachers;
    die(json_encode($result));
}elseif($operation == 'copy'){
    $copyInfo = pdo_fetch("SELECT * FROM ".GetTableName('ddscorecategory')." WHERE schoolid = '{$schoolid}' AND id = '{$_GPC['copyid']}'");
    $data = array(
        'weid'          => $weid,
        'schoolid'      => $schoolid,
        'ssort'         => $_GPC['ssort'],
        'title'         => $_GPC['title'],
        'bjidstr'       => $copyInfo['bjidstr'],
        'type'          => $copyInfo['type'],
        'addition'      => $copyInfo['addition'],
        'tid'           => $copyInfo['tid'],
        'score'         => $copyInfo['score'],
        'createtime'    => time(),
    );
    pdo_insert(GetTableName('ddscorecategory',false),$data);
    $result['msg'] = '复制成功';
    $result['result'] = true;
    die(json_encode($result));
}
include $this->template('web/ddscorecategory');
?>