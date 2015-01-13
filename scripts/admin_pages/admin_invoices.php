<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
switch ($this->get['tx_multishop_pi1']['action']) {
	case 'mail_invoices':
		// send invoices by mail
		$erno=array();
		if ($this->get['tx_multishop_pi1']['mailto'] and is_array($this->get['selected_invoices']) and count($this->get['selected_invoices'])) {
			$attachments=array();
			foreach ($this->get['selected_invoices'] as $invoice) {
				if (is_numeric($invoice)) {
					$invoice=mslib_fe::getInvoice($invoice,'id');
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
					$invoice=mslib_fe::getInvoice($invoice,'id');
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
					$invoice=mslib_fe::getInvoice($invoice,'id');
					if ($invoice['id']) {
						$order=mslib_fe::getOrder($invoice['orders_id']);
						if ($order['orders_id']) {
							if ($this->get['tx_multishop_pi1']['action']=='update_selected_invoices_to_paid') {
								mslib_fe::updateOrderStatusToPaid($order['orders_id']);
							} else {
								$updateArray=array('paid'=>0);
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
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_invoices.php']['adminInvoicesPostHookProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		break;
}
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
	$this->get['skeyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['skeyword'], TRUE);
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
foreach ($option_search as $key=>$val) {
	$option_item.='<option value="'.$key.'" '.($this->get['type_search']==$key ? "selected" : "").'>'.$val.'</option>';
}
$all_orders_status=mslib_fe::getAllOrderStatus();
$orders_status_list='<select name="orders_status_search" class="invoice_select2" style="width:200px"><option value="0" '.((!$order_status_search_selected) ? 'selected' : '').'>'.$this->pi_getLL('all_orders_status', 'All orders status').'</option>';
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
$customer_groups_input.='<select id="groups" class="invoice_select2" name="usergroup" style="width:200px">'."\n";
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
	$payment_methods[$row['payment_method']]=$row['payment_method_label'] . ($row['payment_method']!='nopm' ? ' (code: '.$row['payment_method'].')' : '');
}
$payment_method_input='';
$payment_method_input.='<select id="payment_method" class="invoice_select2" name="payment_method" style="width:200px">'."\n";
$payment_method_input.='<option value="all">'.$this->pi_getLL('all').' '.ucfirst(strtolower($this->pi_getLL('admin_payment_methods'))).'</option>'."\n";
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
	$shipping_methods[$row['shipping_method']]=$row['shipping_method_label'] . ($row['shipping_method']!='nosm' ? ' (code: '.$row['shipping_method'].')' : '');
}
$shipping_method_input='';
$shipping_method_input.='<select id="shipping_method" class="order_select2" name="shipping_method" style="width:200px">'."\n";
$shipping_method_input.='<option value="all">'.$this->pi_getLL('all').' '.ucfirst(strtolower($this->pi_getLL('admin_shipping_methods'))).'</option>'."\n";
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
$billing_countries_sb='<select class="invoice_select2" name="country" id="country""><option value="">'.$this->pi_getLL('all').' '.$this->pi_getLL('countries').'</option>'.implode("\n", $order_billing_country).'</select>';
$form_orders_search='<div id="search-orders">
	<input name="id" type="hidden" value="'.$this->showCatalogFromPage.'" />
	<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_invoices" />
	<input name="id" type="hidden" value="'.$this->shop_pid.'" />
	<input name="type" type="hidden" value="2003" />
	<div class="row formfield-container-wrapper">
		<div class="col-sm-4 formfield-wrapper">
			<label>'.ucfirst($this->pi_getLL('keyword')).'</label>
			<input type="text" name="skeyword" value="'.($this->get['skeyword'] ? $this->get['skeyword'] : "").'"></input>
			<label for="type_search">'.$this->pi_getLL('search_for').'</label>
			<select name="type_search" class="invoice_select2" style="width:200px"><option value="all">'.$this->pi_getLL('all').'</option>
				'.$option_item.'
			</select>
			<label for="groups" class="labelInbetween">'.$this->pi_getLL('usergroup').'</label>
			'.$customer_groups_input.'
		</div>
		<div class="col-sm-4 formfield-wrapper">
			<label for="order_date_from">'.$this->pi_getLL('from').':</label>
			<input type="text" name="invoice_date_from" id="invoice_date_from" value="'.$this->get['invoice_date_from'].'">
			<label for="order_date_till" class="labelInbetween">'.$this->pi_getLL('to').':</label>
			<input type="text" name="invoice_date_till" id="invoice_date_till" value="'.$this->get['invoice_date_till'].'">
			<label for="orders_status_search">'.$this->pi_getLL('order_status').'</label>
			'.$orders_status_list.'
			<label for="paid_invoices_only">'.$this->pi_getLL('show_paid_invoices_only').'</label>
			<input type="checkbox" class="PrettyInput" id="paid_invoices_only" name="paid_invoices_only"  value="1"'.($this->cookie['paid_invoices_only'] ? ' checked' : '').' >
		</div>
		<div class="col-sm-4 formfield-wrapper">
			<label for="payment_method">'.$this->pi_getLL('payment_method').'</label>
			'.$payment_method_input.'
			<label for="shipping_method" class="labelInbetween">'.$this->pi_getLL('shipping_method').'</label>
			'.$shipping_method_input.'
			<label for="country">'.$this->pi_getLL('countries').'</label>
			'.$billing_countries_sb.'
			<label>'.$this->pi_getLL('limit_number_of_records_to').':</label>
			<select name="limit">';
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
	$form_orders_search.='<option value="'.$limit.'"'.($limit==$this->get['limit'] ? ' selected' : '').'>'.$limit.'</option>';
}
$form_orders_search.='
			</select>
		</div>
	</div>
	<div class="row formfield-container-wrapper">
		<div class="col-sm-12 formfield-wrapper">
			<input type="submit" name="Search" value="'.htmlspecialchars($this->pi_getLL('search')).'"></input>
		</div>
	</div>
</div>';
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
$listing_content='';
if ($pageset['total_rows']>0) {
	$this->ms['MODULES']['PAGESET_LIMIT']=$this->ms['MODULES']['ORDERS_LISTING_LIMIT'];
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/invoices/invoices_listing_table.php');
	// pagination
	if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['ORDERS_LISTING_LIMIT']) {
		// reassign the listing table content to $listing_content, because the pagination also use $tmp and cleared the variable before use
		$listing_content=$tmp;
		//require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/invoices/pagination.php');
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
		// concate it again
		$listing_content.=$tmp;
	}
	// pagination eof
} else {
	$tmp=$this->pi_getLL('no_invoices_found').'.';
}
if (!empty($listing_content)) {
	$tmp='';
	$tmp=$listing_content;
}
$tabs=array();
$tabs['Invoices_By_Date']=array(
	$this->pi_getLL('admin_invoices'),
	$tmp
);
$tmp='';
$content.='
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".tab_content").hide();
	$("ul.tabs li:first").addClass("active").show();
	$(".tab_content:first").show();
	$("ul.tabs li").click(function() {
		$("ul.tabs li").removeClass("active");
		$(this).addClass("active");
		$(".tab_content").hide();
		var activeTab = $(this).find("a").attr("href");
		$(activeTab).fadeIn(0);
		return false;
	});
	$("#invoice_date_from").datetimepicker({
		dateFormat: "dd/mm/yy",
		showSecond: true,
		timeFormat: "HH:mm:ss"
	});
	$("#invoice_date_till").datetimepicker({
		dateFormat: "dd/mm/yy",
		showSecond: true,
		timeFormat: "HH:mm:ss"
	});
	$(".invoice_select2").select2();
});
</script>
<div id="tab-container">
    <ul class="tabs" id="admin_invoices">
';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='<li'.(($count==1) ? ' class="active"' : '').'><a href="#'.$key.'">'.$value[0].'</a></li>';
}
$content.='
    </ul>
    <div class="tab_container">
	<form action="index.php" method="get">
	';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='
        <div style="display: block;" id="'.$key.'" class="tab_content">
        	'.$form_orders_search.'
			'.$value[1].'
        </div>
	';
}
$content.='
	</form>
    </div>
</div>
';
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".order_select2").select2();
});
</script>
';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';

?>