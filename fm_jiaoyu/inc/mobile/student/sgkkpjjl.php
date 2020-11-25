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
        $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));	
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
			
				$mypjlist =  pdo_fetchall("SELECT distinct gkkid FROM " . tablename($this->table_gkkpj) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And userid='{$it['id']}' ORDER BY createtime DESC LIMIT 0,10 ");
				//var_dump($mypjlist) ;
				$gkklist = array();
				foreach( $mypjlist as $key => $value )
				{
					$gkklist[$key] = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And id='{$value['gkkid']}' ");
				}
				
				//$gkklist = pdo_fetchall("SELECT * FROM " . tablename($this->table_gongkaike) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid='{$it['tid']}'   ORDER BY createtime DESC LIMIT 0,10 ");
				
			
			
			
			//print_r($leave1);
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
			include $this->template(''.$school['style2'].'/sgkkpjjl');	
		}				        		
        if(empty($it['id'])){
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>