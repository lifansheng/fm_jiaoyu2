<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$action = 'kswaring';
$this1 = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$schoolid = intval($_GPC['schoolid']);
$kcid = intval($_GPC['kcid']);
$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ORDER BY ssort DESC", array(':id' => $schoolid));
$over_status = ($_GPC['over_status']) ? intval($_GPC['over_status']) : -1;
$kecheng = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where id = :id", array(':id' => $kcid));
$checkNowTime = strtotime(date("Y-m-d",time()));
$kcall = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " where schoolid ='{$schoolid}' and weid = '{$weid}' and end > $checkNowTime AND kc_type != 1 ");
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
$optype = $_GPC['optype'];
if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $nowtime = $_GPC['createtime'] ? strtotime($_GPC['createtime']) : time();
    mload()->model('kc');
    $data = GetKsWaring($weid,$schoolid,$nowtime,$kcid,$over_status,$pindex,$psize);
    $list = $data['list'];
    foreach($list as $index => $row){
        //获取绑定者相关信息
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
		if(!empty($student)){
			$userdata = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " WHERE sid = :sid ", array(':sid' => $row['sid']));
			$userinfo = unserialize($userdata['userinfo']);
			$guanxi = get_guanxi($userdata['pard']);
			$user = array(
				'guanxi' => $guanxi,
				'name' => $userinfo['name'],
				'mobile' => $userinfo['mobile'],
			);
			$kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
			$buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
			$hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
			//获取最近一次签到时间
			$nearkcsign =  pdo_fetchcolumn("SELECT MAX(createtime) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $row['kcid'],':schoolid'=> $schoolid));
			$list[$index]['userinfo'] = $user;
			$list[$index]['s_name'] = trim($student['s_name']);
			$list[$index]['mobile'] = $student['mobile'];
			$list[$index]['kcname'] = $kc['name'];
			$list[$index]['restnum'] = $buycourse - $hasSign;
			$list[$index]['nearkcsign'] =  $nearkcsign ? date('Y-m-d',$nearkcsign) : '从未上课';
			//如果是导出excel(排除不需要的内容)
			if($_GPC['out_putcode'] == 'out_putcode'){
				unset($list[$index]['sid']);
				unset($list[$index]['createtime']);
				unset($list[$index]['ksnum']);
			}
		}else{
			unset($list[$index]);
		}
    }
	if($_GPC['out_putcode'] == 'out_putcode'){
        $this->exportexcel($list, array('学生', '联系方式','课程名称','剩余课时','上次上课时间','剩余课时','上次签到日期'), '持续未到或课程不足学生信息');
        exit();
    }
	if($optype == 'kcyj_list'){
		$pager = paginations($data['total'], $pindex, $psize,'',array('before' => 0, 'after' => 0, 'ajaxcallback' => true, 'callbackfuncname' => 'page_kcyjlist'));
	}else{
		$pager = pagination($data['total'], $pindex, $psize);
	}
}
if($optype == 'kcyj_list'){
	include $this->template ( 'public/kc_bm_list' );
}else{
	include $this->template ( 'web/kswaring' );
}
?>