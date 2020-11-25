<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */

        global $_GPC, $_W;
		$weid = $_W['uniacid'];
		load()->func('tpl');
        $action = 'newbinding';
        $this1 = 'no1';
        $GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
        $logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
        $member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
        $logo['logo'] = tomedia('headimg_'.$weid.'.jpg');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';    
        if ($operation == 'display') {
			load()->func('tpl');
			
			$item = pdo_fetch("SELECT id,bd_set,sms_use_times,baidumapapi FROM " . tablename($this->table_set) . " WHERE weid = :weid ",array(':weid' => $weid));
			$bdset = unserialize($item['bd_set']);
			if(checksubmit('submit')){
				$id = intval($_GPC['id']);
				$temp = array(
					'bd_type'   	=> intval($_GPC['bd_type']),
					'binding_sms'   => intval($_GPC['binding_sms']),
					'code_time' 	=> trim($bdset['code_time']),
					'sms_SignName'  => trim($bdset['sms_SignName']),
					'sms_Code' 		=> trim($bdset['sms_Code']),
				);
				$data['bd_set'] = serialize($temp);
				if($temp['binding_sms'] == 1){
					if(empty($temp['sms_SignName']) || empty($temp['sms_Code'])){
						message('启用短信验证码时，请完善短信相关设置', referer(), 'error');
					}
					if($temp['code_time'] < 300){
						message('短信有效时间建议设置为大于5分钟', referer(), 'error');
					}					
				}
				if($_GPC['baidumapapi']){
					$data['baidumapapi'] = trim($_GPC['baidumapapi']);
				}
				if(!empty($id)){
					pdo_update($this->table_set, $data, array('id' => $id));
				}else{
					$data['weid'] = $weid;
					pdo_insert($this->table_set, $data);
				}
                $this->imessage('设置成功', $this->createWebUrl('newbinding'), 'success');
			}
        } else{
            $this->imessage('操作失败, 非法访问', $this->createWebUrl('newbinding'), 'error');
		}			
		
   include $this->template ( 'web/newbinding' );
?>