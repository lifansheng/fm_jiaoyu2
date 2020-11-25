<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'yzxx';
$this1             = 'no3';
$schoolid          = intval($_GPC['schoolid']);
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1001901,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
if($operation == 'display'){
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 30;
    $condition = '';
    $params    = array();

    $list  = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
		foreach( $list as $key => $value )
		{
			$stu= pdo_fetch("SELECT sid,pard FROM ".tablename($this->table_user)." WHERE :weid = weid AND :id = id AND :schoolid = schoolid", array(':weid' => $weid, ':id' => $value['fromuserid'], ':schoolid' => $schoolid));
			$students = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $stu['sid']));
			$teacher = pdo_fetch("SELECT tname,openid FROM " . tablename($this->table_teachers) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $value['totid']));//查询master
			$guanxi = "本人";
			if($stu['pard'] == 2){
				$guanxi = "妈妈";
			}else if($stu['pard'] ==3) {
				$guanxi = "爸爸";
			}else if($stu['pard'] == 5) {
				$guanxi = "家长";
			}
			$list[$key]['tname'] =$teacher['tname'];
			$list[$key]['sname'] = $students['s_name'].$guanxi ;
		}
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_courseorder) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 ");
    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'post'){

}elseif($operation == 'delete'){
    $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_courseorder) . " WHERE id = '$id'");
    if(empty($article)){
        $this->imessage('抱歉，记录不存在或是已经被删除！', $this->createWebUrl('yzxx', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_courseorder, array('id' => $id));

    $this->imessage('mail删除成功！', $this->createWebUrl('yzxx', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}elseif($operation == 'post'){
	 $id      = intval($_GPC['id']);
    $article = pdo_fetch("SELECT id FROM " . tablename($this->table_courseorder) . " WHERE id = '$id'");
}
include $this->template('web/yzxx');
?>