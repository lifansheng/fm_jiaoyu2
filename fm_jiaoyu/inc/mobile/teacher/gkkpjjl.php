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
        $getMy = $_GPC['getMy'];
       
        //查询是否用户登录	
        $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');	
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));		
			

		$thistime = trim($_GPC['limit']);
		if($thistime){
			$condition = " AND createtime < '{$thistime}'";	
				$mypjlist1 =  pdo_fetchall("SELECT distinct gkkid FROM " . tablename($this->table_gkkpj) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And userid='{$it['id']}'  $condition  ORDER BY createtime DESC LIMIT 0,10 ");
				$gkklist1 = array();
				foreach( $mypjlist1 as $key => $value )
				{
					$gkklist1[$key] = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id='{$value['gkkid']}' ");
				}
			foreach($gkklist1 as $key =>$row){
				$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$kemu = pdo_fetch("SELECT sname,icon FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['km_id']));
				
				$gkklist1[$key]['kmname'] = $kemu['sname'];
				$gkklist1[$key]['kmicon'] = empty($kemu['icon']) ? $school['logo'] : $kemu['icon'];
				$gkklist1[$key]['banji'] = $banji['sname'];
				$gkklist1[$key]['tname'] = $teach['tname'];
				$gkklist1[$key]['thumb'] = $teach['thumb'];
			} 
			include $this->template('comtool/gkkpjjl'); 
		}else{
			
				//$mypjlist =  pdo_fetchall("SELECT distinct gkkid FROM " . tablename($this->table_gkkpj) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And (userid='{$it['id']}' or tid = '{$it['tid']}') ORDER BY createtime DESC LIMIT 0,10 ");
            $mypjlist =  pdo_fetchall("SELECT distinct gkkid FROM " . tablename($this->table_gkkpj) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And  tid = '{$it['tid']}' ORDER BY createtime DESC LIMIT 0,10 ");
				$gkklist = array();
				foreach( $mypjlist as $key => $value )
				{
					$gkklist[$key] = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id='{$value['gkkid']}' ");
				}
			foreach($gkklist as $key =>$row){
				$banji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$kemu = pdo_fetch("SELECT sname,icon FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['km_id']));
				
				$gkklist[$key]['kmname'] = $kemu['sname'];
				$gkklist[$key]['kmicon'] = empty($kemu['icon']) ? $school['logo'] : $kemu['icon'];
				$gkklist[$key]['banji'] = $banji['sname'];
				$gkklist[$key]['tname'] = $teach['tname'];
				$gkklist[$key]['thumb'] = $teach['thumb'];
				$leave[$key]['time'] = date('Y-m-d H:i', $row['createtime']);	
			}
			//var_dump($gkklist); 
			include $this->template(''.$school['style3'].'/gkkpjjl');	
		}				        		
        if(empty($it['id'])){
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>