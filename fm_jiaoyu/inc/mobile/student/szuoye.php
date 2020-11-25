<?php
/**
 * 微教育模块
 *QQ：332035136
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
       // $json = $_GPC['txtItemJson'];
        mload()->model('que');

        
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$leaveid = intval($_GPC['id']);
		$record_id = intval($_GPC['record_id']);
		//mload()->model('store');
		if (!empty($_GPC['userid'])){
		    $_SESSION['user'] = $_GPC['userid'];
		}
		$obid = 1;
        
        //查询是否用户登录
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id ", array(':id' => $_SESSION['user']));
		$userid = $it['id'];
		$leave = pdo_fetch("SELECT * FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $leaveid));
		$anstype = iunserializer($leave['anstype']);
		
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
		$category = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " WHERE weid =  :weid AND schoolid =:schoolid ORDER BY sid ASC, ssort DESC", array(':weid' => $weid, ':schoolid' => $schoolid), 'sid');
		
        $bzrtid = $category[$leave['bj_id']]['tid'];
		$bjname = $category[$leave['bj_id']]['sname'];
		$remark = pdo_fetch("SELECT * FROM " . tablename($this->table_ans_remark) . " where weid='{$weid}' and  schoolid='{$schoolid}'  And sid = '{$it['sid']}' And zyid ='{$leaveid}' ");
        if(!empty($it)){
			$allstud = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':schoolid' => $schoolid, ':bj_id' => $leave['bj_id']));			
			$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));
			$this->checkobjiect($schoolid, $it['sid'], $obid);
			$picarr = iunserializer($leave['picarr']);
			$recode = pdo_fetch("SELECT * FROM " . tablename($this->table_record) . " where schoolid = :schoolid And noticeid = :noticeid And sid = :sid And userid = :userid", array(':schoolid' => $schoolid,':noticeid' => $leaveid,':sid' => $it['sid'],':userid' => $it['id']));
			$ZY_contents = GetZyContent($leaveid,$schoolid,$weid);
			if ($recode){
				//$id = !empty($record_id) ? $record_id : $recode['id'];
				//$record = pdo_fetch("SELECT readtime FROM " . tablename($this->table_record) . " where id = :id", array(':id' => $recode['id']));
				if($recode['readtime'] == 0){
					$date = array(
						'readtime' =>time()
					);
					pdo_update($this->table_record, $date, array('id' => $recode['id']));				
				}			
			}else{
				$data = array(
					'weid' =>  $weid,
					'schoolid' => $schoolid,
					'noticeid' => $leaveid,
					'sid' => $it['sid'],
					'userid' => $it['id'],
					'openid' => $openid,
					'type' => $leave['type'],
					'createtime' => $leave['createtime'],
					'readtime' =>time()
				);
				pdo_insert($this->table_record, $data);		
			}
	$student = pdo_fetch("SELECT s_name,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $it['sid']));
			$testAA = GetMyAnswerAll($it['sid'] ,$leaveid,$schoolid,$weid);
			$testBB = GetMyAnswer_type7($it['sid'],$leaveid);
			if(is_showgkk())
			{
				//$teaPy 获取当前回答的批阅_C
				$teaPy = GetPyContent_c($it['sid'],$leaveid);
				//$teaPy 获取当前回答的批阅_P
				$teaPy_p = GetPyContent_p($it['sid'],$leaveid);
			}
		  include $this->template(''.$school['style2'].'/szuoye');
        }else{
			session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }        
?>