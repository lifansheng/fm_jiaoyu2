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
		$time = $_GPC['time'];
        $bj_id = 0;
        //查询是否用户登录
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		if (!(IsHasQx($tid_global,2000601,2,$schoolid))){
			message('您无权查看本页面');
		}
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
        if(!empty($userid['id'])){
			mload()->model('tea');
    		$njlist = GetTeaManagerBjlist($weid,$schoolid,$it['tid']);
			if(!empty($njlist[0])){
				$IsShowCheckDetail = true;
			}else{
				$IsShowCheckDetail = false;
			}
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

			$teachers = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['tid']));

			$bjlist = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'theclass' and is_over != 2 ORDER BY sid ASC, ssort DESC");

			if(is_njzr($teachers['id'])){
				$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And tid = '{$it['tid']}' And type = 'semester' and is_over != 2");
				$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And parentid = '{$myfisrtnj['sid']}' and is_over != 2");
			}else{
				$fisrtbj = pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where schoolid = {$schoolid} And type = 'theclass' And tid = {$it['tid']} and is_over != 2 ORDER BY ssort DESC LIMIT 0,1 ");
				if($teachers['status'] == 2){
					$myfisrtnj =  pdo_fetch("SELECT sid FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And type = 'semester' and is_over != 2 ");
					$fisrtbj =  pdo_fetch("SELECT sid as bj_id FROM " . tablename($this->table_classify) . " where weid = '{$weid}' And schoolid = '{$schoolid}' And parentid = '{$myfisrtnj['sid']}' and is_over != 2");
				}
			}
			if(!empty($_GPC['bj_id'])){
				$bj_id = intval($_GPC['bj_id']);
			}else{
				$bj_id = $fisrtbj['bj_id']?$fisrtbj['bj_id']:0;
			}
			$nowbj = pdo_fetch("SELECT sid,sname FROM " . tablename($this->table_classify) . " where sid = :sid ", array(':sid' => $bj_id));
			if(is_njzr($teachers['id'])){
				$mynjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'semester' and is_over != 2 ORDER BY ssort DESC");
				foreach($mynjlist as $key =>$row){
					$mynjlist[$key]['bjlist'] = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And parentid = '{$row['sid']}' And type = 'theclass' and is_over != 2 ORDER BY sid ASC, ssort DESC");
					foreach($mynjlist[$key]['bjlist'] as $k => $v){

					}
				}
			}else{
				if($teachers['status'] == 2){
					$bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'theclass' and is_over != 2 ORDER BY sid ASC, ssort DESC");
				}
			}

			if(empty($time)){

				$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));

				$endtime = $starttime + 86399;

				$condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			}else{

				$date = explode ( '-', $time );

				$starttime = mktime(0,0,0,$date[1],$date[2],$date[0]);

				$endtime = $starttime + 86399;

				$condition = " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
			}

			$qdwqram =  pdo_fetchall("SELECT id,sid,createtime FROM " . tablename($this->table_checklog) . " where schoolid = {$schoolid} And bj_id = {$bj_id} And leixing = 1 And isconfirm = 2 $condition ORDER BY createtime ASC");
			$qdams = count($qdwqram);
			foreach($qdwqram as $key =>$row){
				$student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id ", array(':schoolid' => $schoolid, ':id' => $row['sid']));
				$qdwqram[$key]['s_name'] = $student['s_name'];
			}
			$qdwqrpm =  pdo_fetchall("SELECT id,sid,createtime FROM " . tablename($this->table_checklog) . " where schoolid = {$schoolid} And bj_id = {$bj_id} And leixing = 2 And isconfirm = 2 $condition ORDER BY createtime ASC");
			$qdamspm = count($qdwqrpm);
			foreach($qdwqrpm as $key =>$row){
				$student = pdo_fetch("SELECT s_name FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id ", array(':schoolid' => $schoolid, ':id' => $row['sid']));
				$qdwqrpm[$key]['s_name'] = $student['s_name'];
            }

            //重构数据库语句，优化查询方式，提升页面速度
            // $students = pdo_fetchall("SELECT id,s_name,bj_id FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND bj_id = :bj_id ORDER BY id ASC", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));

            //重构数据库语句，优化查询方式，提升页面速度
            $Sql = "  SELECT s.id,s.s_name,s.bj_id,ca.id as ischeck,  ca.createtime as amtime,cp.id as ischeckpm,cp.createtime as pmtime   FROM " . GetTableName('students') . " as s LEFT JOIN ( select id,createtime,sid FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE  leixing = 1  And isconfirm = 1 $condition  ORDER BY createtime ASC ) as cal  GROUP BY cal.sid  ) as ca ON ca.sid = s.id  LEFT JOIN ( select id,createtime,sid FROM ( SELECT * FROM ".GetTableName('checklog')." WHERE  leixing = 2  And isconfirm = 1 $condition  ORDER BY createtime ASC ) as cpl  GROUP BY cpl.sid  ) as cp ON cp.sid = s.id   where s.schoolid = '{$schoolid}' AND s.bj_id = '{$bj_id}'   ORDER BY id ASC  ";

            $students = pdo_fetchall($Sql);

			$rlogmub = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_checklog) . " where bj_id = :bj_id $condition ", array(':bj_id' => $bj_id));
			$nlogmub = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_checklog) . " where bj_id = :bj_id $condition ", array(':bj_id' => $bj_id));
			$snum = count($students);
			$wqdnum = 0;
			$yqdnum = 0;
			$wqdnumpm = 0;
			$yqdnumpm = 0;
			foreach($students as $index => $row){

                //重构数据库语句，优化查询方式，提升页面速度
				// $ischeck = pdo_fetch("SELECT id,createtime FROM " . tablename($this->table_checklog) . " where sid = {$row['id']} And bj_id = {$row['bj_id']} And leixing = 1 And isconfirm = 1 $condition ORDER BY createtime ASC LIMIT 0,1");
                // $ischeckpm = pdo_fetch("SELECT id,createtime FROM " . tablename($this->table_checklog) . " where sid = {$row['id']} And bj_id = {$row['bj_id']} And leixing = 2  And isconfirm = 1 $condition ORDER BY createtime DESC LIMIT 0,1");


				// $students[$index]['ischeck'] = $ischeck['id'];
				// $students[$index]['ischeckpm'] = $ischeckpm['id'];
				if(!$row['ischeck']){
					$wqdnum ++;
				}else{
					$students[$index]['amtime'] = $ischeck['createtime'];
					$students[$index]['logid'] = $ischeck['id'];
					$yqdnum ++;
				}
				if(!$row['ischeckpm']){
					$wqdnumpm ++;
				}else{
					$students[$index]['pmtime'] = $ischeckpm['createtime'];
					$students[$index]['logid'] = $ischeckpm['id'];
					$yqdnumpm ++;
				}
			}
			if($snum != 0 ){
				$jxl=round($yqdnum/$snum*100, 2);
				$lxl=round($yqdnumpm/$snum*100, 2);
			}

			include $this->template(''.$school['style3'].'/signlist');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }
?>