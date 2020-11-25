<?php
/*
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2020-05-06 16:14:16
 * @LastEditTime: 2020-05-08 11:28:42
 */
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$id = intval($_GPC['id']);
$openid = $_W['openid'];
$schoolid = intval($_GPC['schoolid']);
$school = pdo_fetch("SELECT style1,tpic,spic,logo,content,title,address,tel  FROM " . GetTableName('index') . " where id=:id", array(':id' => $schoolid));
$operation   = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if($operation == 'display'){
    $alltype = pdo_fetchall("SELECT sid,sname FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' And type = 'semester' ORDER BY sid ASC, ssort DESC ");
    if(!empty($alltype)){
        foreach($alltype as $key => $row){
            $alltype[$key]['kcclass'] = pdo_fetchall("SELECT sid,sname,parentid FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' And parentid = '{$row['sid']}' And type = 'kcclass' ORDER BY sid ASC, ssort DESC ");
        }
    }
    include $this->template(''.$school['style1'].'/kctypelist');
}
if($operation == 'kclist'){
    $nowtime = time();
    $keyword = trim($_GPC['keyword']); //课程名称查找
    $ke = !empty($_GPC['limit'])  ? $_GPC['limit'] :  0;
    if(!empty($_GPC['TypeIdArr'])){
        $TypeA = $_GPC['TypeIdArr']['a'];
        $TypeC = $_GPC['TypeIdArr']['c'];
        $TypeB = explode(',',$_GPC['TypeIdArr']['b']) ;
    }else {
        $TypeA = -1;
        $TypeC = -1;
        $TypeB = array('-1','-1');
    }



    $OrderCon = 'ORDER BY tc.ssort DESC , tc.end DESC';
    if($TypeA != '-1'){
        $OrderCon = "ORDER BY {$TypeA} DESC , tc.end DESC ";
    }

    $Condition = '';


	switch ( intval($TypeC) ){
		case -1 :
			break;

			break;
		case 1 : //团购
			$condition .= " And tc.sale_type = 1 and sale.endtime > '{$nowtime}'  ";
			break;
		case 2 ://助力
			$condition .= " And tc.sale_type = 2 and sale.endtime > '{$nowtime}'  ";
			break;
		case 3 :
            $condition .= " And tc.is_try = 1 ";
        case 4 :
            $condition .= " And tc.kc_type != 1" ;
            break;
        case 5 :
            $condition .= " And tc.kc_type = 1 ";
            break;
        case 6 : //推荐
            $condition .= " And tc.is_tuijian = 1 ";
            break;
        case 7 : //精品
            $condition .= " And tc.is_hot = 1 ";
            break;
		default:
			break;
    }


    // if($value['sale_type'] > 0){
    //     $saleset = pdo_fetch("SELECT endtime FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $value['sale_id']));
    //     if($saleset['endtime'] > $nowtime && $value['sale_type'] == 1){
    //         $kclist[$key]['tuan'] = true;
    //     }
    //     if($saleset['endtime'] > $nowtime && $value['sale_type'] == 2){
    //         $kclist[$key]['zhuli'] = true;
    //     }
    //     $kclist[$key]['enddd']  = date('Y-m-d',$saleset['endtime']);
    // }

if(!empty($_GPC['typeid'])){
    $TypeB = explode(',',$_GPC['typeid']) ;
    // $condition .= " And tc.xq_id = '{$_GPC['typeid']}'";
}
    if( intval($TypeB[0]) != -1){
        if(intval($TypeB[1] == -1)){
            $condition .= " And ( c.parentid =  '{$TypeB[0]}' or tc.xq_id = '{$TypeB[0]}' )";
        }else {
            $condition .= " And tc.xq_id = '{$TypeB[1]}' ";
        }
    }

    if(!empty($keyword)){
        $condition = " and tc.name like '%{$keyword}%' ";
    }
        // LEFT JOIN ".GetTableName('kcpingjia')." as kp

    $kclist = pdo_fetchall("
        SELECT
            (SELECT AVG(star)  FROM ".GetTableName('kcpingjia')." WHERE  kcid = tc.id And type = 1 And totid > 0 ) as allstar,tc.*
        FROM
            ".GetTableName('tcourse')." as tc
            LEFT JOIN ".GetTableName('kc_saleset')." as sale
            ON sale.id = tc.sale_id

            LEFT JOIN ".GetTableName('classify')." as c
            ON c.sid = tc.xq_id

        WHERE
            tc.schoolid = '{$schoolid}' and tc.is_show = 1  And tc.end > '{$nowtime}'  $condition  $OrderCon  LIMIT {$ke},6
        ");

    // $kclist = pdo_fetchall("SELECT * FROM " . GetTableName('tcourse') . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'AND is_show = 1 And end > '{$nowtime}' ORDER BY ssort DESC , end DESC ");
    foreach($kclist as $key => $value){
        $kclist[$key]['extra'] = $ke + 1 + $key;
        $kclist[$key]['fans_list'] = GetKcStuFans($value['id'],4);
        $kclist[$key]['zhuli'] = false; $kclist[$key]['tuan'] = false;
        $kclist[$key]['pingjia'] = false;$kclist[$key]['hastry'] = false;$kclist[$key]['hasvideo'] = false;$kclist[$key]['alive'] = false;
        if($value['kc_type'] == 1){
            $checktry = pdo_fetch("SELECT id FROM ".GetTableName('kcbiao')." WHERE  kcid = '{$value['id']}'  And is_try_see = 1  ");
            if(!empty($checktry)){
                $kclist[$key]['hastry'] = true;
            }
            $checkvideo = pdo_fetch("SELECT id FROM ".GetTableName('kcbiao')." WHERE  kcid = '{$value['id']}'  And content_type = 2  ");
            if(!empty($checkvideo)){
                $kclist[$key]['hasvideo'] = true;
            }
            $checkalive = pdo_fetch("SELECT * FROM ".GetTableName('kcbiao')." WHERE  kcid = '{$value['id']}'  And content_type = 1 And sk_start < '{$nowtime}' And sk_end >  '{$nowtime}' ");
            if(!empty($checkalive)){
                $kclist[$key]['hasvideo'] = false;
                $kclist[$key]['alive'] = true;
            }
        }
        $kcpingjia = pdo_fetchall("SELECT star FROM ".GetTableName('kcpingjia')." WHERE  kcid = '{$value['id']}' And type = 1 And totid > 0 ");
        if($kcpingjia){
            $star = 0;
            foreach($kcpingjia as $v){
                $star = $star + $v['star'];
            }
            $kclist[$key]['pingjia'] =round( $star/count($kcpingjia),1);
        }
        if($value['sale_type'] > 0){
            $saleset = pdo_fetch("SELECT endtime FROM " . GetTableName('kc_saleset') . " WHERE id = :id ", array(':id' => $value['sale_id']));
            if($saleset['endtime'] > $nowtime && $value['sale_type'] == 1){
                $kclist[$key]['tuan'] = true;
            }
            if($saleset['endtime'] > $nowtime && $value['sale_type'] == 2){
                $kclist[$key]['zhuli'] = true;
            }
            $kclist[$key]['enddd']  = date('Y-m-d',$saleset['endtime']);
        }
    }
    include $this->template(''.$school['style1'].'/kctypelist_temp');
}
?>