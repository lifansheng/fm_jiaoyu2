<?php 
defined('IN_IA') or exit('Access Denied');
load()->func('communication');




require_once(MODULE_ROOT.'/public/php_util/tcpdf.php');

class pdf {
    # 常量设置
    const PDF_LOGO       = '';                    // LOGO路径 该路径是tcpdf下
    const PDF_LOGO_WIDTH = '0';                                    // LOGO宽度
    const PDF_TITLE      = '';                       //
    const PDF_HEAD       = '';
    const PDF_FONT       = 'stsongstdlight';
    const PDF_FONT_STYLE = '';
    const PDF_FONT_SIZE  = 10;
    const PDF_FONT_MONOSPACED = 'courier';
    const PDF_IMAGE_SCALE='1';


    # tcpdf对象存储
    protected $pdf = null;

    /**
     * 构造函数 引入插件并实例化
     */
    public function __construct() {
        # 实例化该插件
        // $this->pdf = new TCPDF('Portrait','mm','B3');
        $this->pdf = new TCPDF('Portrait','mm',array(750,530));
    }

    /**
     * 设置文档信息
     * @param  $user        string  文档作者
     * @param  $title       string  文档标题
     * @param  $subject     string  文档主题
     * @param  $keywords    string  文档关键字
     * @return null
     */
    protected function setDocumentInfo($user = '', $title = '', $subject ='', $keywords = '') {
        if(empty($user) || empty($title)) return false;
        # 文档创建者名称
        $this->pdf->SetCreator("123");
        # 作者
        $this->pdf->SetAuthor($user);
        # 文档标题
        $this->pdf->SetTitle($title);
        # 文档主题
        if(!empty($subject)) $this->pdf->SetSubject($subject);
        # 文档关键字
        if(!empty($keywords)) $this->pdf->SetKeywords($keywords);

    }

    /**
     * 设置文档的页眉页脚信息
     * @param  null
     * @return null
     */
    protected function setHeaderFooter() {
        # 设置页眉信息
        # 格式 logo地址 logo宽度 页眉标题 页眉说明文字 页眉字体颜色 页眉下划线颜色
        $this->pdf->SetHeaderData(self::PDF_LOGO , self::PDF_LOGO_WIDTH , self::PDF_TITLE , self::PDF_HEAD , array(35 , 35 , 35) , array(221,221,221));
        # 设置页脚信息
        # 格式 页脚字体颜色 页脚下划线颜色
        $this->pdf->setFooterData(array(35 , 35 , 35) , array(221,221,221));

        # 设置页眉页脚字体
        $this->pdf->setHeaderFont(array('stsongstdlight' , self::PDF_FONT_STYLE , self::PDF_FONT_SIZE));
        $this->pdf->setFooterFont(array('helvetica' , self::PDF_FONT_STYLE , self::PDF_FONT_SIZE));
    }

    /**
     * 关闭页眉页脚
     * @param  null
     * @return null
     */
    protected function closeHeaderFooter() {
        # 关闭页头
        $this->pdf->setPrintHeader(false);
        # 关闭页脚
        $this->pdf->setPrintFooter(false);
    }

    /**
     * 设置间距 包括正文间距 页眉页脚间距
     * @param  null
     * @return null
     */
    protected function setMargin() {
        # 设置默认的等宽字体
        $this->pdf->SetDefaultMonospacedFont('courier');
        # 正文左侧 上侧 右侧间距
        $this->pdf->SetMargins(0, 0, 0);
        # 页眉间距
        $this->pdf->SetHeaderMargin(0);
        # 页脚间距
        $this->pdf->SetFooterMargin(0);
    }

    /**
     * 正文设置 包括 分页 图片比例 正文字体
     * @param  null
     * @return null
     */
    protected function setMainBody() {
        # 开启分页 true开启 false关闭 开启分页时参数2起作用 表示正文距底部的间距
        $this->pdf->SetAutoPageBreak(true , 0);
        # 设置图片比例
        $this->pdf->setImageScale(self::PDF_IMAGE_SCALE);
        #
        $this->pdf->setFontSubsetting(true);
        # 设置正文字体 stsongstdlight是Adobe Reader默认字体
       $this->pdf->SetFont('stsongstdlight', '', 14);
        
    }

