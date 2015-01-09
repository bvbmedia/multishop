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
	$product=mslib_fe::getProduct($this->get['pid']);
	$quantity=$this->get['qty'];
	if ($product['staffel_price']) {
		$staffel_price['price']=(mslib_fe::calculateStaffelPrice($product['staffel_price'], $quantity)/$quantity);
	} else {
		$staffel_price['price']=($product['final_price']);
	}
	//if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
		$staffel_price['price_include_vat']=mslib_fe::taxDecimalCrop($staffel_price['price']+($staffel_price['price']*$product['tax_rate']), 2, false);
	//}
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