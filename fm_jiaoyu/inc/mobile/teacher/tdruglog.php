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
        $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
        //查询是否用户登录
        $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		//喂药动作列表
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid_global = $it['tid'];
 
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));  
		if(!empty($userid['id'])){
			if($operation == 'display'){
				$condition = '';
				$starttime = strtotime(date("Y-m-d"));
				$endtime = strtotime(date("Y-m-d")) + 86399;
				$condition .= " AND (datetime >= '{$starttime}' AND datetime <= '{$endtime}')";
		
				$drugloglist = pdo_fetchall(" SELECT * FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' $condition ORDER BY datetime ASC LIMIT 0,6  ");
				//总数
				$all = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}'  $condition ");
				//未处理
				$NoDeal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and status = 0 $condition ");
				//已处理
				$HasDeal = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and status = 1 $condition ");
		
				foreach($drugloglist as $key => $value){
					$student = pdo_fetch("SELECT s_name,bj_id,xq_id,icon FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and id = '{$value['sid']}' ");
					$bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$student['bj_id']}'  ");
					$njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$student['xq_id']}'  ");
					$drugsqinfo = pdo_fetch("SELECT * FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' and id = '{$value['drugid']}'  ");
					$drugloglist[$key]['sname'] = $student['s_name'];
					$drugloglist[$key]['icon'] = $student['icon'];
					$drugloglist[$key]['bjname'] = $bjname['sname'];
					$drugloglist[$key]['njname'] = $njname['sname'];
					$drugloglist[$key]['info'] = $drugsqinfo;
				}
				include $this->template(''.$school['style3'].'/tdruglog');
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
				$total_1 = pdo_fetchcolumn(" SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND status = '1' $condition");
				$total_2 = pdo_fetchcolumn(" SELECT count(id) FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND status = '0' $condition");
				$total_1 = $total_1 ? $total_1 : 0;
				$total_2 = $total_2 ? $total_2 : 0;
				$drugloglist = pdo_fetchall(" SELECT * FROM ".GetTableName('druglog')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' $condition $condition1 ORDER BY datetime ASC LIMIT {$limit_start},6  ");
				foreach($drugloglist as $key => $value){
					$student = pdo_fetch("SELECT s_name,bj_id,xq_id,icon FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and id = '{$value['sid']}' ");
					$bjname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$student['bj_id']}'  ");
					$njname = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$student['xq_id']}'  ");
					$drugsqinfo = pdo_fetch("SELECT * FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' and id = '{$value['drugid']}'  ");
					$drugloglist[$key]['sname'] = $student['s_name'];
					$drugloglist[$key]['icon'] = $student['icon'];
					$drugloglist[$key]['bjname'] = $bjname['sname'];
					$drugloglist[$key]['njname'] = $njname['sname'];
					$drugloglist[$key]['info'] = $drugsqinfo;
					$drugloglist[$key]['location'] = $key + $limit_start;
				}
				include $this->template('comtool/tdruglog');
				exit;
			}elseif($operation == 'weiyao'){
				$id = $_GPC['id'];
				$schoolid = $_GPC['schoolid'];
				$druglog = pdo_fetch("SELECT id FROM " . GetTableName('druglog') . " WHERE schoolid = '{$schoolid}' AND id = '{$id}'");
				if(empty($druglog)){
					$result['result'] = false;
					$result['msg'] = '抱歉，本条信息不存在或是已经被删除！';
				}else{
					pdo_update(GetTableName('druglog',false),array('updatetime'=>time(),'status'=>1),array('id'=>$id));
					$result['result'] = true;
					$result['msg'] = '操作成功！';
				}
				die(json_encode($result));  
			}elseif($operation == 'GetDrugInfo'){
				$id = $_GPC['id'];
				$schoolid = $_GPC['schoolid'];
				$weid = $_GPC['weid'];
				$data = pdo_fetch("SELECT * FROM ".GetTableName('drug')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND id = '{$id}' ");
				$data['headimg'] = unserialize($data['headimg']);
				include $this->template('comtool/tdrugloginfo');
				exit;
			}
			
		}else{
			$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>