<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$noticeid = intval($_GPC['noticeid']);
		$openid = $_W['openid'];
		mload()->model('que');
		$bj_id = intval($_GPC['bj_id']);
		if(!empty($_GPC['sid'])){
			$sid = intval($_GPC['sid']);
		}else{
			$fristsid = pdo_fetch("SELECT id FROM " . tablename($this->table_students) . " where schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
			$sid = $fristsid['sid'];
		}
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
        if(!empty($it)){
			$school = pdo_fetch("SELECT style1,title,spic,headcolor FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
			$nowbj = pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where :sid = sid", array(':sid' => $bj_id));
			$leave = pdo_fetch("SELECT title,anstype FROM " . tablename($this->table_notice) . " where :id = id", array(':id' => $noticeid));
			
			$anstype = unserialize($leave['anstype']);
			//如果是电脑端发布的作业，$ZY_contents不为空
			$ZY_contents = GetZyContent($noticeid,$schoolid,$weid);
			//$answer获取的是手机端发布的作业的回答内容
			$answer = GetMyAnswer_type7($sid,$noticeid);
			//$testAA获取的是电脑端发布的作业的回答内容
			$testAA =GetMyAnswerAll($sid,$noticeid);
			//$teaPy 获取当前回答的批阅_C(不管是不是这个老师的)
			$teaPy = GetPyContent_c($sid,$noticeid);
			//$teaPy 获取当前回答的批阅_P(不管是不是这个老师的)
			$teaPy_p = GetPyContent_p($sid,$noticeid);
			
			$allstud = pdo_fetchall("SELECT id,s_name,icon FROM " . tablename($this->table_students) . " where schoolid = :schoolid And bj_id = :bj_id ORDER BY id DESC", array(':schoolid' => $schoolid, ':bj_id' => $bj_id));
			$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where schoolid = :schoolid AND id = :id", array(':schoolid' => $schoolid, ':id' => $sid));
			include $this->template(''.$school['style1'].'/showhomework');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>