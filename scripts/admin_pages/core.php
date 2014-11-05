<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$this->ms['page']=$this->get['tx_multishop_pi1']['page_section'];
switch ($this->ms['page']) {
	case 'admin_action_notification_log':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_action_notification_log.php');
		}
		break;
	case 'admin_system_clear_multishop_cache':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_system_clear_multishop_cache.php');
		}
		break;
	case 'admin_shipping_countries':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_shipping_countries.php');
		}
		break;
	case 'admin_zone_payment_mappings':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_zone_payment_mappings.php');
		}
		break;
	case 'admin_zone_shipping_mappings':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_zone_shipping_mappings.php');
		}
		break;
	case 'admin_shipping_payment_mappings':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_shipping_payment_mappings.php');
		}
		break;
	case 'admin_shipping_costs':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_shipping_costs.php');
		}
		break;
	case 'admin_shipping_zones':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_shipping_zones.php');
		}
		break;
	case 'admin_new_order':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/manual_order/admin_new_order.php');
		}
		break;
	case 'admin_processed_manual_order':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/manual_order/admin_processed_manual_order.php');
		}
		break;
	case 'admin_order_units':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_order_units.php');
		}
		break;
	case 'admin_shipping_modules':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_shipping_modules.php');
		}
		break;
	case 'admin_payment_modules':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_payment_modules.php');
		}
		break;
	case 'admin_taxes':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_taxes.php');
		}
		break;
	case 'admin_tax_rule_groups':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_tax_rule_groups.php');
		}
		break;
	case 'admin_tax_rules':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_tax_rules.php');
		}
		break;
	case 'admin_invoices':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_invoices.php');
		}
		break;
	case 'admin_orders_status':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_orders_status.php');
		}
		break;
	case 'admin_modules':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_modules.php');
		}
		break;
	case 'admin_product_attributes':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_product_attributes.php');
		}
		break;
	case 'admin_attributes_options_groups':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_attributes_options_groups.php');
		}
		break;
	case 'admin_manufacturers':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_manufacturers.php');
		}
		break;
	case 'admin_coupons':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_coupons.php');
		}
		break;
	case 'admin_products_search_stats':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_products_search_stats.php');
		}
		break;
	case 'admin_shopping_cart_stats':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_shopping_cart_stats.php');
		}
		break;
	case 'admin_customers':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_customers.php');
		}
		break;
	case 'admin_cms':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_cms.php');
		}
		break;
	case 'admin_products_search_and_edit':
		if ($this->ADMIN_USER) {
			if (strstr($this->ms['MODULES']['ADMIN_PRODUCTS_SEARCH_AND_EDIT'], "..")) {
				die('error in ADMIN_PRODUCTS_SEARCH_AND_EDIT value');
			} else {
				if (strstr($this->ms['MODULES']['ADMIN_PRODUCTS_SEARCH_AND_EDIT'], "/")) {
					// relative mode
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_PRODUCTS_SEARCH_AND_EDIT'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_products_search_and_edit.php');
				}
			}
			// product updater, excel download/upload
		}
		break;
	case 'admin_import':
		if (!$this->ms['MODULES']['ADMIN_PRODUCTS_IMPORT_TYPE']) {
			$script='admin_import.php';
		}
		if (strstr($this->ms['MODULES']['ADMIN_PRODUCTS_IMPORT_TYPE'], "..")) {
			die('error in ADMIN_PRODUCTS_IMPORT_TYPE value');
		} else {
			if (strstr($this->ms['MODULES']['ADMIN_PRODUCTS_IMPORT_TYPE'], "/")) {
				// relative mode
				$script=$this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_PRODUCTS_IMPORT_TYPE'].'.php';
			} else {
				$script='admin_import.php';
			}
		}
		if ($this->get['action']=='run_job' and $this->get['code']) {
			$this->get['job_id']='';
			$str="SELECT id FROM `tx_multishop_import_jobs` where code='".addslashes($this->get['code'])."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$this->get['job_id']=$row['id'];
			}
			if (is_numeric($this->get['job_id'])) {
				require($script);
			}
		} else {
			if ($this->ADMIN_USER) {
				require($script);
			}
		}
		break;
	case 'admin_price_update_up_xls':
		if ($this->ADMIN_USER) {
			if (isset($this->post['Submit'])) {
				$dest=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$_FILES['datafile']['name'];
				if (move_uploaded_file($_FILES['datafile']['tmp_name'], $dest)) {
					$filename=$_FILES['datafile']['name'];
				} else {
					$filename='';
				}
			}
			if (!empty($filename)) {
				require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/price_update/mass_price_update_xls_import.php');
			}
		}
		break;
	case 'admin_stats_orders':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_stats_orders.php');
		}
		break;
	case 'admin_stats_user_agent':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_stats_user_agent.php');
		}
		break;
	case 'admin_stats_products':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_stats_products.php');
		}
		break;
	case 'admin_stats_customers':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_stats_customers.php');
		}
		break;
	case 'admin_orders':
		if ($this->ADMIN_USER) {
			if (strstr($this->ms['MODULES']['ADMIN_ORDERS_TYPE'], "..")) {
				die('error in ADMIN_ORDERS_TYPE value');
			} else {
				if (strstr($this->ms['MODULES']['ADMIN_ORDERS_TYPE'], "/")) {
					// relative mode
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_ORDERS_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_orders.php');
				}
			}
		}
		break;
	// to find out
	case 'admin_categories':
		if ($this->ADMIN_USER) {
			if ($this->get['action']=='move_categories') {
				$selected_cats=count($this->post['movecats']);
				if ($selected_cats>0) {
					if (isset($this->post['move_selected_categories'])) {
						$new_parent_id=$this->post['move_to_cat'];
						foreach ($this->post['movecats'] as $move_catid) {
							$sql_update='update tx_multishop_categories set parent_id = '.$new_parent_id.' where categories_id = '.$move_catid;
							$GLOBALS['TYPO3_DB']->sql_query($sql_update);
						}
					} else {
						if (isset($this->post['delete_selected_categories'])) {
							foreach ($this->post['movecats'] as $move_catid) {
								mslib_befe::deleteCategory($move_catid);
							}
						}
					}
				}
				header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_categories&cid='.$this->get['categories_id']));
			}
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_categories.php');
		}
		break;
	case 'admin_product_feeds':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_product_feeds.php');
		}
		break;
	case 'admin_export_orders':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_export_orders.php');
		}
		break;
	case 'admin_export_invoices':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_export_invoices.php');
		}
		break;
	case 'admin_customer_export':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_customer_export.php');
		}
		break;
	case 'admin_useragent_export':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_useragent_export.php');
		}
		break;
	case 'admin_customer_import':
		if ($this->ADMIN_USER) {
			if (!$this->ms['MODULES']['ADMIN_CUSTOMERS_IMPORT_TYPE']) {
				$script='admin_customer_import.php';
			}
			if (strstr($this->ms['MODULES']['ADMIN_CUSTOMERS_IMPORT_TYPE'], "..")) {
				die('error in ADMIN_CUSTOMERS_IMPORT_TYPE value');
			} else {
				if (strstr($this->ms['MODULES']['ADMIN_CUSTOMERS_IMPORT_TYPE'], "/")) {
					// relative mode
					$script=$this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_CUSTOMERS_IMPORT_TYPE'].'.php';
				} else {
					$script='admin_customer_import.php';
				}
			}
			require($script);
		}
		break;
	case 'admin_system_update_catalog_languages':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_system_update_catalog_languages.php');
		}
		break;
	case 'admin_system_fix_catalog_default_language':
		if ($this->ADMIN_USER) {
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_description', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_description', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers_cms', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers_info', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_extra', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_reviews_description', '', array('language_id'=>0));
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$content.='Whole catalog is updated to the default language ID.';
		}
		break;
	case 'admin_customer_groups':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_customer_groups.php');
		}
		break;
	case 'admin_system_delete_disabled_products':
		if ($this->ADMIN_USER) {
			set_time_limit(86400);
			ignore_user_abort(true);
			$content.='<div class="main-heading"><h2>Deleting disabled products</h2></div>';
			$str="SELECT products_id from tx_multishop_products where products_status=0";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				$counter=0;
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					mslib_befe::deleteProduct($row['products_id']);
					$counter++;
				}
				$content.='<strong>'.$counter.' products has been deleted.</strong>';
			}
		}
		break;
	case 'admin_system_sort_catalog':
		if ($this->ADMIN_USER and $this->get['tx_multishop_pi1']['sortItem']) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_system_sort_catalog.php');
		}
		break;
	case 'admin_search':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_search.php');
		}
		break;
	case 'admin_home':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_home.php');
		}
		break;
	case 'admin_ajax':
		if ($this->ADMIN_USER) {
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/admin_ajax.php');
		}
		break;
	case 'custom_page':
		if ($this->ADMIN_USER) {
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['customAdminPage'])) {
				$params=array(
					'content'=>&$content
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['customAdminPage'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom page hook that can be controlled by third-party plugin eof
		}
		break;
}
if (!$this->ADMIN_USER) {
	header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid));
	exit();
}
?>