<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_time_limit(0);
require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel.php');
// define the different columns
$fields=array();
$fields['uid']='customer id';
$fields['email']='e-mail';
$fields['username']='username';
$fields['company']='company name';
$fields['first_name']='first name';
$fields['middle_name']='middle name';
$fields['last_name']='last name';
$fields['address']='address';
$fields['street_name']='street name';
$fields['address_number']='address number';
$fields['address_ext']='address number extension';
$fields['zip']='zip';
$fields['city']='city';
$fields['country']='country';
//$fields['region']					='region';
$fields['telephone']='telephone';
$fields['fax']='fax';
$fields['mobile']='mobile';
//$fields['vat_id']					='VAT id';
$fields['tx_multishop_newsletter']='newsletter';
$fields['tx_multishop_discount']='discount';
$fields['tx_multishop_vat_id']='VAT ID';
$fields['tx_multishop_coc_id']='CoC ID';
//hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_export.php']['msCustomersExportFieldsHook'])) {
	$params=array(
		'fields'=>&$fields
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_export.php']['msCustomersExportFieldsHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
$field_keys=array_flip($fields);
$filter=array();
if (!$this->masterShop) {
	$filter[]='page_uid=\''.$this->shop_pid.'\'';
}
$limit='';
if (isset($this->get['tx_multishop_pi1']['limit']) and strstr($this->get['tx_multishop_pi1']['limit'], ',')) {
	$tmpArray=explode(',', $this->get['tx_multishop_pi1']['limit']);
	if (is_numeric($tmpArray[0]) and is_numeric($tmpArray[1])) {
		$limit=$tmpArray[0].','.$tmpArray[1];
	}
}
$filter[]='disable=0 and deleted=0';
$str=$GLOBALS['TYPO3_DB']->SELECTquery(implode(',', $field_keys), // SELECT ...
	'fe_users', // FROM ...
	implode(' AND ', $filter), // WHERE...
	'', // GROUP BY...
	'', // ORDER BY...
	$limit // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$rows=array();
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	$rows[]=$row;
}
$dir=$this->DOCUMENT_ROOT;
$filename=uniqid().".xls";
$export_file=$dir."uploads/tx_multishop/tmp/".$filename;
$worksheet_name='customers';
$phpexcel=new PHPExcel();
$phpexcel->getActiveSheet()->setTitle($worksheet_name);
$phpexcel->setActiveSheetIndex(0);
$header_style['font']=array('bold'=>true);
// write the header
$row_count=1;
$col_count=0;
$colwidth=array();
foreach ($rows[0] as $key=>$val) {
	$colwidth[$col_count]=strlen($fields[$key])+1;
	$cell=$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col_count, $row_count, $fields[$key]);
	$col_count++;
}
$row_count++;
foreach ($rows as $item) {
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