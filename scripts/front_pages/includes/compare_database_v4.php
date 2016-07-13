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

?>