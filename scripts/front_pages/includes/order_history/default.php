<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (mslib_fe::loggedin()) {
	switch ($this->get['tx_multishop_pi1']['page_section']) {
		case 'order_details':
			if (is_numeric($this->get['tx_multishop_pi1']['orders_id']) and $GLOBALS["TSFE"]->fe_user->user['uid']) {
				$order=mslib_fe::getOrder($this->get['tx_multishop_pi1']['orders_id']);
				if ($order['customer_id']==$GLOBALS["TSFE"]->fe_user->user['uid']) {
					$content.='<h1>'.$this->pi_getLL('orders_id').': '.$order['orders_id'].'</h1>';
					$content.=mslib_fe::printOrderDetailsTable($order, 'order_history_site');
					$content.='
					<div id="bottom-navigation">
						<a href="'.mslib_fe::typolink('', '').'" class="back_button">'.$this->pi_getLL('back').'</a>
						<div id="navigation"> 							
							<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[re-order]=1&tx_multishop_pi1[orders_id]='.$order['orders_id']).'"><input type="submit" id="submit" value="'.htmlspecialchars($this->pi_getLL('re-order')).'" /></a>
						</div>
					</div>					
					';
				}
			}
			break;
		default:
			if (is_numeric($this->get['tx_multishop_pi1']['orders_id']) and $this->get['tx_multishop_pi1']['re-order']) {
				$order=mslib_fe::getOrder($this->get['tx_multishop_pi1']['orders_id']);
				if ($order['customer_id']==$GLOBALS['TSFE']->fe_user->user['uid']) {
					foreach ($order['products'] as $product) {
						$this->post=array();
						$this->post['products_id']=$product['products_id'];
						$this->post['quantity']=number_format($product['qty']);
						if (is_array($product['attributes']) and count($product['attributes'])) {
							foreach ($product['attributes'] as $attribute) {
								if ($attribute['products_options_values_id']) {
									$value=$attribute['products_options_values_id'];
								} else {
									$value=$attribute['products_options_values'];
								}
								$this->post['attributes'][$attribute['products_options_id']][]=$value;
							}
						}
						require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
						$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
						$mslib_cart->init($this);
						$mslib_cart->updateCart();
					}
					header('Location: '.t3lib_div::locationHeaderUrl($this->FULL_HTTP_URL.mslib_fe::typolink($this->conf['shoppingcart_page_pid'], '&tx_multishop_pi1[page_section]=shopping_cart')));
				}
			}
			$this->ms['MODULES']['ORDERS_LISTING_LIMIT']=50;
			if (is_numeric($this->get['p'])) {
				$p=$this->get['p'];
			}
			if ($p>0) {
				$offset=(((($p)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])));
			} else {
				$p=0;
				$offset=0;
			}
			$tmp='';
			$filter=array();
			$from=array();
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			$select[]='o.*, osd.name as orders_status';
			$orderby[]='o.orders_id desc';
			$filter[]='o.is_proposal=0';
			$filter[]='o.deleted=0';
			$filter[]='o.customer_id='.$GLOBALS['TSFE']->fe_user->user['uid'];
			$pageset=mslib_fe::getOrdersPageSet($filter, $offset, $this->get['limit'], $orderby, $having, $select, $where, $from);
			$tmporders=$pageset['orders'];
			if (!$this->hideHeader) {
				$tmp.='<h2>'.$this->pi_getLL('account_order_history').'</h2>';
			}
			if ($pageset['total_rows']>0) {
				$tmp.='<table id="account_orders_history_listing">';
				$tmp.='<tr>
				<th class="cell_orders_id">'.$this->pi_getLL('orders_id').'</th>
				<th class="cell_amount">'.$this->pi_getLL('amount').'</th>
				<th class="cell_date">'.$this->pi_getLL('order_date').'</th>
				';
				if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
					$tmp.='<th class="cell_invoice">'.$this->pi_getLL('invoice').'</th>';
				}
				//	$tmp.='<th class="cell_shipping_method">'.$this->pi_getLL('shipping_method').'</th>';
				//	$tmp.='<th class="cell_payment_method">'.$this->pi_getLL('payment_method').'</th>';
				$tmp.='<th class="cell_order_status">'.$this->pi_getLL('status').'</th>';
				$tmp.='<th class="cell_action">&nbsp;</th>';
				$tmp.='</tr>';
				$tr_type='even';
				foreach ($tmporders as $order) {
					if (!$tr_type or $tr_type=='even') {
						$tr_type='odd';
					} else {
						$tr_type='even';
					}
					$tmp.='<tr class="'.$tr_type.'">';
					$tmp.='<td align="right" nowrap class="cell_orders_id">
					<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=order_details&tx_multishop_pi1[orders_id]='.$order['orders_id']).'">'.$order['orders_id'].'</a></td>';
					$tmp.='<td align="right" nowrap class="cell_amount">'.mslib_fe::amount2Cents(mslib_fe::getOrderTotalPrice($order['orders_id'])).'</td>';
					$tmp.='<td align="center" nowrap class="cell_date">'.strftime("%x", $order['crdate']).'</td>';
					if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
						$tmp.='<td align="center" nowrap class="cell_invoice">
						';
						$invoice=mslib_fe::getInvoice($order['orders_id'], 'orders_id');
						if ($invoice['id']) {
							$tmp.='<a href="'.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'" target="_blank" class="msfront_download_invoice" title="download invoice">'.$this->pi_getLL('download').'</a>';
						}
						$tmp.='
						</td>';
					}
					//		$tmp.='<td align="left" nowrap>'.$order['shipping_method_label'].'</td>';
					//		$tmp.='<td align="left" nowrap>'.$order['payment_method_label'].'</td>';
					$tmp.='<td align="left" nowrap class="cell_order_status">'.$order['orders_status'].'</td>';
					$tmp.='<td align="center" nowrap class="cell_action">
					';
					$tmp.='<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[re-order]=1&tx_multishop_pi1[orders_id]='.$order['orders_id']).'" class="msfront_reorder" title="'.htmlspecialchars($this->pi_getLL('re-order')).'">'.$this->pi_getLL('re-order').'</a>';
					$tmp.='
					</td>';
					$tmp.='</tr>';
				}
				$tmp.='</table>';
				// pagination
				if (!$this->hidePagination and $pageset['total_rows']>$this->ms['MODULES']['ORDERS_LISTING_LIMIT']) {
					$tmp.='<table id="pagenav_container">
					<tr>
					 <td class="pagenav_first"><table><tr><td>';
					if ($p>0) {
						$tmp.=mslib_fe::flexibutton('<a class="pagination_button" href="'.mslib_fe::typolink('', ''.mslib_fe::tep_get_all_get_params(array(
									'id',
									'p',
									'Submit',
									'tx_multishop_pi1[action]',
									'clearcache'
								))).'">'.$this->pi_getLL('first').'</a>');
					} else {
						$tmp.='&nbsp;';
					}
					$tmp.='</td></tr></table></td><td class="pagenav_previous"><table><tr><td>';
					if ($p>0) {
						if (($p-1)>0) {
							$tmp.=mslib_fe::flexibutton('<a class="pagination_button" href="'.mslib_fe::typolink('', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
										'id',
										'p',
										'Submit',
										'tx_multishop_pi1[action]',
										'clearcache'
									))).'">'.$this->pi_getLL('previous').'</a>', 'pagenav_previous');
						} else {
							$tmp.=mslib_fe::flexibutton('<a class="pagination_button" href="'.mslib_fe::typolink('', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
										'id',
										'p',
										'Submit',
										'tx_multishop_pi1[action]',
										'clearcache'
									))).'">'.$this->pi_getLL('previous').'</a>', 'pagenav_previous');
						}
					} else {
						$tmp.='&nbsp;';
					}
					$tmp.='</td></tr></table></td><td class="pagenav_next"><table><tr><td>';
					if ((($p+1)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])<$pageset['total_rows']) {
						$tmp.=mslib_fe::flexibutton('<a class="pagination_button" href="'.mslib_fe::typolink('', 'p='.($p+1).'&'.mslib_fe::tep_get_all_get_params(array(
									'id',
									'p',
									'Submit',
									'tx_multishop_pi1[action]',
									'clearcache'
								))).'">'.$this->pi_getLL('next').'</a>', 'pagenav_next');
					} else {
						$tmp.='&nbsp;';
					}
					$tmp.='</td></tr></table></td><td class="pagenav_last"><table><tr><td>';
					if ((($p+1)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])<$pageset['total_rows']) {
						$lastpage=floor(($pageset['total_rows']/$this->ms['MODULES']['ORDERS_LISTING_LIMIT']));
						$tmp.=mslib_fe::flexibutton('<a class="pagination_button" href="'.mslib_fe::typolink('', 'p='.$lastpage.'&'.mslib_fe::tep_get_all_get_params(array(
									'id',
									'p',
									'Submit',
									'tx_multishop_pi1[action]',
									'clearcache'
								))).'">'.$this->pi_getLL('last').'</a>', 'pagenav_last');
					} else {
						$tmp.='&nbsp;';
					}
					$tmp.='</td></tr></table></td></tr>
					</table>';
				}
				// pagination eof	
			} else {
				$tmp.=$this->pi_getLL('no_orders_found').'.';
			}
			$content.=$tmp;
			break;
	}
}
?>