<?php
/*
 * @Discription:  华瑞安接口文件
 * @Author: Hannibal·Lee
 * @Date: 2020-07-17 15:04:21
 * @LastEditTime: 2020-08-27 16:41:11
 */

global $_GPC, $_W;

$operation = in_array($_GPC['op'], array('default', 'getSchoolList', 'getGradePageList', 'getClassPageList', 'getStudentList', 'getTeeacherList', 'getParentList', 'checkReport', 'tempReport', 'newsSync', 'getTeacherList','getCourseList','getSchoolInfo','getSignList','getCookBook')) ? $_GPC['op'] : 'default';

if ($operation == 'default') {
    die(json_encode(array(
        'code' => -1,
        'msg' => '非法请求',
    ), JSON_UNESCAPED_UNICODE));
}

$resultData = array(
    'code' => 200,
    'msg' => '',
);

$_PData = $_GPC;
if (!empty($_GPC['i'])) {
    $weid = $_GPC['i'];
}

//获取学校列表
if ($operation == 'getSchoolList') {
    $schoolList = pdo_fetchall("SELECT id as schoolId,title as schoolName FROM " . GetTableName('index') . " WHERE id != 3 ");
    // if (!empty($weid)) {
    //     $schoolList = pdo_fetchall("SELECT id as schoolId,title as schooolName FROM " . GetTableName('index') . " WHERE weid = '{$weid}' ");
    // }
    $resultData['data'] = $schoolList;
    $resultData['msg'] = '获取学校列表成功';
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//获取本校年级列表
// @api(value="用户controller",tags={"用户操作接口"})
if ($operation == 'getGradePageList') {
    $schoolid = $_PData['schoolId'];
    if (empty($schoolid)) {
        $resultData['code'] = -1;
        $resultData['msg'] = '请求参数缺失';
    } else {
        $GradeList = pdo_fetchall("SELECT sid as gradeId  , sid as gradeCode , sid as gradeScode , sname as gradeName , '' as studyStageCode  FROM " . GetTableName('classify') . " WHERE  schoolid ='{$schoolid}' and type = 'semester' and is_over != 2 ORDER BY ssort DESC, sid DESC  ");
        $resultData['data'] = $GradeList;
        $resultData['msg'] = '获取年级列表成功';
    }
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//获取班级列表
if ($operation == 'getClassPageList') {
    $schoolid = $_PData['schoolId'];
    $gradeid = $_PData['gradeId'];

    if (empty($schoolid) || empty($gradeid)) {
        $resultData['code'] = -1;
        $resultData['msg'] = '请求参数缺失';
    } else {
        $classList = pdo_fetchall("SELECT sid as classId,sname as className , schoolid as schoolId , parentid as gradeId , tid as teacherId FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' and parentid = '{$gradeid}' and type ='theclass' and is_over != 2 ORDER BY ssort DESC , sid DESC  ");
        foreach($classList as &$class) {
            $Bz = pdo_fetch("SELECT id,s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and bj_id = '{$class['classId']}' and is_banzhang = 1 ORDER BY id ASC LIMIT 0,1 ");
            $class['monitorName'] = $Bz['s_name'];
        }
        $resultData['data'] = $classList;
        $resultData['msg'] = '获取班级列表成功';
    }
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));

}

