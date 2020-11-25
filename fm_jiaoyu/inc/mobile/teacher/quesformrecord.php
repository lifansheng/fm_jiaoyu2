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
        $noticeid = $_GPC['noticeid'];
        $school = pdo_fetch("SELECT style3,spic,tpic FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ORDER BY ssort DESC", array(':weid' => $weid, ':id' => $schoolid));
        
        //查询是否用户登录
        $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
        if (!empty($it['id'])) {
			$op = $_GPC['op'] ? $_GPC['op'] : 'display';
			if($op == 'getAnsInfo'){
				mload()->model('que');
				
				$ansinfo = pdo_fetch("SELECT * FROM ".GetTableName('answers')." WHERE id = '${_GPC['ansid']}' ");
				
				$usertype = 0;
				if($ansinfo['tid'] !='0' && $ansinfo['sid'] == '0'){
					$usertype = 1;
				}

				if($usertype == 1){
					$testAA = GetMyAnswerAll_tea($ansinfo['tid'],$ansinfo['zyid'],$schoolid,$weid);
				}elseif($usertype == 0){
					$testAA = GetMyAnswerAll($ansinfo['sid'],$ansinfo['zyid'],$schoolid,$weid);
				}
				$ZY_contents = GetZyContent($ansinfo['zyid'],$schoolid,$weid);
				$result['ques'] = $ZY_contents;
				$result['ans'] = $testAA;
				$result['status'] = true;
				//   die(json_encode($result));
				include $this->template(''.$school['style3'].'/quesform/quesrecord_bot');
				die();
			}


            $leave = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $noticeid));


            if ($leave['usertype'] == 'school') {
                $teachers = "SELECT ans.id as ansid, t.id,t.tname as name, t.thumb as icon, c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid LEFT JOIN ".GetTableName('answers')." as ans ON ans.tid = t.id WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}' and ans.zyid='{$noticeid}'  ORDER BY t.id DESC GROUP BY t.id";
				$teachersn = "SELECT  t.id,t.tname as name, t.thumb as icon, c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid   WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'  ORDER BY t.id DESC GROUP BY t.id";
				
                $students = "SELECT ans.id as ansid, s.id,s.s_name as name, s.icon as icon, c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid LEFT JOIN ".GetTableName('answers')." as ans ON ans.sid = s.id WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'   and ans.zyid='{$noticeid}' ORDER BY s.id DESC GROUP BY s.id";
                $studentsn = "SELECT   s.id,s.s_name as name, s.icon as icon, c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid   WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'   ORDER BY s.id DESC GROUP BY s.id";
                $sql = " SELECT * FROM ( {$teachers} ) as t union ( {$students} ) ";
                $sqln = " SELECT * FROM ( {$teachersn} ) as t union ( {$studentsn} ) ";
            } elseif ($leave['usertype'] == 'alltea') {
                $sql = "SELECT ans.id as ansid, t.id,t.tname as name,t.thumb as icon, c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid LEFT JOIN ".GetTableName('answers')." as ans ON ans.tid = t.id WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'  and ans.zyid='{$noticeid}' GROUP BY t.id ";
                $sqln = "SELECT t.id,t.tname as name,t.thumb as icon, c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid   WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'    GROUP BY t.id ";
            } elseif ($leave['usertype'] == 'allstu') {
                $sql = "SELECT ans.id as ansid, s.id,s.s_name as name,s.icon as icon,c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid LEFT JOIN ".GetTableName('answers')." as ans ON ans.sid = s.id WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'  and ans.zyid='{$noticeid}' GROUP BY s.id ";
                $sqln = "SELECT  s.id,s.s_name as name,s.icon as icon,c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid   WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'    GROUP BY s.id ";
            } elseif ($leave['usertype'] == 'bj') {
                $sql = "SELECT ans.id as ansid, s.id,s.s_name as name,s.icon as icon,c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid LEFT JOIN ".GetTableName('answers')." as ans ON ans.sid = s.id WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'  and ans.zyid='{$noticeid}' AND FIND_IN_SET(s.bj_id,'{$leave['userdatas']}') GROUP BY s.id ";
                $sqln = "SELECT s.id,s.s_name as name,s.icon as icon,c.sname,1 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE s.id = a.sid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('students') . " as s LEFT JOIN " . GetTableName('classify') . " as c ON s.bj_id = c.sid   WHERE s.weid = '{$weid}' AND s.schoolid = '{$schoolid}'    AND FIND_IN_SET(s.bj_id,'{$leave['userdatas']}') GROUP BY s.id ";
            } elseif ($leave['usertype'] == 'jsfz') {
                $sql = "SELECT ans.id as ansid, t.id,t.tname as name,t.thumb as icon, c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid LEFT JOIN ".GetTableName('answers')." as ans ON ans.tid = t.id WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'  and ans.zyid='{$noticeid}' AND FIND_IN_SET(t.fz_id,'{$leave['userdatas']}') GROUP BY t.id ";
                $sqln = "SELECT  t.id,t.tname as name,t.thumb as icon, c.sname,0 as usertype,IFNULL((SELECT createtime FROM " . GetTableName('answers') ." as a WHERE t.id = a.tid AND a.zyid = '{$noticeid}' LIMIT 1),0) as createtime FROM " . GetTableName('teachers') . " as t LEFT JOIN " . GetTableName('classify') . " as c ON t.fz_id = c.sid   WHERE t.weid = '{$weid}' AND t.schoolid = '{$schoolid}'    AND FIND_IN_SET(t.fz_id,'{$leave['userdatas']}') GROUP BY t.id ";
            }
            $ansList = pdo_fetchall("SELECT * FROM ( ".$sql." ) as aaa WHERE aaa.createtime > 0");
            foreach ($ansList as &$v_a) {
                if (empty($v_a['icon'])) {
                    $v_a['icon'] = $v_a['usertype'] == 1 ? $school['spic'] : $school['tpic'];
                }
            }
            $NotAnsList = pdo_fetchall("SELECT * FROM ( ".$sqln." ) as aaa WHERE aaa.createtime <= 0");
            foreach ($NotAnsList as &$v_na) {
                if (empty($v_na['icon'])) {
                    $v_na['icon'] = $v_na['usertype'] == 1 ? $school['spic'] : $school['tpic'];
                }
            }
            $Count = pdo_fetch("SELECT IFNULL(SUM(case when createtime > 0 then 1 else 0 end),0) as ans,count(*) as allc FROM ( ".$sqln." ) as aaa");

            include $this->template(''.$school['style3'].'/quesform/quesformrecord');
        } else {
            session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
            header("location:$stopurl");
        }
