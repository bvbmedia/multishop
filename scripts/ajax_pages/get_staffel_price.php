<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$products_id=intval($this->get['pid']);
if (!$products_id) {
	exit();
}
$qty=$this->get['quantity'];
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_staffel_price.php']['getProductPricePreProc'])) {
	$params=array(
		'products_id'=>&$products_id,
		'qty'=>&$qty
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_staffel_price.php']['getProductPricePreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// hook eof
// convert the dec sign from comma to dot
if (strpos($qty, ',')!==false) {
	$qty=str_replace(',', '.', $qty);
}
// correction for quantity
// 1.23 corrected to 1.2
$qty_decimal_correction='';
if (strstr($qty, ".")) {
	$decimals=explode('.', $qty);
	if (strlen($decimals[1])>1) {
		$decimals[1]=$decimals[1][0];
		$qty=implode('.', $decimals);
		$qty_decimal_correction=$qty;
	}
}
// caller marker for the mslib_fe::getProduct
$this->post['caller_script']='get_staffel_price';
if ($this->ADMIN_USER) {
	$product=mslib_fe::getProduct($products_id, '', '', 1);
} else {
	$product=mslib_fe::getProduct($products_id);
}
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_staffel_price.php']['getProductPostProc'])) {
	$params=array(
		'products_id'=>&$products_id,
		'product'=>&$product
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_staffel_price.php']['getProductPostProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// hook eof
$price=mslib_fe::final_products_price($product, $qty, 0)*$qty;
$original_price=$product['products_price']*$qty;
if ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
	//$price=round($price*(1+$product['tax_rate']), 2);
	$original_price=round($original_price*(1+$product['tax_rate']), 2);
}
$attr=array();
$original_attr=array();
if (is_array($this->get['attributes'])) {
	foreach ($this->get['attributes'] as $key=>$value) {
		if (is_numeric($key)) {
			$str="SELECT products_options_name,listtype from tx_multishop_products_options o where o.products_options_id='".$key."' and language_id='".$this->sys_language_uid."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		}
		$continue=0;
		switch ($row['listtype']) {
			case 'checkbox':
				$multiple=1;
				$continue=1;
				break;
			case 'input':
				$attr['attributes'][$key]['products_options_name']=$row['products_options_name'];
				$attr['attributes'][$key]['products_options_values_name']=$value;
				$continue=0;
				$multiple=0;
				break;
			default:
				$continue=1;
				$multiple=0;
				break;
		}
		if ($continue) {
			if (is_array($value)) {
				$array=$value;
			} elseif ($value) {
				$array=array($value);
			}
			if (count($array)) {
				if ($multiple) {
					// reset first
					unset($attr['attributes'][$key]);
				}
				foreach ($array as $item) {
					$str="SELECT * from tx_multishop_products_attributes a, tx_multishop_products_options o, tx_multishop_products_options_values ov where a.products_id='".$products_id."' and a.options_id='".$key."' and a.options_values_id='".$item."' and a.page_uid='".$this->showCatalogFromPage."' and (o.hide_in_cart=0 or o.hide_in_cart is null) and a.options_id=o.products_options_id and o.language_id='".$this->sys_language_uid."' and ov.language_id='".$this->sys_language_uid."' and a.options_values_id=ov.products_options_values_id";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
						$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
						$original_row=$row;
						// hook to let other plugins further manipulate the option values display
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_staffel_price.php']['ajaxCartAttributesArray'])) {
							$params=array(
								'product_id'=>$products_id,
								'options_id'=>&$key,
								'row'=>&$row,
								'original_row'=>&$original_row
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_staffel_price.php']['ajaxCartAttributesArray'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						// hook
						//if ($multiple) {
						$attr['attributes'][$key][]=$row;
						$original_attr['attributes'][$key][]=$original_row;
						//} else {
						//$attr['attributes'][$key]=$row;
						//$original_attr['attributes'][$key]=$original_row;
						//}
					}
				}
			}
		}
	}
}
if (is_array($attr['attributes'])) {
	foreach ($attr['attributes'] as $attribute_key=>$attribute_values) {
		if (!isset($attribute_values[0])) {
			$attributes_array=array($attribute_values);
		} else {
			$attributes_array=$attribute_values;
		}
		foreach ($attributes_array as $idx=>$attribute_array) {
			$original_attribute_values=$original_attr['attributes'][$attribute_key][$idx];
			if ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$attribute_array['options_values_price']=round($attribute_array['options_values_price']*(1+$product['tax_rate']), 2);
				$original_attribute_values['options_values_price']=round($original_attribute_values['options_values_price']*(1+$product['tax_rate']), 2);
			} else {
				$attribute_array['options_values_price']=round($attribute_array['options_values_price'], 2);
				$original_attribute_values['options_values_price']=round($original_attribute_values['options_values_price'], 2);
			}
			$price=$price+($qty*($attribute_array['price_prefix'].$attribute_array['options_values_price']));
			$original_price=$original_price+($qty*($original_attribute_values['price_prefix'].$original_attribute_values['options_values_price']));
		}
	}
}
$data['old_price_format']='';
$data['old_price']=0;
$data['price_format']='';
$data['price']=0;
$data['qty_correction']=0;
if ($price>0) {
	if ($price!=$original_price && $original_price>0) {
		$data['old_price_format']=mslib_fe::amount2Cents($original_price, 1);
		$data['old_price']=$original_price;
	}
	$data['price_format']=mslib_fe::amount2Cents($price, 1);
	$data['price']=$price;
	$data['qty_correction']=$qty_decimal_correction;
}
echo json_encode($data, ENT_NOQUOTES);
exit();
?>