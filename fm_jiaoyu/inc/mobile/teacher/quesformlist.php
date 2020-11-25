<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        $obid = 1;
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000201,2,$schoolid))){
			message('您无权查看本页面');
		}
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));		
		$thistime = trim($_GPC['limit']);
		$condition2 = " And type = 5 AND ( usertype = 'school' Or usertype = 'alltea' Or FIND_IN_SET('{$teachers['fz_id']}',userdatas) Or tid = '{$tid_global}') ";
		
		if($thistime){
			$condition = " And createtime < '{$thistime}'";	
		    $leave1 = pdo_fetchall("SELECT id,title,tname,createtime,type,tid,content,usertype,userdatas FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  $condition $condition2 ORDER BY createtime DESC LIMIT 0,5 ");
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
                $leave1[$key]['tzlx'] = "问卷调查";
				$leave1[$key]['time'] = date('Y-m-d H:i', $row['createtime']);
				if($row['usertype'] == 'jsfz'){
					$userdatas = explode(',',$row['userdatas']);
					if(!in_array($teachers['fz_id'],$userdatas) && !(IsHasQx($tid_global,2000202,2,$schoolid))){
						unset($leave1[$key]);
					}
				}
			} 
			include $this->template('comtool/quesformlist'); 
		}else{
		    $leave = pdo_fetchall("SELECT id,bj_id,title,tname,createtime,type,tid,content,usertype,userdatas FROM " . tablename($this->table_notice) . " where weid = '{$weid}' And schoolid = '{$schoolid}' $condition2 ORDER BY createtime DESC LIMIT 0,5 ");
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
                $leave[$key]['tzlx'] = "问卷调查";
				$leave[$key]['time'] = date('Y-m-d H:i', $row['createtime']);
				if($row['usertype'] == 'jsfz'){
					$userdatas = explode(',',$row['userdatas']);
					if(!in_array($teachers['fz_id'],$userdatas) && !(IsHasQx($tid_global,2000202,2,$schoolid))){
						unset($leave[$key]);
					}
				}				
			} 
			include $this->template($school['style3'].'/quesform/quesformlist');	
		}	
		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }       

?>