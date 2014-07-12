<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
switch ($this->get['tx_multishop_pi1']['action']) {
	case 'mail_invoices':
		// send invoices by mail
		if ($this->get['tx_multishop_pi1']['mailto'] and is_array($this->get['selected_invoices']) and count($this->get['selected_invoices'])) {
			$attachments=array();
			foreach ($this->get['selected_invoices'] as $invoice) {
				if (is_numeric($invoice)) {
					$invoice=mslib_fe::getInvoice($invoice, 'invoice_id');
					// invoice as attachment
					$invoice_path=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$invoice['invoice_id'].'.pdf';
					$invoice_data=mslib_fe::file_get_contents($this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']));
					// write temporary to disk
					file_put_contents($invoice_path, $invoice_data);
					$attachments[]=$invoice_path;
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
					$invoice=mslib_fe::getInvoice($invoice, 'invoice_id');
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
					$invoice=mslib_fe::getInvoice($invoice, 'invoice_id');
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
	"crdate"=>$this->pi_getLL('admin_order_date'),
	"billing_zip"=>$this->pi_getLL('admin_zip'),
	"billing_city"=>$this->pi_getLL('admin_city'),
	"billing_address"=>$this->pi_getLL('admin_address'),
	"billing_company"=>$this->pi_getLL('admin_company'),
	"shipping_method"=>$this->pi_getLL('admin_shipping_method'),
	"payment_method"=>$this->pi_getLL('admin_payment_method')
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
$form_orders_search='<div id="search-orders">
	<input name="id" type="hidden" value="'.$this->showCatalogFromPage.'" />
	<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_invoices" />
	<input name="id" type="hidden" value="'.$this->shop_pid.'" />
	<input name="type" type="hidden" value="2003" />
	<table width="100%">
		<tr>
			<td>
				<div style="float:right;">
					<label>'.$this->pi_getLL('limit_number_of_records_to').':</label>
					<select name="limit">
					';
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
				<label>'.ucfirst($this->pi_getLL('keyword')).'</label>
				<input type="text" name="skeyword" value="'.($this->get['skeyword'] ? $this->get['skeyword'] : "").'"></input>
				<select name="type_search"><option value="all">'.$this->pi_getLL('all').'</option>
				'.$option_item.'
				</select>
				<label for="paid_invoices_only">'.$this->pi_getLL('show_paid_invoices_only').'</label>
				<input type="checkbox" class="PrettyInput" id="paid_invoices_only" name="paid_invoices_only"  value="1"'.($this->cookie['paid_invoices_only'] ? ' checked' : '').' >
				<input type="submit" name="Search" value="'.htmlspecialchars($this->pi_getLL('search')).'"></input>
			</td>
		</tr>
	</table>
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
		case 'billing_address':
			$filter[]=" o.billing_address LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'billing_company':
			$filter[]=" o.billing_company LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'shipping_method':
			$filter[]=" o.shipping_method LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'payment_method':
			$filter[]=" o.payment_method LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'customer_id':
			$filter[]=" o.customer_id LIKE '%".addslashes($this->get['skeyword'])."%'";
			break;
		case 'crdate':
			$start_time=date("Y-m-d", strtotime($this->get['skeyword']))." 00:00:00";
			$till_time=date("Y-m-d", strtotime($this->get['skeyword']))." 23:59:59";
			$filter[]=" crdate BETWEEN '".addslashes($start_time)."' and '".addslashes($till_time)."'";
			$ors[]=" ($type_search >= $date_search) ";
			break;
	}
}
if ($this->cookie['paid_invoices_only']) {
	$filter[]="(i.paid='1')";
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
	'.$form_orders_search;
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='
        <div style="display: block;" id="'.$key.'" class="tab_content">
			'.$value[1].'
        </div>
	';
}
$content.='
	</form>
    </div>
</div>

';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>