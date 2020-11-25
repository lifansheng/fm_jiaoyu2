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
		$schooltype = $_W['schooltype'];
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :openid = openid And :sid = sid", array(':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0));
        if(!empty($it)){
			$school = pdo_fetch("SELECT style3,title,videopic,logo FROM " . tablename($this->table_index) . " where id = :id", array(':id' => $schoolid));
			mload()->model('tea');
			$allkclist = GetAllClassInfoByTid($schoolid,1,$schooltype,$it['tid']);
			if($_W['schooltype']){
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND videotype = 2 ");
				$jiaoshi = pdo_fetchall("SELECT video,sname,sid,( SELECT thumb FROM " . GetTableName('index') . " WHERE id = '{$schoolid}') as thumb FROM " . tablename($this->table_classify) . " WHERE weid = '{$weid}' And type = 'addr' And schoolid = '{$schoolid}' AND video != '' ORDER BY sid ASC, ssort DESC");
				foreach ($list as $key => $row) {
					$plsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 2");
					$dzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 1");
					$isdz  = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $row['id'], ':userid' => $it['id']));
					$list[$key]['plsl'] = $plsl;
					$list[$key]['dianzan'] = $dzsl;
				}
				include $this->template(''.$school['style3'].'/newtallcamera');
				die;
			}else{
				if($_GPC['bj_id']){
					$nowbj = $_GPC['bj_id'];
				}else{
					$nowbj = $allkclist[0]['sid'];
				}
				$mybj = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And sid = '{$nowbj}' ");
				$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_allcamera) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY ssort DESC");
				foreach ($list as $key => $row) {
					$plsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 2");
					$dzsl  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_camerapl) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' AND carmeraid = '{$row['id']}' And type = 1");
					$isdz  = pdo_fetch("SELECT id FROM " . tablename($this->table_camerapl) . " where weid = :weid AND schoolid = :schoolid AND carmeraid = :carmeraid AND userid = :userid", array(':weid' => $weid, ':schoolid' => $schoolid, ':carmeraid' => $row['id'], ':userid' => $it['id']));
					$list[$key]['plsl'] = $plsl;
					$list[$key]['dianzan'] = $dzsl;
					$list[$key]['isdz'] = $isdz;
					if(!empty($row['bj_id'])){
						$list[$key]['myvideo'] = explode(',', $row['bj_id']);
						foreach($list[$key]['myvideo'] as $r) {
								$list[$key]['myvideo']['pic'] == $row['videopic'];
								$list[$key]['myvideo']['name'] == $row['name'];
						}
					}
				}
			}
			include $this->template(''.$school['style3'].'/tallcamera');
        }else{
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
        }
?>