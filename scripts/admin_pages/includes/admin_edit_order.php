<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$subpartArray=array();
$subpartArray['###VALUE_REFERRER###']='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
}
if (is_numeric($this->get['orders_id'])) {
	$order=mslib_fe::getOrder($this->get['orders_id']);
	if ($this->post) {
		if ($order['customer_id']) {
			if (!$customer_address=mslib_fe::getAddressInfo('customer', $order['customer_id'])) {
				$customer_address['country']=$order['billing_country'];
				$customer_address['region']=$order['billing_region'];
			}
			$country=mslib_fe::getCountryByName($customer_address['country']);
			if (!empty($customer_address['region'])) {
				$zone=mslib_fe::getRegionByName($customer_address['region']);
			} else {
				$zone['zn_country_iso_nr']=0;
			}
		}
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$order['is_locked']) {
			$delivery_country=mslib_fe::getCountryByName($this->post['delivery_country']);
			$updateArray=array();
			if ($this->post['shipping_method']) {
				$shipping_method=mslib_fe::getShippingMethod($this->post['shipping_method']);
				if (empty($order['orders_tax_data'])) {
					// temporary call, replacing the inner tax_ruleset inside the getShippingMethod
					$tax_ruleset=mslib_fe::taxRuleSet($shipping_method['tax_id'], 0, $country['cn_iso_nr'], $zone['zn_country_iso_nr']);
					$shipping_method['tax_rate']=($tax_ruleset['total_tax_rate']/100);
					$shipping_method['country_tax_rate']=($tax_ruleset['country_tax_rate']/100);
					$shipping_method['region_tax_rate']=($tax_ruleset['state_tax_rate']/100);
				}
				if ($this->post['tx_multishop_pi1']['shipping_method_costs']) {
					$price=$this->post['tx_multishop_pi1']['shipping_method_costs'];
				} else {
					$price=mslib_fe::getShippingCosts($delivery_country['cn_iso_nr'], $this->post['shipping_method']);
				}
				if ($price>0) {
					if (strstr($price, "%")) {
						// calculate total shipping costs based by %
						$subtotal=0;
						foreach ($order['products'] as $products_id=>$value) {
							if (is_numeric($products_id)) {
								$subtotal=$subtotal+($value['qty']*$value['final_price']);
							}
						}
						if ($subtotal) {
							$percentage=str_replace("%", '', $price);
							if ($percentage) {
								$price=($subtotal/100*$percentage);
							}
						}
					} else {
						if (!strstr($price, "%")) {
							if (strstr($price, ",")) {
								$steps=explode(",", $price);
								// calculate total costs
								$subtotal=mslib_fe::getOrderTotalPrice($this->get['orders_id'], 1);
								$count=0;
								foreach ($steps as $step) {
									// the   value 200:15 means below 200 euro the shipping costs are 15 euro, above and equal 200 euro the shipping costs are 0 euro
									$split=explode(":", $step);
									if (is_numeric($split[0])) {
										if ($count==0) {
											$price=$split[1];
										}
										if ($subtotal>$split[0]) {
											$price=$split[1];
											next();
										}
									}
									$count++;
								}
							}
						}
					}
				}
				if ($price) {
					$updateArray['shipping_method_costs']=$price;
				} else {
					$updateArray['shipping_method_costs']=0;
				}
				if ($shipping_method['tax_id'] && $updateArray['shipping_method_costs']) {
					$shipping_tax['shipping_total_tax_rate']=$shipping_method['tax_rate'];
					if ($shipping_method['country_tax_rate']) {
						$shipping_tax['shipping_country_tax_rate']=$shipping_method['country_tax_rate'];
						$shipping_tax['shipping_country_tax']=mslib_fe::taxDecimalCrop($updateArray['shipping_method_costs']*($shipping_method['country_tax_rate']));
					} else {
						$shipping_tax['shipping_country_tax_rate']=0;
						$shipping_tax['shipping_country_tax']=0;
					}
					if ($shipping_method['region_tax_rate']) {
						$shipping_tax['shipping_region_tax_rate']=$shipping_method['region_tax_rate'];
						$shipping_tax['shipping_region_tax']=mslib_fe::taxDecimalCrop($updateArray['shipping_method_costs']*($shipping_method['region_tax_rate']));
					} else {
						$shipping_tax['shipping_region_tax_rate']=0;
						$shipping_tax['shipping_region_tax']=0;
					}
					if ($shipping_tax['shipping_region_tax'] && $shipping_tax['shipping_country_tax']) {
						$shipping_tax['shipping_tax']=$shipping_tax['shipping_country_tax']+$shipping_tax['shipping_region_tax'];
					} else {
						$shipping_tax['shipping_tax']=mslib_fe::taxDecimalCrop($updateArray['shipping_method_costs']*($shipping_method['tax_rate']));
					}
				} else {
					$shipping_tax['shipping_tax']=0;
					$shipping_tax['shipping_country_tax']=0;
					$shipping_tax['shipping_region_tax']=0;
					$shipping_tax['shipping_total_tax_rate']=0;
					$shipping_tax['shipping_country_tax_rate']=0;
					$shipping_tax['shipping_region_tax_rate']=0;
				}
				$updateArray['shipping_method']=$shipping_method['code'];
				$updateArray['shipping_method_label']=$shipping_method['name'];
			}
			if ($this->post['payment_method']) {
				$payment_method=mslib_fe::getPaymentMethod($this->post['payment_method']);
				if (empty($order['orders_tax_data'])) {
					// temporary call, replacing the inner tax_ruleset inside the getPaymentMethod
					$tax_ruleset=mslib_fe::taxRuleSet($payment_method['tax_id'], 0, $country['cn_iso_nr'], $zone['zn_country_iso_nr']);
					$payment_method['tax_rate']=($tax_ruleset['total_tax_rate']/100);
					$payment_method['country_tax_rate']=($tax_ruleset['country_tax_rate']/100);
					$payment_method['region_tax_rate']=($tax_ruleset['state_tax_rate']/100);
				}
				if ($this->post['tx_multishop_pi1']['payment_method_costs']) {
					$price=$this->post['tx_multishop_pi1']['payment_method_costs'];
				} else {
					$price=$payment_method['handling_costs'];
				}
				if ($price) {
					if (!strstr($price, "%")) {
						$user['payment_method_costs']=$price;
					} else {
						// calculate total payment costs based by %
						$subtotal=0;
						foreach ($order['products'] as $products_id=>$value) {
							if (is_numeric($value['products_id'])) {
								$subtotal=$subtotal+($value['qty']*$value['final_price']);
							}
						}
						if ($subtotal) {
							$percentage=str_replace("%", '', $price);
							if ($percentage) {
								$price=($subtotal/100*$percentage);
							}
						}
					}
				} else {
					$price=0;
				}
				$updateArray['payment_method_costs']=$price;
				if ($payment_method['tax_id'] && $updateArray['payment_method_costs']) {
					$payment_tax['payment_total_tax_rate']=$payment_method['tax_rate'];
					if ($payment_method['country_tax_rate']) {
						$payment_tax['payment_country_tax_rate']=$payment_method['country_tax_rate'];
						$payment_tax['payment_country_tax']=mslib_fe::taxDecimalCrop($updateArray['payment_method_costs']*($payment_method['country_tax_rate']));
					} else {
						$payment_tax['payment_country_tax_rate']=0;
						$payment_tax['payment_country_tax']=0;
					}
					if ($payment_method['region_tax_rate']) {
						$payment_tax['payment_region_tax_rate']=$payment_method['region_tax_rate'];
						$payment_tax['payment_region_tax']=mslib_fe::taxDecimalCrop($updateArray['payment_method_costs']*($payment_method['region_tax_rate']));
					} else {
						$payment_tax['payment_region_tax_rate']=0;
						$payment_tax['payment_region_tax']=0;
					}
					if ($payment_tax['payment_region_tax'] && $payment_tax['payment_country_tax']) {
						$payment_tax['payment_tax']=$user['payment_country_tax']+$user['payment_region_tax'];
					} else {
						$payment_tax['payment_tax']=mslib_fe::taxDecimalCrop($updateArray['payment_method_costs']*($payment_method['tax_rate']));
					}
				} else {
					$payment_tax['payment_tax']=0;
					$payment_tax['payment_country_tax']=0;
					$payment_tax['payment_region_tax']=0;
					$payment_tax['payment_total_tax_rate']=0;
					$payment_tax['payment_country_tax_rate']=0;
					$payment_tax['payment_region_tax_rate']=0;
				}
				$updateArray['payment_method']=$payment_method['code'];
				$updateArray['payment_method_label']=$payment_method['name'];
			}
			$keys=array();
			$keys[]='company';
			$keys[]='name';
			$keys[]='street_name';
			$keys[]='address_number';
			$keys[]='address_ext';
			$keys[]='building';
			$keys[]='zip';
			$keys[]='city';
			$keys[]='country';
			$keys[]='email';
			$keys[]='telephone';
			$keys[]='mobile';
			$keys[]='fax';
			foreach ($keys as $key) {
				$string='billing_'.$key;
				$updateArray[$string]=$this->post['tx_multishop_pi1'][$string];
				$string='delivery_'.$key;
				$updateArray[$string]=$this->post['tx_multishop_pi1'][$string];
			}
			$updateArray['billing_address']=preg_replace('/ +/', ' ', $updateArray['billing_street_name'].' '.$updateArray['billing_address_number'].' '.$updateArray['billing_address_ext']);
			$updateArray['delivery_address']=preg_replace('/ +/', ' ', $updateArray['delivery_street_name'].' '.$updateArray['delivery_address_number'].' '.$updateArray['delivery_address_ext']);
			if (count($updateArray)) {
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$this->get['orders_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
	if ($order['orders_id'] and !$order['is_locked']) {
		if ($this->post['manual_products_id']>0 && $this->post['manual_product_name']) {
			$this->post['manual_product_qty']=str_replace(',', '.', $this->post['manual_product_qty']);
			if (empty($this->post['manual_product_price'])) {
				$this->post['manual_product_price']='0';
			}
			$sql="insert into tx_multishop_orders_products (orders_id, products_id, qty, products_name, products_price, final_price, products_tax) values ('".$this->get['orders_id']."', '".$this->post['manual_products_id']."', '".$this->post['manual_product_qty']."', '".addslashes($this->post['manual_product_name'])."', '".$this->post['manual_product_price']."', '".$this->post['manual_product_price']."', '".$this->post['manual_product_tax']."')";
			$GLOBALS['TYPO3_DB']->sql_query($sql);
			$orders_products_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
			if (count($this->post['option'])>0) {
				foreach ($this->post['option'] as $optid=>$optval) {
					// get price and price prefix
					$sql="select pov.products_options_values_name, po.products_options_name, pa.price_prefix, pa.options_values_price from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id left join tx_multishop_products_options_values pov on pa.options_values_id = pov.products_options_values_id where po.language_id = '".$this->sys_language_uid."' and pov.language_id = '".$this->sys_language_uid."' and pa.options_id = '".$optid."' and pa.options_values_id = '".$optval."' and pa.products_id = ".$this->post['manual_products_id'];
					$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
					$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					if (strpos($this->post['predef_price'][$optid], '-')!==false) {
						$price_prefix='-';
						$this->post['predef_price'][$optid]=str_replace('-', '', $this->post['predef_price'][$optid]);
						$price=$this->post['predef_price'][$optid];
					} else {
						$price_prefix='+';
						$this->post['predef_price'][$optid]=str_replace('+', '', $this->post['predef_price'][$optid]);
						$price=$this->post['predef_price'][$optid];
					}
					if (empty($this->post['predef_price'][$optid])) {
						$price=$rs['options_values_price'];
						$price_prefix=$rs['price_prefix'];
					}
					$option=$rs['products_options_name'];
					$option_value=$rs['products_options_values_name'];
					$sql="insert into tx_multishop_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix, attributes_values, products_options_id, products_options_values_id) values ('".$this->get['orders_id']."', '".$orders_products_id."', '".$option."', '".$option_value."', '".$price."', '".$price_prefix."', NULL, '".$optid."', '".$optval."')";
					$GLOBALS['TYPO3_DB']->sql_query($sql);
				}
			}
			for ($y=0; $y<count($this->post['manual_option']); $y++) {
				if (strpos($this->post['manual_price'][$y], '-')!==false) {
					$price_prefix='-';
					$this->post['manual_price'][$y]=str_replace('-', '', $this->post['manual_price'][$y]);
				} else {
					$price_prefix='+';
					$this->post['manual_price'][$y]=str_replace('+', '', $this->post['manual_price'][$y]);
				}
				if (!empty($this->post['manual_option'][$y]) && !empty($this->post['manual_values'][$y])) {
					$sql="insert into tx_multishop_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix, attributes_values, products_options_id, products_options_values_id) values ('".$this->get['orders_id']."', '".$orders_products_id."', '".$this->post['manual_option'][$y]."', '".$this->post['manual_values'][$y]."', '".$this->post['manual_price'][$y]."', '".$price_prefix."', NULL, '0', '0')";
					$GLOBALS['TYPO3_DB']->sql_query($sql);
				}
			}
		} else {
			if ($this->post['manual_products_id']==0 && $this->post['manual_product_name']) {
				$this->post['manual_product_qty']=str_replace(',', '.', $this->post['manual_product_qty']);
				if (empty($this->post['manual_product_price'])) {
					$this->post['manual_product_price']='0';
				}
				$sql="insert into tx_multishop_orders_products (orders_id, products_id, qty, products_name, products_price, final_price, products_tax) values ('".$this->get['orders_id']."', '0', '".$this->post['manual_product_qty']."', '".addslashes($this->post['manual_product_name'])."', '".$this->post['manual_product_price']."', '".$this->post['manual_product_price']."', '".$this->post['manual_product_tax']."')";
				$GLOBALS['TYPO3_DB']->sql_query($sql);
				$orders_products_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
				if (count($this->post['option'])>0) {
					foreach ($this->post['option'] as $optid=>$optval) {
						// get price and price prefix
						$sql="select pov.products_options_values_name, po.products_options_name, pa.price_prefix, pa.options_values_price from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id left join tx_multishop_products_options_values pov on pa.options_values_id = pov.products_options_values_id where po.language_id = '".$this->sys_language_uid."' and pov.language_id = '".$this->sys_language_uid."' and pa.options_id = '".$optid."' and pa.options_values_id = '".$optval."' and pa.products_id = ".$this->post['manual_products_id'];
						$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
						$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
						if (strpos($this->post['predef_price'][$optid], '-')!==false) {
							$price_prefix='-';
							$this->post['predef_price'][$optid]=str_replace('-', '', $this->post['predef_price'][$optid]);
							$price=$this->post['predef_price'][$optid];
						} else {
							$price_prefix='+';
							$this->post['predef_price'][$optid]=str_replace('+', '', $this->post['predef_price'][$optid]);
							$price=$this->post['predef_price'][$optid];
						}
						if (empty($this->post['predef_price'][$optid])) {
							$price=$rs['options_values_price'];
							$price_prefix=$rs['price_prefix'];
						}
						$option=$rs['products_options_name'];
						$option_value=$rs['products_options_values_name'];
						$sql="insert into tx_multishop_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix, attributes_values, products_options_id, products_options_values_id) values ('".$this->get['orders_id']."', '".$orders_products_id."', '".$option."', '".$option_value."', '".$price."', '".$price_prefix."', NULL, '".$optid."', '".$optval."')";
						$GLOBALS['TYPO3_DB']->sql_query($sql);
					}
				}
				for ($y=0; $y<count($this->post['manual_option']); $y++) {
					if (strpos($this->post['manual_price'][$y], '-')!==false) {
						$price_prefix='-';
						$this->post['manual_price'][$y]=str_replace('-', '', $this->post['manual_price'][$y]);
					} else {
						$price_prefix='+';
						$this->post['manual_price'][$y]=str_replace('+', '', $this->post['manual_price'][$y]);
					}
					if (!empty($this->post['manual_option'][$y]) && !empty($this->post['manual_values'][$y])) {
						$sql="insert into tx_multishop_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix, attributes_values, products_options_id, products_options_values_id) values ('".$this->get['orders_id']."', '".$orders_products_id."', '".$this->post['manual_option'][$y]."', '".$this->post['manual_values'][$y]."', '".$this->post['manual_price'][$y]."', '".$price_prefix."', NULL, '0', '0')";
						$GLOBALS['TYPO3_DB']->sql_query($sql);
					}
				}
			}
		}
		if (is_numeric($this->post['orders_products_id'])>0 && $this->post['product_name']) {
			$this->post['product_qty']=str_replace(',', '.', $this->post['product_qty']);
			if (empty($this->post['product_price'])) {
				$this->post['product_price']='0';
			}
			$sql="update tx_multishop_orders_products set products_id = '".$this->post['products_id']."', qty = '".$this->post['product_qty']."', products_name ='".addslashes($this->post['product_name'])."', products_price = '".addslashes($this->post['product_price'])."', final_price = '".$this->post['product_price']."', products_tax = '".$this->post['product_tax']."' where orders_id = ".$this->get['orders_id']." and orders_products_id = '".$this->post['orders_products_id']."'";
			$GLOBALS['TYPO3_DB']->sql_query($sql);
			// clean up the order product attributes to prepare the update
			$sql="delete from tx_multishop_orders_products_attributes where orders_id = ".$this->get['orders_id']." and orders_products_id = ".$this->post['orders_products_id'];
			$GLOBALS['TYPO3_DB']->sql_query($sql);
			// insert the update attributes
			if (count($this->post['option'])>0) {
				foreach ($this->post['option'] as $optid=>$optval) {
					// get price and price prefix
					$sql="select pov.products_options_values_name, po.products_options_name, pa.price_prefix, pa.options_values_price from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id left join tx_multishop_products_options_values pov on pa.options_values_id = pov.products_options_values_id where po.language_id = '".$this->sys_language_uid."' and pov.language_id = '".$this->sys_language_uid."' and pa.options_id = '".$optid."' and pa.options_values_id = '".$optval."' and pa.products_id = ".$this->post['products_id'];
					$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
					$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					if (strpos($this->post['option_price'][$optid], '-')!==false) {
						$man_price_prefix='-';
						$this->post['option_price'][$optid]=str_replace('-', '', $this->post['option_price'][$optid]);
					} else {
						$man_price_prefix='+';
						$this->post['option_price'][$optid]=str_replace('+', '', $this->post['option_price'][$optid]);
					}
					$price_prefix=$rs['price_prefix'];
					if ($price_prefix!=$man_price_prefix) {
						$price_prefix=$man_price_prefix;
					}
					$price=$rs['options_values_price'];
					if ($this->post['option_price'][$optid]!=$rs['options_values_price']) {
						$price=$this->post['option_price'][$optid];
					}
					$option=$rs['products_options_name'];
					$option_value=$rs['products_options_values_name'];
					$sql="insert into tx_multishop_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix, attributes_values, products_options_id, products_options_values_id) values ('".$this->get['orders_id']."', '".$this->post['orders_products_id']."', '".$option."', '".$option_value."', '".$price."', '".$price_prefix."', NULL, '".$optid."', '".$optval."')";
					$GLOBALS['TYPO3_DB']->sql_query($sql);
				}
			}
			for ($x=0; $x<count($this->post['edit_manual_option']); $x++) {
				if (strpos($this->post['edit_manual_price'][$x], '-')!==false) {
					$price_prefix='-';
					$this->post['edit_manual_price'][$x]=str_replace('-', '', $this->post['edit_manual_price'][$x]);
				} else {
					$price_prefix='+';
					$this->post['edit_manual_price'][$x]=str_replace('+', '', $this->post['edit_manual_price'][$x]);
				}
				if (!empty($this->post['edit_manual_option'][$x]) && !empty($this->post['edit_manual_values'][$x])) {
					$sql="insert into tx_multishop_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix, attributes_values, products_options_id, products_options_values_id) values ('".$this->get['orders_id']."', '".$this->post['orders_products_id']."', '".$this->post['edit_manual_option'][$x]."', '".$this->post['edit_manual_values'][$x]."', '".$this->post['edit_manual_price'][$x]."', '".$price_prefix."', NULL, '0', '0')";
					$GLOBALS['TYPO3_DB']->sql_query($sql);
				}
			}
		}
		// delete single item in order
		$redirect_after_delete=false;
		if (isset($this->get['delete_product']) && $this->get['delete_product']==1) {
			if (isset($this->get['order_pid']) && $this->get['order_pid']>0) {
				$sql="delete from tx_multishop_orders_products where orders_products_id = ".$this->get['order_pid']." limit 1";
				$GLOBALS['TYPO3_DB']->sql_query($sql);
				$sql="delete from tx_multishop_orders_products_attributes where orders_products_id = ".$this->get['order_pid'];
				$GLOBALS['TYPO3_DB']->sql_query($sql);
				$redirect_after_delete=true;
			}
		}
		if ($this->post) {
			// updating the *_tax_data on orders, orders_products and orders_products_attributes
			$sql_order="select shipping_method_costs, payment_method_costs from tx_multishop_orders where orders_id = ".$this->get['orders_id'];
			$qry_order=$GLOBALS['TYPO3_DB']->sql_query($sql_order);
			$rs_order=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_order);
			$orders_tax['shipping_tax']=(string)$shipping_tax['shipping_tax'];
			$orders_tax['shipping_country_tax']=(string)$shipping_tax['shipping_country_tax'];
			$orders_tax['shipping_region_tax']=(string)$shipping_tax['shipping_region_tax'];
			$orders_tax['shipping_total_tax_rate']=(string)$shipping_tax['shipping_total_tax_rate'];
			$orders_tax['shipping_country_tax_rate']=(string)$shipping_tax['shipping_country_tax_rate'];
			$orders_tax['shipping_region_tax_rate']=(string)$shipping_tax['shipping_region_tax_rate'];
			$orders_tax['payment_tax']=(string)$payment_tax['payment_tax'];
			$orders_tax['payment_country_tax']=(string)$payment_tax['payment_country_tax'];
			$orders_tax['payment_region_tax']=(string)$payment_tax['payment_region_tax'];
			$orders_tax['payment_total_tax_rate']=(string)$payment_tax['payment_total_tax_rate'];
			$orders_tax['payment_country_tax_rate']=(string)$payment_tax['payment_country_tax_rate'];
			$orders_tax['payment_region_tax_rate']=(string)$payment_tax['payment_region_tax_rate'];
			$grand_total['shipping_cost']=$rs_order['shipping_method_costs'];
			$grand_total['payment_cost']=$rs_order['payment_method_costs'];
			$grand_total['shipping_tax']=$orders_tax['shipping_tax'];
			$grand_total['payment_tax']=$orders_tax['payment_tax'];
			$total_order_tax['shipping_tax']=$orders_tax['shipping_tax'];
			$total_order_tax['payment_tax']=$orders_tax['payment_tax'];
			$sql_order_products="select orders_products_id, products_id, qty, final_price, products_tax from tx_multishop_orders_products where orders_id = ".$this->get['orders_id'];
			$qry_order_products=$GLOBALS['TYPO3_DB']->sql_query($sql_order_products);
			while ($rs_order_products=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_order_products)) {
				$product_final_price=$rs_order_products['final_price'];
				$product_qty=$rs_order_products['qty'];
				$product_opid=$rs_order_products['orders_products_id'];
				$product_tax=$rs_order_products['products_tax'];
				$products_id=$rs_order_products['products_id'];
				if ($products_id>0) {
					$product=mslib_fe::getProduct($products_id);
					$tax_ruleset=mslib_fe::taxRuleSet($product['tax_id'], 0, $country['cn_iso_nr'], $zone['zn_country_iso_nr']);
					$tax_rate=($tax_ruleset['total_tax_rate']/100);
					$region_tax_rate=(isset($tax_ruleset['state_tax_rate']) ? ($tax_ruleset['state_tax_rate']/100) : 0);
					$country_tax_rate=(isset($tax_ruleset['country_tax_rate']) ? ($tax_ruleset['country_tax_rate']/100) : 0);
					$product_country_tax=mslib_fe::taxDecimalCrop($product_final_price*$country_tax_rate);
					$product_region_tax=mslib_fe::taxDecimalCrop($product_final_price*$region_tax_rate);
					if ($country_tax_rate && $region_tax_rate) {
						$product_tax=$product_country_tax+$product_region_tax;
					} else {
						$product_tax=mslib_fe::taxDecimalCrop($product_final_price*$tax_rate);
					}
				} else {
					$tax_rate=($rs_order_products['products_tax']/100);
					$product_tax=mslib_fe::taxDecimalCrop($product_final_price*$tax_rate);
				}
				$product_tax_data['country_tax_rate']=(string)$country_tax_rate;
				$product_tax_data['region_tax_rate']=(string)$region_tax_rate;
				$product_tax_data['total_tax_rate']=(string)$tax_rate;
				$product_tax_data['country_tax']=(string)$product_country_tax;
				$product_tax_data['region_tax']=(string)$product_region_tax;
				$product_tax_data['total_tax']=(string)$product_tax;
				$grand_total['product_price']+=$product_final_price*$product_qty;
				$grand_total['product_tax']+=$product_tax*$product_qty;
				$total_order_tax['product_tax']+=$product_tax*$product_qty;
				$sql_orders_attributes="select orders_products_attributes_id, price_prefix, options_values_price from tx_multishop_orders_products_attributes where orders_products_id = ".$rs_order_products['orders_products_id']." and orders_id = ".$this->get['orders_id'];
				$qry_orders_attributes=$GLOBALS['TYPO3_DB']->sql_query($sql_orders_attributes);
				while ($rs_orders_attributes=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_orders_attributes)) {
					$attr_id=$rs_orders_attributes['orders_products_attributes_id'];
					$price_prefix=$rs_orders_attributes['price_prefix'];
					$attr_price=$rs_orders_attributes['options_values_price'];
					$row_country_tax=mslib_fe::taxDecimalCrop(($price_prefix.$price)*$country_tax_rate);
					$row_region_tax=mslib_fe::taxDecimalCrop(($price_prefix.$price)*$region_tax_rate);
					if ($country_tax_rate && $region_tax_rate) {
						$row_tax=$row_country_tax+$row_region_tax;
					} else {
						$row_tax=mslib_fe::taxDecimalCrop(($price_prefix.$attr_price)*$tax_rate);
					}
					$attribute_tax['country_tax']=$row_country_tax;
					$attribute_tax['region_tax']=$row_region_tax;
					$attribute_tax['tax']=$row_tax;
					$updateArray=array();
					$updateArray['attributes_tax_data']=serialize($attribute_tax);
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products_attributes', 'orders_products_attributes_id = \''.$attr_id.'\'', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					$grand_total['product_attributes_price']+=($price_prefix.$attr_price)*$product_qty;
					$grand_total['product_attributes_tax']+=$row_tax;
					$product_tax_data['total_attributes_tax']+=$row_tax;
				}
				$product_tax_data['total_attributes_tax']=(string)$product_tax_data['total_attributes_tax'];
				$updateArray=array();
				$updateArray['products_tax_data']=serialize($product_tax_data);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'orders_products_id = \''.$product_opid.'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$orders_tax['grand_total']=(string)array_sum($grand_total);
			$orders_tax['total_orders_tax']=(string)array_sum($total_order_tax);
			$updateArray=array();
			$updateArray['orders_tax_data']=serialize($orders_tax);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id = \''.$this->get['orders_id'].'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// repair tax stuff
			require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
			$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
			$mslib_order->init($this);
			$mslib_order->repairOrder($this->get['orders_id']);
		}
		if ($redirect_after_delete) {
			// to redirect to 'normal url' after successfull deletion of item in order
			echo '<script>location.href=\''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$this->get['orders_id'].'&action=edit_order', 1).'\'</script>';
		}
		// redirect after editing or adding order product
		if (!empty($this->post['product_name']) || !empty($this->post['manual_product_name'])) {
			echo '<script>location.href=\''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$this->get['orders_id'].'&action=edit_order', 1).'\'</script>';
		}
	}
	$str="SELECT *, o.crdate, o.status, osd.name as orders_status from tx_multishop_orders o left join tx_multishop_orders_status os on o.status=os.id left join tx_multishop_orders_status_description osd on (os.id=osd.orders_status_id AND o.language_id=osd.language_id) where o.orders_id='".$this->get['orders_id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$orders=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	if ($orders['orders_id']) {
		if ($this->post) {
			$updateArray=array();
			if ($this->post['expected_delivery_date']) {
				$updateArray['expected_delivery_date']=strtotime($this->post['expected_delivery_date']);
			}
			if ($this->post['track_and_trace_code']) {
				$updateArray['track_and_trace_code']=$this->post['track_and_trace_code'];
			}
			if ($this->post['order_memo']) {
				$updateArray['order_memo']=$this->post['order_memo'];
			}
			if (count($updateArray)) {
				$close_window=1;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$_REQUEST['orders_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$orders['expected_delivery_date']=$this->post['expected_delivery_date'];
				$orders['track_and_trace_code']=$this->post['track_and_trace_code'];
				$orders['order_memo']=$this->post['order_memo'];
			}
		}
		if ($this->post['order_status']) {
			// first get current status
			if ($this->post['order_status']==$orders['status']) {
				// no new order status has been defined. only mail when the email text box is containing content
				if ($this->post['comments']) {
					$continue_update=1;
				}
			} else {
				$continue_update=1;
			}
			$close_window=1;
			if ($continue_update) {
				// dynamic variables
				mslib_befe::updateOrderStatus($this->get['orders_id'], $this->post['order_status'], $this->post['customer_notified']);
			}
		}
		if ($close_window) {
			if ($this->post['tx_multishop_pi1']['referrer']) {
				header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
				exit();
			} else {
				header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders', 1));
				exit();
			}
			if ($this->post['tx_multishop_pi1']['referrer']) {
				header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
				exit();
			} else {
				header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders', 1));
				exit();
			}
		}
		$save_block='
			<div class="save_block">
				<a href="'.$subpartArray['###VALUE_REFERRER###'].'" class="msBackendButton backState arrowLeft arrowPosLeft"><span>'.$this->pi_getLL('cancel').'</span></a>
				<span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" /></span>
			</div>
			';
		// count total products
		$total_amount=0;
		$str2="SELECT * from tx_multishop_orders_products where orders_id='".$orders['orders_id']."'";
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
			$orders_products[]=$row;
			$total_amount=($row['qty']*$row['final_price'])+$total_amount;
			// now count the attributes
			$str3="SELECT * from tx_multishop_orders_products_attributes where orders_products_id='".$row['orders_products_id']."'";
			$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
			while (($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3))!=false) {
				if ($row3['price_prefix']=='+') {
					$total_amount=$total_amount+($row['qty']*$row3['options_values_price']);
				} else {
					$total_amount=$total_amount-($row['qty']*$row3['options_values_price']);
				}
				$orders_products_attributes[$row['orders_products_id']][]=$row3;
			}
			// now count the attributes eof
		}
		// count eof products
		$order_date=strftime("%x", $orders['crdate']);
		$tmpcontent='';
		$tmpcontent.='
