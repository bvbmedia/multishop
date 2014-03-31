<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$sql_year="select crdate from tx_multishop_orders order by orders_id asc limit 1";
$qry_year=$GLOBALS['TYPO3_DB']->sql_query($sql_year);
$row_year=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);
if ($row_year['crdate']>0) {
	$oldest_year=date("Y", $row_year['crdate']);
} else {
	$oldest_year=date("Y");
}
$current_year=date("Y");
$selected_year='Y-';
$select_year=date('Y');
if ($this->get['stats_year_sb']>0) {
	$selected_year=$this->get['stats_year_sb']."-";
	$select_year=$this->get['stats_year_sb'];
}
$filename='orders_stats_'.time().'.xls';
$dir=$this->DOCUMENT_ROOT;
$export_file=$dir."uploads/tx_multishop/tmp/".$filename;
$colwidth=array();
$colwidth[0]=20;
$colwidth[1]=10;
$colwidth[2]=30;
$colwidth[3]=27;
$colwidth[4]=12;
$colwidth[5]=25;
$colwidth[6]=25;
$colwidth[7]=20;
$colwidth[8]=20;
$colwidth[9]=20;
// Creating a workbook
$phpexcel=new PHPExcel();
$phpexcel->getActiveSheet()->setTitle('orders statistics');
$phpexcel->setActiveSheetIndex(0);
$header_style['font']=array('bold'=>true);
/* $format =& $workbook->addFormat(array('Align' => 'center', 'Bold' => '1'));
$format_header =& $workbook->addFormat(array('Align' => 'left', 'Bold' => '1', 'Size' => '12'));

$valformat =& $workbook->addFormat(array('TextWrap' => 1));
$format_value_left_align =& $workbook->addFormat(array('Align' => 'left')); */
// header
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $this->pi_getLL('sales_volume_by_month'));
$phpexcel->getActiveSheet()->getStyle('A1')->applyFromArray($header_style);
$phpexcel->getActiveSheet()->mergeCells('A1:N1');
for ($i=1; $i<13; $i++) {
	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date($selected_year."m", $time);
}
$col=0;
$col_char='A';
foreach ($dates as $key=>$value) {
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 2, ucfirst($key));
	$phpexcel->getActiveSheet()->getColumnDimension($col_char)->setWidth(16);
	$col_char++;
	$col++;
}
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 2, $this->pi_getLL('total'));
$phpexcel->getActiveSheet()->getColumnDimension($col_char)->setWidth(16);
$col_char++;
$col++;
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 2, $this->pi_getLL('cumulative'));
$phpexcel->getActiveSheet()->getColumnDimension($col_char)->setWidth(29);
// reset the col
$col=0;
$col_char='A';
$total_amount=0;
foreach ($dates as $key=>$value) {
	$total_price=0;
	$start_time=strtotime($value."-01 00:00:00");
	//$end_time=strtotime($value."-31 23:59:59");
	$end_time=strtotime($value."-01 00:00:00 +1 MONTH");
	$where=array();
	if ($this->get['paid_orders_only']) {
		$where[]='(o.paid=1)';
	} else {
		$where[]='(o.paid=1 or o.paid=0)';
	}
	$where[]='(o.deleted=0)';
	$str="SELECT o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$total_price=($total_price+$row['grand_total']);
	}
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 3, number_format($total_price, 2, ',', '.'));
	$total_amount=$total_amount+$total_price;
	$col_char++;
	$col++;
}
if ($this->get['stats_year_sb']==date("Y") || !$this->get['stats_year_sb']) {
	$month=date("m");
	$currentDay=date("d");
	$dayOfTheYear=date("z");
	$currentYear=1;
	if ($month==1) {
		$currentMonth=1;
	}
} else {
	$month=12;
	$dayOfTheYear=365;
	$currentDay=31;
	$currentYear=0;
	$currentMonth=0;
}
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 3, number_format($total_amount, 2, ',', '.'));
$col_char++;
$col++;
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 3, number_format(($total_amount/$dayOfTheYear)*365, 2, ',', '.'));
//$worksheet->setMerge(3, 0, 3, 13);
// header
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(0, 4, strtoupper($this->pi_getLL('sales_volume_by_day')));
$phpexcel->getActiveSheet()->getStyle('A4')->applyFromArray($header_style);
$phpexcel->getActiveSheet()->mergeCells('A4:N4');
if ($currentMonth) {
	$endDay=date("d");
} else {
	$endDay=31;
}
$dates=array();
for ($i=0; $i<$endDay; $i++) {
	$time=strtotime("-".$i." day", strtotime(date($currentDay.'-'.$month.'-'.$select_year)));
	$dates[strftime("%x", $time)]=$time;
}
$col=0;
$col_char='A';
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 5, strtoupper($this->pi_getLL('day')));
$phpexcel->getActiveSheet()->getColumnDimension($col_char)->setWidth(16);
$col_char++;
$col++;
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 5, strtoupper($this->pi_getLL('amount')));
$phpexcel->getActiveSheet()->getColumnDimension($col_char)->setWidth(16);
$col_char++;
$col++;
$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, 5, strtoupper($this->pi_getLL('orders_id')));
$phpexcel->getActiveSheet()->mergeCells('C5:N5');
$row=6;
$col=0;
foreach ($dates as $key=>$value) {
	$total_price=0;
	$system_date=date($selected_year."m-d", $value);
	$start_time=strtotime($system_date." 00:00:00");
	$end_time=strtotime($system_date." 23:59:59");
	$where=array();
	if ($this->get['paid_orders_only']) {
		$where[]='(o.paid=1)';
	} else {
		$where[]='(o.paid=1 or o.paid=0)';
	}
	$where[]='(o.deleted=0)';
	$str="SELECT o.customer_id, o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$uids=array();
	$users=array();
	while ($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
		$total_price=$total_price+$rs['grand_total'];
		$uids[]=$rs['orders_id'];
	}
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, number_format($total_price, 2, ',', '.'));
	if (count($uids)) {
		$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, implode(", ", $uids).',');
		$phpexcel->getActiveSheet()->mergeCells('C'.$row.':N'.$row);
	} else {
		$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, '');
		$phpexcel->getActiveSheet()->mergeCells('C'.$row.':N'.$row);
	}
	$row++;
}
// Let's send the file
// redirect output to client browser
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$objWriter=PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>