<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
	'tx_multishop_products_options', // FROM ...
	'language_id=\'0\'', // WHERE...
	'', // GROUP BY...
	'sort_order', // ORDER BY...
	'' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
$options_data=array();
$target_merge_sb=array();
if ($rows) {
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$options_data[]=$row;
		$target_merge_sb[]=$row;
	}
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('merge_attribute_options').'</h1></div>
<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_attribute_options').'" method="post">
	<div class="account-field">
			<ul>
			';
foreach ($options_data as $option_val) {
	$content.='<li>';
	$content.='<div class="merge_attribute_options_wrapper">';
	$content.='<div class="merge_attribute_options_source">';
	$content.='<span class="label_merge_source">'.$this->pi_getLL('merge').'</span>';
	$content.='<input name="tx_multishop_pi1[merge_attribute_options_src]['.$option_val['products_options_id'].']" id="merge_src_'.$option_val['products_options_id'].'" type="checkbox" value="'.$option_val['products_options_id'].'" />';
	$content.='<label for="merge_src_'.$option_val['products_options_id'].'"><span>'.$option_val['products_options_name'].'</span></label>';
	$content.='</div>';
	$content.='<div class="merge_attribute_options_target">';
	$content.='<label for="merge_target_'.$target_option_val['products_options_id'].'"><span>'.$this->pi_getLL('merge_to').'</span></label>';
	$content.='<select id="merge_target_'.$target_option_val['products_options_id'].'" name="tx_multishop_pi1[merge_attribute_options_target]['.$option_val['products_options_id'].']">';
	$content.='<option value="0">'.$this->pi_getLL('choose').'...</option>';
	foreach ($target_merge_sb as $target_option_val) {
		if ($target_option_val['products_options_id']!=$option_val['products_options_id']) {
			$content.='<option value="'.$target_option_val['products_options_id'].'">'.$target_option_val['products_options_name'].'</option>';
		}
	}
	$content.='</select>';
	$content.='</div>';
	$content.='</div>';
	$content.='</li>'."\n";
}
$content.='
			</ul>
	</div>
	<div class="account-field">
			<label></label>
			<input type="submit" id="submit" class="msadmin_button" value="'.$this->pi_getLL('merge_selected').'" />
	</div>
</form>
';
if ($this->post && (is_array($this->post['tx_multishop_pi1']['merge_attribute_options_src']) and count($this->post['tx_multishop_pi1']['merge_attribute_options_src'])) &&
   (is_array($this->post['tx_multishop_pi1']['merge_attribute_options_target']) and count($this->post['tx_multishop_pi1']['merge_attribute_options_target']))) {
	foreach ($this->post['tx_multishop_pi1']['merge_attribute_options_src'] as $src_option_id => $item) {
		$target_option_id=$this->post['tx_multishop_pi1']['merge_attribute_options_target'][$src_option_id];
		if ($src_option_id>0 && $target_option_id>0) {
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options', 'products_options_id='.$src_option_id);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// update the deleted src option_id to target option id
			$updateArray=array();
			$updateArray['products_options_id']=$target_option_id;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery(' tx_multishop_products_options_values_to_products_options', 'products_options_id=\''.$src_option_id.'\'', $updateArray);
			$GLOBALS['TYPO3_DB']->sql_query($query);
			//
			$updateArray=array();
			$updateArray['options_id']=$target_option_id;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery(' tx_multishop_products_attributes', 'options_id=\''.$src_option_id.'\'', $updateArray);
			$GLOBALS['TYPO3_DB']->sql_query($query);
			//
			header('Location: ' . mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_attribute_options'));
		}
	}
}
?>