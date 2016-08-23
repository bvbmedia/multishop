<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$this->box_class="multishop_basket";
$this->cObj->data['header']='<a href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart').'">'.$this->pi_getLL('basket').'</a>';
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
$content.='<div class="multishop_basketbox">';
$content.='<span id="basket_message">'.(($totalitems==1) ? sprintf($this->pi_getLL('you_have_item_in_your_cart'), $totalitems) : sprintf($this->pi_getLL('you_have_items_in_your_cart'), $totalitems)).'</span>';
$content.='
<ul>
	<li><a class="multishop_goto_shopping_cart" href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart').'">'.$this->pi_getLL('view_contents', 'Bekijk inhoud').'</a></li>
	<li><a class="multishop_goto_checkout" href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=checkout').'">'.$this->pi_getLL('go_directly_to_checkout', 'Ga direct naar afrekenen').'</a></li>
</ul>
';
$content.='</div>
<div id="ajax_add_to_cart"></div>
';
/*
<a href="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'],'&tx_multishop_pi1[page_section]=shopping_cart').'"> See all Products </a>
*/
$content.='
';
$content.='
<script>
jQuery(document).ready(function($){
';
if (!$totalitems) {
	$content.='
	$(".multishop_goto_checkout").hide();
	$(".multishop_goto_shopping_cart").hide();	
	'."\n";
}
//$(".ajax_add_to_cart").bind("click", function(e) {
$content.='
$("#ajax_cart_tooltip").hide();
// ajax add to cart
$(document).on("click", ".ajax_add_to_cart", function(e) {
		e.preventDefault();
		var products_id=$(this).attr("rel");
		var quantity=$(this).parent().parent().find(".relation_cart_quantity").val();		
		if (typeof quantity == "undefined") {
			quantity = 1;
		}
		$.ajax({ 
				type:   "POST", 
				url:    "'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=products_to_basket').'",
				data:   "products_id="+products_id+"&quantity="+quantity, 
				dataType: "json",
				success: function(data) {
					if(data.cartCount != null){
						$("#basket_message").html(data.cartMessage);
						$("html,body").scrollTop(0);
						$("#ajax_add_to_cart").html(data.added_product[\'products_name\']+" is toegevoegd!");
						$("#ajax_add_to_cart").show().delay(2500).fadeOut(1500);
						$(".multishop_goto_checkout").show();
						$(".multishop_goto_shopping_cart").show();
					}
				} 
		});
	});
});
</script>	
';
?>