<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
global $_GPC, $_W;

$weid              = $_W['uniacid'];
$action            = 'signup';
$this1             = 'no3';
$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
$schoolid          = intval($_GPC['schoolid']);
$logo              = pdo_fetch("SELECT logo,title,is_stuewcode,spic,picarrset,textarrset,is_picarr,is_textarr FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
$picarrset = unserialize($logo['picarrset']);
$textarrset = unserialize($logo['textarrset']);
$school    = pdo_fetch("SELECT signset FROM " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
$bjlist    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'theclass', ':schoolid' => $schoolid));
$njlist    = pdo_fetchall("SELECT sid,sname FROM " . tablename($this->table_classify) . " where weid = :weid AND schoolid = :schoolid And type = :type ORDER BY ssort DESC", array(':weid' => $weid, ':type' => 'semester', ':schoolid' => $schoolid));
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tid_global = $_W['tid'];
if($operation == 'display'){
	if (!(IsHasQx($tid_global,1001301,1,$schoolid))){
		$this->imessage('非法访问，您无权操作该页面','','error');	
	}
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $condition = '';

    if(!empty($_GPC['bj_id'])){
        $bj_id     = intval($_GPC['bj_id']);
        $condition .= " And bj_id = '{$bj_id}' ";
    }

    if(!empty($_GPC['nj_id'])){
        $nj_id     = intval($_GPC['nj_id']);
        $condition .= " And nj_id = '{$nj_id}' ";
    }

    if(!empty($_GPC['status'])){
        $status    = intval($_GPC['status']);
        $condition .= " And status = '{$status}' ";
    }

    $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_signup) . " where schoolid = '{$schoolid}' And weid = '{$weid}' $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

    foreach($list as $key => $row){
        $bj                     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $row['bj_id']));
        $nj                     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $row['nj_id']));
        $order                  = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id", array(':id' => $row['orderid']));
        $member                 = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W ['uniacid'], ':uid' => $row['uid']));
        $list[$key]['avatar']   = $member['avatar'];
        $list[$key]['nickname'] = $member['nickname'];
        $list[$key]['bjname']   = $bj['sname'];
        $list[$key]['njname']   = $nj['sname'];
        $list[$key]['order']    = $order['status'];
    }
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_signup) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' $condition");

    $pager = pagination($total, $pindex, $psize);
}elseif($operation == 'pass'){

    $id = intval($_GPC['id']);

    $item = pdo_fetch("SELECT * FROM " . tablename($this->table_signup) . " WHERE :id = id", array(':id' => $id));
	$randStr = str_shuffle('1234567890');
	$rand    = substr($randStr, 0, 6);
    $temp = array(
        'weid'           => $item['weid'],
        'schoolid'       => $item['schoolid'],
        's_name'         => $item['name'],
		'icon' 			 => $item['icon'],
        'sex'            => $item['sex'],
        'numberid'       => $item['numberid'],
        'mobile'         => $item['mobile'],
        'xq_id'          => $item['nj_id'],
        'bj_id'          => $item['bj_id'],
        'note'           => $item['idcard'],
		'code'			 => $rand,
        'birthdate'      => $item['birthday'],
        'seffectivetime' => time(),
        'createdate'     => time()
    );	
    pdo_insert($this->table_students, $temp);
    $studentid = pdo_insertid();

    if(keep_Blacklist()){
        pdo_update(GetTableName('students',false),array('numberid'=>$studentid),array('id'=>$studentid));
    }
    
	if($logo['is_stuewcode'] ==1){
		if(empty($item['icon'])){
			if(empty($logo['spic'])){
				$this->imessage('抱歉,本校开启了用户二维码功能,请上传学生头像或设置校园默认学生头像');
			}
		}
		load()->func('tpl');
		load()->func('file');
		$barcode = array(
			'expire_seconds' =>2592000,
			'action_name' => '',
			'action_info' => array(
				'scene' => array(
					'scene_id' => $studentid
				),
			),
		);
		$uniacccount = WeAccount::create($_W['acid']);
		$barcode['action_name'] = 'QR_SCENE';
		$result = $uniacccount->barCodeCreateDisposable($barcode);
		if (is_error($result)) {
			message($result['message'], referer(), 'fail');
		}
		if (!is_error($result)) {
			$showurl = $this->createImageUrlCenterForUser("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $result['ticket'], $studentid, 0, $schoolid);
			$urlarr = explode('/',$showurl);
			$qrurls = "images/fm_jiaoyu/".$urlarr['4'];	
			$insert = array(
				'weid' => $weid,
				'schoolid' => $schoolid,
				'qrcid' => $studentid, 
				'name' => '用户绑定临时二维码', 
				'model' => 1,
				'ticket' => $result['ticket'], 
				'show_url' => $qrurls,
				'qr_url' => ltrim($result['url'],"http://weixin.qq.com/q/"),
				'expire' => $result['expire_seconds'] + time(), 
				'createtime' => time(),
				'status' => '1',
				'type' => '3'
			);
			pdo_insert($this->table_qrinfo, $insert);
			$qrid = pdo_insertid();
			$qrurl = pdo_fetch("SELECT show_url FROM " . tablename($this->table_qrinfo) . " WHERE id = '{$qrid}'");
			$arr = explode('/',$qrurl['show_url']);
			$pathname = "images/fm_jiaoyu/".$arr['2'];
			if (!empty($_W['setting']['remote']['type'])) {
				$remotestatus = file_remote_upload($pathname);
					if (is_error($remotestatus)) {
						message('远程附件上传失败，'.$pathname.'请检查配置并重新上传');
					}
			}
		}
	}
	$temp1 = array(
		'keyid' => $studentid,
		'qrcode_id' => $qrid
	);
	pdo_update($this->table_students, $temp1, array('id' =>$studentid)); 	
    $user      = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where schoolid = '{$item['schoolid']}' And weid = '{$item['weid']}' And (own = '{$item['openid']}' or dad = '{$item['openid']}' or mom = '{$item['openid']}') ");
    $singset   = iunserializer($school['signset']);
    if($singset['is_bd'] == 1){
        if(empty($user)){
            if(!empty($item['pard'])){
                if($item['pard'] == 2){
                    $data = array(
                        'numberid' => $item['numberid'],
                        'mom'      => $item['openid'],
                        'muid'     => $item['uid']
                    );
                    $info = array('name' => '', 'mobile' => $item ['mobile']);
                }
                if($item['pard'] == 3){
                    $data = array(
                        'numberid' => $item['numberid'],
                        'dad'      => $item['openid'],
                        'duid'     => $item['uid']
                    );
                    $info = array('name' => '', 'mobile' => $item ['mobile']);
                }
                if($item['pard'] == 4){
                    $data = array(
                        'numberid' => $item['numberid'],
                        'own'      => $item['openid'],
                        'ouid'     => $item['uid']
                    );
                    $info = array('name' => $item ['name'], 'mobile' => $item ['mobile']);
                }
                if($item['pard'] == 5){
                    $data = array(
                        'numberid' => $item['numberid'],
                        'other'      => $item['openid'],
                        'otheruid'     => $item['uid']
                    );
                    $info = array('name' => $item ['name'], 'mobile' => $item ['mobile']);
                }				
                $temp2             = array(
                    'sid'      => $studentid,
                    'weid'     => $item ['weid'],
                    'schoolid' => $item ['schoolid'],
                    'openid'   => $item ['openid'],
                    'pard'     => $item['pard'],
                    'uid'      => $item['uid'],
					'createtime' => time()
                );
				$temp2['realname'] = $info['name'];
				$temp2['mobile'] = $info['mobile'];
                pdo_insert($this->table_user, $temp2);
				$userid = pdo_insertid();
				if($item['pard'] == 2){
					$data['muserid'] = $userid;
				}
				if($item['pard'] == 3){
					$data['duserid'] = $userid;
				}
				if($item['pard'] == 4){
					$data['ouserid'] = $userid;
				}
				if($item['pard'] == 5){
					$data['otheruserid'] = $userid;
				}				
                pdo_update($this->table_students, $data, array('id' => $studentid));
                $this->DoTag($item['schoolid'],$userid); //打标签
            }
        }
    }
    $temp1 = array(
		'sid'   => $studentid,
        'status'   => 2,
        'passtime' => time()
    );

    pdo_update($this->table_signup, $temp1, array('id' => $id));
    $this->sendMobileBmshjgtz($id, $item['schoolid'], $item['weid'], $item['openid'], $item['name'], $rand);
    $this->imessage('审核成功！', referer(), 'success');

}elseif($operation == 'defid'){
    $id = intval($_GPC['id']);

    $item = pdo_fetch("SELECT * FROM " . tablename($this->table_signup) . " WHERE :id = id", array(':id' => $id));

    $temp = array(
        'status'   => 3,
        'passtime' => time()
    );

    pdo_update($this->table_signup, $temp, array('id' => $id));
    $this->sendMobileBmshjgtz($_GPC['id'], $item['schoolid'], $item['weid'], $item['openid'], $item['name'], 1);

    $this->imessage('已拒绝该申请！', referer(), 'success');
}elseif($operation == 'delete'){
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id FROM " . tablename($this->table_signup) . " WHERE id = '$id'");
    if(empty($item)){
        $this->imessage('抱歉，不存在或是已经被删除！', 'error');
    }
    pdo_delete($this->table_signup, array('id' => $id), 'OR');

    $this->imessage('删除成功！', referer(), 'success');
}elseif($operation == 'out_putcode'){
  	$list_out = pdo_fetchall("SELECT * FROM " . tablename($this->table_signup) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}'  ORDER BY createtime DESC " );
	$ii   = 0;
	 foreach($list_out as $key => $row){
        $bj                     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $row['bj_id']));
        $nj                     = pdo_fetch("SELECT * FROM " . tablename($this->table_classify) . " WHERE sid = :sid", array(':sid' => $row['nj_id']));
        $order                  = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " WHERE id = :id", array(':id' => $row['orderid']));
        $member                 = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " where uniacid = :uniacid And uid = :uid ", array(':uniacid' => $_W ['uniacid'], ':uid' => $row['uid']));
        $list_out[$key]['avatar']   = $member['avatar'];
        $list_out[$key]['nickname'] = $member['nickname'];
        $arr[$ii]['name'] = $member['nickname'];
        $arr[$ii]['sname'] = $row['name'];
        if($row['sex'] == 1){
	        $arr[$ii]['sex'] ="男";
        }elseif($row['sex'] == 2){
	        $arr[$ii]['sex'] ="女";
        }
        $arr[$ii]['phone'] = $row['mobile'];
     	
     	$arr[$ii]['njname'] = $nj['sname'];
     	$arr[$ii]['bjname'] = $bj['sname'];
     	$arr[$ii]['sqtime'] = date('Y-m-d H:i', $row['createtime']);
     	if(!empty($row['passtime'])){
	     	$arr[$ii]['passtime'] = date('Y-m-d H:i', $row['passtime']);
     	}else{
	     	$arr[$ii]['passtime'] = 0 ;
     	}
        if ($row['status'] ==1){
         	$arr[$ii]['status'] = "审核中";
        }elseif ($row['status'] ==2){
         	$arr[$ii]['status'] = "已通过";
        }elseif ($row['status'] ==3){
         	$arr[$ii]['status'] = "已拒绝";
        }
       
        if(!empty($row['cost'])){
	        $arr[$ii]['cost'] = $row['cost'];
        }else{
	        $arr[$ii]['cost'] = 0;
        }
        if ($row['pard'] == 2){
         	$arr[$ii]['guanxi'] = "母亲";
        }elseif ($row['pard'] == 3){
         	$arr[$ii]['guanxi'] = "父亲";
        }elseif ($row['pard'] == 4){
         	$arr[$ii]['guanxi'] = "本人";
        }elseif ($row['pard'] == 5){
         	$arr[$ii]['guanxi'] = "家长";
        }

        if($textarrset['is_textarr1'] == 1){
	        $arr[$ii]['textarr1'] = $row['textarr1'];
        }
        if($textarrset['is_textarr2'] == 1){
	        $arr[$ii]['textarr2'] = $row['textarr2'];
        }
         if($textarrset['is_textarr3'] == 1){
	        $arr[$ii]['textarr3'] = $row['textarr3'];
        }
         if($textarrset['is_textarr4'] == 1){
	        $arr[$ii]['textarr4'] = $row['textarr4'];
        }
         if($textarrset['is_textarr5'] == 1){
	        $arr[$ii]['textarr5'] = $row['textarr5'];
        }
         if($textarrset['is_textarr6'] == 1){
	        $arr[$ii]['textarr6'] = $row['textarr6'];
        }
         if($textarrset['is_textarr7'] == 1){
	        $arr[$ii]['textarr7'] = $row['textarr7'];
        }
         if($textarrset['is_textarr8'] == 1){
	        $arr[$ii]['textarr8'] = $row['textarr8'];
        }
         if($textarrset['is_textarr9'] == 1){
	        $arr[$ii]['textarr9'] = $row['textarr9'];
        }
         if($textarrset['is_textarr10'] == 1){
	        $arr[$ii]['textarr10'] = $row['textarr10'];
        }
        $ii++;
    }
	$oo = 0 ;
	$title = array();
	$title[$oo] = "微信";
	$oo++;
	$title[$oo] ="学生";
	$oo++; 
	$title[$oo] ="性别";
	$oo++;
	$title[$oo] ="手机";
	$oo++;
	$title[$oo] ="年级";
	$oo++;
	$title[$oo] ="班级";
	$oo++;
	$title[$oo] ="申请时间";
	$oo++; 
	$title[$oo] ="处理时间";
	$oo++;
	$title[$oo] ="审核状态";
	$oo++;
	$title[$oo] ="费用";
	$oo++; 
	$title[$oo] ="关系";
	$oo++;
 	if($textarrset['is_textarr1'] == 1){
       	$title[$oo] =$textarrset['textarr1_name'];
		$oo++;
    }
    if($textarrset['is_textarr2'] == 1){
       	$title[$oo] =$textarrset['textarr2_name'];
		$oo++;
    }
    if($textarrset['is_textarr3'] == 1){
       	$title[$oo] =$textarrset['textarr3_name'];
		$oo++;
    }
    if($textarrset['is_textarr4'] == 1){
       	$title[$oo] =$textarrset['textarr4_name'];
		$oo++;
    }
    if($textarrset['is_textarr5'] == 1){
       	$title[$oo] =$textarrset['textarr5_name'];
		$oo++;
    }
    if($textarrset['is_textarr6'] == 1){
       	$title[$oo] =$textarrset['textarr6_name'];
		$oo++;
    }
    if($textarrset['is_textarr7'] == 1){
       	$title[$oo] =$textarrset['textarr7_name'];
		$oo++;
    }
    if($textarrset['is_textarr8'] == 1){
       	$title[$oo] =$textarrset['textarr8_name'];
		$oo++;
    }
    if($textarrset['is_textarr9'] == 1){
       	$title[$oo] =$textarrset['textarr9_name'];
		$oo++;
    }
    if($textarrset['is_textarr10'] == 1){
       	$title[$oo] =$textarrset['textarr10_name'];
		$oo++;
    }   
	     
	$this->exportexcel($arr, $title, '报名情况'.date("Y年m月d日",time()));
    exit();




        }
include $this->template('web/signup');
?>