<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');
/****************************TODO:keep_Ls()定制功能*********************************/

/**
 * 获取请假列表
 *
 * @param int $weid
 * @param int $schoolid
 * @param int $tid
 * @param int $start
 * @param int $end
 * @param string $sf
 * @param string $type
 * @param int $id
 *
 * @return void
 */
function getQjInfo($weid,$schoolid,$tid,$start,$end,$sf,$type = 'all',$id = 0){
   
    mload()->model('tea');
    if($type == 'all'){ //获取全部（校长或年级主任）
        if($sf == 'xiaozhang' || $sf == 'director'){
            if($sf == 'director'){
                $condition = " AND nj.tid = '{$tid}' ";
            }
            $list = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'semester' And schoolid = '{$schoolid}' AND is_over = 1 $condition ORDER BY sid ASC, ssort DESC");
            $QjList = pdo_fetchall("SELECT s.id,s.s_name,l.startime1,l.endtime1,s.bj_id,s.xq_id FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('leave')." as l ON l.sid = s.id   LEFT JOIN ".GetTableName('classify')." as bj ON bj.sid = l.bj_id   LEFT JOIN ".GetTableName('classify')." as nj ON nj.sid = bj.parentid   WHERE  l.tid = 0 and l.isliuyan = 0 and l.status = 1 and l.kcid = 0 and l.schoolid = '{$schoolid}'  AND l.startime1 <= {$start} && l.endtime1 >= {$end} AND nj.type = 'semester' and nj.schoolid = '{$schoolid}' and nj.is_over = 1 $condition ORDER BY l.startime1 ASC ");
        }
    }
    if($type == 'nj'){ //查看指定年级
        if($sf == 'xiaozhang' || $sf == 'director'){ //校长或年级主任
            $list = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'semester' And schoolid = '{$schoolid}' AND is_over = 1 $condition ORDER BY sid ASC, ssort DESC");
            $QjList = pdo_fetchall("SELECT s.id,s.s_name,l.startime1,l.endtime1,s.bj_id,s.xq_id FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('leave')." as l ON l.sid = s.id   LEFT JOIN ".GetTableName('classify')." as bj ON bj.sid = l.bj_id   WHERE  l.tid = 0 and l.isliuyan = 0  and l.status = 1 and l.kcid = 0 and l.schoolid = '{$schoolid}'  AND l.startime1 <= {$start} && l.endtime1 >= {$end} AND bj.type = 'theclass' and bj.schoolid = '{$schoolid}' and bj.is_over = 1 AND bj.parentid = '{$id}' ORDER BY l.startime1 ASC ");
        }else {
            //先获取班主任的班级
            $listA = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'theclass' and is_over = 1 and tid = '{$tid}'  ");
            $listB = pdo_fetchall("SELECT bj_id as sid  FROM ".GetTableName('user_class')." WHERE schoolid = '{$schoolid}' and tid = '{$tid}' ");
            $listStr = arrayToString(array_merge($listA,$listB));

            $QjList = pdo_fetchall("SELECT s.id,s.s_name,l.startime1,l.endtime1,s.bj_id,s.xq_id FROM ".GetTableName('leave')." as l LEFT JOIN ".GetTableName("students")." as s ON s.id = l.sid  WHERE l.tid = 0 AND l.isliuyan = 0 AND l.kcid=0 AND l.status = 1 AND l.ksid=0 AND l.schoolid = '{$schoolid}' AND l.startime1 <= $start && l.endtime1 >= $end AND FIND_IN_SET(l.bj_id,'{$listStr}') ");
        }
    }
    return $QjList;
}

/**
 * 获取考勤统计信息数据
 *
 * @param int $weid
 * @param int $schoolid
 * @param int $tid
 * @param int $start
 * @param int $end
 * @param int $sf
 * @param bool $nj_id
 * @param string $type
 *
 * @return void
 */
function getKqInfo($weid,$schoolid,$tid,$start,$end,$sf,$nj_id = false,$type = 'all'){
    mload()->model('tea');
    $qjCount = 0;
    $zdschuqinCount = 0;
    $zxschuqinCount = 0;
    $zdsqueqinCount = 0;
    $zxsqueqinCount = 0;
	if($type == 'all'){
        if($sf == 'director'){
            $condition = " AND tid = '{$tid}' ";
        }
        //年级数据统计
        $list = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'semester' And schoolid = '{$schoolid}' AND is_over = 1 $condition ORDER BY sid ASC, ssort DESC");
        $zdqjCount = 0;
        $zxqjCount = 0;
        foreach ($list as $key => $value) {
            $bj = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' AND parentid = '{$value['sid']}' ");
            $bjStr = arrayToString($bj);
            //当天请假学生
            $qj = pdo_fetchAll("SELECT sid FROM ".GetTableName('leave')." WHERE tid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end AND FIND_IN_SET(bj_id,'{$bjStr}')");//请假数量

            //走读生请假数量
            $zdsqj = pdo_fetchall("SELECT sid FROM ".GetTableName('leave')." as l LEFT JOIN ".GetTableName('students')." as s ON  l.sid = s.id   WHERE  l.tid = 0 and l.isliuyan = 0 AND l.kcid = 0 AND l.schoolid = '{$schoolid}' AND l.startime1 <= {$start} AND l.endtime1 >= $end AND FIND_IN_SET(l.bj_id,'{$bjStr}') AND s.s_type != 2   ");

            //住校生请假数量
            $zxsqj = pdo_fetchall("SELECT sid FROM ".GetTableName('leave')." as l LEFT JOIN ".GetTableName('students')." as s ON  l.sid = s.id   WHERE  l.tid = 0 and l.isliuyan = 0 AND l.kcid = 0 AND l.schoolid = '{$schoolid}' AND l.startime1 <= {$start} AND l.endtime1 >= $end AND FIND_IN_SET(l.bj_id,'{$bjStr}') AND s.s_type = 2   ");


            $qjSidStr = arrayToString($qj);
            //获取住校和走读学生数量
            $students = pdo_fetch("SELECT IFNULL(SUM(case when s_type = 2 then 1 else 0 end),0) as zxs, IFNULL(SUM(case when s_type != 2 then 1 else 0 end),0) as zds FROM ".GetTableName('students')." WHERE schoolid = :schoolid AND FIND_IN_SET(bj_id,:bj_id)",array(':schoolid'=>$schoolid,':bj_id'=>$bjStr));

            $stuCount = intval($students['zxs']) + intval($students['zds']);
            /****************************************住校生在校,离校数据****************************************/
            $zxs = $students['zxs']; //当前年级的住校生
            $zxsrecord = pdo_fetch("SELECT IFNULL(SUM(case when leixing = 1 then 1 else 0 end),0) as chuqin FROM (( SELECT c.leixing FROM".GetTableName('students')." as s LEFT JOIN  ( SELECT c.* FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE schoolid = :schoolid AND sc_ap = 0 ORDER BY createtime DESC ) as c GROUP BY c.sid ) as c ON s.id = c.sid WHERE (c.sc_ap = 0 OR c.sc_ap is NULL) AND s.s_type = :s_stype AND s.schoolid = :schoolid AND FIND_IN_SET(c.bj_id,:bj_id) AND NOT FIND_IN_SET(s.id,:id) AND createtime <= $end ORDER BY c.createtime DESC)) as al",array(':schoolid'=>$schoolid,':bj_id'=>$bjStr,':id'=>$qjSidStr,':s_stype'=>2));


            $zxschuqin = intval($zxsrecord['chuqin']);
            $zxsqueqin = intval($zxs) - intval($zxsrecord['chuqin']) - count($zxsqj);
            /****************************************住校生在校,离校数据****************************************/
            /****************************************走读生在校,离校数据****************************************/
            $zds = $students['zds']; //当前年级的走读生
            $zdsrecord = pdo_fetchcolumn("SELECT count(id) FROM (SELECT * FROM (SELECT s.id,c.leixing FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('checklog')." as c ON s.id = c.sid WHERE c.sc_ap = 0 AND s.s_type != :s_stype AND s.schoolid = :schoolid AND FIND_IN_SET(s.bj_id,:bj_id) AND NOT FIND_IN_SET(s.id,:id) AND c.createtime <= $end ORDER BY c.createtime DESC) as a GROUP BY id ) as csl WHERE csl.leixing = 1 ",array(':schoolid'=>$schoolid,':bj_id'=>$bjStr,':id'=>$qjSidStr,':s_stype'=>2));
            $zdschuqin = intval($zdsrecord);
            $zdsqueqin = intval($zds) - intval($zdsrecord) - count($zdsqj);
            /****************************************走读生在校,离校数据****************************************/
            $list[$key]['stuCount'] = $stuCount;//当前年级的学生总数
            $list[$key]['qjStuCount'] = count($qj);
            $list[$key]['chuqin'] = intval($zxschuqin) + intval($zdschuqin); //住校+走读在校
            $list[$key]['queqin'] = intval($zxsqueqin) + intval($zdsqueqin); //住校+走读离校
            $qjCount +=count($qj);
            $zdqjCount += count($zdsqj); //走读生请假总计
            $zxqjCount += count($zxsqj); //住校生请假总计
            $list[$key]['DayStu']['count'] = intval($students['zds']);//走读生人数
            $list[$key]['DayStu']['attendance'] = intval($zdschuqin); // 走读生在校
            $list[$key]['DayStu']['absence'] = $zdsqueqin;//走读生离校
            $list[$key]['DayStu']['leave'] = intval($zdsqj); // 走读生在校
            $list[$key]['Boarder']['count'] = intval($students['zxs']);//走读生人数
            $list[$key]['Boarder']['attendance'] = intval($zxschuqin); // 走读生在校
            $list[$key]['Boarder']['absence'] = $zxsqueqin;//走读生离校
            $list[$key]['Boarder']['leave'] = intval($zxsqj); // 走读生在校
            $zdschuqinCount += intval($zdschuqin);
            $zdsqueqinCount += intval($zdsqueqin);
            $zxschuqinCount += intval($zxschuqin);
            $zxsqueqinCount += intval($zxsqueqin);
        }
        $zdsTj['data'][0] = array('value' => $zdqjCount, 'name' => '请假人数'.$zdqjCount);
        $zdsTj['data'][1] = array('value' => $zdschuqinCount, 'name' => '在校人数'.$zdschuqinCount);
        $zdsTj['data'][2] = array('value' => $zdsqueqinCount, 'name' => '离校人数'.$zdsqueqinCount);
        $zdsTj['title'] = ['请假人数'.$zdqjCount,'在校人数'.$zdschuqinCount,'离校人数'.$zdsqueqinCount];
        $zxsTj['data'][0] = array('value' => $zxqjCount, 'name' => '请假人数'.$zxqjCount);
        $zxsTj['data'][1] = array('value' => $zxschuqinCount, 'name' => '在校人数'.$zxschuqinCount);
        $zxsTj['data'][2] = array('value' => $zxsqueqinCount, 'name' => '离校人数'.$zxsqueqinCount);
        $zxsTj['title'] = ['请假人数'.$zxqjCount,'在校人数'.$zxschuqinCount,'离校人数'.$zxsqueqinCount];
        $return_data['list'] = $list;
        $return_data['zdsTj'] = $zdsTj;
        $return_data['zxsTj'] = $zxsTj;
    }else{
        // //班级数据统计
        // if($nj_id){
        //     $list = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = '{$schoolid}' AND is_over = 1 AND parentid = '{$nj_id}' ORDER BY sid ASC, ssort DESC");
        // }else{
        //     $list = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = '{$schoolid}' AND is_over = 1 AND tid = '{$tid}' ORDER BY sid ASC, ssort DESC");


        // }

        if($sf != 'teachers'){
            $list = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = '{$schoolid}' AND is_over = 1 AND parentid = '{$nj_id}' ORDER BY sid ASC, ssort DESC");
        }else {
            $listA = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and type = 'theclass' and is_over = 1 and tid = '{$tid}'  ");
            $listB = pdo_fetchall("SELECT bj_id as sid FROM ".GetTableName('user_class')." WHERE schoolid = '{$schoolid}' and tid = '{$tid}' and bj_id != 0 and type = 1 ");
            
            if(!empty($listA) && !empty($listB)){
                $list = array_merge($listA,$listB);
            }else{
                $list = !empty($listA) ?  $listA : $listB;
            }
        }
        $zdqjCount = 0;
        $zxqjCount = 0;
        $TempList = [];
        foreach ($list as $key => $value) {
            if(!in_array($value['sid'],$TempList)){
                $list[$key]['sname'] = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$value['sid']}' ")['sname'];
                $TempList[] = $value['sid'];
                //当天请假学生
                $qj = pdo_fetchAll("SELECT sid FROM ".GetTableName('leave')." WHERE tid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end AND bj_id = '{$value['sid']}'");//请假数量


                //走读生请假数量
                $zdsqj = pdo_fetchall("SELECT sid FROM ".GetTableName('leave')." as l LEFT JOIN ".GetTableName('students')." as s ON  l.sid = s.id   WHERE  l.tid = 0 and l.isliuyan = 0 AND l.kcid = 0 AND l.schoolid = '{$schoolid}' AND l.startime1 <= {$start} AND l.endtime1 >= $end AND l.bj_id = '{$value['sid']}' AND s.s_type != 2   ");

                //住校生请假数量
                $zxsqj = pdo_fetchall("SELECT sid FROM ".GetTableName('leave')." as l LEFT JOIN ".GetTableName('students')." as s ON  l.sid = s.id   WHERE  l.tid = 0 and l.isliuyan = 0 AND l.kcid = 0 AND l.schoolid = '{$schoolid}' AND l.startime1 <= {$start} AND l.endtime1 >= $end AND l.bj_id = '{$value['sid']}' AND s.s_type = 2   ");


                $qjSidStr = arrayToString($qj);
                //获取住校和走读学生数量
                $students = pdo_fetch("SELECT IFNULL(SUM(case when s_type = 2 then 1 else 0 end),0) as zxs, IFNULL(SUM(case when s_type != 2 then 1 else 0 end),0) as zds FROM ".GetTableName('students')." WHERE schoolid = :schoolid AND bj_id = :bj_id",array(':schoolid'=>$schoolid,':bj_id'=>$value['sid']));
                $stuCount = intval($students['zxs']) + intval($students['zds']);
                /****************************************住校生在校,离校数据****************************************/

                $zxs = $students['zxs']; //当前年级的住校生
                // $zxsrecord = pdo_fetch("SELECT IFNULL(SUM(case when leixing = 1 then 1 else 0 end),0) as chuqin FROM (( SELECT s.id, c.leixing FROM".GetTableName('students')." as s LEFT JOIN ".GetTableName('checklog')." as c ON s.id = c.sid WHERE (c.sc_ap = 0 OR c.sc_ap is NULL) AND s.s_type = :s_stype AND s.schoolid = :schoolid AND c.bj_id = :bj_id AND NOT FIND_IN_SET(s.id,:id) AND createtime <= $end ORDER BY c.createtime DESC)) as al GROUP BY id",array(':schoolid'=>$schoolid,':bj_id'=>$value['sid'],':id'=>$qjSidStr,':s_stype'=>2));
                $zxsrecord = pdo_fetch("SELECT IFNULL(SUM(case when leixing = 1 then 1 else 0 end),0) as chuqin FROM (( SELECT c.leixing FROM".GetTableName('students')." as s LEFT JOIN  ( SELECT cc.* FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE schoolid = :schoolid AND sc_ap = 0 ORDER BY createtime DESC ) as cc GROUP BY cc.sid ) as c ON s.id = c.sid WHERE (c.sc_ap = 0 OR c.sc_ap is NULL) AND s.s_type = :s_stype AND s.schoolid = :schoolid AND FIND_IN_SET(c.bj_id,:bj_id) AND NOT FIND_IN_SET(s.id,:id) AND createtime <= $end ORDER BY c.createtime DESC)) as al",array(':schoolid'=>$schoolid,':bj_id'=>$value['sid'],':id'=>$qjSidStr,':s_stype'=>2));


                $zxschuqin = intval($zxsrecord['chuqin']);
                $zxsqueqin = intval($zxs) - intval($zxsrecord['chuqin']) - count($zxsqj);

                /****************************************住校生在校,离校数据****************************************/
                /****************************************走读生在校,离校数据****************************************/

                $zds = $students['zds']; //当前年级的走读生
                $zdsrecord = pdo_fetchcolumn(" SELECT COUNT(id) FROM ( SELECT * FROM (SELECT s.id,c.leixing FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('checklog')." as c ON s.id = c.sid WHERE c.sc_ap = 0 AND s.s_type != :s_stype AND s.schoolid = :schoolid AND s.bj_id = :bj_id AND NOT FIND_IN_SET(s.id,:id) AND c.createtime <= $end ORDER BY c.createtime DESC) as a GROUP BY id) as csl WHERE csl.leixing = 1 ",array(':schoolid'=>$schoolid,':bj_id'=>$value['sid'],':id'=>$qjSidStr,':s_stype'=>2));
                $zdschuqin = intval($zdsrecord);
                $zdsqueqin = intval($zds) - intval($zdsrecord) - count($zdsqj);
                
                /****************************************走读生在校,离校数据****************************************/
                $list[$key]['stuCount'] = $stuCount;//当前年级的学生总数
                $list[$key]['qjStuCount'] = count($qj);
                $list[$key]['chuqin'] = intval($zxschuqin) + intval($zdschuqin); //住校+走读在校
                $list[$key]['queqin'] = intval($zxsqueqin) + intval($zdsqueqin); //住校+走读离校
                $qjCount +=count($qj);


                $zdqjCount += count($zdsqj); //走读生请假总计
                $zxqjCount += count($zxsqj); //住校生请假总计
                $list[$key]['DayStu']['count'] = intval($students['zds']);//走读生人数
                $list[$key]['DayStu']['attendance'] = intval($zdschuqin); // 走读生在校
                $list[$key]['DayStu']['absence'] = $zdsqueqin;//走读生离校
                $list[$key]['DayStu']['leave'] = intval($zdsqj); // 走读生在校
                $list[$key]['Boarder']['count'] = intval($students['zxs']);//走读生人数
                $list[$key]['Boarder']['attendance'] = intval($zxschuqin); // 走读生在校
                $list[$key]['Boarder']['absence'] = $zxsqueqin;//走读生离校
                $list[$key]['Boarder']['leave'] = intval($zxsqj); // 走读生在校

                $zdschuqinCount += intval($zdschuqin);
                $zdsqueqinCount += intval($zdsqueqin);
                $zxschuqinCount += intval($zxschuqin);
                $zxsqueqinCount += intval($zxsqueqin);
            }else {
                unset($list[$key]);
            }
            
        }
        $zdsTj['data'][0] = array('value' => $zdqjCount, 'name' => '请假人数'.$zdqjCount);
        $zdsTj['data'][1] = array('value' => $zdschuqinCount, 'name' => '在校人数'.$zdschuqinCount);
        $zdsTj['data'][2] = array('value' => $zdsqueqinCount, 'name' => '离校人数'.$zdsqueqinCount);
        $zdsTj['title'] = ['请假人数'.$zdqjCount,'在校人数'.$zdschuqinCount,'离校人数'.$zdsqueqinCount];
        $zxsTj['data'][0] = array('value' => $zxqjCount, 'name' => '请假人数'.$zxqjCount);
        $zxsTj['data'][1] = array('value' => $zxschuqinCount, 'name' => '在校人数'.$zxschuqinCount);
        $zxsTj['data'][2] = array('value' => $zxsqueqinCount, 'name' => '离校人数'.$zxsqueqinCount);
        $zxsTj['title'] = ['请假人数'.$zxqjCount,'在校人数'.$zxschuqinCount,'离校人数'.$zxsqueqinCount];
        $return_data['list'] = $list;
        $return_data['zdsTj'] = $zdsTj;
        $return_data['zxsTj'] = $zxsTj;
    }
    return $return_data;
}

