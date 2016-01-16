<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if (!$this->imageWidth) {
	$this->imageWidth='100';
}
// now parse all the objects in the tmpl file
if ($this->conf['products_listing_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['products_listing_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/products_listing.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['header_data']=$this->cObj->getSubpart($template, '###HEADER_DATA###');
if ($subparts['header_data']) {
	$output_array['meta']['products_listing_header_data']=$subparts['header_data'];
}
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['ITEM']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
$subparts['ITEM_1']=$this->cObj->getSubpart($subparts['template'], '###ITEM_1###');
$subparts['ITEM_2']=$this->cObj->getSubpart($subparts['template'], '###ITEM_2###');
if ($subparts['ITEM_1']) {
	$oddEvenMarker=1;
}
if (!$this->ms['MODULES']['PRODUCTS_LISTING_DISPLAY_PAGINATION_FORM'] && !$this->ms['MODULES']['PRODUCTS_LISTING_DISPLAY_ORDERBY_FORM']) {
	// clear coupon html
	// because the DISCOUNT_MODULE_WRAPPER is inside the CART_FOOTER wrapper we have to substitute it on the footer
	$subHeaderparts=array();
	$subHeaderparts['listing_sorting']=$this->cObj->getSubpart($subparts['template'], '###LISTING_SORTING###');
	$subpartHeader=array();
	$subpartHeader['###LISTING_SORTING###']='';
	$subparts['template']=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartHeader);
}
$contentItem='';
$itemCounter=0;
if (is_array($products) && count($products)) {
	foreach ($products as $current_product) {
		$itemCounter++;
		$markerKey='ITEM';
		if ($oddEvenMarker) {
			if ($subparts['ITEM_'.$itemCounter]) {
				$markerKey='ITEM_'.$itemCounter;
			} else {
				$markerKey='ITEM_1';
				$itemCounter=1;
			}
		}
		$output=array();
		$where='';
		if ($current_product['categories_id']) {
			// get all cats to generate multilevel fake url
			$level=0;
			$cats=mslib_fe::Crumbar($current_product['categories_id']);
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
		$output['link']=mslib_fe::typolink($this->conf['products_detail_page_pid'], $where.'&products_id='.$current_product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
		$output['catlink']=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
		$formats=array();
		$formats[]='100';
		$formats[]='200';
		$formats[]='300';
		foreach ($formats as $format) {
			if ($current_product['products_image']) {
				$key='image_'.$format;
				if ($this->imageWidth==$format) {
					$key='image';
				}
				$imagePath=mslib_befe::getImagePath($current_product['products_image'], 'products', $format);
				$output[$key]='<img src="'.$imagePath.'" alt="'.htmlspecialchars($current_product['products_name']).'" />';
			} else {
				$output[$key]='<div class="no_image"></div>';
			}
		}
		$final_price=mslib_fe::final_products_price($current_product);
		if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($current_product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
			$old_price=$current_product['products_price']*(1+$current_product['tax_rate']);
		} else {
			$old_price=$current_product['products_price'];
		}
		if ($current_product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT']) {
			$output['products_price'].='<div class="price_excluding_vat">'.$this->pi_getLL('excluding_vat').' '.mslib_fe::amount2Cents($current_product['final_price']).'</div>';
		}
		if ($current_product['products_price']<>$current_product['final_price']) {
			if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($current_product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
				$old_price=$current_product['products_price']*(1+$current_product['tax_rate']);
			} else {
				$old_price=$current_product['products_price'];
			}
			$output['products_price'].='<div class="old_price_wrapper"><div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div></div><div class="specials_price_wrapper"><div class="specials_price">'.mslib_fe::amount2Cents($final_price).'</div></div>';
		} else {
			$output['products_price'].='<div class="price">'.mslib_fe::amount2Cents($final_price).'</div>';
		}
		/*$current_product['products_price_including_vat']=$current_product['products_price'];
		$current_product['final_price_including_vat']=$current_product['final_price'];
		if ($current_product['tax_rate']) {
			if ($current_product['products_price']) {
				$current_product['products_price_including_vat']=mslib_fe::final_products_price($current_product,1,1,0,'products_price');
			}
			if ($current_product['final_price']) {
				$current_product['final_price_including_vat']=mslib_fe::final_products_price($current_product,1,1,0,'final_price');
			}
		}
		if ($this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT']) {
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$output['products_price'].='<div class="price_excluding_vat">'.$this->pi_getLL('excluding_vat').' '.mslib_fe::amount2Cents($current_product['final_price']).'</div>';
			} else {
				$output['products_price'].='<div class="price_including_vat">'.$this->pi_getLL('including_vat').' '.mslib_fe::amount2Cents($current_product['final_price_including_vat']).'</div>';
			}
		}
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			// OVERWRITE INCLUDING VAT PRICES SO THEY ARE PRINTED CORRECTLY
			$current_product['products_price']=$current_product['products_price_including_vat'];
			$current_product['final_price']=$current_product['final_price_including_vat'];
		}
		if (round($current_product['products_price'],2)<>round($current_product['final_price'],2)) {
			$current_product['old_price']=$current_product['products_price'];
		}
		if (round($current_product['old_price'],2) > 0 && round($current_product['old_price'],2)<>round($current_product['final_price'],2)) {
			$output['products_price'].='<div class="old_price">'.mslib_fe::amount2Cents($current_product['old_price']).'</div><div class="specials_price">'.mslib_fe::amount2Cents($current_product['final_price']).'</div>';
		} else {
			$output['products_price'].='<div class="price">'.mslib_fe::amount2Cents($current_product['final_price']).'</div>';
		}*/
		if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
			$output['admin_icons']='<div class="admin_menu">
		<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=edit_product&cid='.$current_product['categories_id'].'&pid='.$current_product['products_id'].'&action=edit_product', 1).'" class="admin_menu_edit"><i class="fa fa-pencil"></i></a>
		<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=delete_product&cid='.$current_product['categories_id'].'&pid='.$current_product['products_id'].'&action=delete_product', 1).'" class="admin_menu_remove" title="Remove"><i class="fa fa-trash-o"></i></a>
		</div>';
		}
		$markerArray=array();
		$markerArray['ADMIN_ICONS']=$output['admin_icons'];
		$markerArray['PRODUCTS_ID']=$current_product['products_id'];
		$markerArray['ITEM_CLASS']='';
		if (($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) and !$current_product['products_status'] and !$this->ms['MODULES']['FLAT_DATABASE']) {
			$markerArray['ITEM_CLASS']='disabled_product';
		}
		$markerArray['ITEM_COUNTER']=$itemCounter;
		$markerArray['PRODUCTS_NAME']=$current_product['products_name'];
		$markerArray['PRODUCTS_MODEL']=$current_product['products_model'];
		$markerArray['PRODUCTS_DESCRIPTION']=$current_product['products_description'];
		$markerArray['PRODUCTS_SHORTDESCRIPTION']=$current_product['products_shortdescription'];
		$markerArray['PRODUCTS_DETAIL_PAGE_LINK']=$output['link'];
		$markerArray['CATEGORIES_NAME']=$current_product['categories_name'];
		$markerArray['CATEGORIES_NAME_PAGE_LINK']=$output['catlink'];
		$markerArray['PRODUCTS_IMAGE']=$output['image'];
		$markerArray['PRODUCTS_IMAGE_200']=$output['image_200'];
		$markerArray['PRODUCTS_IMAGE_300']=$output['image_300'];
		$markerArray['PRODUCTS_PRICE']=$output['products_price'];
		$markerArray['PRODUCTS_SKU']=$current_product['sku_code'];
		$markerArray['PRODUCTS_EAN']=$current_product['ean_code'];
		$markerArray['PRODUCTS_URL']=$current_product['products_url'];
		$markerArray['ORDER_UNIT_NAME']=$current_product['order_unit_name'];
		$markerArray['OLD_PRICE']=mslib_fe::amount2Cents($current_product['old_price']);
		$markerArray['FINAL_PRICE']=mslib_fe::amount2Cents($current_product['final_price']);
		$markerArray['OLD_PRICE_PLAIN']=number_format($current_product['old_price'], 2, ',', '.');
		$markerArray['FINAL_PRICE_PLAIN']=number_format($current_product['final_price'], 2, ',', '.');
		// STOCK INDICATOR
		$product_qty=$current_product['products_quantity'];
		if ($this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN']!='no') {
			switch ($this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN']) {
				case 'yes_with_image':
					if ($product_qty) {
						$product_qty='<div class="products_stock"><span class="stock_label">'.$this->pi_getLL('stock').':</span><img src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/images/icons/status_green.png" alt="'.htmlspecialchars($this->pi_getLL('in_stock')).'" /></div>';
					} else {
						$product_qty='<div class="products_stock"><span class="stock_label">'.$this->pi_getLL('stock').':</span><img src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/images/icons/status_red.png" alt="'.htmlspecialchars($this->pi_getLL('not_in_stock')).'" /></div>';
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
		$markerArray['PRODUCTS_STOCK']=$product_qty;
		// STOCK INDICATOR EOF
		if (mslib_fe::ProductHasAttributes($current_product['products_id'])) {
			$markerArray['PRODUCTS_ADD_TO_CART_BUTTON_LINK']=$output['link'];
			$button_submit='<a href="'.$link.'" class="ajax_link"><input name="Submit" type="submit" value="'.$this->pi_getLL('add_to_basket').'"/></a>';
		} else {
			$markerArray['PRODUCTS_ADD_TO_CART_BUTTON_LINK']=mslib_fe::typolink($this->shop_pid, '&tx_multishop_pi1[page_section]=shopping_cart&tx_multishop_pi1[action]=add_to_cart&products_id='.$current_product['products_id']);
			$button_submit='<input name="Submit" type="submit" value="'.$this->pi_getLL('add_to_basket').'"/>';
		}
		$qty=1;
		if ($current_product['minimum_quantity']>0) {
			$qty=round($current_product['minimum_quantity'], 2);
		}
		if ($current_product['products_multiplication']>0) {
			$qty=round($current_product['products_multiplication'], 2);
		}
		$markerArray['PRODUCTS_ADD_TO_CART_BUTTON']='
		<div class="msFrontAddToCartButton">
			<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=shopping_cart&products_id='.$current_product['products_id']).'" method="post">
				<input type="hidden" name="quantity" value="'.$qty.'" />
				<input type="hidden" name="products_id" value="'.$current_product['products_id'].'" />
				'.$button_submit.'
			</form>
		</div>
	';
		// ADD TO CART BUTTON WITH QUANTITY FIELD
		$quantity_html='';
		//if ($current_product['maximum_quantity']>0 || (is_numeric($current_product['products_multiplication']) && $current_product['products_multiplication']>0)) {
		if ($current_product['maximum_quantity']>0) {
			if ($current_product['maximum_quantity']>0) {
				$ending_number=$current_product['maximum_quantity'];
			}
			if ($current_product['minimum_quantity']>0) {
				$start_number=$current_product['minimum_quantity'];
			} else {
				if ($current_product['products_multiplication']) {
					$start_number=$current_product['products_multiplication'];
				}
			}
			if (!$start_number) {
				$start_number=1;
			}
			$quantity_html.='<select name="quantity" id="quantity">';
			$count=0;
			$steps=10;
			if ($current_product['maximum_quantity'] && $current_product['products_multiplication']) {
				$steps=floor($current_product['maximum_quantity']/$current_product['products_multiplication']);
			} else {
				if ($current_product['maximum_quantity'] && !$current_product['products_multiplication']) {
					$steps=($ending_number-$start_number)+1;
				}
			}
			$count=$start_number;
			$products_multiplication=0;
			for ($i=0; $i<$steps; $i++) {
				if ($current_product['products_multiplication']) {
					$products_multiplication=$current_product['products_multiplication'];
				} else {
					if ($i) {
						$products_multiplication=1;
					}
				}
				$quantity_html.='<option value="'.$count.'"'.($qty==$count ? ' selected' : '').'>'.$count.'</option>';
				$count=($count+$products_multiplication);
			}
			$quantity_html.='</select>';
		} else {
			$quantity_html.='<div class="quantity buttons_added" style=""><input type="button" value="-" class="qty_minus"><input type="text" name="quantity" size="5" rel="'.$current_product['products_id'].'" data-step-size="'.($current_product['products_multiplication']!='0.00' ? $current_product['products_multiplication'] : '1').'" class="qtyInput" value="'.$qty.'" /><input type="button" value="+" class="qty_plus"></div>';
		}
		// show selectbox by products multiplication or show default input eof
		$markerArray['PRODUCTS_QUANTITY_INPUT_AND_ADD_TO_CART_BUTTON']='
		<div class="msFrontAddToCartButton">
			<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=shopping_cart&products_id='.$current_product['products_id']).'" method="post">
				<div class="quantity">
					<label>'.$this->pi_getLL('quantity').'</label>
					'.$quantity_html.'
				</div>
				<input type="hidden" name="products_id" value="'.$current_product['products_id'].'" />
				'.$button_submit.'
			</form>
		</div>
	';
		// ADD TO CART BUTTON WITH QUANTITY FIELD EOL
		$plugins_item_extra_content=array();
		// shipping cost popup
		if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
			$plugins_item_extra_content[]='<div class="shipping_cost_popup_link_wrapper"><a href="#" class="show_shipping_cost_table" class="btn btn-primary" data-toggle="modal" data-target="#shippingCostsModal" data-productid="'.$current_product['products_id'].'"><span>'.$this->pi_getLL('shipping_costs').'</span></a></div>';
		}
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingRecordHook'])) {
			$params=array(
				'markerArray'=>&$markerArray,
				'product'=>&$current_product,
				'output'=>&$output,
				'products_compare'=>&$products_compare,
				'plugins_item_extra_content'=>&$plugins_item_extra_content
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingRecordHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		$markerArray['PRODUCT_LISTING_ITEM_PLUGIN_EXTRA_CONTENT']='';
		if (count($plugins_item_extra_content)) {
			$markerArray['PRODUCT_LISTING_ITEM_PLUGIN_EXTRA_CONTENT']=implode("\n", $plugins_item_extra_content);
		}
		// custom hook that can be controlled by third-party plugin eof
		$contentItem.=$this->cObj->substituteMarkerArray($subparts[$markerKey], $markerArray, '###|###');
	}
}
// fill the row marker with the expanded rows
$subpartArray['###CURRENT_CATEGORIES_TOP_DESCRIPTION###']='';
if ($current['content']) {
	$subpartArray['###CURRENT_CATEGORIES_TOP_DESCRIPTION###']='<div class="categories_top_description">'.trim($current['content']).'</div>';
}
$subpartArray['###CURRENT_CATEGORIES_BOTTOM_DESCRIPTION###']='';
if ($current['content_footer']) {
	$subpartArray['###CURRENT_CATEGORIES_BOTTOM_DESCRIPTION###']='<div class="categories_bottom_description">'.trim($current['content_footer']).'</div>';
}
$subpartArray['###CURRENT_CATEGORIES_NAME###']='';
if (is_array($current) && $current['categories_name']) {
	$subpartArray['###CURRENT_CATEGORIES_NAME###']='<h1>'.trim($current['categories_name']).'</h1>';
}
$subpartArray['###ITEM_1###']='';
$subpartArray['###ITEM_2###']='';
$subpartArray['###ITEM###']=$contentItem;
$product_listing_form_content='';
if ($this->ms['MODULES']['PRODUCTS_LISTING_DISPLAY_PAGINATION_FORM']) {
	$limit_options=array();
	$limit_options[]=5;
	$limit_options[]=10;
	$limit_options[]=20;
	$limit_options[]=30;
	$limit_options[]=50;
	$limit_options[]=100;
	$product_listing_form_content.='<div class="listing_limit_selectbox">';
	$product_listing_form_content.='<label for="limitsb">'.$this->pi_getLL('products_per_page', 'Products per page').':</label>';
	$product_listing_form_content.='<select name="tx_multishop_pi1[limitsb]" id="limitsb" class="products_listing_filter">';
	if (!in_array($default_limit_page, $limit_options)) {
		$product_listing_form_content.='<option value="'.$default_limit_page.'">'.$default_limit_page.'</option>';
	}
	foreach ($limit_options as $limit_option) {
		if (isset($this->cookie['limitsb']) && !empty($this->cookie['limitsb']) && $limit_option==$this->cookie['limitsb']) {
			$product_listing_form_content.='<option value="'.$limit_option.'" selected="selected">'.$limit_option.'</option>';
		} else {
			if ($limit_option==$default_limit_page && !isset($this->cookie['limitsb']) && empty($this->cookie['limitsb'])) {
				$product_listing_form_content.='<option value="'.$limit_option.'" selected="selected">'.$limit_option.'</option>';
			} else {
				$product_listing_form_content.='<option value="'.$limit_option.'">'.$limit_option.'</option>';
			}
		}
	}
	$product_listing_form_content.='</select>';
	$product_listing_form_content.='</div>';
}
if ($this->ms['MODULES']['PRODUCTS_LISTING_DISPLAY_ORDERBY_FORM']) {
	$sortby_options=array();
	$sortby_options['manufacturers_asc']=$this->pi_getLL('sortby_options_label_manufacturers_asc', 'Manufacturers (asc)');
	$sortby_options['manufacturers_desc']=$this->pi_getLL('sortby_options_label_manufacturers_desc', 'Manufacturers (desc)');
	$sortby_options['best_selling_asc']=$this->pi_getLL('sortby_options_label_bestselling_asc', 'Best selling (asc)');
	$sortby_options['best_selling_desc']=$this->pi_getLL('sortby_options_label_bestselling_desc', 'Best selling (desc)');
	$sortby_options['price_asc']=$this->pi_getLL('sortby_options_label_price_asc', 'Price (asc)');
	$sortby_options['price_desc']=$this->pi_getLL('sortby_options_label_price_desc', 'Price (desc)');
	$sortby_options['new_asc']=$this->pi_getLL('sortby_options_label_new_asc', 'New (asc)');
	$sortby_options['new_desc']=$this->pi_getLL('sortby_options_label_new_desc', 'New (desc)');
	$product_listing_form_content.='<div class="listing_sortby_selectbox">';
	$product_listing_form_content.='<label for="sortbysb">'.$this->pi_getLL('sort_by', 'Sort by').':</label>';
	$product_listing_form_content.='<select name="tx_multishop_pi1[sortbysb]" id="sortbysb" class="products_listing_filter">';
	$product_listing_form_content.='<option value="">'.$this->pi_getLL('default').'</option>';
	foreach ($sortby_options as $sortby_key=>$sortby_label) {
		if ($sortby_key==$this->cookie['sortbysb']) {
			$product_listing_form_content.='<option value="'.$sortby_key.'" selected="selected">'.$sortby_label.'</option>';
		} else {
			$product_listing_form_content.='<option value="'.$sortby_key.'">'.$sortby_label.'</option>';
		}
	}
	$product_listing_form_content.='</select>';
	$product_listing_form_content.='</div>';
}
if (!empty($product_listing_form_content)) {
	$product_listing_form_content.='<input type="hidden" name="id" value="'.$this->get['id'].'">';
	if ($this->get['tx_multishop_pi1']['page_section']=='products_listing') {
		$product_listing_form_content.='<input type="hidden" name="categories_id" value="'.$this->get['categories_id'].'">';
	} else if ($this->get['tx_multishop_pi1']['page_section']=='manufacturers_products_listing') {
		$product_listing_form_content.='<input type="hidden" name="manufacturers_id" value="'.$this->get['manufacturers_id'].'">';
	}
	if ($this->get['tx_multishop_pi1']['page_section']=='products_search') {
		$product_listing_form_content.='<input type="hidden" name="skeyword" value="'.$this->get['skeyword'].'">';
		$product_listing_form_content.='<input type="hidden" name="Submit" value="Zoeken">';
	}
	$product_listing_form_content.='<input type="hidden" name="tx_multishop_pi1[page_section]" value="'.$this->get['tx_multishop_pi1']['page_section'].'">';
	if ($p>0) {
		if ($this->get['tx_multishop_pi1']['page_section']=='products_listing') {
			$product_listing_form_content.='<input type="hidden" name="p" value="'.$p.'">';
		}
		if ($this->get['tx_multishop_pi1']['page_section']=='products_search') {
			$product_listing_form_content.='<input type="hidden" name="page" value="'.$this->get['page'].'">';
		}
	}
	$product_listing_form_content.='<script type="text/javascript">
	  jQuery(document).ready(function($) {
			$(".products_listing_filter").change(function(){
				$("#sorting_products_listing").submit();
			});
	  });
	  </script>';
	// get all cats to generate multilevel fake url
	$level=0;
	$cats=mslib_fe::Crumbar($this->get['categories_id']);
	$cats=array_reverse($cats);
	$where='';
	if (count($cats)>0) {
		foreach ($cats as $item) {
			$where.="categories_id[".$level."]=".$item['id']."&";
			$level++;
		}
		$where=substr($where, 0, (strlen($where)-1));
	}
	// get all cats to generate multilevel fake url eof
	$form_action_url=mslib_fe::typolink($this->conf['products_listing_page_pid'], $where.'&tx_multishop_pi1[page_section]=products_listing');
	$subpartArray['###PRODUCTS_LISTING_FILTER_FORM_URL###']='';
	$subpartArray['###PRODUCTS_LISTING_FORM_CONTENT###']=$product_listing_form_content;
} else {
	$subpartArray['###PRODUCTS_LISTING_FILTER_FORM_URL###']='';
	$subpartArray['###PRODUCTS_LISTING_FORM_CONTENT###']='';
}
// completed the template expansion by replacing the "item" marker in the template
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingPagePostHook'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'current'=>&$current,
		'output_array'=>&$output_array
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingPagePostHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
// ADD OPACITY TO PRODUCTS THAT ARE TURNED OFF
$content.='
	<script type="text/javascript">
	  jQuery(document).ready(function($) {
		$(".disabled_product").css({ opacity: 0.6 });
		$(".disabled_product").hover(
		  function () {
			$(".disabled_product").css({ opacity: 1 });
		  },
		  function () {
			$(".disabled_product").css({ opacity: 0.6 });
		  }
		)
	  });
	</script>
';
$skippedTypes=array();
$skippedTypes[]='products_modified';
$skippedTypes[]='products_search';
$skippedTypes[]='products_new';
$skippedTypes[]='products_specials';
$skippedTypes[]='specials_listing_page';
if (!in_array($this->contentType, $skippedTypes) and ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER))) {
	$content.='
	<script type="text/javascript">
	  jQuery(document).ready(function($) {
		var result = jQuery(".product_listing").sortable({
			cursor:     "move",
			//axis:       "y",
			update: function(e, ui) {
				href = "'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=product&catid='.$current_product['categories_id']).'";
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
if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
	$content.='
	<div class="modal" id="shippingCostsModal" tabindex="-1" role="dialog" aria-labelledby="shippingCostModalTitle" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="shippingCostModalTitle">'.$this->pi_getLL('shipping_costs').'</h4>
		  </div>
		  <div class="modal-body"></div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
		  </div>
		</div>
	  </div>
	</div>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
	  	$(\'#shippingCostsModal\').modal({
			show:false,
			backdrop:false
		});
		$(\'#shippingCostsModal\').on(\'show.bs.modal\', function (event) {
			var button = $(event.relatedTarget) // Button that triggered the modal
  			var product_id = button.data(\'productid\') // Extract info from data-* attributes
			var modalBox = $(this);
			modalBox.find(\'.modal-body\').empty();
			if (modalBox.find(\'.modal-body\').html()==\'\') {
				modalBox.find(\'.modal-body\').html(\'<div class="text-center" id="loading_icon_wrapper"><img src="typo3conf/ext/multishop/templates/images/loading.gif" id="loading_icon" />&nbsp;Loading...</div>\');
				jQuery.ajax({
					url: \''.mslib_fe::typolink('', 'type=2002&tx_multishop_pi1[page_section]=get_product_shippingcost_overview').'\',
					data: \'tx_multishop_pi1[pid]=\' + product_id + \'&tx_multishop_pi1[qty]=\' + $("#quantity").val(),
					type: \'post\',
					dataType: \'json\',
					success: function (j) {
						if (j) {
							var shipping_cost_popup=\'<div class="product_shippingcost_popup_wrapper">\';
							shipping_cost_popup+=\'<div class="product_shippingcost_popup_header">'.$this->pi_getLL('product_shipping_and_handling_cost_overview').'</div>\';
							shipping_cost_popup+=\'<div class="product_shippingcost_popup_table_wrapper">\';
							shipping_cost_popup+=\'<table id="product_shippingcost_popup_table" class="table table-striped">\';
							shipping_cost_popup+=\'<tr>\';
							shipping_cost_popup+=\'<td colspan="3" class="product_shippingcost_popup_table_product_name">\' + j.products_name + \'</td>\';
							shipping_cost_popup+=\'</tr>\';
							shipping_cost_popup+=\'<tr>\';
							shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col">'.$this->pi_getLL('deliver_in').'</td>\';
							shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_center_col">'.$this->pi_getLL('shipping_and_handling_cost_overview').'</td>\';
							shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_right_col">'.$this->pi_getLL('deliver_by').'</td>\';
							shipping_cost_popup+=\'</tr>\';
							$.each(j.shipping_costs_display, function(shipping_method, shipping_data) {
								$.each(shipping_data, function(country_iso_nr, shipping_cost){
									shipping_cost_popup+=\'<tr>\';
									shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col">\' + j.deliver_to[shipping_method][country_iso_nr] + \'</td>\';
									shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_center_col">\' + shipping_cost + \'</td>\';
									shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_right_col">\' + j.deliver_by[shipping_method][country_iso_nr] + \'</td>\';
									shipping_cost_popup+=\'</tr>\';
								});
							});
							if (j.delivery_time!=\'e\') {
								shipping_cost_popup+=\'<tr>\';
								shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col"><strong>'.$this->pi_getLL('admin_delivery_time').'</strong></td>\';
								shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col" colspan="2">\' + j.delivery_time + \'</td>\';
								shipping_cost_popup+=\'</tr>\';
							}
							shipping_cost_popup+=\'</table>\';
							shipping_cost_popup+=\'</div>\';
							shipping_cost_popup+=\'</div>\';
							//modalBox.find(\'.modal-title\').html('.$this->pi_getLL('product_shipping_and_handling_cost_overview').');
							modalBox.find(\'.modal-body\').empty();
							modalBox.find(\'.modal-body\').html(shipping_cost_popup);
							//msDialog("'.$this->pi_getLL('shipping_costs').'", shipping_cost_popup, 650);
						}
					}
				});
			}
		});

		/*$(document).on("click", ".show_shipping_cost_table", function(e) {
			e.preventDefault();
			var pid=jQuery(this).attr("rel");
			jQuery.ajax({
				url: \''.mslib_fe::typolink('', 'type=2002&tx_multishop_pi1[page_section]=get_product_shippingcost_overview').'\',
				data: \'tx_multishop_pi1[pid]=\' + pid + \'&tx_multishop_pi1[qty]=1\',
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
						shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col">'.$this->pi_getLL('deliver_in').'</td>\';
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
		});*/
	});
	</script>
	';
}
?>