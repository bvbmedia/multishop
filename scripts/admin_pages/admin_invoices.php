<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='';
switch ($this->get['tx_multishop_pi1']['action']) {
	case 'mail_invoices':
		// send invoices by mail
		$erno=array();
		if ($this->get['tx_multishop_pi1']['mailto'] and is_array($this->get['selected_invoices']) and count($this->get['selected_invoices'])) {
			$attachments=array();
			foreach ($this->get['selected_invoices'] as $invoice) {
				if (is_numeric($invoice)) {
					$invoice=mslib_fe::getInvoice($invoice, 'id');
					if ($invoice['id']) {
						// invoice as attachment
						$invoice_path=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$invoice['invoice_id'].'.pdf';
						$invoice_data=mslib_fe::file_get_contents($this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']));
						// write temporary to disk
						file_put_contents($invoice_path, $invoice_data);
						$attachments[]=$invoice_path;
					} else {
						$erno[]='Cannot retrieve invoice: '.$invoice;
					}
				}
			}
			if (count($attachments)) {
				// send mail
				$user=array();
				$user['name']=$this->ms['MODULES']['STORE_NAME'];
				$user['email']=$this->get['tx_multishop_pi1']['mailto'];
				mslib_fe::mailUser($user, $this->ms['MODULES']['STORE_NAME'].' invoices', $this->ms['MODULES']['STORE_NAME'].' invoices', $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME'], $attachments);
				// delete temporary invoice from disk
				foreach ($attachments as $attachment) {
					unlink($attachment);
				}
			}
		}
		break;
	case 'create_reversal_invoice':
		if (is_array($this->get['selected_invoices']) and count($this->get['selected_invoices'])) {
			foreach ($this->get['selected_invoices'] as $invoice) {
				if (is_numeric($invoice)) {
					$invoice=mslib_fe::getInvoice($invoice, 'id');
					if ($invoice['id'] and $invoice['reversal_invoice']==0) {
						mslib_fe::generateReversalInvoice($invoice['id']);
					}
				}
			}
		}
		break;
	case 'update_selected_invoices_to_paid':
	case 'update_selected_invoices_to_not_paid':
		if (is_array($this->get['selected_invoices']) and count($this->get['selected_invoices'])) {
			foreach ($this->get['selected_invoices'] as $invoice) {
				if (is_numeric($invoice)) {
					$invoice=mslib_fe::getInvoice($invoice, 'id');
					if ($invoice['id']) {
						$order=mslib_fe::getOrder($invoice['orders_id']);
						if ($order['orders_id']) {
							if ($this->get['tx_multishop_pi1']['action']=='update_selected_invoices_to_paid') {
								mslib_fe::updateOrderStatusToPaid($order['orders_id']);
							} else {
								$updateArray=array('paid'=>0);
								$updateArray['orders_last_modified']=time();
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id='.$order['orders_id'], $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								$updateArray=array('paid'=>0);
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id='.$invoice['id'], $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							}
						} else {
							// this invoice has no belonging order. This could be true in specific cases so just update the invoice to not paid.
							if ($this->get['tx_multishop_pi1']['action']=='update_selected_invoices_to_paid') {
								$updateArray=array('paid'=>1);
							} else {
								$updateArray=array('paid'=>0);
							}
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id='.$invoice['id'], $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					}
				}
			}
		}
		break;
	default:
		// post processing by third party plugins
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_invoices.php']['adminInvoicesPostHookProc'])) {
			$params=array();
			$params['content']=&$content;
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_invoices.php']['adminInvoicesPostHookProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		break;
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_invoices_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_invoices_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/admin_invoices.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['invoices_results']=$this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['invoices_listing']=$this->cObj->getSubpart($subparts['invoices_results'], '###INVOICES_LISTING###');
$subparts['invoices_noresults']=$this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
//
if ($this->get['Search'] and ($this->get['paid_invoices_only']!=$this->cookie['paid_invoices_only'])) {
	$this->cookie['paid_invoices_only']=$this->get['paid_invoices_only'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->get['Search'] and ($this->get['limit']!=$this->cookie['limit'])) {
	$this->cookie['limit']=$this->get['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
	$this->get['limit']=$this->cookie['limit'];
} else {
	$this->get['limit']=15;
}
$this->ms['MODULES']['ORDERS_LISTING_LIMIT']=$this->get['limit'];
$option_search=array(
	"orders_id"=>$this->pi_getLL('admin_order_id'),
	"invoice"=>$this->pi_getLL('admin_invoice_number'),
	"customer_id"=>$this->pi_getLL('admin_customer_id'),
	"billing_email"=>$this->pi_getLL('admin_customer_email'),
	"delivery_name"=>$this->pi_getLL('admin_customer_name'),
	//"crdate"=>$this->pi_getLL('admin_order_date'),
	"billing_zip"=>$this->pi_getLL('admin_zip'),
	"billing_city"=>$this->pi_getLL('admin_city'),
	"billing_address"=>$this->pi_getLL('admin_address'),
	"billing_company"=>$this->pi_getLL('admin_company'),
	"shipping_method"=>$this->pi_getLL('admin_shipping_method')
	//"payment_method"=>$this->pi_getLL('admin_payment_method')
);
asort($option_search);
$type_search=$this->get['type_search'];
if ($_REQUEST['skeyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['skeyword']=$_REQUEST['skeyword'];
	$this->get['skeyword']=trim($this->get['skeyword']);
	$this->get['skeyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['skeyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['skeyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['skeyword'], true);
	$this->get['skeyword']=mslib_fe::RemoveXSS($this->get['skeyword']);
}
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($p>0) {
	$offset=(((($p)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])));
} else {
	$p=0;
	$offset=0;
}
// orders search
$option_item='<select name="type_search" class="invoice_select2"><option value="all">'.$this->pi_getLL('all').'</option>';
foreach ($option_search as $key=>$val) {
	$option_item.='<option value="'.$key.'" '.($this->get['type_search']==$key ? "selected" : "").'>'.$val.'</option>';
}
$option_item.='</select>';
//
$all_orders_status=mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
$orders_status_list='<select name="orders_status_search" class="invoice_select2"><option value="0" '.((!$order_status_search_selected) ? 'selected' : '').'>'.$this->pi_getLL('all_orders_status', 'All orders status').'</option>';
if (is_array($all_orders_status)) {
	$order_status_search_selected=false;
	foreach ($all_orders_status as $row) {
		$orders_status_list.='<option value="'.$row['id'].'" '.(($this->get['orders_status_search']==$row['id']) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
		if ($this->get['orders_status_search']==$row['id']) {
			$order_status_search_selected=true;
		}
	}
}
$orders_status_list.='</select>';
$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input='';
$customer_groups_input.='<select id="groups" class="invoice_select2" name="usergroup">'."\n";
$customer_groups_input.='<option value="0">'.$this->pi_getLL('all').' '.$this->pi_getLL('usergroup').'</option>'."\n";
if (is_array($groups) and count($groups)) {
	foreach ($groups as $group) {
		$customer_groups_input.='<option value="'.$group['uid'].'"'.($this->get['usergroup']==$group['uid'] ? ' selected="selected"' : '').'>'.$group['title'].'</option>'."\n";
	}
}
$customer_groups_input.='</select>'."\n";
// payment method
$payment_methods=array();
$sql=$GLOBALS['TYPO3_DB']->SELECTquery('payment_method, payment_method_label', // SELECT ...
	'tx_multishop_orders', // FROM ...
	'', // WHERE...
	'payment_method', // GROUP BY...
	'payment_method_label', // ORDER BY...
	'' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	if (empty($row['payment_method_label'])) {
		$row['payment_method']='nopm';
		$row['payment_method_label']='Empty payment method';
	}
	$payment_methods[$row['payment_method']]=$row['payment_method_label'].($row['payment_method']!='nopm' ? ' (code: '.$row['payment_method'].')' : '');
}
$payment_method_input='';
$payment_method_input.='<select id="payment_method" class="invoice_select2" name="payment_method">'."\n";
$payment_method_input.='<option value="all">'.$this->pi_getLL('all_payment_methods').'</option>'."\n";
if (is_array($payment_methods) and count($payment_methods)) {
	foreach ($payment_methods as $payment_method_code=>$payment_method) {
		$payment_method_input.='<option value="'.$payment_method_code.'"'.($this->get['payment_method']==$payment_method_code ? ' selected="selected"' : '').'>'.$payment_method.'</option>'."\n";
	}
}
$payment_method_input.='</select>'."\n";
// shipping method
$shipping_methods=array();
$sql=$GLOBALS['TYPO3_DB']->SELECTquery('shipping_method, shipping_method_label', // SELECT ...
	'tx_multishop_orders', // FROM ...
	'', // WHERE...
	'shipping_method', // GROUP BY...
	'shipping_method_label', // ORDER BY...
	'' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	if (empty($row['shipping_method_label'])) {
		$row['shipping_method']='nosm';
		$row['shipping_method_label']='Empty shipping method';
	}
	$shipping_methods[$row['shipping_method']]=$row['shipping_method_label'].($row['shipping_method']!='nosm' ? ' (code: '.$row['shipping_method'].')' : '');
}
$shipping_method_input='';
$shipping_method_input.='<select id="shipping_method" class="invoice_select2" name="shipping_method">'."\n";
$shipping_method_input.='<option value="all">'.$this->pi_getLL('all_shipping_methods').'</option>'."\n";
if (is_array($shipping_methods) and count($shipping_methods)) {
	foreach ($shipping_methods as $shipping_method_code=>$shipping_method) {
		$shipping_method_input.='<option value="'.$shipping_method_code.'"'.($this->get['shipping_method']==$shipping_method_code ? ' selected="selected"' : '').'>'.$shipping_method.'</option>'."\n";
	}
}
$shipping_method_input.='</select>'."\n";
// billing countries
$order_countries=mslib_befe::getRecords('', 'tx_multishop_orders', '', array(), 'billing_country', 'billing_country asc');
$order_billing_country=array();
foreach ($order_countries as $order_country) {
	$cn_localized_name=htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order_country['billing_country']));
	$order_billing_country[]='<option value="'.mslib_befe::strtolower($order_country['billing_country']).'" '.((mslib_befe::strtolower($this->post['country'])==strtolower($order_country['billing_country'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
}
ksort($order_billing_country);
$billing_countries_sb='<select class="invoice_select2" name="country" id="country""><option value="">'.$this->pi_getLL('all_countries').'</option>'.implode("\n", $order_billing_country).'</select>';
$limit_selectbox='<select name="limit" class="form-control">';
$limits=array();
$limits[]='15';
$limits[]='20';
$limits[]='25';
$limits[]='30';
$limits[]='40';
$limits[]='50';
$limits[]='100';
$limits[]='150';
foreach ($limits as $limit) {
	$limit_selectbox.='<option value="'.$limit.'"'.($limit==$this->get['limit'] ? ' selected' : '').'>'.$limit.'</option>';
}
$limit_selectbox.='</select>';
$filter=array();
$from=array();
$having=array();
$match=array();
$orderby=array();
$where=array();
$orderby=array();
$select=array();
if ($this->get['skeyword']) {
	switch ($type_search) {
		case 'all':
			$option_fields=$option_search;
			unset($option_fields['all']);
			unset($option_fields['invoice']);
			unset($option_fields['crdate']);
			unset($option_fields['delivery_name']);
			//print_r($option_fields);
			$items=array();
			foreach ($option_fields as $fields=>$label) {
				$items[]='o.'.$fields." LIKE '%".addslashes($this->get['skeyword'])."%'";
			}
			$items[]="o.delivery_name LIKE '%".addslashes($this->get['skeyword'])."%'";
			$items[]="i.invoice_id LIKE '%".addslashes($this->get['skeyword'])."%'";
			$filter[]=implode(" or ", $items);
			break;
		case 'orders_id':
			$filter[]=" o.orders_id='".addslashes($this->get['skeyword'])."'";
			break;
		case 'invoice':
			$filter[]=" i.invoice_id LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'billing_email':
			$filter[]=" o.billing_email LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'delivery_name':
			$filter[]=" o.delivery_name LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'billing_zip':
			$filter[]=" o.billing_zip LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'billing_city':
			$filter[]=" o.billing_city LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		/*case 'billing_country':
			$filter[]=" o.billing_country LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;*/
		case 'billing_address':
			$filter[]=" o.billing_address LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'billing_company':
			$filter[]=" o.billing_company LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		/*case 'shipping_method':
			$filter[]=" (o.shipping_method LIKE '%".addslashes($this->get['skeyword'])."%' or o.shipping_method_label LIKE '%".addslashes($this->get['skeyword'])."%')";
			break;
		case 'payment_method':
			$filter[]=" (o.payment_method LIKE '%".addslashes($this->get['skeyword'])."%' or o.payment_method_label LIKE '%".addslashes($this->get['skeyword'])."%')";
			break;*/
		case 'customer_id':
			$filter[]=" o.customer_id LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		/*case 'crdate':
			$start_time=date("Y-m-d", strtotime($this->get['skeyword']))." 00:00:00";
			$till_time=date("Y-m-d", strtotime($this->get['skeyword']))." 23:59:59";
			$filter[]=" crdate BETWEEN '".addslashes($start_time)."' and '".addslashes($till_time)."'";
			$ors[]=" ($type_search >= $date_search) ";
			break;*/
	}
}
if (!empty($this->get['invoice_date_from']) && !empty($this->get['invoice_date_till'])) {
	list($from_date, $from_time)=explode(" ", $this->get['invoice_date_from']);
	list($fd, $fm, $fy)=explode('/', $from_date);
	list($till_date, $till_time)=explode(" ", $this->get['invoice_date_till']);
	list($td, $tm, $ty)=explode('/', $till_date);
	$start_time=strtotime($fy.'-'.$fm.'-'.$fd.' '.$from_time);
	$end_time=strtotime($ty.'-'.$tm.'-'.$td.' '.$till_time);
	$column='i.crdate';
	$filter[]=$column." BETWEEN '".$start_time."' and '".$end_time."'";
}
if (isset($this->get['usergroup']) && $this->get['usergroup']>0) {
	$filter[]=' i.customer_id IN (SELECT uid from fe_users where '.$GLOBALS['TYPO3_DB']->listQuery('usergroup', $this->get['usergroup'], 'fe_users').')';
}
if ($this->get['orders_status_search']>0) {
	$filter[]="(o.status='".$this->get['orders_status_search']."')";
}
if (isset($this->get['payment_method']) && $this->get['payment_method']!='all') {
	if ($this->get['payment_method']=='nopm') {
		$filter[]="(o.payment_method is null)";
	} else {
		$filter[]="(o.payment_method='".$this->get['payment_method']."')";
	}
}
if (isset($this->get['shipping_method']) && $this->get['shipping_method']!='all') {
	if ($this->get['shipping_method']=='nosm') {
		$filter[]="(o.shipping_method is null)";
	} else {
		$filter[]="(o.shipping_method='".$this->get['shipping_method']."')";
	}
}
if ($this->cookie['paid_invoices_only']) {
	$filter[]="(i.paid='1')";
}
if (isset($this->get['country']) && !empty($this->get['country'])) {
	$filter[]="o.billing_country='".$this->get['country']."'";
}
if (!$this->masterShop) {
	$filter[]='i.page_uid='.$this->showCatalogFromPage;
}
//$orderby[]='orders_id desc';
$select[]='*, i.hash';
$orderby[]='i.id desc';
$pageset=mslib_fe::getInvoicesPageSet($filter, $offset, $this->get['limit'], $orderby, $having, $select, $where, $from);
$invoices=$pageset['invoices'];
if ($pageset['total_rows']>0) {
	$this->ms['MODULES']['PAGESET_LIMIT']=$this->ms['MODULES']['ORDERS_LISTING_LIMIT'];
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/invoices/invoices_listing_table.php');
} else {
	$subpartArray=array();
	$subpartArray['###LABEL_NO_RESULTS###']=$this->pi_getLL('no_invoices_found').'.';
	$no_results=$this->cObj->substituteMarkerArrayCached($subparts['invoices_noresults'], array(), $subpartArray);
}
//
$subpartArray=array();
$subpartArray['###PAGE_ID###']=$this->showCatalogFromPage;
$subpartArray['###SHOP_PID###']=$this->shop_pid;
$subpartArray['###LABEL_KEYWORD###']=ucfirst($this->pi_getLL('keyword'));
$subpartArray['###VALUE_KEYWORD###']=($this->get['skeyword'] ? $this->get['skeyword'] : "");
$subpartArray['###LABEL_SEARCH_ON###']=$this->pi_getLL('search_for');
$subpartArray['###OPTION_ITEM_SELECTBOX###']=$option_item;
$subpartArray['###LABEL_USERGROUP###']=$this->pi_getLL('usergroup');
$subpartArray['###USERGROUP_SELECTBOX###']=$customer_groups_input;
$subpartArray['###LABEL_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
$subpartArray['###PAYMENT_METHOD_SELECTBOX###']=$payment_method_input;
$subpartArray['###LABEL_SHIPPING_METHOD###']=$this->pi_getLL('shipping_method');
$subpartArray['###SHIPPING_METHOD_SELECTBOX###']=$shipping_method_input;
$subpartArray['###LABEL_ORDER_STATUS###']=$this->pi_getLL('order_status');
$subpartArray['###INVOICES_STATUS_LIST_SELECTBOX###']=$orders_status_list;
$subpartArray['###VALUE_SEARCH###']=htmlspecialchars($this->pi_getLL('search'));
$subpartArray['###LABEL_FILTER_BY_DATE###']=$this->pi_getLL('filter_by_date');
$subpartArray['###LABEL_DATE_FROM###']=$this->pi_getLL('from');
$subpartArray['###LABEL_DATE###']=$this->pi_getLL('date');
$subpartArray['###VALUE_DATE_FROM###']=$this->get['invoice_date_from'];
$subpartArray['###LABEL_DATE_TO###']=$this->pi_getLL('to');
$subpartArray['###VALUE_DATE_TO###']=$this->get['invoice_date_till'];
$subpartArray['###LABEL_FILTER_BY_PAID_INVOICES_ONLY###']=$this->pi_getLL('show_paid_invoices_only');
$subpartArray['###FILTER_BY_PAID_INVOICES_ONLY_CHECKED###']=($this->cookie['paid_invoices_only'] ? ' checked' : '');
$subpartArray['###LABEL_RESULTS_LIMIT_SELECTBOX###']=$this->pi_getLL('limit_number_of_records_to');
$subpartArray['###RESULTS_LIMIT_SELECTBOX###']=$limit_selectbox;
$subpartArray['###RESULTS###']=$invoices_results;
$subpartArray['###NORESULTS###']=$no_results;
$subpartArray['###ADMIN_LABEL_TABS_INVOICES###']=$this->pi_getLL('admin_invoices');
$subpartArray['###LABEL_COUNTRIES_SELECTBOX###']=$this->pi_getLL('countries');
$subpartArray['###COUNTRIES_SELECTBOX###']=$billing_countries_sb;
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
//
$content.='<hr><div class="clearfix"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
$GLOBALS['TSFE']->additionalHeaderData[]='
<script>
	jQuery(document).ready(function($) {
		'.($this->get['tx_multishop_pi1']['action']!='mail_invoices' ? '$("#msadmin_invoices_mailto").hide();' : '').'
	});
</script>
';
?>