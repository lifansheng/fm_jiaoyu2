<?php
/**
 * 微教育模块
 *
 * @author  
 */
        global $_W, $_GPC;
        $weid = $this->weid;
        $from_user = $this->_fromuser;
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        $school = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
        $op = $_GPC['op'] ? $_GPC['op'] : 'display';
        //查询是否用户登录		
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
        if(!empty($it['id'])){
            if($op == 'display'){
                $FzList = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'jsfz' ");
                $bjlist_temp = pdo_fetchall("SELECT bj.sid as bjid,bj.sname as bjname,nj.sid as njid , nj.sname as njname FROM ".GetTableName('classify')." as bj LEFT JOIN ".GetTableName('classify')." as nj ON nj.sid = bj.parentid  WHERE bj.schoolid = '{$schoolid}' and bj.type = 'theclass' and nj.type = 'semester' ");
                $bjlist = [];
                foreach($bjlist_temp as $value){
                    $njid = $value['njid'];
                    if(empty($bjlist[$njid])){
                        $bjlist[$njid] = [];
                    }
                    $bjlist[$njid]['bjlist'][] = $value;
                    $bjlist[$njid]['njname'] = $value['njname'];
                }
                $FzList[] = array(
                    'sid' => 0,
                    'sname' => '未分组'
                );
                include $this->template(''.$school['style3'].'/quesform/createquesform');
                die;
            }elseif($op == 'save'){
                $teacher = pdo_fetch("SELECT * FROM " . tablename($this->table_teachers) . " WHERE id = '{$it['tid']}'");
                $temp = array(
                    'weid'       => $weid,
                    'schoolid'   => $schoolid,
                    'tid'        => $it['tid'],
                    'tname'      => $teacher['tname'],
                    'title'      => $_GPC['title'],
                    'content'    => htmlspecialchars_decode($_GPC['content']),
                    'createtime' => time(),
                    'starttime' => strtotime($_GPC['start']),
                    'endtime' => strtotime($_GPC['end'])+86399,
                    'type'       => 5,
                    'is_research'=> 1,
                );

                if ($_GPC['type'] == 1) {
                    $temp['usertype'] = 'school';
                }
                if ($_GPC['type'] == 2) {
                    $temp['usertype'] = 'alltea';
                }
                if ($_GPC['type'] == 3) {
                    $temp['usertype'] = 'allstu';
                }
                if ($_GPC['type'] == 4) {
                    $temp['usertype'] = 'jsfz';
                    $temp['userdatas'] = arrayToString($_GPC['fzlist']);
                }
                if ($_GPC['type'] == 5) {
                    $temp['usertype'] = 'bj';
                    $temp['userdatas'] = arrayToString($_GPC['bjlist']);
                } 
                pdo_insert($this->table_notice, $temp);
                $notice_id = pdo_insertid();
                // $notice_id = 2679;
                // (问卷写入)
                $quesList = $_GPC['quesList'];
                if(!empty($quesList)){
                    foreach($quesList as $key=>$value){
                        $WJ_temp = array(
                            'weid'     => $weid,
                            'schoolid' => $schoolid,
                            'tid'      => $it['tid'],
                            'zyid'     => $notice_id,
                            'qorder'   => $value['displayorder'],
                            'title'    => $value['title'],
                            'type'     => $value['type']
                        );
                        $temp1=[];
                        if($value['anslist']){
                            foreach ($value['anslist'] as $keys=>$values){
                                $temp1[$keys+1] = array(
                                    'title' =>$values
                                );
                            }
                            $temp11 = iserializer($temp1);
                            $WJ_temp['content'] = $temp11;
                        }
                        
                        pdo_insert($this->table_questions,$WJ_temp);
                    }
                }
                if($_GPC['issend'] == 1){
                    $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
                    $data = array(
                        'notice_id' => $notice_id,
                        'schoolid' => $schoolid,
                        'weid' => $weid,
                        'tname' => $teacher['tname'],
                        'schooltype' => $schooltype,
                        'op' => 'sendMobileQuesForm',
                    );
                    timeOutPost($url, $data);
                }
                $result['msg'] = '创建成功，请勿重新创建';
                $result['result'] = true;
                die(json_encode($result));
            }
            
		}else{
         	$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
		}        
?>