<script language="JavaScript" type="text/JavaScript">
	function CONFIRM() {
		if (confirm("'.addslashes($this->pi_getLL('are_you_sure')).'?")) {
			return true;
		} else {
			return false;
		}
	}
</script>';
		$enabled_countries=mslib_fe::loadEnabledCountries();
		$dont_overide_billing_countries=false;
		$dont_overide_delivery_countries=false;
		foreach ($enabled_countries as $country) {
			$billing_countries[]='<option value="'.strtolower($country['cn_short_en']).'" '.((strtolower($orders['billing_country'])==strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
			if (strtolower($orders['billing_country'])==strtolower($country['cn_short_en'])) {
				$dont_overide_billing_countries=true;
			}
			$delivery_countries[]='<option value="'.strtolower($country['cn_short_en']).'" '.((strtolower($orders['delivery_country'])==strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
			if (strtolower($orders['delivery_country'])==strtolower($country['cn_short_en'])) {
				$dont_overide_delivery_countries=true;
			}
		}
		if ($dont_overide_billing_countries) {
			$billing_countries=array_merge(array('<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>'), $billing_countries);
		} else {
			$billing_countries=array_merge(array('<option value="'.$orders['billing_country'].'">'.$orders['billing_country'].'</option>'), $billing_countries);
		}
		$billing_countries_sb='<select name="tx_multishop_pi1[billing_country]" id="edit_billing_country" required="required">'.implode("\n", $billing_countries).'</select>';
		if ($dont_overide_delivery_countries) {
			$delivery_countries=array_merge(array('<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>'), $delivery_countries);
		} else {
			$delivery_countries=array_merge(array('<option value="'.$orders['delivery_country'].'">'.$orders['delivery_country'].'</option>'), $delivery_countries);
		}
		$delivery_countries_sb='<select name="tx_multishop_pi1[delivery_country]" id="edit_delivery_country">'.implode("\n", $delivery_countries).'</select>';
		$editOrderFormFieldset=array();
		$tmpcontent.='
	<div class="tabs-fieldset" id="address_details">
	<fieldset>
		<legend>'.$this->pi_getLL('address_details').'</legend>
		<table id="address_details">
			<tr>
				<td width="50%" valign="top" id="billing_details">
					<table>
					<tr>
						<td align="left" valign="top">
						<h3>'.$this->pi_getLL('billing_details').'</h3>';
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$hide_billing_vcard=false;
			if (empty($orders['billing_telephone']) || empty($orders['billing_name']) || empty($orders['billing_street_name']) || empty($orders['billing_address_number']) || empty($orders['billing_zip']) || empty($orders['billing_city']) || empty($orders['billing_country']) || empty($orders['billing_email']) || empty($orders['billing_telephone'])) {
				$tmpcontent.='<div class="edit_billing_details_container" id="edit_billing_details_container">';
				$hide_billing_vcard=true;
			} else {
				$tmpcontent.='<div class="edit_billing_details_container" id="edit_billing_details_container" style="display:none">';
			}
			$tmpcontent.='<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('company')).':</label>
	<input name="tx_multishop_pi1[billing_company]" type="text" id="edit_billing_company" value="'.$orders['billing_company'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('name')).'*:</label>
	<input name="tx_multishop_pi1[billing_name]" type="text" id="edit_billing_name" value="'.$orders['billing_name'].'" required="required" />
	</div>
	<div class="account-field">
	<label for="delivery_address">'.ucfirst($this->pi_getLL('street_address')).'*:</label>
	<input name="tx_multishop_pi1[billing_street_name]" type="text" id="edit_billing_street_name" value="'.$orders['billing_street_name'].'" required="required" />
	<span  class="error-space left-this"></span>
	</div>
	<div class="account-field">
	<label class="billing_account-addressnumber" for="billing_address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
	<input name="tx_multishop_pi1[billing_address_number]" type="text" id="edit_billing_address_number" value="'.$orders['billing_address_number'].'" required="required" /><span class="error-space left-this"></span>
	</div>
	<div class="account-field">
	<label class="billing_account-address_ext" for="billing_address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
	<input name="tx_multishop_pi1[billing_address_ext]" type="text" id="edit_billing_address_ext" value="'.$orders['billing_address_ext'].'" /><span class="error-space left-this"></span>
	</div>
	<div class="account-field">
	<label class="billing_account-building" for="billing_building">&nbsp;</label>
	<input name="tx_multishop_pi1[billing_building]" type="text" id="edit_billing_building" value="'.$orders['billing_building'].'" /><span class="error-space left-this"></span>
	</div>
	<div class="account-field">
	<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
	<input name="tx_multishop_pi1[billing_zip]" type="text" id="edit_billing_zip" value="'.$orders['billing_zip'].'" required="required" /><span class="error-space"></span>
	</div>
	<div class="account-field">
	<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
	<input name="tx_multishop_pi1[billing_city]" type="text" id="edit_billing_city" value="'.$orders['billing_city'].'" required="required" /><span class="error-space"></span>
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('country')).'*:</label>
	'.$billing_countries_sb.'
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('email')).'*:</label>
	<input name="tx_multishop_pi1[billing_email]" type="text" id="edit_billing_email" value="'.$orders['billing_email'].'" required="required" />
	</div>
	<div class="account-field">';
			if (!empty($orders['billing_telephone'])) {
				$tmpcontent.='<label>'.ucfirst($this->pi_getLL('telephone')).'*:</label>
		<input name="tx_multishop_pi1[billing_telephone]" type="text" id="edit_billing_telephone" value="'.$orders['billing_telephone'].'" required="required" />';
			} else {
				$tmpcontent.='<label>'.ucfirst($this->pi_getLL('telephone')).':</label>
		<input name="tx_multishop_pi1[billing_telephone]" type="text" id="edit_billing_telephone" value="'.$orders['billing_telephone'].'" />';
			}
			$tmpcontent.='</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('mobile')).':</label>
	<input name="tx_multishop_pi1[billing_mobile]" type="text" id="edit_billing_mobile" value="'.$orders['billing_mobile'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('fax')).':</label>
	<input name="tx_multishop_pi1[billing_fax]" type="text" id="edit_billing_fax" value="'.$orders['billing_fax'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('vat_id', 'VAT ID')).'</label>
	<input name="tx_multishop_pi1[billing_vat_id]" type="text" id="edit_billing_vat_id" value="'.$orders['billing_vat_id'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('coc_id', 'COC Nr.:')).'</label>
	<input name="tx_multishop_pi1[billing_coc_id]" type="text" id="edit_billing_coc_id" value="'.$orders['billing_coc_id'].'" />
	</div>
	<a href="#" id="close_edit_billing_info" class="float_right msadmin_button">'.$this->pi_getLL('save').'</a>
	</div>';
		}
		if ($hide_billing_vcard) {
			$tmpcontent.='<div class="address_details_container" id="billing_details_container" style="display:none">';
		} else {
			$tmpcontent.='<div class="address_details_container" id="billing_details_container">';
		}
		if ($orders['billing_company']) {
			$tmpcontent.='<strong>'.$orders['billing_company'].'</strong><br />';
		}
		$tmpcontent.=$orders['billing_name'].'<br />
	'.$orders['billing_address'].'<br />
	'.$orders['billing_zip'].' '.$orders['billing_city'].' <br />
	'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $orders['billing_country']).'<br /><br />';
		if ($orders['billing_email']) {
			$tmpcontent.=$this->pi_getLL('email').': <a href="mailto:'.$orders['billing_email'].'">'.$orders['billing_email'].'</a><br />';
		}
		if ($orders['billing_telephone']) {
			$tmpcontent.=$this->pi_getLL('telephone').': '.$orders['billing_telephone'].'<br />';
		}
		if ($orders['billing_mobile']) {
			$tmpcontent.=$this->pi_getLL('mobile').': '.$orders['billing_mobile'].'<br />';
		}
		if ($orders['billing_fax']) {
			$tmpcontent.=$this->pi_getLL('fax').': '.$orders['billing_fax'].'<br />';
		}
		if ($orders['billing_vat_id']) {
			$tmpcontent.='<strong>'.$this->pi_getLL('vat_id').' '.$orders['billing_vat_id'].'</strong><br />';
		}
		if ($orders['billing_coc_id']) {
			$tmpcontent.='<strong>'.$this->pi_getLL('coc_id').': '.$orders['billing_coc_id'].'</strong><br />';
		}
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$tmpcontent.='<span><a href="#" id="edit_billing_info" class="msadmin_button">'.$this->pi_getLL('edit').'</a></span>';
		}
		$tmpcontent.='</div>';
		$tmpcontent.='
		</td>
	</tr>
	</table>		
