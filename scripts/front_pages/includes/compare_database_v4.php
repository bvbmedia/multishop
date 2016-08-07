<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$str="select products_id from tx_multishop_orders_products where categories_id=0 group by products_id";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$filter=array();
	$filter[]='is_deepest=1';
	$record=mslib_befe::getRecord($row['products_id'],'tx_multishop_products_to_categories','products_id',$filter);

	if (is_array($record) && $record['crumbar_identifier']) {
		$updateArray=array();

		$catIds=explode(',',$record['crumbar_identifier']);
		foreach ($catIds as $catId) {
			$counter=0;
			$category=mslib_befe::getRecord($catId,'tx_multishop_categories_description','categories_id');
			if ($category['categories_id']) {
				$updateArray['categories_id_'.$counter]=$category['categories_id'];
				$updateArray['categories_name_'.$counter]=$category['categories_name'];
			}
			$counter++;
		}
		$updateArray['categories_id']=$updateArray['categories_id_'.($counter-1)];
		//$updateArray['categories_name']=$updateArray['categories_name_'.($counter-1)];
		$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'products_id=\''.$row['products_id'].'\'', $updateArray);
		$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
		$messages[]=$query2;
	}
}
$str="select image_filename from tt_address limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tt_address` ADD `image_filename` tinytext";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}
/*$key='PRICE_FILTER_WITHOUT_CATEGORY_QUERY_STRING';
$title='Price Filter without category query string';
$description='Optional field.';
$default_value='0';
if (!isset($settings['GLOBAL_MODULES'][$key])) {
	$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 3, NULL, NULL, now(), 'tep_cfg_select_option(array(''0'',''1''),');";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$messages[]=$str;
}*/

$str="describe `tx_multishop_products_options`";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if ($row['Field']=='products_options_name') {
		if ($row['Type']=='varchar(64)') {
			$str2="ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_name`  `products_options_name` varchar(150)";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			$messages[]=$str2;
		}
	}
}
$str="describe `tx_multishop_products_options_values`";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if ($row['Field']=='products_options_values_name') {
		if ($row['Type']=='varchar(64)') {
			$str2="ALTER TABLE  `tx_multishop_products_options_values` CHANGE  `products_options_values_name`  `products_options_values_name` varchar(150)";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			$messages[]=$str2;
		}
	}
}
$str="describe `tx_multishop_products_options`";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if ($row['Field']=='products_options_descriptions') {
		if ($row['Type']=='varchar(255)') {
			$str2="ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_descriptions`  `products_options_descriptions` text";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			$messages[]=$str2;
		}
	}
}


?>