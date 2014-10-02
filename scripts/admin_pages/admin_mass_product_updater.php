<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='<div class="main-heading"><h2>Mass Product Update</h2></div>';
if ($this->post) {
	// update tax rate
	if (isset($this->post['rules_group_id']) && !empty($this->post['rules_group_id'])) {
		$str="update tx_multishop_products set tax_id='".$this->post['rules_group_id']."' where page_uid='".$this->showCatalogFromPage."'";
		$res=$GLOBALS['TYPO3_DB']->sql_query($str);
		$content.='<strong>'.sprintf($this->pi_getLL('admin_mass_updater_for_x_products_the_tax_rules_group_has_been_changed'), $GLOBALS['TYPO3_DB']->sql_affected_rows()).'</strong><br />';
	}
	// update prices
	if (!empty($this->post['amount'])) {
		$originalAmount=$this->post['amount'];
		// convert the dec sign from comma to dot
		if (strpos($originalAmount, ',')!==false) {
			$originalAmount=str_replace(',', '.', $originalAmount);
		}
		if (isset($this->post['amount_vat']) && $this->post['amount_vat']>0) {
			$sql_get_products="select p.products_id, pt.rate as tax_rate from tx_multishop_products p left join tx_multishop_taxes pt on pt.tax_id = p.tax_id where page_uid='".$this->showCatalogFromPage."'";
			$qry_get_products=$GLOBALS['TYPO3_DB']->sql_query($sql_get_products);
			$sql_affected_rows=0;
			while ($rs_get_products=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_get_products)) {
				$amount=$originalAmount;
				$tax_rate=$rs_get_products['tax_rate'];
				if ($tax_rate>0) {
					$amount=($amount/(100+$tax_rate))*100;
				}
				if (($this->ADMIN_USER && !$this->ROOTADMIN_USER) || ($this->ROOTADMIN_USER && in_array('products', $this->post['tx_multishop_pi1']['price_update_area']))) {
					$str="update tx_multishop_products set products_price=(products_price+".$amount.") where products_id = '".$rs_get_products['products_id']."' and page_uid='".$this->showCatalogFromPage."'";
					$res=$GLOBALS['TYPO3_DB']->sql_query($str);
					$sql_affected_rows+=$GLOBALS['TYPO3_DB']->sql_affected_rows();
				}
				if (($this->ADMIN_USER && !$this->ROOTADMIN_USER) || ($this->ROOTADMIN_USER && in_array('specials', $this->post['tx_multishop_pi1']['price_update_area']))) {
					$str="update tx_multishop_specials set specials_new_products_price=(specials_new_products_price+".$amount.") where products_id = '".$rs_get_products['products_id']."' and page_uid='".$this->showCatalogFromPage."'";
					$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				}
			}
			$content.='<strong>'.$this->pi_getLL('admin_mass_updater_flash_msg_price_update_complete').' '.sprintf($this->pi_getLL('admin_mass_updater_flash_msg_x_products_has_been_updated'), $sql_affected_rows).'</strong><br />';
			if ($sql_affected_rows>0 && $this->ms['MODULES']['FLAT_DATABASE']) {
				// if the flat database module is enabled we have to sync the changes to the flat table
				set_time_limit(86400);
				ignore_user_abort(true);
				mslib_befe::rebuildFlatDatabase();
			}
		} else {
			$amount=$originalAmount;
			if (($this->ADMIN_USER && !$this->ROOTADMIN_USER) || ($this->ROOTADMIN_USER && in_array('products', $this->post['tx_multishop_pi1']['price_update_area']))) {
				$str="update tx_multishop_products set products_price=(products_price+".$amount.") where page_uid='".$this->showCatalogFromPage."'";
				$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				$sql_affected_rows=$GLOBALS['TYPO3_DB']->sql_affected_rows();
				$content.='<strong>'.sprintf($this->pi_getLL('admin_mass_updater_flash_msg_x_products_has_been_updated'), $sql_affected_rows).'</strong><br />';
			}
			if (($this->ADMIN_USER && !$this->ROOTADMIN_USER) || ($this->ROOTADMIN_USER && in_array('specials', $this->post['tx_multishop_pi1']['price_update_area']))) {
				$str="update tx_multishop_specials set specials_new_products_price=(specials_new_products_price+".$amount.") where page_uid='".$this->showCatalogFromPage."'";
				$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				$sql_affected_rows=$GLOBALS['TYPO3_DB']->sql_affected_rows();
				$content.='<strong>'.sprintf($this->pi_getLL('admin_mass_updater_flash_msg_x_specials_has_been_updated'), $sql_affected_rows).'</strong><br />';
			}
			if ($sql_affected_rows>0 && $this->ms['MODULES']['FLAT_DATABASE']) {
				// if the flat database module is enabled we have to sync the changes to the flat table
				set_time_limit(86400);
				ignore_user_abort(true);
				mslib_befe::rebuildFlatDatabase();
			}
		}
	}
	if ($this->post['percentage']) {
		$multiply=(100+$this->post['percentage'])/100;
		if (($this->ADMIN_USER && !$this->ROOTADMIN_USER) || ($this->ROOTADMIN_USER && in_array('products', $this->post['tx_multishop_pi1']['price_update_area']))) {
			$str="update tx_multishop_products set products_price=(products_price*".$multiply.") where page_uid='".$this->showCatalogFromPage."'";
			$res=$GLOBALS['TYPO3_DB']->sql_query($str);
			$sql_affected_rows=$GLOBALS['TYPO3_DB']->sql_affected_rows();
			$content.='<strong>'.sprintf($this->pi_getLL('admin_mass_updater_flash_msg_x_products_has_been_updated'), $GLOBALS['TYPO3_DB']->sql_affected_rows()).'</strong><br />';
		}
		if (($this->ADMIN_USER && !$this->ROOTADMIN_USER) || ($this->ROOTADMIN_USER && in_array('specials', $this->post['tx_multishop_pi1']['price_update_area']))) {
			$str="update tx_multishop_specials set specials_new_products_price=(specials_new_products_price*".$multiply.") where page_uid='".$this->showCatalogFromPage."'";
			$res=$GLOBALS['TYPO3_DB']->sql_query($str);
			$sql_affected_rows=$GLOBALS['TYPO3_DB']->sql_affected_rows();
			$content.='<strong>'.sprintf($this->pi_getLL('admin_mass_updater_flash_msg_x_specials_has_been_updated'), $sql_affected_rows).'</strong><br />';
		}
		if (($this->ADMIN_USER && !$this->ROOTADMIN_USER) || ($this->ROOTADMIN_USER && in_array('attributes', $this->post['tx_multishop_pi1']['price_update_area']))) {
			$sql_products="select products_id from tx_multishop_products where page_uid='".$this->showCatalogFromPage."'";
			$qry_products=$GLOBALS['TYPO3_DB']->sql_query($sql_products);
			$sql_attribute_values_affected_rows=0;
			while ($rs_products=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_products)) {
				$str="update tx_multishop_products_attributes set options_values_price=(options_values_price*".$multiply.") where options_values_price>0 and products_id='".$rs_products['products_id']."'";
				$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				$sql_attribute_values_affected_rows+=$GLOBALS['TYPO3_DB']->sql_affected_rows();

			}
			$content.='<strong>'.sprintf($this->pi_getLL('admin_mass_updater_flash_msg_x_attributes_updated'), $sql_attribute_values_affected_rows).'</strong><br />';
		}
	}
	if ($this->ms['MODULES']['FLAT_DATABASE']) {
		mslib_befe::rebuildFlatDatabase();
	}
} else {
	$content.='
	<p>
	<strong>'.$this->pi_getLL('admin_mass_updater_warning_a').'</strong><br />'.$this->pi_getLL('admin_mass_updater_warning_b').'<br />
	<br />
	</p>
	<form class="edit_form"  method="post" action="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page']).'" >
		<div class="account-field">
			<label for="percentage">'.$this->pi_getLL('admin_mass_updater_increase_decrease_product_price_by_percentage').'</label>
			<input name="percentage" type="text" value="'.$this->post['percentage'].'" size="10" id="percentage" />%. '.$this->pi_getLL('admin_mass_updater_example_update').'<br/>- '.strtoupper($this->pi_getLL('or')).' -<br/>
		</div>
		<div class="account-field">
			<label for="amount">'.$this->pi_getLL('admin_mass_updater_by_amount').'</label>
			<input name="amount" id="amount" type="text" value="'.$this->post['amount'].'" size="10" />&nbsp;<input name="amount_vat" id="amount_vat" type="checkbox" value="1" checked="checked" />
			<label for="amount_vat">'.$this->pi_getLL('admin_mass_updater_substract_increase_price_based_in_incl_vat').'</label>
		</div>';
	if ($this->ROOTADMIN_USER) {
		$content.='<div class="account-field">
			<label for="amount">'.$this->pi_getLL('admin_mass_updater_price_area_for_update').'</label>
			<input name="tx_multishop_pi1[price_update_area][]" type="checkbox" value="products" />&nbsp;'.ucfirst($this->pi_getLL('products')).'&nbsp;&nbsp;<input name="tx_multishop_pi1[price_update_area][]" type="checkbox" value="specials" />&nbsp;'.ucfirst($this->pi_getLL('specials')).'&nbsp;&nbsp;<input name="tx_multishop_pi1[price_update_area][]" type="checkbox" value="attributes" />&nbsp;'.ucfirst($this->pi_getLL('attributes')).'
		</div>';
	}
	$content.='
		<div class="hr"></div>
		<div class="account-field">
			<label for="rules_group_id">'.$this->pi_getLL('admin_vat_rate').'</label>
			<select name="rules_group_id"><option value="">'.$this->pi_getLL('skip').'</option>
				<option value="0">'.$this->pi_getLL('admin_no_tax').'</option>';
	$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$content.='<option value="'.$row['rules_group_id'].'">'.htmlspecialchars($row['name']).'</option>';
	}
	$content.='
			</select>
		</div>
		<div class="account-field">
			<label for="">&nbsp;</label>
			<input name="Submit" type="submit" value="'.$this->pi_getLL('admin_mass_updater_update_all_products').'" onclick="return confirm(\''.$this->pi_getLL('admin_label_js_are_you_sure').'\')" class="msadmin_button" />
		</div>
		</form>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(document).on("change", "#percentage", function(){
					if ($(this).val() != "") {
						$("#amount").val("");
					}
				});
				$(document).on("change", "#amount", function(){
					if ($(this).val() != "") {
						$("#percentage").val("");
					}
				});
			});
		</script>';
}
?>