//获取班级学生信息
//TODO: 答题卡号是什么
if ($operation == 'getStudentList') {
    $schoolid = $_PData['schoolId'];
    $gradeid = $_PData['gradeId'];
    $classid = $_PData['classId'];

    if (empty($schoolid) || empty($gradeid) || empty($classid)) {
        $resultData['code'] = -1;
        $resultData['msg'] = '请求参数缺失';
    } else {
        $studentsList = pdo_fetchall("SELECT s_name as realName , sex , numberid as studentNumber , {$gradeid} as gradeId , {$classid} as classId , {$schoolid} as schoolId ,'' as answerTool ,''  as quanPin  , icon FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' and bj_id = '{$classid}' ");
        foreach ($studentsList as &$value) {
            $value['studentHeadImage'] = tomedia($value['icon']);
            unset($value['icon']);
        }
        $resultData['data'] = $studentsList;
        $resultData['msg'] = '获取学生列表成功';
    }
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//获取家长信息
//TODO:parentType 家长类型是什么
if ($operation == 'getParentList') {
    $schoolid = $_PData['schoolId'];
    $pageSize = $_PData['pageSize'];
    $pageNo = $_PData['pageNo'];

    if (empty($schoolid) || empty($pageSize) || empty($pageNo)) {
        $resultData['code'] = -1;
        $resultData['msg'] = '请求参数缺失';
    } else {
        $total = pdo_fetchcolumn(" SELECT count(u.id) FROM " . GetTableName('user') . " as u LEFT JOIN " . GetTableName('students') . " as s on u.sid = s.id  LEFT JOIN " . GetTableName('classify') . " as c on s.bj_id = c.sid  LEFT JOIN " . GetTableName('classify') . " as g on c.parentid = g.sid  WHERE u.schoolid = '{$schoolid}' and u.sid != 0 and u.tid = 0 and c.is_over != 2 and g.is_over != 2 ");
        $totalPage = ceil($total / $pageSize);

        $parentList = pdo_fetchall("SELECT u.schoolid as schoolId, s.numberid as studentNumber , u.mobile as phone , u.realname as realName , ( CASE u.pard WHEN  2 THEN 1 WHEN 3 THEN 0 WHEN 5 THEN 2  END ) as sex , '' as parentAccount , '' as parentType , '' as quanpin , '' as answerTool , s.bj_id as classId , s.xq_id as gradeId , ( CASE u.pard WHEN  2 THEN 28 WHEN 3 THEN 22 WHEN 5 THEN 29  END ) as sex FROM " . GetTableName('user') . " as u LEFT JOIN " . GetTableName('students') . " as s ON  u.sid = s.id   WHERE  u.schoolid = '{$schoolid}' and u.sid != 0 and u.tid = 0 and u.pard != 4 ORDER BY u.id DESC LIMIT " . ($pageNo - 1) * $pageSize . ", {$pageSize} ");
        $resultData['total'] = intval($total);
        $resultData['totalPage'] = intval($totalPage);
        $resultData['data'] = $parentList;
    }
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//提交刷卡考勤
if ($operation == 'checkReport') {
    $SignStatus = array(
        0 => 2,
        1 => 1,
    );
    // $_PData1 = file_get_contents('php://input');
    // CommonWriteLog($_PData1,'hra');

    // // $_PData = json_decode($_PData1,true);
    $v = $data = $_PData['data'];
    $dataBody = json_decode(htmlspecialchars_decode($_PData['bodyData']), true);

    if (!empty($dataBody)) {
        foreach ($dataBody as $key => $v) {
            $checkStu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " WHERE schoolid = '{$v['schoolId']}' and xq_id = '{$v['gradeId']}' and bj_id = '{$v['classId']}' and numberid = '{$v['studentNumber']}' ");
            $school = pdo_fetch("SELECT * FROM " . GetTableName('index') . " WHERE id = '{$v['schoolId']}' ");
            $schoolid = $school['id'];
            $weid = $school['weid'];
            if (!empty($checkStu)) {
                $signMode = $SignStatus[$v['signed']];
                include '../mac/checktime.php';
                $data = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'macid' => $v['macId'],
                    'sid' => $checkStu['id'],
                    'bj_id' => $v['classId'],
                    'type' => $type,
                    'pic' => $v['pic'],
                    'leixing' => $leixing,
                    'pard' => 4,
                    'createtime' => strtotime($v['signedTime']),
                    'surestatus' => 1,
                );
                pdo_insert(GetTableName('checklog', false), $data);
            }
        }

        $resultData['msg'] = '接收考勤数据成功';

    } else {
        $resultData['code'] = -1;
        $resultData['msg'] = '无考勤数据';
    }
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));

}

