<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action1           = 'malladd';
$this1             = 'no6';
$action            = 'malladd';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];

//默认op,商品列表
//操作： 读取商品表信息并展示（title，thumb，qty，price,points,text等）
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1002601,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$pindex    = max(1, intval($_GPC['page']));
	$psize     = 20;	
    $allgoods = pdo_fetchall("SELECT * FROM " . tablename($this->table_mall) . " WHERE weid = '{$weid}'  And schoolid = '{$schoolid}' ORDER BY sort DESC ,id ASC  LIMIT " . ($pindex - 1) * $psize . ',' . $psize);	
   foreach( $allgoods as $key => $value )
   {
   	$tempimg = unserialize($value['thumb']);
   	$allgoods[$key]['thumb']  = $tempimg[0];
   	$NumofSold = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_mallorder)." where weid = :weid And schoolid = :schoolid And goodsid = :goodsid",array(':weid'=>$weid, ':schoolid'=>$schoolid, ':goodsid'=>$value['id']));
   	$allgoods[$key]['tqty']  = intval($allgoods[$key]['qty']) - $NumofSold;
	$allgoods[$key]['tsold']  = intval($allgoods[$key]['sold']) + $NumofSold;
   }
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_mall) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'");
	$pager = pagination($total, $pindex, $psize);
	
	////////////////////////////////	
}
elseif ($operation == 'post' )
{
	if (!(IsHasQx($tid_global,1002602,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
	$goodsid = $_GPC['goodsid'];
	if(!empty($goodsid))
	{
		$goods = pdo_fetch("SELECT * FROM " . tablename($this->table_mall) . " WHERE id = '$goodsid'");
		$imagearr = iunserializer($goods['thumb']);
	}else{
        $goods = array(
            'sort' => 0
        );
    };
	 if(checksubmit('submit')){
		if(empty( $_GPC['gtitle'])){
			$this->imessage('抱歉，商品名称不能为空！', referer(), 'error');
		};
		if(empty( $_GPC['gthumb'])){
			$this->imessage('抱歉，商品缩略图不能为空！', referer(), 'error');
		};
		if(empty( $_GPC['gcontent'])){
			$this->imessage('抱歉，商品详情不能为空！', referer(), 'error');
		};
		if(empty( $_GPC['gqty'])){
			$this->imessage('抱歉，商品库存不能为空！', referer(), 'error');
		};
		
		if(!empty($_GPC['xsxg'])){
			$xsxg = $_GPC['xsxg'];
		}else{
			$xsxg = 0 ;
		}
		$imageInarr = iserializer($_GPC['gthumb']);
		$price = trim($_GPC['gnew_price']);
		$point = intval($_GPC['gpoint']);
		$temp = array(
			'weid'       => $weid,
			'schoolid'   => $schoolid,
			'title'      => trim($_GPC['gtitle']),
			'sort'       => intval($_GPC['gsort']),
			'thumb'      => $imageInarr,
			'content'    => trim($_GPC['gcontent']),
			'old_price'  => trim($_GPC['gold_price']),
			'new_price'  => trim($_GPC['gnew_price']),
			'points'     => intval($_GPC['gpoint']),
			'qty'        => intval($_GPC['gqty']),
			'sold'       => intval($_GPC['gsold']),
			'xsxg' 		 =>	$xsxg,
			'showtype'	 => $_GPC['showtype']
		);
		if($_GPC['showtype'] == 0){
			$temp['xsxg'] = 0;
		}
		if( $price == 0 && $point != 0){
			$temp['cop'] = 1;
		};
		if( $price != 0 && $point == 0){
			$temp['cop'] = 2;
		};
		if( $price != 0 && $point != 0){
			$temp['cop'] = 3;
		};
		if(empty($goodsid))
		{
			pdo_insert($this->table_mall, $temp);
		}else{
			pdo_update($this->table_mall,$temp,array('id'=>$goodsid));
		}
		$this->imessage('更新商品成功！', $this->createWebUrl('malladd', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
	};
}
elseif($operation == 'delete'){
	$goodsid = $_GPC['goodsid'];
	$check = pdo_fetchall("SELECT * FROM " . tablename($this->table_mallorder) . " WHERE goodsid = :goodsid And  status != '4' ",array(':goodsid' => $goodsid));
	if(!empty($check))
	{
		$this->imessage('抱歉，商品有未完成订单，不可删除！', referer(), 'error');
	}else if(empty($check))
	{
		pdo_delete($this->table_mall,array('id'=>$goodsid));
		 $this->imessage('删除商品成功！', $this->createWebUrl('malladd', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
	};
	
}
include $this->template('web/malladd');


	
?>
