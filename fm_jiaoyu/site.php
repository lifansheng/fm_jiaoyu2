<?php
/**
 * 微教育模块
 * @author 高贵血迹
 * @url http://www.daren007.com
 */
defined ( 'IN_IA' ) or exit ( 'Access Denied' );
require  'inc/func/menu.php';
include 'model.php';
define('OSSURL', $_W['sitescheme'].getoauthurl().'/addons/fm_jiaoyu/');
define('MODULE_URL_MAIN', $_W['sitescheme'].getoauthurl().'/addons/fm_jiaoyu/');
class Fm_jiaoyuModuleSite extends Menu {
	// 载入逻辑方法
	private function getLogic($_name, $type = "web", $auth = false) {
		global $_W, $_GPC;
		if ($type == 'web') {
            include_once 'inc/func/list.php';
			checkLogin();  //检查登陆
			if($_GPC['schoolid']){
				get_language($_GPC['schoolid']);
				$language = $_W['lanconfig'][$_GPC['do']];
			}
			include_once 'inc/web/' . strtolower ( substr ( $_name, 5 ) ) . '.php';
		} else if ($type == 'mobile') {
			get_language($_GPC['schoolid']);
			$language = $_W['lanconfig'][$_GPC['do']];
			include_once 'inc/mobile/' . strtolower ( substr ( $_name, 8 ) ) . '.php';
		} else if ($type == 'func') {
			include_once 'inc/func/' . strtolower ( substr ( $_name, 8 ) ) . '.php';
		}
	}

	//定义mobile common 接入方法
	private function getLogicmc($_name, $type = "web", $auth = false) {
		global $_W, $_GPC;
		mload()->model('read');
		$unread = check_unread($_SESSION['user']);
		if ($type == 'mobile') {
			 if ($auth) {
				  include_once 'inc/func/isauth.php';
			  }
			session_start();
			get_language($_GPC['schoolid']);
			$language = $_W['lanconfig'][$_GPC['do']];
			include_once 'inc/mobile/common/' . strtolower ( substr ( $_name, 8 ) ) . '.php';
		}
	}

	private function getLogicms($_name, $type = "web", $auth = false) {
		global $_W, $_GPC;
		mload()->model('read');
		$unread = check_unread($_SESSION['user']);
		if ($type == 'mobile') {
			 if ($auth) {
				  include_once 'inc/func/isauth.php';
			  }
			session_start();
			get_language($_GPC['schoolid']);
			$language = $_W['lanconfig'][$_GPC['do']];
			include_once 'inc/mobile/student/' . strtolower ( substr ( $_name, 8 ) ) . '.php';
		}
	}

	private function getLogicmt($_name, $type = "web", $auth = false) {
		global $_W, $_GPC;
		if ($type == 'mobile') {
			if ($auth) {
				include_once 'inc/func/isauth.php';
			}
			get_language($_GPC['schoolid']);
			$language = $_W['lanconfig'][$_GPC['do']];
			//if(strstr('kc',strtolower ( substr ( $_name, 8 ) ))){
				//include_once 'inc/mobile/teacher/kc/' . strtolower ( substr ( $_name, 8 ) ) . '.php';
			//}else{
				include_once 'inc/mobile/teacher/' . strtolower ( substr ( $_name, 8 ) ) . '.php';
			//}
		}
	}

	private function CheckAboveAuth(){
		global $_W;
		if(empty($_W['uid'])){
			message('请先登录', './index.php?c=home&a=welcome&do=platform&', 'error');
		}
	}

	private function getLogicheck($_name, $type = "web", $auth = false) {
		global $_W, $_GPC;
		if ($type == 'mobile') {
			include_once 'inc/mobile/' . strtolower ( substr ( $_name, 8 ) ) . '.php';
		}
	}

	// 载入逻辑方法
	private function getNewManagerLogic($_name, $type = "web", $auth = false) {
		global $_W, $_GPC;
		if ($type == 'web') {
            include_once 'inc/func/newlist.php';
			checkLogin ();  //检查登陆
			include_once 'inc/web/' . strtolower ( substr ( $_name, 5 ) ) . '.php';
		}
	}


	public function doWebAnttop() {
		// $this->CheckAboveAuth();
		//include_once 'inc/web/loginctrl.php';
		include $this->template ( 'dist/index' );
    }

	public function doMobileTestapi() {
        include_once 'inc/webapi/testapi.php';
    }
	
    public function doWebLoginctrl() {
		$this->CheckAboveAuth();
        include_once 'inc/web/loginctrl.php';
    }
    public function doWebLanset() {
		$this->CheckAboveAuth();
        include_once 'inc/web/lanset.php';
    }
    public function doWebUpgrade() {
        include_once 'inc/web/upgrade.php';
    }

	public function doWebIndexajax() {
		include_once 'inc/web/indexajax.php';
	}
	public function doWebExecl_input() {
		include_once 'inc/web/execl_input.php';
	}
    public function doWebFenzu() {
		$this->CheckAboveAuth();
        include_once 'inc/web/fenzu.php';
    }

    public function doWebArea() {
		$this->CheckAboveAuth();
        include_once 'inc/web/area.php';
    }

    public function doWebType() {
		$this->CheckAboveAuth();
        include_once 'inc/web/type.php';
    }

    public function doWebManager() {
		$this->CheckAboveAuth();
        include_once 'inc/web/manager.php';
    }

	public function doWebCity() {
		$this->CheckAboveAuth();
		include_once 'inc/web/city.php';
	}

	public function doWebBanners() {
		$this->CheckAboveAuth();
		include_once 'inc/web/banners.php';
	}

    public function doWebQuery() {
		$this->CheckAboveAuth();
        include_once 'inc/web/query.php';
    }
    public function doWebManger_apps() {
        include_once 'inc/web/manger_apps.php';
    }
    public function doWebBasic() {
		$this->CheckAboveAuth();
        include_once 'inc/web/basic.php';
	}
	/*****************独立后台登陆单独页面入口 START*******************/
	public function doWebNewType() {
        $this->getNewManagerLogic ( __FUNCTION__, 'web' );
    }

