<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$userid_from = $_GPC['userid'];
		if($userid_from){
			$userid = $userid_from;
		}else{
			$userid = $_SESSION['user'];

		}
		$openid = $_W['openid'];
		
		
		$JFinfo = pdo_fetch("SELECT Is_point FROM " . tablename($this->table_index) . " WHERE :schoolid = id AND weid=:weid ", array(':schoolid' => $schoolid,':weid'=>$weid ));
		$goods = pdo_fetchall("SELECT * FROM " . tablename($this->table_mall) . " where :schoolid = schoolid And :weid = weid And showtype !=:showtype ORDER BY sort DESC, id ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':showtype'=> 0 ));
		foreach( $goods as $key => $value )
		{
			$tempimg = unserialize($value['thumb']);
			$goods[$key]['thumb'] = $tempimg[0];
			$NumofSold = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_mallorder)." where weid = :weid And schoolid = :schoolid And goodsid = :goodsid",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':goodsid'=>$value['id']));
   		$goods[$key]['tqty']  = intval($goods[$key]['qty']) - $NumofSold;
		$goods[$key]['tsold']  = intval($goods[$key]['sold']) + $NumofSold;
		}
        //查询是否用户登录
        		
		$userid_1 = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :id = id", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':id' => $userid));
		
		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid_1['id']));
		
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $it['sid']));
		
        if(!empty($userid_1['id'])){
			include $this->template(''.$school['style2'].'/sgoodslist');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>