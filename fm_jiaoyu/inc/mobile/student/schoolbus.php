<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$time = $_GPC['time'];
		if (!empty($_GPC['userid'])){
			$_SESSION['user'] = $_GPC['userid'];
		}
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id And openid = :openid ", array(':id' => $_SESSION['user'],':openid' => $openid));
		$tit = pdo_fetch("SELECT tid,uid,id FROM " . tablename($this->table_user) . " where weid = :weid And :schoolid = schoolid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));		
		$school = pdo_fetch("SELECT style2,lng,lat FROM " . tablename($this->table_index) . " where weid = :weid AND id = :id ", array(':weid' => $weid, ':id' => $schoolid));
		$student = pdo_fetch("SELECT s_name,bj_id FROM " . tablename($this->table_students) . " where id = :id AND schoolid = :schoolid ", array(':id' => $it['sid'], ':schoolid' => $schoolid));
		if($it || $tit){
			$time = $_GPC['time'];
			if(empty($time)){
				$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
				$endtime = $starttime + 86399;
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			}else{
				$date = explode ( '-', $time );
				$starttime = mktime(0,0,0,$date[1],$date[2],$date[0]);
				$endtime = $starttime + 86399;
				$condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			}
			$allbus = pdo_fetchall("SELECT macid,name FROM " . tablename($this->table_checkmac) . " where weid = '{$weid}' AND schoolid = '{$schoolid}' AND is_bobao = 2  ORDER by id ASC");
			$old_point = '';
			if($allbus){
				foreach($allbus as $key =>$row){
					$gpslog = pdo_fetchall("SELECT id,lon,lat,createtime FROM " . tablename($this->table_busgps) . " where macid = '{$row['macid']}' AND schoolid = '{$schoolid}' $condition ORDER BY createtime DESC");
					if($gpslog){
						$old_point .= '['.$gpslog[0]['lon'].','.$gpslog[0]['lat'].']'.',';
						$allbus[$key]['macid'] = $row['macid'];
						$allbus[$key]['lastid'] = $gpslog[0]['id'];
						$allbus[$key]['createtime'] = $gpslog[0]['createtime'];
						$allbus[$key]['lon'] = $gpslog[0]['lon'];
						$allbus[$key]['lat'] = $gpslog[0]['lat'];
					}else{
						$old_point .= '['.$school['lng'].','.$school['lat'].']'.',';
						$allbus[$key]['macid'] = $row['macid'];
						$allbus[$key]['createtime'] = $starttime;
						$allbus[$key]['lon'] = $school['lng'];
						$allbus[$key]['lat'] = $school['lat'];
					}
				}
			}
			$hasbus = pdo_fetch("SELECT id FROM " . tablename($this->table_busgps) . " where schoolid = '{$schoolid}' $condition ");
			//print_r($allbus);
			$old_point = rtrim($old_point,',');
			$allbuss = json_encode($allbus);

			include $this->template(''.$school['style2'].'/schoolbus');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>