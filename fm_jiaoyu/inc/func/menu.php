<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
require  'core.php';
class Menu extends Core
{
    public function getNaveMenu($schoolid, $action, $IsDep = false)
    {
        global $_W, $_GPC;
        $do = $_GPC['do'];
        $op = $_GPC['op'];
        $navemenu = array();
        $school = pdo_fetch("SELECT is_cost,is_recordmac,is_rest,shoucename,is_video,videoname,is_kb,mallsetinfo,issale,is_chongzhi,is_qx,is_printer,is_buzhu,is_ap,is_book  FROM " . GetTableName('index') . " WHERE :id = id", array(':id' => $schoolid));
        $schoolset = pdo_fetch("SELECT * FROM ".GetTableName('schoolset')." WHERE schoolid = '{$schoolid}' ");

        $mallsetinfo = unserialize($school['mallsetinfo']);
        if (!empty($_W['tid']) && $_W['tid'] != 0) {
            $tid = $_W['tid'];
            //查看是否是喂药管家
            $doctor = pdo_fetch("SELECT doctorid FROM " . GetTableName('schoolset') . " WHERE schoolid = '{$schoolid}'");
            if ($doctor['doctorid'] == $tid) {
                $drugmanager = true;
            } else {
                $drugmanager = false;
            }
            //查看是否是校长
            $ismaster = pdo_fetch("SELECT status FROM " . GetTableName('teachers') . " WHERE schoolid = '{$schoolid}' AND id = '{$tid}'");

            if ($ismaster['status'] == 2) {
                $schoolteacher = true;
            } else {
                $schoolteacher = false;
            }

            $loginTeaFzid =  pdo_fetch("SELECT fz_id FROM " . tablename($this->table_teachers) . " where schoolid = :schoolid And id =:id ", array(':schoolid' => $schoolid,':id'=>$tid));
            $qxarr = GetQxByFz($loginTeaFzid['fz_id'], 1, $schoolid);
        }
        if ($IsDep == false) {
			$navemenu[0] = array(
				'title' => '<icon class="fa fa123 fa-cog"></icon><span class="big_title">基本设置</span>',
				'items' => array(

				),
				'icon' => 'fa fa-user-md',
				'this' => 'no1'
			);
            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '1005401')) {
                $navemenu[0]['items'][] =  array(
                    'title' => '校园概览 ',
                    'url' => $do != 'start' ? $this->createWebUrl('start', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                    'active' => $action == 'start' ? ' active' : '',
                    'append' => array(
                        'title' => '',
                    ),
                );
            }
            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100010')) {
                $navemenu[0]['items'][] =  array(
                    'title' => '校园设置 ',
                    'url' => $do != 'schoolset' ? $this->createWebUrl('schoolset', array('op' => 'post', 'schoolid' => $schoolid)) : '#',
                    'active' => $action == 'schoolset' ? ' active' : '',
                    'append' => array(
                        'title' => '',
                    ),
                );
            }
            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10002')) {
                $navemenu[0]['items'][] = array(
                    'title' => '基础设置 ',
                    'url' => $do != 'semester' ? $this->createWebUrl('semester', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                    'active' => $action == 'semester' ? ' active' : '',
                    'append' => array(
                        'title' => '<i style="color:#d9534f;" class="fa fa-bars"></i>',
                    ),
                );
            }
            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100030')) {
                if ($school['is_video']==1 && !empty($school['videoname'])) {
                    $navemenu[0]['items'][] = array(
                        'title' => $school['videoname'],
                        'url' => $do != 'allcamera' ? $this->createWebUrl('allcamera', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'allcamera' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#d9534f;" class="fa fa-eye"></i>',
                        ),
                    );
                }
            }
            if (!$_W['schooltype']) {
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100040')) {
                    $navemenu[0]['items'][] = array(
                        'title' => '食谱管理', 'url' => $do != 'cook' ? $this->createWebUrl('cook', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'cook' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#d9534f;" class="fa fa-cutlery"></i>',
                        )
                    );
                }
            }
            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100050')) {
                $navemenu[0]['items'][] = array(
                    'title' => '幻灯片管理',
                    'url' => $do != 'banner' ? $this->createWebUrl('banner', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                    'active' => $action == 'banner' ? ' active' : '',
                    'append' => array(
                        'title' => '<i style="color:#d9534f;" class="fa fa-image"></i>',
                    ),
                );
            }
            if (!ShowSomeThing()) {
                if ($_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[0]['items'][] = array(
                        'title' => '应用列表',
                        'url' => $do != 'apps' ? $this->createWebUrl('apps', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'apps' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#d9534f;" class="fa fa-cubes"></i>',
                        ),
                    );
                }
            }

            $tag = 1 ;
            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100060') || strstr($qxarr, '100070') || strstr($qxarr, '1004101')   || strstr($qxarr, '100080') || strstr($qxarr, '10009') || strstr($qxarr, '100100') || strstr($qxarr, '10011')) {
                $navemenu[$tag] = array(
                    'title' => '<icon class="fa fa123 fa-database"></icon>  <span class="big_title">教务管理</span>',
                    'this' => 'no2'
                );
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100060')) {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '教师管理',
                        'url' => $do != 'assess' ? $this->createWebUrl('assess', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'assess' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#7228b5;" class="fa fa-user"></i>',
                        ),
                    );
                }
                if (!$_W['schooltype']) {
                    if (is_showpf()) {
                        if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100310')) {
                            $navemenu[$tag]['items'][] = array(
                                'title' => '教师评分',
                                'url' => $do != 'teascore' ? $this->createWebUrl('teascore', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                'active' => $action == 'teascore' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#7228b5;" class="fa fa-pencil"></i>',
                                ),
                            );
                        }
                        if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '1003801')) {
                            $navemenu[$tag]['items'][] = array(
                                'title' => '资料上传',
                                'url' => $do != 'uploadsence' ? $this->createWebUrl('uploadsence', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                'active' => $action == 'uploadsence' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#7228b5;" class="fa fa-upload"></i>',
                                ),
                            );
                        }
                    }
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100070')) {
                    $navemenu[$tag]['items'][] =  array(
                        'title' => '学生管理',
                        'url' => $do != 'students' ? $this->createWebUrl('students', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'students' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#7228b5;" class="fa fa-users"></i>',
                        ),
                    );
                }
                if (!$_W['schooltype']) {
                    if (is_showpf()) {
                        if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100330')) {
                            $navemenu[$tag]['items'][] =  array(
                                'title' => '学生评分',
                                'url' => $do != 'studentscore' ? $this->createWebUrl('studentscore', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                'active' => $action == 'studentscore' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#7228b5;" class="fa fa-pencil-square-o"></i>',
                                ),
                            );
                        }

                        if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '1004101')) {
                            $navemenu[$tag]['items'][] =  array(
                                'title' => '班级评分',
                                'url' => $do != 'bjscore' ? $this->createWebUrl('bjscore', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                'active' => $action == 'bjscore' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#7228b5;" class="fa fa-pencil-square-o"></i>',
                                ),
                            );
                        }
                    }
                    if (Keep_MC()) {
                        $navemenu[$tag]['items'][] =  array(
                            'title' => '行为评测',
                            'url' => $do != 'behaviorscore' ? $this->createWebUrl('behaviorscore', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'behaviorscore' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#7228b5;" class="fa fa-pencil-square-o"></i>',
                            ),
                        );
                    }
                    if (Keep_DD()) {
                        if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10050')) {
                            $navemenu[$tag]['items'][] =  array(
                                'title' => '班级考核',
                                'url' => $do != 'ddscorelog' ? $this->createWebUrl('ddscorelog', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                'active' => $action == 'ddscorelog' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#7228b5;" class="fa fa-pencil-square-o"></i>',
                                ),
                            );
                        }
                    }
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100080')) {
                    $navemenu[$tag]['items'][] =  array(
                        'title' => '成绩管理',
                        'url' => $do != 'chengji' ? $this->createWebUrl('chengji', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'chengji' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#7228b5;" class="fa fa-book"></i>',
                        ),
                    );
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10009')) {
                    if (strstr($qxarr, '1000901') || strstr($qxarr, '1000921') || strstr($qxarr, '1000941') || (is_showgkk() && strstr($qxarr, '1000951')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '课程管理', 'url' => $do != 'kecheng' ? $this->createWebUrl('kecheng', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'kecheng' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#7228b5;" class="fa fa-graduation-cap"></i>',
                            ),
                        );
                    }
                }
                if ($_W['schooltype']) {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '课时预警', 'url' => $do != 'kswaring' ? $this->createWebUrl('kswaring', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'kswaring' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#7228b5;" class="fa fa-question-circle"></i>',
                        ),
                    );
                    $navemenu[$tag]['items'][] = array(
                        'title' => '本周课表', 'url' => $do != 'kcbiao' ? $this->createWebUrl('kcbiao', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'kcbiao' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#7228b5;" class="fa fa-child"></i>',
                        ),
                    );
                    $navemenu[$tag]['items'][] = array(
                        'title' => '转班记录', 'url' => $do != 'zhuanbjlog' ? $this->createWebUrl('zhuanbjlog', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'zhuanbjlog' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#7228b5;" class="fa fa-bomb"></i>',
                        ),
                    );
                }
                if (!$_W['schooltype']) {
                    if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100100')) {
                        if ($school['is_kb'] == 1) {
                            $navemenu[$tag]['items'][] = array(
                                'title' => '公立课表',
                                'url' => $do != 'timetable' ? $this->createWebUrl('timetable', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                'active' => $action == 'timetable' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#7228b5;" class="fa fa-bomb"></i>',
                                ),
                            );
                        }
                    }
                    if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10011')) {
                        if ($school['is_rest'] == 1 && $school['shoucename']) {
                            $navemenu[$tag]['items'][] = array(
                                'title' => $school['shoucename'],
                                'url' => $do != 'shoucelist' ? $this->createWebUrl('shoucelist', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                'active' => $action == 'shoucelist' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#7228b5;" class="fa fa-child"></i>',
                                ),
                            );
                        }
                    }
                }
                if ($_W['schooltype']) {
                    if ($_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '营&nbsp;&nbsp;销&nbsp;&nbsp;宝',
                            'url' => $do != 'special' ? $this->createWebUrl('special', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'special' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#7228b5;" class="fa fa-pencil"></i>',
                            ),
                        );
                    }
                }
                $tag++;
            }
            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10012') || strstr($qxarr, '100130') || strstr($qxarr, '10014') || strstr($qxarr, '100150') || strstr($qxarr, '100160') || strstr($qxarr, '100170') || strstr($qxarr, '100180') || strstr($qxarr, '100190')  || $drugmanager || $schoolteacher || strstr($qxarr, '1004501')) {
                $navemenu[$tag] = array(
                    'title' => '<icon class="fa fa123 fa-wechat"></icon> <span class="big_title">互动管理</span> ',
                    'this' => 'no3'
                );
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10012')) {
                    $navemenu[$tag]['items'][] = array(
                        'title' =>'作业通知请假',
                        'url' => $do != 'notice' ? $this->createWebUrl('notice', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'notice' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#258a25;" class="fa fa-bullhorn"></i>',
                        ),
                    );
                }
                if (!$_W['schooltype']) {
                    if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100130')) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '报名管理',
                            'url' => $do != 'signup' ? $this->createWebUrl('signup', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'signup' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#258a25;" class="fa fa-comments"></i>',
                            ),
                        );
                    }
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10014')) {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '文章系统',
                        'url' => $do != 'article' ? $this->createWebUrl('article', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'article' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#258a25;" class="fa fa-desktop"></i>',
                        ),
                    );
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100150')) {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '班级圈管理',
                        'url' => $do != 'bjquan' ? $this->createWebUrl('bjquan', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'bjquan' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#258a25;" class="fa fa-wechat"></i>',
                        ),
                    );
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100160')) {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '相册管理',
                        'url' => $do != 'photos' ? $this->createWebUrl('photos', array('op' => 'basic', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'photos' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#258a25;" class="fa fa-camera"></i>',
                        ),
                    );
                }
                if (!$_W['schooltype']) {
                    if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100170')) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '集体活动',
                            'url' => $do != 'groupactivity' ? $this->createWebUrl('groupactivity', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'groupactivity' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#258a25;" class="fa fa-trophy"></i>',
                            ),
                        );
                    }
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100180')) {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '家政家教',
                        'url' => $do != 'houseorder' ? $this->createWebUrl('houseorder', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'houseorder' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#258a25;" class="fa fa-coffee"></i>',
                        ),
                    );
                }
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '100190')) {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '校长信箱',
                        'url' => $do != 'yzxx' ? $this->createWebUrl('yzxx', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'yzxx' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#258a25;" class="fa fa-comments"></i>',
                        ),
                    );
                }
                if (!$_W['schooltype']) {
                    if (keep_Blacklist()) {
                        if ($_W['isfounder'] || $_W['role'] == 'owner' || $drugmanager || $schoolteacher || strstr($qxarr, '1004501')) {
                            $navemenu[$tag]['items'][] = array(
                                'title' => '喂药管理',
                                'url' => $do != 'drug' ? $this->createWebUrl('drug', array('schoolid' => $schoolid,'status'=>'-1')) : '#',
                                'active' => $action == 'drug' ? ' active' : '',
                                'append' => array(
                                    'title' => '<i style="color:#258a25;" class="fa fa-medkit" aria-hidden="true"></i>',
                                ),
                            );
                        }
                    }
                }
                $tag++;
            }
            if ($_W['isfounder'] || $_W['role'] == 'owner' || ($school['is_cost'] != 2 && (strstr($qxarr, '100200') || strstr($qxarr, '100210') || strstr($qxarr, '100220') || strstr($qxarr, '100221') || strstr($qxarr, '100222')))) {
                $navemenu[$tag] = array(
                    'title' => '<icon class="fa fa123 fa-money"></icon> <span class="big_title">财务管理</span> ',
                    'this' => 'no4'
                );
                if (($school['is_cost'] != 2 && strstr($qxarr, '100200')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '缴费管理',
                        'url' => $do != 'cost' ? $this->createWebUrl('cost', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'cost' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#cc6b08;" class="fa fa-money"></i>',
                        ),
                    );
                }
                if (($school['is_cost'] != 2 && strstr($qxarr, '100210')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '订单管理',
                        'url' => $do != 'payall' ? $this->createWebUrl('payall', array('op' => 'display', 'schoolid' => $schoolid, 'is_pay' => '-1')) : '#',
                        'active' => $action == 'payall' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#cc6b08;" class="fa fa-bar-chart-o"></i>',
                        ),
                    );
                }
                if (($school['is_cost'] != 2 && (strstr($qxarr, '100220') || strstr($qxarr, '100221') || strstr($qxarr, '100222'))) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    if ($school['is_chongzhi'] == 1) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '充值管理',
                            'url' => $do != 'chongzhi' ? $this->createWebUrl('chongzhi', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'chongzhi' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#cc6b08;" class="fa fa-cny"></i>',
                            ),
                        );
                    }
                }
                if (($schoolset['tx_pay'] != 2 && (strstr($qxarr, '100600') )) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    if ($school['is_chongzhi'] == 1) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '提现管理',
                            'url' => $do != 'getcash' ? $this->createWebUrl('getcash', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'getcash' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#cc6b08;" class="fa fa-cny"></i>',
                            ),
                        );
                    }
                }
                // if (($school['is_cost'] != 2 &&  strstr($qxarr,'1004401')) || $_W['isfounder'] || $_W['role'] == 'owner'    ) {
                //     if($school['is_chongzhi'] == 1){
                //         $navemenu[$tag]['items'][] = array(
                //             'title' => '退费管理',
                //             'url' => $do != 'yuerefound' ? $this->createWebUrl('yuerefound', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                //             'active' => $action == 'yuerefound' ? ' active' : '',
                //             'append' => array(
                //                 'title' => '<i style="color:#cc6b08;" class="fa fa-mail-reply"></i>',
                //             ),
                //         );
                //     }
                // }
                if (($school['is_cost'] != 2 && strstr($qxarr, '10030')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    if ($school['is_printer'] == 1) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '小票打印',
                            'url' => $do != 'printlog' ? $this->createWebUrl('printlog', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'printlog' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#cc6b08;" class="fa fa-print"></i>',
                            ),
                        );
                    }
                }
                if (!$_W['schooltype']) {
                    if (is_showap()) {
                        if (($school['is_cost'] != 2 && strstr($qxarr, '1003901')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                            if ($school['is_buzhu'] == 1) {
                                $navemenu[$tag]['items'][] = array(
                                    'title' => '发放补助',
                                    'url' => $do != 'buzhu' ? $this->createWebUrl('buzhu', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'buzhu' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#cc6b08;" class="fa fa-heart"></i>',
                                    ),
                                );
                            }
                        }
                    }
                }

                // if (($school['is_cost'] != 2 && strstr($qxarr,'1003901') ) || $_W['isfounder'] || $_W['role'] == 'owner' ) {
                if ($school['is_buzhu'] == 1 || $school['is_chongzhi'] || CheckXZF($schoolid)) {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '消费记录',
                            'url' => $do != 'yuecostlog' ? $this->createWebUrl('yuecostlog', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'yuecostlog' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#cc6b08;" class="fa fa-heart"></i>',
                            ),
                        );
                }
                // }

                if ($_W['schooltype']) {
                    if ($_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '红包管理',
                            'url' => $do != 'redpacket' ? $this->createWebUrl('redpacket', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'redpacket' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#cc6b08;" class="fa fa-gift"></i>',
                            ),
                        );

                        $navemenu[$tag]['items'][] = array(
                            'title' => '优惠券管理',
                            'url' => $do != 'coupon' ? $this->createWebUrl('coupon', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'coupon' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#cc6b08;" class="fa fa-barcode"></i>',
                            ),
                        );
                    }
                }

                $tag++;
            }
            if ($_W['isfounder'] || $_W['role'] == 'owner' || ($school['is_recordmac'] != 2 && (strstr($qxarr, '100230') || strstr($qxarr, '100240') || strstr($qxarr, '100250')))) {
                $navemenu[$tag] = array(
                    'title' => '<icon class="fa fa123 fa-credit-card"></icon> <span class="big_title">考勤管理</span> ',
                    'this' => 'no5'
                );
                if (!$_W['schooltype']) {
                    if (($school['is_recordmac'] != 2 && strstr($qxarr, '100290')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '时间设置',
                            'url' => $do != 'checkdateset' ? $this->createWebUrl('checkdateset', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'checkdateset' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#077ccc;" class="fa fa-indent"></i>',
                            ),
                        );
                    }
                }
                if (($school['is_recordmac'] != 2 && strstr($qxarr, '100230')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '考勤记录',
                        'url' => $do != 'checklog' ? $this->createWebUrl('checklog', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'checklog' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#077ccc;" class="fa fa-table"></i>',
                        ),
                    );
                }
                if (($school['is_recordmac'] != 2 && strstr($qxarr, '100250')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '考勤卡库',
                        'url' => $do != 'cardlist' ? $this->createWebUrl('cardlist', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'cardlist' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#077ccc;" class="fa fa-credit-card"></i>',
                        ),
                    );
                }
                if (!$_W['schooltype']) {
                    if (vis()) {
                        if (strstr($qxarr, '1004001') || strstr($qxarr, '1004011')  || $_W['isfounder'] || $_W['role'] == 'owner') {
                            $navemenu[$tag]['items'][] = array(
                            'title' => '访问预约',
                            'url' => $do != 'visitors' ? $this->createWebUrl('visitors', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'visitors' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#077ccc;" class="fa fa-male"></i>',
                            ),
                        );
                        }
                    }
                }
                if (keep_Lx()) {
                    if ($_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                        'title' => '到校访问',
                        'url' => $do != 'lxvis' ? $this->createWebUrl('lxvis', array('op' => 'display', 'schoolid' => $schoolid,'status'=>-1)) : '#',
                        'active' => $action == 'lxvis' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#077ccc;" class="fa fa-male"></i>',
                        ),
                    );
                    }
                }

                if (keep_ZHXZY()) {
                    if ( $_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '1005101')) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '点到记录',
                            'url' => $do != 'checkinhome' ? $this->createWebUrl('checkinhome', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'checkinhome' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#077ccc;" class="fa fa-male"></i>',
                            ),
                        );
                    }
                }
                $tag++;
            }
            if ($mallsetinfo['isShow'] == 1) {
                if ($_W['isfounder'] || $_W['role'] == 'owner' || ($mallsetinfo['isShow'] == 1 && (strstr($qxarr, '100260') || strstr($qxarr, '100270') || strstr($qxarr, '10028')))) {
                    //商城
                    $navemenu[$tag] = array(
                        'title' => '<icon class="fa fa123 fa-shopping-cart"></icon> <span class="big_title">商城管理</span> ',
                        'this' => 'no6'
                    );

                    if (($mallsetinfo['isShow'] == 1 && strstr($qxarr, '100260')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '商品设置',
                            'url' => $do != 'malladd' ? $this->createWebUrl('malladd', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'malladd' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-gift"></i>',
                            ),
                        );
                    }
                    if (($mallsetinfo['isShow'] == 1 && strstr($qxarr, '100270')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '商城订单',
                            'url' => $do != 'mallorder' ? $this->createWebUrl('mallorder', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'mallorder' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-reorder"></i>',
                            ),
                        );
                    }
                    if (($mallsetinfo['isShow'] == 1 && strstr($qxarr, '10028')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '积分管理',
                            'url' => $do != 'points' ? $this->createWebUrl('points', array('op' => 'post', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'points' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-paper-plane-o"></i>',
                            ),
                        );
                    }
                    $tag++;
                }
            }
            // if(!$_W['schooltype']){
            if ($_W['isfounder'] || $_W['role'] == 'owner' || ($schoolset['is_dybp'] == 2 && strstr($qxarr, '10046'))) {
                $navemenu[$tag] = array(
                        'title' => '<icon class="fa fa123 fa-credit-card"></icon> <span class="big_title">班牌管理</span> ',
                        'this' => 'no10'
                    );

                if ((strstr($qxarr, '1004601')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '基础设置',
                            'url' => $do != 'classcardset' ? $this->createWebUrl('classcardset', array('op' => 'post', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classcardset' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-cog"></i>',
                            ),
                        );
                }
                if ((strstr($qxarr, '1004602')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '班级口号',
                            'url' => $do != 'classkh' ? $this->createWebUrl('classkh', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classkh' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-bullhorn"></i>',
                            ),
                        );
                }
                if ((strstr($qxarr, '1004603')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '班级荣誉',
                            'url' => $do != 'classhonour' ? $this->createWebUrl('classhonour', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classhonour' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-glass"></i>',
                            ),
                        );
                }
                if ((strstr($qxarr, '1004604')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '电子板报',
                            'url' => $do != 'classepaper' ? $this->createWebUrl('classepaper', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classepaper' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-edit"></i>',
                            ),
                        );
                }
                if ((strstr($qxarr, '1004605')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '班级值日生',
                            'url' => $do != 'classeduty' ? $this->createWebUrl('classeduty', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classeduty' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-paper-plane-o"></i>',
                            ),
                        );
                }

                if ((strstr($qxarr, '1004606')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '优秀学生',
                            'url' => $do != 'classepraise' ? $this->createWebUrl('classepraise', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classepraise' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-child"></i>',
                            ),
                        );
                }

                if ((strstr($qxarr, '1004601')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '班级活动',
                        'url' => $do != 'classcardactivity' ? $this->createWebUrl('classcardactivity', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'classcardactivity' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#47e0d5;" class="fa fa-credit-card"></i>',
                        ),
                    );
                }

                if ((strstr($qxarr, '1004601')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '倒计时',
                        'url' => $do != 'classcardcountdown' ? $this->createWebUrl('classcardcountdown', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'classcardcountdown' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#47e0d5;" class="fa fa-credit-card"></i>',
                        ),
                    );
                }
                if ((strstr($qxarr, '1004601')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '班级考试',
                        'url' => $do != 'classcardexam' ? $this->createWebUrl('classcardexam', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'classcardexam' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#47e0d5;" class="fa fa-credit-card"></i>',
                        ),
                    );
                }
                if ((strstr($qxarr, '1004601')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                        'title' => '班牌插件',
                        'url' => $do != 'classcardapplication' ? $this->createWebUrl('classcardapplication', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                        'active' => $action == 'classcardapplication' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#47e0d5;" class="fa fa-credit-card"></i>',
                        ),
                    );
                }
                if ((strstr($qxarr, '1004607')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '课堂考勤',
                            'url' => $do != 'classchecklog' ? $this->createWebUrl('classchecklog', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classchecklog' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-building-o"></i>',
                            ),
                        );
                }
                if ((strstr($qxarr, '1004608')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                    $navemenu[$tag]['items'][] = array(
                            'title' => '班牌列表',
                            'url' => $do != 'classcards' ? $this->createWebUrl('classcards', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'classcards' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-credit-card"></i>',
                            ),
                        );
                }
                $tag++;
            }
            // }

            if (!$_W['schooltype']) {
                if ($school['is_ap'] == 1) {
                    if ($_W['isfounder'] || $_W['role'] == 'owner' || (strstr($qxarr, '10032')  || strstr($qxarr, '100340'))) {
                        $navemenu[$tag] = array(
                                'title' => '<icon class="fa fa123 fa-building-o"></icon>  <span class="big_title">宿舍管理</span>',
                                'this' => 'no7'
                            );
                        if (strstr($qxarr, '10032')  || $_W['isfounder'] || $_W['role'] == 'owner') {
                            $navemenu[$tag]['items'][] = array(
                                    'title' => '楼栋设置',
                                    'url' => $do != 'apartmentset' ? $this->createWebUrl('apartmentset', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'apartmentset' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#b0c312;" class="fa fa-tasks"></i>',
                                    ),
                                );
                        }
                        if (strstr($qxarr, '100340')  || $_W['isfounder'] || $_W['role'] == 'owner') {
                            $navemenu[$tag]['items'][] = array(
                                    'title' => '宿舍考勤',
                                    'url' => $do != 'apcheck' ? $this->createWebUrl('apcheck', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'apcheck' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#b0c312;" class="fa fa-calendar"></i>',
                                    ),
                                );
                        }
                        if (strstr($qxarr, '1003501')  || $_W['isfounder'] || $_W['role'] == 'owner') {
                            $navemenu[$tag]['items'][] = array(
                                    'title' => '实时汇总',
                                    'url' => $do != 'apcheckall' ? $this->createWebUrl('apcheckall', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'apcheckall' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#b0c312;" class="fa fa-desktop"></i>',
                                    ),
                                );
                        }
                        $tag++;
                    }
                }
            }
            if (is_showap()) {
                if (!$_W['schooltype']) {
                    if ($school['is_book'] == 1) {
                        if ($_W['isfounder'] || $_W['role'] == 'owner' || (strstr($qxarr, '100360') || strstr($qxarr, '100370'))) {
                            $navemenu[$tag] = array(
                                'title' => '<icon class="fa fa123 fa-book"></icon>  <span class="big_title">图书借阅</span>',
                                'this' => 'no8'
                            );
                            if (($school['is_recordmac'] != 2 && strstr($qxarr, '100360')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                                $navemenu[$tag]['items'][] = array(
                                    'title' => '借阅与归还',
                                    'url' => $do != 'booksborrow' ? $this->createWebUrl('booksborrow', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'booksborrow' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#fd65ff;" class="fa fa-language"></i>',
                                    ),
                                );
                            }

                            if (strstr($qxarr, '100370')  || $_W['isfounder'] || $_W['role'] == 'owner') {
                                $navemenu[$tag]['items'][] = array(
                                    'title' => '借阅记录',
                                    'url' => $do != 'booksrecord' ? $this->createWebUrl('booksrecord', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'booksrecord' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#fd65ff;" class="fa fa-reorder"></i>',
                                    ),
                                );
                            }
                        }
                        $tag++;
                    }
                }
            }




            if (!$_W['schooltype']) {
                if (assets()) {
                    if ($schoolset['is_gw'] == 1 || $schoolset['is_csyd'] == 1) {
                        if ($_W['isfounder'] || $_W['role'] == 'owner' || (strstr($qxarr, '10042') || strstr($qxarr, '10043'))) {
                            $navemenu[$tag] = array(
                                'title' => '<icon class="fa fa123 fa-th"></icon>  <span class="big_title">公物管理</span>',
                                'this' => 'no9'
                            );
                            if (($schoolset['is_csyd'] == 1 && strstr($qxarr, '1004201')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                                $navemenu[$tag]['items'][] = array(
                                    'title' => '场室预定',
                                    'url' => $do != 'roomreservelog' ? $this->createWebUrl('roomreservelog', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'roomreservelog' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#7963d0;" class="fa fa-thumb-tack"></i>',
                                    ),
                                );
                            }

                            if (($schoolset['is_gw'] == 1 && strstr($qxarr, '10043')) || $_W['isfounder'] || $_W['role'] == 'owner') {
                                $navemenu[$tag]['items'][] = array(
                                    'title' => '公物管理',
                                    'url' => $do != 'assetsmanager' ? $this->createWebUrl('assetsmanager', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                                    'active' => $action == 'assetsmanager' ? ' active' : '',
                                    'append' => array(
                                        'title' => '<i style="color:#7963d0;" class="fa fa-table"></i>',
                                    ),
                                );
                            }
                        }
                        $tag++;
                    }
                }
            }

            if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '10024')) {
                $navemenu[$tag] = array(
                    'title' => '<icon class="fa fa123 fa fa-gears"></icon> <span class="big_title">设备管理</span> ',
                    'icon' => 'fa fa-user-md',
                    'this' => 'no11',
                );
                $navemenu[$tag]['items'][] = array(
                    'title' => '设备管理',
                    'url' => $do != 'check' ? $this->createWebUrl('check', array('op' => 'newdisplay', 'schoolid' => $schoolid)) : '#',
                    'active' => $action == 'check' ? ' active' : '',
                    'append' => array(
                        'title' => '<i style="color:#47e0d5;" class="fa fa-child"></i>',
                    ),
                );
                $tag++;
            }
            if (!$_W['schooltype']) {
                if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '1004801')) {
                    if (keep_MC()) {
                        $navemenu[$tag] = array(
                            'title' => '<icon class="fa fa123 fa-stethoscope"></icon> <span class="big_title">晨检管理</span> ',
                            'icon' => 'fa fa-user-md',
                            'this' => 'no12',
                        );
                        $navemenu[$tag]['items'][] = array(
                            'title' => '晨检管理',
                            'url' => $do != 'mcmanage' ? $this->createWebUrl('mcmanage', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'mcmanage' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-child"></i>',
                            ),
                        );
                    } else {
                        $navemenu[$tag] = array(
                            'title' => '<icon class="fa fa123 fa-stethoscope"></icon> <span class="big_title">体检检测</span> ',
                            'url' => $do != 'mcmanage' ? $this->createWebUrl('mcmanage', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'this' => 'no12',
                            'active' => $action == 'mcmanage' ? ' active' : '',
                        );
                    }
                    
                    $tag++;
                }
                if (keep_MC() && $schoolset['is_manual'] == 1) {
                    // if($_W['isfounder'] || $_W['role'] == 'owner'){
                    $navemenu[$tag] = array(
                            'title' => '<icon class="fa fa123 fa-child"></icon>  <span class="big_title">成长手册</span>',
                            'this' => 'no14'
                        );
                    if ($schoolteacher || $_W['isfounder'] || $_W['role'] == 'owner') {
                        $op = 'display';
                    } else {
                        $op = 'teawrite';
                    }
                    $navemenu[$tag]['items'][] = array(
                            'title' => '手册管理',
                            'url' => $do != 'manual' ? $this->createWebUrl('manual', array('op' => $op, 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'manual' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-child"></i>',
                            ),
                        );
                    $navemenu[$tag]['items'][] = array(
                            'title' => '模板管理',
                            'url' => $do != 'manualmuban' ? $this->createWebUrl('manualmuban', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'manualmuban' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-folder"></i>',
                            ),
                        );
                    $navemenu[$tag]['items'][] = array(
                            'title' => '评语管理',
                            'url' => $do != 'manualword' ? $this->createWebUrl('manualword', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'manualword' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-book"></i>',
                            ),
                        );
                    $tag++;
                    // }
                }
                $navemenu[$tag] = array(
                    'title' => '<icon class="fa fa123 fa-medkit"></icon> <span class="big_title">疫情打卡</span> ',
                    'this' => 'no13',
                );
                $navemenu[$tag]['items'][] = array(
                    'title' => '疫情打卡',
                    'url' => $do != 'yqdkgather' ? $this->createWebUrl('yqdkgather', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                    'active' => $action == 'yqdkgather' ? ' active' : '',
                    'append' => array(
                        'title' => '<i style="color:#47e0d5;" class="fa fa-child"></i>',
                    ),
                );
                $tag++;
            }



            //1004701
            if (keep_Bjq()) {
                if ($_W['isfounder'] || $_W['role'] == 'owner' || CheckShrink($_W['tid']) || strstr($qxarr, '1004701')) {
                    $navemenu[$tag] = array(
                        'title' => '<icon class="fa fa123 fa-child"></icon>  <span class="big_title">心理咨询</span>',
                        'this' => 'no15'
                    );
                    if ($_W['isfounder'] || $_W['role'] == 'owner' || strstr($qxarr, '1004701')) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '老师管理',
                            'url' => $do != 'shrink' ? $this->createWebUrl('shrink', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'shrink' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-child"></i>',
                            ),
                        );
                    }
                    if (CheckShrink($_W['tid'])) {
                        $navemenu[$tag]['items'][] = array(
                            'title' => '记录管理',
                            'url' => $do != 'psychology' ? $this->createWebUrl('psychology', array('op' => 'display', 'schoolid' => $schoolid)) : '#',
                            'active' => $action == 'psychology' ? ' active' : '',
                            'append' => array(
                                'title' => '<i style="color:#47e0d5;" class="fa fa-folder"></i>',
                            ),
                        );
                    }
                    $tag++;
                }
            }
        } else {
            $navemenu[0] = array(
                'title' => '<icon class="fa fa123 fa-cog"></icon>  <span class="big_title">基本设置</span>',
                'items' => array(
                    0 => array(
                        'title' => '分类设置 ',
                        'url' => $do != 'newtype' ? $this->createWebUrl('newtype', array('op' => 'display')) : $this->createWebUrl('newtype', array('op' => 'display')),
                        'active' => $action == 'newtype' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#d9534f;" class="fa fa-bars"></i>',
                        ),
                    ),
                    1 => array(
                        'title' => '学校管理 ',
                        'url' => $do != 'newschool' ? $this->createWebUrl('newschool', array('op' => 'display')) : $this->createWebUrl('newtype', array('op' => 'display')),
                        'active' => $action == 'newschool' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#d9534f;" class="fa fa-bank"></i>',
                        ),
                    ),
                    2 => array(
                        'title' => '校区设置 ',
                        'url' => $do != 'newarea' ? $this->createWebUrl('newarea', array('op' => 'display')) : $this->createWebUrl('newarea', array('op' => 'display')),
                        'active' => $action == 'newarea' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#d9534f;" class="fa fa-cog"></i>',
                        ),
                    ),
                    3 => array(
                        'title' => '平台功能 ',
                        'url' => $do != 'newmanager' ? $this->createWebUrl('newmanager', array('op' => 'display')) : $this->createWebUrl('newmanager', array('op' => 'display')),
                        'active' => $action == 'newmanager' || $action == 'new_manger_apps' || $action == 'newlanset' || $action == 'newsensitive' || $action == 'newbinding' || $action == 'newloginctrl' || $action == 'newcomload' || $action == 'newguid' || $action == 'newcomad' || $action == 'newbanners' || $action == 'newfenzu' ? ' active' : '',
                        'append' => array(
                            'title' => '<i style="color:#d9534f;" class="fa fa-cubes"></i>',
                        ),
                    ),

                ),
                'icon' => 'fa fa-user-md',
                'this' => 'no1'
            );
        }


        return $navemenu;
    }
}
