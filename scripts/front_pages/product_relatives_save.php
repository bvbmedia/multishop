<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if($_REQUEST['status'] == "del") {
	$where_relatives='products_id = '.$_REQUEST['pid'].' AND relative_product_id = '.$_REQUEST['product_id'];
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_relative_products', $where_relatives);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	if($res) {
		echo "Deleted is  OK";
	}
} else {
	$where_relatives='products_id = '.$_REQUEST['pid'].' AND relative_product_id = '.$_REQUEST['product_id'];
	$query_checking=$GLOBALS['TYPO3_DB']->SELECTquery('count(*) as jum', // SELECT ...
		'tx_multishop_products_to_relative_products', // FROM ...
		$where_relatives, // WHERE.
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$res_checking=$GLOBALS['TYPO3_DB']->sql_query($query_checking);
	$row_check=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
	//die($query_checking);
	//if empty on database => save products
	if($row_check['jum'] == 0) {
		//echo $row_check['jum'];
		$updateArray=array();
		$updateArray=array(
			"products_id"=>$_REQUEST['pid'],
			"relative_product_id"=>$_REQUEST['product_id']);
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if($res) {
			echo "Save database succesfully";
		}
	}
}
?>