</td>
<td width="50%" valign="top" id="delivery_details">
	<table>
	<tr>
		<td align="left" valign="top">
		<h3>'.$this->pi_getLL('delivery_details').'</h3>
';
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$hide_delivery_vcard=false;
			if (empty($orders['billing_telephone']) || empty($orders['delivery_name']) || empty($orders['delivery_street_name']) || empty($orders['delivery_address_number']) || empty($orders['delivery_zip']) || empty($orders['delivery_city']) || empty($orders['delivery_country']) || empty($orders['delivery_email']) || empty($orders['delivery_telephone'])) {
				$tmpcontent.='<div class="edit_delivery_details_container" id="edit_delivery_details_container">';
				$hide_delivery_vcard=true;
			} else {
				$tmpcontent.='<div class="edit_delivery_details_container" id="edit_delivery_details_container" style="display:none">';
			}
			$tmpcontent.='<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('company')).':</label>
	<input name="tx_multishop_pi1[delivery_company]" type="text" id="edit_delivery_company" value="'.$orders['delivery_company'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('name')).'*:</label>
	<input name="tx_multishop_pi1[delivery_name]" type="text" id="edit_delivery_name" value="'.$orders['delivery_name'].'" />
	</div>
	<div class="account-field">
	<label for="delivery_address">'.ucfirst($this->pi_getLL('street_address')).'*:</label>
	<input name="tx_multishop_pi1[delivery_street_name]" type="text" id="edit_delivery_street_name" value="'.$orders['delivery_street_name'].'" />
	<span  class="error-space left-this"></span>
	</div>
	<div class="account-field">
	<label class="delivery_account-addressnumber" for="delivery_address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
	<input name="tx_multishop_pi1[delivery_address_number]" type="text" id="edit_delivery_address_number" value="'.$orders['delivery_address_number'].'" /><span class="error-space left-this"></span></div>
	<div class="account-field">
	<label class="delivery_account-address_ext" for="delivery_address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
	<input name="tx_multishop_pi1[delivery_address_ext]" type="text" id="edit_delivery_address_ext" value="'.$orders['delivery_address_ext'].'" /><span class="error-space left-this"></span>
	</div>
	<div class="account-field">
	<label class="delivery_account-building" for="delivery_building">&nbsp;</label>
	<input name="tx_multishop_pi1[delivery_building]" type="text" id="edit_delivery_building" value="'.$orders['delivery_building'].'" /><span class="error-space left-this"></span>
	</div>
	<div class="account-field">
	<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
	<input name="tx_multishop_pi1[delivery_zip]" type="text" id="edit_delivery_zip" value="'.$orders['delivery_zip'].'" /><span class="error-space"></span>
	</div>
	<div class="account-field">
	<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
	<input name="tx_multishop_pi1[delivery_city]" type="text" id="edit_delivery_city" value="'.$orders['delivery_city'].'" /><span class="error-space"></span>
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('country')).'*:</label>
	'.$delivery_countries_sb.'
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('email')).'*:</label>
	<input name="tx_multishop_pi1[delivery_email]" type="text" id="edit_delivery_email" value="'.$orders['delivery_email'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('telephone')).'*:</label>
	<input name="tx_multishop_pi1[delivery_telephone]" type="text" id="edit_delivery_telephone" value="'.$orders['delivery_telephone'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('mobile')).':</label>
	<input name="tx_multishop_pi1[delivery_mobile]" type="text" id="edit_delivery_mobile" value="'.$orders['delivery_mobile'].'" />
	</div>
	<div class="account-field">
	<label>'.ucfirst($this->pi_getLL('fax')).':</label>
	<input name="tx_multishop_pi1[delivery_fax]" type="text" id="edit_delivery_fax" value="'.$orders['delivery_fax'].'" />
	</div>
	<a href="#" id="close_edit_delivery_info" class="float_right msadmin_button">'.$this->pi_getLL('save').'</a>
	</div>
	';
		}
		if ($hide_delivery_vcard) {
			$tmpcontent.='<div class="address_details_container" id="delivery_details_container" style="display:none">';
		} else {
			$tmpcontent.='<div class="address_details_container" id="delivery_details_container">';
		}
		if ($orders['delivery_company']) {
			$tmpcontent.='<strong>'.$orders['delivery_company'].'</strong><br />';
		}
		$tmpcontent.=$orders['delivery_name'].'<br />
	      '.$orders['delivery_address'].'<br />
	      '.$orders['delivery_zip'].' '.$orders['delivery_city'].' <br />
	      '.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $orders['delivery_country']).'<br /><br />';
		if ($orders['delivery_email']) {
			$tmpcontent.=$this->pi_getLL('email').': <a href="mailto:'.$orders['delivery_email'].'">'.$orders['delivery_email'].'</a><br />';
		}
		if ($orders['delivery_telephone']) {
			$tmpcontent.=$this->pi_getLL('telephone').': '.$orders['delivery_telephone'].'<br />';
		}
		if ($orders['delivery_mobile']) {
			$tmpcontent.=$this->pi_getLL('mobile').': '.$orders['delivery_mobile'].'<br />';
		}
		if ($orders['delivery_fax']) {
			$tmpcontent.=$this->pi_getLL('fax').': '.$orders['delivery_fax'].'<br />';
		}
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$tmpcontent.='<span><a href="#" id="edit_delivery_info" class="msadmin_button">'.$this->pi_getLL('edit').'</a></span>';
		}
		$tmpcontent.='</div>';
		$tmpcontent.='
						</td>
					</tr>
					</table>				
				</td>
			</tr>
		</table>';
		$headerData='
	<script type="text/javascript">
	function updateCustomerOrderDetails(type, data_serial) {
		href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=update_customer_order_details', 1).'&details_type=" + type + "&orders_id='.$this->get['orders_id'].'";
		jQuery.ajax({ 
				type:   "POST", 
				url:    href, 
				data:   data_serial,
				dataType: "json",
				success: function(r) {} 
		});
	}
	jQuery(document).ready(function($) {
		$(document).on("click", ".submit_button", function() {
			var edit_form = $(".admin_product_edit");
			if (!edit_form[0].checkValidity()) {
				jQuery("ul.tabs li").removeClass("active");
				jQuery("ul.tabs li").each(function(i, v){
					if (i == 0) {
						jQuery(v).addClass("active"); 
						jQuery(".tab_content").hide();
						var activeTab = jQuery(v).find("a").attr("href");
						jQuery(activeTab).fadeIn(0);
					}
				});
				
				$("#billing_details_container").hide();
				$("#edit_billing_details_container").show();
			
				$("#delivery_details_container").hide();
				$("#edit_delivery_details_container").show();
			}
		});
		$(document).on("click", "#edit_billing_info", function(e) {
			e.preventDefault();			
			$("#billing_details_container").hide();
			$("#edit_billing_details_container").show();
		});		
		$("#close_edit_billing_info").click(function(e) {
			e.preventDefault();		
			var billing_details 	= "";
			var address_data 		= "";
			$("[id^=edit_billing]").each(function(){
				if ($(this).attr("id") == "edit_billing_company") {
					if ($(this).val() != "") {
						billing_details += "<strong>" + $(this).val() + "</strong><br/>";
					}
				} else if ($(this).attr("id") == "edit_billing_name") {
					billing_details += $(this).val() + "<br/>";
				} else if ($(this).attr("id") == "edit_billing_street_name") {
					address_data += $(this).val() + " ";		
				} else if ($(this).attr("id") == "edit_billing_address_number") {
					address_data += $(this).val() + " ";
				} else if ($(this).attr("id") == "edit_billing_address_ext") {
					address_data += $(this).val();
					address_data_replace = address_data.replace(/\s\s+/g, " ");
					billing_details += address_data_replace + "<br/>";
		
				} else if ($(this).attr("id") == "edit_billing_building") {
					if ($(this).val() != "") {
						billing_details += $(this).val() + "<br/>";
					}
				} else if ($(this).attr("id") == "edit_billing_zip") {
					billing_details += $(this).val() + " ";
				} else if ($(this).attr("id") == "edit_billing_city") {
					billing_details += $(this).val() + "<br/>";
				} else if ($(this).attr("id") == "edit_billing_country") {
					billing_details += $(this).val() + "<br/><br/>";		
				} else if ($(this).attr("id") == "edit_billing_email") {
					if ($(this).val() != "") {
						billing_details += "'.$this->pi_getLL('email').': <a href=\"mailto:" + $(this).val() + "\">" + $(this).val() + "</a><br/>";
					}
				} else if ($(this).attr("id") == "edit_billing_telephone") {
					if ($(this).val() != "") {
						billing_details += "'.$this->pi_getLL('telephone').': " + $(this).val() + "<br/>";
					}
				} else if ($(this).attr("id") == "edit_billing_mobile") {
					if ($(this).val() != "") {
						billing_details += "'.$this->pi_getLL('mobile').': " + $(this).val() + "<br/>";
					}
				} else if ($(this).attr("id") == "edit_billing_fax") {
					if ($(this).val() != "") {
						billing_details += "'.$this->pi_getLL('fax').': " + $(this).val() + "<br/>";
					}
				} else if ($(this).attr("id") == "edit_billing_vat_id") {
					if ($(this).val() != "") {
						billing_details += "<strong>'.$this->pi_getLL('vat_id').' " + $(this).val() + "</strong><br/>";
					}
				} else if ($(this).attr("id") == "edit_billing_coc_id") {
					if ($(this).val() != "") {
						billing_details += "<strong>'.$this->pi_getLL('coc_id', 'COC Nr.:').' " + $(this).val() + "</strong><br/>";
					}
				}
			});
								
			$("#billing_details_container").empty();
			$("#billing_details_container").html(billing_details + "<span><a href=\"#\" id=\"edit_billing_info\" class=\"msadmin_button\">'.$this->pi_getLL('edit').'</a></span>");							
			updateCustomerOrderDetails("billing_details", $("[id^=edit_billing]").serialize());		
			$("#billing_details_container").show();
			$("#edit_billing_details_container").hide();
		});
		$(document).on("click", "#edit_delivery_info", function(e) {
			e.preventDefault();
			$("#delivery_details_container").hide();
			$("#edit_delivery_details_container").show();
		});		
		$("#close_edit_delivery_info").click(function(e) {
			e.preventDefault();
			var delivery_details 	= "";
			var address_data 		= "";
			$("[id^=edit_delivery]").each(function() {
				if ($(this).attr("id") == "edit_delivery_company") {
					if ($(this).val() != "") {
						delivery_details += "<strong>" + $(this).val() + "</strong><br/>";
					}
				} else if ($(this).attr("id") == "edit_delivery_name") {
					delivery_details += $(this).val() + "<br/>";
				} else if ($(this).attr("id") == "edit_delivery_street_name") {
					address_data += $(this).val() + " ";
				} else if ($(this).attr("id") == "edit_delivery_address_number") {
					address_data += $(this).val() + " ";
				} else if ($(this).attr("id") == "edit_delivery_address_ext") {
					address_data += $(this).val();	
					address_data_replace = address_data.replace(/\s\s+/g, " ");
					delivery_details += address_data_replace + "<br/>";
				} else if ($(this).attr("id") == "edit_delivery_building") {
					if ($(this).val() != "") {
						delivery_details += $(this).val() + "<br/>";
					}
				} else if ($(this).attr("id") == "edit_delivery_zip") {
					delivery_details += $(this).val() + " ";
				} else if ($(this).attr("id") == "edit_delivery_city") {
					delivery_details += $(this).val() + "<br/>";
				} else if ($(this).attr("id") == "edit_delivery_country") {
					delivery_details += $(this).val() + "<br/><br/>";	
				} else if ($(this).attr("id") == "edit_delivery_email") {
					if ($(this).val() != "") {
						delivery_details += "'.$this->pi_getLL('email').': <a href=\"mailto:" + $(this).val() + "\">" + $(this).val() + "</a><br/>";
					}
				} else if ($(this).attr("id") == "edit_delivery_telephone") {
					if ($(this).val() != "") {
						delivery_details += "'.$this->pi_getLL('telephone').': " + $(this).val() + "<br/>";
					}
				} else if ($(this).attr("id") == "edit_delivery_mobile") {
					if ($(this).val() != "") {
						delivery_details += "'.$this->pi_getLL('mobile').': " + $(this).val() + "<br/>";
					}
				} else if ($(this).attr("id") == "edit_delivery_fax") {
					if ($(this).val() != "") {
						delivery_details += "'.$this->pi_getLL('fax').': " + $(this).val() + "<br/>";
					}
				}
			});
			$("#delivery_details_container").empty();
			$("#delivery_details_container").html(delivery_details + "<span><a href=\"#\" id=\"edit_delivery_info\" class=\"msadmin_button\">'.$this->pi_getLL('edit').'</a></span>");			
			updateCustomerOrderDetails("delivery_details", $("[id^=edit_delivery]").serialize());								
			$("#delivery_details_container").show();
			$("#edit_delivery_details_container").hide();
		});
	});
	</script>';
		$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
		$headerData='';
		$tmpcontent.='
	</fieldset>
	</div>
	';
		$editOrderFormFieldset[]=$tmpcontent;
		$tmpcontent='';
		$orderDetails=array();
		$orderDetails[]='
