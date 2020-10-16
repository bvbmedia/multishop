<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (isset($this->get['download']) && $this->get['download'] == 'export_orders_task' && is_numeric($this->get['orders_export_id'])) {
    $sql = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
            'tx_multishop_orders_export ', // FROM ...
            'id= \'' . $this->get['orders_export_id'] . '\'', // WHERE...
            '', // GROUP BY...
            '', // ORDER BY...
            '' // LIMIT ...
    );
    $qry = $GLOBALS['TYPO3_DB']->sql_query($sql);
    if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
        $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
        $serial_value = array();
        foreach ($data as $key_idx => $key_val) {
            if ($key_idx != 'id' && $key_idx != 'page_uid') {
                $serial_value[$key_idx] = $key_val;
            }
        }
        $serial_data = '';
        if (count($serial_value) > 0) {
            $serial_data = serialize($serial_value);
        }
        $filename = 'multishop_export_order_record_' . date('YmdHis') . '.txt';
        $filepath = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $filename;
        file_put_contents($filepath, $serial_data);
        header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
        header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
        readfile($filepath); //Read and stream the file
        @unlink($filepath);
        exit();
    }
}
if (isset($this->get['upload']) && $this->get['upload'] == 'export_orders_task' && $_FILES) {
    if (!$_FILES['export_orders_record_file']['error']) {
        $filename = $_FILES['export_orders_record_file']['name'];
        $target = $this->DOCUMENT_ROOT . '/uploads/tx_multishop' . $filename;
        if (move_uploaded_file($_FILES['export_orders_record_file']['tmp_name'], $target)) {
            $task_content = file_get_contents($target);
            $unserial_task_data = unserialize($task_content);
            $insertArray = array();
            $insertArray['page_uid'] = $this->showCatalogFromPage;
            foreach ($unserial_task_data as $col_name => $col_val) {
                if ($col_name == 'code') {
                    $insertArray[$col_name] = md5(uniqid());
                } else if ($col_name == 'name' && isset($this->post['new_name']) && !empty($this->post['new_name'])) {
                    $insertArray[$col_name] = $this->post['new_name'];
                } else if ($col_name == 'crdate') {
                    $insertArray[$col_name] = time();
                } else {
                    $insertArray[$col_name] = $col_val;
                }
            }
            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_export', $insertArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            @unlink($target);
        }
    }
    header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_export_orders'));
    exit();
}
// defining the types
$array = array();
$array['orders_id'] = $this->pi_getLL('feed_exporter_fields_label_orders_id');
$array['orders_status'] = $this->pi_getLL('feed_exporter_fields_label_orders_status');
$array['customer_id'] = $this->pi_getLL('feed_exporter_fields_label_customer_id');
$array['customer_billing_telephone'] = $this->pi_getLL('feed_exporter_fields_label_customer_billing_telephone');
$array['customer_billing_email'] = $this->pi_getLL('feed_exporter_fields_label_customer_billing_email');
$array['customer_billing_name'] = $this->pi_getLL('feed_exporter_fields_label_customer_billing_name');
$array['customer_billing_address'] = $this->pi_getLL('feed_exporter_fields_label_customer_billing_address');
$array['customer_billing_city'] = $this->pi_getLL('feed_exporter_fields_label_customer_billing_city');
$array['customer_billing_zip'] = $this->pi_getLL('feed_exporter_fields_label_customer_billing_zip');
$array['customer_billing_country'] = $this->pi_getLL('feed_exporter_fields_label_customer_billing_country');
$array['customer_delivery_telephone'] = $this->pi_getLL('feed_exporter_fields_label_customer_delivery_telephone');
$array['customer_delivery_email'] = $this->pi_getLL('feed_exporter_fields_label_customer_delivery_email');
$array['customer_delivery_name'] = $this->pi_getLL('feed_exporter_fields_label_customer_delivery_name');
$array['customer_delivery_address'] = $this->pi_getLL('feed_exporter_fields_label_customer_delivery_address');
$array['customer_delivery_city'] = $this->pi_getLL('feed_exporter_fields_label_customer_delivery_city');
$array['customer_delivery_zip'] = $this->pi_getLL('feed_exporter_fields_label_customer_delivery_zip');
$array['customer_delivery_country'] = $this->pi_getLL('feed_exporter_fields_label_customer_delivery_country');
$array['orders_grand_total_excl_vat'] = $this->pi_getLL('feed_exporter_fields_label_orders_grand_total_excl_vat');
$array['orders_grand_total_incl_vat'] = $this->pi_getLL('feed_exporter_fields_label_orders_grand_total_incl_vat');
$array['payment_status'] = $this->pi_getLL('feed_exporter_fields_label_orders_payment_status');
$array['shipping_method'] = $this->pi_getLL('feed_exporter_fields_label_shipping_method');
$array['shipping_cost_excl_vat'] = $this->pi_getLL('feed_exporter_fields_label_shipping_costs_excl_vat');
$array['shipping_cost_incl_vat'] = $this->pi_getLL('feed_exporter_fields_label_shipping_costs_incl_vat');
$array['shipping_cost_vat_rate'] = $this->pi_getLL('feed_exporter_fields_label_shipping_costs_tax_rate');
$array['payment_method'] = $this->pi_getLL('feed_exporter_fields_label_payment_method');
$array['payment_cost_excl_vat'] = $this->pi_getLL('feed_exporter_fields_label_payment_costs_excl_vat');
$array['payment_cost_incl_vat'] = $this->pi_getLL('feed_exporter_fields_label_payment_costs_incl_vat');
$array['payment_cost_vat_rate'] = $this->pi_getLL('feed_exporter_fields_label_payment_costs_tax_rate');
$array['order_products'] = $this->pi_getLL('feed_exporter_fields_label_order_products');
$array['order_total_vat'] = $this->pi_getLL('feed_exporter_fields_label_order_total_vat');
$array['order_date'] = $this->pi_getLL('feed_exporter_fields_label_order_date');
$array['order_datetime'] = $this->pi_getLL('feed_exporter_fields_label_order_datetime');
$array['order_company_name'] = $this->pi_getLL('feed_exporter_fields_label_order_company_name');
$array['order_vat_id'] = $this->pi_getLL('feed_exporter_fields_label_order_vat_id');
$array['order_customer_currency'] = $this->pi_getLL('feed_exporter_fields_label_order_customer_currency');
$array['order_customer_currency_rate'] = $this->pi_getLL('feed_exporter_fields_label_order_customer_currency_rate');
$array['order_customer_language_id'] = $this->pi_getLL('feed_exporter_fields_label_order_customer_language_id');
$array['order_track_and_trace_code'] = $this->pi_getLL('feed_exporter_fields_label_order_track_and_trace_code');
$array['order_orders_paid_timestamp'] = $this->pi_getLL('feed_exporter_fields_label_order_orders_paid_timestamp');
$array['order_status_last_modified'] = $this->pi_getLL('feed_exporter_fields_label_order_status_last_modified');
$array['order_orders_last_modified'] = $this->pi_getLL('feed_exporter_fields_label_order_orders_last_modified');
$array['order_expected_delivery_date'] = $this->pi_getLL('feed_exporter_fields_label_order_expected_delivery_date');
$array['order_by_phone'] = $this->pi_getLL('feed_exporter_fields_label_order_by_phone');
$array['turnover_per_category_incl_vat'] = $this->pi_getLL('feed_exporter_fields_label_turnover_per_category_incl_vat');
$array['turnover_per_category_excl_vat'] = $this->pi_getLL('feed_exporter_fields_label_turnover_per_category_excl_vat');
$array['turnover_per_main_category_incl_vat'] = $this->pi_getLL('feed_exporter_fields_label_turnover_per_main_category_incl_vat');
$array['turnover_per_main_category_excl_vat'] = $this->pi_getLL('feed_exporter_fields_label_turnover_per_main_category_excl_vat');
$array['bought_products_per_main_category'] = $this->pi_getLL('feed_exporter_fields_label_bought_products_per_main_category');
$array['ordered_by'] = $this->pi_getLL('ordered_by');
$array['discount'] = $this->pi_getLL('discount');
$array['customer_comments'] = $this->pi_getLL('customer_comments');
$array['order_memo'] = $this->pi_getLL('order_memo');
// get orders vat rate
$str = "SELECT trg.*, t.rate FROM `tx_multishop_tax_rule_groups` trg, `tx_multishop_tax_rules` tr, `tx_multishop_taxes` t where trg.rules_group_id=tr.rules_group_id and tr.tax_id=t.tax_id group by trg.rules_group_id order by trg.rules_group_id asc";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $array['order_grand_total_tax_' . $row['name']] = sprintf($this->pi_getLL('feed_exporter_fields_label_order_tax_total'), $row['name']);
}
/*
$array['products_id']='Products id';
$array['products_name']='Products name';
$array['products_model']='Products model';
$array['products_qty']='Products quantity';
$array['products_vat_rate']='Products vat rate';
$array['products_final_price_excl_vat']='Products price (excl. vat)';
$array['products_final_price_incl_vat']='Products price (incl. vat)';
*/
//hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['adminExportOrdersColtypesHook'])) {
    $params = array(
            'array' => &$array
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['adminExportOrdersColtypesHook'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
asort($array);
if ($_REQUEST['section'] == 'edit' or $_REQUEST['section'] == 'add') {
    if ($this->post) {
        $erno = array();
        if (!$this->post['name']) {
            $erno[] = $this->pi_getLL('feed_exporter_label_error_name_is_required');
        } else {
            if (!is_array($this->post['fields']) || !count($this->post['fields'])) {
                $erno[] = $this->pi_getLL('feed_exporter_label_error_no_fields_defined');
            }
        }
        if (empty($this->post['visual_orders_date_from'])) {
            $this->post['orders_date_from'] = '';
        }
        if (empty($this->post['visual_orders_date_till'])) {
            $this->post['orders_date_till'] = '';
        }
        if (is_array($erno) and count($erno) > 0) {
            $content .= '<div class="alert alert-danger">';
            $content .= '<h3>' . $this->pi_getLL('the_following_errors_occurred') . '</h3><ul>';
            foreach ($erno as $item) {
                $content .= '<li>' . $item . '</li>';
            }
            $content .= '</ul>';
            $content .= '</div>';
        } else {
            // lets save it
            $updateArray = array();
            $updateArray['name'] = $this->post['name'];
            $updateArray['status'] = $this->post['status'];
            $updateArray['fields'] = serialize($this->post['fields']);
            $updateArray['post_data'] = serialize($this->post);
            if (is_numeric($this->post['orders_export_id'])) {
                // edit
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_export', 'id=\'' . $this->post['orders_export_id'] . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            } else {
                // insert
                $updateArray['page_uid'] = $this->showCatalogFromPage;
                $updateArray['crdate'] = time();
                $updateArray['code'] = md5(uniqid());
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_export', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
            $this->ms['show_main'] = 1;
            header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_export_orders'));
            exit();
        }
    } else {
        if ($_REQUEST['section'] == 'edit' and is_numeric($this->get['orders_export_id'])) {
            $str = "SELECT * from tx_multishop_orders_export where id='" . $this->get['orders_export_id'] . "'";
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            $feeds = array();
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                $this->post = $row;
                $this->post['fields'] = unserialize($row['fields']);
                // now also unserialize for the custom field
                $post_data = unserialize($row['post_data']);
                $this->post['fields_headers'] = $post_data['fields_headers'];
                $this->post['fields_values'] = $post_data['fields_values'];
            }
        }
    }
    if (!$this->ms['show_main']) {
        $first_order_sql = "SELECT crdate from tx_multishop_orders order by orders_id asc limit 1";
        $first_order_qry = $GLOBALS['TYPO3_DB']->sql_query($first_order_sql);
        $first_order_rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($first_order_qry);
        $first_year = date('Y', $first_order_rs['crdate']);
        $content .= '
		<div class="panel panel-default">
		<div class="panel-heading"><h3>' . $this->pi_getLL('feed_exporter_label_orders_export_wizard') . '</h3></div>
		<div class="panel-body">
		<form method="post" action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page']) . '" id="orders_export_form" class="form-horizontal">
			<div class="form-group">
				<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('name')) . '</label>
				<div class="col-md-10">
					<input class="form-control" type="text" name="name" value="' . htmlspecialchars($this->post['name']) . '" />
				</div>
			</div>';
        // order status selectbox
        $all_orders_status = mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
        $order_status_sb = '<select name="order_status" class="form-control">
		<option value="all"' . ($post_data['order_status'] == 'all' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('all') . '</option>';
        if (is_array($all_orders_status) and count($all_orders_status)) {
            foreach ($all_orders_status as $row) {
                if ($post_data['order_status'] == $row['id']) {
                    $order_status_sb .= '<option value="' . $row['id'] . '" selected="selected">' . $row['name'] . '</option>' . "\n";
                } else {
                    $order_status_sb .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>' . "\n";
                }
            }
        }
        $order_status_sb .= '</select>';
        // shipping methods
        $shipping_methods = mslib_fe::loadShippingMethods();
        // shipping methods selectbox
        $shipping_method_sb = '<select name="shipping_method" class="form-control">
			<option value="all"' . ($post_data['shipping_method'] == 'all' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('all') . '</option>
		';
        foreach ($shipping_methods as $shipping_method) {
            $shipping_method_sb .= '<option value="' . $shipping_method['code'] . '"' . ($post_data['shipping_method'] == $shipping_method['code'] ? ' selected="selected"' : '') . '>' . $shipping_method['name'] . '</option>';
        }
        $shipping_method_sb .= '</select>';
        // payment methods
        $payment_methods = mslib_fe::loadPaymentMethods();
        // payment methods selectbox
        $payment_method_sb = '<select name="payment_method" class="form-control">
			<option value="all"' . ($post_data['payment_method'] == 'all' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('all') . '</option>
		';
        foreach ($payment_methods as $payment_method) {
            $payment_method_sb .= '<option value="' . $payment_method['code'] . '"' . ($post_data['payment_method'] == $payment_method['code'] ? ' selected="selected"' : '') . '>' . $payment_method['name'] . '</option>';
        }
        $payment_method_sb .= '</select>';
        // payment status selectbox
        $payment_status_sb = '<select name="payment_status" class="form-control">
			<option value="all"' . ($post_data['payment_status'] == 'all' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('all') . '</option>
			<option value="paid"' . ($post_data['payment_status'] == 'paid' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('paid') . '</option>
			<option value="unpaid"' . ($post_data['payment_status'] == 'unpaid' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('unpaid') . '</option>
		</select>';
        // order by selectbox
        $order_by_sb = '<select name="order_by" class="form-control">
			<option value="orders_id"' . ($post_data['order_by'] == 'orders_id' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('orders_id') . '</option>
			<option value="status_last_modified"' . ($post_data['order_by'] == 'status_last_modified' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('status_last_modified') . '</option>
			<option value="billing_name"' . ($post_data['order_by'] == 'billing_name' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('billing_name') . '</option>
			<option value="crdate"' . ($post_data['order_by'] == 'crdate' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('creation_date') . '</option>
			<option value="grand_total"' . ($post_data['order_by'] == 'grand_total' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('grand_total') . '</option>
			<option value="shipping_method_label"' . ($post_data['order_by'] == 'shipping_method_label' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('shipping_method_label') . '</option>
			<option value="payment_method_label"' . ($post_data['order_by'] == 'payment_method_label' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('payment_method_label') . '</option>
		</select>';
        // sort direction selectbox
        $sort_direction_sb = '<select name="sort_direction" class="form-control">
			<option value="desc"' . ($post_data['sort_direction'] == 'desc' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('sort_direction_desc') . '</option>
			<option value="asc"' . ($post_data['sort_direction'] == 'asc' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('sort_direction_asc') . '</option>
		</select>';
        // order type selectbox
        $order_type_sb = '<select name="order_type" class="form-control">
			<option value="all"' . ($post_data['order_type'] == 'desc' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('all') . '</option>
			<option value="by_phone"' . ($post_data['order_type'] == 'by_phone' ? ' selected="selected"' : '') . '>' . ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_manual_order'))) . '</option>
		</select>';
        // delimeter type selectbox
        $delimeter_type_sb = '<select name="delimeter_type" class="form-control">
			<option value=";"' . ($post_data['delimeter_type'] == ';' ? ' selected="selected"' : '') . '>semicolon (;)</option>
			<option value=","' . ($post_data['delimeter_type'] == ',' ? ' selected="selected"' : '') . '>comma (,)</option>
			<option value="\t"' . ($post_data['delimeter_type'] == '\t' ? ' selected="selected"' : '') . '>tabs (\t)</option>
			<option value="|"' . ($post_data['delimeter_type'] == '|' ? ' selected="selected"' : '') . '>pipe (|)</option>
		</select>';
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('multishop_orders_archiver')) {
            $content .= '
                <div class="form-group">
                    <label class="control-label col-md-2">Order table type</label>
                    <div class="col-md-10">
                        <select name="order_table_type" class="form-control">
                            <option value="active"' . ($post_data['order_table_type'] == 'active' ? ' selected="selected"' : '') . '>Active</option>
                            <option value="archive"' . ($post_data['order_table_type'] == 'archive' ? ' selected="selected"' : '') . '>Archive</option>
                        </select>
                    </div>
                </div>';
        }
        $content .= '
		 <div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('order_type')) . '</label>
			<div class="col-md-10">
			' . $order_type_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('order_date')) . '</label>
			<div class="col-md-10">
			<span class="input_label_wrapper form-inline">
				<label for="visual_orders_date_from">' . htmlspecialchars($this->pi_getLL('admin_from')) . '</label>
				<input name="visual_orders_date_from" id="visual_orders_date_from" type="text" class="form-control" value="' . $post_data['visual_orders_date_from'] . '" />
				<input name="orders_date_from" id="orders_date_from" type="hidden" value="' . $post_data['orders_date_from'] . '" />
			</span>
			<span class="input_label_wrapper form-inline">
				<label for="visual_orders_date_till">' . htmlspecialchars($this->pi_getLL('admin_till')) . '</label>
				<input name="visual_orders_date_till" id="visual_orders_date_till" type="text" class="form-control" value="' . $post_data['visual_orders_date_till'] . '" />
				<input name="orders_date_till" id="orders_date_till" type="hidden" value="' . $post_data['orders_date_till'] . '" />
			</span>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('duration_date_range')) . '</label>
			<div class="col-md-10">
			<span class="input_label_wrapper form-inline">
				<label for="start_duration">' . htmlspecialchars($this->pi_getLL('start_duration')) . '</label>
				<input name="start_duration" id="start_duration" type="text" class="form-control" value="' . $post_data['start_duration'] . '" />
			</span>
			<span class="input_label_wrapper form-inline">
				<label for="end_duration">' . htmlspecialchars($this->pi_getLL('end_duration')) . '</label>
				<input name="end_duration" id="end_duration" type="text" class="form-control" value="' . $post_data['end_duration'] . '" />
				
			</span>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('order_status')) . '</label>
			<div class="col-md-10">
			' . $order_status_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('order_payment_status')) . '</label>
			<div class="col-md-10">
			' . $payment_status_sb . '
			</div>
		</div>';

        // load enabled countries to array
        $str2 = "SELECT * from static_countries sc, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_countries c where c.page_uid='" . $this->showCatalogFromPage . "' and c2z.hide_in_frontend=0 and sc.cn_iso_nr=c.cn_iso_nr and c2z.cn_iso_nr=sc.cn_iso_nr group by c.cn_iso_nr order by sc.cn_short_en";
        //$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
        $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
        $enabled_countries = array();
        while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
            $enabled_countries[] = $row2;
        }
        $tmpcontent_con = '<select name="billing_country" class="form-control">
			<option value="all">' . $this->pi_getLL('all') . '</option>';
        $tmpcontent_con_delivery = '<select name="delivery_country" class="form-control">
			<option value="all">' . $this->pi_getLL('all') . '</option>';
        foreach ($enabled_countries as $country) {
            $tmpcontent_con .= '<option value="' . mslib_befe::strtolower($country['cn_short_en']) . '" ' . ((mslib_befe::strtolower($post_data['billing_country']) == mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '') . '>' . htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])) . '</option>';
            $tmpcontent_con_delivery .= '<option value="' . mslib_befe::strtolower($country['cn_short_en']) . '" ' . ((mslib_befe::strtolower($post_data['delivery_country']) == mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '') . '>' . htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])) . '</option>';
        }
        $tmpcontent_con .= '/<select>';
        $tmpcontent_con_delivery .= '/<select>';

        $content .= '<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('feed_exporter_fields_label_customer_billing_country')) . '</label>
			<div class="col-md-10">
			' . $tmpcontent_con . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('feed_exporter_fields_label_customer_delivery_country')) . '</label>
			<div class="col-md-10">
			' . $tmpcontent_con_delivery . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('delivery_date')) . '</label>
			<div class="col-md-10">
			<span class="input_label_wrapper form-inline">
				<label for="visual_orders_delivery_date_from">' . htmlspecialchars($this->pi_getLL('admin_from')) . '</label>
				<input name="visual_orders_delivery_date_from" id="visual_orders_delivery_date_from" type="text" class="form-control" value="' . $post_data['visual_orders_delivery_date_from'] . '" />
				<input name="orders_delivery_date_from" id="orders_delivery_date_from" type="hidden" value="' . $post_data['orders_delivery_date_from'] . '" />
			</span>
			<span class="input_label_wrapper form-inline">
				<label for="visual_orders_delivery_date_till">' . htmlspecialchars($this->pi_getLL('admin_till')) . '</label>
				<input name="visual_orders_delivery_date_till" id="visual_orders_delivery_date_till" type="text" class="form-control" value="' . $post_data['visual_orders_delivery_date_till'] . '" />
				<input name="orders_delivery_date_till" id="orders_delivery_date_till" type="hidden" value="' . $post_data['orders_delivery_date_till'] . '" />
			</span>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('shipping_method')) . '</label>
			<div class="col-md-10">
			' . $shipping_method_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('payment_method')) . '</label>
			<div class="col-md-10">
			' . $payment_method_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('order_by')) . '</label>
			<div class="col-md-10">
			' . $order_by_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('sort_direction')) . '</label>
			<div class="col-md-10">
			' . $sort_direction_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('delimited_by')) . '</label>
			<div class="col-md-10">
			' . $delimeter_type_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('maximum_number_of_order_products')) . '</label>
			<div class="col-md-10">
			<input type="text" class="form-control" name="maximum_number_of_order_products" value="' . ($post_data['maximum_number_of_order_products'] ? $post_data['maximum_number_of_order_products'] : '25') . '" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('status')) . '</label>
			<div class="col-md-10">
				<div class="radio radio-success radio-inline">
				<input name="status" id="radio0" type="radio" value="0"' . ((isset($this->post['status']) and !$this->post['status']) ? ' checked' : '') . ' /><label for="radio0">' . htmlspecialchars($this->pi_getLL('disabled')) . '</label>
				</div>
				<div class="radio radio-success radio-inline">
				<input name="status" id="radio1" type="radio" value="1"' . ((!isset($this->post['status']) or $this->post['status']) ? ' checked' : '') . ' /><label for="radio1">' . htmlspecialchars($this->pi_getLL('enabled')) . '</label>
				</div>
			</div>
		</div>
		<hr class="hide_pf">
		<div class="form-group hide_pf">
				<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('fields')) . '</label>
				<div class="col-md-10">
					<input id="add_field" name="add_field" type="button" value="' . htmlspecialchars($this->pi_getLL('add_field')) . '" class="btn btn-success" />
				</div>
		</div>
		<div id="admin_orders_exports_fields">';
        $counter = 0;
        if (is_array($this->post['fields']) and count($this->post['fields'])) {
            foreach ($this->post['fields'] as $field) {
                $counter++;
                $content .= '<div class="form-group"><label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('type')) . '</label><div class="col-md-10"><select name="fields[' . $counter . ']" rel="' . $counter . '" class="msAdminOrdersExportSelectField">';
                foreach ($array as $key => $option) {
                    $content .= '<option value="' . $key . '"' . ($field == $key ? ' selected' : '') . '>' . htmlspecialchars($option) . '</option>';
                }
                $content .= '</select> <button class="delete_field btn btn-danger" name="delete_field" type="button" value="' . htmlspecialchars($this->pi_getLL('delete')) . '"><i class="fa fa-trash-o"></i></button></div></div>';
                // custom field
                if ($field == 'custom_field') {
                    $content .= '<div class="form-group"><label></label><span class="key">Key</span><input name="fields_headers[' . $counter . ']" type="text" value="' . $this->post['fields_headers'][$counter] . '" /><span class="value">Value</span><input name="fields_values[' . $counter . ']" type="text" value="' . $this->post['fields_values'][$counter] . '" /></div>';
                }
                $content .= '';
            }
        }
        $content .= '
		</div>
		<hr>
		<div class="form-group">
				<label class="col-md-2"></label>
				<div class="col-md-10">
				<input name="Submit" type="submit" value="' . htmlspecialchars($this->pi_getLL('save')) . '" class="btn btn-success" />
				</div>
		</div>
		<input name="orders_export_id" type="hidden" value="' . $this->get['orders_export_id'] . '" />
		<input name="section" type="hidden" value="' . $_REQUEST['section'] . '" />
		</form>
		<script type="text/javascript">
		 $("#visual_orders_date_from").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
			altField: "#orders_date_from",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . $first_year . ':' . (date('Y') + 1) . '"
		});
		$("#visual_orders_date_till").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
			altField: "#orders_date_till",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . $first_year . ':' . (date('Y') + 1) . '"
		});
		$("#visual_orders_delivery_date_from").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
			altField: "#orders_delivery_date_from",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . $first_year . ':' . (date('Y') + 1) . '"
		});
		$("#visual_orders_delivery_date_till").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
			altField: "#orders_delivery_date_till",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . $first_year . ':' . (date('Y') + 1) . '"
		});
		jQuery(document).ready(function($) {
			jQuery("#admin_orders_exports_fields").sortable({
				cursor:     "move",
				//axis:       "y",
				update: function(e, ui) {
					jQuery(this).sortable("refresh");
				}
			});
			var counter=\'' . $counter . '\';
			$("#feed_type").change(function(){
				var selected=$("#feed_type option:selected").val();
				if (selected) {
					// hide
					$(".hide_pf").hide();
				} else {
					$(".hide_pf").show();
				}
			});
			$(document).on("click", "#add_field", function(event) {
				counter++;
				var item=\'<div class="form-group"><label class="control-label col-md-2">Type</label><div class="col-md-10"><select name="fields[\'+counter+\']" rel="\'+counter+\'" class="msAdminOrdersExportSelectField">';
        foreach ($array as $key => $option) {
            $content .= '<option value="' . $key . '">' . htmlspecialchars(addslashes($option)) . '</option>';
        }
        $content .= '</select> <button class="delete_field btn btn-danger" name="delete_field" type="button" value="' . htmlspecialchars($this->pi_getLL('delete')) . '"><i class="fa fa-trash-o"></i></button></div></div>\';
				$(\'#admin_orders_exports_fields\').append(item);
				$(\'select.msAdminOrdersExportSelectField\').select2({
					width:\'650px\'
				});
			});
			$(document).on("click", ".delete_field", function() {
				jQuery(this).parent().parent().remove();
			});
			$(\'.msAdminOrdersExportSelectField\').select2({
					width:\'650px\'
			});
			$(document).on("change", ".msAdminOrdersExportSelectField", function() {
				var selected=$(this).val();
				var counter=$(this).attr("rel");
				if(selected==\'custom_field\') {
					$(this).next().remove();
					$(this).parent().append(\'<div class="form-group"><label></label><span class="key">Key</span><input name="fields_headers[\'+counter+\']" type="text" /><span class="value">Value</span><input name="fields_values[\'+counter+\']" type="text" /></div>\');
				}
			});
		});
		</script></div></div>';
    }
} else {
    $this->ms['show_main'] = 1;
}
if ($this->ms['show_main']) {
    if (is_numeric($this->get['status']) and is_numeric($this->get['orders_export_id'])) {
        $updateArray = array();
        $updateArray['status'] = $this->get['status'];
        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_export', 'id=\'' . $this->get['orders_export_id'] . '\'', $updateArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    }
    if (is_numeric($this->get['delete']) and is_numeric($this->get['orders_export_id'])) {
        $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orders_export', 'id=\'' . $this->get['orders_export_id'] . '\'');
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    }
    // show listing
    $str = "SELECT * from tx_multishop_orders_export order by id desc";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $orders = array();
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $orders[] = $row;
    }
    if (is_array($orders) and count($orders)) {
        $content .= '<div class="panel panel-default"><div class="panel-heading"><h3>' . htmlspecialchars($this->pi_getLL('admin_export_orders')) . '</h3></div>
		<div class="panel-body">
		<table width="100%" border="0" align="center" class="table table-striped table-bordered" id="admin_modules_listing">
		<thead>
		<tr>
			<th class="cellID">' . htmlspecialchars($this->pi_getLL('id')) . '</th>
			<th class="cellName">' . htmlspecialchars($this->pi_getLL('name')) . '</th>
			<th class="cellDate" nowrap>' . htmlspecialchars($this->pi_getLL('created')) . '</th>
			<th class="cellStatus">' . htmlspecialchars($this->pi_getLL('status')) . '</th>
			<th class="cellDownload">' . htmlspecialchars($this->pi_getLL('download')) . '</th>
			<th class="cellAction">' . htmlspecialchars($this->pi_getLL('action')) . '</th>
			<th class="cellBackup">' . htmlspecialchars($this->pi_getLL('download_export_record')) . '</th>
		</tr>
		</thead>
		';
        foreach ($orders as $order) {
            $order['plain_text_link'] = $this->FULL_HTTP_URL . 'index.php?id=' . $this->shop_pid . '&type=2002&tx_multishop_pi1[page_section]=download_orders_export&orders_export_hash=' . $order['code'];
            $order['orders_export_link_excel'] = $this->FULL_HTTP_URL . 'index.php?id=' . $this->shop_pid . '&type=2002&tx_multishop_pi1[page_section]=download_orders_export&orders_export_hash=' . $order['code'] . '&format=excel';
            // custom page hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['ordersIterationItem'])) {
                $params = array(
                        'order' => &$order
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_orders.php']['ordersIterationItem'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom page hook that can be controlled by third-party plugin eof
            $content .= '
			<tr>
				<td align="right" class="cellID" nowrap><a href="' . $order['feed_link'] . '" target="_blank">' . htmlspecialchars($order['id']) . '</a></td>
				<td class="cellName"><a href="' . $order['plain_text_link'] . '" target="_blank">' . htmlspecialchars($order['name']) . '</a></td>
				<td class="cellDate" align="center" nowrap>' . date("Y-m-d", $order['crdate']) . '</td>
				<td class="cellStatus">
				';
            if (!$order['status']) {
                $content .= '<span class="admin_status_red" alt="Disable"></span>';
                $content .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&orders_export_id=' . $order['id'] . '&status=1') . '"><span class="admin_status_green disabled" alt="Enabled"></span></a>';
            } else {
                $content .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&orders_export_id=' . $order['id'] . '&status=0') . '"><span class="admin_status_red disabled" alt="Disabled"></span></a>';
                $content .= '<span class="admin_status_green" alt="Enable"></span>';
            }
            $content .= '</td>
			<td class="cellDownload">
				<a href="' . $order['plain_text_link'] . '" class="btn btn-success btn-sm"><i class="fa fa-download"></i> Download</a><br/>
				<a href="' . $order['orders_export_link_excel'] . '" class="btn btn-success btn-sm"><i class="fa fa-download"></i> ' . $this->pi_getLL('admin_label_link_download_as_excel') . '</a>
			</td>
			<td class="cellAction">
                <a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&orders_export_id=' . $order['id'] . '&section=edit') . '" class="btn btn-primary btn-sm admin_menu_edit"><i class="fa fa-pencil fa-fw"></i></a>
				<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&orders_export_id=' . $order['id'] . '&delete=1') . '" onclick="return confirm(\'' . $this->pi_getLL('are_you_sure') . '?\')" class="btn btn-danger btn-sm admin_menu_remove" alt="Remove"><i class="fa fa-trash-o fa-fw"></i></a>
			';
            $content .= '
			</td>
			<td class="cellBackup">
				<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&download=export_orders_task&orders_export_id=' . $order['id']) . '" class="btn btn-success btn-sm"><i>' . $this->pi_getLL('download_export_record') . '</i></a>
			</td>
			</tr>';
        }
        $content .= '</table>
        </div>
        </div>
        ';
    } else {
        $content .= '<div class="alert alert-warning"><h3>' . htmlspecialchars($this->pi_getLL('currently_there_are_no_orders_export_created')) . '</h3></div>';
    }
    $content .= '<div class="panel panel-default"><div class="panel-heading"><h3>' . $this->pi_getLL('import_export_record') . '</h3></div>
	<div class="panel-body">
<fieldset id="scheduled_import_jobs_form">
		<form action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_export_orders&upload=export_orders_task') . '" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="form-horizontal blockSubmitForm">
			<div class="form-group">
				<label for="new_name" class="control-label col-md-2">' . $this->pi_getLL('name') . '</label>
				<div class="col-md-10">
				<input class="form-control" name="new_name" type="text" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="upload_export_orders_file" class="control-label col-md-2">' . $this->pi_getLL('file') . '</label>
				<div class="col-md-10">
				<div class="input-group">
				<input type="file" name="export_orders_record_file" class="form-control">
				<span class="input-group-btn">
				<input type="submit" name="upload_export_orders_file" class="submit btn btn-success" id="upload_export_orders_file" value="upload">
				</span>
				</div>
				</div>
			</div>
		</form>
	</fieldset>';
    $content .= '<hr><div class="clearfix"><div class="pull-right"><a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&section=add') . '" class="btn btn-success"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-plus fa-stack-1x"></i></span> ' . htmlspecialchars($this->pi_getLL('add')) . '</a></div></div></div></div>';
}
?>
