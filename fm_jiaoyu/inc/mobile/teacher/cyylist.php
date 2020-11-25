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
        $obid = 1;
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));		
		$thistime = trim($_GPC['limit']);
		if($thistime){
			$condition = " AND createtime < '{$thistime}'";	
		    $leave1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' $condition And type=0 ORDER BY createtime DESC LIMIT 0,5 ");			
			foreach($leave1 as $key =>$row){
				$teach = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $row['kcid']));
				if(!empty($teach['name'])){
					$leave1[$key]['kname'] = $teach['name'];
				}else{
					$leave1[$key]['kname'] = "未指定课程";
				}
				$tbeizhu =  pdo_fetch("SELECT id FROM " . tablename($this->table_cyybeizhu_teacher) . " where cyyid = :cyyid ", array(':cyyid' => $row['id']));
				$leave1[$key]['tbeizhu'] = $tbeizhu['id'];
				$leave1[$key]['time'] = date('Y-m-d H:i', $row['createtime']);	
			}
			include $this->template('comtool/cyylist'); 
		}else{
		    $leave = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}'  And type=0 ORDER BY createtime DESC LIMIT 0,5 ");
			foreach($leave as $key =>$row){
				$teach = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where id = :id ", array(':id' => $row['kcid']));
				if(!empty($teach['name'])){
					$leave[$key]['kname'] = $teach['name'];
				}else{
					$leave[$key]['kname'] = "未指定课程";
				}
				$tbeizhu =  pdo_fetch("SELECT id FROM " . tablename($this->table_cyybeizhu_teacher) . " where cyyid = :cyyid ", array(':cyyid' => $row['id']));
				$leave[$key]['tbeizhu'] = $tbeizhu['id'];
				$leave[$key]['time'] = date('Y-m-d H:i', $row['createtime']);	
			} 
			include $this->template(''.$school['style3'].'/cyylist');	
		}	
		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }       








?>