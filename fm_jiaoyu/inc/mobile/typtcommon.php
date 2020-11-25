<?php
 

 
//  https://manger.daren007.com/app/index.php?i=25&c=entry&schoolid=4&do=typtcommon&m=fm_jiaoyu&op=enter&sign=eyJ1c2VyX01vYmlsZSI6IjE1ODU1NTU1NTU1IiwidXNlcl9JZCI6IjQxNTEzNzkwIiwic2Nob29sX0lkIjoiMjIyMjIyIiwiZWNfQ29kZSI6IjMzMzMzMiIsIm9wZW5JZCI6Ind4X3MwMzA0MzBzaG4wMDBlZSJ9Cg==
//$_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i=3&schoolid=4&do=typtcommon&op=enter"
 
//  $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i=3&schoolid=4&do=typtcommon&op=enter";
 
 

global $_W, $_GPC;
$openid     = $_W['openid'];
$DJson      = $_GPC['sign'];
$data       = json_decode(base64_decode($DJson),true);
$typt_id    = $data['user_Id'];
$usermobile = $data['user_Mobile'];
$tarWeid    = $_GPC['i'];  //当前公众号id
$return_msg = '';
$JumpType   = 0;

$HXYType = $_GPC['type'];

if($_GPC['op'] == 'enter'){
if(!empty($typt_id)){
    $Check_typt = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE  typt_user_id = '{$typt_id}'  "); //查学生里是否存在
    $Check_typt_Tea = pdo_fetch("SELECT * FROM ".GetTableName('teachers')." WHERE typt_user_id = '{$typt_id}'  "); //查老师里是否存在
}
    if(empty($Check_typt) && empty($Check_typt_Tea)){ //用户不存在 跳转到提示页面
        $return_msg = "用户不存在";
        $JumpType = 0;
        $Nouser = 'Nouser';
        include $this->template('typtcommon');
        

    }elseif(!empty($Check_typt)){ //存在学生用户
        $weid = $Check_typt['weid']; //用户所属公众号id
        $schoolid = $Check_typt['schoolid'];

        $schoolinfo = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");

        if($weid != $tarWeid){ //如果学生所属weid不是入口weid.必须跳转，不然获取到的信息不对
            $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i={$weid}&schoolid={$schoolid}&do=typtcommon&op=enter&sign={$DJson}";
            header("location:$stopurl");
        }
        $return_msg .= $Check_typt['s_name']." | ";
        $sid = $Check_typt['id'];

        $CheckBD = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE sid = '{$sid}' and openid = '{$openid}'  "); //检查当前微信是否已经绑定了当前学生
        if(!empty($CheckBD)){ //如果用户已绑定 跳转至学校首页
           $return_msg .= "已绑定，userid为 ".$CheckBD['id']." | ";
           $JumpType = 1;
           if($HXYType == 'ShowMessage'){
           	 $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i={$weid}&schoolid={$schoolid}&do=user&userid={$CheckBD['id']}";
           }else{
           	 $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i={$weid}&schoolid={$schoolid}&do=detail";
           }
           header("location:$stopurl");
        }else{ //如果用户未绑定，则展示绑定页面
            $sicon = $Check_typt['icon'] ? $Check_typt['icon'] : $schoolinfo['spic']; //处理学生头像
            include $this->template('typtcommon');
            die();
       }
    }elseif(!empty($Check_typt_Tea)){ //存在教师用户
        
        $return_msg .= $Check_typt['s_name']." | ";
        $weid = $Check_typt_Tea['weid'];
        $schoolid = $Check_typt_Tea['schoolid'];
        if($weid != $tarWeid){ //如果教师所属weid不是入口weid ,必须跳转
            $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i={$weid}&schoolid={$schoolid}&do=typtcommon&op=enter&sign={$DJson}";
            header("location:$stopurl");
        }
        
       $tid = $Check_typt_Tea['id'];
       $CheckBD = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE tid = '{$tid}' and openid = '{$openid}'  ");
   
       if(!empty($CheckBD)){ //如果老师已绑定
           $return_msg .= "已绑定，userid为 ".$CheckBD['id']." | ";
           
           if($HXYType == 'ShowMessage'){
           	 $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i={$weid}&schoolid={$schoolid}&do=myschool&userid={$CheckBD['id']}";
           }else{
           	  $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i={$weid}&schoolid={$schoolid}&do=detail";
           }
           
          
           header("location:$stopurl");
       }else{ //如果老师未绑定，直接绑定，因为老师只有一个身份，直接绑定就是，绑定完了再跳转
 
           $return_msg .= "未绑定，正在绑定中 |  ";
            $userdata = array(
                'tid' => $tid,
                'weid' =>  $weid,
                'schoolid' => $schoolid,
                'openid' => $openid,
                'uid' => $_W['member']['uid']
            );
           if(!empty($usermobile)){
               $userdata['mobile'] = $usermobile;
               $userdata['realname'] = $Check_typt_Tea['tname'];
           }			
           pdo_insert($this->table_user, $userdata);			
           $userid = pdo_insertid();
           $return_msg .= "绑定成功，userid 为 ". $userid." |  ";
           $stopurl = $_W['siteroot'] ."app/index.php?c=entry&m=fm_jiaoyu&i={$weid}&schoolid={$schoolid}&do=detail";
           header("location:$stopurl");
       }
    }
}
if($_GPC['op'] =='bdstu'){ //学生绑定，ajax提交过来
    $subjectId = $_GPC['subjectId'];
    $sid = $_GPC['sid'];
    $uid = $_GPC['uid'];
    $StudentInfo = pdo_fetch("SELECT * FROM ".GetTableName('students')." WHERE id = '{$sid}' ");
    $CheckIsUsed = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE  sid = '{$sid}' and pard = '{$subjectId}'  ");
    if(!empty($CheckIsUsed)){
        $result['status'] = false;
        $result['msg'] = "对不起，该学生当前身份已绑定";
        die(json_encode($result));
    }else{
        $userdata = array(
            'sid' => $sid,
            'weid' =>  $StudentInfo['weid'],
            'schoolid' => $StudentInfo['schoolid'],
            'openid' => $openid,
            'pard' => $subjectId,
            'uid' =>  $uid
        );


        if(!empty($StudentInfo['mobile'])){
            $userdata['mobile'] =$StudentInfo['mobile'];
            $userdata['realname'] = $StudentInfo['s_name'];
        };
        pdo_insert($this->table_user, $userdata);			
        $userid = pdo_insertid();

        if($subjectId == 2){
            $temp = array( 
                'mom' => $openid,
                'muserid' => $userid,
                'muid' =>  $uid,
            );
        }
        if($subjectId == 3){
            $temp = array(
                'dad' => $openid,
                'duserid' => $userid,
                'duid' =>  $uid
            );
        }
        if($subjectId == 4){
            $temp = array(
                'own' => $openid,
                'ouserid' => $userid,
                'ouid' =>  $uid
            );
        }
        if($subjectId == 5){
            $temp = array(
                'other' => $openid,
                'otheruserid' => $userid,
                'otheruid' =>  $uid
            );
        }
        pdo_update($this->table_students, $temp, array('id' => $sid)); 
        $result['status'] = true;
        $result['msg'] = "绑定成功！即将进入首页";
        $result['data'] = array(
            'weid' =>  $StudentInfo['weid'],
            'schoolid' => $StudentInfo['schoolid']
        );
        die(json_encode($result));
    }

}

 