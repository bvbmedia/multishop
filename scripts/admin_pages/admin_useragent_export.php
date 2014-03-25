<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_time_limit(0);
require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel.php');
$filter=array();
$from=array();
$having=array();
$match=array();
$orderby=array();
$where=array();
$orderby=array();
$select=array();
$select[]='o.billing_name, o.ip_address, o.user_agent';
$order_by='o.orders_id';
$order='desc';
$order_link='a';
$orderby[]=$order_by.' '.$order;
$pageset=mslib_fe::getOrdersPageSet($filter, $offset, 60000, $orderby, $having, $select, $where, $from);
$tmporders=$pageset['orders'];
// define the different columns
$fields=array();
$fields['billing_name']='customer name';
$fields['ip_address']='IP Address';
$fields['user_agent']='User agents';
$dir=$this->DOCUMENT_ROOT;
$filename=uniqid().".xls";
$export_file=$dir."uploads/tx_multishop/tmp/".$filename;
$worksheet_name='user-agent';
$phpexcel=new PHPExcel();
$phpexcel->getActiveSheet()->setTitle($worksheet_name);
$phpexcel->setActiveSheetIndex(0);
$header_style['font']=array('bold'=>true);
// write the header
$row_count=1;
$col_count=0;
$colwidth=array();
foreach ($fields as $key=>$val) {
	$colwidth[$col_count]=strlen($val)+1;
	$cell=$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col_count, $row_count, $val);
	$col_count++;
}
$row_count++;
foreach ($tmporders as $item) {
	$col_count=0;
	foreach ($item as $key=>$val) {
		if (strlen($val)+5>$colwidth[$col_count]) {
			$colwidth[$col_count]=strlen($val)+5;
		}
		//$val = iconv('ASCII', 'UTF-8//IGNORE', $val);
		$cell=$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col_count, $row_count, $val);
		$col_count++;
	}
	$row_count++;
}
// set bold to the header title
$last_col=$phpexcel->getActiveSheet()->getHighestColumn();
$phpexcel->getActiveSheet()->getStyle('A1:'.$last_col.'1')->applyFromArray($header_style);
$col_id='A';
foreach ($colwidth as $col_key=>$col_val) {
	$phpexcel->getActiveSheet()->getColumnDimension($col_id)->setWidth($col_val);
	$col_id++;
}
// redirect output to client browser
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$objWriter=PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
$objWriter->save('php://output');
exit();
?>