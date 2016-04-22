<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
	$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']=(int)$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'];
	$jsonData=array();
	$customer_currency=0;
	if (is_numeric($this->post['tx_multishop_pi1']['orders_id'])) {
		$order=mslib_fe::getOrder($this->post['tx_multishop_pi1']['orders_id']);
		if ($order['orders_id']) {
			$jsonData['content']='';
			$jsonData_content='';
			if (count($order['products'])) {
				// address details:
				$jsonData_content.='
				<div class="msAdminTooltipOrderDetailsAddressWrapper">
					<div class="row">
					<div class="col-md-6">
					<div class="msAdminTooltipBillingAddressDetails">
						<h3>'.$this->pi_getLL('billing_details').'</h3>
';
				if ($order['billing_company']) {
					$jsonData_content.=$order['billing_company'].'<br />';
				}
				$customer_edit_link=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]='.$order['customer_id'].'&action=edit_customer', 1);
				$jsonData_content.='<a href="'.$customer_edit_link.'">'.$order['billing_name'].'</a><br />
'.$order['billing_address'].'<br />
'.$order['billing_zip'].' '.$order['billing_city'].' <br />
'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['billing_country']).'<br /><br />
';
				if ($order['billing_email']) {
					$jsonData_content.=$this->pi_getLL('email').': <a href="mailto:'.$order['billing_email'].'">'.$order['billing_email'].'</a><br />';
				}
				if ($order['billing_telephone']) {
					$jsonData_content.=$this->pi_getLL('telephone').': '.$order['billing_telephone'].'<br />';
				}
				if ($order['billing_mobile']) {
					$jsonData_content.=$this->pi_getLL('mobile').': '.$order['billing_mobile'].'<br />';
				}
				if ($order['billing_fax']) {
					$jsonData_content.=$this->pi_getLL('fax').': '.$order['billing_fax'].'<br />';
				}
				$jsonData_content.='
					</div>
					</div>
					<div class="col-md-6">
					<div class="msAdminTooltipDeliveryAddressDetails">
						<h3>'.$this->pi_getLL('delivery_details').'</h3>
';
				if ($order['delivery_company']) {
					$jsonData_content.=$order['delivery_company'].'<br />';
				}
				$jsonData_content.=$order['delivery_name'].'<br />
						'.$order['delivery_address'].'<br />
						'.$order['delivery_zip'].' '.$order['delivery_city'].' <br />
						'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['delivery_country']).'<br /><br />
';
				if ($order['delivery_email']) {
					$jsonData_content.=$this->pi_getLL('email').': <a href="mailto:'.$order['delivery_email'].'">'.$order['delivery_email'].'</a><br />';
				}
				if ($order['delivery_telephone']) {
					$jsonData_content.=$this->pi_getLL('telephone').': '.$order['delivery_telephone'].'<br />';
				}
				if ($order['delivery_mobile']) {
					$jsonData_content.=$this->pi_getLL('mobile').': '.$order['delivery_mobile'].'<br />';
				}
				if ($order['delivery_fax']) {
					$jsonData_content.=$this->pi_getLL('fax').': '.$order['delivery_fax'].'<br />';
				}
				$jsonData_content.='
					</div>
				</div>
				</div>
				';
				$jsonData_content.='
				<table width="100%" class="table table-bordered">
				<thead>
				<tr>
				<th>'.$this->pi_getLL('products_id').'</th>
				<th>'.$this->pi_getLL('qty').'</th>
				<th>'.$this->pi_getLL('products_name').'</th>
				<th>'.$this->pi_getLL('price').'</th>
				<th>'.$this->pi_getLL('total_price').'</th>
				</tr>
				</thead>
				<tbody>
				';
				foreach ($order['products'] as $product) {
					if (!$tr_subtype or $tr_subtype=='even') {
						$tr_subtype='odd';
					} else {
						$tr_subtype='even';
					}
					$where='';
					if (!$product['categories_id']) {
						// fix fold old orders that did not have categories id in orders_products table
						$tmpProduct=mslib_fe::getProduct($product['products_id']);
						$product['categories_id']=$tmpProduct;
					}
					if ($product['categories_id']) {
						// get all cats to generate multilevel fake url
						$level=0;
						$cats=mslib_fe::Crumbar($product['categories_id']);
						$cats=array_reverse($cats);
						$where='';
						if (count($cats)>0) {
							foreach ($cats as $cat) {
								$where.="categories_id[".$level."]=".$cat['id']."&";
								$level++;
							}
							$where=substr($where, 0, (strlen($where)-1));
						}
						// get all cats to generate multilevel fake url eof
						$productLink=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
					} else {
						$productLink='';
					}
					$jsonData_content.='<tr class="'.$tr_subtype.'">
					<td class="text-right"><a href="'.$productLink.'" target="_blank">'.$product['products_id'].'</a></td>
					<td class="text-right">'.round($product['qty'], 13).'</td>
					<td><a href="'.$productLink.'" target="_blank">'.$product['products_name'].'</a></td>';
					if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
						$jsonData_content.='<td class="text-right noWrap">'.mslib_fe::amount2Cents(($product['final_price']+$product['products_tax_data']['total_tax']), $customer_currency).'</td>';
					} else {
						$jsonData_content.='<td class="text-right noWrap">'.mslib_fe::amount2Cents($product['final_price'], $customer_currency).'</td>';
					}
					if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
						$jsonData_content.='<td class="text-right noWrap">'.mslib_fe::amount2Cents($product['qty']*($product['final_price']+$product['products_tax_data']['total_tax']), $customer_currency).'</td>';
					} else {
						$jsonData_content.='<td class="text-right noWrap">'.mslib_fe::amount2Cents($product['qty']*$product['final_price'], $customer_currency).'</td>';
					}
					$jsonData_content.='</tr>';
					if (count($product['attributes'])) {
						foreach ($product['attributes'] as $attributes) {
							$jsonData_content.='<tr class="'.$tr_subtype.'">
							<td class="text-right">&nbsp;</td>
							<td class="text-right">&nbsp;</td>
							<td>'.$attributes['products_options'].': '.$attributes['products_options_values'].'</td>';
							if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
								$jsonData_content.='<td class="text-right noWrap">'.($attributes['price_prefix']=='-' ? '- ' : '').mslib_fe::amount2Cents(($attributes['price_prefix'].$attributes['options_values_price'])+$attributes['attributes_tax_data']['tax'], $customer_currency).'</td>';
							} else {
								$jsonData_content.='<td class="text-right noWrap">'.($attributes['price_prefix']=='-' ? '- ' : '').mslib_fe::amount2Cents($attributes['options_values_price'], $customer_currency).'</td>';
							}
							if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
								$jsonData_content.='<td class="text-right noWrap">'.($attributes['price_prefix']=='-' ? '- ' : '').mslib_fe::amount2Cents($product['qty']*(($attributes['price_prefix'].$attributes['options_values_price'])+$attributes['attributes_tax_data']['tax']), $customer_currency).'</td>';
							} else {
								$jsonData_content.='<td class="text-right noWrap">'.($attributes['price_prefix']=='-' ? '- ' : '').mslib_fe::amount2Cents($product['qty']*$attributes['options_values_price'], $customer_currency).'</td>';
							}
							$jsonData_content.='</tr>';
						}
					}
				}
				$jsonData_content.='
				<tr class="removeTableCellBorder msAdminSubtotalRow">
					<td colspan="4">&nbsp;</td>
				</tr>';
				if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="4" class="text-right">'.$this->pi_getLL('sub_total').'</td>
						<td class="text-right">'.mslib_fe::amount2Cents($order['orders_tax_data']['sub_total'], $customer_currency).'</td>
					</tr>';
				} else {
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="4" class="text-right">'.$this->pi_getLL('sub_total').'</td>
						<td class="text-right">'.mslib_fe::amount2Cents($order['subtotal_amount'], $customer_currency).'</td>
					</tr>';
				}
				if ($order['shipping_method_label']) {
					if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
						$jsonData_content.='
						<tr class="removeTableCellBorder msAdminSubtotalRow">
							<td colspan="4" class="text-right">'.htmlspecialchars($order['shipping_method_label']).'</td>
							<td class="text-right">'.mslib_fe::amount2Cents($order['shipping_method_costs']+$order['orders_tax_data']['shipping_tax'], $customer_currency).'</td>
						</tr>';
					} else {
						$jsonData_content.='
						<tr class="removeTableCellBorder msAdminSubtotalRow">
							<td colspan="4" class="text-right">'.htmlspecialchars($order['shipping_method_label']).'</td>
							<td class="text-right">'.mslib_fe::amount2Cents($order['shipping_method_costs'], $customer_currency).'</td>
						</tr>';
					}
				}
				if ($order['payment_method_label']) {
					if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
						$jsonData_content.='
						<tr class="removeTableCellBorder msAdminSubtotalRow">
							<td colspan="4" class="text-right">'.htmlspecialchars($order['payment_method_label']).'</td>
							<td class="text-right">'.mslib_fe::amount2Cents($order['payment_method_costs']+$order['orders_tax_data']['payment_tax'], $customer_currency).'</td>
						</tr>';
					} else {
						$jsonData_content.='
						<tr class="removeTableCellBorder msAdminSubtotalRow">
							<td colspan="4" class="text-right">'.htmlspecialchars($order['payment_method_label']).'</td>
							<td class="text-right">'.mslib_fe::amount2Cents($order['payment_method_costs'], $customer_currency).'</td>
						</tr>';
					}
				}
				/*if (!$order['payment_method_label']) {
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="3" class="text-right">'.$this->pi_getLL('vat').'</td>
						<td class="text-right">'.mslib_fe::amount2Cents($order['subtotal_tax']).'</td>
					</tr>';
				}*/
				if ($order['discount']>0) {
					$coupon_code='';
					if (!empty($order['coupon_code'])) {
						$coupon_code=' (code: '.$order['coupon_code'].')';
					}
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="4" class="text-right">'.htmlspecialchars($this->pi_getLL('discount')).$coupon_code.'</td>
						<td class="text-right">'.mslib_fe::amount2Cents($order['discount'], $customer_currency).'</td>
					</tr>
					';
				}
				if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']>0) {
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="5"><hr></td>
					</tr>';
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="4" class="text-right"><strong>'.(!$order['orders_tax_data']['total_orders_tax'] ? ucfirst($this->pi_getLL('total_excl_vat')) : ucfirst($this->pi_getLL('total'))).'</strong></td>
						<td class="text-right"><strong>'.mslib_fe::amount2Cents($order['grand_total'], $customer_currency).'</strong></td>
					</tr>';
					//if ($order['payment_method_label']) {
					$jsonData_content.='
						<tr class="removeTableCellBorder msAdminSubtotalRow">
							<td colspan="4" class="text-right">'.$this->pi_getLL('included_vat_amount').'</td>
							<td class="text-right">'.mslib_fe::amount2Cents($order['subtotal_tax'], $customer_currency).'</td>
						</tr>';
					//}
				} else {
					//if ($order['payment_method_label']) {
					$jsonData_content.='
						<tr class="removeTableCellBorder msAdminSubtotalRow">
							<td colspan="4" class="text-right">'.$this->pi_getLL('vat').'</td>
							<td class="text-right">'.mslib_fe::amount2Cents($order['orders_tax_data']['total_orders_tax'], $customer_currency).'</td>
						</tr>';
					//}
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="5"><hr></td>
					</tr>';
					$jsonData_content.='
					<tr class="removeTableCellBorder msAdminSubtotalRow">
						<td colspan="4" class="text-right"><strong>'.ucfirst($this->pi_getLL('total')).'</strong></td>
						<td class="text-right"><strong>'.mslib_fe::amount2Cents($order['grand_total'], $customer_currency).'</strong></td>
					</tr>';
				}
				$jsonData_content.='</tbody></table>
				';
				$extraDetails=array();
				if ($order['cruser_id']) {
					$user=mslib_fe::getUser($order['cruser_id']);
					if ($user['username']) {
						$customer_edit_link=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]='.$user['uid'].'&action=edit_customer');
						$extraDetails['right'][]=$this->pi_getLL('ordered_by').': <strong><a href="'.$customer_edit_link.'">'.$user['username'].'</a></strong><br />';
					}
				}
				if ($order['ip_address']) {
					$extraDetails['right'][]=$this->pi_getLL('ip_address', 'IP address').': <strong>'.$order['ip_address'].'</strong><br />';
				}
				if ($order['http_referer']) {
					$domain=parse_url($order['http_referer']);
					if ($domain['host']) {
						$extraDetails['left'][]=$this->pi_getLL('referrer', 'Referrer').': <strong><a href="'.$order['http_referer'].'" target="_blank" rel="noreferrer">'.$domain['host'].'</a></strong>';
					}
				}
				if (count($extraDetails)) {
					$jsonData_content.='<hr><div class="row">';
					$jsonData_content.='<div id="adminOrderDetailsFooter" class="col-md-6">';
					if (is_array($extraDetails['left']) && count($extraDetails['left'])) {
						$jsonData_content.=implode("", $extraDetails['left']);
					}
					$jsonData_content.='</div><div class="col-md-6 text-right">';
					if (is_array($extraDetails['right']) && count($extraDetails['right'])) {
						$jsonData_content.=implode("", $extraDetails['right']);
					}
					$jsonData_content.='</div>';
				}
				$jsonData_content.='</div>';
			}
			if (!empty($jsonData_content)) {
				$jsonData['title']='<h3 class="popover-title">'.$this->pi_getLL('admin_label_cms_marker_order_number').': '.$order['orders_id'].'</h3>';
				$jsonData['content']='<div class="popover-content">'.$jsonData_content.'</div>';
			}
		} else {
			$jsonData_content='No data.';
			$jsonData['content']=$jsonData_content;
		}
	}
	echo json_encode($jsonData, ENT_NOQUOTES);
}
exit();
?>