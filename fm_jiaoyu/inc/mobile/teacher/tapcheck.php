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
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
if(!empty($it)){
    $teacher = pdo_fetch("SELECT * FROM ".GetTableName("teachers")." WHERE id = '{$tid}' ");
    mload()->model('tea');
    $njlist = getSchoolBjlist($weid,$schoolid);
    $NjId = -1;
    $NjKey = -1;
    $BjId = -1;
    $NjN = "不限年级";
    $BjN = "不限班级";

    if($opereation == 'display'){
        $endtime = strtotime(date("Y-m-d",time())) + 86399 ;
        $starttime = $endtime - 8*86400 + 1;
        $list = pdo_fetchall("SELECT id,sid,apid,roomid,ap_type,bj_id FROM ".GetTableName('checklog') ." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and sc_ap = 1 AND createtime BETWEEN '{$starttime}' AND '{$endtime}' ORDER BY createtime DESC LIMIT 0,20 ");
        foreach($list as $key=>$value){
            $student = pdo_fetch("SELECT bj_id,s_name,icon FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            if($value['bj_id'] == $student['bj_id']){
                $apatrment =  pdo_fetch("SELECT name FROM " . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$value['apid']}' ")['name'];
                $aproom =  pdo_fetch("SELECT name FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$value['roomid']}'")['name'];
                $bjname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$student['bj_id']}' ")['sname'];
                $list[$key]['icon'] = $student['icon'] ?$student['icon'] : $school['spic'] ;
                $list[$key]['sname'] = $student['s_name'];
                $list[$key]['aproom'] = $aproom;
                $list[$key]['apatrment'] = $apatrment;
                $list[$key]['bjname'] = $bjname;
                $list[$key]['logtype'] = $value['ap_type'] == 1 ? "进寝":"离寝"; 
            }else{
                unset($list[$key]);
            }
            
        }
        include $this->template(''.$school['style3'].'/tapcheck');
    }elseif($opereation == 'More_Data'){
        $More_starttime = strtotime($_GPC['StartDate']);
        $More_endtime = strtotime($_GPC['EndDate']) + 86399;
        $Limit_start = $_GPC['LiData']['time'] ? $_GPC['LiData']['time'] +1 : 0 ;
        $condition = '';
        if($_GPC['ErrorSch'] == 'false' ){
            $condition .= ' and leixing != 3  ';
        }
        if($_GPC['InAp'] == 'false' && $_GPC['OutAp'] == 'false'){
            $condition .= ' and sc_ap != 1  ';
        }else{
            if($_GPC['InAp'] == 'false' ){
                $condition .= ' and ap_type != 1  ';
            }
            if($_GPC['OutAp'] == 'false' ){
                $condition .= ' and leixing != 2  ';
            } 
        }

        if($_GPC['NjId'] != -1){ //按年级搜索
            if($_GPC['BjId'] != -1){
                $condition .=" AND bj_id = '{$_GPC['BjId']}' ";
            }else{
                $Nj_Bj = pdo_fetchall("SELECT sid FROM ".GetTableName('classify')." WHERE parentid = '{$_GPC['NjId']}' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
                $Nj_Bj_str = '';
                foreach($Nj_Bj as $value){
                    $Nj_Bj_str .= $value['sid'].",";
                }
                $BjStr = trim($Nj_Bj_str,',');
                $condition .=" AND FIND_IN_SET(bj_id,'{$BjStr}') ";
            }
            
        }
        
        if($_GPC['LdId'] != -1){ //按楼栋搜索
            if($_GPC['LdId'] != -1){
                $condition .= " AND roomid = '{$_GPC['RoomId']}' ";
            }else{
                $condition .= " AND apid = '{$_GPC['LdId']}' ";
            }
            
        }

        $list1 = pdo_fetchall("SELECT id,sid,apid,roomid,ap_type,bj_id FROM ".GetTableName('checklog') ." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and sc_ap = 1 AND createtime BETWEEN '{$More_starttime}' AND '{$More_endtime}' {$condition} ORDER BY createtime DESC LIMIT {$Limit_start},20 ");
        foreach($list1 as  $key_1=>$value_1){
            $student = pdo_fetch("SELECT bj_id,s_name,icon FROM ".GetTableName('students')." WHERE id = '{$value_1['sid']}' ");
            if($value_1['bj_id'] == $student['bj_id']){
                $apatrment =  pdo_fetch("SELECT name FROM " . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$value_1['apid']}' ")['name'];
                $aproom =  pdo_fetch("SELECT name FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$value_1['roomid']}'")['name'];
                $bjname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$student['bj_id']}' ")['sname'];
            
                $list1[$key_1]['icon'] = $student['icon'] ?$student['icon'] : $school['spic'] ;
                $list1[$key_1]['sname'] = $student['s_name'];
                $list1[$key_1]['logtype'] = $value_1['ap_type'] == 1 ? "进寝":"离寝"; 
                $list1[$key_1]['aproom'] = $aproom;
                $list1[$key_1]['apatrment'] = $apatrment;
                $list1[$key_1]['bjname'] = $bjname;
                $list1[$key_1]['location'] = $key_1 + $Limit_start;
            }else{
                unset($list1[$key_1]);
            }
            
        }
        include $this->template('comtool/tapcheck');
    }elseif($opereation == 'GetDetail'){
        $id = $_GPC['id'];
        $Info = pdo_fetch("SELECT * FROM ".GetTableName('checklog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and id = '{$id}' ");
        $student = pdo_fetch("SELECT s_name,bj_id FROM ".GetTableName('students'). " WHERE id = '{$Info['sid']}' ");

        $apatrment =  pdo_fetch("SELECT name FROM " . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$Info['apid']}' ")['name'];
        $aproom =  pdo_fetch("SELECT name FROM " . tablename($this->table_aproom) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$Info['roomid']}'")['name'];
        $bj = pdo_fetch("SELECT sname,parentid FROM " . tablename($this->table_classify) . " WHERE sid = '{$student['bj_id']}' ");
        $NjName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$bj['parentid']}' ")['sname'];
        
        $pard = '';
        switch ($Info['pard']) {
            case 1:
                $pard = '本人';
                break;
            case 2:
                $pard = '母亲';
                break;
            case 3:
                $pard = '父亲';
                break;
            default:
                $pard = '其他家长';
                break;
        }
        $status = '';
        $status = $Info['ap_type'] == 1 ? "进寝":"离寝"; 
        $result['status'] = true;
        $result['data'] = array(
            'pard'=>$pard,
            'bjname'=>$bj['sname'],
            'njname'=>$NjName,
            'sname'=>$student['s_name'],
            'apatrment'=>$apatrment,
            'aproom'=>$aproom,
            'status' => $status,
            'checkTime' => date("Y-m-d H:i",$Info['createtime']),
            'pic' => tomedia($Info['pic']),
            'pic2' => tomedia($Info['pic2']),
        );
        die(json_encode($result));
    }elseif($opereation == 'GetNjListData'){ //获取年级/班级
        die(json_encode($njlist));
    }elseif($opereation == 'GetRoomListData'){//获取楼栋宿舍
        $allAp =  pdo_fetchall("SELECT id,name FROM " . tablename($this->table_apartment) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND FIND_IN_SET('{$tid}',tid)  ORDER BY CONVERT(name USING gbk) ASC");
        foreach ($allAp as $key => $value) {
            $roomlist = pdo_fetchall("SELECT id,name FROM " . tablename($this->table_aproom) . " where schoolid = '{$_GPC['schoolid']}' And apid = '{$value['id']}'  ORDER BY CONVERT(name USING gbk) ASC");
            $allAp[$key]['roomlist'] = $roomlist;
        }
        die(json_encode($allAp));

    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}