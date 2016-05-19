<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (is_numeric($this->get['orders_id'])) {
	$invoice=mslib_fe::getOrderInvoice($this->get['orders_id']);
	if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE'] && $this->ms['MODULES']['INVOICE_PDF_DIRECT_LINK_FROM_ORDERS_LISTING'] && $this->get['print']!='pakbon') {
		header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']));
		exit();
	}
	$order=mslib_fe::getOrder($this->get['orders_id']);
	$orders_tax_data=$order['orders_tax_data'];
	if ($order['orders_id']) {
		$tmpcontent.='<div class="panel panel-default">
			<div class="panel-body">
			<ul id="msadmin_tools_nav" class="pagination">
				<li>
					<a href="#" class="multishop_print_icon"><i class="fa fa-print"></i> Print</a>
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
					<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'" target="_blank" class="multishop_pdf_icon"><i class="fa fa-file-pdf-o"></i> PDF</a>
				</li>';
		}
		if ($this->get['print']=='packing') {
			$tmpcontent.='<li>
					<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_packingslip&tx_multishop_pi1[order_id]='.$this->get['orders_id']).'" target="_blank" class="multishop_pdf_icon"><i class="fa fa-file-pdf-o"></i> PDF</a>
				</li>';
		}
		$tmpcontent.='
			</ul>
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
		<div class="panel panel-default tabs-fieldset">
		<div class="panel-heading"><h3>'.$this->pi_getLL('address_details').'</h3></div>
		<div class="panel-body">
		<table class="table no-mb">
		<thead>
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
							'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['billing_country']).'<br /><br />
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
							'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['delivery_country']).'<br /><br />
							'.$order['delivery_email'].'<br />
							'.$order['delivery_telephone'].'<BR />
							'.$order['delivery_mobile'].'<BR />
							'.$order['delivery_fax'].'<BR />
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</thead>
		</table>
		<table class="table table-striped no-mb">
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
		</div>
		</div>
		<div class="panel panel-default tabs-fieldset">
		<div class="panel-heading"><h3>'.$this->pi_getLL('product_details').'</h3></div><div class="panel-body">';
		//print_r($order); die();
		$tr_type='even';
		$tmpcontent.='<table class="table table-striped table-bordered msadmin_border no-mb">';
		if ($this->get['print']=='invoice') {
			$tmpcontent.='<thead><tr><th class="cellQty">'.ucfirst($this->pi_getLL('qty')).'</th>
						  <th class="cellID">'.$this->pi_getLL('products_id').'</th>
						  <th class="cellModel">'.$this->pi_getLL('products_model').'</th>
						  <th class="cellName">'.$this->pi_getLL('products_name').'</th>';
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.='<th class="cellVat">'.$this->pi_getLL('vat').'</th>
							  <th class="cellPrice">'.$this->pi_getLL('normal_price').'</th>
						  	  <th class="cellSpecialPrice">'.$this->pi_getLL('final_price_inc_vat').'</th>';
			} else {
				$tmpcontent.='<th class="cellPrice">'.$this->pi_getLL('normal_price').'</th>
							  <th class="cellVat">'.$this->pi_getLL('vat').'</th>
						  	  <th class="cellSpecialPrice">'.$this->pi_getLL('final_price_ex_vat').'</th>';
			}
			$tmpcontent.='</tr></thead>';
		} else {
			$tmpcontent.='<thead><tr><th class="cellQty">'.$this->pi_getLL('qty').'</th>
						  <th class="cellID">'.$this->pi_getLL('products_id').'</th>
						  <th class="cellModel">'.$this->pi_getLL('products_model').'</th>
						  <th class="cellName">'.$this->pi_getLL('products_name').'</th>
						  </tr></thead>';
		}
		$total_tax=0;
		foreach ($order['products'] as $product) {
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$tmpcontent.='<tr class="'.$tr_type.'">';
			$tmpcontent.='<td class="cellQty">'.number_format($product['qty']).'</td>';
			$tmpcontent.='<td class="cellID">'.$product['products_id'].'</td>';
			$tmpcontent.='<td class="cellModel">'.$product['products_model'].'</td>';
			$product_tmp=mslib_fe::getProduct($product['products_id']);
			if ($this->ms['MODULES']['DISPLAY_PRODUCT_IMAGE_IN_ADMIN_PACKING_SLIP'] and $product_tmp['products_image']) {
				$tmpcontent.='<td class="cellName"><strong>';
				$tmpcontent.='<img src="'.mslib_befe::getImagePath($product_tmp['products_image'], 'products', '50').'"> ';
				$tmpcontent.=$product['products_name'];
			} else {
				$tmpcontent.='<td class="cellName"><strong>'.$product['products_name'];
			}
			if ($product['products_article_number']) {
				$tmpcontent.=' ('.$product['products_article_number'].')';
			}
			$tmpcontent.='</strong>';
			if ($this->ms['MODULES']['DISPLAY_EAN_IN_ORDER_DETAILS']=='1' && !empty($product['ean_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_ean').': '.$product['ean_code'];
			}
			if ($this->ms['MODULES']['DISPLAY_SKU_IN_ORDER_DETAILS']=='1' && !empty($product['sku_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_sku').': '.$product['sku_code'];
			}
			if ($this->ms['MODULES']['DISPLAY_VENDOR_IN_ORDER_DETAILS']=='1' && !empty($product['vendor_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_vendor_code').': '.$product['vendor_code'];
			}
			$tmpcontent.='</td>';
			if ($this->get['print']=='invoice') {
				if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
					$tmpcontent.='<td class="cellVat">'.str_replace('.00', '', number_format($product['products_tax'], 2)).'%</td>';
					$tmpcontent.='<td class="cellPrice">'.mslib_fe::amount2Cents($product['final_price']+$product['products_tax_data']['total_tax'], 0).'</td>';
					$tmpcontent.='<td class="cellSpecialPrice">'.mslib_fe::amount2Cents(($product['qty']*($product['final_price']+$product['products_tax_data']['total_tax'])), 0).'</td>';
				} else {
					$tmpcontent.='<td class="cellPrice">'.mslib_fe::amount2Cents($product['final_price'], 0).'</td>';
					$tmpcontent.='<td class="cellVat">'.str_replace('.00', '', number_format($product['products_tax'], 2)).'%</td>';
					$tmpcontent.='<td class="cellSpecialPrice">'.mslib_fe::amount2Cents(($product['qty']*$product['final_price']), 0).'</td>';
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
								$tmpcontent.='<td class="cellVat">'.$cell_products_vat.'</td>';
								$tmpcontent.='<td class="cellPrice">'.$cell_products_normal_price.'</td>';
								$tmpcontent.='<td class="cellSpecialPrice">'.$cell_products_final_price.'</td>';
							} else {
								$tmpcontent.='<td class="cellPrice">'.$cell_products_normal_price.'</td>';
								$tmpcontent.='<td class="cellVat">'.$cell_products_vat.'</td>';
								$tmpcontent.='<td class="cellSpecialPrice">'.$cell_products_final_price.'</td>';
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
			$tmpcontent.='<tfoot><tr><td class="order_total_data text-right" colspan="'.$colspan.'">';
			$tmpcontent.='<div class="order_total form-horizontal">';
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.='<div class="form-group">
						<label class="control-label col-md-10">'.$this->pi_getLL('sub_total').'</label>
						<div class="col-md-2">
						<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($order['orders_tax_data']['sub_total'], 0).'</p>
						</div>
					</div>';
				$content_vat='<div class="form-group">
						<label class="control-label col-md-10">'.$this->pi_getLL('included_vat_amount').'</label>
						<div class="col-md-2">
						<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0).'</p>
						</div>
					</div>';
				if ($order['shipping_method_costs']>0) {
					$content_shipping_costs='
						<div class="form-group">
							<label class="control-label col-md-10">'.$this->pi_getLL('shipping_costs').'</label>
							<div class="col-md-2">
							<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($order['shipping_method_costs']+$order['orders_tax_data']['shipping_tax'], 0).'</p>
							</div>
						</div>';
				}
				if ($order['payment_method_costs']>0) {
					$content_payment_costs='
						<div class="form-group">
							<label class="control-label col-md-10">'.$this->pi_getLL('payment_costs').'</label>
							<div class="col-md-2">
							<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($order['payment_method_costs']+$order['orders_tax_data']['payment_tax'], 0).'</p>
							</div>
						</div>
				';
				}
			} else {
				$tmpcontent.='<div class="form-group">
						<label class="control-label col-md-10">'.$this->pi_getLL('sub_total').'</label>
						<div class="col-md-2">
						<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($order['subtotal_amount'], 0).'</p>
						</div>
					</div>';
				$content_vat='<div class="form-group">
						<label class="control-label col-md-10">'.$this->pi_getLL('vat').'</label>
						<div class="col-md-2">
						<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0).'</p>
						</span>
					</div>';
				if ($order['shipping_method_costs']>0) {
					$content_shipping_costs='
						<div class="form-group">
							<label class="control-label col-md-10">'.$this->pi_getLL('shipping_costs').'</label>
							<div class="col-md-2">
							<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($order['shipping_method_costs'], 0).'</p>
							</div>
						</div>';
				}
				if ($order['payment_method_costs']>0) {
					$content_payment_costs='
						<div class="form-group">
							<label class="control-label col-md-10">'.$this->pi_getLL('payment_costs').'</label>
							<div class="col-md-2">
							<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($order['payment_method_costs'], 0).'</p>
							</div>
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
				<div class="form-group">
					<label class="control-label col-md-10">'.$this->pi_getLL('discount').'</label>
					<div class="col-md-2">
					<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($order['discount'], 0).'</p>
					</div>
				</div>
				';
			}
			$tmpcontent.='
			<div class="form-group">
				<label class="control-label col-md-10">'.$this->pi_getLL('total').'</label>
				<div class="col-md-2">
				<p class="form-control-static order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['grand_total'], 0).'</p>
				</div>
			</div>';
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.=$content_vat;
			}
			$tmpcontent.='</div>';
			$tmpcontent.='</td></tr></tfoot></table>';
		}
		$tmpcontent.='</table>';
		$tmpcontent.='
		</div></div>
		'.($order['customer_comments'] ? '
		<fieldset>
					<legend>'.$this->pi_getLL('comments').'</legend>
					'.$order['customer_comments'].'
				</fieldset>
		' : '').'
		</div></form></div></div>';
	}
	$content.=$tmpcontent;
}
?>