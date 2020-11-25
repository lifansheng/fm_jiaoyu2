<?php
/*
 * @Discription:  教师会议列表
 * @Author: Hannibal·Lee
 * @Date: 2020-09-23 14:26:05
 * @LastEditTime: 2020-09-28 10:21:18
 */
 

global $_W, $_GPC;
$weid = $_W['uniacid'];
$from_user = $this->_fromuser;
$schoolid = intval($_GPC['schoolid']);
$openid = $_W['openid'];

$it = pdo_fetch("SELECT * FROM ".GetTableName('user')." WHERE weid = '{$weid}' and schoolid = '{$schoolid}' and openid = '{$openid}' and tid != 0 and sid = 0 ");
$school = pdo_fetch("SELECT * FROM ".GetTableName('index')." WHERE id = '{$schoolid}' ");
if (empty($it['id'])) {
    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
    header("location:$stopurl");
}else{
    $op = $_GPC['op'] ? $_GPC['op'] : 'display';
    $teafzid = pdo_fetch("SELECT fz_id FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and id = '{$it['tid']}' ")['fz_id'];
    if($op == 'display'){
        $FzList = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type = 'jsfz' ");
        $FzList[] = array(
            'sid' => 0,
            'sname' => '未分组'
        );
        foreach ($FzList as &$value) {
            $fzid = $value['sid'];
            $value['tealist'] = pdo_fetchall("SELECT id,tname FROM ".GetTableName('teachers')." WHERE schoolid = '{$schoolid}' and fz_id = '{$fzid}' ");
        }
        $AllBj = pdo_fetchall("SELECT sid,sname FROM ".GetTableName('classify')." WHERE schoolid = '{$schoolid}' and type='theclass' ");
        include $this->template(''.$school['style3'].'/tcreatemeeting');
    }elseif($_GPC['op'] == 'save'){
        load()->func('communication');
        load()->func('file');
        $token2 = $this->getAccessToken2();
        if(!empty($_GPC ['thumb'])) {
            $url = 'https://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token2.'&media_id='.$_GPC ['thumb'];
            $pic_data = ihttp_request($url);
            $path = "images/fm_jiaoyu/";
            $picurl = $path.random(30) .".jpg";
            file_write($picurl,$pic_data['content']);
            if (!empty($_W['setting']['remote']['type'])) { // 
                $remotestatus = file_remote_upload($picurl); //
                if (is_error($remotestatus)) {
                    $datas ['result'] = false;
                    $datas ['msg'] = '发送图片失败！';	
                    die ( json_encode ( $datas ) );			
                }
            }
        }

        $data = array(
            'weid' => $weid,
            'schoolid' => $schoolid,
            'title' => trim($_GPC['title']),
            'starttime' => strtotime($_GPC['date'].' '.$_GPC['starttime']),
            'endtime' => strtotime($_GPC['date'].' '.$_GPC['endtime']),
            'thumb' => $picurl,
            'content' => $_GPC['content'],
            'type' => $_GPC['type'],
            'earlytime' => intval($_GPC['earlytime']),
            'creator_tid' => $it['tid'],
            'createtime' => time(),
        );
        if ($_GPC['type'] == 1) { //教师会议
            if($_GPC['fzlist']){
                $data['fzlist'] = implode(',', $_GPC['fzlist']);
            }
            if($_GPC['tidlist']){
                $data['tidlist'] = implode(',', $_GPC['tidlist']);
            }
        } else {
            $njid = pdo_fetch("SELECT parentid FROM ".GetTableName('classify')." WHERE sid = '{$_GPC['bjid']}' ")['parentid'];
            $data['bjidlist'] = $_GPC['bjid'];
            $data['njid'] = $njid;
        }
        pdo_insert(GetTableName('checkmeeting', false), $data);
        $id = pdo_insertid();
        //群发操作
        if ($_GPC['issend'] == 1) {
            $url = $_W['siteroot'] . 'app/index.php?i=' . $weid . '&c=entry&schoolid=' . $schoolid . '&m=fm_jiaoyu&do=sendMsg';
            $data = array(
                'id' => $id,
                'schoolid' => $schoolid,
                'weid' => $weid,
                'op' => 'sendMobileMeeting',
            );
            timeOutPost($url, $data);
        }
        $result['msg'] = '创建成功';
        $result['result'] = false;
        die(json_encode($result));

    }
}
