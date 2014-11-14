<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
/*
sort the attributes on alphabet with integer sorting logics
$query=$GLOBALS['TYPO3_DB']->sql_query("select products_options_id from tx_multishop_products_options where language_id=0");
while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query))
{
	// iterating the values
	$counter=0;
	$query2=$GLOBALS['TYPO3_DB']->sql_query("select povp.products_options_id, pov.products_options_values_id from tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pov.language_id=0 and povp.products_options_id='".$row['products_options_id']."' and pov.products_options_values_id=povp.products_options_values_id order by CAST(pov.products_options_values_name AS SIGNED)");
	while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query2))
	{
		// updating the value
		$where = "products_options_id='".$row['products_options_id']."' and products_options_values_id = ".$row2['products_options_values_id'];
		$updateArray = array(
			'sort_order' => $counter
		);
		$query3 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', $where,$updateArray);
		echo $query3.'<br>';
		$res = $GLOBALS['TYPO3_DB']->sql_query($query3);
		$counter++;
	}
}
die();
*/
$navItems=array();
$navItems['categories']=$this->pi_getLL('categories');
$navItems['products']=$this->pi_getLL('products');
$navItems['products_attributes']=$this->pi_getLL('admin_label_products_attributes');
$navItems['manufacturers']=$this->pi_getLL('manufacturers');
$navItems['orders']=$this->pi_getLL('orders');
$navItems['everything']=$this->pi_getLL('admin_label_everything');
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_label_clear_database').'</h1></div>
<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_system_clear_database').'" method="post">
	<div class="account-field">
			<label>'.$this->pi_getLL('admin_label_items_to_delete').'</label>
			<ul>
			';
foreach ($navItems as $key=>$val) {
	$content.='<li><input name="tx_multishop_pi1[items][]" type="checkbox" value="'.$key.'" /> '.$val.'</li>'."\n";
}
$content.='
			</ul>
	</div>
	<div class="account-field">
			<label></label>
			<input type="submit" id="submit" class="msadmin_button" value="'.$this->pi_getLL('delete').'" />
	</div>