<li>
	<label>'.$this->pi_getLL('orders_id').'</label><span>'.$orders['orders_id'].'</span>
	<label>'.$this->pi_getLL('admin_customer_id').'</label><span>'.$orders['customer_id'].'</span>
	<label>'.$this->pi_getLL('order_date').'</label><span>'.$order_date.'</span>
</li>
';
		$orderDetailsItem='<li>';
		$orderDetailsItem.='<label>'.$this->pi_getLL('shipping_method').'</label>';
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$shipping_methods=mslib_fe::loadShippingMethods(1);
			$payment_methods=mslib_fe::loadPaymentMethods(1);
			if (is_array($shipping_methods) and count($shipping_methods)) {
				$optionItems=array();
				$dontOverrideDefaultOption=0;
				foreach ($shipping_methods as $code=>$item) {
					if (!$item['status']) {
						$item['name'].=' ('.$this->pi_getLL('hidden_in_checkout').')';
					}
					$optionItems[]='<option value="'.$item['id'].'"'.($code==$orders['shipping_method'] ? ' selected' : '').'>'.htmlspecialchars($item['name']).'</option>';
					if ($code==$orders['shipping_method']) {
						$dontOverrideDefaultOption=1;
					}
				}
				if ($dontOverrideDefaultOption) {
					$optionItems=array_merge(array('<option value="">'.ucfirst($this->pi_getLL('choose')).'</option>'), $optionItems);
				} else {
					$optionItems=array_merge(array('<option value="">'.($orders['shipping_method_label'] ? $orders['shipping_method_label'] : $orders['shipping_method']).'</option>'), $optionItems);
				}
				$orderDetailsItem.='<select name="shipping_method">'.implode("\n", $optionItems).'</select>';
			} else {
				$orderDetailsItem.='<span>'.($orders['shipping_method_label'] ? $orders['shipping_method_label'] : $orders['shipping_method']).'</span>';
			}
		} else {
			$orderDetailsItem.='<span>'.($orders['shipping_method_label'] ? $orders['shipping_method_label'] : $orders['shipping_method']).'</span>';
		}
		$orderDetailsItem.='</li>';
		$orderDetails[]=$orderDetailsItem;
		$orderDetailsItem='';
		$orderDetailsItem='<li>';
		$orderDetailsItem.='<label>'.$this->pi_getLL('payment_method').'</label>';
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			if (is_array($payment_methods) and count($payment_methods)) {
				$optionItems=array();
				$dontOverrideDefaultOption=0;
				foreach ($payment_methods as $code=>$item) {
					if (!$item['status']) {
						$item['name'].=' ('.$this->pi_getLL('hidden_in_checkout').')';
					}
					$optionItems[]='<option value="'.$item['id'].'"'.($code==$orders['payment_method'] ? ' selected' : '').'>'.htmlspecialchars($item['name']).'</option>';
					if ($code==$orders['payment_method']) {
						$dontOverrideDefaultOption=1;
					}
				}
				if ($dontOverrideDefaultOption) {
					$optionItems=array_merge(array('<option value="">'.ucfirst($this->pi_getLL('choose')).'</option>'), $optionItems);
				} else {
					$optionItems=array_merge(array('<option value="">'.($orders['payment_method_label'] ? $orders['payment_method_label'] : $orders['payment_method']).'</option>'), $optionItems);
				}
				$orderDetailsItem.='<select name="payment_method">'.implode("\n", $optionItems).'</select>';
			} else {
				$orderDetailsItem.='<span>'.($orders['payment_method_label'] ? $orders['payment_method_label'] : $orders['payment_method']).'</span>';
			}
		} else {
			$orderDetailsItem.='<span>'.($orders['payment_method_label'] ? $orders['payment_method_label'] : $orders['payment_method']).'</span>';
		}
		$orderDetailsItem.='</li>';
		$orderDetails[]=$orderDetailsItem;
		$orderDetailsItem='';
		if ($orders['customer_comments']) {
			$orderDetailsItem='
	<li id="customer_comments"><label>'.$this->pi_getLL('customer_comments').'</label>	
		<span>'.nl2br($orders['customer_comments']).'</span>
	</li>
	';
			$orderDetails[]=$orderDetailsItem;
		}
		// hook for adding new items to details fieldset
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsFieldset'])) {
			// hook
			$params=array(
				'orderDetails'=>&$orderDetails,
				'orders'=>&$orders
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsFieldset'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
			// hook oef
		}
		$tmpcontent.='
