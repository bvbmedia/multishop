<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$this->box_class="multishop_basket";
$this->cObj->data['header']='<a href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart').'">'.$this->pi_getLL('basket').'</a>';
$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
$totalitems=0;
if(count($cart['products']) > 0) {
	foreach($cart['products'] as $product) {
		if(is_numeric($product['qty'])) {
			$totalitems=$totalitems+$product['qty'];
		}
	}
}
$content.='
<a href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart').'" class="multishop_basketbox"><span>'.$totalitems.'</span></a>
';
?>