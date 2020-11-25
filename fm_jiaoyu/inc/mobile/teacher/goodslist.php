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
		
		$tid = $_GPC['tid'];


		$goods = pdo_fetchall("SELECT * FROM " . tablename($this->table_mall) . " where :schoolid = schoolid And :weid = weid And showtype !=:showtype ORDER BY sort DESC, id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':showtype'=> 1 ));
		foreach( $goods as $key => $value )
		{
			$tempimg = unserialize($value['thumb']);
			$goods[$key]['thumb'] = $tempimg[0];
			$NumofSold = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_mallorder)." where weid = :weid And schoolid = :schoolid And goodsid = :goodsid",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':goodsid'=>$value['id']));
   		$goods[$key]['tqty']  = intval($goods[$key]['qty']) - $NumofSold;
		$goods[$key]['tsold']  = intval($goods[$key]['sold']) + $NumofSold;
		}
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		
		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
		
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));
		
        if(!empty($userid['id'])){
			include $this->template(''.$school['style3'].'/goodslist');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>