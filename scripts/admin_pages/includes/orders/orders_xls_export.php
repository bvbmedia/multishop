<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel.php');
$option_search=array(
	"orders_id"=>$this->pi_getLL('admin_order_id'),
	"invoice"=>$this->pi_getLL('admin_invoice_number'),
	"customer_id"=>$this->pi_getLL('admin_customer_id'),
	"billing_email"=>$this->pi_getLL('admin_customer_email'),
	"delivery_name"=>$this->pi_getLL('admin_customer_name'),
	//"crdate" =>				$this->pi_getLL('admin_order_date'),
	"billing_zip"=>$this->pi_getLL('admin_zip'),
	"billing_city"=>$this->pi_getLL('admin_city'),
	"billing_address"=>$this->pi_getLL('admin_address'),
	"billing_company"=>$this->pi_getLL('admin_company'),
	"shipping_method"=>$this->pi_getLL('admin_shipping_method'),
	"payment_method"=>$this->pi_getLL('admin_payment_method')
);
asort($option_search);
$filter=array();
$from=array();
$having=array();
$match=array();
$orderby=array();
$where=array();
$orderby=array();
$select=array();
if ($this->post['skeyword']) {
	switch ($this->post['type_search']) {
		case 'all':
			$option_fields=$option_search;
			unset($option_fields['all']);
			unset($option_fields['invoice']);
			unset($option_fields['crdate']);
			unset($option_fields['delivery_name']);
			//print_r($option_fields);
			$items=array();
			foreach ($option_fields as $fields=>$label) {
				$items[]=$fields." LIKE '%".addslashes($this->post['skeyword'])."%'";
			}
			$items[]="delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
			$filter[]=implode(" or ", $items);
			break;
		case 'orders_id':
			$filter[]=" orders_id='".addslashes($this->post['skeyword'])."'";
			break;
		case 'invoice':
			$filter[]=" invoice_id LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_email':
			$filter[]=" billing_email LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'delivery_name':
			$filter[]=" delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_zip':
			$filter[]=" billing_zip LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_city':
			$filter[]=" billing_city LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_address':
			$filter[]=" billing_address LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_company':
			$filter[]=" billing_company LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'shipping_method':
			$filter[]=" shipping_method LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'payment_method':
			$filter[]=" payment_method LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'customer_id':
			$filter[]=" customer_id='".addslashes($this->post['skeyword'])."'";
			break;
	}
}
if (!empty($this->post['order_date_from']) && !empty($this->post['order_date_till'])) {
	list($from_date, $from_time)=explode(" ", $this->post['order_date_from']);
	list($fd, $fm, $fy)=explode('/', $from_date);
	list($till_date, $till_time)=explode(" ", $this->post['order_date_till']);
	list($td, $tm, $ty)=explode('/', $till_date);
	$start_time=strtotime($fy.'-'.$fm.'-'.$fd.' '.$from_time);
	$end_time=strtotime($ty.'-'.$tm.'-'.$td.' '.$till_time);
	if ($this->post['search_by_status_last_modified']) {
		$column='o.status_last_modified';
	} else {
		$column='o.crdate';
	}
	$filter[]=$column." BETWEEN '".$start_time."' and '".$end_time."'";
}
if ($this->post['orders_status_search']>0) {
	$filter[]="(o.status='".$this->post['orders_status_search']."')";
}
if ($this->post['paid_orders_only']) {
	$filter[]="(o.paid='1')";
}
if (!$this->masterShop) {
	$filter[]='o.page_uid='.$this->shop_pid;
}
//$orderby[]='orders_id desc';	
$select[]='o.*, osd.name as orders_status';
$orderby[]='o.orders_id desc';
if ($this->post['tx_multishop_pi1']['by_phone']) {
	$filter[]='o.by_phone=1';
}
if ($this->post['tx_multishop_pi1']['is_proposal']) {
	$filter[]='o.is_proposal=1';
} else {
	$filter[]='o.is_proposal=0';
}
if (isset($this->post['selected_orders'])) {
	$selected_orders='(o.orders_id='.implode(" or o.orders_id=", $this->post['selected_orders']).')';
	$filter[]=$selected_orders;
}
$pageset=mslib_fe::getOrdersPageSet($filter, $offset, 500000, $orderby, $having, $select, $where, $from);
$tmporders=$pageset['orders'];
$filename='orders_listing_'.time().'.xls';
$dir=$this->DOCUMENT_ROOT;
$export_file=$dir."uploads/tx_multishop/tmp/".$filename;
// Creating a workbook
$phpexcel=new PHPExcel();
$phpexcel->getActiveSheet()->setTitle('orders listing');
$phpexcel->setActiveSheetIndex(0);
$header_style['font']=array('bold'=>true);
// write the header
$sheet_header[]=$this->pi_getLL('orders_id');
$sheet_header[]=$this->pi_getLL('store');
$sheet_header[]=$this->pi_getLL('customer');
$sheet_header[]=$this->pi_getLL('order_date');
$sheet_header[]=$this->pi_getLL('amount');
$sheet_header[]=$this->pi_getLL('shipping_method');
$sheet_header[]=$this->pi_getLL('payment_method');
$sheet_header[]=$this->pi_getLL('order_status');
$sheet_header[]=$this->pi_getLL('modified_on', 'Modified on');
$sheet_header[]=$this->pi_getLL('admin_paid');
// write the header
$row_count=1;
$col_count=0;
$colwidth=array();
foreach ($sheet_header as $key=>$val) {
	$colwidth[$col_count]=strlen($val)+2;
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col_count, $row_count, $val);
	$col_count++;
}
$row_count++;
foreach ($tmporders as $order) {
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row_count, $order['orders_id']);
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row_count, mslib_fe::getShopNameByPageUid($order['page_uid']));
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row_count, $order['billing_name']);
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row_count, strftime("%x %X", $order['crdate']));
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row_count, number_format($order['grand_total'], 2, ',', '.'));
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row_count, $order['shipping_method_label']);
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row_count, $order['payment_method_label']);
	$order_status='';
	if (is_array($all_orders_status)) {
		foreach ($all_orders_status as $item) {
			if ($item['id']==$order['status']) {
				$order_status=$item['name'];
			}
		}
	}
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row_count, $order_status);
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(8, $row_count, ($order['status_last_modified'] ? strftime("%x %X", $order['status_last_modified']) : ''));
	if (!$order['paid']) {
		$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(9, $row_count, "No");
	} else {
		$phpexcel->getActiveSheet()->setCellValueByColumnAndRow(9, $row_count, "Yes");
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
// Let's send the file
// redirect output to client browser
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$objWriter=PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
$objWriter->save('php://output');
$filter=array();
$from=array();
$having=array();
$match=array();
$orderby=array();
$where=array();
$orderby=array();
$select=array();
$pageset='';
$tmporders='';
exit;
?>