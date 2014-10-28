<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$rel_products=$pageset['products'];
if (!$this->imageWidth) {
	$this->imageWidth='100';
}
$subpartArray=array();
// now parse all the objects in the tmpl file
if ($this->conf['products_relatives_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['products_relatives_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/products_relatives.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['title']=$this->cObj->getSubpart($subparts['template'], '###TITLE###');
$subparts['header']=$this->cObj->getSubpart($subparts['template'], '###HEADER###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
// TITLE
$markerArray=array();
$markerArray['TITLE_LABEL']=htmlspecialchars($this->pi_getLL('product_relatives'));
$subpartArray['###TITLE###']=$this->cObj->substituteMarkerArray($subparts['title'], $markerArray, '###|###');
// TABLE HEADER FIRST
$markerArray=array();
$markerArray['HEADER_NAME']=htmlspecialchars(ucfirst($this->pi_getLL('products_name')));
$markerArray['HEADER_PRICE']=htmlspecialchars(ucfirst($this->pi_getLL('price')));
$markerArray['HEADER_QUANTITY']=htmlspecialchars(ucfirst($this->pi_getLL('qty')));
$markerArray['HEADER_BUY_NOW']=htmlspecialchars(ucfirst($this->pi_getLL('buy_now')));
$markerArray['HEADER_STOCK']=htmlspecialchars(ucfirst($this->pi_getLL('stock')));
$subpartArray['###HEADER###']=$this->cObj->substituteMarkerArray($subparts['header'], $markerArray, '###|###');
// NOW THE PRODUCT ITEMS
$contentItem='';
$i=0;
$tr_type='even';
foreach ($rel_products as $rel_rs) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$products_attributes=$GLOBALS['TYPO3_DB']->sql_query("select popt.products_options_name from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='".$product['products_id']."' and patrib.options_id = popt.products_options_id and popt.language_id = '".$languages_id."' order by popt.products_options_id");
	//$products_attributes = tep_db_query();
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($products_attributes)) {
		$products_attributes='1';
	} else {
		$products_attributes='0';
	}
	if ($products_attributes) {
		$opt_sql="select distinct popt.products_options_id, popt.products_options_name from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='".$product['products_id']."' and patrib.options_id = popt.products_options_id and popt.language_id = '".$languages_id."'";
		$products_options_name=$GLOBALS['TYPO3_DB']->sql_query($opt_sql);
		while ($products_options_name_values=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_options_name)) {
			$selected=0;
			$products_options=$GLOBALS['TYPO3_DB']->sql_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov where pa.products_id = '".$product['products_id']."' and pa.options_id = '".$products_options_name_values['products_options_id']."' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '".$languages_id."' order by pa.options_values_price,pov.products_options_values_id, pov.products_options_values_name");
			while ($products_options_values=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_options)) {
				if ($products_options_values['options_values_price']==0 && $selected==0) {
					$rel_rs['hidden_fields'].='<input type="hidden" name="relation_id['.$i.']['.$products_options_name_values['products_options_id'].']" value="'.$products_options_values['products_options_values_id'].'" />';
					$selected=1;
				}
			}
		}
	}
	if ($rel_rs['categories_id']) {
		// get all cats to generate multilevel fake url
		$level=0;
		$cats=mslib_fe::Crumbar($rel_rs['categories_id']);
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
	$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], $where.'&products_id='.$rel_rs['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
	// STOCK INDICATOR
	$product_qty=$rel_rs['products_quantity'];
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
	$markerArray['PRODUCTS_STOCK']=$product_qty;
	// STOCK INDICATOR EOF
	if ($rel_rs['products_image']) {
		$image='<img src="'.mslib_befe::getImagePath($rel_rs['products_image'], 'products', '50').'" alt="'.htmlspecialchars($rel_rs['products_name']).'" />';
	} else {
		$image='<div class="no_image_50"></div>';
	}
	$final_price=mslib_fe::final_products_price($rel_rs);
	$rel_rs['hidden_fields'].='<input type="hidden" name="relation_products_id['.$i.']" value="'.$rel_rs['products_id'].'" />';
	$markerArray=array();
	$markerArray['ITEM_CLASS']=$tr_type;
	$markerArray['PRODUCTS_LINK']=$link;
	$markerArray['ITEM_PRODUCTS_IMAGE']=$image;
	$markerArray['ITEM_PRODUCTS_NAME']=$rel_rs['products_name'].($rel_rs['products_model'] ? ' <br />'.$rel_rs['products_model'] : '');
	$markerArray['ITEM_PRODUCTS_PRICE']=mslib_fe::amount2Cents($final_price);
	$markerArray['ITEM_PRODUCTS_QUANTITY']='<input type="text" name="relation_cart_quantity['.$i.']" value="1" maxlength="4" size="2" />';
	$markerArray['ITEM_BUY_NOW']='<label for="relative_'.$i.'"></label>
		<input type="checkbox" class="PrettyInput" name="winkelwagen['.$i.']" id="relative_'.$i.'" value="1">'.$rel_rs['hidden_fields'];
	$markerArray['ITEM_PRODUCTS_STOCK']=$rel_rs['products_quantity'];
	$markerArray['ITEM_PRODUCTS_SKU']=$rel_rs['sku_code'];
	$markerArray['ITEM_PRODUCTS_EAN']=$rel_rs['ean_code'];
	$i++;
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingRecordHook'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'product'=>&$current_product,
			'output'=>&$output,
			'products_compare'=>&$products_compare
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingRecordHook'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom hook that can be controlled by third-party plugin eof
	$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
}
// fill the row marker with the expanded rows
$subpartArray['###ITEM###']=$contentItem;
// completed the template expansion by replacing the "item" marker in the template
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingPagePostHook'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'current'=>&$current
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingPagePostHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
?>