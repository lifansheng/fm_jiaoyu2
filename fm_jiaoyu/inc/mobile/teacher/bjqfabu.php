<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $this->weid;
        $from_user = $this->_fromuser;
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		if(is_showgkk()){
			if(!empty($_GPC['op'])){
				$op = $_GPC['op']; 
				$sid = $_GPC['sid'];
				$noticeid = $_GPC['noticeid'];
				$bj_id = $_GPC['bj_id'];
				$student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $sid));
				$leave = pdo_fetch("SELECT title FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $noticeid));
			}
		}
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));	
        if(!empty($userid['id'])){
			$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
			$school = pdo_fetch("SELECT title,isopen,bjqstyle,style3,txid,txms,is_fbnew,is_fbvocie FROM " . tablename($this->table_index) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $schoolid));
			$bjlists = get_mylist($schoolid,$it['tid'],'teacher');
			if(is_njzr($teachers['id'])){
				$mynjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' ORDER BY ssort DESC");
				foreach($mynjlist as $key =>$row){
					$mynjlist[$key]['bjlist'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$row['sid']}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
					foreach($mynjlist[$key]['bjlist'] as $k => $v){

					}
				}
			}else{
				if($teachers['status'] == 2){
					$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
				}			
			}	           
			include $this->template(''.$school['style3'].'/bjqfabunew');
		}else{
         	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
		 
        
		}        
?>