    /**
     * 生成pdf
     * @param  $info    array
     *   array(
     *          'user'=>'文档作者' ,
     *          'title'=>'文档标题' ,
     *          'subject'=>'文档主题' ,
     *          'keywords'=>'文档关键字' ,
     *          'content'=>array(
     *              0 => '内容，base64'
     *          ) ,
     *          'HT'=>'是否开启页眉页脚' ,
     *          'path'=>'文档保存路径'
     *   );
     * @return null
     */
    public function createPDF($info = array()) {
        if(empty($info) || !is_array($info)) return false;

        $this->setDocumentInfo($info['user'] , $info['title'] , $info['subject'] , $info['keywords']);
        if(!$info['HT']) {
            $this->closeHeaderFooter();
        } else {
            $this->setHeaderFooter();
        }
        $this->closeHeaderFooter();
        $this->setMargin();
        $this->setMainBody();

        foreach($info['content'] as  $v){
            # 添加页面 该方法如果前面已有页面 会在将页脚添加到页面中 并自动添加下一页 否则添加新一页
            $this->pdf->AddPage();
            // $this->pdf->Image("https://manger.weimeizhan.com/attachment/images/3/2020/04/V9X8pRo1Orb2828XFXJX1iF8piNKR1.png", 0, 0, 354,500, 'PNG', '', 'center', true, 300);
            // $this->pdf->Image("https://manger.weimeizhan.com/attachment/images/3/2020/04/U804urp2juuIIggg0RmdG4638aa8w0.png", 0, 0, 354,536, 'PNG', '', 'center', true, 300);
            # 写入内容

                $tmp  = base64_decode(substr(base64EncodeImage($v),22));
                // return $tmp;
                // $tmp  = tomedia($v);
                // $tmp  = base64EncodeImage($v);
                // $this->pdf->Image($tmp,0, 0, 750,530, 'PNG', '', 'center', true, 300 );
                $this->pdf->Image('@'.$tmp,0, 0, 750,530, 'JPG', '', 'center', true, 300 );
            // $this->pdf->writeHTML($v, true, false, true, false, '');
        }

        ob_clean();
        # 输出  I输出到浏览器 F输出到指定路径
        $this->pdf->Output($info['path'] , 'F');

        //  $this->DownloadFileNoDel($info['path'],$info['filename']);
    }


}

function base64EncodeImage ($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}
/**
 * $schoolid    学校ID
 * $sid         学生ID
 * $gid         growupfile的ID
 * $auth          2老师3学生
 * $ismobile    是否前端
 */
