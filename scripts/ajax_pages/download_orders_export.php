<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
if ($this->get['orders_export_hash']) {
    set_time_limit(86400);
    ignore_user_abort(true);
    $orders_export = mslib_fe::getOrdersExportWizard($this->get['orders_export_hash'], 'code');
    $lifetime = 7200;
    if ($this->ADMIN_USER) {
        $lifetime = 0;
    }
    $options = array(
            'caching' => true,
            'cacheDir' => $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/cache/',
            'lifeTime' => $lifetime
    );
    $Cache_Lite = new Cache_Lite($options);
    $string = 'productfeed_' . $this->shop_pid . '_' . serialize($orders_export) . '-' . md5($this->cObj->data['uid'] . '_' . $this->server['REQUEST_URI'] . $this->server['QUERY_STRING']);
    if ($this->ADMIN_USER and $this->get['clear_cache']) {
        if ($Cache_Lite->get($string)) {
            $Cache_Lite->remove($string);
        }
    }
    if (!$content = $Cache_Lite->get($string)) {
	    // custom page hook that can be controlled by third-party plugin
	    $fields = unserialize($orders_export['fields']);
	    $post_data = unserialize($orders_export['post_data']);
	    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrdersExportFeedTypesProc'])) {
		    $params = array(
			    'orders_export' => &$orders_export,
			    'fields' => &$fields,
			    'post_data' => &$post_data,
		    );
		    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrdersExportFeedTypesProc'] as $funcRef) {
			    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		    }
	    }
        switch ($post_data['delimeter_type']) {
            case '\t':
                $post_data['delimeter_type'] = "\t";
                break;
            case '':
                $post_data['delimeter_type'] = ';';
                break;
        }
        $fields_values = $post_data['fields_values'];
        $records = array();
        // orders record
        $filter = array();
        $from = array();
        $having = array();
        $match = array();
        $orderby = array();
        $where = array();
        $orderby = array();
        $select = array();
        if (!empty($post_data['orders_date_from']) && !empty($post_data['orders_date_till'])) {
            $start_time = strtotime($post_data['orders_date_from']);
            $end_time = strtotime($post_data['orders_date_till']);
            $column = 'o.crdate';
            $filter[] = $column . " BETWEEN '" . $start_time . "' and '" . $end_time . "'";
        }
        if (!empty($post_data['orders_delivery_date_from']) && !empty($post_data['orders_delivery_date_till'])) {
            $start_time = strtotime($post_data['orders_delivery_date_from']);
            $end_time = strtotime($post_data['orders_delivery_date_till']);
            $column = 'o.expected_delivery_date';
            $filter[] = $column . " BETWEEN '" . $start_time . "' and '" . $end_time . "'";
        }
        if (!empty($post_data['start_duration'])) {
            $start_duration = strtotime(date('Y-m-d 00:00:00', strtotime($post_data['start_duration'])));
            if (!empty($post_data['end_duration'])) {
                $end_duration = strtotime(date('Y-m-d 23:59:59', strtotime($post_data['end_duration'])));
            } else {
                $end_duration = time();
            }
            $column = 'o.crdate';
            $filter[] = $column . " BETWEEN '" . $start_duration . "' and '" . $end_duration . "'";
        }
        if ($post_data['order_status'] !== 'all') {
            $filter[] = "(o.status='" . $post_data['order_status'] . "')";
        }
        if ($post_data['payment_status'] == 'paid') {
            $filter[] = "(o.paid='1')";
        } else if ($post_data['payment_status'] == 'unpaid') {
            $filter[] = "(o.paid='0')";
        }
        if (isset($post_data['shipping_method']) && !empty($post_data['shipping_method']) && $post_data['shipping_method'] != 'all') {
            $filter[] = "(o.shipping_method='" . addslashes($post_data['shipping_method']) . "')";
        }
        if (isset($post_data['payment_method']) && !empty($post_data['payment_method']) && $post_data['payment_method'] != 'all') {
            $filter[] = "(o.payment_method='" . addslashes($post_data['payment_method']) . "')";
        }
        if (isset($post_data['billing_country']) && !empty($post_data['billing_country']) && $post_data['billing_country'] != 'all') {
            $filter[] = "(o.billing_country='" . addslashes($post_data['billing_country']) . "')";
        }
        if (isset($post_data['delivery_country']) && !empty($post_data['delivery_country']) && $post_data['delivery_country'] != 'all') {
            $filter[] = "(o.delivery_country='" . addslashes($post_data['delivery_country']) . "')";
        }
        if (!$this->masterShop) {
            $filter[] = 'o.page_uid=' . $this->shop_pid;
        }
        $select[] = 'o.*, osd.name as orders_status';
        switch ($post_data['order_by']) {
            case 'billing_name':
                $order_by = 'o.billing_name';
                break;
            case 'crdate':
                $order_by = 'o.crdate';
                break;
            case 'grand_total':
                $order_by = 'o.grand_total';
                break;
            case 'shipping_method_label':
                $order_by = 'o.shipping_method_label';
                break;
            case 'payment_method_label':
                $order_by = 'o.payment_method_label';
                break;
            case 'status_last_modified':
                $order_by = 'o.status_last_modified';
                break;
            case 'orders_id':
            default:
                $order_by = 'o.orders_id';
                break;
        }
        switch ($post_data['sort_direction']) {
            case 'asc':
                $order = 'asc';
                break;
            case 'desc':
            default:
                $order = 'desc';
                break;
        }
        $orderby[] = $order_by . ' ' . $order;
        if ($post_data['order_type'] == 'by_phone') {
            $filter[] = 'o.by_phone=1';
        }
        //if ($this->get['format'] == 'excel') {
        //    $ox_limit = 65000;
        //} else {
            $ox_limit = 500000;
        //}
        $order_table_type = 'active';
        if (isset($post_data['order_table_type']) && $post_data['order_table_type']) {
            $order_table_type = $post_data['order_table_type'];
        }
	    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrdersExportQueryPostProc'])) {
		    $params = array(
			    'filter' => &$filter,
		    );
		    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrdersExportQueryPostProc'] as $funcRef) {
			    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		    }
	    }
        $pageset = mslib_fe::getOrdersPageSet($filter, $offset, $ox_limit, $orderby, $having, $select, $where, $from, '', $order_table_type);
        //print_r($pageset);
        //die();
        $records = $pageset['orders'];
        // load all products
        $excelRows = array();
        $excelHeaderCols = array();
        foreach ($fields as $counter => $field) {
            if ($field != 'order_products' && $field != 'turnover_per_category_incl_vat' && $field != 'turnover_per_category_excl_vat' && $field != 'turnover_per_main_category_incl_vat' && $field != 'turnover_per_main_category_excl_vat' && $field != 'bought_products_per_main_category') {
                $excelHeaderCols[$field . '-' . $counter] = $field;
            } else {
                switch ($field) {
                    case 'order_products':
                        switch ($post_data['show_order_products_in']) {
                            case 'horizontal':
                                $max_cols_num = ($post_data['maximum_number_of_order_products'] ? $post_data['maximum_number_of_order_products'] : 25);
                                for ($i = 0; $i < $max_cols_num; $i++) {
                                    $excelHeaderCols['product_id' . $i] = 'product_id' . $i;
                                    $excelHeaderCols['product_name' . $i] = 'product_name' . $i;
                                    $excelHeaderCols['product_model' . $i] = 'product_model' . $i;
                                    $excelHeaderCols['ean_code' . $i] = 'ean_code' . $i;
                                    $excelHeaderCols['sku_code' . $i] = 'sku_code' . $i;
                                    $excelHeaderCols['product_qty' . $i] = 'product_qty' . $i;
                                    $excelHeaderCols['product_final_price_excl_tax' . $i] = 'product_final_price_excl_tax' . $i;
                                    $excelHeaderCols['product_final_price_incl_tax' . $i] = 'product_final_price_incl_tax' . $i;
                                    $excelHeaderCols['product_price_total_excl_tax' . $i] = 'product_final_price_total_excl_tax' . $i;
                                    $excelHeaderCols['product_price_total_incl_tax' . $i] = 'product_final_price_total_incl_tax' . $i;
                                    $excelHeaderCols['product_tax_rate' . $i] = 'product_tax_rate' . $i;
                                    //hook to let other plugins further manipulate the replacers
                                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersHeaderOrderProductsPostProc'])) {
                                        $params = array(
                                                'excelHeaderCols' => &$excelHeaderCols,
                                                'i' => &$i
                                        );
                                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersHeaderOrderProductsPostProc'] as $funcRef) {
                                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                        }
                                    }
                                }
                                break;
                            case 'vertical':
                                $excelHeaderCols['product_id'] = 'product_id';
                                $excelHeaderCols['product_name'] = 'product_name';
                                $excelHeaderCols['product_model'] = 'product_model';
                                $excelHeaderCols['ean_code'] = 'ean_code';
                                $excelHeaderCols['sku_code'] = 'sku_code';
                                $excelHeaderCols['product_qty'] = 'product_qty';
                                $excelHeaderCols['product_final_price_excl_tax'] = 'product_final_price_excl_tax';
                                $excelHeaderCols['product_final_price_incl_tax'] = 'product_final_price_incl_tax';
                                $excelHeaderCols['product_price_total_excl_tax'] = 'product_final_price_total_excl_tax';
                                $excelHeaderCols['product_price_total_incl_tax'] = 'product_final_price_total_incl_tax';
                                $excelHeaderCols['product_tax_rate'] = 'product_tax_rate';
                                break;
                        }
                        break;
                    case 'turnover_per_category_incl_vat':
                        $categories_data_incl_vat = array();
                        foreach ($records as $record) {
                            if (isset($post_data['order_table_type']) && $post_data['order_table_type'] == 'archive') {
                                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                                $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                                $mslib_order->init($this);
                                $order_tmp = $mslib_order->getOrderArchive($record['orders_id']);
                            } else {
                                $order_tmp = mslib_fe::getOrder($record['orders_id']);
                            }
                            foreach ($order_tmp['products'] as $product) {
                                $category_name = $product['categories_name'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                if ($product['categories_id'] > 0) {
                                    $categories_data_incl_vat[$category_name] = $product['categories_id'];
                                } else {
                                    $categories_data_incl_vat[$category_name] = $this->pi_getLL('unknown');
                                }
                            }
                        }
                        if (is_array($categories_data_incl_vat) && count($categories_data_incl_vat)) {
                            foreach ($categories_data_incl_vat as $category_name => $category_id) {
                                $cats = mslib_fe::Crumbar($category_id);
                                $catPath = array();
                                if (is_array($cats) && count($cats)) {
                                    $cats = array_reverse($cats);
                                    if (count($cats) > 0) {
                                        $i = 0;
                                        foreach ($cats as $cat) {
                                            $catPath[$i] = $cat['name'];
                                            $i++;
                                        }
                                    }
                                    // get all cats to generate multilevel fake url eof
                                }
                                $header_category_name = $category_name;
                                if (count($catPath)) {
                                    $header_category_name = implode(' > ', $catPath);
                                }
                                $excelHeaderCols['categories_id_' . $category_name . '_incl_vat'] = sprintf($this->pi_getLL('turnover_per_category_incl_vat'), $header_category_name);
                            }
                        }
                        break;
                    case 'turnover_per_category_excl_vat':
                        $categories_data_excl_vat = array();
                        foreach ($records as $record) {
                            if (isset($post_data['order_table_type']) && $post_data['order_table_type'] == 'archive') {
                                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                                $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                                $mslib_order->init($this);
                                $order_tmp = $mslib_order->getOrderArchive($record['orders_id']);
                            } else {
                                $order_tmp = mslib_fe::getOrder($record['orders_id']);
                            }
                            foreach ($order_tmp['products'] as $product) {
                                $category_name = $product['categories_name'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                if ($product['categories_id'] > 0) {
                                    $categories_data_excl_vat[$category_name] = $product['categories_id'];
                                } else {
                                    $categories_data_excl_vat[$category_name] = $this->pi_getLL('unknown');
                                }
                            }
                        }
                        if (is_array($categories_data_excl_vat) && count($categories_data_excl_vat)) {
                            foreach ($categories_data_excl_vat as $category_name => $category_id) {
                                $cats = mslib_fe::Crumbar($category_id);
                                $catPath = array();
                                if (is_array($cats) && count($cats)) {
                                    $cats = array_reverse($cats);
                                    if (count($cats) > 0) {
                                        $i = 0;
                                        foreach ($cats as $cat) {
                                            $catPath[$i] = $cat['name'];
                                            $i++;
                                        }
                                    }
                                    // get all cats to generate multilevel fake url eof
                                }
                                $header_category_name = $category_name;
                                if (count($catPath)) {
                                    $header_category_name = implode(' > ', $catPath);
                                }
                                $excelHeaderCols['categories_id_' . $category_name . '_excl_vat'] = sprintf($this->pi_getLL('turnover_per_category_excl_vat'), $header_category_name);
                            }
                        }
                        break;
                    case 'turnover_per_main_category_incl_vat':
                        $main_categories_data_incl_vat = array();
                        foreach ($records as $record) {
                            if (isset($post_data['order_table_type']) && $post_data['order_table_type'] == 'archive') {
                                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                                $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                                $mslib_order->init($this);
                                $order_tmp = $mslib_order->getOrderArchive($record['orders_id']);
                            } else {
                                $order_tmp = mslib_fe::getOrder($record['orders_id']);
                            }
                            foreach ($order_tmp['products'] as $product) {
                                $category_name = $product['categories_name_0'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                if ($product['categories_id_0'] > 0) {
                                    $main_categories_data_incl_vat[$category_name] = $product['categories_id_0'];
                                } else {
                                    $main_categories_data_incl_vat[$category_name] = $this->pi_getLL('unknown');
                                }
                            }
                        }
                        if (is_array($main_categories_data_incl_vat) && count($main_categories_data_incl_vat)) {
                            foreach ($main_categories_data_incl_vat as $category_name => $category_id) {
                                $excelHeaderCols['main_categories_id_' . $category_name . '_incl_vat'] = sprintf($this->pi_getLL('turnover_per_main_category_incl_vat'), $category_name);
                            }
                        }
                        break;
                    case 'turnover_per_main_category_excl_vat':
                        $main_categories_data_excl_vat = array();
                        foreach ($records as $record) {
                            if (isset($post_data['order_table_type']) && $post_data['order_table_type'] == 'archive') {
                                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                                $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                                $mslib_order->init($this);
                                $order_tmp = $mslib_order->getOrderArchive($record['orders_id']);
                            } else {
                                $order_tmp = mslib_fe::getOrder($record['orders_id']);
                            }
                            foreach ($order_tmp['products'] as $product) {
                                $category_name = $product['categories_name_0'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                if ($product['categories_id_0'] > 0) {
                                    $main_categories_data_excl_vat[$category_name] = $product['categories_id_0'];
                                } else {
                                    $main_categories_data_excl_vat[$category_name] = $this->pi_getLL('unknown');
                                }
                            }
                        }
                        if (is_array($main_categories_data_excl_vat) && count($main_categories_data_excl_vat)) {
                            foreach ($main_categories_data_excl_vat as $category_name => $category_id) {
                                $excelHeaderCols['main_categories_id_' . $category_name . '_excl_vat'] = sprintf($this->pi_getLL('turnover_per_main_category_excl_vat'), $category_name);
                            }
                        }
                        break;
                    case 'bought_products_per_main_category':
                        $main_categories_data_bought_products = array();
                        foreach ($records as $record) {
                            if (isset($post_data['order_table_type']) && $post_data['order_table_type'] == 'archive') {
                                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                                $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                                $mslib_order->init($this);
                                $order_tmp = $mslib_order->getOrderArchive($record['orders_id']);
                            } else {
                                $order_tmp = mslib_fe::getOrder($record['orders_id']);
                            }
                            foreach ($order_tmp['products'] as $product) {
                                $category_name = $product['categories_name_0'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                if ($product['categories_id_0'] > 0) {
                                    $main_categories_data_bought_products[$category_name] = $product['categories_id_0'];
                                } else {
                                    $main_categories_data_bought_products[$category_name] = $this->pi_getLL('unknown');
                                }
                            }
                        }
                        if (is_array($main_categories_data_bought_products) && count($main_categories_data_bought_products)) {
                            foreach ($main_categories_data_bought_products as $category_name => $category_id) {
                                $excelHeaderCols['bought_products_main_categories_id_' . $category_name] = sprintf($this->pi_getLL('bought_products_per_main_category'), $category_name);
                            }
                        }
                        break;
                }
            }
        }
		//hook to let other plugins further manipulate the settings
	    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrdersExportFeedHeaderPostProc'])) {
		    $params = array(
			    'excelHeaderCols' => &$excelHeaderCols,
			    'post_data' => $post_data
		    );
		    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrdersExportFeedHeaderPostProc'] as $funcRef) {
			    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		    }
	    }
        if ($this->get['format'] == 'excel') {
            $excelRows[] = $excelHeaderCols;
        } else {
            $excelRows[] = implode($post_data['delimeter_type'], $excelHeaderCols);
        }
        foreach ($records as $row) {
            $order_tax_data = unserialize($row['orders_tax_data']);
            if (isset($post_data['order_table_type']) && $post_data['order_table_type'] == 'archive') {
                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                $mslib_order->init($this);
                $order_tmp = $mslib_order->getOrderArchive($row['orders_id']);
            } else {
                $order_tmp = mslib_fe::getOrder($row['orders_id']);
            }
            $excelCols = array();
            $verticalProductsData = array();
            $total = count($fields);
            $count = 0;
            foreach ($fields as $counter => $field) {
                $count++;
                $tmpcontent = '';
                switch ($field) {
                    case 'orders_id':
                        $excelCols[$field] = $row['orders_id'];
                        break;
                    case 'customer_id':
                        $excelCols[$field] = $row['customer_id'];
                        break;
                    case 'orders_status':
                        $excelCols[$field] = $row['orders_status'];
                        break;
                    case 'customer_billing_email':
                        $excelCols[$field] = $row['billing_email'];
                        break;
                    case 'customer_billing_telephone':
                        $excelCols[$field] = $row['billing_telephone'];
                        break;
                    case 'customer_billing_mobile':
                        $excelCols[$field] = $row['billing_mobile'];
                        break;
                    case 'customer_billing_name':
                        $excelCols[$field] = $row['billing_name'];
                        break;
                    case 'customer_billing_company':
                        $excelCols[$field] = $row['billing_company'];
                        break;
                    case 'customer_billing_address':
                        $excelCols[$field] = $row['billing_address'];
                        break;
                    case 'customer_billing_street_name':
                        $excelCols[$field] = $row['billing_street_name'];
                        break;
                    case 'customer_billing_address_number':
                        $excelCols[$field] = $row['billing_address_number'];
                        break;
                    case 'customer_billing_address_ext':
                        $excelCols[$field] = $row['billing_address_ext'];
                        break;
                    case 'customer_billing_city':
                        $excelCols[$field] = $row['billing_city'];
                        break;
                    case 'customer_billing_zip':
                        $excelCols[$field] = $row['billing_zip'];
                        break;
                    case 'customer_billing_country':
                        $excelCols[$field] = $row['billing_country'];
                        break;
                    case 'customer_delivery_email':
                        $excelCols[$field] = $row['delivery_email'];
                        break;
                    case 'customer_delivery_telephone':
                        $excelCols[$field] = $row['delivery_telephone'];
                        break;
                    case 'customer_delivery_mobile':
                        $excelCols[$field] = $row['delivery_mobile'];
                        break;
                    case 'customer_delivery_name':
                        $excelCols[$field] = $row['delivery_name'];
                        break;
                    case 'customer_delivery_company':
                        $excelCols[$field] = $row['delivery_company'];
                        break;
                    case 'customer_delivery_address':
                        $excelCols[$field] = $row['delivery_address'];
                        break;
                    case 'customer_delivery_street_name':
                        $excelCols[$field] = $row['delivery_street_name'];
                        break;
                    case 'customer_delivery_address_number':
                        $excelCols[$field] = $row['delivery_address_number'];
                        break;
                    case 'customer_delivery_address_ext':
                        $excelCols[$field] = $row['delivery_address_ext'];
                        break;
                    case 'customer_delivery_city':
                        $excelCols[$field] = $row['delivery_city'];
                        break;
                    case 'customer_delivery_zip':
                        $excelCols[$field] = $row['delivery_zip'];
                        break;
                    case 'customer_delivery_country':
                        $excelCols[$field] = $row['delivery_country'];
                        break;
                    case 'orders_grand_total_excl_vat':
                        $excelCols[$field] = number_format($order_tax_data['grand_total'] - $order_tax_data['total_orders_tax'], 2, ',', '.');
                        break;
                    case 'orders_grand_total_incl_vat':
                        $excelCols[$field] = number_format($order_tax_data['grand_total'], 2, ',', '.');
                        break;
                    case 'payment_status':
                        $excelCols[$field] = ($row['paid']) ? $this->pi_getLL('paid') : $this->pi_getLL('unpaid');
                        break;
                    case 'shipping_method':
                        $excelCols[$field] = $row['shipping_method_label'];
                        break;
                    case 'shipping_cost_excl_vat':
                        $excelCols[$field] = number_format($row['shipping_method_costs'], 2, ',', '.');
                        break;
                    case 'shipping_cost_incl_vat':
                        $excelCols[$field] = number_format($row['shipping_method_costs'] + $order_tmp['orders_tax_data']['shipping_tax'], 2, ',', '.');
                        break;
                    case 'shipping_cost_vat_rate':
                        $excelCols[$field] = ($order_tmp['orders_tax_data']['shipping_total_tax_rate'] * 100) . '%';
                        break;
                    case 'payment_method':
                        $excelCols[$field] = $row['payment_method_label'];
                        break;
                    case 'payment_cost_excl_vat':
                        $excelCols[$field] = number_format($row['payment_method_cost'], 2, ',', '.');
                        break;
                    case 'payment_cost_incl_vat':
                        $excelCols[$field] = number_format($row['payment_method_cost'] + $order_tmp['orders_tax_data']['payment_tax'], 2, ',', '.');
                        break;
                    case 'payment_cost_vat_rate':
                        $excelCols[$field] = ($order_tmp['orders_tax_data']['payment_total_tax_rate'] * 100) . '%';
                        break;
                    case 'order_products':
                        $order_products = $order_tmp['products'];
                        switch ($post_data['show_order_products_in']) {
                            case 'horizontal':
                                $max_cols_num = ($post_data['maximum_number_of_order_products'] ? $post_data['maximum_number_of_order_products'] : 25);
                                $prod_ctr = 0;
                                foreach ($order_products as $product_tmp) {
                                    if ($prod_ctr >= $max_cols_num) {
                                        break;
                                    }
                                    $excelCols[] = $product_tmp['products_id'];
                                    $excelCols[] = $product_tmp['products_name'];
                                    $excelCols[] = $product_tmp['product_model'];
                                    $excelCols[] = $product_tmp['ean_code'];
                                    $excelCols[] = $product_tmp['sku_code'];
                                    $excelCols[] = $product_tmp['qty'];
                                    $excelCols[] = number_format($product_tmp['final_price'], 2, ',', '.');
                                    $excelCols[] = number_format($product_tmp['final_price'] + $product_tmp['products_tax_data']['total_tax'], 2, ',', '.');
                                    $excelCols[] = number_format($product_tmp['final_price'] * $product_tmp['qty'], 2, ',', '.');
                                    $excelCols[] = number_format(($product_tmp['final_price'] + $product_tmp['products_tax_data']['total_tax']) * $product_tmp['qty'], 2, ',', '.');
                                    $excelCols[] = $product_tmp['products_tax'] . '%';
                                    //hook to let other plugins further manipulate the replacers
                                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersBodyOrderProductsPostProc'])) {
                                        $params = array(
                                                'excelCols' => &$excelCols,
                                                'product_tmp' => &$product_tmp,
                                                'prod_ctr' => &$prod_ctr
                                        );
                                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersBodyOrderProductsPostProc'] as $funcRef) {
                                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                        }
                                    }
                                    $prod_ctr++;
                                }
                                if ($prod_ctr < $max_cols_num) {
                                    for ($x = $prod_ctr; $x < $max_cols_num; $x++) {
                                        for ($i=1;$i<=11;$i++) {
                                            $excelCols[] = '';
                                        }
                                        //hook to let other plugins further manipulate the replacers
                                        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersBodyOrderProductsAppendPostProc'])) {
                                            $params = array(
                                                    'excelCols' => &$excelCols,
                                                    'product_tmp' => &$product_tmp,
                                                    'prod_ctr' => &$prod_ctr
                                            );
                                            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersBodyOrderProductsAppendPostProc'] as $funcRef) {
                                                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'vertical':
                                foreach ($order_products as $idx => $product_tmp) {
                                    $productCols = array();
                                    $productCols[] = $product_tmp['products_id'];
                                    $productCols[] = $product_tmp['products_name'];
                                    $productCols[] = $product_tmp['product_model'];
                                    $productCols[] = $product_tmp['ean_code'];
                                    $productCols[] = $product_tmp['sku_code'];
                                    $productCols[] = $product_tmp['qty'];
                                    $productCols[] = number_format($product_tmp['final_price'], 2, ',', '.');
                                    $productCols[] = number_format($product_tmp['final_price'] + $product_tmp['products_tax_data']['total_tax'], 2, ',', '.');
                                    $productCols[] = number_format($product_tmp['final_price'] * $product_tmp['qty'], 2, ',', '.');
                                    $productCols[] = number_format(($product_tmp['final_price'] + $product_tmp['products_tax_data']['total_tax']) * $product_tmp['qty'], 2, ',', '.');
                                    $productCols[] = $product_tmp['products_tax'] . '%';
                                    $verticalProductsData[] = $productCols;
                                }
                                break;
                        }
                        break;
                    case 'order_total_vat':
                        $excelCols[$field] = number_format($order_tax_data['total_orders_tax'], 2, ',', '.');
                        break;
                    case 'order_date':
                        $excelCols[$field] = ($row['crdate'] > 0 ? strftime('%x', $row['crdate']) : '');
                        break;
                    case 'order_datetime':
                        $excelCols[$field] = ($row['crdate'] > 0 ? date('Y-m-d G:i:s', $row['crdate']) : '');
                        break;
                    case 'order_company_name':
                        $excelCols[$field] = $row['billing_company'];
                        break;
                    case 'order_vat_id':
                        $excelCols[$field] = $row['billing_vat_id'];
                        break;
                    case 'order_customer_currency':
                        $excelCols[$field] = $row['customer_currency'];
                        break;
                    case 'order_customer_currency_rate':
                        $excelCols[$field] = $row['currency_rate'];
                        break;
                    case 'order_customer_language_id':
                        $excelCols[$field] = $row['language_id'];
                        break;
                    case 'order_track_and_trace_code':
                        $excelCols[$field] = $row['track_and_trace_code'];
                        break;
                    case 'http_host':
                        $excelCols[$field] = $row['http_host'];
                        break;
                    case 'order_orders_paid_timestamp':
                        $excelCols[$field] = strftime('%x', $row['orders_paid_timestamp']);
                        break;
                    case 'order_status_last_modified':
                        $excelCols[$field] = strftime('%x', $row['status_last_modified']);
                        break;
                    case 'order_orders_last_modified':
                        $excelCols[$field] = strftime('%x', $row['orders_last_modified']);
                        break;
                    case 'order_expected_delivery_date':
                        if ($this->ms['MODULES']['ADD_HOURS_TO_EDIT_ORDER_EXPECTED_DELIVERY_DATE'] == '1') {
                            $array2[] = strftime("%x %T", $order['expected_delivery_date']);
                        } else {
                            $excelCols[$field] = strftime('%x', $row['expected_delivery_date']);
                        }
                        break;
                    case 'order_by_phone':
                        $excelCols[$field] = ($row['by_phone'] > 0 ? $this->pi_getLL('yes') : $this->pi_getLL('no'));
                        break;
                    case 'turnover_per_category_incl_vat':
                        $order_products = $order_tmp['products'];
                        $categories_data_amount_incl_vat = array();
                        if (is_array($categories_data_incl_vat) && count($categories_data_incl_vat) > 0) {
                            foreach ($order_products as $product_tmp) {
                                $category_name = $product_tmp['categories_name'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                $categories_data_amount_incl_vat[$order_tmp['orders_id']][$category_name] += ($product_tmp['final_price'] + $product_tmp['products_tax_data']['total_tax']) * $product_tmp['qty'];
                                // fetch attributes
                                $active_table = 'tx_multishop_orders_products_attributes';
                                if ($post_data['order_table_type'] == 'archive') {
                                    $active_table = 'tx_multishop_archive_orders_products_attributes';
                                }
                                $str_opa = "SELECT * from ' . $active_table . ' where orders_products_id='" . $product_tmp['orders_products_id'] . "' order by orders_products_attributes_id asc";
                                $qry_opa = $GLOBALS['TYPO3_DB']->sql_query($str_opa);
                                while (($order_product_attributes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opa)) != false) {
                                    $options_attributes_tax_data = unserialize($order_product_attributes['attributes_tax_data']);
                                    $categories_data_amount_incl_vat[$order_tmp['orders_id']][$category_name] += (($order_product_attributes['price_prefix'] . $order_product_attributes['options_values_price']) + $options_attributes_tax_data['tax']) * $product_tmp['qty'];
                                }
                            }
                        }
                        foreach ($categories_data_incl_vat as $categories_index_main => $categories_id) {
                            if (isset($categories_data_amount_incl_vat[$order_tmp['orders_id']][$categories_index_main])) {
                                $excelCols[] = number_format($categories_data_amount_incl_vat[$order_tmp['orders_id']][$categories_index_main], 2, ',', '.');
                            } else {
                                $excelCols[] = number_format(0, 2, ',', '.');
                            }
                        }
                        break;
                    case 'turnover_per_category_excl_vat':
                        $order_products = $order_tmp['products'];
                        $categories_data_amount_excl_vat = array();
                        if (is_array($categories_data_excl_vat) && count($categories_data_excl_vat) > 0) {
                            foreach ($order_products as $product_tmp) {
                                $category_name = $product_tmp['categories_name'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                $categories_data_amount_excl_vat[$order_tmp['orders_id']][$category_name] += $product_tmp['final_price'] * $product_tmp['qty'];
                                // fetch attributes
                                $active_table = 'tx_multishop_orders_products_attributes';
                                if ($post_data['order_table_type'] == 'archive') {
                                    $active_table = 'tx_multishop_archive_orders_products_attributes';
                                }
                                $str_opa = "SELECT * from ' . $active_table . ' where orders_products_id='" . $product_tmp['orders_products_id'] . "' order by orders_products_attributes_id asc";
                                $qry_opa = $GLOBALS['TYPO3_DB']->sql_query($str_opa);
                                while (($order_product_attributes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opa)) != false) {
                                    $categories_data_amount_excl_vat[$order_tmp['orders_id']][$category_name] += (($order_product_attributes['price_prefix'] . $order_product_attributes['options_values_price'])) * $product_tmp['qty'];
                                }
                            }
                        }
                        foreach ($categories_data_excl_vat as $categories_index_main => $categories_id) {
                            if (isset($categories_data_amount_excl_vat[$order_tmp['orders_id']][$categories_index_main])) {
                                $excelCols[] = number_format($categories_data_amount_excl_vat[$order_tmp['orders_id']][$categories_index_main], 2, ',', '.');
                            } else {
                                $excelCols[] = number_format(0, 2, ',', '.');
                            }
                        }
                        break;
                    case 'turnover_per_main_category_incl_vat':
                        $order_products = $order_tmp['products'];
                        $main_categories_data_amount_incl_vat = array();
                        if (is_array($main_categories_data_incl_vat) && count($main_categories_data_incl_vat) > 0) {
                            foreach ($order_products as $product_tmp) {
                                $category_name = $product_tmp['categories_name_0'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                $main_categories_data_amount_incl_vat[$order_tmp['orders_id']][$category_name] += ($product_tmp['final_price'] + $product_tmp['products_tax_data']['total_tax']) * $product_tmp['qty'];
                                // fetch attributes
                                $active_table = 'tx_multishop_orders_products_attributes';
                                if ($post_data['order_table_type'] == 'archive') {
                                    $active_table = 'tx_multishop_archive_orders_products_attributes';
                                }
                                $str_opa = "SELECT * from ' . $active_table . ' where orders_products_id='" . $product_tmp['orders_products_id'] . "' order by orders_products_attributes_id asc";
                                $qry_opa = $GLOBALS['TYPO3_DB']->sql_query($str_opa);
                                while (($order_product_attributes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opa)) != false) {
                                    $options_attributes_tax_data = unserialize($order_product_attributes['attributes_tax_data']);
                                    $main_categories_data_amount_incl_vat[$order_tmp['orders_id']][$category_name] += (($order_product_attributes['price_prefix'] . $order_product_attributes['options_values_price']) + $options_attributes_tax_data['tax']) * $product_tmp['qty'];
                                }
                            }
                        }
                        foreach ($main_categories_data_incl_vat as $categories_index_main => $categories_id) {
                            if (isset($main_categories_data_amount_incl_vat[$order_tmp['orders_id']][$categories_index_main])) {
                                $excelCols[] = number_format($main_categories_data_amount_incl_vat[$order_tmp['orders_id']][$categories_index_main], 2, ',', '.');
                            } else {
                                $excelCols[] = number_format(0, 2, ',', '.');
                            }
                        }
                        break;
                    case 'turnover_per_main_category_excl_vat':
                        $order_products = $order_tmp['products'];
                        $main_categories_data_amount_excl_vat = array();
                        if (is_array($main_categories_data_excl_vat) && count($main_categories_data_excl_vat) > 0) {
                            foreach ($order_products as $product_tmp) {
                                $category_name = $product_tmp['categories_name_0'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                $main_categories_data_amount_excl_vat[$order_tmp['orders_id']][$category_name] += $product_tmp['final_price'] * $product_tmp['qty'];
                                // fetch attributes
                                $active_table = 'tx_multishop_orders_products_attributes';
                                if ($post_data['order_table_type'] == 'archive') {
                                    $active_table = 'tx_multishop_archive_orders_products_attributes';
                                }
                                $str_opa = "SELECT * from ' . $active_table . ' where orders_products_id='" . $product_tmp['orders_products_id'] . "' order by orders_products_attributes_id asc";
                                $qry_opa = $GLOBALS['TYPO3_DB']->sql_query($str_opa);
                                while (($order_product_attributes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opa)) != false) {
                                    $main_categories_data_amount_excl_vat[$order_tmp['orders_id']][$category_name] += (($order_product_attributes['price_prefix'] . $order_product_attributes['options_values_price'])) * $product_tmp['qty'];
                                }
                            }
                        }
                        foreach ($main_categories_data_excl_vat as $categories_index_main => $categories_id) {
                            if (isset($main_categories_data_amount_excl_vat[$order_tmp['orders_id']][$categories_index_main])) {
                                $excelCols[] = number_format($main_categories_data_amount_excl_vat[$order_tmp['orders_id']][$categories_index_main], 2, ',', '.');
                            } else {
                                $excelCols[] = number_format(0, 2, ',', '.');
                            }
                        }
                        break;
                    case 'bought_products_per_main_category':
                        $order_products = $order_tmp['products'];
                        $main_categories_data_bought_products_amount = array();
                        if (is_array($main_categories_data_bought_products) && count($main_categories_data_bought_products) > 0) {
                            foreach ($order_products as $product_tmp) {
                                $category_name = $product_tmp['categories_name_0'];
                                if (!$category_name) {
                                    $category_name = $this->pi_getLL('unknown');
                                }
                                $main_categories_data_bought_products_amount[$order_tmp['orders_id']][$category_name] += $product_tmp['qty'];
                            }
                        }
                        foreach ($main_categories_data_bought_products as $categories_index_main => $categories_id) {
                            if (isset($main_categories_data_bought_products_amount[$order_tmp['orders_id']][$categories_index_main])) {
                                $excelCols[] = $main_categories_data_bought_products_amount[$order_tmp['orders_id']][$categories_index_main];
                            } else {
                                $excelCols[] = '0';
                            }
                        }
                        break;
                    case 'ordered_by':
                        if ($row['cruser_id']) {
                            $user = mslib_fe::getUser($row['cruser_id']);
                            if ($user['username']) {
                                $excelCols[$field] = $user['username'];
                            } else {
                                $excelCols[$field] = '';
                            }
                        } else {
                            $excelCols[$field] = '';
                        }
                        break;
                    case 'discount':
                        if ($row['discount']) {
                            $excelCols[$field] = number_format($row['discount'], 2, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '');
                        } else {
                            $excelCols[$field] = '';
                        }
                        break;
                    case 'order_memo':
                        if ($row['order_memo']) {
                            $memo = str_replace('<p>', '', $row['order_memo']);
                            $memo = str_replace('</p>', " ", $memo);
                            $excelCols[$field] = strip_tags($memo);
                        } else {
                            $excelCols[$field] = '';
                        }
                        break;
                    case 'customer_comments':
                        if ($row['customer_comments']) {
                            $excelCols[$field] = $row['customer_comments'];
                        } else {
                            $excelCols[$field] = '';
                        }
                        break;
                    default:
                        if (strpos($field, 'order_grand_total_tax_') !== false) {
                            $tmp_tax_str = explode('_', $field);
                            $tax_rate = str_replace('%', '', $tmp_tax_str[4]);
                            if (isset($order_tax_data['tax_separation'][$tax_rate])) {
                                $excelCols[$field] = number_format($order_tax_data['tax_separation'][$tax_rate]['products_total_tax'] + $order_tax_data['tax_separation'][$tax_rate]['shipping_tax'], 2, ',', '.');
                            } else {
                                $excelCols[$field] = '';
                            }
                        }
                        break;
                }
                // hook to let other plugins further manipulate the replacers
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrderExportFieldIteratorPostProc'])) {
                    $params = array(
                            'field' => &$field,
                            'excelCols' => &$excelCols,
                            'row' => &$row,
                            'counter' => $counter,
                            'post_data' => $post_data
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['downloadOrderExportFieldIteratorPostProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
            }
            // new rows
            if (count($verticalProductsData)) {
                foreach ($verticalProductsData as $productCols) {
                    $defaultCols = $excelCols;
                    foreach ($productCols as $productCol) {
                        $defaultCols[] = $productCol;
                    }
                    if ($this->get['format'] == 'excel') {
                        $excelRows[] = $defaultCols;
                    } else {
                        $excelRows[] = implode($post_data['delimeter_type'], $defaultCols);
                    }

                }
            } else {
                if ($this->get['format'] == 'excel') {
                    $excelRows[] = $excelCols;
                } else {
                    $excelRows[] = implode($post_data['delimeter_type'], $excelCols);
                }
            }
        }
        if ($this->get['format'] == 'excel') {
            $paths = array();
            $paths[] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service') . 'Classes/Service/PHPExcel.php';
            $paths[] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service') . 'Classes/PHPExcel.php';
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once($path);
                    break;
                }
            }
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getSheet(0)->setTitle('Orders Export');
            $objPHPExcel->getActiveSheet()->fromArray($excelRows);
            $ExcelWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="orders_export_' . $this->get['orders_export_hash'] . '.xlsx"');
            $ExcelWriter->save('php://output');
            exit();
        } else {
            $content = implode("\n", $excelRows);
        }
        $Cache_Lite->save($content);
    }
    if ($this->get['downloadAsFile']) {
        $file='export_orders_'.date('Ymd_Hi').'.csv';
        $filePath=$this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/'.$file;
        //hook to let other plugins further manipulate the replacers
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersDownloadAsFilePreProc'])) {
            $params = array(
                    'content' => &$content
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_orders_export.php']['exportOrdersDownloadAsFilePreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        if (file_put_contents($filePath,$content)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            // Remove tmp file
            unlink($filePath);
            exit();
        }
    }
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header('Content-Encoding: UTF-8');
    header('Content-type: text/plain; charset=UTF-8');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo "\xEF\xBB\xBF" . $content;
    exit();
}
exit();
