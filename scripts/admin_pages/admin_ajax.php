<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jquery.blockUI.js"></script>
<link href="'.$this->FULL_HTTP_URL_MS.'js/jqui/css/smoothness/jquery-ui-1.8.custom.css" rel="stylesheet" type="text/css"/>
<link type="text/css" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jqui/css/smoothness/jquery-ui-1.8.custom.css" rel="stylesheet" />
<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jquery.h5validate.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/valums-file-uploader/client/fileuploader.js" type="text/javascript"></script>
<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/multiselect/js/ui.multiselect_normal.js"></script>
<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jquery.timepicker/jquery-ui-sliderAccess.js"></script>
<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jquery.timepicker/jquery-ui-timepicker-addon.js"></script>
<link href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jquery.timepicker/jquery-ui-timepicker-addon.css" rel="stylesheet" type="text/css"/>
<link href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/multiselect/css/ui.multiselect.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript">
	jQuery(function($){
		$(".multiselect").multiselect();
	});
</script>
<link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/css/style.css">
<link rel="stylesheet" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.css" />
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/table.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/fontcolor.js"></script>
<script type="text/javascript">
$(function() {
	$(\'.mceEditor\').redactor({
		focus: false,
		clipboardUploadUrl: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=clipboardUploadUrl').'\',
		imageUpload: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=imageUpload').'\',
		fileUpload: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=fileUpload').'\',
		imageGetJson: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=imageGetJson').'\',
		minHeight:\'400\',
		plugins: [\'table\',\'fontcolor\']
	});
});
</script>
<script type="text/javascript">
var browser_width;
var browser_height;
jQuery().ready(function($){
	browser_width=$(document).width();
	browser_height=$(document).height();
	$(document).on("click", ".toggle_advanced_options", function(){
		var value=$(this).val();
		if (value==\''.addslashes($this->pi_getLL('admin_show_options')).'\')
		{
			$.cookie("hide_advanced_options", "0", { expires: 7, path: \'/\', domain: \''.$this->server['HTTP_HOST'].'\'});
			$(this).val("'.addslashes($this->pi_getLL('admin_hide_options')).'");
			$(".toggle_advanced_option").show();
		}
		else
		{
			$.cookie("hide_advanced_options", "1", { expires: 7, path: \'/\', domain: \''.$this->server['HTTP_HOST'].'\'});
			$(this).val("'.addslashes($this->pi_getLL('admin_show_options')).'");
			$(".toggle_advanced_option").hide();
		}
	});
});
</script>
<link rel="stylesheet" type="text/css" href="'.$this->FULL_HTTP_URL_MS.'templates/global/css/print.css" media="print" />
';
/*
<script type="text/javascript" src="'.$this->FULL_HTTP_URL_MS.'js/jquery.textarea-expander.js"></script>
*/
if (strstr($this->conf['admin_template_folder'], "/")) {
	$prefixed_url=$this->FULL_HTTP_URL;
} else {
	$prefixed_url=$this->FULL_HTTP_URL_MS.'templates/';
}
if ($this->conf['highslide_folder']=='highslide') {
	// this shop uses highslide with black outlines. lets include black tab css to change the css
	$GLOBALS['TSFE']->additionalHeaderData[]='<link rel="stylesheet" type="text/css" href="'.$this->FULL_HTTP_URL_MS.'templates/global/css/tab_black.css" media="screen" />';
}
$GLOBALS['TSFE']->additionalHeaderData[]=mslib_fe::jQueryBlockUI();
$content.='
<div id="tx_multishop_pi1_core" class="msAdminHighslidePopup">
';
switch ($_REQUEST['action']) {
	case 'edit_customer_group':
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_customer_group.php');
		break;
	case 'add_product':
	case 'edit_product':
		if (strstr($this->ms['MODULES']['ADMIN_PRODUCTS_EDIT_TYPE'], "..")) {
			die('error in ADMIN_PRODUCTS_EDIT_TYPE value');
		} else {
			if (strstr($this->ms['MODULES']['ADMIN_PRODUCTS_EDIT_TYPE'], "/")) {
				// relative mode
				require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_PRODUCTS_EDIT_TYPE'].'.php');
			} else {
				require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_product.php');
			}
		}
		break;
	case 'delete_product':
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_delete_product.php');
		break;
	case 'add_category':
	case 'edit_category':
		if (strstr($this->ms['MODULES']['ADMIN_CATEGORIES_EDIT_TYPE'], "..")) {
			die('error in ADMIN_CATEGORIES_EDIT_TYPE value');
		} else {
			if (strstr($this->ms['MODULES']['ADMIN_CATEGORIES_EDIT_TYPE'], "/")) {
				// relative mode
				require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_CATEGORIES_EDIT_TYPE'].'.php');
			} else {
				require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_category.php');
			}
		}
		break;
	case 'add_multiple_category':
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_add_multiple_categories.php');
		break;
	case 'delete_category':
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_delete_category.php');
		break;
	case 'mail_order':
		if ($this->post['orders_id'] and $this->post['tx_multishop_pi1']['email']) {
			mslib_fe::mailOrder($this->post['orders_id'], 1, $this->post['tx_multishop_pi1']['email']);
			/*
			$content.='
			<script type="text/javascript">
			parent.window.location.reload();
			</script>
		';
			*/
		} else {
			if ($this->get['orders_id']) {
				$order=mslib_fe::getOrder($this->get['orders_id']);
				if ($order['orders_id']) {
					$content.='
			<div id="mini-form-field">
				<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=mail_order').'">
					<div class="mini-account-field">
						<label>E-mail address</label>
						<input name="tx_multishop_pi1[email]" type="text" value="'.htmlspecialchars($order['billing_email']).'" />
						<input name="Submit" class="msadmin_button" type="submit" value="send e-mail" />
						<input name="orders_id" type="hidden" value="'.$order['orders_id'].'" />
					</div>
				</form>
			</div>
			';
				}
			}
		}
		break;
	case 'edit_order':
		if (isset($_GET['print'])) {
			if (strstr($this->ms['MODULES']['ADMIN_EDIT_ORDER_PRINT_TYPE'], "..")) {
				die('error in ADMIN_EDIT_ORDER_PRINT_TYPE value');
			} else {
				if (strstr($this->ms['MODULES']['ADMIN_EDIT_ORDER_PRINT_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_EDIT_ORDER_PRINT_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_order_print.php');
				}
			}
		} else {
			if (strstr($this->ms['MODULES']['ADMIN_EDIT_ORDER_TYPE'], "..")) {
				die('error in ADMIN_EDIT_ORDER_TYPE value');
			} else {
				if (strstr($this->ms['MODULES']['ADMIN_EDIT_ORDER_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_EDIT_ORDER_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_order.php');
				}
			}
		}
		break;
	case 'edit_module':
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_module.php');
		break;
	case 'edit_cms':
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_cms.php');
		break;
	case 'add_manufacturer':
	case 'edit_manufacturer':
		if (strstr($this->ms['MODULES']['ADMIN_MANUFACTURERS_EDIT_TYPE'], "..")) {
			die('error in ADMIN_MANUFACTURERS_EDIT_TYPE value');
		} else {
			if (strstr($this->ms['MODULES']['ADMIN_MANUFACTURERS_EDIT_TYPE'], "/")) {
				// relative mode
				require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_MANUFACTURERS_EDIT_TYPE'].'.php');
			} else {
				require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_manufacturer.php');
			}
		}
		break;
	case 'add_customer':
	case 'edit_customer':
		if (strstr($this->ms['MODULES']['ADMIN_CUSTOMERS_EDIT_TYPE'], "..")) {
			die('error in ADMIN_CUSTOMERS_EDIT_TYPE value');
		} else {
			if (strstr($this->ms['MODULES']['ADMIN_CUSTOMERS_EDIT_TYPE'], "/")) {
				// relative mode
				require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ADMIN_CUSTOMERS_EDIT_TYPE'].'.php');
			} else {
				require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_edit_customer.php');
			}
		}
		break;
	case 'custom_page':
		// custom page hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_ajax.php']['customAdminAjaxPage'])) {
			$params=array(
				'status'=>$status,
				'table'=>$table,
				'id'=>$id,
				'content'=>&$content
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_ajax.php']['customAdminAjaxPage'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom page hook that can be controlled by third-party plugin eof
		break;
}
$content.='</div>';
?>