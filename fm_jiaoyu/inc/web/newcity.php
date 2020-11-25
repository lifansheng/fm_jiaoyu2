<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_GPC, $_W;
        //$GLOBALS['frames'] = $this->getNaveMenu();
        $weid = $_W['uniacid'];  
        $action = 'newarea';
        $this1 = 'no1';
        $GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
        $logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
        $member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
        $logo['logo'] = tomedia('headimg_'.$weid.'.jpg');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            if (!empty($_GPC['ssort'])) {
                foreach ($_GPC['ssort'] as $id => $ssort) {
                    pdo_update($this->table_area, array('ssort' => $ssort), array('id' => $id));
                }
                $this->imessage('城市排序更新成功！', $this->createWebUrl('newcity', array('op' => 'display')), 'success');
            }
            $children = array();
            $city = pdo_fetchall("SELECT * FROM " . tablename($this->table_area) . " WHERE weid = '{$weid}' And type = 'city'  ORDER BY parentid ASC, ssort DESC");
            foreach ($city as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($city[$index]);
                }
            }
        } elseif ($operation == 'post') {
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $city = pdo_fetch("SELECT * FROM " . tablename($this->table_area) . " WHERE id = '{$id}'");
            } else {
                $city = array(
                    'ssort' => 0,
                );
            }

            if (checksubmit('submit')) {
                if (empty($_GPC['catename'])) {
                    $this->imessage('请输入城市名称！');
                }

                $data = array(
                    'weid' => $weid,
                    'name' => $_GPC['catename'],
                    'ssort' => intval($_GPC['ssort']),
                    'parentid' => intval($parentid),
					'type' => 'city',
                );


                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update($this->table_area, $data, array('id' => $id));
                } else {
                    pdo_insert($this->table_area, $data);
                    $id = pdo_insertid();
                }
                $this->imessage('更新城市成功！', $this->createWebUrl('newcity', array('op' => 'display')), 'success');
            }
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $city = pdo_fetch("SELECT id, parentid FROM " . tablename($this->table_area) . " WHERE id = '{$id}'");
            if (empty($city)) {
                $this->imessage('抱歉，城市不存在或是已经被删除！', $this->createWebUrl('newcity', array('op' => 'display')), 'error');
            }
            pdo_delete($this->table_area, array('id' => $id, 'parentid' => $id), 'OR');
            $this->imessage('城市删除成功！', $this->createWebUrl('newcity', array('op' => 'display')), 'success');
        }
   include $this->template ( 'web/newcity' );
?>