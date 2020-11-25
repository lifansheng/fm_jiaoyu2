<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹 
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $id = intval($_GPC['id']);
		$openid = $_W['openid'];
		$schoolid = intval($_GPC['schoolid']);
		$mastertid = intval($_GPC['mastertid']);//推广员TID
		$masteruid = $_GPC['masteruid']?$_GPC['masteruid']:$openid;//归属粉丝openid默认自己
		$masteruserid = $_GPC['masteruserid'];//归属用户ID
		$com_form = $_GPC['com_form']?$_GPC['com_form']:6;//默认无人推广粉丝自己点击
		$item = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $id));
		$nowtime = time();
		mload()->model('kc');
		$tuan = check_app('tuan',$weid,$schoolid);$tuiguang = check_app('tuiguang',$weid,$schoolid);$zhuli = check_app('zhuli',$weid,$schoolid);
		if($item['sale_type'] != 0 || ($tuan||$tuiguang||$zhuli)){
			SetFansInfoByKc($id,$openid,0,$mastertid,$masteruid,$masteruserid,$com_form);
			$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('hookcom', array('goto' => 'video_kc','kcid' => $id,'schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
		}
		$myAllStudent = get_myallclass_this_school($weid,$openid,$schoolid);
		$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
		$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :tid = tid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':tid' => 0), 'id');
		$its = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $_SESSION['user']));		
		$userinfo = iunserializer($its['userinfo']);
		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND schoolid=:schoolid AND id=:id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $its['sid']));
		$school = pdo_fetch("SELECT style1,tpic,spic,logo,content,title,address,tel  FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
        $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_kcbiao) . " WHERE weid = :weid AND schoolid =:schoolid AND kcid = :kcid  ORDER BY date ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':kcid' => $id));

      	foreach( $list as $key => $value ) {
      		$weekarray=array("日","一","二","三","四","五","六"); //先定义一个数组
			$list[$key]['week'] = $weekarray[date("w",$value['date'])];
			$SD = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid =:schoolid AND sid = :sid  ", array(':weid' => $weid, ':schoolid' => $schoolid, ':sid' => $value['sd_id']));
			$list[$key]['sdname'] = $SD['sname'];
			$list[$key]['number'] = $key+1;	
      	}
        $allnum = count($list);
        
		$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid,':is_over'=>"2"));
		$nj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid,':is_over'=>"2"));
		$bj_str_temp = '0,';
		foreach($bj as $key_b=>$value_b){
			$bj_str_temp .=$value_b['sid'].",";
		}
		$bj_str = trim($bj_str_temp,",");
		$nj_str_temp = '0,';
		foreach($nj as $key_n=>$value_n){
			$nj_str_temp .=$value_n['sid'].",";
		}
		$nj_str = trim($nj_str_temp,",");
		$others = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id != :id And weid=:weid And schoolid=:schoolid and is_show = :isshow And end > :timeEnd  and FIND_IN_SET(bj_id,:bj_str) and FIND_IN_SET(xq_id,:nj_str) ORDER BY  RAND() LIMIT 0,5 ", array(':id' => $id,':weid'=>$weid,':schoolid'=>$schoolid,':timeEnd'=>time(),':bj_str'=>$bj_str,':nj_str'=>$nj_str,':isshow'=>1));
		 
		$yb = pdo_fetchcolumn("select count(distinct sid) FROM ".tablename($this->table_order)." WHERE kcid = '".$id."' And status = 2 ");
		$addr = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = :sid ", array(':sid' => $item['adrr']));
		$item['yb'] = $yb + $item['yibao'];
		$item['address'] = $addr['sname'];
		$teacher_array =  explode(',', $item['tid']);
		$tid_array = array();
		foreach( $teacher_array as $key => $value )
		{
			$teacher = pdo_fetch("SELECT tname,id,thumb FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND id=:id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $value));
			$tid_array[$key]['tname']  = $teacher['tname'];
			$tid_array[$key]['tid']   = $teacher['id'];
			$tid_array[$key]['thumb'] = $teacher['thumb'];
		}
		if($item['is_dm'] ==1){
			$dm = array();
			$yblist = pdo_fetchall("select distinct sid FROM ".tablename($this->table_order)." WHERE kcid = '".$id."' And status = 2 ");
			foreach( $yblist as $key => $row ){
				$stuinfo = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where id=:id", array(':id' => $row['sid']));
				$dm[$key]['text']  = $stuinfo['s_name'].',已报名本课程';
				$dm[$key]['icon'] = !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
			}
			$dmlist = json_encode($dm);
		}
        $teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND id=:id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $item['tid']));
		$title = $item['title'];
        $category = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid =  :weid AND schoolid =:schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');
		$yb = pdo_fetchcolumn("select count(distinct sid) FROM ".tablename($this->table_order)." WHERE kcid = '".$id."' And status = 2 ");
		$rest = $item['minge'] - $yb;
		
		$isfull =false;
		
		if ($rest < 1){
			
		$isfull =true;
		
		}
		
		$km = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid =:schoolid And type =:type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $sid));
   		
        //include $this->template(''.$school['style1'].'/kcinfo');
        include $this->template(''.$school['style1'].'/coursedetail_new');
?>