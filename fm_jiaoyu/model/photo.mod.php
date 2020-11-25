<?php
/*
 * @Discription:  
 * @Author: Hannibal·Lee
 * @Date: 2020-02-08 10:01:10
 * @LastEditTime : 2020-02-12 11:17:11
 */
/**
 * Undocumented function 获取相册分类
 *
 * @param [type] $weid
 * @param [type] $schoolid
 * @param [type] $bj_id 
 * @param [type] $type 2班级 1个人
 * @param [type] $sid 指定学生
 *
 * @return void
 */
function GetPhotoType($weid,$schoolid,$bj_id,$type,$sid='',$is_video=0,$manual=false)
{
    if($manual){
        $condition = "AND is_video = '{$is_video}'";
    }
    if($type == 2){
        
        /*班级封面*/
        $class     = pdo_fetch("SELECT sid,sname FROM " . GetTableName('classify') . " where sid = :sid", array(':sid' => $bj_id));
        $cfrist = pdo_fetch("SELECT picurl FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And type = 2 AND (ctype = '0' || ctype = '') And bj_id1 = '{$bj_id}' $condition ORDER BY createtime DESC LIMIT 0,1");
        $ctotal = pdo_fetchcolumn("SELECT count(*) as total FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And type = 2 AND (ctype = '0' || ctype = '') And bj_id1 = '{$bj_id}' $condition");
        $bjinfo[0]['bj_id'] = $bj_id;
        $bjinfo[0]['sname'] = $class['sname'];
        $bjinfo[0]['picurl'] = $cfrist['picurl'];
        $bjinfo[0]['total'] = $ctotal;
        $bjinfo[0]['ctype'] = '0';
        //默认显示相册
        $alydb   = pdo_fetch("select teapictype,stupictype from " . GetTableName('schoolset') . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
        if(!empty($alydb['teapictype'])){
            $teapictype =unserialize($alydb['teapictype']);
            $teadata = [];
            foreach ($teapictype as $key => $value) {
                $teapic = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value}' AND bj_id1 = '{$bj_id}' $condition ORDER BY createtime LIMIT 0,1");
                $total = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value}' AND bj_id1 = '{$bj_id}' $condition");
            
                $teadata[$key]['sname'] = GetphotoTypeName($value);

                $teadata[$key]['picurl'] = $teapic['picurl'];
                $teadata[$key]['total'] = $total;
                $teadata[$key]['bj_id'] = $bj_id;
                $teadata[$key]['ctype'] = $value;
            }
        }
        //自定义分类相册
        $photoclass   = pdo_fetchAll("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = :schoolid AND bjid = :bjid AND phototype = :phototype ORDER BY sid ", array(':bjid' => $bj_id,':schoolid'=>$schoolid,':phototype'=>$type));
        if(!empty($photoclass)){
            foreach ($photoclass as $key1 => $value1) {
                $zdytype = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value1['sid']}' $condition ORDER BY createtime LIMIT 0,1");
                $total = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value1['sid']}' $condition");
                $photoclass[$key1]['sname'] = $value1['sname'];
                $photoclass[$key1]['picurl'] = $zdytype['picurl'];
                $photoclass[$key1]['total'] = $total;
                $photoclass[$key1]['bj_id'] = $bj_id;
                $photoclass[$key1]['ctype'] = $value1['sid'];
            }
        }
        $bjphototypedata = array_merge($bjinfo,$teadata,$photoclass);
        return $bjphototypedata;
    }elseif($type == 1){
        /*班级封面*/
        $class     = pdo_fetch("SELECT sid,sname FROM " . GetTableName('classify') . " where sid = :sid", array(':sid' => $bj_id));
        if(!empty($sid)){
            $condition .= " AND sid = '{$sid}'";
        }
        $condition .= " AND bj_id1 = '{$bj_id}'";
        //默认显示相册
        $alydb   = pdo_fetch("select stupictype from " . GetTableName('schoolset') . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
        if(!empty($alydb['stupictype'])){
            $stupictype =unserialize($alydb['stupictype']);
            $studata = [];
            foreach ($stupictype as $key => $value) {
                $stupic = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value}' $condition ORDER BY createtime DESC LIMIT 0,1");
                $count = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value}' $condition");
                $studata[$key]['picurl'] = $stupic['picurl'];
                $studata[$key]['ctype'] = $value;
                $studata[$key]['total'] = $count;
                $studata[$key]['sname'] = GetphotoTypeName($value);
                $studata[$key]['bjname'] = $class['sname'];
                $studata[$key]['bj_id'] = $bj_id;
            }
        }
        //自定义分类相册
        $photoclass   = pdo_fetchAll("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = :schoolid AND bjid = :bjid AND phototype = :phototype ORDER BY sid ", array(':bjid' => $bj_id,':schoolid'=>$schoolid,':phototype'=>$type));
        if(!empty($photoclass)){
            foreach ($photoclass as $key1 => $value1) {
                $zdytype = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value1['sid']}' $condition ORDER BY createtime LIMIT 0,1");
                $total = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value1['sid']}' $condition");
                $photoclass[$key1]['sname'] = $value1['sname'];
                $photoclass[$key1]['picurl'] = $zdytype['picurl'];
                $photoclass[$key1]['total'] = $total;
                $photoclass[$key1]['bj_id'] = $bj_id;
                $photoclass[$key1]['bjname'] = $class['sname'];
                $photoclass[$key1]['ctype'] = $value1['sid'];
            }
        }
        //其他相册
        $otherpic = pdo_fetch("SELECT picurl FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 AND picurl != '' AND (ctype = '0' || ctype = '') $condition ORDER BY createtime");
        $othercount = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 AND (ctype = '0' || ctype = '') $condition");
        $otherdata[0]['picurl'] = $otherpic['picurl'];
        $otherdata[0]['ctype'] = 0;
        $otherdata[0]['total'] = $othercount;
        $otherdata[0]['sname'] = '其他相册';
        $otherdata[0]['bjname'] = $class['sname'];
        $otherdata[0]['bj_id'] = $bj_id;
        $stuAllXc = array_merge($studata,$photoclass,$otherdata);
        return $stuAllXc;
    }
    
}
/**
 * Undocumented function 获取培训机构相册分类
 *
 * @param [type] $weid
 * @param [type] $schoolid
 * @param [type] $kcid
 * @param [type] $type
 * @param string $sid
 *
 * @return void
 */
