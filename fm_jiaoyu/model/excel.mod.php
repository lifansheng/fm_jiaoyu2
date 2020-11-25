<?php
/*
 * @Discription:
 * @Author: Hannibal·Lee
 * @Date: 2019-08-16 18:48:00
 * @LastEditTime: 2020-05-06 16:16:35
 */

/**
 *
 */
function CreateModalExcel($data,$Excelname){
    $list = $data;
    $kkk = 100;
    ob_start();
    include '../addons/fm_jiaoyu/public/example/'.$Excelname;
    $wordpic = ob_get_contents();
    ob_end_clean(); //清除缓冲区的内容，并将缓冲区关闭，但不会输出内容

    $zifile_name = IA_ROOT.'/attachment/down/充值记录'.time().'.xls';
    file_put_contents($zifile_name,$wordpic);
    $fp=fopen($zifile_name,"r");
    $file_size=filesize($zifile_name);
    //下载文件需要用到的头
    $fileName = basename($zifile_name);
    /*header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length: " . filesize($zip_name));
    header("Content-Disposition: attachment; filename=\"" . basename($zip_name) . "\"");
    readfile($zip_name);  */
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
    if($file_count >= $file_size)
    {
        unlink($zifile_name);
    }
}



/**
 * excel数据导出
 *
 * @param [string] $title  模型名（如Member），用于导出生成文件名的前缀
 * @param [array] $cellName 表头及字段名
 * @param [array] $data 导出的表数据
 *
 * 特殊处理：合并单元格需要先对数据进行处理
 * @return void
 */
function ReExportExcel($title,$cellName,$data,$HasTitle = false){
	include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    //定义配置
    $topNumber = 1;//表头有几行占用
    $xlsTitle = iconv('utf-8', 'gb2312', $title);//文件大标题
    $fileName = $title.date('_YmdHis');//文件名称
    $cellKey = array(
        'A','B','C','D','E','F','G','H','I','J','K','L','M',
        'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
        'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
        'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
    );


if($HasTitle == true){
    $topNumber = 2;//表头有几行占用

    $objPHPExcel->getActiveSheet()->mergeCells('A1:'.$cellKey[count($cellName)-1].'1');//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$title);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
}


   //处理表头
   foreach ($cellName as $k=>$v)
   {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellKey[$k].$topNumber, $v[1]);//设置表头数据
    //    $objPHPExcel->getActiveSheet()->freezePane($cellKey[$k].($topNumber+1));//冻结窗口

    //    if($v[3] > 0)//大于0表示需要设置宽度
    //    {
    //        $objPHPExcel->getActiveSheet()->getColumnDimension($cellKey[$k])->setWidth($v[3]);//设置列宽度
    //    }
   }

     //处理数据
     foreach ($data as $k=>$v)
     {
         foreach ($cellName as $k1=>$v1)
         {
            $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1].($k+1+$topNumber), $v[$v1[0]]);

         }
     }
     ob_end_clean();
     ob_start();
         //导出excel
  
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition:attachment;filename={$fileName}.xlsx");//attachment新窗口打印inline本窗口打印
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;

}





/**
 * 创建(导出)Excel数据表格
 * @param  array   $list        要导出的数组格式的数据
 * @param  string  $filename    导出的Excel表格数据表的文件名
 * @param  array   $indexKey    $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
 * @param  array   $startRow    第一条数据在Excel表格中起始行
 * @param  [bool]  $excel2007   是否生成Excel2007(.xlsx)以上兼容的数据表
 * 比如: $indexKey与$list数组对应关系如下:
 *     $indexKey = array('id','username','sex','age');
 *     $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
 */
function exportExcel($filename,$indexKey,$list,$startRow=1,$excel2007=false){
    //文件引入
    require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
    require_once  IA_ROOT . '/framework/library/phpexcel/PHPExcel/Writer/Excel2007.php';

    if(empty($filename)) $filename = time();
    if( !is_array($indexKey)) return false;

    $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    //初始化PHPExcel()
    $objPHPExcel = new PHPExcel();

    //设置保存版本格式
    if($excel2007){
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $filename = $filename.'.xlsx';
    }else{
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $filename = $filename.'.xls';
    }

    //接下来就是写数据到表格里面去
    $objActSheet = $objPHPExcel->getActiveSheet();
    //$startRow = 1;
    foreach ($list as $row) {
        foreach ($indexKey as $key => $value){
            //这里是设置单元格的内容
            $objActSheet->setCellValue($header_arr[$key].$startRow,$row[$value]);
        }
        $startRow++;
    }
    // 下载这个表格，在浏览器输出
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");;
    header('Content-Disposition:attachment;filename='.$filename.'');
    header("Content-Transfer-Encoding:binary");
    $objWriter->save('php://output');
}



