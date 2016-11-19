<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$content='0%';
// first check group discount
if ($GLOBALS["TSFE"]->fe_user->user['uid']) {
	$discount_percentage=mslib_fe::getUserGroupDiscount($GLOBALS["TSFE"]->fe_user->user['uid']);
	if ($discount_percentage) {
		$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
		$mslib_cart->init($this);
		$cart=$mslib_cart->getCart();
		$cart['coupon_code']='';
		$cart['discount']=$discount_percentage;
		$cart['discount_type']='percentage';
		//$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
		//$GLOBALS['TSFE']->fe_user->storeSessionData();
		tx_mslib_cart::storeCart($cart);
		$content=number_format($discount_percentage).'%';
	}
}
//if(!$discount_percentage)
if (!empty($_POST['code']) && $_POST['code']!='undefined') {
	$code=mslib_fe::RemoveXSS(mslib_befe::strtolower($_POST['code']));
	$time=time();
	$str="SELECT * from tx_multishop_coupons where code = '".addslashes($code)."' and status = 1 and (page_uid=0 or page_uid='".$this->showCatalogFromPage."') and (startdate <= '".$time."' and enddate >= '".$time."')";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$continue_calculate_discount=true;
		if ($row['max_usage']>0) {
			if ($row['times_used']>=$row['max_usage']) {
				$content="0%";
				$continue_calculate_discount=false;
			}
		}
		if ($continue_calculate_discount) {
			switch ($row['discount_type']) {
				case 'percentage':
					$content=number_format($row['discount']).'%';
					break;
				case 'price':
					$total_price=mslib_fe::countCartTotalPrice(1, 1);
					if ($total_price<$row['discount']) {
						//$row['discount']=$total_price;
					}
					$content=mslib_fe::amount2Cents($row['discount']);
					break;
			}
			//$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
			$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
			$mslib_cart->init($this);
			$cart=$mslib_cart->getCart();
			$cart['coupon_code']=$code;
			$cart['discount']=$row['discount'];
			$cart['discount_type']=$row['discount_type'];
			//$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
			//$GLOBALS['TSFE']->fe_user->storeSessionData();
			tx_mslib_cart::storeCart($cart);
		}
	} else {
		//$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
		$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
		$mslib_cart->init($this);
		$cart=$mslib_cart->getCart();
		$cart['coupon_code']='';
		$cart['discount']='';
		$cart['discount_type']='';
		$cart['discount_amount']='';
		//$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
		//$GLOBALS['TSFE']->fe_user->storeSessionData();
		tx_mslib_cart::storeCart($cart);
		$content="0%";
	}
} else {
	if ($content=='0%') {
		//$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
		$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
		$mslib_cart->init($this);
		$cart=$mslib_cart->getCart();
		$cart['coupon_code'] = '';
		$cart['discount'] = '';
		$cart['discount_type'] = '';
		$cart['discount_amount']='';
		//$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
		//$GLOBALS['TSFE']->fe_user->storeSessionData();
		tx_mslib_cart::storeCart($cart);
		$content = "0%";
	}
}
$return_data['discount_percentage'] = $content;
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_discount.php']['getDiscountPostHook'])) {
	$params=array(
		'cart'=>&$cart,
		'return_data'=>&$return_data
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_discount.php']['getDiscountPostHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// get the discount_percentage by recalculating the cart content
if ($cart['discount']>0 && !$cart['discount_amount']) {
    $mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
    $mslib_cart->init($this);
    $cart=$mslib_cart->getCart();
    switch ($cart['discount_type']) {
        case 'percentage':
            $return_data['discount_percentage'] = mslib_fe::amount2Cents($cart['discount_amount']);
            break;
    }
}
// hook oef
//
if ($this->tta_user_info['default']['country']) {
	$iso_customer=mslib_fe::getCountryByName($this->tta_user_info['default']['country']);
} else {
	$iso_customer=$this->tta_shop_info;
}
if (!$iso_customer['cn_iso_nr']) {
	// fall back (had issue with admin notification)
	$iso_customer=mslib_fe::getCountryByName($this->tta_shop_info['country']);
}
//
if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_SHOPPING_CART_PAGE']>0) {
	if ($this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']>0) {
		$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']=1;
	}
	$delivery_country_id=$this->post['tx_multishop_pi1']['country_id'];
	$shipping_method_id=$this->post['tx_multishop_pi1']['shipping_method'];
	$shipping_cost_data=mslib_fe::getShoppingcartShippingCostsOverview($iso_customer['cn_iso_nr'], $delivery_country_id, $shipping_method_id);
	$count_cart_incl_vat=0;
	if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
		$count_cart_incl_vat=1;
	}
	//
	$return_data['shipping_cost']=0;
	$return_data['shipping_costs_display']=mslib_fe::amount2Cents(0);
	$return_data['shipping_method']['deliver_by']='';
	$return_data['shopping_cart_total_price']=mslib_fe::amount2Cents(mslib_fe::countCartTotalPrice(1, $count_cart_incl_vat, $iso_customer['cn_iso_nr']));
	//
	foreach ($shipping_cost_data as $shipping_code=>$shipping_cost) {
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
			$count_cart_incl_vat=1;
			$return_data['shipping_cost']=$shipping_cost['shipping_costs_including_vat'];
			$return_data['shipping_costs_display']=mslib_fe::amount2Cents($shipping_cost['shipping_costs_including_vat']);
		} else {
			$return_data['shipping_cost']=$shipping_cost['shipping_costs'];
			$return_data['shipping_costs_display']=mslib_fe::amount2Cents($shipping_cost['shipping_costs']);
		}
		$return_data['shipping_method']=$shipping_cost;
		$return_data['shopping_cart_total_price']=mslib_fe::amount2Cents(mslib_fe::countCartTotalPrice(1, $count_cart_incl_vat, $iso_customer['cn_iso_nr'])+$return_data['shipping_cost']);
	}
} else {
	$count_cart_incl_vat = 0;
	if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
		$count_cart_incl_vat = 1;
	}
	$return_data['shopping_cart_total_price'] = mslib_fe::amount2Cents(mslib_fe::countCartTotalPrice(1, $count_cart_incl_vat, $iso_customer['cn_iso_nr']));
}
echo json_encode($return_data);
exit();
?>