<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
        $schoolid = intval($_GPC['schoolid']);
        //$_Dialog['d0']
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
		$_dialog['d1'][] = array(
			'type'=> "plain",
			'author'=>array(
				'id'=>'me',
				'name'=>"小赫依",
				'avatar'=>"http://c1.mifile.cn/f/i/hd/2016051101/a-lj.png",
			),
		'content'=> "D1——你们的产品一套要多少钱",
		'pause'=> 1000
		
		);
		
		$_dialog['d1'][] = array(
			'type'=> "picture",
			'author'=>array(
				'id'=>'lj',
				'name'=>"雷军",
				'avatar'=>"images/3/2018/02/c0d3T3MUImT9k19daKDM03kz29a09i.png",
			),
		'content'=> "http://c1.mifile.cn/f/i/hd/2016051101/d-1-contrast.jpg",
		
		);
	$_dialog['d1'][] = array(
			'type'=> "plain",
			'author'=>array(
				'id'=>'lj',
				'name'=>"雷军",
				'avatar'=>tomedia("images/3/2018/02/c0d3T3MUImT9k19daKDM03kz29a09i.png"),
			),
		'content'=> "D1_huida——你好，您是想了解年费版，还是单校版，还是多校版呢",
		
		);
		$_dialog['d2'][] = array(
			'type'=> "plain",
			'author'=>array(
				'id'=>'me',
				'name'=>"小赫依",
				'avatar'=>"http://c1.mifile.cn/f/i/hd/2016051101/a-lj.png",
			),
		'content'=> "D2——小米Max能存多少东西？好想放很多很多视频，路上慢慢看",
		
		);
		$ques[] = array(
			'dialog'=> 1 , //提问归宿组（一级）
			'choice' => '1_1' , //提问后续组（二级）
			'title' => '问价格' //title
		);
		$ques[] = array(
			'dialog'=> 2 ,
			'choice' => '2_2' ,
			'title' => '提问2'
		);
		$ques[] = array(
			'dialog'=> 3 ,
			'choice' => 4,
			'title' => '提问3'
		);
	//		_dialog.d2 = [ {
	//	type: 'plain',
	//	author: _members.lwq,
	//	content: '海内存知己，大内存天下啊！',
	//}, {
	//	type: 'plain',
	//	author: _members.zgp,
	//	content: '最高可通过3选2卡槽，扩展到256GB @王川 能装多少部剧呢？',
	//	pause: 3000,
	//}, {
	//	type: 'plain',
	//	author: _members.wc,
	//	content: '我统计了一下，@' + userName + ' 给你三个选项，要不你来猜猜？',
	//},];
 $back = json_encode($_dialog);
        $school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id= :id", array(':weid' => $weid, ':id' => $schoolid));
        $title = $school['title'];

        if (empty($school)) {
            message('参数错误');
        }
        include $this->template(''.$school['style1'].'/mimax');
?>