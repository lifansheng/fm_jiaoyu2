<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $schoolid = intval($_GPC['schoolid']);
		$_dialog['d0'][] = array(
			'type'=> "plain",
			'author'=>array(
				'id'=>'lj',
				'name'=>"雷军",
				'avatar'=>"http://c1.mifile.cn/f/i/hd/2016051101/a-lj.png",
			),
		'content'=> " 你好，are you ok？",
		'pause'=> 1000
		);
		$_dialog['d0'][] = array(
			'type'=> "plain",
			'author'=>array(
				'id'=>'lj',
				'name'=>"雷军",
				'avatar'=>"http://c1.mifile.cn/f/i/hd/2016051101/a-lj.png",
			),
		'content'=> " 你好，are you ok？",
		'pause'=> 1000
		);$_dialog['d0'][] = array(
			'type'=> "plain",
			'author'=>array(
				'id'=>'lj',
				'name'=>"雷军",
				'avatar'=>"http://c1.mifile.cn/f/i/hd/2016051101/a-lj.png",
			),
		'content'=> " 你好，are you ok？",
		'pause'=> 1000
		);$_dialog['d0'][] = array(
			'type'=> "plain",
			'author'=>array(
				'id'=>'lj',
				'name'=>"雷军",
				'avatar'=>"http://c1.mifile.cn/f/i/hd/2016051101/a-lj.png",
			),
		'content'=> " 你好，are you ok？",
		'pause'=> 1000
		);
 $back = json_encode($_dialog);
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id= :id", array(':weid' => $weid, ':id' => $schoolid));
        $title = $school['title'];

        if (empty($school)) {
            message('参数错误');
        }
        //var_dump($_dialog);
        include $this->template('web/mimax');
?>