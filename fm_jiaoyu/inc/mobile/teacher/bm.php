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
		$signid = $_GPC['id'];
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid_global = $it['tid'];
		if (!((IsHasQx($tid_global,2000702,2,$schoolid)) || (IsHasQx($tid_global,2000703,2,$schoolid)))){
			message('您无权查看本页面');
		}
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
		$picarrSet_out = unserialize($school['picarrset']);
		$textarrSet_out = unserialize($school['textarrset']);
		
		
	   if(!empty($userid['id'])){
	   
			$item = pdo_fetch("SELECT * FROM " . tablename($this->table_signup) . " where :id = id", array(':id' => $signid));
			$xueqi = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $item['nj_id']));
			$class = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $item['bj_id']));
			$order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where id = :id ", array(':id' => $item['orderid']));
			
			$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$item['nj_id']}' And type = 'theclass' ORDER BY ssort ASC");	
			
			include $this->template(''.$school['style3'].'/bm');
        }else{
			$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>