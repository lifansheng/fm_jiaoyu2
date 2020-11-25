<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_GPC, $_W;
        $weid = $_W['uniacid'];
		$tid = $_GPC['tid'];
	    $schoolid = intval($_GPC['schoolid']);

		$logo = pdo_fetch("SELECT logo,title FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
        $userid = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where :schoolid = schoolid And :weid = weid And :openid = openid And :sid = sid", array(':weid' => $weid, ':schoolid' => $schoolid, ':openid' => $openid, ':sid' => 0), 'id');
        $it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where weid = :weid AND id=:id ORDER BY id DESC", array(':weid' => $weid, ':id' => $userid['id']));
        $tid_global =$it['tid'];
		$xueqi = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));		
		$km = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'subject', ':schoolid' => $schoolid));
		$bj = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
		$xq = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'week', ':schoolid' => $schoolid));
		$sd = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'timeframe', ':schoolid' => $schoolid));
		$qh = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'score', ':schoolid' => $schoolid));
        $operation = $_GPC['op'];
        load()->func('tpl');
        load()->func('file');
        $gkkpjbz = pdo_fetchall("SELECT * FROM " . tablename($this->table_gkkpjbz) . " where schoolid='{$schoolid}' ORDER BY ssort ASC");
        $teachers = pdo_fetchall("SELECT * FROM " . tablename ($this->table_teachers) . " where weid = :weid And schoolid = :schoolid ORDER BY  CONVERT(tname USING gbk)  ASC ", array(
                ':weid' => $weid,
                ':schoolid' => $schoolid
            ) );

        //这里开始提交
        if ($operation == 'add') {
            $date_temp  = strtotime($_GPC['gkkdate']);
            $bj_id      = $_GPC['bj_id'];
            $km_id      = $_GPC['km_id'];
            $nj_id      = $_GPC['nj_id'];
            $addr       = $_GPC['addr'];
            $is_pj      = $_GPC['is_pj'];
            $dagang     = $_GPC['dagang'];
            $pjbz       = $_GPC['pjbz'];
            $title      = $_GPC['title'];
            $starttime  = $_GPC['starttime'];
            $endtime    = $_GPC['endtime'];

            $data = array(
                'weid' => $weid,
                'schoolid' => $schoolid,
                'tid' => intval($_GPC['tid']),
                'bzid' => $pjbz,
                'xq_id' => $nj_id,
                'km_id' => $km_id,
                'bj_id' => $bj_id,
                'name' => $title,
                'dagang' => $dagang,
                'addr' =>$addr,
                'is_pj' => $is_pj,
                'starttime' => $starttime,
                'endtime' => $endtime
            );
            if($tid_global !='founder' && $tid_global != 'owner'){
                $data['createtid'] = $tid_global;
            }elseif($tid_global =='founder' || $tid_global == 'owner'){
                $data['createtid'] = '-1';
            }
            $data['createtime'] = time();
            pdo_insert($this->table_gongkaike, $data);
            $data_back['status'] = 1 ;
            $data_back['info'] = '创建公开课成功';
            die(json_encode($data_back));
        }
        ////到这里结束提交

        include $this->template ( ''.$school['style3'].'/gkkadd' );
?>