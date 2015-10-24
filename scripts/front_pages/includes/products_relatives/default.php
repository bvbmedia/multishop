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
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/products_relatives.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['title']=$this->cObj->getSubpart($subparts['template'], '###TITLE###');
$subparts['header']=$this->cObj->getSubpart($subparts['template'], '###HEADER###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
$markerArray=array();
// TITLE
$title=$this->pi_getLL('product_relatives');
switch ($type) {
	case 'customers_also_bought':
		$title=$this->pi_getLL('customers_that_bought_this_product_also_bought');
		break;
}
$markerArray['TITLE_LABEL']=htmlspecialchars($title);
$subpartArray['###TITLE###']=$this->cObj->substituteMarkerArray($subparts['title'], $markerArray, '###|###');
// TABLE HEADER FIRST
$markerArray=array();
$markerArray['HEADER_NAME']=htmlspecialchars(ucfirst($this->pi_getLL('products_name')));
$markerArray['HEADER_SHIPPING_COSTS_OVERVIEW_RELATIVE']='';
if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
	$markerArray['HEADER_SHIPPING_COSTS_OVERVIEW_RELATIVE']='<th class="relatives_products_products_shipping_costs_overview">&nbsp;</th>';
}
$markerArray['HEADER_PRICE']=htmlspecialchars(ucfirst($this->pi_getLL('price')));
$markerArray['HEADER_QUANTITY']=htmlspecialchars(ucfirst($this->pi_getLL('qty')));
$markerArray['HEADER_BUY_NOW']=htmlspecialchars(ucfirst($this->pi_getLL('buy_now')));
if (!$this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
	$subpartHeaderArray=array();
	$subpartHeaderArray['###HEADER_SHIPPING_COSTS_OVERVIEW_RELATIVE_WRAPPER###']='';
	$subparts['header']=$this->cObj->substituteMarkerArrayCached($subparts['header'], array(), $subpartHeaderArray);
}
if (!$this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
	$subpartHeaderArray=array();
	$subpartHeaderArray['###ITEM_SHIPPING_COSTS_OVERVIEW_RELATIVE_WRAPPER###']='';
	$subparts['item']=$this->cObj->substituteMarkerArrayCached($subparts['item'], array(), $subpartHeaderArray);
}
if ($this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN'] && $this->ms['MODULES']['SHOW_STOCK_LEVEL_AS_BOOLEAN']=='no') {
	$subpartHeaderArray=array();
	$subpartHeaderArray['###HEADER_STOCK_RELATIVE_WRAPPER###']='';
	$subparts['header']=$this->cObj->substituteMarkerArrayCached($subparts['header'], array(), $subpartHeaderArray);
	$subpartHeaderArray=array();
	$subpartHeaderArray['###ITEM_STOCK_RELATIVE_WRAPPER###']='';
	$subparts['item']=$this->cObj->substituteMarkerArrayCached($subparts['item'], array(), $subpartHeaderArray);
} else {
	$markerArray['HEADER_STOCK']=htmlspecialchars(ucfirst($this->pi_getLL('stock')));
}
$subpartArray['###HEADER###']=$this->cObj->substituteMarkerArray($subparts['header'], $markerArray, '###|###');
// NOW THE PRODUCT ITEMS
$contentItem='';
$i=0;
$tr_type='even';
if (is_array($rel_products) && count($rel_products)) {
	foreach ($rel_products as $rel_rs) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$products_attributes=$GLOBALS['TYPO3_DB']->sql_query("select popt.products_options_name from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='".$product['products_id']."' and patrib.page_uid='".$this->showCatalogFromPage."' and patrib.options_id = popt.products_options_id and popt.language_id = '".$languages_id."' order by popt.products_options_id");
		//$products_attributes = tep_db_query();
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($products_attributes)) {
			$products_attributes='1';
		} else {
			$products_attributes='0';
		}
		if ($products_attributes) {
			$opt_sql="select distinct popt.products_options_id, popt.products_options_name from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='".$product['products_id']."' and patrib.page_uid='".$this->showCatalogFromPage."' and patrib.options_id = popt.products_options_id and popt.language_id = '".$languages_id."'";
			$products_options_name=$GLOBALS['TYPO3_DB']->sql_query($opt_sql);
			while ($products_options_name_values=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_options_name)) {
				$selected=0;
				$products_options=$GLOBALS['TYPO3_DB']->sql_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov where pa.products_id = '".$product['products_id']."' and pa.page_uid='".$this->showCatalogFromPage."' and pa.options_id = '".$products_options_name_values['products_options_id']."' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '".$languages_id."' order by pa.options_values_price,pov.products_options_values_id, pov.products_options_values_name");
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
		$final_price=mslib_fe::final_products_price($rel_rs);
		$rel_rs['hidden_fields'].='<input type="hidden" name="relation_products_id['.$i.']" value="'.$rel_rs['products_id'].'" />';
		$markerArray=array();
		$markerArray['ITEM_CLASS']=$tr_type;
		$markerArray['PRODUCTS_LINK']=$link;
		if ($rel_rs['products_image']) {
			$markerArray['ITEM_PRODUCTS_IMAGE']='<img src="'.mslib_befe::getImagePath($rel_rs['products_image'], 'products', '50').'" alt="'.htmlspecialchars($rel_rs['products_name']).'" />';
			$markerArray['ITEM_PRODUCTS_IMAGE_100']='<img src="'.mslib_befe::getImagePath($rel_rs['products_image'], 'products', '100').'" alt="'.htmlspecialchars($rel_rs['products_name']).'" />';
			$markerArray['ITEM_PRODUCTS_IMAGE_200']='<img src="'.mslib_befe::getImagePath($rel_rs['products_image'], 'products', '200').'" alt="'.htmlspecialchars($rel_rs['products_name']).'" />';
			$markerArray['ITEM_PRODUCTS_IMAGE_300']='<img src="'.mslib_befe::getImagePath($rel_rs['products_image'], 'products', '300').'" alt="'.htmlspecialchars($rel_rs['products_name']).'" />';
		} else {
			$markerArray['ITEM_PRODUCTS_IMAGE']='<div class="no_image_50"></div>';
			$markerArray['ITEM_PRODUCTS_IMAGE_100']='<div class="no_image_50"></div>';
			$markerArray['ITEM_PRODUCTS_IMAGE_200']='<div class="no_image_50"></div>';
			$markerArray['ITEM_PRODUCTS_IMAGE_300']='<div class="no_image_50"></div>';
		}
		$markerArray['ITEM_PRODUCTS_NAME']=$rel_rs['products_name'].($rel_rs['products_model'] ? ' <br />'.$rel_rs['products_model'] : '');
		$markerArray['ITEM_PRODUCTS_SHORTDESCRIPTION_ENCODED']=htmlspecialchars($rel_rs['products_shortdescription']);
		$markerArray['ITEM_PRODUCTS_SHORTDESCRIPTION']=$rel_rs['products_shortdescription'];
		$markerArray['ITEM_PRODUCTS_PRICE']=mslib_fe::amount2Cents($final_price);
		$quantity_html='<div class="quantity buttons_added">';
		$quantity_html.='<input type="button" value="-" data-stepSize="'.($rel_rs['products_multiplication']!='0.00' ? $rel_rs['products_multiplication'] : '1').'" data-minQty="'.($rel_rs['minimum_quantity']!='0.00' ? $rel_rs['minimum_quantity'] : '1').'" data-maxQty="'.($rel_rs['maximum_quantity']!='0.00' ? $rel_rs['maximum_quantity'] : '0').'" class="rel_qty_minus" rel="relation_cart_quantity_'.$i.'">';
		$quantity_html.='<input class="qty_input" name="relation_cart_quantity['.$i.']" type="text" id="relation_cart_quantity_'.$i.'" value="1" size="4" maxlength="4" />';
		$quantity_html.='<input type="button" value="+" data-stepSize="'.($rel_rs['products_multiplication']!='0.00' ? $rel_rs['products_multiplication'] : '1').'" data-minQty="'.($rel_rs['minimum_quantity']!='0.00' ? $rel_rs['minimum_quantity'] : '1').'" data-maxQty="'.($rel_rs['maximum_quantity']!='0.00' ? $rel_rs['maximum_quantity'] : '0').'" class="rel_qty_plus" rel="relation_cart_quantity_'.$i.'"></div>';
		$markerArray['ITEM_PRODUCTS_QUANTITY']=$quantity_html;//'<input type="text" name="relation_cart_quantity['.$i.']" value="1" maxlength="4" size="2" />';
		$markerArray['ITEM_BUY_NOW']='<div class="checkbox checkbox-success">
		<input type="checkbox" name="winkelwagen['.$i.']" id="relative_'.$i.'" value="1"><label for="relative_'.$i.'"></label></div>'.$rel_rs['hidden_fields'];
		$markerArray['ITEM_PRODUCTS_STOCK']=$rel_rs['products_quantity'];
		$markerArray['ITEM_SHIPPING_COSTS_OVERVIEW_RELATIVE']='';
		$markerArray['ITEM_PRODUCTS_ID']=$rel_rs['products_id'];
		// for compatibility with normal listing
		$markerArray['PRODUCTS_ID']=$markerArray['ITEM_PRODUCTS_ID'];
		$markerArray['ITEM_LABEL_SHIPPING_COSTS_OVERVIEW']='';
		if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
			$markerArray['ITEM_LABEL_SHIPPING_COSTS_OVERVIEW']=$this->pi_getLL('shipping_costs');
		}
		$markerArray['ITEM_PRODUCTS_SKU']=$rel_rs['sku_code'];
		$markerArray['ITEM_PRODUCTS_EAN']=$rel_rs['ean_code'];
		$markerArray['ITEM_HEADER_NAME']=htmlspecialchars(ucfirst($this->pi_getLL('products_name')));
		$markerArray['ITEM_HEADER_PRICE']=htmlspecialchars(ucfirst($this->pi_getLL('price')));
		$markerArray['ITEM_HEADER_QUANTITY']=htmlspecialchars(ucfirst($this->pi_getLL('qty')));
		$markerArray['ITEM_HEADER_BUY_NOW']=htmlspecialchars(ucfirst($this->pi_getLL('buy_now')));
		$markerArray['ITEM_HEADER_STOCK']=htmlspecialchars(ucfirst($this->pi_getLL('stock')));
		$i++;
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingRecordHook'])) {
			$params=array(
				'markerArray'=>&$markerArray,
				'product'=>&$rel_rs,
				'output'=>&$output,
				'products_compare'=>&$products_compare
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingRecordHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
	}
}
// fill the row marker with the expanded rows
$subpartArray['###ITEM###']=$contentItem;
// completed the template expansion by replacing the "item" marker in the template
if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
	$content.='
	<div class="modal" id="shippingCostsModalRelatives" tabindex="-1" role="dialog" aria-labelledby="shippingCostModalTitleRelatives" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="shippingCostModalTitleRelatives">'.$this->pi_getLL('shipping_costs').'</h4>
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
	  	$(\'#shippingCostsModalRelatives\').modal({
			show:false,
			backdrop:false
		});
		$(\'#shippingCostsModalRelatives\').on(\'show.bs.modal\', function (event) {
			var button = $(event.relatedTarget) // Button that triggered the modal
  			var product_id = button.data(\'productid\') // Extract info from data-* attributes
			var modalBox = $(this);
			modalBox.find(\'.modal-body\').empty();
			if (modalBox.find(\'.modal-body\').html()==\'\') {
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
							shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col">'.$this->pi_getLL('deliver_to').'</td>\';
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
							modalBox.find(\'.modal-body\').html(shipping_cost_popup);
							//msDialog("'.$this->pi_getLL('shipping_costs').'", shipping_cost_popup, 650);
						}
					}
				});
			}
		});
	});
	</script>
	';
}
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingPagePostHook'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'current'=>&$current
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_relatives.php']['productsListingPagePostHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
?>