function GetStuManualPage($schoolid, $sid, $gid,$auth='',$ismobile = false){
    if($auth == 2){
        if($ismobile){
            $condition = " AND g.auth = '{$auth}'";
        }else{
            $condition = " AND g.auth != 1";
        }
    }elseif($auth == 3){
        $condition = " AND g.auth = '{$auth}'";
    }elseif($auth == -1){
        $condition = "";
    }
    $school = pdo_fetch("SELECT title,qroce,logo FROM " . GetTableName('index') . " where id=:id", array(':id' => $schoolid));
    $student = pdo_fetch("SELECT * FROM " . GetTableName('students') . " where :schoolid = schoolid AND id=:id", array(':schoolid' => $schoolid, ':id' => $sid));
    $class = pdo_fetch("SELECT sname FROM " . GetTableName('classify') . " where :schoolid = schoolid AND sid=:sid", array(':schoolid' => $schoolid, ':sid' => $student['bj_id']));
    $list = pdo_fetchAll("SELECT g.sid,g.content_data,g.pdffile,g.bjid,g.isok,g.bjid,g.ssort as gssort,g.auth as gauth,g.id as nowid,g.content_data as gcontainer,m.* FROM ".GetTableName('growuppage')." as g LEFT JOIN " . GetTableName('mubanpage') . " as m on m.id = g.pid WHERE g.gid = '{$gid}' AND g.sid='{$sid}' $condition ORDER BY g.ssort ");
    foreach ($list as $key => $value) {
        $container = json_decode($value['gcontainer'],true);
        foreach ($container as $k => $v) {
            if($v['Client']['showInfo'] == 'schoolQrcode'){
                $container[$k]['Client']['showTitle'] = tomedia($school['qroce']);
            }
            if($v['Client']['showInfo'] == 'schoolLogo'){
                $container[$k]['Client']['showTitle'] = tomedia($school['logo']);
            }
            if($v['Client']['showInfo'] == 'schoolTitle'){
                $container[$k]['Client']['showTitle'] = $school['title'];
            }
            if($v['Client']['showInfo'] == 'stuClass'){
                $container[$k]['Client']['showTitle'] = $class['sname'];
            }
            if($v['Client']['showInfo'] == 'stuName'){
                $container[$k]['Client']['showTitle'] = $student['s_name'];
            }
        }
        $item[$key]['id'] = intval($value['nowid']);
        $item[$key]['sid'] = intval($value['sid']);
        $item[$key]['bjid'] = intval($value['bjid']);
        $item[$key]['gid'] = intval($gid);
        $item[$key]['auth'] = intval($value['gauth']);
        $item[$key]['thumb'] = $value['thumb'];
        $item[$key]['title'] = $value['title'];
        $item[$key]['smallimg'] = tomedia($value['thumb']);
        $item[$key]['order'] = intval($value['gssort']);
        $item[$key]['tagid'] = intval($gid.$value['gssort']);
        $item[$key]['IsStart'] = $value['pagetype'] == 1 ? true : false;
        $item[$key]['IsEnd'] = $value['pagetype'] == 2 ? true : false;
        $item[$key]['data']['backimgurl'] = tomedia($value['bgimg']);
        $item[$key]['data']['pageData'] = $container;
        $item[$key]['is_ok'] = $value['isok'] ? true : false;
    }
    return $item;
}

/**
 * 下载文件（不删除源文件）
 *
 * @param [type] $filePath 文件路径
 * @param [type] $fileName 下载后的文件名
 *
 * @return null
 */
// function DownloadFileNoDel($filePath,$fileName){
//     $fp=fopen($filePath,"r");
//     $file_size=filesize($filePath);
//     //下载文件需要用到的头
//     Header("Content-type: application/octet-stream");
//     Header("Accept-Ranges: bytes");
//     Header("Accept-Length:".$file_size);
//     Header("Content-Disposition: attachment; filename=".$fileName);
//     $buffer=1024;  //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器）
//     $file_count=0; //读取的总字节数
//     //向浏览器返回数据
//     while(!feof($fp) && $file_count<$file_size){
//         $file_con=fread($fp,$buffer);
//         $file_count+=$buffer;
//         echo $file_con;
//     }
//     fclose($fp);
// }
/**
 * 下载文件（不删除源文件）
 *
 * @param [type] $schoolid 文件路径
 * @param [type] $sid 学生id
 * @param [type] $gid growupfile对应的id
 *
 * @return null
 */
function DownloadFileNoDel($schoolid,$sid,$gid){
    $page = pdo_fetch("SELECT pdffile FROM ".GetTableName('growuppage')." WHERE schoolid = '{$schoolid}' AND sid = '{$sid}' AND gid = '{$gid}' ");
    $file = pdo_fetch("SELECT title FROM ".GetTableName('growupfile')." WHERE schoolid = '{$schoolid}' AND id = '{$gid}' ");
    $student = pdo_fetch("SELECT s_name FROM ".GetTableName('students')." WHERE schoolid = '{$schoolid}' AND id = '{$sid}' ");
    $filePath = $_SERVER['DOCUMENT_ROOT'].'attachment/'.$page['pdffile'];
    $fileName = "{$student['s_name']}的{$file['title']}.pdf";
    $fp=fopen($filePath,"r");
    $file_size=filesize($filePath);
    //下载文件需要用到的头
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length:".$file_size);
    Header("Content-Disposition: attachment; filename=".$fileName);
    $buffer=1024;  //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器）
    $file_count=0; //读取的总字节数
    //向浏览器返回数据
    while(!feof($fp) && $file_count<$file_size){
        $file_con=fread($fp,$buffer);
        $file_count+=$buffer;
        echo $file_con;
    }
    fclose($fp);
}