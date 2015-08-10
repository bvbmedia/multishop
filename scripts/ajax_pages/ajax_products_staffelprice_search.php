<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
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
	$string=md5('ajax_products_staffelprice_search_'.$this->shop_pid.'_'.$_REQUEST['pid']);
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string))) {
	if ($_REQUEST['pid']) {
		$this->get['pid']=(int)$_REQUEST['pid'];
		$this->get['qty']=(int)$_REQUEST['qty'];
	}
	if ($_REQUEST['pid'] and strlen($_REQUEST['pid'])<1) {
		exit();
	}
	$product=mslib_fe::getProduct($this->get['pid'], '', '', 1, 1);
	$quantity=$this->get['qty'];
	if ($product['staffel_price']) {
		$staffel_price['price']=(mslib_fe::calculateStaffelPrice($product['staffel_price'], $quantity)/$quantity);
	} else {
		$staffel_price['price']=($product['final_price']);
	}
	//if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
	$staffel_price['price_include_vat']=$staffel_price['price']+($staffel_price['price']*$product['tax_rate']);
	//}
	$staffel_price['display_price']=mslib_fe::taxDecimalCrop($staffel_price['price'], 2, false);
	$staffel_price['display_price_include_vat']=mslib_fe::taxDecimalCrop($staffel_price['price_include_vat'], 2, false);
	//
	$staffel_price['use_tax_id']=true;
	if (isset($this->get['oid']) && is_numeric($this->get['oid']) && $this->get['oid']>0) {
		$orders=mslib_fe::getOrder($this->get['oid']);
		$iso_customer = mslib_fe::getCountryByName($orders['billing_country']);
		$iso_customer['country'] = $iso_customer['cn_short_en'];
		if (strtolower($iso_customer['country'])!=strtolower($this->tta_shop_info['country'])) {
			$sql_tax_sb=$GLOBALS['TYPO3_DB']->SELECTquery('t.tax_id, t.rate, t.name, trg.default_status', // SELECT ...
					'tx_multishop_taxes t, tx_multishop_tax_rules tr, tx_multishop_tax_rule_groups trg', // FROM ...
					't.tax_id=tr.tax_id and tr.rules_group_id=trg.rules_group_id and trg.status=1 and tr.cn_iso_nr=\''.$iso_customer['cn_iso_nr'].'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
			);
			$qry_tax_sb=$GLOBALS['TYPO3_DB']->sql_query($sql_tax_sb);
			$rs_tx_sb=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tax_sb);
			if ($rs_tx_sb['tax_id']>0) {
				if ($rs_tx_sb['rate']<0.1) {
					$staffel_price['use_tax_id']=false;
				}
				$product['tax_id']=$rs_tx_sb['tax_id'];
			}
		}
	}
	//
	$staffel_price['tax_id']=$product['tax_id'];
	$content=$staffel_price;
	$content=json_encode($content, ENT_NOQUOTES);
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($content);
	}
}
echo $content;
exit;
?>