<div class="tabs-fieldset" id="order_properties">
<fieldset>
	<legend>Details</legend>
	<ul class="formDetails">	
';
		$tmpcontent.=implode("", $orderDetails);
		$tmpcontent.='
	</ul>
</fieldset>
</div>
';
		$tmpcontent.='
<div class="clear_both"></div>
<div class="tabs-fieldset" id="product_details">
<fieldset>
<legend>'.$this->pi_getLL('product_details').'</legend>';
		$tr_type='even';
		$tmpcontent.='<table class="msZebraTable msadmin_border" width="100%">';
		$order_product_level_th='';
		if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
			$all_orders_status=mslib_fe::getAllOrderStatus();
			$order_product_level_th='<th class="cell_status">'.$this->pi_getLL('order_status').'</th>';
		}
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$tmpcontent.='<tr><th class="cell_products_id">'.$this->pi_getLL('products_id').'</th><th class="cell_products_qty">'.$this->pi_getLL('qty').'</th><th class="cell_products_name">'.$this->pi_getLL('products_name').'</th>'.$order_product_level_th.'<th class="cell_products_normal_price">'.$this->pi_getLL('normal_price').'</th><th  class="cell_products_vat">'.$this->pi_getLL('vat').'</th><th class="cell_products_final_price">'.$this->pi_getLL('final_price_ex_vat').'</th><th>&nbsp;</th></tr>';
		} else {
			$tmpcontent.='<tr><th class="cell_products_id">'.$this->pi_getLL('products_id').'</th><th class="cell_products_qty">'.$this->pi_getLL('qty').'</th><th class="cell_products_name">'.$this->pi_getLL('products_name').'</th>'.$order_product_level_th.'<th class="cell_products_normal_price">'.$this->pi_getLL('normal_price').'</th><th class="cell_products_vat">'.$this->pi_getLL('vat').'</th><th class="cell_products_final_price">'.$this->pi_getLL('final_price_ex_vat').'</th></tr>';
		}
		$total_tax=0;
		if (is_array($orders_products) and count($orders_products)) {
			foreach ($orders_products as $order) {
				if (!$tr_type or $tr_type=='even') {
					$tr_type='odd';
				} else {
					$tr_type='even';
				}
				$tmpcontent.='<tr class="'.$tr_type.'">';
				if (($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) and ($this->get['edit_product']==1 && $this->get['order_pid']==$order['orders_products_id'])) {
					$tmpcontent.='<td align="right" class="cell_products_products_id">'.$order['products_id'].'<input type="hidden" id="edit_product_row_counter" value="0"></td>';
					$tmpcontent.='<td align="right" class="cell_products_qty"><input type="hidden" name="products_id" id="products_id" value="'.$order['products_id'].'"><input type="hidden" name="orders_products_id" value="'.$order['orders_products_id'].'"><input class="text" style="width:25px" type="text" name="product_qty" value="'.round($order['qty'], 13).'" /></td>';
					$tmpcontent.='<td align="left" class="cell_products_name"><input class="product_name_input" type="text" name="product_name" value="'.$order['products_name'].'" />
					</td>';
					if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
						$tmpcontent.='<td align="center" class="cell_products_status">';
						//<div class="orders_status_button_gray" title="'.htmlspecialchars($order['orders_status']).'">'.$order['orders_status'].'</div>
						$tmpcontent.='<select name="order_product_status" class="change_order_product_status" rel="'.$order['orders_products_id'].'" id="orders_'.$order['orders_products_id'].'">
							<option value="">'.$this->pi_getLL('choose').'</option>						
						';
						if (is_array($all_orders_status)) {
							foreach ($all_orders_status as $item) {
								$tmpcontent.='<option value="'.$item['id'].'"'.($item['id']==$order['status'] ? ' selected' : '').'>'.$item['name'].'</option>'."\n";
							}
						}
						$tmpcontent.='</select>';
						$tmpcontent.='</td>';
					}
					$tmpcontent.='<td align="right" class="cell_products_normal_price"><input class="text" style="width:44px" type="text" name="product_price" id="product_price" value="'.$order['products_price'].'" /></td>';
					$tmpcontent.='<td align="right" class="cell_products_vat"><input class="text" style="width:40px" type="text" name="product_tax" value="'.$order['products_tax'].'" /> %</td>';
					$tmpcontent.='<td align="right" class="cell_products_final_price">'.mslib_fe::amount2Cents($order['final_price'], 0).'</td>';
				} else {
					$row=array();
					$where='';
					$product=mslib_fe::getProduct($order['products_id']);
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
							$where.='&';
						}
						// get all cats to generate multilevel fake url eof
					}
					$row[0]=$order['products_id'];
					$row[1]=number_format($order['qty'], 2);
					if ($this->ms['MODULES']['DISPLAY_PRODUCT_IMAGE_IN_ADMIN_ORDER_DETAILS'] and $product['products_image']) {
						$row[2]='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '50').'">';
						$row[2].='<a href="'.mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$order['products_id'].'&tx_multishop_pi1[page_section]=products_detail').'" target="_blank">'.$order['products_name'].'</a>'.($order['products_model'] ? ' ('.$order['products_model'].')' : '').($product['ean_code'] ? '<br />EAN: '.$product['ean_code'].'' : '').($product['sku_code'] ? '<br />SKU: '.$product['sku_code'].'' : '').($product['vendor_code'] ? '<br />Vendor code: '.$product['vendor_code'].'' : '');
					} else {
						$row[2]='<a href="'.mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$order['products_id'].'&tx_multishop_pi1[page_section]=products_detail').'" target="_blank">'.$order['products_name'].'</a>'.($order['products_model'] ? ' ('.$order['products_model'].')' : '').($product['ean_code'] ? '<br />EAN: '.$product['ean_code'].'' : '').($product['sku_code'] ? '<br />SKU: '.$product['sku_code'].'' : '').($product['vendor_code'] ? '<br />Vendor code: '.$product['vendor_code'].'' : '');
					}
					$row[3]=mslib_fe::amount2Cents($order['final_price'], 0);
					$row[4]=number_format($order['products_tax'], 2);
					$row[4]=str_replace('.00', '', $order['products_tax']).'%';
					$row[5]=mslib_fe::amount2Cents($order['qty']*$order['final_price'], 0);
					// custom hook that can be controlled by third-party plugin
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderListItemPreHook'])) {
						$params=array(
							'order'=>&$order,
							'row'=>&$row
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderListItemPreHook'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					// custom hook that can be controlled by third-party plugin eof
					$tmpcontent.='<td align="right" class="cell_products_products_id">'.$row[0].'</td>';
					$tmpcontent.='<td align="right" class="cell_products_qty">'.round($row[1], 13).'</td>';
					$tmpcontent.='<td align="left" class="cell_products_name">'.$row[2].'</td>';
					if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
						$tmpcontent.='<td align="center" class="cell_products_status">';
						//<div class="orders_status_button_gray" title="'.htmlspecialchars($order['orders_status']).'">'.$order['orders_status'].'</div>
						$tmpcontent.='<select name="order_product_status" class="change_order_product_status" rel="'.$order['orders_products_id'].'" id="orders_'.$order['orders_products_id'].'">
						<option value="">'.$this->pi_getLL('choose').'</option>
						';
						if (is_array($all_orders_status)) {
							foreach ($all_orders_status as $item) {
								$tmpcontent.='<option value="'.$item['id'].'"'.($item['id']==$order['status'] ? ' selected' : '').'>'.$item['name'].'</option>'."\n";
							}
						}
						$tmpcontent.='</select>';
						$tmpcontent.='</td>';
					}
					$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$row[3].'</td>';
					$tmpcontent.='<td align="right" class="cell_products_vat">'.$row[4].'</td>';
					$tmpcontent.='<td align="right" class="cell_products_final_price">'.$row[5].'</td>';
				}
				if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
					if (!$this->get['edit_product'] || ($this->get['edit_product'] && $this->get['order_pid']!=$order['orders_products_id'])) {
						$tmpcontent.='<td align="right" class="cell_products_action">
						<input type="button" value="'.$this->pi_getLL('edit').'" onclick="location.href=\''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$this->get['orders_id']).'&action=edit_order&edit_product=1&order_pid='.$order['orders_products_id'].'\'" class="msadmin_button">
						<a href="'.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$this->get['orders_id']).'&action=edit_order&delete_product=1&order_pid='.$order['orders_products_id'].'" style="text-decoration:none"><input type="button" value="'.$this->pi_getLL('delete').'" onclick="return CONFIRM();" class="msadmin_button"></a></td>';
					} else {
						$tmpcontent.='<td align="right" class="cell_products_action"><input type="button" value="'.$this->pi_getLL('cancel').'" onclick="location.href=\''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$this->get['orders_id']).'&action=edit_order\'" class="msadmin_button">&nbsp;<input type="submit" value="'.$this->pi_getLL('save').'" class="msadmin_button submit_button"></td>';
					}
				}
				$tmpcontent.='</tr>';
				if ($orders_products_attributes[$order['orders_products_id']]) {
					$attr_counter=0;
					if ($this->get['edit_product'] && $this->get['order_pid']==$order['orders_products_id']) {
						$selected_attr=array();
						$manual_attr=array();
						foreach ($orders_products_attributes[$order['orders_products_id']] as $tmpkey=>$options) {
							if ($options['products_options_id']>0 && $options['products_options_values_id']>0) {
								$selected_attr[$options['products_options_id']]=$options['products_options_values_id'];
							} else {
								$manual_attr['optname'][]=$options['products_options'];
								$manual_attr['optvalue'][]=$options['products_options_values'];
								$manual_attr['optprice'][]=$options['options_values_price'];
							}
						}
						$sql_option="select po.products_options_name, po.products_options_id from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id where (po.hide_in_cart=0 or po.hide_in_cart is null) and po.language_id = '".$this->sys_language_uid."' and pa.products_id = ".$order['products_id']." group by pa.options_id";
						$qry_option=$GLOBALS['TYPO3_DB']->sql_query($sql_option);
						while (($rs_option=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_option))!=false) {
							$tmpcontent.='<tr class="'.$tr_type.'"><td>&nbsp;</td><td>&nbsp;</td>';
							$tmpcontent.='<td align="left"><div id="product_attributes_wrapper"><label for="edit_option_'.$rs_option['products_options_id'].'">'.$rs_option['products_options_name'].':</label> ';
							$tmpcontent.='<select name="option['.$rs_option['products_options_id'].']" id="edit_option_'.$rs_option['products_options_id'].'">';
							$sql_optval="select pa.options_values_id, pov.products_options_values_name from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id left join tx_multishop_products_options_values pov on pa.options_values_id = pov.products_options_values_id where pov.language_id = '".$this->sys_language_uid."' and pa.options_id = '".$rs_option['products_options_id']."' and pa.products_id = ".$order['products_id'];
							$qry_optval=$GLOBALS['TYPO3_DB']->sql_query($sql_optval);
							while (($rs_optval=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_optval))!=false) {
								if ($selected_attr[$rs_option['products_options_id']]==$rs_optval['options_values_id']) {
									$tmpcontent.='<option value="'.$rs_optval['options_values_id'].'" selected="selected">'.$rs_optval['products_options_values_name'].'</option>';
								} else {
									$tmpcontent.='<option value="'.$rs_optval['options_values_id'].'">'.$rs_optval['products_options_values_name'].'</option>';
								}
							}
							$tmpcontent.='</select>';
							$sql_pap="select opa.options_values_price, opa.price_prefix from tx_multishop_orders_products_attributes opa where products_options_id = '".$rs_option['products_options_id']."' and products_options_values_id = '".$selected_attr[$rs_option['products_options_id']]."' and orders_products_id = '".$order['orders_products_id']."'";
							$qry_pap=$GLOBALS['TYPO3_DB']->sql_query($sql_pap);
							$rs_pap=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_pap);
							$opt_price='';
							if ($rs_pap['options_values_price']>0) {
								if ($rs_pap['price_prefix']=='-') {
									$opt_price='-'.$rs_pap['options_values_price'];
								} else {
									$opt_price=$rs_pap['options_values_price'];
								}
							}
							$tmpcontent.='</div></td>';
							if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
								$tmpcontent.='<td align="right">&nbsp;</td>';
							}
							$tmpcontent.='<td align="right"><input class="text" style="width:44px" type="text" name="option_price['.$rs_option['products_options_id'].']" id="option_price_'.$rs_option['products_options_id'].'" value="'.$opt_price.'" /></td>';
							$tmpcontent.='<td align="right">&nbsp;</td>';
							$tmpcontent.='<td align="right">&nbsp;</td>';
							if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
								$tmpcontent.='<td align="right">&nbsp;</td>';
							}
							$tmpcontent.='</tr>';
							$attr_counter++;
						}
						if (count($manual_attr['optname'])>0) {
							foreach ($manual_attr['optname'] as $idx=>$optname) {
								$optvalue=$manual_attr['optvalue'][$idx];
								$optprice='';
								if ($manual_attr['optprice'][$idx]>0) {
									$optprice=$manual_attr['optprice'][$idx];
								}
								$tmpcontent.='<tr class="'.$tr_type.'" id="edit_product_row_'.$attr_counter.'"><td>&nbsp;</td><td>&nbsp;</td>';
								$tmpcontent.='<td align="left"><div class="optionWrapper"><div id="product_attributes_wrapper"><input type="text" class="text" name="edit_manual_option[]" style="width:90px" value="'.$optname.'" /> <span>:</span> ';
								$tmpcontent.='<input type="text" class="manual_values_input text" name="edit_manual_values[]" value="'.$optvalue.'" />&nbsp;<input type="button" class="msadmin_button" value="-" onclick="edit_remove_manual_row(\''.$attr_counter.'\')">';
								$tmpcontent.='</div></div></td>';
								if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
									$tmpcontent.='<td align="right">&nbsp;</td>';
								}
								$tmpcontent.='<td align="right"><input type="text" name="edit_manual_price[]" class="text" style="width:44px" value="'.$optprice.'"></td><td align="right">&nbsp;</td>';
								$tmpcontent.='<td align="right">&nbsp;</td>';
								if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
									$tmpcontent.='<td align="right">&nbsp;</td>';
								}
								$tmpcontent.='</tr>';
								$attr_counter++;
							}
						}
					} else {
						foreach ($orders_products_attributes[$order['orders_products_id']] as $tmpkey=>$options) {
							if (is_numeric($options['products_options_id'])) {
								$str="SELECT listtype from tx_multishop_products_options o where o.products_options_id='".$options['products_options_id']."' and language_id='".$this->sys_language_uid."'";
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$rowCheck=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
							}
							$attributes_tax_data=unserialize($options['attributes_tax_data']);
							$tmpcontent.='<tr class="'.$tr_type.'"><td>&nbsp;</td><td>&nbsp;</td>';
							if ($rowCheck['listtype']=='file') {
								if ($options['products_options_values']) {
									$filePath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/order_resources/'.rawurlencode($options['products_options_values']);
									if (file_exists($filePath)) {
										$displayImage=0;
										$imgtype=mslib_befe::exif_imagetype($filePath);
										if ($imgtype) {
											// valid image
											$ext=image_type_to_extension($imgtype, false);
											if ($ext) {
												$displayImage=1;
											}
										}
										if ($displayImage) {
											$size=getimagesize($filePath);
											$width='';
											if ($size[0]>350) {
												$width='150';
											}
											// display image with link
											$htmlContent='<br /><a href="'.$this->FULL_HTTP_URL.'uploads/tx_multishop/order_resources/'.rawurlencode($options['products_options_values']).'" class="msAdminDownloadIcon" target="_blank"><img src="'.$this->FULL_HTTP_URL.'uploads/tx_multishop/order_resources/'.rawurlencode($options['products_options_values']).'" width="'.$width.'" /></a>';
										} else {
											// display text with link
											$htmlContent='<a href="'.$this->FULL_HTTP_URL.'uploads/tx_multishop/order_resources/'.rawurlencode($options['products_options_values']).'" class="msAdminDownloadIcon" target="_blank"><span>[save file]</span></a>';
										}
									}
								}
								$tmpcontent.='<td align="left"><a href="'.$this->FULL_HTTP_URL.'uploads/tx_multishop/order_resources/'.rawurlencode($options['products_options_values']).'" class="msAdminDownloadIcon" target="_blank">'.$options['products_options'].': '.$options['products_options_values'].$htmlContent.'</a></td>';
							} else {
								$tmpcontent.='<td align="left">'.$options['products_options'].': '.$options['products_options_values'].'</td>';
							}
							$cell_products_normal_price='';
							$cell_products_vat='';
							$cell_products_final_price='';
							if ($options['options_values_price']>0) {
								$cell_products_normal_price=mslib_fe::amount2Cents(($options['price_prefix'].$options['options_values_price']), 0);
								$cell_products_vat=$row[4];
								$cell_products_final_price=mslib_fe::amount2Cents(($options['price_prefix'].$options['options_values_price'])*$row[1], 0);
							}
							if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
								$tmpcontent.='<td align="right">&nbsp;</td>';
							}
							$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$cell_products_normal_price.'</td>';
							$tmpcontent.='<td align="right" class="cell_products_vat">'.$cell_products_vat.'</td>';
							$tmpcontent.='<td align="right" class="cell_products_final_price">'.$cell_products_final_price.'</td>';
							if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
								$tmpcontent.='<td align="right">&nbsp;</td>';
							}
							// count the vat
							if ($options['options_values_price'] and $order['products_tax']) {
								$item_tax=$order['qty']*$attributes_tax_data['tax'];
								if ($options['price_prefix']=='+') {
									$total_tax=$total_tax+$item_tax;
								} else {
									if ($options['price_prefix']=='-') {
										$total_tax=$total_tax-$item_tax;
									}
								}
							}
							$tmpcontent.='</tr>';
						}
					}
				} else {
					if ($this->get['edit_product'] && $this->get['order_pid']==$order['orders_products_id']) {
						$sql_option="select po.products_options_name, po.products_options_id from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id where (po.hide_in_cart=0 or po.hide_in_cart is null) and po.language_id = '".$this->sys_language_uid."' and pa.products_id = ".$order['products_id']." group by pa.options_id";
						$qry_option=$GLOBALS['TYPO3_DB']->sql_query($sql_option);
						while (($rs_option=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_option))!=false) {
							$tmpcontent.='<tr class="'.$tr_type.'"><td>&nbsp;</td><td>&nbsp;</td>';
							$tmpcontent.='<td align="left">'.$rs_option['products_options_name'].': ';
							$tmpcontent.='<select name="option['.$rs_option['products_options_id'].']" id="option_'.$rs_option['products_options_id'].'">';
							$sql_optval="select pa.options_values_id, pov.products_options_values_name from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id left join tx_multishop_products_options_values pov on pa.options_values_id = pov.products_options_values_id where pov.language_id = '".$this->sys_language_uid."' and pa.options_id = '".$rs_option['products_options_id']."' and pa.products_id = ".$order['products_id'];
							$qry_optval=$GLOBALS['TYPO3_DB']->sql_query($sql_optval);
							while (($rs_optval=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_optval))!=false) {
								$tmpcontent.='<option value="'.$rs_optval['options_values_id'].'">'.$rs_optval['products_options_values_name'].'</option>';
							}
							$tmpcontent.='</select>';
							$tmpcontent.='</td>';
							if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
								$tmpcontent.='<td align="right">&nbsp;</td>';
							}
							$tmpcontent.='<td align="right">&nbsp;</td>';
							$tmpcontent.='<td align="right">&nbsp;</td>';
							$tmpcontent.='<td align="right">&nbsp;</td>';
							if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
								$tmpcontent.='<td align="right">&nbsp;</td>';
							}
							$tmpcontent.='</tr>';
						}
					}
				}
				// count the vat
				if ($order['final_price'] and $order['products_tax']) {
					$product_tax_data=unserialize($order['products_tax_data']);
					$item_tax=$order['qty']*($product_tax_data['total_tax']+$product_tax_data['total_attributes_tax']);
					$total_tax=$total_tax+$item_tax;
				}
				if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked'] and $this->get['edit_product'] and ($this->get['order_pid']==$order['orders_products_id'])) {
					$tmpcontent.='<tr id="last_edit_product_row">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><input type="button" id="edit_add_attributes" class="msadmin_button" value="add attribute"></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					</tr>';
				}
			}
		}
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$colspan=8;
			$tmpcontent.='<tr class="manual_add_new_product" style="display:none"><th colspan="'.$colspan.'" style="text-align:left;">'.$this->pi_getLL('add_item_to_order').'</th></tr>';
			$tmpcontent.='<tr class="odd manual_add_new_product" style="display:none">';
			$tmpcontent.='<td align="right">
							<input type="hidden" value="0" id="product_row_counter">
						  </td>
						  
				<td align="right" valign="top">
					<table width="100%" cellspacing="0" cellpadding="0" style="border:0px;">
						<tr>
							<td style="border:0px solid #fff">
								<input type="hidden" name="manual_products_id" id="manual_products_id" value="">
								<input class="text" style="width:25px" type="text" name="manual_product_qty" id="manual_product_qty" value="1" tabindex="1" />
							</td>
						</tr>
					</table>
				</td>';
			$tmpcontent.='<td align="left" valign="top" id="manual_add_product">
				<table width="100%" cellspacing="0" cellpadding="0" style="border:0px;">
					<tr id="product_row_start">
						<td style="border:0px solid #fff">
							<input class="product_name_input" type="text" name="manual_product_name" id="skeyword" value="" tabindex="2" />
							<input type="hidden" name="page" id="page" value="1" />
						</td>
					</tr>
					
					<tr id="product_row_end">
						<td style="border:0px solid #fff">
							<input type="button" class="msadmin_button" value="add attribute" id="add_attributes" />
						</td>
					</tr>
				</table>
			</td>';
			if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
				$tmpcontent.='<td align="right">&nbsp;</td>';
			}
			$tmpcontent.='<td align="right" valign="top">
				<table width="100%" cellspacing="0" cellpadding="0" style="border:0px;">
					<tr id="price_row_start">
						<td style="border:0px solid #fff">
							<input class="text" style="width:44px" type="text" name="manual_product_price" id="manual_product_price" value="" tabindex="3" />
						</td>
					</tr>
					
					<tr id="price_row_end" style="display:none">
						<td>&nbsp;</td>
					</tr>
				</table>
						  </td>';
			$tmpcontent.='<td align="right" class="cell_products_vat" valign="top">
				<table width="100%" cellspacing="0" cellpadding="0" style="border:0px;">
					<tr id="vat_row_start">
						<td style="border:0px solid #fff">
							<input class="text" style="width:40px" type="text" name="manual_product_tax" id="manual_product_tax" value="" tabindex="4" /> %
						</td>
					</tr>
					
					<tr id="vat_row_end" style="display:none">
						<td>&nbsp;</td>
					</tr>
				</table>
			
						</td>';
			$tmpcontent.='<td align="right" id="manual_final_price">&nbsp;</td>';
			$tmpcontent.='<td align="right"><input type="submit" value="'.$this->pi_getLL('add').'" class="msadmin_button submit_button"></td>';
			$tmpcontent.='';
			$tmpcontent.='</tr>';
			$tmpcontent.='<tr><td colspan="'.$colspan.'" style="text-align:left;"><a href="#" id="button_manual_new_product" class="msadmin_button">'.$this->pi_getLL('add_manual_product', 'ADD ITEM').'</a></td></tr>';
		} else {
			$colspan=7;
		}
