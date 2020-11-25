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
    $bjid = $_GPC['bjid'];
    $qhid = $_GPC['qhid'];
    //查询是否用户登录		
    $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
    $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
    $tid_global = $it['tid'];
    $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
    mload()->model('tea');

    $qh = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$qhid}' ");
    $bj = pdo_fetch("SELECT sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$bjid}'  ");
    $stulist = pdo_fetchall("SELECT s_name,id,icon FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' and bj_id = '{$bjid}' and keyid = id ORDER BY CONVERT(s_name USING gbk) ASC ");
    foreach ($stulist as $key => $value) {
        $stulist[$key]['isdone'] = false;
        $check = pdo_fetchall("SELECT * FROM ".GetTableName('behaviorscorelog')." WHERE schoolid = '{$schoolid}' and tid = '{$tid_global}' and sid = '{$value['id']}' and qhid = '{$qhid}' ",array(),'bhsid');
        if(!empty($check)){
            $stulist[$key]['isdone'] = true;
        }
        if(empty($value['icon'])){
            $stulist[$key]['icon'] = $school['spic'];
        }
    }
    $NowStuId = $stulist[0]['id'];
 
    $op = $_GPC['op'] ? $_GPC['op'] : 'display';
    if($op == 'getscorellist'){
        $stuid = $_GPC['stuid'];

        $bhs_list = pdo_fetchall("SELECT sname,sid FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and FIND_IN_SET('{$bjid}',qh_bjlist) and type = 'behaviorscore' order by ssort DESC ");
        $StuBHSList = pdo_fetchall("SELECT * FROM ".GetTableName('behaviorscorelog')." WHERE schoolid = '{$schoolid}' and qhid = '{$qhid}' and sid = '{$stuid}' and tid = '{$tid_global}' ",array(),'bhsid');
        foreach ($bhs_list as $key => $value) {
           $bhs_list[$key]['setscore'] = $StuBHSList[$value['sid']]['score'] ? $StuBHSList[$value['sid']]['score'] : 5 ; 
           $bhs_list[$key]['setword']  = $StuBHSList[$value['sid']]['word'] ? $StuBHSList[$value['sid']]['word'] : '' ; 
        }
        $IsDone = false;
        if(!empty($StuBHSList)){
            $IsDone = true;
        }
        die(json_encode(array(
            'bhslist' => $bhs_list,
            'isdone' => $IsDone
        )));
    }elseif($op == 'display'){
        if(!empty($userid['id'])){
            if(!empty($qhlist)){
                $qhid = $qhlist[0]['sid'];
                if(!empty($_GPC['qhid'])){
                    $qhid = $_GPC['qhid'];
                }
            }
            $bjlist = GetAllClassInfoByTid($schoolid,0,$_W['schooltype'],$tid_global);
            include $this->template(''.$school['style3'].'/tbhsdetail');
        }else{
            session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
            header("location:$stopurl");
        }   
    }elseif($op == 'getBHSword'){
        $BHSid = $_GPC['BHSid'];
        $word = pdo_fetch("SELECT addedinfo FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and sid = '{$BHSid}' ");
        die($word['addedinfo']);
    }elseif($op == 'SaveBHS'){
        $Score = $_GPC['score'];
        $Word = $_GPC['word'];
        $StuId = $_GPC['stuid'];
        $QhId = $_GPC['qhid']; 
      
         
        foreach($Score as $key_s => $value_s){
            $insertData = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'sid' => $StuId,
                'qhid' => $QhId,
                'score' => $value_s,
                'word' => $Word[$key_s],
                'bhsid' => $key_s,
                'createtime' => time(),
                'tid' => $tid_global 
            );
            $tid = $tid_global  ;
            $check = pdo_fetch("SELECT id FROM ".GetTableName('behaviorscorelog')." WHERE schoolid = '{$schoolid}' and sid = '{$StuId}' and qhid = '{$QhId}' and bhsid = '{$key_s}' and tid = '{$tid}' ");
            if(!empty($check)){
                pdo_update(GetTableName('behaviorscorelog',false),$insertData,array('id' => $check['id']));
            }else{
                pdo_insert(GetTableName('behaviorscorelog',false),$insertData);
            }
        }
        die(json_encode(array(
            'status' => true,
            'msg' => '提交成功'
        )));
    }
		     
?>