<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (isset($this->get['download']) && $this->get['download']=='export_orders_task' && is_numeric($this->get['orders_export_id'])) {
	$sql=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
		'tx_multishop_invoices_export ', // FROM ...
		'id= \''.$this->get['orders_export_id'].'\'', // WHERE...
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$serial_value=array();
		foreach ($data as $key_idx=>$key_val) {
			if ($key_idx!='id' && $key_idx!='page_uid') {
				$serial_value[$key_idx]=$key_val;
			}
		}
		$serial_data='';
		if (count($serial_value)>0) {
			$serial_data=serialize($serial_value);
		}
		$filename='multishop_export_invoice_record_'.date('YmdHis').'.txt';
		$filepath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/'.$filename;
		file_put_contents($filepath, $serial_data);
		header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
		header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
		readfile($filepath); //Read and stream the file
		@unlink($filepath);
		exit();
	}
}
if (isset($this->get['upload']) && $this->get['upload']=='export_orders_task' && $_FILES) {
	if (!$_FILES['export_orders_record_file']['error']) {
		$filename=$_FILES['export_orders_record_file']['name'];
		$target=$this->DOCUMENT_ROOT.'/uploads/tx_multishop'.$filename;
		if (move_uploaded_file($_FILES['export_orders_record_file']['tmp_name'], $target)) {
			$task_content=file_get_contents($target);
			$unserial_task_data=unserialize($task_content);
			$insertArray=array();
			$insertArray['page_uid']=$this->showCatalogFromPage;
			foreach ($unserial_task_data as $col_name=>$col_val) {
				if ($col_name=='code') {
					$insertArray[$col_name]=md5(uniqid());
				} else if ($col_name=='name' && isset($this->post['new_name']) && !empty($this->post['new_name'])) {
					$insertArray[$col_name]=$this->post['new_name'];
				} else if ($col_name=='crdate') {
					$insertArray[$col_name]=time();
				} else {
					$insertArray[$col_name]=$col_val;
				}
			}
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_invoices_export', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			@unlink($target);
		}
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_export_invoices'));
}
// defining the types
$array=array();
$array['orders_id']=$this->pi_getLL('feed_exporter_fields_label_orders_id');
//$array['orders_status']=$this->pi_getLL('feed_exporter_fields_label_orders_status');
$array['customer_id']=$this->pi_getLL('feed_exporter_fields_label_customer_id');
$array['customer_billing_telephone']=$this->pi_getLL('feed_exporter_fields_label_customer_billing_telephone');
$array['customer_billing_email']=$this->pi_getLL('feed_exporter_fields_label_customer_billing_email');
$array['customer_billing_name']=$this->pi_getLL('feed_exporter_fields_label_customer_billing_name');
$array['customer_billing_address']=$this->pi_getLL('feed_exporter_fields_label_customer_billing_address');
$array['customer_billing_city']=$this->pi_getLL('feed_exporter_fields_label_customer_billing_city');
$array['customer_billing_zip']=$this->pi_getLL('feed_exporter_fields_label_customer_billing_zip');
$array['customer_billing_country']=$this->pi_getLL('feed_exporter_fields_label_customer_billing_country');
$array['customer_delivery_telephone']=$this->pi_getLL('feed_exporter_fields_label_customer_delivery_telephone');
$array['customer_delivery_email']=$this->pi_getLL('feed_exporter_fields_label_customer_delivery_email');
$array['customer_delivery_name']=$this->pi_getLL('feed_exporter_fields_label_customer_delivery_name');
$array['customer_delivery_address']=$this->pi_getLL('feed_exporter_fields_label_customer_delivery_address');
$array['customer_delivery_city']=$this->pi_getLL('feed_exporter_fields_label_customer_delivery_city');
$array['customer_delivery_zip']=$this->pi_getLL('feed_exporter_fields_label_customer_delivery_zip');
$array['customer_delivery_country']=$this->pi_getLL('feed_exporter_fields_label_customer_delivery_country');
$array['orders_grand_total_excl_vat']=$this->pi_getLL('feed_exporter_fields_label_orders_grand_total_excl_vat');
$array['orders_grand_total_incl_vat']=$this->pi_getLL('feed_exporter_fields_label_orders_grand_total_incl_vat');
$array['payment_status']=$this->pi_getLL('feed_exporter_fields_label_orders_payment_status');
$array['shipping_method']=$this->pi_getLL('feed_exporter_fields_label_shipping_method');
$array['shipping_cost_excl_vat']=$this->pi_getLL('feed_exporter_fields_label_shipping_costs_excl_vat');
$array['shipping_cost_incl_vat']=$this->pi_getLL('feed_exporter_fields_label_shipping_costs_incl_vat');
$array['shipping_cost_vat_rate']=$this->pi_getLL('feed_exporter_fields_label_shipping_costs_tax_rate');
$array['payment_method']=$this->pi_getLL('feed_exporter_fields_label_payment_method');
$array['payment_cost_excl_vat']=$this->pi_getLL('feed_exporter_fields_label_payment_costs_excl_vat');
$array['payment_cost_incl_vat']=$this->pi_getLL('feed_exporter_fields_label_payment_costs_incl_vat');
$array['payment_cost_vat_rate']=$this->pi_getLL('feed_exporter_fields_label_payment_costs_tax_rate');
$array['order_products']=$this->pi_getLL('feed_exporter_fields_label_order_products');
$array['order_total_vat']=$this->pi_getLL('feed_exporter_fields_label_order_total_vat');
$array['invoice_number']=$this->pi_getLL('feed_exporter_fields_label_invoice_number');
$array['invoice_create_date']=$this->pi_getLL('feed_exporter_fields_label_invoice_date');
$array['invoice_due_date']=$this->pi_getLL('feed_exporter_fields_label_invoice_due_date');
$array['invoice_reference']=$this->pi_getLL('feed_exporter_fields_label_invoice_reference');
$array['invoice_status']=$this->pi_getLL('feed_exporter_fields_label_invoice_status');
$array['invoice_ordered_by']=$this->pi_getLL('feed_exporter_fields_label_invoice_ordered_by');
$array['invoice_to']=$this->pi_getLL('feed_exporter_fields_label_invoice_to');
$array['invoice_payment_condition']=$this->pi_getLL('feed_exporter_fields_label_invoice_payment_condition');
$array['invoice_paid_status']=$this->pi_getLL('feed_exporter_fields_label_invoice_paid_status');
$array['invoice_reversal_status']=$this->pi_getLL('feed_exporter_fields_label_invoice_reversal_status');
$array['invoice_reversal_related_id']=$this->pi_getLL('feed_exporter_fields_label_invoice_reversal_related_id');
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
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_invoices.php']['adminExportInvoiceColtypesHook'])) {
	$params=array(
		'array'=>&$array
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_invoices.php']['adminExportInvoiceColtypesHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
asort($array);
if ($_REQUEST['section']=='edit' or $_REQUEST['section']=='add') {
	if ($this->post) {
		$erno=array();
		if (!$this->post['name']) {
			$erno[]=$this->pi_getLL('feed_exporter_label_error_name_is_required');
		} else {
			if (!is_array($this->post['fields']) || !count($this->post['fields'])) {
				$erno[]=$this->pi_getLL('feed_exporter_label_error_no_fields_defined');
			}
		}
		if (empty($this->post['visual_orders_date_from'])) {
			$this->post['orders_date_from']='';
		}
		if (empty($this->post['visual_orders_date_till'])) {
			$this->post['orders_date_till']='';
		}
		if (is_array($erno) and count($erno)>0) {
			$content.='<div class="error_msg">';
			$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content.='<li>'.$item.'</li>';
			}
			$content.='</ul>';
			$content.='</div>';
		} else {
			// lets save it
			$updateArray=array();
			$updateArray['name']=$this->post['name'];
			$updateArray['status']=$this->post['status'];
			$updateArray['fields']=serialize($this->post['fields']);
			$updateArray['post_data']=serialize($this->post);
			if (is_numeric($this->post['invoices_export_id'])) {
				// edit
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices_export', 'id=\''.$this->post['invoices_export_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				// insert
				$updateArray['page_uid']=$this->showCatalogFromPage;
				$updateArray['crdate']=time();
				$updateArray['code']=md5(uniqid());
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_invoices_export', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$this->ms['show_main']=1;
			header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_export_invoices'));
		}
	} else {
		if ($_REQUEST['section']=='edit' and is_numeric($this->get['invoices_export_id'])) {
			$str="SELECT * from tx_multishop_invoices_export where id='".$this->get['invoices_export_id']."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$feeds=array();
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$this->post=$row;
				$this->post['fields']=unserialize($row['fields']);
				// now also unserialize for the custom field
				$post_data=unserialize($row['post_data']);
				$this->post['fields_headers']=$post_data['fields_headers'];
				$this->post['fields_values']=$post_data['fields_values'];
			}
		}
	}
	if (!$this->ms['show_main']) {
		$first_order_sql="SELECT crdate from tx_multishop_orders order by orders_id asc limit 1";
		$first_order_qry=$GLOBALS['TYPO3_DB']->sql_query($first_order_sql);
		$first_order_rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($first_order_qry);
		$first_year=date('Y', $first_order_rs['crdate']);
		$content.='
		<div class="main-heading"><h2>'.$this->pi_getLL('feed_exporter_label_invoices_export_wizard').'</h2></div>
		<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" id="invoices_export_form">
			<div class="account-field">
				<label>'.htmlspecialchars($this->pi_getLL('name')).'</label><input type="text" name="name" value="'.htmlspecialchars($this->post['name']).'" />
			</div>';
		// order status selectbox
		$all_orders_status=mslib_fe::getAllOrderStatus();
		$order_status_sb='<select name="order_status">
		<option value="all"'.($post_data['order_status']=='all' ? ' selected="selected"' : '').'>'.$this->pi_getLL('all').'</option>';
		if (is_array($all_orders_status) and count($all_orders_status)) {
			foreach ($all_orders_status as $row) {
				if ($post_data['order_status']==$row['id']) {
					$order_status_sb.='<option value="'.$row['id'].'" selected="selected">'.$row['name'].'</option>'."\n";
				} else {
					$order_status_sb.='<option value="'.$row['id'].'">'.$row['name'].'</option>'."\n";
				}
			}
		}
		$order_status_sb.='</select>';
		// payment status selectbox
		$payment_status_sb='<select name="payment_status">
			<option value="all"'.($post_data['payment_status']=='all' ? ' selected="selected"' : '').'>'.$this->pi_getLL('all').'</option>
			<option value="paid"'.($post_data['payment_status']=='paid' ? ' selected="selected"' : '').'>'.$this->pi_getLL('paid').'</option>
			<option value="unpaid"'.($post_data['payment_status']=='unpaid' ? ' selected="selected"' : '').'>'.$this->pi_getLL('unpaid').'</option>
		</select>';
		// order by selectbox
		$order_by_sb='<select name="order_by">
			<option value="orders_id"'.($post_data['order_by']=='orders_id' ? ' selected="selected"' : '').'>'.$this->pi_getLL('orders_id').'</option>
			<option value="status_last_modified"'.($post_data['order_by']=='status_last_modified' ? ' selected="selected"' : '').'>'.$this->pi_getLL('status_last_modified').'</option>
			<option value="billing_name"'.($post_data['order_by']=='billing_name' ? ' selected="selected"' : '').'>'.$this->pi_getLL('billing_name').'</option>
			<option value="crdate"'.($post_data['order_by']=='crdate' ? ' selected="selected"' : '').'>'.$this->pi_getLL('creation_date').'</option>
			<option value="grand_total"'.($post_data['order_by']=='grand_total' ? ' selected="selected"' : '').'>'.$this->pi_getLL('grand_total').'</option>
			<option value="shipping_method_label"'.($post_data['order_by']=='shipping_method_label' ? ' selected="selected"' : '').'>'.$this->pi_getLL('shipping_method_label').'</option>
			<option value="payment_method_label"'.($post_data['order_by']=='payment_method_label' ? ' selected="selected"' : '').'>'.$this->pi_getLL('payment_method_label').'</option>
		</select>';
		// sort direction selectbox
		$sort_direction_sb='<select name="payment_status">
			<option value="desc"'.($post_data['payment_status']=='desc' ? ' selected="selected"' : '').'>'.$this->pi_getLL('sort_direction_desc').'</option>
			<option value="asc"'.($post_data['payment_status']=='asc' ? ' selected="selected"' : '').'>'.$this->pi_getLL('sort_direction_asc').'</option>
		</select>';
		// order type selectbox
		$order_type_sb='<select name="order_type">
			<option value="all"'.($post_data['order_type']=='desc' ? ' selected="selected"' : '').'>'.$this->pi_getLL('orders').'</option>
			<option value="by_phone"'.($post_data['order_type']=='by_phone' ? ' selected="selected"' : '').'>'.ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_manual_order'))).'</option>
			<option value="proposal"'.($post_data['order_type']=='proposal' ? ' selected="selected"' : '').'>'.$this->pi_getLL('admin_proposals').'</option>
		</select>';
		// delimeter type selectbox
		$delimeter_type_sb='<select name="delimeter_type">
			<option value=";"'.($post_data['order_type']==';' ? ' selected="selected"' : '').'>semicolon (;)</option>
			<option value=","'.($post_data['order_type']==',' ? ' selected="selected"' : '').'>comma (,)</option>
			<option value="\t"'.($post_data['order_type']=='\t' ? ' selected="selected"' : '').'>tabs (\t)</option>
			<option value="|"'.($post_data['order_type']=='|' ? ' selected="selected"' : '').'>pipe (|)</option>
		</select>';
		$content.='
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_type')).'</label>
			'.$order_type_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_date')).'</label>
			<div class="input_label_wrapper">
				<label for="visual_orders_date_from">'.htmlspecialchars($this->pi_getLL('admin_from')).'</label>
				<input name="visual_orders_date_from" id="visual_orders_date_from" type="text" value="'.$post_data['visual_orders_date_from'].'" />
				<input name="orders_date_from" id="orders_date_from" type="hidden" value="'.$post_data['orders_date_from'].'" />
			</div>
			<div class="input_label_wrapper">
				<label for="visual_orders_date_till">'.htmlspecialchars($this->pi_getLL('admin_till')).'</label>
				<input name="visual_orders_date_till" id="visual_orders_date_till" type="text" value="'.$post_data['visual_orders_date_till'].'" />
				<input name="orders_date_till" id="orders_date_till" type="hidden" value="'.$post_data['orders_date_till'].'" />
			</div>
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_status')).'</label>
			'.$order_status_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_payment_status')).'</label>
			'.$payment_status_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('order_by')).'</label>
			'.$order_by_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('sort_direction')).'</label>
			'.$sort_direction_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('delimited_by')).'</label>
			'.$delimeter_type_sb.'
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('maximum_number_of_order_products')).'</label>
			<input type="text" name="maximum_number_of_order_products" value="'.($post_data['maximum_number_of_order_products'] ? $post_data['maximum_number_of_order_products'] : '25').'" />
		</div>
		<div class="account-field">
			<label>'.htmlspecialchars($this->pi_getLL('status')).'</label>
			<input name="status" type="radio" value="0"'.((isset($this->post['status']) and !$this->post['status']) ? ' checked' : '').' /> '.htmlspecialchars($this->pi_getLL('disabled')).'
			<input name="status" type="radio" value="1"'.((!isset($this->post['status']) or $this->post['status']) ? ' checked' : '').' /> '.htmlspecialchars($this->pi_getLL('enabled')).'
		</div>
		<div class="account-field hide_pf">
			<div class="hr"></div>
		</div>
		<div class="account-field hide_pf">
				<label>'.htmlspecialchars($this->pi_getLL('fields')).'</label>
				<input id="add_field" name="add_field" type="button" value="'.htmlspecialchars($this->pi_getLL('add_field')).'" class="btn btn-success" />
		</div>
		<div id="admin_invoices_exports_fields">';
		$counter=0;
		if (is_array($this->post['fields']) and count($this->post['fields'])) {
			foreach ($this->post['fields'] as $field) {
				$counter++;
				$content.='<div><div class="account-field"><label>'.htmlspecialchars($this->pi_getLL('type')).'</label><select name="fields['.$counter.']" rel="'.$counter.'" class="msAdminInvoicesExportSelectField">';
				foreach ($array as $key=>$option) {
					$content.='<option value="'.$key.'"'.($field==$key ? ' selected' : '').'>'.htmlspecialchars($option).'</option>';
				}
				$content.='</select><input class="delete_field btn btn-success" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div>';
				// custom field
				if ($field=='custom_field') {
					$content.='<div class="account-field"><label></label><span class="key">Key</span><input name="fields_headers['.$counter.']" type="text" value="'.$this->post['fields_headers'][$counter].'" /><span class="value">Value</span><input name="fields_values['.$counter.']" type="text" value="'.$this->post['fields_values'][$counter].'" /></div>';
				}
				$content.='
				</div>';
			}
		}
		$content.='
		</div>
		<div class="account-field">
			<div class="hr"></div>
		</div>
		<div class="account-field">
				<label>&nbsp;</label>
				<span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.htmlspecialchars($this->pi_getLL('save')).'" class="btn btn-success" /></span>
		</div>
		<input name="invoices_export_id" type="hidden" value="'.$this->get['invoices_export_id'].'" />
		<input name="section" type="hidden" value="'.$_REQUEST['section'].'" />
		</form>
		<script type="text/javascript">
		 $("#visual_orders_date_from").datepicker({
			dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'yy/mm/dd').'",
			altField: "#orders_date_from",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "'.$first_year.':'.(date('Y')+1).'"
		});
		$("#visual_orders_date_till").datepicker({
			dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'yy/mm/dd').'",
			altField: "#orders_date_till",
        	altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "'.$first_year.':'.(date('Y')+1).'"
		});
		jQuery(document).ready(function($) {
			jQuery("#admin_invoices_exports_fields").sortable({
				cursor:     "move",
				//axis:       "y",
				update: function(e, ui) {
					jQuery(this).sortable("refresh");
				}
			});
			var counter=\''.$counter.'\';
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
				var item=\'<div><div class="account-field"><label>Type</label><select name="fields[\'+counter+\']" rel="\'+counter+\'" class="msAdminInvoicesExportSelectField">';
		foreach ($array as $key=>$option) {
			$content.='<option value="'.$key.'">'.htmlspecialchars($option).'</option>';
		}
		$content.='</select><input class="delete_field btn btn-success" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div></div>\';
				$(\'#admin_invoices_exports_fields\').append(item);
				$(\'select.msAdminInvoicesExportSelectField\').select2({
					width:\'650px\'
				});
			});
			$(document).on("click", ".delete_field", function() {
				jQuery(this).parent().remove();
			});
			$(\'.msAdminInvoicesExportSelectField\').select2({
					width:\'650px\'
			});
			$(document).on("change", ".msAdminInvoicesExportSelectField", function() {
				var selected=$(this).val();
				var counter=$(this).attr("rel");
				if(selected==\'custom_field\') {
					$(this).next().remove();
					$(this).parent().append(\'<div class="account-field"><label></label><span class="key">Key</span><input name="fields_headers[\'+counter+\']" type="text" /><span class="value">Value</span><input name="fields_values[\'+counter+\']" type="text" /></div>\');
				}
			});
		});
		</script>';
	}
} else {
	$this->ms['show_main']=1;
}
if ($this->ms['show_main']) {
	if (is_numeric($this->get['status']) and is_numeric($this->get['invoices_export_id'])) {
		$updateArray=array();
		$updateArray['status']=$this->get['status'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices_export', 'id=\''.$this->get['invoices_export_id'].'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	if (is_numeric($this->get['delete']) and is_numeric($this->get['invoices_export_id'])) {
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_invoices_export', 'id=\''.$this->get['invoices_export_id'].'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	// show listing
	$str="SELECT * from tx_multishop_invoices_export order by id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$orders=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$orders[]=$row;
	}
	if (is_array($orders) and count($orders)) {
		$content.='<div class="main-heading"><h2>'.htmlspecialchars($this->pi_getLL('admin_export_invoices')).'</h2></div>
		<table width="100%" border="0" align="center" class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">
		<tr>
			<th width="25">'.htmlspecialchars($this->pi_getLL('id')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('name')).'</th>
			<th width="100" nowrap>'.htmlspecialchars($this->pi_getLL('created')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('status')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('download')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('action')).'</th>
			<th width="100">'.htmlspecialchars($this->pi_getLL('download_export_record')).'</th>
		</tr>
		';
		foreach ($orders as $order) {
			$order['plain_text_link']=$this->FULL_HTTP_URL.'index.php?id='.$this->shop_pid.'&type=2002&tx_multishop_pi1[page_section]=download_invoices_export&invoices_export_hash='.$order['code'];
			$order['invoices_export_link_excel']=$this->FULL_HTTP_URL.'index.php?id='.$this->shop_pid.'&type=2002&tx_multishop_pi1[page_section]=download_invoices_export&invoices_export_hash='.$order['code'].'&format=excel';
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_invoices.php']['ordersIterationItem'])) {
				$params=array(
					'order'=>&$order
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_export_invoices.php']['ordersIterationItem'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom page hook that can be controlled by third-party plugin eof
			$content.='
			<tr>
				<td align="right" width="25" nowrap><a href="'.$order['feed_link'].'" target="_blank">'.htmlspecialchars($order['id']).'</a></td>
				<td><a href="'.$order['plain_text_link'].'" target="_blank">'.htmlspecialchars($order['name']).'</a></td>
				<td width="100" align="center" nowrap>'.date("Y-m-d", $order['crdate']).'</td>
				<td width="50">
				';
			if (!$order['status']) {
				$content.='<span class="admin_status_red" alt="Disable"></span>';
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&invoices_export_id='.$order['id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';
			} else {
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&invoices_export_id='.$order['id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';
				$content.='<span class="admin_status_green" alt="Enable"></span>';
			}
			$content.='</td>
			<td width="150">
				<a href="'.$order['plain_text_link'].'" target="_blank" class="admin_menu">Download</a><br/>
				<a href="'.$order['invoices_export_link_excel'].'" class="admin_menu">'.$this->pi_getLL('admin_label_link_download_as_excel').'</a>
			</td>
			<td width="50">
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&invoices_export_id='.$order['id'].'&section=edit').'" class="admin_menu_edit">edit</a>
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&invoices_export_id='.$order['id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="admin_menu_remove" alt="Remove"></a>';
			$content.='
			</td>
			<td>
				<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&download=export_orders_task&orders_export_id='.$order['id']).'" class="btn btn-success"><i>'.$this->pi_getLL('download_export_record').'</i></a>
			</td>
			</tr>
			';
		}
		$content.='</table>';
	} else {
		$content.='<h3>'.htmlspecialchars($this->pi_getLL('currently_there_are_no_invoices_export_created')).'</h3>';
	}
	$content.='<fieldset id="scheduled_import_jobs_form"><legend>'.$this->pi_getLL('import_export_record').'</legend>
		<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_export_invoices&upload=export_orders_task').'" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="blockSubmitForm">
			<div class="account-field">
				<label for="new_name">'.$this->pi_getLL('name').'</label>
				<input name="new_name" type="text" value="" />
			</div>
			<div class="account-field">
				<label for="upload_export_orders_file">'.$this->pi_getLL('file').'</label>
				<input type="file" name="export_orders_record_file">&nbsp;<input type="submit" name="upload_export_orders_file" class="submit btn btn-success" id="upload_export_orders_file" value="upload">
			</div>
		</form>
	</fieldset>';
	$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&section=add').'" class="msBackendButton continueState arrowRight arrowPosLeft float_right"><span>'.htmlspecialchars($this->pi_getLL('add')).'</span></a>';
}
?>