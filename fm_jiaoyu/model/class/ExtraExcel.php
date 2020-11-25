<?php



use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\RichText\RichText;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExtraExcel
{
    private $_HeadArr =  array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
    private $_WeekArr =  ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];

    public function DDExtraExcelWriter($params, $filename = '')
    {
        $schoolid = $params['schoolid'];
        $weid = $params['weid'];
        $YEAR = $params['year']; //当前年
        $MONTH = $params['month']; //当前月
        $NE = $MONTH + 1; //临时变量， 用于计算结束日期
        $startTime = strtotime($YEAR.'-'.$MONTH.'-01 00:00');
        $endTime = strtotime($YEAR.'-'.$NE.'-01 00:00') - 1 ;

        $dateCount = date("d",$endTime);


     

        // 考勤记录
        $checklist = pdo_fetchall("SELECT * FROM ".GetTableName('checklog')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and createtime >= '{$startTime}' and createtime <= '{$endTime}' and sid = 0 and tid != 0 ");
        //请假记录
        $leavelist = pdo_fetchall("SELECT * FROM ".GetTableName('leave')." WHERE schoolid = '{$schoolid}' and weid = '{$weid}' and isliuyan = 0 and endtime1 >= '{$startTime}' and startime1 <= '{$endTime}' and tid != 0 and status = 1  ");
        //教师列表
        $teacherlist = pdo_fetchall("SELECT t.tname,t.id , CASE t.fz_id WHEN 0 THEN '未分组' ELSE c.sname END as partname  FROM ".GetTableName('teachers')." as t LEFT JOIN ".GetTableName('classify')." as c ON c.sid = t.fz_id WHERE t.schoolid = '{$schoolid}' and t.weid = '{$weid}'   ORDER BY CONVERT(tname USING gbk) ASC  ", array(), 'id');


        //组装考勤数据
        foreach ($checklist as $vc) {
            $AP = date("Hi", $vc['createtime']) > 1200 ? 'pm' : 'am';
            $date = intval(date("d", $vc['createtime']));
            //common
            $spical = 0;
            if (strstr($vc['type'], '异常')) {
                $spical = 1;
            }
            
            $vc['type'] = $vc['leixing'] == 1 ? "进校": ($vc['leixing'] == 2 ? "离校": "异常");
            
            $temp = array(
                'word' => $vc['type'].":".date("H:i", $vc['createtime']),
                'type' => $vc['leixing'],
                'spical' => $spical
            );

            if (!empty($teacherlist[$vc['tid']])) {
                $teacherlist[$vc['tid']]['log'][$AP][$date]['type'] = 'check';
                $teacherlist[$vc['tid']]['log'][$AP][$date]['content'][] = $temp;
            }
            // spical
            if (strstr($vc['type'], '异常')) { //异常刷卡
                if(empty($teacherlist[$vc['tid']]['splog']['sipcal'][$vc['leixing']][$AP])){
                    $teacherlist[$vc['tid']]['splog']['sipcal'][$vc['leixing']][$AP] = 0;
                }
                $teacherlist[$vc['tid']]['splog']['sipcal'][$vc['leixing']][$AP]++;
            }
        }
        //组装请假数据
        foreach ($leavelist as $vl) {
            $startdate = strtotime(date("Y-m-d", $vl['startime1']));
            $enddate = strtotime(date("Y-m-d", $vl['endtime1'])) + 86399;
            for ($i = $startdate ; $i < $enddate;$i += 86400) {
                $month = date("m", $i);
                $date = intval(date("d", $i));
                if ($month == $MONTH) {
                    if ($startdate < $i) { //当前天之前开始，则当前天的请假开始时段为00
                        $st = '00:00';
                        $stnum = 0;
                    } else {
                        $st = date("H:i", $vl['startime1']);
                        $stnum = date("Hi", $vl['startime1']);
                    }

                    if ($enddate - 86399 > $i) {
                        $et = "23:59";
                        $etnum = 2359;
                    } else {
                        $et = date("H:i", $vl['endtime1']);
                        $etnum = date("Hi", $vl['endtime1']);
                    }
                    $temp = array(
                        'word' => "请假：{$vl['type']}  {$st} - {$et}\r\n理由：{$vl['conet']}",
                        'leave' => 1
                    );
                    if (!empty($teacherlist[$vl['tid']])) {
                        if ($etnum <= 1200) {
                            $teacherlist[$vl['tid']]['log']['am'][$date]['type'] = 'leave';
                            $teacherlist[$vl['tid']]['log']['am'][$date]['content'][] = $temp;
                        } elseif ($stnum > 1200) {
                            $teacherlist[$vl['tid']]['log']['pm'][$date]['type'] = 'leave';
                            $teacherlist[$vl['tid']]['log']['pm'][$date]['content'][] = $temp;
                        } else {
                            $teacherlist[$vl['tid']]['log']['am'][$date]['type'] = 'leave';
                            $teacherlist[$vl['tid']]['log']['am'][$date]['content'][] = $temp;
                            $teacherlist[$vl['tid']]['log']['pm'][$date]['type'] = 'leave';
                            $teacherlist[$vl['tid']]['log']['pm'][$date]['content'][] = $temp;
                        }
                    }
                }
            }
        }


        $sheet0Data = array(
            'start'=>$startTime,
            'end'=>$endTime,
            'data' => $teacherlist
        );

        ini_set("memory_limit", '1024M');
        set_time_limit(0);

        switch ($params['excelType']) {
            case 1: //常规记录
                $filename = $YEAR."年".$MONTH."月常规记录";
                $this->GetExcelUsualLog($sheet0Data, $filename);
                break;
            case 2: //异常记录
                $filename = $YEAR."年".$MONTH."月异常记录";
                $this->GetExcelUnusualLog($sheet0Data, $filename);
                break;
            case 3: //异常统计
                $filename = $YEAR."年".$MONTH."月异常统计";
                $this->GetExcelUnusualStatistics($sheet0Data, $filename);
                break;
            default:
                break;
        }

    }


    public function GetExcelKqNew($sheet0data = [],$filename = 'kq')
    {

        # 初始化一些东西
        ob_end_clean();
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        # 文件名
        $filename = $filename.'-'.date("YmdHis");
        $spreadsheet = new  Spreadsheet(); //初始化excel文件
        /*****************************************************************************************************************
        ***                                            sheet0内容控制 考勤记录                                            ***
        ******************************************************************************************************************/
        $spreadsheet->setActiveSheetIndex(0); //激活sheet0

        $ObjActiveSheet = $spreadsheet->getActiveSheet();

        $start = $sheet0data['start'];
        $end = $sheet0data['end'];
        $Month = date("m", $start);
        $Year = date("Y", $start);
        $dateCount = date("d", $end);
        $ObjActiveSheet->setTitle('考勤记录');

        $weekArr = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];

        /*********************** 合并单元格 ******************/
        $ObjActiveSheet->mergeCells('B1:'.$header_arr[$dateCount].'1');
        $ObjActiveSheet->mergeCells('A1:A2');
        $ObjActiveSheet->mergeCells('C2:E2');
        $ObjActiveSheet->mergeCells($header_arr[$dateCount-2].'2:'.$header_arr[$dateCount].'2');
        $ObjActiveSheet->mergeCells('F2:'.$header_arr[$dateCount-4].'2');
        /*********************** 设置样式 ******************/
        $ObjActiveSheet->getDefaultRowDimension()->setRowHeight(30); //设置默认行高
        $ObjActiveSheet->getRowDimension('1')->setRowHeight(30);
        $ObjActiveSheet->getColumnDimension('A')->setWidth(10); //设置指定列宽
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);        //默认字体大小
        $ObjActiveSheet->getStyle('B1')->getFont()->setBold(true);      //第一行是否加粗
        $ObjActiveSheet->getStyle('B1')->getFont()->setSize(20);         //第一行字体大小

        /*********************** 填充内容 ******************/
        $ObjActiveSheet->setCellValue('B1', '刷卡记录表');
        $ObjActiveSheet->setCellValue('B2', '考勤日期：');
        $ObjActiveSheet->setCellValue('C2', date("Y/m/d", $start)."-".date("m/d", $end));
        $ObjActiveSheet->setCellValue($header_arr[$dateCount-3].'2', "制表时间：");
        $ObjActiveSheet->setCellValue($header_arr[$dateCount-2].'2', date("Y/m/d"));

        $rowi = 5;
        // 填充考勤数据
        foreach ($sheet0data['data'] as $kv=>$vt) {
            $ObjActiveSheet->setCellValue('B'.$rowi, '姓名：');
            $ObjActiveSheet->setCellValue('C'.$rowi, $vt['tname']);
            $ObjActiveSheet->setCellValue('E'.$rowi, '工号：');
            $ObjActiveSheet->setCellValue('F'.$rowi, $vt['id']);
            $ObjActiveSheet->setCellValue('H'.$rowi, '部门：');
            $ObjActiveSheet->setCellValue('I'.$rowi, $vt['partname']);
            $ObjActiveSheet->getStyle('B'.$rowi.':K'.$rowi)->getFont()->setSize(15);         //第一行字体大小

            $spicallist[$kv] = array(
            'tname' => $vt['tname'],
            'id' => $vt['id'],
            'partname' => $vt['partname'],
            'spdata' => array(
                'spical'=>array(
                    '1' =>array( 'am' => 0, 'pm' => 0 ), //异常进
                    '2' =>array( 'am' => 0, 'pm' => 0 ), //异常出
                    '3' => array( 'am' => 0, 'pm' => 0 ) //异常 不知道进出
                ),
                'leave' => array( 'am' => 0, 'pm' => 0 ),//请假
                'absent' => array( 'am' => 0, 'pm' => 0 )//缺勤
            )
        );

        $ObjActiveSheet->getStyle("B".$rowi.":".$header_arr[$dateCount].$rowi)->applyFromArray(
            array(
                'fill' => array(
                    'fillType' => 'solid',
                    'color' => array('rgb' => '00cbfd')
                ),
            )
        );

        $rowitw = $rowi-2; //星期几
        $rowitn = $rowi-1; //几号
        $rowita = $rowi+1; //上午
        $rowitp = $rowi+2; //下午
        $ObjActiveSheet->getRowDimension($rowi.'')->setRowHeight(30);
        $ObjActiveSheet->getRowDimension($rowita)->setRowHeight(132);
        $ObjActiveSheet->getRowDimension($rowitp)->setRowHeight(132);

        $ObjActiveSheet->setCellValue('A'.$rowita, '上午');
        $ObjActiveSheet->setCellValue('A'.$rowitp, '下午');

        for ($i=1;$i<=$dateCount;$i++) { //填充星期和日期
           $ObjActiveSheet->getColumnDimension($header_arr[$i])->setWidth(17); //设置列宽
           $pidate = strtotime($Year.'-'.$Month.'-'.$i);
                $Week = date("w", $pidate);
                $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitw, $weekArr[$Week]);
                $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitn, $i);
                if ($Week == 0 || $Week == 6) { //如果是周末
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitw)->applyFromArray(
                        array(
                            'fill' => array(
                                'fillType' => 'solid',
                                'color' => array('rgb' => 'ff0004')
                            ),
                        )
                    );
                }

                $pidate = strtotime($Year.'-'.$Month.'-'.$i);
                $Week = date("w", $pidate);

                if (!empty($vt['log']['am'][$i])) { //如果当天上午有数据
                    $didata = $vt['log']['am'][$i]['content'];
                    $objRichText = new RichText();
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        $objPayable = $objRichText->createTextRun($vdd['word']."\r\n");
                        $objPayable->getFont()->setSize(10);
                        $objPayable->getFont()->setName('微软雅黑');
                        if ($vdd['spical'] == 1) {
                            $objPayable->getFont()->setColor(
                                new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
                            );
                            $spicallist[$kv]['spdata']['spical'][''.$vdd['type']]['am']++; //计算异常
                        }
                    }

                    $ObjActiveSheet->getCell($header_arr[$i].''.$rowita)->setValue($objRichText);//填充
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->getAlignment()->setWrapText(true); //允许换行

                    if ($vt['log']['am'][$i]['type'] == 'leave') { //如果当前天是请假
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => 'ffff00')
                                ),

                            )
                        );
                        $spicallist[$kv]['spdata']['leave']['am']++; //计算请假
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果是周末
                        $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowita, "缺勤");
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => '92d25b')
                                ),
                            )
                        );
                        $spicallist[$kv]['spdata']['absent']['am']++; //计算缺勤
                    }
                }

                if (!empty($vt['log']['pm'][$i])) { //如果当天下午有数据
                    $didata = $vt['log']['pm'][$i]['content'];
                    $objRichText = new RichText();
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        $objPayable = $objRichText->createTextRun($vdd['word']."\r\n");
                        $objPayable->getFont()->setSize(10);
                        $objPayable->getFont()->setName('微软雅黑');
                        if ($vdd['spical'] == 1) {
                            $objPayable->getFont()->setColor(
                                new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
                            );
                            $spicallist[$kv]['spdata']['spical'][''.$vdd['type']]['pm']++; //计算异常
                        }
                    }
                    $ObjActiveSheet->getCell($header_arr[$i].''.$rowitp)->setValue($objRichText);//填充
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->getAlignment()->setWrapText(true); //允许换行

                    if ($vt['log']['pm'][$i]['type'] == 'leave') { //如果当前天是请假
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => 'ffff00')
                                ),
                            )
                        );
                        $spicallist[$kv]['spdata']['leave']['pm']++; //计算请假
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果不是周末
                        $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitp, "缺勤");
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => '92d25b')
                                ),
                            )
                        );
                        $spicallist[$kv]['spdata']['absent']['pm']++; //计算缺勤
                    }
                }
            }
            $rowi += 5;
        }

        $EndLine = $rowi - 3;
        $ObjActiveSheet->getStyle('A1:'.$header_arr[$dateCount].''.$EndLine)->applyFromArray(   //有内容的单元格边框变粗
            array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => 'thin',//边框
                        'color' => array('rgb' => '000000')
                    )
                ),
                'alignment' => [
                    'horizontal' => 'center', //水平居中
                    'vertical' => 'center', //垂直居中
                ],
            )
        );

        /******************************************************************************************************************
        ***                                            sheet1内容控制 异常统计                                             ***
        *******************************************************************************************************************/
        $spreadsheet->createSheet(); //创建新的sheet
        $spreadsheet->setactivesheetindex(1); //激活sheet1

        $ObjActiveSheet = $spreadsheet->getActiveSheet();
        $ObjActiveSheet->freezePane('A5');

        //合并单元格
        $ObjActiveSheet->mergeCells('A1:N1');
        $ObjActiveSheet->mergeCells('B2:D2');
        $ObjActiveSheet->mergeCells('A3:A4');
        $ObjActiveSheet->mergeCells('B3:B4');
        $ObjActiveSheet->mergeCells('C3:C4');
        $ObjActiveSheet->mergeCells('D3:D4');
        $ObjActiveSheet->mergeCells('E3:F3');
        $ObjActiveSheet->mergeCells('G3:H3');
        $ObjActiveSheet->mergeCells('I3:J3');
        $ObjActiveSheet->mergeCells('K3:L3');
        $ObjActiveSheet->mergeCells('M3:N3');
        $ObjActiveSheet->mergeCells('E2:N2');

        //设置宽高
        $ObjActiveSheet->getRowDimension('1')->setRowHeight(30);
        $ObjActiveSheet->getRowDimension('2')->setRowHeight(25);
        $ObjActiveSheet->getRowDimension('3')->setRowHeight(25);
        $ObjActiveSheet->getRowDimension('4')->setRowHeight(25);
        $ObjActiveSheet->getColumnDimension('A')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('B')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('C')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('D')->setWidth(24);
        $ObjActiveSheet->getColumnDimension('E')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('F')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('G')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('H')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('I')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('J')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('K')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('L')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('M')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('N')->setWidth(15);
        
        $spreadsheet->getDefaultStyle()->getFont()->setSize(14);    //默认字体大小
        $ObjActiveSheet->getStyle('A1')->getFont()->setSize(24);    //第一行字体大小

        //填充固定内容
        $ObjActiveSheet->setCellValue('A1', '异常统计表');
        $ObjActiveSheet->setCellValue('A2', '考勤日期：');
        $ObjActiveSheet->setCellValue('A3', '工号');
        $ObjActiveSheet->setCellValue('B3', '姓名');
        $ObjActiveSheet->setCellValue('C3', '部门');
        $ObjActiveSheet->setCellValue('D3', '日期');
        $ObjActiveSheet->setCellValue('E3', '异常进校');
        $ObjActiveSheet->setCellValue('G3', '异常离校');
        $ObjActiveSheet->setCellValue('I3', '请假');
        $ObjActiveSheet->setCellValue('K3', '缺勤');
        $ObjActiveSheet->setCellValue('E4', '上午（次）');
        $ObjActiveSheet->setCellValue('F4', '下午（次）');
        $ObjActiveSheet->setCellValue('G4', '上午（次）');
        $ObjActiveSheet->setCellValue('H4', '下午（次）');
        $ObjActiveSheet->setCellValue('I4', '上午（次）');
        $ObjActiveSheet->setCellValue('J4', '下午（次）');
        $ObjActiveSheet->setCellValue('K4', '上午（次）');
        $ObjActiveSheet->setCellValue('L4', '下午（次）');
        $ObjActiveSheet->setCellValue('M3', '异常刷卡');
        $ObjActiveSheet->setCellValue('M4', '上午（次）');
        $ObjActiveSheet->setCellValue('N4', '下午（次）');

        //填充日期
        $ObjActiveSheet->setCellValue('B2', date("Y/m/d", $start)."-".date("m/d", $end)); //具体时间
        $ObjActiveSheet->setTitle('异常统计');

        $sci = 4;
        //填充具体数据
        foreach ($spicallist as $sk=>$sv) {
            $sci++;
            $ObjActiveSheet->getRowDimension($sci)->setRowHeight(25);
            $spdata = $sv['spdata'];
            $ObjActiveSheet->setCellValue('A'.$sci, $sv['id']);
            $ObjActiveSheet->setCellValue('B'.$sci, $sv['tname']);
            $ObjActiveSheet->setCellValue('C'.$sci, $sv['partname']);
            $ObjActiveSheet->setCellValue('D'.$sci, date("Y/m/d", $start)."-".date("m/d", $end));
            $ObjActiveSheet->setCellValue('E'.$sci, $spdata['spical']['1']['am']);
            $ObjActiveSheet->setCellValue('F'.$sci, $spdata['spical']['1']['pm']);
            $ObjActiveSheet->setCellValue('G'.$sci, $spdata['spical']['2']['am']);
            $ObjActiveSheet->setCellValue('H'.$sci, $spdata['spical']['2']['pm']);
            $ObjActiveSheet->setCellValue('I'.$sci, $spdata['leave']['am']);
            $ObjActiveSheet->setCellValue('J'.$sci, $spdata['leave']['pm']);
            $ObjActiveSheet->setCellValue('K'.$sci, $spdata['absent']['am']);
            $ObjActiveSheet->setCellValue('L'.$sci, $spdata['absent']['pm']);
            $ObjActiveSheet->setCellValue('M'.$sci, $spdata['spical']['3']['am']);
            $ObjActiveSheet->setCellValue('N'.$sci, $spdata['spical']['3']['pm']);
        }

        $ObjActiveSheet->getStyle('E3:H'.$sci)->applyFromArray( //两个异常对应的文字为红色
            array(
                'font' => array(
                    'color' => array('rgb' => 'ff0000'),// 红色
                ),
            )
        );
        $ObjActiveSheet->getStyle('I3:J'.$sci)->applyFromArray( //请假
            array(
                'fill' => array(
                    'fillType' => 'solid',
                    'color' => array('rgb' => 'ffff3d'),// 黄色
                ),
            )
        );
        $ObjActiveSheet->getStyle('K3:L'.$sci)->applyFromArray( //缺勤
            array(
                'fill' => array(
                    'fillType' => 'solid',
                    'color' => array('rgb' => '92d25b'),// 绿色
                ),
            )
        );
        $ObjActiveSheet->getStyle('M3:N'.$sci)->applyFromArray( //异常刷卡
            array(
                'fill' => array(
                    'fillType' => 'solid',
                    'color' => array('rgb' => 'ffc22c'),// 橙色
                ),
            )
        );

        $ObjActiveSheet->getStyle('A1:N'.$sci)->applyFromArray(   //有内容的单元格边框变粗，但会导致内存溢出（测试环境缺勤的人太多了）
            array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => 'thin',//边框
                        'color' => array('rgb' => '000000')
                    )
                )
            )
        );
        $ObjActiveSheet->getStyle('A4:N4')->applyFromArray(   //有内容的单元格边框变粗，但会导致内存溢出（测试环境缺勤的人太多了）
            array(
                'borders' => array(
                    'bottom' => array(
                        'borderStyle' => 'thick',//边框
                        'color' => array('rgb' => '000000')
                    )
                )
            )
        );
        //垂直居中
        $ObjActiveSheet->getStyle('A1:N'.$sci)->getAlignment()->setVertical('center');
        //水平居中
        $ObjActiveSheet->getStyle('A1:N'.$sci)->getAlignment()->setHorizontal('center');
        $spreadsheet->setactivesheetindex(0); //激活sheet1
        $objWriter = new Xlsx($spreadsheet);
        $filename = $filename.'.xlsx';

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename='.$filename.'');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        exit();
    }

    public function GetExcelUnusualStatistics($sheet0data = [], $filename = 'kq')
    {
        # 初始化一些东西
        ob_end_clean();
        $header_arr = $this->_HeadArr;

        # 文件名
        $filename = $filename.'-'.date("YmdHis");
        $spreadsheet = new  Spreadsheet(); //初始化excel文件
        $spreadsheet->setActiveSheetIndex(0); //激活sheet0

        $ObjActiveSheet = $spreadsheet->getActiveSheet();

        $start = $sheet0data['start'];
        $end = $sheet0data['end'];
        $Month = date("m", $start);
        $Year = date("Y", $start);
        $dateCount = date("d", $end);

        $weekArr = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
        

        $rowi = 5;
        // 填充考勤数据
        foreach ($sheet0data['data'] as $kv=>$vt) {
            $spicallist[$kv] = array(
                'tname' => $vt['tname'],
                'id' => $vt['id'],
                'partname' => $vt['partname'],
                'spdata' => array(
                    'spical'=>array(
                        '1' =>array( 'am' => 0, 'pm' => 0 ), //异常进
                        '2' =>array( 'am' => 0, 'pm' => 0 ), //异常出
                        '3' => array( 'am' => 0, 'pm' => 0 ) //异常 不知道进出
                    ),
                    'leave' => array( 'am' => 0, 'pm' => 0 ),//请假
                    'absent' => array( 'am' => 0, 'pm' => 0 )//缺勤
                )
            );

            for ($i=1;$i<=$dateCount;$i++) { //填充星期和日期
                $pidate = strtotime($Year.'-'.$Month.'-'.$i);
                $Week = date("w", $pidate);

                if (!empty($vt['log']['am'][$i])) { //如果当天上午有数据
                    $didata = $vt['log']['am'][$i]['content'];
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        if ($vdd['spical'] == 1) {
                            $spicallist[$kv]['spdata']['spical'][''.$vdd['type']]['am']++; //计算异常
                        }
                    }

                    if ($vt['log']['am'][$i]['type'] == 'leave') { //如果当前天是请假
                        $spicallist[$kv]['spdata']['leave']['am']++; //计算请假
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果是周末
                        $spicallist[$kv]['spdata']['absent']['am']++; //计算缺勤
                    }
                }

                if (!empty($vt['log']['pm'][$i])) { //如果当天下午有数据
                    $didata = $vt['log']['pm'][$i]['content'];
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        if ($vdd['spical'] == 1) {
                            $spicallist[$kv]['spdata']['spical'][''.$vdd['type']]['pm']++; //计算异常
                        }
                    }
                    if ($vt['log']['pm'][$i]['type'] == 'leave') { //如果当前天是请假
                        $spicallist[$kv]['spdata']['leave']['pm']++; //计算请假
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果不是周末
                        $spicallist[$kv]['spdata']['absent']['pm']++; //计算缺勤
                    }
                }
            }
        }

        $ObjActiveSheet->freezePane('A5');
        //合并单元格
        $ObjActiveSheet->mergeCells('A1:N1');
        $ObjActiveSheet->mergeCells('B2:D2');
        $ObjActiveSheet->mergeCells('A3:A4');
        $ObjActiveSheet->mergeCells('B3:B4');
        $ObjActiveSheet->mergeCells('C3:C4');
        $ObjActiveSheet->mergeCells('D3:D4');
        $ObjActiveSheet->mergeCells('E3:F3');
        $ObjActiveSheet->mergeCells('G3:H3');
        $ObjActiveSheet->mergeCells('I3:J3');
        $ObjActiveSheet->mergeCells('K3:L3');
        $ObjActiveSheet->mergeCells('M3:N3');
        $ObjActiveSheet->mergeCells('E2:N2');

        //设置宽高
        $ObjActiveSheet->getRowDimension('1')->setRowHeight(30);
        $ObjActiveSheet->getRowDimension('2')->setRowHeight(25);
        $ObjActiveSheet->getRowDimension('3')->setRowHeight(25);
        $ObjActiveSheet->getRowDimension('4')->setRowHeight(25);
        $ObjActiveSheet->getColumnDimension('A')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('B')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('C')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('D')->setWidth(24);
        $ObjActiveSheet->getColumnDimension('E')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('F')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('G')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('H')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('I')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('J')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('K')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('L')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('M')->setWidth(15);
        $ObjActiveSheet->getColumnDimension('N')->setWidth(15);
        
        $spreadsheet->getDefaultStyle()->getFont()->setSize(14);    //默认字体大小
        $ObjActiveSheet->getStyle('A1')->getFont()->setSize(24);    //第一行字体大小

        //填充固定内容
        $ObjActiveSheet->setCellValue('A1', '异常统计表');
        $ObjActiveSheet->setCellValue('A2', '考勤日期：');
        $ObjActiveSheet->setCellValue('A3', '工号');
        $ObjActiveSheet->setCellValue('B3', '姓名');
        $ObjActiveSheet->setCellValue('C3', '部门');
        $ObjActiveSheet->setCellValue('D3', '日期');
        $ObjActiveSheet->setCellValue('E3', '异常进校');
        $ObjActiveSheet->setCellValue('G3', '异常离校');
        $ObjActiveSheet->setCellValue('I3', '请假');
        $ObjActiveSheet->setCellValue('K3', '缺勤');
        $ObjActiveSheet->setCellValue('E4', '上午（次）');
        $ObjActiveSheet->setCellValue('F4', '下午（次）');
        $ObjActiveSheet->setCellValue('G4', '上午（次）');
        $ObjActiveSheet->setCellValue('H4', '下午（次）');
        $ObjActiveSheet->setCellValue('I4', '上午（次）');
        $ObjActiveSheet->setCellValue('J4', '下午（次）');
        $ObjActiveSheet->setCellValue('K4', '上午（次）');
        $ObjActiveSheet->setCellValue('L4', '下午（次）');
        $ObjActiveSheet->setCellValue('M3', '异常刷卡');
        $ObjActiveSheet->setCellValue('M4', '上午（次）');
        $ObjActiveSheet->setCellValue('N4', '下午（次）');

        //填充日期
        $ObjActiveSheet->setCellValue('B2', date("Y/m/d", $start)."-".date("m/d", $end)); //具体时间
        $ObjActiveSheet->setTitle('异常统计');

        $sci = 4;
        //填充具体数据
        foreach ($spicallist as $sk=>$sv) {
            $sci++;
            $ObjActiveSheet->getRowDimension($sci)->setRowHeight(25);
            $spdata = $sv['spdata'];
            $ObjActiveSheet->setCellValue('A'.$sci, $sv['id']);
            $ObjActiveSheet->setCellValue('B'.$sci, $sv['tname']);
            $ObjActiveSheet->setCellValue('C'.$sci, $sv['partname']);
            $ObjActiveSheet->setCellValue('D'.$sci, date("Y/m/d", $start)."-".date("m/d", $end));
            $ObjActiveSheet->setCellValue('E'.$sci, $spdata['spical']['1']['am']);
            $ObjActiveSheet->setCellValue('F'.$sci, $spdata['spical']['1']['pm']);
            $ObjActiveSheet->setCellValue('G'.$sci, $spdata['spical']['2']['am']);
            $ObjActiveSheet->setCellValue('H'.$sci, $spdata['spical']['2']['pm']);
            $ObjActiveSheet->setCellValue('I'.$sci, $spdata['leave']['am']);
            $ObjActiveSheet->setCellValue('J'.$sci, $spdata['leave']['pm']);
            $ObjActiveSheet->setCellValue('K'.$sci, $spdata['absent']['am']);
            $ObjActiveSheet->setCellValue('L'.$sci, $spdata['absent']['pm']);
            $ObjActiveSheet->setCellValue('M'.$sci, $spdata['spical']['3']['am']);
            $ObjActiveSheet->setCellValue('N'.$sci, $spdata['spical']['3']['pm']);
        }

        $ObjActiveSheet->getStyle('E3:H'.$sci)->applyFromArray( //两个异常对应的文字为红色
            array(
                'font' => array(
                    'color' => array('rgb' => 'ff0000'),// 红色
                ),
            )
        );
        $ObjActiveSheet->getStyle('I3:J'.$sci)->applyFromArray( //请假
            array(
                'fill' => array(
                    'fillType' => 'solid',
                    'color' => array('rgb' => 'ffff3d'),// 黄色
                ),
            )
        );
        $ObjActiveSheet->getStyle('K3:L'.$sci)->applyFromArray( //缺勤
            array(
                'fill' => array(
                    'fillType' => 'solid',
                    'color' => array('rgb' => '92d25b'),// 绿色
                ),
            )
        );
        $ObjActiveSheet->getStyle('M3:N'.$sci)->applyFromArray( //异常刷卡
            array(
                'fill' => array(
                    'fillType' => 'solid',
                    'color' => array('rgb' => 'ffc22c'),// 橙色
                ),
            )
        );

        $ObjActiveSheet->getStyle('A1:N'.$sci)->applyFromArray(   //有内容的单元格边框变粗，但会导致内存溢出（测试环境缺勤的人太多了）
            array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => 'thin',//边框
                        'color' => array('rgb' => '000000')
                    )
                )
            )
        );
        $ObjActiveSheet->getStyle('A4:N4')->applyFromArray(   
            array(
                'borders' => array(
                    'bottom' => array(
                        'borderStyle' => 'thick',//边框
                        'color' => array('rgb' => '000000')
                    )
                )
            )
        );
        //垂直居中
        $ObjActiveSheet->getStyle('A1:N'.$sci)->getAlignment()->setVertical('center');
        //水平居中
        $ObjActiveSheet->getStyle('A1:N'.$sci)->getAlignment()->setHorizontal('center');
        $objWriter = new Xlsx($spreadsheet);
        $filename = $filename.'.xlsx';

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename='.$filename.'');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        exit();
    }


    /**
     * 导出异常详细记录
     *
     * @param array $sheet0data
     * @param string $filename
     *
     * @return void
     */
    public function GetExcelUnusualLog($sheet0data = [], $filename = 'UnusualDetailLog')
    {
        # 初始化一些东西
        ob_end_clean();
        $header_arr = $this->_HeadArr;

        # 文件名
        $filename = $filename.'-'.date("YmdHis");
        $spreadsheet = new  Spreadsheet(); //初始化excel文件
        $spreadsheet->setActiveSheetIndex(0); //激活sheet0
        $ObjActiveSheet = $spreadsheet->getActiveSheet();

        $start = $sheet0data['start'];
        $end = $sheet0data['end'];
        $Month = date("m", $start);
        $Year = date("Y", $start);
        $dateCount = date("d", $end);
        $ObjActiveSheet->setTitle('异常记录');

        $weekArr = $this->_WeekArr;

        /*********************** 合并单元格 ******************/
        $ObjActiveSheet->mergeCells('B1:'.$header_arr[$dateCount].'1');
        $ObjActiveSheet->mergeCells('A1:A2');
        $ObjActiveSheet->mergeCells('C2:E2');
        $ObjActiveSheet->mergeCells($header_arr[$dateCount-2].'2:'.$header_arr[$dateCount].'2');
        $ObjActiveSheet->mergeCells('F2:'.$header_arr[$dateCount-4].'2');
        /*********************** 设置样式 ******************/
        $ObjActiveSheet->getDefaultRowDimension()->setRowHeight(30); //设置默认行高
        $ObjActiveSheet->getRowDimension('1')->setRowHeight(30);
        $ObjActiveSheet->getColumnDimension('A')->setWidth(10); //设置指定列宽
        $spreadsheet->getDefaultStyle()->getFont()->setSize(8);        //默认字体大小
        $ObjActiveSheet->getStyle('B1')->getFont()->setBold(true);      //第一行是否加粗
        $ObjActiveSheet->getStyle('B1')->getFont()->setSize(20);         //第一行字体大小
        /*********************** 填充内容 ******************/
        $ObjActiveSheet->setCellValue('B1', '异常记录表');
        $ObjActiveSheet->setCellValue('B2', '考勤日期：');
        $ObjActiveSheet->setCellValue('C2', date("Y/m/d", $start)."-".date("m/d", $end));
        $ObjActiveSheet->setCellValue($header_arr[$dateCount-3].'2', "制表时间：");
        $ObjActiveSheet->setCellValue($header_arr[$dateCount-2].'2', date("Y/m/d"));

        $rowi = 5;
        // 填充考勤数据
        foreach ($sheet0data['data'] as $kv=>$vt) {
            $ObjActiveSheet->setCellValue('B'.$rowi, '姓名：');
            $ObjActiveSheet->mergeCells('C'.$rowi.':D'.$rowi);
            $ObjActiveSheet->setCellValue('C'.$rowi, $vt['tname']);
            $ObjActiveSheet->setCellValue('F'.$rowi, '工号：');
            $ObjActiveSheet->mergeCells('G'.$rowi.':H'.$rowi);
            $ObjActiveSheet->setCellValue('G'.$rowi, $vt['id']);
            $ObjActiveSheet->setCellValue('J'.$rowi, '部门：');
            $ObjActiveSheet->mergeCells('K'.$rowi.':L'.$rowi);

            $ObjActiveSheet->setCellValue('K'.$rowi, $vt['partname']);
            $ObjActiveSheet->getStyle('B'.$rowi.':K'.$rowi)->getFont()->setSize(15);         //第一行字体大小

   

            $ObjActiveSheet->getStyle("B".$rowi.":".$header_arr[$dateCount].$rowi)->applyFromArray(
                array(
                    'fill' => array(
                        'fillType' => 'solid',
                        'color' => array('rgb' => '00cbfd')
                    ),
                )
            );

            $rowitw = $rowi-2; //星期几
            $rowitn = $rowi-1; //几号
            $rowita = $rowi+1; //上午
            $rowitp = $rowi+2; //下午
            $ObjActiveSheet->getRowDimension($rowi.'')->setRowHeight(30);
            $ObjActiveSheet->getRowDimension($rowita)->setRowHeight(132);
            $ObjActiveSheet->getRowDimension($rowitp)->setRowHeight(132);

            $ObjActiveSheet->setCellValue('A'.$rowita, '上午');
            $ObjActiveSheet->setCellValue('A'.$rowitp, '下午');

            for ($i=1;$i<=$dateCount;$i++) { //填充星期和日期
                $ObjActiveSheet->getColumnDimension($header_arr[$i])->setWidth(17); //设置列宽
                $pidate = strtotime($Year.'-'.$Month.'-'.$i);
                $Week = date("w", $pidate);
                $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitw, $weekArr[$Week]);
                $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitn, $i);
                if ($Week == 0 || $Week == 6) { //如果是周末
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitw)->applyFromArray(
                        array(
                            'fill' => array(
                                'fillType' => 'solid',
                                'color' => array('rgb' => 'ff0004')
                            ),
                        )
                    );
                }

                if (!empty($vt['log']['am'][$i])) { //如果当天上午有数据
                    $didata = $vt['log']['am'][$i]['content'];
                    $objRichText = new RichText();
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        if ($vdd['spical'] == 1 || $vdd['leave'] == 1) {
                            $objPayable = $objRichText->createTextRun($vdd['word']."\r\n");
                            $objPayable->getFont()->setSize(10);
                            $objPayable->getFont()->setName('微软雅黑');
                            if ($vdd['spical'] == 1){
                                $objPayable->getFont()->setColor(
                                    new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
                                );
                            }
                            
                        }
                    }

                    $ObjActiveSheet->getCell($header_arr[$i].''.$rowita)->setValue($objRichText);//填充
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->getAlignment()->setWrapText(true); //允许换行

                    if ($vt['log']['am'][$i]['type'] == 'leave') { //如果当前天是请假
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => 'ffff00')
                                ),

                            )
                        );
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果是周末
                        $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowita, "缺勤");
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => '92d25b')
                                ),
                            )
                        );
                    }
                }

                if (!empty($vt['log']['pm'][$i])) { //如果当天下午有数据
                    $didata = $vt['log']['pm'][$i]['content'];
                    $objRichText = new RichText();
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        if ($vdd['spical'] == 1 || $vdd['leave'] == 1) {
                            $objPayable = $objRichText->createTextRun($vdd['word']."\r\n");
                            $objPayable->getFont()->setSize(10);
                            $objPayable->getFont()->setName('微软雅黑');
                            if ($vdd['spical'] == 1) {
                                $objPayable->getFont()->setColor(
                                    new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
                                );
                            }
                        }
                    }
                    $ObjActiveSheet->getCell($header_arr[$i].''.$rowitp)->setValue($objRichText);//填充
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->getAlignment()->setWrapText(true); //允许换行

                    if ($vt['log']['pm'][$i]['type'] == 'leave') { //如果当前天是请假
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => 'ffff00')
                                ),
                            )
                        );
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果不是周末
                        $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitp, "缺勤");
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => '92d25b')
                                ),
                            )
                        );
                    }
                }
            }
            $rowi += 5;
        }

        $EndLine = $rowi - 3;
        $ObjActiveSheet->getStyle('A1:'.$header_arr[$dateCount].''.$EndLine)->applyFromArray(   //有内容的单元格边框变粗
            array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => 'thin',//边框
                        'color' => array('rgb' => '000000')
                    )
                ),
                'alignment' => [
                    'horizontal' => 'center', //水平居中
                    'vertical' => 'center', //垂直居中
                ],
            )
        );
        $objWriter = new Xlsx($spreadsheet);
        $filename = $filename.'.xlsx';

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename='.$filename.'');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        exit();
    }


    /**
     * 导出详细考勤记录
     *
     * @param array $sheet0data
     * @param string $filename
     *
     * @return void
     */
    public function GetExcelUsualLog($sheet0data = [], $filename = 'detailLog')
    {
        # 初始化一些东西
        ob_end_clean();
        $header_arr = $this->_HeadArr;
        # 文件名
        $filename = $filename.'-'.date("YmdHis");
        $spreadsheet = new  Spreadsheet(); //初始化excel文件
        $spreadsheet->setActiveSheetIndex(0); //激活sheet0

        $ObjActiveSheet = $spreadsheet->getActiveSheet();
        $start = $sheet0data['start'];
        $end = $sheet0data['end'];
        $Month = date("m", $start);
        $Year = date("Y", $start);
        $dateCount = date("d", $end);
        $ObjActiveSheet->setTitle('考勤记录');

        $weekArr = $this->_WeekArr;
        /*********************** 合并单元格 ******************/
        $ObjActiveSheet->mergeCells('B1:'.$header_arr[$dateCount].'1');
        $ObjActiveSheet->mergeCells('A1:A2');
        $ObjActiveSheet->mergeCells('C2:E2');
        $ObjActiveSheet->mergeCells($header_arr[$dateCount-2].'2:'.$header_arr[$dateCount].'2');
        $ObjActiveSheet->mergeCells('F2:'.$header_arr[$dateCount-4].'2');
        /*********************** 设置样式 ******************/
        $ObjActiveSheet->getDefaultRowDimension()->setRowHeight(30); //设置默认行高
        $ObjActiveSheet->getRowDimension('1')->setRowHeight(30);
        $ObjActiveSheet->getColumnDimension('A')->setWidth(10); //设置指定列宽
        $spreadsheet->getDefaultStyle()->getFont()->setSize(8);        //默认字体大小
        $ObjActiveSheet->getStyle('B1')->getFont()->setBold(true);      //第一行是否加粗
        $ObjActiveSheet->getStyle('B1')->getFont()->setSize(20);         //第一行字体大小
        /*********************** 填充内容 ******************/
        $ObjActiveSheet->setCellValue('B1', '刷卡记录表');
        $ObjActiveSheet->setCellValue('B2', '考勤日期：');
        $ObjActiveSheet->setCellValue('C2', date("Y/m/d", $start)."-".date("m/d", $end));
        $ObjActiveSheet->setCellValue($header_arr[$dateCount-3].'2', "制表时间：");
        $ObjActiveSheet->setCellValue($header_arr[$dateCount-2].'2', date("Y/m/d"));

        $rowi = 5;
        // 填充考勤数据
        foreach ($sheet0data['data'] as $kv=>$vt) {
            $ObjActiveSheet->setCellValue('B'.$rowi, '姓名：');
            $ObjActiveSheet->mergeCells('C'.$rowi.':D'.$rowi);
            $ObjActiveSheet->setCellValue('C'.$rowi, $vt['tname']);
            $ObjActiveSheet->setCellValue('F'.$rowi, '工号：');
            $ObjActiveSheet->mergeCells('G'.$rowi.':H'.$rowi);
            $ObjActiveSheet->setCellValue('G'.$rowi, $vt['id']);
            $ObjActiveSheet->setCellValue('J'.$rowi, '部门：');
            $ObjActiveSheet->mergeCells('K'.$rowi.':L'.$rowi);

            $ObjActiveSheet->setCellValue('K'.$rowi, $vt['partname']);
            $ObjActiveSheet->getStyle('B'.$rowi.':K'.$rowi)->getFont()->setSize(15);         //第一行字体大小

            $ObjActiveSheet->getStyle("B".$rowi.":".$header_arr[$dateCount].$rowi)->applyFromArray(
                array(
                    'fill' => array(
                        'fillType' => 'solid',
                        'color' => array('rgb' => '00cbfd')
                    ),
                )
            );

            $rowitw = $rowi-2; //星期几
            $rowitn = $rowi-1; //几号
            $rowita = $rowi+1; //上午
            $rowitp = $rowi+2; //下午
            $ObjActiveSheet->getRowDimension($rowi.'')->setRowHeight(30);
            $ObjActiveSheet->getRowDimension($rowita)->setRowHeight(132);
            $ObjActiveSheet->getRowDimension($rowitp)->setRowHeight(132);

            $ObjActiveSheet->setCellValue('A'.$rowita, '上午');
            $ObjActiveSheet->setCellValue('A'.$rowitp, '下午');

            for ($i=1;$i<=$dateCount;$i++) { //填充星期和日期
           $ObjActiveSheet->getColumnDimension($header_arr[$i])->setWidth(17); //设置列宽
           $pidate = strtotime($Year.'-'.$Month.'-'.$i);
                $Week = date("w", $pidate);
                $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitw, $weekArr[$Week]);
                $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitn, $i);
                if ($Week == 0 || $Week == 6) { //如果是周末
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitw)->applyFromArray(
                        array(
                            'fill' => array(
                                'fillType' => 'solid',
                                'color' => array('rgb' => 'ff0004')
                            ),
                        )
                    );
                }

                $pidate = strtotime($Year.'-'.$Month.'-'.$i);
                $Week = date("w", $pidate);

                if (!empty($vt['log']['am'][$i])) { //如果当天上午有数据
                    $didata = $vt['log']['am'][$i]['content'];
                    $objRichText = new RichText();
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        $objPayable = $objRichText->createTextRun($vdd['word']."\r\n");
                        $objPayable->getFont()->setSize(10);
                        $objPayable->getFont()->setName('微软雅黑');
                        if ($vdd['spical'] == 1) {
                            $objPayable->getFont()->setColor(
                                new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
                            );
                        }
                    }
                    $ObjActiveSheet->getCell($header_arr[$i].''.$rowita)->setValue($objRichText);//填充
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->getAlignment()->setWrapText(true); //允许换行
                    if ($vt['log']['am'][$i]['type'] == 'leave') { //如果当前天是请假
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => 'ffff00')
                                ),

                            )
                        );
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果是周末
                        $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowita, "缺勤");
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowita)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => '92d25b')
                                ),
                            )
                        );
                    }
                }

                if (!empty($vt['log']['pm'][$i])) { //如果当天下午有数据
                    $didata = $vt['log']['pm'][$i]['content'];
                    $objRichText = new RichText();
                    foreach ($didata as $vdd) { //构造单元格内的文字
                        $objPayable = $objRichText->createTextRun($vdd['word']."\r\n");
                        $objPayable->getFont()->setSize(10);
                        $objPayable->getFont()->setName('微软雅黑');
                        if ($vdd['spical'] == 1) {
                            $objPayable->getFont()->setColor(
                                new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
                            );
                        }
                    }
                    $ObjActiveSheet->getCell($header_arr[$i].''.$rowitp)->setValue($objRichText);//填充
                    $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->getAlignment()->setWrapText(true); //允许换行

                    if ($vt['log']['pm'][$i]['type'] == 'leave') { //如果当前天是请假
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => 'ffff00')
                                ),
                            )
                        );
                    }
                } else { //否则
                    if ($Week != 0 && $Week != 6) { //如果不是周末
                        $ObjActiveSheet->setCellValue($header_arr[$i].''.$rowitp, "缺勤");
                        $ObjActiveSheet->getStyle($header_arr[$i].''.$rowitp)->applyFromArray(
                            array(
                                'fill' => array(
                                    'fillType' => 'solid',
                                    'color' => array('rgb' => '92d25b')
                                ),
                            )
                        );
                    }
                }
            }
            $rowi += 5;
        }

        $EndLine = $rowi - 3;
        $ObjActiveSheet->getStyle('A1:'.$header_arr[$dateCount].''.$EndLine)->applyFromArray(   //有内容的单元格边框变粗
            array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => 'thin',//边框
                        'color' => array('rgb' => '000000')
                    )
                ),
                'alignment' => [
                    'horizontal' => 'center', //水平居中
                    'vertical' => 'center', //垂直居中
                ],
            )
        );
        $objWriter = new Xlsx($spreadsheet);
        $filename = $filename.'.xlsx';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename='.$filename.'');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        exit();
    }
}
