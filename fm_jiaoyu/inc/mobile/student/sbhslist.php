<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$userss = !empty($_GPC['userid']) ? intval($_GPC['userid']) : 1;
		$openid = $_W['openid'];
		$obid = 2;

        //查询是否用户登录
		if(empty($_SESSION['user'])){
			mload()->model('user');
			$_SESSION['user'] = check_userlogin($weid,$schoolid,$openid,$userss);
			if ($_SESSION['user'] == 2){
				include $this->template('bangding');
			}
        }
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
        $it = pdo_fetch("SELECT id,sid,is_allowmsg FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
       
		$qhlist  = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'xq_score' and is_show_qh = 1 ORDER BY sd_end DESC ");
		$qhid_now = pdo_fetch("SELECT sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'xq_score' and is_show_qh = 1 and sd_start < '".time()."' and sd_end > '".time()."' ")['sid'];
		$qhid_last = pdo_fetch("SELECT c.sid FROM ".GetTableName('classify')." as c , ".GetTableName('behaviorscorelog')." as b WHERE b.qhid = c.sid and c.schoolid = '{$schoolid}' and c.is_show_qh = 1 and c.type = 'xq_score' and b.tid = '{$tid_global}' ORDER BY  c.sd_end DESC ")['sid'];
		$qhid = $qhid_now ? $qhid_now : ( $qhid_last ? $qhid_last : $qhlist[0]['sid'] ) ;
        $op = $_GPC['op'] ? $_GPC['op'] : 'display';
        $sid = $it['sid'];

        $student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE id = '{$sid}' ");
        

        if($op == 'switchxq'){
			$qhid = $_GPC['id'];
			$ResData = [];
            $Alltotal = 0;
            $qhinfo = pdo_fetch("SELECT addedinfo FROM ".GetTableName('classify')." WHERE sid = '{$qhid}' ");
            $othertip = json_decode($qhinfo['addedinfo'],true);
            $list = pdo_fetchall("SELECT tid,createtime FROM ".GetTableName('behaviorscorelog')." WHERE schoolid = '{$schoolid}' and sid = '{$sid}' and qhid = '{$qhid}' GROUP BY tid ORDER BY createtime DESC  ");
            if(!empty($list)){
                foreach($list as $key => $value){
                    if($value['tid'] > 0 ){
                        $tname = pdo_fetch("SELECT tname,thumb FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and id = '{$value['tid']}' ");
                        $list[$key]['tname'] = $tname['tname'];
                        $list[$key]['thumb'] =  $tname['thumb'] ? tomedia($tname['thumb']) : tomedia($school['tpic']);
                    }else{
                        $list[$key]['tname'] = '管理员';
                        $list[$key]['thumb'] =  tomedia($school['tpic']);
                    }
                    $list[$key]['time'] = date("Y-m-d H:i",$value['createtime']);
                }
                die(json_encode(array(
                    'data' => $list,
                    'status' => true
                )));
            }else {
                die(json_encode(array(
                    'data' =>  $othertip['othertip'],
                    'status' => false
                )));
            }
        
		}elseif($op == 'display'){
            if(!empty($it)){
                // $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
                // $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " WHERE weid = :weid AND schoolid =:schoolid  AND isfrist = :isfrist And :isliuyan = isliuyan And (userid = :userid OR touserid = :touserid) ORDER BY createtime DESC", array(
                // 	':weid' => $weid,
                // 	':schoolid' => $schoolid,
                // 	':isfrist' => 1,
                // 	':isliuyan' => 2,
                // 	':userid' => $it['id'],
                // 	':touserid' => $it['id']
                // ));
                // foreach ($list as $index => $row) {
                // 	if($row['userid'] == $it['id']){
                // 		$user = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $row['touserid']));
                // 		$students = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $user['sid']));
                // 		$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $user['tid']));
                // 		$list[$index]['huifu'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where weid = :weid AND leaveid = :leaveid ORDER BY createtime DESC LIMIT 0,1", array(':weid' => $weid, ':leaveid' => $row['id']));
                // 		foreach ($list[$index]['huifu'] as $k => $v) {
                // 			$list[$index]['huifu'][$k]['sj'] = sub_day($v['createtime']);
                // 			$list[$index]['huifu'][$k]['lastconet'] = $v['conet'];
                // 			$list[$index]['huifu'][$k]['myid'] = $v['userid'];
                // 			$list[$index]['huifu'][$k]['mytoid'] = $v['touserid'];
                // 			if($v['userid'] == $it['id']){
                // 				$users = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $v['touserid']));
                // 			}
                // 			if($v['touserid'] == $it['id']){
                // 				$users = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $v['userid']));
                // 			}
                // 			if($users['sid']){
                // 				$list[$index]['huifu'][$k]['sf'] = 1;
                // 			}
                // 			if($users['tid']){
                // 				$list[$index]['huifu'][$k]['sf'] = 2;		
                // 			}						
                // 		}
                // 		$list[$index]['tname'] = $teacher['tname'];					
                // 		$list[$index]['s_name'] = $students['s_name'];					
                // 		$list[$index]['pard'] = $user['pard'];
                // 		if($user['sid']){
                // 			$list[$index]['shenfen'] = 1;
                // 			$list[$index]['userinfo'] = $user['userinfo'];
                // 		}
                // 		if($user['tid']){
                // 			$list[$index]['shenfen'] = 2;
                // 		}					
                // 	}
                // 	if($row['touserid'] == $it['id']){
                // 		$user = pdo_fetch("SELECT pard,sid,tid FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $row['userid']));
                // 		$students = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $user['sid']));
                // 		$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $user['tid']));
                // 		$list[$index]['huifu'] = pdo_fetchall("SELECT * FROM " . tablename($this->table_leave) . " where weid = :weid AND leaveid = :leaveid ORDER BY createtime DESC LIMIT 0,1", array(':weid' => $weid, ':leaveid' => $row['id']));	
                // 		foreach ($list[$index]['huifu'] as $k => $v) {
                // 			$list[$index]['huifu'][$k]['sj'] = sub_day($v['createtime']);
                // 			$list[$index]['huifu'][$k]['lastconet'] = $v['conet'];
                // 			$list[$index]['huifu'][$k]['myid'] = $v['userid'];
                // 			$list[$index]['huifu'][$k]['mytoid'] = $v['touserid'];
                // 			if($v['userid'] == $it['id']){
                // 				$user = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $v['touserid']));
                // 			}
                // 			if($v['touserid'] == $it['id']){
                // 				$user = pdo_fetch("SELECT pard,sid,tid,userinfo FROM " . tablename($this->table_user) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $v['userid']));
                // 			}
                // 			if($user['sid']){
                // 				$list[$index]['huifu'][$k]['sf'] = 1;
                // 			}
                // 			if($user['tid']){
                // 				$list[$index]['huifu'][$k]['sf'] = 2;		
                // 			}						
                // 		}	
                // 		$list[$index]['tname'] = $teacher['tname'];					
                // 		$list[$index]['s_name'] = $students['s_name'];					
                // 		$list[$index]['pard'] = $user['pard'];				
                // 		if($user['sid']){
                // 			$list[$index]['shenfen'] = 1;
                // 			$list[$index]['userinfo'] = $user['userinfo'];
                // 		}
                // 		if($user['tid']){
                // 			$list[$index]['shenfen'] = 2;		
                // 		}					
                // 	}				
                // }			
                // $this->checkobjiect($schoolid, $it['sid'], $obid);
                include $this->template(''.$school['style2'].'/sbhslist');
            }else{
                session_destroy();
                $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
                header("location:$stopurl");
                exit;
            } 
        }
       
?>