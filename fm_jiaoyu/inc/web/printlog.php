<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_GPC, $_W;
       
        $weid = $_W['uniacid'];
        $action = 'printlog';
		$this1 = 'no4';
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	    $schoolid = intval($_GPC['schoolid']);
		$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));	 $tid_global = $_W['tid'];		
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
 
        if ($operation == 'display') {
			
		  
			mload()->model('print');
			$types = print_printer_types();
			$printers = pdo_fetchall("SELECT id,name FROM " . tablename($this->table_printer) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY createtime DESC ");
            $pindex = max(1, intval($_GPC['page']));
            $psize = 15;
            $condition = '';
            if (!empty($_GPC['pid'])) {
                $condition .= " AND pid = '{$_GPC['pid']}'";
            }
            if (!empty($_GPC['oid'])) {
                $condition .= " AND oid = '{$_GPC['oid']}'";
            }
            if (!empty($_GPC['type'])) {
                $condition .= " AND printer_type = '{$_GPC['type']}'";
            }			
			if(!empty($_GPC['createtime'])) {
				$starttime = strtotime($_GPC['createtime']['start']);
				$endtime = strtotime($_GPC['createtime']['end']) + 86399;
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			} else {
				$starttime = strtotime('-600 day');
				$endtime = TIMESTAMP;
			}
			$params[':start'] = $starttime;
			$params[':end'] = $endtime;
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_print_log) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
				foreach($list as $index => $row){
					$printer = pdo_fetch("SELECT * FROM " . tablename($this->table_printer) . " WHERE id = '{$row['pid']}' AND schoolid = '{$schoolid}' ");
					if(!empty($printer['print_no'])) {
						if(in_array($printer['type'], array('feie','feiyin','365','AiPrint'))) {
							$list[$index]['status_cn'] = print_query_order_status($printer['type'], $printer['print_no'], $printer['key'], $printer['member_code'],$row['foid']);
						} else {
							$list[$index]['status_cn'] = '打印机不支持查询状态';
						}
					} else {
						$row['status_cn'] = '未知';
					}
				}
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_print_log) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
            $pager = pagination($total, $pindex, $psize);			
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
            pdo_delete($this->table_print_log, array('id' => $id));
            $this->imessage('操作成功！', referer(), 'success');
        } elseif ($operation == 'deleteall') {
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                if (!empty($id)) {
                    $log = pdo_fetch("SELECT * FROM " . tablename($this->table_print_log) . " WHERE id = :id", array(':id' => $id));
                    if (empty($log)) {
                        $notrowcount++;
                        continue;
                    }
                    pdo_delete($this->table_print_log, array('id' => $id));
                    $rowcount++;
                }
            }
			$message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";
			$data ['result'] = true;
			$data ['msg'] = $message;
			die (json_encode($data));
        }	
        include $this->template ( 'web/printlog' );
?>