	public function doWebNewSchool() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewArea() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewCity() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewManager() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewFenzu() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewBanners() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewComad() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}
	public function doWebNewGuid() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewComload() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewLoginctrl() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewBinding() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewSensitive() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewLanset() {
		$this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNew_Manger_apps() {
        $this->getNewManagerLogic ( __FUNCTION__, 'web' );
	}
	/*****************独立后台登陆单独页面入口 END*******************/

    public function doWebRefund() {
        include_once 'inc/web/refund.php';
    }

    public function doWebGuid() {
        include_once 'inc/web/guid.php';
    }

	public function doWebComad() {
		include_once 'inc/web/comad.php';
	}

	public function doWebSms() {
		include_once 'inc/web/sms.php';
	}

	public function doWebBinding() {
		include_once 'inc/web/binding.php';
	}
	/**
	 * 人脸检测
	 */
	public function doWebFaceSet() {
		include_once 'inc/web/faceset.php';
	}

	/**
	 * 校智付
	 */
	public function doWebXzf() {
		include_once 'inc/web/xzf.php';
	}

	public function doMobileXzfPort() {
		include_once 'inc/func/xzfport.php';
	}

	public function doMobileXzftask() {
		include_once 'inc/func/xzftask.php';
	}

	public function doWebSensitive() {
		include_once 'inc/web/sensitive.php';
	}

	public function doWebComload() {
		include_once 'inc/web/comload.php';
	}

	public function doMobileCheckjl() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkjl.php';
	}

	public function doMobileCheckxz() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkxz.php';
	}

	public function doMobileCheckzbh() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkzbh.php';
	}

	public function doMobileCheckhra() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkhra.php';
	}

	public function doMobileChecktask() {
		global $_GPC, $_W;
		include_once 'inc/func/task.php';
	}

	public function doMobileSendMsg() {
		global $_GPC, $_W;
		include_once 'inc/func/sendmsg.php';
	}
	public function doMobileSync() {
		global $_GPC, $_W;
		include_once 'inc/func/sync.php';
	}

	public function doMobileApcheckdaily() {
		global $_GPC, $_W;
		include_once 'inc/func/apcheckdaily.php';
	}
	public function doMobileHxyport() {
		global $_GPC, $_W;
		include_once 'inc/func/hxyport.php';
	}

	public function doMobileCheckym() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkym.php';
	}

	public function doMobileCheckabb() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkabb.php';
	}

	public function doMobileCheckhx() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkhx.php';
	}

	public function doMobileCheckwn() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkwn.php';
	}

	//班牌
	public function doMobileCheckcc() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkcc.php';
	}

	public function doMobileCheckdy() {
		global $_GPC, $_W;
		include_once 'inc/mac/checkdy.php';
	}

    public function doMobileCheckwt() {
        global $_GPC, $_W;
        include_once 'inc/mac/checkwt.php';
    }

    public function doMobileCheckzb() {
        global $_GPC, $_W;
        include_once 'inc/mac/checkzb.php';
    }
	public function doMobileCash() {
		global $_GPC, $_W;
		include_once 'inc/func/cash.php';
	}
	public function doMobileOpenid() {
		global $_GPC, $_W;
		include_once 'inc/func/openid.php';
	}
	public function doMobileCommon_newpay() {
        include_once 'inc/mobile/common_newpay.php';
    }
    public function doMobileScplforxs() {
		global $_GPC, $_W;
		include_once 'inc/func/isauth.php';
		include_once 'inc/mobile/student/scplforxs.php';
	}

	public function doMobileZhxzyTask() {
		include_once 'inc/func/zhxzytask.php';
	}
	public function doMobileGetcash_wx() {
		global $_GPC, $_W;
		include_once 'inc/func/isauth.php';
		include_once 'inc/func/getcash_wx.php';
	}
	public function doMobilePcauthlogin() {
		global $_GPC, $_W;
		include_once 'inc/func/isauth.php';
		include_once 'inc/func/pcauthlogin.php';
	}
	// ====================== 讯贞新增 =====================
	public function doWebXz_device() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebXz_group() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebRemote() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

 	public function doMobileXz_device() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	// ====================== Web =====================

	public function doWebFace() {
		include_once 'inc/func/face.php';
	}


    public function doWebHelp() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }
	// 学校管理
	public function doWebSchool() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

    public function doWebQrlist() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebPoints() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebMalladd() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebMallorder() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebTemplate() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebPermiss() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebCreates() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 分类管理
	public function doWebSchoolset() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 分类管理
	public function doWebSemester() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	//用户中心
	public function doWebUsercenter() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	// 教师管理
	public function doWebAssess() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 教师评分
	public function doWebGrade() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 学生管理
	public function doWebStudents() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 成绩查询
	public function doWebChengji() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebRemind() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebKsWaring() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebZhuanBjLog() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

    // 课程安排
	public function doWebKecheng() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 营销宝设置
	public function doWebSpecial() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 课表安排
	public function doWebKcbiao() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

    // 公立课表
    public function doWebTimetable () {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	// 课程预约
	public function doWebSubscribe() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 食谱安排
	public function doWebCookBook() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	// 首页导航
	public function doWebNave() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	//班级管理
	public function doWebTheclass() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	//成绩管理
	public function doWebScore() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	//评分项目
	public function doWebPfxm() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	//余额/国家补助消费记录
	public function doWebYuecostlog() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}



	//科目管理
	public function doWebSubject() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

    //时段管理
	public function doWebTimeframe() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	//星期管理
	public function doWebWeek() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	//教师分组
	public function doWebJsfz() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	//访问事由
	public function doWebVisireason() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}


	//Lee作业写入
	public function doWebWjxr() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
		//Lee作业编辑
	public function doWebZybj() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

    //排课设置
    public function doWebCourseSort () {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebBanner() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebApps() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
    public function doWebCook() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }
    //forSUTELIST
    public function doWebBaoming() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

    public function doWebArticle() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

    public function doWebNews() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

    public function doWebWenzhang() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

    public function doWebBjquan() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebCost() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebTestforlee() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebSolotest() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebTestforchieh() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebPayall() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebPhotos() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNotice() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebSignup() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebCheck() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebChecklog() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebCardlist() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebstart() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebShoucelist() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebShouceset() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebAllcamera() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebGongkaike() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	// 课程设置
	public function doWebKcset() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebGkkpjxt() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebGkkpjtj() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebShowgkkpj() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebShowpjdetail() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebGroupactivity() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebZdytest() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebGasignup() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebHouseorder() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebHorecord() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebCoursetype() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebKcyy() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebKcsign() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebEditaddr() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
    public function doWebYzxx() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebDrug() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

    public function doWebKcpingjiashow() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }
    public function doWebChongzhi() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }
	public function doWebMimax() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebSendmsg_muti() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebKcallstusign() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebCheckdateset() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebCheckdatedetail() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebChecktimeset() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebApartmentset() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebAproomset() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebApcheck() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebBooksborrow() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebBooksrecord() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebApcheckall() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebPrinter() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebPrintlog() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebTeascore() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebTscoreobject() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebStudentscore() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

    public function doWebBjscore() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebBuzhu() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebReview() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebUploadsence() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebUpsencerecord() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebNewapcheckall() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}


	#访客功能
	public function doWebVisitors() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}


    public function doWebStuovertime() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }


    public function doWebSendmsgbyall() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	//公物管理
	public function doWebAssetsmanager() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	//公物领用
	public function doWebAssetsuselog() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	//公物维修
	public function doWebAssetsfixlog() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebRoomreservelog() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebYuerefound() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	//班牌功能
    public function doWebClasscards() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClasscardset() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClasskh() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClasshonour() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClassepaper() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClassepraise() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClassepraisetype() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClassepraisecomment() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClasseduty() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebClasschecklog() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}




    public function doWebClasscardactivity(){
        $this->getLogic ( __FUNCTION__, 'web' );
    }
    public function doWebClasscardapplication(){
        $this->getLogic ( __FUNCTION__, 'web' );
    }
    public function doWebClasscardexam(){
        $this->getLogic ( __FUNCTION__, 'web' );
    }
    public function doWebClasscardcountdown(){
        $this->getLogic ( __FUNCTION__, 'web' );
    }
    public function doWebClasscardactivityresult() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }



	//疫情打卡管理
	public function doWebYqdk() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}
	//疫情打卡汇总
	public function doWebYqdkGather() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	//行为评测
	public function doWebBehaviorscore() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}
	
	public function doWebBehaviormanager() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebBehaviorlog() {
        $this->getLogic ( __FUNCTION__, 'web' );
    }

	public function doWebRedPacket() { //红包
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebCoupon() { //优惠券
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	
	public function doWebGetcash() { //提现管理
		$this->getLogic ( __FUNCTION__, 'web' );
	}
	
	/******************TODO:keep_DD()评分考核************/
	public function doWebDdCheckScore() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebDdScoreCategory() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebDdScoreLog() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}
	public function doWebDdEcharts() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebCheckmeeting() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doMobileTDdScorelist(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTDdScoreDetail(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTDdScoreLooklist(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTDdScoreLookDetail(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	/******************TODO:keep_DD()评分考核************/

	// ====================== keep_MC() ================

	public function doMobileTmcEchars() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMorningCheck() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileMcList() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSMcDetail() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}


	public function doMobileSmcList() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMcEchars() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doWebTeachertag(){
		$this->getLogic(__FUNCTION__,'web');
	}
	public function doWebMcManage() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebManual() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebManualmuban() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebManualMubanPage() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebManualGrowPage() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebManualStuGrowPage() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebManualBjGrowPage() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebManualword() {
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebPsychology(){
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebShrink(){
        $this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doWebCheckinhome(){
        $this->getLogic ( __FUNCTION__, 'web' );
	}



	/******************TODO:keep_Lx()**********************/

	public function doWebLxVis() {
		$this->getLogic ( __FUNCTION__, 'web' );
	}

	public function doMobileLxTVis() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileLxTVisLog() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileLxTDoorVis() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileLxSVis() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileLxSVisList() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	/******************TODO:keep_Lx()**********************/

	// ====================== FUNC =====================
	public function doMobileAuth() {
		$this->getLogic ( __FUNCTION__, 'func' );
	}
    // ====================== HOOK=====================
	public function doMobileHookcom() {
		global $_W, $_GPC;
		include_once 'inc/func/isauth.php';
		get_language($_GPC['schoolid']);
		$usertype = 'common';
		include $this->template('hook');
	}
	public function doMobileHookstu() {
		global $_W, $_GPC;
		include_once 'inc/func/isauth.php';
		get_language($_GPC['schoolid']);
		$usertype = 'student';
		include $this->template('hook');
	}
	public function doMobileHooktea() {
		global $_W, $_GPC;
		include_once 'inc/func/isauth.php';
		get_language($_GPC['schoolid']);
		$usertype = 'teacher';
		include $this->template('hook');
	}
	public function doWebHook() {
		global $_W, $_GPC;
		include_once 'inc/func/list.php';
		include $this->template('hook');
	}

	// ====================== HOOKVIS=====================
    public function doMobileHookviscom() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'common';
        include $this->template('hookvis');
    }
    public function doMobileHookvistea() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'teacher';
        include $this->template('hookvis');
    }


    // ====================== HOOKBIGDATA=====================
    public function doMobileHookbigdatacom() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'common';
        include $this->template('hookbigdata');
    }
    public function doMobileHookbigdatatea() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'teacher';
        include $this->template('hookbigdata');
    }


    public function doMobileHookbigdatastu() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'student';
        include $this->template('hookbigdata');
    }

    public function doWebHookbigdata() {
        global $_W, $_GPC;
        include_once 'inc/func/list.php';
        include $this->template('hookbigdata');
	}



    // ====================== HOOKASSETS=====================
    public function doMobileHookassetscom() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'common';
        include $this->template('hookassets');
    }
    public function doMobileHookassetstea() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'teacher';
        include $this->template('hookassets');
    }


    public function doMobileHookassetsstu() {
        global $_W, $_GPC;
        include_once 'inc/func/isauth.php';
        get_language($_GPC['schoolid']);
        $usertype = 'student';
        include $this->template('hookassets');
    }

    public function doWebHookassets() {
        global $_W, $_GPC;
        include_once 'inc/func/list.php';
        include $this->template('web/hookassets');
    }

	// ====================== Mobile=====================

	public function doMobileFace() {
		include_once 'inc/func/face.php';
	}

	public function doMobileZnlport() {
		include_once 'inc/func/znlport.php';
	}

	public function doMobileTestallmobile() {
		include_once 'inc/mobile/testallmobile.php';
	}







 	// 公共部分
	public function doMobileQkbinding() { //快速绑定入口
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileBjBangDing() { //快速绑定入口
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileMtestforlee() { //快速绑定入口
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileBinding() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}
    public function doMobileTrcroom() {
		$this->getLogicms ( __FUNCTION__, 'mobile' );
	}
	public function doMobileComajax() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileShareajax() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileIndexajax() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileTecherajax() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileBjqajax() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

    public function doMobileDongtaiajax() {
		$this->getLogic ( __FUNCTION__, 'mobile' );
	}

	public function doMobileWapindex() {
		$this->getLogic ( __FUNCTION__, 'mobile', true );
	}

	public function doMobilePayajax() {
		$this->getLogic ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileBdajax() {
		$this->getLogic ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileBangding() {
		$this->getLogic ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileNewbding() {
		$this->getLogic ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileXsqj() {
		// $this->getLogic ( __FUNCTION__, 'mobile', true );
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobilekcajax() {
		$this->getLogic ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileLogin() {

		include_once 'inc/mobile/loginapi.php';
	}

	public function doMobileTyptcommon() {
		include_once 'inc/mobile/typtcommon.php';
	}



	public function doWebTestall(){
		$this->getLogic(__FUNCTION__,'web');
	}

	public function doWebCreateQuesForm(){
		$this->getLogic(__FUNCTION__,'web');
	}
	public function doWebQuesFormEcharts(){
		$this->getLogic(__FUNCTION__,'web');
	}
	// ====================== Mobile Common=====================
	public function doMobileGuid() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileShowhomework() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileCooklist() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileCook() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileDetail() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileJianjie() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileKc() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileKcinfo() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileKcdg() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileZhaosheng() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileNewslist() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileNew() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileTeachers() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileTcinfo() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSignup() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSignupjc() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSignuplist() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	#添加访客
	public function doMobileVisitors() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	#访客记录
	public function doMobileVisitorslist() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}
	#访客结果返回
	public function doMobileVisitorsjc() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	#喂药
	public function doMobileDrug() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSdrugLog() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileGoodstemp() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}


	public function doMobileHorder() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileHodetail() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileNewcoursedetail() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileYystcom() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileYzxx() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}


	public function doMobileMimax() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobilePhotoList() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileKctypelist() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSpecial() {
		$this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMcReportInfo(){
        $this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMcReportInfoyear(){
        $this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMcReportInfoxq(){
        $this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}

	/*************************心理咨询****************************/
	public function doMobileShrinkList(){
        $this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSPsychology(){
        $this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSFansPsychology(){
        $this->getLogicmc ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTPsychology(){
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTFansPsychology(){
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	/*************************心理咨询****************************/
	// ====================== Mobile Student=====================
	public function doMobileSzjhlist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileAssteach() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileChecklogDetail() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSchoolbus() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileWxsign() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileCalendar() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSzjh() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileGopay() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileTimetable() {
        $this->getLogicms ( __FUNCTION__, 'mobile', true );
    }

	public function doMobileVideo() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSxcfb() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileChaxun() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileChengji() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileUser() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMyShareList() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileMyfamily() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileUseredit() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMysaleinfo() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileMyinfo() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileJiaoliu() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileMytecher() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileMyclass() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSnoticelist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSnotice() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSzuoye() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSzuoyelist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSbjqfabu() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSbjq() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileOrder() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileMykcinfo() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileMykcdetial() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileObinfo() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSxc() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSxclist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileCheckcard() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileChecklog() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileCallbook() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSlylist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMyKcCalendar() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSduihua() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSliuyan() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileScforxs() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileSclistforxs() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileLeavelist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileAllcamera() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileCamera() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSgoodslist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSgoodsdetail() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSgetorder() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSeditorder() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSmallpay() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSgkkpjjl() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSignrecord() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGalist() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGadetail() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileShrecord() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSsetaddress() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSyzxx() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileKcpingjia() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileChongzhi() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileMysharedetail() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSmyscore() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileYuecostlog() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileStuinfocard() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSmychecklog() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}


	public function doMobileKc_Video_Room() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	//疫情打卡
	public function doMobileSyqdk() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	//疫情打卡列表
	public function doMobileSyqdkList() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	//行为评测列表
	public	function doMobileSbhslist(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	//行为评测详情
	public	function doMobileSbhsdetail(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSmanualList() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSmanualStuPage() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSmanualLookStuPage() {
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSshrinkLog(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMyWelfare(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSmssageToPard(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSmcReportList(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	// ===================== keep_ZHXZY() =========================
	public function doMobileCheckinhome(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSmeetingList(){
		$this->getLogicms ( __FUNCTION__, 'mobile', true );
	}

	// ====================== Mobile Teacher =====================


	//问卷调查 教师端
	public function doMobileQuesformlist(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );
	}
	public function doMobileQuesformdata(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );
	}
	public function doMobileQuesformrecord(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );
	}
	public function doMobileQuesforminfo(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );
	}
	public function doMobileCreatequesform(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );
	}

	public function doMobileSQuesforminfo(){
		$this->getLogicms( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSQuesformList(){
		$this->getLogicms( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSddscoreLookList(){
		$this->getLogicms( __FUNCTION__, 'mobile', true );
	}

	//教师会议列表
	public function doMobileTmeetinglist(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTmeetingInfo(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );

	}

	public function doMobileTcreatemeeting(){
		$this->getLogicmt( __FUNCTION__, 'mobile', true );
	}

	//for master
	public function doMobileHealthcenter() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTempcenter() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTempBjcenter() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTempforclass() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileHealthdatas() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTallcamera() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTcamera() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileNewTcamera() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileClasschecklog() { //与狼共舞
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobilePaystat() { //与狼共舞
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileMallpay() { //与狼共舞
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileStulist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileRehomework() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGetorder() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileEditorder() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGoodslist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileNopoint() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	 public function doMobileGoodsdetail() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileSetaddress() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileShangcheng() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileQuestionnaire() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileQuestatistics() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTcalendar() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileWxsignforteacher() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileLeavelistforteacher() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileCheckcardforteacher() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTlylist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileSign() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileSignlist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTduihua() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTmssage() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTmcomet() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileSmssage() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileLeaveToDoorList() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileSmcomet() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileMnotice() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileMnoticelist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileMfabu() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileQingjia() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileZfabu() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileBjqfabu() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileMyschool() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileBjq() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileZuoye() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileZuoyelist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileFabu() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileNoticelist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileNotice() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTjiaoliulist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTjiaoliu() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileXclist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileXc() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileXcfb() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileBmlist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileBm() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTchecklog() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileJschecklog() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTongxunlu() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTzjhlist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTzjh() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTzjhadd() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileShoucelist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileShoucepl() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileShouce() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileShoucepy() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileShouceadd() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileShoucepygl() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileShoucepyglad() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileRecod() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobilePointdetail() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobilePointrule() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGkklist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGkkdetail() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGkkadd() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileGkkpjjl() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTqzkh() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTKpi() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTGrade() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTDrugLog() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTDrugLogInfo() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTDrugList() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	//**************2018.2.26(Lee)**************
	public function doMobileCreatetodo() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTodolist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	//**************2018.4.1(Lee)**************
	public function doMobileCyylist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileCyydetail() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTmycourse() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTmykcinfo() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//学生补签课程
	public function doMobileTxsbqkc() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTqrjsqd() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTkcsignall() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTkcsigndetail() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTkcsignks() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	public function doMobileTyzxx() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTkcstu() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTmyscore() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTscoreall() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTstuscore() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileMyinfodetail() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTstuapinfo() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTsturoominfo() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTqingjiaall() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileChengjireview() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTsencerecord() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTuploadsence() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTcreatesence() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
    public function doMobileTeatimetable() {
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
    }

	public function doMobileTremind() {
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileGkkpingjia() {
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
    }

    public function doMobileGkkpjshare() {
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
    }

    public function doMobileChengjidetail() {
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
    }
	#预约信息
	public function doMobileTvisitors() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

    public function doMobileTchecklogdetail() {
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
    }
    public function doMobileTbjscore() {
        $this->getLogicmt ( __FUNCTION__, 'mobile', true );
    }
	//查看学生的所有考勤记录
	public function doMobileTallstuchecklog() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	//公物借用列表
	public function doMobileAssetstake() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	//公物借用列表
	public function doMobileAssetslist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	//寝室未按时归寝统计
	public function doMobileOntimeap() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	//快速录入学员考勤卡
	public function doMobileTcardList() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//疫情打卡列表
	public function doMobileTyqdkList() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//疫情打卡详情
	public function doMobileTyqdkInfo() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTMcDetail() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTkcpingjia() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTkctable() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTmanuallist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTmanualstulist() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTmanualStuPage() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTmanualLookStuPage() {
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTshrinkLog(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//年级考勤统计
	public function doMobileTkqStatistics(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//班级考勤数据统计
	public function doMobileTkqBjStatistics(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//学生个人考勤数据统计
	public function doMobileTkqStuStatistics(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//*************** keep_MC() ******************/
	public function doMobileTbhslist(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTbhsdetail(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}
	//宿舍详情
	public function doMobileTapcheck(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	//晨检报告
	public function doMobileTmcReportList(){
		$this->getLogicmt ( __FUNCTION__, 'mobile', true );
	}

	public function doMobileTeacheckinhomelog(){
        $this->getLogicmt ( __FUNCTION__, 'mobile', true  );
	}
	//提现
	public function doMobileGetcash(){
        $this->getLogicmt ( __FUNCTION__, 'mobile', true  );
	}
	
    public function set_tabbar($action, $schoolid, $role, $isfounder) {
		$logo = pdo_fetch("SELECT is_openht FROM " . tablename($this->table_index) . " WHERE id = '{$schoolid}'");
		$actions_titles = $this->actions_titles;
		if ($isfounder || $role == 'owner' ){
			$actions_titles = $this->actions_titles11;
			if(keep_sk77()){
                $actions_titles['theclass'] = '项目管理';
			}
			if(is_TestFz()){
                $actions_titles['score'] = '考试管理';
                $actions_titles['pfxm'] = '评分项目';
			}
			if(is_showpf()){
				$actions_titles['tscoreobject'] = '评分项目';
			}
			if(keep_MC()){
				$actions_titles['teachertag'] = '教师标签';
			}
            if(vis()){
                $arr_insert = array(
                    'visireason' => '访客事由',
                );
                $actions_titles['visireason']= '访客事由';
            }
		}

		if ($role == 'manager' && $logo['is_openht'] == 1){
			$actions_titles = $this->actions_titles22;
		}

		$html = '<ul class="nav nav-tabs">';
        foreach ($actions_titles as $key => $value) {
            $url = $this->createWebUrl($key, array('op' => 'display', 'schoolid' => $schoolid));
            $html .= '<li class="' . ($key == $action ? 'active' : '') . '"><a href="' . $url . '">' . $value . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public $actions_titles = array();
    public $actions_titles22 = array();
    public $actions_titles2 = array( );
    function __construct(){
        session_start();
	    global $_W, $_GPC;

	    $tid = $_W['tid']?$_W['tid']:$_W['role'];
        if(empty($tid) || ($tid != 'founder' && $tid !='owner' )){
            $tid = $_SESSION['tid'];
        }
		$schoolid = $_GPC['schoolid'];
	   if(!empty($tid) && !empty($_GPC['schoolid'])){
			$schoolid = $_GPC['schoolid'];
		
			if(IsHasQx($tid,1000211,1,$schoolid)){
				$this->actions_titles['semester'] = NJNAMEGL;
				$this->actions_titles22['semester'] = NJNAMEGL;
			}
			if(IsHasQx($tid,1000221,1,$schoolid)){
			    if(keep_sk77()){
                    $this->actions_titles['theclass'] = '项目管理';
                    $this->actions_titles22['theclass'] = '项目管理';
                }else{
                    $this->actions_titles['theclass'] = '班级管理';
                    $this->actions_titles22['theclass'] = '班级管理';
                }

			}

			if(IsHasQx($tid,1000231,1,$schoolid)){
				$this->actions_titles['score'] = '期号管理';
				$this->actions_titles22['score'] = '期号管理';
			}
			if(IsHasQx($tid,1000241,1,$schoolid)){
				$this->actions_titles['coursetype'] ='课程类型';
				$this->actions_titles22['coursetype'] ='课程类型';
			}
			if(IsHasQx($tid,1000251,1,$schoolid)){
				$this->actions_titles['editaddr'] = '教室管理';
				$this->actions_titles22['editaddr'] = '教室管理';
			}
			if(IsHasQx($tid,1000261,1,$schoolid)){
				$this->actions_titles['subject'] = '科目管理';
				$this->actions_titles22['subject'] = '科目管理';
			}

			if(keep_MC()){
				if(intval($tid) > 0){
					$t_status = pdo_fetch("SELECT status FROM ".GetTableName('teachers')." WHERE id = '{$tid}' ");
					if($t_status['status'] == 2 ){
						$IsXZ = true;
					}else{
						$IsXZ = false;
					}
				}else{
					$IsXZ = true;
				}
				if($IsXZ){
					$this->actions_titles['teachertag'] = '教师标签';
					$this->actions_titles22['teachertag'] = '教师标签';

				}
			}
			if(IsHasQx($tid,1000271,1,$schoolid)){
				$this->actions_titles['timeframe'] = '时段管理';
				$this->actions_titles22['timeframe'] = '时段管理';
			}
			if(vis()){
               if(IsHasQx($tid,1004101,1,$schoolid)){
                   $this->actions_titles['visireason'] = '访客事由';
                   $this->actions_titles22['visireason'] = '访客事由';
               }
			}
			if(is_showpf()){
				if(IsHasQx($tid,1000291,1,$schoolid)){
					$this->actions_titles['tscoreobject'] = '评分项目';
					$this->actions_titles22['tscoreobject'] = '评分项目';
				}
			}
			$this->actions_titles22['jsfz'] = '教师分组';
			$this->actions_titles22['permiss'] = '帐号管理';

			if(IsHasQx($tid,1001401,1,$schoolid)){
				$this->actions_titles2['article'] = '校园公告';
			}
			if(IsHasQx($tid,1001411,1,$schoolid)){
				$this->actions_titles2['news'] = '校园新闻';
			}
			if(IsHasQx($tid,1001421,1,$schoolid)){
				$this->actions_titles2['wenzhang'] = '精选文章';
			}
	 	}
	}

    public $actions_titles11 = array(
	    'semester'   => NJNAMEGL,
	    'theclass'   => '班级管理',
		'score'      => '期号管理',
		'coursetype' => '课程类型',
	    'editaddr'   => '教室管理',
		'subject'    => '科目管理',
	    'timeframe'  => '时段管理',
	    'jsfz'       => '教师分组',
	    'permiss'    => '帐号管理',
	    'template'   => '模板管理',
    );


    public function set_tabbar2($action, $schoolid) {

        $actions_titles2 = $this->actions_titles2;
        $html = '<ul class="nav nav-tabs">';
        foreach ($actions_titles2 as $key => $value) {
            $url = $this->createWebUrl($key, array('op' => 'display', 'schoolid' => $schoolid));
            $html .= '<li class="' . ($key == $action ? 'active' : '') . '"><a href="' . $url . '">' . $value . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function showMessageAjax($msg, $code = 0){
        $result['code'] = $code;
        $result['msg'] = $msg;
        message($result, '', 'ajax');
    }

    protected function exportexcel($data = array(), $title = array(), $filename = 'report') {
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        //导出xls 开始
        if (!empty($title)) {
            foreach ($title as $k => $v) {
                $title[$k] = iconv("UTF-8", "GBK", $v);
            }
            $title = implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                foreach ($val as $ck => $cv) {
                    $data[$key][$ck] = iconv("UTF-8", "GBK", $cv);
                }
                $data[$key] = implode("\t", $data[$key]);

            }
            echo implode("\n", $data);
        }
    }
	public function weixin_fans_group($url, $data) {
		global $_W, $_GPC;
		$access_token = $this->getAccessToken2();
		$url = sprintf($url, $access_token);
		load()->func('communication');
		$response = ihttp_request($url, $data);
		if (is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if (empty($result)) {
		} elseif (!empty($result['errcode'])) {
			if($result['errcode'] == 45157){
				message("标签名非法，请注意不能和其他标签重名");
			}
			if($result['errcode'] == 45158){
				message("标签名长度超过30个字节");
			}
			if($result['errcode'] == 45056){
				message("创建的标签数过多，请注意不能超过100个,如有特殊需求，请向微信团队申请");
			}
			if($result['errcode'] == -1){
				message("微信服务器繁忙，请稍后再试");
			}
		}
		return $result;
	}

	public function createImageUrlCenter($qr_file,$schoolid) {
		global $_W, $_GPC;
		$param = pdo_fetch("select * from " . tablename($this->table_qrset) . " where id = :id", array(':id' => 1));
		$school = pdo_fetch("select logo,title from " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
		load()->func('file');
		mkdirs('../attachment/images/');
		$target_file = "../attachment/images/". time() . random(16) . ".jpg";

		if (!empty($school['logo'])) {
			$src_file = tomedia($school['logo']);
		} else {
			message('抱歉，'.$school['title'].'没有设置LOGO,请先到学校管理编辑上传学校的LOGO');
		}
		$this->resizeImage($this->imagecreate($qr_file), intval($param['logoqrwidth']), intval($param['logoqrheight']), $target_file);
		list($qrWidth, $qrHeight) = getimagesize($target_file);
		$centerleft = ($qrWidth - intval($param['logowidth'])) / 2;
		$centertop = ($qrHeight - intval($param['logoheight'])) / 2;
		$this->mergeImage($target_file, $src_file, $target_file, array('left' => $centerleft, 'top' => $centertop, 'width' => $param['logowidth'], 'height' => $param['logoheight']));
		return $target_file;
	}

	public function createImageUrlCenterForUser($qr_file,$sid,$tid,$schoolid) {
		global $_W, $_GPC;
		$param = pdo_fetch("select * from " . tablename($this->table_qrset) . " where id = :id", array(':id' => 1));
		if($tid == 0){
			$student = pdo_fetch("select icon from " . tablename($this->table_students) . " where id = :id ", array(':id' => $sid));
			if(!$student['icon']){
				$school = pdo_fetch("select spic,logo from " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
				$src_file = tomedia($school['spic']);
				if($sid == 9999999999){
					$src_file = tomedia($school['logo']);
				}
			}else{
				$src_file = tomedia($student['icon']);
			}
		}
		if($sid == 0){
			$techer = pdo_fetch("select thumb from " . tablename($this->table_teachers) . " where id = :id ", array(':id' => $tid));
			if(!$techer['thumb']){
				$school = pdo_fetch("select tpic from " . tablename($this->table_index) . " where id = :id ", array(':id' => $schoolid));
				$src_file = tomedia($school['tpic']);
			}else{
				$src_file = tomedia($techer['thumb']);
			}
		}
		load()->func('file');
		mkdirs('../attachment/images/fm_jiaoyu/');
		$target_file = "../attachment/images/fm_jiaoyu/". time() . random(16) . ".jpg";
		$this->resizeImage($this->imagecreate($qr_file), intval($param['logoqrwidth']), intval($param['logoqrheight']), $target_file);
		list($qrWidth, $qrHeight) = getimagesize($target_file);
		$centerleft = ($qrWidth - intval($param['logowidth'])) / 2;
		$centertop = ($qrHeight - intval($param['logoheight'])) / 2;
		$this->mergeImage($target_file, $src_file, $target_file, array('left' => $centerleft, 'top' => $centertop, 'width' => $param['logowidth'], 'height' => $param['logoheight']));
		return $target_file;
	}

	private function mergeImage($bg, $qr, $out, $param) {

		global $_W, $_GPC;
		load()->func('file');
		list($bgWidth, $bgHeight) = getimagesize($bg);
		list($qrWidth, $qrHeight) = getimagesize($qr);
		$bgImg = $this->imagecreate($bg);
		$qrImg = $this->imagecreate($qr);
		imagecopyresized($bgImg, $qrImg, $param['left'], $param['top'], 0, 0, $param['width'], $param['height'], $qrWidth, $qrHeight);
		ob_start();
		imagejpeg($bgImg, NULL, 100);
		$contents = ob_get_contents();
		ob_end_clean();
		imagedestroy($bgImg);
		imagedestroy($qrImg);

		file_write($out, $contents);

		//$fh = fopen($out, "w+");
		//fwrite($fh, $contents);
		//fclose($fh);
	}

	function resizeImage($im, $maxwidth, $maxheight, $path)	{
		$pic_width = imagesx($im);
		$pic_height = imagesy($im);
		if (($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
			if ($maxwidth && $pic_width > $maxwidth) {
				$widthratio = $maxwidth / $pic_width;
				$resizewidth_tag = true;
			}
			if ($maxheight && $pic_height > $maxheight) {
				$heightratio = $maxheight / $pic_height;
				$resizeheight_tag = true;
			}
			if ($resizewidth_tag && $resizeheight_tag) {
				if ($widthratio < $heightratio) $ratio = $widthratio; else $ratio = $heightratio;
			}
			if ($resizewidth_tag && !$resizeheight_tag) $ratio = $widthratio;
			if ($resizeheight_tag && !$resizewidth_tag) $ratio = $heightratio;
			$newwidth = $pic_width * $ratio;
			$newheight = $pic_height * $ratio;
			if (function_exists('imagecopyresampled')) {
				$newim = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
			} else {
				$newim = imagecreate($newwidth, $newheight);
				imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
			}
			imagejpeg($newim, $path);
			imagedestroy($newim);
		} else {
			imagejpeg($im, $path);
		}
	}

	private function imagecreate($bg) {
		$bgImg = @imagecreatefromjpeg($bg);
		if (FALSE == $bgImg) {
			$bgImg = @imagecreatefrompng($bg);
		}
		if (FALSE == $bgImg) {
			$bgImg = @imagecreatefromgif($bg);
		}
		return $bgImg;
	}

	public function doMobilePay() {
		global $_W, $_GPC;
        checkauth();
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
		$cose = $_GPC ['cose'];
		$wxpayid = intval($_GPC ['wxpay']);
        //构造支付请求中的参数
        $params = array(
            'tid' => $wxpayid,      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
            'ordersn' => time(),  //收银台中显示的订单号
            'title' => '在线缴费',          //收银台中显示的标题
            'fee' => $cose,
            //'user' => $_W['member']['uid'],     //付款用户, 付款的用户名(选填项)
        );
        //调用pay方法
        include $this->template('students/pay');
	}
    /**
     * 支付后触发这个方法
     * @param $params
     */
	public function payResult($params) {
		global $_W, $_GPC;
		$orderid = $params['tid'];
        $wxpay = pdo_fetch("SELECT * FROM " . tablename($this->table_wxpay) . " WHERE id = '{$orderid}'");
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			$log = pdo_fetch("SELECT tag FROM " . tablename('core_paylog') . " where tid = :tid ", array(':tid' => $orderid));
			$tag = iunserializer($log['tag']);
			$uniontid = $tag['transaction_id'];
			pdo_update($this->table_wxpay, array('status' => 2), array('id' => $orderid));
			pdo_update($this->table_order, array('status' => 2, 'uniontid' => $uniontid, 'paytime' => time(), 'paytype' => 1, 'pay_type' => $params['type']), array('id' => $wxpay['od1']));
			$order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where id = :id ", array(':id' => $wxpay['od1']));
			$cose = $order['cose'];
			if(!$order['couponlogid'] && !$order['redpacketlogid']){
				if ($params['fee'] != $cose) {
					exit('支付失败');
				}
			}
			mload()->model('pay');
			//商城订单
			if(!empty($order['morderid']) && $order['type'] == 6 ){
				DealMallPayResult($order);
			}
			//课程订单
			if($order['type'] == 1){
				DealKcPayResult($order);
				if(!empty($order['team_id'])){//处理订单归属队伍 (团购订单需在此处理)
					$team = pdo_fetch("SELECT * FROM " . GetTableName('sale_team') . " where :id = id ", array(':id' => $order['team_id']));
					if(!empty($team)){
						pdo_update(GetTableName('sale_team',false), array('is_sale' => 1), array('id' => $team['id']));
						pdo_update(GetTableName('sale_team',false), array('is_sale' => 1), array('kcid' => $team['kcid'],'userid' => $team['userid']));
						$teamisfull = CheckTemIsFull($team['id']);
						if($teamisfull){
							SetTeamStuStatus($team['masterid'],$order['sale_type']);
						}
					}
					$thisuser = pdo_fetch("SELECT openid FROM " . GetTableName('user') . " where :id = id ", array(':id' => $order['userid']));
					if($order['sale_type'] == 1){
						$this->sendMobilePttz($order['team_id'],$thisuser['openid']);
					}
					if($order['sale_type'] == 2){
						$this->sendMobileZltz($order['team_id'],$thisuser['openid']);
					}
				}
				if($order['new_stu'] == 1){//处理新增学生 直接购买的用户解锁
					pdo_update(GetTableName('user',false), array('status' => 0), array('id' => $order['userid']));
					pdo_update(GetTableName('students',false), array('status' => 0), array('id' => $order['sid']));
				}
				if($order['couponlogid']){
					pdo_update(GetTableName('welfarelog',false),array('status'=>2,'usetime'=>time()),array('id'=>$order['couponlogid']));
					pdo_run("UPDATE ".GetTableName('order')." SET `couponlogid` = 0 WHERE couponlogid = '{$order['couponlogid']}' AND id != '{$order['id']}' ");
				}
				if($order['redpacketlogid']){
					pdo_update(GetTableName('welfarelog',false),array('status'=>2,'usetime'=>time()),array('id'=>$order['redpacketlogid']));
					pdo_run("UPDATE ".GetTableName('order')." SET `redpacketlogid` = 0 WHERE redpacketlogid = '{$order['redpacketlogid']}' AND id != '{$order['id']}' ");
				}
	
				$send = $this->sendMobileJfjgtz($order['id']);
			}else if($order['type'] == 5){//考勤卡续费
				DealKqkfPayResult($order);
				$send = $this->sendMobileJfjgtz($order['id']);
			}else if($order['type'] == 8){//充值订单
				DealCzPayResult($order);
				$send = $this->sendMobileJfjgtz($order['id']);
			}else if($order['type'] == 9){//充电桩充值
				DealCdzCzPayResult($order);
				$send = $this->sendMobileJfjgtz($order['id']);
			}else if($order['type'] == 4){//报名付费
				$sign = pdo_fetch("SELECT name,nj_id FROM " . tablename($this->table_signup) . " where :id = id", array(':id' => $order['signid']));
				$njinfo = pdo_fetch("SELECT tid FROM " . tablename($this->table_classify) . " WHERE :sid = sid ", array(':sid' => $sign['nj_id']));
				$njzr = pdo_fetch("SELECT openid FROM " . tablename($this->table_teachers) . " WHERE :id = id ", array(':id' => $njinfo['tid']));
				if(!empty($njzr)){
					$this->sendMobileBmshtz($order['signid'], $order['schoolid'], $order['weid'], $njzr['openid'], $sign['name']);
				}
			}else{
				//考勤消费处理
				if($order['type'] == 3){ //功能消费
					$hasKqXf = pdo_fetch("SELECT id,serverend FROM ".GetTableName('cost')." WHERE id = '{$order['costid']}' ");
					if(!empty($hasKqXf) && $hasKqXf['serverend']>0){ //考勤卡消费，开通考勤消费通知
						pdo_update(GetTableName('students',false),array('isopen'=>1),array('id'=>$order['sid'])); 
						pdo_update(GetTableName('idcard',false),array('severend'=>$hasKqXf['serverend']),array('sid'=>$order['sid'],'pard'=>1)); 
					}
				}
				$send = $this->sendMobileJfjgtz($order['id']);
			}
			mload()->model('print');
			order_print($order['id']);
			if($params['chongzhi'] == 'chongzhi'){
				$data_yuelog = array(
					'schoolid' 	=> $order['schoolid'],
					'weid'	   	=> $order['weid'],
					'sid'	   	=> $order['sid'],
					'yue_type' 	=> 2,
					'cost_type' => 2,
					'cost'	   	=> $order['cose'],
					'costtime' 	=> $order['paytime'],
					'orderid'  	=> $order['id'],
					'on_offline' => 1,
					'createtime' => time()
				);
				pdo_insert($this->table_yuecostlog,$data_yuelog);
				$params['from'] = 'return';
			}
		}
		if (empty($params['result']) || $params['result'] != 'success') {
			 pdo_update($this->table_wxpay, array('status' => 1), array('id' => $orderid));
			 pdo_update($this->table_order, array('status' => 1), array('id' => $wxpay['od1']));
			 $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where id = :id ", array(':id' => $wxpay['od1']));
			 if(!empty($order['morderid']) && $order['type'] == 6 ){
				pdo_update($this->table_mallorder, array('status' => 1), array('id' => $order['morderid']));
			}

		}
		if ($params['from'] == 'return' && empty($params['returnurl'])) {
			 $order = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where id = :id ", array(':id' => $wxpay['od1']));
			 $teaid = pdo_fetch("SELECT * FROM " . tablename($this->table_mallorder) . " where id = :id ", array(':id' => $order['morderid']));
			if($order['type'] == 4){
				$url = $_W['siteroot'] . 'app/index.php?i=' . $wxpay['weid'] . '&c=entry&schoolid=' . $wxpay['schoolid'] . '&id=' . $order['signid'] . '&do=signupjc&m=fm_jiaoyu';
			}else if($order['type'] == 5){
				$url = $_W['siteroot'] . 'app/index.php?i=' . $wxpay['weid'] . '&c=entry&schoolid=' . $wxpay['schoolid'] . '&do=user&m=fm_jiaoyu';
			}else if($order['type'] == 7){
				$url = $_W['siteroot'] . 'app/index.php?i=' . $wxpay['weid'] . '&c=entry&schoolid=' . $wxpay['schoolid'] . '&do=user&m=fm_jiaoyu';
			}else if($order['type'] == 6){
				if(!empty($teaid['tid']) && empty($teaid['sid'])){
					$url = $_W['siteroot'] . 'app/index.php?i=' . $wxpay['weid'] . '&c=entry&schoolid=' . $wxpay['schoolid'] . '&do=getorder&m=fm_jiaoyu';
				}else{
					$url = $_W['siteroot'] . 'app/index.php?i=' . $wxpay['weid'] . '&c=entry&schoolid=' . $wxpay['schoolid'] . '&do=sgetorder&userid='.$teaid['userid'].'&op=yes_g&m=fm_jiaoyu';
				}
			}
			else{
				$url = $_W['siteroot'] . 'app/index.php?i=' . $wxpay['weid'] . '&c=entry&schoolid=' . $wxpay['schoolid'] . '&do=user&m=fm_jiaoyu';
			}
			if ($params['result'] == 'success') {
				message('支付成功！', $url, 'success');
			} else {
				message('支付失败！', $url);
			}
		}
	}

	public function uniarr($uniarr, $id) {
		foreach ($uniarr as $key => $value) {
			if ($id == $value) {
				return true;
			}
		}
		return false;
	}

	public function checkpay($schoolid, $sid, $userid, $uid) {
		global $_W, $_GPC;

		$student = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " WHERE :weid = weid And :schoolid = schoolid And :id = id", array(':weid' => $_W['uniacid'], ':schoolid' => $schoolid, ':id' => $sid));
		$cost = pdo_fetchall("SELECT * FROM " . tablename($this->table_cost) . " where weid = :weid And schoolid = :schoolid And is_on = :is_on ", array(':weid' => $_W['uniacid'], ':schoolid' => $schoolid, ':is_on' => 1));

		foreach ($cost as $key => $value) {
			$bjarr = explode(',',$value['bj_id']);
			$is = $this->uniarr($bjarr, $student['bj_id']);
			//print_r($bjarr);
			if ($is) {
				//$bjstatus = true;
				$orderst = pdo_fetch("SELECT * FROM " . tablename($this->table_order) . " where weid = :weid And schoolid = :schoolid And obid = :obid And costid = :costid And sid = :sid And type = :type ", array(
							':weid' => $_W['uniacid'],
							':schoolid' => $schoolid,
							':costid' => $value['id'],
							':obid' => $value['about'],
							':sid' => $sid,
							':type' => 3
							));
				if (empty($orderst)) {
					$orderid = "{$uid}{$sid}";
						$date = array(
							'weid' =>  $_W['uniacid'],
							'schoolid' => $schoolid,
							'sid' => $sid,
							'userid' => $userid,
							'type' => 3,
							'status' => 1,
							'obid' => $value ['about'],
							'costid' => $value ['id'],
							'uid' => $uid,
							'cose' => $value['cost'],
							'payweid' => $value['payweid'],
							'orderid' => $orderid,
							'createtime' => time(),
						);
					pdo_insert($this->table_order, $date);
				}
			}
		}
	}

	public function checkobjiect($schoolid, $sid, $obid) {
		global $_W, $_GPC;
		$order = pdo_fetchall("SELECT costid,paytime,status FROM " . tablename($this->table_order) . " where weid = :weid And schoolid = :schoolid And sid = :sid And type = :type And obid = :obid ORDER BY id DESC LIMIT 0,1", array(
				':weid' => $_W['uniacid'],
				':schoolid' => $schoolid,
				':sid' => $sid,
				':obid' => $obid,
				':type' => 3,
				));
		foreach ($order as $key => $value) {
			$cost = pdo_fetch("SELECT * FROM " . tablename($this->table_cost) . " where weid = :weid And schoolid = :schoolid And is_on = :is_on  And id = :id", array(
					':weid' => $_W['uniacid'],
					':schoolid' => $schoolid,
					':id' => $value['costid'],
					':is_on' => 1
					));
			if (!empty($cost)){
				if ($value['status'] == 2) {
					if ($cost['is_time'] == 1){
						if($cost['endtime'] < TIMESTAMP){
							$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('obinfo', array('id' => $value['costid'], 'schoolid' => $schoolid, 'type' => 1));
							header("location:$stopurl");
						}else if($cost['starttime'] > TIMESTAMP){
							$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('obinfo', array('id' => $value['costid'], 'schoolid' => $schoolid, 'type' => 2));
							header("location:$stopurl");
						}
					}else{
						$time = $cost['dataline'] * 86400;
						$times = $time + $value['paytime'];
						$rest = $times - TIMESTAMP;
						$restday = $rest/86400;
						if ($restday < 0){
							$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('obinfo', array('id' => $value['costid'], 'schoolid' => $schoolid, 'type' => 1));
							header("location:$stopurl");
						}
					}
				}else{
					$stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('obinfo', array('id' => $value['costid'], 'schoolid' => $schoolid, 'type' => 1));
					header("location:$stopurl");
				}
			}
		}
	}

	public function imessage($msg, $redirect = '', $type = '', $tip = '', $btn_text = '确定') {
			global $_W;
			if ($redirect == 'refresh') {
				$redirect = $_W['script_name'] . '?' . $_SERVER['QUERY_STRING'];
				var_dump( $redirect);
			} elseif (!empty($redirect) && !strexists($redirect, 'http://')) {
				$urls = parse_url($redirect);
				$redirect = $_W['siteroot'] . 'web/index.php?' . $urls['query'];
			}
			if ($redirect == '') {
				$type = in_array($type, array('success', 'error', 'info', 'ajax')) ? $type : 'info';
			} else {
				$type = in_array($type, array('success', 'error', 'info', 'ajax')) ? $type : 'success';
			}
			$label = $type;
			if($type == 'error') {
				$label = 'danger';
			}
			if($type == 'ajax' || $type == 'sql') {
				$label = 'warning';
			}
			include $this->template('public/message', TEMPLATE_INCLUDEPATH);
			die;
	}

	public function GetSensitiveWord ($weid){
		$word = pdo_fetch("SELECT sensitive_word FROM " . tablename('wx_school_set') . " WHERE weid = {$weid}");
		return $word['sensitive_word'];
	}

	public function getAccessToken2() {
		global $_GPC, $_W;
		load()->func('communication');
		load()->classs('weixin.account');
		$account_api = WeAccount::create();
		$token = $account_api->getAccessToken();
		return $token;
	}
	public function getAccessToken3($weid) {//返回原来TOKEN
		global $_GPC, $_W;
		load()->func('communication');
		load()->classs('weixin.account');
		$jsauth = pdo_fetch("SELECT * FROM " . tablename('account_wechats') . " WHERE uniacid = '{$weid}'");
		$uniacccount = WeAccount::create($jsauth['acid']);
		$token = $uniacccount->getAccessToken();
		return $token;
	}
	//重定义的paymethod
	public function doMobilePaymethodredefined() {
		global $_W, $_GPC;
		$params = array(
			'fee' => floatval($_GPC['fee']),
			'tid' => $_GPC['tid'],
			'module' => $_GPC['module'],
		);
		if (empty($params['tid']) || empty($params['fee']) || empty($params['module'])) {
			message(error(1, '支付参数不完整'));
		}
		if($params['fee'] <= 0) {
			$notify_params = array(
				'form' => 'return',
				'result' => 'success',
				'type' => '',
				'tid' => $params['tid'],
			);
			$site = WeUtility::createModuleSite($params['module']);
			$method = 'payResult';
			if (method_exists($site, $method)) {
				$site->$method($notify_params);
				message(error(-1, '支付成功'));
			}
		}

		$log = pdo_get('core_paylog', array('uniacid' => $_GPC['payweid'], 'module' => $params['module'], 'tid' => $params['tid']));
		if (empty($log)) {
			$log = array(
				'uniacid' => $_GPC['payweid'],
				'acid' => $_W['acid'],
				'openid' => $_W['member']['uid'],
				'module' => $params['module'],
				'tid' => $params['tid'],
				'fee' => $params['fee'],
				'card_fee' => $params['fee'],
				'status' => '0',
				'is_usecard' => '0',
			);
			pdo_insert('core_paylog', $log);
		}
		if($log['status'] == '1') {
			message(error(1, '订单已经支付'));
		}
		$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
		if(!is_array($setting['payment'])) {
			message(error(1, '暂无有效支付方式'));
		}
		$pay = $setting['payment'];
		if (empty($_W['member']['uid'])) {
			$pay['credit']['switch'] = false;
		}
		if (!empty($pay['credit']['switch'])) {
			$credtis = mc_credit_fetch($_W['member']['uid']);
		}

		include $this->template('pay');
	}

	/**
	 * 执行打标签
	 */
	function DoTag($schoolid,$userid){
		$groupid = GetSchoolGroup($schoolid);
		$result = $this->weixin_fans_group_tag($userid,$groupid);
		$openid_list = $result['openid_list'];
		foreach ($openid_list as $value) {
			mc_init_fans_info($value,false);
		}
	}

	public function weixin_fans_group_tag($userarr, $tagid) {
		global $_W, $_GPC;
		$useridstr = arrayToString($userarr);
		$openlist = pdo_fetchall("SELECT openid FROM " . GetTableName('user') . " WHERE FIND_IN_SET(id,'{$useridstr}')");
		$openidarr = array_column($openlist,'openid');
		if(!$openidarr){
			return error(-1, "用户信息不存在");
		}
		$access_token = $this->getAccessToken2();
		$url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=".$access_token;
		$data = array(
			'openid_list' => $openidarr,
			'tagid' => $tagid,
		);
		load()->func('communication');
		ihttp_request($url, json_encode($data));
		return $data;
	}


	private function doMobilePayWechatReDefined($paylog = array()) {
		global $_W;
		load()->model('payment');

		pdo_update('core_paylog', array(
			'openid' => $_W['openid'],
			'tag' => iserializer(array('acid' => $_W['acid'], 'uid' => $_W['member']['uid']))
		), array('plid' => $paylog['plid']));

		$_W['uniacid'] = $paylog['uniacid'];

		$setting = uni_setting($_W['uniacid'], array('payment'));
		$wechat_payment = $setting['payment']['wechat'];

		$account = pdo_get('account_wechats', array('acid' => $wechat_payment['account']), array('key', 'secret'));

		$wechat_payment['appid'] = $account['key'];
		$wechat_payment['secret'] = $account['secret'];

		$params = array(
			'tid' => $paylog['tid'],
			'fee' => $paylog['card_fee'],
			'user' => $paylog['openid'],
			'title' => urldecode($paylog['title']),
			'uniontid' => $paylog['uniontid'],
		);
		if (intval($wechat_payment['switch']) == PAYMENT_WECHAT_TYPE_SERVICE || intval($wechat_payment['switch']) == PAYMENT_WECHAT_TYPE_BORROW) {
			if (!empty($_W['openid'])) {
				$params['sub_user'] = $_W['openid'];
				$wechat_payment_params = wechat_proxy_build($params, $wechat_payment);
			} else {
				$params['tid'] = $paylog['plid'];
								$params['title'] = urlencode($params['title']);
				$sl = base64_encode(json_encode($params));
				$auth = sha1($sl . $paylog['uniacid'] . $_W['config']['setting']['authkey']);

				$callback = urlencode($_W['siteroot'] . "payment/wechat/pay.php?i={$_W['uniacid']}&auth={$auth}&ps={$sl}");
				$proxy_pay_account = payment_proxy_pay_account();
				if (!is_error($proxy_pay_account)) {
					$forward = $proxy_pay_account->getOauthCodeUrl($callback, 'we7sid-'.$_W['session_id']);
					message(error(2, $forward), $forward, 'ajax');
					exit;
				}
			}
		} else {
			unset($wechat_payment['sub_mch_id']);
			$wechat_payment_params = wechat_build($params, $wechat_payment);
		}
		if (is_error($wechat_payment_params)) {
			message($wechat_payment_params, '', 'ajax', true);
		} else {
			message(error(0, $wechat_payment_params), '', 'ajax', true);
		}
	}

	private function doMobilePayAlipayReDefined($paylog = array()) {
		global $_W;
		load()->model('payment');
		load()->func('communication');
		$_W['uniacid'] = $paylog['uniacid'];
		$setting = uni_setting($_W['uniacid'], array('payment'));
		$params = array(
			'tid' => $paylog['tid'],
			'fee' => $paylog['card_fee'],
			'user' => $paylog['openid'],
			'title' => urldecode($paylog['title']),
			'uniontid' => $paylog['uniontid'],
		);
		$alipay_payment_params = alipay_build($params, $setting['payment']['alipay']);
		if($alipay_payment_params['url']) {
			message(error(0, $alipay_payment_params['url']), '', 'ajax', true);
			exit();
		}
	}

	public function sendCustomNotice($data) {
		if(empty($data)) {
			return error(-1, '参数错误');
		}
		$token = $this->getAccessToken2();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		$response = ihttp_request($url, urldecode(json_encode($data,JSON_UNESCAPED_UNICODE)));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}	

	//重定义的pay
	public function doMobilePayredefined() {
		global $_W, $_GPC;
		$moduels = uni_modules();
		$params = $_POST;

		if(empty($params) || !array_key_exists($params['module'], $moduels)) {
			message(error(1, '模块不存在'), '', 'ajax', true);
		}

		$setting = uni_setting($_W['uniacid'], 'payment');
		$dos = array();
		if(!empty($setting['payment']['credit']['pay_switch'])) {
			$dos[] = 'credit';
		}
		if(!empty($setting['payment']['alipay']['pay_switch'])) {
			$dos[] = 'alipay';
		}
		if(!empty($setting['payment']['wechat']['pay_switch'])) {
			$dos[] = 'wechat';
		}
		if(!empty($setting['payment']['delivery']['pay_switch'])) {
			$dos[] = 'delivery';
		}
		if(!empty($setting['payment']['unionpay']['pay_switch'])) {
			$dos[] = 'unionpay';
		}
		if(!empty($setting['payment']['baifubao']['pay_switch'])) {
			$dos[] = 'baifubao';
		}
		$type = in_array($params['method'], $dos) ? $params['method'] : '';
		if(empty($type)) {
			message(error(1, '暂无有效支付方式,请联系商家'), '', 'ajax', true);
		}
		$moduleid = pdo_getcolumn('modules', array('name' => $params['module']), 'mid');
		$moduleid = empty($moduleid) ? '000000' : sprintf("%06d", $moduleid);
		$uniontid = date('YmdHis').$moduleid.random(8,1);

		$paylog = pdo_get('core_paylog', array('uniacid' => $_GPC['payweid'], 'module' => $params['module'], 'tid' => $params['tid']));
		if (empty($paylog)) {
			$paylog = array(
				'uniacid' => $_GPC['payweid'],
				'acid' => $_W['acid'],
				'openid' => $_W['member']['uid'],
				'module' => $params['module'],
				'tid' => $params['tid'],
				'uniontid' => $uniontid,
				'fee' => $params['fee'],
				'card_fee' => $params['fee'],
				'status' => '0',
				'is_usecard' => '0',
			);
			pdo_insert('core_paylog', $paylog);
			$paylog['plid'] = pdo_insertid();
		}
		if(!empty($paylog) && $paylog['status'] != '0') {
			message(error(1, '这个订单已经支付成功, 不需要重复支付.'), '', 'ajax', true);
		}
		if (!empty($paylog) && empty($paylog['uniontid'])) {
			pdo_update('core_paylog', array(
				'uniontid' => $uniontid,
			), array('plid' => $paylog['plid']));
		}
		$paylog['title'] = $params['title'];
		if (intval($_GPC['iswxapp'])) {
			message(error(2, $_W['siteroot']."app/index.php?i={$_W['uniacid']}&c=wxapp&a=home&do=go_paycenter&title={$params['title']}&plid={$paylog['plid']}"), '', 'ajax', true);
		}
		if ($params['method'] == 'wechat') {
			return $this->doMobilePayWechatReDefined($paylog);
		} elseif ($params['method'] == 'alipay') {
			return $this->doMobilePayAlipayReDefined($paylog);
		} else {
			$params['tid'] = $paylog['plid'];
			$sl = base64_encode(json_encode($params));
			$auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
			message(error(0, $_W['siteroot'] . "/payment/{$type}/pay.php?i={$_W['uniacid']}&auth={$auth}&ps={$sl}"), '', 'ajax', true);
			exit();
		}
	}

}
if(getoauthurl() == 'edu.d3xf.com'){
	define('NJNAME', '机构');
	define('NJNAMEGL', '机构管理');
}else{
	define('NJNAME', '年级');
	define('NJNAMEGL', '年级管理');
}
