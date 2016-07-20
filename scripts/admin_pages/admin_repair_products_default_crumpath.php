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
        $p2c_records=mslib_befe::getRecords($product['products_id'], 'tx_multishop_products_to_categories', 'products_id', array('is_deepest=1'), '', 'products_to_categories_id asc');
        if (is_array($p2c_records) && count($p2c_records)>1) {
            $set_default_path=true;
            foreach ($p2c_records as $p2c_record) {
                if ($p2c_record['default_path']>0) {
                    $set_default_path=false;
                    break;
                }
            }
            if ($set_default_path) {
                $updateArray=array();
                $updateArray['default_path']=1;
                $queryProduct=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_to_categories_id=\''.$p2c_records[0]['products_to_categories_id'].'\' and categories_id=\''.$p2c_records[0]['categories_id'].'\' and products_id=\''.$p2c_records[0]['products_id'].'\'', $updateArray);
                $GLOBALS['TYPO3_DB']->sql_query($queryProduct);
            }
        }
	}
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('repair_products_default_crumpath').'</h1></div>';
$content.='<p>'.$this->pi_getLL('admin_label_repair_products_default_crumpath_done').'</p>';
?>