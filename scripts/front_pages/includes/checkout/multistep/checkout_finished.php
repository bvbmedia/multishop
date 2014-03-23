<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<div class="checkout_thank_you">';
$order_session=$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_order');
if(!$order_session['orders_id']) {
	$content.='Something went wrong. Please contact the support department.';
} else {
	$order=mslib_fe::getOrder($order_session['orders_id']);
	$orders_id=$order['orders_id'];
	// replacing the variables with dynamic values			
	$billing_address='';
	$delivery_address='';
	$full_customer_name=$order['billing_first_name'];
	if($order['billing_middle_name']) {
		$full_customer_name.=' '.$order['billing_middle_name'];
	}
	if($order['billing_last_name']) {
		$full_customer_name.=' '.$order['billing_last_name'];
	}
	$delivery_full_customer_name=$order['delivery_first_name'];
	if($order['delivery_middle_name']) {
		$delivery_full_customer_name.=' '.$order['delivery_middle_name'];
	}
	if($order['delivery_last_name']) {
		$delivery_full_customer_name.=' '.$order['delivery_last_name'];
	}
	$full_customer_name=preg_replace('/\s+/', ' ', $full_customer_name);
	$delivery_full_customer_name=preg_replace('/\s+/', ' ', $delivery_full_customer_name);
	if($order['delivery_company']) {
		$delivery_address=$order['delivery_company']."<br />";
	}
	if($delivery_full_customer_name) {
		$delivery_address.=$delivery_full_customer_name."<br />";
	}
	if($order['delivery_address']) {
		$delivery_address.=$order['delivery_address']."<br />";
	}
	if($order['delivery_zip'] and $order['delivery_city']) {
		$delivery_address.=$order['delivery_zip']." ".$order['delivery_city'];
	}
	if($order['delivery_country']) {
		$delivery_address.='<br />'.ucfirst(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['delivery_country']));
	}
	if($order['billing_company']) {
		$billing_address=$order['billing_company']."<br />";
	}
	if($full_customer_name) {
		$billing_address.=$full_customer_name."<br />";
	}
	if($order['billing_address']) {
		$billing_address.=$order['billing_address']."<br />";
	}
	if($order['billing_zip'] and $order['billing_city']) {
		$billing_address.=$order['billing_zip']." ".$order['billing_city'];
	}
	if($order['billing_country']) {
		$billing_address.='<br />'.ucfirst(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['billing_country']));
	}
	$array1=array();
	$array2=array();
	$array1[]='###DELIVERY_FIRST_NAME###';
	$array2[]=$order['delivery_first_name'];
	$array1[]='###DELIVERY_LAST_NAME###';
	$array2[]=preg_replace('/\s+/', ' ', $order['delivery_middle_name'].' '.$order['delivery_last_name']);
	$array1[]='###BILLING_FIRST_NAME###';
	$array2[]=$order['billing_first_name'];
	$array1[]='###BILLING_LAST_NAME###';
	$array2[]=preg_replace('/\s+/', ' ', $order['billing_middle_name'].' '.$order['billing_last_name']);
	$array1[]='###BILLING_TELEPHONE###';
	$array2[]=$order['billing_telephone'];
	$array1[]='###DELIVERY_TELEPHONE###';
	$array2[]=$order['delivery_telephone'];
	$array1[]='###BILLING_MOBILE###';
	$array2[]=$order['billing_mobile'];
	$array1[]='###DELIVERY_MOBILE###';
	$array2[]=$order['delivery_mobile'];
	$array1[]='###FULL_NAME###';
	$array2[]=$full_customer_name;
	$array1[]='###DELIVERY_FULL_NAME###';
	$array2[]=$delivery_full_customer_name;
	$array1[]='###BILLING_NAME###';
	$array2[]=$order['billing_name'];
	$array1[]='###BILLING_EMAIL###';
	$array2[]=$order['billing_email'];
	$array1[]='###DELIVERY_EMAIL###';
	$array2[]=$order['delivery_email'];
	$array1[]='###DELIVERY_NAME###';
	$array2[]=$order['delivery_name'];
	$array1[]='###CUSTOMER_EMAIL###';
	$array2[]=$order['billing_email'];
	$array1[]='###STORE_NAME###';
	$array2[]=$this->ms['MODULES']['STORE_NAME'];
	$array1[]='###TOTAL_AMOUNT###';
	$array2[]=mslib_fe::amount2Cents($order['total_amount']);
	require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
	$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
	$mslib_order->init($this);
	$ORDER_DETAILS=$mslib_order->printOrderDetailsTable($order, 'site');
	$array1[]='###ORDER_DETAILS###';
	$array2[]=$ORDER_DETAILS;
	$array1[]='###BILLING_ADDRESS###';
	$array2[]=$billing_address;
	$array1[]='###DELIVERY_ADDRESS###';
	$array2[]=$delivery_address;
	$array1[]='###CUSTOMER_ID###';
	$array2[]=$order['customer_id'];
	$array1[]='###SHIPPING_METHOD###';
	$array2[]=$order['shipping_method_label'];
	$array1[]='###PAYMENT_METHOD###';
	$array2[]=$order['payment_method_label'];
	$array1[]='###ORDERS_ID###';
	$array2[]=$order['orders_id'];
	$invoice=mslib_fe::getOrderInvoice($order['orders_id'], 0);
	$invoice_id='';
	$invoice_link='';
	if(is_array($invoice)) {
		$invoice_id=$invoice['invoice_id'];
		$invoice_link='<a href="'.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'">'.$invoice['invoice_id'].'</a>';
	}
	$array1[]='###INVOICE_NUMBER###';
	$array2[]=$invoice_id;
	$array1[]='###INVOICE_LINK###';
	$array2[]=$invoice_link;
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
	$array1[]='###CUSTOMER_ID###';
	$array2[]=$order['customer_id'];
	// for on the site eof
	$page=array();
	// first try to load the custom thank you page based on the payment method
	if($order['payment_method']) {
		$page=mslib_fe::getCMScontent('order_received_thank_you_page_'.$order['payment_method'], $GLOBALS['TSFE']->sys_language_uid);
	}
	if(!count($page[0])) {
		$page=mslib_fe::getCMScontent('order_received_thank_you_page', $GLOBALS['TSFE']->sys_language_uid);
	}
	// custom hook that can be controlled by third-party plugin
	if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout.php']['checkoutThankYouPageMarkerPreProc'])) {
		$params=array(
			'order'=>$order,
			'page'=>$page,
			'array1'=>&$array1,
			'array2'=>&$array2);
		foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout.php']['checkoutThankYouPageMarkerPreProc'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom hook that can be controlled by third-party plugin eof		
	if($page[0]['name']) {
		if($page[0]['name']) {
			$page[0]['name']=str_replace($array1, $array2, $page[0]['name']);
			$content.='<div class="main-heading"><h2>'.$page[0]['name'].'</h2></div>';
		}
		if($page[0]['content']) {
			$page[0]['content']=str_replace($array1, $array2, $page[0]['content']);
			$content.=$page[0]['content'];
		}
	} else {
		// show standard thank you
		$content.='<div class="main-heading"><h2>'.$this->pi_getLL('your_order_has_been_received').'</h2></div>';
	}
	//	Thank you for ordering on our shop!
	if($order['payment_method'] and $order['paid']) {
		// order has been paid, so dont load the psp
		$content.='Your order has been paid.';
	} elseif($order['payment_method']) {
		// load optional payment button
		$mslib_payment=t3lib_div::makeInstance('mslib_payment');
		$mslib_payment->init($this);
		$paymentMethods=$mslib_payment->getEnabledPaymentMethods();
		if(is_array($paymentMethods)) {
			foreach($paymentMethods as $user_method) {
				if($user_method['code'] == $order['payment_method']) {
					if($user_method['vars'] and $user_method['provider']) {
						$vars=unserialize($user_method['vars']);
						if($mslib_payment->setPaymentMethod($user_method['provider'])) {
							$extkey='multishop_'.$user_method['provider'];
							if(t3lib_extMgm::isLoaded($extkey)) {
								require(t3lib_extMgm::extPath($extkey).'class.multishop_payment_method.php');
								$paymentMethod=t3lib_div::makeInstance('tx_multishop_payment_method');
								$paymentMethod->setPaymentMethod($user_method['provider']);
								$paymentMethod->setVariables($vars);
								$content.=$paymentMethod->displayPaymentButton($order['orders_id'], $this);
							}
							break;
						}
					}
				}
			}
		}
	}
}
$content.='</div>';
?>