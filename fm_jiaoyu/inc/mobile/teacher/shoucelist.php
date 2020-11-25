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
        $nowtime = time();
        //查询是否用户登录
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000801,2,$schoolid))){
			message('您无权查看本页面');
		}
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid)); 	
		$teachers = pdo_fetch("SELECT id,status FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));		
 		$bjlists = get_mylist($schoolid,$it['tid'],'teacher');	
		if(is_njzr($teachers['id'])){
			$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' And type = 'semester'");
			$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And parentid = '{$myfisrtnj['sid']}'");
		}else{
			$fisrtbj =  pdo_fetch("SELECT bj_id FROM " . tablename($this->table_class) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  And tid = {$it['tid']} ");
			if($teachers['status'] == 2){
				$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = 'theclass'");
			}			
		}
		if(!empty($_GPC['bj_id'])){
			$bj_id = intval($_GPC['bj_id']);			
		}else{
			$bj_id = $fisrtbj['bj_id'];
		}
		$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
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
		if($_GPC['op'] == 'del'){
			if(!$_GPC['tid']){
				$data = explode ( '|', $_GPC ['json'] );
				$data ['status'] = 2;
				$data ['info'] = '非法请求！';				
			}else{
				pdo_delete($this->table_sc, array('id' => $_GPC['id']));
				pdo_delete($this->table_scforxs, array('scid' => $_GPC['id']));				
				$data ['status'] = 1;
				$data ['info'] = '删除成功！';				
			}
			die ( json_encode ( $data ) );	
		}       
	   if(!empty($userid['id'])){
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_sc) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And bj_id = '{$bj_id}' ORDER BY createtime DESC");
			foreach($list as $key => $vule){
				$scset = pdo_fetch("SELECT icon FROM " . tablename($this->table_scset) . " where :id = id ", array(':id' => $vule['setid'])); 
				$kc = pdo_fetch("SELECT name FROM " . tablename($this->table_tcourse) . " where id = '{$vule['kcid']}' ");
				$bj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :sid = sid ", array(':sid' => $vule['bj_id']));
				$qh = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :sid = sid ", array(':sid' => $vule['xq_id']));				
				$list[$key]['icon'] = $scset['icon'];
				$list[$key]['kcname'] = $kc['name'];
				$list[$key]['bjname'] = $bj['sname'];
				$list[$key]['xqname'] = $qh['sname'];
			}
			include $this->template(''.$school['style3'].'/shoucelist');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>