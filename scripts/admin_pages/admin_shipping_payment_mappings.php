<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$shipping_methods=mslib_fe::loadShippingMethods();
$payment_methods=mslib_fe::loadPaymentMethods();
if($this->post) {
	foreach($shipping_methods as $shipping_method) {
		foreach($payment_methods as $payment_method) {
			if($this->post['checkbox'][$shipping_method['id']][$payment_method['id']]) {
				// add mapping
				$insertArray=array();
				$insertArray['shipping_method']=$shipping_method['id'];
				$insertArray['payment_method']=$payment_method['id'];
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_shipping_mappings', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				// delete mapping
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_payment_shipping_mappings', 'shipping_method=\''.$shipping_method['id'].'\' and payment_method=\''.$payment_method['id'].'\'');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
}
if(count($payment_methods)) {
	$content.='<div class="main-heading"><h2>'.$this->pi_getLL('shipping_to_payment_mapping').'</h2></div>';
	$colspan=4;
	$tr_type='even';
	if(count($shipping_methods)) {
		$content.='<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'">';
		$content.='<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">';
		$content.='<tr><th>&nbsp;</th>';
		foreach($payment_methods as $payment_method) {
			$content.='<th>'.$payment_method['name'].'</th>';
		}
		$content.='</tr>';
		foreach($shipping_methods as $row) {
			//		$content.='<h3>'.$cat['name'].'</h3>';
			if(!$tr_type or $tr_type == 'even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$content.='<tr class="'.$tr_type.'">
			<td><strong>'.$row['name'].'</strong></td>';
			foreach($payment_methods as $payment_method) {
				$content.='<td>';
				$content.='<input name="checkbox['.$row['id'].']['.$payment_method['id'].']" type="checkbox" value="1" onclick="this.form.submit();" ';
				$str2="SELECT * from tx_multishop_payment_shipping_mappings where payment_method='".$payment_method['id']."' and shipping_method='".$row['id']."'";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($qry2) > 0) {
					$content.='checked';
				}
				$content.=' /></td>';
			}
			$content.='</tr>';
		}
		$content.='</table>';
		$content.='<input name="param" type="hidden" value="update_mapping" /></form>';
	} else {
		$content.='Currently there isn\'t any shipping methods defined.';
	}
} else {
	$content.='Currently there isn\'t any payment methods defined.';
}
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>