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
} elseif ($this->conf['products_listing_tmpl']) {
	$template=$this->cObj->fileResource($this->conf['products_listing_tmpl']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/products_listing.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
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
foreach ($products as $current_product) {
	$output=array();
	$final_price=mslib_fe::final_products_price($current_product);
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
	if ($current_product['products_image']) {
		$output['image']='<img src="'.mslib_befe::getImagePath($current_product['products_image'], 'products', $this->imageWidth).'" alt="'.htmlspecialchars($current_product['products_name']).'" />';
	} else {
		$output['image']='<div class="no_image"></div>';
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
		$output['products_price'].='<div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="specials_price">'.mslib_fe::amount2Cents($final_price).'</div>';
	} else {
		$output['products_price'].='<div class="price">'.mslib_fe::amount2Cents($final_price).'</div>';
	}
	if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
		$output['admin_icons']='<div class="admin_menu">
		<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$current_product['categories_id'].'&pid='.$current_product['products_id'].'&action=edit_product',1).'" class="admin_menu_edit"></a>
		<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$current_product['categories_id'].'&pid='.$current_product['products_id'].'&action=delete_product',1).'" class="admin_menu_remove" title="Remove"></a>
		</div>';
	}
	$markerArray=array();
	$markerArray['ADMIN_ICONS']=$output['admin_icons'];
	$markerArray['PRODUCTS_ID']=$current_product['products_id'];
	$markerArray['ITEM_CLASS']='';

	if (($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) and !$current_product['products_status'] and !$this->ms['MODULES']['FLAT_DATABASE']) {
		$markerArray['ITEM_CLASS']='disabled_product';
	}
	$markerArray['PRODUCTS_NAME']=$current_product['products_name'];
	$markerArray['PRODUCTS_MODEL']=$current_product['products_model'];
	$markerArray['PRODUCTS_DESCRIPTION']=$current_product['products_description'];
	$markerArray['PRODUCTS_SHORTDESCRIPTION']=$current_product['products_shortdescription'];
	$markerArray['PRODUCTS_DETAIL_PAGE_LINK']=$output['link'];
	$markerArray['CATEGORIES_NAME']=$current_product['categories_name'];
	$markerArray['CATEGORIES_NAME_PAGE_LINK']=$output['catlink'];
	$markerArray['PRODUCTS_IMAGE']=$output['image'];
	$markerArray['PRODUCTS_PRICE']=$output['products_price'];
	$markerArray['PRODUCTS_SKU']=$current_product['sku_code'];
	$markerArray['PRODUCTS_EAN']=$current_product['ean_code'];
	// STOCK INDICATOR
	$product_qty=$product['products_quantity'];
	if ($this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN']!='no') {
		switch ($this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN']) {
			case 'yes_with_image':
				if ($current_product['products_quantity']) {
					$product_qty='Voorraad: <img src="'.t3lib_extMgm::siteRelPath('multishop').'templates/images/icons/status_green.png" alt="'.htmlspecialchars($this->pi_getLL('in_stock')).'" />';
				} else {
					$product_qty='Voorraad: <img src="'.t3lib_extMgm::siteRelPath('multishop').'templates/images/icons/status_red.png" alt="'.htmlspecialchars($this->pi_getLL('not_in_stock')).'" />';
				}
				break;
			case 'yes_without_image':
				if ($current_product['products_quantity']) {
					$product_qty=$this->pi_getLL('admin_yes');
				} else {
					$product_qty=$this->pi_getLL('admin_no');
				}
				break;
		}
	}
	$markerArray['PRODUCTS_STOCK']=$product_qty;
	// STOCK INDICATOR EOF
	if (mslib_fe::ProductHasAttributes($current_product['products_id'])) {
		$button_submit='<a href="'.$link.'" class="ajax_link"><input name="Submit" type="submit" value="'.$this->pi_getLL('add_to_basket').'"/></a>';
	} else {
		$button_submit='<input name="Submit" type="submit" value="'.$this->pi_getLL('add_to_basket').'"/>';
	}
	$markerArray['PRODUCTS_ADD_TO_CART_BUTTON']='
		<div class="msFrontAddToCartButton">
			<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=shopping_cart&products_id='.$current_product['products_id']).'" method="post">
				<input type="hidden" name="quantity" value="1" />
				<input type="hidden" name="products_id" value="'.$current_product['products_id'].'" />
				'.$button_submit.'
			</form>
		</div>
	';
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingRecordHook'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'product'=>&$current_product,
			'output'=>&$output,
			'products_compare'=>&$products_compare
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingRecordHook'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom hook that can be controlled by third-party plugin eof		
	$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
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
$subpartArray['###CURRENT_CATEGORIES_NAME###']=trim($current['categories_name']);
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
		'current'=>&$current
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingPagePostHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
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
		var result = jQuery("#product_listing").sortable({
			cursor:     "move", 
			//axis:       "y", 
			update: function(e, ui) { 
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=product&catid='.$current_product['categories_id']).'";
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
?>