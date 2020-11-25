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
		$obid = 1;
		// var_dump(1111);
        //查询是否用户登录
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');

		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];

		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));

		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }else{
			if($_GPC['op'] == 'ajaxGetData'){
				$bjid = $_GPC['bjid'];
				$date = $_GPC['date'];
				$TodayStart = strtotime($_GPC['date']);
				$TodayEnd = $TodayStart + 86399;
				$StudentList = pdo_fetchall("SELECT s.s_name,s.icon,s.sex,c.createtime,u.pard,u.realname FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('checkinhome')." as c ON c.sid = s.id LEFT JOIN ".GetTableName('user')."  as u ON c.userid = u.id WHERE s.weid = '{$weid}' and s.bj_id = '{$bjid}' and (( c.createtime >= '{$TodayStart}' and c.createtime <= '{$TodayEnd}') or c.createtime is null) ");
				$CheckedList = $NotCheckedList = [];
				foreach ($StudentList as $value) {
					$value['icon'] = !empty($value['icon']) ? tomedia($value['icon']) : tomedia($school['spic']);
					if($value['createtime'] != null){
						switch ($value['pard']) {
							case '2':
								$value['gx'] = "父亲";
								break;
							case '3':
								$value['gx'] = "母亲";
								break;
							case '4':
								$value['gx'] = "本人";
								break;
							case '5':
								$value['gx'] = "家长";
								break;
							default:
								$value['gx'] = "家长";
								break;
						}
						$value['CheckTime'] = date("H时i分s秒",$value['createtime']);
						$CheckedList[] = $value;
					}else{
						$NotCheckedList[] = $value;
					}
				}
				$result['status'] = true;
				$result['msg'] = '获取成功';
				$result['NotCheck'] = $NotCheckedList;
				$result['Check'] = $CheckedList;
				die(json_encode($result));
			}else{
				mload()->model('tea');
				$classList = GetAllClassInfoByTid($schoolid,'1',$_W['schooltype'],$tid_global);
				if(empty($_GPC['bjid'])){
					$bjid = $classList[0]['sid'];
				}else{
					$bjid = $_GPC['bjid'];
				}
				$bjinfo = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE sid = '{$bjid}' ");
				$date = date("Y-m-d",time());
				if(!empty($_GPC['date'])){
					$date = $_GPC['date'];
				}
				$TodayStart = strtotime($date);
				$TodayEnd = $TodayStart + 86399;
				$StudentList = pdo_fetchall("SELECT s.s_name,s.icon,s.sex,c.createtime,u.pard,u.realname FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('checkinhome')." as c ON c.sid = s.id LEFT JOIN ".GetTableName('user')."  as u ON c.userid = u.id WHERE s.weid = '{$weid}' and s.bj_id = '{$bjid}' and (( c.createtime >= '{$TodayStart}' and c.createtime <= '{$TodayEnd}') or c.createtime is null) ");
				$CheckedList = $NotCheckedList = [];
				foreach ($StudentList as $value) {
					$value['icon'] = !empty($value['icon']) ? tomedia($value['icon']) : tomedia($school['spic']);
					if($value['createtime'] != null){
						switch ($value['pard']) {
							case '2':
								$value['gx'] = "父亲";
								break;
							case '3':
								$value['gx'] = "母亲";
								break;
							case '4':
								$value['gx'] = "本人";
								break;
							case '5':
								$value['gx'] = "家长";
								break;
							default:
								$value['gx'] = "家长";
								break;
						}
						$value['CheckTime'] = date("H时i分s秒",$value['createtime']);

						$CheckedList[] = $value;
					}else{
						$NotCheckedList[] = $value;
					}
				}
				include $this->template(''.$school['style3'].'/checkinhome/teacheckinhomelog');
			}

		}

?>