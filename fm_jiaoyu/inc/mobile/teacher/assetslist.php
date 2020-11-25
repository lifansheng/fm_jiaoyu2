<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W ['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		
        $user = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
        $tid_global = $user['tid'];
        $school = pdo_fetch("SELECT style3 FROM ".GetTableName('index')." WHERE id = {$schoolid} ");
        $op = $_GPC['op'] ? $_GPC['op'] : 'display';
        if(!empty($tid_global)){

            if($op == 'display'){
                $list = pdo_fetchall("SELECT * FROM ".GetTableName('assets')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' LIMIT 0,10 ");
                foreach($list as $key=>$value){
                    $checkuse = pdo_fetchcolumn("SELECT sum(num) FROM ".GetTableName('assetstake')." WHERE schoolid = '{$schoolid}' and gwid = '{$value['id']}' and status = 2 and is_return = 1 ");
                    $restnum = $value['basicnum'] - $value['wastenum'] -  $checkuse;
                    $list[$key]['restnum'] = $restnum;
                }
                include $this->template(''.$school['style3'].'/assetslist');
            }elseif($op = 'scroll_more'){
                $time = $_GPC['LiData']['time'];
                $limit_start = $time + 1;
                $list = pdo_fetchall("SELECT * FROM ".GetTableName('assets')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' LIMIT {$limit_start},8 ");
                foreach($list as $key=>$value){
                    $checkuse = pdo_fetchcolumn("SELECT sum(num) FROM ".GetTableName('assetstake')." WHERE schoolid = '{$schoolid}' and gwid = '{$value['id']}' and status = 2 and is_return = 1 ");
                    $restnum = $value['basicnum'] - $value['wastenum'] -  $checkuse;
                    $list[$key]['restnum'] = $restnum;
                    $list[$key]['location'] = $key + $limit_start;
                }
                include $this->template('comtool/assetslist');
            }
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        } 
        
?>