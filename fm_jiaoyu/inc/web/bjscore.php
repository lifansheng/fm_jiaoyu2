<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'bjscore';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$km    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'subject' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
$bj    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'theclass' ORDER BY ssort DESC");
$nj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'semester' ORDER BY ssort DESC");
$xq = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'xq_score' ORDER BY ssort DESC");
$sd    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'timeframe' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));
$qh    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' AND schoolid ={$schoolid} And type = 'score' ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
$tid_global = $_W['tid'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if (!(IsHasQx($tid_global,1004101,1,$schoolid))){
    $this->imessage('非法访问，您无权操作该页面','','error');
}

if($operation == 'post'){
    if (!(IsHasQx($tid_global,1004102,1,$schoolid))){
        $this->imessage('非法访问，您无权操作该页面','','error');
    }
    load()->func('tpl');
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $item    = pdo_fetch("SELECT * FROM " . tablename($this->table_teascore) . " WHERE id = :id", array(':id' => $id));
        $bj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$item['bj_id']}' ");
        $nj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$item['nj_id']}' ");


        if(empty($item)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }
    }
    if(checksubmit('submit')){
        $data = array(
            'score' => trim($_GPC['score']),
        );

        if(empty($id)){
            $this->imessage('抱歉，本条信息不存在在或是已经删除！', '', 'error');
        }else{
            pdo_update($this->table_teascore, $data, array('id' => $id));
        }
        $this->imessage('修改班级量化评分成功！', $this->createWebUrl('bjscore', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
    }
}elseif($operation == 'out_list'){
    //导出班级量化积分*****************************************************

    $scoretime = strtotime($_GPC['out_scoretime']);
    if(empty($_GPC['out_scoretime'])){
         $this->imessage('抱歉，请先选择评分时间！','','error');
    }

    $condition .= " and scoretime = '{$scoretime}' ";


    //获取当前学期
    $score_xueqi = pdo_fetch("select * FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sd_start <='{$scoretime}' and sd_end >='{$scoretime}'  ");
    //获得当前月初始时间与结束时间
    $nowmonthstart = mktime(0, 0 , 0,date("m",$scoretime),1,date("Y",$scoretime));
    $nowmonthend = mktime(23,59,59,date("m",$scoretime),date("t",$scoretime),date("Y",$scoretime));
    //获取当前周初始时间与结束时间
    $nowweekstart = mktime(0,0,0,date("m",$scoretime),date("d",$scoretime)-date("w",$scoretime)+1,date("Y",$scoretime));
    $nowweekend = mktime(23,59,59,date("m",$scoretime),date("d",$scoretime)-date("w",$scoretime)+7,date("Y",$scoretime));
    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 2 ORDER BY id DESC  " );
    $xueqi_array = array();
    $month_array = array();
    $week_array  = array();
    $xueqi_rank_arr = array();
    $month_rank_arr = array();
    $week_rank_arr  = array();
    foreach($list as $key => $row){
        $bj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$row['bj_id']}' ");
        $nj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$row['nj_id']}' ");
        //学期得分与排名
        $all_score_xueqi = pdo_fetchcolumn("select SUM(score) FROM " . tablename($this->table_teascore) . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}' ");
        $count_before = pdo_fetchall(" select bj_id ,SUM(score) FROM " . tablename($this->table_teascore) . "  where   scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0 > {$all_score_xueqi}+0   " );
        $xueqi_rank = count($count_before)+1; //学期排名
        $xueqi_array[$key]['score'] = $all_score_xueqi;
        $xueqi_array[$key]['rank'] = $xueqi_rank;
        $xueqi_array[$key]['inside'] = "({$xueqi_rank})  {$bj_name['sname']}   {$all_score_xueqi}分 ";
        $xueqi_rank_arr[$key] = intval($xueqi_rank);
        //月得分与排名
        $all_score_month = pdo_fetchcolumn("select SUM(score) FROM " . tablename($this->table_teascore) . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$nowmonthstart}' AND scoretime <='{$nowmonthend}' ");
        $count_before_month = pdo_fetchall(" select bj_id  FROM " . tablename($this->table_teascore) . "  where   scoretime >='{$nowmonthstart}' AND scoretime <='{$nowmonthend}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0>{$all_score_month} +0   " );
        $month_rank = count($count_before_month)+1; //月排名
        $month_array[$key]['score'] = $all_score_month;
        $month_array[$key]['rank'] = $month_rank;
        $month_array[$key]['inside'] = "({$month_rank})  {$bj_name['sname']}   {$all_score_month}分 ";
        $month_rank_arr[$key] = intval($month_rank);
        //周得分与排名
        $all_score_week = pdo_fetchcolumn("select SUM(score) FROM " . tablename($this->table_teascore) . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$nowweekstart}' AND scoretime <='{$nowweekend}' ");
        $count_before_week = pdo_fetchall(" select bj_id  FROM " . tablename($this->table_teascore) . "  where   scoretime >='{$nowweekstart}' AND scoretime <='{$nowweekend}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0>{$all_score_week} +0   " );
        $week_rank = count($count_before_week)+1; //学期排名
        $week_array[$key]['score'] = $all_score_week;
        $week_array[$key]['rank'] = $week_rank;
        $week_array[$key]['inside'] = "({$week_rank})  {$bj_name['sname']}   {$all_score_week}分 ";
        $week_rank_arr[$key] = intval($week_rank);
    }

    array_multisort($week_rank_arr, SORT_ASC, $week_array);
    array_multisort($month_rank_arr, SORT_ASC, $month_array);
    array_multisort($xueqi_rank_arr, SORT_ASC, $xueqi_array);

        $ii = 0;
        $array_out = array();
        foreach($week_array as $key => $row){
            $array_out[$ii]['xueqi'] = $xueqi_array[$key]['inside'];
            $array_out[$ii]['month'] = $month_array[$key]['inside'];
            $array_out[$ii]['week'] = $week_array[$key]['inside'];
             $ii++;
        }
        /*var_dump($array_out);
        die();*/
        $first_title = array('学期排名','当月排名','当周排名');

            $title="班级评分信息-".date("Y-m-d",$scoretime);
            $this->exportexcel($array_out, $first_title, $title);
            exit();

}elseif($operation == 'display'){

    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;

    if(!empty($_GPC['nj_id'])){
        $condition .= " AND nj_id = '{$_GPC['nj_id']}'";
    }

    if(!empty($_GPC['bj_id'])){
        $condition .= " AND bj_id = '{$_GPC['bj_id']}'";
    }

    if(!empty($_GPC['scoretime'])){
        $starttime = strtotime($_GPC['scoretime']['start']);
        $endtime   = strtotime($_GPC['scoretime']['end']) + 86399;
        $condition .= " AND scoretime <= '{$endtime}'  AND scoretime >= '{$starttime}'";
    }else{
        $starttime = strtotime('-300 day');
        $endtime   = TIMESTAMP;
    }

    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 2 ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    //按成绩排名
    //var_dump($list);
    $nowweekstart = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
    $nowweekend = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));



    foreach($list as $key => $row){

        $score_xueqi = pdo_fetch("select * FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sd_start <='{$row['scoretime']}' and sd_end >='{$row['scoretime']}' and type='xq_score'  ");


        $all_score_xueqi = pdo_fetchcolumn("select SUM(score) FROM " . tablename($this->table_teascore) . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}' ");

        $count_before = pdo_fetchall(" select bj_id ,SUM(score) FROM " . tablename($this->table_teascore) . "  where   scoretime >='{$score_xueqi['sd_start']}' AND scoretime <='{$score_xueqi['sd_end']}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0 > {$all_score_xueqi}+0   " );
        $xueqi_rank = count($count_before)+1; //学期排名

        $nowmonthstart = mktime(0, 0 , 0,date("m",$row['scoretime']),1,date("Y",$row['scoretime']));
        $nowmonthend = mktime(23,59,59,date("m",$row['scoretime']),date("t",$row['scoretime']),date("Y",$row['scoretime']));
        $all_score_month = pdo_fetchcolumn("select SUM(score) FROM " . tablename($this->table_teascore) . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$nowmonthstart}' AND scoretime <='{$nowmonthend}' ");
        $count_before_month = pdo_fetchall(" select bj_id  FROM " . tablename($this->table_teascore) . "  where   scoretime >='{$nowmonthstart}' AND scoretime <='{$nowmonthend}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0>{$all_score_month} +0   " );
        $month_rank = count($count_before_month)+1; //学期排名




        $nowweekstart = mktime(0, 0 , 0,date("m",$row['scoretime']),date("d",$row['scoretime'])-date("w",$row['scoretime'])+1,date("Y",$row['scoretime']));
        $nowweekend = mktime(23,59,59,date("m",$row['scoretime']),date("d",$row['scoretime'])-date("w",$row['scoretime'])+7,date("Y",$row['scoretime']));
        $all_score_week = pdo_fetchcolumn("select SUM(score) FROM " . tablename($this->table_teascore) . "  where weid = '{$weid}' AND schoolid = '{$schoolid}' and bj_id = '{$row['bj_id']}' and type='2' AND scoretime >='{$nowweekstart}' AND scoretime <='{$nowweekend}' ");
        $count_before_week = pdo_fetchall(" select bj_id  FROM " . tablename($this->table_teascore) . "  where   scoretime >='{$nowweekstart}' AND scoretime <='{$nowweekend}' and type='2'  AND schoolid = '{$schoolid}'  group by bj_id   HAVING SUM(score)+0>{$all_score_week} +0   " );
        $week_rank = count($count_before_week)+1; //学期排名

        //var_dump($month_rank);



        //var_dump($row['bj_id']);
        //var_dump($all_score_xueqi);
       // var_dump($count_before);
        //var_dump($bj_rank_all);
       // echo "</br>";



        $bj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$row['bj_id']}' ");
        $nj_name = pdo_fetch("select sname FROM " . tablename($this->table_classify) . "  where  weid = '{$weid}' AND schoolid = '{$schoolid}' AND sid = '{$row['nj_id']}' ");


        $list[$key]['bj_name'] = $bj_name['sname'];
        $list[$key]['nj_name'] = $nj_name['sname'];
        $list[$key]['xueqi_score'] = $all_score_xueqi;
        $list[$key]['month_score'] = $all_score_month;
        $list[$key]['week_score'] = $all_score_week;
        $list[$key]['xueqi_rank'] = $xueqi_rank;
        $list[$key]['month_rank'] = $month_rank;
        $list[$key]['week_rank'] = $week_rank;
        $list[$key]['score_xueqi_name'] = $score_xueqi['sname'];


    }
    $total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename($this->table_teascore) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition  and type = 2 ORDER BY id DESC" );
   // var_dump($total);
    $pager = pagination($total, $pindex, $psize);

}elseif($operation == 'delete'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        $this->imessage('抱歉，本条信息不存在在或是已经被删除！');
    }
    pdo_delete($this->table_teascore, array('id' => $id));
    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'deleteall'){
    $rowcount    = 0;
    $notrowcount = 0;
    foreach($_GPC['idArr'] as $k => $id){
        $id = intval($id);
        if(!empty($id)){
            $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_teascore) . " WHERE id = :id", array(':id' => $id));
            if(empty($goods)){
                $notrowcount++;
                continue;
            }
            pdo_delete($this->table_teascore, array('id' => $id, 'weid' => $weid));
            $rowcount++;
        }
    }
    $message = "操作成功！共删除{$rowcount}条数据,{$notrowcount}条数据不能删除!";

    $data ['result'] = true;

    $data ['msg'] = $message;

    die (json_encode($data));
}
include $this->template('web/bjscore');
?>