<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $this->weid;
        $from_user = $this->_fromuser;
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        $obid = 1;
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$tid = $it['tid'];
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));		

		$thistime = trim($_GPC['limit']);
		if($thistime){
			
			$condition = " AND createtime < '{$thistime}'";	
		    $list1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_pointsrecord) . " where weid = :weid AND schoolid=:schoolid AND tid = :tid $condition  ORDER BY createtime DESC LIMIT 0,10" , array(':weid' => $weid, ':schoolid' => $schoolid, ':tid'=> $tid));
				
			foreach($list1 as $key => $row){
				$point = pdo_fetch("SELECT * FROM " . tablename($this->table_points) . " where weid = :weid AND schoolid=:schoolid AND id = :id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id'=> $row['pid']));
				$list1[$key]['Tdate'] = date("Y-m-d H:i",$row['createtime']);
				$list1[$key]['Point'] = $point['adpoint'];
				$list1[$key]['Name'] = $point['name'];
			}	
		    include $this->template('comtool/pointdetail');
		}else{
		    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_pointsrecord) . " where weid = :weid AND schoolid=:schoolid AND tid = :tid   ORDER BY createtime DESC LIMIT 0,10" , array(':weid' => $weid, ':schoolid' => $schoolid, ':tid'=> $tid));
				
			foreach($list as $key => $row){
				$point = pdo_fetch("SELECT * FROM " . tablename($this->table_points) . " where weid = :weid AND schoolid=:schoolid AND id = :id", array(':weid' => $weid, ':schoolid' => $schoolid, ':id'=> $row['pid']));
				$list[$key]['Tdate'] = date("Y-m-d H:i",$row['createtime']);
				$list[$key]['Point'] = $point['adpoint'];
				$list[$key]['Name'] = $point['name'];
				$list[$key]['max'] = $point['dailytime'];
			}	
		    include $this->template(''.$school['style3'].'/pointdetail');	
		}	
		if(empty($it)){
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }       








?>