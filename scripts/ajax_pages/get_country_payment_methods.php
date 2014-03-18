<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$str3 = "SELECT * from static_countries where cn_short_en='".addslashes($this->post['cc'])."' ";
$qry3 = $GLOBALS['TYPO3_DB']->sql_query($str3);
$row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
$countries_id = $row3['cn_iso_nr'];

$payment_methods = mslib_fe::loadPaymentMethods(0, $countries_id);

$data = array();

$k = 0;
foreach ($payment_methods as $payment_name => $payment_data) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else								$tr_type='even';
	
	if (!$payment_data['sort_order']) {
		$payment_data['sort_order'] = $k;
	}
	
	// refine the payment data, only sent what it's need to rebuild the <li> on the client side
	$data[$payment_data['sort_order']]['payment_id'] 			= $payment_data['id'];
	$data[$payment_data['sort_order']]['payment_label'] 		= $payment_data['name'];
	$data[$payment_data['sort_order']]['payment_description'] 	= $payment_data['description'];
	$data[$payment_data['sort_order']]['li_class'] 				= $tr_type;
	$data[$payment_data['sort_order']]['radio_class'] 			= 'regular-payment';
	
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scripts/ajax_pages/get_country_payment_methods.php']['paymentMethodDataArray'])) {
		$params = array (
				'data' => &$data,
				'payment_data' => &$payment_data
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scripts/ajax_pages/get_country_payment_methods.php']['paymentMethodDataArray'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	
	$k++;
}

// make sure the sort_order of payment method is maintained
ksort($data);
$json = json_encode($data, ENT_NOQUOTES);
echo $json;
exit();
?>