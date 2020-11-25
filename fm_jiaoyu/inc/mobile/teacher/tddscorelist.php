<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];

//查询是否用户登录		
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid_global = $it['tid'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
if($op == 'display'){
    if(!empty($userid['id'])){
        $date = strtotime(date("Y-m-d",time()));
        $njlist = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'semester' And schoolid = '{$schoolid}' ORDER BY sid ASC, ssort DESC");
        foreach ($njlist as $k => $v) {
            $bjid = pdo_fetchAll("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND parentid = '{$v['sid']}' ");
            $bjidstr = arrayToString($bjid); 
            $bjnum = count($bjid); //当前年级下的班级数量
            $done = pdo_fetchAll("SELECT DISTINCT(bjid) FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(bjid,'{$bjidstr}') AND date = '{$date}' ");
            $donebjnum = count($done);
            $njlist[$k]['bjnum'] = $bjnum;
            $njlist[$k]['donenum'] = $donebjnum;
            $njlist[$k]['nodonenum'] = $bjnum - $donebjnum;
        }
        $allbj = pdo_fetchcolumn("SELECT count(sid) FROM ".GetTableName('classify')." WHERE weid = '{$weid}' And type = 'theclass' And schoolid = '{$schoolid}' ");
        $donenum = pdo_fetchcolumn("SELECT count(DISTINCT bjid) FROM ".GetTableName('ddscorelog')." WHERE schoolid = '{$schoolid}' AND date = '{$date}' ");
        $nodonenum = intval($allbj) - intval($donenum);
        include $this->template(''.$school['style3'].'/tddscorelist');
    }else{
        session_destroy();
        $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
        header("location:$stopurl");
    }   
}
        
?>