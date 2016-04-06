<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (!isset($this->post['cc'])) {
	$this->post['cc']=$this->post['b_cc'];
}
$str3="SELECT * from static_countries where cn_short_en='".addslashes($this->post['cc'])."' ";
$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
$countries_id=$row3['cn_iso_nr'];
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
$payment_methods_product=array();
$payment_methods_group=array();
$payment_methods_user=array();
$payment_methods=array();
$load_mappings_order=array();
$load_mappings_order[]='products';
$load_mappings_order[]='customers_groups';
$load_mappings_order[]='customers';
foreach ($load_mappings_order as $mapping) {
	switch ($mapping) {
		case 'products':
			if ($this->ms['MODULES']['PRODUCT_EDIT_METHOD_FILTER']) {
				$pids=array();
				foreach ($cart['products'] as $key=>$array) {
					if (is_numeric($array['products_id'])) {
						$pids[]=$array['products_id'];
					}
				}
				if (count($pids)) {
                    $payment_methods_product=mslib_fe::getProductMappedMethods($pids, 'payment', $countries_id);
				}
			}
			break;
		case 'customers_groups':
			if (mslib_fe::loggedin() && $this->ms['MODULES']['GROUP_EDIT_METHOD_FILTER']) {
				$user_groups=array();
				$user_groups=explode(',', $GLOBALS['TSFE']->fe_user->user['usergroup']);
				if (count($user_groups)) {
                    $payment_methods_group=mslib_fe::getCustomerGroupMappedMethods($user_groups, 'payment', $countries_id);
				}
			}
			break;
		case 'customers':
			if (mslib_fe::loggedin() && $this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
				$user_id=array();
				$user_id=$GLOBALS['TSFE']->fe_user->user['uid'];
				if (is_numeric($user_id)) {
                    $payment_methods_user=mslib_fe::getCustomerMappedMethods($user_id, 'payment', $countries_id);
				}
			}
			break;
	}
}
if (count($payment_methods_user)) {
    $payment_methods = $payment_methods_user;
} else if (count($payment_methods_group)) {
    $payment_methods = $payment_methods_group;
} else {
    $payment_methods = $payment_methods_product;
}
if (!count($payment_methods)) {
	// nothing is loaded. this cant be valid so let's load the default methods.
	// load payment method based on store origin country
	$payment_methods=mslib_fe::loadPaymentMethods(0, $countries_id, true, true);
}
$data=array();
$k=0;
foreach ($payment_methods as $payment_name=>$payment_data) {
	$vars=unserialize($payment_data['vars']);
	if ($countries_id>0) {
		$tax_ruleset=mslib_fe::taxRuleSet($payment_data['tax_id'], 0, $countries_id, 0);
	} else {
		$tax_ruleset=mslib_fe::getTaxRuleSet($payment_data['tax_id'], 0);
	}
	$payment_data['tax_rate']=($tax_ruleset['total_tax_rate']/100);
	//print_r($payment_data);
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	if (!$payment_data['sort_order']) {
		$payment_data['sort_order']=$k;
	}
	// refine the payment data, only sent what it's need to rebuild the <li> on the client side
	$data[$payment_data['sort_order']]['payment_id']=$payment_data['id'];
	$data[$payment_data['sort_order']]['payment_label']=$payment_data['name'];
	$data[$payment_data['sort_order']]['payment_description']=$payment_data['description'];
	$data[$payment_data['sort_order']]['li_class']=$tr_type;
	$data[$payment_data['sort_order']]['radio_class']='regular-payment';
	$price='';
	if (!empty($payment_data['handling_costs'])) {
		$price=$payment_data['handling_costs'];
		if ($vars['handling_costs_type']!='percentage') {
			if ($price && $payment_data['tax_rate']>0 && $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$price=($price*$payment_data['tax_rate'])+$price;
			}
			if ($price>0.00 || $price<0.00) {
				$price=mslib_fe::currency() . ' +' . mslib_fe::amount2Cents($price, 0, 0);
			} else {
				$price='';
			}
		}
		$data[$payment_data['sort_order']]['handling_cost']=$price;
	} else {
		$data[$payment_data['sort_order']]['handling_cost']='';
	}
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scripts/ajax_pages/get_country_payment_methods.php']['paymentMethodDataArray'])) {
		$params=array(
			'data'=>&$data,
			'payment_data'=>&$payment_data
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scripts/ajax_pages/get_country_payment_methods.php']['paymentMethodDataArray'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	$k++;
}
// make sure the sort_order of payment method is maintained
ksort($data);
$json=json_encode($data, ENT_NOQUOTES);
echo $json;
exit();
?>