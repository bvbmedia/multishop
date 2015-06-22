<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$data=array();
if ($this->post['products_id']) {
	$product=mslib_fe::getProduct($this->post['products_id']);
	if ($product['products_id']) {
		$mslib_cart->updateCart();
		$data['added_product']['products_name']=$product['products_name'];
		$data['added_product']['products_model']=$product['products_model'];
	}
}
//$cart = $GLOBALS['TSFE']->fe_user->getKey('ses',$this->cart_page_uid);
$cart=$mslib_cart->getCart();
$totalitems=0;
if (count($cart['products'])>0) {
	foreach ($cart['products'] as $product) {
		if ($product['qty']>0) {
			$totalitems=$totalitems+$product['qty'];
		}
	}
}
$totalitems=ceil($totalitems);
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/products_to_basket.php']['CartItemsCountLabelPostHook'])) {
	$params=array(
		'cart'=>&$cart,
		'totalitems'=>&$totalitems
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/products_to_basket.php']['CartItemsCountLabelPostHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// hook oef					
$data['cartCount']=$totalitems;
$data['cartMessage']=(($totalitems==1) ? sprintf($this->pi_getLL('you_have_item_in_your_cart'), $totalitems) : sprintf($this->pi_getLL('you_have_items_in_your_cart'), $totalitems));
if ($totalitems>0) {
	$basket_link=mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart');
	$data['cartMessage']='<a href="'.$basket_link.'">'.$data['cartMessage'].'</a>';
}
$cart=$mslib_cart->getCart();
$data['cartContents']=$mslib_cart->getHtmlCartContents('ajaxProductsToBasket');
echo json_encode($data, ENT_NOQUOTES);
exit();
?>