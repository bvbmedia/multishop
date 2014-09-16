<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->get['orders_export_hash']) {
	set_time_limit(86400);
	ignore_user_abort(true);
	$orders_export=mslib_fe::getOrdersExportWizard($this->get['orders_export_hash'], 'code');
	$lifetime=7200;
	if ($this->ADMIN_USER) {
		$lifetime=0;
	}
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$lifetime
	);
	$Cache_Lite=new Cache_Lite($options);
	$string='productfeed_'.$this->shop_pid.'_'.serialize($orders_export).'-'.md5($this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING']);
	if ($this->ADMIN_USER and $this->get['clear_cache']) {
		$Cache_Lite->remove($string);
	}
	if (!$content=$Cache_Lite->get($string)) {
		$fields=unserialize($orders_export['fields']);
		$post_data=unserialize($orders_export['post_data']);
		$fields_values=$post_data['fields_values'];

		$records=array();
		// orders record
		$filter=array();
		$from=array();
		$having=array();
		$match=array();
		$orderby=array();
		$where=array();
		$orderby=array();
		$select=array();
		if (!empty($post_data['orders_date_from']) && !empty($post_data['orders_date_till'])) {
			$start_time=strtotime($post_data['orders_date_from']);
			$end_time=strtotime($post_data['orders_date_till']);
			$column='o.crdate';
			$filter[]=$column." BETWEEN '".$start_time."' and '".$end_time."'";
		}
		if ($post_data['order_status']!=='all') {
			$filter[]="(o.status='".$post_data['order_status']."')";
		}
		if ($post_data['payment_status']=='paid') {
			$filter[]="(o.paid='1')";
		} else if ($post_data['payment_status']=='unpaid') {
			$filter[]="(o.paid='0')";
		}
		if (!$this->masterShop) {
			$filter[]='o.page_uid='.$this->shop_pid;
		}
		$select[]='o.*, osd.name as orders_status';
		switch ($post_data['order_by']) {
			case 'billing_name':
				$order_by='o.billing_name';
				break;
			case 'crdate':
				$order_by='o.crdate';
				break;
			case 'grand_total':
				$order_by='o.grand_total';
				break;
			case 'shipping_method_label':
				$order_by='o.shipping_method_label';
				break;
			case 'payment_method_label':
				$order_by='o.payment_method_label';
				break;
			case 'status_last_modified':
				$order_by='o.status_last_modified';
				break;
			case 'orders_id':
			default:
				$order_by='o.orders_id';
				break;
		}
		switch ($post_data['sort_direction']) {
			case 'asc':
				$order='asc';
				break;
			case 'desc':
			default:
				$order='desc';
				break;
		}
		$orderby[]=$order_by.' '.$order;
		if ($post_data['order_type']=='by_phone') {
			$filter[]='o.by_phone=1';
		}
		if ($post_data['order_type']=='proposal') {
			$filter[]='o.is_proposal=1';
		} else {
			$filter[]='o.is_proposal=0';
		}
		$pageset=mslib_fe::getOrdersPageSet($filter, $offset, 1000, $orderby, $having, $select, $where, $from);
		$records=$pageset['orders'];
		// load all products
		$excelHeaderCols=array();
		$excelRows[0]=$excelHeaderCols;
		$excelRows=array();
		foreach ($records as $row) {
			$order_tax_data=unserialize($row['orders_tax_data']);
			if ($this->get['format']=='excel') {
				$excelCols=array();
			}
			$total=count($fields);
			$count=0;
			foreach ($fields as $counter=>$field) {
				$count++;
				$tmpcontent='';
				if ($field!='order_products') {
					$excelHeaderCols[$field]=$field;
				}
				switch ($field) {
					case 'orders_id':
						$excelCols[]=$row['orders_id'];
						break;
					case 'customer_id':
						$excelCols[]=$row['customer_id'];
						break;
					case 'orders_status':
						$excelCols[]=$row['orders_status'];
						break;
					case 'customer_billing_email':
						$excelCols[]=$row['billing_email'];
						break;
					case 'customer_billing_telephone':
						$excelCols[]=$row['billing_telephone'];
						break;
					case 'customer_billing_name':
						$excelCols[]=$row['billing_name'];
						break;
					case 'customer_billing_address':
						$excelCols[]=$row['billing_address'];
						break;
					case 'customer_billing_city':
						$excelCols[]=$row['billing_city'];
						break;
					case 'customer_billing_zip':
						$excelCols[]=$row['billing_zip'];
						break;
					case 'customer_billing_country':
						$excelCols[]=$row['billing_country'];
						break;
					case 'customer_delivery_email':
						$excelCols[]=$row['delivery_email'];
						break;
					case 'customer_delivery_telephone':
						$excelCols[]=$row['delivery_telephone'];
						break;
					case 'customer_delivery_name':
						$excelCols[]=$row['delivery_name'];
						break;
					case 'customer_delivery_address':
						$excelCols[]=$row['delivery_address'];
						break;
					case 'customer_delivery_city':
						$excelCols[]=$row['delivery_city'];
						break;
					case 'customer_delivery_zip':
						$excelCols[]=$row['delivery_zip'];
						break;
					case 'customer_delivery_country':
						$excelCols[]=$row['delivery_country'];
						break;
					case 'orders_grand_total_excl_vat':
						$excelCols[]=($order_tax_data['grand_total']-$order_tax_data['total_orders_tax']);
						break;
					case 'orders_grand_total_incl_vat':
						$excelCols[]=$order_tax_data['grand_total'];
						break;
					case 'payment_status':
						$excelCols[]=($row['paid'])?$this->pi_getLL('paid'):$this->pi_getLL('unpaid');
						break;
					case 'shipping_method':
						$excelCols[]=$row['shipping_method_label'];
						break;
					case 'shipping_cost':
						$excelCols[]=$row['shipping_method_costs'];
						break;
					case 'payment_method':
						$excelCols[]=$row['payment_method_label'];
						break;
					case 'payment_cost':
						$excelCols[]=$row['payment_method_cost'];
						break;
					case 'order_products':
						$order_tmp=mslib_fe::getOrder($row['orders_id']);
						$order_products=$order_tmp['products'];
						$prod_ctr=0;
						foreach ($order_products as $product_tmp) {
							$excelHeaderCols['product_id' . $prod_ctr]='product_id' . $prod_ctr;
							$excelCols[]=$product_tmp['products_id'];
							$excelHeaderCols['product_name' . $prod_ctr]='product_name' . $prod_ctr;
							if (!empty($product_tmp['products_model'])) {
								$excelCols[]=$product_tmp['products_name'] . ' ('.$product_tmp['products_model'].')';
							} else {
								$excelCols[]=$product_tmp['products_name'];;
							}
							$excelHeaderCols['product_qty' . $prod_ctr]='product_qty' . $prod_ctr;
							$excelCols[]=$product_tmp['qty'];
							$excelHeaderCols['product_final_price_excl_tax' . $prod_ctr]='product_final_price_excl_tax' . $prod_ctr;
							$excelCols[]=number_format($product_tmp['final_price'], 2, ',', '.');
							$excelHeaderCols['product_tax_rate' . $prod_ctr]='product_tax_rate' . $prod_ctr;
							$excelCols[]=$product_tmp['products_tax'].'%';
							$prod_ctr++;
						}
						$tmpcontent.=$row['products_quantity'];
						break;
				}
			}
			// new rows
			if ($this->get['format']=='excel') {
				$excelRows[]=$excelCols;
			}
		}
		$excelRows[0]=$excelHeaderCols;
		if ($this->get['format']=='excel') {
			require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel.php');
			$objPHPExcel=new PHPExcel();
			$objPHPExcel->getSheet(0)->setTitle('Orders Export');
			$objPHPExcel->getActiveSheet()->fromArray($excelRows);
			$ExcelWriter=new PHPExcel_Writer_Excel2007($objPHPExcel);
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="orders_export_'.$this->get['orders_export_hash'].'.xlsx"');
			$ExcelWriter->save('php://output');
			exit();
		}
		$Cache_Lite->save($content);
	}
	header("Content-Type: text/plain");
	echo $content;
	exit();
}
exit();
?>