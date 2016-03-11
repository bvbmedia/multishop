<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->get['customers_export_hash']) {
	set_time_limit(86400);
	ignore_user_abort(true);
	$customers_export=mslib_fe::getCustomersExportWizard($this->get['customers_export_hash'], 'code');
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
	$string='customersfeed_'.$this->shop_pid.'_'.serialize($customers_export).'-'.md5($this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING']);
	if ($this->ADMIN_USER and $this->get['clear_cache']) {
		$Cache_Lite->remove($string);
	}
	if (!$content=$Cache_Lite->get($string)) {
		$fields=unserialize($customers_export['fields']);
		$post_data=unserialize($customers_export['post_data']);
		if (!$post_data['delimeter_type']) {
			$post_data['delimeter_type']=';';
		}
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
		if (!empty($post_data['customers_date_from']) && !empty($post_data['customers_date_till'])) {
			$start_time=strtotime($post_data['customers_date_from']);
			$end_time=strtotime($post_data['customers_date_till']);
			$column='f.crdate';
			$filter[]=$column." BETWEEN '".$start_time."' and '".$end_time."'";
		}
		if (isset($post_data['status'])) {
			if (!$post_data['status']) {
				$filter[]="(f.disable='1')";
			} else {
				$filter[]="(f.disable='0')";
			}
		}
		if (!$this->masterShop) {
			$filter[]='f.page_uid='.$this->shop_pid;
		}
		$order_by='f.uid';
		switch ($post_data['sort_direction']) {
			case 'asc':
			default:
				$order='asc';
				break;
			case 'desc':
				$order='desc';
				break;
		}
		$orderby[]=$order_by.' '.$order;
		$pageset=mslib_fe::getCustomersPageSet($filter, $offset, 999999999, $orderby, $having, $select, $where);
		$records=$pageset['customers'];
		// load all products
		$excelRows=array();
		$excelHeaderCols=array();
		foreach ($fields as $counter=>$field) {
			$excelHeaderCols[$field]=$field;
		}
		if ($this->get['format']=='excel') {
			$excelRows[]=$excelHeaderCols;
		} else {
			$excelRows[]=implode($post_data['delimeter_type'], $excelHeaderCols);
		}
		foreach ($records as $row) {
			$excelCols=array();
			$total=count($fields);
			$count=0;
			foreach ($fields as $counter=>$field) {
				$count++;
				$tmpcontent='';
				switch ($field) {
					case 'orders_id':
						$orders_record=mslib_befe::getRecords($row['uid'], 'tx_multishop_orders', 'customer_id', array(), '', 'orders_id asc');
						$order_id_rec=array();
						if (is_array($orders_record) && count($orders_record)) {
							foreach ($orders_record as $order) {
								$order_id_rec[]=$order['orders_id'];
							}
						}
						if ($this->get['format']=='excel') {
							$excelCols[]=implode(',', $order_id_rec);
						} else {
							$excelCols[]='"'.implode(',', $order_id_rec).'"';
						}
						break;
					case 'customer_id':
						$excelCols[]=$row['uid'];
						break;
					case 'customer_email':
						$excelCols[]=$row['email'];
						break;
					case 'customer_telephone':
						$excelCols[]=$row['telephone'];
						break;
					case 'customer_mobile':
						$excelCols[]=$row['mobile'];
						break;
					case 'customer_www':
						$excelCols[]=$row['www'];
						break;
					case 'customer_fax':
						$excelCols[]=$row['fax'];
						break;
					case 'customer_gender':
						switch ($row['gender']) {
							case '0':
								$gender='m';
								break;
							case '1':
								$gender='f';
								break;
							default:
								$gender='';
								break;
						}
						$excelCols[]=$gender;
						break;
					case 'customer_first_name':
						$excelCols[]=$row['first_name'];
						break;
					case 'customer_middle_name':
						$excelCols[]=$row['middle_name'];
						break;
					case 'customer_last_name':
						$excelCols[]=$row['last_name'];
						break;
					case 'customer_name':
						$excelCols[]=$row['name'];
						break;
					case 'customer_username':
						$excelCols[]=$row['username'];
						break;
					case 'customer_company':
						$excelCols[]=$row['company'];
						break;
					case 'customer_address':
						$excelCols[]=$row['address'];
						break;
					case 'customer_city':
						$excelCols[]=$row['city'];
						break;
					case 'customer_zip':
						$excelCols[]=$row['zip'];
						break;
					case 'customer_country':
						$excelCols[]=$row['country'];
						break;
					case 'customer_tx_multishop_newsletter':
						$excelCols[]=$row['tx_multishop_newsletter'];
						break;
					default:
						// custom page hook that can be controlled by third-party plugin
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_customers_export.php']['downloadCustomersExportIterateItemFieldProc'])) {
							$output=$row[$field];
							$params=array(
								'field'=>$field,
								'row'=>&$row,
								'output'=>&$output
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_customers_export.php']['downloadCustomersExportIterateItemFieldProc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
							$row[$field]=$output;
						}
						// custom page hook that can be controlled by third-party plugin eof
						if (isset($row[$field])) {
							$excelCols[]=$row[$field];
						}
						break;
				}
			}
			// new rows
			if ($this->get['format']=='excel') {
				$excelRows[]=$excelCols;
			} else {
				$excelRows[]=implode($post_data['delimeter_type'], $excelCols);
			}
		}
		if ($this->get['format']=='excel') {
			require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel.php');
			$objPHPExcel=new PHPExcel();
			$objPHPExcel->getSheet(0)->setTitle('Orders Export');
			$objPHPExcel->getActiveSheet()->fromArray($excelRows);
			$ExcelWriter=new PHPExcel_Writer_Excel2007($objPHPExcel);
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="customers_export_'.$this->get['customers_export_hash'].'.xlsx"');
			$ExcelWriter->save('php://output');
			exit();
		} else {
			$content=implode("\n", $excelRows);
		}
		$Cache_Lite->save($content);
	}
	header("Content-Type: text/plain");
	echo $content;
	exit();
}
exit();
?>