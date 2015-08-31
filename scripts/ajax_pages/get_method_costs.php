<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
header("Content-Type:application/json; charset=UTF-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D,d M YH:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
$tmp_countries=mslib_fe::getCountryByName($this->post['d_cc']);
$mslib_cart->setCountry($this->post['b_cc'], $tmp_countries['cn_iso_nr']);
$mslib_cart->setShippingMethod($this->post['tx_multishop_pi1']['sid']);
$mslib_cart->setPaymentMethod($this->post['tx_multishop_pi1']['pid']);
$cart=$mslib_cart->getCart();
$countries_id=$tmp_countries['cn_iso_nr'];
$payment_method=mslib_fe::getPaymentMethod($this->post['tx_multishop_pi1']['pid'], 'p.id', $countries_id, true);
if ($payment_method['handling_costs']) {
	if (!strstr($payment_method['handling_costs'], "%")) {
		$payment_method_costs=$payment_method['handling_costs'];
	} else {
		// calculate total payment costs based by %
		$subtotal=$cart['summarize']['sub_total_including_vat'];
		if ($subtotal) {
			if (strstr($payment_method['handling_costs'], "%")) {
				$percentage=str_replace("%", '', $payment_method['handling_costs']);
				$payment_method_costs=($subtotal/100*$percentage);
			} else {
				$payment_method_costs=$payment_method['handling_costs'];
			}
		}
	}
} else {
	$payment_method_costs=0;
}
if ($payment_method_costs && $payment_method['tax_rate'] && $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
	$payment_method_costs=($payment_method_costs*$payment_method['tax_rate'])+$payment_method_costs;
}
$payment_method_code=$payment_method['code'];
if (strlen($payment_method['name'])>1) {
	$char=substr($payment_method['name'], 1, 1);
	if ($char==mslib_befe::strtolower($char)) {
		$payment_method['name']=$payment_method['name'];
	}
} else {
	$payment_method['name']=$payment_method['name'];
}
$payment_method_label=$payment_method['name'];
$available_sid=array();
$str_s2p="select * from tx_multishop_payment_shipping_mappings where payment_method = '".addslashes($this->post['tx_multishop_pi1']['pid'])."'";
$qry_s2p=$GLOBALS['TYPO3_DB']->sql_query($str_s2p);
while ($row_s2p=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_s2p)) {
	$str3="SELECT * from static_countries where cn_short_en='".addslashes($this->post['d_cc'])."'";
	$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
	$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
	$user_country=$row3['cn_iso_nr'];
	$str="SELECT c2z.id as c2z_id, s.* from tx_multishop_shipping_methods s, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_methods_to_zones p2z where ";
	$str.="s.status=1 and ";
	$str.="c2z.cn_iso_nr = ".$user_country." and c2z.zone_id = p2z.zone_id and p2z.shipping_method_id = s.id and s.id=".$row_s2p['shipping_method'];
	$qry_s2z=$GLOBALS['TYPO3_DB']->sql_query($str);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_s2z)) {
		$available_sid[]=$row_s2p['shipping_method'];
	}
}

