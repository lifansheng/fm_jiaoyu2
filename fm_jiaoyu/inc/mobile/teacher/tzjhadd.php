<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
		$openid = $_W['openid'];
		$schoolid = intval($_GPC['schoolid']); 
		$bj_id = intval($_GPC['bj_id']);
		$id = trim($_GPC['id']); 
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000901,2,$schoolid)) && !(IsHasQx($tid_global,2000911,2,$schoolid))){
			message('您无权查看本页面');
		}
		if($it){
			$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
			if($id){
				$PlanUid = $id;
				$palan = pdo_fetch("SELECT * FROM " . tablename($this->table_zjh) . " where weid = :weid AND schoolid = :schoolid  AND planuid = :planuid ", array(':weid' => $weid, ':schoolid' => $schoolid, ':planuid' => $PlanUid));
			}else{
				$PlanUid = getRandomString(8).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(4).'-'.getRandomString(12);
			}
			$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
			if(is_showgkk() && IsHasQx($tid_global,2000911,2,$schoolid) && $bj_id == -1 )
			    include $this->template(''.$school['style3'].'/tzjhadd_tea');
			else
                include $this->template(''.$school['style3'].'/tzjhadd');
		}else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
		}	
?>