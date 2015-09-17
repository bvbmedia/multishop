<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
	$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']=1;
}
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
$mslib_cart=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_cart');
$mslib_cart->init($this);
$disable_checkout=false;
$output=array();
$GLOBALS['TSFE']->additionalHeaderData[]='<meta name="robots" content="noindex">'."\n";
// now parse all the objects in the tmpl file
if ($this->conf['shopping_cart_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['shopping_cart_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/shopping_cart.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
$subparts['footer']=$this->cObj->getSubpart($subparts['template'], '###CART_FOOTER###');
if (!$this->ms['MODULES']['COUPONS']) {
	// clear coupon html
	// because the DISCOUNT_MODULE_WRAPPER is inside the CART_FOOTER wrapper we have to substitute it on the footer
	$subFooterparts=array();
	$subFooterparts['discount_module']=$this->cObj->getSubpart($subparts['footer'], '###DISCOUNT_MODULE_WRAPPER###');
	$subpartFooterArray=array();
	$subpartFooterArray['###DISCOUNT_MODULE_WRAPPER###']='';
	$subparts['footer']=$this->cObj->substituteMarkerArrayCached($subparts['footer'], array(), $subpartFooterArray);
}
//JS
if ($this->ms['MODULES']['COUPONS']) {
	$GLOBALS['TSFE']->additionalHeaderData[]='<script type="text/javascript">
	function postCoupon(value) {
		jQuery.ajax({
			type: "POST",
			url: "'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_discount').'",
			cache :false,
			data: "code=" + value,
			success:
				function(t) {
					jQuery("#span_discount").html(t);
					jQuery("#korting").val(t);
				},
			error:
				function() {
					jQuery("#span_discount").html("0");
				}
		 });
	}
	jQuery(document).ready(function($) {
		postCoupon(jQuery("#coupons_code").val());
		jQuery("#coupons_code").bind("keyup",function() {
			postCoupon(this.value);
		});
	});
	</script>';
}
$output['shopping_cart_colspan']=5;
$output['shopping_cart_header']=ucfirst($this->pi_getLL('basket'));
$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
if (count($cart['products'])>0) {
	$output['shopping_cart_form_action_url']=mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart');
	$output['col_header_shopping_cart_product']=ucfirst($this->pi_getLL('product'));
	$output['col_header_shopping_cart_qty']=ucfirst($this->pi_getLL('qty'));
	$output['col_header_shopping_cart_total']=ucfirst($this->pi_getLL('total'));
	$contentItem='';
	foreach ($cart['products'] as $shopping_cart_item=>$value) {
		if (is_numeric($value['products_id'])) {
			$ordered_qty=$value['qty'];
			$product_info=mslib_fe::getProduct($value['products_id']);
			$products_id=$value['products_id'];
			$product=$value;
			if (!$output['product_row_type'] || $output['product_row_type']=='even') {
				$output['product_row_type']='odd';
			} else {
				$output['product_row_type']='even';
			}
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				if ($value['country_tax_rate'] && $value['region_tax_rate']) {
					$country_tax_rate=mslib_fe::taxDecimalCrop($value['final_price']*($value['country_tax_rate']));
					$region_tax_rate=mslib_fe::taxDecimalCrop($value['final_price']*($value['region_tax_rate']));
					$tax_rate=$country_tax_rate+$region_tax_rate;
				} else {
					$tax_rate=mslib_fe::taxDecimalCrop($value['final_price']*($value['tax_rate']));
				}
				$value['final_price']=$value['final_price']+$tax_rate;
			}
			$final_price=($value['qty']*$value['final_price']);
			$price=($value['qty']*$value['final_price']);
			if (!$product['products_image']) {
				$output['product_image']='<div class="no_image_50"></div>';
			} else {
				$output['product_image']='<img src="'.$product['products_image'].'">';
			}
			$output['product_link']=$value['link'];
			$output['product_name']=$product['products_name'].($product['products_model'] ? '  <span class="checkout_listing_products_model">('.$product['products_model'].')</span>' : '').'</a></span>';
			$output['product_attributes']='';
			if (is_array($value['attributes'])) {
				// loading the attributes
				foreach ($value['attributes'] as $attribute_key=>$attribute_values) {
					$continue=0;
					if (is_numeric($attribute_key)) {
						$str="SELECT products_options_name,listtype from tx_multishop_products_options o where o.products_options_id='".$attribute_key."' ";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					}
					switch ($row['listtype']) {
						case 'checkbox':
							$output['product_attributes'].='<div class="attributes-items"><span class="attribute-option">'.$row['products_options_name'].':</span> ';
							$continue=0;
							$total=count($attribute_values);
							$counter=0;
							foreach ($attribute_values as $item) {
								$counter++;
								if ($product['tax_rate'] && $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
									$item['options_values_price']=round($item['options_values_price']*(1+$product['tax_rate']), 2);
								} else {
									$item['options_values_price']=round($item['options_values_price'], 2);
								}
								$output['product_attributes'].='<span class="attribute-value">'.trim($item['products_options_values_name']).'</span>';
								$price=$price+($value['qty']*($item['price_prefix'].$item['options_values_price']));
								if ($item['options_values_price']>0) {
									$subprices.=mslib_fe::amount2Cents(($value['qty']*($item['price_prefix'].$item['options_values_price'])));
								}
								$subprices.='<br />';
								if ($counter<$total) {
									$output['product_attributes'].=', ';
								}
							}
							break;
						case 'input':
							$output['product_attributes'].='<div class="attributes-items"><span class="attribute-option">'.$row['products_options_name'].':</span><span class="attribute-value">'.$value['attributes'][$attribute_key]['products_options_values_name'].'</span></div>';
							$multiple=0;
							$continue=0;
							break;
						default:
							$multiple=0;
							$continue=1;
							break;
					}
					if ($continue) {
						$array=array($attribute_values);
						foreach ($array as $item) {
							if ($product['tax_rate'] && $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
								if ($value['country_tax_rate'] && $value['region_tax_rate']) {
									$country_tax_rate=mslib_fe::taxDecimalCrop($item['options_values_price']*($value['country_tax_rate']));
									$region_tax_rate=mslib_fe::taxDecimalCrop($item['options_values_price']*($value['region_tax_rate']));
									$item_tax_rate=$country_tax_rate+$region_tax_rate;
								} else {
									$item_tax_rate=mslib_fe::taxDecimalCrop($item['options_values_price']*($value['tax_rate']));
								}
								$item['options_values_price']=$item['options_values_price']+($item_tax_rate);
							} else {
								$item['options_values_price']=round($item['options_values_price'], 2);
							}
							if ($item['options_values_price']>0) {
								$subprices.=mslib_fe::amount2Cents(($value['qty']*($item['price_prefix'].$item['options_values_price'])));
							}
							$subprices.='<br />';
							$output['product_attributes'].='<div class="attributes-items"><span class="attribute-option">'.$item['products_options_name'].':</span><span class="attribute-value">'.$item['products_options_values_name'].$t.'</span></div>';
							$price=$price+($value['qty']*($item['price_prefix'].$item['options_values_price']));
						}
					}
				}
				// loading the attributes eof
			}
			if (!empty($output['product_attributes'])) {
				$output['product_attributes']='<div class="shoppingcart-attributes-wrapper">'.$output['product_attributes'].'</div>';
			}
			// show selectbox by products multiplication or show default input
			$quantity_html='';
			/*if ($product['maximum_quantity']>0 || (is_numeric($product['products_multiplication']) && $product['products_multiplication']>0)) {
				$start_number='';
				$ending_number='';
				$item='';
				if ($product['maximum_quantity']>0) {
					$ending_number=$product['maximum_quantity'];
				}
				if ($product['minimum_quantity']>0) {
					$start_number=$product['minimum_quantity'];
				} else {
					if ($product['products_multiplication']) {
						$start_number=$product['products_multiplication'];
					}
				}
				if (!$start_number) {
					$start_number=1;
				}
				$quantity_html.='<select name="qty['.$shopping_cart_item.']">';
				$count=0;
				$steps=10;
				if ($product['maximum_quantity'] && $product['products_multiplication']) {
					$steps=floor($product['maximum_quantity']/$product['products_multiplication']);
				} else {
					if ($product['maximum_quantity'] && !$product['products_multiplication']) {
						$steps=($ending_number-$start_number)+1;
					}
				}
				$count=$start_number;
				if ($ending_number && $value['qty']>$ending_number) {
					$value['qty']=$ending_number;
				}
				for ($i=0; $i<$steps; $i++) {
					if ($product['products_multiplication']) {
						$item=$product['products_multiplication'];
					} else {
						if ($i) {
							$item=1;
						}
					}
					$quantity_html.='<option value="'.$count.'"'.($value['qty']==$count ? ' selected' : '').'>'.$count.'</option>';
					$count=($count+$item);
				}
				$quantity_html.='</select>';
			} else {
				$quantity_html.='<div class="quantity buttons_added"><input type="button" value="-" class="qty_minus" rel="qty_'.$shopping_cart_item.'"><input class="qty_input" name="qty['.$shopping_cart_item.']" type="text" id="qty_'.$shopping_cart_item.'" value="'.$value['qty'].'" size="4" maxlength="4" /><input type="button" value="+" class="qty_plus" rel="qty_'.$shopping_cart_item.'"></div>';
			}*/
			$quantity_html.='<div class="quantity buttons_added">';
			$quantity_html.='<input type="button" value="-" data-stepSize="'.($product['products_multiplication']!='0.00' ? $product['products_multiplication'] : '1').'" data-minQty="'.($product['minimum_quantity']!='0.00' ? $product['minimum_quantity'] : '1').'" data-maxQty="'.($product['maximum_quantity']!='0.00' ? $product['maximum_quantity'] : '0').'" class="qty_minus" rel="qty_'.$shopping_cart_item.'">';
			$quantity_html.='<input class="qty_input" name="qty['.$shopping_cart_item.']" type="text" id="qty_'.$shopping_cart_item.'" value="'.$value['qty'].'" size="4" maxlength="4" />';
			$quantity_html.='<input type="button" value="+" data-stepSize="'.($product['products_multiplication']!='0.00' ? $product['products_multiplication'] : '1').'" data-minQty="'.($product['minimum_quantity']!='0.00' ? $product['minimum_quantity'] : '1').'" data-maxQty="'.($product['maximum_quantity']!='0.00' ? $product['maximum_quantity'] : '0').'" class="qty_plus" rel="qty_'.$shopping_cart_item.'"></div>';
			// show selectbox by products multiplication or show default input eof
			if (!$this->ms['MODULES']['ALLOW_ORDER_OUT_OF_STOCK_PRODUCT']) {
				if ($value['qty']>$value['products_quantity']) {
					$disable_checkout=true;
				}
			}
			if ($subprices) {
				$subprices='<div class="attribute_prices">'.$subprices.'</div>';
			}
			$output['product_qty']=$quantity_html;
			$output['product_link_delete']=mslib_fe::typolink($this->conf['shopping_cart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart&delete_products_id='.$shopping_cart_item);
			$output['label_remove_product']=$this->pi_getLL('remove_product_from_basket');
			$output['product_final_price']=mslib_fe::amount2Cents($final_price).$subprices;
			$subprices='';
			$subtotal=($subtotal+$price);
		}
		if ($disable_checkout) {
			if (!$this->ms['MODULES']['DISABLE_OUT_OF_STOCK_PRODUCT_WARNING_MESSAGE']) {
				if ($value['products_quantity']>0) {
					if ($ordered_qty>$value['products_quantity']) {
						$output['product_attributes'].='<br/><span class="out-of-stock-warning"><strong>'.sprintf($this->pi_getLL('ordered_product_qty_exceed_maximum_stock', 'the quantity you request for this product are exceeding the stock we have, at this moment the maximum quantity you may order for this product is: %s<br/>please update the order quantity for this product, and continue the checkout'), $value['products_quantity']).'</strong></span>';
					}
				} else {
					$output['product_attributes'].='<br/><span class="out-of-stock-warning"><strong>'.$this->pi_getLL('ordered_product_not_instock', 'this product is currently not available').'</strong></span>';
				}
			}
		} else {
			if (!$this->ms['MODULES']['DISABLE_OUT_OF_STOCK_PRODUCT_WARNING_MESSAGE']) {
				if ($value['products_quantity']<0 || ($value['qty']>$value['products_quantity'])) {
					$output['product_attributes'].='<br/><span class="out-of-stock-warning"><strong>'.$this->pi_getLL('ordered_product_stock_not_available_waiting_for_restock', 'due to the quantity you order for this product are exceeding the stock we have, we will process the order for this product after the re-stock. you can continue the checkout.').'</strong></span>';
				}
			}
		}
		$markerArray=array();
		$markerArray['PRODUCT_ROW_TYPE']=$output['product_row_type'];
		$markerArray['PRODUCT_IMAGE']=$output['product_image'];
		$markerArray['PRODUCT_LINK']=$output['product_link'];
		$markerArray['PRODUCT_NAME']=$output['product_name'];
		$markerArray['PRODUCT_ATTRIBUTES']=$output['product_attributes'];
		$markerArray['PRODUCT_QTY']=$output['product_qty'];
		$markerArray['PRODUCT_LINK_DELETE']=$output['product_link_delete'];
		$markerArray['LABEL_REMOVE_PRODUCT']=$output['label_remove_product'];
		$markerArray['PRODUCT_FINAL_PRICE']=$output['product_final_price'];
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/shopping_cart/default.php']['cartItemPostHook'])) {
			$params=array(
				'markerArray'=>&$markerArray
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/shopping_cart/default.php']['cartItemPostHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
	}
	if (!$output['product_row_type'] || $output['product_row_type']=='even') {
		$output['product_row_type']='odd';
	} else {
		$output['product_row_type']='even';
	}
	$output['label_shopping_cart_subtotal']=$this->pi_getLL('subtotal');
	$output['shopping_cart_subtotal']=mslib_fe::amount2Cents($subtotal);
	//coupons code
	if ($this->ms['MODULES']['COUPONS']) {
		if (!$tr_type || $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$output['label_coupon_code']=$this->pi_getLL('coupon_code');
		$output['coupon_code']=($cart['coupon_code'] ? $cart['coupon_code'] : '');
		$output['coupon_code_value']=($cart['coupon_discount'] ? $cart['coupon_discount'] : 0);
		$output['label_discount']=$this->pi_getLL('discount');
		if ($cart['coupon_discount']) {
			switch ($cart['coupon_discount_type']) {
				case 'percentage':
					$output['discount_value']=number_format($cart['coupon_discount']).'%';
					break;
				case 'price':
					$output['discount_value']=mslib_fe::amount2Cents($cart['coupon_discount']);
					break;
			}
		} else {
			$output['discount_value']='0%';
		}
	}
	//coupons code eof
	if (!$output['product_row_type'] || $output['product_row_type']=='even') {
		$output['product_row_type2']='odd';
	} else {
		$output['product_row_type2']='even';
	}
	$output['label_update_shopping_cart']=$this->pi_getLL('update_shopping_cart');
	$output['goto_catalog_link']=mslib_fe::typolink($this->shop_pid, '');
	$output['label_goto_catalog']=$this->pi_getLL('go_to_catalog');
	$output['label_checkout']=$this->pi_getLL('proceed_to_checkout');
	// MINIMUM ORDER AMOUNT
	$count_products=count($cart['products']);
	$cart_total_amount=$mslib_cart->countCartTotalPrice(0);
	$minmax_warning='';
	if (!empty($this->ms['MODULES']['MINIMUM_ORDER_AMOUNT']) && $this->ms['MODULES']['MINIMUM_ORDER_AMOUNT']>0) {
		if ($cart_total_amount<$this->ms['MODULES']['MINIMUM_ORDER_AMOUNT']) {
			$disable_checkout=true;
			$minmax_warning=sprintf($this->pi_getLL('minimum_cart_amount_must_at_least_x'), mslib_fe::amount2Cents($this->ms['MODULES']['MINIMUM_ORDER_AMOUNT']));
		}
	}
	// MAXIMUM ORDER AMOUNT
	if (!empty($this->ms['MODULES']['MAXIMUM_ORDER_AMOUNT']) && $this->ms['MODULES']['MAXIMUM_ORDER_AMOUNT']>0) {
		if ($cart_total_amount>$this->ms['MODULES']['MAXIMUM_ORDER_AMOUNT']) {
			$disable_checkout=true;
			$minmax_warning=sprintf($this->pi_getLL('maximum_cart_total_amount_are_x'), mslib_fe::amount2Cents($this->ms['MODULES']['MAXIMUM_ORDER_AMOUNT']));
		}
	}
	if (empty($minmax_warning)) {
		// clear coupon html
		// because the DISCOUNT_MODULE_WRAPPER is inside the CART_FOOTER wrapper we have to substitute it on the footer
		$subFooterparts=array();
		$subFooterparts['minmax_warning']=$this->cObj->getSubpart($subparts['footer'], '###MIN_MAX_CART_AMOUNT_WRAPPER###');
		$subpartFooterArray=array();
		$subpartFooterArray['###MIN_MAX_CART_AMOUNT_WRAPPER###']='';
		$subparts['footer']=$this->cObj->substituteMarkerArrayCached($subparts['footer'], array(), $subpartFooterArray);
	}
	if ($disable_checkout) {
		$output['checkout_link']='javascript:void(0)';
	} else {
		$output['checkout_link']=mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]=checkout');
	}
	// fill the row marker with the expanded rows
	$markerArray=array();
	$markerArray['LABEL_COUPON_CODE']=$output['label_coupon_code'];
	$markerArray['COUPON_CODE']=$output['coupon_code'];
	$markerArray['COUPON_CODE_VALUE']=$output['coupon_code_value'];
	$markerArray['LABEL_DISCOUNT']=$output['label_discount'];
	$markerArray['DISCOUNT_VALUE']=$output['discount_value'];
	$markerArray['LABEL_SHOPPING_CART_SUBTOTAL']=$output['label_shopping_cart_subtotal'];
	$markerArray['SHOPPING_CART_SUBTOTAL']=$output['shopping_cart_subtotal'];
	$markerArray['SHOPPING_CART_COLSPAN']=$output['shopping_cart_colspan'];
	$markerArray['PRODUCT_ROW_TYPE2']=$output['product_row_type2'];
	$markerArray['LABEL_UPDATE_SHOPPING_CART']=$output['label_update_shopping_cart'];
	$markerArray['MINMAX_AMOUNT_WARNING']=$minmax_warning;
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/shopping_cart/default.php']['footerPostHook'])) {
		$params=array(
			'markerArray'=>&$markerArray
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/shopping_cart/default.php']['footerPostHook'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	$footerItem=$this->cObj->substituteMarkerArray($subparts['footer'], $markerArray, '###|###');
	// header part
	$subpartArray=array();
	$subpartArray['###SHOPPING_CART_FORM_ACTION_URL###']=$output['shopping_cart_form_action_url'];
	$subpartArray['###SHOPPING_CART_HEADER###']=$output['shopping_cart_header'];
	$subpartArray['###COL_HEADER_SHOPPING_CART_PRODUCT###']=$output['col_header_shopping_cart_product'];
	$subpartArray['###COL_HEADER_SHOPPING_CART_QTY###']=$output['col_header_shopping_cart_qty'];
	$subpartArray['###COL_HEADER_SHOPPING_CART_TOTAL###']=$output['col_header_shopping_cart_total'];
	$subpartArray['###GOTO_CATALOG_LINK###']=$output['goto_catalog_link'];
	$subpartArray['###LABEL_GOTO_CATALOG###']=$output['label_goto_catalog'];
	$subpartArray['###CHECKOUT_LINK###']=$output['checkout_link'];
	$subpartArray['###LABEL_CHECKOUT###']=$output['label_checkout'];
	$subpartArray['###ITEM###']=$contentItem;
	$subpartArray['###CART_FOOTER###']=$footerItem;
	// shipping cost
	$subpartArray['###SHIPPING_COST_COUNTRY_SELECTION###']='';
	if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_SHOPPING_CART_PAGE']) {
		// load countries
		$deliver_to_sb='';
		// load enabled countries to array
		$str2="SELECT * from static_countries sc, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_countries c where c.page_uid='".$this->showCatalogFromPage."' and sc.cn_iso_nr=c.cn_iso_nr and c2z.cn_iso_nr=sc.cn_iso_nr group by c.cn_iso_nr order by sc.cn_short_en";
		//$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		$enabled_countries=array();
		while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
			$enabled_countries[]=$row2;
		}
		// load enabled countries to array eof
		if (count($enabled_countries)==1) {
			$deliver_to_sb.='<input name="deliver_to_country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
		} else {
			$default_country=mslib_fe::getCountryByIso($this->tta_shop_info['cn_iso_nr']);
			foreach ($enabled_countries as $country) {
				$tmpcontent_con.='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($default_country['cn_short_en'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
			}
			if ($tmpcontent_con) {
				$deliver_to_sb.='
				<select name="deliver_to_country" id="deliver_to_country" class="country" title="'.$this->pi_getLL('country_is_required').'">
					'.$tmpcontent_con.'
				</select>
				';
			}
		}
		// country eof
		$subpartArray['###SHIPPING_COST_COUNTRY_SELECTION###']='
		<div class="shoppingcart_shipping_cost_wrapper">
			<div class="shipping_deliver_to"><label for="deliver_to_country">'.$this->pi_getLL('deliver_in').'<label>'.$deliver_to_sb.'</div>
			<div class="shipping_deliver_by"></div>
		</div>
		';
	}
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/shopping_cart/default.php']['shoppingCartPagePostHook'])) {
		$params=array(
			'template'=>$template,
			'subpartArray'=>&$subpartArray,
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/shopping_cart/default.php']['shoppingCartPagePostHook'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	// completed the template expansion by replacing the "item" marker in the template
	$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
} else {
	$content.='<div class="noitems_message">'.$this->pi_getLL('there_are_no_products_in_your_cart').'</div>';
	$content.='
	<div id="bottom-navigation"><a href="'.mslib_fe::typolink($this->shop_pid).'" class="msFrontButton continueState arrowRight arrowPosLeft proceed_to_shop"><span>'.$this->pi_getLL('go_to_catalog').'</span></a>
		<div class="cart"></div>
	</div>';
}
?>