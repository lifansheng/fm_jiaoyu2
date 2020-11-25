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
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
						
        if(!empty($userid['id'])){
			 if(is_showgkk())
			 {
			        $njzr = GetNjzr($it['tid']);
			       // var_dump($njzr);
			       $is_njzr = is_njzr($it['tid']);
				   if(!$is_njzr){
					   $is_njzr = 0 ;
				   }
			     // var_dump($is_njzr);
			 }else{
				 $is_njzr = 1 ;
			 }
			$fzstr = GetFzByQx('shjsqj',2,$schoolid);
            $xzlist = pdo_fetchall("SELECT tname,openid,id FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND status=:status", array(':weid' => $weid, ':schoolid' => $schoolid, ':status' => 2)); 
            $shlist = pdo_fetchall("SELECT tname,openid,id FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND FIND_IN_SET(fz_id,:fzstr) AND is_show = :is_show", array(':weid' => $weid, ':schoolid' => $schoolid, ':fzstr' => $fzstr,':is_show'=>0)); 
			$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $it['tid']));
			$item = pdo_fetch("SELECT * FROM " . tablename ( 'mc_members' ) . " where uniacid = :uniacid AND uid=:uid ", array(':uid' => $it['uid'], ':uniacid' => $weid)); 
			$teacher_classlist = pdo_fetchall("SELECT * FROM " . tablename ($this->table_class) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' and tid = '{$it['tid']}' and type = 1 ORDER BY id DESC");
			foreach($teacher_classlist as $key=>$row){
				$bj_name = pdo_fetch("SELECT sname FROM " . tablename ($this->table_classify) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$row['bj_id']}' and type='theclass' ");
				$km_name = pdo_fetch("SELECT sname FROM " . tablename ($this->table_classify) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' and sid = '{$row['km_id']}' and type='subject' ");
				$teacher_classlist[$key]['bj_name'] = $bj_name['sname'];
				$teacher_classlist[$key]['km_name'] = $km_name['sname'];				
				
			}
		    $userinfo = iunserializer($it['userinfo']);
		    
		 include $this->template(''.$school['style3'].'/qingjia');
          }else{
     		session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
          }        
?>