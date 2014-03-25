<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if (!$this->post['tx_multishop_pi1']['action'] && $this->get['tx_multishop_pi1']['action']) {
	$this->post['tx_multishop_pi1']['action']=$this->get['tx_multishop_pi1']['action'];
}
if ($this->post) {
	foreach ($this->post as $post_idx=>$post_val) {
		$this->get[$post_idx]=$post_val;
	}
}
if ($this->get) {
	foreach ($this->get as $get_idx=>$get_val) {
		$this->post[$get_idx]=$get_val;
	}
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_useragents_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_useragents_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_useragents.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['useragents_results']=$this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['useragents_listing']=$this->cObj->getSubpart($subparts['useragents_results'], '###ORDERS_LISTING###');
$subparts['useragents_noresults']=$this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
if ($this->post['limit']!=$this->cookie['limit']) {
	$this->cookie['limit']=$this->post['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
	$this->post['limit']=$this->cookie['limit'];
} else {
	$this->post['limit']=10;
}
$this->ms['MODULES']['ORDERS_LISTING_LIMIT']=$this->post['limit'];
$option_search=array(
	"orders_id"=>$this->pi_getLL('admin_order_id'),
	"invoice"=>$this->pi_getLL('admin_invoice_number'),
	"customer_id"=>$this->pi_getLL('admin_customer_id'),
	"billing_email"=>$this->pi_getLL('admin_customer_email'),
	"delivery_name"=>$this->pi_getLL('admin_customer_name'),
	//"crdate" => $this->pi_getLL('admin_order_date'),
	"billing_zip"=>$this->pi_getLL('admin_zip'),
	"billing_city"=>$this->pi_getLL('admin_city'),
	"billing_address"=>$this->pi_getLL('admin_address'),
	"billing_company"=>$this->pi_getLL('admin_company'),
	"shipping_method"=>$this->pi_getLL('admin_shipping_method'),
	"payment_method"=>$this->pi_getLL('admin_payment_method')
);
asort($option_search);
$type_search=$this->post['type_search'];
if (is_numeric($this->post['p'])) {
	$p=$this->post['p'];
}
if ($p>0) {
	$offset=(((($p)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])));
} else {
	$p=0;
	$offset=0;
}
// orders search
$limit_selectbox='<select name="limit" onchange="this.form.submit()">';
$limits=array();
$limits[]='10';
$limits[]='15';
$limits[]='20';
$limits[]='25';
$limits[]='30';
$limits[]='40';
$limits[]='48';
$limits[]='50';
$limits[]='100';
$limits[]='150';
$limits[]='200';
$limits[]='250';
$limits[]='300';
$limits[]='350';
$limits[]='400';
$limits[]='450';
$limits[]='500';
foreach ($limits as $limit) {
	$limit_selectbox.='<option value="'.$limit.'"'.($limit==$this->post['limit'] ? ' selected' : '').'>'.$limit.'</option>';
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
if ($this->post['skeyword']) {
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
				$items[]=$fields." LIKE '%".addslashes($this->post['skeyword'])."%'";
			}
			$items[]="delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
			$filter[]=implode(" or ", $items);
			break;
		case 'orders_id':
			$filter[]=" orders_id='".addslashes($this->post['skeyword'])."'";
			break;
		case 'invoice':
			$filter[]=" invoice_id LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_email':
			$filter[]=" billing_email LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'delivery_name':
			$filter[]=" delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_zip':
			$filter[]=" billing_zip LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_city':
			$filter[]=" billing_city LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_address':
			$filter[]=" billing_address LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'billing_company':
			$filter[]=" billing_company LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'shipping_method':
			$filter[]=" shipping_method LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'payment_method':
			$filter[]=" payment_method LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;
		case 'customer_id':
			$filter[]=" customer_id='".addslashes($this->post['skeyword'])."'";
			break;
	}
}
if (!empty($this->post['order_date_from']) && !empty($this->post['order_date_till'])) {
	list($from_date, $from_time)=explode(" ", $this->post['order_date_from']);
	list($fd, $fm, $fy)=explode('/', $from_date);
	list($till_date, $till_time)=explode(" ", $this->post['order_date_till']);
	list($td, $tm, $ty)=explode('/', $till_date);
	$start_time=strtotime($fy.'-'.$fm.'-'.$fd.' '.$from_time);
	$end_time=strtotime($ty.'-'.$tm.'-'.$td.' '.$till_time);
	if ($this->post['search_by_status_last_modified']) {
		$column='o.status_last_modified';
	} else {
		$column='o.crdate';
	}
	$filter[]=$column." BETWEEN '".$start_time."' and '".$end_time."'";
}
//print_r($filter);
//print_r($this->post);
//die();
if ($this->post['orders_status_search']>0) {
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
$pageset=mslib_fe::getOrdersPageSet($filter, $offset, $this->post['limit'], $orderby, $having, $select, $where, $from);
$tmporders=$pageset['orders'];
if ($pageset['total_rows']>0) {
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/user-agent_listing_table.php');
} else {
	$subpartArray=array();
	$subpartArray['###LABEL_NO_RESULTS###']=$this->pi_getLL('no_orders_found').'.';
	$no_results=$this->cObj->substituteMarkerArrayCached($subparts['useragents_noresults'], array(), $subpartArray);
}
$subpartArray=array();
$subpartArray['###FORM_SEARCH_ACTION_URL###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_user_agent');
$subpartArray['###SHOP_PID###']=$this->shop_pid;
$subpartArray['###LABEL_RESULTS_LIMIT_SELECTBOX###']=$this->pi_getLL('limit_number_of_records_to');
$subpartArray['###RESULTS_LIMIT_SELECTBOX###']=$limit_selectbox;
$subpartArray['###RESULTS###']=$order_results;
$subpartArray['###NORESULTS###']=$no_results;
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
//$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
$content='<div class="fullwidth_div">'.$content.'</div>';

?>