function getStuKqInfo($weid,$schoolid,$start,$end,$bj_id){
    $zxs = 0;
    $zds = 0;
    $qjCount = 0;
    $zdschuqinCount = 0;
    $zxschuqinCount = 0;
    $zdsqueqinCount = 0;
    $zxsqueqinCount = 0;
    $zdqjCount = 0;
    $zxqjCount = 0;
    //班级数据统计
    $list = pdo_fetchall("SELECT id as sid,s_name as sname,s_type FROM " . GetTableName('students') . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' AND bj_id = '{$bj_id}' ORDER BY id ASC");
    foreach ($list as $key => $value) {
        //当天学生是否请假
        $qj = pdo_fetch("SELECT sid FROM ".GetTableName('leave')." WHERE tid = 0 AND isliuyan = 0 AND kcid=0 AND ksid=0 AND schoolid = '{$schoolid}' AND startime1 <= $start && endtime1 >= $end AND sid = '{$value['sid']}' LIMIT 1");//请假数量
        if(empty($qj)){ //排除请假在统计数据
            if($value['s_type'] == 2){ //住校生
                $zxs ++;  //住校生总数
                $zxschuqin = pdo_fetch("SELECT sid,createtime,leixing FROM ".GetTableName('checklog')." WHERE sid = :sid AND schoolid = :schoolid AND (sc_ap = 0 OR sc_ap is null) AND createtime <= $end ORDER BY createtime DESC LIMIT 1",array(':sid'=>$value['sid'],':schoolid'=>$schoolid));
                if($zxschuqin['leixing'] == 1){
                    $list[$key]['chuqin'] = 1;
                    $list[$key]['queqin'] = 0;
                    $list[$key]['status'] = '在校 '.date("n月d日 H:i",$zxschuqin['createtime']);
                    $zxschuqinCount ++;
                }else{
                    $list[$key]['chuqin'] = 0;
                    $list[$key]['queqin'] = 1;
                    $list[$key]['status'] = "离校";
                    $zxsqueqinCount ++;
                }
            }else{
                $zds ++; //走读生总数
                $zdschuqin = pdo_fetch("SELECT sid,createtime,leixing FROM ".GetTableName('checklog')." WHERE sid = :sid AND schoolid = :schoolid AND sc_ap = 0 AND createtime <= $end ORDER BY createtime DESC LIMIT 1",array(':sid'=>$value['sid'],':schoolid'=>$schoolid));
                if($zdschuqin['leixing'] == 1){
                    $list[$key]['chuqin'] = 1;
                    $list[$key]['queqin'] = 0;
                    $list[$key]['status'] = '在校 '.date("n月d日 H:i",$zdschuqin['createtime']);
                    $zdschuqinCount ++;
                }else{
                    $list[$key]['chuqin'] = 0;
                    $list[$key]['queqin'] = 1;
                    $list[$key]['status'] = "离校";
                    $zdsqueqinCount ++;
                }
            }
            $list[$key]['qjStuCount'] = 0;
        }else{
            //走读生请假数量
            $zdsqj = pdo_fetchall("SELECT sid FROM ".GetTableName('leave')." as l LEFT JOIN ".GetTableName('students')." as s ON  l.sid = s.id   WHERE  l.tid = 0 and l.isliuyan = 0 AND l.kcid = 0 AND l.schoolid = '{$schoolid}' AND l.startime1 <= {$start} AND l.endtime1 >= $end AND l.sid = '{$value['sid']}' AND s.s_type != 2   ");

            //住校生请假数量
            $zxsqj = pdo_fetchall("SELECT sid FROM ".GetTableName('leave')." as l LEFT JOIN ".GetTableName('students')." as s ON  l.sid = s.id   WHERE  l.tid = 0 and l.isliuyan = 0 AND l.kcid = 0 AND l.schoolid = '{$schoolid}' AND l.startime1 <= {$start} AND l.endtime1 >= $end AND l.sid = '{$value['sid']}' AND s.s_type = 2   ");
            $zdqjCount += count($zdsqj); //走读生请假总计
            $zxqjCount += count($zxsqj); //住校生请假总计
            $qjCount ++;
            $list[$key]['chuqin'] = 0;
            $list[$key]['queqin'] = 0;
            $list[$key]['qjStuCount'] = 1;
            $list[$key]['status'] = "请假";
        }
    }
    $zdsTj['data'][0] = array('value' => $zdqjCount, 'name' => '请假人数'.$zdqjCount);
    $zdsTj['data'][1] = array('value' => $zdschuqinCount, 'name' => '在校人数'.$zdschuqinCount);
    $zdsTj['data'][2] = array('value' => $zdsqueqinCount, 'name' => '离校人数'.$zdsqueqinCount);
    $zdsTj['title'] = ['请假人数'.$zdqjCount,'在校人数'.$zdschuqinCount,'离校人数'.$zdsqueqinCount];
    $zxsTj['data'][0] = array('value' => $zxqjCount, 'name' => '请假人数'.$zxqjCount);
    $zxsTj['data'][1] = array('value' => $zxschuqinCount, 'name' => '在校人数'.$zxschuqinCount);
    $zxsTj['data'][2] = array('value' => $zxsqueqinCount, 'name' => '离校人数'.$zxsqueqinCount);
    $zxsTj['title'] = ['请假人数'.$zxqjCount,'在校人数'.$zxschuqinCount,'离校人数'.$zxsqueqinCount];
    $return_data['list'] = $list;
    $return_data['zdsTj'] = $zdsTj;
    $return_data['zxsTj'] = $zxsTj;
    return $return_data;
}
/****************************TODO:keep_Ls()定制功能*********************************/
