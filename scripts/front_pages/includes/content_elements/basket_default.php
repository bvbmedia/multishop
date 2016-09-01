<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$output=array();
// now parse all the objects in the tmpl file
if ($this->conf['basket_default_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['basket_default_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/basket_default.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$output['basket_header']='<a href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart').'">'.$this->pi_getLL('basket').'</a>';
//$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
$totalitems=0;
if (count($cart['products'])>0) {
	foreach ($cart['products'] as $product) {
		if (is_numeric($product['qty'])) {
			$totalitems=$totalitems+$product['qty'];
		}
	}
}
$output['total_items']=(($totalitems==1) ? sprintf($this->pi_getLL('you_have_item_in_your_cart'), $totalitems) : sprintf($this->pi_getLL('you_have_items_in_your_cart'), $totalitems)).'.';
if ($totalitems>0) {
	$output['goto_shoppingcart_link']=' <a class="multishop_goto_checkout" href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=checkout').'">'.$this->pi_getLL('proceed_to_checkout').'</a>';
}
// fill the row marker with the expanded rows
$subpartArray['###BASKET_HEADER###']=$output['basket_header'];
$subpartArray['###TOTAL_ITEMS###']=$output['total_items'];
$subpartArray['###LINK_TO_SHOPPINGCART###']=$output['goto_shoppingcart_link'];
// completed the template expansion by replacing the "item" marker in the template
$content=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
?>