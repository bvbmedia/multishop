<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$headerData='';
$headerData .= '
<script type="text/javascript">
function submitToHighslide(form) {
	// identify the submit button to start the animation from
	var anchor;
	for (var i = 0; i < form.elements.length; i++) {
		if (form.elements[i].type == "submit") {
			anchor = form.elements[i];
			break;
		}
	}
	// open an expander and submit our form when the iframe is ready
	hs.overrides.push("onAfterExpand");
	hs.htmlExpand(anchor, {
         objectType: "iframe",
         src: "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&action=edit_order').'",
         width: 380,
         height: 90,
         onAfterExpand: function(expander) {
            form.target = expander.iframe.name;
            form.submit();
         }
	});
	// return false to delay the sumbit until the iframe is ready
	return false;
}
</script>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(\'.change_orders_status\').change(function(){
			var orders_id=$(this).attr("rel");
			var orders_status_id=$("option:selected", this).val();
			var orders_status_label=$("option:selected", this).text();
			if (confirm("Do you want to change orders id: "+orders_id+" to status: "+orders_status_label))
			{
				$.ajax({ 
						type:   "POST", 
						url:    "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_update_orders_status').'",
						dataType: \'json\',
						data:   "tx_multishop_pi1[orders_id]="+orders_id+"&tx_multishop_pi1[orders_status_id]="+orders_status_id, 
						success: function(msg) {
						}
				});
			}
		});
		
		$(\'#selected_orders_action\').change(function(){
			if ($(this).val()==\'change_order_status_for_selected_orders\')
			{
				$("#msadmin_order_status_select").show();
			}
			else
			{
				$("#msadmin_order_status_select").hide();
			}';
								

// extra input jquery
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputJQueryProc'])) {
	$params = array('tmp' => &$headerData);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputJQueryProc'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}

$headerData .=		'});
		'.($this->get['tx_multishop_pi1']['action']!='change_order_status_for_selected_orders'?'$("#msadmin_order_status_select").hide();':'').'
		$(".tooltip").tooltip({position: "bottom",
			onBeforeShow: function() {
				var that=this;
				var orders_id=this.getTrigger().attr(\'rel\');
				$.ajax({ 
					type:   "POST", 
					url:    \''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=getAdminOrdersListingDetails').'\', 
					data:   \'tx_multishop_pi1[orders_id]=\'+orders_id, 
					dataType: "json",
					success: function(data) { 
						that.getTip().html(data.html);
					} 
				}); 				
			
			}
		});
		$(\'#check_all_1\').click(function(){			
			checkAllPrettyCheckboxes(this,$(\'.msadmin_orders_listing\'));
		});	
	});	
</script>';
$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
$headerData='';


$filter		=array();
$from		=array();
$having		=array();
$match		=array();
$orderby	=array();
$where		=array();
$orderby	=array();
$select		=array();
if ($this->post['skeyword']) {
	switch ($type_search) {
		case 'all':
			$option_fields = $option_search;
			unset($option_fields['all']);
			unset($option_fields['invoice']);
			unset($option_fields['crdate']);
			unset($option_fields['delivery_name']);
			//print_r($option_fields);
			$items=array();
			foreach ($option_fields as $fields=>$label) {
				 $items[] = $fields." LIKE '%".addslashes($this->post['skeyword'])."%'";
			}
			$items[] = "delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
			$filter[]=implode(" or ",$items);
		break;
		case 'orders_id':
			$filter[] =" orders_id='".addslashes($this->post['skeyword'])."'";
		break;
		case 'invoice':
			$filter[] =" invoice_id LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;
		case 'billing_email':
			$filter[] =" billing_email LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;	
		case 'delivery_name':
			$filter[] =" delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_zip':
			$filter[] =" billing_zip LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_city':
			$filter[] =" billing_city LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_address':
			$filter[] =" billing_address LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_company':
			$filter[] =" billing_company LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'shipping_method':
			$filter[] =" shipping_method LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;	
		case 'payment_method':
			$filter[] =" payment_method LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;	
		case 'customer_id':
			$filter[] =" customer_id='".addslashes($this->post['skeyword'])."'";
		break;
	}
}


if (!empty($this->post['order_date_from']) && !empty($this->post['order_date_till'])) {
	list($from_date, $from_time) = explode(" ", $this->post['order_date_from']);
	list($fd, $fm, $fy) = explode('/', $from_date);
	
	list($till_date, $till_time) = explode(" ", $this->post['order_date_till']);
	list($td, $tm, $ty) = explode('/', $till_date);
	
	$start_time 	= strtotime($fy . '-' . $fm . '-' . $fd .' ' . $from_time);
	$end_time 		= strtotime($ty . '-' . $tm . '-' . $td .' ' . $till_time);
	if ($this->post['search_by_status_last_modified']) {
		$column='o.status_last_modified';
	} else {
		$column='o.crdate';
	}
	$filter[] 		= $column." BETWEEN '".$start_time."' and '".$end_time."'";
}
//print_r($filter);
//print_r($this->post);
//die();
if ($this->post['orders_status_search'] > 0) {
	$filter[]="(o.status='".$this->post['orders_status_search']."')";
}
if ($this->cookie['paid_orders_only']) {
	$filter[]="(o.paid='1')";		
}
if (!$this->masterShop) {
	$filter[]='o.page_uid='.$this->shop_pid;
}
//$orderby[]='orders_id desc';	
$select[]='o.*, osd.name as orders_status';
//$orderby[]='o.orders_id desc';

switch ($this->get['tx_multishop_pi1']['order_by']) {
	case 'billing_name':
		$order_by='o.billing_name';
		break;
	case 'crdate':
		$order_by='o.crdate';
		break;
	case 'grand_total':
		$order_by='o.grand_total';
		break;
	case 'shipping_method_label':
		$order_by='o.shipping_method_label';
		break;
	case 'payment_method_label':
		$order_by='o.payment_method_label';
		break;
	case 'status_last_modified':
		$order_by='o.status_last_modified';
		break;
	case 'orders_id':
	default:
		$order_by='o.orders_id';
		break;
}
switch ($this->get['tx_multishop_pi1']['order']) {
	case 'a':
		$order='asc';
		$order_link='d';
		break;
	case 'd':
	default:
		$order='desc';
		$order_link='a';
		break;
}
$orderby[]=$order_by.' '.$order;

if ($this->post['tx_multishop_pi1']['by_phone']) {
	$filter[]='o.by_phone=1';
}
if ($this->post['tx_multishop_pi1']['is_proposal']) {
	$filter[]='o.is_proposal=1';
} else {
	$filter[]='o.is_proposal=0';
}
$pageset=mslib_fe::getOrdersPageSet($filter,$offset,20,$orderby,$having,$select,$where,$from);
$tmporders=$pageset['orders'];		
if ($pageset['total_rows'] > 0) {
	$data=array();
	$data[]=array('Bestelnummer','Bedrag',$this->pi_getLL('admin_paid'),$this->pi_getLL('admin_payment_method'));
	foreach ($tmporders as $order) {
		$edit_order_popup_width = 980;
		if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
			$edit_order_popup_width += 70;
		}
		if ($this->ms['MODULES']['ORDER_EDIT'] && !$order['is_locked']) {
			if ($edit_order_popup_width > 980) {
				$edit_order_popup_width += 155;
			} else {
				$edit_order_popup_width += 70;
			}
		}
		
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';			
		}
		if ($this->masterShop) {				
			$master_shop_col ='<td align="left" nowrap>'.mslib_fe::getShopNameByPageUid($order['page_uid']).'</td>';
		}
		if ($order['billing_company']) {
			$customer_name=$order['billing_company'];
		} else {
			$customer_name=$order['billing_name'];
		}	
	
		$paid_status = '';
		if (!$order['paid']) {
			$paid_status.='<span class="admin_status_red" alt="'.$this->pi_getLL('has_not_been_paid').'" title="'.$this->pi_getLL('has_not_been_paid').'"></span>&nbsp;';								
							
		} else {
			$paid_status.='<span class="admin_status_green" alt="'.$this->pi_getLL('has_been_paid').'" title="'.$this->pi_getLL('has_been_paid').'"></span>';					
		}
			
		$print_order_list_button = false;
		switch ($page_type) {
			case 'proposals':
					$orderlist_buttons['mail_order'] = '<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=mail_order').'" rel="email" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: \'910\', height: browser_height} )" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('email')).'</a>';
					$orderlist_buttons['convert_to_order'] = '<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&orders_id='.$order['orders_id'].'&tx_multishop_pi1[action]=convert_to_order').'" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('convert_to_order')).'</a>';
					$print_order_list_button = true;
			break;	
			case 'orders':
				if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE'] || $this->ms['MODULES']['PACKING_LIST_PRINT']) {	
					if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
						$orderlist_buttons['invoice'] = '<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order&print=invoice').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: \'910\', height: browser_height} )" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('invoice')).'</a>';
						$print_order_list_button = true;
					}					
					if ($this->ms['MODULES']['PACKING_LIST_PRINT']) {
						$orderlist_buttons['pakbon'] = '<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order&print=packing').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: \'910\', height: browser_height} )" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('packing_list')).'</a>';
						$print_order_list_button = true;
					}
				}		
			break;
		}
		// extra input jquery
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingButton'])) {
			$params = array('orderlist_buttons' => &$orderlist_buttons,
					'order' => &$order
			);	
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingButton'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}	
		$order_list_button_extra = '';
		if ($print_order_list_button) {
			//button area
			$order_list_button_extra.='<td align="center" nowrap>';
			$order_list_button_extra .= implode("&nbsp;", $orderlist_buttons);
			$order_list_button_extra.='</td>';
		}
		$ip_address='';
		if ($order['ip_address']) {
			$ip_address=$order['ip_address'];
		}		
		$http_referer='';		
		if ($order['http_referer']) {
			$domain=parse_url($order['http_referer']);
			if ($domain['host']) {
				$http_referer='<a href="'.$order['http_referer'].'" target="_blank" rel="noreferrer">'.$domain['host'].'</a>';
			}					
		}		
