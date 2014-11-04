<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$teller=0;
$specials_items='';
if (!$this->imageWidth) {
	$this->imageWidth=200;
}
// now parse all the objects in the tmpl file
if ($this->conf['specials_sections_products_listing_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['specials_sections_products_listing_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/specials_sections.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
foreach ($products as $product) {
	$output=array();
	if ($product['products_image']) {
		$output['image']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'">';
	} else {
		$output['image']='<div class="no_image"></div>';
	}
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
	$output['link']=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
	$final_price=mslib_fe::final_products_price($product);
	if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
		$old_price=$product['products_price']*(1+$product['tax_rate']);
	} else {
		$old_price=$product['products_price'];
	}
	$special_section_price='';
	if ($old_price and $final_price) {
		$output['special_section_price']='<div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="specials_price">'.mslib_fe::amount2Cents($final_price).'</div>';
	} else {
		$output['special_section_price']='<div class="price">'.mslib_fe::amount2Cents($final_price).'</div>';
	}
	$output['products_name']=htmlspecialchars($product['products_name']);
	$markerArray=array();
	$markerArray['ITEM_DETAILS_PAGE_LINK']=$output['link'];
	$markerArray['ITEM_PRODUCTS_NAME']=$output['products_name'];
	$markerArray['ITEM_PRODUCTS_IMAGE']=$output['image'];
	$markerArray['ITEM_PRODUCTS_PRICE']=$output['special_section_price'];
	if (mslib_fe::ProductHasAttributes($current_product['products_id'])) {
		$button_submit='<a href="'.$output['link'].'" class="ajax_link"><input name="Submit" type="submit" value="'.$this->pi_getLL('add_to_basket').'"/></a>';
	} else {
		$button_submit='<input name="Submit" type="submit" value="'.$this->pi_getLL('add_to_basket').'"/>';
	}
	$markerArray['ITEM_DETAILS_ADD_TO_CART_BUTTON']='
		<div class="msFrontAddToCartButton">
			<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=shopping_cart&products_id='.$product['products_id']).'" method="post">
				<input type="hidden" name="quantity" value="1" />
				<input type="hidden" name="products_id" value="'.$product['products_id'].'" />
				'.$button_submit.'
			</form>
		</div>
	';
	// get the specials id
	$str=$GLOBALS['TYPO3_DB']->SELECTquery('specials_id', // SELECT ...
		'tx_multishop_specials', // FROM ...
		'products_id="'.$product['products_id'].'"', // WHERE...
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$res=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);

	$markerArray['SPECIALS_SECTIONS_ID']=$res['specials_id'];
	$markerArray['SPECIALS_SECTIONS_CODE']=$this->section_code;
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionProductsListingHook'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'product'=>&$product,
			'output'=>&$output,
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionsProductsListingHook'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom hook that can be controlled by third-party plugin eof
	$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
}
$subpartArray=array();
$subpartArray['###ITEM###']=$contentItem;
$subpartArray['###SPECIALS_SECTIONS_CODE_ID###']=$this->section_code;
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionsPostHook'])) {
	$params=array(
		'subpartArray'=>&$subpartArray
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionsPostHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof


$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
	$content.='
	<script type="text/javascript">
	  jQuery(document).ready(function($) {
		var result = jQuery("#specialssections_listing_'.$this->section_code.' .product_listing").sortable({
			cursor:     "move",
			//axis:       "y",
			update: function(e, ui) {
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=sort_specials_sections&tx_multishop_pi1[sort_specials_sections]=' . $this->section_code).'";
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