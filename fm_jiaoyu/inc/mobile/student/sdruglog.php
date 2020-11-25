<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$id = $_GPC['id'];
        $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
        //查询是否用户登录
        $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		//喂药动作列表
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));  
		if(!empty($it['id'])){
			if($operation == 'display'){
				$drug = pdo_fetch(" SELECT starttime,endtime FROM ".GetTableName('drug')." WHERE id = {$_GPC['id']}");
				$condition = '';
				$starttime = $drug['starttime'];
        		$endtime = $drug['endtime'];
				$condition .= " AND (datetime >= '{$starttime}' AND datetime <= '{$endtime}')";
		
				$drugloglist = pdo_fetchall(" SELECT * FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND sid = '{$it['sid']}' AND drugid = '{$id}' $condition ORDER BY datetime ASC LIMIT 0,6  ");
				//总数
				$all = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND sid = '{$it['sid']}' AND drugid = '{$id}' $condition ");
				//未处理
				$NoDeal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}'  AND sid = '{$it['sid']}' and status = 0 AND drugid = '{$id}' $condition ");
				//已处理
				$HasDeal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND sid = '{$it['sid']}' and status = 1 AND drugid = '{$id}' $condition ");
		
				foreach($drugloglist as $key => $value){
					$student = pdo_fetch("SELECT s_name,bj_id,xq_id,icon FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and id = '{$value['sid']}' ");
					$teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and id = '{$value['tid']}' ");
					$drugsqinfo = pdo_fetch("SELECT * FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' and id = '{$value['drugid']}'  ");
					$drugloglist[$key]['sname'] = $student['s_name'];
					$drugloglist[$key]['updatetime'] = $value['updatetime'] ? date("Y-m-d H:i:s",$value['updatetime']) : '尚未开始喂药';
					$drugloglist[$key]['tname'] = $teacher['tname'];
					$drugloglist[$key]['icon'] = $student['icon'];
					$drugloglist[$key]['info'] = $drugsqinfo;
				}
				include $this->template(''.$school['style2'].'/sdruglog');
				exit;
			}elseif($operation == 'scroll_more'){
				$nowday = false;
				$time = $_GPC['LiData']['time'] ? $_GPC['LiData']['time'] : -1 ;
				$ctype = $_GPC['LiData']['ctype'];
				$limit_start = $time + 1;
				
				if(!empty($_GPC['starttime']) && !empty($_GPC['endtime']) ) {
					$starttime = strtotime($_GPC['starttime']);
					$endtime = strtotime($_GPC['endtime']) + 86399;
					$condition .= " AND (datetime >= '{$starttime}' AND datetime <= '{$endtime}')";
				}
				if($ctype == '1'){
					$condition1 .= " AND status = '{$ctype}'";
				}elseif($ctype == '2'){
					$condition1 .= " AND status = '0'";
				}
				$total_1 = pdo_fetchcolumn(" SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND status = '1' AND sid = '{$it['sid']}' AND drugid = '{$_GPC['id']}' $condition");
				$total_2 = pdo_fetchcolumn(" SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND status = '0' AND sid = '{$it['sid']}' AND drugid = '{$_GPC['id']}' $condition");
				$total_1 = $total_1 ? $total_1 : 0;
				$total_2 = $total_2 ? $total_2 : 0;
				$drugloglist = pdo_fetchall(" SELECT * FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND sid = '{$it['sid']}' AND drugid = '{$_GPC['id']}' $condition $condition1 ORDER BY datetime ASC LIMIT {$limit_start},6  ");
				foreach($drugloglist as $key => $value){
					$student = pdo_fetch("SELECT s_name,bj_id,xq_id,icon FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and id = '{$value['sid']}' ");
                    $drugsqinfo = pdo_fetch("SELECT * FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' and id = '{$value['drugid']}'  ");
					$teacher = pdo_fetch("SELECT tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and id = '{$value['tid']}' ");
                    $drugloglist[$key]['updatetime'] = $value['updatetime'] ? date("Y-m-d H:i:s",$value['updatetime']) : '尚未开始喂药';
					$drugloglist[$key]['tname'] = $teacher['tname'];
					$drugloglist[$key]['sname'] = $student['s_name'];
					$drugloglist[$key]['icon'] = $student['icon'];
					$drugloglist[$key]['info'] = $drugsqinfo;
					$drugloglist[$key]['location'] = $key + $limit_start;
				}
				include $this->template('comtool/sdruglog');
				exit;
			}
		}else{
			$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>