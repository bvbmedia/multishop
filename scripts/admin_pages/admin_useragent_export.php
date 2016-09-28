<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_time_limit(0);
$paths=array();
$paths[]=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/Service/PHPExcel.php';
$paths[]=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel.php';
foreach ($paths as $path) {
	if (file_exists($path)) {
		require_once($path);
		break;
	}
}
$data_query=array();
$data_query['filter']=array();
$data_query['from']=array();
$data_query['having']=array();
$data_query['match']=array();
$data_query['order_by']=array();
$data_query['where']=array();
$data_query['select']=array();
$data_query['select'][]='o.billing_name, o.ip_address, o.user_agent';
$order_by='o.orders_id';
$order='desc';
$order_link='a';
$data_query['order_by'][]=$order_by.' '.$order;
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_useragent_export.php']['excelStatsUseragentQueryHookPreProc'])) {
	$params=array(
		'data_query'=>&$data_query
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_useragent_export.php']['excelStatsUseragentQueryHookPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$pageset=mslib_fe::getOrdersPageSet($data_query['filter'], $offset, 60000, $data_query['order_by'], $data_query['having'], $data_query['select'], $data_query['where'], $data_query['from']);
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