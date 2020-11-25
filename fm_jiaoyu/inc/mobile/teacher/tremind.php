<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2019-08-23 15:39:57
 * @LastEditTime: 2020-03-31 14:37:32
 */
/**
 * 教师端 查看 进出校 以及 归寝 考勤记录 
 * @copyright 2019 微美科技
 * @author Hannibal·Lee <No@email.com>
 */

global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$opereation = $_GPC['op'] ? $_GPC['op'] : 'display';
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid = $it['tid'];
$tid_global = $it['tid'];
mload()->model('kc');
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($it)){
    $kclist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_tcourse) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND FIND_IN_SET($tid_global,tid) ORDER BY id");
    if(empty($kclist)){
        die();
    }

    if($opereation == 'display'){
        $endtime = strtotime(date("Y-m-d",time())) + 86399;
        $pindex = 1;
        $psize = 10;
        $nowtime = time();
        $kcid = $kclist[0]['id'];
        $data = GetReMind($weid,$schoolid,$nowtime,$kcid,'-1',$pindex,$psize);
        $list = $data['list'];
        foreach($list as $index => $row){
            $student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
            $kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $kcid));
            $buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $kcid,':schoolid'=> $schoolid));
            $hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $kcid,':schoolid'=> $schoolid));
            $nearkcsign =  pdo_fetchcolumn("SELECT MAX(createtime) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $kcid,':schoolid'=> $schoolid));
            $list[$index]['s_name'] = trim($student['s_name']);
            $list[$index]['sicon'] = $student['icon'] ? $student['icon'] : $school['spic'] ;
            $list[$index]['mobile'] = $student['mobile'];
            $list[$index]['kcname'] = $kc['name'];
            $list[$index]['restnum'] = $buycourse - $hasSign;
            $list[$index]['nearkcsign'] =  $nearkcsign ? date('Y年m月d日',$nearkcsign) : '未上课';
        }
        include $this->template(''.$school['style3'].'/tremind');
    }elseif($opereation == 'More_Data'){
        $More_endtime = strtotime($_GPC['EndDate']) + 86399;
        $kcid = $_GPC['KcId'];
        $status = $_GPC['status'];
        $Limit_start = $_GPC['LiData']['time'] ? $_GPC['LiData']['time'] +1 : 0 ;
        $psize = 10;
        $data = GetReMind($weid,$schoolid,$More_endtime,$kcid,$status,$Limit_start,$psize,true);
        $list = $data['list'];
       
        foreach($list as $index => $row){
            $student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
            $kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $kcid));
            $buycourse = pdo_fetchcolumn("SELECT ksnum FROM " . tablename($this->table_coursebuy) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid", array(':sid' => $row['sid'],':kcid'=> $kcid,':schoolid'=> $schoolid));
            $hasSign =  pdo_fetchcolumn("SELECT sum(costnum) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $kcid,':schoolid'=> $schoolid));
            $nearkcsign =  pdo_fetchcolumn("SELECT MAX(createtime) FROM " . tablename($this->table_kcsign) . " WHERE sid = :sid AND kcid=:kcid and  schoolid =:schoolid AND status =2 ", array(':sid' => $row['sid'],':kcid'=> $kcid,':schoolid'=> $schoolid));
            $list[$index]['s_name'] = trim($student['s_name']);
            $list[$index]['sicon'] = $student['icon'] ? $student['icon'] : $school['spic'] ;
            $list[$index]['mobile'] = $student['mobile'];
            $list[$index]['kcname'] = $kc['name'];
            $list[$index]['restnum'] = $buycourse - $hasSign;
            $list[$index]['nearkcsign'] =  $nearkcsign ? date('Y年m月d日',$nearkcsign) : '未上课';
            $list[$index]['location'] = $index + $Limit_start;
        }
        
        include $this->template('comtool/tremind');
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}