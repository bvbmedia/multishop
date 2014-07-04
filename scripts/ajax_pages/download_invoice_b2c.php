<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$hash=$this->get['tx_multishop_pi1']['hash'];
$invoice=mslib_fe::getInvoice($hash, 'hash');
$pdf_filename = 'invoice_'.$hash.'.pdf';
$pdfoutput = $this->DOCUMENT_ROOT.'uploads/tx_multishop/invoice_'.$hash.'.pdf';
if ($invoice['orders_id'] && !file_exists($pdfoutput)) {
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
		if ($this->ms['MODULES']['INVOICE_PDF_HEADER_IMAGE']) {
			$markerArray['###INVOICE_HEADER_BACKGROUND_IMAGE###']=' <img src="'.$this->ms['MODULES']['INVOICE_PDF_HEADER_IMAGE'].'" style="width: 100%"/>';
		} else {
			$markerArray['###INVOICE_HEADER_BACKGROUND_IMAGE###']='';
		}
		if ($this->ms['MODULES']['INVOICE_PDF_FOOTER_IMAGE']) {
			$markerArray['###INVOICE_FOOTER_BACKGROUND_IMAGE###']=' <img src="'.$this->ms['MODULES']['INVOICE_PDF_FOOTER_IMAGE'].'" style="width: 100%"/>';
		} else {
			$markerArray['###INVOICE_FOOTER_BACKGROUND_IMAGE###']='';
		}
		$markerArray['###LABEL_INVOICE_HEADER###']=$this->pi_getLL('invoice');
		if (!empty($order['delivery_company'])) {
			$markerArray['###DELIVERY_COMPANY###']='<strong>'.$order['delivery_company'].'</strong><br/>';
		} else {
			$markerArray['###DELIVERY_COMPANY###']='';
		}
		$markerArray['###DELIVERY_NAME###']=$order['delivery_name'];
		$markerArray['###DELIVERY_ADDRESS###']=$order['delivery_address'];
		$markerArray['###DELIVERY_ZIP###']=$order['delivery_zip'];
		$markerArray['###DELIVERY_CITY###']=$order['delivery_city'];
		$markerArray['###DELIVERY_COUNTRY###']=ucfirst($order['delivery_country']);
		$markerArray['###LABEL_CUSTOMER_ID###']=$this->pi_getLL('admin_customer_id');
		$markerArray['###CUSTOMER_ID###']=$order['customer_id'];
		$markerArray['###LABEL_ORDER_ID###']=$this->pi_getLL('orders_id');
		$markerArray['###ORDER_ID###']=$invoice['orders_id'];
		$markerArray['###LABEL_ORDER_DATE###']=$this->pi_getLL('admin_order_date');
		$markerArray['###ORDER_DATE###']=strftime("%x", $order['crdate']);
		$markerArray['###LABEL_INVOICE_DATE###']=$this->pi_getLL('invoice_date');
		$markerArray['###INVOICE_DATE###']=strftime("%x", $invoice['crdate']);
		$markerArray['###LABEL_INVOICE_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
		$markerArray['###INVOICE_PAYMENT_METHOD###']=$order['payment_method_label'];
		$markerArray['###INVOICE_ORDER_DETAILS###']=mslib_befe::printInvoiceOrderDetailsTable($order, $invoice['invoice_id'], $prefix);
		$content_cms_header=mslib_fe::getCMScontent('pdf_invoice_header_message', $GLOBALS['TSFE']->sys_language_uid);
		if (!empty($content_cms_header[0]['content'])) {
			$markerArray['###INVOICE_CONTENT_HEADER_MESSAGE###']='<div class="content_header_message">
			<br/><br/>
			'.$content_cms_header[0]['content'].'
			<br/><br/>
			</div>';
		} else {
			$markerArray['###INVOICE_CONTENT_HEADER_MESSAGE###']='<br/><br/>';
		}
		$content_cms_footer=mslib_fe::getCMScontent('pdf_invoice_footer_message', $GLOBALS['TSFE']->sys_language_uid);
		if (!empty($content_cms_footer[0]['content'])) {
			$markerArray['###INVOICE_CONTENT_FOOTER_MESSAGE###']='<div class="content_footer_message" style="page-break-before:auto">
			<br/><br/><br/>
			'.$content_cms_footer[0]['content'].'
			</div>';
		} else {
			$markerArray['###INVOICE_CONTENT_FOOTER_MESSAGE###']='';
		}
		$tmpcontent=$this->cObj->substituteMarkerArray($template, $markerArray);
		include(t3lib_extMgm::extPath('multishop').'res/dompdf/dompdf_config.inc.php');
		$content=$tmpcontent;
		$dompdf = new DOMPDF();
		$dompdf->set_paper('a4');
		$dompdf->load_html($content);
		$dompdf->render();
		// add the page numbering in footer
		$canvas = $dompdf->get_canvas();
		$font = Font_Metrics::get_font("arial", "bold");
		$canvas->page_text(500, 795, "page: {PAGE_NUM} of {PAGE_COUNT}", $font, 11, array(0,0,0));
		// save to real file
		file_put_contents($pdfoutput, $dompdf->output(array('compress' => 0)));
	}
}
header("Content-type:application/pdf");
readfile($pdfoutput);
exit();
?>