<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='';
$max_pages=2;
$prefix_domain=$this->FULL_HTTP_URL;
@unlink($log_file);
set_time_limit(7200);
ignore_user_abort(true);
$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_id', // SELECT ...
	'tx_multishop_products', // FROM ...
	'', // WHERE...
	'', // GROUP BY...
	'products_id asc', // ORDER BY...
	'' // LIMIT ...
);
$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
	while ($product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk)) {
        mslib_befe::setProductDefaultCrumpath($product['products_id']);
	}
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('repair_products_default_crumpath').'</h1></div>';
$content.='<p>'.$this->pi_getLL('admin_label_repair_products_default_crumpath_done').'</p>';
?>