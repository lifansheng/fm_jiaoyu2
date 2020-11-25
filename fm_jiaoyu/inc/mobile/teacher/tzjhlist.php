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
        $nowtime = time();
        //查询是否用户登录
		$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $userid['id']));
		$tid_global = $it['tid'];
		if(is_showgkk()){
            if (!(IsHasQx($tid_global,2000901,2,$schoolid)) && !(IsHasQx($tid_global,2000911,2,$schoolid))){
                message('您无权查看本页面');
            }
        }else{
            if (!(IsHasQx($tid_global,2000901,2,$schoolid))){
                message('您无权查看本页面');
            }
        }

		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));

        if(!empty($userid['id'])){
            if(IsHasQx($tid_global,2000901,2,$schoolid)){
                $bjlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And tid = '{$it['tid']}' And type = 'theclass' ORDER BY sid ASC, ssort DESC");
                //var_dump($bjlist);
                if(!empty($_GPC['bj_id'])){
                    $bj_id = $_GPC['bj_id'];
                }else{
                    $mybjlist = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where schoolid = {$schoolid} And type = 'theclass' And tid = {$it['tid']} ORDER BY ssort DESC LIMIT 0,1 ");
                    $bj_id = $mybjlist['sid'];
                }

            }elseif(!(IsHasQx($tid_global,2000901,2,$schoolid)) && IsHasQx($tid_global,2000911,2,$schoolid)){
                if(is_showgkk())
                            $bj_id = -1;
            }


            //var_dump($bj_id);


            if($bj_id == -1){

                    $bjidname['sname'] = '教师周计划';
            }else{
                $bjidname = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " where :sid = sid ", array(':sid' => $bj_id));
            }
			$weekplanlist = pdo_fetchall("SELECT * FROM " . tablename($this->table_zjh) . " where schoolid = '{$schoolid}' And weid = '{$weid}' And bj_id = '{$bj_id}' ORDER BY createtime,is_on,end DESC");
			foreach($weekplanlist as $key => $vule){
				if($vule['type'] == 1){
					$weekplanlist[$key]['type'] = 'img';
				}else{
					$weekplanlist[$key]['type'] = 'word';
				}
			}
			include $this->template(''.$school['style3'].'/tzjhlist');
        }else{
			session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
        }        
?>