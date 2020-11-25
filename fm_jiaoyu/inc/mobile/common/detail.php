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
		$act = "sy";
		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid), 'id');		
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id= :id", array(':weid' => $weid, ':id' => $schoolid));
        $title = $school['title'];
		$user = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$ite = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $user['id']));	
		$isxz = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $ite['tid']));
		//查询科目设置
	    $km = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid =:schoolid And type =:type ORDER BY ssort DESC", array(':weid' => $_W['uniacid'], ':type' => 'subject', ':schoolid' => $schoolid));
	    $it = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $sid));
        if (empty($school)) {
            message('没有找到该学校，请联系管理员！');
        }
		$icon1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = {$weid} AND schoolid= '{$schoolid}' AND status=1 AND place=1 AND ssort<5 ORDER BY ssort ASC");
		$icon2 = pdo_fetchall("SELECT * FROM " . tablename($this->table_icon) . " where weid = {$weid} AND schoolid= '{$schoolid}' AND status=1 AND place=1 AND ssort>4 ORDER BY ssort ASC");
		$links = $_W['siteroot'] .'app/'.$this->createMobileUrl('detail', array('schoolid' => $schoolid));
		$list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_news) . " where (:schoolid = schoolid OR FIND_IN_SET('{$schoolid}',schoolidstr)) And :weid = weid And :type = type ORDER BY id DESC,displayorder DESC LIMIT 0,4", array(
		         ':weid' => $_W['uniacid'],
				 ':schoolid' => $schoolid,
				 ':type' => 'article'
				 ));
		$list2 = pdo_fetchall("SELECT * FROM " . tablename($this->table_news) . " where (:schoolid = schoolid OR FIND_IN_SET('{$schoolid}',schoolidstr)) And :weid = weid And :type = type ORDER BY id DESC,displayorder DESC LIMIT 0,4", array(
		         ':weid' => $_W['uniacid'],
				 ':schoolid' => $schoolid,
				 ':type' => 'news'
				 ));
		$list3 = pdo_fetchall("SELECT * FROM " . tablename($this->table_news) . " where (:schoolid = schoolid OR FIND_IN_SET('{$schoolid}',schoolidstr)) And :weid = weid And :type = type ORDER BY id DESC,displayorder DESC LIMIT 0,4", array(
		         ':weid' => $_W['uniacid'],
				 ':schoolid' => $schoolid,
				 ':type' => 'wenzhang'
				 ));
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

		//  $list_jpk = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE weid=:weid And schoolid=:schoolid And is_hot =:is_hot  And end > :timeEnd and is_show = :is_show and FIND_IN_SET(bj_id,:bj_str) and FIND_IN_SET(xq_id,:nj_str) ORDER BY  RAND() LIMIT 0,5 ", array(':weid'=>$weid,':schoolid'=>$schoolid,'is_hot'=>1,':timeEnd'=>TIMESTAMP,':bj_str'=>$bj_str,':nj_str'=>$nj_str,':is_show'=> 1 ));	
		 $list_jpk = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE weid=:weid And schoolid=:schoolid And is_hot =:is_hot  And end > :timeEnd and is_show = :is_show ORDER BY id DESC LIMIT 0,5 ", array(':weid'=>$weid,':schoolid'=>$schoolid,'is_hot'=>1,':timeEnd'=>TIMESTAMP,':is_show'=> 1 ));	

		 if($list_jpk){
			foreach ($list_jpk as $key => $value) {
				if($value['guigetype'] == 1){
					$guige = pdo_fetch("SELECT price,ksnum FROM ".GetTableName('guige')." WHERE kcid = '{$value['id']}' ORDER BY price ");
					$list_jpk[$key]['cose'] = $guige['price'];
				}
			}
		}

		// $list_tuijian = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE weid=:weid And schoolid=:schoolid And is_tuijian =:is_tuijian  And end > :timeEnd  and is_show = :is_show and FIND_IN_SET(bj_id,:bj_str) and FIND_IN_SET(xq_id,:nj_str) ORDER BY  RAND() LIMIT 0,3 ", array(':weid'=>$weid,':schoolid'=>$schoolid,'is_tuijian'=>1,':timeEnd'=>TIMESTAMP,':bj_str'=>$bj_str,':nj_str'=>$nj_str,':is_show'=> 1));	
		$list_tuijian = pdo_fetchall("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE weid=:weid And schoolid=:schoolid And is_tuijian =:is_tuijian  And end > :timeEnd  and is_show = :is_show ORDER BY id DESC LIMIT 0,3 ", array(':weid'=>$weid,':schoolid'=>$schoolid,'is_tuijian'=>1,':timeEnd'=>TIMESTAMP,':is_show'=> 1));	
		if($list_tuijian){
			foreach ($list_tuijian as $key => $value) {
				if($value['guigetype'] == 1){
					$guige = pdo_fetch("SELECT price,ksnum FROM ".GetTableName('guige')." WHERE kcid = '{$value['id']}' ORDER BY price");
					$list_tuijian[$key]['cose'] = $guige['price'];
				}
			}
		}

        $item1 = pdo_fetch("SELECT * FROM " . tablename($this->table_news) . " WHERE id = :id ", array(':id' => $id));		

		$banners = pdo_fetchall("SELECT * FROM " . tablename($this->table_banners) . " WHERE enabled = 1 AND weid = '{$_W['uniacid']}' ORDER BY leixing DESC, displayorder ASC");
		$barr = array();
		foreach ($banners as $key => $banner) {
			if ($banner['leixing'] == 1) {
				$uniarr = explode(',',$banner['arr']); 
				$is = $this->uniarr($uniarr,$schoolid);
				if ($is && TIMESTAMP >= $banner['begintime'] && TIMESTAMP < $banner['endtime']) {
					$barr[$banner['leixing'].$key] = $banner;
				}
			}else{
				if ($banner['schoolid'] == $schoolid) {
					$barr[$banner['leixing'].$key] = $banner;
				}
			}
		}
		//arsort($barr);
        include $this->template(''.$school['style1'].'/detail');
?>