<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $schoolid = intval($_GPC['schoolid']);
        $userss = !empty($_GPC['userid']) ? intval($_GPC['userid']) : 1;
        $openid = $_W['openid'];
        if (!empty($_GPC['userid'])) {
            mload()->model('user');
            $_SESSION['user'] = check_userlogin($weid, $schoolid, $openid, $userss);
            if ($_SESSION['user'] == 2) {
                include $this->template('bangding');
            }
        }
        $it = pdo_fetch("SELECT * FROM " . GetTableName('user') . " where id = :id And openid = :openid and sid != 0 and tid = 0 ", array(':id' => $_SESSION['user'],':openid' => $openid));
        $school = pdo_fetch("SELECT style2,title,spic,headcolor FROM " . GetTableName('index') . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $schoolid));
        $student = pdo_fetch("SELECT s.s_name,s.icon,c.sname as bjname ,s.sex,s.id FROM ".GetTableName('students')." as s LEFT JOIN ".GetTableName('classify')." as c ON c.sid = s.bj_id WHERE s.schoolid = '{$schoolid}' and s.id = '{$it['sid']}'  ");
        $student['icon'] = !empty($student['icon']) ? $student['icon'] : $school['spic'];
        if (!empty($it)) {
            if($_GPC['op'] == 'scroll_more'){
                $limit  =$_GPC['limit'];
                $condition = " and id < '{$limit}' ";
                $CheckList = pdo_fetchall("SELECT ck.*,u.pard,u.realname FROM ".GetTableName('checkinhome')." as ck LEFT JOIN ".GetTableName('user')." as u ON ck.userid = u.id  WHERE ck.schoolid = '{$schoolid}' and u.sid = '{$student['id']}' {$condition} ORDER BY id DESC LIMIT 0,3  ");
                foreach($CheckList as &$value){
                    switch ($value['pard']) {
                        case '2':
                            $value['gx'] = "父亲";
                            break;
                        case '3':
                            $value['gx'] = "母亲";
                            break;
                        case '4':
                            $value['gx'] = "本人";
                            break;
                        case '5':
                            $value['gx'] = "家长";
                            break;
                        default:
                            $value['gx'] = "家长";
                            break;
                    }
                }
                include $this->template('comtool/checkinhome_bot');

            }else{
                $starttime = strtotime(date("Y-m-d",time()));
                $endtime = $starttime + 86399;
                $IsTodayCheck = 0;
                $CheckToday = pdo_fetch("SELECT id FROM ".GetTableName('checkinhome')." WHERE weid = '{$weid}' and sid = '{$student['id']}' and createtime >= '{$starttime}' and createtime <= '{$endtime}' ");
                if(!empty($CheckToday['id'])){
                    $IsTodayCheck = 1;
                }
                $CheckList = pdo_fetchall("SELECT ck.*,u.pard,u.realname FROM ".GetTableName('checkinhome')." as ck LEFT JOIN ".GetTableName('user')." as u ON ck.userid = u.id  WHERE ck.schoolid = '{$schoolid}' and u.sid = '{$student['id']}' ORDER BY id DESC LIMIT 0,20  ");
                foreach($CheckList as &$value){
                    switch ($value['pard']) {
                        case '2':
                            $value['gx'] = "父亲";
                            break;
                        case '3':
                            $value['gx'] = "母亲";
                            break;
                        case '4':
                            $value['gx'] = "本人";
                            break;
                        case '5':
                            $value['gx'] = "家长";
                            break;
                        default:
                            $value['gx'] = "家长";
                            break;
                    }
                }
    
                include $this->template(''.$school['style2'].'/checkinhome');
            }
           
        } else {
            session_destroy();
            $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
            header("location:$stopurl");
            exit;
        }
