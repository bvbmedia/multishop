<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
if(count($cart['products']) < 1) {
	$content.='<div class="noitems_message">'.$this->pi_getLL('there_are_no_products_in_your_cart').'</div>';
} else {
	if($posted_page == current($stepCodes)) {
		if(!$this->post['accept_general_conditions']) {
			$erno[]=$this->pi_getLL('you_havent_accepted_the_general_conditions').'.';
		}
		if(!count($erno)) {
			// good, proceed with the next step
			require('checkout_process.php');
		}
	} else {
		$show_review=1;
	}
	if($erno or $show_review) {
		$content.=CheckoutStepping($stepCodes, current($stepCodes), $this);
		$back_button_link=mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[previous_checkout_section]='.prev($stepCodes));
		next($stepCodes);
		$products=$cart['products'];
		if(count($products) < 1) {
			$content.='<div class="noitems_message">'.$this->pi_getLL('there_are_no_products_in_your_cart').'</div>';
		} else {
			if(is_array($erno) and count($erno) > 0) {
				$content.='<div class="error_msg">';
				$content.=$this->pi_getLL('the_following_errors_occurred').': <ul>';
				foreach($erno as $item) {
					$content.='<li>'.$item.'</li>';
				}
				$content.='</ul>';
				$content.='</div>';
			}
			$colspan=5;
			$content.='
			<div class="main-heading"><h2>'.$this->pi_getLL('review_your_order').'</h2></div>
			<div class="content">
			<form action="'.mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[previous_checkout_section]='.current($stepCodes)).'" method="post" name="checkout" id="checkout">';
			$orderDetails=$mslib_cart->getHtmlCartContents();
			$content.='
			<table width="100%">
			<tr id="bottomColumnRight">
				<td colspan="'.$colspan.'">'.$orderDetails.'</td>
			</tr>
			<tr id="bottomColumnComments"> 
				<td colspan="'.$colspan.'"> 
					<div class="shoppingcart_description"> 
						<div class="shoppingcart_label"><strong>'.$this->pi_getLL('comments').'</strong></div> 
						<textarea name="customer_comments" id="customer_comments"></textarea> 
					</div> 
				</td> 
			</tr> 			
			';
			$content.='</table>';
			$content.='
				<hr>
				<div class="accept_general_conditions_container">					
					<input name="accept_general_conditions" id="accept_general_conditions" type="checkbox" value="1" />
					<label for="accept_general_conditions">'.$this->pi_getLL('click_here_if_you_agree_the_general_conditions');
			$page=mslib_fe::getCMScontent('general_conditions', $GLOBALS['TSFE']->sys_language_uid);
			if($page[0]['content']) {
				$content.=' (<a href="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=info&tx_multishop_pi1[cms_hash]='.$page[0]['hash']).'" target="_blank" class="read_general_conditions">'.$this->pi_getLL('view_general_conditions').'</a>)';
			}
			$content.='
				</div>
				<div id="bottom-navigation">
					<a href="'.$back_button_link.'" class="back_button">'.$this->pi_getLL('back').'</a>
					<div class="cart">
					<input name="Submit" type="submit" class="float_right confirm_order_en" value="'.$this->pi_getLL('confirm_order').'" /></div>
				</div>
			</form>	
			</div>
			<script>
			jQuery("#checkout").submit(function(){
				if (!jQuery("#accept_general_conditions").is(":checked")){
					alert(\''.htmlspecialchars(addslashes($this->pi_getLL('you_havent_accepted_the_general_conditions').'.')).'\');
					return false;
				}
			});
			</script>
			';
		}
	}
}
?>