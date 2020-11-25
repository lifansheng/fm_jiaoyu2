<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_GPC, $_W;
       include 'phpqrcode.php';  
        $weid = $_W['uniacid'];
        $action = 'kecheng';
		$this1 = 'no2';
		$pjrid = $_GPC['pjrid'];
		
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
	    $schoolid = intval($_GPC['schoolid']);
		$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $sid));
		$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));		
		$km = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
		$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
		$xq = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'week', ':schoolid' => $schoolid));
		$sd = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));
		$qh = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
        $category = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " WHERE weid = :weid AND schoolid = :schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $condition = '';
			$gkkid = $_GPC['gkkid'];
			
			$gkkinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$gkkid}'");
			
			$gkkteacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$gkkinfo['tid']}'");
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_gkkpj) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and gkkid = '{$gkkid}' AND userid='{$pjrid}' ORDER BY id ASC ");
            $userinfoarr = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id='{$pjrid}'  ");
           if(!empty($userinfoarr['sid']))
           {
	           $student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id='{$userinfoarr['sid']}'  ");
	           $xq = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid='{$student['xq_id']}'  ");
	           $bj =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and sid='{$student['bj_id']}'  ");
	           $listinfo['xq'] = $xq['sname'];
	           $listinfo['bj'] = $bj['sname'];
	           $listinfo['is_xs'] = 1 ;
           }
					$userarray = get_myname($weid,$pjrid,$schoolid);					
					$pard = get_guanxi($userarray['pard']);
					if(!$pard){
						$pard = '本人';
					}
					if($userarray['type'] == 1)
					{
						$listinfo['username'] =$userarray['s_name'];
						$listinfo['pard'] = $pard;
					}elseif($userarray['type'] == 2)
					{
						$listinfo['username'] =$userarray['tname'];
						$listinfo['pard'] = '老师';
					}

					
            foreach($list as $key => $row){
                if($row['iconid'] != 0 )
                {
					$pjxxinfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_gkkpjk) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id='{$row['iconid']}'  ");
					
					if($row['iconlevel'] == 1 )
					{
						$pjlevel = $pjxxinfo['icon1title'];
					}
					if($row['iconlevel'] == 2 )
					{
						$pjlevel = $pjxxinfo['icon2title'];
					}
					if($row['iconlevel'] == 3 )
					{
						$pjlevel = $pjxxinfo['icon3title'];
					}
					if($row['iconlevel'] == 4 )
					{
						$pjlevel = $pjxxinfo['icon4title'];
					}
					if($row['iconlevel'] == 5 )
					{
						$pjlevel = $pjxxinfo['icon5title'];
					}
					$list[$key]['title'] = $pjxxinfo['title'] ;
					$list[$key]['level'] = $pjlevel ;
                    $listfinish[$pjxxinfo['ssort']] = $list[$key];
                }
            }
    $listfinish[] = $list[0];
            //var_dump($listfinish);

        include $this->template ( 'web/showpjdetail' );
?>