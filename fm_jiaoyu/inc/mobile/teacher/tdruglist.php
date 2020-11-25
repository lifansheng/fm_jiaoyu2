<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_W, $_GPC;
load()->func('tpl');
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
// 查询学校
$school = pdo_fetch("SELECT style3,title,spic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id", array(':weid' => $weid, ':id' => $schoolid));
 //查询是否用户登录
 $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
 //喂药动作列表
 $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));	
if(!empty($it)){
    $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
    if($operation == 'display'){
        //总数
        $total1 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}'");

        //未处理
        $total2 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND status = 0");

        //已通过
        $total3 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND status = 1");

        //已拒绝
        $total4 = pdo_fetchcolumn("SELECT count(id) FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' AND status = 2");

        $list = pdo_fetchall("SELECT * FROM ".GetTableName('drug')." WHERE schoolid = '{$schoolid}' AND weid = '{$weid}' ORDER BY createtime DESC  LIMIT 0,10 ");
        foreach ($list as $key => $value) {
            $student = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $list[$key]['icon'] = tomedia($student['icon']);
            $list[$key]['sname'] = $student['s_name'];
            $list[$key]['starttime'] = date("Y-m-d",$value['starttime']);
            $list[$key]['endtime'] = date("Y-m-d",$value['endtime']);
            $datetime = unserialize($value['datetime']);
            $list[$key]['datetime'] = arrayToString($datetime);
        }
        include $this->template(''.$school['style3'].'/tdruglist');       
    }elseif($operation == 'scroll_more'){
        $time = $_GPC['LiData']['time'];
        $ctype = $_GPC['LiData']['ctype'];
        $limit_start = $time + 1;
        if($ctype == '-1'){
            $conditions .= " ";
        }else{
            $conditions .= " AND status = '{$ctype}'";
            $limit_start = 0;
        }
        $list = pdo_fetchall("SELECT * FROM ".GetTableName('drug')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' $conditions ORDER BY createtime DESC LIMIT {$limit_start},10 ");
        foreach ($list as $key => $value) {
            $student = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$value['sid']}' ");
            $list[$key]['icon'] = tomedia($student['icon']);
            $list[$key]['sname'] = $student['s_name'];
            $list[$key]['starttime'] = date("Y-m-d",$value['starttime']);
            $list[$key]['endtime'] = date("Y-m-d",$value['endtime']);
            $datetime = unserialize($value['datetime']);
            $list[$key]['datetime'] = arrayToString($datetime);
            $list[$key]['location'] = $key + $limit_start;
        }
        include $this->template('comtool/tdruglist');
        exit;
    }elseif($operation == 'GetDrugInfo'){
        $id = $_GPC['id'];
        $schoolid = $_GPC['schoolid'];
        $weid = $_GPC['weid'];
        $data = pdo_fetch("SELECT * FROM ".GetTableName('drug')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' AND id = '{$id}' ");

        $student = pdo_fetch("SELECT icon,s_name FROM ".GetTableName('students')." WHERE id = '{$data['sid']}' ");
        $data['sname'] = $student['s_name'];

        $data['headimg'] = unserialize($data['headimg']);
        $data['datetime'] = arrayToString(unserialize($data['datetime']));
        include $this->template('comtool/tdruginfo');
        exit;
    }elseif($operation == 'refuse'){
        $id = $_GPC['id'];
        $schoolid = $_GPC['schoolid'];
        $refuse = $_GPC['refuse'];
        $druginfo = pdo_fetch("SELECT id FROM " . GetTableName('drug') . " WHERE schoolid = '{$schoolid}' AND id = '{$id}'");
        if(empty($druginfo)){
            $result['result'] = false;
            $result['msg'] = '抱歉，本条信息不存在或是已经被删除！';
        }else{
            pdo_update(GetTableName('drug',false),array('updatetime'=>time(),'status'=>2,'refuse'=>$refuse),array('id'=>$id));
            $result['result'] = true;
            $result['msg'] = '操作成功！';
        }
        die(json_encode($result));  
    }elseif($operation == 'argee'){
        $id = $_GPC['id'];
        $schoolid = $_GPC['schoolid'];
        //获取申请信息
        $druginfo = pdo_fetch("SELECT * FROM " . GetTableName('drug') . " WHERE id = '{$id}'");
        $sd = unserialize($druginfo['datetime']); //喂药时段
        //获取两个时间相差天数
        $diffday = diffBetweenTwoDays(date("Y-m-d",$druginfo['starttime']) ,date("Y-m-d",$druginfo['endtime']) ) +1;
        //获取班主任id
        $student = pdo_fetch("SELECT bj_id FROM " . GetTableName('students') . " WHERE id = '{$druginfo['sid']}'");
        $classify = pdo_fetch("SELECT tid FROM " . GetTableName('classify') . " WHERE schoolid = '{$schoolid}' AND sid = '{$student['bj_id']}'");
        for ($i=0; $i < $diffday; $i++) { 
            foreach ($sd as $key => $value) {
                $datetime = strtotime(date("Y-m-d $value",$druginfo['starttime'])) + $i*86400;
                $data = array(
                    'weid' => $weid,
                    'schoolid' => $schoolid,
                    'drugid' => $id,
                    'sid' => $druginfo['sid'],
                    'status'=>0,
                    'createtime'=>time(),
                    'datetime' => $datetime,
                    'tid' => $classify['tid'],
                );
                pdo_insert(GetTableName('druglog',false),$data);
            }
        }
        pdo_update(GetTableName('drug',false),array('updatetime'=>time(),'status'=>1),array('id'=>$id));   
        $result['result'] = true;
        $result['msg'] = '操作成功！';
        die(json_encode($result));  
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}

?>