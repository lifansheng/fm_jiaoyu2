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
      	$tid_global = $_W['tid'];
		if ($operation == 'display') {
			if (!(IsHasQx($tid_global,1000955,1,$schoolid))){
				$this->imessage('非法访问，您无权操作该页面','','error');	
			}
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $condition = '';
			$gkkid = $_GPC['gkkid'];
			$gkkinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$gkkid}'");
			$gkkteacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$gkkinfo['tid']}'");
            $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_gkkpj) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and gkkid = '{$gkkid}'AND type='1' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
				foreach($list as $key => $row){
					$userarray = get_myname($weid,$row['userid'],$schoolid);					
					$pard = get_guanxi($userarray['pard']);
					if(!$pard){
						$pard = '本人';
					}
					if($userarray['type'] == 1)
					{
						$list[$key]['username'] =$userarray['s_name'];
						$list[$key]['pard'] = $pard;
					}elseif($userarray['type'] == 2)
					{
						$list[$key]['username'] =$userarray['tname'];
						$list[$key]['pard'] = '老师';
					}
				}
				
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_gkkpj) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and gkkid = '{$gkkid}'AND type='1'");
            $pager = pagination($total, $pindex, $psize);
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，本条信息不存在在或是已经被删除！');
            }
            pdo_delete($this->table_gkkpj, array('id' => $id));
            message('删除成功！', referer(), 'success');
        } elseif ($operation == 'deleteall') {
            $rowcount = 0;
            $notrowcount = 0;
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                if (!empty($id)) {
                    $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_gkkpj) . " WHERE id = :id", array(':id' => $id));
                    if (empty($goods)) {
                        $notrowcount++;
                        continue;
                    }
                    pdo_delete($this->table_gongkaike, array('id' => $id, 'weid' => $weid));
                    $rowcount++;
                }
            }
            message("操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!", '', 0);
        }elseif($operation == 'out_putcode'){
			$outid = $_GPC['outid'];
			$gkkid = $_GPC['gkkid'];
			$weid = $_GPC['weid'];
			$schoolid = $_GPC['schoolid'];
			
			$gkkinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$gkkid}'");
            $gkkteacher = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$gkkinfo['tid']}'");
		 	$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_gkkpj) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and gkkid = '{$gkkid}' AND userid='{$outid}' ORDER BY iconid ASC ");
		 	$userarray = get_myname($weid,$outid,$schoolid);					
			$pard = get_guanxi($userarray['pard']);
			if(!$pard){
				$pard = '本人';
			}
			if($userarray['type'] == 1)
			{
				$username =$userarray['s_name'];
				$pard = $pard;
			}elseif($userarray['type'] == 2)
			{
				$username =$userarray['tname'];
				$pard= '老师';
			}
            $createtime = '';
			foreach($list as $key => $row){
			    $createtime = date("y年m月d日",$row['createtime']);
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
                    $list[$key]['ssort'] = $pjxxinfo['ssort'];
                $listfinish[$pjxxinfo['ssort']] = $list[$key];
                }else{
                    $list[$key]['title'] = '评语';
                    $list[$key]['level'] = $row['content'];
                    $list[$key]['ssort'] = -1 ;
                }
			}
            $listfinish[] = $list[0];

			$arr[0]['name'] = '主讲人';
            $arr[0]['code'] = $gkkteacher['tname'];
            $arr[1]['name'] = '评价日期';
            $arr[1]['code'] = $createtime;
            $MaxIndex   = 2;
			foreach($listfinish as $index => $row){

			    if($row['ssort'] >=0 ){
                    $index_this = $row['ssort'] + 2;
                    $MaxIndex = $MaxIndex<$index_this?$index_this:$MaxIndex;
                    $arr[$index_this]['name'] = $row['title'];
                    $arr[$index_this]['code']  = $row['level'];
                    $ii++;
                }
			}
            $arr[$MaxIndex+1]['name'] = $list[0]['title'];
            $arr[$MaxIndex+1]['code']  = $list[0]['level'];
            ksort($arr);
            //var_dump($arr);
            //die();
            $name = $username.$pard."对公开课【".$gkkinfo['name']."】的评价-{$createtime}";
            //跨浏览器解决下载文件乱码
            $ua = $_SERVER["HTTP_USER_AGENT"];
            if (preg_match("/Edge/", $ua)) {//Edge浏览器
                $name = urlencode($name);
            }elseif(preg_match("/MSIE/", $ua)){//IE浏览器
                $name = urlencode($name);
            }
			$this->exportexcel($arr, array('选项','评价'), $name);
            exit();
		}elseif($operation == 'out_putcode_all'){
			$gkkid = $_GPC['gkkid'];
			$weid = $_GPC['weid'];
			$schoolid = $_GPC['schoolid'];
			$i_p = 0 ;
			$i_tit = 1 ;
			$arr_out = array();
			$list_all = pdo_fetchall("SELECT userid FROM " . tablename($this->table_gkkpj) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and gkkid = '{$gkkid}'AND type='1' ORDER BY id DESC " );
			$gkkinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_gongkaike) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id = '{$gkkid}'");
			$bzinfo = pdo_fetchall("SELECT title FROM " . tablename($this->table_gkkpjk) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and bzid='{$gkkinfo['bzid']}' ORDER BY id DESC ");
			$excel_title = array();
			$excel_title[0] = "评价人";
			foreach( $bzinfo as $key_b => $value_b )
			{
				$excel_title[$i_tit] = $value_b['title'];
				$i_tit++;
			}
			$excel_title[$i_tit] = "评语";
			foreach( $list_all as $key => $value )
			{
				$userarray = get_myname($weid,$value['userid'],$schoolid);					
				$pard = get_guanxi($userarray['pard']);
				if(!$pard){
					$pard = '本人';
				}
				if($userarray['type'] == 1)
				{
					$username =$userarray['s_name'];
					$pard = $pard;
				}elseif($userarray['type'] == 2)
				{
					$username =$userarray['tname'];
					$pard= '老师';
				}
				$arr_out[$i_p]['name'] = $username.$pard;
				$oneinfo = pdo_fetchall("SELECT * FROM " . tablename($this->table_gkkpj) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and gkkid = '{$gkkid}'AND userid='{$value['userid']}' ORDER BY iconid DESC ");
				foreach($oneinfo as $key1 => $row1){
				if($row1['iconid'] != 0 )
				{
					$pjxxinfo =  pdo_fetch("SELECT * FROM " . tablename($this->table_gkkpjk) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' and id='{$row1['iconid']}'  ");
					if($row1['iconlevel'] == 1 )
					{
						$pjlevel = $pjxxinfo['icon1title'];
					}
					if($row1['iconlevel'] == 2 )
					{
						$pjlevel = $pjxxinfo['icon2title'];
					}
					if($row1['iconlevel'] == 3 )
					{
						$pjlevel = $pjxxinfo['icon3title'];
					}
					if($row1['iconlevel'] == 4 )
					{
						$pjlevel = $pjxxinfo['icon4title'];
					}
					if($row1['iconlevel'] == 5 )
					{
						$pjlevel = $pjxxinfo['icon5title'];
					}
					$arr_out[$pjxxinfo['ssort']][$pjxxinfo['title']] = $pjlevel;
					}else{
						$arr_out[10000]['评语'] = $row1['content'];
					}
					$listfinish[$key+1] = $list[$key];
				}
				$i_p++;	
			}
            $name = "公开课【".$gkkinfo['name']."】的评价";


            //跨浏览器解决下载文件乱码
            $ua = $_SERVER["HTTP_USER_AGENT"];
            if (preg_match("/Edge/", $ua)) {//Edge浏览器
                $name = urlencode($name);
            }elseif(preg_match("/MSIE/", $ua)){//IE浏览器
                $name = urlencode($name);
            }

            ksort($arr_out);
			$this->exportexcel($arr_out,$excel_title, $name);
            exit();
		}

        include $this->template ( 'web/showgkkpj' );
?>