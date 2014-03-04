<?php
$content.='Fix B2C shops that has cents issues';
$str="SELECT * FROM tx_multishop_products";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);		
while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
	$product=mslib_fe::getProduct($row['products_id']);
	$final_price=mslib_fe::final_products_price($product);		
	
	// first we have to force the calculation to use 16 decimals precision (otherwise php.ini default 14 will overrule)
	$amount=number_format(($final_price/(100+($product['tax_rate']*100))*100),16);
	// next we chop the 16th decimal so it cannot influence rounding
	$explode=explode('.',$amount);
	$newAmount=$explode[0].'.'.substr($explode[1],0,15);

	$updateArray=array();
	$updateArray['products_price'] = $newAmount;
	$query2 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$row['products_id'].'\'',$updateArray);
	$res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
	
	// delete specials
	$query2 = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_specials','products_id='.$row['products_id']);	
	$res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);	
} 
?>