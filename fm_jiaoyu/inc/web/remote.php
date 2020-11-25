<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
		global $_GPC, $_W;

		$weid              = $_W['uniacid'];
		$action            = 'check';
		$this1             = 'no5';
		$schoolid          = intval($_GPC['schoolid']);
		$logo              = pdo_fetch("SELECT logo,title,gonggao FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
		load()->func('tpl');
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if(!empty($_W['setting']['remote']['type'])){
			$urls = $_W['attachurl'];
		}else{
			$urls = $_W['siteroot'] . 'attachment/';
		}
		$pid=$_GPC['pid'];
		if($operation == 'display'){

			$list = pdo_fetchall("SELECT * FROM " . tablename('wx_school_checkmac_remote') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and pid='{$pid}' ORDER BY id DESC");
			foreach($list as $key => $row){
				
				$list[$key]['cameras'] = unserialize($row['cameras']);
				
			}
		//var_dump($list);die;
		}elseif($operation == 'edit'){

			$id     = intval($_GPC['id']);
			$device   = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE id = {$pid} ");
			
			if(!empty($id)){
				
				$item   = pdo_fetch("SELECT * FROM " . tablename('wx_school_checkmac_remote') . " WHERE id = {$id} ");
				$cameras=unserialize($item['cameras']);
				}
			
			if($_GPC['submit']){
				
				$data=array(
					'weid'=>$weid,
					'pid'=>$pid,
					'schoolid'=>$schoolid,
					'deviceId'=>$_GPC['deviceId'],
					'passType'=>$_GPC['passType'],
					'passDeviceId'=>$_GPC['passDeviceId'],
					'cameras'=>serialize($_GPC['cameras']),
				);
			    if(empty($id)){
					//print_r($data);die;
					$res=pdo_insert('wx_school_checkmac_remote',$data);
					}else{
						$res=pdo_update('wx_school_checkmac_remote', $data, array('id' => $id));
					}
			    
				if($res) message('编辑成功',$this->createWebUrl('remote', array('schoolid' => $schoolid,'pid' => $pid)),'success');
			}

			

		}elseif($operation == 'del'){

			$id     = intval($_GPC['id']);
			$res=pdo_delete('wx_school_checkmac_remote',array('id' => $id));
			if($res) message('删除成功',$this->createWebUrl('remote', array('schoolid' => $schoolid,'pid' => $pid)),'success');
			}else{
			$this->imessage('请求方式不存在');
		}
		include $this->template('web/remote');
?>