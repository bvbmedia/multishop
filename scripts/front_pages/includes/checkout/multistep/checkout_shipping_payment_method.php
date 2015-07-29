<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$cart=$mslib_cart->getCart();
if ($posted_page==current($stepCodes)) {
	// now verify the posted values
	if (!$this->post['shipping_method'] and count($shipping_methods)) {
		// check to see if its allowed
		$str3="SELECT * from tx_multishop_payment_shipping_mappings where payment_method='".addslashes($this->post['payment_method'])."'";
		$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry3)>0) {
			$erno[]=$this->pi_getLL('no_shipping_method_chosen').'.';
		}
	}
	if (!$this->post['payment_method']) {
		$erno[]=$this->pi_getLL('no_payment_method_chosen').'.';
	}
	if (!$erno) {
		// shipping
		$mslib_cart->setShippingMethod($this->post['shipping_method']);
		$mslib_cart->setPaymentMethod($this->post['payment_method']);
		$cart=$mslib_cart->getCart();
		// good, proceed with the next step
		next($stepCodes);
		require(current($stepCodes).'.php');
	}
} else {
	$show_shipping_payment_method=1;
}
if ($erno or $show_shipping_payment_method) {
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_shipping_payment_method']['checkoutMultistepShippingPaymentPreHook'])) {
		$params=array(
			'content'=>&$content,
			'stepCodes'=>&$stepCodes,
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_shipping_payment_method']['checkoutMultistepShippingPaymentPreHook'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	$content.=CheckoutStepping($stepCodes, current($stepCodes), $this);
	$back_button_link=mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[previous_checkout_section]='.prev($stepCodes));
	next($stepCodes);
	if (is_array($erno) and count($erno)>0) {
		$content.='<div class="alert alert-danger">';
		$content.=$this->pi_getLL('the_following_errors_occurred').': <ul>';
		foreach ($erno as $item) {
			$content.='<li>'.$item.'</li>';
		}
		$content.='</ul>';
		$content.='</div>';
	}
	$content.='
	<form action="'.mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[previous_checkout_section]='.current($stepCodes)).'" method="post" name="checkout" id="checkout">
	';
	if (count($payment_methods)) {
		$content.='
		<div id="multishopPaymentMethodWrapper">
		<div class="main-heading"><h2>'.$this->pi_getLL('choose_payment_method').'</h2></div>
		<ul id="multishopPaymentMethod" class="row">';
		$count=0;
		$tr_type='even';
		$countries_id=$mslib_cart->getCountry();
		foreach ($payment_methods as $code=>$item) {
			$payment_method=mslib_fe::getPaymentMethod($code, 'p.code', $countries_id);
			$vars=unserialize($payment_method['vars']);
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$count++;
			// costs
			$price_wrap='';
			$price=$item['handling_costs'];
			if ($price>0) {
				if ($vars['handling_costs_type']!='percentage') {
					if ($price and $payment_method['tax_rate']>0) {
						$price=($price*$payment_method['tax_rate'])+$price;
					}
					$price=mslib_fe::amount2Cents($price);
				}
				$price_wrap='<div class="shipping_price" style="float:right" id="shipping_price_'.$item['id'].'">'.$price.'</div>';
			}
			// costs eof
			$content.='<li id="multishop_payment_method_'.$item['id'].'" class="'.$tr_type.' col-sm-4"><label for="payment_method_'.$item['id'].'" class="name"><div class="listing_item">';
			if ($price_wrap) {
				$content.=$price_wrap;
			}
			$content.='<input name="payment_method" id="payment_method_'.$item['id'].'" type="radio" value="'.htmlspecialchars($item['id']).'" '.((($this->get['tx_multishop_pi1']['previous_checkout_section']<>current($stepCodes) and $count==1) or $user['payment_method']==$item['code']) ? 'checked' : '').' /><strong class="method_name">'.$item['name'].'</strong>';
			if ($item['description']) {
				$content.='<span class="description">'.$item['description'].'</span>';
			}
			$content.='</div></label>';
			$content.='</li>';
		}
		$content.='
		</ul>
		</div>
		';
	}
	if (count($shipping_methods)) {
		$content.='
		<div id="multishopShippingMethodWrapper">
		<div class="main-heading"><h2>'.$this->pi_getLL('choose_shipping_method').'</h2></div>
		<ul id="multishopShippingMethod" class="row">';
		$count=0;
		foreach ($shipping_methods as $code=>$item) {
			$shipping_method=mslib_fe::getShippingMethod($item['id'], 's.id', $cart['user']['countries_id']);
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_shipping_payment_method']['checkoutMultistepShippingMethodSelectionHook'])) {
				$params=array(
					'shipping_method'=>&$shipping_method,
					'item'=>&$item,
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_shipping_payment_method']['checkoutMultistepShippingMethodSelectionHook'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			$count++;
			// costs
			$price_wrap='';
			$priceArray=mslib_fe::getShippingCosts($cart['user']['countries_id'], $item['id']);
			if ($priceArray['shipping_costs_including_vat']>0) {
				$data['shipping_cost']=$priceArray['shipping_costs_including_vat'];
				$price_wrap='<div class="shipping_price" style="float:right" id="shipping_price_'.$item['id'].'">'.mslib_fe::amount2Cents($priceArray['shipping_costs_including_vat']).'</div>';
			}
			// costs eof
			$content.='<li id="multishop_shipping_method_'.$item['id'].'" class="col-sm-4"><label for="shipping_method_'.$item['id'].'" class="name" id="label_shipping_method_'.$item['id'].'"><div class="listing_item">';
			if ($price_wrap) {
				$content.=$price_wrap;
			}
			$content.='<input name="shipping_method" id="shipping_method_'.$item['id'].'" type="radio" value="'.htmlspecialchars($item['id']).'" '.(($this->post['tx_multishop_pi1']['previous_checkout_section']<>current($stepCodes) and $count==1) ? 'checked' : '').' /><strong class="method_name">'.$item['name'].'</strong>';
			if ($item['description']) {
				$content.='<span class="description">'.$item['description'].'</span>';
			}
			$content.='</div></label>';
			$content.='</li>';
		}
		$content.='
		 </ul>
	 	 </div>
		 ';
		/*  if (count($shipping_methods)==1)
		 {
			$content.='
			<script>
			  jQuery(document).ready(function($) {
				 //$("#shipping_payment_method").hide();
			  });
			 </script>
			 ';
		 } */
	}
	$content.='
		<div id="bottom-navigation">
			<a href="'.$back_button_link.'" class="msFrontButton backState arrowLeft arrowPosLeft"><span>'.$this->pi_getLL('back').'</span></a>
			<span class="msFrontButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" class="proceed_to_checkout_button_en" value="'.$this->pi_getLL('proceed_to_checkout').'" /></span>
		</div>
	</form>
	';
	if ($this->ADMIN_USER) {
		$content.='
		<script>
		  jQuery(document).ready(function($) {
			var result 	= jQuery("#multishopPaymentMethod").sortable({
					cursor:     "move",
			    //axis:       "y",
			    update: function(e, ui) {
			        href = "'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=method_sortables').'";
			        jQuery(this).sortable("refresh");
			        sorted = jQuery(this).sortable("serialize", "id");
			        jQuery.ajax({
			                type:   "POST",
			                url:    href,
			                data:   sorted,
			                success: function(msg) {
			                        //do something with the sorted data
			                }
			        });
			    }
			});
			var result2	= jQuery("#multishopShippingMethod").sortable({
					cursor:     "move",
			    //axis:       "y",
			    update: function(e, ui) {
			        href = "'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=method_sortables').'";
			        jQuery(this).sortable("refresh");
			        sorted = jQuery(this).sortable("serialize", "id");
			        jQuery.ajax({
			                type:   "POST",
			                url:    href,
			                data:   sorted,
			                success: function(msg) {
			                        //do something with the sorted data
			                }
			        });
			    }
			});
		  });
		  </script>
		';
	}
// jquery mapping table
	$mappings=array();
	foreach ($payment_methods as $code=>$item) {
		$mappings[$item['id']]='';
	}
	$str3="SELECT * from tx_multishop_payment_shipping_mappings";
	$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
	while (($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3))!=false) {
		$mappings[$row3['payment_method']][$row3['shipping_method']]=1;
	}
	$jquery_content='';
	$jquery_content.='
	<script>
	jQuery(document).ready(function($) {
	';
	$xxi=0;
	if (is_array($mappings) and count($mappings)) {
		foreach ($mappings as $mapping_payment_method_id=>$mapping_shipping_methods) {
			if ($xxi==0) {
				if (is_array($shipping_methods) and count($shipping_methods)) {
					foreach ($shipping_methods as $code=>$item) {
						if (!is_array($mapping_shipping_methods) or !$mapping_shipping_methods[$item['id']]) {
							// shipping option has no mapping to the payment option so must be grayed
							$jquery_content.='jQuery(\'#shipping_method_'.$item['id'].'\').attr("disabled", true).hide();'."\n";
							$jquery_content.='jQuery(\'#label_shipping_method_'.$item['id'].'\').hide();'."\n";
							$jquery_content.='jQuery(\'#shipping_price_'.$item['id'].'\').hide();'."\n";
							$jquery_content.='jQuery(\'#multishop_shipping_method_'.$item['id'].'\').hide();'."\n";
						} else {
							// shipping option has mapping to the payment option so must be shown
							$jquery_content.='jQuery(\'#shipping_method_'.$item['id'].'\').removeAttr("disabled").show().attr("checked", true);'."\n";
							$jquery_content.='jQuery(\'#label_shipping_method_'.$item['id'].'\').show();'."\n";
							$jquery_content.='jQuery(\'#shipping_price_'.$item['id'].'\').show();'."\n";
							$jquery_content.='jQuery(\'#multishop_shipping_method_'.$item['id'].'\').show();'."\n";
							$hide=0;
						}
					}
				}
				if ($hide) {
					$jquery_content.='jQuery(\'#shipping_method\').hide(\'slow\', function(){});';
				}
				/*
								else		$jquery_content.='
								var shipping_methods= $(\'.shipping_method li\').length;
								var hidden_shipping_methods= $(\'.shipping_method li :hidden\').length;
								if (shipping_methods == hidden_shipping_methods || shipping_methods < hidden_shipping_methods ) {

								}
								else
								{
									jQuery(\'#shipping_method\').show(\'slow\', function(){});
								}
								';
				*/
				$jquery_content.='
				jQuery(\'#shipping_method\').show();
				var shipping_methods= $(\'.shipping_method li\').length;
				var hidden_shipping_methods= $(\'.shipping_method li :hidden\').length;
				if ($(\'.shipping_method li :visible\').length == 0) {
					jQuery(\'#shipping_method\').hide();
				}
				';
			}
			$jquery_content.='

				jQuery("#payment_method_'.$mapping_payment_method_id.'").click(function(event)
				{
					$("#shippingPaymentMethod").show();
			';
			$hide=1;
			$checked=0;
			if (is_array($shipping_methods) and count($shipping_methods)) {
				foreach ($shipping_methods as $code=>$item) {
					if (!is_array($mapping_shipping_methods) or !$mapping_shipping_methods[$item['id']]) {
						// shipping option has no mapping to the payment option so must be grayed
						$jquery_content.='jQuery(\'#shipping_method_'.$item['id'].'\').attr("disabled", true).hide();'."\n";
						$jquery_content.='jQuery(\'#label_shipping_method_'.$item['id'].'\').hide();'."\n";
						$jquery_content.='jQuery(\'#shipping_price_'.$item['id'].'\').hide();'."\n";
						$jquery_content.='jQuery(\'#multishop_shipping_method_'.$item['id'].'\').hide();'."\n";
					} else {
						// shipping option has mapping to the payment option so must be shown
						$jquery_content.='jQuery(\'#shipping_method_'.$item['id'].'\').removeAttr("disabled").show()'.($checked==0 ? '.attr("checked", true)' : '').';'."\n";
						$jquery_content.='jQuery(\'#label_shipping_method_'.$item['id'].'\').show();'."\n";
						$jquery_content.='jQuery(\'#shipping_price_'.$item['id'].'\').show();'."\n";
						$jquery_content.='jQuery(\'#multishop_shipping_method_'.$item['id'].'\').show();'."\n";
						$hide=0;
						$checked++;
					}
				}
			}
			if ($hide) {
				$jquery_content.='jQuery(\'#shipping_method\').hide(\'slow\', function(){});';
			} else {
				$jquery_content.='jQuery(\'#shipping_method\').show(\'slow\', function(){});';
			}
			$jquery_content.='
				});
			';
			$xxi++;
		}
	}
	$jquery_content.='
	});
	</script>
	';
	$content.=$jquery_content;
// jquery mapping table eof
}
?>