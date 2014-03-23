<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
	$this->ms['MODULES']['CACHE_FRONT_END']=0;
}
if($this->ms['MODULES']['CACHE_FRONT_END']) {
	$this->cacheLifeTime=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cacheLifeTime', 's_advanced');
	if(!$this->cacheLifeTime) {
		$this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES'];
	}
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$this->cacheLifeTime);
	$Cache_Lite=new Cache_Lite($options);
	$string=$this->cObj->data['uid'].'_manufacturers_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
}
if(!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content=$Cache_Lite->get($string)) {
	$content.='<div id="tx_multishop_pi1_core">';
	$str="SELECT m.manufacturers_id, m.manufacturers_name from tx_multishop_manufacturers m, tx_multishop_manufacturers_info mi where m.status=1 and mi.language_id='".$this->sys_language_uid."' and m.manufacturers_id=mi.manufacturers_id order by m.sort_order";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$manufacturers=array();
	while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
		$manufacturers[]=$row;
	}
	if(count($manufacturers) > 0) {
		$content.='<ul id="manufacturers_listing">';
		foreach($manufacturers as $row) {
			$content.='<li><a href="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id='.$row['manufacturers_id']).'">'.$row['manufacturers_name'].'</a></li>';
		}
		$content.='</ul>';
	}
	$content.='</div>';
	if($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($content);
	}
}
?>