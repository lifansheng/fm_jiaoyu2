<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_GPC, $_W;
		$weid = $_W['uniacid'];
		load()->func('tpl');
		$action = 'newloginctrl';
        $this1 = 'no1';
        $GLOBALS['frames'] = $this->getNaveMenu('',$action,true);
        $logo = pdo_fetch("SELECT a.uniacid as weid ,a.name as title FROM " . tablename('uni_account') . " as a LEFT JOIN". tablename('account'). " as b ON a.uniacid = b.uniacid AND a.default_acid = b.acid where b.isdeleted != 1 And a.default_acid != 0 AND a.uniacid = {$weid}");
        $member = pdo_fetch("SELECT username FROM " . tablename('users') . " WHERE uid = {$_W['uid']}");
        $logo['logo'] = tomedia('headimg_'.$weid.'.jpg');
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';    
        if ($operation == 'display') {
			load()->func('tpl');
			$item = pdo_fetch("SELECT id,is_new,newcenteriocn,banquan,htname,bgcolor,bgimg,banner1,banner2,banner3,banner4 FROM " . tablename($this->table_set) . " ORDER BY id ASC LIMIT 0,1");
		
			if(checksubmit('submit')){
				$data = array(
					'htname'   => trim($_GPC['htname']),
					'bgcolor'  => trim($_GPC['bgcolor']),
					'bgimg'    => trim($_GPC['bgimg']),
					'banner1'  => trim($_GPC['banner1']),
					'banner2'  => trim($_GPC['banner2']),
					'banner3'  => trim($_GPC['banner3']),
					'banner4'  => trim($_GPC['banner4']),
					'banquan'  => trim($_GPC['banquan']),
					'is_new'   => $_GPC['is_new'],
					'newcenteriocn'   => trim($_GPC['newcenteriocn']),
				);
				if(empty($data['htname'])){
					$this->imessage('请输入后台系统名称！', referer(), 'error');
				}
				if($item){
					pdo_update($this->table_set, $data, array('id' => $item['id']));
				}else{
					$data['weid'] = $weid;
					pdo_insert($this->table_set,$data);
                }
                $this->imessage('操作成功', $this->createWebUrl('newloginctrl'), 'success');
			}
        }else if ($operation == 'change_bj') {
			$email = $_GPC['bj_id'];
			if(empty($email)){
				$data ['result'] = false;
				$message = "请输入粉丝昵称！";
			}
			$member = pdo_fetchall("SELECT * FROM " . tablename('mc_members') . " WHERE nickname like '{$email}' And uniacid = '{$weid}' ");
			if($member){
				$m = 0;
				foreach($member as $vel){
					pdo_delete("mc_members", array('uid' => $vel['uid']));
					$m++;
				}
				$fans = pdo_fetchall("SELECT * FROM " . tablename('mc_mapping_fans') . " WHERE nickname like '{$email}' And uniacid = '{$weid}' ");
				if($fans){
					$f = 0;
					foreach($fans as $row){
						pdo_delete("mc_mapping_fans", array('fanid' => $row['fanid']));
						$f++;
					}
				}
				$data ['result'] = true;
				$message = "清理垃圾会员信息{$m}条，清理粉丝垃圾信息{$f}条!";
			}else{
				$data ['result'] = false;
				$message = "未查到相关数据";
			}
			$data ['msg'] = $message;
			die (json_encode($data));
        }else{
			$this->imessage('操作失败, 非法访问.');
		}

include $this->template ( 'web/newloginctrl' );
?>