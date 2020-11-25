<?php
/*
 * @Discription:  讯贞10分钟定时检查新增绑定考勤卡
 * @Author: Hannibal·Lee
 * @Date: 2020-01-15 10:11:49
 * @LastEditTime: 2020-09-04 10:09:56
 */
global $_GPC, $_W;
//有人脸的学校列表

$schoollist = pdo_fetchall("SELECT distinct schoolid FROM ".GetTableName('checkmac')." WHERE weid = '{$_W['uniacid']}' and  macname = 2 ");
$porturl = '/add_users_by_device.do';
if(!empty($schoollist)){
    foreach($schoollist as $vs){
        $checkmac = pdo_fetch("SELECT macid FROM ".GetTableName('checkmac')." WHERE schoolid = '{$vs['schoolid']}' AND macname = 2 ORDER BY id DESC  ");
        $deviceNo  = $checkmac['macid'];
        $userInfos = array();
        //检查当前学校是否有新绑定的卡
        $cardlist = pdo_fetchall("SELECT i.usertype,i.idcard,i.spic,i.tpic,s.s_name,s.s_type,s.icon ,s.bj_id,t.tname,t.fz_id,t.thumb,i.tid,i.sid FROM ".GetTableName('idcard')." as i left join  ".GetTableName('students')." as s  ON s.id = i.sid  left join ".GetTableName('teachers')." as t on i.tid = t.id WHERE i.schoolid = '{$vs['schoolid']}' and i.face_status = 1  ");
        foreach($cardlist as $value_cl){
            if($value_cl['usertype'] == 1){ //老师
                $imagePath = !empty($value_cl['tpic']) ? $value_cl['tpic'] : $value_cl['thumb'] ;
                $userName  = $value_cl['tname'];
                $userType  = 4;
                $groupNo   ='909'.$value_cl['fz_id'];
                $groupName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$groupNo}' ")['sname'];
                $userNo    = '909'.$value_cl['tid'];
            }elseif($value_cl['usertype'] == 0){ //学生
                $imagePath = !empty($value_cl['spic']) ? $value_cl['spic'] : $value_cl['icon'] ;
                $userName  = $value_cl['s_name'];
                $userType  = $value_cl['s_type'];
                $groupNo   = $value_cl['bj_id'];
                $userNo    = $value_cl['sid'];
                $groupName = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$groupNo}' ")['sname'];
            }
            $userInfos[] = array(
                'idCardNum' => '',
                'imagePath' => tomedia($imagePath),
                'rfid'      => $value_cl['idcard'],
                'userName'  => $userName,
                'userNo'    => $userNo,
                'userType'  => $userType,
                'groupNo'   => $groupNo,
                'groupName' => $groupName,
                'timeSet'   => array(
                    'timeType'=>'weeks',
                    'weeks'=>array(
                        'day' => '1,2,3,4,5,6,7',
                        'passTime'=>array(
                            array(
                                'start'=>'00:00',
                                'end'=>'23:59'
                            ),
                        )
                    ),
                    
                ),
            );
        }
        $sendData['deviceNo']  = $deviceNo;
        $sendData['userInfos'] = $userInfos;
        if(!empty($cardlist)){
            $res = json_decode(FaceSendData($porturl,$sendData),true);
            if(intval($res['code']) ==  200){
                pdo_update(GetTableName('idcard',false),array('face_status' => 0),array('schoolid'=>$vs['schoolid']));
                $data['msg']    = "任务执行完成";
            }else {
                $data['msg']    = "任务执行完成,返回结果不成功";
            }
        }else{
            $data['msg']    = "任务执行完成,暂无新增卡";

        }
        $data['extra'][] = array(
            'res' => $res,
            'mac' => $deviceNo,
            'schoolid' => $vs['schoolid'],
        );

    }

    $data['res'] = intval($res['code']) ;
    $data['sendData'] = $sendData ;
    $data['result'] = true;
}else{
    $data['msg']    = "当前无可执行任务";
    $data['result'] = false;
}

die(json_encode($data));

// $schoolset = pdo_fetchall("SELECT schoolid,id,weid FROM " . GetTableName('schoolset') . " WHERE top = 1 ");
// if(!empty($schoolset)){
//     foreach ($schoolset as $key => $value) {
//         $checkmac = pdo_fetch("SELECT macid FROM ".GetTableName('checkmac')." WHERE schoolid = '{$value['schoolid']}' AND weid = '{$value['weid']}' AND macname = 2 AND macid like 'A%'");
//         if($checkmac){
//             $hasdata = GetFaceUrlData('http://yz.kstms.com',$checkmac['macid']);
//             if(!empty($hasdata)){
//                 pdo_update(GetTableName('schoolset',false),array('top'=>0),array('id'=>$value['id']));
//                 $data['msg'] = "任务执行完成";
//                 $data['result'] = true;
//             }else{
//                 $data['msg'] = "当前无可执行任务";
//                 $data['result'] = false;
//             }
//         }
//     }
// }else{
//     $data['msg'] = "当前无可执行任务";
//     $data['result'] = false;
// }
// echo json_encode($data);
// exit;
// die;
