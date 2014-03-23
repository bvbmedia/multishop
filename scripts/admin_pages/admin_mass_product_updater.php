<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='<div class="main-heading"><h2>Mass Product Update</h2></div>';
if($this->post) {
	// update tax rate
	if(isset($this->post['rules_group_id'])) {
		$str="update tx_multishop_products set tax_id='".$this->post['rules_group_id']."' where page_uid='".$this->showCatalogFromPage."'";
		$res=$GLOBALS['TYPO3_DB']->sql_query($str);
		$content.='<strong>For '.$GLOBALS['TYPO3_DB']->sql_affected_rows().' product(s) the TAX rules group has been changed.</strong><br />';
	}
	// update prices
	if($this->post['percentage']) {
		$multiply=(100+$this->post['percentage'])/100;
		$str="update tx_multishop_products set products_price=(products_price*".$multiply.") where page_uid='".$this->showCatalogFromPage."'";
		$res=$GLOBALS['TYPO3_DB']->sql_query($str);
		$content.='<strong>Price update completed. '.$GLOBALS['TYPO3_DB']->sql_affected_rows().' products has been updated.</strong><br />';
		$str="update tx_multishop_specials set specials_new_products_price=(specials_new_products_price*".$multiply.") where page_uid='".$this->showCatalogFromPage."'";
		$res=$GLOBALS['TYPO3_DB']->sql_query($str);
	}
	if($this->ms['MODULES']['FLAT_DATABASE']) {
		mslib_befe::rebuildFlatDatabase();
	}
} else {
	$content.='
	<p>
	<strong>Please be careful with this module.</strong><br />This form is created to agressively update all products. It can be handful if you want to update the prices of each product and/or to change the VAT id for each product.<br />
	<br />
	</p>
	<form class="edit_form"  method="post" action="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page']).'" >
		<div class="account-field">
			<label for="percentage">Increase / decrease product prices by percentage</label>
			<input name="percentage" type="text" value="'.$this->post['percentage'].'" size="10" />%. Example: -10 to decrease product prices with 10%, or 10 to increase.
		</div>
<div class="account-field">
		<label for="rules_group_id">VAT Rate</label>	
		<select name="rules_group_id"><option value="">skip</option>
		<option value="0">No VAT</option>
		';
	$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$content.='<option value="'.$row['rules_group_id'].'">'.htmlspecialchars($row['name']).'</option>';
	}
	$content.='
		</select>
		</div>			
		<div class="account-field">
			<label for="">&nbsp;</label>
			<input name="Submit" type="submit" value="Update All Products" onclick="return confirm(\'Are you sure?\')" class="msadmin_button" />
		</div>			
		</form>
	';
}
?>