<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (isset($this->get['download']) && $this->get['download'] == 'export_customers_task' && is_numeric($this->get['customers_export_id'])) {
    $sql = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
            'tx_multishop_customers_export ', // FROM ...
            'id= \'' . $this->get['customers_export_id'] . '\'', // WHERE...
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
        $filename = 'multishop_export_customers_record_' . date('YmdHis') . '.txt';
        $filepath = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $filename;
        file_put_contents($filepath, $serial_data);
        header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
        header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
        readfile($filepath); //Read and stream the file
        @unlink($filepath);
        exit();
    }
}
if (isset($this->get['upload']) && $this->get['upload'] == 'export_customers_task' && $_FILES) {
    if (!$_FILES['export_customers_record_file']['error']) {
        $filename = $_FILES['export_customers_record_file']['name'];
        $target = $this->DOCUMENT_ROOT . '/uploads/tx_multishop' . $filename;
        if (move_uploaded_file($_FILES['export_customers_record_file']['tmp_name'], $target)) {
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
            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_export', $insertArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            @unlink($target);
        }
    }
    header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_customer_export'));
    exit();
}
// defining the types
$array = array();
$array['orders_id'] = $this->pi_getLL('feed_exporter_fields_label_orders_id');
//$array['orders_status']=$this->pi_getLL('feed_exporter_fields_label_orders_status');
$array['customer_id'] = $this->pi_getLL('admin_customer_id');
$array['customer_telephone'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('telephone');
$array['customer_mobile'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('mobile');
$array['customer_www'] = $this->pi_getLL('customers') . ' ' . strtolower($this->pi_getLL('admin_external_url'));
$array['customer_fax'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('fax');
$array['customer_email'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('e-mail_address');
$array['customer_first_name'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('first_name');
$array['customer_middle_name'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('middle_name');
$array['customer_last_name'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('last_name');
$array['customer_name'] = $this->pi_getLL('admin_customer_name');
$array['customer_company'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('company');
$array['customer_street_address'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('street_address');
$array['customer_street_address_number'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('street_address_number');
$array['customer_address_number_extension'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('address_number_extension');
$array['customer_address'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('address');
$array['customer_city'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('city');
$array['customer_zip'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('admin_zip');
$array['customer_country'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('country');
$array['customer_gender'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('gender');
$array['customer_username'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('username');
$array['customer_tx_multishop_newsletter'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('newsletter');
$array['customer_salutation'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('admin_label_gender_salutation');
$array['customer_department'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('department');
$array['customer_vat_id'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('vat_id');
$array['customer_coc_id'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('coc_id');
$array['customer_contact_email'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('contact_email');
$array['customer_payment_condition'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('payment_condition');
$array['customer_usergroups'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('usergroup');
$array['foreign_customer_id'] = $this->pi_getLL('customers') . ' ' . $this->pi_getLL('foreign_customer_id');
//hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_customers.php']['adminExportCustomersColtypesHook'])) {
    $params = array(
            'array' => &$array
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_customers.php']['adminExportCustomersColtypesHook'] as $funcRef) {
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
        if (empty($this->post['visual_customers_date_from'])) {
            $this->post['customers_date_from'] = '';
        }
        if (empty($this->post['visual_customers_date_till'])) {
            $this->post['customers_date_till'] = '';
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
            if (is_numeric($this->post['customers_export_id'])) {
                // edit
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_customers_export', 'id=\'' . $this->post['customers_export_id'] . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            } else {
                // insert
                $updateArray['page_uid'] = $this->showCatalogFromPage;
                $updateArray['crdate'] = time();
                $updateArray['code'] = md5(uniqid());
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_export', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
            $this->ms['show_main'] = 1;
            header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_customer_export'));
            exit();
        }
    } else {
        if ($_REQUEST['section'] == 'edit' and is_numeric($this->get['customers_export_id'])) {
            $str = "SELECT * from tx_multishop_customers_export where id='" . $this->get['customers_export_id'] . "'";
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
        $first_order_sql = "SELECT crdate from fe_users where usergroup='" . $this->conf['fe_customer_usergroup'] . "' order by orders_id asc limit 1";
        $first_order_qry = $GLOBALS['TYPO3_DB']->sql_query($first_order_sql);
        $first_order_rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($first_order_qry);
        $first_year = date('Y', $first_order_rs['crdate']);
        $content .= '
		<div class="panel panel-default">
		<div class="panel-heading"><h3>' . $this->pi_getLL('feed_exporter_label_customers_export_wizard') . '</h3></div>
		<div class="panel-body">
		<form method="post" action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page']) . '" id="customers_export_form" class="form-horizontal">
			<div class="form-group">
				<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('name')) . '</label>
				<div class="col-md-10">
					<input type="text" class="form-control" name="name" value="' . htmlspecialchars($this->post['name']) . '" />
				</div>
			</div>';
        // delimeter type selectbox
        $delimeter_type_sb = '<select name="delimeter_type" class="form-control">
			<option value=";"' . ($post_data['delimeter_type'] == ';' ? ' selected="selected"' : '') . '>semicolon (;)</option>
			<option value=","' . ($post_data['delimeter_type'] == ',' ? ' selected="selected"' : '') . '>comma (,)</option>
			<option value="\t"' . ($post_data['delimeter_type'] == '\t' ? ' selected="selected"' : '') . '>tabs (\t)</option>
			<option value="|"' . ($post_data['delimeter_type'] == '|' ? ' selected="selected"' : '') . '>pipe (|)</option>
		</select>';
        $content .= '
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('order_date')) . '</label>
			<div class="col-md-10">
				<div class="form-inline">
					<div class="form-group input_label_wrapper">
						<div class="col-md-12">
						<label for="visual_customers_date_from">' . htmlspecialchars($this->pi_getLL('admin_from')) . '</label>
						<input name="visual_customers_date_from" id="visual_customers_date_from" class="form-control" type="text" value="' . $post_data['visual_customers_date_from'] . '" autocomplete="off" />
						<input name="customers_date_from" id="customers_date_from" type="hidden" value="' . $post_data['customers_date_from'] . '" />
						</div>
					</div>
					<div class="form-group input_label_wrapper">
						<div class="col-md-12">
						<label for="visual_customers_date_till">' . htmlspecialchars($this->pi_getLL('admin_till')) . '</label>
						<input name="visual_customers_date_till" id="visual_customers_date_till" class="form-control" type="text" value="' . $post_data['visual_customers_date_till'] . '" autocomplete="off" />
						<input name="customers_date_till" id="customers_date_till" type="hidden" value="' . $post_data['customers_date_till'] . '" />
						</div>
					</div>
				</div>
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
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('export_customer_with_discount')) . '</label>
            <div class="col-md-10">
                <div class="radio radio-success radio-inline">
                    <input name="customer_with_discount" type="radio" id="valueAll" value="all"' . ((!isset($post_data['customer_with_discount']) or $post_data['customer_with_discount'] == 'all') ? ' checked' : '') . ' /><label for="valueAll">' . htmlspecialchars($this->pi_getLL('all')) . '</label>
                </div>
                <div class="radio radio-success radio-inline">
                    <input name="customer_with_discount" type="radio" id="valueNo" value="0"' . ((isset($post_data['customer_with_discount']) and $post_data['customer_with_discount'] == '0') ? ' checked' : '') . ' /><label for="valueNo">' . htmlspecialchars($this->pi_getLL('no')) . '</label>
                </div>
                <div class="radio radio-success radio-inline">
                    <input name="customer_with_discount" type="radio" id="valueYes" value="1"' . ((isset($post_data['customer_with_discount']) and $post_data['customer_with_discount'] == '1') ? ' checked' : '') . ' /><label for="valueYes">' . htmlspecialchars($this->pi_getLL('yes')) . '</label>
                </div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('customers_status')) . '</label>
			<div class="col-md-10">
				<div class="radio radio-success radio-inline">
					<input name="customer_status" type="radio" id="value2" value="2"' . (!isset($post_data['customer_status']) || $post_data['customer_status'] == '2' ? ' checked' : '') . ' /><label for="value2">' . htmlspecialchars($this->pi_getLL('all')) . '</label>
				</div>
				<div class="radio radio-success radio-inline">
					<input name="customer_status" type="radio" id="value0" value="0"' . ($post_data['customer_status'] == '0' ? ' checked' : '') . ' /><label for="value0">' . htmlspecialchars($this->pi_getLL('disabled')) . '</label>
				</div>
				<div class="radio radio-success radio-inline">
					<input name="customer_status" type="radio" id="value1" value="1"' . ($post_data['customer_status'] == '1' ? ' checked' : '') . ' /><label for="value1">' . htmlspecialchars($this->pi_getLL('enabled')) . '</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('delimited_by')) . '</label>
			<div class="col-md-10">
			' . $delimeter_type_sb . '
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('status')) . '</label>
			<div class="col-md-10">
				<div class="radio radio-success radio-inline">
					<input name="status" type="radio" id="value0" value="0"' . ((isset($this->post['status']) and !$this->post['status']) ? ' checked' : '') . ' /><label for="value0">' . htmlspecialchars($this->pi_getLL('disabled')) . '</label>
				</div>
				<div class="radio radio-success radio-inline">
					<input name="status" type="radio" id="value1" value="1"' . ((!isset($this->post['status']) or $this->post['status']) ? ' checked' : '') . ' /><label for="value1">' . htmlspecialchars($this->pi_getLL('enabled')) . '</label>
				</div>
			</div>
		</div>
		<hr class="hide_pf">
		<div class="form-group hide_pf">
			<label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('fields')) . '</label>
			<div class="col-md-10">
				<button id="add_field" name="add_field" type="button" value="" class="btn btn-success"><i class="fa fa-plus"></i> ' . htmlspecialchars($this->pi_getLL('add_field')) . '</button>
			</div>
		</div>
		<div id="admin_customers_exports_fields">';
        $counter = 0;
        if (is_array($this->post['fields']) and count($this->post['fields'])) {
            foreach ($this->post['fields'] as $field) {
                $counter++;
                $content .= '<div class="form-group"><label class="control-label col-md-2">' . htmlspecialchars($this->pi_getLL('type')) . '</label><div class="col-md-10"><select name="fields[' . $counter . ']" rel="' . $counter . '" class="msAdminCustomersExportSelectField">';
                foreach ($array as $key => $option) {
                    $content .= '<option value="' . $key . '"' . ($field == $key ? ' selected' : '') . '>' . htmlspecialchars($option) . '</option>';
                }
                $content .= '</select><button class="delete_field btn btn-danger" name="delete_field" type="button" value="' . htmlspecialchars($this->pi_getLL('delete')) . '"><i class="fa fa-trash-o"></i></button></div></div>';
                // custom field
                if ($field == 'custom_field') {
                    $content .= '<div class="form-group"><label></label><span class="key">Key</span><input name="fields_headers[' . $counter . ']" type="text" value="' . $this->post['fields_headers'][$counter] . '" /><span class="value">Value</span><input name="fields_values[' . $counter . ']" type="text" value="' . $this->post['fields_values'][$counter] . '" /></div>';
                }
            }
        }
        $content .= '
		</div>
		<hr>
		<div class="clearfix">
			<div class="pull-right">
				<button name="Submit" type="submit" value="" class="btn btn-success"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-save fa-stack-1x"></i></span> ' . htmlspecialchars($this->pi_getLL('save')) . '</button>
			</div>
		</div>
		<input name="customers_export_id" type="hidden" value="' . $this->get['customers_export_id'] . '" />
		<input name="section" type="hidden" value="' . $_REQUEST['section'] . '" />
		</form>
		</div>
		</div>
		<script type="text/javascript">
		 $("#visual_customers_date_from").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
			altField: "#customers_date_from",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . $first_year . ':' . (date('Y') + 1) . '"
		});
		$("#visual_customers_date_till").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
			altField: "#customers_date_till",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . $first_year . ':' . (date('Y') + 1) . '"
		});
		jQuery(document).ready(function($) {
			jQuery("#admin_customers_exports_fields").sortable({
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
				var item=\'<div class="form-group"><label class="control-label col-md-2">Type</label><div class="col-md-10"><select name="fields[\'+counter+\']" rel="\'+counter+\'" class="msAdminCustomersExportSelectField">';
        foreach ($array as $key => $option) {
            $content .= '<option value="' . $key . '">' . addslashes(htmlspecialchars($option)) . '</option>';
        }
        $content .= '</select><button class="delete_field btn btn-danger" name="delete_field" type="button" value="' . htmlspecialchars($this->pi_getLL('delete')) . '"><i class="fa fa-trash-o"></i></button></div></div>\';
				$(\'#admin_customers_exports_fields\').append(item);
				$(\'select.msAdminCustomersExportSelectField\').select2({
					width:\'650px\'
				});
			});
			$(document).on("click", ".delete_field", function() {
				jQuery(this).parent().parent().remove();
			});
			$(\'.msAdminCustomersExportSelectField\').select2({
					width:\'650px\'
			});
			$(document).on("change", ".msAdminCustomersExportSelectField", function() {
				var selected=$(this).val();
				var counter=$(this).attr("rel");
				if(selected==\'custom_field\') {
					$(this).next().remove();
					$(this).parent().append(\'<div class="form-group"><label></label><span class="key">Key</span><input name="fields_headers[\'+counter+\']" type="text" /><span class="value">Value</span><input name="fields_values[\'+counter+\']" type="text" /></div>\');
				}
			});
		});
		</script>';
    }
} else {
    $this->ms['show_main'] = 1;
}
if ($this->ms['show_main']) {
    if (is_numeric($this->get['status']) and is_numeric($this->get['customers_export_id'])) {
        $updateArray = array();
        $updateArray['status'] = $this->get['status'];
        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_customers_export', 'id=\'' . $this->get['customers_export_id'] . '\'', $updateArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    }
    if (is_numeric($this->get['delete']) and is_numeric($this->get['customers_export_id'])) {
        $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_customers_export', 'id=\'' . $this->get['customers_export_id'] . '\'');
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    }
    // show listing
    $str = "SELECT * from tx_multishop_customers_export order by id desc";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $orders = array();
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $orders[] = $row;
    }
    if (is_array($orders) and count($orders)) {
        $content .= '<div class="panel-heading"><h3>' . htmlspecialchars($this->pi_getLL('admin_export_customers')) . '</h3></div>
		<table width="100%" border="0" align="center" class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">
		<tr>
			<th width="25">' . htmlspecialchars($this->pi_getLL('id')) . '</th>
			<th>' . htmlspecialchars($this->pi_getLL('name')) . '</th>
			<th width="100" nowrap>' . htmlspecialchars($this->pi_getLL('created')) . '</th>
			<th>' . htmlspecialchars($this->pi_getLL('status')) . '</th>
			<th>' . htmlspecialchars($this->pi_getLL('download')) . '</th>
			<th>' . htmlspecialchars($this->pi_getLL('action')) . '</th>
			<th width="100">' . htmlspecialchars($this->pi_getLL('download_export_record')) . '</th>
		</tr>
		';
        foreach ($orders as $order) {
            $order['plain_text_link'] = $this->FULL_HTTP_URL . 'index.php?id=' . $this->shop_pid . '&type=2002&tx_multishop_pi1[page_section]=download_customers_export&customers_export_hash=' . $order['code'];
            $order['customers_export_link_excel'] = $this->FULL_HTTP_URL . 'index.php?id=' . $this->shop_pid . '&type=2002&tx_multishop_pi1[page_section]=download_customers_export&customers_export_hash=' . $order['code'] . '&format=excel';
            // custom page hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_customers.php']['ordersIterationItem'])) {
                $params = array(
                        'order' => &$order
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_customers.php']['ordersIterationItem'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom page hook that can be controlled by third-party plugin eof
            $content .= '
			<tr>
				<td align="right" width="25" nowrap><a href="' . $order['feed_link'] . '" target="_blank">' . htmlspecialchars($order['id']) . '</a></td>
				<td><a href="' . $order['plain_text_link'] . '" target="_blank">' . htmlspecialchars($order['name']) . '</a></td>
				<td width="100" align="center" nowrap>' . date("Y-m-d", $order['crdate']) . '</td>
				<td width="50">
				';
            if (!$order['status']) {
                $content .= '<span class="admin_status_red" alt="Disable"></span>';
                $content .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&customers_export_id=' . $order['id'] . '&status=1') . '"><span class="admin_status_green disabled" alt="Enabled"></span></a>';
            } else {
                $content .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&customers_export_id=' . $order['id'] . '&status=0') . '"><span class="admin_status_red disabled" alt="Disabled"></span></a>';
                $content .= '<span class="admin_status_green" alt="Enable"></span>';
            }
            $content .= '</td>
			<td width="150">
				<a href="' . $order['plain_text_link'] . '" target="_blank" class="admin_menu">Download</a><br/>
				<a href="' . $order['customers_export_link_excel'] . '" class="admin_menu">' . $this->pi_getLL('admin_label_link_download_as_excel') . '</a>
			</td>
			<td width="50">
				<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&customers_export_id=' . $order['id'] . '&section=edit') . '" class="admin_menu_edit">edit</a>
				<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&customers_export_id=' . $order['id'] . '&delete=1') . '" onclick="return confirm(\'Are you sure?\')" class="admin_menu_remove" alt="Remove"></a>';
            $content .= '
			</td>
			<td>
				<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&download=export_customers_task&customers_export_id=' . $order['id']) . '" class="btn btn-success"><i>' . $this->pi_getLL('download_export_record') . '</i></a>
			</td>
			</tr>
			';
        }
        $content .= '</table>';
    }
    $content .= '<div class="panel panel-default">
	<div class="panel-heading"><h3>' . $this->pi_getLL('import_export_record') . '</h3></div>
	<div class="panel-body">
	<fieldset id="scheduled_import_jobs_form">
		<form action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_customer_export&upload=export_customers_task') . '" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="form-horizontal blockSubmitForm">
			<div class="form-group">
				<label for="new_name" class="control-label col-md-2">' . $this->pi_getLL('name') . '</label>
				<div class="col-md-10">
				<input class="form-control" name="new_name" type="text" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="upload_export_customers_file" class="control-label col-md-2">' . $this->pi_getLL('file') . '</label>
				<div class="col-md-10">
				<div class="input-group">
				<input type="file" name="export_customers_record_file" class="form-control">
				<span class="input-group-btn">
				<input type="submit" name="upload_export_customers_file" class="submit btn btn-success" id="upload_export_customers_file" value="upload">
				</span>
				</div>
				</div>
			</div>
		</form>
	</fieldset>';
    $content .= '<hr><div class="clearfix"><div class="pull-right"><a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&section=add') . '" class="btn btn-success"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-plus fa-stack-1x"></i></span> ' . htmlspecialchars($this->pi_getLL('add')) . '</a></div></div></div></div>';
}
