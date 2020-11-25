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

        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');	
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));		


        if(!empty($_GPC['gettype'])){
            $gettype = $_GPC['gettype'];
            if($gettype == 'all'){
                $conditon_tid = " ";
            }elseif($gettype == 'my'){
                $conditon_tid = "and tid ='{$it['tid']}' ";
            }
        }else{
            $gettype = 'all';
            $conditon_tid = " ";
        }
		$thistime = trim($_GPC['limit']);
		if($thistime){
			$condition = " AND createtime < '{$thistime}'";
            $gkklist1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_gongkaike) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  $condition $conditon_tid ORDER BY createtime DESC LIMIT 0,10 ");

			foreach($gkklist1 as $key =>$row){
				$banji = pdo_fetch("SELECT sname,parentid FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
                $nianji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $banji['parentid']));
                $teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$kemu = pdo_fetch("SELECT sname,icon FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['km_id']));
				
				$gkklist1[$key]['kmname'] = $kemu['sname'];
				$gkklist1[$key]['kmicon'] = empty($kemu['icon']) ? $school['logo'] : $kemu['icon'];
				$gkklist1[$key]['banji'] = $banji['sname'];
                $gkklist1[$key]['nianji'] = $nianji['sname'];
				$gkklist1[$key]['tname'] = $teach['tname'];
                if($row['createtid'] == '-1' || empty($row['createtid'])){
                    $gkklist1[$key]['createtname'] = "管理员";
                }else{
                    $teacher_create = pdo_fetch("SELECT tname FROM " . tablename ($this->table_teachers) . " where id = :id ", array(':id' => $row['createtid']));
                    $gkklist1[$key]['createtname'] = $teacher_create['tname'];
                }
			} 
			include $this->template('comtool/gkklist');	 
		}else{

            $gkklist = pdo_fetchall("SELECT * FROM " . tablename($this->table_gongkaike) . " where weid = '{$weid}' And schoolid = '{$schoolid}' $conditon_tid  ORDER BY createtime DESC LIMIT 0,10 ");
			
			
			//print_r($leave1);
			foreach($gkklist as $key =>$row){
                $banji = pdo_fetch("SELECT sname,parentid FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['bj_id']));
                $nianji = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $banji['parentid']));
				$teach = pdo_fetch("SELECT status,thumb,tname FROM " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $row['tid']));
				$kemu = pdo_fetch("SELECT sname,icon FROM " . tablename($this->table_classify) . " where sid = :sid And schoolid = :schoolid ", array(':schoolid' => $schoolid,':sid' => $row['km_id']));
				
				$gkklist[$key]['kmname'] = $kemu['sname'];
				$gkklist[$key]['kmicon'] = empty($kemu['icon']) ? $school['logo'] : $kemu['icon'];
				$gkklist[$key]['banji'] = $banji['sname'];
                $gkklist[$key]['nianji'] = $nianji['sname'];
				$gkklist[$key]['tname'] = $teach['tname'];
                if($row['createtid'] == '-1' || empty($row['createtid'])){
                    $gkklist[$key]['createtname'] = "管理员";
                }else{
                    $teacher_create = pdo_fetch("SELECT tname FROM " . tablename ($this->table_teachers) . " where id = :id ", array(':id' => $row['createtid']));
                    $gkklist[$key]['createtname'] = $teacher_create['tname'];
                }
			} 
			include $this->template(''.$school['style3'].'/gkklist');	
		}				        		
        if(empty($userid['id'])){
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>