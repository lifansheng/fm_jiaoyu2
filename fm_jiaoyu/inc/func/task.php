<?php
/**
 * By 高贵血迹
 */
	global $_GPC, $_W;
	$weid = $_GPC['i'];
	$schoolid = $_GPC['schoolid'];
	$starttime = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$endtime = $starttime + 86399 + 48*60*60;
	if(!empty($schoolid)){
		$condition1 = " AND schoolid = '{$schoolid}' ";
	}
	$condition = " AND date > '{$starttime}' AND date < '{$endtime}'";
	$condition2 = " AND sk_start > '{$starttime}' AND sk_start < '{$endtime}'";
	$alljobs = pdo_fetchall("SELECT id,kcid,status,type FROM " . GetTableName('task') . " WHERE status = 1 $condition1 ");
	if($alljobs){
		foreach($alljobs as $key => $row){
			if($row['type'] == 1){//课程提醒
				$kcinfo = pdo_fetch("SELECT id,is_tx,txtime,remindday,kc_type FROM " .GetTableName ('tcourse'). " where id = :id ", array(':id' => $row['kcid']));
				if($kcinfo['is_tx'] == 1){
					if($kcinfo['kc_type'] == 1){
						$allks = pdo_fetchall("SELECT id,weid,schoolid,sk_start,sk_end FROM " . GetTableName('kcbiao') . " WHERE kcid = '{$row['kcid']}' And content_type = 1 $condition2 ");//只查询直播课时
						if($allks){
							foreach($allks as $k => $val){
								$checksend = pdo_fetch("SELECT * FROM " . GetTableName('task_list') . " WHERE ksid = '{$val['id']}' And taskid = '{$row['id']}' And remind_type = 0");//查询提醒任务
								if(empty($checksend)){//为空则执行
									$nowtime = time();
									$check_start = $val['sk_start'];
									$ksstarttime = $check_start - $kcinfo['txtime']*60;
									if($nowtime >= $ksstarttime){//判断时间
										$data = array(
											'weid' => $val['weid'],
											'schoolid' =>  $val['schoolid'],
											'taskid' => $row['id'],
											'ksid' => $val['id'],
											'type' => 1,
											'createtime' => $nowtime
										);
										pdo_insert(GetTableName('task_list',false), $data);
										$this->sendMobileJssktx($val['id'],$val['schoolid'],$val['weid']);
									}
								}
							}
						}
					}else{
						//$send_kctx = send_kctx($row['kcid'],$kcinfo['txtime']);
						$allks = pdo_fetchall("SELECT id,weid,schoolid,sd_id,date FROM " . GetTableName('kcbiao') . " WHERE kcid = '{$row['kcid']}' $condition ");
						if($allks){
							foreach($allks as $k => $val){
								$sdinfo = pdo_fetch("SELECT sd_start FROM " . GetTableName ('classify') . " where sid = :sid ", array(':sid' => $val['sd_id']));
								if($sdinfo){
									$checksend = pdo_fetch("SELECT * FROM " . GetTableName('task_list') . " WHERE ksid = '{$val['id']}' And taskid = '{$row['id']}' And remind_type = 0");//查询提醒任务
									if(empty($checksend)){//为空则执行
										$nowtime = time();
										$check_start = strtotime(date("Y-m-d",$val['date']).date(" H:i",$sdinfo['sd_start']));
										$ksstarttime = $check_start - $kcinfo['txtime']*60;
										if($nowtime >= $ksstarttime){//判断时间
											$data = array(
												'weid' => $val['weid'],
												'schoolid' =>  $val['schoolid'],
												'taskid' => $row['id'],
												'ksid' => $val['id'],
												'type' => 1,
												'createtime' => $nowtime
											);
											pdo_insert(GetTableName('task_list',false), $data);
											$this->sendMobileJssktx($val['id'],$val['schoolid'],$val['weid']);
										}
									}
								}
							}
						}
					}
				}
                $nowtime_over = time();
				if(keep_sk77()){//定制
				    if($kcinfo['remindday'] != 0){//设置了过期时间

				        $StuByList = pdo_fetchall("SELECT sid,overtime FROM " .GetTableName('coursebuy'). " WHERE schoolid = '{$schoolid}' And weid = '{$weid}' And kcid = '{$row['kcid']}' ");
				        foreach ($StuByList as $keyS=>$valueS){

                            if($valueS['overtime'] != 0 ){//存在过期时间
                                $overtimeRemind = $valueS['overtime'] - $kcinfo['remindday']*86400;
                                if($nowtime_over > $overtimeRemind && $nowtime_over < $valueS['overtime'] ){
                                    //即将过期
                                    $checksend_over = pdo_fetch("SELECT * FROM " . GetTableName('task_list') . " WHERE taskid = '{$row['id']}' and sid ='{$valueS['id']}' and kcid ='{$row['kcid']}' and  remind_type = 1");//查询提醒任务
                                    if(empty($checksend_over)){
                                        $data_over = array(
                                            'weid'        => $val['weid'],
                                            'schoolid'    => $val['schoolid'],
                                            'taskid'      => $row['id'],
                                            'type'        => 1,
                                            'createtime'  => $nowtime,
                                            'kcid'        => $row['kcid'],
                                            'sid'         => $valueS['id'],
                                            'remind_type' => 1
                                        );
                                        pdo_insert(GetTableName('task_list',false), $data_over);
                                        $this->sendMobileOvertimeTx($row['kcid'],$valueS['id'],$val['schoolid'],$val['weid']);
                                    }
                                }
                            }
                        }
                    }
                }
			}
		}
		$data['alljobs'] = $alljobs;
		$data['result'] = true;
	}else{
		$data['msg'] = "当前无可执行任务";
		$data['result'] = true;
	}
	echo json_encode($data);
	exit;
?>