//接收体温上报数据
if ($operation == 'tempReport') {
    $d = $dataBody = json_decode(htmlspecialchars_decode($_PData['bodyData']), true);
    $checkStu = pdo_fetch("SELECT id,weid FROM " . GetTableName('students') . " WHERE schoolid = '{$d['schoolId']}' and xq_id = '{$d['gradeId']}' and bj_id = '{$d['classId']}' and numberid = '{$d['$studentNumber']}'   ");
    if (empty($checkStu)) {
        $resultData['code'] = -1;
        $resultData['msg'] = '未找到学生';
    } else {
        $data = array(
            'weid' => $checkStu['weid'],
            'schoolid' => $d['schoolId'],
            'bj_id' => $d['classId'],
            'sid' => $checkStu['id'],
            'macid' => $d['macid'],
            'tiwen' => $d['temp'],
            'createtime' => time(),
            'createdate' => strtotime($d['day']),
            'issb' => 1,
            'is_mc' => 0,
        );
        pdo_insert(GetTableName('morningcheck', false), $data);
        $resultData['msg'] = '接收体温数据成功';
    }
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//微教育发布的新闻同步至班牌
if ($operation == 'newsSync') {
    $d = $_PData;
    // !=3排除作业
    $allNotice = pdo_fetchall("SELECT * FROM " . GetTableName('notice') . " WHERE schoolid = '{$d['schoolId']}' AND type != 3 AND is_sync = 0 ORDER BY id DESC ");
    $returnData = [];
    foreach ($allNotice as $key => $value) {
        $returnData[$key]['schoolId'] = $value['schoolid'];
        $returnData[$key]['content'] = htmlspecialchars_decode($value['content']);

        $returnData[$key]['category'] = $value['type'] == 1 ? 2 : 1;
        if ($value['bj_id']) {
            $returnData[$key]['classes']['class_id'][] = $value['bj_id'];
        } else {
            $returnData[$key]['classes'] = [];
        }
        $returnData[$key]['title'] = $value['title'];
        $returnData[$key]['sender'] = $value['tname'];
        $returnData[$key]['publish_time'] = date("Y-m-d H:i:s", $value['createtime']);
    }
    $resultData['msg'] = '获取所有数据成功';
    $resultData['data'] = $returnData;
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//获取教师列表
if ($operation == 'getTeacherList') {
    $d = $_PData;
    $allTeachers = pdo_fetchall("SELECT id as teacherId, tname as teacherName, sex , '' as pinYin, '' as quanPin FROM " . GetTableName('teachers') . " WHERE schoolid = '{$d['schoolId']}' ORDER BY id DESC ");
    $resultData['msg'] = '获取所有数据成功';
    $resultData['data'] = $allTeachers;
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

// 获取课表
if($operation == 'getCourseList'){
    $WeekArr = ['sun','mon','tue','wed','thu','fri','sat'];
    $gradeId = $_PData['gradeId'];
    $classId = $_PData['classId'];
    $schoolid = $_PData['schoolId'];
    $returnData = [];
    for($i=0;$i<7;$i++){
        $time = time() + $i*86400;
        $courselist = getkcbiao($schoolid,$time ,$classId);
        foreach ($courselist as $value) {
            $returnData[] = array(
            'section_number' => ''.$value['section'],
            'weekday' => $WeekArr[$value['week']],
            'course' => $value['course_name']
            );
        }
    }
    $resultData['msg'] = '获取所有数据成功';
    $resultData['data'] = $returnData;
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}


//获取学校信息
if($operation == 'getSchoolInfo'){
    $schoolid = $_PData['schoolId'];
    $schoolinfo = pdo_fetch("SELECT logo,title,content FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
    if(empty($schoolinfo)){
    $resultData['msg'] = "学校不存在";
    $resultData['status'] = false;
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));

        
    }
    $banners = pdo_fetchall("SELECT thumb,displayorder FROM ".GetTableName('banners')." WHERE schoolid = '{$schoolid}'  ORDER BY displayorder DESC");
    foreach ($banners as &$value) {
        $value['imgUrl'] = tomedia($value['thumb']);
        $value['displayorder'] = intval($value['displayorder']);
        unset($value['thumb']);
    }
    $schoolinfo['logo'] = tomedia($schoolinfo['logo']);
    $resultData['msg'] = "获取学校信息成功";
    $resultData['data'] = array(
        'schoolLogo'=> $schoolinfo['logo'],
        'schoolName' => $schoolinfo['title'],
        'content' => htmlspecialchars_decode($schoolinfo['content']),
        'banners' => $banners
    );
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//获取班级签到数据
if($operation == 'getSignList'){
    $schoolid = $_PData['schoolId'];
    $gradeId = $_PData['gradeId'];
    $classId = $_PData['classId'];
    $checktime = strtotime($_PData['checktime']);
    $CheckStarttime = strtotime(date("Y-m-d",$checktime));
    if (empty($schoolid) || empty($gradeId) || empty($classId)) {
        $resultData['code'] = -1;
        $resultData['msg'] = '请求参数缺失';
    }else{
        $starttime = strtotime(date("Y-m-d",$checktime));
        $checkStu = pdo_fetchall("SELECT s.s_name as studentName,s.numberid as studentNumber,FROM_UNIXTIME(c.createtime,'%Y-%m-%d %T') as signtime,c.leixing as InOrOut  FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('checklog')." as c ON s.id = c.sid  WHERE  s.schoolid = '{$schoolid}' and s.bj_id = '{$classId}' and c.createtime > '{$CheckStarttime}' and c.createtime <= '{$checktime}' and (c.leixing = 1 or c.leixing = 2) and c.isconfirm = 1 ");
        $resultData['msg'] = '获取班级签到数据成功';
        $resultData['data'] = $checkStu;
    }
   
    die(json_encode($resultData, JSON_UNESCAPED_UNICODE));
}

//获取食谱信息
if($operation == 'getCookBook'){
    $schoolid = $_PData['schoolId'];
    $starttime = strtotime(date("Y-m-d",time()));
    $endtime = strtotime(date("Y-m-d 23:59:59",time()));
    $cooklist = pdo_fetch("SELECT * FROM ".GetTableName('cookbook')." WHERE schoolid = '{$schoolid}' and ishow = 1 and begintime <= '{$starttime}' and endtime >= '{$endtime}' ORDER BY id DESC ");
    $monday = unserialize($cooklist['monday']);
    $tuesday = unserialize($cooklist['tuesday']);
    $wednesday = unserialize($cooklist['wednesday']);
    $thursday = unserialize($cooklist['thursday']);
    $friday = unserialize($cooklist['friday']);
    $saturday = unserialize($cooklist['saturday']);
    $sunday = unserialize($cooklist['sunday']);
    
    $list = [];
    $mon = array(
        0 => array(
            'part'=>'breakfast',
            'title'=>$monday['mon_zc'],
            'image'=>tomedia($monday['mon_zc_pic'])
        ),
        1 => array(
            'part'=>'morningTea',
            'title'=>$monday['mon_zjc'],
            'image'=>tomedia($monday['mon_zjc_pic'])
        ),
        2 => array(
            'part'=>'lunch',
            'title'=>$monday['mon_wc'],
            'image'=>tomedia($monday['mon_wc_pic'])
        ),
        3 => array(
            'part'=>'afternoonTea',
            'title'=>$monday['mon_wjc'],
            'image'=>tomedia($monday['mon_wjc_pic'])
        ),
        4 => array(
            'part'=>'supper',
            'title'=>$monday['mon_wwc'],
            'image'=>tomedia($monday['mon_wwc_pic'])
        ),
    );

    $tus = array(
        0 => array(
            'part'=>'breakfast',
            'title'=>$tuesday['tus_zc'],
            'image'=>tomedia($tuesday['tus_zc_pic'])
        ),
        1 => array(
            'part'=>'morningTea',
            'title'=>$tuesday['tus_zjc'],
            'image'=>tomedia($tuesday['tus_zjc_pic'])
        ),
        2 => array(
            'part'=>'lunch',
            'title'=>$tuesday['tus_wc'],
            'image'=>tomedia($tuesday['tus_wc_pic'])
        ),
        3 => array(
            'part'=>'afternoonTea',
            'title'=>$tuesday['tus_wjc'],
            'image'=>tomedia($tuesday['tus_wjc_pic'])
        ),
        4 => array(
            'part'=>'supper',
            'title'=>$tuesday['tus_wwc'],
            'image'=>tomedia($tuesday['tus_wwc_pic'])
        )
    );

    $wed = array(
        0 => array(
            'part'=>'breakfast',
            'title'=>$wednesday['wed_zc'],
            'image'=>tomedia($wednesday['wed_zc_pic'])
        ),
        1 => array(
            'part'=>'morningTea',
            'title'=>$wednesday['wed_zjc'],
            'image'=>tomedia($wednesday['wed_zjc_pic'])
        ),
        2 => array(
            'part'=>'lunch',
            'title'=>$wednesday['wed_wc'],
            'image'=>tomedia($wednesday['wed_wc_pic'])
        ),
        3 => array(
            'part'=>'afternoonTea',
            'title'=>$wednesday['wed_wjc'],
            'image'=>tomedia($wednesday['wed_wjc_pic'])
        ),
        4 => array(
            'part'=>'supper',
            'title'=>$wednesday['wed_wwc'],
            'image'=>tomedia($wednesday['wed_wwc_pic'])
        )
    );

    $thu = array(
        0 => array(
            'part'=>'breakfast',
            'title'=>$thursday['thu_zc'],
            'image'=>tomedia($thursday['thu_zc_pic'])
        ),
        1 => array(
            'part'=>'morningTea',
            'title'=>$thursday['thu_zjc'],
            'image'=>tomedia($thursday['thu_zjc_pic'])
        ),
        2 => array(
            'part'=>'lunch',
            'title'=>$thursday['thu_wc'],
            'image'=>tomedia($thursday['thu_wc_pic'])
        ),
        3 => array(
            'part'=>'afternoonTea',
            'title'=>$thursday['thu_wjc'],
            'image'=>tomedia($thursday['thu_wjc_pic'])
        ),
        4 => array(
            'part'=>'supper',
            'title'=>$thursday['thu_wwc'],
            'image'=>tomedia($thursday['thu_wwc_pic'])
        )
    );

    $fri = array(
        0 => array(
            'part'=>'breakfast',
            'title'=>$friday['fri_zc'],
            'image'=>tomedia($friday['fri_zc_pic'])
        ),
        1 => array(
            'part'=>'morningTea',
            'title'=>$friday['fri_zjc'],
            'image'=>tomedia($friday['fri_zjc_pic'])
        ),
        2 => array(
            'part'=>'lunch',
            'title'=>$friday['fri_wc'],
            'image'=>tomedia($friday['fri_wc_pic'])
        ),
        3 => array(
            'part'=>'afternoonTea',
            'title'=>$friday['fri_wjc'],
            'image'=>tomedia($friday['fri_wjc_pic'])
        ),
        4 => array(
            'part'=>'supper',
            'title'=>$friday['fri_wwc'],
            'image'=>tomedia($friday['fri_wwc_pic'])
        )
    );

    $sat = array(
        0 => array(
            'part'=>'breakfast',
            'title'=>$saturday['sat_zc'],
            'image'=>tomedia($saturday['sat_zc_pic'])
        ),
        1 => array(
            'part'=>'morningTea',
            'title'=>$saturday['sat_zjc'],
            'image'=>tomedia($saturday['sat_zjc_pic'])
        ),
        2 => array(
            'part'=>'lunch',
            'title'=>$saturday['sat_wc'],
            'image'=>tomedia($saturday['sat_wc_pic'])
        ),
        3 => array(
            'part'=>'afternoonTea',
            'title'=>$saturday['sat_wjc'],
            'image'=>tomedia($saturday['sat_wjc_pic'])
        ),
        4 => array(
            'part'=>'supper',
            'title'=>$saturday['sat_wwc'],
            'image'=>tomedia($saturday['sat_wwc_pic'])
        )
    );

    $sun = array(
        0 => array(
            'part'=>'breakfast',
            'title'=>$sunday['sat_zc'],
            'image'=>tomedia($sunday['sat_zc_pic'])
        ),
        1 => array(
            'part'=>'morningTea',
            'title'=>$sunday['sat_zjc'],
            'image'=>tomedia($sunday['sat_zjc_pic'])
        ),
        2 => array(
            'part'=>'lunch',
            'title'=>$sunday['sat_wc'],
            'image'=>tomedia($sunday['sat_wc_pic'])
        ),
        3 => array(
            'part'=>'afternoonTea',
            'title'=>$sunday['sat_wjc'],
            'image'=>tomedia($sunday['sat_wjc_pic'])
        ),
        4 => array(
            'part'=>'supper',
            'title'=>$sunday['sat_wwc'],
            'image'=>tomedia($sunday['sat_wwc_pic'])
        )
    );
    $list[] = array(
        'weekday'=>'mon',
        'cookbook' => $mon
    );
    $list[] = array(
        'weekday'=>'mon',
        'cookbook' => $mon
    );
    $list[] = array(
        'weekday'=>'tus',
        'cookbook' => $tus
    );
    $list[] = array(
        'weekday'=>'wed',
        'cookbook' => $wed
    );
    $list[] = array(
        'weekday'=>'thu',
        'cookbook' => $thu
    );
    $list[] = array(
        'weekday'=>'sat',
        'cookbook' => $sat
    );
    $list[] = array(
        'weekday'=>'sun',
        'cookbook' => $sun
    );
    $resultData['msg'] = "获取食谱成功";
    $resultData['data'] = $list;
    die(json_encode($resultData,JSON_UNESCAPED_UNICODE));
    
}
