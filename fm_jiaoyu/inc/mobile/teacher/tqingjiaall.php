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
		$time = $_GPC['time'];
        
        //查询是否用户登录		
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid)); 		
        if(!empty($userid['id'])){
			$user = pdo_fetch("SELECT status FROM " . tablename($this->table_teachers) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $it['tid']));
			if($_GPC['op'] == 'scroll_add'){
				$ajax_startdate = $_GPC['startdate'];
				$ajax_enddate = $_GPC['enddate'];
				$ajax_starttime = strtotime($ajax_startdate);
				$ajax_endtime = strtotime($ajax_enddate);
				if($ajax_endtime<=$ajax_starttime ){
					$result['status'] = 1;
					$result['msg'] = '开始日期必须小于结束日期';
					die(json_encode($result));
				}else{
					$condition_this .= " AND createtime >= '{$ajax_starttime}' AND createtime <= '{$ajax_endtime}'";
					$tealist_this = pdo_fetchall("SELECT distinct tid FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and tid != 0 {$condition_this} ORDER BY createtime DESC, id DESC ");
					if(!empty($tealist_this)){
						foreach($tealist_this as $key=>$row){
							$all_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' $condition_this ");
							$all_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' $condition_this ");
							$tealist_this[$key]['allcount'] = intval($all_day).'天</br>+'.intval($all_ks).'节'; //总数统计
							$bing_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='病假' $condition_this ");
							$bing_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='病假' $condition_this ");
							$tealist_this[$key]['bingcount'] = intval($bing_day).'天</br>+'.intval($bing_ks).'节'; //病假统计
							$shi_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='事假'  $condition_this ");
							$shi_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='事假'  $condition_this ");
							$tealist_this[$key]['shicount'] = intval($shi_day).'天</br>+'.intval($shi_ks).'节'; //事假统计
							$chai_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='公差'  $condition_this ");
							$chai_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='公差'  $condition_this ");
							$tealist_this[$key]['chaicount'] = intval($chai_day).'天</br>+'.intval($chai_ks).'节'; //公差统计
							$qita_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='其他'  $condition_this ");
							$qita_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='其他'  $condition_this ");
							$tealist_this[$key]['qitacount'] = intval($qita_day).'天</br>+'.intval($qita_ks).'节'; //其他统计
							$teacherinfo = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id = '{$row['tid']}' ");
							$tealist_this[$key]['tname'] = $teacherinfo['tname'];
						}

						$result['status'] = 2;
						$result['msg'] = '获取数据成功！';
						$result['data'] = $tealist_this;
						$result['allnum'] = count($tealist_this);
						die(json_encode($result));

						
					}else{
						$result['status'] = 3;
						$result['msg'] = '当前时间范围内无教师请假信息';
						die(json_encode($result));
						
					}
					
				}
				exit();
			}else{
				$this_starttime_t = strtotime(date("Y-m-d",time()));
				$this_endtime = $this_starttime_t + 86399;
				$this_starttime =  $this_starttime_t - 86400*60;
				$condition_this .= " AND createtime >= '{$this_starttime}' AND createtime <= '{$this_endtime}'";
				$tealist_this = pdo_fetchall("SELECT distinct tid FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and tid != 0 $condition_this ORDER BY createtime DESC, id DESC ");
				foreach($tealist_this as $key=>$row){
					$all_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' $condition_this ");
					$all_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' $condition_this ");
					$tealist_this[$key]['allcount'] = intval($all_day).'天</br>+'.intval($all_ks).'节'; //总数统计
					$bing_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='病假' $condition_this ");
					$bing_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='病假' $condition_this ");
					$tealist_this[$key]['bingcount'] = intval($bing_day).'天</br>+'.intval($bing_ks).'节'; //病假统计
					$shi_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='事假'  $condition_this ");
					$shi_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='事假'  $condition_this ");
					$tealist_this[$key]['shicount'] = intval($shi_day).'天</br>+'.intval($shi_ks).'节'; //事假统计
					$chai_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='公差'  $condition_this ");
					$chai_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='公差'  $condition_this ");
					$tealist_this[$key]['chaicount'] = intval($chai_day).'天</br>+'.intval($chai_ks).'节'; //公差统计
					$qita_day = pdo_fetchcolumn("SELECT sum((endtime1 - startime1 + 1)/86400) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =2 and tid= '{$row['tid']}' and type='其他'  $condition_this ");
					$qita_ks = pdo_fetchcolumn("SELECT sum(ksnum) FROM " . tablename($this->table_leave) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and status = 1 And isliuyan = 0 and more_less =1 and tid= '{$row['tid']}' and type='其他'  $condition_this ");
					$tealist_this[$key]['qitacount'] = intval($qita_day).'天</br>+'.intval($qita_ks).'节'; //其他统计
					$teacherinfo = pdo_fetch("SELECT tname FROM " . tablename($this->table_teachers) . " WHERE weid = '{$weid}' And schoolid = '{$schoolid}' and id = '{$row['tid']}' ");
					$tealist_this[$key]['tname'] = $teacherinfo['tname'];
				}
				$allnum = count($tealist_this);
				include $this->template(''.$school['style3'].'/tqingjiaall');
			}
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>