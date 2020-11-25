<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
		global $_GPC, $_W;

		$weid              = $_W['uniacid'];
		$action            = 'check';
		$this1             = 'no11';
		$schoolid          = intval($_GPC['schoolid']);
		$logo              = pdo_fetch("SELECT logo,title,gonggao FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		$hasmacid = pdo_fetchall("SELECT macid FROM ".GetTableName('xzmacgroup')." WHERE schoolid = '{$schoolid}' ");
		$condition = '';
		if($hasmacid){
			$macidstr = arrayToString($hasmacid);
			$condition = " AND NOT FIND_IN_SET(id,'{$macidstr}')";
		}
		$sblist = pdo_fetchall("SELECT name,id FROM " . tablename($this->table_checkmac) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND macname = 2 $condition ORDER BY id DESC");
		$sbmacid = arrayToString(array_column($sblist,'id'));
		if($operation == 'display'){
			$pindex    = max(1, intval($_GPC['page']));
			$psize     = 15;
			$list = pdo_fetchall("SELECT * FROM " . GetTableName('xzmacgroup') . "  WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			foreach ($list as $key => $value) {
				$macname = pdo_fetchall("SELECT name FROM ".GetTableName('checkmac')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(id,'{$value['macid']}') ");
				$list[$key]['maclist'] = $macname;
			}
		}elseif($operation == 'GetGroupInfo'){
			$hasmac = [];
			$hasnomac = [];
			if($_GPC['id']){
				$groupInfo = pdo_fetch("SELECT name,id,macid,time FROM " . GetTableName('xzmacgroup') . "  WHERE id = '{$_GPC['id']}' AND schoolid = '{$schoolid}' ");
				$hasmac = pdo_fetchall("SELECT name,id,1 as hasmac FROM ".GetTableName('checkmac')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(id,'{$groupInfo['macid']}')");
			}else{
				$groupInfo = array(
					array(
						'name' => '',
						'id' => '',
						'macid' => [],
						'time' => '',
					),
				);
			}
			$hasnomac = pdo_fetchall("SELECT name,id,0 as hasmac FROM ".GetTableName('checkmac')." WHERE schoolid = '{$schoolid}' AND FIND_IN_SET(id,'{$sbmacid}')");
			$groupInfo['maclist'] = array_merge($hasmac,$hasnomac);
			$result['data'] = $groupInfo;
			die(json_encode($result));
		}elseif($operation == 'saveGroup'){
			if(!$_GPC['macid']){
				$result['msg'] = '无设备,添加失败';
				$result['result'] = false;
				die(json_encode($result));
			}
			$data = array(
				'weid' => $weid,
				'schoolid' => $schoolid,
				'name' => $_GPC['name'],
				'macid' => arrayToString($_GPC['macid']),
				'createtime' => time(),
				'time' => $_GPC['time'],
			);
			if($_GPC['id']){ //修改
				pdo_update(GetTableName('xzmacgroup',false),$data,array('id'=>$_GPC['id']));
				$result['msg'] = '修改成功';
				$result['result'] = true;
			}else{ //新增
				pdo_insert(GetTableName('xzmacgroup',false),$data);
				$result['msg'] = '添加成功';
				$result['result'] = true;
			}
			die(json_encode($result));
		}elseif($operation == 'delete'){
			$id       = intval($_GPC['id']);
			$checklog = pdo_fetch("SELECT id FROM " . GetTableName('xzmacgroup') . " WHERE id = '{$id}' ");
			if(empty($checklog)){
				$this->imessage('抱歉，不存在或是已经被删除！', $this->createWebUrl('xz_group', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
			}
			pdo_delete(GetTableName('xzmacgroup',false), array('id' => $id));
			$this->imessage('删除成功！', $this->createWebUrl('xz_group', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
		}
		include $this->template('web/xz_group');
?>