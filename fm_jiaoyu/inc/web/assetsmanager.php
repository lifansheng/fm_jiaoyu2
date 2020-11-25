<?php
  	global $_GPC, $_W;
	$weid = $_W['uniacid'];
	$action = 'assetsmanager';
	$this1 = 'no9';
	$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	$schoolid = intval($_GPC['schoolid']);
	$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
	$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));			
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	$tid_global = $_W['tid'];




	if($operation =='display' && !(IsHasQx($tid_global,1004301,1,$schoolid))){
		$operation = 'assetsuselog';
		$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl('chongzhi', array('schoolid' => $schoolid,'op'=>$operation));
		header("location:$stopurl");
	}
	if($operation =='assetsuselog' && !(IsHasQx($tid_global,1004311,1,$schoolid))){
		$operation = 'assetsfixlog';
		$stopurl = $_W['siteroot'] .'web/'.$this->createWebUrl('chongzhi', array('schoolid' => $schoolid,'op'=>$operation));
		header("location:$stopurl");
	}
	if($operation =='assetsfixlog' && !(IsHasQx($tid_global,1004321,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}


	if ($operation == 'display') {	
		if (!(IsHasQx($tid_global,1004301,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = '';
		if(!empty($_GPC['name'])){
			$condition = " AND name like '%{$_GPC['name']}%' ";
		}
		$list = pdo_fetchall(" SELECT * FROM ".GetTableName('assets')." WHERE schoolid = '{$schoolid}' $condition ORDER BY ssort DESC LIMIT ".($pindex - 1)*$psize." ,".$psize);
		foreach ($list as $key => $value) {
			$checkuse = pdo_fetchcolumn("SELECT sum(num) FROM ".GetTableName('assetstake')." WHERE id='{$value['id']}' and schoolid = '{$schoolid}' and status = 2  ");
			$restnum = $value['basicnum'] - $value['wastenum'] - $checkuse;
			$list[$key]['restnum'] = $restnum;
			$list[$key]['takenum'] = $checkuse ? $checkuse : 0;
		}
		$total =pdo_fetchcolumn("SELECT count(*) FROM ".GetTableName('assets')." WHERE schoolid = '{$schoolid}' $condition ");
		$pager = pagination($total, $pindex, $psize);	
	}elseif ($operation == 'post') {
		if (!(IsHasQx($tid_global,1004302,1,$schoolid))){
			$this->imessage('非法访问，您无权操作该页面','','error');	
		}
		$id = $_GPC['id'];
       if(!empty($id)){
		   $item = pdo_fetch(" SELECT * FROM ".GetTableName('assets')." WHERE id = '{$id}' ");
	   }
         if(checksubmit('submit')){
            if(empty($_GPC['assets_name'])){
                $this->imessage('抱歉，物品名称不能为空！', referer(), 'error');
            };
            if(empty( $_GPC['assetsthumb'])){
                $this->imessage('抱歉，缩略图不能为空！', referer(), 'error');
            };
            if(empty( $_GPC['content'])){
                $this->imessage('抱歉，描述不能为空！', referer(), 'error');
            };
            if(empty( $_GPC['basic_num'])){
                $this->imessage('抱歉，基础库存不能为空！', referer(), 'error');
            };
            if(empty( $_GPC['danwei'])){
                $this->imessage('抱歉，计量单位不能为空！', referer(), 'error');
            };
            $temp = array(
                'weid'     => $weid,
                'schoolid' => $schoolid,
                'name'     => trim($_GPC['assets_name']),
                'ssort'    => intval($_GPC['ssort']),
                'thumb'    => $_GPC['assetsthumb'],
                'type'     => $_GPC['assets_type'],
                'disc'     => trim($_GPC['content']),
                'basicnum' => trim($_GPC['basic_num']),
                'wastenum' => intval($_GPC['waste_num']),
                'danwei'   => $_GPC['danwei'],
            );
			if(!empty($id)){
				pdo_update(GetTableName('assets',false),$temp,array('id'=>$id));
			}else{
				pdo_insert(GetTableName('assets',false),$temp);
				//pdo_insert(GetTableName('assets',false),$temp);
			}
            $this->imessage('更新公物信息成功！', $this->createWebUrl('assetsmanager', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
        };
	}elseif($operation == 'delete'){
		$id = $_GPC['id'];
		if(!empty($id)){
			$check = pdo_fetch("SELECT id FROM ".GetTableName('assets')." WHERE id = '{$id}' ");
			if(!empty($check)){
				pdo_delete(GetTableName('assets',false),array('id'=>$id));
				$this->imessage('删除公物成功！', $this->createWebUrl('assetsmanager', array('op' => 'display','schoolid' => $schoolid,'weid' => $weid )), 'success');
			}else{
				$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
			}
		}else{
			$this->imessage('该条记录不存在或是已删除！', referer(), 'error');
		}
	}elseif ($operation == 'deleteall') {
		$rowcount = 0;
		$notrowcount = 0;
		foreach ($_GPC['idArr'] as $k => $id) {
			$id = intval($id);
			if (!empty($id)) {
				$check = pdo_fetch("SELECT * FROM " . GetTableName('assets'). " WHERE id = :id", array(':id' => $id));
				if (empty($goocheckds)) {
					$notrowcount++;
					continue;
				}
				pdo_delete(GetTableName('assets',false), array('id' => $id));
				$rowcount++;
			}
		}
		$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
		$data ['result'] = true;
		$data ['msg'] = $message;
		die (json_encode($data));
	}	

  // include $this->template ( 'web/aproomset' );
  include $this->template ( 'web/assetsmanager' );
?>