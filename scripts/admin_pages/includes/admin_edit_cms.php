<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
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
	echo $this->pi_getLL('content_saved').'.';
	echo '<script type="text/javascript">
	parent.window.location.reload();
	</script>';
	exit();
}
if ($cms['id'] or $_REQUEST['action']=='edit_cms') {
	$save_block='
		<div class="save_block">
			<input name="cancel" type="button" value="'.htmlspecialchars($this->pi_getLL('cancel')).'" onClick="parent.window.hs.close();" class="submit" />
			<input name="Submit" type="submit" value="'.htmlspecialchars($this->pi_getLL('save')).'" class="submit" />
		</div>';
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
			<select name="tx_multishop_pi1[type]" id="selected_type"><option value="">'.htmlspecialchars($this->pi_getLL('choose_type_of_content')).'</option>';
	asort($types);
	foreach ($types as $key=>$value) {
		$tmpcontent.='<option value="'.$key.'" '.(($cms[0]['type']==$key) ? 'selected' : '').'>'.htmlspecialchars($value).'</option>'."\n";
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
			jQuery(document).ready(function($){
				updateForm();								
			});
		</script>
		<div class="account-field">
			<label>Dynamic markers</label>
			<div class="valueField">
				<ul>';
	$markers=array();
	$markers['DELIVERY_FIRST_NAME']='First name (delivery)';
	$markers['DELIVERY_LAST_NAME']='Last name (delivery)';
	$markers['BILLING_FIRST_NAME']='First name (billing)';
	$markers['BILLING_LAST_NAME']='Last name (billing)';
	$markers['BILLING_TELEPHONE']='Telephone (billing)';
	$markers['DELIVERY_TELEPHONE']='Telephone (delivery)';
	$markers['BILLING_MOBILE']='Mobile (billing)';
	$markers['DELIVERY_MOBILE']='Mobile (delivery)';
	$markers['FULL_NAME']='Full name (billing)';
	$markers['DELIVERY_FULL_NAME']='Full name (delivery)';
	$markers['CUSTOMER_EMAIL']='Customer email address';
	$markers['ORDER_DATE_LONG']='Order date in long format (date of purchase)';
	$markers['CURRENT_DATE_LONG']='Current date in long format (date of sending the message)';
	$markers['STORE_NAME']='Store name';
	$markers['TOTAL_AMOUNT']='Order total amount';
	$markers['PROPOSAL_NUMBER']='Proposal number';
	$markers['ORDER_NUMBER']='Order number (orders id)';
	$markers['BILLING_ADDRESS']='Billing address';
	$markers['BILLING_COMPANY']='Billing company';
	$markers['DELIVERY_COMPANY']='Delivery company';
	$markers['DELIVERY_ADDRESS']='Delivery address';
	$markers['CUSTOMER_ID']='Customer id';
	$markers['SHIPPING_METHOD']='Shipping method';
	$markers['PAYMENT_METHOD']='Payment method';
	$markers['ORDER_DETAILS']='Order details';
	if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
		$markers['INVOICE_LINK']='Invoice link';
		$markers['INVOICE_NUMBER']='Invoice number';
	}
	$markers['BILLING_NAME']='Name (billing)';
	$markers['BILLING_EMAIL']='Customer email address (billing)';
	$markers['DELIVERY_EMAIL']='Customer email address (delivery)';
	$markers['DELIVERY_NAME']='Name (delivery)';
	$markers['CUSTOMER_COMMENTS']='Customer comments (used by order status update letter)';
	$markers['OLD_ORDER_STATUS']='Old order status (used by order status update letter)';
	$markers['ORDER_STATUS']='New order status (used by order status update letter)';
	$markers['EXPECTED_DELIVERY_DATE']='Expected delivery date';
	$markers['TRACK_AND_TRACE_CODE']='Track and Trace code';
	$markers['CONFIRMATION_LINK']='Create account confirmation link';
	$markers['CUSTOMER_COMMENTS']='Customer comments';
	$markers['PAYMENT_PAGE_LINK']='Payment link (for payment reminder mail template)';
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
	foreach ($markers as $key=>$label) {
		$tmpcontent.='<li><span class="marker_description">'.htmlspecialchars($label).':</span><span class="marker_key">###'.$key.'###</span></li>'."\n";
	}
	$tmpcontent.='</ul>
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
	<form class="admin_cms_edit" name="admin_categories_edit_'.$cms['id'].'" id="admin_categories_edit_'.$cms['id'].'" method="post" action="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax').'" enctype="multipart/form-data">';
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
?>