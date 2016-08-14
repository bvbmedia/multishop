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
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/specials_sections.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
foreach ($products as $product) {
	if ($product['products_price']<>$product['final_price']) {
		$markerArray=array();
		$output=array();
		if ($product['products_image']) {
			$output['image']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'">';
			$markerArray['ITEM_PRODUCTS_IMAGE']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'">';
			$markerArray['ITEM_PRODUCTS_IMAGE_100']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '100').'">';
			$markerArray['ITEM_PRODUCTS_IMAGE_200']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '200').'">';
			$markerArray['ITEM_PRODUCTS_IMAGE_300']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '300').'">';
			$markerArray['PRODUCTS_IMAGE']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'">';
			$markerArray['PRODUCTS_IMAGE_100']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '100').'">';
			$markerArray['PRODUCTS_IMAGE_200']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '200').'">';
			$markerArray['PRODUCTS_IMAGE_300']='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '300').'">';

			$markerArray['PRODUCTS_IMAGE_URL']=mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth);
			$markerArray['PRODUCTS_IMAGE_URL_100']=mslib_befe::getImagePath($product['products_image'], 'products', '100');
			$markerArray['PRODUCTS_IMAGE_URL_200']=mslib_befe::getImagePath($product['products_image'], 'products', '200');
			$markerArray['PRODUCTS_IMAGE_URL_300']=mslib_befe::getImagePath($product['products_image'], 'products', '300');
		} else {
			$output['image']='<div class="no_image"></div>';
			$markerArray['ITEM_PRODUCTS_IMAGE']=$output['image'];
			$markerArray['ITEM_PRODUCTS_IMAGE_100']=$output['image'];
			$markerArray['ITEM_PRODUCTS_IMAGE_200']=$output['image'];
			$markerArray['ITEM_PRODUCTS_IMAGE_300']=$output['image'];
			$markerArray['PRODUCTS_IMAGE']=$output['image'];
			$markerArray['PRODUCTS_IMAGE_100']=$output['image'];
			$markerArray['PRODUCTS_IMAGE_200']=$output['image'];
			$markerArray['PRODUCTS_IMAGE_300']=$output['image'];
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
		$admin_menu='';
		if ($this->ADMIN_USER) {
			$admin_menu='<div class="admin_menu"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=edit_product&pid='.$product['products_id'].'&action=edit_product', 1).'" class="admin_menu_edit"><i class="fa fa-pencil"></i></a><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=delete_product&pid='.$product['products_id'].'&action=delete_product', 1).'" class="admin_menu_remove" title="Remove"><i class="fa fa-trash-o"></i></a></div>';
		}
		$markerArray['ADMIN_MENU']=$admin_menu;
		$markerArray['ADMIN_ICONS']=$admin_menu;
		$output['link']=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
        $output['catlink']=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
		$final_price=mslib_fe::final_products_price($product);
		if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
			$old_price=$product['products_price']*(1+$product['tax_rate']);
		} else {
			$old_price=$product['products_price'];
		}
		$special_section_price='';
		$current_product=$product;
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
		$output['special_section_price']=$output['products_price'];
		/*
		if ($old_price and $final_price) {
			$output['special_section_price']='<div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="specials_price">'.mslib_fe::amount2Cents($final_price).'</div>';
		} else {
			$output['special_section_price']='<div class="price">'.mslib_fe::amount2Cents($final_price).'</div>';
		}
		*/
		$output['products_name']=htmlspecialchars($product['products_name']);
		$markerArray['ITEM_PRODUCTS_ID']=$product['products_id'];
		$markerArray['ITEM_DETAILS_PAGE_LINK']=$output['link'];
		$markerArray['ITEM_PRODUCTS_NAME']=$output['products_name'];
		$markerArray['ITEM_PRODUCTS_PRICE']=$output['special_section_price'];
		$markerArray['ITEM_LABEL_SHIPPING_COSTS_OVERVIEW']='';
		if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
			$markerArray['ITEM_LABEL_SHIPPING_COSTS_OVERVIEW']=$this->pi_getLL('shipping_costs');
			$markerArray['LABEL_SHIPPING_COSTS_OVERVIEW']=$this->pi_getLL('shipping_costs');
		}
		$markerArray['PRODUCTS_ID']=$product['products_id'];
		$markerArray['PRODUCTS_SHORTDESCRIPTION']=$product['products_shortdescription'];
		$markerArray['PRODUCTS_DESCRIPTION']=$product['products_description'];
		$markerArray['PRODUCTS_DETAIL_PAGE_LINK']=$output['link'];
		$markerArray['DETAILS_PAGE_LINK']=$output['link'];
		$markerArray['PRODUCTS_NAME']=$output['products_name'];
		$markerArray['PRODUCTS_PRICE']=$output['special_section_price'];
		if (mslib_fe::ProductHasAttributes($current_product['products_id'])) {
			$markerArray['PRODUCTS_ADD_TO_CART_BUTTON_LINK']=$output['link'];
			$button_submit='<a href="'.$output['link'].'" class="ajax_link"><input name="Submit" type="submit" value="'.$this->pi_getLL('add_to_basket').'"/></a>';
		} else {
			$markerArray['PRODUCTS_ADD_TO_CART_BUTTON_LINK']=mslib_fe::typolink($this->shop_pid, '&tx_multishop_pi1[page_section]=shopping_cart&tx_multishop_pi1[action]=add_to_cart&products_id='.$product['products_id']);
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

        $markerArray['CATEGORIES_NAME']=$product['categories_name'];
        $markerArray['CATEGORIES_NAME_PAGE_LINK']=$output['catlink'];

		// custom hook that can be controlled by third-party plugin
		$plugins_item_extra_content=array();
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionProductsListingHook'])) {
			$params=array(
				'markerArray'=>&$markerArray,
				'product'=>&$product,
				'output'=>&$output,
				'plugins_item_extra_content'=>&$plugins_item_extra_content,
				'limit_per_page'=>&$limit_per_page,
				'p'=>&$p
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionProductsListingHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		$markerArray['PRODUCT_LISTING_ITEM_PLUGIN_EXTRA_CONTENT']='';
		if (count($plugins_item_extra_content)) {
			$markerArray['PRODUCT_LISTING_ITEM_PLUGIN_EXTRA_CONTENT']=implode("\n", $plugins_item_extra_content);
		}
		// custom hook that can be controlled by third-party plugin eof
		if (!$this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
			$subpartHeaderArray=array();
			$subpartHeaderArray['###ITEM_SHIPPING_COSTS_OVERVIEW_RELATIVE_WRAPPER###']='';
			$subparts['item']=$this->cObj->substituteMarkerArrayCached($subparts['item'], array(), $subpartHeaderArray);
		}
		$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
	}
}
$subpartArray=array();
$subpartArray['###ITEM###']=$contentItem;
$subpartArray['###SPECIALS_SECTIONS_CODE_ID###']=$this->section_code;
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionsPostHook'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'current'=>&$current,
		'output_array'=>&$output_array
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/specials_section_listing']['specialsSectionsPostHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
    $content.='
    <div class="modal" id="specialsSectionsShippingCostsModal" tabindex="-1" role="dialog" aria-labelledby="shippingCostModalTitle" aria-hidden="true">
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
    </div>';
    $GLOBALS['TSFE']->additionalHeaderData['special_sections_shipping_costs_overview']='<script type="text/javascript">
    jQuery(document).ready(function($) {
        $(\'#specialsSectionsShippingCostsModal\').modal({
            show:false,
            backdrop:false
        });
        $(\'#specialsSectionsShippingCostsModal\').on(\'show.bs.modal\', function (event) {
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
                            $.each(j.shipping_costs_display, function(zone_id, shipping_cost_display) {
                                $.each(j.shipping_cost_display, function(shipping_method, shipping_data) {
                                    $.each(shipping_data, function(country_iso_nr, shipping_cost) {
                                        shipping_cost_popup+=\'<tr>\';
                                        shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_left_col">\' + j.deliver_to[shipping_method][country_iso_nr] + \'</td>\';
                                        shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_center_col">\' + shipping_cost + \'</td>\';
                                        shipping_cost_popup+=\'<td class="product_shippingcost_popup_table_right_col">\' + j.deliver_by[shipping_method][country_iso_nr] + \'</td>\';
                                        shipping_cost_popup+=\'</tr>\';
                                    });
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
if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
	$content.='
	<script type="text/javascript">
	  jQuery(document).ready(function($) {
		var result = jQuery("#specialssections_listing_'.$this->section_code.' .product_listing").sortable({
			cursor:     "move",
			//axis:       "y",
			update: function(e, ui) {
				href = "'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=sort_specials_sections&tx_multishop_pi1[sort_specials_sections]='.$this->section_code).'";
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