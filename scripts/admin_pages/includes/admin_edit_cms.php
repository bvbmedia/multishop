<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$subpartArray['###VALUE_REFERRER###']='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
}
$tabs=array();
if ($_REQUEST['action']=='edit_cms') {
	$str="SELECT * from tx_multishop_cms c, tx_multishop_cms_description cd where c.id='".$_REQUEST['cms_id']."' and cd.id=c.id";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$cms[$row['language_id']]=$row;
	}
}
if ($this->post and $_REQUEST['action']=='edit_cms') {
	if ($this->post['cms_id']) {
		// update
		$array=array();
		if (!$this->post['tx_multishop_pi1']['type'] and $this->post['tx_multishop_pi1']['custom_type']) {
			$array['type']=$this->post['tx_multishop_pi1']['custom_type'];
		} else {
			$array['type']=$this->post['tx_multishop_pi1']['type'];
		}
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_cms', 'id=\''.addslashes($this->post['cms_id']).'\'', $array);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$cms_id=$this->post['cms_id'];
	} else {
		// add
		$array=array();
		$array['status']=1;
		$array['page_uid']=$this->shop_pid;
		if (!$this->post['tx_multishop_pi1']['type'] and $this->post['tx_multishop_pi1']['custom_type']) {
			$array['type']=$this->post['tx_multishop_pi1']['custom_type'];
		} else {
			$array['type']=$this->post['tx_multishop_pi1']['type'];
		}
		$array['crdate']=time();
		$array['hash']=md5(uniqid('', TRUE));
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_cms', $array);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$cms_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
	}
	if (is_array($this->post['cms_name'])) {
		foreach ($this->post['cms_name'] as $key=>$value) {
			$str="select 1 from tx_multishop_cms_description where id='".$cms_id."' and language_id='".$key."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				$array=array();
				$array['name']=$value;
				$array['content']=$this->post['cms_content'][$key];
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_cms_description', 'id=\''.addslashes($cms_id).'\' and language_id=\''.$key.'\'', $array);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				$array=array();
				$array['id']=$cms_id;
				$array['language_id']=$key;
				$array['name']=$value;
				$array['content']=$this->post['cms_content'][$key];
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_cms_description', $array);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
	if ($this->post['tx_multishop_pi1']['referrer']) {
		header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
		exit();
	} else {
		header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms', 1));
		exit();
	}
}
if ($cms['id'] or $_REQUEST['action']=='edit_cms') {
	$save_block='
		<div class="save_block">
			<a href="'.$subpartArray['###VALUE_REFERRER###'].'" class="msBackendButton backState arrowLeft arrowPosLeft"><span>'.$this->pi_getLL('cancel').'</span></a>
			<span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" /></span>
		</div>
	';
	$types=array();
	$payment_methods=mslib_fe::loadPaymentMethods();
	// Home
	$types['home_top']='Home '.$this->pi_getLL('top');
	$types['home_bottom']='Home '.$this->pi_getLL('bottom');
	// psp pages
	$types['psp_accepturl']='PSP: '.htmlspecialchars($this->pi_getLL('payment_accepted_page'));
	$types['psp_pendingurl']='PSP: '.htmlspecialchars($this->pi_getLL('payment_pending_page', 'Payment Pending Page'));
	$types['psp_declineurl']='PSP: '.htmlspecialchars($this->pi_getLL('payment_declined_page'));
	$types['psp_exceptionurl']='PSP: '.htmlspecialchars($this->pi_getLL('payment_exception_page'));
	$types['psp_cancelurl']='PSP: '.htmlspecialchars($this->pi_getLL('payment_cancelled_page'));
	// psp pages eof
	$types['email_order_proposal']=htmlspecialchars($this->pi_getLL('email_order_proposal_letter'));
	$types['email_order_confirmation']=htmlspecialchars($this->pi_getLL('email_order_confirmation_letter'));
	if (is_array($payment_methods)) {
		foreach ($payment_methods as $key=>$value) {
			$types['email_order_confirmation_'.$key]=htmlspecialchars($this->pi_getLL('email_order_confirmation_letter')).' ('.$key.')';
		}
	}
	$types['email_order_paid_letter']=htmlspecialchars($this->pi_getLL('email_order_paid_letter'));
	if (is_array($payment_methods)) {
		foreach ($payment_methods as $key=>$value) {
			$types['email_order_paid_letter_'.$key]=htmlspecialchars($this->pi_getLL('email_order_paid_letter')).' ('.$key.')';
		}
	}
	$types['email_order_status_changed']=htmlspecialchars($this->pi_getLL('email_order_status_changed_letter'));
	$types['email_order_status_changed']=$this->pi_getLL('email_order_status_changed_letter').' (Default)';
	$orders_status=mslib_fe::getAllOrderStatus(0);
	if (is_array($orders_status) and count($orders_status)) {
		foreach ($orders_status as $item) {
			$types['email_order_status_changed_'.t3lib_div::strtolower($item['name'])]=$this->pi_getLL('email_order_status_changed_letter').' ('.$item['name'].')';
		}
	}
	$types['order_received_thank_you_page']=htmlspecialchars($this->pi_getLL('checkout_finished_page'));
	if (is_array($payment_methods)) {
		foreach ($payment_methods as $key=>$value) {
			$types['order_received_thank_you_page_'.$key]=htmlspecialchars($this->pi_getLL('checkout_finished_page')).' ('.$key.')';
		}
	}
	// payment reminder email templates
	$types['payment_reminder_email_templates']=htmlspecialchars($this->pi_getLL('payment_reminder_email_templates', 'Payment reminder email templates'));
	if (is_array($payment_methods)) {
		foreach ($payment_methods as $key=>$value) {
			$types['payment_reminder_email_templates_'.$key]=htmlspecialchars($this->pi_getLL('payment_reminder_email_templates', 'Payment reminder email templates')).' ('.$key.')';
		}
	}
	// General conditions
	$types['general_conditions']=$this->pi_getLL('general_conditions');
	$types['email_create_account_confirmation']=$this->pi_getLL('email_create_account_confirmation');
	$types['create_account_thank_you_page']=$this->pi_getLL('create_account_thank_you_page');
	$types['email_alert_quantity_threshold_letter']=$this->pi_getLL('email_alert_quantity_threshold_letter', 'Alert quantity threshold e-mail content');
	// invoice pdf
	$types['pdf_invoice_header_message']=$this->pi_getLL('pdf_invoice_header_message', 'PDF Invoice header message before order details table');
	$types['pdf_invoice_footer_message']=$this->pi_getLL('pdf_invoice_footer_message', 'PDF Invoice footer message after order details table');
	// extra cms type
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_cms.php']['adminEditCMSExtraTypes'])) {
		$params=array('types'=>&$types);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_cms.php']['adminEditCMSExtraTypes'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	$tmpcontent.='<div class="account-field" id="cms_types">
			<label for="type">Type
			<span class="multishop_help_icon">
				<a href="http://www.typo3multishop.com/help/english/multishop-owners/setting-up-your-multishop/catalog/content-management/e-mail-order-confirmation-letter/" target="_blank"></a>
			</span>
			</label>
			<select name="tx_multishop_pi1[type]" id="selected_type"><option value="" data-title="'.htmlspecialchars($this->pi_getLL('choose_type_of_content')).'">'.htmlspecialchars($this->pi_getLL('choose_type_of_content')).'</option>';
	asort($types);
	foreach ($types as $key=>$value) {
		$tmpcontent.='<option value="'.$key.'" '.(($cms[0]['type']==$key) ? 'selected' : '').' data-title="'.htmlspecialchars($value).'">'.htmlspecialchars('<h3>'.$value.'</h3>Key: '.$key).'</option>'."\n";
	}
	$tmpcontent.='</select>
		</div>
		<div class="account-field custom_type">
			<label>custom type</label>
			<div><input name="tx_multishop_pi1[custom_type]" type="text" value="'.htmlspecialchars($cms[0]['type']).'" class="text" /></div>
		</div>
		<script type="text/javascript">
			function updateForm() {
				var selected_type=$("#selected_type option:selected").val();
				if (selected_type) {
					$(".custom_type").hide();
				} else {
					$(".custom_type").show();
				}
			}
			$("#selected_type").change(function(){
				updateForm();
			});
			jQuery(document).ready(function($) {
				updateForm();
			});
		</script>
		<div class="account-field" style="display:none" id="msadminMarkersBox">
			<label>'.$this->pi_getLL('marker').'</label>
			<div class="valueField">
				<table width="100%" cellpadding="0" cellspacing="0" border="0" id="product_import_table" class="msAdminTooltipTable msZebraTable msadmin_orders_listing">
				<tr>
					<th>'.$this->pi_getLL('marker').'</th>
					<th>'.$this->pi_getLL('description').'</th>
				</tr>
				';
	$markers=array();
	$markers['GENDER_SALUTATION']=$this->pi_getLL('admin_label_gender_salutation');
	$markers['DELIVERY_FIRST_NAME']=$this->pi_getLL('admin_label_cms_marker_first_name_delivery');
	$markers['DELIVERY_LAST_NAME']=$this->pi_getLL('admin_label_cms_marker_last_name_delivery');
	$markers['BILLING_FIRST_NAME']=$this->pi_getLL('admin_label_cms_marker_first_name_billing');
	$markers['BILLING_LAST_NAME']=$this->pi_getLL('admin_label_cms_marker_last_name_billing');
	$markers['BILLING_TELEPHONE']=$this->pi_getLL('admin_label_cms_marker_telephone_billing');
	$markers['DELIVERY_TELEPHONE']=$this->pi_getLL('admin_label_cms_marker_telephone_delivery');
	$markers['BILLING_MOBILE']=$this->pi_getLL('admin_label_cms_marker_mobile_billing');
	$markers['DELIVERY_MOBILE']=$this->pi_getLL('admin_label_cms_marker_mobile_delivery');
	$markers['FULL_NAME']=$this->pi_getLL('admin_label_cms_marker_full_name_billing');
	$markers['DELIVERY_FULL_NAME']=$this->pi_getLL('admin_label_cms_marker_full_name_delivery');
	$markers['CUSTOMER_EMAIL']=$this->pi_getLL('admin_label_cms_marker_customer_email');
	$markers['ORDER_DATE_LONG']=$this->pi_getLL('admin_label_cms_marker_order_date_in_long_format');
	$markers['CURRENT_DATE_LONG']=$this->pi_getLL('admin_label_cms_marker_current_date_in_long_format');
	$markers['STORE_NAME']=$this->pi_getLL('admin_label_cms_marker_store_name');
	$markers['TOTAL_AMOUNT']=$this->pi_getLL('admin_label_cms_marker_order_total_amount');
	$markers['PROPOSAL_NUMBER']=$this->pi_getLL('admin_label_cms_marker_proposal_number');
	$markers['ORDER_NUMBER']=$this->pi_getLL('admin_label_cms_marker_order_number');
	$markers['BILLING_ADDRESS']=$this->pi_getLL('admin_label_cms_marker_billing_address');
	$markers['BILLING_COMPANY']=$this->pi_getLL('admin_label_cms_marker_billing_company');
	$markers['DELIVERY_COMPANY']=$this->pi_getLL('admin_label_cms_marker_delivery_company');
	$markers['DELIVERY_ADDRESS']=$this->pi_getLL('admin_label_cms_marker_delivery_address');
	$markers['CUSTOMER_ID']=$this->pi_getLL('admin_label_cms_marker_customer_id');
	$markers['SHIPPING_METHOD']=$this->pi_getLL('admin_label_cms_marker_shipping_method');
	$markers['PAYMENT_METHOD']=$this->pi_getLL('admin_label_cms_marker_payment_method');
	$markers['ORDER_DETAILS']=$this->pi_getLL('admin_label_cms_marker_order_details');
	if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
		$markers['INVOICE_LINK']=$this->pi_getLL('admin_label_cms_marker_invoice_link');
		$markers['INVOICE_NUMBER']=$this->pi_getLL('admin_label_cms_marker_invoice_number');
	}
	$markers['BILLING_NAME']=$this->pi_getLL('admin_label_cms_marker_name_billing');
	$markers['BILLING_EMAIL']=$this->pi_getLL('admin_label_cms_marker_customer_email_billing');
	$markers['DELIVERY_EMAIL']=$this->pi_getLL('admin_label_cms_marker_customer_email_delivery');
	$markers['DELIVERY_NAME']=$this->pi_getLL('admin_label_cms_marker_name_delivery');
	$markers['CUSTOMER_COMMENTS']=$this->pi_getLL('admin_label_cms_marker_customer_comments_update_status');
	$markers['OLD_ORDER_STATUS']=$this->pi_getLL('admin_label_cms_marker_old_order_status');
	$markers['ORDER_STATUS']=$this->pi_getLL('admin_label_cms_marker_new_order_status');
	$markers['EXPECTED_DELIVERY_DATE']=$this->pi_getLL('admin_label_cms_marker_expected_delivery_date');
	$markers['TRACK_AND_TRACE_CODE']=$this->pi_getLL('admin_label_cms_marker_track_and_trace_code');
	$markers['CONFIRMATION_LINK']=$this->pi_getLL('admin_label_cms_marker_create_account_confirmation_link');
	$markers['PAYMENT_PAGE_LINK']=$this->pi_getLL('admin_label_cms_marker_payment_link');
	//hook to let other plugins further manipulate the markers
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin/admin_edit_cms.php']['CmsMarkersPostProc'])) {
		$params=array(
			'markers'=>&$markers
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin/admin_edit_cms.php']['CmsMarkersPostProc'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	ksort($markers);
	$tr_subtype='';
	foreach ($markers as $key=>$label) {
		if (!$tr_subtype or $tr_subtype=='even') {
			$tr_subtype='odd';
		} else {
			$tr_subtype='even';
		}
		$tmpcontent.='<tr class="'.$tr_subtype.'"><td class="marker_key">###'.$key.'###</td><td class="marker_description">'.htmlspecialchars($label).'</td></tr>'."\n";
	}
	$tmpcontent.='</table>
				</div>
			</div>';
	foreach ($this->languages as $key=>$language) {
		$tmpcontent.='
		<div class="account-field">
		<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
			$tmpcontent.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
		}
		$tmpcontent.=''.$language['title'].'
		</div>
		<div class="account-field">
			<label for="cms_name['.$language['uid'].']">'.htmlspecialchars($this->pi_getLL('name')).'</label>
			<input spellcheck="true" type="text" class="text" name="cms_name['.$language['uid'].']" id="cms_name['.$language['uid'].']" value="'.htmlspecialchars($cms[$language['uid']]['name']).'">
			<span><a href="#" class="tooltipMarker" title="Dynamic markers">markers</a></span>
		</div>
		<div class="account-field">
			<label for="cms_content['.$language['uid'].']">'.htmlspecialchars($this->pi_getLL('content')).'</label>
			<textarea spellcheck="true" name="cms_content['.$language['uid'].']" id="cms_content['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($cms[$language['uid']]['content']).'</textarea>
		</div>';
	}
	$tabs['cms_details']=array(
		$this->pi_getLL('admin_cms'),
		$tmpcontent
	);
	$tmpcontent='';
	// tabs
	$content.='<script type="text/javascript">
	jQuery(document).ready(function($) {
	 	var url_relatives = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax_product_relatives').'";
		jQuery(".tab_content").hide();
		jQuery("ul.tabs li:first").addClass("active").show();
		jQuery(".tab_content:first").show();
		jQuery("ul.tabs li").click(function() {
			jQuery("ul.tabs li").removeClass("active");
			jQuery(this).addClass("active");
			jQuery(".tab_content").hide();
			var activeTab = jQuery(this).find("a").attr("href");
			jQuery(activeTab).show();
			return false;
		});
		jQuery("#load").hide();
		jQuery().ajaxStart(function() {
			jQuery("#load").show();
			jQuery("#has").hide();
		}).ajaxStop(function() {
			jQuery("#load").hide();
			jQuery("#has").show();

		});
		$(\'#selected_type\').select2({
			width:\'650px\',
			formatResult: function(item) {
				return item.text;
			},
			formatSelection: function(item) {
				return $(item.element).data("title");
			},
			escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
		});
	});
	</script>
	<div id="tab-container">
	    <ul class="tabs">';
	foreach ($tabs as $key=>$value) {
		$count++;
		$content.='<li'.(($count==1) ? ' class="active"' : '').'><a href="#'.$key.'">'.$value[0].'</a></li>';
	}
	$content.='</ul>
	    <div class="tab_container">
	<form class="admin_cms_edit" name="admin_categories_edit_'.$cms['id'].'" id="admin_categories_edit_'.$cms['id'].'" method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax').'" enctype="multipart/form-data">
	<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$subpartArray['###VALUE_REFERRER###'].'" >
	';
	$count=0;
	foreach ($tabs as $key=>$value) {
		$count++;
		$content.='<div style="display: block;" id="'.$key.'" class="tab_content">
				'.$value[1].'
	        </div>';
	}
	$content.=$save_block.'<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
		<input name="cms_id" type="hidden" value="'.$_REQUEST['cms_id'].'" />
	</form>
	</div>
	</div>';
	// tabs eof
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(document).on("click", ".tooltipMarker", function(e){ e.preventDefault(); });
		$(".tooltipMarker").tooltip({
			position: "bottom",
			onBeforeShow: function() {
				var html=$("#msadminMarkersBox .valueField").html();
				this.getTip().html("<h1>'.$this->pi_getLL('marker').'</h1>"+html);
			}
		});
	});
</script>
';
?>