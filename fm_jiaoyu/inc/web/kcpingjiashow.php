<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_GPC, $_W;
        
        $weid = $_W['uniacid'];
        $action = 'kecheng';
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	    $schoolid = intval($_GPC['schoolid']);
		$kcid1 = intval($_GPC['kcid']);
		$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ORDER BY ssort DESC", array(':id' => $schoolid));
		$kecheng = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where id = :id", array(':id' => $kcid1));
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        $tid_global = $_W['tid'];
       	if ($operation == 'display') {
			if (!(IsHasQx($tid_global,1000911,1,$schoolid))){
				$this->imessage('非法访问，您无权操作该页面','','error');	
			}
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
			$params[':start'] = $starttime;
			$params[':end'] = $endtime;
			
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_kcpingjia) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND type = 2 AND kcid='{$kcid1}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
				foreach($list as $index => $row){
					$user = pdo_fetch("SELECT userinfo,pard FROM " . tablename($this->table_user) . " WHERE id = :id ", array(':id' => $row['userid']));
					$student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " WHERE id = :id ", array(':id' => $row['sid']));
					$list[$index]['s_name'] = $student['s_name'];
					$list[$index]['userinfo'] = $user['userinfo'];
					$list[$index]['pard'] = $user['pard'];
				}
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_kcpingjia) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'AND type = 2 AND kcid='{$kcid1}'  ");
            $pager = pagination($total, $pindex, $psize);
			
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！', referer(), 'error');
            }
            pdo_delete($this->table_kcpingjia, array('id' => $id));
            $this->imessage('删除成功！', referer(), 'success');
        }  elseif ($operation == 'deleteall') {
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                if (!empty($id)) {
                    $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_kcpingjia) . " WHERE id = :id", array(':id' => $id));
                    if (empty($goods)) {
                        $notrowcount++;
                        continue;
                    }
                    pdo_delete($this->table_kcpingjia, array('id' => $id, 'weid' => $weid));
                    $rowcount++;
                }
            }			
			$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
			$data ['result'] = true;
			$data ['msg'] = $message;
			die (json_encode($data));
        }	
        include $this->template ( 'web/kcpingjiashow' );
?>