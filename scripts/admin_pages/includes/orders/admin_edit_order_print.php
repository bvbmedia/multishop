<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (is_numeric($this->get['orders_id'])) {
	$invoice=mslib_fe::getOrderInvoice($this->get['orders_id']);
	$order=mslib_fe::getOrder($this->get['orders_id']);
	$orders_tax_data=$order['orders_tax_data'];
	if ($order['orders_id']) {
		$tmpcontent.='
		<div class="float_right" id="msadmin_tools_nav">
			<ul>
				<li>
					<a href="#" class="multishop_print_icon"><span>Print</span></a>
					<script>
					jQuery(document).ready(function($) {
						$("#barkode_image").hide();

						$(".multishop_print_icon").click(function(e){
							e.preventDefault();
							$("#msadmin_tools_nav").hide();
							$("#tx_multishop_admin_header_wrapper").hide();
							$("#tx_multishop_admin_footer_wrapper").hide();
							$("#msadmin_footer").hide();
							$("#barkode_image").show();
							window.print();
							// revert previous state after print
							$("#msadmin_tools_nav").show();
							$("#tx_multishop_admin_header_wrapper").show();
							$("#tx_multishop_admin_footer_wrapper").show();
							$("#msadmin_footer").show();
							$("#barkode_image").hide();
							return false;
					 	});
					});
					</script>
				</li>';
		if ($this->get['print']=='invoice') {
			$tmpcontent.='<li>
					<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'" target="_blank" class="multishop_pdf_icon"><span>PDF</span></a>
				</li>';
		}
		$tmpcontent.='
			</ul>
		</div>
		<img id="barkode_image" src="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=generateBarkode&tx_multishop_pi1[orders_id]='.$order['orders_id'].'&tx_multishop_pi1[string]='.$order['orders_id']).'" alt="'.$order['orders_id'].'" title="'.$order['orders_id'].'">
		';
		//		<div class="barkode">*'.$order['orders_id'].'*</div>
		// count total products
		if ($this->get['print']=='invoice') {
			$invheader='<table cellspacing="0" cellpadding="0" width="100%" class="invoice_header">
				<tr>
					<td width="50%">'.$this->pi_getLL('invoice').'</td>
					<td>&nbsp;</td>
				</tr>
			</table>';
			$invnumber='<tr>
			<td width="150" align="right">'.$invheader=$this->pi_getLL('invoice_number').':</td>
			<td width="150">'.$invoice['invoice_id'].'
			</td>
			<td width="" align="right">&nbsp;</td>
			<td>&nbsp;</td>
			</tr>';
		} else {
			$invheader='<table cellspacing="0" cellpadding="0" width="100%" class="invoice_header">
				<tr>
					<td width="50%">'.$this->pi_getLL('packing_list').'</td>
					<td>&nbsp;</td>
				</tr>
			</table>';
			$invnumber='';
		}
		$content_cms=mslib_fe::getCMScontent('invoice_header', $GLOBALS['TSFE']->sys_language_uid);
		// count eof products
		$tmpcontent.='<form id="admin_product_edit_" class="admin_product_edit"><div style="display: block;" id="Order_Details" class="tab_content">';
		if ($content_cms[0]['content']) {
			$tmpcontent.='
				<div id="logo-invoice"><div id="logo-invoice-img">'.$content_cms[0]['content'].'</div></div>
				<div style="display: block;" id="Order_Details" class="tab_content"><h1>'.$invheader.'</h1>';
		}
		$tmpcontent.='
		<fieldset class="tabs-fieldset">
		<legend>'.$this->pi_getLL('address_details').'</legend>
		<table width="100%">
			<tr>
				<td width="50%" valign="top">
					<table>
					<tr>
						<td width="150" align="left" valign="top">'.$this->pi_getLL('billing_details').':</td>
						<td>
							<strong>'.$order['billing_company'].'</strong><br />
							'.$order['billing_name'].'<br />
							'.$order['billing_address'].'<br />
							'.$order['billing_zip'].' '.$order['billing_city'].'<br />
							'.$order['billing_country'].'<br /><br />
							'.$order['billing_email'].'<br />
							'.$order['billing_telephone'].'<BR />
							'.$order['billing_mobile'].'<BR />
							'.$order['billing_fax'].'<BR />
						</td>
					</tr>
					</table>
				</td>
				<td width="50%" valign="top">
					<table>
					<tr>
						<td width="150" align="left" valign="top">'.$this->pi_getLL('delivery_details').':</td>
						<td>
							<strong>'.$order['delivery_company'].'</strong><br />
							'.$order['delivery_name'].'<br />
							'.$order['delivery_address'].'<br />
							'.$order['delivery_zip'].' '.$order['delivery_city'].'<br />
							'.$order['delivery_country'].'<br /><br />
							'.$order['delivery_email'].'<br />
							'.$order['delivery_telephone'].'<BR />
							'.$order['delivery_mobile'].'<BR />
							'.$order['delivery_fax'].'<BR />
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
		<table>
		'.$invnumber.'
		<tr>
			<td width="" align="right">'.$this->pi_getLL('order_number').':</td>
			<td width="150">'.$order['orders_id'].'</td>
			<td width="" align="right">'.$this->pi_getLL('shipping_method').':</td>
			<td>'.$order['shipping_method_label'].'</td>
		</tr>
		<tr>
			<td width="150" align="right">'.$this->pi_getLL('order_date').':</td>
			<td width="150">'.strftime("%x", $order['crdate']).'</td>
			<td width="150" align="right">'.$this->pi_getLL('payment_method').':</td>
			<td>'.$order['payment_method_label'].'</td>
		</tr>
		</table>
		</fieldset>
		<fieldset class="tabs-fieldset">
		<legend>'.$this->pi_getLL('product_details').'</legend>';
		//print_r($order); die();
		$tr_type='even';
		$tmpcontent.='<table class="msadmin_border" width="100%" border="1" cellspacing="0" cellpadding="2">';
		if ($this->get['print']=='invoice') {
			$tmpcontent.='<tr><th class="cell_qty align_right">'.ucfirst($this->pi_getLL('qty')).'</th>
						  <th class="cell_products_id align_left">'.$this->pi_getLL('products_id').'</th>
						  <th class="cell_products_model align_left">'.$this->pi_getLL('products_model').'</th>
						  <th class="cell_products_name align_left">'.$this->pi_getLL('products_name').'</th>';
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.='<th class="cell_products_vat align_right">'.$this->pi_getLL('vat').'</th>
							  <th class="cell_products_normal_price align_right">'.$this->pi_getLL('normal_price').'</th>
						  	  <th class="cell_products_final_price align_right">'.$this->pi_getLL('final_price_inc_vat').'</th>';
			} else {
				$tmpcontent.='<th class="cell_products_normal_price align_right">'.$this->pi_getLL('normal_price').'</th>
							  <th class="cell_products_vat align_right">'.$this->pi_getLL('vat').'</th>
						  	  <th class="cell_products_final_price align_right">'.$this->pi_getLL('final_price_ex_vat').'</th>';
			}
			$tmpcontent.='</tr>';
		} else {
			$tmpcontent.='<tr><th class="cell_qty align_right">'.$this->pi_getLL('qty').'</th>
						  <th class="cell_products_id align_left">'.$this->pi_getLL('products_id').'</th>
						  <th class="cell_products_model align_left">'.$this->pi_getLL('products_model').'</th>
						  <th class="cell_products_name align_left">'.$this->pi_getLL('products_name').'</th>
						  </tr>';
		}
		$total_tax=0;
		foreach ($order['products'] as $product) {
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$tmpcontent.='<tr class="'.$tr_type.'">';
			$tmpcontent.='<td align="right" class="cell_products_qty">'.number_format($product['qty']).'</td>';
			$tmpcontent.='<td align="right" class="cell_products_id">'.$product['products_id'].'</td>';
			$tmpcontent.='<td align="left" class="cell_products_model">'.$product['products_model'].'</td>';
			$product_tmp=mslib_fe::getProduct($product['products_id']);
			if ($this->ms['MODULES']['DISPLAY_PRODUCT_IMAGE_IN_ADMIN_PACKING_SLIP'] and $product_tmp['products_image']) {
				$tmpcontent.='<td align="left" class="cell_products_name"><strong>';
				$tmpcontent.='<img src="'.mslib_befe::getImagePath($product_tmp['products_image'], 'products', '50').'"> ';
				$tmpcontent.=$product['products_name'];
			} else {
				$tmpcontent.='<td align="left" class="cell_products_name"><strong>'.$product['products_name'];
			}
			if ($product['products_article_number']) {
				$tmpcontent.=' ('.$product['products_article_number'].')';
			}
			$tmpcontent.='</strong>';
			if (!empty($product['ean_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_ean').': '.$product['ean_code'];
			}
			if (!empty($product['sku_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_sku').': '.$product['sku_code'];
			}
			if (!empty($product['vendor_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_vendor_code').': '.$product['vendor_code'];
			}
			$tmpcontent.='</td>';
			if ($this->get['print']=='invoice') {
				if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
					$tmpcontent.='<td align="right" class="cell_products_vat">'.str_replace('.00', '', number_format($product['products_tax'], 2)).'%</td>';
					$tmpcontent.='<td align="right" class="cell_products_normal_price">'.mslib_fe::amount2Cents($product['final_price']+$product['products_tax_data']['total_tax'], 0).'</td>';
					$tmpcontent.='<td align="right" class="cell_products_final_price">'.mslib_fe::amount2Cents(($product['qty']*($product['final_price']+$product['products_tax_data']['total_tax'])), 0).'</td>';
				} else {
					$tmpcontent.='<td align="right" class="cell_products_normal_price">'.mslib_fe::amount2Cents($product['final_price'], 0).'</td>';
					$tmpcontent.='<td align="right" class="cell_products_vat">'.str_replace('.00', '', number_format($product['products_tax'], 2)).'%</td>';
					$tmpcontent.='<td align="right" class="cell_products_final_price">'.mslib_fe::amount2Cents(($product['qty']*$product['final_price']), 0).'</td>';
				}
			}
			$tmpcontent.='</tr>';
			if (count($product['attributes'])) {
				foreach ($product['attributes'] as $tmpkey=>$options) {
					if ($options['products_options_values']) {
						$tmpcontent.='<tr class="'.$tr_type.'"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td align="left">'.$options['products_options'].': '.$options['products_options_values'].'</td>';
						//<td align="right">&nbsp;</td><td align="right">&nbsp;</td><td align="right">
						if ($this->get['print']=='invoice') {
							$cell_products_normal_price='';
							$cell_products_vat='';
							$cell_products_final_price='';
							if ($options['options_values_price']>0) {
								if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
									$attributes_price=$options['price_prefix'].$options['options_values_price']+$options['attributes_tax_data']['tax'];
									$total_attributes_price=$attributes_price*$product['qty'];
									$cell_products_normal_price=mslib_fe::amount2Cents($attributes_price, 0);
									$cell_products_final_price=mslib_fe::amount2Cents($total_attributes_price, 0);
								} else {
									$cell_products_normal_price=mslib_fe::amount2Cents(($options['price_prefix'].$options['options_values_price']), 0);
									$cell_products_final_price=mslib_fe::amount2Cents(($options['price_prefix'].$options['options_values_price'])*$product['qty'], 0);
								}
							}
							if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
								$tmpcontent.='<td align="right" class="cell_products_vat">'.$cell_products_vat.'</td>';
								$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$cell_products_normal_price.'</td>';
								$tmpcontent.='<td align="right" class="cell_products_final_price">'.$cell_products_final_price.'</td>';
							} else {
								$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$cell_products_normal_price.'</td>';
								$tmpcontent.='<td align="right" class="cell_products_vat">'.$cell_products_vat.'</td>';
								$tmpcontent.='<td align="right" class="cell_products_final_price">'.$cell_products_final_price.'</td>';
							}
						}
					}
				}
			}
			$tmpcontent.='</tr>';
			// count the vat
			if ($order['final_price'] and $order['products_tax']) {
				$item_tax=$order['qty']*($order['final_price']*$order['products_tax']/100);
				$total_tax=$total_tax+$item_tax;
			}
		}
		$colspan=7;
		if ($this->get['print']=='invoice') {
			$tmpcontent.='<tr><td align="right" colspan="'.$colspan.'">';
			$tmpcontent.='<div class="order_total">';
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.='<div class="account-field">
						<label>'.$this->pi_getLL('sub_total').'</label>
						<span class="order_total_value">'.mslib_fe::amount2Cents($order['orders_tax_data']['sub_total'], 0).'</span>
					</div>';
				$content_vat='<div class="account-field">
						<label>'.$this->pi_getLL('included_vat_amount').'</label>
						<span class="order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0).'</span>
					</div>';
				if ($order['shipping_method_costs']>0) {
					$content_shipping_costs='
						<div class="account-field">
							<label>'.$this->pi_getLL('shipping_costs').'</label>
							<span class="order_total_value">'.mslib_fe::amount2Cents($order['shipping_method_costs']+$order['orders_tax_data']['shipping_tax'], 0).'</span>
						</div>';
				}
				if ($order['payment_method_costs']>0) {
					$content_payment_costs='
						<div class="account-field">
							<label>'.$this->pi_getLL('payment_costs').'</label>
							<span class="order_total_value">'.mslib_fe::amount2Cents($order['payment_method_costs']+$order['orders_tax_data']['payment_tax'], 0).'</span>
						</div>
				';
				}
			} else {
				$tmpcontent.='<div class="account-field">
						<label>'.$this->pi_getLL('sub_total').'</label>
						<span class="order_total_value">'.mslib_fe::amount2Cents($order['subtotal_amount'], 0).'</span>
					</div>';
				$content_vat='<div class="account-field">
						<label>'.$this->pi_getLL('vat').'</label>
						<span class="order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0).'</span>
					</div>';
				if ($order['shipping_method_costs']>0) {
					$content_shipping_costs='
						<div class="account-field">
							<label>'.$this->pi_getLL('shipping_costs').'</label>
							<span class="order_total_value">'.mslib_fe::amount2Cents($order['shipping_method_costs'], 0).'</span>
						</div>';
				}
				if ($order['payment_method_costs']>0) {
					$content_payment_costs='
						<div class="account-field">
							<label>'.$this->pi_getLL('payment_costs').'</label>
							<span class="order_total_value">'.mslib_fe::amount2Cents($order['payment_method_costs'], 0).'</span>
						</div>
				';
				}
			}
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.=$content_shipping_costs;
				$tmpcontent.=$content_payment_costs;
			} else {
				if ($order['orders_tax_data']['shipping_tax'] || $order['orders_tax_data']['payment_tax']) {
					$tmpcontent.=$content_shipping_costs;
					$tmpcontent.=$content_payment_costs;
					$tmpcontent.=$content_vat;
				} else {
					$tmpcontent.=$content_vat;
					$tmpcontent.=$content_shipping_costs;
					$tmpcontent.=$content_payment_costs;
				}
			}
			if ($order['discount']>0) {
				$tmpcontent.='
				<div class="account-field">
					<label>'.$this->pi_getLL('discount').'</label>
					<span class="order_total_value">'.mslib_fe::amount2Cents($order['discount'], 0).'</span>
				</div>
				';
			}
			$tmpcontent.='
			<div class="account-field">
				<label>'.$this->pi_getLL('total').'</label>
				<span class="order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['grand_total'], 0).'</span>
			</div>';
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.=$content_vat;
			}
			$tmpcontent.='</div>';
			$tmpcontent.='</td></tr></table>';
		}
		$tmpcontent.='</table>';
		$tmpcontent.='
		</fieldset>
		'.($order['customer_comments'] ? '
		<fieldset>
					<legend>'.$this->pi_getLL('comments').'</legend>
					'.$order['customer_comments'].'
				</fieldset>
		' : '').'
		</div></form>';
	}
	$content.=$tmpcontent;
}
?>