function GetPxPhotoType($weid,$schoolid,$kcid,$type,$sid='')
{
    /*课程封面*/
    $kc = pdo_fetch("select name,thumb FROM ".tablename('wx_school_tcourse')." WHERE schoolid = '{$schoolid}' AND id = '{$kcid}' ");
    if($type == 2){
        $cfrist = pdo_fetch("SELECT picurl FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And type = 2 AND (ctype = '0' || ctype = '') AND is_video = 0 And kcid = '{$kcid}' ORDER BY id DESC LIMIT 0,1");
        $ctotal = pdo_fetchcolumn("SELECT count(*) as total FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And type = 2 AND (ctype = '0' || ctype = '') And kcid = '{$kcid}' ");
        $bjinfo[0]['kcid'] = $kcid;
        $bjinfo[0]['sname'] = $kc['name'];
        $bjinfo[0]['picurl'] = $cfrist['picurl'] ? $cfrist['picurl'] : $kc['thumb'];
        $bjinfo[0]['total'] = $ctotal;
        $bjinfo[0]['ctype'] = '0';
        //默认显示相册
        $alydb   = pdo_fetch("select teapictype,stupictype from " . GetTableName('schoolset') . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
        if(!empty($alydb['teapictype'])){
            $teapictype =unserialize($alydb['teapictype']);
            $teadata = [];
            foreach ($teapictype as $key => $value) {
                $teapic = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND is_video = 0 AND ctype ='{$value}' AND kcid = '{$kcid}' LIMIT 0,1");
                $total = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value}' AND kcid = '{$kcid}' ");
                $teadata[$key]['sname'] = GetphotoTypeName($value);
                $teadata[$key]['picurl'] = $teapic['picurl'];
                $teadata[$key]['total'] = $total;
                $teadata[$key]['kcid'] = $kcid;
                $teadata[$key]['ctype'] = $value;
            }
        }
        //自定义分类相册
        $photoclass   = pdo_fetchAll("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = :schoolid AND kcid = :kcid AND phototype = :phototype ORDER BY sid ", array(':kcid' => $kcid,':schoolid'=>$schoolid,':phototype'=>$type));
        if(!empty($photoclass)){
            foreach ($photoclass as $key1 => $value1) {
                $zdytype = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND is_video = 0 AND ctype ='{$value1['sid']}' LIMIT 0,1");
                $total = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value1['sid']}'");
                $photoclass[$key1]['sname'] = $value1['sname'];
                $photoclass[$key1]['picurl'] = $zdytype['picurl'];
                $photoclass[$key1]['total'] = $total;
                $photoclass[$key1]['kcid'] = $kcid;
                $photoclass[$key1]['ctype'] = $value1['sid'];
            }
        }
        $bjphototypedata = array_merge($bjinfo,$teadata,$photoclass);
        return $bjphototypedata;
    }elseif($type == 1){
        /*个人封面*/
        if(!empty($sid)){
            $condition .= " AND sid = '{$sid}'";
        }
        
        // $condition .= " AND ( kcid = '{$kcid}' OR kcid = 0  )";
        $condition .= " AND kcid = '{$kcid}'";
        //默认显示相册
        $alydb   = pdo_fetch("select stupictype from " . GetTableName('schoolset') . " where schoolid=:schoolid and weid =:weid", array(':schoolid' => $schoolid, ':weid' => $weid));
        if(!empty($alydb['stupictype'])){
            $stupictype =unserialize($alydb['stupictype']);
            $studata = [];
            foreach ($stupictype as $key => $value) {
                $stupic = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value}' $condition LIMIT 0,1");
                $count = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value}' $condition");
                $studata[$key]['picurl'] = $stupic['picurl'];
                $studata[$key]['ctype'] = $value;
                $studata[$key]['total'] = $count;
                $studata[$key]['sname'] = GetphotoTypeName($value);
                $studata[$key]['bjname'] = $kc['name'];
                $studata[$key]['kcid'] = $kcid;
            }
        }
        //自定义分类相册
        // $photoclass   = pdo_fetchAll("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = :schoolid AND kcid = :kcid AND phototype = :phototype ORDER BY sid ", array(':kcid' => $kcid,':schoolid'=>$schoolid,':phototype'=>$type));
        $photoclass   = pdo_fetchAll("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid = :schoolid AND phototype = :phototype ORDER BY sid ", array(':schoolid'=>$schoolid,':phototype'=>$type));
        if(!empty($photoclass)){
            foreach ($photoclass as $key1 => $value1) {
                $zdytype = pdo_fetch("SELECT picurl,ctype FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND is_video = 0 AND ctype ='{$value1['sid']}' $condition LIMIT 0,1");
                $total = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND ctype ='{$value1['sid']}' $condition");
                $photoclass[$key1]['sname'] = $value1['sname'];
                $photoclass[$key1]['picurl'] = $zdytype['picurl'];
                $photoclass[$key1]['total'] = $total;
                $photoclass[$key1]['kcid'] = $kcid;
                $photoclass[$key1]['bjname'] = $kc['name'];
                $photoclass[$key1]['ctype'] = $value1['sid'];
            }
        }
        //其他相册
        $otherpic = pdo_fetch("SELECT picurl FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 AND picurl != '' AND (ctype = '0' || ctype = '') $condition");
        $othercount = pdo_fetchcolumn("SELECT count(*) FROM " . GetTableName('media') . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 AND (ctype = '0' || ctype = '') $condition");
        // var_dump($condition);die;
        $otherdata[0]['picurl'] = $otherpic['picurl'];
        $otherdata[0]['ctype'] = 0;
        $otherdata[0]['total'] = $othercount;
        $otherdata[0]['sname'] = '其他相册';
        $otherdata[0]['bjname'] = $kc['name'];
        $otherdata[0]['kcid'] = $kcid;
        $stuAllXc = array_merge($studata,$photoclass,$otherdata);
        return $stuAllXc;
    }
    
}
/**
 * 获取默认分类的名称
 * @param [type] 默认变量 
 * @return void
 */
function GetphotoTypeName($value){
    if($value == 'teaactivity'){
        $name = '活动相册';
    }elseif($value == 'tealife'){
        $name = '生活相册';
    }elseif($value == 'teaworks'){
        $name = '我的作品';
    }elseif($value == 'stuworks'){
        $name = '我的作品';
    }elseif($value == 'stulife'){
        $name = '生活相册';
    }elseif($value == 'stumyinschool'){
        $name = '在校的我';
    }elseif($value == 'stumyinhome'){
        $name = '在家的我';
    }
    return $name;
}
