<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
$data=array();
if($this->post['itemKey']) {
	if(count($cart['products']) > 0) {
		foreach($cart['products'] as $key=>$product) {
			if($this->post['itemKey'] == $key) {
				$mslib_cart->removeFromCart($key);
			}
		}
	}
}
exit();
?>