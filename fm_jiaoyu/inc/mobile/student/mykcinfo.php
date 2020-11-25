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
		$schooltype = $_W['schooltype'];
		//获取基本信息
		if(empty($_GPC['userid'])){
			$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		}elseif(!empty($_GPC['userid'])){
			$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_GPC['userid']));
			$_SESSION['user'] = $_GPC['userid'];
		}
		if($it){
			$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND schoolid=:schoolid AND id=:id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $it['sid']));
			$share = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->table_order) . " where weid = '{$weid}' AND schoolid='{$schoolid}' AND shareuserid='{$it['id']}' and kcid = '{$id}' and type=1 and status = 2 ");
			//var_dump($share);
			$stup=$student['points']?$student['points']:0;
			$school = pdo_fetch("SELECT style2,title,tpic,logo,Is_point,thumb FROM " . tablename($this->table_index) . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $schoolid));
			$kcinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_tcourse) . " WHERE id = :id ", array(':id' => $id));
			$category = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');
			$kmname = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = :sid ", array(':sid' => $kcinfo['km_id']));
			$jsname = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " WHERE sid = :sid ", array(':sid' => $kcinfo['adrr']));
			$t_array = explode(',',$kcinfo['tid']);
			$tname_array = ' ';
			foreach( $t_array as $key_t => $value_t ){
				$teacher_all =  pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE schoolid = :schoolid And id = :id", array(':schoolid' => $schoolid,':id' => $value_t));	
				$tname_array.=$teacher_all['tname']."/";
			}
			$tname_array_end = trim($tname_array,"/");
			
			$time = strtotime(date('Ymd'));
			$time1 =$time + 86399;
			if($kcinfo['OldOrNew'] == 0){
				$isHaveKs = pdo_fetch("select * FROM ".tablename($this->table_kcbiao)." WHERE date>'{$time}' AND date<'{$time1}' And kcid = '{$kcinfo['id']}'");
				if (!empty($isHaveKs)){
					$checknubs = GetOneKcKsOrder($id,$isHaveKs['id']);
					$nubmer = $checknubs['nuber'];
					$hasSign = pdo_fetch("select id FROM ".tablename($this->table_kcsign)." WHERE createtime>='{$time}' AND createtime<'{$time1}' And kcid = '{$kcinfo['id']}' AND ksid = '{$isHaveKs['id']}' AND sid='{$it['sid']}' ");
				}
				//获取课时信息
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_kcbiao) . " WHERE weid = :weid AND schoolid =:schoolid AND kcid = :kcid  ORDER BY date ASC", array(':weid' => $weid, ':schoolid' => $schoolid, ':kcid' => $id));
				foreach( $list as $key => $value ){
					$checksign = pdo_fetch("SELECT id,status FROM " . tablename($this->table_kcsign) . " WHERE weid='{$weid}' AND schoolid='{$schoolid}' AND  ksid = '{$value['id']}' AND kcid='{$id}' AND sid='{$it['sid']}' ");
					if(!empty($checksign)){
						$list[$key]['checksign'] = $checksign['status'];
					}else{
						$list[$key]['checksign'] = 0;
					}
					$list[$key]['nub'] = $key +1;
				}
			}else{
				$hasSign = pdo_fetch("select id FROM ".tablename($this->table_kcsign)." WHERE createtime>='{$time}' AND createtime<'{$time1}' And kcid = '{$kcinfo['id']}' AND sid='{$it['sid']}'  ");
				$signlist =  pdo_fetchall("select * FROM ".tablename($this->table_kcsign)." WHERE kcid = '{$kcinfo['id']}' AND sid='{$it['sid']}' AND weid='{$weid}' AND schoolid='{$schoolid}' ORDER BY createtime ASC ");
			}
			$restks = GetRestKsBySid($kcinfo['id'],$it['sid']);
			$shenyuks = $restks['restnumber'];
			//获取该课程教师信息
			if($kcinfo['maintid'] != 0){
				$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND id=:id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $kcinfo['maintid']));
			}else{
				$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND schoolid=:schoolid AND id=:id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id' => $kcinfo['tid']));
			}
			if($kcinfo['kc_type'] == 1){
				if($kcinfo['allow_menu'] == 1){//启用章节
					$menulist = pdo_fetchall("SELECT * FROM " . GetTableName('kc_menu') . " WHERE schoolid = '{$schoolid}' And kcid = '{$id}' ORDER BY id ASC ");
					$checkzj = pdo_fetch("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And kcid = '{$id}' And menu_id = 0 ");
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
				foreach($menulist as $k => $item){
					if($kcinfo['allow_menu'] == 1){//启用章节
						$condition = "And menu_id = '{$item['id']}' ";
					}
					$menulist[$k]['list'] = pdo_fetchall("SELECT * FROM " . GetTableName('kcbiao') . " WHERE schoolid = '{$schoolid}' And kcid = '{$id}' $condition ORDER BY ssort DESC ");
					foreach($menulist[$k]['list'] as $key => $row){
						$menulist[$k]['list'][$key]['read'] = true;
						$checksign = pdo_fetch("SELECT id FROM " . GetTableName('kcsign') . " WHERE :ksid = ksid And :sid = sid", array(':sid' => $it['sid'],':ksid' => $row['id']));
						if(!empty($checksign)){
							$menulist[$k]['list'][$key]['read'] = false;
							$read++;
						}
						$menulist[$k]['list'][$key]['already'] = false; //验证是否直播中
						if($row['content_type'] == 1){
							$checktime = $row['sk_start'] - $kcinfo['txtime']*60;
							if($nowtime >= $checktime && $row['sk_end'] > $nowtime){
								$menulist[$k]['list'][$key]['already'] = true;
							}
						}
						$menulist[$k]['list'][$key]['number'] = $number;
						$number ++;
					}
					$menunub++;
				}
				$jdbili = $number-1 != 0 ? $read/($number-1)*100 : 0;
			}
			$title = $kcinfo['name'];
			$title1 .= "我分享了一个课程，快来看看吧";  
			$sharetitle = $title1;
			$sharedesc = $title."【课程分享】";
			$shareimgUrl = tomedia($kcinfo['thumb']);
			$links = $_W['siteroot'] .'app/'.$this->createMobileUrl('kcinfo', array('schoolid' => $schoolid,'id' => $id,'shareuserid'=>$it['id'],'fenxiang'=> 'fenxiang'));
			if($kcinfo['kc_type'] == 1){
				include $this->template(''.$school['style2'].'/mykconline');
			}else{
				include $this->template(''.$school['style2'].'/mykcinfo');
			}
		}else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
		}
?>