</form>
';
if ($this->post and is_array($this->post['tx_multishop_pi1']['items']) and count($this->post['tx_multishop_pi1']['items'])) {
	set_time_limit(86400);
	ignore_user_abort(true);
	foreach ($this->post['tx_multishop_pi1']['items'] as $item) {
		switch ($item) {
			case 'orders':
				$tables='tx_multishop_orders
				tx_multishop_orders_products
				tx_multishop_orders_products_attributes
				tx_multishop_orders_status_history';
				$tableArray=explode("\n", $tables);
				foreach ($tableArray as $table) {
					$table=trim($table);
					$qry=$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE '.$table);
				}
				/*
				$query=$GLOBALS['TYPO3_DB']->sql_query("select orders_id from tx_multishop_orders where page_uid='".$this->showCatalogFromPage."'");
				while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query)) != false) {
					$query2 = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orders', 'orders_id='.$row['orders_id']);
					$res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
					$query2 = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orders_products', 'orders_id='.$row['orders_id']);
					$res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
					$query2 = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orders_products_attributes', 'orders_id='.$row['orders_id']);
					$res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
					$query2 = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orders_status_history', 'orders_id='.$row['orders_id']);
					$res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
				}
				*/
				$content.='<p>'.$this->pi_getLL('admin_label_orders_has_been_cleared').'</p>';
				break;
			case 'categories':
				$query=$GLOBALS['TYPO3_DB']->sql_query("select categories_id from tx_multishop_categories where parent_id='0' and page_uid='".$this->showCatalogFromPage."'");
				while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($query))!=false) {
					mslib_befe::deleteCategory($row['categories_id']);
				}
				$content.='<p>'.$this->pi_getLL('admin_label_categories_has_been_cleared').'</p>';
				break;
			case 'products':
				$query=$GLOBALS['TYPO3_DB']->sql_query("select products_id from tx_multishop_products where page_uid='".$this->showCatalogFromPage."'");
				while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($query))!=false) {
					mslib_befe::deleteProduct($row['products_id']);
				}
				$content.='<p>'.$this->pi_getLL('admin_label_products_has_been_cleared').'</p>';
				break;
			case 'manufacturers':
				$string='TRUNCATE `tx_multishop_manufacturers`;
				TRUNCATE `tx_multishop_manufacturers_cms`;
				TRUNCATE `tx_multishop_manufacturers_info`;';
				$array=explode("\n", $string);
				foreach ($array as $item) {
					if ($item) {
						$qry=$GLOBALS['TYPO3_DB']->sql_query($item);
					}
				}
				$content.='<p>'.$this->pi_getLL('admin_label_manufacturers_has_been_cleared').'</p>';
				break;
			case 'products_attributes':
				//TRUNCATE `tx_multishop_products_attributes_extra`;
				//TRUNCATE `tx_multishop_products_options_values_extra`;
			$string='TRUNCATE `tx_multishop_products_attributes`;
				TRUNCATE `tx_multishop_products_attributes_download`;
				TRUNCATE `tx_multishop_products_options`;
				TRUNCATE `tx_multishop_products_options_values`;
				TRUNCATE `tx_multishop_products_options_values_to_products_options`
				TRUNCATE `tx_multishop_attributes_options_groups`
				TRUNCATE `tx_multishop_attributes_options_groups_to_products_options`';
				$array=explode("\n", $string);
				foreach ($array as $item) {
					if ($item) {
						$qry=$GLOBALS['TYPO3_DB']->sql_query($item);
					}
				}
				$content.='<p>products_attributes has been cleared.</p>';
				break;
			case 'everything':
				//TRUNCATE `tx_multishop_products_attributes_extra`;
				$string='TRUNCATE `tx_multishop_categories`;
				TRUNCATE `tx_multishop_categories_description`;
				TRUNCATE `tx_multishop_manufacturers`;
				TRUNCATE `tx_multishop_manufacturers_cms`;
				TRUNCATE `tx_multishop_manufacturers_info`;
				TRUNCATE `tx_multishop_products`;
				'.(($this->ms['MODULES']['FLAT_DATABASE']) ? 'TRUNCATE `tx_multishop_products_flat`;' : '').'
				TRUNCATE `tx_multishop_products_attributes`;
				TRUNCATE `tx_multishop_products_attributes_download`;
				TRUNCATE `tx_multishop_products_description`;
				TRUNCATE `tx_multishop_products_faq`;
				TRUNCATE `tx_multishop_products_options`;
				TRUNCATE `tx_multishop_products_options_values`;
				TRUNCATE `tx_multishop_products_options_values_extra`;
				TRUNCATE `tx_multishop_products_options_values_to_products_options`;
				TRUNCATE `tx_multishop_products_to_categories`;
				TRUNCATE `tx_multishop_products_to_extra_options`;
				TRUNCATE `tx_multishop_products_to_relative_products`;
				TRUNCATE `tx_multishop_specials`;
				TRUNCATE `tx_multishop_specials_sections`;
				TRUNCATE `tx_multishop_attributes_options_groups`;
				TRUNCATE `tx_multishop_attributes_options_groups_to_products_options`;
				TRUNCATE `tx_multishop_undo_products`;';
				/*
				TRUNCATE `tx_multishop_coupons`;
				TRUNCATE `tx_multishop_import_jobs`;
				TRUNCATE `tx_multishop_invoices`;
				TRUNCATE `tx_multishop_orders`;
				TRUNCATE `tx_multishop_orders_products`;
				TRUNCATE `tx_multishop_orders_products_attributes`;
				TRUNCATE `tx_multishop_orders_status_history`;
				TRUNCATE `tx_multishop_payment_methods`;
				TRUNCATE `tx_multishop_payment_methods_description`;
				TRUNCATE `tx_multishop_payment_shipping_mappings`;
				TRUNCATE `tx_multishop_payment_transactions`;
				TRUNCATE `tx_multishop_products_method_mappings`;
				TRUNCATE `tx_multishop_shipping_methods_costs`;
				TRUNCATE `tx_multishop_shipping_methods_description`;
				TRUNCATE `tx_multishop_stores`;
				TRUNCATE `tx_multishop_stores_description`;
				*/
				$array=explode("\n", $string);
				foreach ($array as $item) {
					if ($item) {
						$qry=$GLOBALS['TYPO3_DB']->sql_query($item);
					}
				}
				foreach ($this->ms['image_paths']['products'] as $key=>$path) {
					if ($path) {
						$return=mslib_befe::deltree($this->DOCUMENT_ROOT.$path);
					}
				}
				foreach ($this->ms['image_paths']['categories'] as $key=>$path) {
					if ($path) {
						$return=mslib_befe::deltree($this->DOCUMENT_ROOT.$path);
					}
				}
				foreach ($this->ms['image_paths']['manufacturers'] as $key=>$path) {
					if ($path) {
						$return=mslib_befe::deltree($this->DOCUMENT_ROOT.$path);
					}
				}
				$content.='<p>'.$this->pi_getLL('admin_label_everything_has_been_cleared').'</p>';
				break;
		}
	}
}
?>