<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
    global $_W, $_GPC;
    $weid = $_W ['uniacid']; 
	$openid = $_W['openid'];
    $id = intval($_GPC['id']);
	$schoolid = intval($_GPC['schoolid']);
	if(!empty($_GPC['checktid'])){
		$checktid = $_GPC['checktid'];
	}
	
	//检查是否用户登陆
	$it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
	$school = pdo_fetch("SELECT style3,title,spic,tpic,logo FROM " . GetTableName('index') . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $schoolid));
	$tid_global = $it['tid'];
	$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	if($it){
		mload()->model('kc');	
		if($operation == 'display'){
			$item = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id ", array(':id' => $id));
			$shetskt = pdo_fetch("SELECT tea_change_stutype FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $item['sign_pl_set']));
			$alljs =  pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid ='{$schoolid}' And type='addr' ORDER BY ssort DESC ");
			$allsd =  pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " where schoolid ='{$schoolid}' And type='timeframe' ORDER BY ssort DESC ");
			$showTime = TIMESTAMP + $item['signTime']*60;
			//组合授课老师
			$t_array = explode(',',$item['tid']);
			$tname_array = '';
			$tid_tname = array();
			$I_Is_This_KcTea = false; //判断当前老师是否是本课的授课老师
			foreach( $t_array as $key_t => $value_t ){
				$teacher_all =  pdo_fetch("SELECT id,tname FROM " . GetTableName('teachers') . " WHERE id = :id", array(':id' => $value_t));	
				$tid_tname[$key_t] = $teacher_all;
				if($tid_global == $value_t){
					$I_Is_This_KcTea = true;
				}
				$tname_array.=$teacher_all['tname']."/";
			}
			$tname_array_end = trim($tname_array,"/");
			if($I_Is_This_KcTea){
				$nowteacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " where id ='{$tid_global}' ");
			}
			$counmks =array();
			for($i == 1 ; $i < 101; $i++){
				$counmks[] = $i;
			}
			if($item['kc_type'] == 0){
				$signset = pdo_fetch("SELECT tea_mobile_pk FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $item['sign_pl_set']));
				$allks = GetOneKcKsOrder($item['id']);//左侧课时列表
				$topstuimg =  GetOneKcStuListForName($item['id'],true);//顶部在读学员随机显示
				$nobodyh5 = '';
				if(count($topstuimg) < 6){
					$copynub = 6-count($topstuimg);
					$html = '<img class="zlheader" src="'.OSSURL.'/public/mobile/img/nobody.png">';
					$nobodyh5 = str_repeat($html,$copynub);
				}
				$allstu = pdo_fetchall("SELECT sid FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$item['id']}' And is_change != 1 ");
				$allstunub = 0;
				foreach($allstu as $row){//排除已删除的学生
					$checkstu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' And id = '{$row['sid']}'");
					if($checkstu){
						$allstunub++;
					}
				}
				$allmyks = pdo_fetchcolumn("SELECT COUNT(id) FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And kcid = '{$item['id']}' ");
				$yskks = pdo_fetchcolumn("SELECT COUNT(distinct ksid ) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And kcid = '{$item['id']}' ");
				$jindu = $yskks/$allmyks*100;
				if(!empty($_GPC['ksid'])){
					$checkksid = intval($_GPC['ksid']);
				}
			}
			if($item['kc_type'] == 1){ //在线课程
				if($item['allow_menu'] == 1){//启用章节
					$menulist = pdo_fetchall("SELECT * FROM " . GetTableName('kc_menu') . " WHERE schoolid = '{$schoolid}' And kcid = '{$item['id']}' ORDER BY id ASC ");
					$checkzj = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And kcid = '{$item['id']}' And menu_id = 0 ");
					if($checkzj){
						array_push($menulist,array('name' => '默认章节'));
					}
				}else{
					$menulist = array(array('name' => '课程排课'));
					$condition = " ";
				}
				$read = 0;
				$number = 1;
				$menunub = 0;
				$nowtime = time();
				foreach($menulist as $k => $val){
					if($item['allow_menu'] == 1){//启用章节
						$condition = "And menu_id = '{$val['id']}' ";
					}
					$menulist[$k]['list'] = pdo_fetchall("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And kcid = '{$item['id']}' $condition ORDER BY ssort DESC ");
					foreach($menulist[$k]['list'] as $key => $row){
						$menulist[$k]['list'][$key]['read'] = true;
						$signstu = pdo_fetchcolumn("SELECT COUNT(distinct sid ) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And status = 2 And kcid = '{$item['id']}' And ksid = '{$row['id']}' And sid != 0   " );
						$menulist[$k]['list'][$key]['ksign'] = $signstu;
						$menulist[$k]['list'][$key]['number'] = $number;
						$menulist[$k]['list'][$key]['already'] = false; //验证是否直播中
						if($row['content_type'] == 1){
							$checktime = $row['sk_start'] - $item['txtime']*60;
							if($nowtime >= $checktime && $row['sk_end'] > $nowtime){
								$menulist[$k]['list'][$key]['already'] = true;
							}
						}
						$number ++;
					}
					$menunub++;
				}
			}
			if($item['kc_type'] == 1){
				include $this->template(''.$school['style3'].'/kc/tmykcinfoonline');
			}else{
				include $this->template(''.$school['style3'].'/kc/tmykcinfo_new');
			}
		}
		if($operation == 'ksinfo'){
			$optype = $_GPC['optype'];
			$ksid = $_GPC['ksid'];
			$ksinfo = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
			$kcid = $ksinfo['kcid'];
			$checkmaster = pdo_fetch("SELECT tid FROM ".GetTableName('kcsign')." WHERE  ksid = {$ksid}  And tid >0 And ismaster_tid = 1 And status  = 2 ");
			if(!empty($checkmaster)){
				$zjteainfo = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' And id = '{$checkmaster['tid']}' ");
			}else{
				$zjteainfo = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' And id = '{$ksinfo['tid']}' ");
			}
			$kcinfo = pdo_fetch("SELECT allow_pl,sign_pl_set FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And id = '{$kcid}' ");
			//时段
			$sdinfo = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And sid = '{$ksinfo['sd_id']}' ");
			$signset = pdo_fetch("SELECT * FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
			if($ksinfo['pkuser']){$PkUser = CheckPkUser($ksinfo['pkuser']);}//排课人
			//获取本课时签到老师
			$fu_tea = 0;//助教人数
			$checksitid = pdo_fetchall("SELECT tid,ismaster_tid FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And ksid = '{$ksid}' And tid != 0 And status = 2 ");
			if(!empty($checksitid)){
				foreach($checksitid as $key => $it){
					$teainfo = pdo_fetch("SELECT tname,thumb FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' And id = '{$it['tid']}' ");
					if($teainfo){
						$checksitid[$key]['tname'] = $teainfo['tname'];
						$checksitid[$key]['thumb'] = !empty($teainfo['thumb'])?tomedia($teainfo['thumb']):tomedia($school['tpic']);
					}else{
						unset($checksitid[$key]);
					}
					$fu_tea++;
				}
			}
			$tidsignnub = count($checksitid);
			if($optype == 'ksinfo'){ //头部需要的信息
				//满班率
				$yskks = pdo_fetchcolumn("SELECT COUNT( sid ) FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And kcid = '{$kcid}' And ksid = '{$ksid}' And status = 2 And tid = 0 ");
				if($yskks >0){
					$jindu = round($yskks/$_GPC['stunuber']*100,1);
				}else{
					$jindu = 0.1;
				}
				//分数 取学生给老师的评分
				$pflist = pdo_fetchall("SELECT id,sid,star FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And sid > 0 And tid = 0 And totid >0 And type = 1 And kcid = '{$kcid}' And ksid = '{$ksid}' ");
				$allpf = count($pflist);
				//评价及分数
				$pjlist = pdo_fetchall("SELECT id,sid FROM " . GetTableName('kcpingjia') . " WHERE schoolid = '{$schoolid}' And is_master = 1 And type = 2 And kcid = '{$kcid}' And ksid = '{$ksid}' ");
				$allpj = count($pjlist);
				$zf = 0;//总分
				if(!empty($pflist)){//排除已删除的学生
					foreach($pflist as $key => $v){
						$stu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " WHERE id = '{$v['sid']}'");
						if(!empty($stu)){
							$zf = $zf + $v['star'];
						}else{
							if($v['sid'] > 0){
								pdo_delete(GetTableName('kcpingjia',false),array('sid' => $v['sid']));//处理已删除的学生
								pdo_delete(GetTableName('kcpingjia',false),array('tosid' => $v['sid']));
							}
						}
					}
				}
				$xingh5 = getStarByNub($zf,$allpf);//获取平均分 星级模板
				$order = GetOneKcKsOrder($kcid,$ksid);
				$nuber = $order['nuber'];
			}
			if($optype == 'stulist'){//学生签到情况信息
				$stunuber = $_GPC['stunuber'];
				$kcid = $_GPC['kcid'];
				$kcinfo = pdo_fetch("SELECT sign_pl_set FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
				$signset = pdo_fetch("SELECT tea_edit_ks FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
				$allsignstu = pdo_fetchall("SELECT sid FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}' And ksid = '{$ksid}' And tid = 0 ");
				$signstu = 0;//已签
				$yqrgnstu = 0;//已确认
				$qrsignstu = 0;//待确认
				$nosignstu = 0;//未签
				$qjsignstu = 0;//请假
				$qksignstu = 0;//缺课
				$buystu = pdo_fetchall("SELECT sid FROM " . GetTableName('coursebuy') . " WHERE kcid = '{$kcid}' And is_change != 1  ");//查询本课已报学生且未转课的正式学生
				foreach($buystu as $k =>$v){//先排除删除的
					$checkstu = pdo_fetch("SELECT id FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' And id = '{$v['sid']}'");
					if(empty($checkstu)){//查到被删除的学生后同时删除签到记录和coursebuy的购买记录
						pdo_delete(GetTableName('kcsign',false),array('sid'=>$v['sid'],'kcid'=>$kcid));
						pdo_delete(GetTableName('coursebuy',false),array('sid'=>$v['sid']));
						unset($buystu[$k]);
					}
				}
				$allstu = $buystu;
				foreach($allstu as $key =>$row){
					$checkstu = pdo_fetch("SELECT s_name,icon FROM " . GetTableName('students') . " WHERE schoolid = '{$schoolid}' And id = '{$row['sid']}'");
					$allstu[$key]['s_name'] = $checkstu['s_name'];
					$allstu[$key]['icon']   = !empty($checkstu['icon'])?tomedia($checkstu['icon']):tomedia($school['spic']);
					$checksign = pdo_fetch("SELECT status FROM " . GetTableName('kcsign') . " WHERE schoolid = '{$schoolid}'  And ksid = '{$ksid}' And kcid = '{$kcid}' And sid = '{$row['sid']}' ");
					if(!empty($checksign)){
						if($checksign['status'] == 1){
							$allstu[$key]['status'] = 1;
							$qrsignstu++;
							$signstu++;
						}
						if($checksign['status'] == 2){
							$allstu[$key]['status'] = 2;
							$yqrgnstu++;
							$signstu++;
						}
						if($checksign['status'] == 3){
							$allstu[$key]['status'] = 3;
							$qjsignstu++;
						}
						if($checksign['status'] == 0){
							$allstu[$key]['status'] = 0;
							$qksignstu++;
						}
					}else{
						$allstu[$key]['status'] = 5;
						$nosignstu++;
					}
				}
			}
			include $this->template(''.$school['style3'].'/kc/kc_stubox');
		}
		if($operation == 'stu_sign'){//老师签到学生
			$kcid = intval($_GPC['kcid']);$ksid = intval($_GPC['ksid']);$tid = intval($_GPC['tid']);$costnum = intval($_GPC['costnum']);$nowtime = time();
			$kcinfo = pdo_fetch("SELECT name,sign_pl_set FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
			$signset = pdo_fetch("SELECT tea_sign_fuke FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
			$checkmy = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And tid = '{$tid}' And status = 2 ");
			if(!empty($checkmy)){
				$sidarr = $_GPC['sidarr'];
				if(!is_array($_GPC['sidarr'])){$sidarr = explode(',',$sidarr);}
				$sucssnumber = 0;$defnumber = 0;
				$defstuarr = array();
				$result['showstubox'] = false;
				$data = array('weid' => $weid,'schoolid' => $schoolid,'kcid' => $kcid,'ksid' => $ksid,'qrtid' => $tid,'ismaster_tid' => 0,'type' => 1,'costnum' => $costnum,'kcname' => $kcinfo['name'],'createtime' => $nowtime,'signtime' => $nowtime,'signtype' => 2);
				foreach($sidarr as $k => $row){
					if($_GPC['type'] == 'queren' || $_GPC['type'] == 'qiandao'){ //扣课时的操作 才让前端显示课时不足框
						$restks = GetRestKsBySid($kcid,intval($row));
						if($restks['restnumber'] >= $costnum){ //课时足够
							$sid = intval($row);$data['sid'] = $sid;
							$checkthissign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And sid = '{$sid}' ");
							$data['status'] = 2;
							if( empty($checkthissign['id'])){
								pdo_insert(GetTableName('kcsign',false), $data);
								$signid = pdo_insertid();					
								$this->sendMobileXsqrqdtz($signid, $schoolid, $weid);
							}else{
								unset($data['signtype']);
								unset($data['createtime']);
								pdo_update(GetTableName('kcsign',false), $data, array('id' => $checkthissign['id']));
								$this->sendMobileXsqrqdtz($checkthissign['id'], $schoolid, $weid);
							}
							$sucssnumber++;
						}else{
							$defstuarr[$k]['sid'] = intval($row);
							$defstuarr[$k]['rest'] = $restks['restnumber'];
							$defnumber++;
							
						}
					}else{
						if($_GPC['type'] == 'qingjia'){
							$data['status'] = 3;
						}
						if($_GPC['type'] == 'queke'){
							$data['status'] = 0;
						}
						$sid = intval($row);$data['sid'] = $sid;
						$checkthissign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And sid = '{$sid}' ");
						if( empty($checkthissign['id'])){
							pdo_insert(GetTableName('kcsign',false), $data);
						}else{
							unset($data['signtype']);
							unset($data['createtime']);
							pdo_update(GetTableName('kcsign',false), $data, array('id' => $checkthissign['id']));
						}
					}	
				}
				if($defnumber > 0){
						$k = 0;$stuarr =  array();
						foreach($defstuarr as $key => $row){
							$stuinfo = pdo_fetch("SELECT s_name,icon,mobile FROM " . GetTableName('students') . " WHERE id = '{$row['sid']}' ");
							$checkuser = pdo_fetch("SELECT mobile FROM " . GetTableName('user') . " WHERE sid = '{$row['sid']}' ORDER by id ASC");
							$stuarr[$k]['sid'] = $row['sid'];
							$stuarr[$k]['rest'] = $row['rest'];
							$stuarr[$k]['s_name'] = $stuinfo['s_name'];
							$stuarr[$k]['icon'] = !empty($stuinfo['icon'])?tomedia($stuinfo['icon']):tomedia($school['spic']);
							$stuarr[$k]['mobile'] = false;
							if($stuinfo['mobile'] || $checkuser['mobile']){$stuarr[$k]['mobile'] = !empty($checkuser['mobile'])?$checkuser['mobile']:$stuinfo['mobile'];}
							$k ++;
						}
						$result['costnum'] = $costnum;//回传给前端老师选中的消耗课时数目 以便继续负课签到
						$result['suctext'] = '成功签到'.$sucssnumber.'人';
						$result['deftext'] = '以下'.$defnumber.'人课时不足请勾选学生继续签到';
						$result['defstuarr'] = $stuarr;
						$result['defnumber'] = '课时不足'.$defnumber.'人';
						$result['showstubox'] = true;//课时不足学生 常显给老师看
						if($signset['tea_sign_fuke'] == 1){
							$result['fu_btn'] = true;
						}else{
							$result['deftext'] = '以下'.$defnumber.'人课时不足请联系学生确认';	
							$result['fu_btn'] = false;
						}
					$result['msg'] = '成功签到'.$sucssnumber.'人,课时不足签到失败'.$defnumber.'人';
				}else{
					$result['msg'] = '成功操作'.$sucssnumber.'人!';
				}
				$result['result'] = true;
			}else{
				$result['msg'] = '抱歉,请您先签到本课后再操作学生状态';
				$result['result'] = false;
			}
			die(json_encode($result));
		}
		if($operation == 'add_newks'){//手机端排课提交
			$kcid = intval($_GPC['kcid']);
			$kcinfo = pdo_fetch("SELECT * FROM " . GetTableName('tcourse') . " WHERE id = :id", array(':id' => $kcid));
			if (empty($kcinfo)) {
				$data['result'] = false;
				$data['msg'] = "抱歉，课程不存在或是已经删除！";
			}
			$dataarray = array(
				'weid' => $weid,
				'schoolid' => $schoolid,
				'tid' => intval($_GPC['tid']),
				'kcid' => $kcid,
				'km_id' => $kcinfo['km_id'],
				'bj_id' => $kcinfo['bj_id'],
				'sd_id' => intval($_GPC['sd_id']),
				'xq_id' => $kcinfo['xq_id'],
				'costnum' => intval($_GPC['costnum']),
				'addr_id'=> $_GPC['adrr'],
				'pkuser' => intval($_GPC['pkuser']),
				'createtime'=> time()
			);
			$pkmode = intval($_GPC['pkmode']);
			if($pkmode == 1 || $pkmode == 2 || $pkmode == 0){
				$dataarray['rulsetid'] = getRandomString(10);
				$sdinfo = pdo_fetch("SELECT sd_start,sd_end,sname FROM " . GetTableName('classify') . " where sid = :sid ", array(':sid' =>  intval($_GPC['sd_id'])));
				$weekarray = $_GPC['week'];
				$start = strtotime(trim($_GPC['start']));
				$end = strtotime(trim($_GPC['end']));
				$starttime = mktime(0,0,0,date('m',$start),date('d',$start),date('Y',$start));
				$endtime = mktime(0,0,0,date('m',$end),date('d',$end)+1,date('Y',$end))-1;
				$j = count_days($starttime,$endtime);
				$dayarray = array();		
				if($pkmode == 0){//日历
					$dataarray['re_type'] =3;
					$gpcdayarray = $_GPC['dataarry'];
					foreach($gpcdayarray as $k => $d){
						$dayarray[] = array(
							'date' => $d,
							'time' => strtotime($d.' 00:00:00')
						);
					}
				}
				if($pkmode == 1){
					$dataarray['re_type'] =1;//每周
					$datearray = array();
					for($i=0;$i<$j;$i++){
						$datearray[] = array(
							'time' => $starttime+$i*86400,//每隔一天赋值给数组
							'date' => date('Y-m-d',$starttime+$i*86400),
							'day' => $i +1,
							'week' => date('w',$starttime+$i*86400) %7==0?7:date('w',$starttime+$i*86400)//处理w=0为7
						);
					}
					foreach($datearray as $key => $row){
						if(in_array($row['week'],$weekarray)){
							$dayarray[] = array(
								'date' => $row['date'],
								'time' => $row['time'],
								'week' => $row['week']
							);
						}
					}
				}
				if($pkmode == 2){
					$dataarray['re_type'] =2;//隔周
					if($j >25){
						$startweek = date('w',$start) %7==0?7:date('w',$start);
						foreach($weekarray as $key => $row){
							if($row >= $startweek){
								$firstKCDate = $start + ($row - $startweek )* 86400;
							}else{
								$firstKCDate = $start + ($row - $startweek + 7)* 86400;//处理w=0为7
							}
							$dayarray[] = array(
								'time' => $firstKCDate,
								'date' => date('Y-m-d',$firstKCDate),
							);
							$inputdate = $firstKCDate;
							while (1){
								$inputdate = $inputdate + 14*86400;
								if($inputdate <= $end){
									$dayarray[] = array(
										'time' => $inputdate,
										'date' => date('Y-m-d',$inputdate),
									);
								}else{
									break;
								}
							}
						}
					}else{
						$data['result'] = false;
						$data['msg'] = "抱歉,你所选的日期范围太小无法执行隔周排课,请至少涵盖25天以上";
					}
				}
				if(count($dayarray) < 1){
					$data['dayarray'] = $dayarray;
					$data['result'] = false;
					$data['msg'] = "抱歉，你选择的日期范围和周几设置无法排课,请检查设置！";
				}else{
					$sucks = 0;
					$defks = 0;
					$defaddr = '';
					$defakc = '';
					$defatea = '';
					foreach($dayarray as $k => $r){
						$lasttime = $r['date'].date(" H:i",$sdinfo['sd_start']);
						$dataarray['date'] = strtotime($lasttime);
						$check_start = strtotime($r['date'].date(" H:i",$sdinfo['sd_start']));
						$check_end   = strtotime($r['date'].date(" H:i",$sdinfo['sd_end']));
						if(intval($_GPC['adrr']) > 0){
							$checkaddr =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where addr_id='{$_GPC['adrr']}' And date >= '{$check_start}' And date < '{$check_end}' ");
							if(empty($checkaddr)){
								$checkkc =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where kcid='{$kcid}' And date >= '{$check_start}' And date < '{$check_end}' ");
								if(empty($checkkc)){
									$checktea =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where tid='{$dataarray['tid']}' And date >= '{$check_start}' And date < '{$check_end}' ");
									if(empty($checktea)){
										pdo_insert(GetTableName('kcbiao',false),$dataarray);
										$sucks ++;
									}else{
										$defatea .= ",".$r['date'];
										$defks ++;
									}
								}else{
									$defakc .= ",".$r['date'];
									$defks ++;
								}
							}else{
								$defaddr .= ",".$r['date'];
								$defks ++;
							}
							$data['OK'] = 1;
							$data['sucks'] = $sucks;
							$data['result'] = true;
							$data['msg'] = "成功排课".$sucks."节";
							if($defakc != '' || $defaddr != '' || $defatea != ''){
								if($defakc != ''){
									$data['msg'] .= ",本课程以下日期排课冲突".$defakc;
								}
								if($defaddr != ''){
									$data['msg'] .= ",本教室以下日期排课冲突".$defaddr;
								}
								if($defatea != ''){
									$data['msg'] .= ",该老师以下日期排课冲突".$defatea;
								}
							}
						}else{
							$checkkc =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where kcid ='{$kcid}' And date >='{$check_start}' And date < '{$check_end}' ");
							if(empty($checkkc)){
								$checktea =  pdo_fetch("SELECT id FROM " . GetTableName('kcbiao') . " where tid ='{$dataarray['tid']}' And date >='{$check_start}' And date < '{$check_end}' ");
								if(empty($checktea)){
									pdo_insert(GetTableName('kcbiao',false),$dataarray);
									$sucks ++;
								}else{
									$defatea .= ",".$r['date'];
									$defks ++;
								}
							}else{
								$defakc .= ",".$r['date'];
								$defks ++;
							}
							$data['OK'] = 2;
							$data['sucks'] = $sucks;
							$data['result'] = true;
							$data['msg'] = "成功排课".$sucks."节";
							if($defakc != '' || $defatea != ''){
								if($defakc != ''){
									$data['msg'] .= ",本课程以下日期排课冲突".$defakc;
								}
								if($defatea != ''){
									$data['msg'] .= ",该老师以下日期排课冲突".$defatea;
								}
							}
						}
					}
				}
			}else{
				$data['result'] = false;
				$data['msg'] = "请选择重复模式";
			}
			die(json_encode($data));
		}
		if($operation == 'stu_fu_sign'){//课时不足学生签到
			$sidarr = $_GPC['sidarr'];
			$kcid = intval($_GPC['kcid']);$ksid = intval($_GPC['ksid']);$tid = intval($_GPC['tid']);$costnum = intval($_GPC['costnum']);$nowtime = time();
			$kcinfo = pdo_fetch("SELECT name FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
			$data = array('weid' => $weid,'schoolid' => $schoolid,'kcid' => $kcid,'ksid' => $ksid,'qrtid' => $tid,'ismaster_tid' => 0,'type' => 1,'status' => 2,'costnum' => $costnum,'kcname' => $kcinfo['name'],'createtime' => $nowtime,'signtime' => $nowtime,'signtype' => 2);
			foreach($sidarr as $row){
				$sid = intval($row);$data['sid'] = $sid;
				$checkthissign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And sid = '{$sid}' ");
				if( empty($checkthissign['id'])){
					pdo_insert(GetTableName('kcsign',false), $data);
					$signid = pdo_insertid();					
					$this->sendMobileXsqrqdtz($signid, $schoolid, $weid);
				}else{
					unset($data['signtype']);
					unset($data['createtime']);
					pdo_update(GetTableName('kcsign',false), $data, array('id' => $checkthissign['id']));
					$this->sendMobileXsqrqdtz($checkthissign['id'], $schoolid, $weid);
				}					
			}
			$result['msg'] = '操作成功';
			$result['result'] = true;
			die(json_encode($result));
		}
		if($operation == 'teasignlist'){//已经签到老师列表
			$kcid = intval($_GPC['kcid']);$ksid = intval($_GPC['ksid']);
			$signlist = pdo_fetchall("SELECT status,ismaster_tid,tid,createtime FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And tid != 0 ORDER BY ismaster_tid ASC ");
			if(!empty($signlist)){
				$suc = 0;$def = 0;
				foreach($signlist as $key => $row){
					$teainfo = pdo_fetch("SELECT tname,thumb,mobile FROM " . GetTableName('teachers') . " WHERE id = '{$row['tid']}' ");
					$signlist[$key]['time'] = date('Y/m/d',$row['createtime']);
					$signlist[$key]['tname'] = $teainfo['tname'];
					$signlist[$key]['mobile'] = $teainfo['mobile'];
					$signlist[$key]['thumb'] = !empty($teainfo['thumb'])?tomedia($teainfo['thumb']):tomedia($school['tpic']);
					if($row['ismaster_tid'] == 1){
						$signlist[$key]['shenfen'] = '主讲';
					}else{
						$signlist[$key]['shenfen'] = '助教';
					}
					if($row['status'] == 2){
						$signlist[$key]['status'] = '已签';
						$suc++;
					}else{
						$signlist[$key]['status'] = '待确认';
						$def++;
					}
				}
				if($def>0){
					$result['title'] = $suc.'人已签到本课，待确认'.$def.'人';
				}else{
					$result['title'] = $suc.'人已签到本课';
				}
				$result['signlist'] = $signlist;
				$result['msg'] = '操作成功';
				$result['result'] = true;
			}else{
				$result['msg'] = '本课无人签到';
				$result['result'] = false;
			}
			die(json_encode($result));
			
		}//老师签到
		if($operation == 'tea_sign'){//老师签到
			$kcid = intval($_GPC['kcid']);$tid = intval($_GPC['tid']);
			$ksid = intval($_GPC['ksid']);$nowtime = time();$signtype = intval($_GPC['signtype']);
			$kcinfo = pdo_fetch("SELECT name,sign_pl_set FROM " . GetTableName('tcourse') . " WHERE id = '{$kcid}' ");
			$signset = pdo_fetch("SELECT tea_sign_confirm,sh_tea_teacherids FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
			$ksinfo = pdo_fetch("SELECT tid FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
			$checksign = pdo_fetch("SELECT id,status FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And tid = '{$tid}' ");
			if(empty($checksign)){
				if($signtype == 1){
					$checkzhu = pdo_fetch("SELECT tid,createtime FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And tid != 0 And status = 2 And ismaster_tid = 1 ");
					if(!empty($checkzhu)){
						$yqtea = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = '{$checkzhu['tid']}' ");
						$result['result'] = false;
						$result['msg'] = '本节主讲已签,签到人:'.$yqtea['tname'].date('Y/m/d H:i',$checkzhu['createtime']);
						die(json_encode($result));
					}
				}
				$data = array('weid' => $weid,'schoolid' => $schoolid,'kcid' => $kcid,'ksid' => $ksid,'tid' => $tid,'ismaster_tid' => $signtype,'type' => 1,'kcname' => $kcinfo['name'],'createtime' => $nowtime,'signtime' => $nowtime,'signtype' => 1);
				if( $signset['tea_sign_confirm'] == 1){
					$data['status'] = 1;
					$result['result'] = true;
					$result['msg'] = '签到成功,请等待审核';
					pdo_insert(GetTableName('kcsign',false), $data);
					$signid = pdo_insertid();					
					$this->sendMobileJsqrqdtz($signid, $schoolid, $weid);
				}else{
					$data['status'] = 2;
					$result['result'] = true;
					$result['msg'] = '签到成功';
					pdo_insert(GetTableName('kcsign',false), $data);
					if($signtype == 1){
						pdo_update(GetTableName('kcbiao',false),array('tid'=>$tid),array('id'=>$ksid));
					}
				}
			}else{
				if($checksign['status'] == 1){
					$result['msg'] = '您已经提交一次签到,请等待审核';
				}
				if($checksign['status'] == 2){
					$result['msg'] = '你已签到本节,不可重复签到';
				}
				$result['result'] = false;
			}
			die(json_encode($result));
		}
		if($operation == 'tea_sign_check'){ //检查教师签到所有条件
			$kcid = $_GPC['kcid'];$tid = $_GPC['tid'];
			$ksid = $_GPC['ksid'];$nowtime = time();
			$kcinfo = pdo_fetch("SELECT isSign,allow_pl,sign_pl_set,signTime FROM " . GetTableName('tcourse') . " WHERE schoolid = '{$schoolid}' And id = '{$kcid}' ");
			$ksinfo = pdo_fetch("SELECT date,sd_id,tid FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
			$sdinfo = pdo_fetch("SELECT sname,sd_start,sd_end FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And sid = '{$ksinfo['sd_id']}' ");
			$signset = pdo_fetch("SELECT * FROM " . GetTableName('kc_signset') . " WHERE id = :id ", array(':id' => $kcinfo['sign_pl_set']));
			if($kcinfo['isSign'] == 1){
				$ksstarttime = strtotime(date("Y-m-d",$ksinfo['date']).date(" H:i",$sdinfo['sd_start']));
				$ksendtime = strtotime(date("Y-m-d",$ksinfo['date']).date(" H:i",$sdinfo['sd_end']));
				//检查本课程主讲
				$zhu_tea = pdo_fetch("SELECT tid FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And tid != 0 And status = 2 And ismaster_tid = 1");
				$checkmysign = pdo_fetch("SELECT id,status FROM " . GetTableName('kcsign') . " WHERE ksid = '{$ksid}' And tid = '{$tid}'");
				if(empty($checkmysign)){
					if($signset['tea_sign_old'] == 1){//任意课时签到
						if($signset['more_tea_sign'] == 1){//允许多师签到
							//检查本课助教
							if(empty($zhu_tea)){
								if($signset['tea_no_myks'] == 1){//允许签到不是自己的课时
									//允许签到 2种身份都显示出来签到
									if($ksinfo['tid'] == $tid){
										$result['zhu_my'] = true;
										$result['fu_my'] = false;
										$result['msg'] = '不限时签到/开启助教/允许签别人的课/自己的课/默认选择主';
									}else{
										$result['zhu_my'] = false;
										$result['fu_my'] = true;
										$result['msg'] = '不限时签到/开启助教/允许签别人的课/别人的课/默认选择辅';
									}
									$result['zhu_tea'] = true;//主盒子显示
									$result['fu_tea'] = true;//辅盒子显示
									$result['result'] = true;
								}else{
									if($ksinfo['tid'] == $tid){
										//允许签到 自己签到 2种身份都显示出来签到
										$result['zhu_my'] = true;
										$result['fu_my'] = false;
										$result['zhu_tea'] = true;
										$result['fu_tea'] = false;
										$result['result'] = true;
										$result['msg'] = '不限时签到/开启助教/不可签别人的课/自己的课程/只允许选择主';
									}else{
										//允许签到 先能选择助教身份签到
										$result['zhu_my'] = false;
										$result['fu_my'] = true;
										$result['zhu_tea'] = false;
										$result['fu_tea'] = true;
										$result['result'] = true;
										$result['msg'] = '不限时签到/开启助教/不可签别人的课/别人的课/只允许选择辅';
									}
								}
							}else{
								//允许签到 有主讲签到了，选择助教身份签到
								$result['zhu_my'] = false;
								$result['fu_my'] = true;
								$result['zhu_tea'] = false;
								$result['fu_tea'] = true;
								$result['result'] = true;
								$result['msg'] = '有主讲，不限时签到/开启助教/只允许选择辅';
							}
						}else{
							if(empty($zhu_tea)){ //未开启 辅导老师情况下 所有以下判断放行的部分 只在前端显示签到未主讲老师 
								if($signset['tea_no_myks'] == 1){//允许签到不是自己的课时
									//允许签到
									$result['zhu_my'] = true;
									$result['fu_my'] = false;
									$result['zhu_tea'] = true;
									$result['fu_tea'] = false;
									$result['result'] = true;
									$result['msg'] = '不限时签到/关闭助教/可签别人的课/只允许选择主/且只显示主盒子';
								}else{
									if($ksinfo['tid'] == $tid){
										//允许签到
										$result['zhu_my'] = true;
										$result['fu_my'] = false;
										$result['zhu_tea'] = true;
										$result['fu_tea'] = false;
										$result['result'] = true;
										$result['msg'] = '不限时签到/关闭助教/不可签别人的课/自己的课程/只允许选择主/且只显示主盒子';
									}else{
										$result['result'] = false;
										$result['msg'] = '抱歉,本节不属于你的课程,无法签到';
									}
								}
							}else{
								$result['result'] = false;
								$result['msg'] = '抱歉,本节已有老师签到,请勿重复签到';
							}
						}
					}else{//只允许签到时间范围内的签到
						$qdtqsj = $ksstarttime - $kcinfo['signTime']*60;
						if($qdtqsj < $nowtime && $nowtime < $ksendtime){
							if($signset['more_tea_sign'] == 1){//允许多师签到
								//检查本课助教
								if(empty($zhu_tea)){
									if($signset['tea_no_myks'] == 1){//允许签到不是自己的课时
										//允许签到 2种身份都显示出来签到
										if($ksinfo['tid'] == $tid){
											$result['zhu_my'] = true;
											$result['fu_my'] = false;
											$result['msg'] = '不限时签到/开启助教/允许签别人的课/自己的课/默认选择主';
										}else{
											$result['zhu_my'] = false;
											$result['fu_my'] = true;
											$result['msg'] = '不限时签到/开启助教/允许签别人的课/别人的课/默认选择辅';
										}
										$result['zhu_tea'] = true;//主盒子显示
										$result['fu_tea'] = true;//辅盒子显示
										$result['result'] = true;
									}else{
										if($ksinfo['tid'] == $tid){
											//允许签到 自己签到 2种身份都显示出来签到
											$result['zhu_my'] = true;
											$result['fu_my'] = false;
											$result['zhu_tea'] = true;
											$result['fu_tea'] = false;
											$result['result'] = true;
											$result['msg'] = '不限时签到/开启助教/不可签别人的课/自己的课程/只允许选择主';
										}else{
											//允许签到 先能选择助教身份签到
											$result['zhu_my'] = false;
											$result['fu_my'] = true;
											$result['zhu_tea'] = false;
											$result['fu_tea'] = true;
											$result['result'] = true;
											$result['msg'] = '不限时签到/开启助教/不可签别人的课/别人的课/只允许选择辅';
										}
									}
								}else{
									//允许签到 有主讲签到了，选择助教身份签到
									$result['zhu_my'] = false;
									$result['fu_my'] = true;
									$result['zhu_tea'] = false;
									$result['fu_tea'] = true;
									$result['result'] = true;
									$result['msg'] = '有主讲，不限时签到/开启助教/只允许选择辅';
								}
							}else{
								if(empty($zhu_tea)){ //未开启 辅导老师情况下 所有以下判断放行的部分 只在前端显示签到未主讲老师 
									if($signset['tea_no_myks'] == 1){//允许签到不是自己的课时
										//允许签到
										$result['zhu_my'] = true;
										$result['fu_my'] = false;
										$result['zhu_tea'] = true;
										$result['fu_tea'] = false;
										$result['result'] = true;
										$result['msg'] = '不限时签到/关闭助教/可签别人的课/只允许选择主/且只显示主盒子';
									}else{
										if($ksinfo['tid'] == $tid){
											//允许签到
											$result['zhu_my'] = true;
											$result['fu_my'] = false;
											$result['zhu_tea'] = true;
											$result['fu_tea'] = false;
											$result['result'] = true;
											$result['msg'] = '不限时签到/关闭助教/不可签别人的课/自己的课程/只允许选择主/且只显示主盒子';
										}else{
											$result['result'] = false;
											$result['msg'] = '抱歉,本节不属于你的课程,无法签到';
										}
									}
								}else{
									$result['result'] = false;
									$result['msg'] = '抱歉,本节已有老师签到,请勿重复签到';
								}
							}
						}else{
							$result['result'] = false;
							$result['msg'] = '错误:本节允许签到时间为'.date('Y/m/d H:i',$qdtqsj).'-'.date(" H:i",$sdinfo['sd_end']);
						}
					}
				}else{
					if($checkmysign['status'] == 1){
						$result['msg'] = '您已经提交一次签到,请等待审核';
					}
					if($checkmysign['status'] == 2){
						$result['msg'] = '你已签到本节,不可重复签到';
					}
					$result['result'] = false;
				}
			}else{
				$result['result'] = false;
				$result['msg'] = '本课已关闭手机端签到功能';
			}
			
			//以下为年级管理权限

			die(json_encode($result));
		}
		if($operation == 'check_ksnub'){//检查当前课程的状态
			$ksid = intval($_GPC['ksid']);
			$ksinfo = pdo_fetch("SELECT kcid,costnum FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And id = '{$ksid}' ");
			$order = GetOneKcKsOrder($ksinfo['kcid'],$ksid);
			$result['order'] = $order['nuber'];
			$result['costnum'] = $ksinfo['costnum'];
			$result['result'] = true;
			die(json_encode($result));
		}
	}else{
		session_destroy();
	    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
		header("location:$stopurl");
		exit;
	}
function count_days($a,$b){
	$a_dt=getdate($a);
	$b_dt=getdate($b);
	$a_new=mktime(12,0,0,$a_dt['mon'],$a_dt['mday'],$a_dt['year']);
	$b_new=mktime(12,0,0,$b_dt['mon'],$b_dt['mday'],$b_dt['year']);
	return round(abs($a_new-$b_new)/86400)+1;
}
?>