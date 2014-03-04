<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$content = '';
if ($_GET['a'] == 'update_options') {
	$str = "select * from tx_multishop_products_options where language_id = '".$this->sys_language_uid."' order by sort_order";
	$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$content .= '<option value="'.$row['products_options_id'].'">'.$row['products_options_name'].'</option>';
	}
}

if ($_GET['a'] == 'update_attributes') {
	$str = "select optval.* from tx_multishop_products_options_values as optval, tx_multishop_products_options_values_to_products_options as optval2opt where optval2opt.products_options_id = ".$_GET['opid']." and optval2opt.products_options_values_id = optval.products_options_values_id and optval.language_id = '".$this->sys_language_uid."' order by optval2opt.sort_order";
	$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$content .= '<option value="'.$row['products_options_values_id'].'">'.$row['products_options_values_name'].'</option>';
	}
}

if ($_GET['a'] == 'add_option') {
	//die(print_r($_GET));
	$optid = 0;
	
	if (!empty($_GET['optname'])) {
		$rowid = $_GET['rowid'] + 1;
		$content .= '<tr><td colspan="5"><div class="wrap-attributes"><table><tr id="attributes_select_box_' . $rowid . '_a" class="option_row"><td><select name="options[]" id="option_' . $rowid . '" onchange="updateAttribute(this.value,\'' . $rowid . '\');"><option value="">choose option</option>';

		$sql_chk = "select products_options_id from tx_multishop_products_options where products_options_name = '".addslashes($_GET['optname'])."' and language_id = '".$this->sys_language_uid."' order by sort_order";
		$qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk) > 0) {
			$rs_chk = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
			$optid = $rs_chk['products_options_id'];
		} else {
			$sql_chk = "select products_options_id from tx_multishop_products_options order by products_options_id desc limit 1";
			$qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
			$rs_chk = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
			$max_optid = $rs_chk['products_options_id'] + 1;
		
			$sql_ins = "insert into tx_multishop_products_options (products_options_id, language_id, products_options_name, listtype, attributes_values, sort_order) values ('".$max_optid."', '0', '".addslashes($_GET['optname'])."', 'pulldownmenu', '0', '".$max_optid."')";
			$GLOBALS['TYPO3_DB']->sql_query($sql_ins);
			$optid = $max_optid;
		}
		
		$str = "select * from tx_multishop_products_options where language_id = '".$this->sys_language_uid."' order by sort_order";
		$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
			if ($optid == $row['products_options_id']) {
				$content .= '<option value="'.$row['products_options_id'].'" selected="selected">'.$row['products_options_name'].'</option>';
			} else {
				$content .= '<option value="'.$row['products_options_id'].'">'.$row['products_options_name'].'</option>';
			}
		}
		
		$content .= '</select></td><td><select name="attributes[]" id="attribute_' . $rowid . '"><option value="">choose attribute</option>';
		
		if (!empty($_GET['optval'])) {
			$sql_chk = "select products_options_values_id from tx_multishop_products_options_values where products_options_values_name = '".addslashes($_GET['optval'])."' and language_id = '".$this->sys_language_uid."'";
			$qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk) > 0) {
				$rs_chk = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
				$valid = $rs_chk['products_options_values_id'];
			
			} else {
				$sql_ins = "insert into tx_multishop_products_options_values (products_options_values_id, language_id, products_options_values_name) values ('', '".$this->sys_language_uid."', '".addslashes($_GET['optval'])."')";
				$GLOBALS['TYPO3_DB']->sql_query($sql_ins);
				$valid = $GLOBALS['TYPO3_DB']->sql_insert_id();
			}
			
			$sql_chk = "select products_options_values_to_products_options_id from tx_multishop_products_options_values_to_products_options where products_options_id = '".$optid."' and  products_options_values_id = '".$valid."'";
			$qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk) == 0) {
				$sql_ins = "insert into tx_multishop_products_options_values_to_products_options (products_options_values_to_products_options_id, products_options_id, products_options_values_id,sort_order) values ('', '".$optid."', '".$valid."','".time()."')";
				$GLOBALS['TYPO3_DB']->sql_query($sql_ins);
			}
			
			
			$str = "select optval.* from tx_multishop_products_options_values as optval, tx_multishop_products_options_values_to_products_options as optval2opt where optval2opt.products_options_id = ".$optid." and optval2opt.products_options_values_id = optval.products_options_values_id and optval.language_id = '".$this->sys_language_uid."' order by optval2opt.sort_order";
			$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
			while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
				if ($valid == $row['products_options_values_id']) {
					$content .= '<option value="'.$row['products_options_values_id'].'" selected="selected">'.$row['products_options_values_name'].'</option>';
				} else {
					$content .= '<option value="'.$row['products_options_values_id'].'">'.$row['products_options_values_name'].'</option>';
				}
			}
	
		}
		
		$content .= '</select></select></td><td><input type="text" name="prefix[]" value="+" /></td><td><div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msAttributesPriceExcludingVat"><label for="display_name">Excl. VAT</label></div><div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msAttributesPriceIncludingVat"><label for="display_name">Incl. VAT</label></div><div class="msAttributesField hidden"><input type="hidden" name="price[]" /></div></td><td><input type="button" value="[-]" onclick="removeAttributeRow(\'' . $rowid . '\')"></td></tr>';
		$content .= '<tr id="attributes_select_box_' . $rowid . '_b" class="option_row"><td>&nbsp;</td><td><input type="text" name="manual_attributes[]" /></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table></div></td></tr>';
	}
}
echo $content;
exit();
?>