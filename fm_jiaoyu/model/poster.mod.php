<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

//生成二维码(返回二维码图片地址)
function CreateQrcode($data = ''){
    include_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
    ob_start(); // 在服务器打开一个缓冲区来保存所有的输出
    $errorCorrectionLevel = 'L';	//容错级别
    $matrixPointSize = 10;			//生成图片大小
	QRcode::png($data,false , $errorCorrectionLevel, $matrixPointSize,2.2);
    $imageString = ob_get_contents();
    ob_end_clean(); //清除缓冲区的内容，并将缓冲区关闭，但不会输出内容
	$qrpic = 'data:image/jpg;base64,'.base64_encode($imageString);
    return $qrpic;
}

/*根据
*qrcode 二维码
*pop_img 背景图片
*$configs 海报各种参数
* 返回 海报路径
*/
function CreatPoster($bgimg,$configs = array()){
	$config = setQrcodePoster($bgimg,$configs);//初始化参数
    $jpgname = 'images/fm_jiaoyu/PosterShare/'.random(30) .'.png';
	load()->func('file');
	mkdirs(IA_ROOT . '/attachment/images/fm_jiaoyu', "0777");
	mkdirs(IA_ROOT . '/attachment/images/fm_jiaoyu/PosterShare', "0777");
	$filename = IA_ROOT.'/attachment/'.$jpgname;
	$pop = MakePoster($config,$filename);//合成
	return $jpgname;
}

/**
 * @param $header
 * @param $poster 设置背景图片
 * @param $text   设置文字
 */

function setQrcodePoster($bgimg,$config = array()){
    $font = IA_ROOT . "/addons/fm_jiaoyu/public/web/fonts/simhei.ttf";
    $data = array(
        'image'=>array(
            array( //二维码
                'url'=>"{$config['qrcode']['url']}",
                'left'=>"{$config['qrcode']['left']}",
                'top'=>"{$config['qrcode']['top']}",
                'right'=>0,
                'stream'=>0,
                'bottom'=>0,
                'width'=>"{$config['qrcode']['width']}",
                'height'=>"{$config['qrcode']['height']}",
                'opacity'=>100,
                'border'=>0
            ),
            array( //二维码中心LOGO
                'url'=>"{$config['qrlogo']['url']}",
                'left'=>"{$config['qrlogo']['left']}",
                'top'=>"{$config['qrlogo']['top']}",
                'right'=>0,
                'stream'=>0,
                'bottom'=>0,
                'width'=>"{$config['qrlogo']['width']}",
                'height'=>"{$config['qrlogo']['height']}",
                'opacity'=>101,
                'border'=>0
            ),
            array( // 用户头像
                'url'=>"{$config['header']['url']}",
                'left'=>"{$config['header']['left']}",
                'top'=>"{$config['header']['top']}",
                'right'=>0,
                'stream'=>0,
                'bottom'=>0,
                'width'=>"{$config['header']['width']}",
                'height'=>"{$config['header']['height']}",
                'opacity'=>100,
                'border'=>0
            ), 
        ),
        'text'=>array(
            array( //标题
                'text'=>"{$config['title']['text']}",
                'left'=>"{$config['title']['left']}",
                'top'=>"{$config['title']['top']}",
                'fontPath'=>"{$font}",
                'fontSize'=>"{$config['title']['fontSize']}",
                'fontColor'=>"{$config['title']['fontColot']}",
                'angle'=>0,
            ),
            array( //分享描述
                'text'=>"{$config['description']['text']}",
                'left'=>"{$config['description']['left']}",
                'top'=>"{$config['description']['top']}",
                'fontPath'=>"{$font}",
                'fontSize'=>"{$config['description']['fontSize']}",
                'fontColor'=>"{$config['description']['fontColot']}",
                'angle'=>0,
            ),
        ),
        'background'=>"{$bgimg}",
    );
    return $data;
}

function MakePoster($config=array(),$filename=""){
	//如果要看报什么错，可以先注释调这个header
	header('Content-Type: text/html; charset=ISO-8859-1');
    if(empty($filename)) header("content-type: image/png");
    $imageDefault = array(
        'left'=>0,
        'top'=>0,
        'right'=>0,
        'bottom'=>0,
        'width'=>100,
        'height'=>100,
        'opacity'=>100
    );
    $textDefault = array(
        'text'=>'',
        'left'=>0,
        'top'=>0,
        'fontSize'=>32,       //字号
        'fontColor'=>'255,255,255', //字体颜色
        'angle'=>0,
    );
    $background = $config['background'];//海报最底层得背景
    //背景方法
    $backgroundInfo = getimagesize($background);
    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    $backgroundWidth = imagesx($background);  //背景宽度
    //$backgroundWidth = '604px';  //背景宽度
    $backgroundHeight = imagesy($background);  //背景高度
    //$backgroundHeight = '1008px';  //背景高度
    $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
	$color = imagecolorallocate($imageRes, 1,0,0); //原始颜色
    // $color = imagecolorallocatealpha($imageRes, 255, 255, 255,127);
    // $color = imagecolorallocate($imageRes, 255, 255, 255);
    imagefill($imageRes, 0, 0, $color);
	imageColorTransparent($imageRes, $color);  //颜色透明
    imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
    //处理了图片
    if(!empty($config['image'])){
        foreach ($config['image'] as $key => $val) {
            $val = array_merge($imageDefault,$val);
            if($val['border'] == 1){
				$borderpic = tomedia(borderpic($val['url']));
				$info = getimagesize($borderpic);
			}else{
				$info = getimagesize($val['url']);
			}
            $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
            if($val['stream']){   //如果传的是字符串图像流
                $info = getimagesizefromstring($val['url']);
                $function = 'imagecreatefromstring';
            }
			if($val['border'] == 1){
				$res = $function($borderpic);
			}else{
				$res = $function($val['url']);
			}
            $resWidth = $info[0];
            $resHeight = $info[1];
            //建立画板 ，缩放图片至指定尺寸
			$canvas=imagecreatetruecolor($val['width'], $val['height']);
			
			imagefill($canvas, 0, 0, $color); //处理背板
			imageColorTransparent($canvas, $color);  //颜色透明

			//将源图拷贝到新图上，并设置在保存 PNG 图像时保存完整的 alpha 通道信息  
            //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
			imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
            //放置图像
			imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
        }
    }
    //处理文字
    if(!empty($config['text'])){
        foreach ($config['text'] as $key => $val) {
			$val = array_merge($textDefault,$val);
			//计算文字居中偏移量 start 最终以 $left 为结果
			preg_match_all("/[^\x{4e00}-\x{9fa5}]/u",$val['text'],$arrAl); //非汉字
			preg_match_all('/[\x{4e00}-\x{9fa5}]/u',$val['text'],$arrCh); //汉字
			$CountCH = count($arrCh[0]);
			$MoveCH = $val['fontSize'] * ($CountCH - 1) / 2;
			$CountRE = count($arrAl[0]);
			$MoveRE = $val['fontSize'] * ($CountRE - 1) / 3.5;
			$Left = $val['left'] ;
            list($R,$G,$B) = explode(',', $val['fontColor']);
            $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
            $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
            imagettftext($imageRes,$val['fontSize'],$val['angle'],$Left,$val['top'],$fontColor,$val['fontPath'],$val['text']);
        }
    }
    //生成图片
    if(!empty($filename)){
        $res = imagejpeg ($imageRes,$filename,90); //保存到本地
        imagedestroy($imageRes);
        if(!$res) return false;
		return $filename;
    }else{
        imagejpeg ($imageRes);     //在浏览器上显示
        imagedestroy($imageRes);
    }
}