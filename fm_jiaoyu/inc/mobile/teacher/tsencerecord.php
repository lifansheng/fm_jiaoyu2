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
		$tid_global = $it['tid'];		
        if(!empty($userid['id'])){
			$thistime = trim($_GPC['limit']);
			if($thistime){
				$condition = " AND id < '{$thistime}'";
				
				$sencelistall1 = pdo_fetchall("SELECT * FROM " . tablename($this->table_upsence) . " where weid ='{$weid}' AND schoolid = '{$schoolid}' $condition ORDER BY id DESC LIMIT 0,6 ");
				foreach($sencelistall1 as $key => $row){
					$checkup = pdo_fetch("SELECT * FROM " . tablename($this->table_teasencefiles) . " where weid = '{$weid}' and schoolid = '{$schoolid}' and senceid = '{$row['id']}' and tid = '{$it['tid']}' ");
					$qxfz =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' and schoolid = '{$schoolid}' and sid = '{$row['qxfzid']}' and type = 'jsfz' ");
					if(!empty($checkup)){
						$sencelistall1[$key]['has_up'] = true;
						$sencelistall1[$key]['uptime'] =  $checkup['createtime'];
					}else{
						$sencelistall1[$key]['has_up'] = false;
					}
					$sencelistall1[$key]['fzname'] = $qxfz['sname'];
				}
				include $this->template('comtool/tsencerecord'); 
			}else{
				$sencelistall = pdo_fetchall("SELECT * FROM " . tablename($this->table_upsence) . " where weid ='{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC LIMIT 0,6 ");
				foreach($sencelistall as $key => $row){
					$checkup = pdo_fetch("SELECT * FROM " . tablename($this->table_teasencefiles) . " where weid = '{$weid}' and schoolid = '{$schoolid}' and senceid = '{$row['id']}' and tid = '{$it['tid']}' ");
					$qxfz =  pdo_fetch("SELECT sname FROM " . tablename($this->table_classify) . " where weid = '{$weid}' and schoolid = '{$schoolid}' and sid = '{$row['qxfzid']}' and type = 'jsfz' ");
					if(!empty($checkup)){
						$sencelistall[$key]['has_up'] = true;
						$sencelistall[$key]['uptime'] =  $checkup['createtime'];
					}else{
						$sencelistall[$key]['has_up'] = false;
					}
					$sencelistall[$key]['fzname'] = $qxfz['sname'];
				}
				include $this->template(''.$school['style3'].'/tsencerecord');	
			}
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>