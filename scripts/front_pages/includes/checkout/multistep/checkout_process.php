<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
if (!count($cart['products'])) {
	$content.='<div class="noitems_message">'.$this->pi_getLL('there_are_no_products_in_your_cart').'</div>';
} else {
	if ($this->post) {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
		$mslib_order= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
		$mslib_order->init($this);
		$orders_id=$mslib_cart->convertCartToOrder($GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid));
		if ($orders_id) {
			$show_thank_you=1;
			if ($show_thank_you) {
				// reload the order so all vars are the same
				$order=mslib_fe::getOrder($orders_id);
				$content.=CheckoutStepping($stepCodes, current($stepCodes), $this);
				// good, proceed to the thank you page
				$send_mail=1;
				if ($send_mail) {
					// replacing the variables with dynamic values eof
					// $user=array();
					// $user['name']	= $full_customer_name;
					// $user['email']	= $address['email'];
					mslib_fe::mailOrder($orders_id, 1);
				}
				$order=$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_order');
				$order['orders_id']=$orders_id;
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_order', $order);
				$GLOBALS['TSFE']->storeSessionData();
				next($stepCodes);
				header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[previous_checkout_section]='.current($stepCodes).'&orders_id='.$orders_id));
				exit();
				//			require(current($stepCodes).'.php');
			}
		}
	} else {
		echo "<pre>";
		print_r($address);
		echo "</pre>";
		$content.='Some error occurred.';
	}
}
?>