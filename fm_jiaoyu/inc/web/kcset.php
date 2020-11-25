<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'kcset';
$this1             = 'no1';
$action            = 'kcset';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$uniacid = intval($_W['uniacid']);
// 权限设置
$tid_global = $_W['tid'];
if($tid_global !='founder' && $tid_global != 'owner'){
	$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
	$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
	$toPage = 'kcset';
	if( !(strstr($qxarr,'1000901'))){
		$toPage = 'kcbiao';
	}
	if(!(strstr($qxarr,'1000921')) && $toPage == 'kcbiao'){
		$toPage = 'kcsign';
	}
	if(!(strstr($qxarr,'1000941')) && $toPage == 'kcsign'){
		$toPage = 'gongkaike';
	}
	if(!(strstr($qxarr,'1000951')) && $toPage == 'gongkaike'){
		$toPage = 'kecheng';
	}

	if($toPage != 'kecheng'){
		$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl($toPage, array('schoolid' => $schoolid,'op'=>'display'));
        // header("location:$stopurl");
	}
}

$state = uni_permission($_W['uid'], $uniacid);
// if($state != 'founder' && $state != 'manager' && $state != 'owner'){
//    $this->imessage('非法访问，您无权操作该页面','','error');
// }
if($operation == 'display2'){
	mload()->model('tea');
	$nowtime = time();
	// 查询出所有老师
	$list = getalljsfzallteainfo($schoolid,0,$schooltype);
	// var_dump($list);die;
	$list2 = getalljsfzallteainfo_nofz($schoolid,$schooltype);
	// 查询所有未结束的课程
	$tcourse = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " where schoolid = :schoolid And end > '{$nowtime}' ORDER by id ASC", array(':schoolid' => $schoolid));
    // 查询所有的轮播图片
	$banner = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = :place ORDER by id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 20));
    // 查询一张轮播图片
	$kcstatus = pdo_fetch("SELECT status FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = :place And status = :status", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 20, ':status' => 1));
	// 查询所有精品课程
	$jpkc = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = :place ORDER by id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 21));
	// 查询固定的三个推荐课程
	$kccommend = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = :place ORDER by id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 22));
    // 查询推荐团购课程
	$kcteam = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = :place ORDER by id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 23));
	//查询名师
    $kcteacher = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = :place ORDER by id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 24));
    // 查询三条标题
	$kctitle = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And place = :place ORDER by id ASC limit 3", array(':weid' => $weid, ':schoolid' => $schoolid, ':place' => 25));
	$lastid = pdo_fetch("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid ORDER by id DESC LIMIT 0,1", array(':weid' => $weid, ':schoolid' => $schoolid));
    // 查询顶部的轮播图
	$stutop = pdo_fetch("SELECT * FROM " . tablename($this->table_icon) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And place = 20 ");//顶部轮播图片
	if(checksubmit('submit')){
        $type               = $_GPC['type'];//类型 1覆盖 2新建
        $btnname            = $_GPC['btnname'];//按钮名称
        $mfbzs              = $_GPC['mfbzs'];//魔方小字
        $iconpics           = $_GPC['iconpics']; //图标地址
        $iconpics_tow           = $_GPC['iconpics_tow']; //图标地址
        $place              = $_GPC['place'];//位置 20轮播 21精品课程 22 三个推荐课程 23推荐团购 24名师
        $url              	= $_GPC['url'];
        foreach($type as $key => $t){
            $id           = intval($key);
            if($t == 1){
                $rec = array(
                    'name'   => trim($btnname[$id]),
                    'beizhu' => trim($mfbzs[$id]),
                    'icon'   => trim($iconpics[$id]),
                    'icon2'   => trim($iconpics_tow[$id]),
                    'url'    => trim($url[$id]),
                    'place'  => intval($place[$id]),
                );
                pdo_update($this->table_icon, $rec, array('id' => $id));
			}else{
                $data = array(
                    'weid'     => trim($_GPC['weid']),
                    'schoolid' => trim($_GPC['schoolid']),
					'beizhu' => trim($mfbzs[$id]),
					'name'   => trim($btnname[$id]),
                    'icon'     => trim($iconpics[$id]),
                    'icon2'     => trim($iconpics_tow[$id]),
                    'url'      => trim($url[$id]),
                    'place'    => intval($place[$id]),
                    'status'   => intval($status),
                );
                pdo_insert($this->table_icon, $data);
            }
        }
        $this->imessage('操作成功!', referer(), 'success');
    }
}elseif($operation == 'change'){
    $status = trim($_GPC['status']);
    $id     = trim($_GPC['id']);
    $data   = array('status' => $status);
    pdo_update($this->table_icon, $data, array('id' => $id));
	if(empty($id)){
		pdo_update($this->table_icon, $data, array('place' => '20'));
	}
}elseif($operation == 'delclass'){
    $id   = trim($_GPC['id']);
    $item = pdo_fetch("SELECT * FROM " . tablename($this->table_icon) . " where weid = :weid And schoolid = :schoolid And id = :id", array(':weid' => $weid, ':schoolid' => $_GPC['schoolid'], ':id' => $id));
	if($item){
        pdo_delete($this->table_icon, array('id' => $id));
        $message         = "删除操作成功！";
        $data ['result'] = true;
        $data ['msg']    = $message;
    }else{
        $message         = "删除失败请重刷新页面重试!";
        $data ['result'] = false;
        $data ['msg']    = $message;
    }
    die (json_encode($data));
}
include $this->template('web/kcset');
?>