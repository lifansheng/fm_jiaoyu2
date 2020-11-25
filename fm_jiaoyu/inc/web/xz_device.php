<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
		global $_GPC, $_W;

		$weid              = $_W['uniacid'];
		$action            = 'check';
		$this1             = 'no5';
		$schoolid          = intval($_GPC['schoolid']);
		$logo              = pdo_fetch("SELECT logo,title,gonggao FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$GLOBALS['frames'] = $this->getNaveMenu($_GPC['schoolid'], $action);
		load()->func('tpl');
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if(!empty($_W['setting']['remote']['type'])){
			$urls = $_W['attachurl'];
		}else{
			$urls = $_W['siteroot'] . 'attachment/';
		}
		if($operation == 'display'){

			$list = pdo_fetchall("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE weid = '{$weid}' AND schoolid = '{$schoolid}' ORDER BY id DESC");
			foreach($list as $key => $row){
				$item                 = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE id = '{$row['id']}'");
				$banner               = unserialize($item['banner']);
				$list[$key]['isflow'] = $banner['isflow'];
				$list[$key]['video']  = $banner['video'];
			}

		}elseif($operation == 'restart'){

			$tag = $_GPC['tag'];

	        $bkinfo = array('status'=>'fail','msg'=>'参数不正确');
	        if($tag){
	            $tag = str_replace(':', '', $tag);
	            $tag = strtoupper($tag);

	            $info = array('status'=>'restart');
	            $msg  = json_encode($info);

	            require_once(MODULE_ROOT.'/inc/func/zhaji/zhaji.php');
	            $zhaji = new zhaji();
	            $result = $zhaji->push($tag,$msg);

	            $bkinfo = array('status'=>'success','msg'=>'命令发送成功，请等待设备重启');
	            if(!$result){
	                $bkinfo = array('status'=>'fail','msg'=>'命令发送失败');
	            }
	        }

	        echo json_encode($bkinfo);

	        exit;
        

		}elseif($operation == 'resync'){

			$tag = $_GPC['tag'];

	        $bkinfo = array('status'=>'fail','msg'=>'参数不正确');
	        if($tag){
	            $tag = str_replace(':', '', $tag);
	            $tag = strtoupper($tag);

	            $info = array('status'=>'refreshPermissions');
	            $msg  = json_encode($info);

	            require_once(MODULE_ROOT.'/inc/func/zhaji/zhaji.php');
	            $zhaji = new zhaji();
	            $result = $zhaji->push($tag,$msg);

	            $bkinfo = array('status'=>'success','msg'=>'命令发送成功，正在执行同步');
	            if(!$result){
	                $bkinfo = array('status'=>'fail','msg'=>'命令发送失败');
	            }
	        }

	        echo json_encode($bkinfo);

	        exit;
        

		}elseif($operation == 'edit'){

			$id     = intval($_GPC['id']);
			$device   = pdo_fetch("SELECT * FROM " . tablename($this->table_checkmac) . " WHERE id = {$id} ");
			// if(!pdo_fieldexists('wx_school_checkmac', 'ipc')) {
				// pdo_query("ALTER TABLE ".tablename('wx_school_checkmac')." ADD `ipc` TEXT NULL DEFAULT NULL COMMENT '摄像机信息';");
				// $device['ipc'] = '';
			// }
			if($_GPC['submit']){
			    $ips    = $_GPC['ip'];
			    $port   = $_GPC['port'];
			    $uname  = $_GPC['uname'];
			    $pwd    = $_GPC['pwd'];

			    $ipcs = array();
			    foreach ($ips as $key => $ip) {
			        $ipcs[] = array(
			            'ip'=>$ip,'port'=>$port[$key],'uname'=>$uname[$key],'pwd'=>$pwd[$key],
			            );
			    }

			    $data = array(
			            'ipc'   => serialize($ipcs)
			        );

			    pdo_update($this->table_checkmac, $data, array('id' => $id));

			    $result = array('status'=>'success','msg'=>'配置修改成功！');
			    exit(json_encode($result));
			}

			if(!$device['ipc']){
				$device['ipc'] = serialize(array());
			}
			$result = array(
				'status'	=> 'success',
				'data'		=> array(
					'ipc' => unserialize($device['ipc'])
				)
			);

			exit(json_encode($result));

		}elseif($operation == 'showqrcode'){

			include 'phpqrcode.php';

			$tag = $_GPC['tag'];
	        $status = $_GPC['status'];

	        if($tag){
	            $tag = str_replace(':', '', $tag);
	            $tag = strtoupper($tag);
	        }

	        $base_dir     = ATTACHMENT_ROOT . 'images/';
	        $dirs = explode('-', date("Y-m-d"));
	        foreach ($dirs as $dir ) {
	        	$base_dir .= $dir .'/';
	        	if(is_dir($base_dir) || mkdir($base_dir ,0777)){
	        	}
	        }


	        
	        //进
	        $before_url   =  $_W['siteroot'].'app/'.$this->createMobileUrl('xz_device').'&tag='.$tag.'&status=opendoorin1';
	        $file_name    = $base_dir.time().rand(111111,999999).'.png';
	        QRcode::png($before_url,$file_name,'',10,2);
	        $up_file_name = str_ireplace(ATTACHMENT_ROOT,'',$file_name);
	        $img_url      = $_W['siteroot'].'/attachment/'.$up_file_name;

	        //出
	        $before_url2   =  $_W['siteroot'].'app/'.$this->createMobileUrl('xz_device').'&tag='.$tag.'&status=opendoorout1';
	        $file_name    = $base_dir.time().rand(111111,999999).'.png';
	        QRcode::png($before_url2,$file_name,'',10,2);
	        $up_file_name = str_ireplace(ATTACHMENT_ROOT,'',$file_name);
	        $img_url2      = $_W['siteroot'].'/attachment/'.$up_file_name;

	        echo json_encode(array('status'=>'success','img_url'=>$img_url,'url'=>$before_url,'img_url2'=>$img_url2,'url2'=>$before_url2));

	        exit;
			
		}else{
			$this->imessage('请求方式不存在');
		}
		include $this->template('web/xz_device');
?>