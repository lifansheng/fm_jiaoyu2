<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
		global $_GPC, $_W;
		$action            = 'classkh';
		$this1             = 'no10';
		$schoolid          = intval($_GPC['schoolid']);
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
		$weid              = $_W['uniacid'];
		$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$tid_global = $_W['tid'];
		
		$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));

		if(empty($schoolid)){
			$this->imessage('非法操作!', referer(), 'error');
		}
		if($operation == 'post'){
			load()->func('tpl');
			$id = intval($_GPC['id']);
			
			$item = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_kh) . " WHERE id = :id", array(':id' => $id));
			
			if(checksubmit('submit')){
				$data = array(
					'weid'      => $weid,
					'schoolid'  => $schoolid,
					'classAdvert'     => trim($_GPC['classAdvert']),
					'bj_id'   => $_GPC['bj_id'],
					'createtime' => time()
				);
				
				$bjid = pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . "where weid = :weid AND schoolid = :schoolid And type = :type and sid=:sid",array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid,':sid'=>$_GPC['bj_id']));
				
				$checkkh=pdo_fetch("SELECT id FROM " . tablename($this->table_classcard_kh) . " WHERE  weid = :weid AND schoolid = :schoolid and bj_id=:bj_id ", array(':weid' => $weid, ':schoolid' => $schoolid,':bj_id' => $data['bj_id']));
				
				if(!empty($checkkh)){
					$this->imessage('该班级口号已存在！', referer(), 'error');
				}
				
				if(empty($bjid)){
					$this->imessage('班级不存在！', referer(), 'error');
				}
				
				if(empty($data['classAdvert'])){
					$this->imessage('请输入班级口号！', referer(), 'error');
				}
				if(empty($data['bj_id'])){
					$this->imessage("请选择班级", referer(), 'error');
					
				}				
				if($item && empty($id)){
					$this->imessage('抱歉！本班在本时间范围内已经添加了课表，请勿重复');
				}				
				
				if(empty($id)){
					pdo_insert($this->table_classcard_kh, $data);
                   
				}else{
					pdo_update($this->table_classcard_kh, $data, array('id' => $id));
                   
				}
                $urlMsg = '操作成功';
				
				$this->imessage($urlMsg, $this->createWebUrl('classkh', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
			}
		}elseif($operation == 'display'){
			$pindex    = max(1, intval($_GPC['page']));
			$psize     = 8;
			$condition = '';
			
			if(!empty($_GPC['bj_id'])){
				$bj_id     = $_GPC['bj_id'];
				$condition .= " AND bj_id = '{$bj_id}' ";
			}

			
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_classcard_kh) . " WHERE weid = '{$weid}' AND schoolid ={$schoolid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			
			foreach($list as $key => $value){
				$bjname=pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And sid = :sid ", array(':weid' => $weid, ':sid' =>$value['bj_id'] , ':schoolid' => $schoolid));
				$list[$key]['bj'] = $bjname['sname'];
			}	
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_classcard_kh) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
			$pager = pagination($total, $pindex, $psize);
							
		}elseif($operation == 'delete'){
			$tid = intval($_GPC['id']);
			$row = pdo_fetch("SELECT * FROM " . tablename($this->table_classcard_kh) . " WHERE id = :id", array(':id' => $tid));
			if(empty($row)){
				$this->imessage('抱歉，口号不存在或是已经被删除！', referer(), 'error');
			}
			
			
			pdo_delete($this->table_classcard_kh, array('id' => $tid));

			$urlMsg = '删除成功！';
			
			$this->imessage($urlMsg, $this->createWebUrl('classkh', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
		}
		include $this->template('web/classkh');
?>