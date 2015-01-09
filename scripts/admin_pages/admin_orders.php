<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$all_orders_status=mslib_fe::getAllOrderStatus();
if ($this->post['tx_multishop_pi1']['edit_order']==1 and is_numeric($this->post['tx_multishop_pi1']['orders_id'])) {
	$url=$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$this->post['tx_multishop_pi1']['orders_id'].'&action=edit_order&tx_multishop_pi1[is_proposal]='.$this->post['tx_multishop_pi1']['is_proposal']);
	header('Location: '.$url);
	exit();
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
switch ($this->post['tx_multishop_pi1']['action']) {
	case 'export_selected_order_to_xls':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel.php');
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/orders/orders_xls_export.php');
		}
		break;
	case 'create_invoice_for_selected_orders':
		if (is_array($this->post['selected_orders']) && count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				$order=mslib_fe::getOrder($orders_id);
				if ($order['orders_id']) {
					mslib_fe::createOrderInvoice($order['orders_id']);
				}
			}
		}
		break;
	case 'convert_to_order':
		if ($this->post['orders_id']) {
			$order=mslib_fe::getOrder($this->post['orders_id']);
			if ($order['is_proposal']) {
				$updateArray=array();
				$updateArray['is_proposal']=0;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$order['orders_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		break;
	case 'change_order_status_for_selected_orders':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders']) and is_numeric($this->post['tx_multishop_pi1']['update_to_order_status'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$orders=mslib_fe::getOrder($orders_id);
					if ($orders['orders_id'] and ($orders['status']!=$this->post['tx_multishop_pi1']['update_to_order_status'])) {
						// mslib_befe::updateOrderStatus($orders['orders_id'],$this->post['tx_multishop_pi1']['update_to_order_status']);
						mslib_befe::updateOrderStatus($orders['orders_id'], $this->post['tx_multishop_pi1']['update_to_order_status'], 1);
					}
				}
			}
		}
		break;
	case 'delete_selected_orders':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$updateArray=array();
					$updateArray['deleted']=1;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$orders_id.'\'', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		break;
	case 'mail_selected_orders_to_merchant':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$order=mslib_fe::getOrder($orders_id);
					if ($order['orders_id']) {
						$mail_template='';
						if ($order['paid']) {
							$mail_template='email_order_paid_letter';
						}
						mslib_fe::mailOrder($orders_id, 0, $this->ms['MODULES']['STORE_EMAIL'], $mail_template);
					}
				}
			}
		}
		break;
	case 'mail_selected_orders_to_customer':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$order=mslib_fe::getOrder($orders_id);
					if ($order['orders_id']) {
						$mail_template='';
						if ($order['paid']) {
							$mail_template='email_order_paid_letter';
						}
						mslib_fe::mailOrder($orders_id, 0, '', $mail_template);
					}
				}
			}
		}
		break;
	case 'update_selected_orders_to_paid':
	case 'update_selected_orders_to_not_paid':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$order=mslib_fe::getOrder($orders_id);
					if ($order['orders_id']) {
						if ($this->post['tx_multishop_pi1']['action']=='update_selected_orders_to_paid') {
							mslib_fe::updateOrderStatusToPaid($orders_id);
						} elseif ($this->post['tx_multishop_pi1']['action']=='update_selected_orders_to_not_paid') {
							$updateArray=array('paid'=>0);
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id='.$orders_id, $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					}
				}
			}
		}
		break;
	case 'mail_selected_orders_for_payment_reminder':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				$tmpArray=mslib_fe::getOrder($orders_id); //=mslib_befe::getRecord($orders_id, 'tx_multishop_orders', 'orders_id');
				if ($tmpArray['paid']==0) {
					// replacing the variables with dynamic values
					$billing_address='';
					$delivery_address='';
					$full_customer_name=$tmpArray['billing_first_name'];
					if ($tmpArray['billing_middle_name']) {
						$full_customer_name.=' '.$tmpArray['billing_middle_name'];
					}
					if ($tmpArray['billing_last_name']) {
						$full_customer_name.=' '.$tmpArray['billing_last_name'];
					}
					$delivery_full_customer_name=$tmpArray['delivery_first_name'];
					if ($tmpArray['delivery_middle_name']) {
						$delivery_full_customer_name.=' '.$tmpArray['delivery_middle_name'];
					}
					if ($order['delivery_last_name']) {
						$delivery_full_customer_name.=' '.$tmpArray['delivery_last_name'];
					}
					$full_customer_name=preg_replace('/\s+/', ' ', $full_customer_name);
					$delivery_full_customer_name=preg_replace('/\s+/', ' ', $delivery_full_customer_name);
					if ($tmpArray['delivery_company']) {
						$delivery_address=$tmpArray['delivery_company']."<br />";
					}
					if ($delivery_full_customer_name) {
						$delivery_address.=$delivery_full_customer_name."<br />";
					}
					if ($tmpArray['delivery_address']) {
						$delivery_address.=$tmpArray['delivery_address']."<br />";
					}
					if ($tmpArray['delivery_zip'] and $tmpArray['delivery_city']) {
						$delivery_address.=$tmpArray['delivery_zip']." ".$tmpArray['delivery_city'];
					}
					if ($tmpArray['delivery_country']) {
						$delivery_address.='<br />'.ucfirst(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $tmpArray['delivery_country']));
					}
					if ($tmpArray['billing_company']) {
						$billing_address=$tmpArray['billing_company']."<br />";
					}
					if ($full_customer_name) {
						$billing_address.=$full_customer_name."<br />";
					}
					if ($tmpArray['billing_address']) {
						$billing_address.=$tmpArray['billing_address']."<br />";
					}
					if ($tmpArray['billing_zip'] and $tmpArray['billing_city']) {
						$billing_address.=$tmpArray['billing_zip']." ".$tmpArray['billing_city'];
					}
					if ($tmpArray['billing_country']) {
						$billing_address.='<br />'.ucfirst(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $tmpArray['billing_country']));
					}
					if (empty($tmpArray['hash'])) {
						$hashcode=md5($orders_id+time());
						$updateArray=array();
						$updateArray['hash']=$hashcode;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id='.$orders_id, $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$hashcode=$tmpArray['hash'];
					}
					$array1=array();
					$array2=array();
					$array1[]='###GENDER_SALUTATION###';
					$array2[]=mslib_fe::genderSalutation($tmpArray['billing_gender']);
					$array1[]='###DELIVERY_FIRST_NAME###';
					$array2[]=$tmpArray['delivery_first_name'];
					$array1[]='###DELIVERY_LAST_NAME###';
					$array2[]=preg_replace('/\s+/', ' ', $tmpArray['delivery_middle_name'].' '.$tmpArray['delivery_last_name']);
					$array1[]='###BILLING_FIRST_NAME###';
					$array2[]=$order['billing_first_name'];
					$array1[]='###BILLING_LAST_NAME###';
					$array2[]=preg_replace('/\s+/', ' ', $tmpArray['billing_middle_name'].' '.$tmpArray['billing_last_name']);
					$array1[]='###BILLING_TELEPHONE###';
					$array2[]=$tmpArray['billing_telephone'];
					$array1[]='###DELIVERY_TELEPHONE###';
					$array2[]=$tmpArray['delivery_telephone'];
					$array1[]='###BILLING_MOBILE###';
					$array2[]=$tmpArray['billing_mobile'];
					$array1[]='###DELIVERY_MOBILE###';
					$array2[]=$tmpArray['delivery_mobile'];
					$array1[]='###FULL_NAME###';
					$array2[]=$full_customer_name;
					$array1[]='###DELIVERY_FULL_NAME###';
					$array2[]=$delivery_full_customer_name;
					$array1[]='###BILLING_NAME###';
					$array2[]=$tmpArray['billing_name'];
					$array1[]='###BILLING_EMAIL###';
					$array2[]=$tmpArray['billing_email'];
					$array1[]='###DELIVERY_EMAIL###';
					$array2[]=$tmpArray['delivery_email'];
					$array1[]='###DELIVERY_NAME###';
					$array2[]=$tmpArray['delivery_name'];
					$array1[]='###CUSTOMER_EMAIL###';
					$array2[]=$tmpArray['billing_email'];
					$array1[]='###STORE_NAME###';
					$array2[]=$this->ms['MODULES']['STORE_NAME'];
					$array1[]='###TOTAL_AMOUNT###';
					$array2[]=mslib_fe::amount2Cents($tmpArray['total_amount']);
					require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
					$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
					$mslib_order->init($this);
					$ORDER_DETAILS=$mslib_order->printOrderDetailsTable($tmpArray, 'site');
					$array1[]='###ORDER_DETAILS###';
					$array2[]=$ORDER_DETAILS;
					$array1[]='###BILLING_ADDRESS###';
					$array2[]=$billing_address;
					$array1[]='###DELIVERY_ADDRESS###';
					$array2[]=$delivery_address;
					$array1[]='###CUSTOMER_ID###';
					$array2[]=$tmpArray['customer_id'];
					$array1[]='###SHIPPING_METHOD###';
					$array2[]=$tmpArray['shipping_method_label'];
					$array1[]='###PAYMENT_METHOD###';
					$array2[]=$tmpArray['payment_method_label'];
					$array1[]='###ORDERS_ID###';
					$array2[]=$tmpArray['orders_id'];
					$invoice=mslib_fe::getOrderInvoice($tmpArray['orders_id'], 0);
					$invoice_id='';
					$invoice_link='';
					if (is_array($invoice)) {
						$invoice_id=$invoice['invoice_id'];
						$invoice_link='<a href="'.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'">'.$invoice['invoice_id'].'</a>';
					}
					$array1[]='###INVOICE_NUMBER###';
					$array2[]=$invoice_id;
					$array1[]='###INVOICE_LINK###';
					$array2[]=$invoice_link;
					$time=$tmpArray['crdate'];
					$long_date=strftime($this->pi_getLL('full_date_format'), $time);
					$array1[]='###ORDER_DATE_LONG###'; // ie woensdag 23 juni, 2010
					$array2[]=$long_date;
					// backwards compatibility
					$array1[]='###LONG_DATE###'; // ie woensdag 23 juni, 2010
					$array2[]=$long_date;
					$time=time();
					$long_date=strftime($this->pi_getLL('full_date_format'), $time);
					$array1[]='###CURRENT_DATE_LONG###'; // ie woensdag 23 juni, 2010
					$array2[]=$long_date;
					$array1[]='###STORE_NAME###';
					$array2[]=$this->ms['MODULES']['STORE_NAME'];
					$array1[]='###TOTAL_AMOUNT###';
					$array2[]=mslib_fe::amount2Cents($tmpArray['total_amount']);
					$array1[]='###PROPOSAL_NUMBER###';
					$array2[]=$tmpArray['orders_id'];
					$array1[]='###ORDER_NUMBER###';
					$array2[]=$tmpArray['orders_id'];
					$array1[]='###ORDER_LINK###';
					$array2[]='';
					$array1[]='###CUSTOMER_ID###';
					$array2[]=$tmpArray['customer_id'];
					$link=$this->FULL_HTTP_URL.mslib_fe::typolink($tmpArray['page_uid'], 'tx_multishop_pi1[page_section]=payment_reminder_checkout&tx_multishop_pi1[hash]='.$hashcode);
					$array1[]='###PAYMENT_PAGE_LINK###';
					$array2[]=$link;
					// psp email template
					$psp_mail_template=array();
					if ($tmpArray['payment_method']) {
						$psp_data=mslib_fe::loadPaymentMethod($tmpArray['payment_method']);
						$psp_vars=unserialize($psp_data['vars']);
						if (isset($psp_vars['order_payment_reminder'])) {
							$psp_mail_template['order_payment_reminder']='';
							if ($psp_vars['order_payment_reminder']>0) {
								$psp_mail_template['order_payment_reminder']=mslib_fe::getCMSType($psp_vars['order_payment_reminder']);
							}
						}
					}
					if (isset($psp_mail_template['order_payment_reminder'])) {
						$page=array();
						if (!empty($psp_mail_template['order_payment_reminder'])) {
							$page=mslib_fe::getCMScontent($psp_mail_template['order_payment_reminder'], $GLOBALS['TSFE']->sys_language_uid);
						}
					} else {
						$cms_type='payment_reminder_email_templates_'.$tmpArray['payment_method'];
						$page=mslib_fe::getCMScontent($cms_type, $GLOBALS['TSFE']->sys_language_uid);
						if (!count($page[0])) {
							$page=mslib_fe::getCMScontent('payment_reminder_email_templates', $GLOBALS['TSFE']->sys_language_uid);
						}
					}
					if ($page[0]['name']) {
						$reminder_cms_content='';
						if ($page[0]['name']) {
							$page[0]['name']=str_replace($array1, $array2, $page[0]['name']);
							$reminder_cms_content.='<div class="main-heading"><h2>'.$page[0]['name'].'</h2></div>';
						}
						if ($page[0]['content']) {
							$page[0]['content']=str_replace($array1, $array2, $page[0]['content']);
							$reminder_cms_content.=$page[0]['content'];
						}
						$full_customer_name=$tmpArray['billing_first_name'];
						if ($order['billing_middle_name']) {
							$full_customer_name.=' '.$tmpArray['billing_middle_name'];
						}
						if ($order['billing_last_name']) {
							$full_customer_name.=' '.$tmpArray['billing_last_name'];
						}
						$user=array();
						$user['name']=$full_customer_name;
						$user['email']=$tmpArray['billing_email'];
						if ($user['email']) {
							mslib_fe::mailUser($user, $page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME']);
						}
					}
				}
			}
		}
		break;
	default:
		// post processing by third party plugins
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersPostHookProc'])) {
			$params=array();
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersPostHookProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		break;
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_orders_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_orders_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_orders.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['orders_results']=$this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['orders_listing']=$this->cObj->getSubpart($subparts['orders_results'], '###ORDERS_LISTING###');
$subparts['orders_noresults']=$this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
if ($this->post['Search'] and ($this->post['payment_status']!=$this->cookie['payment_status'])) {
	$this->cookie['payment_status']=$this->post['payment_status'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->post['Search'] and ($this->post['limit']!=$this->cookie['limit'])) {
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
	//"crdate" =>				$this->pi_getLL('admin_order_date'),
	"billing_zip"=>$this->pi_getLL('admin_zip'),
	"billing_city"=>$this->pi_getLL('admin_city'),
	"billing_address"=>$this->pi_getLL('admin_address'),
	"billing_company"=>$this->pi_getLL('admin_company'),
	"shipping_method"=>$this->pi_getLL('admin_shipping_method'),
	//"payment_method"=>$this->pi_getLL('admin_payment_method'),
	"order_products"=>$this->pi_getLL('admin_order_products'),
	/*"billing_country"=>ucfirst(strtolower($this->pi_getLL('admin_countries'))),*/
	"billing_telephone"=>$this->pi_getLL('telephone')
);
asort($option_search);
$type_search=$this->post['type_search'];
if ($_REQUEST['skeyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->post['skeyword']=$_REQUEST['skeyword'];
	$this->post['skeyword']=trim($this->post['skeyword']);
	$this->post['skeyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->post['skeyword'], $GLOBALS['TSFE']->metaCharset);
	$this->post['skeyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->post['skeyword'], TRUE);
	$this->post['skeyword']=mslib_fe::RemoveXSS($this->post['skeyword']);
}
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
$option_item='<select name="type_search" class="order_select2" style="width:200px" id="type_search"><option value="all">'.$this->pi_getLL('all').'</option>';
foreach ($option_search as $key=>$val) {
	$option_item.='<option value="'.$key.'" '.($this->post['type_search']==$key ? "selected" : "").'>'.$val.'</option>';
}
$option_item.='</select>';
$orders_status_list='<select name="orders_status_search" id="orders_status_search" class="order_select2" style="width:200px"><option value="0" '.((!$order_status_search_selected) ? 'selected' : '').'>'.$this->pi_getLL('all_orders_status', 'All orders status').'</option>';
if (is_array($all_orders_status)) {
	$order_status_search_selected=false;
	foreach ($all_orders_status as $row) {
		$orders_status_list.='<option value="'.$row['id'].'" '.(($this->post['orders_status_search']==$row['id']) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
		if ($this->post['orders_status_search']==$row['id']) {
			$order_status_search_selected=true;
		}
	}
}
$orders_status_list.='</select>';
$limit_selectbox='<select name="limit" id="limit">';
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
				switch($fields) {
					case 'orders_id':
						$items[]="o.".$fields." LIKE '%".addslashes($this->post['skeyword'])."%'";
						break;
					case 'order_products':
						//$items[]="(op.products_name LIKE '%".addslashes($this->post['skeyword'])."%' or op.products_description LIKE '%".addslashes($this->post['skeyword'])."%')";
						$items[]=" orders_id IN (SELECT op.orders_id from tx_multishop_orders_products op where op.products_name LIKE '%".addslashes($this->post['skeyword'])."%' or op.products_description LIKE '%".addslashes($this->post['skeyword'])."%')";
						break;
					default:
						$items[]=$fields." LIKE '%".addslashes($this->post['skeyword'])."%'";
						break;
				}
			}
			$items[]="delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
			$filter[]=implode(" or ", $items);
			break;
		case 'orders_id':
			$filter[]=" o.orders_id='".addslashes($this->post['skeyword'])."'";
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
			$filter[]=" (shipping_method_label '%".addslashes($this->post['skeyword'])."%' or shipping_method_label LIKE '%".addslashes($this->post['skeyword'])."%')";
			break;
		/*case 'payment_method':
			$filter[]=" (payment_method LIKE '%".addslashes($this->post['skeyword'])."%' or payment_method_label LIKE '%".addslashes($this->post['skeyword'])."%')";
			break;*/
		case 'customer_id':
			$filter[]=" customer_id='".addslashes($this->post['skeyword'])."'";
			break;
		case 'order_products':
			$filter[]=" orders_id IN (SELECT op.orders_id from tx_multishop_orders_products op where op.products_name LIKE '%".addslashes($this->post['skeyword'])."%' or op.products_description LIKE '%".addslashes($this->post['skeyword'])."%')";
			/*
			$filter[]=" (op.products_name LIKE '%".addslashes($this->post['skeyword'])."%' or op.products_description LIKE '%".addslashes($this->post['skeyword'])."%')";
			$from[]=' tx_multishop_orders_products op';
			$where[]=' o.orders_id=op.orders_id';
			*/
			break;
		/*case 'billing_country':
			$filter[]=" billing_country LIKE '%".addslashes($this->post['skeyword'])."%'";
			break;*/
		case 'billing_telephone':
			$filter[]=" billing_telephone LIKE '%".addslashes($this->post['skeyword'])."%'";
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
if (isset($this->get['payment_method']) && $this->post['payment_method']!='all') {
	if ($this->post['payment_method']=='nopm') {
		$filter[]="(o.payment_method is null)";
	} else {
		$filter[]="(o.payment_method='".$this->post['payment_method']."')";
	}
}
if (isset($this->post['usergroup']) && $this->post['usergroup']>0) {
	$filter[]=' o.customer_id IN (SELECT uid from fe_users where '.$GLOBALS['TYPO3_DB']->listQuery('usergroup', $this->post['usergroup'], 'fe_users').')';
}
if ($this->cookie['payment_status']=='paid_only') {
	$filter[]="(o.paid='1')";
} else {
	if ($this->cookie['payment_status']=='unpaid_only') {
		$filter[]="(o.paid='0')";
	}
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
if (isset($this->post['country']) && !empty($this->post['country'])) {
	$filter[]="o.billing_country='".$this->post['country']."'";
}
if ($this->post['tx_multishop_pi1']['is_proposal']) {
	$filter[]='o.is_proposal=1';
} else {
	$filter[]='o.is_proposal=0';
}
$pageset=mslib_fe::getOrdersPageSet($filter, $offset, $this->post['limit'], $orderby, $having, $select, $where, $from);
$tmporders=$pageset['orders'];
if ($pageset['total_rows']>0) {
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/orders/orders_listing_table.php');
} else {
	$subpartArray=array();
	$subpartArray['###LABEL_NO_RESULTS###']=$this->pi_getLL('no_orders_found').'.';
	$no_results=$this->cObj->substituteMarkerArrayCached($subparts['orders_noresults'], array(), $subpartArray);
}
$payment_status_select='<select name="payment_status" id="payment_status" class="order_select2" style="width:250px">
<option value="">'.$this->pi_getLL('select_orders_payment_status').'</option>';
if ($this->cookie['payment_status']=='paid_only') {
	$payment_status_select.='<option value="paid_only" selected="selected">'.$this->pi_getLL('show_paid_orders_only').'</option>';
} else {
	$payment_status_select.='<option value="paid_only">'.$this->pi_getLL('show_paid_orders_only').'</option>';
}
if ($this->cookie['payment_status']=='unpaid_only') {
	$payment_status_select.='<option value="unpaid_only" selected="selected">'.$this->pi_getLL('show_unpaid_orders_only').'</option>';
} else {
	$payment_status_select.='<option value="unpaid_only">'.$this->pi_getLL('show_unpaid_orders_only').'</option>';
}
$payment_status_select.='</select>';
$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input='';
if (is_array($groups) and count($groups)) {
	$customer_groups_input.='<select id="groups" class="order_select2" name="usergroup" style="width:200px">'."\n";
	$customer_groups_input.='<option value="0">'.$this->pi_getLL('all').' '.$this->pi_getLL('usergroup').'</option>'."\n";
	foreach ($groups as $group) {
		$customer_groups_input.='<option value="'.$group['uid'].'"'.($this->post['usergroup']==$group['uid'] ? ' selected="selected"' : '').'>'.$group['title'].'</option>'."\n";
	}
	$customer_groups_input.='</select>'."\n";
}
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
	$payment_methods[$row['payment_method']]=$row['payment_method_label'];
}
$payment_method_input='';
if (is_array($payment_methods) and count($payment_methods)) {
	$payment_method_input.='<select id="payment_method" class="order_select2" name="payment_method" style="width:200px">'."\n";
	$payment_method_input.='<option value="all">'.$this->pi_getLL('all').' '.ucfirst(strtolower($this->pi_getLL('admin_payment_methods'))).'</option>'."\n";
	foreach ($payment_methods as $payment_method_code=>$payment_method) {
		$payment_method_input.='<option value="'.$payment_method_code.'"'.($this->post['payment_method']==$payment_method_code ? ' selected="selected"' : '').'>'.$payment_method.'</option>'."\n";
	}
	$payment_method_input.='</select>'."\n";
}
$order_countries=mslib_befe::getRecords('', 'tx_multishop_orders', '', array(), 'billing_country', 'billing_country asc');
$order_billing_country=array();
foreach ($order_countries as $order_country) {
	$order_billing_country[]=mslib_befe::strtolower($order_country['billing_country']);
}
$enabled_countries=mslib_fe::loadEnabledCountries();
$billing_countries_array=array();
foreach ($enabled_countries as $country) {
	if (in_array(mslib_befe::strtolower($country['cn_short_en']), $order_billing_country)) {
		$billing_countries_array[]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($this->post['country'])==strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
	}
}
$billing_countries_selectbox='<select class="order_select2" name="country" id="country""><option value="">'.$this->pi_getLL('all').' '.$this->pi_getLL('countries').'</option>'.implode("\n", $billing_countries_array).'</select>';
$subpartArray=array();
$subpartArray['###AJAX_ADMIN_EDIT_ORDER_URL###']=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&action=edit_order');
$subpartArray['###FORM_SEARCH_ACTION_URL###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders');
$subpartArray['###SHOP_PID###']=$this->shop_pid;
$subpartArray['###LABEL_KEYWORD###']=ucfirst($this->pi_getLL('keyword'));
$subpartArray['###VALUE_KEYWORD###']=($this->post['skeyword'] ? $this->post['skeyword'] : "");
$subpartArray['###LABEL_SEARCH_ON###']=$this->pi_getLL('search_for');
$subpartArray['###OPTION_ITEM_SELECTBOX###']=$option_item;
$subpartArray['###LABEL_USERGROUP###']=$this->pi_getLL('usergroup');
$subpartArray['###USERGROUP_SELECTBOX###']=$customer_groups_input;
$subpartArray['###LABEL_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
$subpartArray['###PAYMENT_METHOD_SELECTBOX###']=$payment_method_input;
$subpartArray['###LABEL_ORDER_STATUS###']=$this->pi_getLL('order_status');
$subpartArray['###ORDERS_STATUS_LIST_SELECTBOX###']=$orders_status_list;
$subpartArray['###VALUE_SEARCH###']=htmlspecialchars($this->pi_getLL('search'));
$subpartArray['###LABEL_DATE_FROM###']=$this->pi_getLL('from');
$subpartArray['###VALUE_DATE_FORM###']=$this->post['order_date_from'];
$subpartArray['###LABEL_DATE_TO###']=$this->pi_getLL('to');
$subpartArray['###VALUE_DATE_TO###']=$this->post['order_date_till'];
$subpartArray['###LABEL_FILTER_LAST_MODIFIED###']=$this->pi_getLL('filter_by_date_status_last_modified', 'Filter by date status last modified');
$subpartArray['###FILTER_BY_LAST_MODIFIED_CHECKED###']=($this->post['search_by_status_last_modified'] ? ' checked' : '');
$subpartArray['###LABEL_PAYMENT_STATUS###']=$this->pi_getLL('order_payment_status');;
$subpartArray['###PAYMENT_STATUS_SELECTBOX###']=$payment_status_select;
$subpartArray['###LABEL_RESULTS_LIMIT_SELECTBOX###']=$this->pi_getLL('limit_number_of_records_to');
$subpartArray['###RESULTS_LIMIT_SELECTBOX###']=$limit_selectbox;
$subpartArray['###RESULTS###']=$order_results;
$subpartArray['###NORESULTS###']=$no_results;
$subpartArray['###ADMIN_LABEL_TABS_ORDERS###']=$this->pi_getLL('admin_label_tabs_orders');
$subpartArray['###LABEL_COUNTRIES_SELECTBOX###']=$this->pi_getLL('countries');
$subpartArray['###COUNTRIES_SELECTBOX###']=$billing_countries_selectbox;
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.$content.'</div>';
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".order_select2").select2();
});
</script>
';

?>