//		$tmpcontent.='<tr><td colspan="'.$colspan.'"><hr class="hr"></td></tr>';
		$tmpcontent.='<tr><td align="right" colspan="'.$colspan.'" class="">';
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$shipping_costs='<input name="tx_multishop_pi1[shipping_method_costs]" type="text" value="'.round($orders['shipping_method_costs'], 4).'" class="align_right" style="width:60px">';
			$payment_costs='<input name="tx_multishop_pi1[payment_method_costs]" type="text" value="'.round($orders['payment_method_costs'], 4).'" class="align_right" style="width:60px">';
		} else {
			$shipping_costs=mslib_fe::amount2Cents($orders['shipping_method_costs'], 0);
			$payment_costs=mslib_fe::amount2Cents($orders['payment_method_costs'], 0);
		}
		$orders_tax_data=unserialize($orders['orders_tax_data']);
		if ($orders_tax_data['shipping_tax'] || $orders_tax_data['payment_tax']) {
			$total_tax+=$orders_tax_data['shipping_tax'];
			$total_tax+=$orders_tax_data['payment_tax'];
		}
		$tmpcontent.='
		<div class="order_total">
		';
		$tmpcontent.='
		<div class="account-field">
			<label>'.$this->pi_getLL('sub_total').'</label>
			<span class="order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['sub_total_excluding_vat'], 0).'</span>
		</div>	
		';
		$content_subtotal_tax='
		<div class="account-field">
			<label>'.$this->pi_getLL('vat').'</label>
			<span class="order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0).'</span>
		</div>	
		';
		$content_shipping_costs='
		<div class="account-field">
			<label>'.$this->pi_getLL('shipping_costs').'</label>
			<span class="order_total_value">'.$shipping_costs.'</span>
		</div>	
		';
		$content_payment_costs='
		<div class="account-field">
			<label>'.$this->pi_getLL('payment_costs').'</label>
			<span class="order_total_value">'.$payment_costs.'</span>
		</div>	
		';
		if ($orders['discount']>0) {
			$content_discount='
			<div class="account-field">
				<label>'.$this->pi_getLL('discount').'</label>
				<span class="order_total_value">'.mslib_fe::amount2Cents($orders['discount'], 0).'</span>
			</div>		
			';
		}
		$content_total='
		<div class="account-field">
			<label>'.$this->pi_getLL('total').'</label>
			<span class="order_total_value">'.mslib_fe::amount2Cents($orders_tax_data['grand_total'], 0).'</span>
		</div>	
		';
		if ($orders_tax_data['shipping_tax'] || $orders_tax_data['payment_tax']) {
			$tmpcontent.=$content_shipping_costs;
			$tmpcontent.=$content_payment_costs;
			$tmpcontent.=$content_subtotal_tax;
			$tmpcontent.=$content_discount;
			$tmpcontent.=$content_total;
		} else {
			$tmpcontent.=$content_subtotal_tax;
			$tmpcontent.=$content_shipping_costs;
			$tmpcontent.=$content_payment_costs;
			$tmpcontent.=$content_discount;
			$tmpcontent.=$content_total;
		}
		$tmpcontent.='</div>';
		$tmpcontent.='</td></tr></table>';
		if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
			$tmpcontent.='<script type="text/javascript">';
			$tmpcontent.='
			// autocomplete for options val
			var option_ac = function (o) {
				var optSendData;
				
				var elemIdSplitter = o.id;
				var elemIdSplitterArray = new Array();
				
				elemIdSplitterArray = elemIdSplitter.split(\'_\');
				
				var fetchType = elemIdSplitterArray[1];
				var ctrNumber = elemIdSplitterArray[2];
				
				var optId = 0;
				if (fetchType == \'values\') {
					optId = jQuery(\'#manual_option_id_\' + ctrNumber).val();
				}
				
				var elemId = \'#\' + o.id;
				
				jQuery("#page").val(0);
				
				jQuery(elemId).bind("keydown.autocomplete",function(e){
					// dont process special keys
					var skipKeys = [ 13,38,40,37,39,27,32,17,18,9,16,20,91,93,8,36,35,45,46,33,34,144,145,19 ];
					if (jQuery.inArray(e.keyCode, skipKeys) != -1) optSendData = false;
					else optSendData = true;
				})
				
				jQuery(elemId).autocomplete({
					//console.log(this);
					minLength: 1,
					delay: 100,
					open: function(event, ui){
						jQuery(".ui-autocomplete li.ui-menu-item:odd a").addClass("ui-menu-item-alternate");
						jQuery(".ui-autocomplete").attr("id", "ui-menu-manual-option");
					},
					source: function( request, response ) {
						if (optSendData){
							jQuery.ajax({
								url: "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=ajax_attributes_option_value_search&tx_multishop_pi1[type]=edit_order').'",
								dataType: "json",
								data: {
									q: jQuery(elemId).val(), page: jQuery("#page").val(), ftype: fetchType, optid: optId
								},
								success: function( data ) {
									var index = 1;
									if(data.options != null){
										response( jQuery.map( data.options, function( item ) {
											index = index + 1;
												
											if (index == 6) {
												return {
													label: item.Title,
													link: item.Link,
													value: \'\',
													skeyword: item.skeyword,
													page: item.Page,
													prod: false
														
												}
											} else {
												return {
													label: "<div class=\"ajax_options_search_item\">" + item.Title + "</div>",
													link: item.Link,
													value: item.Name,
													skeyword: item.skeyword,
													page: item.Page,
													prod: true,
													options_id : item.products_options_id
												}
											}
												
										})
										);
									} //end if data
									//alert(index);
								}
							});
						} // and if optSendData
					},
					
					select: function(event, ui ) {
						jQuery(elemId).val(ui.item.skeyword);
						jQuery("#page").val(ui.item.page);
						//console.log(ui);
						
						var link = \'\';
						if (ui.item.link) {
							link = "'.$this->FULL_HTTP_URL.'" + ui.item.link ;
						}
						
						if (fetchType == \'option\') {
							jQuery("#manual_option_id_" + ctrNumber).val(ui.item.options_id);
						}
						
						if (ui.item.prod == true){
							//open(link,\'_self\',\'resizable,location,menubar,toolbar,scrollbars,status\');
						} else {
							if (ui.item.value == \'\') {
								jQuery(elemId).autocomplete("search");
							}
						}
					},
										
					focus: function(event, ui) {
						jQuery(elemId).val(ui.item.skeyword);
						jQuery("#page").val(0);
						return false;
					}
										
				}).data(\'ui-autocomplete\')._renderItem = function (ul, item) {
				
					return jQuery("<li></li>").data("item.autocomplete", item).append(jQuery("<a></a>").html(item.label)).appendTo(ul);
				
				}; 
				
				
				
			}
			// eof autocomplete for option
				
			// manual function for removing the manual attributes			
			var remove_manual_row = function (id) {
				jQuery(\'#product_row_\' + id).remove();
				jQuery(\'#price_row_\' + id).remove();
				//jQuery(\'#vat_row_\' + id).remove();
				
				jQuery(\'#product_row_counter\').val(parseInt(jQuery(\'#product_row_counter\').val()) - 1);
			}
			
			var edit_remove_manual_row = function (id) {
				jQuery(\'#edit_product_row_\' + id).remove();
				
				jQuery(\'#edit_product_row_counter\').val(parseInt(jQuery(\'#edit_product_row_counter\').val()) - 1);
			}
			
			jQuery(document).ready(function($){';
			if ($this->get['edit_product']) {
				$tmpcontent.='jQuery(\'#edit_product_row_counter\').val(\''.$attr_counter.'\');';
			}
			$tmpcontent.='
				jQuery(\'#edit_add_attributes\').click(function() {
					var pr_counter = jQuery(\'#edit_product_row_counter\').val();
					
					// option: values
					var selectbox = \'\';
					selectbox += \'<tr id="edit_product_row_\' + pr_counter + \'">\';
					selectbox += \'<td>&nbsp;</td>\';
					selectbox += \'<td>&nbsp;</td>\';
					selectbox += \'<td>\';
					selectbox += \'<div id="product_attributes_wrapper"><input type="text" class="text" name="edit_manual_option[]" style="width:90px" /> <span>:</span> \';
					selectbox += \'<input type="text" class="manual_values_input text" name="edit_manual_values[]" />&nbsp;<input type="button" class="msadmin_button" value="-" onclick="edit_remove_manual_row(\' + pr_counter + \')">\';
					selectbox += \'</div>\';
					selectbox += \'</td>\';
					selectbox += \'<td align="right">\';
					selectbox += \'<div id="product_attributes_wrapper">\';
					selectbox += \'<input type="text" name="edit_manual_price[]" class="text" style="width:44px">\';
					selectbox += \'</div>\';
					selectbox += \'</td>\';
					selectbox += \'<td align="right">&nbsp;\';
					/* selectbox += \'<div id="product_attributes_wrapper">\';
					selectbox += \'<input type="text" name="edit_manual_vat[]" class="text" style="width:40px"> %\';
					selectbox += \'</div>\'; */
					selectbox += \'</td>\';
					selectbox += \'<td>&nbsp;</td>\';
					selectbox += \'<td>&nbsp;</td>\';
					selectbox += \'</tr>\';
					
					jQuery(\'#last_edit_product_row\').before(selectbox);
					
					jQuery(\'#edit_product_row_counter\').val(parseInt(jQuery(\'#edit_product_row_counter\').val()) + 1); 	
				
				});
				
				// for adding manual attribute
				jQuery(\'#add_attributes\').click(function() {
					var pr_counter = jQuery(\'#product_row_counter\').val();
					
					// option: values
					var selectbox = \'\'; 
					selectbox += \'<tr id="product_row_\'+ pr_counter +\'">\';
					selectbox += \'<td style="border:0px solid #fff">\';
					selectbox += \'<div id="product_attributes_wrapper"><div class="optionWrapper"><input type="hidden" name="manual_option_id[]" id="manual_option_id_\'+ pr_counter +\'" /><input type="text" class="text" name="manual_option[]" id="manual_option_\'+ pr_counter +\'" style="width:90px" onclick="option_ac(this);" /> <span>:</span> \';
					selectbox += \'<input type="hidden" name="manual_values_id[]" id="manual_values_id_\'+ pr_counter +\'" /><input type="text" class="manual_values_input text" name="manual_values[]" id="manual_values_\'+ pr_counter +\'" onclick="option_ac(this)" />&nbsp;<input type="button" class="msadmin_button" value="-" onclick="remove_manual_row(\' + pr_counter + \')">\';
					selectbox += \'</div></div>\';
					selectbox += \'</td>\';
					selectbox += \'</tr>\';
					
					jQuery(\'#product_row_end\').before(selectbox);
					
					var pricebox = \'\'; 
					pricebox += \'<tr id="price_row_\'+ pr_counter +\'">\';
					pricebox += \'<td style="border:0px solid #fff">\';
					pricebox += \'<div id="product_attributes_wrapper"><div class="optionWrapper">\';
					pricebox += \'<input type="text" name="manual_price[]" class="text" style="width:44px">\';
					pricebox += \'</div></div>\';
					pricebox += \'</td>\';
					pricebox += \'</tr>\';
					
					jQuery(\'#price_row_end\').before(pricebox);
					
					
					/* var vatbox = \'\'; 
					vatbox += \'<tr id="vat_row_\'+ pr_counter +\'">\';
					vatbox += \'<td style="border:0px solid #fff">\';
					vatbox += \'<div id="product_attributes_wrapper"><div class="optionWrapper">\';
					vatbox += \'<input type="text" name="manual_vat[]" class="text" style="width:40px"> %\';
					vatbox += \'</div></div>\';
					vatbox += \'</td>\';
					vatbox += \'</tr>\';
					
					jQuery(\'#vat_row_end\').before(vatbox); */
					

					jQuery(\'#product_row_counter\').val(parseInt(jQuery(\'#product_row_counter\').val()) + 1); 	
				
				});
					
				jQuery("#manual_product_qty").bind("keyup",function(){
					if (jQuery("#manual_products_id").val() > 0) {
						jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=ajax_products_staffelprice_search&tx_multishop_pi1[type]=edit_order').'",{pid: jQuery("#manual_products_id").val(), qty: jQuery("#manual_product_qty").val()}, function(d){
							jQuery("#manual_product_price").val(d["price"]);
						});
					}
				});
				
				
				var sendData;
				jQuery("#skeyword").bind("focus",function(){
					jQuery("#page").val(0);
				})
				jQuery("#skeyword").bind("keydown.autocomplete",function(e){
					// dont process special keys
					var skipKeys = [ 13,38,40,37,39,27,32,17,18,9,16,20,91,93,8,36,35,45,46,33,34,144,145,19 ];
					if (jQuery.inArray(e.keyCode, skipKeys) != -1) sendData = false;
					else sendData = true;
				})
				jQuery("#skeyword").autocomplete({
					//console.log(this);
					minLength: 1,
					delay: 250,
					open: function(event, ui){
						jQuery(".ui-autocomplete li.ui-menu-item:odd a").addClass("ui-menu-item-alternate");
						jQuery(".ui-autocomplete").attr("id", "ui-menu-manual-product");
					},
					source: function( request, response ) {
						if (sendData){
							jQuery.ajax({
								url: "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=ajax_products_search&tx_multishop_pi1[type]=edit_order').'",
								dataType: "json",
								data: {
									q: jQuery("#skeyword").val(), page: jQuery("#page").val()
								},
								success: function( data ) {
									var index = 1;
									if(data.products != null){
										response( jQuery.map( data.products, function( item ) {
											index = index + 1;
											
											if (index == 6) {
												return {
													label: item.Title,
													value: item.Name,
													link: item.Link,
													skeyword: item.skeyword,
													page: item.Page,
													priceNum: item.final_price,
													tax_rate: item.tax_rate,
													prod: item.Product
														
												}
											} else {
												return {
													label: "<div class=\"ajax_products_image_wrapper\">"+item.Image + "</div><div class=\"ajax_products_search_item\">" + item.Title  + item.Price + "</div>",
													value: item.Name,
													link: item.Link,
													skeyword: item.skeyword,
													page: item.Page,
													prod: item.Product,
													priceNum: item.final_price,
													tax_rate: item.tax_rate,
													products_id : item.products_id
												}
											}
												
										})
										);
									} //end if data
									//alert(index);
								}
							});
						} // and if sendData
					},
					
					select: function(event, ui ) {
						jQuery("#skeyword").val(ui.item.skeyword);
						jQuery("#page").val(ui.item.page);
						//console.log(ui);
						//alert(ui.toSource());
						
						var link = "'.$this->FULL_HTTP_URL.'" + ui.item.link ;
						
						jQuery("#manual_products_id").val(ui.item.products_id);
						jQuery("#manual_product_tax").val(ui.item.tax_rate);
						

						if (jQuery("#manual_product_qty").val() > 0) {
							jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=ajax_products_staffelprice_search&tx_multishop_pi1[type]=edit_order').'",{pid: ui.item.products_id, qty: jQuery("#manual_product_qty").val()}, function(d){
								jQuery("#manual_product_price").val(d["price"]);
							});
							
						} else {
							jQuery("#manual_product_price").val(ui.item.priceNum);		
						}
								
						
						if (ui.item.prod == true){
							//open(link,\'_self\',\'resizable,location,menubar,toolbar,scrollbars,status\');
						} else {
							jQuery("#skeyword").autocomplete("search");
						}
			
						// fetch the attributes for the selected products on autocomplete list
						jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=ajax_products_attributes_search&tx_multishop_pi1[type]=edit_order').'",{pid: ui.item.products_id, optid: 0}, function(j){
							jQuery(\'.optionWrapper\').remove();
							
							// clearing the existing row
							for (var i = 0; i < jQuery(\'#product_row_counter\').val(); i++) {
								jQuery(\'#product_row_\' + i).remove();
								jQuery(\'#price_row_\' + i).remove();
								jQuery(\'#vat_row_\' + i).remove();
							}
							
							// reset the row counter
							jQuery(\'#product_row_counter\').val(\'0\');
							
							jQuery.each(j, function(key, val) {
								var pr_counter = jQuery(\'#product_row_counter\').val();
								
								// option: values
								var selectbox = \'\'; 
								selectbox += \'<tr id="product_row_\'+ pr_counter +\'">\';
								selectbox += \'<td style="border:0px solid #fff">\';
								selectbox += \'<div id="product_attributes_wrapper"><div class="optionWrapper"><label for="option_\' + val.optid + \'">\' + val.optname + \':</label>\';
								selectbox += \'<select name="option[\' + val.optid + \']" id="option_\' + val.optid + \'">\';
								selectbox += \'</select></div></div>\';
								selectbox += \'</td>\';
								selectbox += \'</tr>\';
								
								jQuery(\'#product_row_end\').before(selectbox);
								
								var pricebox = \'\'; 
								pricebox += \'<tr id="price_row_\'+ pr_counter +\'">\';
								pricebox += \'<td style="border:0px solid #fff">\';
								pricebox += \'<div id="product_attributes_wrapper"><div class="optionWrapper">\';
								pricebox += \'<input type="text" name="predef_price[\' + val.optid + \']" class="text" style="width:100px">\';
								pricebox += \'</div></div>\';
								pricebox += \'</td>\';
								pricebox += \'</tr>\';
								
								jQuery(\'#price_row_end\').before(pricebox);
								
								
								/* var vatbox = \'\'; 
								vatbox += \'<tr id="vat_row_\'+ pr_counter +\'">\';
								vatbox += \'<td style="border:0px solid #fff">\';
								vatbox += \'<div id="product_attributes_wrapper"><div class="optionWrapper">\';
								vatbox += \'<input type="text" name="predef_vat[\' + val.optid + \']" class="text" style="width:40px"> %\';
								vatbox += \'</div></div>\';
								vatbox += \'</td>\';
								vatbox += \'</tr>\';
								
								jQuery(\'#vat_row_end\').before(vatbox); */
								
	
								jQuery(\'#product_row_counter\').val(parseInt(jQuery(\'#product_row_counter\').val()) + 1); 
								
								jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=ajax_products_attributes_search&tx_multishop_pi1[type]=edit_order').'",{pid: ui.item.products_id, optid: val.optid}, function(d){
									var optionbox = \'\';
										
									jQuery.each(d, function(key2, val2){
										if (val2.valprice != undefined)
										{
											optionbox += \'<option value="\' + val2.valid + \'">\' + val2.valname + val2.valprice + \'</option>\';
										}
										else
										{
											optionbox += \'<option value="\' + val2.valid + \'">\' + val2.valname + \'</option>\';
										}
									})
								
									jQuery(optionbox).appendTo(\'#option_\' + val.optid);
								})
							});
						})				
					},
										
					focus: function(event, ui) {
						jQuery("#skeyword").val(ui.item.skeyword);
						jQuery("#page").val(0);
						return false;
					}
										
				}).data(\'ui-autocomplete\')._renderItem = function (ul, item) {
				
					return jQuery("<li></li>").data("item.autocomplete", item).append(jQuery("<a></a>").html(item.label)).appendTo(ul);
				
				};
				
			});
			</script>';
		}
	}
	$tmpcontent.='
	</fieldset>
	</div>
	';
	$editOrderFormFieldset[]=$tmpcontent;
	$tmpcontent='';
	// hook for adding new fieldsets into edit_order
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersFieldset'])) {
		// hook
		$params=array(
			'editOrderFormFieldset'=>&$editOrderFormFieldset,
			'orders'=>&$orders
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersFieldset'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
		// hook oef
	}
	$tabs['Order_Details']=array(
		$this->pi_getLL('order_details'),
		implode('', $editOrderFormFieldset)
	);
	// order details tab eof	
	// order memo/status tab
	$tmpcontent='';
	$tmpcontent.='
	<div class="account-field">
		<label for="order_status">'.$this->pi_getLL('order_status').'</label>
		';
	$all_orders_status=mslib_fe::getAllOrderStatus();
	if (is_array($all_orders_status) and count($all_orders_status)) {
		$tmpcontent.='<select name="order_status">
		<option value="">'.$this->pi_getLL('choose').'</option>
		';
		foreach ($all_orders_status as $row) {
			if ($this->get['tx_multishop_pi1']['is_manual']=='1' && $this->get['action']=='edit_order' && $orders['orders_status_id']==0) {
				$tmpcontent.='<option value="'.$row['id'].'" '.(($row['default_status']>0) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
			} else {
				$tmpcontent.='<option value="'.$row['id'].'" '.(($orders['orders_status_id']==$row['id']) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
			}
		}
		$tmpcontent.='</select>
		';
	}
	if (!$orders['expected_delivery_date']) {
		$orders['expected_delivery_date']=time();
	}
	$tmpcontent.='
	</div>	
	<div class="account-field">
		<label for="expected_delivery_date">'.$this->pi_getLL('expected_delivery_date').'</label>
		<input type="text" name="expected_delivery_date_local" readonly class="" id="expected_delivery_date_local" value="'.date("d-m-Y", $orders['expected_delivery_date']).'" >
		<input name="expected_delivery_date" id="expected_delivery_date" type="hidden" value="'.date("Y-m-d", $orders['expected_delivery_date']).'" />
	</div>	
	<div class="account-field">
		<label for="order_memo">'.$this->pi_getLL('track_and_trace_code').'</label>
		<input name="track_and_trace_code" type="text" value="'.htmlspecialchars($orders['track_and_trace_code']).'" />
	</div>		
	<div class="account-field">
		<label for="customer_notified">'.$this->pi_getLL('send_email_to_customer').'</label>
		<input name="customer_notified" type="radio" value="0" /> '.$this->pi_getLL('no').'			
		<input name="customer_notified" id="customer_notified" type="radio" value="1" checked /> '.$this->pi_getLL('yes').'
	</div>	
	<div class="account-field">
		<label for="order_memo">'.$this->pi_getLL('order_memo').'</label>
		<textarea name="order_memo" id="order_memo" class="mceEditor" rows="4">'.htmlspecialchars($orders['order_memo']).'</textarea>	
	</div>		
	<div class="account-field">
		<label for="comments">'.$this->pi_getLL('email_message').'</label>
		<textarea name="comments" id="comments" class="mceEditor" rows="4"></textarea>	
	</div>		

	';
	$GLOBALS['TSFE']->additionalHeaderData[]='
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("#expected_delivery_date_local").datepicker({
				dateFormat: "dd-mm-yy",
				minDate: 0,
				altField: "#expected_delivery_date",
				altFormat: "yy-mm-dd",												
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,  
				yearRange: "'.(date("Y")).':'.(date("Y")+2).'" 
			});
		});			
	 </script>
	 ';
	// load the status history
	$str="select * from tx_multishop_orders_status_history order by name";
	$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
		'tx_multishop_orders_status_history', // FROM ...
		'orders_id=\''.$orders['orders_id'].'\'', // WHERE.
		'', // GROUP BY...
		'orders_status_history_id desc', // ORDER BY...
		'' // LIMIT ...
	);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$order_status_history_items=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
		$order_status_history_items[]=$row;
	}
	if (count($order_status_history_items)>0) {
		$tmpcontent.='
		<div class="tabs-fieldset" id="order_status_history">
		<fieldset>
		<legend>'.$this->pi_getLL('order_status_history').'</legend>
		<table class="msZebraTable msadmin_border" width="100%">
		<tr>
			<th>'.$this->pi_getLL('status').'</th>
			<th>'.$this->pi_getLL('old_status').'</th>
			<th>'.$this->pi_getLL('date').'</th>
			<th>'.$this->pi_getLL('customer_notified').'</th>			
		</tr>		
		';
		foreach ($order_status_history_items as $row) {
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$old_status_name=$all_orders_status[$row['old_value']]['name'];
			if (!$old_status_name) {
				$old_status_name=$this->pi_getLL('admin_label_unknown_order_status');
			}
			$status_name=$all_orders_status[$row['new_value']]['name'];
			if (!$status_name) {
				$status_name=$this->pi_getLL('admin_label_unknown_order_status');
			}
			$tmpcontent.='<tr class="odd">
				<td><strong>'.$status_name.'</strong></td>
				<td>'.$old_status_name.'</td>
				<td>'.strftime("%x %X", $row['crdate']).'</td>
				<td align="center">'.($row['customer_notified'] ? $this->pi_getLL('yes') : $this->pi_getLL('no')).'</td>
			</tr>
			';
			if ($row['comments']) {
				$tmpcontent.='
				<tr class="even">
					<td colspan="4">'.$row['comments'].'</td>
				</tr>
				';
			}
		}
		$tmpcontent.='</table>
		</fieldset>
		</div>
		';
	}
	// load the status history eof
	$tabs['Order_Status']=array(
		$this->pi_getLL('order_status'),
		$tmpcontent
	);
	// order status tab eof
	// hook for adding new tabs into edit_order
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersTabs'])) {
		// hook
		$params=array(
			'tabs'=>&$tabs,
			'orders'=>&$orders
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersTabs'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
		// hook oef
	}
	$tmpcontent='';
	if ($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) {
		$new_manual_product_js='
$("#button_manual_new_product").click(function(e) {
	e.preventDefault();
	if ($(".manual_add_new_product").is(":hidden")) {
		$(".manual_add_new_product").show();
		$("#button_manual_new_product").hide();
	} else {
		$(".manual_add_new_product").hide();
	}
});		
		';
	}
	$content.='
	<script type="text/javascript"> 
	jQuery(document).ready(function($) {
		'.$new_manual_product_js.'
		$(\'.change_order_product_status\').change(function() {
			var order_pid = $(this).attr("rel");
			var orders_status_id = $("option:selected", this).val();
			var orders_status_label = $("option:selected", this).text();
			if (confirm("Do you want to change orders product id: " + order_pid + " to status: " + orders_status_label)) {
				$.ajax({
					type: "POST",
					url: "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_update_order_product_status').'",
					dataType: \'json\',
					data: "tx_multishop_pi1[orders_id]='.$order['orders_id'].'&tx_multishop_pi1[order_product_id]=" + order_pid + "&tx_multishop_pi1[orders_status_id]=" + orders_status_id,
					success: function(msg) {}
				});
			}
		});
		var url_relatives = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax_product_relatives').'";
		jQuery(".tab_content").hide(); 
		jQuery("ul.tabs li:first").addClass("active").show();
		jQuery(".tab_content:first").show();
		jQuery("ul.tabs li").click(function() {
			jQuery("ul.tabs li").removeClass("active");
			jQuery(this).addClass("active"); 
			jQuery(".tab_content").hide();
			var activeTab = jQuery(this).find("a").attr("href");
			jQuery(activeTab).fadeIn(0);
			return false;
		});
		jQuery("#load").hide();
		jQuery().ajaxStart(function() {
			jQuery("#load").show();
			jQuery("#has").hide();
		}).ajaxStop(function() {
			jQuery("#load").hide();
			jQuery("#has").show();		
		});
		jQuery("#filter").click(function(){
			if(jQuery("#key").val().length === 0 ){
				var keywords = 2;
				//alert("hore");		
			} else {
				var keywords = jQuery("#key").val();
			}		
			jQuery.ajax({
				type: "POST",
				url: url_relatives,
				data: {keypas:keywords,pid:"'.$_REQUEST['pid'].'"},
				success: function(data) {
					jQuery("#has").html(data);
				}
			});
		});
	});
	</script>
	<div id="tab-container">
		<ul class="tabs">
	';
	foreach ($tabs as $key=>$value) {
		$count++;
		$content.='<li'.(($count==1) ? ' class="active"' : '').'><a href="#'.$key.'">'.$value[0].'</a></li>';
	}
	$content.='
		</ul>
		<div class="tab_container">
	<form class="admin_product_edit blockSubmitForm" name="admin_product_edit_'.$product['products_id'].'" id="admin_product_edit_'.$product['products_id'].'" method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&action=edit_order&orders_id='.$_REQUEST['orders_id']).'" enctype="multipart/form-data">
		<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$subpartArray['###VALUE_REFERRER###'].'" >
		';
	$count=0;
	foreach ($tabs as $key=>$value) {
		$count++;
		$content.='
			<div style="display: block;" id="'.$key.'" class="tab_content">
				'.$value[1].'
			</div>
		';
	}
	$content.=$save_block.'
			</form>	
		</div>
	</div>';
}
?>