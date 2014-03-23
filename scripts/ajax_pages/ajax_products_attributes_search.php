<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if($this->ADMIN_USER) {
	header("Content-Type:application/json; charset=UTF-8");
	if($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
		$this->ms['MODULES']['CACHE_FRONT_END']=0;
	}
	if($this->ms['MODULES']['CACHE_FRONT_END']) {
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']);
		$Cache_Lite=new Cache_Lite($options);
		$string=md5('ajax_products_attributes_search_'.$this->shop_pid.'_'.$_REQUEST['pid']);
	}
	if(!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string))) {
		if($_REQUEST['pid']) {
			$this->get['pid']=(int)$_REQUEST['pid'];
			$this->get['optid']=(int)$_REQUEST['optid'];
		}
		if($_REQUEST['pid'] and strlen($_REQUEST['pid']) < 1) {
			exit();
		}
		$option_data=array();
		if($this->get['optid'] == 0) {
			$sql_option="select popt.products_options_id, popt.products_options_name from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='".$this->get['pid']."' and popt.language_id = '".$this->sys_language_uid."' and patrib.options_id = popt.products_options_id group by popt.products_options_id order by popt.sort_order";
			$qry_option=$GLOBALS['TYPO3_DB']->sql_query($sql_option);
			$ctr=0;
			while(($rs_option=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_option)) != false) {
				$option_data[$ctr]['optid']=$rs_option['products_options_id'];
				$option_data[$ctr]['optname']=$rs_option['products_options_name'];
				$ctr++;
			}
		} else {
			$sql_option="select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.options_values_id, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pa.products_id = '".$this->get['pid']."' and pa.options_id = '".$this->get['optid']."' and pov.language_id = '".$this->sys_language_uid."' and pa.options_values_id = pov.products_options_values_id and povp.products_options_id='".$this->get['optid']."' and povp.products_options_values_id=pov.products_options_values_id order by povp.sort_order";
			$qry_option=$GLOBALS['TYPO3_DB']->sql_query($sql_option);
			$ctr=0;
			while(($rs_option=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_option)) != false) {
				$option_data[$ctr]['valid']=$rs_option['options_values_id'];
				$option_data[$ctr]['valname']=$rs_option['products_options_values_name'];
				$ctr++;
			}
		}
		$content=$option_data;
		$content=json_encode($content, ENT_NOQUOTES);
		if($this->ms['MODULES']['CACHE_FRONT_END']) {
			$Cache_Lite->save($content);
		}
	}
	echo $content;
	exit;
}
?>