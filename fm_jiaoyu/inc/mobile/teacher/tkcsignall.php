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
        $nj_id = !empty($_GPC['nj_id'])?$_GPC['nj_id']:0;
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
		$tid_global = $it['tid'];
		if(!empty($it)){
			$bj_t = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid,':is_over'=>"2"));
			$nj_t = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type and is_over!=:is_over ORDER BY CONVERT(sname USING gbk) ASC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid,':is_over'=>"2"));
			$bj_str_temp = '0,';
			foreach($bj_t as $key_b=>$value_b){
				$bj_str_temp .=$value_b['sid'].",";
			}
			$bj_str = trim($bj_str_temp,",");
			$nj_str_temp = '0,';
			foreach($nj_t as $key_n=>$value_n){
				$nj_str_temp .=$value_n['sid'].",";
			}
			$nj_str = trim($nj_str_temp,",");
			
			
			
			
			//下拉加载
			if(!empty($_GPC['limit'])){
				$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));	
				if($teacher['status'] ==2 && !(is_njzr($teacher['id']))){
					$condition = "And teachers.id >{$_GPC['limit']} ";
					if($nj_id != 0){
						$condition.="And tcourse.xq_id = {$nj_id}";
					}
					$allteacher = pdo_fetchall("SELECT teachers.id,teachers.tname,teachers.thumb,tcourse.tid FROM " . tablename($this->table_teachers) . " as teachers," . tablename($this->table_tcourse) ." as tcourse where teachers.weid ='{$weid}' AND teachers.schoolid='{$schoolid}'  And (tcourse.tid like concat('%,',teachers.id,',%') or tcourse.tid like concat('%,',teachers.id) or tcourse.tid like concat(teachers.id,',%') or tcourse.tid = teachers.id ) {$condition} and FIND_IN_SET(tcourse.bj_id,'{$bj_str}') and FIND_IN_SET(tcourse.xq_id,'{$nj_str}') group BY teachers.id ORDER BY teachers.id ASC LIMIT 0,5 ");
					foreach( $allteacher as $key_a => $value_a )
					{
						$courselist = pdo_fetchall("SELECT id FROM " . tablename($this->table_tcourse) . " where weid = {$weid} AND schoolid ={$schoolid} and (tid like '%,{$value_a['id']},%'  or tid like '%,{$value_a['id']}' or tid like '{$value_a['id']},%' or tid ='{$value_a['id']}')  and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}') ");
						$course_str = '';
						foreach( $courselist as $key_c => $value_c )
						{
							$course_str.=$value_c['id'].",";
						}
						$c_str_a = trim($course_str,",");
						$signNum = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_kcsign) . " where weid = {$weid} AND schoolid ={$schoolid} and status = 2 And tid='{$value_a['id']}' And kcid in ({$c_str_a})");
						$allteacher[$key_a]['courseNum'] = count($courselist);
						$allteacher[$key_a]['signNum'] = $signNum;
					}
				}elseif($teacher['status'] !=2 && is_njzr($teacher['id'])){
					$allnj =getallnj($it['tid']);
					$nj_str = '';
					foreach( $allnj as $key => $value )
					{
						$nj_str.=$value['sid'].","; 
					}
					$nj_after = trim($nj_str,",");
					$condition = "And teachers.id >{$_GPC['limit']} ";
					if($nj_id != 0){
						$condition .="And tcourse.xq_id = {$nj_id}";
					}elseif($nj_id ==0){
						$condition .= "And tcourse.xq_id in ({$nj_after}) ";
					}
					$allteacher = pdo_fetchall("SELECT teachers.id,teachers.tname,teachers.thumb,tcourse.tid FROM " . tablename($this->table_tcourse) . " as tcourse," . tablename($this->table_teachers) ." as teachers where tcourse.weid ='{$weid}' AND tcourse.schoolid='{$schoolid}'  And (tcourse.tid like concat('%,',teachers.id,',%') or tcourse.tid like concat('%,',teachers.id) or tcourse.tid like concat(teachers.id,',%') or tcourse.tid = teachers.id ) {$condition} and FIND_IN_SET(tcourse.bj_id,'{$bj_str}') and FIND_IN_SET(tcourse.xq_id,'{$nj_str}') group BY teachers.id ORDER BY teachers.id ASC LIMIT 0,5 ");
					foreach( $allteacher as $key_a => $value_a )
					{
						$courselist = pdo_fetchall("SELECT id FROM " . tablename($this->table_tcourse) . " where weid = {$weid} AND schoolid ={$schoolid} and (tid like '%,{$value_a['id']},%'  or tid like '%,{$value_a['id']}' or tid like '{$value_a['id']},%' or tid ='{$value_a['id']}') And FIND_IN_SET(xq_id,'{$nj_after}') and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}')");
						$course_str = '';
						foreach( $courselist as $key_c => $value_c )
						{
							$course_str.=$value_c['id'].",";
						}
						$c_str_a = trim($course_str,",");
						$signNum = 	 pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_kcsign) . " where weid = {$weid} AND schoolid ={$schoolid} and status = 2 And tid='{$value_a['id']}' And kcid in ({$c_str_a})");
						$allteacher[$key_a]['courseNum'] = count($courselist);
						$allteacher[$key_a]['signNum'] = $signNum;
					}
				}
				include $this->template('comtool/tkcsignall');		
			}elseif(empty($_GPC['limit'])){
				$teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $_W ['uniacid'], ':id' => $it['tid']));	
				if($teacher['status'] ==2 && !(is_njzr($teacher['id']))){
					if($nj_id != 0){
						$condition = "And tcourse.xq_id = {$nj_id}";
					}elseif($nj_id = 0){
						$condition = " ";
					}
					$allnj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = {$weid} AND schoolid ={$schoolid} And type='semester' and is_over != 2");
					$allteacher = pdo_fetchall("SELECT teachers.id,teachers.tname,teachers.thumb,tcourse.tid FROM " . tablename($this->table_teachers) . " as teachers," . tablename($this->table_tcourse) ." as tcourse where teachers.weid ='{$weid}' AND teachers.schoolid='{$schoolid}'  And (tcourse.tid like concat('%,',teachers.id,',%') or tcourse.tid like concat('%,',teachers.id) or tcourse.tid like concat(teachers.id,',%') or tcourse.tid = teachers.id ) {$condition} and FIND_IN_SET(tcourse.bj_id,'{$bj_str}') and FIND_IN_SET(tcourse.xq_id,'{$nj_str}') group BY teachers.id ORDER BY teachers.id ASC LIMIT 0,5 ");
					foreach( $allteacher as $key_a => $value_a )
					{
						$courselist = pdo_fetchall("SELECT id FROM " . tablename($this->table_tcourse) . " where weid = {$weid} AND schoolid ={$schoolid} and (tid like '%,{$value_a['id']},%'  or tid like '%,{$value_a['id']}' or tid like '{$value_a['id']},%' or tid ='{$value_a['id']}') and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}') ");
						$course_str = '';
						foreach( $courselist as $key_c => $value_c )
						{
							$course_str.=$value_c['id'].",";
						}
						$c_str_a = trim($course_str,",");
						$signNum = 	 pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_kcsign) . " where weid = {$weid} AND schoolid ={$schoolid} and status = 2 And tid='{$value_a['id']}' And kcid in ({$c_str_a})");
						$allteacher[$key_a]['courseNum'] = count($courselist);
						$allteacher[$key_a]['signNum'] = $signNum;
					}
				}elseif($teacher['status'] !=2 && is_njzr($teacher['id'])){
					$allnj =getallnj($it['tid']);
					$nj_str = '';
					foreach( $allnj as $key => $value )
					{
						$nj_str.=$value['sid'].","; 
					}
					$nj_after = trim($nj_str,",");
					if($nj_id != 0){
						$condition ="And tcourse.xq_id = {$nj_id}";
					}elseif($nj_id ==0){
						$condition = "And tcourse.xq_id in ({$nj_after}) ";
					}
					$allteacher = pdo_fetchall("SELECT teachers.id,teachers.tname,teachers.thumb,tcourse.tid FROM " . tablename($this->table_tcourse) . " as tcourse," . tablename($this->table_teachers) ." as teachers where tcourse.weid ='{$weid}' AND tcourse.schoolid='{$schoolid}'  And (tcourse.tid like concat('%,',teachers.id,',%') or tcourse.tid like concat('%,',teachers.id) or tcourse.tid like concat(teachers.id,',%') or tcourse.tid = teachers.id ) {$condition} and FIND_IN_SET(tcourse.bj_id,'{$bj_str}') and FIND_IN_SET(tcourse.xq_id,'{$nj_str}') group BY teachers.id ORDER BY teachers.id ASC LIMIT 0,5 ");
					foreach( $allteacher as $key_a => $value_a )
					{
						$courselist = pdo_fetchall("SELECT id FROM " . tablename($this->table_tcourse) . " where weid = {$weid} AND schoolid ={$schoolid} and (tid like '%,{$value_a['id']},%'  or tid like '%,{$value_a['id']}' or tid like '{$value_a['id']},%' or tid ='{$value_a['id']}')  And FIND_IN_SET(xq_id,'{$nj_after}') and FIND_IN_SET(bj_id,'{$bj_str}') and FIND_IN_SET(xq_id,'{$nj_str}') ");
						$course_str = '';
						foreach( $courselist as $key_c => $value_c )
						{
							$course_str.=$value_c['id'].",";
						}
						$c_str_a = trim($course_str,",");
						$signNum = 	 pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_kcsign) . " where weid = {$weid} AND schoolid ={$schoolid} and status = 2 And tid='{$value_a['id']}' And kcid in ({$c_str_a})");
						$allteacher[$key_a]['courseNum'] = count($courselist);
						$allteacher[$key_a]['signNum'] = $signNum;
					}
				}
				include $this->template(''.$school['style3'].'/tkcsignall');		
			}
		}else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }       








?>