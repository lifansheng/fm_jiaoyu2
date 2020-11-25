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
		$leaveid = $_GPC['id'];
        $fzstr = GetFzByQx('shjsqj',2,$schoolid);
        $fzarr = explode(',',$fzstr);
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $userid['id']));	
		$leave = pdo_fetch("SELECT * FROM " . tablename($this->table_leave) . " where :id = id", array(':id' => $leaveid));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		if($leave['more_less'] == 2){
			$time_t =$leave['endtime1'] - $leave['startime1'] + 1;
			$leave['ksnum'] = $time_t/86400;		
			
			
		}
		$xiaozhang = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid And id=:tid AND schoolid=:schoolid", array(':tid'=>$leave['cltid'],':weid' => $weid, ':schoolid' => $schoolid));				
        if(!empty($userid['id'])){
            
			$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $leave['tid']));
			$member = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid = :uid", array(':uniacid' => $_W ['uniacid'], ':uid'=> $leave['uid']));
			$isxz = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));
			
		 include $this->template(''.$school['style3'].'/tmcomet');
          }else{
         	session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
          }        
?>