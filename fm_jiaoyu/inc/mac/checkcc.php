<?php
/**
 * By 高贵血迹
 */
include_once 'aliyun-php-sdk-core/Config.php';
use Sts\Request\V20150401 as Sts;
global $_GPC, $_W;
$operation = in_array ( $_GPC ['op'], array ('postTemperature','Temperature','Epaper','Honour','Exam','Countdown','Application','default','notice', 'device', 'class','classes', 'student','check','teacher','picture','attendence','news','activity','postactivity','timetable','duty', 'praise','food','log','daylog', 'banner', 'video', 'start',  'users', 'getdate','custom','command','getdevremote','checkap','getroomlist','token','getmessage','postmessage','postread','rtc') ) ? $_GPC ['op'] : 'default';
$weid      = $_GPC['i'];
$schoolid  = $_GPC['schoolid'];
$macid     = empty($_GPC['macid'])? $_GPC['deviceId'] : $_GPC['macid'];
$ckmac     = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_mac) . " WHERE macid = '{$macid}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
$school    = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}' ");
$xk_type = pdo_fetch("SELECT * FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}' ");
$school['xk_type'] =$xk_type['xk_type'];
$pindex    = max(1, intval($_GPC['currentPage']));
$psize     = empty($_GPC['pageSize'])?'10':$_GPC['pageSize'];
if ($operation == 'default') {
	$result['status'] = -1;
	$result['msg']    = "对不起，你的请求不存在！";
	echo json_encode($result);
	exit;
}
if(empty($school)) {
	$result['status'] = -1;
	$result['msg']    = "找不到本校，设备未关联学校?";
	echo json_encode($result);
	exit;
}
if(empty($ckmac)) {
	$result['status'] = -1;
	$result['msg']    = "没找到设备,请添加设备";
	echo json_encode($result);
	exit;
}
if($school['is_recordmac'] == 2) {
	$result['status'] = -1;
	$result['msg']    = "本校无权使用设备,请联系管理员";
	echo json_encode($result);
	exit;
}
if ($ckmac['is_on'] == 2) {
	$result['status'] = -1;
	$result['msg'] = "本设备已关闭,请联系管理员";
	echo json_encode($result);
	exit;
}
if (empty($_W['setting']['remote']['type'])) {
	$urls = $_W['SITEROOT'].$_W['config']['upload']['attachdir'].'/';
} else {
	$urls = $_W['attachurl'];
}
if ($operation == 'Honour') {
    if(!empty($ckmac)) {
        $list = pdo_fetchall("SELECT honourName,id as honourId,addTime,pictureUrls  FROM " . tablename($this->table_classcard_honour) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And bj_id = {$ckmac['bj_id']} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $total=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_honour) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  and bj_id = '{$ckmac['bj_id']}' ");
        $data=array();
        foreach ($list as $key => $row) {
            $data[$key]['honourName'] =$row['honourName'] ;
            $data[$key]['honourId'] =$row['honourId'] ;
            $data[$key]['addTime'] =$row['addTime'] ;
            $imgarr1=[];
            foreach (unserialize($row['pictureUrls']) as $k => $m) {
                $imgarr1[$k] = tomedia($m);
            }
            $data[$key]['pictureUrls'] = $imgarr1;
        }
        $result['status'] = "0";
        $result['msg']    = "获取数据成功";
        $result['data']['total']=$total;
        $result['data']['currentPage']=$pindex;
        $result['data']['totalPage']= ceil(($total)/$psize);
        $result['data']['pageSize']=$psize;
        $result['data']['list'] = $data;
        $result['servertime'] = time();
        echo json_encode($result);
        die();
    }
}
if ($operation == 'Epaper') {
    if(!empty($ckmac)) {
        $list = pdo_fetchall("SELECT epaperName,id as epaperId,addTime,pictureUrls FROM " . tablename($this->table_classcard_epaper) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And bj_id = {$ckmac['bj_id']} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $total=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_epaper) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}'  and bj_id = '{$ckmac['bj_id']}' ");
        $data=array();
        foreach ($list as $key => $row) {
            $data[$key]['epaperName'] =$row['epaperName'] ;
            $data[$key]['epaperId'] =$row['epaperId'] ;
            $data[$key]['addTime'] =$row['addTime'] ;
            $imgarr1=[];
            foreach (unserialize($row['pictureUrls']) as $k => $m) {
                $imgarr1[$k] = tomedia($m);
            }
            $data[$key]['pictureUrls'] = $imgarr1;
        }
        $result['status'] = "0";
        $result['msg']    = "获取数据成功";
        $result['data']['total']=$total;
        $result['data']['currentPage']=$pindex;
        $result['data']['totalPage']= ceil(($total)/$psize);
        $result['data']['pageSize']=$psize;
        $result['data']['list'] = $data;
		$result['servertime'] = time();
        } else {
            $result['status'] = -1;
            $result['msg'] = "本校未添加班级信息";
        }
        echo json_encode($result);
        die();
}
if($operation=='Application'){
    $data['serverTime']=time();
    $m=pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_application) . " WHERE  school_id = '{$schoolid}' AND FIND_IN_SET('{$ckmac['bj_id']}',bjarray)");

    if($m) {
        foreach ($m as $k => $v) {
            $data['data'][] = [
                'downloadUrl' => $v['download_url'],
                'applicationName' => $v['application_name'],
                'applicationId' => $v['application_id'],
                'versionCode' => $v['version_code'],
                'versionName' => $v['version_name'],
                'applicationIcon' => tomedia($v['application_icon'])
            ];
        }
    }else{
        $data['data']=null;
	}
    $data['msg']='获取数据成功';
    $data['status']=0;
    echo json_encode($data);
    exit;
}
if($operation=='Exam'){
    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $start = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
    $end= mktime(23,59,59,$month,$day,$year);//当天结束时间戳

    $bj_exam=pdo_fetchall("SELECT c.sname, ed.`code`, tc1.tname,tc2.tname as tname1 ,ed.course_id,ed.id,e.exam_name,e.start_time,e.end_time FROM " .
        tablename($this->table_classcard_exam_detail).'as ed'
        .' LEFT JOIN '.tablename($this->table_classcard_exam).'as e ON  ed.exam_id=e.id '
        .' LEFT JOIN '.tablename($this->table_teachers).'as tc1 ON  tc1.id=ed.teacher_id '
        .' LEFT JOIN '.tablename($this->table_teachers).'as tc2 ON  tc2.id=ed.teacher_id1 '
        .' LEFT JOIN '.tablename($this->table_classify).'as c ON  c.sid=ed.course_id '
        ." WHERE  FIND_IN_SET('{$ckmac['bj_id']}',ed.bj_id)"
        ." AND  e.start_time BETWEEN {$start} AND {$end}");
    $data=[];
    foreach ($bj_exam as $k=>$v){
    	$data['data'][]=[
    		'examRoom'=>$v['code'],
            'examCourse'=>$v['course_id'],
            'examId'=>$v['id'],
			'examName'=>$v['exam_name'],
            'startTime'=>$v['start_time'],
            'endTime'=>$v['end_time'],
            'examCourse'=>$v['sname'],
			'invigilator'=>[$v['tname'],$v['tname1']]
		];
	}
    $data['msg']='获取数据成功';
    $data['status']=0;
    echo json_encode($data);
    exit;
}
if($operation=='Countdown'){
    $data['serverTime']=time();
    $m=pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_countdown) . " WHERE  schoolid = '{$schoolid}' AND FIND_IN_SET('{$ckmac['bj_id']}',bj_id)");
    $data['data']=[
        'project'=>$m['project'],
        'countdown'=> floor(intval($m['count_down']-time()) / (3600*24))+1
    ];
    $data['msg']='获取数据成功';
    $data['status']=0;
    echo json_encode($data);
    exit;
}
if($operation=='postactivity'){
   $rs= pdo_fetch('select id from '.tablename($this->table_classcard_activity_result).
		' WHERE activity_id=:activity_id and userid=:userid',array(':activity_id'=>$_GPC['activityId'],':userid'=>$_GPC['studentId']));
    if($rs){
        $data['data']=null;
        $data['msg']='请勿重复操作！';
        $data['status']=-1;
	}else{
        $data['userid']=$_GPC['studentId'];
        $data['schoolid']=$schoolid;
        $data['activity_id']=$_GPC['activityId'];
        $data['options']=$_GPC['options'];
        $data['weid']=$weid;
        $data['bj_id']=$ckmac['bj_id'];
        $data['createtime']=time();
        $rs= pdo_insert('wx_school_classcard_activity_result',$data);
        $data['data']=null;
        $data['msg']='上传成功';
        $data['status']=0;
        }
    $data['serverTime']=time();
    echo json_encode($data);
    exit;
}
if ($operation == 'rtc') {
    $rs= pdo_fetch('select id,tappId, tappKey,room_id from '.tablename($this->table_classcard_set).
        ' WHERE schoolid=:schoolid and weid=:weid',array(':schoolid'=>$_GPC['schoolid'],':weid'=>$weid));
          pdo_query('update  '.tablename($this->table_classcard_set).
          'SET room_id=room_id+1 WHERE schoolid=:schoolid and weid=:weid',
          array(':schoolid'=>$_GPC['schoolid'],':weid'=>$weid));
             $class = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$schoolid} And sid = {$ckmac['bj_id']} ");
             $students= pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " WHERE  schoolid = {$schoolid} And bj_id = {$ckmac['bj_id']} And id={$_GPC['studentId']}");
              $identifier=$rs['room_id']+1;
                         $datas=array(
                            'name'=>array('value'=>'您收到一个电子班牌视频通话','color'=>'#173177'),
                            'first'=>array('value'=>'','color'=>'#1587CD'),
                            'keyword1'=>array('value'=>$class['sname'],'color'=>'#1587CD'),
                            'keyword2'=>array('value'=>$students['s_name'],'color'=>'#2D6A90'),
                            'keyword3'=>array('value'=>date('Y-m-d H:i:s',time()),'color'=>'#1587CD'),//时间
                            'keyword4'=>array('value'=>'电子班牌视频通话','color'=>'#1587CD'),
                            'remark'=> array('value'=>'点击本消息直接进入视频通话','color'=>'#FF9E05')
                        );
	                     $data = json_encode($datas);
                         $url=$_W['siteroot'].'app/index.php?i='.$weid.'&c=entry&schoolid='.$schoolid.'&roomid='.$identifier.'&do=trcroom&m=fm_jiaoyu&wxref=mp.weixin.qq.com#wechat_redirect';
                         $openid=$_GPC['UnionID'];
                         $smsset = get_weidset($weid,'xxtongzhi');
                         $this->sendtempmsg($smsset['xxtongzhi'], $url, $data, '#FF0000', $openid);
                         $trtc=IA_ROOT . '/addons/fm_jiaoyu/inc/func/trtc/TLSSigAPIv2.php';
                       require $trtc;
                      
                       $SDKAppID=$rs['tappId'];
                 $api = new trtc\TLSSigAPIv2($SDKAppID, $rs['tappKey']);
                 $sig = $api->genSig($identifier);
                $result=[
               'status'=>0, 
               'msg'=>'发送成功', 
               'data'=>["roomId"=>$identifier, "SDKAppID"=>$SDKAppID, "UserSig"=>$sig,'identifier'=>$identifier ],
               'serverTime'=>time()
              ];
        echo json_encode($result);
        exit;
}
if ($operation == 'notice') {
	if(!empty($school)) {
		$banner = unserialize($ckmac['banner']);
		$result['status'] = 0;
		$result['msg'] = "获取数据成功";
		$result['data'] = array(
		                'getDeviceUrl'   => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=device&m=fm_jiaoyu',
		                'getClassUrl'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=class&m=fm_jiaoyu',
                        'getClassesUrl'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=classes&m=fm_jiaoyu',
		                'getStudentUrl'  => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=student&m=fm_jiaoyu',
						'postUrl3'       => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=check&m=fm_jiaoyu',
						'getTeacherUrl'  => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=teacher&m=fm_jiaoyu',
						'postMessageUrl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=postmessage&m=fm_jiaoyu',
						'getMessageUrl'  => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=getmessage&m=fm_jiaoyu',
						'postReadUrl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=postread&m=fm_jiaoyu',
						'getAliossTokenUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=token&m=fm_jiaoyu',
		                //'getLeaveUrl'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=getleave&m=fm_jiaoyu',
		'getAttendenceStatusUrl'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=attendence&m=fm_jiaoyu',
						'getPictureUrl'	 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=picture&m=fm_jiaoyu',
						'getTimetableUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=timetable&m=fm_jiaoyu',
						'getDutyUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=duty&m=fm_jiaoyu',
            'postActivityUrl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=postactivity&m=fm_jiaoyu',
			'getActivityUrl' => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=activity&m=fm_jiaoyu',
						'getNoticeUrl'	 =>	$_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=news&m=fm_jiaoyu',
						'getLogUrl'	 	 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=log&m=fm_jiaoyu',
						'getDayLogUrl'	 	 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=daylog&m=fm_jiaoyu',
						'getGoodStudentUrl'	 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=praise&m=fm_jiaoyu',
						'getCustomUrl'	 => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=custom&m=fm_jiaoyu',
						'commandUrl'     => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=command&m=fm_jiaoyu',
						'RTC'    => $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=rtc&m=fm_jiaoyu',
						'getCookbookUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=food&m=fm_jiaoyu',
            'getApplicationUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=Application&m=fm_jiaoyu',
            'getCountdownUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=Countdown&m=fm_jiaoyu',
            'getExamUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=Exam&m=fm_jiaoyu',
      'getHonourUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=Honour&m=fm_jiaoyu',
      'getEpaperUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=Epaper&m=fm_jiaoyu',
      'postTemperatureUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=postTemperature&m=fm_jiaoyu',
            'getTemperatureUrl'=> $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&do=checkcc&op=Temperature&m=fm_jiaoyu',
					);
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}
if($operation == 'postTemperature'){
	$data=[
		'macid'=>$_GPC['deviceId'],
        'cardid'=>$_GPC['cardCode'],
        'temperature'=>$_GPC['temperature'],
		'add_time'=>time(),
		'schoolid'=>$school['id'],
		'weid'=>$weid
	];

    $rs= pdo_insert('wx_school_classcard_temperature_log',$data);
    if($rs){
        $result['status'] = "0";
        $result['msg']    = "保存成功";
        $result['servertime'] = time();
        echo json_encode($result);
        die();
	}else{
        $result['status'] = -1;
        $result['msg'] = "保存失败";
        echo json_encode($result);
        exit;
	}

}
if($operation == 'Temperature'){
	$start_time=strtotime(date('Y-m-d'));
    $rs=pdo_fetchall("SELECT DISTINCT a.sid as classId, a.pname as name,a.sid as studentId,tid as teacherId,b.add_time as time,b.temperature FROM " .
		tablename($this->table_idcard).' a Left JOIN '.
        tablename($this->table_classcard_temperature_log).' b on  b.cardid=a.idcard '
		." WHERE pard=1 AND  a.weid = '{$weid}' And a.schoolid = {$school['id']} AND b.add_time >={$start_time}");
//    $rs2=pdo_fetchall(
//    	"select 1 as type , s_name as name,a.id as studentId,b.add_time as time ,b.temperature from ". tablename($this->table_students).' a '.
//		" LEFT JOIN ".tablename($this->table_classcard_temperature_log).' b'.
//		' ON a.numberid=b.cardid '. " WHERE a.weid = '{$weid}' And a.schoolid = {$school['id']} "
//	);
//    $rs1=pdo_fetchall(
//        "select 2 as type, tname as name,a.id as teacherId,b.add_time as time ,b.temperature  from ". tablename($this->table_teachers).' a '.
//		" LEFT JOIN ".tablename($this->table_classcard_temperature_log).' b'.
//        ' ON a.code=b.cardid '. " WHERE a.weid = '{$weid}' And a.schoolid = {$school['id']} "
//    );
//    $rs=array_merge($rs2,$rs1);
    $result['status'] = "0";
    $result['data']=$rs;
    $result['msg']    = "获取数据成功";
    $result['servertime'] = time();
    echo json_encode($result);
    die();
}
if ($operation == 'device') {
	//获取设备信息
	$classcardset=pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$class = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$school['id']} And sid = {$ckmac['bj_id']} ");
    $ckmac     = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_mac) . " WHERE macid = '{$macid}' And weid = '{$weid}' And schoolid = '{$schoolid} And bj_id = {$ckmac['bj_id']}' ");

    if(!empty($ckmac)) {
		$result['status'] = 0;
		$result['msg'] = "获取数据成功";
		if($ckmac['macset']) {
			$macset = unserialize($ckmac['macset']);
		}
        $bg=[];
		if($ckmac['bg']){
            $bg[]=tomedia($ckmac['bg']);
		}
        if($ckmac['bg1']){
            $bg[]=tomedia($ckmac['bg1']);
        }
		$result['data'] = array(
		                'deviceTag'    => $ckmac['name'],
		                'welcome'      => $macset['welcome'],
		                'schoolName'   => $school['title'],
		                'schoolLogo'   => tomedia($school['logo']),
		                'className'    => $class['sname'],
		                'classId'      => $class['sid'],
		                'passWord'     => $macset['password'],
						'accessKeyID'     =>$classcardset['accessKeyID'],
				        'accessKeySecret'     =>$classcardset['accessKeySecret'],
		                'background'    =>$bg,
		                'endpoint'     => $classcardset['endpoint'],
		                'bucket'       => $classcardset['bucket'],
					);
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'class') {
    if(!empty($ckmac)) {
      $class = pdo_fetch("SELECT tid, sid as classId ,sname as className FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'theclass' And schoolid = {$school['id']} And sid = {$ckmac['bj_id']} ");
        $teacher= pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id", array(':id' => $class['tid']));
        $students= pdo_fetchall("SELECT s_name FROM " . tablename($this->table_students) . " WHERE is_banzhang=1 AND  schoolid = {$school['id']} And bj_id = {$ckmac['bj_id']} ");
        if($class) {
			$result['status'] = 0;
			$result['msg'] = "获取班级数据成功";
			$classAdvert=pdo_fetch("SELECT classAdvert FROM " . tablename($this->table_classcard_kh) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} And bj_id = {$ckmac['bj_id']} ");
			$result['data']['classAdviser'] = $teacher['tname'];
			$sname=[];
			foreach ($students as $k=>$v){
                $sname[]=$v['s_name'];
                }
            $result['data']['className']=$class['className'];
			$result['data']['classMonitor'] =$sname;
			$result['data']['classAdvert'] = $classAdvert['classAdvert'];
			$result['servertime'] = time();
		} else {
			$result['status'] = -1;
			$result['msg'] = "本校未添加班级信息";
		}
		echo json_encode($result);
		die();
	}
}
if ($operation == 'classes') {
   
    if(!empty($ckmac)) {
      $class = pdo_fetchall("SELECT  sid as classId ,sname as className  FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and type = 'theclass'  ");
     
        if($class) {
		   $result['status'] = 0;
			$result['msg'] = "获取班级数据成功";
            $result['data']=$class;
	
		} else {
			$result['status'] = -1;
			$result['msg'] = "本校未添加班级信息";
		}
		echo json_encode($result);
		die();
	}
}
if ($operation == 'student') {
	if(!empty($ckmac)) {
		$students = pdo_fetchall("SELECT id as studentId, s_name as studentName,is_banzhang as classMonitor  FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}'  ORDER BY id DESC " );
		foreach($students as $key=>$row) {
			$family=pdo_fetchall("SELECT * FROM " . tablename($this->table_user) . " where  weid = :weid And  schoolid = :schoolid And sid = :sid ", array(':weid' => $weid, ':schoolid' => $schoolid,'sid'=>$row['studentId']));
			$relation=array();
			$brcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['studentId']}' and pard=1 ");
			$students[$key]['cardCode']=$brcard['idcard'];
			$students[$key]['faceUrl']=tomedia($brcard['spic'],false,true);
			foreach($family as $f=>$v) {
				if($v['pard']!='1') {
					$relation[$f]['relationship'] = '';
					$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['studentId']}' and pard='{$v['pard']}' ");
					$relation[$f]['UnionID'] =$v['openid'] ;
					$relation[$f]['cardCode'] =$fcard['idcard'] ;
					if($v['pard']=='2') {
						$relation[$f]['relationship'] = '母亲';
					} elseif($v['pard']=='3') {
						$relation[$f]['relationship'] = '父亲';
					} elseif($v['pard']=='4') {
						$relation[$f]['relationship'] = '爷爷';
					} elseif($v['pard']=='5') {
						$relation[$f]['relationship'] = '奶奶';
					} elseif($v['pard']=='6') {
						$relation[$f]['relationship'] = '外公';
					} elseif($v['pard']=='7') {
						$relation[$f]['relationship'] = '外婆';
					} elseif($v['pard']=='8') {
						$relation[$f]['relationship'] = '叔叔';
					} elseif($v['pard']=='9') {
						$relation[$f]['relationship'] = '阿姨';
					} elseif($v['pard']=='10') {
						$relation[$f]['relationship'] = '其他';
					}
					$relation[$f]['faceUrl'] =tomedia($fcard['spic'],false,true) ;
				}
			}
			$students[$key]['family']=$relation;
		}
		$result['status'] = 0;
		$result['data'] = $students;
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'teacher') {
	if(!empty($ckmac)) {
		$bj_t = pdo_fetchall("SELECT *  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 ORDER BY id DESC  ");
		$teachers=array();
		foreach($bj_t as $key=>$row) {
			$teachers[$key]=pdo_fetch("SELECT id as teacherId,tname as teacherName,info as teacherProfile FROM " . tablename($this->table_teachers) . " WHERE id = '{$row['tid']}' ");
			$tcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE tid = '{$row['tid']}' ");
			$teachers[$key]['cardCode']=$tcard['idcard'];
			$teachers[$key]['faceUrl']=tomedia($tcard['spic'],false,true);
			$course=pdo_fetch("SELECT  sid,sname  FROM " . tablename($this->table_classify) . " WHERE sid = '{$row['km_id']}' ");
			$teachers[$key]['courseName']=$course['sname'];
			$teachers[$key]['courseId']=$course['sid'];

		}
		$result['status'] = 0;
		$result['data'] = $teachers;
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'leave') {
	$starttime=strtotime(date('Y-m-d 00:00:00'));
	$endtime = strtotime(date('Y-m-d 23:59:59'));
	$condition .= " AND tid = 0 ";
	$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And isliuyan = 0 $condition ORDER BY createtime DESC, id DESC ");
	$data=array();
	foreach($list as $key => $row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row['sid']));
		$data[$key]['studentId'] = $student['s_name'];
		$data[$key]['studentName'] = $student['s_name'];
		$data[$key]['faceUrl'] = tomedia($brcard['spic']);
	}
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data'] = $data;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
}
if ($operation == 'attendence') {
	$starttime=strtotime(date('Y-m-d 00:00:00'));
	$endtime = strtotime(date('Y-m-d 23:59:59'));
	$condition .= " AND tid = 0 ";
	$condition .= " AND createtime >= '{$starttime}' AND createtime <= '{$endtime}'";
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And bj_id = :bj_id ", array(':weid' => $_W ['uniacid'], ':bj_id' => $ckmac['bj_id']));
	$data=array();
	foreach($list as $key => $row) {
		$leave =pdo_fetch("SELECT * FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And  schoolid = '{$schoolid}' And sid=:sid  And isliuyan = 0 $condition ",array(':sid'=>$row['id']));
		$data[$key]['studentId'] = $row['id'];
		$data[$key]['studentName'] = $row['s_name'];
		
		if(empty($leave)) {
			$kaoqin=pdo_fetch('SELECT leixing FROM ' . tablename($this->table_classcard_checklog). " WHERE bj_id = '{$ckmac['bj_id']}' And createtime >= '{$starttime}' And createtime <= '{$endtime}' and sid='{$row['id']}' And schoolid = '{$schoolid}' ");
			if(empty($kaoqin)) {
				$data[$key]['attendenceStatus'] = 2;
			} else {
				if($kaoqin['leixing']==3)
										$data[$key]['attendenceStatus'] = '1';
				if($kaoqin['leixing']==1)
										$data[$key]['attendenceStatus'] = '0';
			}
		} else {
			$data[$key]['attendenceStatus'] = '3';
		}
		$brcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['id']}' and pard=1 ");
		$data[$key]['faceUrl'] = tomedia($brcard['spic'],false,true);
	}
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data'] = $data;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
}
if ($operation == 'picture') {
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_media) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' AND ((type=1 && isfm=1) or type=2 )and bj_id1 = '{$ckmac['bj_id']}' ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$total=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_media) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' AND type in (1,2)  and bj_id1 = '{$ckmac['bj_id']}' ");
	$data=array();
	foreach($list as $key => $row) {
		if($row['isfm']==1 && $row['type']==1) {
			$students  = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND :schoolid = schoolid AND id = :id", array(':weid'=> $weid,':schoolid' => $schoolid,':id'=> $row['sid']));
			$pic['pictureName'] = $students['s_name'];
			$pic['pictureId'] = $row['id'];
			$pic['cover'] = tomedia($row['fmpicurl']);
			$picarr=pdo_fetchall("SELECT picurl FROM " . tablename($this->table_media) . " where schoolid = '{$schoolid}' And weid = '{$weid}' AND type= 1 And sid = '{$row['sid']}' ORDER BY id DESC");
			$imgarr=array();
			foreach($picarr as $key1=>$m) {
				if(!empty($m)) {
					$imgarr[$key1]=tomedia($m['picurl']);
				}
			}
			$pic['pictureUrls'] = $imgarr;
			$pic['addTime'] = $row['createtime'];
			array_push($data,$pic);
		} elseif(empty($row['isfm']) && $row['type']==2) {
			$pic['pictureName'] = '公共相册';
			$pic['pictureId'] = $row['id'];
			$pic['cover'] = tomedia($row['picurl']);
			$imgarr1[0]=tomedia($row['picurl']);
			$pic['pictureUrls'] = $imgarr1;
			$pic['addTime'] = $row['createtime'];
			array_push($data,$pic);
		}
	}
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data']['total']=$total;
	$result['data']['currentPage']=$pindex;
	$result['data']['totalPage']= ceil(($total)/$psize);
	$result['data']['pageSize']=$psize;
	$result['data']['list'] = $data;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
}
if ($operation == 'news') {
	$starttime=strtotime(date('Y-m-d 00:00:00'));
	$endtime = strtotime(date('Y-m-d 23:59:59'));
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_notice) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 and bj_id = '{$ckmac['bj_id']}' ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	$total=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_notice) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And type = 1 and bj_id = '{$ckmac['bj_id']}' ");
	$data=array();
	foreach($list as $key => $row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row['sid']));
		$data[$key]['title'] = $row['title'];
		$data[$key]['id'] = $row['id'];
		$data[$key]['info'] = $row['content'];//strip_tags(ihtml_entity_decode($row['content']));
		$data[$key]['publisher'] = $row['tname'];
		$data[$key]['addTime'] = $row['createtime'];
		$picarr=iunserializer($row['picarr']);
		$imgarr=array();
		foreach($picarr as $key1=>$m) {
			if(!empty($m)) {
				$imgarr[$key1]=tomedia($m);
			}
		}
		$data[$key]['pictureUrls'] = $imgarr;
	}
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data']['total']=$total;
	$result['data']['currentPage']=$pindex;
	$result['data']['totalPage']= ceil(($total)/$psize);
	$result['data']['pageSize']=$psize;
	$result['data']['list'] = $data;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
}
if ($operation == 'activity') {
    $time=time();
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_activity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and FIND_IN_SET('{$ckmac['bj_id']}',bjarray) ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_activity) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and FIND_IN_SET('{$ckmac['bj_id']}',bjarray) ");
	$data=array();
	foreach($list as $key => $row) {
		$data[$key]['id'] = $row['id'];
		$data[$key]['title'] = $row['title'];
		$data[$key]['info'] = $row['content'];//strip_tags(ihtml_entity_decode($row['content']));
		$data[$key]['publisher'] = '';
		$data[$key]['addTime'] = $row['createtime'];
		$data[$key]['position'] = '';
		$picarr=iunserializer($row['banner']);
		$imgarr=array();
		foreach($picarr as $key1=>$m) {
			if(!empty($m)) {
				$imgarr[$key1]=tomedia($m);
			}
		}
		$data[$key]['startTime'] = $row['starttime'];
		$data[$key]['endTime'] = $row['endtime'];
        $data[$key]['SignStartTime'] = $row['starttime1'];
        $data[$key]['SignEndTime'] = $row['endtime1'];
        $data[$key]['maxCount'] = $row['total_count'];
        $attr=unserialize($row['attr']);
        $i=1;
        if(is_array($attr)){
            foreach($attr as $v){
                $data[$key]['options'][$i++]=$v ;
            }
		}
		$data[$key]['type'] =  $row['cate'];
        $data[$key]['position'] =  $row['addr'];
	}
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data']['total']=$total;
	$result['data']['currentPage']=$pindex;
	$result['data']['totalPage']= ceil(($total)/$psize);
	$result['data']['pageSize']=$psize;
	$result['data']['list'] = $data;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
}
if ($operation == 'timetable') {
	$data = array();
	$time = time();
	$timetable=pdo_fetch("SELECT * FROM " . tablename($this->table_timetable) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and bj_id = :bj_id and begintime<='{$time}' and endtime>='{$time}' ", array(':bj_id' => $ckmac['bj_id']));
	$monarr = iunserializer($timetable['monday']);
	$tusarr = iunserializer($timetable['tuesday']);
	$wedarr = iunserializer($timetable['wednesday']);
	$thuarr = iunserializer($timetable['thursday']);
	$friarr = iunserializer($timetable['friday']);
	$satarr = iunserializer($timetable['saturday']);
	$sunarr = iunserializer($timetable['sunday']);
	$data[0]['week']='1';
	$data[1]['week']='2';
	$data[2]['week']='3';
	$data[3]['week']='4';
	$data[4]['week']='5';
	$data[5]['week']='6';
	$data[6]['week']='7';
	$monday=array();
	$tuesday=array();
	$wednesday=array();
	$thursday=array();
	$friday=array();
	$saturday=array();
	$sunday=array();
	for ($i=1;$i<=12;$i++) {
		$sd='mon_'.$i.'_sd';
		$km='mon_'.$i.'_km';
		$week=$monarr;
		if(!empty($week[$sd]) || !empty($week[$km])) {
			$kmname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject', ':sid' => $week[$km]));
			$sdname = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'timeframe', ':sid' => $week[$sd]));
			$bj_t = pdo_fetch("SELECT tid  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 and km_id='{$week[$km]}' ORDER BY id DESC  ");
			$tname=pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$bj_t['tid']}' ");
			$k['id']= $week[$km].'1'.$i;
			$k['courseId']= $week[$km];
			$k['courseName']=$kmname['sname'];
			$k['teacherId']=$bj_t['tid'];
			$k['teacherName']=$tname['tname'];
			if(!empty($sdname)) {
				$k['startTime']=date('H:i',$sdname['sd_start']);
				$k['endTime']=date('H:i',$sdname['sd_end']);
			} else {
				$k['startTime']='';
				$k['endTime']='';
			}
			array_push($monday,$k);
		}
	}
	for ($i=1;$i<=12;$i++) {
		$sd='tus_'.$i.'_sd';
		$km='tus_'.$i.'_km';
		$week=$tusarr;
		if(!empty($week[$sd]) || !empty($week[$km])) {
			$kmname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject', ':sid' => $week[$km]));
			$sdname = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'timeframe', ':sid' => $week[$sd]));
			$bj_t = pdo_fetch("SELECT tid  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 and km_id='{$week[$km]}' ORDER BY id DESC  ");
			$tname=pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$bj_t['tid']}' ");
			$k['id']= $week[$km].'2'.$i;
			$k['courseId']= $week[$km];
			$k['courseName']=$kmname['sname'];
			$k['teacherId']=$bj_t['tid'];
			$k['teacherName']=$tname['tname'];
			if(!empty($sdname)) {
				$k['startTime']=date('H:i',$sdname['sd_start']);
				$k['endTime']=date('H:i',$sdname['sd_end']);
			} else {
				$k['startTime']='';
				$k['endTime']='';
			}
			array_push($tuesday,$k);
		}
	}
	for ($i=1;$i<=12;$i++) {
		$sd='wed_'.$i.'_sd';
		$km='wed_'.$i.'_km';
		$week=$wedarr;
		if(!empty($week[$sd]) || !empty($week[$km])) {
			$kmname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject', ':sid' => $week[$km]));
			$sdname = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'timeframe', ':sid' => $week[$sd]));
			$bj_t = pdo_fetch("SELECT tid  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 and km_id='{$week[$km]}' ORDER BY id DESC  ");
			$tname=pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$bj_t['tid']}' ");
			$k['id']= $week[$km].'3'.$i;
			$k['courseId']= $week[$km];
			$k['courseName']=$kmname['sname'];
			$k['teacherId']=$bj_t['tid'];
			$k['teacherName']=$tname['tname'];
			if(!empty($sdname)) {
				$k['startTime']=date('H:i',$sdname['sd_start']);
				$k['endTime']=date('H:i',$sdname['sd_end']);
			} else {
				$k['startTime']='';
				$k['endTime']='';
			}
			array_push($wednesday,$k);
		}
	}
	for ($i=1;$i<=12;$i++) {
		$sd='thu_'.$i.'_sd';
		$km='thu_'.$i.'_km';
		$week=$thuarr;
		if(!empty($week[$sd]) || !empty($week[$km])) {
			$kmname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject', ':sid' => $week[$km]));
			$sdname = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'timeframe', ':sid' => $week[$sd]));
			$bj_t = pdo_fetch("SELECT tid  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 and km_id='{$week[$km]}' ORDER BY id DESC  ");
			$tname=pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$bj_t['tid']}' ");
			$k['id']= $week[$km].'4'.$i;
			$k['courseId']= $week[$km];
			$k['courseName']=$kmname['sname'];
			$k['teacherId']=$bj_t['tid'];
			$k['teacherName']=$tname['tname'];
			if(!empty($sdname)) {
				$k['startTime']=date('H:i',$sdname['sd_start']);
				$k['endTime']=date('H:i',$sdname['sd_end']);
			} else {
				$k['startTime']='';
				$k['endTime']='';
			}
			array_push($thursday,$k);
		}
	}
	for ($i=1;$i<=12;$i++) {
		$sd='fri_'.$i.'_sd';
		$km='fri_'.$i.'_km';
		$week=$friarr;
		if(!empty($week[$sd]) || !empty($week[$km])) {
			$kmname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject', ':sid' => $week[$km]));
			$sdname = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'timeframe', ':sid' => $week[$sd]));
			$bj_t = pdo_fetch("SELECT tid  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 and km_id='{$week[$km]}' ORDER BY id DESC  ");
			$tname=pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$bj_t['tid']}' ");
			$k['id']= $week[$km].'5'.$i;
			$k['courseId']= $week[$km];
			$k['courseName']=$kmname['sname'];
			$k['teacherId']=$bj_t['tid'];
			$k['teacherName']=$tname['tname'];
			if(!empty($sdname)) {
				$k['startTime']=date('H:i',$sdname['sd_start']);
				$k['endTime']=date('H:i',$sdname['sd_end']);
			} else {
				$k['startTime']='';
				$k['endTime']='';
			}
			array_push($friday,$k);
		}
	}
	for ($i=1;$i<=12;$i++) {
		$sd='sat_'.$i.'_sd';
		$km='sat_'.$i.'_km';
		$week=$satarr;
		if(!empty($week[$sd]) || !empty($week[$km])) {
			$kmname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject', ':sid' => $week[$km]));
			$sdname = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'timeframe', ':sid' => $week[$sd]));
			$bj_t = pdo_fetch("SELECT tid  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 and km_id='{$week[$km]}' ORDER BY id DESC  ");
			$tname=pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$bj_t['tid']}' ");
			$k['id']= $week[$km].'6'.$i;
			$k['courseId']= $week[$km];
			$k['courseName']=$kmname['sname'];
			$k['teacherId']=$bj_t['tid'];
			$k['teacherName']=$tname['tname'];
			if(!empty($sdname)) {
				$k['startTime']=date('H:i',$sdname['sd_start']);
				$k['endTime']=date('H:i',$sdname['sd_end']);
			} else {
				$k['startTime']='';
				$k['endTime']='';
			}
			array_push($saturday,$k);
		}
	}
	for ($i=1;$i<=12;$i++) {
		$sd='sun_'.$i.'_sd';
		$km='sun_'.$i.'_km';
		$week=$sunarr;
		if(!empty($week[$sd]) || !empty($week[$km])) {
			$kmname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'subject', ':sid' => $week[$km]));
			$sdname = pdo_fetch("SELECT sd_start,sd_end FROM " . tablename($this->table_classify) . " WHERE weid = :weid And schoolid = :schoolid And type = :type and sid=:sid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':type' => 'timeframe', ':sid' => $week[$sd]));
			$bj_t = pdo_fetch("SELECT tid  FROM " . tablename($this->table_class) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' And bj_id = '{$ckmac['bj_id']}' and type=1 and km_id='{$week[$km]}' ORDER BY id DESC  ");
			$tname=pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = '{$bj_t['tid']}' ");
			$k['id']= $week[$km].'7'.$i;
			$k['courseId']= $week[$km];
			$k['courseName']=$kmname['sname'];
			$k['teacherId']=$bj_t['tid'];
			$k['teacherName']=$tname['tname'];
			if(!empty($sdname)) {
				$k['startTime']=date('H:i',$sdname['sd_start']);
				$k['endTime']=date('H:i',$sdname['sd_end']);
			} else {
				$k['startTime']='';
				$k['endTime']='';
			}
			array_push($sunday,$k);
		}
	}
	$data[0]['courseList']=$monday;
	$data[1]['courseList']=$tuesday;
	$data[2]['courseList']=$wednesday;
	$data[3]['courseList']=$thursday;
	$data[4]['courseList']=$friday;
	$data[5]['courseList']=$saturday;
	$data[6]['courseList']=$sunday;
	$result['status'] = 0;
	$result['msg']    = "获取数据成功";
	$result['data']   = $data;
	echo json_encode($result);
	exit;
}
if ($operation == 'duty') {
	$data = array();
	$item     = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_duty) . " WHERE schoolid = :schoolid And bj_id = :bj_id ", array(':schoolid' => $schoolid, ':bj_id' => $ckmac['bj_id']));
	$monarr = iunserializer($item['monday']);
	$tusarr = iunserializer($item['tuesday']);
	$wedarr = iunserializer($item['wednesday']);
	$thuarr = iunserializer($item['thursday']);
	$friarr = iunserializer($item['friday']);
	$satarr = iunserializer($item['saturday']);
	$sunarr = iunserializer($item['sunday']);
	$data[0]['week']='1';
	$data[1]['week']='2';
	$data[2]['week']='3';
	$data[3]['week']='4';
	$data[4]['week']='5';
	$data[5]['week']='6';
	$data[6]['week']='7';
	$monday=array();
	$tuesday=array();
	$wednesday=array();
	$thursday=array();
	$friday=array();
	$saturday=array();
	$sunday=array();
	foreach($monarr as $key=>$row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row));
		$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row}' and pard=1 ");
		$k['studentId']= $row;
		$k['faceUrl']=tomedia($fcard['spic'],false,true);
		$k['studentName']=$student['s_name'];
		array_push($monday,$k);
	}
	foreach($tusarr as $key=>$row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row));
		$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row}' and pard=1 ");
		$k['studentId']= $row;
		$k['faceUrl']=tomedia($fcard['spic'],false,true);
		$k['studentName']=$student['s_name'];
		array_push($tuesday,$k);
	}
	foreach($wedarr as $key=>$row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row));
		$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row}' and pard=1 ");
		$k['studentId']= $row;
		$k['faceUrl']=tomedia($fcard['spic'],false,true);
		$k['studentName']=$student['s_name'];
		array_push($wednesday,$k);
	}
	foreach($thuarr as $key=>$row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row));
		$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row}' and pard=1 ");
		$k['studentId']= $row;
		$k['faceUrl']=tomedia($fcard['spic'],false,true);
		$k['studentName']=$student['s_name'];
		array_push($thursday,$k);
	}
	foreach($friarr as $key=>$row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row));
		$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row}' and pard=1 ");
		$k['studentId']= $row;
		$k['faceUrl']=tomedia($fcard['spic'],false,true);
		$k['studentName']=$student['s_name'];
		array_push($friday,$k);
	}
	foreach($satarr as $key=>$row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row));
		$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row}' and pard=1 ");
		$k['studentId']= $row;
		$k['faceUrl']=tomedia($fcard['spic'],false,true);
		$k['studentName']=$student['s_name'];
		array_push($saturday,$k);
	}
	foreach($sunarr as $key=>$row) {
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row));
		$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row}' and pard=1 ");
		$k['studentId']= $row;
		$k['faceUrl']=tomedia($fcard['spic'],false,true);
		$k['studentName']=$student['s_name'];
		array_push($sunday,$k);
	}
	$data[0]['studentList']=$monday;
	$data[1]['studentList']=$tuesday;
	$data[2]['studentList']=$wednesday;
	$data[3]['studentList']=$thursday;
	$data[4]['studentList']=$friday;
	$data[5]['studentList']=$saturday;
	$data[6]['studentList']=$sunday;
	$result['status'] = 0;
	$result['msg']    = "获取数据成功";
	$result['data']   = $data;
	echo json_encode($result);
	exit;
}
if ($operation == 'praise') {
	$praise=pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_praise) . " WHERE schoolid = :schoolid And bj_id = :bj_id LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':schoolid' => $schoolid, ':bj_id' => $ckmac['bj_id']));
	$total=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_praise) . " WHERE schoolid = :schoolid And bj_id = :bj_id ",array(':schoolid' => $schoolid, ':bj_id' => $ckmac['bj_id']));
	$student=array();
	$i=0;
	foreach($praise as $m=>$p) {
		$list=iunserializer($p['praise']);
		foreach($list as $key=>$row) {
			$stu=pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row['id']));
			$type=pdo_fetch("SELECT title FROM " . tablename($this->table_classcard_praise_type) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row['type']));
			$comment=pdo_fetch("SELECT title FROM " . tablename($this->table_classcard_praise_comment) . " where weid = :weid And id = :id ", array(':weid' => $_W ['uniacid'], ':id' => $row['py']));
			$fcard = pdo_fetch("SELECT  idcard,spic  FROM " . tablename($this->table_idcard) . " WHERE sid = '{$row['id']}' and pard=1 ");
			$student[$i]['id']=$p['id'].$row['id'];
			$student[$i]['studentId']=$row['id'];
			$student[$i]['studentName']=$stu['s_name'];
			$student[$i]['faceUrl']=tomedia($fcard['spic'],false,true);
			$student[$i]['goodStudentType']=$type['title'];
			$student[$i]['goodStudentInfo']=$comment['title'];
			$student[$i]['addTime']=$p['createtime'];
			$i++;
		}
	}
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data']['total']=$total;
	$result['data']['currentPage']=$pindex;
	$result['data']['totalPage']= ceil(($total)/$psize);
	$result['data']['pageSize']=$psize;
	$result['data']['list'] = $student;
	$result['servertime'] = time();
	echo json_encode($result);
	exit;
}
if ($operation == 'command') {
	if(!empty($ckmac)) {
		$order = pdo_fetch("SELECT * FROM " . tablename($this->table_online) . " WHERE :macid = macid And result = 2", array(':macid' => trim($ckmac['id'])));
		if($order) {
			$result['status']=0;
			$result['msg']='请求成功';
			$result['data']=$order['commond'];
		} else {
			$result['status']=0;
			$result['msg']='请求成功';
			$result['data']=null;
		}
		$result['servertime']=time();
	}
	if($order) {
		pdo_update($this->table_online, array('result'=>1), array('id' => $order['id']));
	}
	echo json_encode($result);
	exit;
}
if ($operation == 'custom') {
	//获取自定义图片
	$classcardset=pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
	if(!empty($ckmac)) {
		$result['status'] = 0;
		$result['msg'] = "获取数据成功";
		$img=unserialize($classcardset['img']);
		foreach($img as $key=>$row) {
			$img[$key]=tomedia($row);
		}
		$result['data'] = $img;
		$result['servertime'] = time();
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'log') {
	$week=get_weeks1();
	$datas=array();
	foreach($week as $key=>$row) {
		$start=strtotime($row);
		$end=$start+86399;
		$k['week']=get_week_num($start);
		$k['late']=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_checklog). " WHERE bj_id = '{$ckmac['bj_id']}' And createtime >= '{$start}' And createtime <= '{$end}' and leixing=3 And schoolid = '{$schoolid}' ");
		//$k['leaveEarly']=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_checklog). " WHERE bj_id = '{$ckmac['bj_id']}' And createtime >= '{$start}' And createtime <= '{$end}' and leixing=4 And schoolid = '{$schoolid}' ");
		$t_num=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_students). " WHERE bj_id = '{$ckmac['bj_id']}' And schoolid = '{$schoolid}' ");
		$k_num=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_checklog). " WHERE bj_id = '{$ckmac['bj_id']}' And createtime >= '{$start}' And createtime <= '{$end}' and leixing=1 And schoolid = '{$schoolid}' ");
		$k['attendence']=$k_num;
		$k['absent']=$t_num-$k_num-$k['late'];
		array_push($datas,$k);
	}
	$result['status'] = 0;
	$result['msg']    = "数据获取成功";
	$result['data']   = $datas;
	echo json_encode($result);
	exit;
}
if ($operation == 'daylog') {
	$datas=array();
	$start=strtotime(date("Y-m-d 00:00:00"));
	$end=$start+86399;
	$datas['late']=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_checklog). " WHERE bj_id = '{$ckmac['bj_id']}' And createtime >= '{$start}' And createtime <= '{$end}' and leixing=3 And schoolid = '{$schoolid}' ");
	$t_num=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_students). " WHERE bj_id = '{$ckmac['bj_id']}' And schoolid = '{$schoolid}' ");
	$k_num=pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_checklog). " WHERE bj_id = '{$ckmac['bj_id']}' And createtime >= '{$start}' And createtime <= '{$end}' and leixing=1 And schoolid = '{$schoolid}' ");
	$datas['attendence']=$k_num;
	$datas['absent']=$t_num-$k_num-$datas['late'];
	$result['status'] = 0;
	$result['msg']    = "数据获取成功";
	$result['data']   = $datas;
	echo json_encode($result);
	exit;
}
if ($operation == 'getdate') {
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['data'] = array(
				'da_start' => array(1,2,3,4,5,6,7)	
			);
	echo json_encode($result);
	exit;
}
if ($operation == 'getmessage') {
	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_liuyan) . " where schoolid = :schoolid AND bj_id = :bj_id and isread=1 ORDER BY createtime ASC ", array(':schoolid' => $schoolid, ':bj_id' => $ckmac['bj_id']));
	$data=array();
	foreach($list as $key=>$row) {
		if($row['leaveid']!=$row['userid']) {
			$message['messageId']=$row['id'];
			$message['studentId']=$row['leaveid'];
			$message['time']=$row['createtime'];
			$message['UnionID']=$row['userid'];
			$user=pdo_fetch("SELECT pard FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $row['userid']));
			if($user['pard']=='2') {
				$relation= '母亲';
			} elseif($user['pard']=='3') {
				$relation= '父亲';
			} elseif($user['pard']=='4') {
				$relation = '爷爷';
			} elseif($user['pard']=='5') {
				$relation= '奶奶';
			} elseif($user['pard']=='6') {
				$relation= '外公';
			} elseif($user['pard']=='7') {
				$relation= '外婆';
			} elseif($user['pard']=='8') {
				$relation= '叔叔';
			} elseif($user['pard']=='9') {
				$relation= '阿姨';
			} elseif($user['pard']=='10') {
				$relation= '其他';
			}
			$message['relationship']=$relation;
			if(!empty($row['conet'])) {
				$message['type']='word';
				$message['info']= $row['conet'];
			} elseif(!empty($row['audio'])) {
				$message['type']='voice';
				$audios = iunserializer($row['audio']);
				$message['info']= tomedia($audios['audio'][0]);
				$message['seconds']= $audios['audioTime'][0];
			}
			array_push($data,$message);
		}
	}
	$result['status'] = "0";
	$result['msg']    = "获取数据成功";
	$result['servertime'] = time();
	$result['data'] = $data;
	echo json_encode($result);
	exit;
}
if ($operation == 'postmessage') {
	$insertdata=array(
				'weid' =>  $weid,
				'schoolid' => $schoolid,
				'bj_id' => $ckmac['bj_id'],
				'leaveid' =>  $_GPC['studentId'],
				'userid' => $_GPC['studentId'],
				'touserid' => $_GPC['UnionID'],
				'isliuyan'=>2,
				'createtime' => time()
			);
	if($_GPC['type']=='word') {
		$insertdata['conet']=urldecode($_GPC['info']);
	} elseif($_GPC['type']=='voice') {
		$mp3[0]= $_GPC['info'];
		$mp3time[0]=$_GPC['seconds'];
		$audios = array(
							'audio' =>  $mp3,
							'audioTime' => $mp3time,
						);
		$insertdata['audio'] = iserializer($audios);
	}
	$l_rs=pdo_insert($this->table_liuyan, $insertdata);
	$result['status'] = "0";
	$result['msg']    = "上传数据成功";
	$result['servertime'] = time();
	$data['messageId']=!empty($l_rs)?pdo_insertid():"";
	$result['data'] = $data;
	echo json_encode($result);
	exit;
}
if ($operation == 'postread') {
	$lastid=$_GPC['messageId'];
	pdo_query("UPDATE ".tablename($this->table_liuyan)." SET isread = :new WHERE id <= :id and isread =1 and bj_id =:bj_id and leaveid=:leaveid and isliuyan=:isliuyan and userid !=:leaveid  ", array('new'=>2,'id' =>  $lastid,'bj_id'=>$ckmac['bj_id'],'leaveid'=>$_GPC['studentId'],'isliuyan'=>2));
	$result['status'] = "0";
	$result['msg']    = "上传数据成功";
	$result['servertime'] = time();
	$result['data'] ='null';
	echo json_encode($result);
	exit;
}
if ($operation == 'check') {
	//刷卡操作
	$starttime=strtotime(date('Y-m-d 00:00:00'));
	$endtime = strtotime(date('Y-m-d 23:59:59'));
	$fstype   = false;
	$signTime = $_GPC['time'];
	if(empty($signTime)) {
		$signTime = time();
	}
	$ckuser = pdo_fetch("SELECT * FROM " . tablename($this->table_idcard) . " WHERE idcard = '{$_GPC['cardCode']}' And weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$Student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id= '{$ckuser['sid']}' ");
	$classcardset=pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$start=strtotime($classcardset['starttime']);
	$end=strtotime($classcardset['endtime']);
	$checkthisdata = pdo_fetch("SELECT id FROM " . tablename($this->table_classcard_checklog) . " WHERE cardid = '{$_GPC['cardCode']}' And createtime = '{$signTime}' And schoolid = '{$schoolid}' ");
	$pictureUrls=$_GPC['pictureUrls'];
	if(!empty($ckuser)) {
		$times = time();
		if(!empty($pictureUrls)) {
			$pic = urldecode($pictureUrls[0]);
			$pic2 = urldecode($pictureUrls[1]);
		}
		$nowtime = date('H:i',$signTime);
		include 'checktime.php';
		$leixing=1;
		$type='正常';
		$todaychecklog = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename($this->table_classcard_checklog). " WHERE cardid = '{$_GPC['cardCode']}' And createtime >= '{$starttime}' And createtime <= '{$endtime}' And schoolid = '{$schoolid}' ");
		if($todaychecklog>=1) {
			$result['status'] = -1;
			$result['msg']    =$Student['s_name']. "请勿重复考勤";
			echo json_encode($result);
			exit;
			/*$leixing=1;
			$type='正常';
			if($signTime>$start && $signTime<$end){
					$leixing=4;
					$type='早退';
					}*/
			/*if($signTime<$start){
					$leixing=5;
					$type='异常';
					}	*/
		} else {
			if($signTime>$start) {
				$leixing=3;
				$type='迟到';
			}
		}
		$data = array(
			'weid'        => $weid,
			'schoolid'    => $schoolid,
			'macid'       => $ckmac['id'],
			'cardid'      => $_GPC['cardCode'],
			'sid'         => $ckuser['sid'],
			'bj_id'       => $ckmac['bj_id'],
			'pic'         => $pic,
			'pic2'        => $pic2,
			'type'        => $type,
			'leixing'     => $leixing,
			'pard'        => $ckuser['pard'],
			'createtime'  => $signTime,
		);
		if($todaychecklog>1) {
		} else {
			pdo_insert($this->table_classcard_checklog, $data);
		}
		$fstype = true;
	} else {
		$fstype = false;
		$result['msg'] = "未查询到本卡绑定情况";
	}
	if ($fstype == true) {
		$result['status'] = 0;
		$result['msg']    = $Student['s_name']. "刷卡成功";
		echo json_encode($result);
		exit;
	} else {
		$result['status'] = -1;
		//$result['msg'] = "失败";
		echo json_encode($result);
		exit;
	}
}
if ($operation == 'getdevremote') {
	$time = $_GPC['signtime'];
	$pid=$ckmac['id'];
	$list = pdo_fetchall("SELECT deviceId,passType,passDeviceId,cameras FROM " . tablename('wx_school_checkmac_remote') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and pid='{$pid}' ORDER BY id DESC");
	foreach($list as $key => $row) {
		$list[$key]['cameras'] = unserialize($row['cameras']);
	}
	if(!empty($list)) {
		$result['status'] = "0";
		$result['msg']    = "获取数据成功";
		$result['data']   = $list;
	} else {
		$result['status'] = "1";
		$result['msg']    = "空数据";
	}
	echo json_encode($result);
	exit;
}
if ($operation == 'food') {
	$data = array();
	$time = time();
	$food=pdo_fetch("SELECT * FROM " . tablename($this->table_cook) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and begintime<='{$time}' and endtime>='{$time}' ");
	$monarr = iunserializer($food['monday']);
	$tusarr = iunserializer($food['tuesday']);
	$wedarr = iunserializer($food['wednesday']);
	$thuarr = iunserializer($food['thursday']);
	$friarr = iunserializer($food['friday']);
	$satarr = iunserializer($food['saturday']);
	$sunarr = iunserializer($food['sunday']);
	$data[0]['week']='1';
	$data[1]['week']='2';
	$data[2]['week']='3';
	$data[3]['week']='4';
	$data[4]['week']='5';
	$data[5]['week']='6';
	$data[6]['week']='7';
	$monday=array();
	$tuesday=array();
	$wednesday=array();
	$thursday=array();
	$friday=array();
	$saturday=array();
	$sunday=array();
	$monday[0]=array('foodId'=>'','foodName'=>$monarr['mon_zc'],'foodUrl'=>tomedia($monarr['mon_zc_pic']));
	$monday[1]=array('foodId'=>'','foodName'=>$monarr['mon_zjc'],'foodUrl'=>tomedia($monarr['mon_zjc_pic']));
	$monday[2]=array('foodId'=>'','foodName'=>$monarr['mon_wc'],'foodUrl'=>tomedia($monarr['mon_wc_pic']));
	$monday[3]=array('foodId'=>'','foodName'=>$monarr['mon_wjc'],'foodUrl'=>tomedia($monarr['mon_wjc_pic']));
	$monday[4]=array('foodId'=>'','foodName'=>$monarr['mon_wwc'],'foodUrl'=>tomedia($monarr['mon_wwc_pic']));
	$tuesday[0]=array('foodId'=>'','foodName'=>$tusarr['tus_zc'],'foodUrl'=>tomedia($tusarr['tus_zc_pic']));
	$tuesday[1]=array('foodId'=>'','foodName'=>$tusarr['tus_zjc'],'foodUrl'=>tomedia($tusarr['tus_zjc_pic']));
	$tuesday[2]=array('foodId'=>'','foodName'=>$tusarr['tus_wc'],'foodUrl'=>tomedia($tusarr['tus_wc_pic']));
	$tuesday[3]=array('foodId'=>'','foodName'=>$tusarr['tus_wjc'],'foodUrl'=>tomedia($tusarr['tus_wjc_pic']));
	$tusday[4]=array('foodId'=>'','foodName'=>$tusarr['tus_wwc'],'foodUrl'=>tomedia($tusarr['tus_wwc_pic']));
	$wednesday[0]=array('foodId'=>'','foodName'=>$wedarr['wed_zc'],'foodUrl'=>tomedia($wedarr['wed_zc_pic']));
	$wednesday[1]=array('foodId'=>'','foodName'=>$wedarr['wed_zjc'],'foodUrl'=>tomedia($wedarr['wed_zjc_pic']));
	$wednesday[2]=array('foodId'=>'','foodName'=>$wedarr['wed_wc'],'foodUrl'=>tomedia($wedarr['wed_wc_pic']));
	$wednesday[3]=array('foodId'=>'','foodName'=>$wedarr['wed_wjc'],'foodUrl'=>tomedia($wedarr['wed_wjc_pic']));
	$wednesday[4]=array('foodId'=>'','foodName'=>$wedarr['wed_wwc'],'foodUrl'=>tomedia($wedarr['wed_wwc_pic']));
	$thursday[0]=array('foodId'=>'','foodName'=>$thuarr['thu_zc'],'foodUrl'=>tomedia($thuarr['thu_zc_pic']));
	$thursday[1]=array('foodId'=>'','foodName'=>$thuarr['thu_zjc'],'foodUrl'=>tomedia($thuarr['thu_zjc_pic']));
	$thursday[2]=array('foodId'=>'','foodName'=>$thuarr['thu_wc'],'foodUrl'=>tomedia($thuarr['thu_wc_pic']));
	$thursday[3]=array('foodId'=>'','foodName'=>$thuarr['thu_wjc'],'foodUrl'=>tomedia($thuarr['thu_wjc_pic']));
	$thursday[4]=array('foodId'=>'','foodName'=>$thuarr['thu_wwc'],'foodUrl'=>tomedia($thuarr['thu_wwc_pic']));
	$friday[0]=array('foodId'=>'','foodName'=>$friarr['fri_zc'],'foodUrl'=>tomedia($friarr['fri_zc_pic']));
	$friday[1]=array('foodId'=>'','foodName'=>$friarr['fri_zjc'],'foodUrl'=>tomedia($friarr['fri_zjc_pic']));
	$friday[2]=array('foodId'=>'','foodName'=>$friarr['fri_wc'],'foodUrl'=>tomedia($friarr['fri_wc_pic']));
	$friday[3]=array('foodId'=>'','foodName'=>$friarr['fri_wjc'],'foodUrl'=>tomedia($friarr['fri_wjc_pic']));
	$friday[4]=array('foodId'=>'','foodName'=>$friarr['fri_wwc'],'foodUrl'=>tomedia($friarr['fri_wwc_pic']));
	$saturday[0]=array('foodId'=>'','foodName'=>$satarr['sat_zc'],'foodUrl'=>tomedia($satarr['sat_zc_pic']));
	$saturday[1]=array('foodId'=>'','foodName'=>$satarr['sat_zjc'],'foodUrl'=>tomedia($satarr['sat_zjc_pic']));
	$saturday[2]=array('foodId'=>'','foodName'=>$satarr['sat_wc'],'foodUrl'=>tomedia($satarr['sat_wc_pic']));
	$saturday[3]=array('foodId'=>'','foodName'=>$satarr['sat_wjc'],'foodUrl'=>tomedia($satarr['sat_wjc_pic']));
	$saturday[4]=array('foodId'=>'','foodName'=>$satarr['sat_wwc'],'foodUrl'=>tomedia($satarr['sat_wwc_pic']));
	$sunday[0]=array('foodId'=>'','foodName'=>$sunarr['sun_zc'],'foodUrl'=>tomedia($sunarr['sun_zc_pic']));
	$sunday[1]=array('foodId'=>'','foodName'=>$sunarr['sun_zjc'],'foodUrl'=>tomedia($sunarr['sun_zjc_pic']));
	$sunday[2]=array('foodId'=>'','foodName'=>$sunarr['sun_wc'],'foodUrl'=>tomedia($sunarr['sun_wc_pic']));
	$sunday[3]=array('foodId'=>'','foodName'=>$sunarr['sun_wjc'],'foodUrl'=>tomedia($sunarr['sun_wjc_pic']));
	$sunday[4]=array('foodId'=>'','foodName'=>$sunarr['sun_wwc'],'foodUrl'=>tomedia($sunarr['sun_wwc_pic']));
	$data[0]['cookbookList']=$monday;
	$data[1]['cookbookList']=$tuesday;
	$data[2]['cookbookList']=$wednesday;
	$data[3]['cookbookList']=$thursday;
	$data[4]['cookbookList']=$friday;
	$data[5]['cookbookList']=$saturday;
	$data[6]['cookbookList']=$sunday;
	$result['status'] = 0;
	$result['msg']    = "获取数据成功";
	$result['data']   = $data;
	echo json_encode($result);
	exit;
}
if ($operation == 'token') {
	$classcardset=pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_set) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' ");
	$accessKeyID = $classcardset['accessKeyID'];
	$accessKeySecret = $classcardset['accessKeySecret'];
	$roleArn = $classcardset['roleArn'];
	$tokenExpire = '900';
	$policy = read_file(MODULE_ROOT.'/inc/mac/policy/all_policy.txt');
	$iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $accessKeyID, $accessKeySecret);
	$client = new DefaultAcsClient($iClientProfile);
	$request = new Sts\AssumeRoleRequest();
	$request->setRoleSessionName("client_name");
	$request->setRoleArn($roleArn);
	$request->setPolicy($policy);
	$request->setDurationSeconds($tokenExpire);
	$response = $client->doAction($request);
	$rows = array();
	$body = $response->getBody();
	$content = json_decode($body);
	if ($response->getStatus() == 200) {
		$result['StatusCode'] = 200;
		$result['AccessKeyId'] = $content->Credentials->AccessKeyId;
		$result['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
		$result['Expiration'] = $content->Credentials->Expiration;
		$result['SecurityToken'] = $content->Credentials->SecurityToken;
	} else {
		$result['StatusCode'] = 500;
		$result['ErrorCode'] = $content->Code;
		$result['ErrorMessage'] = $content->Message;
	}
	echo json_encode($result);
	exit;
}
function read_file($fname) {
	$content = '';
	if (!file_exists($fname)) {
		echo "The file $fname does not exist\n";
		exit (0);
	}
	$handle = fopen($fname, "rb");
	while (!feof($handle)) {
		$content .= fread($handle, 10000);
	}
	fclose($handle);
	return $content;
}
function get_weeks1($time = '', $format='Y-m-d') {
	$time = $time != '' ? $time : time();
	//获取当前周几
	$week = date('w', $time);
	$date = [];
	for ($i=1; $i<=7; $i++) {
		if($week==0) {
			$date[$i] = date($format,strtotime( '+' . $i-$week-7 .' days', $time));
		} else {
			$date[$i] = date($format,strtotime( '+' . $i-$week .' days', $time));
		}
	}
	return $date;
}
function get_week_num($time) {
	if(date('w',$time) == 0 ) {
		$day = '7';
	}
	if(date('w',$time) == 1 ) {
		$day = '1';
	}
	if(date('w',$time) == 2 ) {
		$day = '2';
	}
	if(date('w',$time) == 3 ) {
		$day = '3';
	}
	if(date('w',$time) == 4 ) {
		$day = '4';
	}
	if(date('w',$time) == 5 ) {
		$day = '5';
	}
	if(date('w',$time) == 6 ) {
		$day = '6';
	}
	return $day;
}
