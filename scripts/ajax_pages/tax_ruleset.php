<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tax_group_id=$_REQUEST['tax_group_id'];
$current_price=$_REQUEST['current_price'];
$to_tax_include=$_REQUEST['to_tax_include'];
if (strpos($current_price, ':')!==false) {
	$price_list_format=explode(',', $current_price);
	$price_list_incl_tax=array();
	foreach ($price_list_format as $price_format) {
		$price_excl=explode(':', $price_format);
		if ($price_excl[1]>0) {
			$data=mslib_fe::getTaxRuleSet($tax_group_id, $price_excl[1], $to_tax_include);
			if ($to_tax_include=='true') {
				$price_excl[1]=str_replace(',', '', $data['price_including_tax']);
			} else {
				$price_excl[1]=str_replace(',', '', $data['price_excluding_tax']);
			}
		} else {
			$price_excl[1]=0;
		}
		$price_excl[1]=mslib_fe::taxDecimalCrop($price_excl[1]);
		$price_list_incl_tax[]=implode(':', $price_excl);
	}
	//$sc_price_display_incl 	= $row3['price'];
	if ($to_tax_include=='true') {
		$data['price_including_tax']=implode(',', $price_list_incl_tax);
	} else {
		$data['price_excluding_tax']=implode(',', $price_list_incl_tax);
	}
} else {
	if (strstr($current_price, ",")) {
		$current_price=str_replace(",", ".", $current_price);
	}
	$data=mslib_fe::getTaxRuleSet($tax_group_id, $current_price, $to_tax_include);
	$data['price_excluding_tax']=str_replace(',', '', $data['price_excluding_tax']);
}
$json_data=json_encode($data, ENT_NOQUOTES);
echo $json_data;
exit();
?>