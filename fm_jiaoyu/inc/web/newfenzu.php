<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

        global $_GPC, $_W;
		$weid = $_W['uniacid'];
		load()->func('tpl');

		$action = 'newfenzu';
        $this1 = 'no1';
        $GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
        $logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
        $member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
        $logo['logo'] = tomedia('headimg_'.$weid.'.jpg');
		$schoolinfo = pdo_fetchall("SELECT * FROM " . tablename($this->table_index) . " WHERE weid = :weid", array(':weid' => $weid), 'id');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';    
        if ($operation == 'display') {

            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;

			$condition = '';
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE '%{$_GPC['keyword']}%'";
            }
			
            $where = "WHERE weid = '{$weid}'";
            $fenzulist = pdo_fetchall("SELECT * FROM " . tablename($this->table_group) . " {$where} $condition ORDER BY ssort DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}");
            if (!empty($fenzulist)) {
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_group) . " $where");
                $pager = pagination($total, $pindex, $psize);  				
            }	

        } elseif ($operation == 'post') {
		    load()->func('tpl');
            $id = intval($_GPC['id']);
            $reply = pdo_fetch("select * from " . tablename($this->table_group) . " where id=:id and weid =:weid", array(':id' => $id, ':weid' => $weid));
			$schoollist = pdo_fetchall("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid ORDER BY ssort DESC", array(':weid' => $weid));
            if (checksubmit('submit')) {
                $data = array(
                    'weid' => intval($weid),
                    'schoolid' => intval($_GPC['schoolid']),
                    'name' => trim($_GPC['name']),
                    'group_desc' => trim($_GPC['group_desc']),
					'is_zhu' => intval($_GPC['is_zhu']),
					'ssort' => intval($_GPC['order']),
                    'createtime' => time(),
                );

                if (istrlen($data['name']) == 0) {
                    $this->imessage('没有输入分组名称.', '', 'error');
                }
                if (istrlen($data['name']) > 8) {
                    $this->imessage('分组名称不能多于8个字。', '', 'error');
                }
                if (empty($_GPC['schoolid'])) {
                    $this->imessage('请选择关联学校', '', 'error');
                }

                if (!empty($id)) {
                    unset($data['createtime']);				
                    pdo_update($this->table_group, $data, array('id' => $id, 'weid' => $weid));
                } else {
					$url = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token=%s";
					$weixindata = "{\"tag\":{\"name\":\"{$_GPC['name']}\"}}";
					$ret = $this->weixin_fans_group($url, $weixindata);
					$data["group_id"] = $ret['tag']['id'];
                    pdo_insert($this->table_group, $data);
                }
				$this->imessage('操作成功', $this->createWebUrl('newfenzu', array('op' => 'display')), 'success');
            }			
		} elseif ($operation == 'delete') {
		    $id = intval($_GPC['id']);
			if (empty($_GPC['group_id'])) {
				$this->imessage('group_id不得为空', $this->createWebUrl('newfenzu', array('op' => 'display')), 'error');
			}
			$url = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=%s";
			$weixindata = "{\"tag\":{\"id\":{$_GPC['group_id']}}}";
			$this->weixin_fans_group($url, $weixindata);
            pdo_delete($this->table_group, array('group_id' => $_GPC['group_id'], 'weid' => $weid));
            $this->imessage('删除成功', $this->createWebUrl('newfenzu', array('op' => 'display')), 'success');		
		} elseif ($operation == 'dellocal') {
            pdo_delete($this->table_group, array('weid' => $weid));
            $this->imessage('删除本地分组成功', $this->createWebUrl('newfenzu', array('op' => 'display')), 'success');				
		} else if ($operation == "sync") {
			$url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token=%s";
			$weixindata = "";
			$ret = $this->weixin_fans_group($url, $weixindata);
			if ($ret && $ret['tags']) {
				$data = array('weid' => $weid, 'name' => $_GPC['name'], 'group_desc' => $_GPC['group_desc']);
				foreach ($ret['tags'] as $group) {
					$groups = pdo_fetch("select * from " . tablename($this->table_group) . " where group_id=:group_id And weid =:weid", array(':group_id' => $group['id'], ':weid' => $weid));
					$data["name"] = $group['name'];
					$data["count"] = $group['count'];
					if($groups){
						pdo_update($this->table_group, $data, array('id' => $groups['id'], 'weid' => $weid));
					}else{
						$data["group_id"] = $group['id'];
						pdo_insert($this->table_group, $data);
					}
				}
				$this->imessage('同步成功', $this->createWebUrl('newfenzu'), 'success');
			} else {
				$this->imessage('同步失败', $this->createWebUrl('newfenzu'), 'fail');
			}
		}			
		
   include $this->template ( 'web/newfenzu' );
?>