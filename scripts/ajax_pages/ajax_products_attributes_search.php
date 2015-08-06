<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
	header("Content-Type:application/json; charset=UTF-8");
	if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
		$this->ms['MODULES']['CACHE_FRONT_END']=0;
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']
		);
		$Cache_Lite=new Cache_Lite($options);
		$string=md5('ajax_products_attributes_search_'.$this->shop_pid.'_'.$_REQUEST['pid']);
	}
    $this->ms['MODULES']['CACHE_FRONT_END']=0;
	//if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string))) {
		if ($_REQUEST['pid']) {
			$this->get['pid']=(int)$_REQUEST['pid'];
			$this->get['optid']=(int)$_REQUEST['optid'];
		}
		if ($_REQUEST['pid'] and strlen($_REQUEST['pid'])<1) {
			exit();
		}
		$option_data=array();
		if ($this->get['optid']==0) {
			$sql_option="select popt.products_options_id, popt.products_options_name, patrib.sort_order_option_name from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='".$this->get['pid']."' and popt.language_id = '".$this->sys_language_uid."' and (popt.hide_in_cart=0 or popt.hide_in_cart is null) and patrib.options_id = popt.products_options_id group by popt.products_options_id order by patrib.sort_order_option_name asc";
			$qry_option=$GLOBALS['TYPO3_DB']->sql_query($sql_option);
            while (($rs_option=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_option))!=false) {
                $option_data[$rs_option['sort_order_option_name']]['optid']=$rs_option['products_options_id'];
                $option_data[$rs_option['sort_order_option_name']]['optname']=$rs_option['products_options_name'];
                //
                if ($this->get['ajax_products_attributes_search']['action']=='get_options_values') {
                    $sql_value="select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.options_values_id, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pa.products_id = '".$this->get['pid']."' and pa.options_id = '".$rs_option['products_options_id']."' and pov.language_id = '".$this->sys_language_uid."' and povp.products_options_id=pa.options_id and pa.options_values_id = pov.products_options_values_id and povp.products_options_values_id=pov.products_options_values_id and povp.products_options_id=pa.options_id order by pa.sort_order_option_name asc, pa.sort_order_option_value asc limit 1";
                    $qry_value=$GLOBALS['TYPO3_DB']->sql_query($sql_value);
                    if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_value)>0) {
                        while (($rs_value=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_value))!=false) {
                            $product=mslib_fe::getProduct($this->get['pid'], '', '', 1, 1);
                            $data=mslib_fe::getTaxRuleSet($product['tax_id'], $product['products_price']);
                            $product_tax_rate=$data['total_tax_rate'];
                            $attributes_tax=mslib_fe::taxDecimalCrop(($rs_value['options_values_price']*$product_tax_rate)/100);
                            $attribute_price_display_incl=mslib_fe::taxDecimalCrop($rs_value['options_values_price']+$attributes_tax, 2, false);
                            $option_data[$rs_option['sort_order_option_name']]['value']['valid']=$rs_value['options_values_id'];
                            $option_data[$rs_option['sort_order_option_name']]['value']['valname']=$rs_value['products_options_values_name'];
                            $option_data[$rs_option['sort_order_option_name']]['value']['values_price']=$rs_value['options_values_price'];
                            $option_data[$rs_option['sort_order_option_name']]['value']['display_values_price']=mslib_fe::taxDecimalCrop($rs_value['options_values_price'], 2, false);
                            $option_data[$rs_option['sort_order_option_name']]['value']['display_values_price_including_vat']=$attribute_price_display_incl;
                            $option_data[$rs_option['sort_order_option_name']]['value']['price_prefix']=$rs_value['price_prefix'];
                        }
                    }
                }
            }
		} else {
			if (isset($this->get['valid'])) {
				$sql_option="select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.options_values_id, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pa.products_id = '".$this->get['pid']."' and pa.options_id = '".$this->get['optid']."' and pa.options_values_id='".$this->get['valid']."' and pov.language_id = '".$this->sys_language_uid."' and pa.options_values_id = pov.products_options_values_id and povp.products_options_values_id=pov.products_options_values_id order by pa.sort_order_option_name asc, pa.sort_order_option_value asc";
			} else {
				$sql_option="select pa.sort_order_option_name, pa.sort_order_option_value, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.options_values_id, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pa.products_id = '".$this->get['pid']."' and pa.options_id = '".$this->get['optid']."' and pov.language_id = '".$this->sys_language_uid."' and povp.products_options_id='".$this->get['optid']."' and pa.options_values_id = pov.products_options_values_id and povp.products_options_values_id=pov.products_options_values_id and povp.products_options_id=pa.options_id order by pa.sort_order_option_name asc, pa.sort_order_option_value asc";
			}
			//var_dump($sql_option);
			$qry_option=$GLOBALS['TYPO3_DB']->sql_query($sql_option);
			$ctr=0;
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_option)>0) {
                while (($rs_option=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_option))!=false) {
                    $product=mslib_fe::getProduct($this->get['pid'], '', '', 1, 1);
                    $data=mslib_fe::getTaxRuleSet($product['tax_id'], $product['products_price']);
                    $product_tax_rate=$data['total_tax_rate'];
                    $attributes_tax=mslib_fe::taxDecimalCrop(($rs_option['options_values_price']*$product_tax_rate)/100);
                    $attribute_price_display_incl=mslib_fe::taxDecimalCrop($rs_option['options_values_price']+$attributes_tax, 2, false);
                    $option_data[$ctr]['sort_order']=(int)$rs_option['sort_order_option_name'];
                    $option_data[$ctr]['optid']=$this->get['optid'];
                    $option_data[$ctr]['valid']=$rs_option['options_values_id'];
                    $option_data[$ctr]['valname']=$rs_option['products_options_values_name'];
                    $option_data[$ctr]['values_price']=$rs_option['options_values_price'];
                    $option_data[$ctr]['display_values_price']=mslib_fe::taxDecimalCrop($rs_option['options_values_price'], 2, false);
                    $option_data[$ctr]['display_values_price_including_vat']=$attribute_price_display_incl;
                    $option_data[$ctr]['price_prefix']=$rs_option['price_prefix'];
                    $ctr++;
                }
            }
		}
		$content=$option_data;
		$content=json_encode($content, ENT_NOQUOTES);
		if ($this->ms['MODULES']['CACHE_FRONT_END']) {
			$Cache_Lite->save($content);
		}
	//}
	echo $content;
	exit;
}
?>