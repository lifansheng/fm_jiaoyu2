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
		
		
		
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'post') {
            load()->func('tpl');
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id ", array(':id' => $id));
                if (empty($item)) {   
                    $this->imessage('抱歉，本条信息不存在在或是已经删除！', referer(), 'error');
                }
            }
        } elseif ($operation == 'display') {

            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
			if(!empty($_GPC['createtime'])) {
				$starttime = strtotime($_GPC['createtime']['start']);
				$endtime = strtotime($_GPC['createtime']['end']) + 86399;
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			} else {
				$starttime = strtotime('-200 day');
				$endtime = TIMESTAMP;
			}
			$params[':start'] = $starttime;
			$params[':end'] = $endtime;
			if(!empty($kcid1)){
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kcid='{$kcid1}'   $condition  ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
				 $total = pdo_fetchcolumn("SELECT * FROM " . tablename($this->table_courseorder) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND kcid='{$kcid1}'   $condition  ORDER BY createtime DESC");
            	$kecheng = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " where id = :id", array(':id' => $kcid1));
			}else{
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_courseorder) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'   $condition  ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
				 $total = pdo_fetchcolumn("SELECT * FROM " . tablename($this->table_courseorder) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  ORDER BY createtime DESC");
			}
				foreach($list as $index => $row){
					$kc = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $row['kcid']));
					$tid = !empty($kc['maintid'])?$kc['maintid']:$school['comtid'];
					$teacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE id = :id ", array(':id' => $tid));
					$checkgj = pdo_fetch("SELECT id FROM " . tablename($this->table_cyybeizhu_teacher) . " WHERE cyyid = :cyyid ", array(':cyyid' => $row['id']));
					$list[$index]['kcnanme'] = $kc['name'];
					$list[$index]['tname']	 = $teacher['tname'];
					$list[$index]['genjin']  = $checkgj['id'];
				}
          
            $pager = pagination($total, $pindex, $psize);
			
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                $this->imessage('抱歉，本条信息不存在在或是已经被删除！', referer(), 'error');
            }
            pdo_delete($this->table_courseorder, array('id' => $id));
            $this->imessage('删除成功！', referer(), 'success');
        }	
        include $this->template ( 'web/kcyy' );
?>