/*
'<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: '.$edit_order_popup_width.', height: browser_height} )" title="Loading" class="tooltip" rel="'.$order['orders_id'].'">'.$customer_name.'</a>'
*/		
		$data[]=array(
			'<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: '.$edit_order_popup_width.', height: browser_height} )" title="Loading" class="tooltip" rel="'.$order['orders_id'].'">'.$order['orders_id'].'</a>',
			mslib_fe::amount2Cents($order['grand_total'],0),
			$paid_status,
			'<span title="'.htmlspecialchars($order['payment_method_label']).'">'.$order['payment_method_label'].'</span>'
		);
						
	}
	$counter=0;
	$libaryWidgets['ordersLatest']['content'].='<div id="tblWidgetOrdersLatest-wrapper"><table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" id="tblWidgetOrdersLatest">';
	$tr_type='';
	$rowCounter=0;
	foreach ($data as $host => $item) {
		$counter++;
		if ($counter==1) {
			$libaryWidgets['ordersLatest']['content'].='<tr class="tblHeader">';
			$colCounter=0;
			foreach ($item as $col) {
				$colCounter++;
				$libaryWidgets['ordersLatest']['content'].='
					<th class="tblHeadCol'.$colCounter.'">'.$col.'</th>
				';
			}
			$libaryWidgets['ordersLatest']['content'].='</tr>';
		} else {	
			$rowCounter++;
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';			
			}		
			$libaryWidgets['ordersLatest']['content'].='<tr class="tblBody '.$tr_type.'">';
			$colCounter=0;
			foreach ($item as $col) {
				$colCounter++;
				$libaryWidgets['ordersLatest']['content'].='
					<td class="tblBodyCol'.$colCounter.'">'.$col.'</td>
				';
			}
			$libaryWidgets['ordersLatest']['content'].='</tr>';
		}
	}
	$libaryWidgets['ordersLatest']['content'].='</table></div>';	
} 

?>