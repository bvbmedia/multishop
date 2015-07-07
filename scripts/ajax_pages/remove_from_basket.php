<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
$data=array();
if ($this->post['itemKey']) {
	if (count($cart['products'])>0) {
		foreach ($cart['products'] as $key=>$product) {
			if ($this->post['itemKey']==$key) {
				$mslib_cart->removeFromCart($key);
			}
		}
	}
}
exit();
?>