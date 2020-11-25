<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

global $_GPC, $_W;
$weid              = $_W['uniacid'];
$action            = 'grade';
$this1             = 'no2';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,shoucename,is_qx FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$operation         = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if (!(IsHasQx($tid_global,1000609,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
if($operation == 'display'){
	// if (!(IsHasQx($tid_global,1001101,1,$schoolid))){
	// 	$this->imessage('非法访问，您无权操作该页面','','error');	
	// }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';
    if(!empty($_GPC['createtime'])) {
        $starttime = strtotime($_GPC['createtime']['start']);
        $endtime = strtotime($_GPC['createtime']['end']) + 86399;
        $condition .= " AND createtime > '{$starttime}' AND createtime < '{$endtime}'";
    } else {
        $starttime = strtotime('-60 day');
        $endtime = TIMESTAMP;
    }

    if($_GPC['out_putcode'] == 'out_putcode'){
        $list = pdo_fetchAll("SELECT kcid,sid,createtime,anony,content,tid FROM " . tablename($this->table_kcpingjia) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and type = 1 $condition GROUP BY tid ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $ii   = 0;
        $ExcelTitle = array('评价人','课程','老师','评价时间','评语'); //标题
        foreach($list as $index => $row){
            $student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE id = '{$row['sid']}'");
            //课程信息
            $kc = pdo_fetch("SELECT name FROM " . GetTableName('tcourse') . " WHERE id = '{$row['kcid']}'");
            //老师信息
            $teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = '{$row['tid']}'");
            $arr[$ii]['s_name'] = $row['anony'] == 0 ? $student['s_name'] : '匿名';
            $arr[$ii]['kcname'] = $kc['name'];
            $arr[$ii]['tname'] = $teacher['tname'];
            $arr[$ii]['createtime'] = date("Y-m-d H:i",$row['createtime']);
            $arr[$ii]['content'] = $row['content'];
            //评分项
            $pfxm = pdo_fetchAll("SELECT pfxmid,star FROM " . tablename($this->table_kcpingjia) . " WHERE tid ='{$row['tid']}' and kcid='{$row['kcid']}' ORDER BY pfxmid");
            foreach($pfxm as $k1 => $v1){
                $sname = pdo_fetch("SELECT sname,sid FROM " . tablename($this->table_classify) . " WHERE sid = '{$v1['pfxmid']}'");
                if(!(in_array($sname['sname'], $ExcelTitle))){
                    $ExcelTitle[] =  $sname['sname'];

                }
                $arr[$ii][$sname['sid']] = $v1['star'];
            }
          
            $ii++;
        }
       // var_dump($arr);die;
        $nowtime = date('Y年m月d日',TIMESTAMP);
        $exceltitle = '评分记录表'.$nowtime.'导出';
        $this->exportexcel($arr, $ExcelTitle, $exceltitle);
        exit();
    }  


    $list = pdo_fetchAll("SELECT * FROM " . tablename($this->table_kcpingjia) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and type = 1 $condition GROUP BY tid ,sid ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

 
    
    foreach($list as $k => $v){
        //评分项
        $pfxm = pdo_fetchAll("SELECT pfxmid,star FROM " . tablename($this->table_kcpingjia) . " WHERE tid ='{$v['tid']}' and kcid='{$v['kcid']}' and sid = '{$v['sid']}' ORDER BY pfxmid ASC");
        foreach($pfxm as $k1 => $v1){
            $sname = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " WHERE sid = '{$v1['pfxmid']}'");
            $list[$k]['pfxm'][$k1]['sname'] = $sname['sname'];
            $list[$k]['pfxm'][$k1]['star'] = $v1['star'];
        }
        //课程信息
        $kc = pdo_fetch("SELECT name,thumb FROM " . GetTableName('tcourse') . " WHERE id = '{$v['kcid']}'");
        $list[$k]['kcname'] = $kc['name'];
        $list[$k]['thumb'] = tomedia($kc['thumb']);
        //学生信息
        $student = pdo_fetch("SELECT s_name FROM " . GetTableName('students') . " WHERE id = '{$v['sid']}'");
        $list[$k]['s_name'] = $v['anony'] == 0 ? $student['s_name'] : '匿名';

        //老师信息
        $teacher = pdo_fetch("SELECT tname FROM " . GetTableName('teachers') . " WHERE id = '{$v['tid']}'");
        $list[$k]['tname'] = $teacher['tname'];
        $list[$k]['createtime'] = date("Y-m-d H:i",$v['createtime']);
    }

    $total = pdo_fetchAll("SELECT id FROM " . tablename($this->table_kcpingjia) . " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' and type = 1 $condition GROUP BY tid ");
    $pager = pagination(count($total), $pindex, $psize);
}elseif($operation == 'delete'){
    $id   = intval($_GPC['id']);
    $list = pdo_fetch("SELECT id FROM " . tablename($this->table_sc) . " WHERE id = '{$id}' ");
    if(empty($list)){
        $this->imessage('抱歉，信息不存在或是已经被删除！', $this->createWebUrl('grade', array('op' => 'display', 'schoolid' => $schoolid)), 'error');
    }
    pdo_delete($this->table_sc, array('id' => $id));
    pdo_delete($this->table_scforxs, array('id' => $id));
    $this->imessage('删除成功！', $this->createWebUrl('grade', array('op' => 'display', 'schoolid' => $schoolid)), 'success');
}
include $this->template('web/grade');
?>