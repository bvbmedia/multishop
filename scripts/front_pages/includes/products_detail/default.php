<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$output=array();
$js_detail_page_triggers=array();
if ($this->ADMIN_USER) {
	$include_disabled_products=1;
} else {
	$include_disabled_products=0;
}
$product=mslib_fe::getProduct($this->get['products_id'], $this->get['categories_id'], '', $include_disabled_products);
if (!$product['products_id']) {
	header('HTTP/1.0 404 Not Found');
	$output_array['http_header']='HTTP/1.0 404 Not Found';
	$content='<div class="main-title"><h1>The product is not existing</h1></div>';
} else {
	if ($this->conf['imageWidth']) {
		$this->imageWidth=$this->conf['imageWidth'];
	}
	if (!$this->imageWidth) {
		$this->imageWidth='300';
	}
	if ($this->conf['imageWidthExtraImages']) {
		$this->imageWidthExtraImages=$this->conf['imageWidthExtraImages'];
	}
	if (!$this->imageWidthExtraImages) {
		$this->imageWidthExtraImages='50';
	}
	if ($product['minimum_quantity']>0) {
		$qty=$product['minimum_quantity'];
	} else {
		$qty=1;
	}
	if (!$this->conf['disableMetatags']) {
		// meta tags
		if ($product['products_meta_title']) {
			$this->ms['title']=$product['products_meta_title'];
		} else {
			$this->ms['title']=$product['products_name'];
		}
		$output_array['meta']['title']='<title>'.htmlspecialchars($this->ms['title']).$this->ms['MODULES']['PAGE_TITLE_DELIMETER'].$this->ms['MODULES']['STORE_NAME'].'</title>';
		if ($product['products_meta_description']) {
			$this->ms['description']=$product['products_meta_description'];
		} else {
			if ($product['products_shortdescription']) {
				$this->ms['description']=$product['products_shortdescription'];
			} else {
				$this->ms['description']='';
			}
		}
		//Product information: '.$product['products_name'].'. Order now!
		if ($this->ms['description']) {
			$output_array['meta']['description']='<meta name="description" content="'.htmlspecialchars($this->ms['description']).'" />';
		}
		if ($product['products_meta_keywords']) {
			$output_array['meta']['keywords']='<meta name="keywords" content="'.htmlspecialchars($product['products_meta_keywords']).'" />';
		}
		// meta tags eof
	}
	// facebook image and open graph
	$where='';
	if ($product['categories_id']) {
		// get all cats to generate multilevel fake url
		$level=0;
		$cats=mslib_fe::Crumbar($product['categories_id']);
		$cats=array_reverse($cats);
		$where='';
		if (count($cats)>0) {
			foreach ($cats as $cat) {
				$where.="categories_id[".$level."]=".$cat['id']."&";
				$level++;
			}
			$where=substr($where, 0, (strlen($where)-1));
			$where.='&';
		}
		// get all cats to generate multilevel fake url eof
	}
	$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
	if ($product['products_image']) {
		$output_array['meta']['image_src']='<link rel="image_src" href="'.$this->FULL_HTTP_URL.mslib_befe::getImagePath($product['products_image'], 'products', '300').'" />
		<meta property="og:image" content="'.$this->FULL_HTTP_URL.mslib_befe::getImagePath($product['products_image'], 'products', '300').'" />';
	}
	$output_array['meta'][]='<meta property="og:title" content="'.htmlspecialchars($product['products_name']).'" />
	<meta property="og:type" content="product" />
	'.($product['products_date_added'] ? '<meta property="article:published_time" content="'.date("Y-m-d", $product['products_date_added']).'" />' : '').'
	'.($product['products_date_modified'] ? '<meta property="article:modified_time" content="'.date("Y-m-d", $product['products_date_modified']).'" />' : '').'
	<meta property="og:url" content="'.$this->FULL_HTTP_URL.$link.'" />';
	// facebook image and open graph eof
	// putting the product vars in an array which will be marked and replaced in dynamic tmpl file
	// products pagination module
	if ($this->ms['MODULES']['PRODUCTS_DETAIL_PAGE_PAGINATION']) {
		// get previous / next record
		$pagination_items=mslib_fe::getNextPreviousProduct($product['products_id'], $product['categories_id']);
		$pagination.='<div id="products_detail_pagination">';
		if ($pagination_items['previous_item']['link']) {
			$pagination.='<div class="pagination_previous"><a href="'.$pagination_items['previous_item']['link'].'">'.$this->pi_getLL('previous').'</a></div>';
		} else {
			$pagination.='<div class="pagination_previous_disabled"><span>'.$this->pi_getLL('previous').'</span></div>';
		}
		if ($pagination_items['next_item']['link']) {
			$pagination.='<div class="pagination_next"><a href="'.$pagination_items['next_item']['link'].'">'.$this->pi_getLL('next').'</a></div>';
		} else {
			$pagination.='<div class="pagination_next_disabled"><span>'.$this->pi_getLL('next').'</span></div>';
		}
		$pagination.='</div>';
		$output['pagination']=$pagination;
	}
	// products pagination module eof
	$output['products_name'].=$product['products_name'];
	if ($this->ROOTADMIN_USER || ($this->ADMIN_USER && $this->CATALOGADMIN_USER)) {
		$output['products_name'].='<div class="admin_menu"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$product['categories_id'].'&pid='.$product['products_id'].'&action=edit_product', 1).'" class="admin_menu_edit">Edit</a> <a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$product['categories_id'].'&pid='.$product['products_id'].'&action=delete_product', 1).'" class="admin_menu_remove" title="Remove"></a></div>';
	}
	$final_price=mslib_fe::final_products_price($product);
	if ($product['tax_id'] && $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT']) {
		$tax=mslib_fe::getTaxById($product['tax_id']);
		if ($tax) {
			if ($product['staffel_price']>0) {
				$price_excl_vat=(mslib_fe::calculateStaffelPrice($product['staffel_price'], $qty)/$qty);
			} else {
				$price_excl_vat=$product['final_price'];
			}
			$sub_content.='<div class="price_excluding_vat">'.$this->pi_getLL('excluding_vat').' '.mslib_fe::amount2Cents($price_excl_vat).'</div>';
		}
	}
	$staffel_price_hid='';
	if ($product['staffel_price'] && $this->ms['MODULES']['STAFFEL_PRICE_MODULE']) {
		$staffel_price_hid='<input type="hidden" name="staffel_price" id="staffel_price" value="'.$product['staffel_price'].'" readonly/>';
	}
	$output['products_price']='<div class="price_div">';
	if ($product['products_price']<>$product['final_price']) {
		if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] && ($product['tax_rate'] && $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
			$old_price=$product['products_price']*(1+$product['tax_rate']);
		} else {
			$old_price=$product['products_price'];
		}
		if ($old_price) {
			$output['products_price'].='<div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div>';
		}
		$output['products_price'].='<input type="hidden" name="price_hid" id="price_default" value="'.$final_price.'"/>
		'.$staffel_price_hid.'
		<div class="specials_price">'.mslib_fe::amount2Cents($final_price).'</div>';
	} else {
		$output['products_price'].='<input type="hidden" name="price_hid" id="price_default" value="'.$final_price.'"/>
	  	<input type="hidden" name="price" id="price" value="'.$final_price.'" readonly/>
		'.$staffel_price_hid.'
	  	<div class="specials_price">'.mslib_fe::amount2Cents($final_price).'</div>';
	}
	$output['products_price'].=$sub_content.'</div>';
	// show selectbox by products multiplication or show default input
	if ($this->get['tx_multishop_pi1']['cart_item']) {
		$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
		$qty=$cart['products'][$this->get['tx_multishop_pi1']['cart_item']]['qty'];
	}
	$quantity_html='';
	if ($product['maximum_quantity']>0 || (is_numeric($product['products_multiplication']) && $product['products_multiplication']>0)) {
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
		$quantity_html.='<select name="quantity" id="quantity">';
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
		for ($i=0; $i<$steps; $i++) {
			if ($product['products_multiplication']) {
				$item=$product['products_multiplication'];
			} else {
				if ($i) {
					$item=1;
				}
			}
			$quantity_html.='<option value="'.$count.'"'.($qty==$count ? ' selected' : '').'>'.$count.'</option>';
			$count=($count+$item);
		}
		$quantity_html.='</select>';
	} else {
		$quantity_html.='<div class="quantity buttons_added" style=""><input type="button" value="-" class="qty_minus"><input type="text" name="quantity" size="5" id="quantity" value="'.$qty.'" /><input type="button" value="+" class="qty_plus"></div>';
	}
	// show selectbox by products multiplication or show default input eof
	$output['quantity']='
	<div class="quantity">
		<label>'.$this->pi_getLL('quantity').'</label>
		'.$quantity_html.'
	</div>';
	$output['back_button']='<a href="#" onClick="history.back();return false;" class="back_button msFrontButton backState arrowLeft arrowPosLeft"><span>'.$this->pi_getLL('back').'</span></a>';
	$product_qty=$product['products_quantity'];
	if ($this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN']!='no') {
		switch ($this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN']) {
			case 'yes_with_image':
				if ($product_qty) {
					$product_qty='<div class="products_stock"><span class="stock_label">'.$this->pi_getLL('stock').':</span><img src="'.t3lib_extMgm::siteRelPath($this->extKey).'templates/images/icons/status_green.png" alt="'.htmlspecialchars($this->pi_getLL('in_stock')).'" /></div>';
				} else {
					$product_qty='<div class="products_stock"><span class="stock_label">'.$this->pi_getLL('stock').':</span><img src="'.t3lib_extMgm::siteRelPath($this->extKey).'templates/images/icons/status_red.png" alt="'.htmlspecialchars($this->pi_getLL('not_in_stock')).'" /></div>';
				}
				break;
			case 'yes_without_image':
				if ($product_qty) {
					$product_qty='<div class="products_stock"><span class="stock_label">'.$this->pi_getLL('stock').':</span><span class="stock_value">'.$this->pi_getLL('admin_yes').'</span></div>';
				} else {
					$product_qty='<div class="products_stock"><span class="stock_label">'.$this->pi_getLL('stock').':</span><span class="stock_value">'.$this->pi_getLL('admin_no').'</span></div>';
				}
				break;
		}
	}
	$output['products_quantity']=$product_qty;
	$output['products_category']='Category: '.$product['categories_name'];
	$output['products_relatives']=mslib_fe::getProductRelativesBox($product);
	$output['customers_also_bought']=mslib_fe::getProductRelativesBox($product, 'customers_also_bought');
	$output['products_description']=$product['products_description'];
	$output['products_extra_description']=$product['products_extra_description'];
	$output['products_image']='<div class="image">';
	if ($product['products_image']) {
		$image='<a id="thumb_0" rel="'.$this->conf['jQueryPopup_rel'].'" class="'.$this->conf['jQueryPopup_rel'].'" href="'.mslib_befe::getImagePath($product['products_image'], 'products', 'normal').'"><img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'"></a>';
	} else {
		$image='<div class="no_image"></div>';
	}
	$output['products_image'].=$image.'</div>';
	$tmpoutput='';
	for ($i=1; $i<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $i++) {
		if ($product['products_image'.$i]) {
			$tmpoutput.='<li>';
			$tmpoutput.='<a id="thumb_'.$i.'" rel="'.$this->conf['jQueryPopup_rel'].'" class="'.$this->conf['jQueryPopup_rel'].'" href="'.mslib_befe::getImagePath($product['products_image'.$i], 'products', 'normal').'"><img src="'.mslib_befe::getImagePath($product['products_image'.$i], 'products', $this->imageWidthExtraImages).'"></a>';
			$tmpoutput.='</li>';
		}
	}
	if ($tmpoutput) {
		$output['products_image_more'].='<div class="more_product_images"><ul>'.$tmpoutput.'</ul></div>';
	}
	// loading the attributes
	$output['product_attributes']=mslib_fe::showAttributes($product['products_id'], $product['tax_rate']);
	// loading the attributes eof
	// add to basket
	if (($this->ROOTADMIN_USER || ($this->ADMIN_USER && $this->CATALOGADMIN_USER)) && !$product['products_status'] && !$this->ms['MODULES']['FLAT_DATABASE']) {
		$order_now_button.='<input id="multishop_add_to_cart" name="Submit" type="button" value="'.htmlspecialchars($this->pi_getLL('disabled_product', 'disabled product')).'" />';
	} else {
		if ($product_qty<1) {
			if ($this->ms['MODULES']['ALLOW_ORDER_OUT_OF_STOCK_PRODUCT']) {
				$order_now_button.='<input id="multishop_add_to_cart" name="Submit" type="submit" value="'.htmlspecialchars($this->pi_getLL('add_to_basket')).'" />';
			} else {
				$order_now_button.='<input id="multishop_add_to_cart" name="Submit" type="button" value="'.htmlspecialchars($this->pi_getLL('disabled_product', 'disabled product')).'" />';
			}
		} else {
			$order_now_button.='<input id="multishop_add_to_cart" name="Submit" type="submit" value="'.htmlspecialchars($this->pi_getLL('add_to_basket')).'" />';
		}
	}
	$output['add_to_cart_button'].='<span class="msFrontButton continueState arrowRight arrowPosLeft"><input name="products_id" id="products_id" type="hidden" value="'.$product['products_id'].'" />'.$order_now_button.'</span>';
	// add to basket eof
	// now parse all the objects in the tmpl file
	if ($this->conf['product_detail_tmpl_path']) {
		$template=$this->cObj->fileResource($this->conf['product_detail_tmpl_path']);
	} else {
		$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/products_detail.tmpl');
	}
	$markerArray['###CONTENT###']=$output['content'];
	$markerArray['###PAGINATION###']=$output['pagination'];
	$markerArray['###STOCK###']=$output['products_quantity'];
	$markerArray['###PRODUCTS_NAME###']=$output['products_name'];
	$markerArray['###PRODUCTS_SHORT_DESCRIPTION###']=$output['PRODUCTS_SHORT_DESCRIPTION'];
	$markerArray['###PRODUCTS_DESCRIPTION###']=$output['products_description'];
	$markerArray['###PRODUCTS_EXTRA_DESCRIPTION###']=$output['products_extra_description'];
	$markerArray['###PRODUCTS_CATEGORY###']=$output['products_category'];
	$markerArray['###PRODUCTS_ATTRIBUTES###']=$output['product_attributes'];
	$markerArray['###PRODUCTS_RELATIVES###']=$output['products_relatives'];
	$markerArray['###PRODUCTS_DELIVERY_TIME###']=$product['delivery_time'];
	$markerArray['###PRODUCTS_MODEL###']=$product['products_model'];
	$markerArray['###PRODUCTS_IMAGE###']=$output['products_image'];
	$markerArray['###PRODUCTS_IMAGE_MORE###']=$output['products_image_more'];
	$markerArray['###PRODUCTS_PRICE###']=$output['products_price'];
	$markerArray['###PRODUCTS_SKU###']=$product['sku_code'];
	$markerArray['###PRODUCTS_EAN###']=$product['ean_code'];
	$markerArray['###PRODUCTS_SPECIAL_PRICE###']=$output['special_price'];
	$markerArray['###OTHER_CUSTOMERS_BOUGHT###']=$output['customers_also_bought'];
	// new
	$markerArray['###QUANTITY###']=$output['quantity'];
	$markerArray['###BACK_BUTTON###']=$output['back_button'];
	$markerArray['###ADD_TO_CART_BUTTON###']=$output['add_to_cart_button'];
	$markerArray['###PRODUCTS_META_DESCRIPTION###']=$product['products_meta_description'];
	$markerArray['###PRODUCTS_META_KEYWORDS###']=$product['products_meta_keywords'];
	$markerArray['###PRODUCTS_META_TITLE###']=$product['products_meta_title'];
	$markerArray['###PRODUCTS_URL###']=$product['products_url'];
	$js_detail_page_triggers[]='
		if ($("#quantity").val() == "") {
			$("#quantity").val("1");
		}
		$(".qty_minus").click(function() {
			var qty = parseInt($("#quantity").val());
			if (qty > 1) {
				var new_val = parseInt(qty - 1);
				$("#quantity").val(new_val);
			}
		});
		$(".qty_plus").click(function() {
			var qty = parseInt($("#quantity").val());
			var new_val = parseInt(qty + 1);
			$("#quantity").val(new_val);
		});
	';
	// shipping cost popup
	if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_DETAIL_PAGE']) {
		$markerArray['###PRODUCTS_SPECIAL_PRICE###'].='<div class="shipping_cost_popup_link_wrapper"><a href="#" id="show_shipping_cost_table"><span>'.$this->pi_getLL('shipping_costs').'</span></a></div>';
		$js_detail_page_triggers[]='
			$(document).on("click", "#show_shipping_cost_table", function(e) {
				e.preventDefault();
				jQuery.ajax({
					url: \''.mslib_fe::typolink('', 'type=2002&tx_multishop_pi1[page_section]=get_product_shippingcost_overview').'\',
					data: \'tx_multishop_pi1[pid]=\' + $("#products_id").val() + \'&tx_multishop_pi1[qty]=\' + $("#quantity").val(),
					type: \'post\',
					dataType: \'json\',
					success: function (j) {
						if (j) {
							var shipping_cost_popup=\'<div class="product_shippingcost_popup_wrapper">\';
							shipping_cost_popup+=\'<div class="product_shippingcost_popup_header">'.$this->pi_getLL('product_shipping_and_handling_cost_overview').'</div>\';
							shipping_cost_popup+=\'<div class="product_shippingcost_popup_table_wrapper">\';
							shipping_cost_popup+=\'<table id="product_shippingcost_popup_table">\';
							shipping_cost_popup+=\'<tr>\';
							shipping_cost_popup+=\'<td colspan="3" class="product_shippingcost_popup_table_product_name">\' + j.products_name + \'</td>\';
							shipping_cost_popup+=\'</tr>\';
							shipping_cost_popup+=\'<tr>\';
							shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col">'.$this->pi_getLL('deliver_to').'</td>\';
							shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_center_col">'.$this->pi_getLL('shipping_and_handling_cost_overview').'</td>\';
							shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_right_col">'.$this->pi_getLL('deliver_by').'</td>\';
							shipping_cost_popup+=\'</tr>\';
							$.each(j.shipping_costs_display, function(country_iso_nr, shipping_cost) {
								shipping_cost_popup+=\'<tr>\';
								shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col">\' + j.deliver_to[country_iso_nr] + \'</td>\';
								shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_center_col">\' + shipping_cost + \'</td>\';
								shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_right_col">\' + j.deliver_by[country_iso_nr] + \'</td>\';
								shipping_cost_popup+=\'</tr>\';
							});
							shipping_cost_popup+=\'</table>\';
							shipping_cost_popup+=\'</div>\';
							shipping_cost_popup+=\'</div>\';
							msDialog("'.$this->pi_getLL('shipping_costs').'", shipping_cost_popup, 650);
						}
					}
				});
			});
		';
	}
	$plugins_extra_content=array();
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_detail.php']['productsDetailsPagePostHook'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'product'=>&$product,
			'output'=>&$output,
			'plugins_extra_content'=>&$plugins_extra_content
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_detail.php']['productsDetailsPagePostHook'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	$markerArray['###PRODUCT_DETAILS_PLUGIN_EXTRA_CONTENT###']='';
	if (count($plugins_extra_content)) {
		$plugin_extra_content=implode("\n", $plugins_extra_content);
		$markerArray['###PRODUCT_DETAILS_PLUGIN_EXTRA_CONTENT###']=$plugin_extra_content;
	}
	if (count($js_detail_page_triggers)) {
		$output_array['meta']['details_page_js']='
			<script type="text/javascript">
			jQuery(document).ready(function($) {
			'.implode("\n", $js_detail_page_triggers).'
			});
			</script>
		';
	}
	// custom hook that can be controlled by third-party plugin eof
	$content.=$output['top_content'].'<form action="'.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart&products_id='.$product['products_id']).'" method="post" name="shopping_cart" id="add_to_shopping_cart_form" enctype="multipart/form-data"><div id="products_detail">'.$this->cObj->substituteMarkerArray($template, $markerArray).'</div><input name="tx_multishop_pi1[cart_item]" type="hidden" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['cart_item']).'" /></form>';
}
?>