<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
/*
	when a web shop contains categories/products and a new language is being added, this script must be runned 1 time to clone the products to the specific language
*/
mslib_befe::loadLanguages();
$tables=array();
$tables['tx_multishop_cms_description']='id';
$tables['tx_multishop_categories_description']='categories_id';
$tables['tx_multishop_products_description']='products_id';
$tables['tx_multishop_manufacturers_cms']='manufacturers_id';
$tables['tx_multishop_manufacturers_info']='manufacturers_id';
$tables['tx_multishop_products_options']='products_options_id';
$tables['tx_multishop_products_options_values']='products_options_values_id';
$tables['tx_multishop_products_options_values_extra']='products_options_values_extra_id';
$tables['tx_multishop_reviews_description']='reviews_id';
foreach ($tables as $table=>$colkey) {
	$counter=0;
	$str="SELECT * from ".$table." where language_id=0 group by ".$colkey;
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			foreach ($this->languagesUids as $key=>$value) {
				$str2="SELECT ".$colkey." from ".$table." where ".$colkey."='".$row[$colkey]."' and language_id='".$key."'";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
					$row['language_id']=$key;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery($table, $row);
					$counter++;
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		$content.='<strong>'.$table.' has been updated ('.$counter.' queries runned).</strong><br />';
	}
}
?>