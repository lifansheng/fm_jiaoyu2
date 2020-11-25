<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $this->weid;
        $from_user = $this->_fromuser;
        $tag = $_GPC['tag'];
        $openid = $_W['openid'];

        $mac = '';
        if($tag){
			for($i=0;$i<6;$i++){
				if($mac) $mac.=':';
				$mac .= strtoupper(substr($tag, $i*2,2));
			}
		}

		$operation = in_array ( $_GPC ['op'], array ('default','jump') ) ? $_GPC ['op'] : 'default';

		
		//根据mac地址找绑定学校
		$ckmac = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE macid = '{$mac}' and is_on=1 ");
		if(empty($ckmac)){
			message('没找到设备或已被禁用！');
		}
		$schoolid = $ckmac['schoolid'];
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid), 'id');	


        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id= :id", array(':weid' => $weid, ':id' => $schoolid));
        $schoolid = $school['id'];
        $title = $school['title'];
        if (empty($school)) {
            message('没有找到该学校，请联系管理员！');
        }

        if($operation == 'default'){
        	$uid = $userid['uid'];

	        //学生
	        $student = pdo_fetchall("SELECT id as childId,'sid' as clsid, bj_id as classId, s_name as name,ouid,muid,duid,otheruid FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and (ouid=:uid or muid=:uid or duid=:uid or otheruid=:uid )", array(':uid' => $uid));

	        //老师
	        $teacher = pdo_fetchall("SELECT id as childId,'tid' as clsid, fz_id as classId, tname as name,0 as ouid,0 as muid,0 as duid,0 as otheruid FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = {$school['id']} and uid=:uid ", array(':uid' => $uid));

	        
	        $users = array_merge($teacher,$student);
	        if(!$users){
	        	message('没有找到绑定记录，扫码失败');
	        }
	        $idcard = false;
	        foreach ($users as $user) {
	        	$pard = 1;

	        	if($user['muid'] == $uid){
	        		$pard = 2;
	        	}elseif($user['duid'] == $uid){
	        		$pard = 3;
	        	}elseif($user['otheruid'] == $uid ){
	        		$pard = '4,5,6,7,8,9,10';
	        	}

	        	$card = pdo_fetchall("SELECT idcard  FROM " . tablename($this->table_idcard) . " WHERE {$user['clsid']} = '{$user['childId']}' and pard in({$pard}) ORDER BY id DESC");
	        	if($card){
	        		//拿到该用户第一张卡号
	        		$idcard = $card[0]['idcard'];
	        		break;
	        	}
	        }


	        if(!$idcard){
	        	message('没有绑定卡号，扫码开门失败');
	        }


	        $status = $_GPC['status'];
	        $info = array('status'=>$status,'signId'=>$idcard);
			$msg  = json_encode($info);
	        
	        require_once(MODULE_ROOT.'/inc/func/zhaji/zhaji.php');


		    $zhaji = new zhaji();
		    //$zhaji->set_debug();//调试环境打开这行
		    $result = $zhaji->push($tag,$msg);

		    if(!$result){
		        message('扫码成功，但推送失败了',$this->createMobileUrl('detail')."&schoolid={$schoolid}",'错误');
		    }


		    $jump_url   =  $_W['siteroot'].'app/'.$this->createMobileUrl('xz_device').'&tag='.$tag.'&op=jump';

		    $_SESSION['scancode'] = true;
		    header("Location:{$jump_url}");
		    exit();

	    }elseif($operation == 'jump'){

	    	if($_SESSION['scancode']){
	    		$_SESSION['scancode'] = false;

	    		message('扫码成功',$this->createMobileUrl('detail')."&schoolid={$schoolid}",'成功');
	    	}else{
	    		message('请返回重新扫码');
	    	}

        }

?>