<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$shipping_methods=mslib_fe::loadShippingMethods();
$payment_methods=mslib_fe::loadPaymentMethods();
$zones=mslib_fe::loadAllCountriesZones();
if ($this->post) {
	foreach ($zones['zone_id'] as $zone_id) {
		foreach ($shipping_methods as $shipping_method) {
			if ($this->post['shipping_zone'][$zone_id][$shipping_method['id']]) {
				// add mapping
				$insertArray=array();
				$insertArray['zone_id']=$zone_id;
				$insertArray['shipping_method_id']=$shipping_method['id'];
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods_to_zones', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				// delete mapping
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_shipping_methods_to_zones', 'zone_id=\''.$zone_id.'\' and shipping_method_id=\''.$shipping_method['id'].'\'');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
}
if (count($zones['zone_id'])) {
	$content.='<div class="main-heading"><h2>'.$this->pi_getLL('shipping_to_zone_mapping', 'Shipping to Zone Mappings').'</h2></div>';
	$colspan=4;
	$tr_type='even';
	if (count($shipping_methods)) {
		$content.='<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'">';
		$content.='<table width="100%" border="0" align="center" class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">';
		$content.='<tr>';
		$content.='<th width="100px">Zones</th>';
		$content.='<th colspan="'.count($shipping_methods).'">Shipping</th>';
		$content.='</tr>';
		foreach ($zones['zone_id'] as $zone_idx=>$zone_id) {
			$content.='<tr>';
			$content.='<td>'.$zones['zone_name'][$zone_idx].' ('.implode('<br/> ', $zones['countries'][$zone_id]).')</td>';
			foreach ($shipping_methods as $shipping_method) {
				$vars=unserialize($shipping_method['vars']);
				$sql_check="select id from tx_multishop_shipping_methods_to_zones where zone_id = ".$zone_id." and shipping_method_id = ".$shipping_method['id'];
				$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check)) {
					$content.='<td><input type="checkbox" name="shipping_zone['.$zone_id.']['.$shipping_method['id'].']" id="shipping_zone_'.$zone_id.'_'.$shipping_method['id'].'" checked="checked" onclick="this.form.submit()"><label for="shipping_zone_'.$zone_id.'_'.$shipping_method['id'].'">'.$vars['name'][0].'</label></td>';
				} else {
					$content.='<td><input type="checkbox" name="shipping_zone['.$zone_id.']['.$shipping_method['id'].']" id="shipping_zone_'.$zone_id.'_'.$shipping_method['id'].'" onclick="this.form.submit()"><label for="shipping_zone_'.$zone_id.'_'.$shipping_method['id'].'">'.$vars['name'][0].'</label></td>';
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
	$content.='Currently there isn\'t any shipping methods defined.';
}
$content.='<p class="extra_padding_bottom"><a class="btn btn-success" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>