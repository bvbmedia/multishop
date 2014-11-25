<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$shipping_methods=mslib_fe::loadShippingMethods();
$payment_methods=mslib_fe::loadPaymentMethods();
$zones=mslib_fe::loadAllCountriesZones();
if ($this->post) {
	foreach ($zones['zone_id'] as $zone_id) {
		foreach ($payment_methods as $payment_method) {
			if ($this->post['payment_zone'][$zone_id][$payment_method['id']]) {
				// add mapping
				$insertArray=array();
				$insertArray['zone_id']=$zone_id;
				$insertArray['payment_method_id']=$payment_method['id'];
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods_to_zones', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				// delete mapping
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_payment_methods_to_zones', 'zone_id=\''.$zone_id.'\' and payment_method_id=\''.$payment_method['id'].'\'');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
}
if (count($zones['zone_id'])) {
	$content.='<div class="main-heading"><h2>'.$this->pi_getLL('payment_to_zone_mapping', 'Payment to Zone Mappings').'</h2></div>';
	$colspan=4;
	$tr_type='even';
	if (count($payment_methods)) {
		$content.='<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'">';
		$content.='<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">';
		$content.='<tr>';
		$content.='<th width="100px">Zones</th>';
		$content.='<th colspan="'.count($payment_methods).'">Payments</th>';
		$content.='</tr>';
		foreach ($zones['zone_id'] as $zone_idx=>$zone_id) {
			$content.='<tr>';
			$content.='<td>'.$zones['zone_name'][$zone_idx].' ('.implode('<br/> ', $zones['countries'][$zone_id]).')</td>';
			foreach ($payment_methods as $payment_method) {
				$vars=unserialize($payment_method['vars']);
				$sql_check="select id from tx_multishop_payment_methods_to_zones where zone_id = ".$zone_id." and payment_method_id = ".$payment_method['id'];
				$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check)) {
					$content.='<td><input type="checkbox" name="payment_zone['.$zone_id.']['.$payment_method['id'].']" id="payment_zone_'.$zone_id.'_'.$payment_method['id'].'" checked="checked" onclick="this.form.submit()"><label for="payment_zone_'.$zone_id.'_'.$payment_method['id'].'">'.$vars['name'][0].'</label></td>';
				} else {
					$content.='<td><input type="checkbox" name="payment_zone['.$zone_id.']['.$payment_method['id'].']" id="payment_zone_'.$zone_id.'_'.$payment_method['id'].'" onclick="this.form.submit()"><label for="payment_zone_'.$zone_id.'_'.$payment_method['id'].'">'.$vars['name'][0].'</label></td>';
				}
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
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>