//
$mapped_shipping_methods_product=array();
$mapped_shipping_methods_group=array();
$mapped_shipping_methods_user=array();
$mapped_shipping_methods=array();
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
                    $mapped_shipping_methods_product=mslib_fe::getProductMappedMethods($pids, 'shipping', $tmp_countries['cn_iso_nr']);
                }
            }
            break;
        case 'customers_groups':
            if (mslib_fe::loggedin() && $this->ms['MODULES']['GROUP_EDIT_METHOD_FILTER']) {
                $user_groups=array();
                $user_groups=explode(',', $GLOBALS['TSFE']->fe_user->user['usergroup']);
                if (count($user_groups)) {
                    $mapped_shipping_methods_group=mslib_fe::getCustomerGroupMappedMethods($user_groups, 'shipping', $tmp_countries['cn_iso_nr']);
                }
            }
            break;
        case 'customers':
            if (mslib_fe::loggedin() && $this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
                $user_id=array();
                $user_id=$GLOBALS['TSFE']->fe_user->user['uid'];
                if (is_numeric($user_id)) {
                    $mapped_shipping_methods_user=mslib_fe::getCustomerMappedMethods($user_id, 'shipping', $tmp_countries['cn_iso_nr']);
                }
            }
            break;
    }
}
if (count($mapped_shipping_methods_user)) {
    $mapped_shipping_methods=$mapped_shipping_methods_user;
} else if (count($mapped_shipping_methods_group)) {
    $mapped_shipping_methods=$mapped_shipping_methods_group;
} else {
    $mapped_shipping_methods=$mapped_shipping_methods_product;
}
if (count($mapped_shipping_methods)) {
    $available_sid=array();
    foreach ($mapped_shipping_methods as $shipping_method_name=>$mapped_shipping_method) {
        $available_sid[]=$mapped_shipping_method['id'];
    }
}
//
if (count($available_sid)>0) {
	if (!$this->ms['MODULES']['PRODUCT_EDIT_METHOD_FILTER']) {
		if (!$this->post['tx_multishop_pi1']['sid'] or !in_array($this->post['tx_multishop_pi1']['sid'], $available_sid)) {
			// if the posted shipping id is not in the available shipping method array then select the first valid shipping method
			$this->post['tx_multishop_pi1']['sid']=$available_sid[0];
		}
	}
	$shipping_method=mslib_fe::getShippingMethod($this->post['tx_multishop_pi1']['sid'], 's.id', $countries_id, true);
	$shipping_method_code=$shipping_method['code'];
	if (strlen($shipping_method['name'])>1) {
		$char=substr($shipping_method['name'], 1, 1);
		if ($char==mslib_befe::strtolower($char)) {
			$shipping_method['name']=$shipping_method['name'];
		}
	} else {
		$shipping_method['name']=$shipping_method['name'];
	}
	$shipping_method_label=$shipping_method['name'];
	// shipping
	$price='';
	$priceArray=mslib_fe::getShippingCosts($countries_id, $this->post['tx_multishop_pi1']['sid']);
	if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
		$data['shipping_cost']=$priceArray['shipping_costs_including_vat'];
		$data['shipping_cost_cur']=mslib_fe::amount2Cents($priceArray['shipping_costs_including_vat']);
	} else {
		$data['shipping_cost']=$priceArray['shipping_costs'];
		$data['shipping_cost_cur']=mslib_fe::amount2Cents($priceArray['shipping_costs']);
	}
	$data['shipping_name']=$shipping_method_label;
} else {
	$data['shipping_cost']=0;
	$data['shipping_cost_cur']=mslib_fe::amount2Cents(0);
	$data['shipping_name']=$shipping_method_label;
}
$data['payment_cost']=$payment_method_costs;
$data['payment_cost_cur']=mslib_fe::amount2Cents($payment_method_costs);
$data['payment_name']=$payment_method_label;
$data['available_shipping']=implode(';', $available_sid);
// rebuilt the shipping cost for available shipping methods based on selected payment
foreach ($available_sid as $sids) {
	$priceArray=mslib_fe::getShippingCosts($countries_id, $sids);
	if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
		$data['available_shippingcost'][$sids]=mslib_fe::amount2Cents($priceArray['shipping_costs_including_vat']);
	} else {
		$data['available_shippingcost'][$sids]=mslib_fe::amount2Cents($priceArray['shipping_costs']);
	}
}
// we display the shipping costs and payment costs including vat
if ($this->ms['MODULES']['PRODUCT_EDIT_METHOD_FILTER'] && !$this->post['tx_multishop_pi1']['sid']) {
	if (count($available_sid)==1) {
		$this->post['tx_multishop_pi1']['sid']=$available_sid[0];
	} else {
		// set to unreachable number for shipping method id so the session for shipping method are cleared
		$this->post['tx_multishop_pi1']['sid']=999999;
	}
}
$mslib_cart->setShippingMethod($this->post['tx_multishop_pi1']['sid']);
$mslib_cart->setPaymentMethod($this->post['tx_multishop_pi1']['pid']);
$cart=$mslib_cart->getCart();
$data['htmlCartContents']=$mslib_cart->getHtmlCartContents('ajaxGetMethodCosts');
$json=json_encode($data, ENT_NOQUOTES);
echo $json;
exit();
?>