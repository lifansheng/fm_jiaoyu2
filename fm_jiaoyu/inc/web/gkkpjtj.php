<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid = $_W['uniacid'];
$this1 = 'no2';
$action = 'kecheng';
$schoolid = intval($_GPC['schoolid']);


$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'],$action);
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$logo = pdo_fetch("SELECT logo,title,is_cost,tpic,spic FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$tid_global = $_W['tid'];
if($tid_global !='founder' && $tid_global != 'owner'){
	$loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid And id =:id ", array(':weid' => $weid,':schoolid' => $schoolid,':id'=>$tid_global));
	$qxarr = GetQxByFz($loginTeaFzid['fz_id'],1,$schoolid);
}
if (!(IsHasQx($tid_global,1000959,1,$schoolid))){
	$this->imessage('非法访问，您无权操作该页面','','error');	
}
/*if ($operation == 'gettongji_gkk'){


	
		$gkkid = $_GPC['gkkid'];
		$schoolid = $_GPC['schoolid'];
		$weid = $_W['uniacid'];
	
	$gkkpjinfo = pdo_fetchall("SELECT distinct iconid FROM " . tablename($this->table_gkkpj) . " where gkkid =:gkkid and schoolid = :schoolid  AND weid = :weid ", array(':gkkid'=>$gkkid,  ':schoolid' =>$schoolid, ':weid' => $weid ));

	 $gkkall = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_gongkaike) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ");
	foreach( $gkkpjinfo as $key => $value )
	{
		if($value['iconid'])
		{
			$level1 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('wx_school_gkkpj') . " where gkkid= :gkkid AND schoolid = :schoolid  AND weid = :weid And iconid =:iconid AND iconlevel = :iconlevel", array( ':gkkid' =>$gkkid, ':schoolid' =>$schoolid, ':weid' => $weid,':iconid'=>$value['iconid'],':iconlevel'=> 1 ));
			$level2 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('wx_school_gkkpj') . " where gkkid= :gkkid AND schoolid = :schoolid  AND weid = :weid And iconid =:iconid AND iconlevel = :iconlevel", array( ':gkkid' =>$gkkid, ':schoolid' =>$schoolid, ':weid' => $weid,':iconid'=>$value['iconid'],':iconlevel'=> 2 ));
			$level3 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('wx_school_gkkpj') . " where gkkid= :gkkid AND schoolid = :schoolid  AND weid = :weid And iconid =:iconid AND iconlevel = :iconlevel", array( ':gkkid' =>$gkkid, ':schoolid' =>$schoolid, ':weid' => $weid,':iconid'=>$value['iconid'],':iconlevel'=> 3 ));
			$level4 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('wx_school_gkkpj') . " where gkkid= :gkkid AND schoolid = :schoolid  AND weid = :weid And iconid =:iconid AND iconlevel = :iconlevel", array( ':gkkid' =>$gkkid, ':schoolid' =>$schoolid, ':weid' => $weid,':iconid'=>$value['iconid'],':iconlevel'=> 4 ));
			$level5 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('wx_school_gkkpj') . " where gkkid= :gkkid AND schoolid = :schoolid  AND weid = :weid And iconid =:iconid AND iconlevel = :iconlevel", array( ':gkkid' =>$gkkid, ':schoolid' =>$schoolid, ':weid' => $weid,':iconid'=>$value['iconid'],':iconlevel'=> 5 ));
			$xiangmu = pdo_fetch("SELECT * FROM " . tablename('wx_school_gkkpjk') . " where  schoolid = :schoolid  AND weid = :weid And id =:id ", array(':schoolid' =>$schoolid, ':weid' => $weid,':id'=>$value['iconid'] ));
			
			$JieGuo_temp = array(
				'question_content' => $xiangmu['title'],
			);

			$key_temp  ;
			if(!empty( $xiangmu['icon1title']))
			{
				$key_temp[0]['name'] = $xiangmu['icon1title'];
				$key_temp[0]['content'] = $xiangmu['icon1title'];
				$key_temp[0]['y'] = intval($level1);
			}
		
			if(!empty( $xiangmu['icon2title']))
			{
				$key_temp[1]['name'] = $xiangmu['icon2title'];
				$key_temp[1]['content'] = $xiangmu['icon21title'];
				$key_temp[1]['y'] =intval( $level2);
			}
			if(!empty( $xiangmu['icon3title']))
			{
				$key_temp[2]['name'] = $xiangmu['icon3title'];
				$key_temp[2]['content'] = $xiangmu['icon3title'];
				$key_temp[2]['y'] = intval($level3);
			}
			if(!empty( $xiangmu['icon4title']))
			{
				$key_temp[3]['name'] = $xiangmu['icon4title'];
				$key_temp[3]['content'] = $xiangmu['icon4title'];
				$key_temp[3]['y'] = intval($level4);
			}
			if(!empty( $xiangmu['icon5title']))
			{
				$key_temp[4]['name'] = $xiangmu['icon5title'];
				$key_temp[4]['content'] = $xiangmu['icon5title'];
				$key_temp[4]['y'] = intval($level5);
			}

			$str = json_encode($key_temp);
			 $do_str =  substr($str, 1,strlen($str)-2);
			$JieGuo_temp['question_data'] = $do_str ;
			
			$JieGuo_temp11 = array( $JieGuo_temp);
			$fanhui = json_encode($JieGuo_temp11);
			$fanhui1 = '"{'.$fanhui.'}"';
			
			$backinfo[] = $JieGuo_temp;
			unset($key_temp);
		}
		
	}
	
 include $this->template ( 'web/gkkpjtj' );
}*/

if ($operation == 'gettongji_js'){
    $out_excel = $_GPC['out_excel'];
    $allls    = pdo_fetchall("SELECT id,tname FROM " . tablename($this->table_teachers) . " WHERE  weid=:weid and schoolid = :schoolid ORDER BY  CONVERT(tname USING gbk)  ASC ", array(':schoolid' => $schoolid,':weid'=>$weid));

    if(!empty($_GPC['createtime'])){
        $starttime = strtotime($_GPC['createtime']['start']);
        $endtime   = strtotime($_GPC['createtime']['end']) + 86399;
    }else{
        $starttime = strtotime('-300 day');
        $endtime   = TIMESTAMP;
    }
    $condition .= " AND createtime <= '{$endtime}'  AND createtime >= '{$starttime}'";
    if(!empty($_GPC['tid'])){
        $tid = $_GPC['tid'];
    }elseif(!empty($_GPC['bas_tid'])){
        $tid = $_GPC['bas_tid'];
    }


    $teacher =  pdo_fetch("SELECT id,tname FROM " . tablename($this->table_teachers) . " where id =:id and schoolid = :schoolid  AND weid = :weid ", array(':id'=>$tid,  ':schoolid' =>$schoolid, ':weid' => $weid ));
    $teaname = $teacher['tname'];
	//var_dump($teacher);
	$tid =$teacher['id']; 
		$gkkid = $_GPC['gkkid'];
		$schoolid = $_GPC['schoolid'];
		$weid = $_W['uniacid'];
	$excel_arr = array();
	$title = array();
    $title['first'] = '评价项目';
	$gkkpjinfo = pdo_fetchall("SELECT distinct iconid FROM " . tablename($this->table_gkkpj) . " where tid ='{$tid}' and schoolid = '{$schoolid}'  AND weid = '{$weid}' $condition order by iconid ASC ");
$backinfo = array();
	 $gkkall = pdo_fetchall('SELECT id,name FROM ' . tablename($this->table_gongkaike) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ");
	foreach( $gkkpjinfo as $key => $value )
	{
		if($value['iconid'])
		{
			$level1 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_gkkpj). " where tid ='{$tid}' and schoolid = '{$schoolid}'  AND weid = '{$weid}'  And iconid ='{$value['iconid']}' AND iconlevel = 1 $condition ");
			$level2 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_gkkpj) . " where  tid ='{$tid}' and schoolid = '{$schoolid}'  AND weid = '{$weid}'  And iconid ='{$value['iconid']}' AND iconlevel = 2 $condition ");
			$level3 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_gkkpj) . " where tid ='{$tid}' and schoolid = '{$schoolid}'  AND weid = '{$weid}'  And iconid ='{$value['iconid']}' AND iconlevel = 3 $condition ");
			$level4 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_gkkpj). " where tid ='{$tid}' and schoolid = '{$schoolid}'  AND weid = '{$weid}'  And iconid ='{$value['iconid']}' AND iconlevel = 4 $condition ");
			$level5 = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_gkkpj) . " where tid ='{$tid}' and schoolid = '{$schoolid}'  AND weid = '{$weid}'  And iconid ='{$value['iconid']}' AND iconlevel = 5 $condition ");
			$xiangmu = pdo_fetch("SELECT * FROM " . tablename($this->table_gkkpjk) . " where  schoolid = :schoolid  AND weid = :weid And id =:id ", array(':schoolid' =>$schoolid, ':weid' => $weid,':id'=>$value['iconid'] ));
			
			$JieGuo_temp = array(
				'question_content' => $xiangmu['title'],
			);
            $excel_arr[$xiangmu['ssort']]['title'] = $xiangmu['title'];
			$key_temp = array();
			if(!empty( $xiangmu['icon1title']))
			{
				$key_temp[0]['name'] = $xiangmu['icon1title'];
				$key_temp[0]['content'] = $xiangmu['icon1title'];
				$key_temp[0]['y'] = intval($level1);
                $excel_arr[$xiangmu['ssort']]['ob1name'] = $xiangmu['icon1title'];
                $excel_arr[$xiangmu['ssort']]['ob1count'] = intval($level1);
                $title['ob1'] ='选项1';
                $title['ob1count'] ='数量';
			}

			if(!empty( $xiangmu['icon2title']))
			{
				$key_temp[1]['name'] = $xiangmu['icon2title'];
				$key_temp[1]['content'] = $xiangmu['icon21title'];
				$key_temp[1]['y'] =intval( $level2);
                $excel_arr[$xiangmu['ssort']]['ob2name'] = $xiangmu['icon2title'];
                $excel_arr[$xiangmu['ssort']]['ob2count'] = intval($level2);
                $title['ob2'] ='选项2';
                $title['ob2count'] ='数量';
			}
			if(!empty( $xiangmu['icon3title']))
			{
				$key_temp[2]['name'] = $xiangmu['icon3title'];
				$key_temp[2]['content'] = $xiangmu['icon3title'];
				$key_temp[2]['y'] = intval($level3);
                $excel_arr[$xiangmu['ssort']]['ob3name'] = $xiangmu['icon3title'];
                $excel_arr[$xiangmu['ssort']]['ob3count'] = intval($level3);
                $title['ob3'] ='选项3';
                $title['ob3count'] ='数量';
			}
			if(!empty( $xiangmu['icon4title']))
			{
				$key_temp[3]['name'] = $xiangmu['icon4title'];
				$key_temp[3]['content'] = $xiangmu['icon4title'];
				$key_temp[3]['y'] = intval($level4);
                $excel_arr[$xiangmu['ssort']]['ob4name'] = $xiangmu['icon4title'];
                $excel_arr[$xiangmu['ssort']]['ob4count'] = intval($level4);
                $title['ob4'] ='选项4';
                $title['ob4count'] ='数量';
			}
			if(!empty( $xiangmu['icon5title']))
			{
				$key_temp[4]['name'] = $xiangmu['icon5title'];
				$key_temp[4]['content'] = $xiangmu['icon5title'];
				$key_temp[4]['y'] = intval($level5);
                $excel_arr[$xiangmu['ssort']]['ob5name'] = $xiangmu['icon5title'];
                $excel_arr[$xiangmu['ssort']]['ob5count'] = intval($level5);
                $title['ob5'] ='选项5';
                $title['ob5count'] ='数量';
			}
            //var_dump($key_temp);
			$str = json_encode($key_temp);
			 $do_str =  substr($str, 1,strlen($str)-2);
			$JieGuo_temp['question_data'] = $do_str ;
			
			$JieGuo_temp11 = array( $JieGuo_temp);
			$fanhui = json_encode($JieGuo_temp11);
			$fanhui1 = '"{'.$fanhui.'}"';
			
			$backinfo[$xiangmu['ssort']] = $JieGuo_temp;
			unset($key_temp);
		}

	}
	ksort($backinfo);
    //var_dump($backinfo);

    if($out_excel == "Yes"){
        $startdate = date("y年m月d日",$starttime);
        $enddate = date("y年m月d日",$endtime);
        $name="{$teaname}【{$startdate}-{$enddate}】统计";
        //跨浏览器解决下载文件乱码
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/Edge/", $ua)) {//Edge浏览器
            $name = urlencode($name);
        }elseif(preg_match("/MSIE/", $ua)){//IE浏览器
            $name = urlencode($name);
        }
        ksort($excel_arr);
        $this->exportexcel($excel_arr,$title, $name);
        exit();

    }

    include $this->template ( 'web/gkkpjtj' );
}	


?>