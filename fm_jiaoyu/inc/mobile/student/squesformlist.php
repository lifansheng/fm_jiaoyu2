<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$schooltype  = $_W['schooltype'];
		$obid = 1;
		
        //查询是否用户登录
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		$student = pdo_fetch("SELECT id,bj_id FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));		
		$bj_id = $student['bj_id'];
		$thistime = trim($_GPC['limit']);
		if(empty($thistime)){
            $condition = " And type = 5 AND ( usertype = 'school' Or usertype = 'allstu' Or FIND_IN_SET('{$student['bj_id']}',userdatas)) ";
            $leave = pdo_fetchall("SELECT id,bj_id,kc_id,km_id,title,tname,createtime,type,tid,content,usertype,userdatas,groupid FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT 0,20 ");
			foreach($leave as $key =>$row){
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$leave[$key]['tname'] = $teach['tname'];
				$leave[$key]['thumb'] = empty($teach['thumb']) ? $school['tpic'] : $teach['thumb'];
				$leave[$key]['shenfen'] = get_teacher($teach['status']);
				mload()->model('read');
				$ydrs = check_readtype($weid,$schoolid,$it['id'],$row['id']);
				if($ydrs == 2){
					$leave[$key]['ydrs'] = "未读";
				}				
				$leave[$key]['time'] = date('Y-m-d H:i', $row['createtime']);
			}
			include $this->template(''.$school['style2'].'/squesformlist');	
		}else{
			if($thistime){
				$condition = " AND createtime < '{$thistime}'";	
			}
            $condition .= " And type = 5 AND ( usertype = 'school' Or usertype = 'allstu' Or FIND_IN_SET('{$student['bj_id']}',userdatas)) ";
		    $leave1 = pdo_fetchall("SELECT id,bj_id,kc_id,km_id,title,tname,createtime,type,tid,content,usertype,userdatas,groupid FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT 0,20 ");
			foreach($leave1 as $key =>$row){
				
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$leave1[$key]['tname'] = $teach['tname'];
				$leave1[$key]['thumb'] = empty($teach['thumb']) ? $school['tpic'] : $teach['thumb'];
				$leave1[$key]['shenfen'] = get_teacher($teach['status']);
				mload()->model('read');
				$ydrs = check_readtype($weid,$schoolid,$it['id'],$row['id']);
				if($ydrs == 2){
					$leave1[$key]['ydrs'] = "未读";
				}			
				$leave1[$key]['time'] = date('Y-m-d H:i', $row['createtime']);
			} 
			include $this->template('comtool/squesformlist'); 
		}		
        if(!empty($it)){
										
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>