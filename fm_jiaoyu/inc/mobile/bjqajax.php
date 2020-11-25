<?php
/**
 * 成都市微美网络科技有限公司
 * www.daren007.com
 */
   global $_W, $_GPC;
   $operation = in_array ( $_GPC ['op'], array ('default', 'fabu', 'getSignature', 'delbjq', 'huifu', 'dianzan', 'sfabu', 'shbjq', 'delmyhf','donwimg','donwvioce','setstar','CreateAliUploadVideo','RefreshAliUploadVideo','GetAliVideoUrl','GetAliVideoCover') ) ? $_GPC ['op'] : 'default';
    if ($operation == 'default') {
	           die ( json_encode ( array (
			         'result' => false,
			         'msg' => '参数错误'
	                ) ) );
    }
	if ($operation == 'GetAliVideoUrl') {
		mload()->model('ali');
		$aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
		$appid = $aliyun['alivodappid'];
		$key = $aliyun['alivodkey'];
		die (json_encode(GetAliVideoUrl($appid,$key,'bbec2d62cbb54ad99e9c7304c32f2956')));
	}
	
	if ($operation == 'GetAliVideoCover') {
		mload()->model('ali');
		$aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
		$appid = $aliyun['alivodappid'];
		$key = $aliyun['alivodkey'];
		die (json_encode(GetAliVideoCover($appid,$key,'bbec2d62cbb54ad99e9c7304c32f2956')));
	}
	
	if ($operation == 'CreateAliUploadVideo') {
		mload()->model('ali');
		$aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
		if($aliyun['result']){
			$appid = $aliyun['alivodappid'];
			$key = $aliyun['alivodkey'];
			$Category = AddCategorys($appid,$key,$_GPC['schoolid'],$_W['uniacid']);
			$fileinfo = array();
			$fileinfo['title'] = $_GPC['title'];        
			$fileinfo['filename'] = $_GPC['filename'];        
			$fileinfo['description'] = $_GPC['description'];        
			$fileinfo['coverurl'] = $_GPC['coverurl'];
			$fileinfo['Category'] = $Category['CateId'];
			$fileinfo['tag'] = $_GPC['tag'];
			try {
				$client = initVodClient($appid,$key,'');
				$uploadInfo = createUploadVideo($client,$fileinfo);
				$result = object_array($uploadInfo);
				$result['info'] = "获取接口成功";
				$result['fileinfo'] = $fileinfo;
				$result['result'] = true;
			} catch (Exception $e) {
				$result['msg'] = $e->getMessage()."\n";
				$result['info'] = "获取接口失败";
				$result['result'] = false;	
			}
		}else{
			$result['info'] = "未设置阿里云帐号";
			$result['result'] = false;
		}
		die (json_encode($result));
	}
	
	if ($operation == 'RefreshAliUploadVideo') {
		mload()->model('ali');
		$aliyun = GetAliApp($weid,$schoolid);
		$appid = $aliyun['alivodappid'];
		$key = $aliyun['alivodkey'];
		try {
			$client = initVodClient($appid,$key,'');
			$refreshInfo = refreshUploadVideo($client, 'videoId');
			$result = object_array($refreshInfo);
			$result['info'] = "获取接口成功";
			$result['result'] = true;
			die (json_encode($result));
		} catch (Exception $e) {
			$result['msg'] = $e->getMessage()."\n";
			$result['info'] = "获取接口失败";
			$result['result'] = false;
			die (json_encode($result));
		}
	}
	
	if ($operation == 'setstar') {
		if(empty($_GPC['weid']) || empty($_GPC['schoolid'])){
		   die ( json_encode ( array (
				 'result' => false,
				 'msg' => '非法访问！'
				) ) );			
		}
		$bj_id 	  = intval($_GPC['bj_id']);
		$mub 	  = intval($_GPC['mub']);
		$sid 	  = intval($_GPC['sid']);
		$school = pdo_fetch("SELECT spic FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => trim($_GPC['schoolid'])));
		$student = pdo_fetch("SELECT icon,s_name FROM " . tablename($this->table_students) . " WHERE :id = id", array(':id' => $sid));
		$thisbj  = pdo_fetch("SELECT is_bjzx,star,tid FROM " . tablename($this->table_classify) . " WHERE :sid = sid", array(':sid' => $bj_id));
		if($thisbj['is_bjzx'] == 1){
			$star = unserialize($thisbj['star']);
			$temp = array(
				'icon1' => $star['icon1'],
				'icon2' => $star['icon2'],
				'icon3' => $star['icon3'],
				'icon4' => $star['icon4'],
				'name1' => $star['name1'],
				'name2' => $star['name2'],
				'name3' => $star['name3'],
				'name4' => $star['name4'],
			);
			if($mub == 1){
				$temp['icon1'] = !empty($student['icon']) ? tomedia($student['icon']) : tomedia($school['spic']);
				$temp['name1'] = $student['s_name'];
			}
			if($mub == 2){
				$temp['icon2'] = !empty($student['icon']) ? tomedia($student['icon']) : tomedia($school['spic']);
				$temp['name2'] = $student['s_name'];
			}
			if($mub == 3){
				$temp['icon3'] = !empty($student['icon']) ? tomedia($student['icon']) : tomedia($school['spic']);
				$temp['name3'] = $student['s_name'];
			}
			if($mub == 4){
				$temp['icon4'] = !empty($student['icon']) ? tomedia($student['icon']) : tomedia($school['spic']);
				$temp['name4'] = $student['s_name'];
			}			
			$datas['star'] = iserializer($temp);
			pdo_update($this->table_classify, $datas, array('sid' => $bj_id));
			$data ['s_name'] = $student['s_name'];
			$data ['icon'] = !empty($student['icon']) ? tomedia($student['icon']) : tomedia($school['spic']);
			$data ['result'] = true;
			$data ['msg'] = '设置成功';
			$actop = 'bjzx';
			$backuser =  pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " WHERE :tid = tid", array(':tid' => $thisbj['tid']));
			$weid = $_GPC['weid'];
			$schoolid = $_GPC['schoolid'];
			$point = PointAct($weid,$schoolid,$backuser['id'],$actop);
			$point1 = PointMission($weid,$schoolid,$backuser['id'],$actop);
			if(!empty($point))
			{
				 $data ['msg'] = '设置成功!积分+'.$point;
				}
		}else{
			$data ['result'] = false;
			$data ['msg'] = '本班未开启班级之星功能';
		}
		die ( json_encode ( $data ) );			
	}

	if ($operation == 'donwimg') {
		//上传图片 (lee 0722)
		$data = explode ( '|', $_GPC ['json'] );
		load()->func('communication');
		load()->func('file');
		$token2 = $this->getAccessToken2();
		$photoUrls = trim($_GPC ['serverId']);
		if(!empty($photoUrls)) {
			$url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls;
			$pic_data = ihttp_request($url);
			$path = "images/bjq/img/";
			$picurl = $path.random(30) .".jpg";
			file_write($picurl,$pic_data['content']);
			if (!empty($_W['setting']['remote']['type'])) { // 
				$remotestatus = file_remote_upload($picurl); //
				if (is_error($remotestatus)) {
					message('远程附件上传失败，请检查配置并重新上传');
				}
			}
		}
		$data ['data'] = $picurl;
		die ( json_encode ( $data ) );	
    }
	if ($operation == 'donwvioce') {
		load()->func('communication');
		load()->func('file');
	    $token2 = $this->getAccessToken2();
		$photoUrls = trim($_GPC ['serverId']);		 
		if($_GPC ['serverId']) {
			$url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$photoUrls;
			$pic_data = ihttp_request($url);
			$path = "images/bjq/vioce/";
			$amr = $path.random(30) .".amr";
			file_write($amr,$pic_data['content']);
			$files = IA_ROOT . "/attachment/".$amr;
			if(file_exists(IA_ROOT . "/attachment/".$amr)){
				$versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
				require $versionfile;
				$test = upload_file_to_cdn($files,FM_JIAOYU_HOST);
				$respoed = json_decode($test,true);
				if($respoed['result']==1){
					$vioce = file_get_contents($respoed['mp3']);
					$mp3 = $path.$respoed['name'].".mp3";
					file_write($mp3,$vioce);
					if(file_exists(IA_ROOT . "/attachment/".$mp3)){
						if (!empty($_W['setting']['remote']['type'])) {
							$remotestatus = file_remote_upload($mp3);
							if (is_error($remotestatus)) {
								message('远程附件上传失败，请检查配置并重新上传');
							}
						}
						file_delete($amr);
					}
				}
			}			
		}
		$data ['data'] = $mp3;
		die ( json_encode ( $data ) );			
    }	
	if ($operation == 'delmyhf') {
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] ) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
            pdo_delete($this->table_bjq, array('id' => $_GPC['id']));			   
			$data ['result'] = true;
			$data ['msg'] = '删除成功';		   
		}
	 die ( json_encode ( $data ) );	
    }	
	if ($operation == 'delbjq') {
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$bjq = pdo_fetch("SELECT ali_vod_id FROM " . tablename($this->table_bjq) . " where id = :id", array(':id' => $_GPC['id']));
			if($bjq['ali_vod_id']){
				mload()->model('ali');
				$aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
				DelAlivod($aliyun['alivodappid'],$aliyun['alivodkey'],$bjq['ali_vod_id']);
			}
            pdo_delete($this->table_bjq, array('id' => $_GPC['id']));			   
            pdo_delete($this->table_media, array('sherid' => $_GPC['id']));
			pdo_delete($this->table_dianzan, array('sherid' => $_GPC['id']));
			$data ['result'] = true;
			$data ['msg'] = '删除成功';		   
		}
	 die ( json_encode ( $data ) );	
    }
	if ($operation == 'shbjq') {
		$data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' 
		               ) ) );
		}else{
			$temp = array(
					'isopen' =>  0
				);	
			$setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE :weid = weid", array(':weid' => $_GPC['weid']));	
			$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " WHERE :weid = weid And :id = id", array(':weid' => $_GPC['weid'], ':id' => $_GPC['schoolid']));
			$bjq = pdo_fetch("SELECT shername,openid,userid FROM " . tablename($this->table_bjq) . " where weid = :weid AND schoolid = :schoolid AND id = :id", array(':weid' => $_GPC ['weid'], ':schoolid' => $_GPC ['schoolid'], ':id' => $_GPC['id']));
			$shername = $bjq['shername'];
			$schoolid = $_GPC['schoolid'];
			$weid = $_GPC['weid'];
			$toopenid = $bjq['openid'];
            pdo_update($this->table_bjq, $temp, array('id' => $_GPC['id']));			
			$data ['result'] = true;
			$data ['msg'] = '审核通过';
			$actop = 'shbjq';
			$userid = $_GPC['userid'];
			$point = PointAct($weid,$schoolid,$userid,$actop);
			$point1 = PointMission($weid,$schoolid,$userid,$actop);
			if(!empty($point))
			{
				 $data ['msg'] = '审核通过!积分+'.$point;
			}	
			$this->sendMobileBjqshjg($schoolid, $weid, $shername, $toopenid, $bjq['userid']);
		}
	 die ( json_encode ( $data ) );	
    }	
	if ($operation == 'huifu') {
		$user = pdo_fetch("SELECT status FROM " . tablename($this->table_user) . " WHERE :id = id", array(':id' => $_GPC['uid']));
		if ($user['status'] == 1) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '抱歉你已被禁言！' 
		               ) ) );
		}else{
			$weid = $_GPC ['weid'];
			$schoolid = $_GPC ['schoolid'];
			$uid = $_GPC ['uid'];
			$sherid = $_GPC ['sherid'];
			$content = $_GPC ['content'];
			$shername = $_GPC ['shername'];
			$plid = $_GPC ['plid'];
			$hftoname = $_GPC ['hftoname'];
			$openid = $_GPC ['openid'];
			$temp = array(
					'weid' =>  $weid,
					'schoolid' => $schoolid,
					'uid' => $uid,
					'userid' => $_GPC ['userid'],
					'shername' => $shername,
					'sherid' => $sherid,
					'hftoname' => $hftoname,
					'plid' => $plid,					
					'openid' => $openid,
					'content' => $content,	
					'createtime' => time(),
					'type'=>1,
				);
			    pdo_insert($this->table_bjq, $temp);			
				$thisid = pdo_insertid();			
			$data ['result'] = true;
			$data ['id'] = $thisid;
			$data ['msg'] = '评论成功';
			$actop = 'hf';
			$point = PointAct($weid,$schoolid,$_GPC['userid'],$actop);
			$point1 = PointMission($weid,$schoolid,$_GPC['userid'],$actop);
			if(!empty($point))
			{
				 $data ['msg'] = '评论成功!积分+'.$point;
			}		   
		}
	 die ( json_encode ( $data ) );	
    }
	if ($operation == 'dianzan') {
			$weid = $_GPC ['weid'];
			$schoolid = $_GPC ['schoolid'];
			$uid = $_GPC ['uid'];
			$sherid = $_GPC ['sherid'];
			$zname = $_GPC ['zname'];
			$userid = pdo_fetch("SELECT * FROM " . tablename($this->table_dianzan) . " where :schoolid = schoolid And :weid = weid  And :uid = uid And :sherid = sherid", array(
			          ':weid' => $weid,
                      ':schoolid' => $schoolid,
					  ':uid' => $uid,
					  ':sherid' => $sherid
					   ));			
		$data = explode ( '|', $_GPC ['json'] );
		if (!empty($userid['id'])) {
			pdo_delete($this->table_dianzan, array('sherid' => $sherid,'uid' => $uid,'schoolid' => $schoolid));	
			$data ['result'] = true;
			$data ['msg'] = '取消赞成功';
		}else{
			$temp = array(
				'weid' =>  $weid,
				'schoolid' => $schoolid,
				'uid' => $uid,
				'userid' => $_GPC ['userid'],
				'zname' => $zname,
				'sherid' => $sherid,
				'createtime' => time(),
			);
			pdo_insert($this->table_dianzan, $temp);			
			$data ['result'] = true;
			$data ['msg'] = '点赞成功';
			$actop = 'dz';
			$point = PointAct($weid,$schoolid,$_GPC['userid'],$actop);
			$point1 = PointMission($weid,$schoolid,$_GPC['userid'],$actop);
			if(!empty($point))
			{
				  $data ['msg'] = '点赞成功!积分+'.$point;
			}		   
		}
	 die ( json_encode ( $data ) );	
    }	
	if ($operation == 'fabu') {
		 load()->func('communication');
		 load()->func('file');
	     $token2 = $this->getAccessToken2();
		 $data = explode ( '|', $_GPC ['json'] );
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' ,
					'status' => 2,
                    'info' => '非法请求！' 
		               ) ) );
	    }else{
			if (empty($_GPC['openid'])) {
                  die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求,请刷新页面！' ,
					'status' => 2,
                    'info' => '非法请求！' 
		               ) ) );	   
		    }else{
				$schoolid = trim($_GPC['schoolid']);
				$weid = trim($_GPC['weid']);
				$content = trim($_GPC['content']);
				$uid = trim($_GPC['uid']);
				$userid = trim($_GPC['userid']);
				$bj_id1 = trim($_GPC ['bj_id']);
				$isopen = trim($_GPC['isopen']);
				$openid = trim($_GPC['openid']);
				$audios = $_GPC ['audioServerid'];
				$audio = $audios[0];
				$audiotimes = $_GPC['audioTime'];
				$audiotime = $audiotimes[0];
				$link = trim($_GPC['linkAddress']);
				$linkdesc = trim($_GPC['linkDesc']);
				$shername = trim($_GPC['shername']);
				$is_private = trim($_GPC['is_private']);
				$msgtype = 1;
				if(!empty($audio)){
					$msgtype = 2;//录音
				}
				$video = '';
				if(!empty($_GPC['videoMediaId'])){
					$msgtype = 3;//视频
					mload()->model('ali');
					$aliyun = GetAliApp($_W['uniacid'],$_GPC['schoolid']);
					if($aliyun['result']){
						$appid = $aliyun['alivodappid'];
						$key = $aliyun['alivodkey'];
						do {
						   $GetAliVideoUrl = GetAliVideoUrl($appid,$key,trim($_GPC['videoMediaId']));
						} while (empty($GetAliVideoUrl['PlayURL']));
						do {
						   $GetAliVideoCover = GetAliVideoCover($appid,$key,trim($_GPC['videoMediaId']));
						} while (empty($GetAliVideoCover['CoverURL']));
						$video = $GetAliVideoUrl['PlayURL'];
						$videoimg = $GetAliVideoCover['CoverURL'];
					}
				}
				if(!empty($_GPC['linkAddress'])){
					$msgtype = 4;//外链
				}				
				if($audio){
					$versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
					require $versionfile;
					$mp3name = str_replace('images/bjq/vioce/','',$audio);
					$mp3 = str_replace('.mp3','',$mp3name);
					delvioce($mp3,FM_JIAOYU_HOST);
				}
                if(trim($_GPC['bj_id']) == '-1'){
                    $is_all ='1';
                }
				$temp = array(
					'weid' =>  $weid,
					'schoolid' => $schoolid,
					'uid' => $uid,
					'shername' => $shername,
					'audio' => $audio,
					'audiotime' => $audiotime,
					'content' => $content,
					'userid' => $userid,
					'videoimg' => $videoimg,
					'video' => $video,
					'ali_vod_id' => trim($_GPC['videoMediaId']),
					'link' => $link,
					'linkdesc' => $linkdesc,
					'bj_id1' => $bj_id1,
					'is_all' => $is_all,
					'openid'=>$openid,
					'isopen'=>0,
					'is_private'=>$is_private,
					'createtime' => time(),
					'msgtype'=>$msgtype,
					'type'=>0,
				);
				if(is_showgkk())
				{
					$iszy = $_GPC['is_zy'];
					if($iszy == 1 )
					{
						$temp['type'] = 2;
					}
				}								
			    pdo_insert($this->table_bjq, $temp);
			    $bjq_id = pdo_insertid();
			    $data1 = array(
					'sherid'=>$bjq_id,
			    );
				pdo_update($this->table_bjq, $data1, array ('id' => $bjq_id) );
				if($_GPC ['photoUrls']){
					$photoUrls = $_GPC ['photoUrls']; 
					$order = 1;
					foreach($photoUrls as $key => $v){
						if(!empty($v)) {
						   $data = array(
							'weid' =>  $weid,
							'schoolid' => $schoolid,
							'uid' => $uid,
							'picurl' => $v,	
							'bj_id1' => $bj_id1,
							'order'=>$order,
							'sherid'=>$bjq_id,
							'createtime' => time(),
						   );
						   pdo_insert($this->table_media, $data);							
						}
						$order++;
					}
				}
		        $data ['status'] = 1;
			    $data ['info'] ='发布成功，请勿重复发布！' ;	
		        $data ['result'] = true;
			}
			$actop = 'fbbjq';
			$point = PointAct($weid,$schoolid,$userid,$actop);
			$point1 = PointMission($weid,$schoolid,$userid,$actop);
			if(!empty($point))
			{
				 $data ['info'] = '发布成功，请勿重复发布！积分+'.$point;
			}
          die ( json_encode ( $data ) ); 
		}
    }
	if ($operation == 'sfabu') {
		 load()->func('communication');
		 load()->func('file');
	     $token2 = $this->getAccessToken2();		
		if (! $_GPC ['schoolid'] || ! $_GPC ['weid']) {
               die ( json_encode ( array (
                    'result' => false,
                    'msg' => '非法请求！' ,
					'status' => 2,
                    'info' => '非法请求！' 
		               ) ) );
	    }else{
			$user = pdo_fetch("SELECT status FROM " . tablename($this->table_user) . " WHERE :id = id", array(':id' => $_GPC['uid']));
			if ($user['status'] == 1) {
                  die ( json_encode ( array (
                    'result' => false,
                    'msg' => '抱歉您已被禁言！' ,
					'status' => 2,
                    'info' => '抱歉您已被禁言！' 
		               ) ) );	   
		    }else{

				$schoolid = trim($_GPC['schoolid']);
				$weid = trim($_GPC['weid']);
				$content = trim($_GPC['content']);
				$uid = trim($_GPC['uid']);
				$userid = trim($_GPC['userid']);
				$bj_id1 = trim($_GPC ['bj_id']);
				$isopen = trim($_GPC['isopen']);
				$openid = trim($_GPC['openid']);
				$audios = $_GPC ['audioServerid'];
				$audio = $audios[0];
				$audiotimes = $_GPC['audioTime'];
				$audiotime = $audiotimes[0];
				$link = trim($_GPC['linkAddress']);
				$linkdesc = trim($_GPC['linkDesc']);
				$shername = trim($_GPC['shername']);
				$is_private = trim($_GPC['is_private']);
				$msgtype = 1;
				if(!empty($audio)){
					$msgtype = 2;//录音
				}
				$video = '';
				if(!empty($_GPC['videoMediaId'])){
					$msgtype = 3;//视频
					mload()->model('ali');
					$aliyun = GetAliApp($weid,$schoolid);
					if($aliyun['result']){
						$appid = $aliyun['alivodappid'];
						$key = $aliyun['alivodkey'];
						do {
						   $GetAliVideoUrl = GetAliVideoUrl($appid,$key,trim($_GPC['videoMediaId']));
						} while (empty($GetAliVideoUrl['PlayURL']));
						do {
						   $GetAliVideoCover = GetAliVideoCover($appid,$key,trim($_GPC['videoMediaId']));
						} while (empty($GetAliVideoCover['CoverURL']));
						$video = $GetAliVideoUrl['PlayURL'];
						$videoimg = $GetAliVideoCover['CoverURL'];
					}
				}
				if(!empty($_GPC['linkAddress'])){
					$msgtype = 4;//外链
				}
				if($audio && $_GPC['photoUrls']){
					$msgtype = 5;//外链
				}				
				if($_GPC['videoMediaId'] && $_GPC['photoUrls']){
					$msgtype = 6;//外链
				}				
				if($audio){
					$versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
					require $versionfile;
					$mp3name = str_replace('images/bjq/vioce/','',$audio);
					$mp3 = str_replace('.mp3','',$mp3name);
					delvioce($mp3,FM_JIAOYU_HOST);
				}
				$temp = array(
					'weid' =>  $weid,
					'schoolid' => $schoolid,
					'uid' => $uid,
					'userid' => $userid,
					'shername' => $shername,
					'audio' => $audio,
					'audiotime' => $audiotime,
					'content' => $content,
					'videoimg' => $videoimg,
					'video' => $video,
					'ali_vod_id' => trim($_GPC['videoMediaId']),
					'link' => $link,
					'linkdesc' => $linkdesc,
					'bj_id1' => $bj_id1,
					'openid'=>$openid,
					'isopen'=>$isopen,
					'is_private'=>$is_private,
					'createtime' => time(),
					'msgtype'=>$msgtype,
					'type'=>0,
				);
			    pdo_insert($this->table_bjq, $temp);
			    $bjq_id = pdo_insertid();
			    $data1 = array(
					'sherid'=>$bjq_id,
			    );
				pdo_update($this->table_bjq, $data1, array ('id' => $bjq_id) );
				if($_GPC ['photoUrls']){
					$photoUrls = $_GPC ['photoUrls']; 
					$order = 1;
					foreach($photoUrls as $key => $v){
						if(!empty($v)) {
						   $data = array(
							'weid' =>  $weid,
							'schoolid' => $schoolid,
							'uid' => $uid,
							'picurl' => $v,	
							'bj_id1' => $bj_id1,
							'order'=>$order,
							'sherid'=>$bjq_id,
							'createtime' => time(),
						   );
						   pdo_insert($this->table_media, $data);							
						}
						$order++;
					}
				}
				$data ['info'] = '发布成功，请勿重复发布！';		
			    //向班主任发送通知
				$school = pdo_fetch("SELECT isopen,bjqstyle,sh_teacherids FROM " . tablename($this->table_index) . " WHERE :id = id", array(':id' => $schoolid));
				if ($school['isopen'] == 1 ) {
					if($school['bjqstyle'] == 'old'){
						if($school['sh_teacherids']){
							if(strstr($school['sh_teacherids'],',')){
								$sh_teacherids = explode(',',$school['sh_teacherids']);
								foreach($sh_teacherids as $row){
									if($row > 0){
										$this->sendMobileBjqshtz($schoolid, $weid, $shername, 0,$row);
									}
								}
							}else{
								$this->sendMobileBjqshtz($schoolid, $weid, $shername, 0,$school['sh_teacherids']);
							}
						}
					}else{
						$this->sendMobileBjqshtz($schoolid, $weid, $shername, $bj_id1,0);
					}
					$data ['info'] = '发布成功，老师审核后方可显示，请勿重复发布！';	
			    }
		        $data ['status'] = 1;
		        $data ['result'] = true;
			}
          die ( json_encode ( $data ) ); 
		}
    }	
?>