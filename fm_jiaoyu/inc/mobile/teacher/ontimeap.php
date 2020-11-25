<?php
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

$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($it)){
    $teacher = pdo_fetch("SELECT * FROM ".GetTableName("teachers")." WHERE id = '{$tid}' ");
	if($teacher['status'] == 2){
        $TeaSF = 2 ;
        $apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
    }else{
        $TeaSF = 1 ;
        $apartmentlist = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and  FIND_IN_SET('{$tid_global}',tid) ORDER BY CONVERT(name USING gbk) ASC ");
    }

if(empty($apartmentlist)){
    die();
}
  
    if($opereation == 'display'){
        $endtime = strtotime(date("Y-m-d",time())) + 86399 ;
        $starttime = $endtime - 8*86400 + 1;
        if($teacher['status'] != 2){ //如果不是校长 取可管辖年级
            $Rooms = pdo_fetchall("SELECT id FROM ".GetTableName('aproom')." WHERE apid = '{$apartmentlist[0]['id']}' ");
            $Apid = $apartmentlist[0]['id'];
            $Roomid = -1;
            $ApKey = 0;
            $RoomStr = '';
            foreach($Rooms as $k => $v){
                $RoomStr .= $v['id'].",";
            }
            $RoomStr = trim($RoomStr,',');
            $condition = " AND FIND_IN_SET(roomid,'{$RoomStr}') ";
 
        }else{ //如果是校长
            $Roomid = -1;
            $ApKey = -1;
            $Apid = -1; 
        }

        $list = pdo_fetchall("SELECT * FROM ".GetTableName('roomcheck')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition AND date >='{$starttime}' AND date <='{$endtime}' ORDER BY id DESC LIMIT 0,20   ");
        
        foreach ($list as $k_1 => $v_1) {
            $student = pdo_fetch("SELECT s_name,bj_id,xq_id,icon FROM ".GetTableName('students')." WHERE id = '{$v_1['sid']}' ");
            $bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");
            $njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['xq_id']}' ");
            $RoomInfo = pdo_fetch("SELECT name,apid FROM ".GetTableName('aproom')." WHERE id = '{$v_1['roomid']}' ");
            $ApInfo = pdo_fetch("SELECT name FROM ".GetTableName('apartment')." WHERE id = '{$RoomInfo['apid']}' ");
            $list[$k_1]['sicon'] = $student['icon'] ? $student['icon'] : $school['spic'] ;
            $list[$k_1]['sname'] = $student['s_name'];
            $list[$k_1]['bjname'] = $bjname['sname'];
            $list[$k_1]['njname'] = $njname['sname'];
            $list[$k_1]['roomname'] = $RoomInfo['name'];
            $list[$k_1]['apname'] = $ApInfo['name'];
        }


        // foreach($list as $key=>$value){
        //     $student = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
        //     $list[$key]['icon'] = $student['icon'] ?$student['icon'] : $school['spic'] ;
        //     $list[$key]['sname'] = $student['s_name'];
        //     if($value['sc_ap'] == 0){
        //         $list[$key]['logtype'] = $value['type']; 
        //     }elseif($value['sc_ap'] == 1){
        //         $list[$key]['logtype'] = $value['ap_type'] == 1 ? "进寝":"离寝"; 
        //     }
        // }
        include $this->template(''.$school['style3'].'/ontimeap');
    }elseif($opereation == 'More_Data'){
        $More_starttime = strtotime($_GPC['StartDate']);
        $More_endtime = strtotime($_GPC['EndDate']) + 86399;
        $Limit_start = $_GPC['LiData']['time'] ? $_GPC['LiData']['time'] +1 : 0 ;
        $condition = '';
        if($_GPC['Apid'] != -1 && $_GPC['RoomListKey'] == -1){ //选了楼栋没选宿舍
            $Rooms = pdo_fetchall("SELECT id FROM ".GetTableName('aproom')." WHERE apid = '{$_GPC['Apid']}' ");
            $RoomStr = '';
            foreach($Rooms as $k => $v){
                $RoomStr .= $v['id'].",";
            }
            $RoomStr = trim($RoomStr,',');
            $condition = " AND FIND_IN_SET(roomid,'{$RoomStr}') ";
        }elseif($_GPC['Apid'] != -1 && $_GPC['RoomListKey'] != -1){
            $condition = " AND roomid = '{$_GPC['RoomListKey']}' ";
        }


       // $list1 = pdo_fetchall("SELECT * FROM ".GetTableName('checklog') ." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' And sid != 0  {$condition} and createtime >='{$More_starttime}' and createtime <='{$More_endtime}'    ORDER BY createtime DESC LIMIT {$Limit_start},20 ");
        // foreach($list1 as  $key_1=>$value_1){
        //     $student = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$value_1['sid']}' ");
        //     $list1[$key_1]['icon'] = $student['icon'] ?$student['icon'] : $school['spic'] ;
        //     $list1[$key_1]['sname'] = $student['s_name'];
        //     if($value_1['sc_ap'] == 0){
        //         $list1[$key_1]['logtype'] = $value_1['type']; 
        //     }elseif($value_1['sc_ap'] == 1){
        //         $list1[$key_1]['logtype'] = $value_1['ap_type'] == 1 ? "进寝":"离寝"; 
        //     }
        //     $list1[$key_1]['location'] = $key_1 + $Limit_start;
        // }

        $list = pdo_fetchall("SELECT * FROM ".GetTableName('roomcheck')." WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition AND date >='{$More_starttime}' AND date <='{$More_endtime}' ORDER BY id DESC LIMIT {$Limit_start},20   ");
        
        foreach ($list as $k_1 => $v_1) {
            $student = pdo_fetch("SELECT s_name,bj_id,xq_id,icon FROM ".GetTableName('students')." WHERE id = '{$v_1['sid']}' ");
            $bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['bj_id']}' ");
            $njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$student['xq_id']}' ");
            $RoomInfo = pdo_fetch("SELECT name,apid FROM ".GetTableName('aproom')." WHERE id = '{$v_1['roomid']}' ");
            $ApInfo = pdo_fetch("SELECT name FROM ".GetTableName('apartment')." WHERE id = '{$RoomInfo['apid']}' ");
            $list[$k_1]['sicon'] = $student['icon'] ? $student['icon'] : $school['spic'] ;
            $list[$k_1]['sname'] = $student['s_name'];
            $list[$k_1]['bjname'] = $bjname['sname'];
            $list[$k_1]['njname'] = $njname['sname'];
            $list[$k_1]['roomname'] = $RoomInfo['name'];
            $list[$k_1]['apname'] = $ApInfo['name'];
            $list[$k_1]['location'] = $k_1 + $Limit_start;
        }

   
        //var_dump( $More_starttime);
        include $this->template('comtool/ontimeap');
    }elseif($opereation == 'GetDetail'){
        $id = $_GPC['id'];
        $Info = pdo_fetch("SELECT * FROM ".GetTableName('roomcheck')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and id = '{$id}' ");



        //var_dump($Info);
        $CheckStu = pdo_fetch("SELECT s_name,bj_id FROM ".GetTableName('students'). " WHERE id = '{$Info['sid']}' ");
        $bj_name = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE sid = '{$CheckStu['bj_id']}' and type = 'theclass' ");
        $Nj_name = pdo_fetch("SELECT * FROM ".GetTableName('classify')." WHERE sid = '{$bj_name['parentid']}' ");
        $RoomInfo = pdo_fetch("SELECT * FROM ".GetTableName('aproom')." WHERE id = '{$Info['roomid']}' ");
        $apinfo  = pdo_fetch("SELECT * FROM ".GetTableName('apartment')." WHERE id = '{$RoomInfo['apid']}' ");
       // $Mac = pdo_fetch("SELECT name FROM ".GetTableName('checkmac'). " WHERE id = '{$Info['macid']}' ");
 
        if($Info['type'] == 1){
            $type = "中午";
        }
        if($Info['type'] == 2){
            $type = "晚上";
        }
       
        $result['status'] = true;
        $result['data'] = array(
            'BjName'=>$bj_name['sname'],
            'NjName'=>$Nj_name['sname'],
            'StuName'=>$CheckStu['s_name'],
            'RoomName'=>$RoomInfo['name'],
            'ApName'=>$apinfo['name'],
            'time' => date("Y年m月d日",$Info['date']),
            'type' => $type
        );
        die(json_encode($result));
    }elseif($opereation == 'GetApData'){
        if($TeaSF == 2){ //如果可以看所有的楼栋
            $apartmentlist = pdo_fetchall('SELECT id,name FROM ' . GetTableName('apartment') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY CONVERT(name USING gbk) ASC ");
            $NjN = "不限楼栋";
        }elseif($TeaSF == 1){ //如果只能看自己管辖的楼栋
            $TeaSF = 1 ;
            $apartmentlist = pdo_fetchall('SELECT id,name FROM ' . GetTableName('apartment') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and  FIND_IN_SET('{$tid_global}',tid) ORDER BY CONVERT(name USING gbk) ASC ");
            $firstAp = $apartmentlist[0];
            $NjN = $firstAp['name'];
        }

        foreach($apartmentlist as $key=>$value){
            $RoomList = pdo_fetchall("SELECT id,name FROM ".GetTableName('aproom')." WHERE apid = '{$value['id']}' ");
            $apartmentlist[$key]['roomlist'] = $RoomList;
        }



        //$firstap = $apartmentlist[0];
       // $firstNj = $njlist[0];
      
    
           
        
        // if(!empty($firstBj)){
        //     $bjcondition = "and bj_id = '{$firstBj['sid']}'";
        //     $BjN = $firstBj['sname'];
        // }else{
        //     $BjN = "不限班级";
        // }


        $result['data'] = $apartmentlist;
        $result['TeaSF'] = $TeaSF;
        die(json_encode($result));

    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}