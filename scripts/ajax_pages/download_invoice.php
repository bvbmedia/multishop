<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (!$this->get['tx_multishop_pi1']['hash']) {
	die();
}
$hash=$this->get['tx_multishop_pi1']['hash'];
$invoice=mslib_fe::getInvoice($hash, 'hash');
$pdfFileName = 'invoice_'.$hash.'.pdf';
$pdfFilePath = $this->DOCUMENT_ROOT.'uploads/tx_multishop/'.$pdfFileName;
if (($this->get['tx_multishop_pi1']['forceRecreate'] || !file_exists($pdfFilePath)) && $invoice['orders_id']) {
	if ($invoice['reversal_invoice']) {
		$prefix='-';
	} else {
		$prefix='';
	}
	$order=mslib_fe::getOrder($invoice['orders_id']);
	$orders_tax_data=$order['orders_tax_data'];
	if ($order['orders_id']) {
		// now parse all the objects in the tmpl file
		if ($this->conf['admin_invoice_pdf_tmpl_path']) {
			$template=$this->cObj->fileResource($this->conf['admin_invoice_pdf_tmpl_path']);
		} else {
			$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_invoice_pdf.tmpl');
		}
		$markerArray=array();
		$markerArray['###GENDER_SALUTATION###']=mslib_fe::genderSalutation($order['billing_gender']);
		if ($this->ms['MODULES']['INVOICE_PDF_HEADER_IMAGE']) {
			$imageLocation=$this->ms['MODULES']['INVOICE_PDF_HEADER_IMAGE'];
			if (!file_exists($imageLocation) && file_exists($this->DOCUMENT_ROOT.$imageLocation)) {
				// relative filepath
				$imageLocation=$this->FULL_HTTP_URL.$imageLocation;
			}
			$markerArray['###INVOICE_HEADER_BACKGROUND_IMAGE###']=' <img src="'.$imageLocation.'" style="width: 100%"/>';
		} else {
			$markerArray['###INVOICE_HEADER_BACKGROUND_IMAGE###']='';
		}
		if ($this->ms['MODULES']['INVOICE_PDF_FOOTER_IMAGE']) {
			$imageLocation=$this->ms['MODULES']['INVOICE_PDF_FOOTER_IMAGE'];
			if (!file_exists($imageLocation) && file_exists($this->DOCUMENT_ROOT.$imageLocation)) {
				// relative filepath
				$imageLocation=$this->FULL_HTTP_URL.$imageLocation;
			}
			$markerArray['###INVOICE_FOOTER_BACKGROUND_IMAGE###']=' <img src="'.$imageLocation.'" style="width: 100%"/>';
		} else {
			$markerArray['###INVOICE_FOOTER_BACKGROUND_IMAGE###']='';
		}
		$markerArray['###LABEL_INVOICE_HEADER###']=$this->pi_getLL('invoice');
		if (!empty($order['billing_company'])) {
			$markerArray['###BILLING_COMPANY###']='<strong>'.$order['billing_company'].'</strong><br/>';
		} else {
			$markerArray['###BILLING_COMPANY###']='';
		}
		$markerArray['###BILLING_NAME###']=$order['billing_name'];
		$markerArray['###BILLING_ADDRESS###']=$order['billing_address'];
		$markerArray['###BILLING_ZIP###']=$order['billing_zip'];
		$markerArray['###BILLING_CITY###']=mslib_befe::strtoupper($order['billing_city']);
		$markerArray['###BILLING_COUNTRY###']='';
		if (mslib_befe::strtolower($order['billing_country'])!=mslib_befe::strtolower($this->tta_shop_info['country'])) {
			// ONLY PRINT COUNTRY IF THE COUNTRY OF THE CUSTOMER IS DIFFERENT THAN FROM THE SHOP
			$markerArray['###BILLING_COUNTRY###']=mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['billing_country']);
		}
		$markerArray['###LABEL_CUSTOMER_ID###']=$this->pi_getLL('admin_customer_id');
		$markerArray['###CUSTOMER_ID###']=$order['customer_id'];
		$markerArray['###LABEL_ORDER_ID###']=$this->pi_getLL('orders_id');
		$markerArray['###ORDER_ID###']=$invoice['orders_id'];
		$markerArray['###LABEL_ORDER_DATE###']=$this->pi_getLL('admin_order_date');
		$markerArray['###ORDER_DATE###']=strftime("%x", $order['crdate']);
		$markerArray['###LABEL_INVOICE_DATE###']=$this->pi_getLL('invoice_date');
		$markerArray['###INVOICE_DATE###']=strftime("%x", $invoice['crdate']);
		$markerArray['###LABEL_INVOICE_NUMBER###']=$this->pi_getLL('invoice_number');
		$markerArray['###INVOICE_NUMBER###']=$invoice['invoice_id'];
		$markerArray['###LABEL_INVOICE_PAYMENT_METHOD###']='';
		$markerArray['###INVOICE_PAYMENT_METHOD###']='';
		if ($order['payment_method_label']) {
			$markerArray['###LABEL_INVOICE_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
			$markerArray['###INVOICE_PAYMENT_METHOD###']=$order['payment_method_label'];
		}
		$markerArray['###INVOICE_ORDER_DETAILS###']=mslib_befe::printInvoiceOrderDetailsTable($order, $invoice['invoice_id'], $prefix);
		$markerArray['###LABEL_YOUR_VAT_ID###']='';
		$markerArray['###YOUR_VAT_ID###']='';
		if ($order['billing_vat_id']) {
			$markerArray['###LABEL_YOUR_VAT_ID###']=$this->pi_getLL('your_vat_id');
			$markerArray['###YOUR_VAT_ID###']=$order['billing_vat_id'];
		}
		// CMS HEADER
		$markerArray['###INVOICE_CONTENT_HEADER_MESSAGE###']='';
		$cmsKeys=array();
		if ($order['payment_method']) {
			$cmsKeys[]='pdf_invoice_header_message_'.$order['payment_method'];
		}
		$cmsKeys[]='pdf_invoice_header_message';
		foreach ($cmsKeys as $cmsKey) {
			$page=mslib_fe::getCMScontent($cmsKey, $GLOBALS['TSFE']->sys_language_uid);
			if (!empty($page[0]['content'])) {
				$markerArray['###INVOICE_CONTENT_HEADER_MESSAGE###']='<div class="content_header_message">
				<br/><br/><br/>
				'.$page[0]['content'].'
				</div>';
			}
			if (is_array($page)) {
				break;
			}
		}
		// CMS FOOTER
		$markerArray['###INVOICE_CONTENT_FOOTER_MESSAGE###']='';
		$cmsKeys=array();
		if ($order['payment_method']) {
			$cmsKeys[]='pdf_invoice_footer_message_'.$order['payment_method'];
		}
		$cmsKeys[]='pdf_invoice_footer_message';
		foreach ($cmsKeys as $cmsKey) {
			$page=mslib_fe::getCMScontent($cmsKey, $GLOBALS['TSFE']->sys_language_uid);
			if (!empty($page[0]['content'])) {
				$markerArray['###INVOICE_CONTENT_FOOTER_MESSAGE###']='<div class="content_footer_message" style="page-break-before:auto">
				<br/><br/><br/>
				'.$page[0]['content'].'
				</div>';
			}
			if (is_array($page)) {
				break;
			}
		}
		// MARKERS
		$array1=array();
		$array2=array();
		$array1[]='###BILLING_FULL_NAME###';
		$array2[]=$order['billing_name'];
		$array1[]='###FULL_NAME###';
		$array2[]=$order['billing_name'];
		$array1[]='###BILLING_NAME###';
		$array2[]=$order['billing_name'];
		$array1[]='###BILLING_FIRST_NAME###';
		$array2[]=$order['billing_first_name'];
		$array1[]='###BILLING_LAST_NAME###';
		$array2[]=preg_replace('/\s+/', ' ', $order['billing_middle_name'].' '.$order['billing_last_name']);
		$array1[]='###BILLING_EMAIL###';
		$array2[]=$order['billing_email'];
		$array1[]='###BILLING_TELEPHONE###';
		$array2[]=$order['billing_telephone'];
		$array1[]='###BILLING_MOBILE###';
		$array2[]=$order['billing_mobile'];
		// full delivery name
		$array1[]='###DELIVERY_NAME###';
		$array2[]=$order['delivery_name'];
		$array1[]='###DELIVERY_FULL_NAME###';
		$array2[]=$order['delivery_name'];
		$array1[]='###DELIVERY_FIRST_NAME###';
		$array2[]=$order['delivery_first_name'];
		$array1[]='###DELIVERY_LAST_NAME###';
		$array2[]=preg_replace('/\s+/', ' ', $order['delivery_middle_name'].' '.$order['delivery_last_name']);
		$array1[]='###DELIVERY_EMAIL###';
		$array2[]=$order['delivery_email'];
		$array1[]='###DELIVERY_TELEPHONE###';
		$array2[]=$order['delivery_telephone'];
		$array1[]='###DELIVERY_MOBILE###';
		$array2[]=$order['delivery_mobile'];
		$array1[]='###CUSTOMER_EMAIL###';
		$array2[]=$order['billing_email'];
		$time=$order['crdate'];
		$long_date=strftime($this->pi_getLL('full_date_format'), $time);
		$array1[]='###ORDER_DATE_LONG###'; // ie woensdag 23 juni, 2010
		$array2[]=$long_date;
		// backwards compatibility
		$array1[]='###LONG_DATE###'; // ie woensdag 23 juni, 2010
		$array2[]=$long_date;
		$time=time();
		$long_date=strftime($this->pi_getLL('full_date_format'), $time);
		$array1[]='###CURRENT_DATE_LONG###'; // ie woensdag 23 juni, 2010
		$array2[]=$long_date;
		$array1[]='###STORE_NAME###';
		$array2[]=$this->ms['MODULES']['STORE_NAME'];
		$array1[]='###TOTAL_AMOUNT###';
		$array2[]=mslib_fe::amount2Cents($order['total_amount']);
		$array1[]='###PROPOSAL_NUMBER###';
		$array2[]=$order['orders_id'];
		$array1[]='###ORDER_NUMBER###';
		$array2[]=$order['orders_id'];
		$array1[]='###ORDER_LINK###';
		$array2[]='';
		$array1[]='###ORDER_STATUS###';
		$array2[]=$order['orders_status'];
		$array1[]='###TRACK_AND_TRACE_CODE###';
		$array2[]=$order['track_and_trace_code'];
		$array1[]='###BILLING_ADDRESS###';
		$array2[]=$billing_address;
		$array1[]='###DELIVERY_ADDRESS###';
		$array2[]=$delivery_address;
		$array1[]='###CUSTOMER_ID###';
		$array2[]=$order['customer_id'];
		$array1[]='###INVOICE_NUMBER###';
		$array2[]=$invoice['invoice_id'];
		$array1[]='###INVOICE_ID###';
		$array2[]=$invoice['invoice_id'];
		$array1[]='###INVOICE_LINK###';
		$array2[]=$invoice_link;
		$array1[]='###ORDER_DETAILS###';
		$array2[]=$ORDER_DETAILS;
		$array1[]='###SHIPPING_METHOD###';
		$array2[]=$order['shipping_method_label'];
		$array1[]='###PAYMENT_METHOD###';
		$array2[]=$order['payment_method_label'];
		$array1[]='###EXPECTED_DELIVERY_DATE###';
		$array2[]=strftime("%x", $order['expected_delivery_date']);
		$array1[]='###CUSTOMER_COMMENTS###';
		$array2[]=$order['customer_comments'];
		$array1[]='###PAYMENT_CONDITION###';
		if ($order['payment_condition']) {
			$array2[]=$order['payment_condition'].' '.$this->pi_getLL('days');
		} else {
			$array2[]='';
		}

		$array1[]='###PAYMENT_DUE_DATE###';
		if ($order['payment_condition']) {
			$array2[]=strftime("%x", strtotime('+'.$order['payment_condition'].' day',$invoice['crdate']));
		} else {
			$array2[]='';
		}
		$array1[]='###GENDER_SALUTATION###';
		$array2[]=mslib_fe::genderSalutation($order['billing_gender']);
		//hook to let other plugins further manipulate the replacers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderReplacersPostProc'])) {
			$params=array(
				'array1'=>&$array1,
				'array2'=>&$array2,
				'order'=>&$order,
				'mail_template'=>$mail_template
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderReplacersPostProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		if ($markerArray['###INVOICE_CONTENT_HEADER_MESSAGE###']) {
			$markerArray['###INVOICE_CONTENT_HEADER_MESSAGE###']=str_replace($array1, $array2, $markerArray['###INVOICE_CONTENT_HEADER_MESSAGE###']);
		}
		if ($markerArray['###INVOICE_CONTENT_FOOTER_MESSAGE###']) {
			$markerArray['###INVOICE_CONTENT_FOOTER_MESSAGE###']=str_replace($array1, $array2, $markerArray['###INVOICE_CONTENT_FOOTER_MESSAGE###']);
		}
		$markerArray['###LABEL_INVOICE_PAYMENT_CONDITION###']='';
		$markerArray['###INVOICE_PAYMENT_CONDITION###']='';
		if ($order['payment_condition']) {
			$markerArray['###LABEL_INVOICE_PAYMENT_CONDITION###']=$this->pi_getLL('payment_condition');
			$markerArray['###INVOICE_PAYMENT_CONDITION###']=$order['payment_condition'].' '.$this->pi_getLL('days');
		}


		// MARKERS EOL
		$tmpcontent=$this->cObj->substituteMarkerArray($template, $markerArray);
		// debug html output
		include(t3lib_extMgm::extPath('multishop').'res/dompdf/dompdf_config.inc.php');
		include(t3lib_extMgm::extPath('multishop').'res/dompdf/dompdf_config.custom.php');
		$content=$tmpcontent;
		/*
		if ($this->get['debug']) {
			echo $content;
			die();
		}
		*/
		$dompdf = new DOMPDF();
		$dompdf->set_paper('A4');
		$dompdf->load_html($content, 'UTF-8');
		$dompdf->render();
		// ADD PAGE NUMBER IN FOOTER
		$canvas = $dompdf->get_canvas();
		$font = Font_Metrics::get_font("arial", "bold");
		$canvas->page_text(500, 795, $this->pi_getLL('page','page').' {PAGE_NUM} '.$this->pi_getLL('of','of').' {PAGE_COUNT}', $font, 11, array(0,0,0));
		// SAVE AS FILE
		file_put_contents($pdfFilePath, $dompdf->output(array('compress' => 0)));
	}
}
if (file_exists($pdfFilePath)) {
	header("Content-type:application/pdf");
	readfile($pdfFilePath);
}
exit();
?>