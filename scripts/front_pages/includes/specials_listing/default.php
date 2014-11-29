<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if (!$this->imageWidth) {
	$this->imageWidth='100';
}
// now parse all the objects in the tmpl file
if ($this->conf['specials_products_listing_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['specials_products_listing_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/specials_listing.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
//
$teller=0;
$specials_items='';
$specials_items_header='';
if (!$this->hideHeader) {
	$specials_items_header='<div class="main-heading"><h2>'.$this->pi_getLL('specials').'</h2></div>';
}
foreach ($products as $product) {
	if ($product['products_image']) {
		$image=mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth);
	} else {
		$image='';
	}
	$teller++;
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
	$tel++;
	if ($this->conf['disableFeFromCalculatingVatPrices']=='1') {
		$final_price=$product['final_price'];
		$old_price=$product['products_price'];
	} else {
		$final_price=mslib_fe::final_products_price($product);
		if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
			$old_price=$product['products_price']*(1+$product['tax_rate']);
		} else {
			$old_price=$product['products_price'];
		}
	}
	if (!$this->ajax_content) {
		if ($old_price) {
			$old_price_label=mslib_fe::amount2Cents($old_price);
		} else {
			$old_price_label='';
		}
		// jcarousel
		$specials_items.='{url: \''.$link.'\', image: \''.$image.'\', title: \''.htmlspecialchars(addslashes($product['products_name'])).'\', price: \''.$old_price_label.'\', specials_price: \''.mslib_fe::amount2Cents($final_price).'\'}';
		if ($teller<$total) {
			$specials_items.=',';
		}
		$specials_items.="\n";
	} else {
		if ($product['products_image']) {
			$image='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'" alt="'.htmlspecialchars($product['products_name']).'" />';
		} else {
			$image='<div class="no_image"></div>';
		}
		// normal
		$final_price=mslib_fe::final_products_price($product);
		$item_products_specials_price='';
		if ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT']) {
			$item_products_specials_price='<div class="price_excluding_vat">'.$this->pi_getLL('excluding_vat').' '.mslib_fe::amount2Cents($product['final_price']).'</div>';
		}
		$item_products_price='';
		if ($product['products_price']<>$product['final_price']) {
			if ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$old_price=$product['products_price']*(1+$product['tax_rate']);
			} else {
				$old_price=$product['products_price'];
			}
			$item_products_price='<div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="final_price">'.mslib_fe::amount2Cents($final_price).'</div>';
		} else {
			$item_products_price='<div class="final_price">'.mslib_fe::amount2Cents($final_price).'</div>';
		}
		$admin_menu='';
		if ($this->ADMIN_USER) {
			$admin_menu='<div class="admin_menu"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$product['products_id'].'&action=edit_product',1).'" class="admin_menu_edit"></a> <a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$product['products_id'].'&action=delete_product',1).'" class="admin_menu_remove" title="Remove"></a></div>';
		}
		if (!strstr($product['products_url'], 'http://') and !strstr($product['products_url'], 'http://')) {
			$product['products_url']='http://'.$product['products_url'];
		}
	}
	$markerArray=array();
	$markerArray['ITEM_PRODUCTS_ID']=$product['products_id'];
	$markerArray['ITEM_PRODUCT_DETAILS_PAGE_LINK']=$link;
	$markerArray['ITEM_PRODUCTS_NAME']=htmlspecialchars($product['products_name']);
	$markerArray['ITEM_PRODUCTS_IMAGE']=$image;
	$markerArray['ITEM_PRODUCTS_SPECIAL_PRICE']=$item_products_specials_price;
	$markerArray['ITEM_PRODUCTS_PRICE']=$item_products_price;
	$markerArray['ADMIN_MENU']=$admin_menu;
	$markerArray['ITEM_PRODUCT_DETAILS_PAGE_LINK_TITLE']=htmlspecialchars($this->pi_getLL('view')).' '.htmlspecialchars($product['products_name']);;
	$markerArray['ITEM_LABEL_VIEW']=htmlspecialchars($this->pi_getLL('view'));
	$markerArray['ITEM_PRODUCTS_EXTERNAL_LINK']=$product['products_url'];
	$markerArray['ITEM_PRODUCTS_EXTERNAL_LINK_TITLE']=htmlspecialchars($this->pi_getLL('buy')).' '.htmlspecialchars($product['products_name']);
	$markerArray['ITEM_LABEL_BUY']=htmlspecialchars($this->pi_getLL('buy'));
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_listing']['specialsProductsListingHook'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'product'=>&$product
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_listing']['specialsProductsListingHook'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom hook that can be controlled by third-party plugin eof
	$specials_items.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
}
if ($specials_items and $this->ajax_content) {
	$subpartArray=array();
	$subpartArray['###ITEM###']=$specials_items;
	$subpartArray['###SPECIALS_HEADER###']=$specials_items_header;
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_listing']['specialsListingPostHook'])) {
		$params=array(
			'subpartArray'=>&$subpartArray
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_listing']['specialsListingPostHook'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom hook that can be controlled by third-party plugin eof
	$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
} elseif ($specials_items and !$this->ajax_content) {
	$content.=$specials_items_header;
	$content.='
	<script type="text/javascript">
	var mycarousel_itemList_'.$content_uid.' = [
		'.$specials_items.'
	];
	function mycarousel_itemVisibleInCallback(carousel, item, i, state, evt) {
		// The index() method calculates the index from a
		// given index who is out of the actual item range.
		var idx = carousel.index(i, mycarousel_itemList_'.$content_uid.'.length);
		carousel.add(i, mycarousel_getItemHTML(mycarousel_itemList_'.$content_uid.'[idx - 1]));
	};
	function mycarousel_itemVisibleOutCallback(carousel, item, i, state, evt) {
		carousel.remove(i);
	};
	/**
	 * Item html creation helper.
	 */
	function mycarousel_getItemHTML(item) {
		var image_tag=\'\';
		if (item.image) image_tag=\'<img src="\' + item.image + \'" alt="\' + item.title + \'" />\';
		else			image_tag=\'<div class="no_image"></div>\'
		return \'<div class="carousel_image"><a href="\' + item.url + \'" class="ajax_link">\'+image_tag+\'</a></div><div class="carousel_title"><a href="\' + item.url + \'" class="ajax_link">\' + item.title + \'</a></div><div class="carousel_price">\' + item.price + \'</div><div class="carousel_specials_price">\' + item.specials_price + \'</div>\';
	};
	jQuery(document).ready(function($) {
		jQuery(\'#mycarousel_'.$content_uid.'\').jcarousel({
			wrap: \'circular\',
			auto:3,
			animation:1000,
			scroll:1,
			itemVisibleInCallback: {onBeforeAnimation: mycarousel_itemVisibleInCallback},
			itemVisibleOutCallback: {onAfterAnimation: mycarousel_itemVisibleOutCallback}
		});
	});
	</script>
	';
	$content.='
	 <ul id="mycarousel_'.$content_uid.'" class="jcarousel-skin-ie7">
		<!-- The content will be dynamically loaded in here -->
	  </ul>
	';
}
?>