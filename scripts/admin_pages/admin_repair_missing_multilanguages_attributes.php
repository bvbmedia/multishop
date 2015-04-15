<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='';
$max_pages=2;
$prefix_domain=$this->FULL_HTTP_URL;
@unlink($log_file);
set_time_limit(7200);
ignore_user_abort(true);

// fix option multilanguage
$pa_option_name=$pa_option;
$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_name, products_options_id', // SELECT ...
	'tx_multishop_products_options', // FROM ...
	'language_id=\'0\'', // WHERE...
	'', // GROUP BY...
	'', // ORDER BY...
	'' // LIMIT ...
);
$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
	while ($option_data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk)) {
		$pa_option=$option_data['products_options_id'];
		$pa_option_name=$option_data['products_options_name'];
		foreach ($this->languages as $key=>$language) {
			if ($language['uid']>0) {
				$sql_chk2=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_name', // SELECT ...
					'tx_multishop_products_options', // FROM ...
					"products_options_id = '".$pa_option."' and language_id = '".$language['uid']."'", // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry_chk2=$GLOBALS['TYPO3_DB']->sql_query($sql_chk2);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk2)>0) {
					// prep for update
					$option_name=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk2);
					if (empty($option_name['products_options_name']) || !$option_name['products_options_name']) {
						$updateArray=array();
						$updateArray['products_options_name']=$pa_option_name;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\''.$pa_option.'\' and language_id=\''.$language['uid'].'\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				} else {
					$tmp_mtime=explode(" ", microtime());
					$mtime=array_sum($tmp_mtime);
					// prep for insertion
					$insertArray=array();
					$insertArray['products_options_id']=$pa_option;
					$insertArray['language_id']=$language['uid'];
					$insertArray['products_options_name']=$pa_option_name;
					$insertArray['listtype']='pulldownmenu';
					$insertArray['attributes_values']='0';
					$insertArray['sort_order']=$mtime;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
	}
}
// fix option values multilanguage
$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_values_name, products_options_values_id', // SELECT ...
	'tx_multishop_products_options_values', // FROM ...
	"language_id = '0'", // WHERE...
	'', // GROUP BY...
	'', // ORDER BY...
	'' // LIMIT ...
);
$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
	while ($option_value_data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk)) {
		$pa_value=$option_value_data['products_options_values_id'];
		$pa_option_value_name=$option_value_data['products_options_values_name'];
		foreach ($this->languages as $key=>$language) {
			if ($language['uid']>0) {
				$sql_chk2=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_values_name', // SELECT ...
					'tx_multishop_products_options_values', // FROM ...
					"products_options_values_id = '".$pa_value."' and language_id = '".$language['uid']."'", // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry_chk2=$GLOBALS['TYPO3_DB']->sql_query($sql_chk2);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk2)>0) {
					// prep for update
					$option_value_name=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk2);
					if (empty($option_value_name['products_options_values_name']) || !$option_value_name['products_options_values_name']) {
						$updateArray=array();
						$updateArray['products_options_values_name']=$pa_option_value_name;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values', 'products_options_values_id=\''.$pa_value.'\' and language_id=\''.$language['uid'].'\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				} else {
					$insertArray=array();
					$insertArray['products_options_values_id']=$pa_value;
					$insertArray['language_id']=$language['uid'];
					$insertArray['products_options_values_name']=$pa_option_value_name;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $insertArray);
					$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
	}
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('repair_missing_attribute_language_values').'</h1></div>';
$content.='<p>'.$this->pi_getLL('admin_label_missing_attribute_languages_values_fixed').'</p>';
?>