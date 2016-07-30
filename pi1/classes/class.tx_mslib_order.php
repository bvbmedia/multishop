<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
/***************************************************************
 *  Copyright notice
 *  (c) 2010 BVB Media BV - Bas van Beek <bvbmedia@gmail.com>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 * Hint: use extdeveval to insert/update function index above.
 */
class tx_mslib_order extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $orders_id='';
	function initLanguage($ms_locallang) {
		$this->pi_loadLL();
		//array_merge with new array first, so a value in locallang (or typoscript) can overwrite values from ../locallang_db
		$this->LOCAL_LANG=array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
		if ($this->altLLkey) {
			$this->LOCAL_LANG=array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
		}
	}
	function init($ref) {
		mslib_fe::init($ref);
	}
	function repairOrder($orders_id) {
		if (is_numeric($orders_id)) {
			$tax_separation=array();
			$sql="select orders_id, orders_tax_data, payment_method_costs, shipping_method_costs, discount, shipping_method, payment_method, billing_region, billing_country, billing_vat_id from tx_multishop_orders where orders_id='".$orders_id."' order by orders_id asc";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$sub_total_tax=0;
				$sub_total=0;
				$sub_total_excluding_vat=0;
				$shipping_tax=0;
				$payment_tax=0;
				$grand_total=0;
				$order_tax_data['shipping_tax']='0';
				$order_tax_data['shipping_country_tax']='0';
				$order_tax_data['shipping_region_tax']='0';
				$order_tax_data['shipping_total_tax_rate']='0';
				$order_tax_data['shipping_country_tax_rate']='0';
				$order_tax_data['shipping_region_tax_rate']='0';
				$order_tax_data['payment_tax']='0';
				$order_tax_data['payment_country_tax']='0';
				$order_tax_data['payment_region_tax']='0';
				$order_tax_data['payment_total_tax_rate']='0';
				$order_tax_data['payment_country_tax_rate']='0';
				$order_tax_data['payment_region_tax_rate']='0';
				$order_tax_data['grand_total']='0';
				$order_tax_data['total_orders_tax']='0';
				// get shipping method by code
				$this->tta_user_info['default']['country']=$row['billing_country'];
				$iso_customer=mslib_fe::getCountryByName($this->tta_user_info['default']['country']);
				$iso_customer['country']=$iso_customer['cn_short_en'];
				// if store country is different from customer country and user provided valid VAT id, change VAT rate to zero
				//$this->ms['MODULES']['DISABLE_VAT_RATE']=0;
				if (!$this->ms['MODULES']['DISABLE_VAT_RATE'] && $this->ms['MODULES']['DISABLE_VAT_FOR_FOREIGN_CUSTOMERS_WITH_COMPANY_VAT_ID'] and $row['billing_vat_id']) {
					if (strtolower($row['billing_country'])!=strtolower($this->tta_shop_info['country'])) {
						$this->ms['MODULES']['DISABLE_VAT_RATE']=1;
					}
				}
				// get shipping tax rate
				$shipping_method=mslib_fe::getShippingMethod($row['shipping_method'], 's.code', $iso_customer['cn_iso_nr']);
				$tax_rate=mslib_fe::taxRuleSet($shipping_method['tax_id'], 0, $iso_customer['cn_iso_nr'], 0);
				if (!$tax_rate['total_tax_rate']) {
					$tax_rate['total_tax_rate']=$this->ms['MODULES']['INCLUDE_VAT_OVER_METHOD_COSTS'];
				}
				if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
					$tax_rate['total_tax_rate']=0;
				}
				$shipping_tax_rate=($tax_rate['total_tax_rate']/100);
				// get payment tax rate
				$payment_method=mslib_fe::getPaymentMethod($row['payment_method'], 'p.code', $iso_customer['cn_iso_nr']);
				$tax_rate=mslib_fe::taxRuleSet($payment_method['tax_id'], 0, $iso_customer['cn_iso_nr'], 0);
				if (!$tax_rate['total_tax_rate']) {
					$tax_rate['total_tax_rate']=$this->ms['MODULES']['INCLUDE_VAT_OVER_METHOD_COSTS'];
				}
				if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
					$tax_rate['total_tax_rate']=0;
				}
				$payment_tax_rate=($tax_rate['total_tax_rate']/100);
				if ($shipping_tax_rate>0 or $payment_tax_rate>0) {
					// disable rounding to prevent cents short issue
					$shipping_tax=$row['shipping_method_costs']*$shipping_tax_rate;
					$payment_tax=$row['payment_method_costs']*$payment_tax_rate;
					/*if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
						$shipping_tax=round($row['shipping_method_costs']*$shipping_tax_rate, 2);
						$payment_tax=round($row['payment_method_costs']*$payment_tax_rate, 2);
					} else {
						$shipping_tax=$row['shipping_method_costs']*$shipping_tax_rate;
						$payment_tax=$row['payment_method_costs']*$payment_tax_rate;
					}*/
					$order_tax_data['shipping_total_tax_rate']=(string)number_format($shipping_tax_rate, 2, '.', ',');
					$order_tax_data['payment_total_tax_rate']=(string)number_format($payment_tax_rate, 2, '.', ',');
					$order_tax_data['shipping_tax']=(string)$shipping_tax;
					$order_tax_data['payment_tax']=(string)$payment_tax;
					//$total_tax+=$shipping_tax+$payment_tax;
					//$grand_total+=(($row['shipping_method_costs']+$row['payment_method_costs'])+($shipping_tax+$payment_tax));
				} else {
					//$grand_total+=($row['shipping_method_costs']+$row['payment_method_costs']);
				}
				$tax_separation[($shipping_tax_rate*100)]['shipping_tax']+=$shipping_tax;
				$tax_separation[($payment_tax_rate*100)]['payment_tax']+=$payment_tax;
				$tax_separation[($shipping_tax_rate*100)]['shipping_costs']=$row['shipping_method_costs'];
				$tax_separation[($payment_tax_rate*100)]['payment_costs']=$row['payment_method_costs'];
				//
				$product_tax_data['country_tax_rate']='0';
				$product_tax_data['region_tax_rate']='0';
				$product_tax_data['total_tax_rate']='0';
				$product_tax_data['country_tax']='0';
				$product_tax_data['region_tax']='0';
				$product_tax_data['total_tax']='0';
				$product_tax_data['total_attributes_tax']='0';
				$sql_prod="select * from tx_multishop_orders_products where orders_id = ".$row['orders_id'];
				$qry_prod=$GLOBALS['TYPO3_DB']->sql_query($sql_prod);
				while ($row_prod=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_prod)) {
					$tax_rate=$row_prod['products_tax']/100;
					$product_tax=unserialize($row_prod['products_tax_data']);
					// attributes tax
					$sql_attr="select * from tx_multishop_orders_products_attributes where orders_products_id = ".$row_prod['orders_products_id']." and orders_id = ".$row_prod['orders_id'];
					$qry_attr=$GLOBALS['TYPO3_DB']->sql_query($sql_attr);
					$attributes_tax=0;
					$tmp_attributes_price=0;
					$tmp_attributes_tax=0;
					while ($row_attr=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_attr)) {
						if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
							// remove the rounding to prevent cents short
							//$tmp_attributes_tax=round(($row_attr['price_prefix'].$row_attr['options_values_price'])*($tax_rate), 2);
							$tmp_attributes_tax=($row_attr['price_prefix'].$row_attr['options_values_price'])*($tax_rate);
						} else {
							$tmp_attributes_tax=mslib_fe::taxDecimalCrop(($row_attr['price_prefix'].$row_attr['options_values_price'])*($tax_rate));
						}
						$attributes_tax+=$tmp_attributes_tax;
						$tmp_attributes_price+=$row_attr['price_prefix'].$row_attr['options_values_price']*$row_prod['qty'];
						$sub_total+=($row_attr['price_prefix'].$row_attr['options_values_price'])*$row_prod['qty'];
						$sub_total_excluding_vat+=$row_attr['price_prefix'].$row_attr['options_values_price']*$row_prod['qty'];
						$grand_total+=$row_attr['price_prefix'].$row_attr['options_values_price']*$row_prod['qty'];
						// set the attributes tax data
						$attributes_tax_data=array();
						if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
							$attributes_tax_data['country_tax']=round(($row_attr['price_prefix'].$row_attr['options_values_price'])*$product_tax['country_tax_rate'], 2);
							$attributes_tax_data['region_tax']=round(($row_attr['price_prefix'].$row_attr['options_values_price'])*$product_tax['region_tax_rate'], 2);
							if ($attributes_tax_data['country_tax'] && $attributes_tax_data['region_tax']) {
								$attributes_tax_data['tax']=$attributes_tax_data['country_tax']+$attributes_tax_data['region_tax'];
							} else {
								$attributes_tax_data['tax']=round(($row_attr['price_prefix'].$row_attr['options_values_price'])*($tax_rate), 2);
							}
						} else {
							$attributes_tax_data['country_tax']=mslib_fe::taxDecimalCrop(($row_attr['price_prefix'].$row_attr['options_values_price'])*$product_tax['country_tax_rate']);
							$attributes_tax_data['region_tax']=mslib_fe::taxDecimalCrop(($row_attr['price_prefix'].$row_attr['options_values_price'])*$product_tax['region_tax_rate']);
							if ($attributes_tax_data['country_tax'] && $attributes_tax_data['region_tax']) {
								$attributes_tax_data['tax']=$attributes_tax_data['country_tax']+$attributes_tax_data['region_tax'];
							} else {
								$attributes_tax_data['tax']=mslib_fe::taxDecimalCrop(($row_attr['price_prefix'].$row_attr['options_values_price'])*($tax_rate));
							}
						}
						$serial_product_attributes_tax=serialize($attributes_tax_data);
						$sql_update="update tx_multishop_orders_products_attributes set attributes_tax_data = '".$serial_product_attributes_tax."' where orders_products_attributes_id='".$row_attr['orders_products_attributes_id']."' and orders_products_id = ".$row_attr['orders_products_id']." and orders_id = ".$row_attr['orders_id'];
						$GLOBALS['TYPO3_DB']->sql_query($sql_update);
					}
					$sub_total_tax+=$attributes_tax*$row_prod['qty'];
					$sub_total+=$attributes_tax*$row_prod['qty']; // subtotal including vat
					$grand_total+=$attributes_tax*$row_prod['qty'];
					$product_tax_data['total_attributes_tax']=(string)$attributes_tax;
					$product_tax_data['total_tax_rate']=(string)number_format($tax_rate, 2, '.', ',');
    				$final_price=$row_prod['final_price'];
					/*if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
						if (!empty($row_prod['discount_amount'])) {
							$final_price-=$row_prod['discount_amount'];
						}
					}*/
					//print_r($row_prod);
					// b2b mode 1 cent bugfix: 2013-05-09 cbc in grand total. this came from the products final price that must be round first
					// I have fixed the b2b issue by updating all the products prices in the database to have max 2 decimals
					// therefore I disabled below bugfix, cause thats a ducktape solution that can break b2c sites
					//$final_price=round($final_price,2);
					/*
					 * the rounding for excluding vat price is causing cents short
					 */
					/*if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
						$tax=round($final_price*$tax_rate, 2);
					} else {
						$tax=$final_price*$tax_rate;
					}
					*/
					$tax=$final_price*$tax_rate;

					$product_tax_data['total_tax']=(string)$tax;
					$sub_total_tax+=$tax*$row_prod['qty'];
					$sub_total+=($final_price+$tax)*$row_prod['qty'];
					$sub_total_excluding_vat+=($final_price)*$row_prod['qty'];
					$grand_total+=($final_price+$tax)*$row_prod['qty'];
					$serial_prod=serialize($product_tax_data);
					$sql_update="update tx_multishop_orders_products set products_tax_data = '".$serial_prod."' where orders_products_id = ".$row_prod['orders_products_id']." and orders_id = ".$row['orders_id'];
					$GLOBALS['TYPO3_DB']->sql_query($sql_update);
					// separation of tax
					$tax_separation[($row_prod['products_tax']/100)*100]['products_total_tax']+=($tax+$attributes_tax)*$row_prod['qty'];
					$tax_separation[($row_prod['products_tax']/100)*100]['products_sub_total_excluding_vat']+=($final_price+$tmp_attributes_price)*$row_prod['qty'];
					$tax_separation[($row_prod['products_tax']/100)*100]['products_sub_total']+=($final_price+$tmp_attributes_price)+($tax+$attributes_tax)*$row_prod['qty'];
				}
				$order_tax_data['total_orders_tax_including_discount']=$order_tax_data['total_orders_tax'];
				$order_tax_data['sub_total']=(string)$sub_total;
				$order_tax_data['sub_total_excluding_vat']=(string)$sub_total_excluding_vat;
				// discount
				//echo "<pre>";
				//echo $sub_total."<br/>";
				//echo $sub_total_excluding_vat."<br/>";
				if ($row['discount']>0) {
					if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
						$discount_price=round($row['discount'], 2);
						//$sub_total_excluding_vat-=$discount_price;
						$discount_percentage=(($discount_price/$sub_total_excluding_vat)*100);
						//$tmp_sub_total=(($sub_total_excluding_vat)/100*(100-$discount_percentage));
						if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
							$sub_total_tax=round((1-($discount_price/$sub_total))*$sub_total_tax, 2);
						} else {
							$sub_total_tax=((($sub_total-$sub_total_excluding_vat)/100)*(100-$discount_percentage));
						}
					} else {
						$discount_price=$row['discount'];
						//$sub_total-=$discount_price;
						$discount_percentage=(($discount_price/$sub_total)*100);
						//$tmp_sub_total=(($sub_total)/100*(100-$discount_percentage));
						//
						if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
							$sub_total_tax=round((1-($discount_price/$sub_total))*$sub_total_tax, 2);
						} else {
							$sub_total_tax=((($sub_total-$sub_total_excluding_vat)/100)*(100-$discount_percentage));
						}
					}
					if (count($tax_separation)>1) {
						$tax_separation=array();
					}
				}
				//echo $discount_percentage."<br/>";
				//echo $discount_price."<br/>";
				//echo $sub_total_tax."<br/>";
				//die();
				$order_tax_data['total_orders_tax']=(string)$sub_total_tax+$shipping_tax+$payment_tax;
				if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
					$order_tax_data['grand_total']=(string)(($sub_total_excluding_vat-$discount_price)+$sub_total_tax)+($row['shipping_method_costs']+$shipping_tax)+($row['payment_method_costs']+$payment_tax);
				} else {
					$order_tax_data['grand_total']=(string)(($sub_total-$discount_price))+($row['shipping_method_costs']+$shipping_tax)+($row['payment_method_costs']+$payment_tax);
				}
				$order_tax_data['grand_total_excluding_vat']=(string)($sub_total_excluding_vat-$discount_price)+($row['shipping_method_costs'])+($row['payment_method_costs']);
				//
				$order_tax_data['tax_separation']=$tax_separation;
				//print_r($order_tax_data);
				$serial_orders=serialize($order_tax_data);
				// update orders
				$updateArray=array();
				$updateArray['grand_total']=round($order_tax_data['grand_total'], 2);
				$updateArray['grand_total_excluding_vat']=round($order_tax_data['grand_total_excluding_vat'], 2);
				$updateArray['orders_tax_data']=$serial_orders;
				if ($row['discount']>0) {
					$updateArray['discount']=$discount_price;
				}
				$updateArray['orders_last_modified']=time();
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$row['orders_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				//$sql_update="update tx_multishop_orders set grand_total='".round($order_tax_data['grand_total'], 2)."', orders_tax_data = '".$serial_orders."' where orders_id = ".$row['orders_id'];
				//$GLOBALS['TYPO3_DB']->sql_query($sql_update);
			}
		}
	}
	function mailOrder($orders_id, $copy_to_merchant=1, $custom_email_address='', $mail_template='') {
		$order=mslib_fe::getOrder($orders_id);
		if ($order['orders_id']) {
			$order['mail_template']=$mail_template;
			if (isset($order['language_id'])) {
				// Switch to language that is stored in the order
				mslib_befe::setSystemLanguage($order['language_id']);
			}
			if (!$custom_email_address) {
				$custom_email_address=$order['billing_email'];
			}
			$billing_address='';
			$delivery_address='';
			$full_customer_name=$order['billing_first_name'];
			if ($order['billing_middle_name']) {
				$full_customer_name.=' '.$order['billing_middle_name'];
			}
			if ($order['billing_last_name']) {
				$full_customer_name.=' '.$order['billing_last_name'];
			}
			$delivery_full_customer_name=$order['delivery_first_name'];
			if ($order['delivery_middle_name']) {
				$delivery_full_customer_name.=' '.$order['delivery_middle_name'];
			}
			if ($order['delivery_last_name']) {
				$delivery_full_customer_name.=' '.$order['delivery_last_name'];
			}
			$full_customer_name=preg_replace('/\s+/', ' ', $full_customer_name);
			$delivery_full_customer_name=preg_replace('/\s+/', ' ', $delivery_full_customer_name);
			if (!$order['delivery_address'] or !$order['delivery_city']) {
				$order['delivery_company']=$order['billing_company'];
				$order['delivery_street_name']=$order['billing_street_name'];
				$order['delivery_address']=$order['billing_address'];
				$order['delivery_address_number']=$order['billing_address_number'];
				$order['delivery_address_ext']=$order['billing_address_ext'];
				$order['delivery_zip']=$order['billing_zip'];
				$order['delivery_city']=$order['billing_city'];
				$order['delivery_telephone']=$order['billing_telephone'];
				$order['delivery_mobile']=$order['billing_mobile'];
			}
			if ($order['delivery_company']) {
				$delivery_address=$order['delivery_company']."<br />";
			}
			if ($delivery_full_customer_name) {
				$delivery_address.=$delivery_full_customer_name."<br />";
			}
			if ($order['delivery_address']) {
				$delivery_address.=$order['delivery_address']."<br />";
			}
			if ($order['delivery_zip'] and $order['delivery_city']) {
				$delivery_address.=$order['delivery_zip']." ".$order['delivery_city'];
			}
			if ($order['delivery_country']) {
				$delivery_address.='<br />'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['delivery_country']);
			}
//		if ($order['delivery_telephone']) 		$delivery_address.=ucfirst($this->pi_getLL('telephone')).': '.$order['delivery_telephone']."<br />";
//		if ($order['delivery_mobile']) 			$delivery_address.=ucfirst($this->pi_getLL('mobile')).': '.$order['delivery_mobile']."<br />";
			if ($order['billing_company']) {
				$billing_address=$order['billing_company']."<br />";
			}
			if ($full_customer_name) {
				$billing_address.=$full_customer_name."<br />";
			}
			if ($order['billing_address']) {
				$billing_address.=$order['billing_address']."<br />";
			}
			if ($order['billing_zip'] and $order['billing_city']) {
				$billing_address.=$order['billing_zip']." ".$order['billing_city'];
			}
			if ($order['billing_country']) {
				$billing_address.='<br />'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['billing_country']);
			}
			$loadFromPids=array();
			if ($this->conf['masterShop']) {
				$loadFromPids[]=$order['page_uid'];
				$loadFromPids[]=$this->shop_pid;
				if ($this->showCatalogFromPage and $this->showCatalogFromPage!=$this->shop_pid) {
					$loadFromPids[]=$this->showCatalogFromPage;
				}
			}
			// psp email template
			$psp_mail_template=array();
			if ($order['payment_method']) {
				$psp_data=mslib_fe::loadPaymentMethod($order['payment_method']);
				$psp_vars=unserialize($psp_data['vars']);
				if (isset($psp_vars['order_confirmation'])) {
					$psp_mail_template['order_confirmation']='';
					if ($psp_vars['order_confirmation']>0) {
						$psp_mail_template['order_confirmation']=mslib_fe::getCMSType($psp_vars['order_confirmation']);
					}
				}
				if (isset($psp_vars['order_paid'])) {
					$psp_mail_template['order_paid']='';
					if ($psp_vars['order_paid']>0) {
						$psp_mail_template['order_paid']=mslib_fe::getCMSType($psp_vars['order_paid']);
					}
				}
			}
			// loading the email template
			$page=array();
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderPreCMSContent'])) {
				$params=array(
						'page'=>&$page,
						'order'=>&$order,
						'mail_template'=>$mail_template,
						'psp_mail_template'=>$psp_mail_template
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderPreCMSContent'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			if ($mail_template) {
				switch ($mail_template) {
					case 'email_order_paid_letter':
						if (isset($psp_mail_template['order_paid'])) {
							$page=array();
							if (!empty($psp_mail_template['order_paid'])) {
								$page=mslib_fe::getCMScontent($psp_mail_template['order_paid'], $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
							}
						} else {
							if ($order['payment_method']) {
								$page=mslib_fe::getCMScontent('email_order_paid_letter_'.$order['payment_method'], $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
							}
							if (!count($page[0])) {
								$page=mslib_fe::getCMScontent('email_order_paid_letter', $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
							}
						}
						break;
					default:
						$page=mslib_fe::getCMScontent($mail_template, $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
						break;
				}
			} else if ($order['is_proposal']) {
				// proposal template
				$mail_template='email_order_proposal';
				$page=mslib_fe::getCMScontent($mail_template, $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
			} else {
				// normal order template
				if (isset($psp_mail_template['order_confirmation'])) {
					$page=array();
					if (!empty($psp_mail_template['order_confirmation'])) {
						$page=mslib_fe::getCMScontent($psp_mail_template['order_confirmation'], $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
					}
				} else {
					if ($order['payment_method']) {
						$page=mslib_fe::getCMScontent('email_order_confirmation_'.$order['payment_method'], $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
					}
					if (!count($page[0])) {
						$page=mslib_fe::getCMScontent('email_order_confirmation', $GLOBALS['TSFE']->sys_language_uid, $loadFromPids);
					}
				}
			}
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderPostCMSContent'])) {
				$params=array(
						'page'=>&$page,
						'order'=>&$order,
						'mail_template'=>$mail_template,
						'psp_mail_template'=>$psp_mail_template
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderPostCMSContent'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			if ($page[0]['content']) {
				// loading the email confirmation letter eof
				// replacing the variables with dynamic values
				$array1=array();
				$array2=array();
				$array1[]='###GENDER_SALUTATION###';
				$array2[]=mslib_fe::genderSalutation($order['billing_gender']);
				// full billing name
				$array1[]='###BILLING_FULL_NAME###';
				$array2[]=$full_customer_name;
				$array1[]='###FULL_NAME###';
				$array2[]=$full_customer_name;
				$array1[]='###BILLING_NAME###';
				$array2[]=$order['billing_name'];
				$array1[]='###BILLING_COMPANY###';
				$array2[]=$order['billing_company'];
				$array1[]='###BILLING_FIRST_NAME###';
				$array2[]=$order['billing_first_name'];
				$array1[]='###BILLING_LAST_NAME###';
				$array2[]=preg_replace('/\s+/', ' ', $order['billing_middle_name'].' '.$order['billing_last_name']);
				$array1[]='###BILLING_EMAIL###';
				$array2[]=$order['billing_email'];
				$array1[]='###BILLING_TELEPHONE###';
				$array2[]=$order['billing_telephone'];
				$array1[]='###BILLING_MOBILE###';
				$array2[]=$order['billing_mobile'];
				// full delivery name
				$array1[]='###DELIVERY_NAME###';
				$array2[]=$order['delivery_name'];
				$array1[]='###DELIVERY_FULL_NAME###';
				$array2[]=$delivery_full_customer_name;
				$array1[]='###DELIVERY_COMPANY###';
				$array2[]=$order['delivery_company'];
				$array1[]='###DELIVERY_FIRST_NAME###';
				$array2[]=$order['delivery_first_name'];
				$array1[]='###DELIVERY_LAST_NAME###';
				$array2[]=preg_replace('/\s+/', ' ', $order['delivery_middle_name'].' '.$order['delivery_last_name']);
				$array1[]='###DELIVERY_EMAIL###';
				$array2[]=$order['delivery_email'];
				$array1[]='###DELIVERY_TELEPHONE###';
				$array2[]=$order['delivery_telephone'];
				$array1[]='###DELIVERY_MOBILE###';
				$array2[]=$order['delivery_mobile'];
				$array1[]='###CUSTOMER_EMAIL###';
				$array2[]=$order['billing_email'];
				if ($order['cruser_id'] && is_numeric($order['cruser_id'])) {
					$crUser=mslib_fe::getUser($order['cruser_id']);
					$array1[]='###CREATED_BY_FIRST_NAME###';
					$array2[]=preg_replace('/\s+/', ' ', $crUser['first_name']);
					$array1[]='###CREATED_BY_LAST_NAME###';
					$array2[]=preg_replace('/\s+/', ' ', $crUser['middle_name'].' '.$crUser['last_name']);
					$array1[]='###CREATED_BY_FULL_NAME###';
					$array2[]=preg_replace('/\s+/', ' ', $crUser['first_name'].' '.$crUser['middle_name'].' '.$crUser['last_name']);
				} else {
					$array1[]='###CREATED_BY_FIRST_NAME###';
					$array2[]='';
					$array1[]='###CREATED_BY_LAST_NAME###';
					$array2[]='';
					$array1[]='###CREATED_BY_FULL_NAME###';
					$array2[]='';
				}
				$time=$order['crdate'];
				$long_date=strftime($this->pi_getLL('full_date_format'), $time);
				$array1[]='###ORDER_DATE_LONG###'; // ie woensdag 23 juni, 2010
				$array2[]=$long_date;
				$array1[]='###ORDER_DATE###'; // 21-12-2010 in localized format
				$array2[]=strftime("%x", $time);
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
				$array2[]=mslib_fe::amount2Cents($order['total_amount']);
				$array1[]='###PROPOSAL_NUMBER###';
				$array2[]=$order['orders_id'];
				$array1[]='###ORDER_NUMBER###';
				$array2[]=$order['orders_id'];
				$array1[]='###ORDER_LINK###';
				$array2[]='';
				$array1[]='###ORDER_STATUS###';
				$array2[]=$order['orders_status'];
				$array1[]='###TRACK_AND_TRACE_CODE###';
				$array2[]=$order['track_and_trace_code'];
				$array1[]='###BILLING_ADDRESS###';
				$array2[]=$billing_address;
				$array1[]='###DELIVERY_ADDRESS###';
				$array2[]=$delivery_address;

				$array1[]='###BILLING_STREET_NAME###';
				$array2[]=$order['billing_street_name'];
				$array1[]='###BILLING_ADDRESS_NUMBER###';
				$array2[]=$order['billing_address_number'];
				$array1[]='###BILLING_ADDRESS_EXT###';
				$array2[]=$order['billing_address_ext'];
				$array1[]='###BILLING_ZIP###';
				$array2[]=$order['billing_zip'];
				$array1[]='###BILLING_CITY###';
				$array2[]=$order['billing_city'];
				$array1[]='###BILLING_COUNTRY###';
				$array2[]=mslib_fe::getTranslatedCountryNameByEnglishName($this->lang,$order['billing_country']);

				$array1[]='###DELIVERY_STREET_NAME###';
				$array2[]=$order['delivery_street_name'];
				$array1[]='###DELIVERY_ADDRESS_NUMBER###';
				$array2[]=$order['delivery_address_number'];
				$array1[]='###DELIVERY_ADDRESS_EXT###';
				$array2[]=$order['delivery_address_ext'];
				$array1[]='###DELIVERY_ZIP###';
				$array2[]=$order['delivery_zip'];
				$array1[]='###DELIVERY_CITY###';
				$array2[]=$order['delivery_city'];
				$array1[]='###DELIVERY_COUNTRY###';
				$array2[]=mslib_fe::getTranslatedCountryNameByEnglishName($this->lang,$order['delivery_country']);

				$array1[]='###CUSTOMER_ID###';
				$array2[]=$order['customer_id'];

				$ORDER_DETAILS=self::printOrderDetailsTable($order, 'email');
				if ($this->ms['MODULES']['CREATE_INVOICE_DIRECTLY_AFTER_CREATING_ORDER']) {
					// FORCE CREATE INVOICE IF NOT ALREADY EXISTING
					$invoice=mslib_fe::getOrderInvoice($order['orders_id'], 1);
				} else {
					$invoice=mslib_fe::getOrderInvoice($order['orders_id'], 0);
				}
				$invoice_id='';
				$invoice_link='';
				if (is_array($invoice)) {
					$invoice_id=$invoice['invoice_id'];
					$invoice_link='<a href="'.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'">'.$invoice['invoice_id'].'</a>';
				}
				$array1[]='###INVOICE_NUMBER###';
				$array2[]=$invoice_id;
				$array1[]='###INVOICE_LINK###';
				$array2[]=$invoice_link;
				$array1[]='###ORDER_DETAILS###';
				$array2[]=$ORDER_DETAILS;
				$array1[]='###SHIPPING_METHOD###';
				$array2[]=$order['shipping_method_label'];
				$array1[]='###PAYMENT_METHOD###';
				$array2[]=$order['payment_method_label'];
				$array1[]='###EXPECTED_DELIVERY_DATE###';
				$array2[]=strftime("%x", $order['expected_delivery_date']);
				$array1[]='###CUSTOMER_COMMENTS###';
				$array2[]=$order['customer_comments'];
				if (isset($this->post['password']) && !empty($this->post['password']) && $order['customer_id']>0) {
					$user=mslib_fe::getUser($order['customer_id']);
					//
					$array1[]='###USERNAME###';
					$array2[]=$user['username'];
					$array1[]='###PASSWORD###';
					$array2[]=$this->post['password'];
				}
				//hook to let other plugins further manipulate the replacers
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderReplacersPostProc'])) {
					$params=array(
							'array1'=>&$array1,
							'array2'=>&$array2,
							'order'=>&$order,
							'mail_template'=>$mail_template
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderReplacersPostProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				if ($page[0]['content']) {
					$page[0]['content']=str_replace($array1, $array2, $page[0]['content']);
				}
				if ($page[0]['name']) {
					$page[0]['name']=str_replace($array1, $array2, $page[0]['name']);
				}
				// replacing the variables with dynamic values eof
				$user=array();
				$user['name']=$full_customer_name;
				$user['email']=$custom_email_address;
				//hook
				$send_mail=1;
				$mail_attachment=array();
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrder'])) {
					$params=array(
							'this'=>&$this,
							'page'=>$page,
							'content'=>&$content,
							'send_mail'=>&$send_mail,
							'user'=>$user,
							'order'=>$order,
							'order_details'=>$ORDER_DETAILS,
							'copy_to_merchant'=>$copy_to_merchant,
							'mail_attachment'=>&$mail_attachment
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrder'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				if ($send_mail) {
					if ($user['email']) {
						if (strpos($mail_template, 'email_order_paid_letter')!==false && $this->ms['MODULES']['ATTACH_INVOICE_PDF_IN_PAID_LETTER_EMAIL']>0) {
							$filterInvoice=array();
							$filterInvoice[]='orders_id='.$order['orders_id'];
							$invoices=mslib_befe::getRecords('','tx_multishop_invoices','',$filterInvoice,'','id desc');
							$invoice=$invoices[0];
							$pdfFileName=$invoice['invoice_id'].'_'.$invoice['orders_id'].'.pdf';
							$pdfFilePath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$pdfFileName;
							// generate the invoice PDF
							// Get Language code (ie nl, en, de)
							$language_code = mslib_befe::getLanguageIso2ByLanguageUid($order['language_id']);
							$language_code = strtolower($language_code);
							// Download invoice in the language of the order
							$invoice_data=mslib_fe::file_get_contents($this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash'].'&tx_multishop_pi1[forceRecreate]=1&language='.$language_code));
							file_put_contents($pdfFilePath, $invoice_data);
							$mail_attachment[]=$pdfFilePath;
						}
						mslib_fe::mailUser($user, $page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME'], $mail_attachment);
						if (strpos($mail_template, 'email_order_paid_letter')!==false && $this->ms['MODULES']['ATTACH_INVOICE_PDF_IN_PAID_LETTER_EMAIL']>0 && file_exists($pdfFilePath)) {
							unlink($pdfFilePath);
						}
					}
					if ($copy_to_merchant) {
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderToMerchant'])) {
							$params=array(
									'this'=>&$this,
									'page'=>$page,
									'content'=>&$content,
									'send_mail'=>&$send_mail,
									'user'=>$user,
									'order'=>$order,
									'order_details'=>$ORDER_DETAILS,
									'mail_attachment'=>&$mail_attachment
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['mailOrderToMerchant'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						// now mail a copy to the merchant
						$merchant=array();
						$merchant['name']=$this->ms['MODULES']['STORE_NAME'];
						$merchant['email']=$this->ms['MODULES']['STORE_EMAIL'];
						mslib_fe::mailUser($merchant, 'Copy for merchant: '.$page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME'], $mail_attachment);
						if ($this->ms['MODULES']['SEND_ORDER_CONFIRMATION_LETTER_ALSO_TO']) {
							$email=array();
							if (!strstr($this->ms['MODULES']['SEND_ORDER_CONFIRMATION_LETTER_ALSO_TO'], ",")) {
								$email[]=$this->ms['MODULES']['SEND_ORDER_CONFIRMATION_LETTER_ALSO_TO'];
							} else {
								$email=explode(',', $this->ms['MODULES']['SEND_ORDER_CONFIRMATION_LETTER_ALSO_TO']);
							}
							if (count($email)) {
								foreach ($email as $item) {
									$merchant=array();
									$merchant['name']=$this->ms['MODULES']['STORE_NAME'];
									$merchant['email']=$item;
									mslib_fe::mailUser($merchant, 'Copy for merchant: '.$page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME'], $mail_attachment);
								}
							}
						}
					}
				}
			}
			if (isset($order['language_id'])) {
				// Switch back to default language
				mslib_befe::resetSystemLanguage();
			}
			return 1;
		}
	}
	function printOrderDetailsTable($order, $template_type='site') {
		$subtotalIncludingVatArray=array();
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['printOrderDetailsTablePreHook'])) {
			$params=array(
				'order'=>&$order,
				'template_type'=>&$template_type
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['printOrderDetailsTablePreHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		switch ($template_type) {
			case 'site':
			case 'order_history_site':
				if ($this->conf['order_details_table_site_tmpl_path']) {
					$template=$this->cObj->fileResource($this->conf['order_details_table_site_tmpl_path']);
				} else {
					if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
						$template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop') . 'templates/order_details_table_site_excluding_vat.tmpl');
					} else {
						$template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop') . 'templates/order_details_table_site.tmpl');
					}
				}
				break;
			case 'email':
				if ($this->conf['order_details_table_email_tmpl_path']) {
					$template=$this->cObj->fileResource($this->conf['order_details_table_email_tmpl_path']);
				} else {
					$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'templates/order_details_table_email.tmpl');
				}
				break;
			case 'pdf':
				if ($this->conf['order_details_table_pdf_tmpl_path']) {
					$template=$this->cObj->fileResource($this->conf['order_details_table_pdf_tmpl_path']);
				} else {
					$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'templates/order_details_table_pdf.tmpl');
				}
				break;
		}
		$itemsWrapper=array();
		$c=true;
		foreach ($order['products'] as $product) {
			if ($product['products_id']) {
				$product_db=mslib_fe::getProduct($product['products_id']);
			}
			$subprices='';
			$price=$product['qty']*$product['final_price'];
			$item=array();
			// ITEM CLASS
			$item['ITEM_CLASS']=(($c=!$c) ? 'odd' : 'even');
			// ITEM IMAGE
			if (!$product_db['products_image']) {
				$item['ITEM_IMAGE']='<div class="no_image_50"></div>';
			} else {
				if (!strstr(mslib_befe::strtolower($product_db['products_image']), 'http://') and !strstr(mslib_befe::strtolower($product_db['products_image']), 'https://')) {
					$product_db['products_image']=mslib_befe::getImagePath($product_db['products_image'], 'products', '50');
				}
				$item['ITEM_IMAGE']='<img src="'.$product_db['products_image'].'" title="'.htmlspecialchars($product['products_name']).'">';
			}
			// ITEM_NAME
			$tmp_item_name=array();
			$tmp_item_name['products_name']=htmlspecialchars($product['products_name']);
			$tmp_item_name['products_model']='';
			if ($this->ms['MODULES']['DISPLAY_PRODUCTS_MODEL_IN_ORDER_DETAILS']=='1' && !empty($product['products_model'])) {
				$tmp_item_name['products_model']=' ('.htmlspecialchars($product['products_model']).') ';
			}
			// for virtual product download link
			if ($template_type=='email' && $order['mail_template']=='email_order_paid_letter' && $order['paid']==1 && isset($product['file_download_code']) && !empty($product['file_download_code'])) {
				$download_link='<br/><a href="'.$this->FULL_HTTP_URL.mslib_fe::typolink(",2002", '&tx_multishop_pi1[page_section]=get_micro_download&orders_id='.$order['orders_id'].'&code='.$product['file_download_code'], 1).'" alt="'.htmlspecialchars($product['products_name']).'" title="'.htmlspecialchars($product['products_name']).'">Download product</a>';
				$tmp_item_name['download_link']=$download_link;
			}
			if ($product['products_description']) {
				$tmp_item_name['products_description']='<br/>'.nl2br(htmlspecialchars($product['products_description']));
			}
			if ($this->ms['MODULES']['DISPLAY_EAN_IN_ORDER_DETAILS']=='1' && !empty($product['ean_code'])) {
				$tmp_item_name['products_ean']='<br/>EAN: '.htmlspecialchars($product['ean_code']);
			}
			if ($this->ms['MODULES']['DISPLAY_SKU_IN_ORDER_DETAILS']=='1' && !empty($product['sku_code'])) {
				$tmp_item_name['products_sku']='<br/>SKU: '.htmlspecialchars($product['sku_code']);
			}
			if ($this->ms['MODULES']['DISPLAY_VENDOR_IN_ORDER_DETAILS']=='1' && !empty($product['vendor_code'])) {
				$tmp_item_name['products_vendor_code']='<br/>'.htmlspecialchars($this->pi_getLL('label_order_details_vendor_code', 'Vendor code')).': '.htmlspecialchars($product['vendor_code']);
			}
			if (count($product['attributes'])) {
				foreach ($product['attributes'] as $tmpkey=>$options) {
					$subprices.='<BR>';
					if ($this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
						$tmp_tax=round(($options['options_values_price']*($product['products_tax']/100)), 2);
						$attribute_price=+$options['options_values_price']+$tmp_tax;
					} else if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
						$attribute_price=round(($options['options_values_price']*($product['products_tax']/100)), 4)+$options['options_values_price'];
					} else {
						$attribute_price=$options['options_values_price'];
					}
					$tmp_item_name['products_attributes'][]='<BR>'.htmlspecialchars($options['products_options']).': '.htmlspecialchars($options['products_options_values']);
					$price=$price+($product['qty']*($options['price_prefix'].$options['options_values_price']));
					if ($price<0) {
						$price=0;
					}
					if ($options['options_values_price']>0) {
						$subprices.=mslib_fe::amount2Cents(($product['qty']*($options['price_prefix'].$attribute_price)));
					}
				}
			}
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order']['printOrderDetailsTableItemNamePreProc'])) {
				$params=array(
					'item_name'=>&$tmp_item_name,
					'order'=>&$order,
					'product'=>&$product,
					'template_type'=>&$template_type
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order']['printOrderDetailsTableItemNamePreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			$item_name='';
			if (is_array($tmp_item_name) && count($tmp_item_name)) {
				foreach ($tmp_item_name as $array_item_name) {
					if (is_array($array_item_name) && count($array_item_name)) {
						$item_name .= implode('', $array_item_name);
					} else {
						$item_name .= $array_item_name;
					}
				}
			}
			$item['ITEM_NAME']=$item_name;
			$item['ITEM_MODEL']=htmlspecialchars($product_db['products_model']);
			// ITEM NAME EOF
			// ITEM_QUANTITY
			$item['ITEM_QUANTITY']=round($product['qty'], 14);
			// ITEM_SKU
			$item['ITEM_SKU']=htmlspecialchars($product_db['sku_code']);
			// ITEM_TOTAL
			if ($this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
				$tmp_tax=round(($product['final_price']*($product['products_tax']/100)), 2);
				$final_price=($product['qty']*($product['final_price']+$tmp_tax));
				$item['ITEM_PRICE_SINGLE']=mslib_fe::amount2Cents($product['final_price']+$tmp_tax);
			} else if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$final_price=($product['qty']*$product['final_price']);
				$final_price=round(($final_price*($product['products_tax']/100)), 4)+$final_price;
				$item['ITEM_PRICE_SINGLE']=mslib_fe::amount2Cents(round(($product['final_price']*($product['products_tax']/100)), 4)+$product['final_price']);
			} else {
				$final_price=($product['qty']*$product['final_price']);
				$item['ITEM_PRICE_SINGLE']=mslib_fe::amount2Cents($product['final_price']);
			}
			$item['ITEM_TOTAL']=mslib_fe::amount2Cents($final_price).$subprices;

			if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0 && $template_type=='order_history_site') {
				$item['ITEM_PRODUCT_STATUS']=htmlspecialchars(mslib_fe::getOrderStatusName($product['status']));
			}
			$item['ITEM_VAT_RATE']=str_replace('.00', '', number_format($product['products_tax'], 2)).'%';
			// GRAND TOTAL CALCULATIONS
			$subtotal=($subtotal+$price);
			$subtotal_tax=($subtotal_tax+$product['products_tax_data']['total_tax']+$product['products_tax_data']['total_attributes_tax']);
			$subtotalIncludingVatArray[]=$product['total_price'];
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                $subtotalIncludingVatArray[] = mslib_fe::taxDecimalCrop($product['qty'] * $product['products_tax_data']['total_tax'], 2, true);
            } else {
                $subtotalIncludingVatArray[] = mslib_fe::taxDecimalCrop($product['qty'] * $product['products_tax_data']['total_tax'], 2, false);
            }
			$subtotalIncludingVatArray[]=$product['qty']*$product['products_tax_data']['total_attributes_tax'];
			// GRAND TOTAL CALCULATIONS EOF
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order']['printOrderDetailsTableItemPreProc'])) {
				$params=array(
					'item'=>&$item,
					'order'=>&$order,
					'product'=>&$product,
					'template_type'=>&$template_type
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order']['printOrderDetailsTableItemPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			$itemsWrapper[]=$item;
		}
		// MERGE TO TEMPLATE
		// Extract the subparts from the template
		$subparts=array();
		$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
		$subparts['ITEMS_HEADER_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###ITEMS_HEADER_WRAPPER###');
		$subparts['ITEMS_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###ITEMS_WRAPPER###');
		$subparts['SUBTOTAL_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###SUBTOTAL_WRAPPER###');
		$subparts['SHIPPING_COSTS_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###SHIPPING_COSTS_WRAPPER###');
		$subparts['PAYMENT_COSTS_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###PAYMENT_COSTS_WRAPPER###');
		$subparts['GRAND_TOTAL_EXCLUDING_VAT_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###GRAND_TOTAL_EXCLUDING_VAT_WRAPPER###');
		$subparts['GRAND_TOTAL_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###GRAND_TOTAL_WRAPPER###');
		$subparts['TAX_COSTS_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###TAX_COSTS_WRAPPER###');
		$subparts['DISCOUNT_WRAPPER']=$this->cObj->getSubpart($subparts['template'], '###DISCOUNT_WRAPPER###');
		$subparts['NEWSUBTOTAL_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###NEWSUBTOTAL_WRAPPER###');
		if (!$this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] || $template_type!='order_history_site') {
			$subProductStatusPart=array();
			$subProductStatusPart['ITEMS_HEADER_PRODUCT_STATUS_WRAPPER']=$this->cObj->getSubpart($subparts['ITEMS_HEADER_WRAPPER'], '###ITEMS_HEADER_PRODUCT_STATUS_WRAPPER###');
			$subProductStatus=array();
			$subProductStatus['###ITEMS_HEADER_PRODUCT_STATUS_WRAPPER###']='';
			$subparts['ITEMS_HEADER_WRAPPER']=$this->cObj->substituteMarkerArrayCached($subparts['ITEMS_HEADER_WRAPPER'], array(), $subProductStatus);
			$subProductStatusPart=array();
			$subProductStatusPart['ITEMS_PRODUCT_STATUS_WRAPPER']=$this->cObj->getSubpart($subparts['ITEMS_WRAPPER'], '###ITEMS_PRODUCT_STATUS_WRAPPER###');
			$subProductStatus=array();
			$subProductStatus['###ITEMS_PRODUCT_STATUS_WRAPPER###']='';
			$subparts['ITEMS_WRAPPER']=$this->cObj->substituteMarkerArrayCached($subparts['ITEMS_WRAPPER'], array(), $subProductStatus);
		}
		$subpartArray=array();
		//ITEMS_HEADER_WRAPPER
		$markerArray=array();
		$markerArray['HEADING_PRODUCTS_NAME']=ucfirst($this->pi_getLL('product'));
		$markerArray['HEADING_SKU']=$this->pi_getLL('sku', 'SKU');
		$markerArray['HEADING_QUANTITY']=$this->pi_getLL('qty');
		$markerArray['HEADING_PRICE']=$this->pi_getLL('price');
		if (!$order['orders_tax_data']['total_orders_tax']) {
			$markerArray['HEADING_TOTAL']=ucfirst($this->pi_getLL('total_excl_vat'));
		} else {
			$markerArray['HEADING_TOTAL']=$this->pi_getLL('total');
		}
		$markerArray['HEADING_TOTAL']=$this->pi_getLL('total');
		$markerArray['HEADING_VAT_RATE']=$this->pi_getLL('vat');
		if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0 && $template_type=='order_history_site') {
			$markerArray['HEADING_PRODUCT_STATUS']=$this->pi_getLL('order_product_status');
		}
		$subpartArray['###ITEMS_HEADER_WRAPPER###']=$this->cObj->substituteMarkerArray($subparts['ITEMS_HEADER_WRAPPER'], $markerArray, '###|###');
		//ITEMS_HEADER_WRAPPER EOF
		//ITEMS_WRAPPER
		$keys=array();
		$keys[]='ITEM_CLASS';
		$keys[]='ITEM_IMAGE';
		$keys[]='ITEM_NAME';
		$keys[]='ITEM_QUANTITY';
		$keys[]='ITEM_SKU';
		$keys[]='ITEM_VAT_RATE';
		$keys[]='ITEM_TOTAL';
		$keys[]='ITEM_PRICE_SINGLE';
		if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0 && $template_type=='order_history_site') {
			$keys[]='ITEM_PRODUCT_STATUS';
		}
		foreach ($itemsWrapper as $item) {
			$markerArray=array();
			foreach ($keys as $key) {
				$markerArray[$key]=$item[$key];
			}
			foreach ($item as $key=>$val) {
				// hooked plugins wants to add more types. lets find them and add them
				if (!in_array($key, $keys)) {
					$markerArray[$key]=$item[$key];
				}
			}
			$contentItem.=$this->cObj->substituteMarkerArray($subparts['ITEMS_WRAPPER'], $markerArray, '###|###');
		}
		$subpartArray['###ITEMS_WRAPPER###']=$contentItem;
		//ITEMS_WRAPPER EOF
		//SUBTOTAL_WRAPPER
		$key='SUBTOTAL_WRAPPER';
		$markerArray=array();
		$markerArray['SUBTOTAL_LABEL']=$this->pi_getLL('subtotal');
		$markerArray['PRODUCTS_TOTAL_PRICE_LABEL']=$this->pi_getLL('total_price');
		$markerArray['PRODUCTS_TOTAL_PRICE_INCLUDING_VAT_LABEL']=$this->pi_getLL('total_price');
		$markerArray['PRODUCTS_SUB_TOTAL_PRICE_LABEL']=$this->pi_getLL('subtotal');
		// rounding is problem with including vat shops.
		$markerArray['PRODUCTS_TOTAL_PRICE_INCLUDING_VAT'] = mslib_fe::amount2Cents(mslib_fe::taxDecimalCrop(array_sum($subtotalIncludingVatArray),2,true));
		//$markerArray['PRODUCTS_TOTAL_PRICE_INCLUDING_VAT']=mslib_fe::amount2Cents(array_sum($subtotalIncludingVatArray));
		$markerArray['PRODUCTS_TOTAL_PRICE']=mslib_fe::amount2Cents($subtotal);
		$subpartArray['###'.$key.'###']=$this->cObj->substituteMarkerArray($subparts[$key], $markerArray, '###|###');
		//SUBTOTAL_WRAPPER EOF
		//SHIPPING_COSTS_WRAPPER
		$key='SHIPPING_COSTS_WRAPPER';
		if ($order['shipping_method_label']) {
			$markerArray=array();
			$markerArray['SHIPPING_COSTS_LABEL']=$this->pi_getLL('shipping_costs').' ('.$order['shipping_method_label'].')';
			$markerArray['SHIPPING_COSTS']=mslib_fe::amount2Cents($order['shipping_method_costs']);
			$markerArray['SHIPPING_COSTS_INCLUDING_VAT_LABEL']=$this->pi_getLL('shipping_costs').' ('.$order['shipping_method_label'].')';
			$markerArray['SHIPPING_COSTS_INCLUDING_VAT']=mslib_fe::amount2Cents(($order['shipping_method_costs']+$order['orders_tax_data']['shipping_tax']));
			$subpartArray['###'.$key.'###']=$this->cObj->substituteMarkerArray($subparts[$key], $markerArray, '###|###');
			if ($order['shipping_method_costs']=='0') {
				if ($this->ms['MODULES']['CHECKOUT_HIDE_ZERO_SHIPPING_COSTS_IN_SUMMARY']=='1') {
					$subpartArray['###'.$key.'###']='';
				}
			}
		} else {
			$subpartArray['###'.$key.'###']='';
		}
		//SHIPPING_COSTS_WRAPPER EOF
		//PAYMENT_COSTS_WRAPPER
		$key='PAYMENT_COSTS_WRAPPER';
		if ($order['payment_method_label']) {
			$markerArray=array();
			$markerArray['PAYMENT_COSTS_LABEL']=$this->pi_getLL('payment_costs').' ('.$order['payment_method_label'].')';
			$markerArray['PAYMENT_COSTS']=mslib_fe::amount2Cents($order['payment_method_costs']);
			$markerArray['PAYMENT_COSTS_INCLUDING_VAT_LABEL']=$this->pi_getLL('payment_costs').' ('.$order['payment_method_label'].')';
			$markerArray['PAYMENT_COSTS_INCLUDING_VAT']=mslib_fe::amount2Cents(($order['payment_method_costs']+$order['orders_tax_data']['payment_tax']));
			$subpartArray['###'.$key.'###']=$this->cObj->substituteMarkerArray($subparts[$key], $markerArray, '###|###');
			if ($order['payment_method_costs']=='0') {
				if ($this->ms['MODULES']['CHECKOUT_HIDE_ZERO_PAYMENT_COSTS_IN_SUMMARY']=='1') {
					$subpartArray['###'.$key.'###']='';
				}
			}
		} else {
			$subpartArray['###'.$key.'###']='';
		}
		//PAYMENT_COSTS_WRAPPER EOF

		//GRAND_TOTAL_EXCLUDING_VAT_WRAPPER
		$key='GRAND_TOTAL_EXCLUDING_VAT_WRAPPER';
		$markerArray=array();
		$markerArray['PRODUCTS_GRAND_TOTAL_EXCLUDING_VAT_LABEL']=$this->pi_getLL('grand_total_excluding_vat');
		$markerArray['PRODUCTS_GRAND_TOTAL_EXCLUDING_VAT_PRICE']=mslib_fe::amount2Cents($order['grand_total_excluding_vat']);
		$subpartArray['###'.$key.'###']=$this->cObj->substituteMarkerArray($subparts[$key], $markerArray, '###|###');
		//GRAND_TOTAL_EXCLUDING_VAT_WRAPPER EOF

		//GRAND_TOTAL_WRAPPER
		$key='GRAND_TOTAL_WRAPPER';
		$markerArray=array();
		if (!$order['orders_tax_data']['total_orders_tax']) {
			$markerArray['GRAND_TOTAL_COSTS_LABEL']=ucfirst($this->pi_getLL('total_excl_vat'));
		} else {
			$markerArray['GRAND_TOTAL_COSTS_LABEL']=ucfirst($this->pi_getLL('total'));
		}
//		$markerArray['GRAND_TOTAL_COSTS'] = mslib_fe::amount2Cents($subtotal+$order['orders_tax_data']['total_orders_tax']+$order['payment_method_costs']+$order['shipping_method_costs']-$order['discount']);
		$markerArray['GRAND_TOTAL_COSTS']=mslib_fe::amount2Cents($order['orders_tax_data']['grand_total']);
		$subpartArray['###'.$key.'###']=$this->cObj->substituteMarkerArray($subparts[$key], $markerArray, '###|###');
		//GRAND_TOTAL_WRAPPER EOF
		//DISCOUNT_WRAPPER
		$key='DISCOUNT_WRAPPER';
		if ($order['discount']>0) {
			$markerArray=array();
			$markerArray['DISCOUNT_LABEL']=$this->pi_getLL('discount');
			$markerArray['DISCOUNT']=mslib_fe::amount2Cents($order['discount']);
			$subpartArray['###'.$key.'###']=$this->cObj->substituteMarkerArray($subparts[$key], $markerArray, '###|###');
		} else {
			$subpartArray['###'.$key.'###']='';
			$subpartArray['###NEWSUBTOTAL_WRAPPER###']='';
		}
		//DISCOUNT_WRAPPER EOF
		if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			// new subtotal
			$markerArray = array();
			$markerArray['PRODUCTS_NEWSUB_TOTAL_PRICE_LABEL'] = $this->pi_getLL('new_subtotal_excl_vat');
			$new_subtotal_amount=($subtotal - $order['discount']) + $order['shipping_method_costs'] + $order['payment_method_costs'];
			$markerArray['PRODUCTS_NEWTOTAL_PRICE'] = mslib_fe::amount2Cents($new_subtotal_amount);
			$subpartArray['###NEWSUBTOTAL_WRAPPER###'] = $this->cObj->substituteMarkerArray($subparts['NEWSUBTOTAL_WRAPPER'], $markerArray, '###|###');
		} else {
			$subpartArray['###NEWSUBTOTAL_WRAPPER###']='';
		}
		//TAX_COSTS_WRAPPER
		$key='TAX_COSTS_WRAPPER';
		if ($order['orders_tax_data']['total_orders_tax']) {
			$markerArray=array();
			$markerArray['TAX_RATE_LABEL']=$this->pi_getLL('vat');
			$markerArray['INCLUDED_TAX_RATE_LABEL']=$this->pi_getLL('included_vat_amount');
			$markerArray['TAX_COSTS']=mslib_fe::amount2Cents($order['orders_tax_data']['total_orders_tax']);
			$subpartArray['###'.$key.'###']=$this->cObj->substituteMarkerArray($subparts[$key], $markerArray, '###|###');
		} else {
			$subpartArray['###'.$key.'###']='';
		}
		// finally convert global markers and return output
		//hook to let other plugins further manipulate the replacers
		$content=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order']['printOrderDetailsTablePostProc'])) {
			$params=array(
				'content'=>&$content,
				'order'=>&$order,
				'template_type'=>&$template_type,
				'subparts'=>&$subparts,
				'subpartArray'=>&$subpartArray,
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order']['printOrderDetailsTablePostProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		return $content;
	}
	function getOrderTotalPrice($orders_id, $skip_method_costs=0) {
		$order=mslib_fe::getOrder($orders_id);
		if ($skip_method_costs) {
			return round($order['orders_tax_data']['sub_total'], 2);
		} else {
			return round($order['orders_tax_data']['grand_total'], 2);
		}
	}
	function getOrder($string, $field='orders_id', $includeDeleted=0) {
		$filter=array();
		switch ($field) {
			case 'orders_id':
				if (!is_numeric($string)) {
					return false;
				}
				$filter[]="o.orders_id='".addslashes($string)."'";
				break;
			case 'hash':
				if (!$string) {
					return false;
				}
				$filter[]="o.hash='".addslashes($string)."'";
				break;
		}
		if (!count($filter)) {
			return false;
		}
		if (!$includeDeleted) {
			$filter[]='o.deleted=0';
		}
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('o.*, osd.name as orders_status', // SELECT ...
			'tx_multishop_orders o left join tx_multishop_orders_status os on o.status=os.id left join tx_multishop_orders_status_description osd on (os.id=osd.orders_status_id AND o.language_id=osd.language_id)', // FROM ...
			implode(" and ", $filter), // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
            $orders=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
            $orders['orders_tax_data']=unserialize($orders['orders_tax_data']);
            $full_customer_name=$orders['billing_first_name'];
            if ($orders['billing_middle_name']) {
                $full_customer_name.=' '.$orders['billing_middle_name'];
            }
            if ($orders['billing_last_name']) {
                $full_customer_name.=' '.$orders['billing_last_name'];
            }
            $orders['billing_full_name']=$full_customer_name;
            // load products
            $total_amount=0;
            $orders_products=array();
            $str2="SELECT * from tx_multishop_orders_products where orders_id='".$orders['orders_id']."' order by sort_order asc";
            $qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
            while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) {
                $row['products_tax_data']=unserialize($row['products_tax_data']);
                $product_amount=0;
				$final_price=$row['final_price'];
				/*if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
					$final_price-=$row['discount_amount'];
				}*/
                $product_amount=($row['qty']*$final_price);
                // now count the attributes
                $str3="SELECT * from tx_multishop_orders_products_attributes where orders_products_id='".$row['orders_products_id']."' order by orders_products_attributes_id asc";
                $qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
                while ($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3)) {
                    $row3['attributes_tax_data']=unserialize($row3['attributes_tax_data']);
                    if ($row3['price_prefix']=='+') {
                        $product_amount=$product_amount+($row['qty']*$row3['options_values_price']);
                    } else {
                        $product_amount=$product_amount-($row['qty']*$row3['options_values_price']);
                    }
                    $row['attributes'][]=$row3;
                }
                // now count the attributes eof
                $total_amount=$total_amount+$product_amount;
                $row['total_price']=round($product_amount, 2);
                $orders_products[$row['orders_products_id']]=$row;
            }
            // load products eof
            $total_tax=0;
            foreach ($orders_products as $key=>$orders_product) {
                $total_tax+=$orders_product['products_tax_data']['total_tax']+$orders_product['products_tax_data']['total_attributes_tax'];
            }
            $orders['products']=$orders_products;
            $orders['subtotal_tax']=$orders['orders_tax_data']['total_orders_tax'];
            $orders['subtotal_amount']=round($total_amount, 2);
            $orders['shipping_method_costs']=round($orders['shipping_method_costs'], 2);
            $orders['payment_method_costs']=round($orders['payment_method_costs'], 2);
            /* if ($orders['orders_tax_data']['shipping_tax'] || $orders['orders_tax_data']['payment_tax']) {
                $extra_vat=0;
                if ($orders['shipping_method_costs']) 	{
                    $extra_vat += $orders['orders_tax_data']['shipping_tax'];
                }

                if ($orders['payment_method_costs']) {
                    $extra_vat += $orders['orders_tax_data']['payment_tax'];
                }

                $orders['subtotal_tax'] = ($orders['subtotal_tax'] + $extra_vat);
            } */
            $orders['total_amount']=round($orders['orders_tax_data']['grand_total'], 2);
            if ($orders['total_amount']>0 && $orders['total_amount']<0.01) {
                $orders['total_amount']=0;
            }
            //round($orders['subtotal_amount']+$orders['subtotal_tax']+$orders['payment_method_costs']+$orders['shipping_method_costs']-$orders['discount'],2);
            return $orders;
        }
        return false;
	}
	function createOrder($address) {
		if (is_numeric($address['uid'])) {
			$customer_id=$address['uid'];
		} else {
			if (!$address['email']) {
				return false;
			}
			$tmp_user=mslib_fe::getUser($address['email'], 'email');
			if ($tmp_user['uid']) {
				$customer_id=$tmp_user['uid'];
			}
		}
		if (!$customer_id) {
			// add new account
			$insertArray=array();
			$insertArray['page_uid']=$this->shop_pid;
			$insertArray['company']=$address['company'];
			$insertArray['name']=$address['first_name'].' '.$address['middle_name'].' '.$address['last_name'];
			$insertArray['name']=preg_replace('/\s+/', ' ', $insertArray['name']);
			$insertArray['first_name']=$address['first_name'];
			$insertArray['last_name']=$address['last_name'];
			$insertArray['username']=$address['email'];
			$insertArray['email']=$address['email'];
			$insertArray['street_name']=$address['street_name'];
			if (!$insertArray['street_name']) {
				$insertArray['street_name']=$address['address'];
			}
			$insertArray['address_number']=$address['address_number'];
			$insertArray['address_ext']=$address['address_ext'];
			if ($address['address']) {
				$insertArray['address']=$address['address'];
			} else {
				$insertArray['address']=$insertArray['street_name'].' '.$insertArray['address_number'].$insertArray['address_ext'];
				$insertArray['address']=preg_replace('/\s+/', ' ', $insertArray['address']);
			}
			$insertArray['zip']=$address['zip'];
			$insertArray['telephone']=$address['telephone'];
			$insertArray['city']=$address['city'];
			$insertArray['country']=$address['country'];
			$insertArray['usergroup']=$this->conf['fe_customer_usergroup'];
			$insertArray['pid']=$this->conf['fe_customer_pid'];
			$insertArray['tstamp']=time();
			$insertArray['tx_multishop_newsletter']=$address['tx_multishop_newsletter'];
			$insertArray['password']=mslib_befe::getHashedPassword(mslib_befe::generateRandomPassword(10, $insertArray['username']));
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($res) {
				$customer_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
			}
		}
		if ($customer_id) {
			if ($this->ms['MODULES']['DISABLE_VAT_FOR_FOREIGN_CUSTOMERS_WITH_COMPANY_VAT_ID']) {
				// if store country is different than customer country change VAT rate to zero
				if ($address['country'] && $address['tx_multishop_vat_id']) {
					$iso_customer=mslib_fe::getCountryByName($address['country']);
					if ($iso_customer['cn_iso_nr']!=$this->ms['MODULES']['COUNTRY_ISO_NR']) {
						$this->ms['MODULES']['DISABLE_VAT_RATE']=1;
					}
				}
				// if store country is different than customer country change VAT rate to zero eof
			}
			// now add the order
			$insertArray=array();
			$insertArray['customer_id']=$customer_id;
			$insertArray['page_uid']=$this->shop_pid;
			$insertArray['language_id']=0;
			if (is_numeric($address['language_id'])) {
				$insertArray['language_id']=$address['language_id'];
			}
			$insertArray['status']=1;
			$insertArray['customer_comments']=$this->post['customer_comments'];
			$insertArray['billing_company']=$address['company'];
			$insertArray['billing_first_name']=$address['first_name'];
			$insertArray['billing_middle_name']=$address['middle_name'];
			$insertArray['billing_last_name']=$address['last_name'];
			$insertArray['billing_name']=preg_replace('/ +/', ' ', $address['first_name'].' '.$address['middle_name'].' '.$address['last_name']);
			$insertArray['billing_email']=$address['email'];
			$insertArray['billing_gender']=$address['gender'];
			$insertArray['billing_birthday']=$address['birthday'];
			if (!$address['street_name']) {
				$address['street_name']=$address['address'];
			}
			$insertArray['billing_street_name']=$address['street_name'];
			$insertArray['billing_address_number']=$address['address_number'];
			$insertArray['billing_address_ext']=$address['address_ext'];
			$insertArray['billing_address']=$address['street_name'].' '.$address['address_number'].$address['address_ext'];
			$insertArray['billing_address']=preg_replace('/\s+/', ' ', $insertArray['billing_address']);
			$insertArray['billing_room']='';
			$insertArray['billing_city']=$address['city'];
			$insertArray['billing_zip']=$address['zip'];
			$insertArray['billing_region']=$address['state'];
			$insertArray['billing_country']=$address['country'];
			$insertArray['billing_telephone']=$address['telephone'];
			$insertArray['billing_mobile']=$address['mobile'];
			$insertArray['billing_fax']='';
			$insertArray['billing_vat_id']=$address['tx_multishop_vat_id'];
			if (!$address['different_delivery_address']) {
				$insertArray['delivery_email']=$insertArray['billing_email'];
				$insertArray['delivery_company']=$insertArray['billing_company'];
				$insertArray['delivery_first_name']=$insertArray['billing_first_name'];
				$insertArray['delivery_middle_name']=$insertArray['billing_middle_name'];
				$insertArray['delivery_last_name']=$insertArray['billing_last_name'];
				$insertArray['delivery_telephone']=$insertArray['billing_telephone'];
				$insertArray['delivery_mobile']=$insertArray['billing_mobile'];
				$insertArray['delivery_gender']=$insertArray['billing_gender'];
				$insertArray['delivery_street_name']=$insertArray['billing_street_name'];
				$insertArray['delivery_address']=$insertArray['billing_address'];
				$insertArray['delivery_address_number']=$insertArray['billing_address_number'];
				$insertArray['delivery_address_ext']=$insertArray['billing_address_ext'];
				$insertArray['delivery_zip']=$insertArray['billing_zip'];
				$insertArray['delivery_city']=$insertArray['billing_city'];
				$insertArray['delivery_country']=$insertArray['billing_country'];
				$insertArray['delivery_telephone']=$insertArray['billing_telephone'];
				$insertArray['delivery_region']=$insertArray['billing_region'];
				$insertArray['delivery_name']=$insertArray['billing_name'];
				$insertArray['delivery_vat_id']=$insertArray['billing_vat_id'];
			} else {
				$insertArray['delivery_company']=$address['delivery_company'];
				$insertArray['delivery_first_name']=$address['delivery_first_name'];
				$insertArray['delivery_middle_name']=$address['delivery_middle_name'];
				$insertArray['delivery_last_name']=$address['delivery_last_name'];
				$insertArray['delivery_name']=preg_replace('/ +/', ' ', $address['delivery_first_name'].' '.$address['delivery_middle_name'].' '.$address['delivery_last_name']);
				$insertArray['delivery_email']=$address['delivery_email'];
				$insertArray['delivery_gender']=$address['delivery_gender'];
				if (!$address['delivery_street_name']) {
					$address['delivery_street_name']=$address['delivery_address'];
				}
				$insertArray['delivery_street_name']=$address['delivery_street_name'];
				$insertArray['delivery_address_number']=$address['delivery_address_number'];
				$insertArray['delivery_address_ext']=$address['delivery_address_ext'];
				$insertArray['delivery_address']=$address['delivery_street_name'].' '.$address['delivery_address_number'].$address['delivery_address_ext'];
				$insertArray['delivery_address']=preg_replace('/\s+/', ' ', $insertArray['delivery_address']);
				$insertArray['delivery_city']=$address['delivery_city'];
				$insertArray['delivery_zip']=$address['delivery_zip'];
				$insertArray['delivery_room']='';
				$insertArray['delivery_region']=$address['delivery_state'];
				$insertArray['delivery_country']=$address['delivery_country'];
				$insertArray['delivery_telephone']=$address['delivery_telephone'];
				$insertArray['delivery_mobile']=$address['delivery_mobile'];
				$insertArray['delivery_fax']='';
				$insertArray['delivery_vat_id']=$address['delivery_vat_id'];
			}
			$insertArray['bill']=1;
			if ($address['forceCustomCrdate']) {
				$insertArray['crdate']=$address['forceCustomCrdate'];
			} else {
				$insertArray['crdate']=time();
			}
			$insertArray['shipping_method']=$address['shipping_method'];
			$insertArray['shipping_method_label']=$address['shipping_method_label'];
			$insertArray['payment_method']=$address['payment_method'];
			$insertArray['payment_method_label']=$address['payment_method_label'];
			$insertArray['shipping_method_costs']=$address['shipping_method_costs'];
			$insertArray['payment_method_costs']=$address['payment_method_costs'];
			$insertArray['payment_condition']=$address['payment_condition'];
			$insertArray['debit_order']=0;
			if (isset($address['debit_order'])) {
				$insertArray['debit_order']=$address['debit_order'];
			}
			$insertArray['store_currency']=$this->ms['MODULES']['CURRENCY_ARRAY']['cu_iso_3'];
			$insertArray['customer_currency']=$this->ms['MODULES']['CURRENCY_ARRAY']['cu_iso_3'];
			if (isset($address['customer_currency']) && $address['customer_currency'] !='') {
				$insertArray['customer_currency']=$address['customer_currency'];
			}
			$insertArray['currency_rate']=1;
			if (isset($address['currency_rate']) && $address['currency_rate'] !='') {
				$insertArray['currency_rate']=$address['currency_rate'];
			}
			// TYPO3 6.2 NULL VALUE BUGFIX
			if (!$insertArray['customer_comments']) {
				$insertArray['customer_comments']='';
			}
			$insertArray['hash']=md5(uniqid('', true));
			$types=array();
			$types[]='billing';
			$types[]='delivery';
			foreach ($types as $type) {
				$str2='select st.* from static_countries sc, static_territories st where sc.cn_short_en=\''.addslashes($insertArray[$type.'_country']).'\' and st.tr_iso_nr=sc.cn_parent_tr_iso_nr';
				$query2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
				$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($query2);
				if ($rows2) {
					$row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query2);
					$insertArray[$type.'_tr_iso_nr']=$row2['tr_iso_nr'];
					$insertArray[$type.'_tr_name_en']=$row2['tr_name_en'];

					$str2='select * from static_territories where tr_iso_nr='.$row2['tr_parent_iso_nr'];
					$query2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
					$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($query2);
					if ($rows2) {
						$row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query2);

						$insertArray[$type.'_tr_parent_iso_nr']=$row2['tr_iso_nr'];
						$insertArray[$type.'_tr_parent_name_en']=$row2['tr_name_en'];
					}
				}
			}
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrderPreProc'])) {
				$params=array(
					'insertArray'=>&$insertArray
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrderPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			foreach ($insertArray as $key => $val) {
				if (is_null($insertArray[$key])) {
					$insertArray[$key]='';
				}
			}
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// now add the order eof
			$orders_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrderPostProc'])) {
				$params=array(
					'insertArray'=>&$insertArray,
					'orders_id'=>&$orders_id
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrderPostProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			if ($orders_id) {
				return $orders_id;
			}
		}
	}
	function createOrdersProduct($orders_id, $insertArray=array()) {
		if ($orders_id) {
			$insertArray['orders_id']=$orders_id;
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductPreProc'])) {
				$params=array(
					'insertArray'=>&$insertArray
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			foreach ($insertArray as $key => $val) {
				if (is_null($insertArray[$key])) {
					$insertArray[$key]='';
				}
			}
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_products', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$orders_products_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductPostProc'])) {
				$params=array(
					'insertArray'=>&$insertArray,
					'orders_products_id'=>&$orders_products_id
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductPostProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			if ($orders_products_id) {
				return $orders_products_id;
			}
		}
	}
	function createOrdersProductAttribute($orders_id, $orders_products_id, $attributes) {
		if ($orders_products_id and is_array($attributes)) {
			foreach ($attributes as $attribute_key=>$attribute_values) {
				$str="SELECT products_options_name,listtype from tx_multishop_products_options o where o.products_options_id='".$attribute_key."' ";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
				switch ($row['listtype']) {
					case 'checkbox':
						$items=$attribute_values;
						break;
					default:
						$items=array($attribute_values);
						break;
				}
				foreach ($items as $item) {
					$attributes_tax['country_tax']=(string)$item['country_tax'];
					$attributes_tax['region_tax']=(string)$item['region_tax'];
					$attributes_tax['tax']=(string)$item['tax'];
					if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
						$attributes_tax['country_tax']=0;
						$attributes_tax['region_tax']=0;
						$attributes_tax['tax']=0;
					}
					$insertArray=array();
					$insertArray['orders_id']=$orders_id;
					$insertArray['orders_products_id']=$orders_products_id;
					$insertArray['products_options']=$item['products_options_name'];
					$insertArray['products_options_values']=$item['products_options_values_name'];
					$insertArray['options_values_price']=$item['options_values_price'];
					$insertArray['price_prefix']=$item['price_prefix'];
					$insertArray['products_options_id']=$item['options_id'];
					$insertArray['products_options_values_id']=$item['options_values_id'];
					$insertArray['attributes_tax_data']=serialize($attributes_tax);
					//hook to let other plugins further manipulate the replacers
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductAttributePreProc'])) {
						$params=array(
							'insertArray'=>&$insertArray
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductAttributePreProc'] as $funcRef) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
						}
					}
					foreach ($insertArray as $key => $val) {
						if (is_null($insertArray[$key])) {
							$insertArray[$key]='';
						}
					}
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_products_attributes', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					$orders_product_attributes_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
					//hook to let other plugins further manipulate the replacers
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductAttributePostProc'])) {
						$params=array(
							'insertArray'=>&$insertArray,
							'orders_product_attributes_id'=>&$orders_product_attributes_id
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_order.php']['createOrdersProductAttributePostProc'] as $funcRef) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
						}
					}
				}
			}
		}
	}
	function getOrderWeight($orders_id) {
		if (is_numeric($orders_id)) {
			$weight=0;
			$order=mslib_fe::getOrder($orders_id);
			foreach ($order['products'] as $product) {
				if (is_numeric($product['products_id'])) {
					$product_db=mslib_fe::getProduct($product['products_id']);
					$weight=($weight+($product['qty']*$product_db['products_weight']));
				}
			}
			return $weight;
		}
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_order.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_order.php"]);
}
?>