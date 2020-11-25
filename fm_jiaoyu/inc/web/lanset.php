<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

        global $_GPC, $_W;
		$weid = $_W['uniacid'];
		load()->func('tpl');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';    
        if ($operation == 'display') {
			load()->func('tpl');
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_lanset) . " WHERE weid = '{$weid}' ORDER BY id DESC");
			foreach($list as $key =>$row){
				$school = pdo_fetch("SELECT title FROM " . tablename($this->table_index) . " WHERE id = '{$row['schoolid']}' ");
				$list[$key]['schoolname'] = $school['title'];
			}
        }else if ($operation == 'post') {
			$filename = MODULE_ROOT . '/model/lan.config.php';
			$pages = MODULE_ROOT . '/model/lan.inc.php';
			require $filename;
			require $pages;
			$id = intval($_GPC['id']);
			if($id){
				$item = pdo_fetch("SELECT * FROM " . tablename($this->table_lanset) . " WHERE id = '{$id}' ");
				$lanconfigs = json_decode($item['lanset'],true);
			}else{
				
			}
			$lanconfig = $config;
			$schoolist = pdo_fetchall("SELECT id,title FROM " . tablename($this->table_index) . " WHERE weid = '{$weid}' ");
			if(checksubmit('submit')){
				$set = array();
				foreach($_GPC['key'] as $key => $row){
					foreach($_GPC[$row] as $k =>$r){
						$set[$row][$r] = '';
						$set[$row][$r] = $_GPC['name'.$r];
					}
				}
				if(empty($_GPC['schoolid'])){
					$this->imessage('请选择学校', referer(), 'error');
				}
				$data = array(
					'weid' 		 => $weid,
					'schoolid'   => $_GPC['schoolid'],
					'lanset' 	 => json_encode($set)
				);
				if($id){
					pdo_update($this->table_lanset, $data, array('id' => $id));
				}else{
					pdo_insert($this->table_lanset, $data);
				}
				message('操作成功', $this->createWebUrl('lanset', array('op' => 'display')), 'success');
			}
		}elseif($operation == 'change'){
			$id    = intval($_GPC['id']);
			$is_on = intval($_GPC['is_on']);
			$data = array('is_on' => $is_on);
			pdo_update($this->table_lanset, $data, array('id' => $id));
			exit;
        }else if ($operation == 'delete') {
			$id = intval($_GPC['id']);
			if($id){
				pdo_delete($this->table_lanset, array('id' => $id));
				message('操作成功', $this->createWebUrl('lanset', array('op' => 'display')), 'success');
			}
        }else{
			message('操作失败, 非法访问.');
		}			
		
   include $this->template ( 'web/lanset' );
?>