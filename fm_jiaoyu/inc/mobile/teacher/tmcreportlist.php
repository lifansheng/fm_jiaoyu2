<?php
global $_W, $_GPC;
$weid = $_W['uniacid'];
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];
$operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
$tid_global = $it['tid'];
$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
mload()->model('tea');
$bjlists = GetAllClassInfoByTid($schoolid,'1',$_W['schooltype'],$tid_global);
if(!empty($it)){
    $teacher = pdo_fetch("SELECT * FROM ".GetTableName("teachers")." WHERE id = '{$tid_global}' ");
    if($operation == 'display'){
        $year = date("Y",time());
        $xq = '上学期';
        $condition = " AND m.bjid = '{$bjlists[0]['sid']}' AND m.type = '1' ";
        
        $condition .= " AND m.year = '{$year}'";
        $list = pdo_fetchall("SELECT m.*,s.s_name,s.icon,c.sname FROM ".GetTableName('mcreportlist')." as m LEFT JOIN " . GetTableName('students') . " as s ON s.id = m.sid LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = s.bj_id WHERE m.schoolid = '{$schoolid}' $condition ORDER BY m.id DESC LIMIT 0,20");
        include $this->template(''.$school['style3'].'/tmcreportlist');
    }elseif($operation == 'More_Data'){ //筛选内容
        $Limit_start = $_GPC['LiData']['time'] ? $_GPC['LiData']['time'] +1 : 0 ;
        $condition = " AND m.bjid = '{$_GPC['bjid']}' AND m.type = '{$_GPC['typeid']}' ";
        
        $condition .= " AND m.year = '{$_GPC['year']}'";	
    
        if(!empty($_GPC['xqListKey']) && $_GPC['typeid'] == 2){
            $condition .= " AND m.semestertype = '{$_GPC['xqListKey']}'";	
        }
        $list = pdo_fetchall("SELECT m.*,s.s_name,s.icon,c.sname FROM ".GetTableName('mcreportlist')." as m LEFT JOIN " . GetTableName('students') . " as s ON s.id = m.sid LEFT JOIN " . GetTableName('classify') . " as c ON c.sid = s.bj_id WHERE m.schoolid = '{$schoolid}' {$condition} ORDER BY m.id DESC LIMIT {$Limit_start},20");
        
        foreach ($list as $k_1 => $v_1) {
            $list[$k_1]['location'] = $k_1 + $Limit_start;
        }
        include $this->template('comtool/tmcreportlist');
    }elseif($operation == 'GetApData'){ //拼接年份和学期
        $xqList = array(
            array('id'=>1,'name'=>'上学期'),
            array('id'=>2,'name'=>'下学期'),
        );
        $searchType = array(
            array('id'=>1,'name'=>'月度报告'),
            array('id'=>2,'name'=>'学期报告'),
            array('id'=>3,'name'=>'年度报告'),
        );
        $bjList = [];
        foreach ($bjlists as $key => $value) {
            $bjList[$key]['id'] = $value['sid'];
            $bjList[$key]['name'] = $value['old_sname'];
        }
        $yearList = pdo_fetchall("SELECT DISTINCT(year) as name FROM ".GetTableName('mcreportlist')." WHERE schoolid = '{$schoolid}' ORDER BY year");
        foreach ($yearList as $key => $value) {
            $yearList[$key]['id'] = $value['name'];
        }

        $result['yearList'] = $yearList;
        $result['xqList'] = $xqList;
        $result['bjList'] = $bjList;
        $result['searchType'] = $searchType;
        die(json_encode($result));

    }elseif($operation == 'getStuList'){
        
        $bjid = $_GPC['__input']['bjid'];
        $StudentList = pdo_fetchall("SELECT s_name as stuName,id FROM ".GetTableName('students')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and bj_id = '{$bjid}' ");

        if(!empty($StudentList)){
            $result['status'] =true;
            $result['msg'] = "获取学生列表成功";
            $result['data'] = $StudentList;
        }else{
            $result['status'] = false;
            $result['msg'] = "获取学生列表失败，请稍后再试";
        }
        die(json_encode($result));

    }elseif($operation == 'getStudentReport'){
        mload()->model('znl');
        $StudentList = znlGetReport($schoolid,$_GPC['__input']['id'],'','',0);
        $result['status'] =true;
        $result['msg'] = "获取学生报告成功";
        $result['data'] = $StudentList;
        die(json_encode($result));
    }
}else{
    session_destroy();
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
    exit;
}