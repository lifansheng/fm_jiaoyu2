<?php



mload()->model('hxy'); 
$filename = MODULE_ROOT . '/model/typt.config.php';
require $filename;

    $op = $_GPC['op'] ? $_GPC['op'] : 'default' ;

    if($op == 'default'){
        $result['error_code'] = "500";
        $result['error_msg'] = "无效请求";
        die(json_encode($result));
    }

    if($op == 'testport'){
        $GetData = $_GPC['__input'];
        $txtname = 'hxyport'.time().'.txt';
        $txtpath_name = IA_ROOT . '/attachment/down/' . $txtname;
       // var_dump($GetData);




        ob_start(); //打开缓冲区
        var_dump($GetData); 
        $a = ob_get_contents(); //输出缓冲区内容到$a,相当于赋值给$a
        ob_clean();   //这里清除缓冲区内容


        $file = fopen($txtpath_name, "w");
        fwrite($file, $a);
        fclose($file);




        $result['error_code'] = "00";
        $result['error_msg'] = "测试成功";
        die(json_encode($result));
    }

  if($op == 'newuser'){ //用户信息推送接口
        // var_dump("YU");
        $GetData = $_GPC['POSTDATA'];
        $Alldata = json_decode(htmlspecialchars_decode($GetData),true); //$data 根据实际情况改动
        $data = $Alldata['Data'][0];
        $optype = $data['optype']; //操作类型
       // var_dump($Alldata);
        $user_id     = $data['user_id'];
        $user_name   = $data['user_name'];
        $user_token  = $data['user_token'];
        $stu_code    = $data['stucode'];
        $ec_code     = $data['ec_code'];
        $school_name = $data['school_name'];
        $role        = $data['role'];
        $gradeid     = $data['gradeid'];
        $gradename   = $data['gradename'];
        $classid     = $data['classid'];
        $classname   = $data['classname'];

        $status = false;
        $msg = '未知错误';
        //检查学校是否存在
        $schoolinfo = pdo_fetch("SELECT weid,id FROM ".GetTableName('index')." WHERE typt_ec_code = '{$ec_code}'  ");
       
        if(empty($schoolinfo)){ //学校不存在
            $status = false;
            $msg = "学校不存在";
        }else{ //学校存在
            $weid = $schoolinfo['weid'];
            $schoolid = $schoolinfo['id'];
            //检查年级和班级是否存在
            $gradeinfo = pdo_fetch("SELECT sid,sname FROM ".GetTableName('classify')." WHERE typt_id = '{$gradeid}' and type = 'semester' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
            $classinfo = pdo_fetch("SELECT sid,sname,parentid FROM ".GetTableName('classify')." WHERE typt_id = '{$classid }' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
            if(empty($gradeinfo)){ //年级不存在
                $CkGRaName = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sname = '{$gradename}' and typt_id = 0 and type = 'semester' ");
                if(!empty($CkGRaName)){
                    pdo_update(GetTableName('classify',false),array('typt_id' => $gradeid),array('sid'=>$CkGRaName['sid']));
                    $njid = $CkGRaName['sid'];
                }else{
                    $InsGradeData = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'sname' => $gradename,
                        'typt_id' => $gradeid,
                        'type' => 'semester'
                    );
                    pdo_insert(GetTableName('classify',false),$InsGradeData);
                    $njid = pdo_insertid();
                }
                $gradeinfo = pdo_fetch("SELECT sid,sname FROM ".GetTableName('classify')." WHERE typt_id = '{$gradeid}' and type = 'semester' and weid = '{$weid}' and schoolid = '{$schoolid}' ");

            }  //年级存在
            if(empty($classinfo)){ //班级不存在
                $CkClsName = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sname = '{$classname}' and typt_id = 0 and type = 'theclass' and parentid = '{$gradeinfo['sid']}' ");
                if(!empty($CkClsName)){
                    pdo_update(GetTableName('classify',false),array('typt_id' => $classid),array('sid'=>$CkClsName['sid']));
                    $bjid = $CkClsName['sid'];
                }else{
                    $InsClassData = array(
                        'weid' => $weid,
                        'schoolid' => $schoolid,
                        'sname' => $classname,
                        'typt_id' => $classid,
                        'type' => 'theclass',
                        'parentid' => $gradeinfo['sid']
                    );
                    pdo_insert(GetTableName('classify',false),$InsClassData);
                    $bjid = pdo_insertid();
                }
                $classinfo = pdo_fetch("SELECT sid,sname,parentid FROM ".GetTableName('classify')." WHERE typt_id = '{$classid }' and type = 'theclass' and weid = '{$weid}' and schoolid = '{$schoolid}' ");

            } //班级存在
            if($classinfo['parentid'] != $gradeinfo['sid']){ //班级与年级不对应
                pdo_update(GetTableName('classify',false),array('parentid'=>$gradeinfo['sid']),array('sid'=>$classinfo['sid']));
            }  //学校、班级、年级信息都对，开始操作用户
            if($optype == 1){ //新增用户
                $rAnd  = substr($rAndStr, 0, 6);
                if($role == 1){ //学生
                    $chcekstu = pdo_fetch("SELECT id FROM ".GetTableName('students')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
                    if(!empty($checkstu)){
                        $status = false;
                        $msg = "当前学生已存在";
                    }else{
                        $stu_insert_data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'numberid' => $stu_code,
                            'xq_id' => $gradeinfo['sid'],
                            'bj_id' => $classinfo['sid'],
                            'seffectivetime' => time(),
                            's_name' => $user_name,
                            'typt_user_id' => $user_id,
                            'code' => $rAnd 
                        );
                        pdo_insert(GetTableName('students',false),$stu_insert_data);
                        $status = true;
                        $msg    = "新增学生成功";
                    }
                }elseif($role == 3){ //教师
                    $checktea = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
                    if(!empty($checktea)){
                        $status = false;
                        $msg = "当前教师已存在";
                    }else{
                        $tea_insert_data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'tname' => $user_name,
                            'typt_user_id' => $user_id,
                            'code' => $rAnd 
                        );
                        pdo_insert(GetTableName('teachers',false),$tea_insert_data);
                        $TeaId = pdo_insertid();
                        CheckZlTeaInfo($typt_appid,$TeaId);
                        $status = true;
                        $msg    = "新增老师成功";
                    }
                }elseif($role == 4){ //教师管理员
                    $checktea = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
                    if(!empty($checktea)){
                        $status = false;
                        $msg = "当前管理员已存在";
                    }else{
                        $tea_insert_data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'tname' => $user_name,
                            'typt_user_id' => $user_id,
                            'code' => $rAnd,
                            'typt_is_admin' => 1
                        );
                        pdo_insert(GetTableName('teachers',false),$tea_insert_data);
                        $TeaId = pdo_insertid();
                        CheckZlTeaInfo($typt_appid,$TeaId);
                        $status = true;
                        $msg    = "新增管理员成功";
                    }
                }else{
                    $status = false;
                    $msg = "新增失败，未知角色";
                }
            }elseif($optype == 2){ //修改用户
                if($role == 1){ //学生
                    $checkstu = pdo_fetch("SELECT id FROM ".GetTableName('students')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
                    if(empty($checkstu)){
                        $status = false;
                        $msg = "当前学生不存在";
                    }else{
                        $stu_update_data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'numberid' => $stu_code,
                            'xq_id' => $gradeinfo['sid'],
                            'bj_id' => $classinfo['sid'],
                            's_name' => $user_name,
                            'typt_user_id' => $user_id,
                        );
                        pdo_update(GetTableName('students',false),$stu_update_data,array('id'=>$checkstu['id']));
                        $status = true;
                        $msg    = "修改学生成功";
                    }
                }elseif($role == 3){ //教师
                    $checktea = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' "); 
                    if(empty($checktea)){
                        $status = false;
                        $msg = "当前教师不存在";
                    }else{
                        $tea_update_data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'tname' => $user_name,
                            'typt_user_id' => $user_id,
                        );
                        pdo_update(GetTableName('teachers',false),$tea_update_data,array('id'=>$checktea['id']));
                        CheckZlTeaInfo($typt_appid,$checktea['id']);
                        $status = true;
                        $msg    = "修改老师成功";
                    }
                }elseif($role == 4){ //教师管理员
                    $checktea = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' "); 
                    if(empty($checktea)){
                        $status = false;
                        $msg = "当前管理员不存在";
                    }else{
                        $tea_update_data = array(
                            'weid' => $weid,
                            'schoolid' => $schoolid,
                            'tname' => $user_name,
                            'typt_user_id' => $user_id,
                            'typt_is_admin' => 1
                        );
                        pdo_update(GetTableName('teachers',false),$tea_update_data,array('id'=>$checktea['id']));
                        CheckZlTeaInfo($typt_appid,$checktea['id']);

                        $status = true;
                        $msg    = "修改管理员成功";
                    }
                }else{
                    $status = false;
                    $msg = "修改失败，未知角色";
                }
            }elseif($optype == 3){ //删除用户 
                if($role == 1){ //学生
                    $checkstu = pdo_fetch("SELECT id FROM ".GetTableName('students')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' ");
                    if(empty($checkstu)){
                        $status = false;
                        $msg = "当前学生不存在";
                    }else{
                        pdo_delete(GetTableName('students',false),array('id'=>$checkstu['id']));
                        DeleteStudent($checkstu['id']);
                        $status = true;
                        $msg    = "删除学生成功";
                    }
                }elseif($role == 3){ //教师
                    $checktea = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' "); 
                    if(empty($checktea)){
                        $status = false;
                        $msg = "当前教师不存在";
                    }else{
                        pdo_delete(GetTableName('teachers',false),array('id'=>$checktea['id']));
                        DeleteTeacher($checktea['id']);
                        $status = true;
                        $msg    = "删除老师成功";
                    }
                }elseif($role == 4){ //教师管理员
                    $checktea = pdo_fetch("SELECT id FROM ".GetTableName('teachers')." WHERE typt_user_id = '{$user_id}' and weid = '{$weid}' and schoolid = '{$schoolid}' "); 
                    if(empty($checktea)){
                        $status = false;
                        $msg = "当前管理员不存在";
                    }else{
                        pdo_delete(GetTableName('teachers',false),array('id'=>$checktea['id']));
                        DeleteTeacher($checktea['id']);
                        $status = true;
                        $msg    = "删除管理员成功";
                    }
                }else{
                    $status = false;
                    $msg = "删除失败，未知角色";
                }
            }else{
                $status = false;
                $msg = '未知错误,数据获取有问题'.$optype; 
            }    
        }
        
        if($status == false){
            $result['error_code'] = "500"; 
        }elseif($status == true){
            $result['error_code'] = "00";          
        }
        $result['error_msg'] = $msg;
        die(json_encode($result)); 
    }
  if($op == 'BorrowBooks'){ //获取图书借阅与归还记录
        global $_GPC;
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['i'];
        $sid = $_GPC['sid'];
        $cardid = $_GPC['cardid'];
        $BookName = htmlspecialchars_decode($_GPC['bookname']);
        $status = $_GPC['status'];
        $time = $_GPC['time'];
        $worth = $_GPC['price'];

       // die(json_encode($_GPC)); //
        $stuid = pdo_fetch("SELECT sid FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and idcard = '{$cardid}' ");
        if(empty($cardid) || empty($BookName) || empty($status) || empty($time)){
            $result['status'] = 0;
           // $result['code'] = '1000';
            $result['msg'] = "参数不完整"; 
            die(json_encode($result));
        }
        if(!empty($stuid['sid'])){
            $sid = $stuid['sid'];
            $Checkstu = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE  id = '{$sid}'  ");
            if(!empty($Checkstu['s_name'])){
                $DBdata = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'sid' => $sid,
                    'bookname' => $BookName,
                    'worth' => $worth,
                    'status' => $status,
                    'createtime' => time()
                );
                if($status == 1){ //新增借阅
                    $DBdata['borrowtime'] = $time;
                    pdo_insert(GetTableName('booksborrow',false),$DBdata);
                    $result['status'] = 1;
                  //  $result['code'] = '100';
                    $result['msg'] = "同步借阅成功！";
                }elseif($status == 2){ //归还图书
                    $check = pdo_fetch("SELECT id FROM ".GetTableName('booksborrow')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and sid = '{$sid}' and bookname = '{$BookName}' and status = 1  ORDER BY createtime ASC ");
                    if(!empty($check)){
                        $logid = $check['id'];
                        pdo_update(GetTableName('booksborrow',false),array('status'=>2,'returntime'=>$time),array('id'=>$logid));
                        $result['status'] = 1;
                       // $result['code'] =  $logid;
                        $result['msg'] = "同步归还成功";
                    }else{
                        $result['status'] = 0;
                      //  $result['code'] = '300';
                        $result['msg'] = "当前借阅记录不存在";
                    }
                }
            }else{
                $result['status'] = 0;
               // $result['code'] = '500';
                $result['msg'] = "当前卡号无对应学生"; 
            }
        }else{
            $result['status'] = 0;
          //  $result['code'] = '400';
            $result['msg'] = "当前卡号不存在";
        }
        die(toJson($result));
    }

    if($op == 'GetAllStu'){ //获取所有绑定了学生的卡
        global $_GPC;
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['i'];
        $stulist = pdo_fetchall("SELECT idcard,sid FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and $weid = '{$weid}' and usertype = 0 and is_on = 1 and cardtype = 1 ");
       // die(json_encode($stulist));

        foreach($stulist as $key=>$value){
            $stuinfo = pdo_fetch("SELECT s_name,bj_id,xq_id FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $bjname = pdo_fetch("SELECT sname,parentid FROM ".GetTableName('classify')." WHERE sid = '{$stuinfo['bj_id']}' ");
            $njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$bjname['parentid']}' ");
            $stulist[$key]['StuName'] = $stuinfo['s_name'];
            $stulist[$key]['ClassName'] = $bjname['sname'];
            $stulist[$key]['GradeName'] = $njname['sname'];
        }
        $result['status'] = 1;
        $result['msg'] = '获取所有学生成功！';
        $result['data'] = $stulist;
        die(json_encode($result));
    }

    if($op == 'GetStuByCardId'){ //获取所有绑定了学生的卡
        global $_GPC;
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['i'];
        $CheckCard = $_GPC['CheckCard'];

        $stulist = pdo_fetch("SELECT idcard,sid FROM ".GetTableName('idcard')." WHERE schoolid = '{$schoolid}' and $weid = '{$weid}' and idcard = '{$CheckCard}' ");
       // die(json_encode($stulist));
        $stuinfo = pdo_fetch("SELECT s_name,bj_id,xq_id FROM ".GetTableName('students')." WHERE id = '{$stulist['sid']}' ");
        $bjname = pdo_fetch("SELECT sname,parentid FROM ".GetTableName('classify')." WHERE sid = '{$stuinfo['bj_id']}' ");
        $njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$bjname['parentid']}' ");
        $stulist['StuName'] = $stuinfo['s_name'];
        $stulist['ClassName'] = $bjname['sname'];
        $stulist['GradeName'] = $njname['sname'];
       
       // var_dump( $stulist);
        if(!empty($stulist['sid']) && $CheckCard != 0){
            $result['status'] = 1;
            $result['msg'] = '查询当前学生成功！';
            $result['data'] = $stulist;
        }else{
            $result['status'] = 0;
            $result['msg'] = '查询失败，未找到学生';
        }
       
        die(toJson($result));

    }
 

    function toJson($arr)  
    {  
          
        $ajax = ToUrlencode($arr);  
        $str_json = json_encode($ajax);  
        return urldecode($str_json);  
    }  
    
    /** 
     * 将数组里面带有中文的字串用urlencode转换格式返回 
     * 
     * @param   array $arr  数组 
     * @return  array 
     */  
    function ToUrlencode($arr)  
    {  
    
        $temp = array();  
        if (is_array($arr))  
        {  
            foreach ($arr AS $key => $row)  
            {  
                $temp[$key] = $row;  
                if (is_array($temp[$key]))  
                {  
                    $temp[$key] = ToUrlencode($temp[$key]);  
                }  
                else  
                {  
                    $temp[$key] = urlencode($row);  
                }  
            }  
        }  
        else  
        {  
            $temp = $arr;  
        }  
        return $temp;  
    } 
 