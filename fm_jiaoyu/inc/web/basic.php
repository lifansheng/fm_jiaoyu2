<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

        global $_GPC, $_W;
        //$GLOBALS['frames'] = $this->getNaveMenu();
		$weid = $_W['uniacid'];
		load()->func('tpl');
		$state = uni_permission($_W['uid'], $uniacid);
		$versionfile = IA_ROOT . '/addons/fm_jiaoyu/inc/func/auth2.php';
		$access_token = $this->getAccessToken2();
		$res = ihttp_post('https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token='.$access_token,$postarr='');
		$content = @json_decode($res['content'],true);
		if($content['primary_industry']){
			$first_class = $content['primary_industry']['first_class'];
			$second_class = $content['primary_industry']['second_class'];
		}
		if($content['secondary_industry']){
			$first_class1 = $content['secondary_industry']['first_class'];
			$second_class1 = $content['secondary_industry']['second_class'];
		}
		require $versionfile;
            $setting = pdo_fetch("SELECT * FROM " . tablename($this->table_set) . " WHERE weid = :weid", array(':weid' => $weid));
			$xsqingjia = get_weidset($weid,'xsqingjia');
			$xsqjsh    = get_weidset($weid,'xsqjsh');
			$jsqingjia = get_weidset($weid,'jsqingjia');
			$jsqjsh    = get_weidset($weid,'jsqjsh');
			$xxtongzhi = get_weidset($weid,'xxtongzhi');
			$liuyan    = get_weidset($weid,'liuyan');
			$liuyanhf  = get_weidset($weid,'liuyanhf');
			$zuoye     = get_weidset($weid,'zuoye');
			$bjtz      = get_weidset($weid,'bjtz');
			$bjqshjg   = get_weidset($weid,'bjqshjg');
			$bjqshtz   = get_weidset($weid,'bjqshtz');
			$jxlxtx    = get_weidset($weid,'jxlxtx');
			$jfjgtz    = get_weidset($weid,'jfjgtz');
			$sykstx    = get_weidset($weid,'sykstx');
			$kcyytx    = get_weidset($weid,'kcyytx');
			$kcqdtx    = get_weidset($weid,'kcqdtx');
			$sktxls    = get_weidset($weid,'sktxls');
			$pttz_set  = get_weidset($weid,'pttz');
			$zltz_set  = get_weidset($weid,'zltz');
			$bd_set    = get_weidset($weid,'bd_set');
			$dkcgtz    = get_weidset($weid,'dkcgtz');
			$twcltz    = get_weidset($weid,'twcltz');
			$kcpjtx    = get_weidset($weid,'kcpjtx');
			$jktxtz    = get_weidset($weid,'jktxtz');
			if(vis() || keep_Lx()){
                $fkyytx    = get_weidset($weid,'fkyytx');
            }
            if (checksubmit('submit')) {
                $data = array(
				    'weid' => $weid,
                    'istplnotice' => intval($_GPC['istplnotice']),
                );
				$xsqingjia = array(
					'xsqingjia'   	   => trim($_GPC['xsqingjia']),
					'sms_SignName'     => trim($_GPC['xsqingjia_singname']),
					'sms_Code'   	   => trim($_GPC['xsqingjia_code']),
				);
				$xsqjsh = array(
					'xsqjsh'   		   => trim($_GPC['xsqjsh']),
					'sms_SignName'     => trim($_GPC['xsqjsh_singname']),
					'sms_Code'         => trim($_GPC['xsqjsh_code']),
				);
				$jsqingjia = array(
					'jsqingjia'   	   => trim($_GPC['jsqingjia']),
					'sms_SignName'     => trim($_GPC['jsqingjia_singname']),
					'sms_Code'         => trim($_GPC['jsqingjia_code']),
				);
				$jsqjsh = array(
					'jsqjsh'   		   => trim($_GPC['jsqjsh']),
					'sms_SignName'     => trim($_GPC['jsqjsh_singname']),
					'sms_Code'         => trim($_GPC['jsqjsh_code']),
				);	
				$xxtongzhi = array(
					'xxtongzhi'   	   => trim($_GPC['xxtongzhi']),
					'sms_SignName'     => trim($_GPC['xxtongzhi_singname']),
					'sms_Code'         => trim($_GPC['xxtongzhi_code']),
				);
				$liuyan = array(
					'liuyan'   	   	   => trim($_GPC['liuyan']),
					'sms_SignName'     => trim($_GPC['liuyan_singname']),
					'sms_Code'         => trim($_GPC['liuyan_code']),
				);	
				$liuyanhf = array(
					'liuyanhf'   	   => trim($_GPC['liuyanhf']),
					'sms_SignName'     => trim($_GPC['liuyanhf_singname']),
					'sms_Code'         => trim($_GPC['liuyanhf_code']),
				);
				$zuoye = array(
					'zuoye'   	  	   => trim($_GPC['zuoye']),
					'sms_SignName'     => trim($_GPC['zuoye_singname']),
					'sms_Code'         => trim($_GPC['zuoye_code']),
				);	
				$bjtz = array(
					'bjtz'   	  	   => trim($_GPC['bjtz']),
					'sms_SignName'     => trim($_GPC['bjtz_singname']),
					'sms_Code'         => trim($_GPC['bjtz_code']),
				);	
				$bjqshjg = array(
					'bjqshjg'   	   => trim($_GPC['bjqshjg']),
					'sms_SignName'     => trim($_GPC['bjqshjg_singname']),
					'sms_Code'         => trim($_GPC['bjqshjg_code']),
				);
				$bjqshtz = array(
					'bjqshtz'   	   => trim($_GPC['bjqshtz']),
					'sms_SignName'     => trim($_GPC['bjqshtz_singname']),
					'sms_Code'         => trim($_GPC['bjqshtz_code']),
				);	
				$jxlxtx = array(
					'jxlxtx'   	  	   => trim($_GPC['jxlxtx']),
					'sms_SignName'     => trim($_GPC['jxlxtx_singname']),
					'sms_Code'         => trim($_GPC['jxlxtx_code']),
				);
				$jfjgtz = array(
					'jfjgtz'   	  	   => trim($_GPC['jfjgtz']),
					'sms_SignName'     => trim($_GPC['jfjgtz_singname']),
					'sms_Code'         => trim($_GPC['jfjgtz_code']),
				);	
				$sykstx = array( //自由课时提醒家长
					'sykstx'   	  	   => trim($_GPC['sykstx']),
					'sms_SignName'     => trim($_GPC['sykstx_singname']),
					'sms_Code'         => trim($_GPC['sykstx_code']),
				);
				$kcqdtx = array(//固定课时提醒家长
					'kcqdtx'   	  	   => trim($_GPC['kcqdtx']),
					'sms_SignName'     => trim($_GPC['kcqdtx_singname']),
					'sms_Code'         => trim($_GPC['kcqdtx_code']),
				);				
				$kcyytx = array(
					'kcyytx'   	  	   => trim($_GPC['kcyytx']),
					'sms_SignName'     => trim($_GPC['kcyytx_singname']),
					'sms_Code'         => trim($_GPC['kcyytx_code']),
				);
				$sktxls = array(
					'sktxls'   	  	   => trim($_GPC['sktxls']),
					'sms_SignName'     => trim($_GPC['sktxls_singname']),
					'sms_Code'         => trim($_GPC['sktxls_code']),
				);				
				$ptjgtz = array(
					'pttz'   	  	   => trim($_GPC['pttz']),
					'sms_SignName'     => trim($_GPC['pttz_singname']),
					'sms_Code'         => trim($_GPC['pttz_code']),
				);
				$zljgtz = array(
					'zltz'   	  	   => trim($_GPC['zltz']),
					'sms_SignName'     => trim($_GPC['zltz_singname']),
					'sms_Code'         => trim($_GPC['zltz_code']),
				);
				$twcltz = array(
					'twcltz'   	  	   => trim($_GPC['twcltz']),
					'sms_SignName'     => trim($_GPC['twcltz_singname']),
					'sms_Code'         => trim($_GPC['twcltz_code']),
				);
				$bd_set = array(
					'bd_type'   	=> $bd_set['bd_type'],
					'binding_sms'   => $bd_set['binding_sms'],
					'code_time'   	=> intval($_GPC['code_time']),
					'sms_SignName'  => trim($_GPC['bd_singname']),
					'sms_Code' 		=> trim($_GPC['bd_code']),
				);
				if(vis() || keep_Lx()){
                    $fkyytx = array(
                        'fkyytx'   	  	   => trim($_GPC['fkyytx']),
                        'sms_SignName'     => trim($_GPC['fkyytx_singname']),
                        'sms_Code'         => trim($_GPC['fkyytx_code']),
                    );
                    $data['fkyytx']    = $_GPC['fkyytx'] || $_GPC['fkyytx_code'] ? serialize($fkyytx) : '';
                }
				$dkcgtz = array(
					'dkcgtz'   	  	   => trim($_GPC['dkcgtz']),
					'sms_SignName'     => trim($_GPC['dkcgtz_singname']),
					'sms_Code'         => trim($_GPC['dkcgtz_code']),
				);
				$kcpjtx = array(
					'kcpjtx'   	  	   => trim($_GPC['kcpjtx']),
					'sms_SignName'     => trim($_GPC['kcpjtx_singname']),
					'sms_Code'         => trim($_GPC['kcpjtx_code']),
				);
				$jktxtz = array(
					'jktxtz'   	  	   => trim($_GPC['jktxtz']),
					'sms_SignName'     => trim($_GPC['jktxtz_singname']),
					'sms_Code'         => trim($_GPC['jktxtz_code']),
				);
				$data['bd_set']    = $_GPC['code_time'] || $_GPC['bd_singname'] || $_GPC['bd_code'] ? serialize($bd_set) : '';				
				$data['xsqingjia'] = $_GPC['xsqingjia'] || $_GPC['xsqingjia_code'] ? serialize($xsqingjia) : '';
				$data['xsqjsh']    = $_GPC['xsqjsh'] || $_GPC['xsqjsh_code'] ? serialize($xsqjsh) : '';
				$data['jsqingjia'] = $_GPC['jsqingjia'] || $_GPC['jsqingjia_code'] ? serialize($jsqingjia) : '';
				$data['jsqjsh']    = $_GPC['jsqjsh'] || $_GPC['jsqjsh_code'] ? serialize($jsqjsh) : '';
				$data['xxtongzhi'] = $_GPC['xxtongzhi'] || $_GPC['xxtongzhi_code'] ? serialize($xxtongzhi) : '';
				$data['liuyan']    = $_GPC['liuyan'] || $_GPC['liuyan_code'] ? serialize($liuyan) : '';
				$data['liuyanhf']  = $_GPC['liuyanhf'] || $_GPC['liuyanhf_code'] ? serialize($liuyanhf) : '';
				$data['zuoye']     = $_GPC['zuoye'] || $_GPC['zuoye_code'] ? serialize($zuoye) : '';
				$data['bjtz']      = $_GPC['bjtz'] || $_GPC['bjtz_code'] ? serialize($bjtz) : ''; 
				$data['bjqshjg']   = $_GPC['bjqshjg'] || $_GPC['bjqshjg_code'] ? serialize($bjqshjg) : '';
				$data['bjqshtz']   = $_GPC['bjqshtz'] || $_GPC['bjqshtz_code'] ? serialize($bjqshtz) : '';
				$data['jxlxtx']    = $_GPC['jxlxtx'] || $_GPC['jxlxtx_code'] ? serialize($jxlxtx) : '';
				$data['jfjgtz']    = $_GPC['jfjgtz'] || $_GPC['jfjgtz_code'] ? serialize($jfjgtz) : '';
				$data['sykstx']    = $_GPC['sykstx'] || $_GPC['sykstx_code'] ? serialize($sykstx) : '';
				$data['kcyytx']    = $_GPC['kcyytx'] || $_GPC['kcyytx_code'] ? serialize($kcyytx) : '';
				$data['kcqdtx']    = $_GPC['kcqdtx'] || $_GPC['kcqdtx_code'] ? serialize($kcqdtx) : '';
				$data['sktxls']    = $_GPC['sktxls'] || $_GPC['sktxls_code'] ? serialize($sktxls) : '';
				$data['pttz']      = $_GPC['pttz'] || $_GPC['pttz_code'] ? serialize($ptjgtz) : '';
				$data['zltz']      = $_GPC['pttz'] || $_GPC['zltz_code'] ? serialize($zljgtz) : '';
				$data['dkcgtz']    = $_GPC['dkcgtz'] || $_GPC['dkcgtz_code'] ? serialize($dkcgtz) : '';
				$data['twcltz']    = $_GPC['twcltz'] || $_GPC['twcltz_code'] ? serialize($twcltz) : '';
				$data['kcpjtx']    = $_GPC['kcpjtx'] || $_GPC['kcpjtx_code'] ? serialize($kcpjtx) : '';
				$data['jktxtz']    = $_GPC['jktxtz'] || $_GPC['jktxtz_code'] ? serialize($jktxtz) : '';
                if (empty($setting)) {
                    pdo_insert($this->table_set, $data);
                } else {
                    pdo_update($this->table_set, $data, array('weid' => $weid));
                }
				itoast('提交成功', $this->createWebUrl('basic'), 'success');
            }
        
		delvioce('schoolid',FM_JIAOYU_HOST);
   include $this->template ( 'web/basic' );
?>