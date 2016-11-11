<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='';
switch ($this->get['tx_multishop_pi1']['admin_ajax_edit_order']) {
	case 'get_order_payment_methods':
		$return_data=array();
		if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
			$order_id=$this->post['tx_multishop_pi1']['order_id'];
			if (is_numeric($order_id)) {
				$payment_methods=mslib_fe::loadPaymentMethods();
				$order_data=mslib_fe::getOrder($order_id);
				//
				$orderDetailsItem='<div class="form-group msAdminEditOrderPaymentMethod row">';
				$orderDetailsItem.='<label class="control-label col-md-3">'.$this->pi_getLL('date_paid','Date paid').': </label>';
				$today_tstamp=time();
				$orders_paid_timestamp_visual=strftime('%x', $today_tstamp);
				$orders_paid_timestamp=date("Y-m-d", $today_tstamp);
				if ($order_data['orders_paid_timestamp']>0) {
					$orders_paid_timestamp_visual=strftime('%x', $order_data['orders_paid_timestamp']);
					$orders_paid_timestamp=date("Y-m-d", $order_data['orders_paid_timestamp']);
				}
				$orderDetailsItem.='<div class="col-md-9">
					<input type="text" name="tx_multishop_pi1[orders_paid_timestamp_visual]" class="form-control" id="orders_paid_timestamp_visual" value="'.htmlspecialchars($orders_paid_timestamp_visual).'">
					<input type="hidden" name="tx_multishop_pi1[orders_paid_timestamp]" id="orders_paid_timestamp" value="'.htmlspecialchars($orders_paid_timestamp).'">
					</div>';

				$orderDetailsItem.='</div>';

				$orderDetailsItem.='<div class="form-group row">';
				$orderDetailsItem.='<label class="control-label col-md-3">'.$this->pi_getLL('payment_method').': </label>';
				//if ($this->ms['MODULES']['ORDER_EDIT']) {
					if (is_array($payment_methods) and count($payment_methods)) {
						$optionItems=array();
						$dontOverrideDefaultOption=0;
						foreach ($payment_methods as $code=>$item) {
							if (!$item['status']) {
								$item['name'].=' ('.$this->pi_getLL('hidden_in_checkout').')';
							}
							$optionItems[]='<option value="'.$item['id'].'"'.($code==$order_data['payment_method'] ? ' selected' : '').'>'.htmlspecialchars($item['name']).'</option>';
							if ($code==$order_data['payment_method']) {
								$dontOverrideDefaultOption=1;
							}
						}
						if (empty($order_data['payment_method'])) {
							$dontOverrideDefaultOption=1;
						}
						if ($dontOverrideDefaultOption) {
							$optionItems=array_merge(array('<option value="">'.ucfirst($this->pi_getLL('choose')).'</option>'), $optionItems);
						} else {
							$optionItems=array_merge(array('<option value="">'.($order_data['payment_method_label'] ? $order_data['payment_method_label'] : $order_data['payment_method']).'</option>'), $optionItems);
						}
						$orderDetailsItem.='<div class="col-md-9"><select name="payment_method" id="payment_method_sb_listing" class="form-control">'.implode("\n", $optionItems).'</select></div>';
					} else {
						$orderDetailsItem.='<div class="col-md-9">'.($order_data['payment_method_label'] ? $order_data['payment_method_label'] : $order_data['payment_method']).'</div>';
					}
				/*} else {
					$orderDetailsItem.='<div class="col-md-9">'.($order_data['payment_method_label'] ? $order_data['payment_method_label'] : $order_data['payment_method']).'</div>';
				}*/
				$orderDetailsItem.='</div>';


				$return_data['payment_method_date_purchased']=$orderDetailsItem;
			}
		}
		echo json_encode($return_data);
		exit();
		breaks;
	case 'update_paid_status_save_popup_value':
		$return_data=array();
		$order_id=$this->post['tx_multishop_pi1']['order_id'];
		$return_data['status']='NOTOK';
		if (is_numeric($order_id) && $order_id>0) {
			$order=mslib_fe::getOrder($order_id);
			if ($order['orders_id']) {
				if ($this->post['tx_multishop_pi1']['action']=='update_selected_orders_to_paid') {
					$date_paid=strtotime($this->post['tx_multishop_pi1']['date_paid']);
					$payment_id=$this->post['tx_multishop_pi1']['payment_id'];
					//
					if (mslib_fe::updateOrderStatusToPaid($order_id)) {
						$return_data['info']=array(
							'status'=>'info',
							'message'=>'Order '.$orders_id.' has been updated to paid.'
						);
						//
						if (is_numeric($payment_id) && $payment_id>0) {
							$payment_method=mslib_fe::getPaymentMethod($payment_id);
							$updateArray=array();
							$updateArray['payment_method_costs']=$payment_method['handling_costs'];
							$updateArray['payment_method']=$payment_method['code'];
							$updateArray['payment_method_label']=$payment_method['name'];
							$updateArray['orders_last_modified']=time();
							$updateArray['orders_paid_timestamp']=$date_paid;
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$order_id.'\'', $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							//
							require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
							$mslib_order=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
							$mslib_order->init($this);
							$mslib_order->repairOrder($order_id);
						}
						$return_data['status']='OK';
					}
				} else {
					$continue=1;
					//hook to let other plugins further manipulate the replacers
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['updateOrderPaidStatusToUnpaidPreProc'])) {
						$params = array(
							'continue' => &$continue
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['updateOrderPaidStatusToUnpaidPreProc'] as $funcRef) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
						}
					}
					if ($continue) {
						//end of hook to let other plugins further manipulate the replacers
						$updateArray=array('paid'=>0);
						$updateArray['orders_last_modified']=time();
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id='.$order_id, $updateArray);
						$return_data['status']='NOTOK';
						if ($res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
							$return_data['status']='OK';
						}
						//hook to let other plugins further manipulate the replacers
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['updateOrderPaidStatusToUnpaidPostProc'])) {
							$params = array(
									'return_data' => &$return_data,
									'order_id' => $order_id
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['updateOrderPaidStatusToUnpaidPostProc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						//end of hook to let other plugins further manipulate the replacers
					}
				}
			}
		}
		echo json_encode($return_data);
		exit();
		break;
	case 'update_invoice_paid_status_save_popup_value':
		$return_data=array();
		$order_id=$this->post['tx_multishop_pi1']['order_id'];
		$invoice_id=$this->post['tx_multishop_pi1']['invoice_id'];
		$invoice_nr=$this->post['tx_multishop_pi1']['invoice_nr'];
		$return_data['status']='NOTOK';

		$return_data['status']='NOTOK';
		if (is_numeric($invoice_id)) {
			$invoice=mslib_fe::getInvoice($invoice_id, 'id');
			if ($invoice['id']) {
				$order=mslib_fe::getOrder($invoice['orders_id']);
				if ($order['orders_id']) {
					$date_paid=strtotime($this->post['tx_multishop_pi1']['date_paid']);
					$payment_id=$this->post['tx_multishop_pi1']['payment_id'];
					//
					if (is_numeric($payment_id) && $payment_id>0) {
						$payment_method=mslib_fe::getPaymentMethod($payment_id);
						$updateArray=array();
						$updateArray['payment_method_costs']=$payment_method['handling_costs'];
						$updateArray['payment_method']=$payment_method['code'];
						$updateArray['payment_method_label']=$payment_method['name'];
						$updateArray['orders_last_modified']=time();
						$updateArray['orders_paid_timestamp']=$date_paid;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$order_id.'\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						//
						require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
						$mslib_order=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
						$mslib_order->init($this);
						$mslib_order->repairOrder($order_id);
					}
					//
					if ($this->post['tx_multishop_pi1']['action']=='update_selected_invoices_to_paid') {
						if (mslib_fe::updateOrderStatusToPaid($order['orders_id'])) {
							/*
							$return_data['info']=array(
								'status'=>'info',
								'message'=>'Invoice '.$invoice['invoice_id'].' has been updated to paid.'
							);
							*/
							$return_data['status']='OK';
						}
					} else {
						// Update to unpaid
						$continue=1;
						//hook to let other plugins further manipulate the replacers
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['updateOrderPaidStatusToUnpaidPreProc'])) {
							$params = array(
								'continue' => &$continue
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['updateOrderPaidStatusToUnpaidPreProc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						if ($continue) {
							$updateArray=array('paid'=>0);
							$updateArray['orders_last_modified']=time();
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id='.$order['orders_id'], $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							$updateArray=array('paid'=>0);
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id='.$invoice['id'], $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							$return_data['status']='OK';
						} else {
							$return_data['error']=array(
								'status'=>'error',
								'message'=>'Invoice '.$invoice['invoice_id'].' has not been updated to paid.'
							);
						}
					}
				} else {
					// this invoice has no belonging order. This could be true in specific cases so just update the invoice to not paid.
					if ($this->post['tx_multishop_pi1']['action']=='update_selected_invoices_to_paid') {
						$updateArray=array('paid'=>1);
					} else {
						$updateArray=array('paid'=>0);
					}
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id='.$invoice['id'], $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					$return_data['status']='OK';
				}
			}
		}
		echo json_encode($return_data);
		exit();
		break;
	case 'sort_orders_products':
		if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
			$no=1;
			foreach ($this->post['orders_products_id'] as $orders_products_id) {
				if (is_numeric($orders_products_id)) {
					$where="orders_products_id = ".$orders_products_id."";
					$updateArray=array(
						'sort_order'=>$no
					);
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', $where, $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					$no++;
				}
			}
		}
		exit();
		breaks;
	case 'get_products':
		$from=array();
		$from[]='tx_multishop_products p';
		$from[]='tx_multishop_products_description pd';
		//
		$where=array();
		$where[]='p.products_id=pd.products_id';
		$where[]='pd.language_id=\''.$this->sys_language_uid.'\'';
		//
		$skip_db=false;
		$limit=50;
		$categories_id=0;
		if (isset($this->get['q']) && !empty($this->get['q'])) {
			if (strpos($this->get['q'], '||catid=')!==false) {
				$tmp_value=explode('||catid=', $this->get['q']);
				$this->get['q']=$tmp_value[0];
				$categories_id=$tmp_value[1];
			}
			if (!is_numeric($this->get['q'])) {
				$where[]='(pd.products_name like \'%'.addslashes($this->get['q']).'%\' or p.sku_code like \'%'.addslashes($this->get['q']).'%\')';
			} else {
				$where[]='(pd.products_name like \'%'.addslashes($this->get['q']).'%\' or p.sku_code like \'%'.addslashes($this->get['q']).'%\' or p.products_id = \''.addslashes($this->get['q']).'\')';
			}
			$limit='';
		} else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
			$where[]='p.products_id = \''.addslashes($this->get['preselected_id']).'\'';
		}
		if (is_numeric($categories_id) && $categories_id>0) {
			$from[]='tx_multishop_products_to_categories p2c';
			$where[]='p2c.categories_id=\''.$categories_id.'\' and p2c.is_deepest=1 and p2c.products_id=p.products_id';
		}
		//hook to let other plugins further manipulate the replacers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['getProductsFilterPostProc'])) {
			$params = array(
				'from' => &$from,
				'where' => &$where
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_edit_order.php']['getProductsFilterPostProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		$str=$GLOBALS ['TYPO3_DB']->SELECTquery('p.*, pd.products_name', // SELECT ...
			implode(', ', $from), // FROM ...
			implode(' and ', $where), // WHERE.
			'p.products_id', // GROUP BY...
			'pd.products_name asc, p.products_status asc', // ORDER BY...
			$limit // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$data=array();
		$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($num_rows) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				if (!empty($row['products_name'])) {
                    if ($row['products_status']<1) {
						$row['products_name'].=' [disabled]';
					}
					if (isset($row['is_hidden'])) {
						if (!$row['is_hidden']) {
							$data[]=array(
								'id'=>$row['products_id'],
								'text'=>$row['products_name'],
                                'sku_code'=>$row['sku_code']
							);
						}
					} else {
						$data[]=array(
							'id'=>$row['products_id'],
							'text'=>$row['products_name'],
                            'sku_code'=>$row['sku_code']
						);
					}
				}
			}
		} else {
			if ($this->ms['MODULES']['DISABLE_EDIT_ORDER_ADD_MANUAL_PRODUCT']=='0') {
				if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
					$data[] = array(
						'id' => $this->get['preselected_id'],
						'text' => $this->get['preselected_id']
					);
				} else {
					$data[] = array(
						'id' => $this->get['q'],
						'text' => $this->get['q']
					);
				}
			}
		}
		$content=json_encode($data);
		break;
	case 'get_attributes_options':
		$from=array();
		$from[]='tx_multishop_products_options po';
		$where=array();
		$where[]="po.language_id = '".$this->sys_language_uid."'";
		$skip_db=false;
		$pid=0;
		if (strpos($this->get['q'], '||pid=')!==false) {
			list($search_term, $tmp_pid) = explode('||pid=', $this->get['q']);
			$this->get['q']=$search_term;
			$pid=$tmp_pid;
		}
		if (isset($this->get['q']) && !empty($this->get['q'])) {
			if (!is_numeric($this->get['q'])) {
				$where[]="po.products_options_name like '%".addslashes($this->get['q'])."%'";
			} else {
				$where[]="po.products_options_id = '".addslashes($this->get['q'])."'";
			}
		} else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
			$where[]="po.products_options_id = '".addslashes($this->get['preselected_id'])."'";
		}
		if (is_numeric($pid) && $pid>0) {
			$from[]='tx_multishop_products_attributes pa';
			$where[]='pa.products_id=\''.$pid.'\' and pa.options_id=po.products_options_id';
		}
		$str=$GLOBALS ['TYPO3_DB']->SELECTquery('po.*', // SELECT ...
			implode(', ', $from), // FROM ...
			implode(' and ', $where), // WHERE.
			'po.products_options_id', // GROUP BY...
			'po.sort_order', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$data=array();
		$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($num_rows) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$data[]=array(
					'id'=>$row['products_options_id'],
					'text'=>$row['products_options_name']
				);
			}
		} else {
			if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
				$data[]=array(
					'id'=>$this->get['preselected_id'],
					'text'=>$this->get['preselected_id']
				);
			} else {
				$data[]=array(
					'id'=>$this->get['q'],
					'text'=>$this->get['q']
				);
			}
		}
		$content=json_encode($data);
		break;
	case 'get_attributes_values':
		$where=array();
		$where[]="optval.language_id = '".$this->sys_language_uid."'";
		$skip_db=false;
		if (isset($this->get['q']) && !empty($this->get['q'])) {
			if (strpos($this->get['q'], '||optid')!==false) {
				list($search_term, $tmp_optid)=explode('||', $this->get['q']);
				$search_term=trim($search_term);
				if (!empty($search_term)) {
					$where_str='';
					if (isset($tmp_optid) && !empty($tmp_optid)) {
						list(, $optid)=explode('=', $tmp_optid);
						if (is_numeric($optid)) {
							$where_str="optval2opt.products_options_id = '".$optid."'";
						}
					}
					if (!empty($where_str)) {
						$where[]="(optval.products_options_values_name like '%".addslashes($search_term)."%' or (".$where_str."))";
					} else {
						$where[]="optval.products_options_values_name like '%".addslashes($search_term)."%'";
					}
				} else {
					if (isset($tmp_optid) && !empty($tmp_optid)) {
						list(, $optid)=explode('=', $tmp_optid);
						if (is_numeric($optid)) {
							$where[]="(optval2opt.products_options_id = '".$optid."')";
						}
					}
				}
			} else {
				$where[]="optval.products_options_values_name like '%".addslashes($this->get['q'])."%'";
			}
		} else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
			if (is_numeric($this->get['preselected_id'])) {
				$where[]="optval2opt.products_options_values_id = '".$this->get['preselected_id']."'";
			} else {
				$where[]="optval.products_options_values_name like '%".addslashes($this->get['preselected_id'])."%'";
			}
		}
		$str=$GLOBALS ['TYPO3_DB']->SELECTquery('optval.*', // SELECT ...
			'tx_multishop_products_options_values as optval left join tx_multishop_products_options_values_to_products_options as optval2opt on optval2opt.products_options_values_id = optval.products_options_values_id', // FROM ...
			implode(' and ', $where), // WHERE.
			'optval.products_options_values_id', // GROUP BY...
			'optval2opt.sort_order', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$data=array();
		$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($num_rows) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$data[]=array(
					'id'=>$row['products_options_values_id'],
					'text'=>$row['products_options_values_name']
				);
			}
		} else {
			if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
				$data[]=array(
					'id'=>$this->get['preselected_id'],
					'text'=>$this->get['preselected_id']
				);
			} else {
				$data[]=array(
					'id'=>$this->get['q'],
					'text'=>$this->get['q']
				);
			}
		}
		$content=json_encode($data);
		break;
	// all calculation are based on excluding vat
	case 'get_product_discount_price':
		$return_data=array();
		$discount_percentage=$this->get['discount_percentage'];
		$current_price=$this->get['current_price'];

		$return_data['status']='NOTOK';
		if (isset($discount_percentage) && is_numeric($discount_percentage) && $discount_percentage>0 && $current_price>0) {
			$discount_amount=mslib_fe::taxDecimalCrop(($current_price*$discount_percentage) / 100, 2, false);
			$return_data['status']='OK';
			$return_data['discount_amount'] = $discount_amount;
			$return_data['price_after_discount'] = ($current_price-$discount_amount);
			$return_data['price_after_discount_format'] = mslib_fe::amount2Cents(($current_price-$discount_amount));
		}
		$content=json_encode($return_data);
		break;
}
echo $content;
exit();
?>