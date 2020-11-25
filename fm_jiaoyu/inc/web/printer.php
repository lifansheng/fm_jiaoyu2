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
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		
		$tid_global = $_W['tid'];

		if($tid_global !='founder' && $tid_global != 'owner'){		
			$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
			$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
			if( !(strstr($qxarr,'100301')) && strstr($qxarr,'100302')) {
				$operation = 'printset';
			}
						
		}
        if ($operation == 'post') {
			
			if (!(IsHasQx($tid_global,1003012,1,$schoolid))){
				$this->imessage('非法访问，您无权操作该页面','','error');	
			}
			
            load()->func('tpl');
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename($this->table_printer) . " WHERE id = :id ", array(':id' => $id));
                if (empty($item)) {   
                    $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
                }
            }
            if (checksubmit('submit')) {
				$data['weid'] = $weid;
				$data['schoolid'] = $schoolid;
				$data['type'] = trim($_GPC['type']);
				$data['status'] = intval($_GPC['status']); 
				$data['name'] = !empty($_GPC['name']) ? trim($_GPC['name']) : $this->imessage('打印机名称不能为空', '', 'error');
				$data['print_no'] = !empty($_GPC['print_no']) ? trim($_GPC['print_no']) : $this->imessage('机器号不能为空', '', 'error');
				$data['key'] = trim($_GPC['key']);
				$data['api_key'] = trim($_GPC['api_key']);
				$data['member_code'] = trim($_GPC['member_code']);
				if($data['type'] == 'yilianyun') {
					$data['member_code'] = trim($_GPC['userid']);
				}
				$data['print_nums'] = intval($_GPC['print_nums']) ? intval($_GPC['print_nums']) : 1;
				if(!empty($_GPC['qrcode_link']) && (strexists($_GPC['qrcode_link'], 'http://') || strexists($_GPC['qrcode_link'], 'https://'))) {
					$data['qrcode_link'] = trim($_GPC['qrcode_link']);
				}
				$data['print_header'] = trim($_GPC['print_header']);
				$data['print_footer'] = trim($_GPC['print_footer']);
                if (!empty($id)) {
                    pdo_update($this->table_printer, $data, array('id' => $id));
                } else {
					$data['createtime'] = time();
                    pdo_insert($this->table_printer, $data);
                }
                $this->imessage('操作成功', $this->createWebUrl('printer', array('op' => 'display','schoolid' => $schoolid)), 'success');
            }
        } elseif ($operation == 'printset') {
		   if (!(IsHasQx($tid_global,1003021,1,$schoolid))){
				$this->imessage('非法访问，您无权操作该页面','','error');	
			}
			
            load()->func('tpl');
			mload()->model('print');
			$allprints = printers($schoolid);
			$printer_name = printer_name();
			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_printset) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  ORDER BY id ASC  ");
			foreach($list as $index => $row){
				if(strpos($row['printarr'],',')){
					$printarrs = explode(',',$row['printarr']);
					$key_last = key($printarrs);
					$list[$index]['printcount'] = count($printarrs);
					foreach($printarrs as $k =>$r){
						$printer = pdo_fetch("SELECT name FROM " . tablename($this->table_printer) . " WHERE id = :id ", array(':id' => $r));
						if ($k == $key_last && $printer) {
							$list[$index]['printnames'] .= $printer['name'].'，';
						}else{
							$list[$index]['printnames'] .= $printer['name'];
						}
					}
				}else{
					$printer = pdo_fetch("SELECT name FROM " . tablename($this->table_printer) . " WHERE id = :id ", array(':id' => $row['printarr']));
					$list[$index]['printnames'] = $printer['name'];
					$list[$index]['printcount'] = 1;
				}
				if($row['ordertype'] ==1){
					$list[$index]['typename'] = '课程付费';
				}
				if($row['ordertype'] ==3){
					$list[$index]['typename'] = '自定义缴费';
				}
				if($row['ordertype'] ==4){
					$list[$index]['typename'] = '报名缴费';
				}
				if($row['ordertype'] ==5){
					$list[$index]['typename'] = '考勤卡费';
				}
				if($row['ordertype'] ==6){
					$list[$index]['typename'] = '商城订单';
				}
				if($row['ordertype'] ==7){
					$list[$index]['typename'] = empty($school['videoname'])?$school['videoname']:'直播监控';
				}
				if($row['ordertype'] ==8){
					$list[$index]['typename'] = '余额充值';
				}
			}
            if (checksubmit('submit')) {
				foreach($_GPC['ordertype'] as $key => $row){
					$id = trim($_GPC['id'][$key]);
					$printarr 	  = rtrim($_GPC['printarr'][$key],",");
					$print_nums = intval($_GPC['print_nums'][$key]);
					$print_header = trim($_GPC['print_header'][$key]);
					$print_footer = trim($_GPC['print_footer'][$key]);
					$qrcode_link  = trim($_GPC['qrcode_link'][$key]);
					$data = array(
						'weid'     => $weid,
						'schoolid' => $_GPC['schoolid'],
						'ordertype'    => $row,
						'printarr'    => $printarr,
						'print_nums'   => $print_nums,
						'print_header' => $print_header,
						'print_footer'     => $print_footer,
						'qrcode_link'       => $qrcode_link
					);
					if (!empty($id)) {
						pdo_update($this->table_printset, $data, array('id' => $id));
					} else {
						pdo_insert($this->table_printset, $data);
					}
				}
                $this->imessage('操作成功', $this->createWebUrl('printer', array('op' => 'printset','schoolid' => $schoolid)), 'success');
            }
        } elseif ($operation == 'display') {
			
		   if (!(IsHasQx($tid_global,1003011,1,$schoolid))){
				$this->imessage('非法访问，您无权操作该页面','','error');	
			}
			
			mload()->model('print');
			$mactype = print_printer_types();			
            $pindex = max(1, intval($_GPC['page']));
            $psize = 15;
            $condition = '';
            if (!empty($_GPC['type'])) {
                $condition .= " AND type = '{$_GPC['type']}'";
            }

            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_printer) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
				foreach($list as $index => $row){
					foreach($mactype as $k => $r){
						if($row['type'] == $k){
							$list[$index]['macname'] = $r['text'];
							$list[$index]['css'] = $r['css'];
						}
					}
					if(!empty($row['print_no'])) {
						if(in_array($row['type'], array('feie', '365','feiyin'))) {
							$list[$index]['status_cn'] = print_query_printer_status($row['type'], $row['print_no'], $row['key'], $row['member_code']);
						} else {
							$list[$index]['status_cn'] = '不支持查询';
						}
					} else {
						$row['status_cn'] = '未知';
					}
					$print_times = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_print_log) . " WHERE pid = '{$row['id']}' AND schoolid = '{$schoolid}' ");
					$list[$index]['print_times'] = $print_times;
				}
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_printer) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");
            $pager = pagination($total, $pindex, $psize);
		}elseif($operation == 'change'){
			$id    = intval($_GPC['id']);
			$status = intval($_GPC['status']);
			$data = array('status' => $status);
			pdo_update($this->table_printer, $data, array('id' => $id));
			exit;
		}elseif($operation == 'changeprintset'){
			$id    = intval($_GPC['id']);
			$status = intval($_GPC['status']);
			$data = array('status' => $status);
			pdo_update($this->table_printset, $data, array('id' => $id));
			exit;
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
            }
            pdo_delete($this->table_printer, array('id' => $id));
            $this->imessage('操作成功！', referer(), 'success');
        }	
        include $this->template ( 'web/printer' );
?>