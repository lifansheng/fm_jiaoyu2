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
		$time = $_GPC['time'];
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid)); 		

		$sch_reviewtype = pdo_fetch("SELECT review_type FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ")['review_type'];
        if(!empty($userid['id'])){
			
			if($_GPC['op'] == 'ajax_get'){
				$qhid = $_GPC['qhid'];
				$njid = $_GPC['njid'];
				$back_result = GetRviewByQhAndNj($qhid,$njid,$schoolid);
				die(json_encode($back_result));
			}
			
			$xq = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");
			$qh = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'score' and is_review = 1 AND is_show_qh = 1 ORDER BY ssort DESC");
			
			if(!empty($_GPC['search_qh'])){
				$this_qhid = $_GPC['search_qh'];
			}else{
				$this_qhid =$qh[0]['sid'];
			}
			$this_qhinfo =  pdo_fetch("SELECT sid,sname,qhtype,qh_bjlist,addedinfo,is_review FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$this_qhid}' And type = 'score' ");
			if($this_qhinfo['is_review'] == 1){
				$this_addinfo = json_decode($this_qhinfo['addedinfo'],true);
				$subarr = explode(',', $this_addinfo['sub_arr']);
				$rowcount = count($subarr) + 1;
			}else{
				$rowcount = 1;
			}

			
			if(!empty($_GPC['search_nj'])){
				$this_njid = $_GPC['search_nj'];
			}else{
				$this_njid =$xq[0]['sid'];
			}
			$this_njinfo =  pdo_fetch("SELECT sid,sname,qhtype,qh_bjlist,addedinfo,is_review FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} and sid='{$this_njid}' And type = 'semester' ");
			$back_result = GetRviewByQhAndNj($this_qhid,$this_njid,$schoolid);	
		
			include $this->template(''.$school['style3'].'/chengjireview');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>