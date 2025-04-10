<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersMainPreProc'])) {
    // hook
    $params = array();
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersMainPreProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
    // hook oef
}
// define the tax rate
$str = $GLOBALS['TYPO3_DB']->SELECTquery('t.tax_id, t.rate, t.name', // SELECT ...
        'tx_multishop_taxes t, tx_multishop_tax_rules tr, tx_multishop_tax_rule_groups trg', // FROM ...
        't.tax_id=tr.tax_id and tr.rules_group_id=trg.rules_group_id and trg.status=1', // WHERE...
        't.tax_id', // GROUP BY...
        '', // ORDER BY...
        '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$tax_list_data = array();
while (($tax_group = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $tax_list_data[] = 'product_tax_rate_list_js["' . $tax_group['tax_id'] . '"]="' . round(number_format($tax_group['rate'], 2), 2) . '";';
}
// js definition for tax
$product_tax_rate_js = array();
if (count($tax_list_data)) {
    $product_tax_rate_js = $tax_list_data;
}
$GLOBALS['TSFE']->additionalHeaderData[] = '
<script type="text/javascript" data-ignore="1">
   var product_tax_rate_list_js=[]
   ' . implode("\n", $product_tax_rate_js) . '
</script>
';
$subpartArray = array();
$subpartArray['###VALUE_REFERRER###'] = '';
if ($this->post['tx_multishop_pi1']['referrer']) {
    $subpartArray['###VALUE_REFERRER###'] = $this->post['tx_multishop_pi1']['referrer'];
} else {
    $subpartArray['###VALUE_REFERRER###'] = $_SERVER['HTTP_REFERER'];
}
if (is_numeric($this->get['orders_id'])) {
    $order = mslib_fe::getOrder($this->get['orders_id']);
    if (!$order['orders_id']) {
        die('Unknown or deleted order');
    }
    if (count($order)) {
        // process create new order
        if (isset($this->get['tx_multishop_pi1']['new_order']) && $this->get['tx_multishop_pi1']['new_order'] == 'true') {
            $address = array();
            $address['uid'] = $order['customer_id'];
            $address['company'] = $order['billing_company'];
            $address['first_name'] = $order['billing_first_name'];
            $address['middle_name'] = $order['billing_middle_name'];
            $address['last_name'] = $order['billing_last_name'];
            $address['name'] = $order['billing_name'];
            $address['email'] = $order['billing_email'];
            $address['gender'] = $order['billing_gender'];
            $address['building'] = $order['billing_building'];
            $address['street_name'] = $order['billing_street_name'];
            $address['address_number'] = $order['billing_address_number'];
            $address['address_ext'] = $order['billing_address_ext'];
            $address['address'] = $order['billing_address'];
            $address['city'] = $order['billing_city'];
            $address['zip'] = $order['billing_zip'];
            $address['region'] = $order['billing_state'];
            $address['country'] = $order['billing_country'];
            $address['telephone'] = $order['billing_telephone'];
            $address['mobile'] = $order['billing_mobile'];
            $address['vat_id'] = $order['billing_vat_id'];
            // delivery address
            $address['different_delivery_address'] = 1;
            $address['delivery_company'] = $order['delivery_company'];
            $address['delivery_first_name'] = $order['delivery_first_name'];
            $address['delivery_middle_name'] = $order['delivery_middle_name'];
            $address['delivery_last_name'] = $order['delivery_last_name'];
            $address['delivery_name'] = $order['delivery_name'];
            $address['delivery_email'] = $order['delivery_email'];
            $address['delivery_gender'] = $order['delivery_gender'];
            $address['delivery_building'] = $order['delivery_building'];
            $address['delivery_street_name'] = $order['delivery_street_name'];
            $address['delivery_address_number'] = $order['delivery_address_number'];
            $address['delivery_address_ext'] = $order['delivery_address_ext'];
            $address['delivery_address'] = $order['delivery_address'];
            $address['delivery_city'] = $order['delivery_city'];
            $address['delivery_zip'] = $order['delivery_zip'];
            $address['delivery_region'] = $order['delivery_state'];
            $address['delivery_country'] = $order['delivery_country'];
            $address['delivery_telephone'] = $order['delivery_telephone'];
            $address['delivery_mobile'] = $order['delivery_mobile'];
            $address['delivery_vat_id'] = $order['delivery_vat_id'];
            $address['by_phone'] = 1;
            $address['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
            $address['shop_pid'] = $order['page_uid'];
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersCreateNewOrderPostProc'])) {
                // hook
                $params = array(
                        'address' => &$address
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersCreateNewOrderPostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
                // hook oef
            }
            $new_order_id = mslib_fe::createOrder($address);
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersCreateNewOrderPreRedirect'])) {
                // hook
                $params = array(
                        'new_order_id' => &$new_order_id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersCreateNewOrderPreRedirect'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
                // hook oef
            }
            if (is_numeric($new_order_id) && $new_order_id > 0) {
                header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $new_order_id . '&action=edit_order'));
                exit();
            }
        }
        if ($order['customer_id']) {
            if (!$customer_address = mslib_fe::getAddressInfo('customer', $order['customer_id'])) {
                $customer_address['country'] = $order['billing_country'];
                $customer_address['region'] = $order['billing_region'];
            }
            if (!isset($customer_address['country']) && isset($customer_address['default']['country'])) {
                $customer_address = $customer_address['default'];
            }
            $country = mslib_fe::getCountryByName($customer_address['country']);
            if (!empty($customer_address['region'])) {
                $zone = mslib_fe::getRegionByName($customer_address['region']);
            } else {
                $zone['zn_country_iso_nr'] = 0;
            }
        }
        $redirect_after_delete = false;
        $close_window = 0;
        if ($this->ms['MODULES']['ORDER_EDIT']) {
            if (!$order['is_locked']) {
                // delete single item in order
                if (isset($this->get['delete_product']) && $this->get['delete_product'] == 1) {
                    if (isset($this->get['order_pid']) && $this->get['order_pid'] > 0) {
                        $sql = "delete from tx_multishop_orders_products where orders_products_id = " . addslashes($this->get['order_pid']) . " limit 1";
                        $GLOBALS['TYPO3_DB']->sql_query($sql);
                        $sql = "delete from tx_multishop_orders_products_attributes where orders_products_id = " . addslashes($this->get['order_pid']);
                        $GLOBALS['TYPO3_DB']->sql_query($sql);
                        // repair tax stuff
                        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                        $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                        $mslib_order->init($this);
                        $mslib_order->repairOrder($this->get['orders_id']);
                        // update order discount if any
                        $order_tax_data_rec = mslib_befe::getRecord($this->get['orders_id'], 'tx_multishop_orders', 'orders_id', array(), 'orders_tax_data, discount_percentage, discount');
                        if (isset($order_tax_data_rec) && !empty($order_tax_data_rec['orders_tax_data'])) {
                            $order_tax_data = unserialize($order_tax_data_rec['orders_tax_data']);
                            $hidden_subtotal = $order_tax_data['sub_total_excluding_vat'];
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                $hidden_subtotal = $order_tax_data['sub_total'];
                            }
                            $discount_value = $order_tax_data_rec['discount'];
                        }
                        $updateArray = array();
                        $updateArray['discount_percentage'] = 0;
                        if (isset($order_tax_data_rec['discount_percentage']) && $order_tax_data_rec['discount_percentage'] > 0) {
                            $updateArray['discount_percentage'] = $order_tax_data_rec['discount_percentage'];
                            $discount_amount = number_format(($hidden_subtotal / 100) * $order_tax_data_rec['discount_percentage'], 2, ',', '');
                            if ($discount_amount != $discount_value) {
                                $discount_value = $discount_amount;
                            }
                        }
                        if (isset($discount_value)) {
                            //$discount_value = mslib_befe::formatNumbersToMysql($discount_value);
                            $updateArray['discount'] = $discount_value;
                        }
                        $updateArray['orders_last_modified'] = time();
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $this->get['orders_id'] . '\'', $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        // repair tax stuff
                        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                        $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                        $mslib_order->init($this);
                        $mslib_order->repairOrder($this->get['orders_id']);
                        // redirect
                        $redirect_after_delete = true;
                    }
                }
                if ($this->post) {
                    if (!$this->ms['MODULES']['DISABLE_VAT_RATE'] && $this->ms['MODULES']['DISABLE_VAT_FOR_FOREIGN_CUSTOMERS_WITH_COMPANY_VAT_ID'] && $this->post['tx_multishop_pi1']['billing_vat_id']) {
                        if (strtolower($this->post['tx_multishop_pi1']['billing_country']) != strtolower($this->tta_shop_info['country'])) {
                            $this->ms['MODULES']['DISABLE_VAT_RATE'] = 1;
                        }
                    }
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersChangesPreProc'])) {
                        // hook
                        $params = array();
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersChangesPreProc'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                        // hook oef
                    }
                    if (!empty($this->post['product_tax']) && $this->post['product_tax'] > 0) {
                        $tr = mslib_fe::getTaxes($this->post['product_tax']);
                        if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
                            $tr['rate'] = 0;
                        }
                        $this->post['product_tax'] = $tr['rate'];
                    }
                    if (!empty($this->post['manual_product_tax']) && $this->post['manual_product_tax'] > 0) {
                        $tr = mslib_fe::getTaxes($this->post['manual_product_tax']);
                        if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
                            $tr['rate'] = 0;
                        }
                        $this->post['manual_product_tax'] = $tr['rate'];
                    }
                    if (!empty($this->post['product_name']) || !empty($this->post['manual_product_name'])) {
                        $updateArray = array();
                        $keys = array();
                        $keys[] = 'company';
                        $keys[] = 'name';
                        $keys[] = 'street_name';
                        $keys[] = 'address_number';
                        $keys[] = 'address_ext';
                        $keys[] = 'building';
                        $keys[] = 'zip';
                        $keys[] = 'city';
                        $keys[] = 'country';
                        $keys[] = 'email';
                        $keys[] = 'telephone';
                        $keys[] = 'mobile';
                        $keys[] = 'fax';
                        foreach ($keys as $key) {
                            $string = 'billing_' . $key;
                            $updateArray[$string] = trim($this->post['tx_multishop_pi1'][$string]);
                            $string = 'delivery_' . $key;
                            $updateArray[$string] = trim($this->post['tx_multishop_pi1'][$string]);
                        }
                        $updateArray['billing_address'] = preg_replace('/\s+/', ' ', $updateArray['billing_street_name'] . ' ' . $updateArray['billing_address_number'] . ' ' . $updateArray['billing_address_ext']);
                        $updateArray['delivery_address'] = preg_replace('/\s+/', ' ', $updateArray['delivery_street_name'] . ' ' . $updateArray['delivery_address_number'] . ' ' . $updateArray['delivery_address_ext']);
                        if (count($updateArray)) {
                            $updateArray['orders_last_modified'] = time();
                            // hook to let other plugins further manipulate
                            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPreProc'])) {
                                $params = array(
                                        'updateArray' => &$updateArray,
                                        'orders_id' => &$this->get['orders_id'],
                                );
                                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPreProc'] as $funcRef) {
                                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                }
                            }
                            // hook eol
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $this->get['orders_id'] . '\'', $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                        if (is_numeric($this->post['orders_products_id']) > 0) {
                            if ($this->post['product_name']) {
                                $this->post['product_qty'] = str_replace(',', '.', $this->post['product_qty']);
                                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                                    $current_qty_delivered = 0;
                                    $updated_qty_delivered = 0;
                                    $this->post['product_qty_delivered'] = str_replace(',', '.', $this->post['product_qty_delivered']);
                                    if ($this->post['product_qty_delivered'] > 0) {
                                        if ($this->post['product_qty_delivered'] > $this->post['product_qty']) {
                                            $this->post['product_qty_delivered'] = $this->post['product_qty'];
                                        }
                                        $filterOProduct = array();
                                        $filterOProduct[] = 'orders_id=' . $this->get['orders_id'];
                                        $filterOProduct[] = 'products_id=' . $this->post['products_id'];
                                        $current_order_product_rec = mslib_befe::getRecord($this->post['orders_products_id'], 'tx_multishop_orders_products', 'orders_products_id', $filterOProduct, 'qty_delivered');
                                        if (is_array($current_order_product_rec)) {
                                            $current_qty_delivered = $current_order_product_rec['qty_delivered'];
                                            if ($this->post['product_qty_delivered'] > $current_qty_delivered) {
                                                $updated_qty_delivered = $this->post['product_qty_delivered'] - $current_qty_delivered;
                                            } else {
                                                $updated_qty_delivered = $current_qty_delivered;
                                            }
                                        }
                                    } else {
                                        $this->post['product_qty_delivered'] = 0;
                                    }
                                }
                                if (empty($this->post['product_price'])) {
                                    $this->post['product_price'] = '0';
                                }
                                $updateArray = array();
                                $updateArray['products_id'] = $this->post['products_id'];
                                // build the categories record
                                $product_data = mslib_fe::getProduct($this->post['products_id'], '', '', 1);
                                $updateArray['categories_id'] = $product_data['categories_id'];
                                $updateArray['categories_name'] = '';
                                $category_name = mslib_fe::getCategoryName($product_data['categories_id']);
                                if ($category_name) {
                                    $updateArray['categories_name'] = $category_name;
                                }
                                $updateArray['sku_code'] = $product_data['sku_code'];
                                $updateArray['ean_code'] = $product_data['ean_code'];
                                $updateArray['vendor_code'] = $product_data['vendor_code'];
                                // get all cats
                                $cats = mslib_fe::Crumbar($product_data['categories_id']);
                                $cats = array_reverse($cats);
                                if (count($cats) > 0) {
                                    $i = 0;
                                    foreach ($cats as $cat) {
                                        $updateArray['categories_id_' . $i] = $cat['id'];
                                        $updateArray['categories_name_' . $i] = $cat['name'];
                                        $i++;
                                    }
                                }
                                // get all cats eof
                                $updateArray['qty'] = $this->post['product_qty'];
                                $updateArray['qty_delivered'] = 0;
                                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0 && $this->post['product_qty_delivered']) {
                                    $updateArray['qty_delivered'] = $this->post['product_qty_delivered'];
                                }
                                if (isset($this->post['custom_manual_product_name']) && !empty($this->post['custom_manual_product_name'])) {
                                    $updateArray['products_name'] = $this->post['custom_manual_product_name'];
                                } else {
                                    $updateArray['products_name'] = $this->post['product_name'];
                                }
                                if ($this->ms['MODULES']['ENABLE_EDIT_ORDER_PRODUCTS_DESCRIPTION_FIELD']) {
                                    $updateArray['products_description'] = $this->post['order_products_description'];
                                }
                                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                                    $updateArray['product_capital_price'] = $this->post['product_capital_price'];
                                }
                                $updateArray['products_price'] = $this->post['product_price'];
                                $updateArray['final_price'] = $this->post['product_price'];
                                // disocunt update
                                if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                                    $updateArray['discount_amount'] = 0;
                                    $updateArray['discount_percentage'] = 0;
                                    if (isset($this->post['product_discount_percentage']) && is_numeric($this->post['product_discount_percentage']) && $this->post['product_discount_percentage'] > 0) {
                                        $updateArray['discount_percentage'] = $this->post['product_discount_percentage'];
                                        $discount_amount = ($this->post['product_price'] * $this->post['product_discount_percentage']) / 100;
                                        $updateArray['discount_amount'] = mslib_fe::taxDecimalCrop($discount_amount, 2, true);
                                    } else if (isset($this->post['product_discount_amount']) && !empty($this->post['product_discount_amount'])) {
                                        $updateArray['discount_amount'] = $this->post['product_discount_amount'];
                                        $discount_percentage = ($this->post['product_discount_amount'] / $this->post['product_price']) * 100;
                                        $updateArray['discount_percentage'] = $discount_percentage;
                                    }
                                }
                                if (!empty($updateArray['discount_amount'])) {
                                    $final_price_after_discount = ($this->post['product_price'] - $updateArray['discount_amount']);
                                    $updateArray['final_price'] = number_format($final_price_after_discount, 12, '.', '');
                                }
                                $updateArray['products_tax'] = $this->post['product_tax'];
                                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS']) {
                                    $updateArray['customer_comments'] = $this->post['product_customer_comments'];
                                }
                                //$product_data=mslib_befe::getRecord($this->post['products_id'], 'tx_multishop_products', 'products_id');
                                $updateArray['products_model'] = $product_data['products_model'];
                                $updateArray['products_tax_id'] = 0;
                                if ($product_data['tax_id']) {
                                    $updateArray['products_tax_id'] = $product_data['tax_id'];
                                }
                                // hook for adding new items to details fieldset
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPreUpdateOrderProducts'])) {
                                    // hook
                                    $params = array(
                                            'updateArray' => &$updateArray
                                    );
                                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPreUpdateOrderProducts'] as $funcRef) {
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                    }
                                    // hook oef
                                }
                                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'orders_id = \'' . (int)$this->get['orders_id'] . '\' and orders_products_id = \'' . (int)$this->post['orders_products_id'] . '\'', $updateArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                // update the orders product qty delivered record
                                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                                    if ($updated_qty_delivered > $current_qty_delivered) {
                                        $insertArray = array();
                                        $insertArray['orders_products_id'] = $this->post['orders_products_id'];
                                        $insertArray['orders_id'] = $this->get['orders_id'];
                                        $insertArray['products_id'] = $this->post['products_id'];
                                        $insertArray['qty'] = $updated_qty_delivered;
                                        //$insertArray['status']=0;
                                        $insertArray['crdate'] = time();
                                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_products_qty_shipped', $insertArray);
                                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                    }
                                }
                                // hook for adding new items to details fieldset
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostUpdateOrderProducts'])) {
                                    // hook
                                    $params = array();
                                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostUpdateOrderProducts'] as $funcRef) {
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                    }
                                    // hook oef
                                }
                                //$sql="update tx_multishop_orders_products set products_id = '".$this->post['products_id']."', qty = '".$this->post['product_qty']."', products_name ='".addslashes($this->post['product_name'])."'".$order_products_description.", products_price = '".addslashes($this->post['product_price'])."', final_price = '".$this->post['product_price']."', products_tax = '".$this->post['product_tax']."' where orders_id = ".$this->get['orders_id']." and orders_products_id = '".$this->post['orders_products_id']."'";
                                //$GLOBALS['TYPO3_DB']->sql_query($sql);
                                // clean up the order product attributes to prepare the update
                                $sql = "delete from tx_multishop_orders_products_attributes where orders_id = " . addslashes($this->get['orders_id']) . " and orders_products_id = " . addslashes($this->post['orders_products_id']);
                                $GLOBALS['TYPO3_DB']->sql_query($sql);
                                // insert the update attributes
                                $count_manual_attributes = count($this->post['edit_manual_option']);
                                if ($count_manual_attributes > 0) {
                                    for ($x = 0; $x < $count_manual_attributes; $x++) {
                                        if (strpos($this->post['edit_manual_price'][$x], '-') !== false) {
                                            $price_prefix = '-';
                                            $this->post['edit_manual_price'][$x] = str_replace('-', '', $this->post['edit_manual_price'][$x]);
                                        } else {
                                            $price_prefix = '+';
                                            $this->post['edit_manual_price'][$x] = str_replace('+', '', $this->post['edit_manual_price'][$x]);
                                        }
                                        if (!empty($this->post['edit_manual_option'][$x]) && !empty($this->post['edit_manual_values'][$x])) {
                                            $optname = $this->post['edit_manual_option'][$x];
                                            $optid = 0;
                                            $optvalname = $this->post['edit_manual_values'][$x];
                                            $optvalid = 0;
                                            if (!$this->post['is_manual_option'][$x]) {
                                                $optid = $this->post['edit_manual_option'][$x];
                                                $optname = mslib_fe::getRealNameOptions($optid);
                                            }
                                            if (!$this->post['is_manual_value'][$x]) {
                                                $optvalid = $this->post['edit_manual_values'][$x];
                                                $optvalname = mslib_fe::getNameOptions($optvalid);
                                            }
                                            $insertArray = array();
                                            $insertArray['orders_id'] = (int)$this->get['orders_id'];
                                            $insertArray['orders_products_id'] = (int)$this->post['orders_products_id'];
                                            $insertArray['products_options'] = $optname;
                                            $insertArray['products_options_values'] = $optvalname;
                                            $insertArray['options_values_price'] = $this->post['edit_manual_price'][$x];
                                            $insertArray['price_prefix'] = $price_prefix;
                                            $insertArray['products_options_id'] = $optid;
                                            $insertArray['products_options_values_id'] = $optvalid;
                                            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_products_attributes', $insertArray);
                                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                            $orderProductAttributesId = $GLOBALS['TYPO3_DB']->sql_insert_id();
                                            // hook for adding new items to details fieldset
                                            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostSaveOrderProductsAttributes'])) {
                                                $params = array(
                                                    'orderProductAttributesId' => $orderProductAttributesId,
                                                    'insertArray' => $insertArray
                                                );
                                                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostSaveOrderProductsAttributes'] as $funcRef) {
                                                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                                }
                                            }
                                            // hook oef
                                            //$sql="insert into tx_multishop_orders_products_attributes (orders_id, orders_products_id, products_options, products_options_values, options_values_price, price_prefix, attributes_values, products_options_id, products_options_values_id) values ('".$this->get['orders_id']."', '".$this->post['orders_products_id']."', '".$optname."', '".$optvalname."', '".$this->post['edit_manual_price'][$x]."', '".$price_prefix."', NULL, '".$optid."', '".$optvalid."')";
                                            //$GLOBALS['TYPO3_DB']->sql_query($sql);
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($this->post['manual_product_name']) {
                                $this->post['manual_product_qty'] = str_replace(',', '.', $this->post['manual_product_qty']);
                                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT'] > 0) {
                                    if (empty($this->post['manual_product_capital_price'])) {
                                        $this->post['manual_product_capital_price'] = '0';
                                    }
                                }
                                if (empty($this->post['manual_product_price'])) {
                                    $this->post['manual_product_price'] = '0';
                                }
                                // determine the sort order for the new orders products
                                $sql_sort_order = "select sort_order from tx_multishop_orders_products where orders_id='" . (int)$this->get['orders_id'] . "' order by sort_order desc limit 1";
                                $qry_sort_order = $GLOBALS['TYPO3_DB']->sql_query($sql_sort_order);
                                $rs_sort_order = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_sort_order);
                                $new_sort_order = $rs_sort_order['sort_order'] + 1;
                                // insert new products
                                $insertArray = array();
                                $insertArray['orders_id'] = (int)$this->get['orders_id'];
                                $insertArray['products_id'] = (int)$this->post['manual_products_id'];
                                //
                                if (is_numeric($this->post['manual_products_id']) && $this->post['manual_products_id'] > 0) {
                                    $product_data = mslib_fe::getProduct($this->post['manual_products_id'], '', '', 1);
                                    $insertArray['categories_id'] = $product_data['categories_id'];
                                    $insertArray['categories_name'] = '';
                                    $category_name = mslib_fe::getCategoryName($product_data['categories_id']);
                                    if ($category_name) {
                                        $insertArray['categories_name'] = $category_name;
                                    }
                                    $insertArray['sku_code'] = $product_data['sku_code'];
                                    $insertArray['ean_code'] = $product_data['ean_code'];
                                    $insertArray['vendor_code'] = $product_data['vendor_code'];
                                    // get all cats
                                    $cats = mslib_fe::Crumbar($product_data['categories_id']);
                                    $cats = array_reverse($cats);
                                    if (count($cats) > 0) {
                                        $i = 0;
                                        foreach ($cats as $cat) {
                                            $insertArray['categories_id_' . $i] = $cat['id'];
                                            $insertArray['categories_name_' . $i] = $cat['name'];
                                            $i++;
                                        }
                                    }
                                    // get all cats eof
                                    if (isset($product_data['manufacturers_id']) && !empty($product_data['manufacturers_id'])) {
                                        $insertArray['manufacturers_id'] = $product_data['manufacturers_id'];
                                    } else {
                                        $insertArray['manufacturers_id'] = '';
                                    }
                                    if (isset($product_data['order_unit_id']) && !empty($product_data['order_unit_id'])) {
                                        $insertArray['order_unit_id'] = $product_data['order_unit_id'];
                                    } else {
                                        $insertArray['order_unit_id'] = '';
                                    }
                                    if (isset($product_data['order_unit_name']) && !empty($product_data['order_unit_name'])) {
                                        $insertArray['order_unit_name'] = $product_data['order_unit_name'];
                                    } else {
                                        $insertArray['order_unit_name'] = '';
                                    }
                                    if (isset($product_data['order_unit_code']) && !empty($product_data['order_unit_code'])) {
                                        $insertArray['order_unit_code'] = $product_data['order_unit_code'];
                                    } else {
                                        $insertArray['order_unit_code'] = '';
                                    }
                                    $insertArray['products_model'] = $product_data['products_model'];
                                    $insertArray['products_tax_id'] = 0;
                                    if ($product_data['tax_id']) {
                                        $insertArray['products_tax_id'] = $product_data['tax_id'];
                                    }
                                }
                                //
                                $insertArray['qty'] = $this->post['manual_product_qty'];
                                if (isset($this->post['custom_manual_product_name']) && !empty($this->post['custom_manual_product_name'])) {
                                    $insertArray['products_name'] = $this->post['custom_manual_product_name'];
                                } else {
                                    $insertArray['products_name'] = $this->post['manual_product_name'];
                                }
                                if ($this->ms['MODULES']['ENABLE_EDIT_ORDER_PRODUCTS_DESCRIPTION_FIELD']) {
                                    $insertArray['products_description'] = $this->post['manual_order_products_description'];
                                }
                                // discount update
                                if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                                    $insertArray['discount_amount'] = 0;
                                    $insertArray['discount_percentage'] = 0;
                                    if (isset($this->post['manual_product_discount_percentage']) && is_numeric($this->post['manual_product_discount_percentage']) && $this->post['manual_product_discount_percentage'] > 0) {
                                        $insertArray['discount_percentage'] = $this->post['manual_product_discount_percentage'];
                                        $discount_amount = ($this->post['manual_product_price'] * $this->post['manual_product_discount_percentage']) / 100;
                                        $insertArray['discount_amount'] = mslib_fe::taxDecimalCrop($discount_amount, 2);
                                    } else if (isset($this->post['manual_product_discount_amount']) && !empty($this->post['manual_product_discount_amount'])) {
                                        $insertArray['discount_amount'] = $this->post['manual_product_discount_amount'];
                                        $discount_percentage = ($this->post['manual_product_discount_amount'] / $this->post['manual_product_price']) * 100;
                                        $insertArray['discount_percentage'] = $discount_percentage;
                                    }
                                }
                                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                                    $insertArray['product_capital_price'] = $this->post['manual_product_capital_price'];
                                }
                                $insertArray['products_price'] = $this->post['manual_product_price'];
                                if (!empty($insertArray['discount_amount'])) {
                                    $insertArray['final_price'] = ($this->post['manual_product_price'] - $insertArray['discount_amount']);
                                } else {
                                    $insertArray['final_price'] = $this->post['manual_product_price'];
                                }
                                $insertArray['products_tax'] = $this->post['manual_product_tax'];
                                $insertArray['sort_order'] = $new_sort_order;
                                $insertArray['crdate'] = time();
                                $insertArray['manufacturers_name'] = $product_data['manufacturers_name'];
                                // hook for adding new items to details fieldset
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPreSaveOrderProducts'])) {
                                    // hook
                                    $params = array(
                                            'insertArray' => &$insertArray
                                    );
                                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPreSaveOrderProducts'] as $funcRef) {
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                    }
                                    // hook oef
                                }
                                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_products', $insertArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                //$sql="insert into tx_multishop_orders_products (orders_id, products_id, qty, products_name".$manual_order_products_description_field.", products_price, final_price, products_tax, sort_order) values ('".$this->get['orders_id']."', '".$this->post['manual_products_id']."', '".$this->post['manual_product_qty']."', '".addslashes($this->post['manual_product_name'])."'".$manual_order_products_description_value.", '".$this->post['manual_product_price']."', '".$this->post['manual_product_price']."', '".$this->post['manual_product_tax']."', '".$new_sort_order."')";
                                //$GLOBALS['TYPO3_DB']->sql_query($sql);
                                $orders_products_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
                                // hook for adding new items to details fieldset
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostSaveOrderProducts'])) {
                                    // hook
                                    $params = array(
                                            'orders_products_id' => $orders_products_id
                                    );
                                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostSaveOrderProducts'] as $funcRef) {
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                    }
                                    // hook oef
                                }
                                // insert the update attributes
                                $count_manual_attributes = count($this->post['edit_manual_option']);
                                if ($count_manual_attributes > 0) {
                                    for ($x = 0; $x < $count_manual_attributes; $x++) {
                                        if (strpos($this->post['edit_manual_price'][$x], '-') !== false) {
                                            $price_prefix = '-';
                                            $this->post['edit_manual_price'][$x] = str_replace('-', '', $this->post['edit_manual_price'][$x]);
                                        } else {
                                            $price_prefix = '+';
                                            $this->post['edit_manual_price'][$x] = str_replace('+', '', $this->post['edit_manual_price'][$x]);
                                        }
                                        if (!empty($this->post['edit_manual_option'][$x]) && !empty($this->post['edit_manual_values'][$x])) {
                                            $optname = $this->post['edit_manual_option'][$x];
                                            $optid = 0;
                                            $optvalname = $this->post['edit_manual_values'][$x];
                                            $optvalid = 0;
                                            if (!$this->post['is_manual_option'][$x]) {
                                                $optid = $this->post['edit_manual_option'][$x];
                                                $optname = mslib_fe::getRealNameOptions($optid);
                                            }
                                            if (!$this->post['is_manual_value'][$x]) {
                                                $optvalid = $this->post['edit_manual_values'][$x];
                                                $optvalname = mslib_fe::getNameOptions($optvalid);
                                            }
                                            $insertArray = array();
                                            $insertArray['orders_id'] = (int)$this->get['orders_id'];
                                            $insertArray['orders_products_id'] = $orders_products_id;
                                            $insertArray['products_options'] = $optname;
                                            $insertArray['products_options_values'] = $optvalname;
                                            $insertArray['options_values_price'] = $this->post['edit_manual_price'][$x];
                                            $insertArray['price_prefix'] = $price_prefix;
                                            $insertArray['products_options_id'] = $optid;
                                            $insertArray['products_options_values_id'] = $optvalid;
                                            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_products_attributes', $insertArray);
                                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                            $orderProductAttributesId = $GLOBALS['TYPO3_DB']->sql_insert_id();
                                            // hook for adding new items to details fieldset
                                            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostSaveOrderProductsAttributes'])) {
                                                $params = array(
                                                    'orderProductAttributesId' => $orderProductAttributesId,
                                                    'insertArray' => $insertArray
                                                );
                                                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPostSaveOrderProductsAttributes'] as $funcRef) {
                                                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                                }
                                            }
                                            // hook oef
                                        }
                                    }
                                }
                            }
                        }
                        $redirect_after_delete = true;
                    }
                    $delivery_country = mslib_fe::getCountryByName($this->post['tx_multishop_pi1']['delivery_country']);
                    $updateArray = array();
                    if ($this->post['shipping_method']) {
                        $shipping_method = mslib_fe::getShippingMethod($this->post['shipping_method']);
                        if (empty($order['orders_tax_data'])) {
                            // temporary call, replacing the inner tax_ruleset inside the getShippingMethod
                            $tax_ruleset = mslib_fe::taxRuleSet($shipping_method['tax_id'], 0, $country['cn_iso_nr'], $zone['zn_country_iso_nr']);
                            $shipping_method['tax_rate'] = ($tax_ruleset['total_tax_rate'] / 100);
                            $shipping_method['country_tax_rate'] = ($tax_ruleset['country_tax_rate'] / 100);
                            $shipping_method['region_tax_rate'] = ($tax_ruleset['state_tax_rate'] / 100);
                        }
                        if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
                            $shipping_method['tax_rate'] = 0;
                            $shipping_method['country_tax_rate'] = 0;
                            $shipping_method['region_tax_rate'] = 0;
                        }
                        $price = 0;
                        if ($this->ms['MODULES']['ENABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER'] == '1') {
                            $tmp_price = mslib_fe::getShippingCosts($delivery_country['cn_iso_nr'], $this->post['shipping_method']);
                            if (is_array($tmp_price) && isset($tmp_price['shipping_costs'])) {
                                $shipping_costs_price = $tmp_price['shipping_costs'];
                            } else {
                                $shipping_costs_price = $tmp_price;
                            }
                            // if shipping_costs_price truly integer/float then it mean the shipping costs are free based on calculation
                            if ($shipping_costs_price === 0 || $shipping_costs_price > 0) {
                                $price = $shipping_costs_price;
                            }
                        } else {
                            $price = 0;
                            $this->post['shipping_costs_manual'] = 1;
                        }
                        //$order_shipping_method=mslib_befe::getRecord($this->get['orders_id'], 'tx_multishop_orders', 'orders_id', array(), 'shipping_method');
                        //if ($this->post['tx_multishop_pi1']['shipping_method_costs']>0 && $shipping_method['code']==$order_shipping_method['shipping_method']) {
                        if (!empty($this->post['tx_multishop_pi1']['shipping_method_costs']) && $this->post['shipping_costs_manual'] == '1') {
                            $this->post['tx_multishop_pi1']['shipping_method_costs'] = mslib_befe::formatNumbersToMysql($this->post['tx_multishop_pi1']['shipping_method_costs']);
                            $price = $this->post['tx_multishop_pi1']['shipping_method_costs'];
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                $tax_rate_for_shipping = ((1 + $shipping_method['tax_rate']) * 100);
                                $price = ($price / $tax_rate_for_shipping) * 100;
                            }
                        }
                        if ($price > 0) {
                            if (strstr($price, "%")) {
                                // calculate total shipping costs based by %
                                $subtotal = 0;
                                foreach ($order['products'] as $products_id => $value) {
                                    if (is_numeric($products_id)) {
                                        $subtotal = $subtotal + ($value['qty'] * $value['final_price']);
                                    }
                                }
                                if ($subtotal) {
                                    $percentage = str_replace("%", '', $price);
                                    if ($percentage) {
                                        $price = ($subtotal / 100 * $percentage);
                                    }
                                }
                            } else {
                                if (!strstr($price, "%")) {
                                    if (strstr($price, ",")) {
                                        $steps = explode(",", $price);
                                        // calculate total costs
                                        $subtotal = mslib_fe::getOrderTotalPrice($this->get['orders_id'], 1);
                                        $count = 0;
                                        foreach ($steps as $step) {
                                            // the   value 200:15 means below 200 euro the shipping costs are 15 euro, above and equal 200 euro the shipping costs are 0 euro
                                            $split = explode(":", $step);
                                            if (is_numeric($split[0])) {
                                                if ($count == 0) {
                                                    $price = $split[1];
                                                }
                                                if ($subtotal > $split[0]) {
                                                    $price = $split[1];
                                                    continue;
                                                }
                                            }
                                            $count++;
                                        }
                                    }
                                }
                            }
                        }
                        if ($price) {
                            $updateArray['shipping_method_costs'] = $price;
                        } else {
                            $updateArray['shipping_method_costs'] = 0;
                        }
                        $updateArray['is_shipping_costs_manual'] = 0;
                        if (!$this->post['tx_multishop_pi1']['shipping_method_costs'] || $this->post['tx_multishop_pi1']['shipping_method_costs'] == '0.00') {
                            if ($this->post['shipping_costs_manual'] == '1') {
                                $updateArray['is_shipping_costs_manual'] = 0;
                            }
                        } else {
                            if ($this->post['shipping_costs_manual'] == '1') {
                                $updateArray['is_shipping_costs_manual'] = 1;
                            }
                        }
                        if ($shipping_method['tax_id'] && $updateArray['shipping_method_costs']) {
                            $shipping_tax['shipping_total_tax_rate'] = $shipping_method['tax_rate'];
                            if ($shipping_method['country_tax_rate']) {
                                $shipping_tax['shipping_country_tax_rate'] = $shipping_method['country_tax_rate'];
                                $shipping_tax['shipping_country_tax'] = mslib_fe::taxDecimalCrop($updateArray['shipping_method_costs'] * ($shipping_method['country_tax_rate']));
                            } else {
                                $shipping_tax['shipping_country_tax_rate'] = 0;
                                $shipping_tax['shipping_country_tax'] = 0;
                            }
                            if ($shipping_method['region_tax_rate']) {
                                $shipping_tax['shipping_region_tax_rate'] = $shipping_method['region_tax_rate'];
                                $shipping_tax['shipping_region_tax'] = mslib_fe::taxDecimalCrop($updateArray['shipping_method_costs'] * ($shipping_method['region_tax_rate']));
                            } else {
                                $shipping_tax['shipping_region_tax_rate'] = 0;
                                $shipping_tax['shipping_region_tax'] = 0;
                            }
                            if ($shipping_tax['shipping_region_tax'] && $shipping_tax['shipping_country_tax']) {
                                $shipping_tax['shipping_tax'] = $shipping_tax['shipping_country_tax'] + $shipping_tax['shipping_region_tax'];
                            } else {
                                $shipping_tax['shipping_tax'] = mslib_fe::taxDecimalCrop($updateArray['shipping_method_costs'] * ($shipping_method['tax_rate']));
                            }
                        } else {
                            $shipping_tax['shipping_tax'] = 0;
                            $shipping_tax['shipping_country_tax'] = 0;
                            $shipping_tax['shipping_region_tax'] = 0;
                            $shipping_tax['shipping_total_tax_rate'] = 0;
                            $shipping_tax['shipping_country_tax_rate'] = 0;
                            $shipping_tax['shipping_region_tax_rate'] = 0;
                        }
                        $updateArray['shipping_method'] = $shipping_method['code'];
                        $updateArray['shipping_method_label'] = $shipping_method['name'];
                    } else {
                        $updateArray['shipping_method_costs'] = 0;
                        $updateArray['shipping_method'] = '';
                        $updateArray['shipping_method_label'] = '';
                    }
                    if ($this->post['payment_method']) {
                        $payment_method = mslib_fe::getPaymentMethod($this->post['payment_method']);
                        if (empty($order['orders_tax_data'])) {
                            // temporary call, replacing the inner tax_ruleset inside the getPaymentMethod
                            $tax_ruleset = mslib_fe::taxRuleSet($payment_method['tax_id'], 0, $country['cn_iso_nr'], $zone['zn_country_iso_nr']);
                            $payment_method['tax_rate'] = ($tax_ruleset['total_tax_rate'] / 100);
                            $payment_method['country_tax_rate'] = ($tax_ruleset['country_tax_rate'] / 100);
                            $payment_method['region_tax_rate'] = ($tax_ruleset['state_tax_rate'] / 100);
                        }
                        if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
                            $payment_method['tax_rate'] = 0;
                            $payment_method['country_tax_rate'] = 0;
                            $payment_method['region_tax_rate'] = 0;
                        }
                        //$order_payment_method=mslib_befe::getRecord($this->get['orders_id'], 'tx_multishop_orders', 'orders_id', array(), 'payment_method');
                        //if ($this->post['tx_multishop_pi1']['payment_method_costs']>0 && $payment_method['code']==$order_payment_method['payment_method']) {
                        if ($this->post['tx_multishop_pi1']['payment_method_costs'] > 0) {
                            $this->post['tx_multishop_pi1']['payment_method_costs'] = mslib_befe::formatNumbersToMysql($this->post['tx_multishop_pi1']['payment_method_costs']);
                            $price = $this->post['tx_multishop_pi1']['payment_method_costs'];
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                $tax_rate_for_payment = ((1 + $payment_method['tax_rate']) * 100);
                                $price = ($price / $tax_rate_for_payment) * 100;
                            }
                        } else {
                            $price = $payment_method['handling_costs'];
                        }
                        $updateArray['payment_method_costs'] = $price;
                        if ($payment_method['tax_id'] && $updateArray['payment_method_costs']) {
                            $payment_tax['payment_total_tax_rate'] = $payment_method['tax_rate'];
                            if ($payment_method['country_tax_rate']) {
                                $payment_tax['payment_country_tax_rate'] = $payment_method['country_tax_rate'];
                                $payment_tax['payment_country_tax'] = mslib_fe::taxDecimalCrop($updateArray['payment_method_costs'] * ($payment_method['country_tax_rate']));
                            } else {
                                $payment_tax['payment_country_tax_rate'] = 0;
                                $payment_tax['payment_country_tax'] = 0;
                            }
                            if ($payment_method['region_tax_rate']) {
                                $payment_tax['payment_region_tax_rate'] = $payment_method['region_tax_rate'];
                                $payment_tax['payment_region_tax'] = mslib_fe::taxDecimalCrop($updateArray['payment_method_costs'] * ($payment_method['region_tax_rate']));
                            } else {
                                $payment_tax['payment_region_tax_rate'] = 0;
                                $payment_tax['payment_region_tax'] = 0;
                            }
                            if ($payment_tax['payment_region_tax'] && $payment_tax['payment_country_tax']) {
                                $payment_tax['payment_tax'] = $payment_tax['payment_country_tax'] + $payment_tax['payment_region_tax'];
                            } else {
                                $payment_tax['payment_tax'] = mslib_fe::taxDecimalCrop($updateArray['payment_method_costs'] * ($payment_method['tax_rate']));
                            }
                        } else {
                            $payment_tax['payment_tax'] = 0;
                            $payment_tax['payment_country_tax'] = 0;
                            $payment_tax['payment_region_tax'] = 0;
                            $payment_tax['payment_total_tax_rate'] = 0;
                            $payment_tax['payment_country_tax_rate'] = 0;
                            $payment_tax['payment_region_tax_rate'] = 0;
                        }
                        $updateArray['payment_method'] = $payment_method['code'];
                        $updateArray['payment_method_label'] = $payment_method['name'];
                    } else {
                        $updateArray['payment_method_costs'] = '0';
                        $updateArray['payment_method'] = '';
                        $updateArray['payment_method_label'] = '';
                    }
                    if (isset($this->post['order_payment_condition'])) {
                        $updateArray['payment_condition'] = $this->post['order_payment_condition'];
                        if ($this->get['orders_id'] && $this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
                            $filter = array();
                            $filter[] = 'orders_id=' . $this->get['orders_id'];
                            $invoices = mslib_befe::getRecords('', 'tx_multishop_invoices', '', $filter, '', 'id desc');
                            if (is_array($invoices) && count($invoices)) {
                                foreach ($invoices as $invoice) {
                                    $updateInvoiceArray = array();
                                    $updateInvoiceArray['payment_condition'] = $this->post['order_payment_condition'];
                                    $queryUpdInvoice = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id=\'' . $invoice['id'] . '\'', $updateInvoiceArray);
                                    $res = $GLOBALS['TYPO3_DB']->sql_query($queryUpdInvoice);
                                }
                            }
                        }
                    }
                    //
                    $billing_name = '';
                    if (isset($this->post['tx_multishop_pi1']['billing_first_name'])) {
                        $billing_name = $this->post['tx_multishop_pi1']['billing_first_name'];
                    }
                    if (isset($this->post['tx_multishop_pi1']['billing_middle_name'])) {
                        $billing_name .= ' ' . $this->post['tx_multishop_pi1']['billing_middle_name'];
                    }
                    if (isset($this->post['tx_multishop_pi1']['billing_last_name'])) {
                        $billing_name .= ' ' . $this->post['tx_multishop_pi1']['billing_last_name'];
                    }
                    $this->post['tx_multishop_pi1']['billing_name'] = '';
                    if ($billing_name) {
                        $billing_name = preg_replace('/\s+/', ' ', $billing_name);
                        $this->post['tx_multishop_pi1']['billing_name'] = $billing_name;
                    }
                    //
                    $delivery_name = '';
                    if (isset($this->post['tx_multishop_pi1']['delivery_first_name'])) {
                        $delivery_name = $this->post['tx_multishop_pi1']['delivery_first_name'];
                    }
                    if (isset($this->post['tx_multishop_pi1']['delivery_middle_name'])) {
                        $delivery_name .= ' ' . $this->post['tx_multishop_pi1']['delivery_middle_name'];
                    }
                    if (isset($this->post['tx_multishop_pi1']['delivery_last_name'])) {
                        $delivery_name .= ' ' . $this->post['tx_multishop_pi1']['delivery_last_name'];
                    }
                    $this->post['tx_multishop_pi1']['delivery_name'] = '';
                    if ($delivery_name) {
                        $delivery_name = preg_replace('/\s+/', ' ', $delivery_name);
                        $this->post['tx_multishop_pi1']['delivery_name'] = $delivery_name;
                    }
                    //
                    $keys = array();
                    $keys[] = 'company';
                    $keys[] = 'name';
                    $keys[] = 'street_name';
                    $keys[] = 'address_number';
                    $keys[] = 'address_ext';
                    $keys[] = 'building';
                    $keys[] = 'zip';
                    $keys[] = 'city';
                    $keys[] = 'country';
                    $keys[] = 'email';
                    $keys[] = 'telephone';
                    $keys[] = 'mobile';
                    $keys[] = 'fax';
                    foreach ($keys as $key) {
                        $string = 'billing_' . $key;
                        $updateArray[$string] = $this->post['tx_multishop_pi1'][$string];
                        $string = 'delivery_' . $key;
                        $updateArray[$string] = $this->post['tx_multishop_pi1'][$string];
                    }
                    $updateArray['billing_address'] = preg_replace('/\s+/', ' ', $updateArray['billing_street_name'] . ' ' . $updateArray['billing_address_number'] . ' ' . $updateArray['billing_address_ext']);
                    $updateArray['delivery_address'] = preg_replace('/\s+/', ' ', $updateArray['delivery_street_name'] . ' ' . $updateArray['delivery_address_number'] . ' ' . $updateArray['delivery_address_ext']);
                    $updateArray['expected_delivery_date'] = '';
                    if ($this->post['expected_delivery_date']) {
                        $updateArray['expected_delivery_date'] = strtotime($this->post['expected_delivery_date']);
                    }
                    $updateArray['track_and_trace_code'] = '';
                    if (isset($this->post['track_and_trace_code'])) {
                        $updateArray['track_and_trace_code'] = $this->post['track_and_trace_code'];
                    }
                    if (count($updateArray)) {
                        $close_window = 1;
                        $updateArray['orders_last_modified'] = time();
                        // hook to let other plugins further manipulate
                        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPreProc'])) {
                            $params = array(
                                    'updateArray' => &$updateArray,
                                    'orders_id' => &$this->get['orders_id'],
                            );
                            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPreProc'] as $funcRef) {
                                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                            }
                        }
                        // hook eol
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $this->get['orders_id'] . '\'', $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        $orders['expected_delivery_date'] = $this->post['expected_delivery_date'];
                        $orders['track_and_trace_code'] = $this->post['track_and_trace_code'];
                        $order_memo = $this->post['order_memo'];
                        if ($order_memo == '<p></p>' || $order_memo == "<p><br></p>\r\n") {
                            $order_memo = '';
                        }
                        $orders['order_memo'] = '';
                        if (!empty($order_memo)) {
                            $orders['order_memo'] = $this->post['order_memo'];
                        }
                        // repair tax stuff
                        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                        $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                        $mslib_order->init($this);
                        $mslib_order->repairOrder($this->get['orders_id']);
                        // update order discount if any
                        $order_tax_data_rec = mslib_befe::getRecord($this->get['orders_id'], 'tx_multishop_orders', 'orders_id', array(), 'orders_tax_data');
                        if (isset($order_tax_data_rec) && !empty($order_tax_data_rec['orders_tax_data'])) {
                            $order_tax_data = unserialize($order_tax_data_rec['orders_tax_data']);
                            $this->post['hidden_subtotal'] = $order_tax_data['sub_total_excluding_vat'];
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                $this->post['hidden_subtotal'] = $order_tax_data['sub_total'];
                            }
                        }
                        $updateArray = array();
                        $updateArray['discount_percentage'] = 0;
                        if (isset($this->post['edit_discount_percentage']) && $this->post['edit_discount_percentage'] > 0) {
                            $updateArray['discount_percentage'] = $this->post['edit_discount_percentage'];
                            $hidden_subtotal = $this->post['hidden_subtotal'];
                            $discount_amount = number_format(($hidden_subtotal / 100) * $this->post['edit_discount_percentage'], 2, ',', '');
                            if ($discount_amount != $this->post['edit_discount_value']) {
                                $this->post['edit_discount_value'] = $discount_amount;
                            }
                        }
                        if (isset($this->post['edit_discount_value'])) {
                            $this->post['edit_discount_value'] = mslib_befe::formatNumbersToMysql($this->post['edit_discount_value']);
                            $updateArray['discount'] = $this->post['edit_discount_value'];
                        }
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $this->get['orders_id'] . '\'', $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        // repair it again
                        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                        $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                        $mslib_order->init($this);
                        $mslib_order->repairOrder($this->get['orders_id']);
                    }
                    $close_window = 1;
                } // if ($this->post) eol
                // disable the repair order in open mode, to prevent data corruption on existing orders.
                // repair tax stuff
                //require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
                //$mslib_order=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                //$mslib_order->init($this);
                //$mslib_order->repairOrder($this->get['orders_id']);
                //is proposal
                $is_proposal_params = '';
                if ($order['is_proposal'] == 1) {
                    $is_proposal_params = '&tx_multishop_pi1[is_proposal]=1';
                }
                // hook to let other plugins further manipulate
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPostProc'])) {
                    $params = array(
                            'updateArray' => &$updateArray,
                            'orders_id' => &$this->get['orders_id'],
                            'order' => &$order
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPostProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                // hook eol
            } // if (!$order['is_locked']) eol
            // update stuff with or without locked order
            if (isset($this->post['tx_multishop_pi1']['orders_paid_timestamp']) && mslib_befe::isValidDate($this->post['tx_multishop_pi1']['orders_paid_timestamp'])) {
                $this->post['tx_multishop_pi1']['orders_paid_timestamp'] = strtotime($this->post['tx_multishop_pi1']['orders_paid_timestamp']);
            } else {
                unset($this->post['tx_multishop_pi1']['orders_paid_timestamp']);
            }
            if ($this->post['tx_multishop_pi1']['orders_paid_timestamp']) {
                if ($order['paid']) {
                    // if order already paid just update timestamp
                    $updateArray = array();
                    if (isset($this->post['tx_multishop_pi1']['orders_paid_timestamp_visual']) && !$this->post['tx_multishop_pi1']['orders_paid_timestamp_visual']) {
                        $updateArray['paid'] = '0';
                        $updateArray['orders_paid_timestamp'] = '';
                    } else {
                        $updateArray['orders_paid_timestamp'] = $this->post['tx_multishop_pi1']['orders_paid_timestamp'];
                    }
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPreProc'])) {
                        $params = array(
                                'updateArray' => &$updateArray,
                                'orders_id' => &$this->get['orders_id'],
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderPreProc'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                    if (count($updateArray)) {
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $this->get['orders_id'] . '\'', $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                } else {
                    // if order not yet paid use official method for updating to status paid
                    mslib_fe::updateOrderStatusToPaid($this->get['orders_id'], $this->post['tx_multishop_pi1']['orders_paid_timestamp']);
                }
            }
        } // if ($this->ms['MODULES']['ORDER_EDIT']) eol
        // editable properties of orders, even when ORDERS_EDIT is disabled
        if ($this->post) {
            $updateArray = array();
            $updateArray['expected_delivery_date'] = '';
            if ($this->post['expected_delivery_date'] && $this->post['expected_delivery_date_local']) {
                $updateArray['expected_delivery_date'] = strtotime($this->post['expected_delivery_date']);
            }
            if (isset($this->post['track_and_trace_code'])) {
                $updateArray['track_and_trace_code'] = $this->post['track_and_trace_code'];
            }
            $order_memo = $this->post['order_memo'];
            if ($order_memo == '<p></p>') {
                $order_memo = '';
            }
            $updateArray['order_memo'] = '';
            if (!empty($order_memo)) {
                $updateArray['order_memo'] = $this->post['order_memo'];
            }
            $order_memo_rec = mslib_befe::getRecord($this->get['orders_id'], 'tx_multishop_orders', 'orders_id', array(), 'order_memo');
            if (!empty($order_memo)) {
                if ($order_memo_rec['order_memo'] != $this->post['order_memo']) {
                    $updateArray['memo_crdate'] = time();
                }
            }
            if (isset($this->post['tx_multishop_pi1']['orders_paid_timestamp']) && mslib_befe::isValidDate($this->post['tx_multishop_pi1']['orders_paid_timestamp'])) {
                $this->post['tx_multishop_pi1']['orders_paid_timestamp'] = strtotime($this->post['tx_multishop_pi1']['orders_paid_timestamp']);
            } else {
                unset($this->post['tx_multishop_pi1']['orders_paid_timestamp']);
            }
            if ($this->post['tx_multishop_pi1']['orders_paid_timestamp']) {
                if ($order['paid']) {
                    if (isset($this->post['tx_multishop_pi1']['orders_paid_timestamp_visual']) && !$this->post['tx_multishop_pi1']['orders_paid_timestamp_visual']) {
                        $updateArray['paid'] = '0';
                        $updateArray['orders_paid_timestamp'] = '';
                    } else {
                        $updateArray['orders_paid_timestamp'] = $this->post['tx_multishop_pi1']['orders_paid_timestamp'];
                    }
                }
            }
            if (count($updateArray)) {
                $close_window = 1;
                $updateArray['orders_last_modified'] = time();
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $this->get['orders_id'] . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                $orders['expected_delivery_date'] = '';
                if (!empty($this->post['expected_delivery_date_local'])) {
                    $orders['expected_delivery_date'] = $this->post['expected_delivery_date'];
                }
                $orders['track_and_trace_code'] = $this->post['track_and_trace_code'];
                $orders['order_memo'] = '';
                if (!empty($order_memo)) {
                    $orders['order_memo'] = $this->post['order_memo'];
                }
            }
            if ($this->post['order_status']) {
                // first get current status
                if ($this->post['order_status'] == $order['status']) {
                    // no new order status has been defined. only mail when the email text box is containing content
                    $comments = $this->post['comments'];
                    if ($comments == '<p></p>' || $comments == "<p><br></p>\r\n") {
                        $comments = '';
                    }
                    if (!empty($comments)) {
                        $continue_update = 1;
                    } else {
                        $this->post['comments'] = '';
                    }
                } else {
                    $continue_update = 1;
                }
                if ($continue_update) {
                    // dynamic variables
                    mslib_befe::updateOrderStatus($this->get['orders_id'], $this->post['order_status'], $this->post['customer_notified'], 'edit_order_save');
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderStatusPostProc'])) {
                        // hook
                        $params = array(
                                'orders_id' => &$this->get['orders_id'],
                                'order_status' => $this->post['order_status']
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrderUpdateOrderStatusPostProc'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                        // hook oef
                    }
                }
            }
        }
        // hook for adding new items to details fieldset
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsSavePostHook'])) {
            // hook
            $params = array(
                    'orders_id' => &$this->get['orders_id'],
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsSavePostHook'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
            // hook oef
        }
        if ($redirect_after_delete) {
            header("Location: " . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $this->get['orders_id'] . '&action=edit_order' . $is_proposal_params, 1));
            exit();
        } else {
            if (isset($this->post['Submit'])) {
                $close_window = 0;
            } else if (isset($this->post['SaveClose'])) {
                $close_window = 1;
            }
            if ($close_window) {
                if ($this->post['tx_multishop_pi1']['referrer']) {
                    if (strpos($this->post['tx_multishop_pi1']['referrer'], 'edit_product') !== false || strpos($this->post['tx_multishop_pi1']['referrer'], 'edit_order') !== false) {
                        $this->post['tx_multishop_pi1']['referrer'] = $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_orders' . $is_proposal_params);
                    }
                    header("Location: " . $this->post['tx_multishop_pi1']['referrer']);
                    exit();
                } else {
                    header("Location: " . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_orders' . $is_proposal_params, 1));
                    exit();
                }
            }
        }
        //
        $str = "SELECT *, o.crdate, o.status, osd.name as orders_status from tx_multishop_orders o left join tx_multishop_orders_status os on o.status=os.id left join tx_multishop_orders_status_description osd on (os.id=osd.orders_status_id AND o.language_id=osd.language_id) where o.orders_id='" . $this->get['orders_id'] . "'";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $orders = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
        if ($orders['orders_id']) {
            $save_block = '
				<hr>
                <div class="clearfix">
                	<div class="pull-right">
                    <a href="' . $subpartArray['###VALUE_REFERRER###'] . '" class="btn btn-danger"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-remove fa-stack-1x"></i></span> ' . $this->pi_getLL('cancel') . '</a>';
            if ($this->get['action'] == 'edit_order') {
                $save_block .= ' <button name="Submit" type="submit" value="" class="btn btn-success" id="btnSave"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span> ' . $this->pi_getLL('update') . '</button>';
                $save_block .= ' <button name="SaveClose" type="submit" value="" class="btn btn-success" id="btnSaveClose"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span> ' . $this->pi_getLL('admin_update_close') . '</button>';
            } else {
                $save_block .= ' <button name="Submit" type="submit" value="" class="btn btn-success" id="btnSave"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span> ' . $this->pi_getLL('save') . '</button>';
                $save_block .= ' <button name="SaveClose" type="submit" value="" class="btn btn-success" id="btnSaveClose"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span> ' . $this->pi_getLL('admin_save_close') . '</button>';
            }
            $save_block .= '</div>
                </div>';
            // count total products
            $total_amount = 0;
            $str2 = "SELECT * from tx_multishop_orders_products where orders_id='" . addslashes($orders['orders_id']) . "' order by sort_order asc";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
                $orders_products[] = $row;
                $total_amount = ($row['qty'] * $row['final_price']) + $total_amount;
                // now count the attributes
                $str3 = "SELECT * from tx_multishop_orders_products_attributes where orders_products_id='" . addslashes($row['orders_products_id']) . "' order by orders_products_attributes_id asc";
                $qry3 = $GLOBALS['TYPO3_DB']->sql_query($str3);
                while (($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3)) != false) {
                    if ($row3['price_prefix'] == '+') {
                        $total_amount = $total_amount + ($row['qty'] * $row3['options_values_price']);
                    } else {
                        $total_amount = $total_amount - ($row['qty'] * $row3['options_values_price']);
                    }
                    $orders_products_attributes[$row['orders_products_id']][] = $row3;
                }
                // now count the attributes eof
            }
            // count eof products
            $order_date = strftime("%x", $orders['crdate']);
            $tmpcontent = '';
            $tmpcontent .= '
    <script language="JavaScript" type="text/JavaScript">
        function CONFIRM() {
            if (confirm("' . addslashes($this->pi_getLL('are_you_sure')) . '?")) {
                return true;
            } else {
                return false;
            }
        }
    </script>';
            $enabled_countries = mslib_fe::loadEnabledCountries();
            $dont_overide_billing_countries = false;
            $dont_overide_delivery_countries = false;
            foreach ($enabled_countries as $country) {
                $billing_countries[] = '<option value="' . mslib_befe::strtolower($country['cn_short_en']) . '" ' . ((mslib_befe::strtolower($orders['billing_country']) == strtolower($country['cn_short_en'])) ? 'selected' : '') . '>' . htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])) . '</option>';
                if (mslib_befe::strtolower($orders['billing_country']) == strtolower($country['cn_short_en'])) {
                    $dont_overide_billing_countries = true;
                }
                $delivery_countries[] = '<option value="' . mslib_befe::strtolower($country['cn_short_en']) . '" ' . ((mslib_befe::strtolower($orders['delivery_country']) == strtolower($country['cn_short_en'])) ? 'selected' : '') . '>' . htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])) . '</option>';
                if (mslib_befe::strtolower($orders['delivery_country']) == strtolower($country['cn_short_en'])) {
                    $dont_overide_delivery_countries = true;
                }
            }
            if ($dont_overide_billing_countries) {
                $billing_countries = array_merge(array('<option value="">' . ucfirst($this->pi_getLL('choose_country')) . '</option>'), $billing_countries);
            } else {
                $billing_countries = array_merge(array('<option value="' . $orders['billing_country'] . '">' . $orders['billing_country'] . '</option>'), $billing_countries);
            }
            $billing_countries_sb = '<select class="form-control" name="tx_multishop_pi1[billing_country]" id="edit_billing_country">' . implode("\n", $billing_countries) . '</select>';
            if ($dont_overide_delivery_countries) {
                $delivery_countries = array_merge(array('<option value="">' . ucfirst($this->pi_getLL('choose_country')) . '</option>'), $delivery_countries);
            } else {
                $delivery_countries = array_merge(array('<option value="' . $orders['delivery_country'] . '">' . $orders['delivery_country'] . '</option>'), $delivery_countries);
            }
            $delivery_countries_sb = '<select class="form-control" name="tx_multishop_pi1[delivery_country]" id="edit_delivery_country" required="required">' . implode("\n", $delivery_countries) . '</select>';
            // settings for controlling order details
            $settings = array();
            $settings['enable_edit_customer_details'] = 1;
            $settings['enable_edit_orders_details'] = 1;
            if ($orders['is_locked']) {
                $settings['enable_edit_customer_details'] = 0;
                $settings['enable_edit_orders_details'] = 0;
            }
            $address_data = array();
            $address_data = $orders;
            $address_data['building'] = $orders['billing_building'];
            $address_data['address'] = $orders['billing_address'];
            $address_data['zip'] = $orders['billing_zip'];
            $address_data['city'] = $orders['billing_city'];
            $address_data['country'] = $orders['billing_country'];
            $settings['billing_address_value'] = mslib_befe::customerAddressFormat($address_data);
            $settings['customer_edit_link'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]=' . $orders['customer_id'] . '&action=edit_customer', 1);
            // hook for adding new items to details fieldset
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPreHook'])) {
                // hook
                $params = array(
                        'orders' => &$orders,
                        'settings' => &$settings
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersPreHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
                // hook oef
            }
            $editOrderFormFieldset = array();
            $tmpcontent .= '
        	<div class="row">
			<div class="col-md-6">
        <div class="panel panel-default" id="address_details">
        <div class="panel-heading"><h3>' . $this->pi_getLL('address_details') . '</h3></div>
        <div class="panel-body">
			<div class="row">
			<div class="col-md-6">
 				<div class="panel panel-default">
        			<div class="panel-heading"><h3>' . $this->pi_getLL('billing_details') . '</h3></div>
						<div class="panel-body">
						';
            //validate first
            $validate_erno = array();
            $this->ms['MODULES']['DISABLE_EDIT_ORDER_CUSTOMER_DETAILS_VALIDATION'] = (int)$this->ms['MODULES']['DISABLE_EDIT_ORDER_CUSTOMER_DETAILS_VALIDATION'];
            if (!$this->ms['MODULES']['DISABLE_EDIT_ORDER_CUSTOMER_DETAILS_VALIDATION']) {
                if (!$orders['billing_email']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_email_address_has_been_specified') . '</li>';
                }
                if (!$orders['billing_street_name']) {
                    $validate_erno[] = '<li>' . 'No street name has been specified' . '</li>';
                }
                if (!$orders['billing_address_number']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_address_number_has_been_specified') . '</li>';
                }
                if (!$orders['billing_first_name']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_first_name_has_been_specified') . '</li>';
                }
                if (!$orders['billing_last_name']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_last_name_has_been_specified') . '</li>';
                }
                if (!$orders['billing_zip']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_zip_has_been_specified') . '</li>';
                }
                if (!$orders['billing_city']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_city_has_been_specified') . '</li>';
                }
                if ($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] && !$orders['billing_company']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('company_is_required') . '</li>';
                }
                if (!$orders['billing_country']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('country_is_required') . '</li>';
                }
            }
            $count_validate_erno = count($validate_erno);
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_customer_details']) {
                $edit_billing_details = array();
                $tmpcontent .= '<div class="edit_billing_details_container" id="edit_billing_details_container"' . ($count_validate_erno ? '' : ' style="display:none"') . '>';
                if ($this->ms['MODULES']['DISPLAY_GENDER_INPUT_IN_EDIT_ORDER_CUSTOMER_DETAILS']) {
                    $edit_billing_details['billing_gender'] = '<div class="form-group">
                        <label class="control-label col-md-5">' . ucfirst($this->pi_getLL('title')) . '</label>
                        <div class="col-sm-7">
                            <div class="radio radio-success radio-inline">
                                <input type="radio" name="tx_multishop_pi1[billing_gender]" value="m" class="InputGroup account-gender-radio" id="edit_billing_gender_male"' . ($orders['billing_gender'] == 'm' ? ' checked="checked"' : '') . ' required="required">
                                <label class="account-male" for="edit_billing_gender_male">' . $this->pi_getLL('mr') . '</label>
                            </div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" name="tx_multishop_pi1[billing_gender]" value="f" class="InputGroup account-gender-radio" id="edit_billing_gender_female"' . ($orders['billing_gender'] == 'f' ? ' checked="checked"' : '') . '>
                                <label class="account-female" for="edit_billing_gender_female">' . $this->pi_getLL('mrs') . '</label>
                            </div>                           
                        </div>
                    </div>';
                }
                $edit_billing_details['billing_company'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('company')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_company]" type="text" id="edit_billing_company" value="' . $orders['billing_company'] . '" />
						</div>
					</div>';
                if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                    $edit_billing_details['billing_department'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('department')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_department]" type="text" id="edit_billing_department" value="' . $orders['billing_department'] . '" />
						</div>
					</div>';
                }
                $edit_billing_details['billing_first_name'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('first_name')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_first_name]" type="text" id="edit_billing_first_name" value="' . $orders['billing_first_name'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_middle_name'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('middle_name')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_middle_name]" type="text" id="edit_billing_middle_name" value="' . $orders['billing_middle_name'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_last_name'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('last_name')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_last_name]" type="text" id="edit_billing_last_name" value="' . $orders['billing_last_name'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_street_name'] = '<div class="form-group">
						<label class="control-label col-md-5" for="delivery_address">' . ucfirst($this->pi_getLL('street_address')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_street_name]" type="text" id="edit_billing_street_name" value="' . $orders['billing_street_name'] . '" />
						</div>
						<span  class="error-space left-this"></span>
					</div>';
                $edit_billing_details['billing_address_number'] = '<div class="form-group">
						<label class="control-label col-md-5 billing_account-addressnumber" for="billing_address_number">' . ucfirst($this->pi_getLL('street_address_number')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_address_number]" type="text" id="edit_billing_address_number" value="' . $orders['billing_address_number'] . '" /><span class="error-space left-this"></span>
						</div>
					</div>';
                $edit_billing_details['billing_address_ext'] = '<div class="form-group">
						<label class="control-label col-md-5 billing_account-address_ext" for="billing_address_ext">' . ucfirst($this->pi_getLL('address_extension')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_address_ext]" type="text" id="edit_billing_address_ext" value="' . $orders['billing_address_ext'] . '" /><span class="error-space left-this"></span>
						</div>
					</div>';
                $edit_billing_details['billing_building'] = '<div class="form-group">
						<label class="control-label col-md-5 billing_account-building" for="billing_building">' . ucfirst($this->pi_getLL('building')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_building]" type="text" id="edit_billing_building" value="' . $orders['billing_building'] . '" /><span class="error-space left-this"></span>
						</div>
					</div>';
                $edit_billing_details['billing_zip'] = '<div class="form-group">
						<label class="control-label col-md-5 account-zip" for="zip">' . ucfirst($this->pi_getLL('zip')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_zip]" type="text" id="edit_billing_zip" value="' . $orders['billing_zip'] . '" /><span class="error-space"></span>
						</div>
					</div>';
                $edit_billing_details['billing_city'] = '<div class="form-group">
						<label class="control-label col-md-5 account-city" for="city">' . ucfirst($this->pi_getLL('city')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_city]" type="text" id="edit_billing_city" value="' . $orders['billing_city'] . '" /><span class="error-space"></span>
						</div>
					</div>';
                $edit_billing_details['billing_country'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('country')) . '</label>
						<div class="col-md-7">
							' . $billing_countries_sb . '
						</div>
					</div>';
                $edit_billing_details['billing_email'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('email')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_email]" type="text" id="edit_billing_email" value="' . $orders['billing_email'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_telephone'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('telephone')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_telephone]" type="text" id="edit_billing_telephone" value="' . $orders['billing_telephone'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_mobile'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('mobile')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_mobile]" type="text" id="edit_billing_mobile" value="' . $orders['billing_mobile'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_fax'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('fax')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_fax]" type="text" id="edit_billing_fax" value="' . $orders['billing_fax'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_vat_id'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('vat_id', 'VAT ID')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_vat_id]" type="text" id="edit_billing_vat_id" value="' . $orders['billing_vat_id'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_coc_id'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('coc_id', 'COC Nr.:')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[billing_coc_id]" type="text" id="edit_billing_coc_id" value="' . $orders['billing_coc_id'] . '" />
						</div>
					</div>';
                $edit_billing_details['billing_save_form'] = '
                    <div id="billing_details_erno_wrapper">
                    ' . ($count_validate_erno ? '<div class="erno_message well text-danger"><ul>' . implode("\n", $validate_erno) . '</ul></div>' : '') . '
                    </div>
                    <hr>
					<div class="clearfix">
						<div class="pull-right">
							<a href="#" id="close_edit_billing_info" class="btn btn-primary"><i class="fa fa-save"></i> ' . $this->pi_getLL('save') . '</a>
						</div>
					</div>';
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['editOrderBillingDetailsInput'])) {
                    $params = array(
                            'order' => $order,
                            'edit_billing_details' => &$edit_billing_details
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['editOrderBillingDetailsInput'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                $tmpcontent .= implode("\n", $edit_billing_details);
                $tmpcontent .= '</div>';
            }
            $billing_details_info = '<div class="address_details_container" id="billing_details_container"' . ($count_validate_erno && $this->ms['MODULES']['ORDER_EDIT'] && $settings['enable_edit_customer_details'] ? ' style="display:none"' : '') . '>';
            if ($orders['billing_company']) {
                $billing_details_info .= '<strong>' . mslib_fe::RemoveXSS($orders['billing_company']) . '</strong><br />';
            }
            if ($orders['billing_department']) {
                $billing_details_info .= '<strong>' . mslib_fe::RemoveXSS($orders['billing_department']) . '</strong><br />';
            }
            $billing_details_info .= '<a href="' . $settings['customer_edit_link'] . '">' . mslib_fe::RemoveXSS($orders['billing_name']) . '</a><br />
            ' . $settings['billing_address_value'] . '<br /><br />';
            if ($orders['billing_email']) {
                $billing_details_info .= $this->pi_getLL('email') . ': <a href="mailto:' . mslib_fe::RemoveXSS($orders['billing_email']) . '">' . mslib_fe::RemoveXSS($orders['billing_email']) . '</a><br />';
            }
            if ($orders['billing_telephone']) {
                $billing_details_info .= $this->pi_getLL('telephone') . ': ' . mslib_fe::RemoveXSS($orders['billing_telephone']) . '<br />';
            }
            if ($orders['billing_mobile']) {
                $billing_details_info .= $this->pi_getLL('mobile') . ': ' . mslib_fe::RemoveXSS($orders['billing_mobile']) . '<br />';
            }
            if ($orders['billing_fax']) {
                $billing_details_info .= $this->pi_getLL('fax') . ': ' . mslib_fe::RemoveXSS($orders['billing_fax']) . '<br />';
            }
            if ($orders['billing_vat_id']) {
                $billing_details_info .= '<strong>' . $this->pi_getLL('vat_id') . ' ' . mslib_fe::RemoveXSS($orders['billing_vat_id']) . '</strong><br />';
            }
            if ($orders['billing_coc_id']) {
                $billing_details_info .= '<strong>' . $this->pi_getLL('coc_id') . ': ' . mslib_fe::RemoveXSS($orders['billing_coc_id']) . '</strong><br />';
            }
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_customer_details']) {
                $billing_details_info .= '<hr><div class="clearfix"><div class="pull-right"><a href="#" id="edit_billing_info" class="btn btn-primary"><i class="fa fa-pencil"></i> ' . $this->pi_getLL('edit') . '</a></div></div>';
            }
            $billing_details_info .= '</div>';
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['editOrderBillingDetailsInfo'])) {
                $params = array(
                        'orders' => $orders,
                        'settings' => $settings,
                        'billing_details_info' => &$billing_details_info,
                        'count_validate_erno' => $count_validate_erno
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['editOrderBillingDetailsInfo'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            $tmpcontent .= $billing_details_info;
            $tmpcontent .= '
   	</div></div></div>
	<div class="col-md-6">
		<div class="panel panel-default">
        	<div class="panel-heading"><h3>' . $this->pi_getLL('delivery_details') . '</h3></div>
			<div class="panel-body">
				';
            //validate first
            $validate_erno = array();
            if (!$this->ms['MODULES']['DISABLE_EDIT_ORDER_CUSTOMER_DETAILS_VALIDATION']) {
                if (!$orders['delivery_email']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_email_address_has_been_specified') . '</li>';
                }
                if (!$orders['delivery_street_name']) {
                    $validate_erno[] = '<li>' . 'No street name has been specified' . '</li>';
                }
                if (!$orders['delivery_address_number']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_address_number_has_been_specified') . '</li>';
                }
                if (!$orders['delivery_first_name']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_first_name_has_been_specified') . '</li>';
                }
                if (!$orders['delivery_last_name']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_last_name_has_been_specified') . '</li>';
                }
                if (!$orders['delivery_zip']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_zip_has_been_specified') . '</li>';
                }
                if (!$orders['delivery_city']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('no_city_has_been_specified') . '</li>';
                }
                if ($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] && !$orders['delivery_company']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('company_is_required') . '</li>';
                }
                if (!$orders['delivery_country']) {
                    $validate_erno[] = '<li>' . $this->pi_getLL('country_is_required') . '</li>';
                }
            }
            $count_validate_erno = count($validate_erno);
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_customer_details']) {
                $edit_delivery_details = array();
                $tmpcontent .= '<div class="edit_delivery_details_container" id="edit_delivery_details_container"' . ($count_validate_erno ? '' : ' style="display:none"') . '>';
                if ($this->ms['MODULES']['DISPLAY_GENDER_INPUT_IN_EDIT_ORDER_CUSTOMER_DETAILS']) {
                    $edit_delivery_details['delivery_gender'] = '<div class="form-group">
                        <label class="control-label col-md-5">' . ucfirst($this->pi_getLL('title')) . '</label>
                        <div class="col-sm-7">
                            <div class="radio radio-success radio-inline">
                                <input type="radio" name="tx_multishop_pi1[delivery_gender]" value="m" class="InputGroup account-delivery-gender-radio" id="edit_delivery_gender_male"' . ($orders['delivery_gender'] == 'm' ? ' checked="checked"' : '') . ' required="required">
                                <label class="account-male" for="edit_delivery_gender_male">' . $this->pi_getLL('mr') . '</label>
                            </div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" name="tx_multishop_pi1[delivery_gender]" value="f" class="InputGroup account-delivery-gender-radio" id="edit_delivery_gender_female"' . ($orders['delivery_gender'] == 'f' ? ' checked="checked"' : '') . '>
                                <label class="account-female" for="edit_delivery_gender_female">' . $this->pi_getLL('mrs') . '</label>
                            </div>                           
                        </div>
                    </div>';
                }
                $edit_delivery_details['delivery_company'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('company')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_company]" type="text" id="edit_delivery_company" value="' . $orders['delivery_company'] . '" />
                	</div>
                </div>';
                if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                    $edit_delivery_details['delivery_department'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('department')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[delivery_department]" type="text" id="edit_delivery_department" value="' . $orders['delivery_department'] . '" />
						</div>
					</div>';
                }
                $edit_delivery_details['delivery_first_name'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('first_name')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_first_name]" type="text" id="edit_delivery_first_name" value="' . $orders['delivery_first_name'] . '" />
                	</div>
                </div>';
                $edit_delivery_details['delivery_middle_name'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('middle_name')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_middle_name]" type="text" id="edit_delivery_middle_name" value="' . $orders['delivery_middle_name'] . '" />
                	</div>
                </div>';
                $edit_delivery_details['delivery_last_name'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('last_name')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_last_name]" type="text" id="edit_delivery_last_name" value="' . $orders['delivery_last_name'] . '" />
                	</div>
                </div>';
                $edit_delivery_details['delivery_street_name'] = '<div class="form-group">
                	<label class="control-label col-md-5" for="delivery_address">' . ucfirst($this->pi_getLL('street_address')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_street_name]" type="text" id="edit_delivery_street_name" value="' . $orders['delivery_street_name'] . '" />
                		<span  class="error-space left-this"></span>
                	</div>
                </div>';
                $edit_delivery_details['delivery_address_number'] = '<div class="form-group">
                	<label class="control-label col-md-5 delivery_account-addressnumber" for="delivery_address_number">' . ucfirst($this->pi_getLL('street_address_number')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_address_number]" type="text" id="edit_delivery_address_number" value="' . $orders['delivery_address_number'] . '" />
                		<span class="error-space left-this"></span>
                	</div>
                </div>';
                $edit_delivery_details['delivery_address_ext'] = '<div class="form-group">
                	<label class="control-label col-md-5 delivery_account-address_ext" for="delivery_address_ext">' . ucfirst($this->pi_getLL('address_extension')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_address_ext]" type="text" id="edit_delivery_address_ext" value="' . $orders['delivery_address_ext'] . '" />
                		<span class="error-space left-this"></span>
                	</div>
                </div>';
                $edit_delivery_details['delivery_building'] = '<div class="form-group">
                	<label class="control-label col-md-5 delivery_account-building" for="delivery_building">&nbsp' . ucfirst($this->pi_getLL('building')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_building]" type="text" id="edit_delivery_building" value="' . $orders['delivery_building'] . '" />
                		<span class="error-space left-this"></span>
                	</div>
                </div>';
                $edit_delivery_details['delivery_zip'] = '<div class="form-group">
                	<label class="control-label col-md-5 account-zip" for="zip">' . ucfirst($this->pi_getLL('zip')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_zip]" type="text" id="edit_delivery_zip" value="' . $orders['delivery_zip'] . '" />
                		<span class="error-space"></span>
                	</div>
                </div>';
                $edit_delivery_details['delivery_city'] = '<div class="form-group">
                	<label class="control-label col-md-5 account-city" for="city">' . ucfirst($this->pi_getLL('city')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_city]" type="text" id="edit_delivery_city" value="' . $orders['delivery_city'] . '" />
                		<span class="error-space"></span>
                	</div>
                </div>';
                $edit_delivery_details['delivery_country'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('country')) . '</label>
                	<div class="col-md-7">
                		' . $delivery_countries_sb . '
                	</div>
                </div>';
                $edit_delivery_details['delivery_email'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('email')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_email]" type="text" id="edit_delivery_email" value="' . $orders['delivery_email'] . '" />
                	</div>
                </div>';
                $edit_delivery_details['delivery_telephone'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('telephone')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_telephone]" type="text" id="edit_delivery_telephone" value="' . $orders['delivery_telephone'] . '" />
                	</div>
                </div>';
                $edit_delivery_details['delivery_mobile'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('mobile')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_mobile]" type="text" id="edit_delivery_mobile" value="' . $orders['delivery_mobile'] . '" />
                	</div>
                </div>';
                $edit_delivery_details['delivery_fax'] = '<div class="form-group">
                	<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('fax')) . '</label>
                	<div class="col-md-7">
                		<input class="form-control" name="tx_multishop_pi1[delivery_fax]" type="text" id="edit_delivery_fax" value="' . $orders['delivery_fax'] . '" />
                	</div>
                </div>';
                $edit_delivery_details['delivery_vat_id'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('vat_id', 'VAT ID')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[delivery_vat_id]" type="text" id="edit_delivery_vat_id" value="' . $orders['delivery_vat_id'] . '" />
						</div>
					</div>';
                $edit_delivery_details['delivery_coc_id'] = '<div class="form-group">
						<label class="control-label col-md-5">' . ucfirst($this->pi_getLL('coc_id', 'COC Nr.:')) . '</label>
						<div class="col-md-7">
							<input class="form-control" name="tx_multishop_pi1[delivery_coc_id]" type="text" id="edit_delivery_coc_id" value="' . $orders['delivery_coc_id'] . '" />
						</div>
					</div>';
                $edit_delivery_details['delivery_save_form'] = '
                <div id="delivery_details_erno_wrapper">
                ' . ($count_validate_erno ? '<div class="erno_message well text-danger"><ul>' . implode("\n", $validate_erno) . '</ul></div>' : '') . '
                </div>
                <hr>
                <div class="clearfix">
                	<div class="pull-right">
                		<a href="#" id="close_edit_delivery_info" class="btn btn-primary"><i class="fa fa-save"></i> ' . $this->pi_getLL('save') . '</a>
                		<a href="#" id="copy_from_billing_details" class="btn btn-primary"><i class="fa fa-copy"></i> ' . $this->pi_getLL('copy_from_billing_details') . '</a>
                	</div>
                </div>';
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['editOrderDeliveryDetailsInput'])) {
                    $params = array(
                            'order' => $order,
                            'edit_delivery_details' => &$edit_delivery_details
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['editOrderDeliveryDetailsInput'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                $tmpcontent .= implode("\n", $edit_delivery_details);
                $tmpcontent .= '</div>';
            }
            $tmpcontent .= '<div class="address_details_container" id="delivery_details_container"' . ($count_validate_erno && $this->ms['MODULES']['ORDER_EDIT'] && $settings['enable_edit_customer_details'] ? ' style="display:none"' : '') . '>';
            if ($orders['delivery_company']) {
                $tmpcontent .= '<strong>' . mslib_fe::RemoveXSS($orders['delivery_company']) . '</strong><br />';
            }
            if ($orders['delivery_department']) {
                $tmpcontent .= '<strong>' . mslib_fe::RemoveXSS($orders['delivery_department']) . '</strong><br />';
            }
            $address_data = array();
            $address_data = $orders;
            $address_data['building'] = mslib_fe::RemoveXSS($orders['delivery_building']);
            $address_data['address'] = mslib_fe::RemoveXSS($orders['delivery_address']);
            $address_data['zip'] = mslib_fe::RemoveXSS($orders['delivery_zip']);
            $address_data['city'] = mslib_fe::RemoveXSS($orders['delivery_city']);
            $address_data['country'] = $orders['delivery_country'];
            $delivery_address_value = mslib_befe::customerAddressFormat($address_data, 'delivery');
            $tmpcontent .= mslib_fe::RemoveXSS($orders['delivery_name']) . '<br />
              ' . $delivery_address_value . '<br /><br />';
            if ($orders['delivery_email']) {
                $tmpcontent .= $this->pi_getLL('email') . ': <a href="mailto:' . $orders['delivery_email'] . '">' . mslib_fe::RemoveXSS($orders['delivery_email']) . '</a><br />';
            }
            if ($orders['delivery_telephone']) {
                $tmpcontent .= $this->pi_getLL('telephone') . ': ' . mslib_fe::RemoveXSS($orders['delivery_telephone']) . '<br />';
            }
            if ($orders['delivery_mobile']) {
                $tmpcontent .= $this->pi_getLL('mobile') . ': ' . mslib_fe::RemoveXSS($orders['delivery_mobile']) . '<br />';
            }
            if ($orders['delivery_fax']) {
                $tmpcontent .= $this->pi_getLL('fax') . ': ' . mslib_fe::RemoveXSS($orders['delivery_fax']) . '<br />';
            }
            if ($orders['delivery_fax']) {
                $tmpcontent .= $this->pi_getLL('fax') . ': ' . mslib_fe::RemoveXSS($orders['delivery_fax']) . '<br />';
            }
            if ($orders['delivery_vat_id']) {
                $tmpcontent .= '<strong>' . $this->pi_getLL('vat_id') . ' ' . mslib_fe::RemoveXSS($orders['delivery_vat_id']) . '</strong><br />';
            }
            if ($orders['delivery_coc_id']) {
                $tmpcontent .= '<strong>' . $this->pi_getLL('coc_id') . ': ' . mslib_fe::RemoveXSS($orders['delivery_coc_id']) . '</strong><br />';
            }
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_customer_details']) {
                $tmpcontent .= '<hr><div class="clearfix"><div class="pull-right"><a href="#" id="edit_delivery_info" class="btn btn-primary"><i class="fa fa-pencil"></i> ' . $this->pi_getLL('edit') . '</a></div></div>';
            }
            $tmpcontent .= '</div>';
            $tmpcontent .= '
</div></div>
</div></div>

            ';
            $headerData = '
        <script type="text/javascript">
        function updateCustomerOrderDetails(type, data_serial) {
            href = "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=update_customer_order_details', 1) . '&details_type=" + type + "&orders_id=' . $this->get['orders_id'] . '";
            jQuery.ajax({
                type:   "POST",
                url:    href,
                data:   data_serial,
                dataType: "json",
                success: function(r) {
                     var details_container_id="#" + type + "_container";
                     var edit_details_container_id="#edit_" + type + "_container";
                     var erno_wrapper_id="#" + type + "_erno_wrapper";
                     if (r.status=="OK") {
                        if (r.customer_details!=\'\') {
                            $(details_container_id).empty();
                            if (type == "billing_details") {
                                var editedInfo = r.customer_details + "<hr><div class=\"clearfix\"><div class=\"pull-right\"><a href=\"#\" id=\"edit_billing_info\" class=\"btn btn-primary\"><i class=\"fa fa-pencil\"></i> ' . $this->pi_getLL('edit') . '</a></div></div>";    
                            } else if (type == "delivery_details") {
                                var editedInfo = r.customer_details + "<hr><div class=\"clearfix\"><div class=\"pull-right\"><a href=\"#\" id=\"edit_delivery_info\" class=\"btn btn-primary\"><i class=\"fa fa-pencil\"></i> ' . $this->pi_getLL('edit') . '</a></div></div>";
                            }
                            $(details_container_id).html(editedInfo);
                        }
                        $(details_container_id).show();
                        $(edit_details_container_id).hide();
                     } else if (r.status=="NOTOK") {
                        $(erno_wrapper_id).empty();
                        $(erno_wrapper_id).append(r.reason);
                     }
                }
            });
        }
        jQuery(document).ready(function($) {
            $(document).on("keyup", "#display_product_price, .edit_manual_price", function(){
                var self=$(this);
                var tax_id=0;
                if ($(self).attr("id")=="display_product_price" || $(self).hasClass("edit_manual_price")) {
                    tax_id=$("#product_tax").val();
                }
                if ($(this).val()!="") {
                    if(tax_id!=0 || tax_id!="") {
                        $.getJSON("' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset') . '", { current_price: $(this).val(), to_tax_include: false, tax_group_id: tax_id }, function (json) {
                            if (json.price_excluding_tax!="") {
                                $(self).parent().next().val(json.price_excluding_tax);
                            } else {
                                $(self).parent().next().val($(self).val());
                            }
                        });
                    } else {
                        $(self).parent().next().val($(self).val());
                    }
                } else {
                    $(self).parent().next().val("0");
                }
            });
            $(document).on("change", "#product_tax", function(){
                var self=$(this);
                $.getJSON("' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset') . '", { current_price: $("#display_product_price").val(), to_tax_include: false, tax_group_id: $(self).val() }, function (json) {
                    $("#display_product_price").next().val(json.price_excluding_tax);
                });
                $(".edit_manual_price").each(function(i, v){
                    $.getJSON("' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset') . '", { current_price: $(v).val(), to_tax_include: false, tax_group_id: $(self).val() }, function (json) {
                        $(v).next().val(json.price_excluding_tax);
                    });
                });
            });
            $(document).on("click", ".submit_button", function() {
                var edit_form = $(".admin_product_edit");
                if (!edit_form[0].checkValidity()) {
                    jQuery("ul.tabs li").removeClass("active");
                    jQuery("ul.tabs li").each(function(i, v) {
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
            $("#copy_from_billing_details").click(function(e) {
                e.preventDefault();
                
                $(".account-delivery-gender-radio").prop("checked", false);
                if ($(".account-gender-radio:checked").val()=="m") {
                    $("#edit_delivery_gender_male").prop("checked", true);
                } else if ($(".account-gender-radio:checked").val()=="f") {
                    $("#edit_delivery_gender_female").prop("checked", true);
                }

                $("#edit_delivery_company").val("");
                $("#edit_delivery_company").val($("#edit_billing_company").val());
                
                ' . ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER'] ? '
                $("#edit_delivery_department").val("");
                $("#edit_delivery_department").val($("#edit_billing_department").val());
                ' : '') . '

                $("#edit_delivery_first_name").val("");
                $("#edit_delivery_first_name").val($("#edit_billing_first_name").val());

                $("#edit_delivery_middle_name").val("");
                $("#edit_delivery_middle_name").val($("#edit_billing_middle_name").val());

                $("#edit_delivery_last_name").val("");
                $("#edit_delivery_last_name").val($("#edit_billing_last_name").val());

                $("#edit_delivery_street_name").val("");
                $("#edit_delivery_street_name").val($("#edit_billing_street_name").val());

                $("#edit_delivery_address_number").val("");
                $("#edit_delivery_address_number").val($("#edit_billing_address_number").val());

                $("#edit_delivery_address_ext").val("");
                $("#edit_delivery_address_ext").val($("#edit_billing_address_ext").val());

                $("#edit_delivery_building").val("");
                $("#edit_delivery_building").val($("#edit_billing_building").val());

                $("#edit_delivery_zip").val("");
                $("#edit_delivery_zip").val($("#edit_billing_zip").val().toUpperCase());

                $("#edit_delivery_region").val("");
                $("#edit_delivery_region").val($("#edit_billing_region").val());

                $("#edit_delivery_city").val("");
                $("#edit_delivery_city").val($("#edit_billing_city").val());

                $("#edit_delivery_country").val("");
                $("#edit_delivery_country").val($("#edit_billing_country").val());

                $("#edit_delivery_email").val("");
                $("#edit_delivery_email").val($("#edit_billing_email").val());

                $("#edit_delivery_telephone").val("");
                $("#edit_delivery_telephone").val($("#edit_billing_telephone").val());

                $("#edit_delivery_mobile").val("");
                $("#edit_delivery_mobile").val($("#edit_billing_mobile").val());

                $("#edit_delivery_fax").val("");
                $("#edit_delivery_fax").val($("#edit_billing_fax").val());
                
                $("#edit_delivery_vat_id").val("");
                $("#edit_delivery_vat_id").val($("#edit_billing_vat_id").val());
                
                $("#edit_delivery_coc_id").val("");
                $("#edit_delivery_coc_id").val($("#edit_billing_coc_id").val());
            });
            $("#close_edit_billing_info").click(function(e) {
                e.preventDefault();
                $("#edit_billing_zip").val($("#edit_billing_zip").val().toUpperCase());
                updateCustomerOrderDetails("billing_details", $("[id^=edit_billing]").serialize() + "&tx_multishop_pi1[billing_gender]=" + $(".account-gender-radio:checked").val());
            });
            $(document).on("click", "#edit_delivery_info", function(e) {
                e.preventDefault();
                $("#delivery_details_container").hide();
                $("#edit_delivery_details_container").show();
            });
            $("#close_edit_delivery_info").click(function(e) {
                e.preventDefault();
                $("#edit_delivery_zip").val($("#edit_delivery_zip").val().toUpperCase());
                updateCustomerOrderDetails("delivery_details", $("[id^=edit_delivery]").serialize() + "&tx_multishop_pi1[delivery_gender]=" + $(".account-delivery-gender-radio:checked").val());
            });
        });
        </script>';
            $GLOBALS['TSFE']->additionalHeaderData[] = $headerData;
            $headerData = '';
            $tmpcontent .= '
        </div>
        </div>';
            $editOrderFormFieldset[] = $tmpcontent;
            $tmpcontent = '';
            $orderDetails = array();
            $orderDetails[] = '
            	<div class="form-group">
					<label class="control-label col-md-3">' . $this->pi_getLL('orders_id') . '</label>
					<div class="col-md-9">
						<div class="row">
							<div class="col-md-4">
								<div class="row">
									<div class="col-md-12"><p class="form-control-static">' . $orders['orders_id'] . '</p></div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row">
                                    <label class="control-label col-md-7">' . $this->pi_getLL('admin_customer_id') . '</label>
                                    <div class="col-md-5"><p class="form-control-static">' . $orders['customer_id'] . '</p></div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row">
                                    <label class="control-label col-md-7">' . $this->pi_getLL('order_date') . '</label>
                                    <div class="col-md-5"><p class="form-control-static">' . $order_date . '</p></div>
								</div>
							</div>
						</div>
					</div>
				</div>
            ';
            $admin_lg_iso_2 = strtolower($this->languages[$this->sys_language_uid]['lg_iso_2']);
            $lg_iso_2 = strtolower($this->languages[$order['language_id']]['lg_iso_2']);
            $language_used = ucfirst($this->languages[$order['language_id']]['title']);
            $invoice_label = '';
            $invoice_number = '';
            $invoice_dl_lang_params = '';
            if ($lg_iso_2 != 'nl') {
                $invoice_dl_lang_params = '&lang=' . $lg_iso_2;
            }
            if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
                $filter = array();
                $filter[] = 'orders_id=' . $orders['orders_id'];
                //$filter[]='deleted=0';
                $invoices = mslib_befe::getRecords('', 'tx_multishop_invoices', '', $filter, '', 'id desc');
                $invoiceArray = array();
                if (count($invoices)) {
                    foreach ($invoices as $invoice) {
                        $link = mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]=' . $invoice['hash'] . $invoice_dl_lang_params);
                        $invoiceArray[$invoice['invoice_id']] = '<a href="' . $link . '" target="_blank" rel="nofollow"><i class="fa fa-file-pdf-o"></i></a> <a href="' . $link . '" target="_blank" rel="nofollow">' . $invoice['invoice_id'] . '</a>';
                        // hook for adding new items to details fieldset
                        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['invoiceArrayDetailsIterator'])) {
                            // hook
                            $params = array(
                                    'order' => $order,
                                    'invoice' => &$invoice,
                                    'invoiceArray' => &$invoiceArray
                            );
                            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['invoiceArrayDetailsIterator'] as $funcRef) {
                                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                            }
                        }
                    }
                }
                if (count($invoiceArray)) {
                    $invoice_label = $this->pi_getLL('admin_invoice_number');
                    $invoice_number = implode(', ', $invoiceArray);
                }
            }
            $orderDetails[] = '
            	<div class="form-group">
					<label class="control-label col-md-3">' . $invoice_label . '</label>
					<div class="col-md-9">
						<div class="row">
							<div class="col-md-4">
								<div class="row">
									<div class="col-md-12">
									    <p class="form-control-static">' . $invoice_number . '</p>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row">
                                    <label class="control-label col-md-7">' . $this->pi_getLL('language') . '</label>
                                    <div class="col-md-5">
                                        <p class="form-control-static">' . $language_used . '</p>
                                    </div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row">
								    <label class="control-label col-md-7"></label>
								</div>
							</div>
						</div>
					</div>
				</div>
            ';
            $paid_status = '';
            if (!$order['paid']) {
                $paid_status .= '<span class="admin_status_red" alt="' . $this->pi_getLL('has_not_been_paid') . '" title="' . $this->pi_getLL('has_not_been_paid') . '"></span> ';
                $paid_status .= '<span class="admin_status_green disabled" alt="' . $this->pi_getLL('change_to_paid') . '" title="' . $this->pi_getLL('change_to_paid') . '"></span>';
            } else {
                $paid_status .= '<span class="admin_status_red disabled" alt="' . $this->pi_getLL('change_to_not_paid') . '" title="' . $this->pi_getLL('change_to_not_paid') . '"></span> ';
                $paid_status .= '<span class="admin_status_green" alt="' . $this->pi_getLL('has_been_paid') . '" title="' . $this->pi_getLL('has_been_paid') . '"></span>';
            }
            $orderDetails[] = '
            <div class="form-group">
                <label class="control-label col-md-3">' . $this->pi_getLL('admin_paid') . '</label>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12"><p class="form-control-static">' . $paid_status . '</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ';
            $orderDetails[] = '<hr>';
            $orderDetailsItem = '<div class="form-group msAdminEditOrderShippingMethod" id="msAdminEditOrderShippingMethod">';
            $orderDetailsItem .= '<label class="control-label col-md-3">' . $this->pi_getLL('shipping_method') . '</label>';
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                $shipping_methods = mslib_fe::loadShippingMethods(1);
                $payment_methods = mslib_fe::loadPaymentMethods(1);
                // sort shipping method
                $shipping_methods_sorted = array();
                foreach ($shipping_methods as $code => $item) {
                    $shipping_methods_sorted[strtoupper($item['name'])] = $item;
                }
                ksort($shipping_methods_sorted);
                // sort payment method
                $payment_methods_sorted = array();
                foreach ($payment_methods as $code => $item) {
                    $payment_methods_sorted[strtoupper($item['name'])] = $item;
                }
                ksort($payment_methods_sorted);
                if (is_array($shipping_methods) and count($shipping_methods)) {
                    $optionItems = array();
                    $dontOverrideDefaultOption = 0;
                    foreach ($shipping_methods_sorted as $idx => $item) {
                        if (!$item['status']) {
                            $item['name'] .= ' (' . $this->pi_getLL('hidden_in_checkout') . ')';
                        }
                        $pageTitle = mslib_fe::getShopNameByPageUid($item['page_uid'], 'All');
                        $shop_title = '';
                        if (!empty($pageTitle)) {
                            $shop_title = ' (' . $pageTitle . ')';
                        }
                        $optionItems[] = '<option value="' . $item['id'] . '"' . ($item['code'] == $orders['shipping_method'] ? ' selected' : '') . '>' . htmlspecialchars($item['name'] . $shop_title) . '</option>';
                        if ($item['code'] == $orders['shipping_method']) {
                            $dontOverrideDefaultOption = 1;
                        }
                    }
                    if (empty($orders['shipping_method'])) {
                        $dontOverrideDefaultOption = 1;
                    }
                    if ($dontOverrideDefaultOption) {
                        $optionItems = array_merge(array('<option value="">' . ucfirst($this->pi_getLL('choose')) . '</option>'), $optionItems);
                    } else {
                        $optionItems = array_merge(array('<option value="">' . ($orders['shipping_method_label'] ? $orders['shipping_method_label'] : $orders['shipping_method']) . '</option>'), $optionItems);
                    }
                    $orderDetailsItem .= '<div class="col-md-9"><select name="shipping_method" id="shipping_method_sb" class="form-control">' . implode("\n", $optionItems) . '</select></div>';
                } else {
                    $orderDetailsItem .= '<div class="col-md-9"><p class="form-control-static">' . ($orders['shipping_method_label'] ? $orders['shipping_method_label'] : $orders['shipping_method']) . '</p></div>';
                }
            } else {
                $orderDetailsItem .= '<div class="col-md-9"><p class="form-control-static">' . ($orders['shipping_method_label'] ? $orders['shipping_method_label'] : $orders['shipping_method']) . '</p></div>';
            }
            $orderDetailsItem .= '</div>';
            $orderDetails[] = $orderDetailsItem;
            $orderDetailsItem = '';
            $orderDetailsItem = '<div class="form-group msAdminEditOrderPaymentMethod" id="msAdminEditOrderPaymentMethod">';
            $orderDetailsItem .= '<label class="control-label col-md-3">' . $this->pi_getLL('payment_method') . '</label>';
            $payment_method_data = mslib_fe::loadPaymentMethod($orders['payment_method']);
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details'] && (empty($orders['payment_method']) || (is_array($payment_method_data) && count($payment_method_data)))) {
                if (is_array($payment_methods) and count($payment_methods)) {
                    $optionItems = array();
                    $dontOverrideDefaultOption = 0;
                    foreach ($payment_methods_sorted as $idx => $item) {
                        if (is_numeric($item['id']) && $item['id'] > 0) {
                            if (!$item['status']) {
                                $item['name'] .= ' (' . $this->pi_getLL('hidden_in_checkout') . ')';
                            }
                            $pageTitle = mslib_fe::getShopNameByPageUid($item['page_uid'], 'All');
                            $shop_title = '';
                            if (!empty($pageTitle)) {
                                $shop_title = ' (' . $pageTitle . ')';
                            }
                            $optionItems[] = '<option value="' . $item['id'] . '"' . (($orders['payment_method'] && $item['code'] == $orders['payment_method']) || (!$orders['payment_method'] && $this->ms['MODULES']['DEFAULT_PAYMENT_METHOD_CODE'] && $this->ms['MODULES']['DEFAULT_PAYMENT_METHOD_CODE'] == $code) ? ' selected' : '') . '>' . htmlspecialchars($item['name'] . $shop_title) . '</option>';
                            if ($item['code'] == $orders['payment_method']) {
                                $dontOverrideDefaultOption = 1;
                            }
                        }
                    }
                    if (empty($orders['payment_method']) || (!empty($orders['payment_method']) && !isset($payment_methods[$orders['payment_method']]))) {
                        $dontOverrideDefaultOption = 1;
                    }
                    if ($dontOverrideDefaultOption) {
                        $optionItems = array_merge(array('<option value="">' . ucfirst($this->pi_getLL('choose')) . '</option>'), $optionItems);
                    } else {
                        $optionItems = array_merge(array('<option value="">' . ($orders['payment_method_label'] ? $orders['payment_method_label'] : $orders['payment_method']) . '</option>'), $optionItems);
                    }
                    $orderDetailsItem .= '<div class="col-md-9"><select name="payment_method" id="payment_method_sb" class="form-control">' . implode("\n", $optionItems) . '</select></div>';
                } else {
                    $orderDetailsItem .= '<div class="col-md-9"><p class="form-control-static">' . ($orders['payment_method_label'] ? $orders['payment_method_label'] : $orders['payment_method']) . '</p></div>';
                }
            } else {
                $payment_method_id = 0;
                $payment_methods = mslib_fe::loadPaymentMethods(1);
                foreach ($payment_methods as $code => $item) {
                    if ($code == $orders['payment_method']) {
                        $payment_method_id = $item['id'];
                    }
                }
                if (!$payment_method_id) {
                    $payment_method_data = mslib_fe::getPaymentMethod($orders['payment_method'], 'p.code');
                    $payment_method_id = $payment_method_data['id'];
                }
                $orderDetailsItem .= '<div class="col-md-9"><p class="form-control-static">' . ($orders['payment_method_label'] ? $orders['payment_method_label'] : $orders['payment_method']) . '<input type="hidden" name="payment_method" value="' . $payment_method_id . '"></p></div>';
            }
            $orderDetailsItem .= '</div>';
            // Date order paid
            $orderDetailsItem .= '<div class="form-group msAdminEditOrderPaymentMethod" id="msAdminEditOrderDatePaid">';
            $orderDetailsItem .= '<label class="control-label col-md-3">' . $this->pi_getLL('date_paid', 'Date paid') . '</label>';
            $orders_paid_timestamp_visual = '';
            $orders_paid_timestamp = '';
            if ($orders['paid']) {
                if ($orders['orders_paid_timestamp']) {
                    $this->post['tx_multishop_pi1']['orders_paid_timestamp'] = $orders['orders_paid_timestamp'];
                }
                if ($this->post['tx_multishop_pi1']['orders_paid_timestamp'] == 0 || empty($this->post['tx_multishop_pi1']['orders_paid_timestamp'])) {
                    $orders_paid_timestamp_visual = '';
                    $orders_paid_timestamp = '';
                } else {
                    $orders_paid_timestamp_visual = date($this->pi_getLL('locale_date_format'), $orders['orders_paid_timestamp']);
                    $orders_paid_timestamp = date("Y-m-d", $orders['orders_paid_timestamp']);
                }
            }
            $orderDetailsItem .= '<div class="col-md-9">
			<input type="text" name="tx_multishop_pi1[orders_paid_timestamp_visual]" class="form-control" id="orders_paid_timestamp_visual" value="' . htmlspecialchars($orders_paid_timestamp_visual) . '" autocomplete="off">
			<input type="hidden" name="tx_multishop_pi1[orders_paid_timestamp]" id="orders_paid_timestamp" value="' . htmlspecialchars($orders_paid_timestamp) . '">
			</div>';
            $GLOBALS['TSFE']->additionalHeaderData[] = '
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$("#orders_paid_timestamp_visual").datepicker({
					dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
					altField: "#orders_paid_timestamp",
					altFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true,
					showOtherMonths: true,
					yearRange: "' . (date("Y") - 15) . ':' . (date("Y") + 2) . '"
				});
			});
			</script>
			';
            $orderDetailsItem .= '</div>';
            $orderDetails[] = $orderDetailsItem;
            if ($this->ms['MODULES']['ENABLE_EDIT_ORDER_PAYMENT_CONDITION_FIELD'] && $this->ms['MODULES']['ORDER_EDIT']) {
                if (!$orders['is_locked'] || ($orders['is_locked'] && $orders['payment_condition'] != '')) {
                    $orderDetailsItem = '';
                    $orderDetailsItem = '<div class="form-group msAdminEditOrderPaymentConditions" id="msAdminEditOrderPaymentConditions">';
                    $orderDetailsItem .= '<label class="control-label col-md-3">' . $this->pi_getLL('payment_condition') . '</label>';
                    if (!$orders['is_locked']) {
                        $orderDetailsItem .= '<div class="col-md-9"><div class="input-group width-fw"><input class="form-control" type="text" name="order_payment_condition" id="order_payment_condition" value="' . mslib_fe::RemoveXSS($orders['payment_condition']) . '" /><span class="input-group-addon">' . $this->pi_getLL('days') . '</span></div></div>';
                    } else {
                        $orderDetailsItem .= '<div class="col-md-9"><p class="form-control-static">' . mslib_fe::RemoveXSS($orders['payment_condition'] . ' ' . $this->pi_getLL('days')) . '</p></div>';
                    }
                    $orderDetailsItem .= '</div>';
                    $orderDetails[] = $orderDetailsItem;
                }
            }
            $orderDetailsItem = '';
            if ($orders['customer_comments']) {
                $orderDetailsItem = '<hr><div class="form-group" id="customer_comments"><label class="control-label col-md-3">' . htmlspecialchars($this->pi_getLL('customer_comments')) . '</label>
                    <div class="col-md-9"><div class="customer_comments_body"><div class="form-control-static">' . nl2br($orders['customer_comments']) . '</div></div></div>
                </div>';
                $orderDetails['customer_comments'] = $orderDetailsItem;
            }
            $extraDetails = array();
            if ($order['cruser_id']) {
                $user = mslib_fe::getUser($order['cruser_id']);
                if ($user['username']) {
                    $customer_edit_link = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]=' . $user['uid'] . '&action=edit_customer');
                    $extraDetails[$this->pi_getLL('ordered_by')] = '<a href="' . $customer_edit_link . '">' . $user['username'] . '</a></strong>';
                }
            }
            if ($order['ip_address']) {
                $extraDetails[$this->pi_getLL('ip_address', 'IP address')] = $order['ip_address'];
            }
            if ($order['http_host']) {
                $extraDetails[$this->pi_getLL('order_on', 'Besteld op')] = $order['http_host'];
            }
            if ($order['http_referer']) {
                $domain = parse_url($order['http_referer']);
                if ($domain['host']) {
                    $extraDetails[$this->pi_getLL('referrer', 'Referrer')] = '<a href="' . $order['http_referer'] . '" target="_blank" rel="noreferrer">' . $domain['host'] . '</a>';
                }
            }
            // hook for adding new items to details fieldset
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsFieldset'])) {
                // hook
                $params = array(
                        'orderDetails' => &$orderDetails,
                        'extraDetails' => &$extraDetails,
                        'orders' => &$orders,
                        'order' => &$order
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsFieldset'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
                // hook oef
            }
            $extraDetailsData = array();
            if (count($extraDetails)) {
                $ed_counter = 0;
                $main_label = '';
                foreach ($extraDetails as $ed_label => $ed_value) {
                    /*
                    if ($ed_counter=='0') {
                        $main_label=$ed_label;
                        $extraDetailsData[]='<div class="col-md-2">
                            <div class="row">
                                <div class="col-md-12"><p class="form-control-static">' . $ed_value . '</p></div>
                            </div>
                        </div>';
                    } else {
                    */
                    $extraDetailsData[] = '<div class="col-md-4">
                            <label class="control-label">' . $ed_label . '</label>
                            <p class="form-control-static">' . $ed_value . '</p>
                        </div>';
                    //}
                    $ed_counter++;
                }
                $orderDetails[] = '
                    <hr/>
                    <div class="form-group edit-order-info">
                        ' . implode('', $extraDetailsData) . '
                    </div>
                ';
            }
            $tmpcontent .= '
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default" id="order_properties">
                        <div class="panel-heading"><h3>Details</h3></div>
                        <div class="panel-body">';
            $tmpcontent .= implode("", $orderDetails);
            $tmpcontent .= '
                        </div>
                    </div>
                </div>
            </div>
    ';
            // order products
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                $js_select2_cache = '';
                $js_select2_cache_products = array();
                $js_select2_cache_options = array();
                $js_select2_cache_values = array();
                $js_select2_cache = '
                <script type="text/javascript">
                    var productsSearch=[];
                    var attributesSearchOptions=[];
                    var attributesSearchValues=[];
                    var Products=[];
                    var attributesOptions=[];
                    var attributesValues=[];' . "\n";
            }
            $tmpcontent .= '
            <div class="panel panel-default" id="product_details">
                <div class="panel-heading"><h3>' . $this->pi_getLL('product_details') . '</h3></div>
                <div class="panel-body">';
            // initiate the array for holding rows data
            $order_products_table = array();
            $order_products_header_data = array();
            $tr_type = 'even';
            $tmpcontent .= '<table class="table table-striped table-bordered msadmin_border orders_products_listing" id="orders_products_listing_table_wrapper">';
            if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                $all_orders_status = mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
            }
            // order products header definition
            // products id header col
            $order_products_header_data['products_id']['class'] = 'cellID cellNoWrap';
            $order_products_header_data['products_id']['value'] = $this->pi_getLL('products_id');
            // products qty header col
            $order_products_header_data['products_qty']['class'] = 'cellQty';
            $order_products_header_data['products_qty']['value'] = $this->pi_getLL('qty');
            if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                // products qty delivered header col
                $order_products_header_data['products_qty_delivered']['class'] = 'cellQty';
                $order_products_header_data['products_qty_delivered']['value'] = $this->pi_getLL('order_product_qty_delivered');
            }
            // products name header col
            $order_products_header_data['products_name']['class'] = 'cellName';
            $order_products_header_data['products_name']['value'] = $this->pi_getLL('products_name');
            // products order status header col
            if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                $order_products_header_data['products_order_status']['class'] = 'cellStatus';
                $order_products_header_data['products_order_status']['value'] = $this->pi_getLL('order_status');
            }
            if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                $order_products_header_data['products_order_customer_comments']['class'] = 'cellComments';
                $order_products_header_data['products_order_customer_comments']['value'] = $this->pi_getLL('customer_comments');
            }
            // products vat header col
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                $order_products_header_data['products_vat']['class'] = 'cellVat';
                $order_products_header_data['products_vat']['value'] = $this->pi_getLL('vat');
            }
            // products capital price header col
            if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                $order_products_header_data['products_capital_price']['class'] = 'cellPrice cellNoWrap';
                $order_products_header_data['products_capital_price']['value'] = $this->pi_getLL('capital_price');
            }
            // products normal price header col
            $order_products_header_data['products_normal_price']['class'] = 'cellPrice cellNoWrap';
            $order_products_header_data['products_normal_price']['value'] = $this->pi_getLL('normal_price');
            // products vat header col
            if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                $order_products_header_data['products_vat']['class'] = 'cellVat';
                $order_products_header_data['products_vat']['value'] = $this->pi_getLL('vat');
            }
            if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                $order_products_header_data['products_discount']['class'] = 'cellDiscount';
                $order_products_header_data['products_discount']['value'] = $this->pi_getLL('discount');
            }
            // products price total header col
            //cellFinalPrice
            $order_products_header_data['products_final_price']['class'] = 'cellPrice cellNoWrap';
            $order_products_header_data['products_final_price']['value'] = $this->pi_getLL('final_price_ex_vat');
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                $order_products_header_data['products_final_price']['value'] = $this->pi_getLL('final_price_inc_vat');
            }
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                $order_products_header_data['products_action']['class'] = 'cellAction';
                $order_products_header_data['products_action']['value'] = '&nbsp;';
            }
            // custom hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderProductsTableHeader'])) {
                $params = array(
                        'orders' => &$orders,
                        'order_products_header_data' => &$order_products_header_data
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderProductsTableHeader'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom hook that can be controlled by third-party plugin eof
            $order_products_table['header']['head']['class'] = '';
            $order_products_table['header']['head']['value'] = $order_products_header_data;
            // order products header definition eol
            $total_tax = 0;
            if (is_array($orders_products) and count($orders_products)) {
                foreach ($orders_products as $order) {
                    if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                        if ($order['products_price'] != $order['final_price'] && !empty($order['discount_amount'])) {
                            $order['final_price'] += $order['discount_amount'];
                        }
                    }
                    if ($order['products_id'] > 0) {
                        $js_select2_cache_products[$order['products_id']] = 'Products[' . $order['products_id'] . ']={id:"' . $order['products_id'] . '", text:"' . $order['products_name'] . '"}';
                    } else {
                        $js_select2_cache_products[$order['products_name']] = 'Products[\'' . $order['products_name'] . '\']={id:"' . $order['products_name'] . '", text:"' . $order['products_name'] . '"}';
                    }
                    $order_products_tax_data = unserialize($order['products_tax_data']);
                    if (!$tr_type or $tr_type == 'even') {
                        $tr_type = 'odd';
                    } else {
                        $tr_type = 'even';
                    }
                    $tbody_tag_class = 'sortbody';
                    $tbody_tag_id = 'orders_products_id_' . $order['orders_products_id'];
                    $order_products_table['body'][$tbody_tag_id]['tbody_class'] = $tbody_tag_class;
                    $order_products_body_data = array();
                    if (($this->ms['MODULES']['ORDER_EDIT'] and !$orders['is_locked']) and ($this->get['edit_product'] == 1 && $this->get['order_pid'] == $order['orders_products_id'])) {
                        $customer_country = mslib_fe::getCountryByName($orders['billing_country']);
                        $current_product_tax = ($order['products_tax'] / 100);
                        $sql_tax_sb = $GLOBALS['TYPO3_DB']->SELECTquery('t.tax_id, t.rate, t.name', // SELECT ...
                                'tx_multishop_taxes t, tx_multishop_tax_rules tr, tx_multishop_tax_rule_groups trg', // FROM ...
                                't.tax_id=tr.tax_id and tr.rules_group_id=trg.rules_group_id and trg.status=1 and tr.cn_iso_nr=\'' . $customer_country['cn_iso_nr'] . '\'', // WHERE...
                                'trg.rules_group_id', // GROUP BY...
                                '', // ORDER BY...
                                '' // LIMIT ...
                        );
                        $qry_tax_sb = $GLOBALS['TYPO3_DB']->sql_query($sql_tax_sb);
                        $vat_sb = '<select name="product_tax" id="product_tax" class="form-control" style="width:auto;">';
                        $vat_sb .= '<option value="">' . $this->pi_getLL('admin_label_no_tax') . '</option>';
                        while ($rs_tx_sb = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tax_sb)) {
                            $sb_tax_rate = ($rs_tx_sb['rate'] / 100);
                            if ($current_product_tax == $sb_tax_rate) {
                                $vat_sb .= '<option value="' . $rs_tx_sb['tax_id'] . '" selected="selected">' . $rs_tx_sb['name'] . '</option>';
                            } else {
                                $vat_sb .= '<option value="' . $rs_tx_sb['tax_id'] . '">' . $rs_tx_sb['name'] . '</option>';
                            }
                        }
                        $vat_sb .= '</select>';
                        // cols
                        // products id col
                        $order_products_body_data['products_id']['align'] = 'right';
                        $order_products_body_data['products_id']['class'] = 'cellID cellNoWrap';
                        $order_products_body_data['products_id']['id'] = 'edit_order_product_id';
                        $order_products_body_data['products_id']['value'] = $order['products_id'];
                        // products qty col
                        $order_products_body_data['products_qty']['align'] = 'right';
                        $order_products_body_data['products_qty']['class'] = 'cellQty';
                        $order_products_body_data['products_qty']['value'] = '<input type="hidden" name="product_name" id="product_name" value="' . htmlspecialchars($order['products_name']) . '">';
                        $order_products_body_data['products_qty']['value'] .= '<input type="hidden" name="orders_products_id" value="' . $order['orders_products_id'] . '">';
                        $quantity_html = '<div class="quantity buttons_added">';
                        $quantity_html .= '<input type="button" value="-" data-stepSize="1" data-minQty="1" data-maxQty="0" class="qty_minus" rel="product_qty">';
                        $quantity_html .= '<input class="form-control text" style="width:70px" type="text" id="product_qty" name="product_qty" value="' . round($order['qty'], 13) . '" />';
                        $quantity_html .= '<input type="button" value="+" data-stepSize="1" data-minQty="1" data-maxQty="0" class="qty_plus" rel="product_qty">';
                        $quantity_html .= '</div>';
                        $order_products_body_data['products_qty']['value'] .= $quantity_html;
                        if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                            // products qty delivered col
                            $order_products_body_data['products_qty_delivered']['align'] = 'right';
                            $order_products_body_data['products_qty_delivered']['class'] = 'cellQty';
                            $quantity_html = '<div class="quantity buttons_added">';
                            if ($order['qty_delivered'] == $order['qty']) {
                                $quantity_html .= '<input class="form-control text" style="width:70px" type="text" id="product_qty_delivered" name="product_qty_delivered" value="' . round($order['qty_delivered'], 13) . '" readonly="readonly" />';
                            } else {
                                $quantity_html .= '<input type="button" value="-" data-stepSize="1" data-minQty="1" data-maxQty="0" class="qty_minus" rel="product_qty_delivered">';
                                $quantity_html .= '<input class="form-control text" style="width:70px" type="text" id="product_qty_delivered" name="product_qty_delivered" value="' . round($order['qty_delivered'], 13) . '" />';
                                $quantity_html .= '<input type="button" value="+" data-stepSize="1" data-minQty="1" data-maxQty="' . $order['qty'] . '" class="qty_plus" rel="product_qty_delivered">';
                            }
                            $quantity_html .= '</div>';
                            $order_products_body_data['products_qty_delivered']['value'] .= $quantity_html;
                        }
                        // products name col
                        $order_products_body_data['products_name']['align'] = 'left';
                        $order_products_body_data['products_name']['class'] = 'cellName';
                        if ($order['products_id'] > 0) {
                            $order_products_body_data['products_name']['value'] = '<div class="categories_products_select2_wrapper select2-container">
								<div class="categories_select2_input">
									<input class="categories_name_input" type="hidden" name="categories_filter_id" id="categories_filter_id" value="' . $order['categories_id'] . '" style="width:380px" />
								</div>
								<div class="products_select2_input">
									<input class="product_name_input" type="hidden" name="products_id" value="' . $order['products_id'] . '" style="width:380px" />
								</div>
							</div>';
                        } else {
                            $order_products_body_data['products_name']['value'] = '<div class="categories_products_select2_wrapper select2-container">
								<div class="categories_select2_input">
									<input class="categories_name_input" type="hidden" name="categories_filter_id" id="categories_filter_id" value="' . $order['categories_id'] . '" style="width:380px" />
								</div>
								<div class="products_select2_input">
									<input class="product_name_input" type="hidden" name="products_id" value="' . $order['products_name'] . '" style="width:402px" />
								</div>
							</div>';
                        }
                        if ($this->ms['MODULES']['ENABLE_MANUAL_ORDER_CUSTOM_ORDER_PRODUCTS_NAME']) {
                            if ($order['products_id'] > 0) {
                                $original_pn = mslib_fe::getProductName($order['products_id']);
                                $custom_product_name = '';
                                if ($original_pn != $order['products_name']) {
                                    $custom_product_name = $order['products_name'];
                                }
                                $order_products_body_data['products_name']['value'] .= '<div id="custom_manual_product_name_wrapper" class="mt-10"><label for="custom_manual_product_name">' . htmlspecialchars($this->pi_getLL('admin_current_custom_product_name')) . ':</label><input type="text" id="custom_manual_product_name" class="form-control" name="custom_manual_product_name" value="' . htmlspecialchars($custom_product_name) . '" style="width:402px;" /></div>';
                            } else {
                                $order_products_body_data['products_name']['value'] .= '<div id="custom_manual_product_name_wrapper" class="mt-10" style="display:none"><label for="custom_manual_product_name">' . htmlspecialchars($this->pi_getLL('admin_custom_product_name')) . ':</label><input type="text" id="custom_manual_product_name" class="form-control" name="custom_manual_product_name" value="" disabled="disabled" style="width:402px;" /></div>';
                            }
                        }
                        if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                            // products status col
                            $order_products_body_data['products_status']['align'] = 'center';
                            $order_products_body_data['products_status']['class'] = 'cellStatus';
                            $order_products_body_data['products_status']['value'] = '<select name="order_product_status" class="width-auto form-control change_order_product_status" rel="' . $order['orders_products_id'] . '" id="orders_' . $order['orders_products_id'] . '">
                            <option value="">' . $this->pi_getLL('choose') . '</option>';
                            if (is_array($all_orders_status)) {
                                foreach ($all_orders_status as $item) {
                                    $order_products_body_data['products_status']['value'] .= '<option value="' . $item['id'] . '"' . ($item['id'] == $order['status'] ? ' selected' : '') . '>' . $item['name'] . '</option>' . "\n";
                                }
                            }
                            $order_products_body_data['products_status']['value'] .= '</select>';
                        }
                        if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                            // products status col
                            $order_products_body_data['products_customer_comments']['align'] = 'left';
                            $order_products_body_data['products_customer_comments']['class'] = 'cellComments';
                            $order_products_body_data['products_customer_comments']['value'] = '<input type="text" name="product_customer_comments" value="' . $order['customer_comments'] . '" />';
                        }
                        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            // products vat col
                            $order_products_body_data['products_vat']['align'] = 'right';
                            $order_products_body_data['products_vat']['class'] = 'cellVat';
                            $order_products_body_data['products_vat']['value'] = $vat_sb;
                        }
                        // capital price
                        if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                            // products price col
                            $order_products_body_data['products_capital_price']['class'] = 'cellPrice cellNoWrap';
                            $order_products_body_data['products_capital_price']['id'] = 'edit_order_product_price';
                            // incl excl vat input
                            $order_products_capital_price_display = mslib_fe::taxDecimalCrop($order['product_capital_price'], 2, false);
                            $order_products_capital_price_display_incl = mslib_fe::taxDecimalCrop($order['product_capital_price'] + ($order['product_capital_price'] * $order_products_tax_data['total_tax_rate']), 2, false);
                            $order_products_body_data['products_capital_price']['value'] = '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="display_name_cp_excluding_vat" name="display_name_excluding_vat" class="form-control msOrderProductPriceExcludingVat priceInputDisplay" value="' . $order_products_capital_price_display . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>';
                            $order_products_body_data['products_capital_price']['value'] .= '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="display_name" id="display_name_cp_including_vat" class="form-control msOrderProductPriceIncludingVat priceInputDisplay" value="' . ($order_products_capital_price_display_incl) . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>';
                            $order_products_body_data['products_capital_price']['value'] .= '<div class="msAttributesField hidden"><input class="priceInputReal text" type="hidden" name="product_capital_price" id="product_capital_price" value="' . $order['product_capital_price'] . '" /></div>';
                        }
                        // products price col
                        $order_products_body_data['products_normal_price']['class'] = 'cellPrice cellNoWrap';
                        $order_products_body_data['products_normal_price']['id'] = 'edit_order_product_price';
                        // incl excl vat input
                        $order_products_price_display = mslib_fe::taxDecimalCrop($order['final_price'], 2, false);
                        $order_products_price_display_incl = mslib_fe::taxDecimalCrop($order['final_price'] + $order_products_tax_data['total_tax'], 2, false);
                        if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                            if (!empty($order['discount_amount'])) {
                                $order_products_price_display_incl = mslib_fe::taxDecimalCrop($order['final_price'] + (($order['final_price'] * $order['products_tax']) / 100), 2, false);
                            }
                        }
                        $order_products_body_data['products_normal_price']['value'] = '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="display_name_excluding_vat" name="display_name_excluding_vat" class="form-control msOrderProductPriceExcludingVat priceInputDisplay" value="' . $order_products_price_display . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>';
                        $order_products_body_data['products_normal_price']['value'] .= '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="display_name" id="display_name_including_vat" class="form-control msOrderProductPriceIncludingVat priceInputDisplay" value="' . ($order_products_price_display_incl) . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>';
                        $order_products_body_data['products_normal_price']['value'] .= '<div class="msAttributesField hidden"><input class="priceInputReal text" type="hidden" name="product_price" id="product_price" value="' . $order['final_price'] . '" /></div>';
                        /*if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
							//$order_products_body_data['products_normal_price']['value']='<input class="text" style="width:44px" type="text" id="display_product_price" value="'.($order['final_price']+$order_products_tax_data['total_tax']).'" />
							//<input type="hidden" name="products_normal_price" id="product_price" value="'.($order['final_price']).'" />';
							$order_products_body_data['products_normal_price']['value']='<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name_excluding_vat" class="msStaffelPriceExcludingVat" value="'.$order_products_price_display.'"><label for="display_name_excluding_vat">'.$this->pi_getLL('excluding_vat').'</label></div>';
							$order_products_body_data['products_normal_price']['value'].='<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name_including_vat" class="msStaffelPriceIncludingVat" value="'.($order_products_price_display_incl).'"><label for="display_name_including_vat">'.$this->pi_getLL('including_vat').'</label></div>';
							$order_products_body_data['products_normal_price']['value'].='<div class="msAttributesField hidden"><input class="text" style="width:44px" type="hidden" name="product_price" id="product_price" value="'.$order['final_price'].'" /></div>';
						} else {
							$order_products_body_data['products_normal_price']['value']='<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name_excluding_vat" class="msStaffelPriceExcludingVat" value="'.$order_products_price_display.'"><label for="display_name_excluding_vat">'.$this->pi_getLL('excluding_vat').'</label></div>';
							$order_products_body_data['products_normal_price']['value'].='<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name_including_vat" class="msStaffelPriceIncludingVat" value="'.($order_products_price_display_incl).'"><label for="display_name_including_vat">'.$this->pi_getLL('including_vat').'</label></div>';
							$order_products_body_data['products_normal_price']['value'].='<div class="msAttributesField hidden"><input class="text" style="width:44px" type="hidden" name="product_price" id="product_price" value="'.$order['final_price'].'" /></div>';
						}*/
                        if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            // products vat col
                            $order_products_body_data['products_vat']['align'] = 'right';
                            $order_products_body_data['products_vat']['class'] = 'cellVat';
                            $order_products_body_data['products_vat']['value'] = $vat_sb;
                        }
                        if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                            $order_products_body_data['products_discount']['class'] = 'cellPrice cellNoWrap';
                            //$order_products_body_data['products_discount']['value']=$this->pi_getLL('discount') . ' input';
                            $order_products_discount_amount_display = mslib_fe::taxDecimalCrop($order['discount_amount'], 2, false);
                            $order_products_discount_amount_display_incl = mslib_fe::taxDecimalCrop($order['discount_amount'] + (($order['discount_amount'] * $order['products_tax']) / 100), 2, false);
                            $percentage_sb = '<div class="discount_percentage_wrapper">
							<select name="product_discount_percentage" id="product_discount_percentage" style="width:210px">
								<option value="">' . $this->pi_getLL('use_discount_amount') . '</option>
							';
                            $selected_percentage = str_replace('.00', '', $order['discount_percentage']);
                            for ($p = 1; $p <= 100; $p++) {
                                if ($selected_percentage == $p) {
                                    $percentage_sb .= '<option value="' . $p . '" selected="selected">' . $p . '%</option>';
                                } else {
                                    $percentage_sb .= '<option value="' . $p . '">' . $p . '%</option>';
                                }
                            }
                            $percentage_sb .= '</select>
							</div>';
                            $order_products_body_data['products_discount']['value'] = $percentage_sb . '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="display_name_discount_excluding_vat" name="display_name_discount_excluding_vat" class="form-control msOrderProductPriceExcludingVat priceInputDisplay" value="' . $order_products_discount_amount_display . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>';
                            $order_products_body_data['products_discount']['value'] .= '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="display_name_discount" id="display_name_discount_including_vat" class="form-control msOrderProductPriceIncludingVat priceInputDisplay" value="' . $order_products_discount_amount_display_incl . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>';
                            $order_products_body_data['products_discount']['value'] .= '<div class="msAttributesField hidden"><input class="text priceInputReal" type="hidden" name="product_discount_amount" id="product_discount_amount" value="' . $order['discount_amount'] . '" /></div>';
                            //if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                            if (!empty($order['discount_amount'])) {
                                $order['final_price'] -= $order['discount_amount'];
                            }
                            //}
                        }
                        // product final price
                        $order_products_body_data['products_final_price']['align'] = 'right';
                        //cellFinalPrice
                        $order_products_body_data['products_final_price']['class'] = 'cellPrice cellNoWrap';
                        $order_products_body_data['products_final_price']['id'] = 'edit_order_product_final_price';
                        $order_products_body_data['products_final_price']['value'] = mslib_fe::amount2Cents($order['final_price'], 0);
                        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            $order_products_body_data['products_final_price']['value'] = mslib_fe::amount2Cents($order['qty'] * ($order['final_price'] + $order_products_tax_data['total_tax']), 0);
                        }
                    } else {
                        $row = array();
                        $where = '';
                        $product = mslib_fe::getProduct($order['products_id']);
                        if (!empty($order['ean_code'])) {
                            $product['ean_code'] = $order['ean_code'];
                        }
                        if (!empty($order['sku_code'])) {
                            $product['sku_code'] = $order['sku_code'];
                        }
                        if (!empty($order['vendor_code'])) {
                            $product['vendor_code'] = $order['vendor_code'];
                        }
                        if ($product['categories_id']) {
                            // get all cats to generate multilevel fake url
                            $level = 0;
                            $cats = mslib_fe::Crumbar($product['categories_id']);
                            $cats = array_reverse($cats);
                            $where = '';
                            if (count($cats) > 0) {
                                foreach ($cats as $cat) {
                                    $where .= "categories_id[" . $level . "]=" . $cat['id'] . "&";
                                    $level++;
                                }
                                $where = substr($where, 0, (strlen($where) - 1));
                                $where .= '&';
                            }
                            // get all cats to generate multilevel fake url eof
                        }
                        $row[0] = $order['products_id'];
                        $row[1] = number_format($order['qty'], 2, '.', '');
                        $row[2] = '';
                        if ($order['products_id'] > 0) {
                            if ($product['products_id']) {
                                // product still exists in database so lets add anchor link to products detail page
                                $row[2] .= '<a href="' . mslib_fe::typolink($this->conf['products_detail_page_pid'], '&' . $where . '&products_id=' . $order['products_id'] . '&tx_multishop_pi1[page_section]=products_detail') . '" target="_blank">';
                            }
                            if ($this->ms['MODULES']['DISPLAY_PRODUCT_IMAGE_IN_ADMIN_ORDER_DETAILS'] and $product['products_image']) {
                                $row[2] .= '<img src="' . mslib_befe::getImagePath($product['products_image'], 'products', '50') . '">';
                            }
                            $row[2] .= $order['products_name'];
                            if ($this->ms['MODULES']['DISPLAY_SKU_IN_ORDER_DETAILS'] == '1' && !empty($product['sku_code'])) {
                                $row[2] .= '<br />' . $this->pi_getLL('sku_number') . ': ' . $product['sku_code'];
                            }
                            if ($this->ms['MODULES']['DISPLAY_PRODUCTS_MODEL_IN_ORDER_DETAILS'] == '1' && !empty($order['products_model'])) {
                                $row[2] .= ' (' . $order['products_model'] . ')';
                            }
                            if ($this->ms['MODULES']['DISPLAY_EAN_IN_ORDER_DETAILS'] == '1' && !empty($product['ean_code'])) {
                                $row[2] .= '<br />EAN: ' . $product['ean_code'];
                            }
                            if ($this->ms['MODULES']['DISPLAY_VENDOR_IN_ORDER_DETAILS'] == '1' && !empty($product['vendor_code'])) {
                                $row[2] .= '<br />Vendor code: ' . $product['vendor_code'];
                            }
                            if ($product['products_id']) {
                                $row[2] .= '</a>';
                            }
                        } else {
                            $row[2] .= $order['products_name'];
                            if ($this->ms['MODULES']['DISPLAY_PRODUCTS_MODEL_IN_ORDER_DETAILS'] == '1' && !empty($order['products_model'])) {
                                $row[2] .= ' (' . $order['products_model'] . ')';
                            }
                        }
                        if (!empty($order['file_label']) && !empty($order['file_location']) && !empty($order['file_download_code'])) {
                            $label = 'Download ' . htmlspecialchars($order['file_label']);
                            $row[2] .= '<br/><a href="' . $this->FULL_HTTP_URL . mslib_fe::typolink(",2002", 'tx_multishop_pi1[page_section]=get_micro_download&tx_multishop_pi1[from_interface]=edit_order&orders_id=' . $order['orders_id'] . '&code=' . $order['file_download_code'], 1) . '" alt="' . $order['products_name'] . '" title="' . $order['products_name'] . '">' . $label . '</a>';
                        }
                        $row[3] = mslib_fe::amount2Cents($order['final_price'], 0);
                        $row[4] = number_format($order['products_tax'], 2);
                        if (!isset($order['products_tax'])) {
                            $order['products_tax'] = '0';
                        }
                        $row[4] = str_replace('.00', '', $order['products_tax']) . '%';
                        $row[5] = mslib_fe::amount2Cents($order['qty'] * $order['final_price'], 0);
                        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            $row[3] = mslib_fe::amount2Cents($order['final_price'] + $order_products_tax_data['total_tax'], 0);
                            $row[5] = mslib_fe::amount2Cents($order['qty'] * ($order['final_price'] + $order_products_tax_data['total_tax']), 0);
                            $row[6] = mslib_fe::amount2Cents($order['discount_amount'] + (($order['discount_amount'] * $order['products_tax']) / 100), 0);
                        }
                        if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                            $row[5] = mslib_fe::amount2Cents($order['qty'] * (($order['final_price'] - $order['discount_amount'])), 0);
                            $discount_amount = $order['discount_amount'];
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                $row[3] = mslib_fe::amount2Cents($order['final_price'] + (($order['final_price'] * $order['products_tax']) / 100), 0);
                                $row[5] = mslib_fe::amount2Cents($order['qty'] * (($order['final_price'] - $order['discount_amount']) + $order_products_tax_data['total_tax']), 0);
                                $discount_amount = $discount_amount + (($discount_amount * $order['products_tax']) / 100);
                            }
                            $discount_amount = intval($discount_amount) . '.' . substr(end(explode('.', $discount_amount)), 0, 2);
                            $row[6] = mslib_fe::amount2Cents($discount_amount, 0);
                        }
                        if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                            $row[7] = mslib_fe::amount2Cents($order['product_capital_price'], 0);
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                $row[7] = mslib_fe::amount2Cents($order['product_capital_price'] + ($order['product_capital_price'] * $order_products_tax_data['total_tax_rate']), 0);
                            }
                        }
                        if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                            $row[8] = $order['qty_delivered'];
                        }
                        // custom hook that can be controlled by third-party plugin
                        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderListItemPreHook'])) {
                            $params = array(
                                    'order' => &$order,
                                    'row' => &$row
                            );
                            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderListItemPreHook'] as $funcRef) {
                                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                            }
                        }
                        // custom hook that can be controlled by third-party plugin eof
                        // products id col
                        $order_products_body_data['products_id']['align'] = 'right';
                        $order_products_body_data['products_id']['class'] = 'cellID cellNoWrap';
                        if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                            if ($this->get['edit_product'] && $this->get['order_pid'] == $order['orders_products_id']) {
                                $order_products_body_data['products_id']['id'] = 'edit_order_product_id';
                            }
                        }
                        $order_products_body_data['products_id']['value'] = $row[0];
                        // products qty col
                        $order_products_body_data['products_qty']['align'] = 'right';
                        $order_products_body_data['products_qty']['class'] = 'cellQty';
                        $order_products_body_data['products_qty']['value'] = round($row[1], 13);
                        if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                            // products qty delivered col
                            $order_products_body_data['products_qty_delivered']['align'] = 'right';
                            $order_products_body_data['products_qty_delivered']['class'] = 'cellQty';
                            if ($row[8] > 0) {
                                $order_products_body_data['products_qty_delivered']['value'] = round($row[8], 13);
                            } else {
                                $order_products_body_data['products_qty_delivered']['value'] = '0';
                            }
                        }
                        // products name col
                        $order_products_body_data['products_name']['align'] = 'left';
                        $order_products_body_data['products_name']['class'] = 'cellName';
                        $order_products_body_data['products_name']['value'] = $row[2];
                        if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                            if ($this->ms['MODULES']['ORDER_EDIT'] > 0) {
                                // products status col
                                $order_products_body_data['products_status']['align'] = 'center';
                                $order_products_body_data['products_status']['class'] = 'cellStatus';
                                $order_products_body_data['products_status']['value'] = '<select name="order_product_status" class="width-auto form-control change_order_product_status" rel="' . $order['orders_products_id'] . '" id="orders_' . $order['orders_products_id'] . '">
                            <option value="">' . $this->pi_getLL('choose') . '</option>';
                                if (is_array($all_orders_status)) {
                                    foreach ($all_orders_status as $item) {
                                        $order_products_body_data['products_status']['value'] .= '<option value="' . $item['id'] . '"' . ($item['id'] == $order['status'] ? ' selected' : '') . '>' . $item['name'] . '</option>' . "\n";
                                    }
                                }
                                $order_products_body_data['products_status']['value'] .= '</select>';
                            } else {
                                if (is_array($all_orders_status)) {
                                    foreach ($all_orders_status as $item) {
                                        if ($item['id'] == $order['status']) {
                                            $order_products_body_data['products_status']['value'] = $item['name'];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                            // products status col
                            $order_products_body_data['products_customer_comments']['align'] = 'left';
                            $order_products_body_data['products_customer_comments']['class'] = 'cellComments';
                            $order_products_body_data['products_customer_comments']['value'] = $order['customer_comments'];
                        }
                        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            // products vat col
                            $order_products_body_data['products_vat']['align'] = 'right';
                            $order_products_body_data['products_vat']['class'] = 'cellVat';
                            $order_products_body_data['products_vat']['value'] = $row[4];
                        }
                        if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                            // products price col
                            $order_products_body_data['products_capital_price']['align'] = 'right';
                            $order_products_body_data['products_capital_price']['class'] = 'cellPrice cellNoWrap';
                            $order_products_body_data['products_capital_price']['id'] = 'edit_order_product_price';
                            $order_products_body_data['products_capital_price']['value'] = $row[7];
                        }
                        // products price col
                        $order_products_body_data['products_normal_price']['align'] = 'right';
                        $order_products_body_data['products_normal_price']['class'] = 'cellPrice cellNoWrap';
                        $order_products_body_data['products_normal_price']['id'] = 'edit_order_product_price';
                        $order_products_body_data['products_normal_price']['value'] = $row[3];
                        if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            // products vat col
                            $order_products_body_data['products_vat']['align'] = 'right';
                            $order_products_body_data['products_vat']['class'] = 'cellVat';
                            $order_products_body_data['products_vat']['value'] = $row[4];
                        }
                        if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                            $order_products_body_data['products_discount']['class'] = 'cellDiscount';
                            $order_products_body_data['products_discount']['value'] = $row[6];
                        }
                        // product final price
                        $order_products_body_data['products_final_price']['align'] = 'right';
                        //cellFinalPrice
                        $order_products_body_data['products_final_price']['class'] = 'cellPrice cellNoWrap';
                        $order_products_body_data['products_final_price']['id'] = 'edit_order_product_final_price';
                        $order_products_body_data['products_final_price']['value'] = $row[5];
                    }
                    if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                        if (!$this->get['edit_product'] || ($this->get['edit_product'] && $this->get['order_pid'] != $order['orders_products_id'])) {
                            $product_action_button = '<button type="button" onclick="location.href=\'' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $this->get['orders_id']) . '&action=edit_order&edit_product=1&order_pid=' . $order['orders_products_id'] . '\'" class="btn btn-primary btn-sm order_product_action"><i class="fa fa-pencil"></i></button> ';
                            $product_action_button .= '<a href="' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $this->get['orders_id']) . '&action=edit_order&delete_product=1&order_pid=' . $order['orders_products_id'] . '" style="text-decoration:none"><button type="button" onclick="return CONFIRM();" class="btn btn-danger btn-sm order_product_action"><i class="fa fa-trash-o"></i></button></a>';
                        } else {
                            $product_action_button = '<button type="button" onclick="location.href=\'' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $this->get['orders_id']) . '&action=edit_order\'" class="btn btn-danger btn-sm order_product_action"><i class="fa fa-remove"></i></button> <button type="submit" value="' . $this->pi_getLL('save') . '" class="btn btn-primary btn-sm submit_button order_product_action"><i class="fa fa-save"></i></button>';
                        }
                        // product final price
                        $order_products_body_data['products_action']['align'] = 'right';
                        $order_products_body_data['products_action']['class'] = 'cellAction';
                        $order_products_body_data['products_action']['value'] = $product_action_button;
                    }
                    $order_products_table['body'][$tbody_tag_id]['rows'][] = array(
                            'class' => $tr_type,
                            'value' => $order_products_body_data
                    );
                    if ($this->get['edit_product'] && $this->get['order_pid'] == $order['orders_products_id']) {
                        if ($this->ms['MODULES']['ENABLE_EDIT_ORDER_PRODUCTS_DESCRIPTION_FIELD']) {
                            $order_products_body_data = array();
                            // products id col
                            $order_products_body_data['products_id']['value'] = '';
                            // products qty col
                            $order_products_body_data['products_qty']['value'] = '';
                            if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                                // products qty delivered col
                                $order_products_body_data['products_qty_delivered']['value'] = '';
                            }
                            // products name col
                            $order_products_body_data['products_name']['value'] = '<label for="order_products_description">' . $this->pi_getLL('admin_edit_order_products_description') . ':</label><br/>
                            <textarea rows="8" id="order_products_description" class="form-control" name="order_products_description">' . $order['products_description'] . '</textarea>';
                            if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                                // products status col
                                $order_products_body_data['products_status']['value'] = '';
                            }
                            if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                                $order_products_body_data['products_customer_comments']['value'] = '';
                            }
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                // products vat col
                                $order_products_body_data['products_vat']['value'] = '';
                            }
                            // products capital price col
                            if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                                $order_products_body_data['products_capital_price']['value'] = '';
                            }
                            // products price col
                            $order_products_body_data['products_normal_price']['value'] = '';
                            if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                // products vat col
                                $order_products_body_data['products_vat']['value'] = '';
                            }
                            if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                                $order_products_body_data['products_discount']['class'] = 'cellDiscount';
                                $order_products_body_data['products_discount']['value'] = '';
                            }
                            if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                                $order_products_body_data['products_discount']['value'] = '';
                            }
                            // product final price
                            $order_products_body_data['products_final_price']['value'] = '';
                            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                                $order_products_body_data['products_action']['value'] = '';
                            }
                            $order_products_table['body'][$tbody_tag_id]['rows'][] = array(
                                    'class' => $tr_type . ' order_products_description',
                                    'value' => $order_products_body_data
                            );
                        }
                    } else {
                        if ($this->ms['MODULES']['ENABLE_EDIT_ORDER_PRODUCTS_DESCRIPTION_FIELD'] && !empty($order['products_description'])) {
                            $order_products_body_data = array();
                            // products id col
                            $order_products_body_data['products_id']['value'] = '';
                            // products qty col
                            $order_products_body_data['products_qty']['value'] = '';
                            if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                                // products qty delivered col
                                $order_products_body_data['products_qty_delivered']['value'] = '';
                            }
                            // products name col
                            $order_products_body_data['products_name']['value'] .= nl2br($order['products_description']);
                            if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                                // products status col
                                $order_products_body_data['products_status']['value'] = '';
                            }
                            if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                                $order_products_body_data['products_customer_comments']['value'] = '';
                            }
                            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                // products vat col
                                $order_products_body_data['products_vat']['value'] = '';
                            }
                            // products capital price col
                            if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                                $order_products_body_data['products_capital_price']['value'] = '';
                            }
                            // products price col
                            $order_products_body_data['products_normal_price']['value'] = '';
                            if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                // products vat col
                                $order_products_body_data['products_vat']['value'] = '';
                            }
                            if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                                $order_products_body_data['products_discount']['value'] = '';
                            }
                            // product final price
                            $order_products_body_data['products_final_price']['value'] = '';
                            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                                $order_products_body_data['products_action']['value'] = '';
                            }
                            $order_products_table['body'][$tbody_tag_id]['rows'][] = array(
                                    'class' => $tr_type . ' order_products_description',
                                    'value' => $order_products_body_data
                            );
                        }
                    }
                    if ($orders_products_attributes[$order['orders_products_id']]) {
                        $attr_counter = 0;
                        if ($this->get['edit_product'] && $this->get['order_pid'] == $order['orders_products_id']) {
                            $manual_attr = array();
                            foreach ($orders_products_attributes[$order['orders_products_id']] as $tmpkey => $options) {
                                $options['qty'] = $order['qty'];
                                $manual_attr['optname'][] = $options['products_options'];
                                $manual_attr['optvalue'][] = $options['products_options_values'];
                                $manual_attr['optprice'][] = $options['options_values_price'];
                                $manual_attr['attributes_data'][] = $options;
                                if ($options['products_options_id'] > 0) {
                                    $js_select2_cache_options[$options['products_options_id']] = 'attributesOptions[' . $options['products_options_id'] . ']={id:"' . $options['products_options_id'] . '", text:"' . $options['products_options'] . '"}';
                                } else {
                                    $js_select2_cache_options[$options['products_options']] = 'attributesOptions[\'' . $options['products_options'] . '\']={id:"' . $options['products_options'] . '", text:"' . $options['products_options'] . '"}';
                                }
                                if ($options['products_options_values_id'] > 0) {
                                    $js_select2_cache_values[$options['products_options_values_id']] = 'attributesValues[' . $options['products_options_values_id'] . ']={id:"' . $options['products_options_values_id'] . '", text:"' . $options['products_options_values'] . '"}';
                                } else {
                                    $js_select2_cache_values[$options['products_options_values']] = 'attributesValues[\'' . $options['products_options_values'] . '\']={id:"' . $options['products_options_values'] . '", text:"' . $options['products_options_values'] . '"}';
                                }
                            }
                            if (count($manual_attr['optname']) > 0) {
                                foreach ($manual_attr['optname'] as $idx => $optname) {
                                    $order_products_body_data = array();
                                    $attributes_data = $manual_attr['attributes_data'][$idx];
                                    $attributes_tax_data = unserialize($attributes_data['attributes_tax_data']);
                                    $attributes_qty = $attributes_data['qty'];
                                    $optvalue = $manual_attr['optvalue'][$idx];
                                    $optprice = 0;
                                    $prrice_prefix = $attributes_data['price_prefix'];
                                    if ($manual_attr['optprice'][$idx] > 0) {
                                        $optprice = $prrice_prefix . $manual_attr['optprice'][$idx];
                                    }
                                    // products id col
                                    $order_products_body_data['products_id']['value'] = '';
                                    // products qty col
                                    $order_products_body_data['products_qty']['value'] = '';
                                    if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                                        // products qty delivered col
                                        $order_products_body_data['products_qty_delivered']['value'] = '';
                                    }
                                    // products name col
                                    $order_products_body_data['products_name']['align'] = 'left';
                                    $order_products_body_data['products_name']['value'] = '<div class="product_attributes_wrapper">';
                                    $order_products_body_data['products_name']['value'] .= '<span class="products_attributes_option">';
                                    if ($attributes_data['products_options_id'] > 0) {
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" class="edit_product_manual_option edit_manual_attributes_input" id="edit_product_manual_option' . $attributes_data['orders_products_attributes_id'] . '" name="edit_manual_option[]" style="width:187px" value="' . $attributes_data['products_options_id'] . '"/> ';
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" name="is_manual_option[]"value="0"/>';
                                    } else {
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" class="edit_product_manual_option edit_manual_attributes_input" id="edit_product_manual_option' . $attributes_data['orders_products_attributes_id'] . '" name="edit_manual_option[]" style="width:187px" value="' . $optname . '"/> ';
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" name="is_manual_option[]"value="1"/>';
                                    }
                                    $order_products_body_data['products_name']['value'] .= '</span>';
                                    $order_products_body_data['products_name']['value'] .= '<span> : </span>';
                                    $order_products_body_data['products_name']['value'] .= '<span class="products_attributes_values">';
                                    if ($attributes_data['products_options_values_id'] > 0) {
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" class="edit_product_manual_values edit_manual_attributes_input" id="edit_product_manual_values' . $attributes_data['orders_products_attributes_id'] . '" name="edit_manual_values[]" style="width:187px" value="' . $attributes_data['products_options_values_id'] . '" rel="' . $attributes_data['orders_products_attributes_id'] . '" />';
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" name="is_manual_value[]"value="0"/>';
                                    } else {
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" class="edit_product_manual_values edit_manual_attributes_input" id="edit_product_manual_values' . $attributes_data['orders_products_attributes_id'] . '" name="edit_manual_values[]" style="width:187px" value="' . $optvalue . '" rel="' . $attributes_data['orders_products_attributes_id'] . '"/>';
                                        $order_products_body_data['products_name']['value'] .= '<input type="hidden" name="is_manual_value[]"value="1"/>';
                                    }
                                    $order_products_body_data['products_name']['value'] .= '</span>';
                                    $order_products_body_data['products_name']['value'] .= '</div>';
                                    if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                                        // products status col
                                        $order_products_body_data['products_status']['value'] = '';
                                    }
                                    if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                                        $order_products_body_data['products_customer_comments']['value'] = '';
                                    }
                                    if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                        // products vat col
                                        $order_products_body_data['products_vat']['value'] = '';
                                    }
                                    // products capital price col
                                    if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                                        $order_products_body_data['products_capital_price']['value'] = '';
                                    }
                                    // products price col
                                    $order_products_body_data['products_normal_price']['value'] = '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="display_manual_name_excluding_vat" name="display_name_excluding_vat" class="form-control msManualOrderProductPriceExcludingVat priceInputDisplay" value="' . number_format($optprice, 2, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '') . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>';
                                    $order_products_body_data['products_normal_price']['value'] .= '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="display_name" id="display_manual_name_including_vat" class="form-control msManualOrderProductPriceIncludingVat priceInputDisplay" value="' . number_format($optprice + $attributes_tax_data['tax'], 2, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '') . '" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>';
                                    $order_products_body_data['products_normal_price']['value'] .= '<div class="msAttributesField hidden"><input class="priceInputReal text" type="hidden" name="edit_manual_price[]" id="edit_manual_price" value="' . $optprice . '" /></div>';
                                    if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                        // products vat col
                                        $order_products_body_data['products_vat']['value'] = '';
                                    }
                                    if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                                        $order_products_body_data['products_discount']['class'] = 'cellDiscount';
                                        $order_products_body_data['products_discount']['value'] = '';
                                    }
                                    if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                        // product final price
                                        $order_products_body_data['products_final_price']['align'] = 'right';
                                        $order_products_body_data['products_final_price']['value'] = mslib_fe::amount2Cents(($optprice + $attributes_tax_data['tax']) * $attributes_qty, 0);
                                    } else {
                                        // product final price
                                        $order_products_body_data['products_final_price']['align'] = 'right';
                                        $order_products_body_data['products_final_price']['value'] = mslib_fe::amount2Cents($optprice, 0);
                                    }
                                    if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                                        // product final price
                                        $order_products_body_data['products_action']['align'] = 'left';
                                        $order_products_body_data['products_action']['value'] = '<button type="button" class="btn btn-danger btn-sm remove_attributes" value=""><i class="fa fa-minus"></i></button>';
                                    }
                                    $order_products_table['body'][$tbody_tag_id]['rows'][] = array(
                                            'class' => $tr_type,
                                            'value' => $order_products_body_data
                                    );
                                    $attr_counter++;
                                }
                            }
                        } else {
                            foreach ($orders_products_attributes[$order['orders_products_id']] as $tmpkey => $options) {
                                $options_attributes_tax_data = unserialize($options['attributes_tax_data']);
                                if (is_numeric($options['products_options_id'])) {
                                    $str = $GLOBALS['TYPO3_DB']->SELECTquery('listtype', // SELECT ...
                                            'tx_multishop_products_options o', // FROM ...
                                            'o.products_options_id=\'' . $options['products_options_id'] . '\' and language_id=\'' . $this->sys_language_uid . '\'', // WHERE...
                                            '', // GROUP BY...
                                            '', // ORDER BY...
                                            '' // LIMIT ...
                                    );
                                    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                                    $rowCheck = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
                                }
                                $attributes_tax_data = unserialize($options['attributes_tax_data']);
                                $order_products_body_data = array();
                                // products id col
                                $order_products_body_data['products_id']['value'] = '';
                                // products qty col
                                $order_products_body_data['products_qty']['value'] = '';
                                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                                    // products qty delivered col
                                    $order_products_body_data['products_qty_delivered']['value'] = '';
                                }
                                // products name col
                                $order_products_body_data['products_name']['align'] = 'left';
                                if ($rowCheck['listtype'] == 'file') {
                                    if ($options['products_options_values']) {
                                        $filePath = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/order_resources/' . rawurlencode($options['products_options_values']);
                                        if (file_exists($filePath)) {
                                            $displayImage = 0;
                                            $imgtype = mslib_befe::exif_imagetype($filePath);
                                            if ($imgtype) {
                                                // valid image
                                                $ext = image_type_to_extension($imgtype, false);
                                                if ($ext) {
                                                    $displayImage = 1;
                                                }
                                            }
                                            if ($displayImage) {
                                                $size = getimagesize($filePath);
                                                $width = '';
                                                if ($size[0] > 350) {
                                                    $width = '150';
                                                }
                                                // display image with link
                                                $htmlContent = '<br /><a href="' . $this->FULL_HTTP_URL . 'uploads/tx_multishop/order_resources/' . rawurlencode($options['products_options_values']) . '" class="msAdminDownloadIcon" target="_blank"><img src="' . $this->FULL_HTTP_URL . 'uploads/tx_multishop/order_resources/' . rawurlencode($options['products_options_values']) . '" width="' . $width . '" /></a>';
                                            } else {
                                                // display text with link
                                                $htmlContent = '<a href="' . $this->FULL_HTTP_URL . 'uploads/tx_multishop/order_resources/' . rawurlencode($options['products_options_values']) . '" class="msAdminDownloadIcon" target="_blank"><span>[save file]</span></a>';
                                            }
                                        }
                                    }
                                    $order_products_body_data['products_name']['value'] = '<a href="' . $this->FULL_HTTP_URL . 'uploads/tx_multishop/order_resources/' . rawurlencode($options['products_options_values']) . '" class="msAdminDownloadIcon" target="_blank">' . $options['products_options'] . ': ' . $options['products_options_values'] . $htmlContent . '</a>';
                                } else {
                                    $order_products_body_data['products_name']['value'] = $options['products_options'] . ': ' . $options['products_options_values'];
                                }
                                $cellPrice = '';
                                $cellVat = '';
                                $cellFinalPrice = '';
                                if ($options['options_values_price'] > 0) {
                                    if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                        $cellPrice = mslib_fe::amount2Cents(($options['price_prefix'] . $options['options_values_price']) + $options_attributes_tax_data['tax'], 0);
                                        $cellFinalPrice = mslib_fe::amount2Cents((($options['price_prefix'] . $options['options_values_price']) + $options_attributes_tax_data['tax']) * $row[1], 0);
                                    } else {
                                        $cellPrice = mslib_fe::amount2Cents(($options['price_prefix'] . $options['options_values_price']), 0);
                                        $cellFinalPrice = mslib_fe::amount2Cents(($options['price_prefix'] . $options['options_values_price']) * $row[1], 0);
                                    }
                                    $cellVat = $row[4];
                                }
                                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                                    // products status col
                                    $order_products_body_data['products_status']['value'] = '';
                                }
                                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                                    $order_products_body_data['products_customer_comments']['value'] = '';
                                }
                                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                    // products vat col
                                    $order_products_body_data['products_vat']['value'] = '';
                                }
                                // products capital price col
                                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                                    $order_products_body_data['products_capital_price']['value'] = '';
                                }
                                // products normal price col
                                $order_products_body_data['products_normal_price']['align'] = 'right';
                                $order_products_body_data['products_normal_price']['class'] = 'cellPrice';
                                $order_products_body_data['products_normal_price']['value'] = $cellPrice;
                                if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                    // products vat col
                                    $order_products_body_data['products_vat']['value'] = '';
                                }
                                if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                                    $order_products_body_data['products_discount']['class'] = 'cellDiscount';
                                    $order_products_body_data['products_discount']['value'] = '';
                                }
                                // product final price
                                $order_products_body_data['products_final_price']['align'] = 'right';
                                $order_products_body_data['products_final_price']['class'] = 'cellFinalPrice';
                                $order_products_body_data['products_final_price']['value'] = $cellFinalPrice;
                                if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                                    $order_products_body_data['products_action']['value'] = '';
                                }
                                // count the vat
                                if ($options['options_values_price'] and $order['products_tax']) {
                                    $item_tax = $order['qty'] * $attributes_tax_data['tax'];
                                    if ($options['price_prefix'] == '+') {
                                        $total_tax = $total_tax + $item_tax;
                                    } else {
                                        if ($options['price_prefix'] == '-') {
                                            $total_tax = $total_tax - $item_tax;
                                        }
                                    }
                                }
                                $order_products_table['body'][$tbody_tag_id]['rows'][] = array(
                                        'class' => $tr_type,
                                        'value' => $order_products_body_data
                                );
                            }
                        }
                    } else {
                        if ($this->get['edit_product'] && $this->get['order_pid'] == $order['orders_products_id']) {
                            $sql_option = "select po.products_options_name, po.products_options_id from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id where (po.hide_in_cart=0 or po.hide_in_cart is null) and po.language_id = '" . $this->sys_language_uid . "' and pa.products_id = " . $order['products_id'] . " and pa.page_uid=" . $this->showCatalogFromPage . " group by pa.options_id";
                            $qry_option = $GLOBALS['TYPO3_DB']->sql_query($sql_option);
                            while (($rs_option = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_option)) != false) {
                                $order_products_body_data = array();
                                // products id col
                                $order_products_body_data['products_id']['value'] = '';
                                // products qty col
                                $order_products_body_data['products_qty']['value'] = '';
                                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                                    // products qty delivered col
                                    $order_products_body_data['products_qty_delivered']['value'] = '';
                                }
                                // products name col
                                $order_products_body_data['products_name']['align'] = 'left';
                                $order_products_body_data['products_name']['value'] .= '<label>' . $rs_option['products_options_name'] . ':</label>';
                                $order_products_body_data['products_name']['value'] .= '<select name="option[' . $rs_option['products_options_id'] . ']" id="option_' . $rs_option['products_options_id'] . '" class="form-control" style="width:402px;">';
                                $sql_optval = "select pa.options_values_id, pov.products_options_values_name from tx_multishop_products_attributes pa left join tx_multishop_products_options po on pa.options_id = po.products_options_id left join tx_multishop_products_options_values pov on pa.options_values_id = pov.products_options_values_id where pov.language_id = '" . $this->sys_language_uid . "' and pa.options_id = '" . $rs_option['products_options_id'] . "' and pa.products_id = " . $order['products_id'] . " and pa.page_uid=" . $this->showCatalogFromPage;
                                $qry_optval = $GLOBALS['TYPO3_DB']->sql_query($sql_optval);
                                while (($rs_optval = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_optval)) != false) {
                                    $order_products_body_data['products_name']['value'] .= '<option value="' . $rs_optval['options_values_id'] . '">' . $rs_optval['products_options_values_name'] . '</option>';
                                }
                                $order_products_body_data['products_name']['value'] .= '</select>';
                                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                                    // products status col
                                    $order_products_body_data['products_status']['value'] = '';
                                }
                                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                                    $order_products_body_data['products_customer_comments']['value'] = '';
                                }
                                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                    // products vat col
                                    $order_products_body_data['products_vat']['value'] = '';
                                }
                                // products capital price col
                                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                                    $order_products_body_data['products_capital_price']['value'] = '';
                                }
                                $order_products_body_data['products_normal_price']['value'] = '';
                                if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                    // products vat col
                                    $order_products_body_data['products_vat']['value'] = '';
                                }
                                $order_products_body_data['products_final_price']['value'] = '';
                                if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                                    // product action col
                                    $order_products_body_data['products_action']['value'] = '';
                                }
                                $order_products_table['body'][$tbody_tag_id]['rows'][] = array(
                                        'class' => $tr_type,
                                        'value' => $order_products_body_data
                                );
                            }
                        }
                    }
                    // count the vat
                    if ($order['final_price'] and $order['products_tax']) {
                        $product_tax_data = unserialize($order['products_tax_data']);
                        $item_tax = $order['qty'] * ($product_tax_data['total_tax'] + $product_tax_data['total_attributes_tax']);
                        $total_tax = $total_tax + $item_tax;
                    }
                    if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details'] and $this->get['edit_product'] and ($this->get['order_pid'] == $order['orders_products_id'])) {
                        $order_products_body_data = array();
                        // products id col
                        $order_products_body_data['products_id']['class'] = 'last_edit_product_row_pid_col';
                        $order_products_body_data['products_id']['value'] = '';
                        // products qty col
                        $order_products_body_data['products_qty']['class'] = 'last_edit_product_row_pqty_col';
                        $order_products_body_data['products_qty']['value'] = '';
                        if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                            // products qty delivered col
                            $order_products_body_data['products_qty_delivered']['class'] = 'last_edit_product_row_pqty_delivered_col';
                            $order_products_body_data['products_qty_delivered']['value'] = '';
                        }
                        // products name col
                        $order_products_body_data['products_name']['class'] = 'last_edit_product_row_pname_col';
                        $order_products_body_data['products_name']['align'] = 'left';
                        $order_products_body_data['products_name']['value'] = '<button type="button" id="edit_add_attributes" class="btn btn-primary btn-sm" value="" style="display:none"><i class="fa fa-plus"></i> ' . $this->pi_getLL('add_attribute') . '</button>';
                        if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                            // products status col
                            $order_products_body_data['products_status']['class'] = 'last_edit_product_row_pstatus_col';
                            $order_products_body_data['products_status']['value'] = '';
                        }
                        if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                            $order_products_body_data['products_customer_comments']['class'] = 'last_edit_product_row_ccomments_col';
                            $order_products_body_data['products_customer_comments']['value'] = '';
                        }
                        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            // products vat col
                            $order_products_body_data['products_vat']['class'] = 'last_edit_product_row_pvat_col';
                            $order_products_body_data['products_vat']['value'] = '';
                        }
                        // products capital price col
                        if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                            $order_products_body_data['products_capital_price']['class'] = 'last_edit_product_row_pcapitalprice_col';
                            $order_products_body_data['products_capital_price']['value'] = '';
                        }
                        $order_products_body_data['products_normal_price']['class'] = 'last_edit_product_row_pprice_col';
                        $order_products_body_data['products_normal_price']['value'] = '';
                        if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                            // products vat col
                            $order_products_body_data['products_vat']['class'] = 'last_edit_product_row_pvat_col';
                            $order_products_body_data['products_vat']['value'] = '';
                        }
                        if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                            $order_products_body_data['products_discount']['class'] = 'cellDiscount';
                            $order_products_body_data['products_discount']['value'] = '';
                        }
                        $order_products_body_data['products_final_price']['class'] = 'last_edit_product_row_pfinalprice_col';
                        $order_products_body_data['products_final_price']['value'] = '';
                        if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                            // product action col
                            $order_products_body_data['products_action']['class'] = 'last_edit_product_row_paction_col';
                            $order_products_body_data['products_action']['value'] = '';
                        }
                        $order_products_table['body'][$tbody_tag_id]['rows'][] = array(
                                'id' => 'last_edit_product_row',
                                'value' => $order_products_body_data
                        );
                    }
                    // custom hook that can be controlled by third-party plugin
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderProductsTableBody'])) {
                        $params = array(
                                'orders' => &$orders,
                                'order' => &$order,
                                'tbody_tag_id' => &$tbody_tag_id,
                                'orders_products_attributes' => &$orders_products_attributes[$order['orders_products_id']],
                                'order_products_table_body' => &$order_products_table['body'],
                                'order_products_tax_data' => &$order_products_tax_data,
                                'settings' => &$settings,
                                'tr_type' => &$tr_type,
                                'all_orders_status' => &$all_orders_status
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderProductsTableBody'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                    // custom hook that can be controlled by third-party plugin eof
                }
            }
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                $colspan = 7;
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT'] > 0) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                    $colspan += 1;
                }
                $order_products_body_data = array();
                // products id col
                $order_products_body_data['products_id']['th'] = true;
                $order_products_body_data['products_id']['colspan'] = $colspan;
                $order_products_body_data['products_id']['style'] = 'text-align:left;';
                $order_products_body_data['products_id']['value'] = $this->pi_getLL('add_item_to_order');
                $order_products_table['body']['manual_add_new_product_header']['rows'][] = array(
                        'class' => 'manual_add_new_product',
                        'style' => 'display:none',
                        'value' => $order_products_body_data
                );
                // manual new product
                $order_products_body_data = array();
                // products id col
                $order_products_body_data['products_id']['value'] = '';
                // products qty col
                $order_products_body_data['products_qty']['align'] = 'right';
                $order_products_body_data['products_qty']['valign'] = 'top';
                $order_products_body_data['products_qty']['value'] = '<input type="hidden" name="manual_product_name" id="product_name" value="">';
                // qty
                $quantity_html = '<div class="quantity buttons_added">';
                $quantity_html .= '<input type="button" value="-" data-stepSize="1" data-minQty="1" data-maxQty="0" class="qty_minus" rel="manual_product_qty">';
                $quantity_html .= '<input class="form-control text" style="width:70px" type="text" name="manual_product_qty" id="manual_product_qty" value="1" tabindex="1" />';
                $quantity_html .= '<input type="button" value="+" data-stepSize="1" data-minQty="1" data-maxQty="0" class="qty_plus" rel="manual_product_qty">';
                $quantity_html .= '</div>';
                $order_products_body_data['products_qty']['value'] .= $quantity_html;
                $order_products_body_data['products_qty']['class'] = 'cellQty';
                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                    // products qty delivered col
                    $order_products_body_data['products_qty_delivered']['align'] = 'right';
                    $order_products_body_data['products_qty_delivered']['valign'] = 'top';
                    $order_products_body_data['products_qty_delivered']['value'] = '';
                    $order_products_body_data['products_qty_delivered']['class'] = 'cellQty';
                }
                // products name col
                $order_products_body_data['products_name']['align'] = 'left';
                $order_products_body_data['products_name']['valign'] = 'top';
                $order_products_body_data['products_name']['id'] = 'manual_add_product';
                $order_products_body_data['products_name']['value'] = '<div class="categories_products_select2_wrapperselect2-container">
					<div class="categories_select2_input">
						<input class="categories_name_input" type="hidden" name="categories_filter_id" id="categories_filter_id" value="" style="width:380px" />
					</div>
					<div id="manual_product_name_select2" class="products_select2_input">
						<input class="product_name" type="hidden" name="manual_products_id" value="" style="width:380px;" tabindex="2" />
					</div>
				</div>';
                if ($this->ms['MODULES']['ENABLE_MANUAL_ORDER_CUSTOM_ORDER_PRODUCTS_NAME']) {
                    $order_products_body_data['products_name']['value'] .= '<div id="custom_manual_product_name_wrapper" class="mt-10" style="display:none"><label for="custom_manual_product_name">' . $this->pi_getLL('admin_custom_product_name') . ':</label><input type="text" id="custom_manual_product_name" name="custom_manual_product_name" value="" disabled="disabled" width="402px" class="form-control" /></div>';
                }
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                    // products status col
                    $order_products_body_data['products_status']['value'] = '';
                }
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                    $order_products_body_data['products_customer_comments']['value'] = '';
                }
                $customer_country = mslib_fe::getCountryByName($orders['billing_country']);
                $sql_tax_sb = $GLOBALS['TYPO3_DB']->SELECTquery('t.tax_id, t.rate, t.name, trg.default_status', // SELECT ...
                        'tx_multishop_taxes t, tx_multishop_tax_rules tr, tx_multishop_tax_rule_groups trg', // FROM ...
                        't.tax_id=tr.tax_id and tr.rules_group_id=trg.rules_group_id and trg.status=1 and tr.cn_iso_nr=\'' . $customer_country['cn_iso_nr'] . '\'', // WHERE...
                        'trg.rules_group_id', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry_tax_sb = $GLOBALS['TYPO3_DB']->sql_query($sql_tax_sb);
                $vat_sb = '<select name="manual_product_tax" id="manual_product_tax" class="form-control" style="width:auto;">';
                $vat_sb .= '<option value="">' . $this->pi_getLL('admin_label_no_tax') . '</option>';
                while ($rs_tx_sb = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tax_sb)) {
                    $sb_tax_rate = ($rs_tx_sb['rate'] / 100);
                    $vat_sb .= '<option value="' . $rs_tx_sb['tax_id'] . '"' . (($rs_tx_sb['default_status']) ? ' selected' : '') . '>' . $rs_tx_sb['name'] . '</option>';
                }
                $vat_sb .= '</select>';
                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                    // products vat col
                    $order_products_body_data['products_vat']['align'] = 'right';
                    $order_products_body_data['products_vat']['valign'] = 'top';
                    $order_products_body_data['products_vat']['class'] = 'cellVat';
                    $order_products_body_data['products_vat']['value'] = $vat_sb;
                }
                // products capital price col
                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                    $order_products_body_data['products_capital_price']['valign'] = 'top';
                    $order_products_body_data['products_capital_price']['class'] = 'cellPrice';
                    $order_products_body_data['products_capital_price']['value'] = '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="display_manual_name_cp_excluding_vat" name="display_name_excluding_vat" class="form-control msManualOrderProductPriceExcludingVat priceInputDisplay" value="" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>';
                    $order_products_body_data['products_capital_price']['value'] .= '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="display_name" id="display_manual_name_cp_including_vat" class="form-control msManualOrderProductPriceIncludingVat priceInputDisplay" value="" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>';
                    $order_products_body_data['products_capital_price']['value'] .= '<div class="msAttributesField hidden"><input class="priceInputReal text" type="hidden" name="manual_product_capital_price" id="manual_product_capital_price" value="" /></div>';
                }
                // product normal price col
                $order_products_body_data['products_normal_price']['valign'] = 'top';
                $order_products_body_data['products_normal_price']['class'] = 'cellPrice';
                $order_products_body_data['products_normal_price']['value'] = '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="display_manual_name_excluding_vat" name="display_name_excluding_vat" class="form-control msManualOrderProductPriceExcludingVat priceInputDisplay" value="" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>';
                $order_products_body_data['products_normal_price']['value'] .= '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="display_name" id="display_manual_name_including_vat" class="form-control msManualOrderProductPriceIncludingVat priceInputDisplay" value="" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>';
                $order_products_body_data['products_normal_price']['value'] .= '<div class="msAttributesField hidden"><input class="priceInputReal text" type="hidden" name="manual_product_price" id="manual_product_price" value="" /></div>';
                /*if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
					$order_products_body_data['products_normal_price']['value']='<input class="text" style="width:44px" type="text" id="display_product_price" value="" tabindex="3"/>';
					$order_products_body_data['products_normal_price']['value'].='<input type="hidden" name="manual_product_price" id="product_price" value=""/>';
				} else {
					$order_products_body_data['products_normal_price']['value']='<input class="text" style="width:44px" type="text" name="manual_product_price" id="product_price" value="" tabindex="3"/>';
				}*/
                if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                    // products vat col
                    $order_products_body_data['products_vat']['align'] = 'right';
                    $order_products_body_data['products_vat']['valign'] = 'top';
                    $order_products_body_data['products_vat']['class'] = 'cellVat';
                    $order_products_body_data['products_vat']['value'] = $vat_sb;
                }
                if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                    $order_products_body_data['products_discount']['class'] = 'cellDiscount';
                    // add manual product korting
                    $percentage_sb = '<div class="discount_percentage_wrapper">
							<select name="manual_product_discount_percentage" id="manual_product_discount_percentage" style="width:210px">
								<option value="">' . $this->pi_getLL('use_discount_amount') . '</option>
							';
                    $selected_percentage = str_replace('.00', '', $order['discount_percentage']);
                    for ($p = 1; $p <= 100; $p++) {
                        $percentage_sb .= '<option value="' . $p . '">' . $p . '%</option>';
                    }
                    $percentage_sb .= '</select>
							</div>';
                    $order_products_body_data['products_discount']['value'] = $percentage_sb . '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="manual_display_name_discount_excluding_vat" name="manual_display_name_discount_excluding_vat" class="form-control msOrderManualProductPriceExcludingVat priceInputDisplay" value="" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>';
                    $order_products_body_data['products_discount']['value'] .= '<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="manual_display_name_discount_including_vat" id="manual_display_name_discount_including_vat" class="form-control msOrderManualProductPriceIncludingVat priceInputDisplay" value="0" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>';
                    $order_products_body_data['products_discount']['value'] .= '<div class="msAttributesField hidden"><input class="priceInputReal text" type="hidden" name="manual_product_discount_amount" id="manual_product_discount_amount" value="0" /></div>';
                }
                // product final price col
                $order_products_body_data['products_final_price']['value'] = '';
                // product action col
                $order_products_body_data['products_action']['align'] = 'right';
                $order_products_body_data['products_action']['class'] = 'cellAction';
                $order_products_body_data['products_action']['value'] = '<button type="button" title="' . $this->pi_getLL('cancel') . '" onclick="location.href=\'' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $this->get['orders_id']) . '&action=edit_order\'" class="btn btn-danger btn-sm"><i class="fa fa-remove"></i></button> ';
                $order_products_body_data['products_action']['value'] .= '<button type="submit" title="' . $this->pi_getLL('add') . '" class="btn btn-primary btn-sm submit_button"><i class="fa fa-plus"></i></button>';
                $order_products_table['body']['manual_add_new_product']['rows'][] = array(
                        'class' => 'odd manual_add_new_product',
                        'style' => 'display:none',
                        'value' => $order_products_body_data
                );
                // order product description
                if ($this->ms['MODULES']['ENABLE_EDIT_ORDER_PRODUCTS_DESCRIPTION_FIELD']) {
                    $order_products_body_data = array();
                    // products id col
                    $order_products_body_data['products_id']['value'] = '';
                    // products qty col
                    $order_products_body_data['products_qty']['value'] = '';
                    if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                        // products qty delivered col
                        $order_products_body_data['products_qty_delivered']['value'] = '';
                    }
                    // products name col
                    $order_products_body_data['products_name']['value'] = '<label for="order_products_description">' . $this->pi_getLL('admin_edit_order_products_description') . ':</label><br/>';
                    $order_products_body_data['products_name']['value'] .= '<textarea rows="8" cols="75" class="form-control" id="manual_order_products_description" name="manual_order_products_description"></textarea>';
                    if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                        // products status col
                        $order_products_body_data['products_status']['value'] = '';
                    }
                    if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                        $order_products_body_data['products_customer_comments']['value'] = '';
                    }
                    if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                        // products vat col
                        $order_products_body_data['products_vat']['value'] = '';
                    }
                    // products capital price col
                    if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                        $order_products_body_data['products_capital_price']['value'] = '';
                    }
                    $order_products_body_data['products_normal_price']['value'] = '';
                    if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                        // products vat col
                        $order_products_body_data['products_vat']['value'] = '';
                    }
                    if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                        $order_products_body_data['products_discount']['class'] = 'cellDiscount';
                        $order_products_body_data['products_discount']['value'] = '';
                    }
                    $order_products_body_data['products_final_price']['value'] = '';
                    if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                        // product action col
                        $order_products_body_data['products_action']['value'] = '';
                    }
                    $order_products_table['body']['manual_add_new_product_description']['rows'][] = array(
                            'class' => 'manual_add_new_product',
                            'style' => 'display:none',
                            'value' => $order_products_body_data
                    );
                }
                $order_products_body_data = array();
                // products id col
                $order_products_body_data['products_id']['class'] = 'last_edit_product_row_pid_col';
                $order_products_body_data['products_id']['value'] = '';
                // products qty col
                $order_products_body_data['products_qty']['class'] = 'last_edit_product_row_pqty_col';
                $order_products_body_data['products_qty']['value'] = '';
                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                    // products qty delivered col
                    $order_products_body_data['products_qty_delivered']['class'] = 'last_edit_product_row_pqty_delivered_col';
                    $order_products_body_data['products_qty_delivered']['value'] = '';
                }
                // products name col
                $order_products_body_data['products_name']['class'] = 'last_edit_product_row_pname_col';
                $order_products_body_data['products_name']['style'] = 'border:0px solid #fff';
                $order_products_body_data['products_name']['value'] = '<button type="button" class="btn btn-primary btn-sm" id="add_attributes" style="display:none"><i class="fa fa-plus"></i> add attribute</button>';
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                    // products status col
                    $order_products_body_data['products_status']['class'] = 'last_edit_product_row_pstatus_col';
                    $order_products_body_data['products_status']['value'] = '';
                }
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                    $order_products_body_data['products_customer_comments']['class'] = 'last_edit_product_row_ccomments_col';
                    $order_products_body_data['products_customer_comments']['value'] = '';
                }
                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                    // products vat col
                    $order_products_body_data['products_vat']['class'] = 'last_edit_product_row_pvat_col';
                    $order_products_body_data['products_vat']['value'] = '';
                }
                // products capital price col
                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                    $order_products_body_data['products_capital_price']['class'] = 'last_edit_product_row_pcapitalprice_col';
                    $order_products_body_data['products_capital_price']['value'] = '';
                }
                $order_products_body_data['products_normal_price']['class'] = 'last_edit_product_row_pprice_col';
                $order_products_body_data['products_normal_price']['value'] = '';
                if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT']) {
                    // products vat col
                    $order_products_body_data['products_discount']['class'] = 'last_edit_product_row_discount_col';
                    $order_products_body_data['products_discount']['value'] = '';
                }
                if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                    // products vat col
                    $order_products_body_data['products_vat']['class'] = 'last_edit_product_row_pvat_col';
                    $order_products_body_data['products_vat']['value'] = '';
                }
                $order_products_body_data['products_final_price']['class'] = 'last_edit_product_row_pfinalprice_col';
                $order_products_body_data['products_final_price']['value'] = '';
                if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                    // product action col
                    $order_products_body_data['products_action']['class'] = 'last_edit_product_row_paction_col';
                    $order_products_body_data['products_action']['value'] = '';
                }
                $order_products_table['body']['last_edit_product_row']['rows'][] = array(
                        'class' => 'manual_add_new_product',
                        'id' => 'last_edit_product_row',
                        'style' => 'display:none',
                        'value' => $order_products_body_data
                );
                if (!isset($this->get['edit_product'])) {
                    $order_products_body_data = array();
                    // products id col
                    $order_products_body_data['products_id']['colspan'] = $colspan;
                    $order_products_body_data['products_id']['style'] = 'text-align:left;';
                    $order_products_body_data['products_id']['value'] = '<a href="#" id="button_manual_new_product" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> ' . $this->pi_getLL('add_manual_product', 'ADD ITEM') . '</a>';
                    $order_products_table['body']['add_new_product_button']['rows'][] = array('value' => $order_products_body_data);
                }
            } else {
                $colspan = 7;
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS'] > 0) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT'] > 0) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT']) {
                    $colspan += 1;
                }
                if ($this->ms['MODULES']['SHOW_QTY_DELIVERED'] > 0) {
                    $colspan += 1;
                }
            }
            // custom hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderProductsTableAddManualProduct'])) {
                $params = array(
                        'orders' => &$orders,
                        'colspan' => &$colspan,
                        'settings' => $settings,
                        'order_products_table' => &$order_products_table['body']
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_order.php']['editOrderProductsTableAddManualProduct'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom hook that can be controlled by third-party plugin eof
            if (is_array($order_products_table) && count($order_products_table)) {
                // order products table header
                foreach ($order_products_table['header'] as $header_data) {
                    $tmpcontent .= '<thead>';
                    $tmpcontent .= '<tr>';
                    foreach ($header_data['value'] as $header_col) {
                        $col_class = '';
                        $col_id = '';
                        $col_style = '';
                        $col_align = '';
                        $col_valign = '';
                        $col_span = '';
                        if (isset($header_col['class'])) {
                            $col_class = ' class="' . $header_col['class'] . '"';
                        }
                        if (isset($header_col['id'])) {
                            $col_id = ' id="' . $header_col['id'] . '"';
                        }
                        if (isset($header_col['style'])) {
                            $col_style = ' style="' . $header_col['style'] . '"';
                        }
                        if (isset($header_col['align'])) {
                            $col_align = ' align="' . $header_col['align'] . '"';
                        }
                        if (isset($header_col['valign'])) {
                            $col_valign = ' valign="' . $header_col['valign'] . '"';
                        }
                        if (isset($header_col['colspan'])) {
                            $col_span = ' colspan="' . $header_col['colspan'] . '"';
                        }
                        $tmpcontent .= '<th' . $col_class . $col_id . $col_style . $col_align . $col_valign . $col_span . '>' . $header_col['value'] . '</th>';
                    }
                    $tmpcontent .= '</tr>';
                    $tmpcontent .= '</thead>';
                }
                // order products table body
                foreach ($order_products_table['body'] as $tbody_tag_id => $body_data) {
                    $use_tbody = false;
                    if (strpos($tbody_tag_id, 'orders_products_id') !== false) {
                        $use_tbody = true;
                        $tmpcontent .= '<tbody id="' . $tbody_tag_id . '" class="' . $body_data['tbody_class'] . '">';
                    }
                    if (is_array($body_data['rows']) && count($body_data['rows'])) {
                        foreach ($body_data['rows'] as $body_rows_data) {
                            $row_class = '';
                            $row_id = '';
                            $row_style = '';
                            $row_align = '';
                            $row_valign = '';
                            if (isset($body_rows_data['class'])) {
                                $row_class = ' class="' . $body_rows_data['class'] . '"';
                            }
                            if (isset($body_rows_data['id'])) {
                                $row_id = ' id="' . $body_rows_data['id'] . '"';
                            }
                            if (isset($body_rows_data['style'])) {
                                $row_style = ' style="' . $body_rows_data['style'] . '"';
                            }
                            if (isset($body_rows_data['align'])) {
                                $row_align = ' align="' . $body_rows_data['align'] . '"';
                            }
                            if (isset($body_rows_data['valign'])) {
                                $row_valign = ' valign="' . $body_rows_data['valign'] . '"';
                            }
                            $tmpcontent .= '<tr' . $row_class . $row_id . $row_style . $row_align . $row_valign . '>';
                            foreach ($body_rows_data['value'] as $body_col) {
                                if (count($body_rows_data['value']) == '1' && isset($body_col['colspan']) && $body_col['colspan'] != $colspan) {
                                    $body_col['colspan'] = $colspan;
                                }
                                $col_class = '';
                                $col_id = '';
                                $col_style = '';
                                $col_align = '';
                                $col_valign = '';
                                $col_span = '';
                                if (isset($body_col['class'])) {
                                    $col_class = ' class="' . $body_col['class'] . '"';
                                }
                                if (isset($body_col['id'])) {
                                    $col_id = ' id="' . $body_col['id'] . '"';
                                }
                                if (isset($body_col['style'])) {
                                    $col_style = ' style="' . $body_col['style'] . '"';
                                }
                                if (isset($body_col['align'])) {
                                    $col_align = ' align="' . $body_col['align'] . '"';
                                }
                                if (isset($body_col['valign'])) {
                                    $col_valign = ' valign="' . $body_col['valign'] . '"';
                                }
                                if (isset($body_col['colspan'])) {
                                    $col_span = ' colspan="' . $body_col['colspan'] . '"';
                                }
                                if ($body_col['value'] == '') {
                                    $body_col['value'] = '&nbsp;';
                                }
                                $col_type = 'td';
                                if (isset($body_col['th']) && $body_col['th']) {
                                    $col_type = 'th';
                                }
                                $tmpcontent .= '<' . $col_type . $col_class . $col_id . $col_style . $col_align . $col_valign . $col_span . '>' . $body_col['value'] . '</' . $col_type . '>';
                            }
                            $tmpcontent .= '</tr>';
                        }
                    }
                    if ($use_tbody) {
                        $tmpcontent .= '</tbody>';
                    }
                }
            }
            //echo '<pre>';
            //print_r($order_products_table);
            //die();
            //		$tmpcontent.='<tr><td colspan="'.$colspan.'"><hr class="hr"></td></tr>';
            $orders_tax_data = unserialize($orders['orders_tax_data']);
            // hook for adding new fieldsets into edit_order
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersOrderTax'])) {
                // hook
                $params = array(
                        'orders_tax_data' => &$orders_tax_data,
                        'orders' => &$orders
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersOrderTax'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
                // hook oef
            }
            $tmpcontent .= '<tfoot><tr><td colspan="' . $colspan . '" class="order_total_data text-right">';
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                $iso_customer = mslib_fe::getCountryByName($orders['billing_country']);
                $iso_customer['country'] = $iso_customer['cn_short_en'];
                //
                $payment_method = mslib_fe::getPaymentMethod($orders['payment_method'], 'p.code', $iso_customer['cn_iso_nr']);
                $shipping_method = mslib_fe::getShippingMethod($orders['shipping_method'], 's.code', $iso_customer['cn_iso_nr']);
                //
                if ($iso_customer['cn_iso_nr'] > 0) {
                    $payment_tax_ruleset = mslib_fe::taxRuleSet($payment_method['tax_id'], 0, $iso_customer['cn_iso_nr'], 0);
                    $shipping_tax_ruleset = mslib_fe::taxRuleSet($shipping_method['tax_id'], 0, $iso_customer['cn_iso_nr'], 0);
                    if (!$payment_tax_ruleset) {
                        $payment_method['tax_id'] = 0;
                    }
                    if (!$shipping_tax_ruleset) {
                        $shipping_method['tax_id'] = 0;
                    }
                }
                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                    $shipping_costs = '<div class="input-group pull-right" style="width:140px;">
						<span class="input-group-addon">' . mslib_fe::currency() . '</span>
						<input name="tx_multishop_pi1[shipping_method_costs]" id="shipping_method_costs" type="text" class="form-control text-right priceInputDisplay" value="' . number_format($orders['shipping_method_costs'] + $orders_tax_data['shipping_tax'], 4, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '') . '" class="align_right" />
						<input type="hidden" id="hidden_shipping_tax" value="' . $orders_tax_data['shipping_tax'] . '">
						<input type="hidden" name="shipping_costs_manual" id="shipping_costs_manual" value="' . $orders['is_shipping_costs_manual'] . '">
					</div>';
                    $payment_costs = '<div class="input-group pull-right" style="width:140px;">
						<span class="input-group-addon">' . mslib_fe::currency() . '</span>
						<input name="tx_multishop_pi1[payment_method_costs]" id="payment_method_costs" type="text" class="form-control text-right priceInputDisplay" value="' . number_format($orders['payment_method_costs'] + $orders_tax_data['payment_tax'], 4, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '') . '" class="align_right" />
						<input type="hidden" id="hidden_payment_tax" value="' . $orders_tax_data['payment_tax'] . '">
					</div>';
                } else {
                    $shipping_costs = '<div class="input-group pull-right" style="width:140px;">
						<span class="input-group-addon">' . mslib_fe::currency() . '</span>
						<input name="tx_multishop_pi1[shipping_method_costs]" id="shipping_method_costs" type="text" value="' . number_format($orders['shipping_method_costs'], 4, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '') . '" class="form-control text-right priceInputDisplay">
						<input type="hidden" id="hidden_shipping_tax" value="' . $orders_tax_data['shipping_tax'] . '">
					</div>';
                    $payment_costs = '<div class="input-group pull-right" style="width:140px;">
						<span class="input-group-addon">' . mslib_fe::currency() . '</span>
						<input name="tx_multishop_pi1[payment_method_costs]" id="payment_method_costs" type="text" value="' . number_format($orders['payment_method_costs'], 4, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '') . '" class="form-control text-right priceInputDisplay">
						<input type="hidden" id="hidden_payment_tax" value="' . $orders_tax_data['payment_tax'] . '">
					</div>';
                }
            } else {
                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                    $shipping_costs .= '<p class="form-control-static order_total_value">' . mslib_fe::amount2Cents($orders['shipping_method_costs'] + $orders_tax_data['shipping_tax'], 0) . '</p>';
                    $payment_costs .= '<p class="form-control-static order_total_value">' . mslib_fe::amount2Cents($orders['payment_method_costs'] + $orders_tax_data['payment_tax'], 0) . '</p>';
                } else {
                    $shipping_costs .= '<p class="form-control-static order_total_value">' . mslib_fe::amount2Cents($orders['shipping_method_costs'], 0) . '</p>';
                    $payment_costs .= '<p class="form-control-static order_total_value">' . mslib_fe::amount2Cents($orders['payment_method_costs'], 0) . '</p>';
                }
            }
            if ($orders_tax_data['shipping_tax'] || $orders_tax_data['payment_tax']) {
                $total_tax += $orders_tax_data['shipping_tax'];
                $total_tax += $orders_tax_data['payment_tax'];
            }
            $tmpcontent .= '
            <div class="order_total">';
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                $tmpcontent .= '
                <div class="form-group">
                    <label class="control-label col-md-10">' . $this->pi_getLL('sub_total') . '</label>
                    <div class="col-md-2">
                    <p class="form-control-static order_total_value">' . mslib_fe::amount2Cents($orders_tax_data['sub_total'], 0) . '</p>
                    <input type="hidden" name="hidden_subtotal" id="hidden_subtotal" value="' . $orders_tax_data['sub_total'] . '">
                    <input type="hidden" name="hidden_subtotal_excl_vat" id="hidden_subtotal_excl_vat" value="' . $orders_tax_data['sub_total_excluding_vat'] . '">
                    </div>
                </div>';
                $content_subtotal_tax = '
                <div class="form-group">
                    <label class="control-label col-md-10">' . $this->pi_getLL('included_vat_amount') . '</label>
                    <div class="col-md-2">
                    <p class="form-control-static order_total_value total_orders_tax">' . mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0) . '</p>
                    <input type="hidden" id="hidden_total_orders_tax" value="' . $orders_tax_data['total_orders_tax'] . '">
                    <input type="hidden" id="hidden_orders_products_tax" value="' . ($orders_tax_data['total_orders_tax'] - ($orders_tax_data['shipping_tax'] + $orders_tax_data['payment_tax'])) . '">
                    </div>
                </div>';
            } else {
                $tmpcontent .= '
                <div class="form-group">
                    <label class="control-label col-md-10">' . $this->pi_getLL('sub_total') . '</label>
                    <div class="col-md-2">
                    <p class="form-control-static order_total_value">' . mslib_fe::amount2Cents($orders_tax_data['sub_total_excluding_vat'], 0) . '</p>
                    <input type="hidden" name="hidden_subtotal" id="hidden_subtotal" value="' . $orders_tax_data['sub_total_excluding_vat'] . '">
                    <input type="hidden" name="hidden_subtotal_excl_vat" id="hidden_subtotal_excl_vat" value="' . $orders_tax_data['sub_total_excluding_vat'] . '">
                    </div>
                </div>';
                $content_subtotal_tax = '
                <div class="form-group">
                    <label class="control-label col-md-10">' . $this->pi_getLL('vat') . '</label>
                    <div class="col-md-2">
                    <p class="form-control-static order_total_value total_orders_tax">' . mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0) . '</p>
                    <input type="hidden" id="hidden_total_orders_tax" value="' . $orders_tax_data['total_orders_tax'] . '">
                    <input type="hidden" id="hidden_orders_products_tax" value="' . ($orders_tax_data['total_orders_tax'] - ($orders_tax_data['shipping_tax'] + $orders_tax_data['payment_tax'])) . '">
                    </div>
                </div>';
            }
            $content_shipping_costs = '
            <div class="form-group" id="shipping_cost_input_wrapper" style="display:none">
                <label class="control-label col-md-10">' . $this->pi_getLL('shipping_costs') . '</label>
                <div class="col-md-2">
                ' . $shipping_costs . '
                </div>
            </div>';
            $content_payment_costs = '
            <div class="form-group" id="payment_cost_input_wrapper" style="display:none">
                <label class="control-label col-md-10">' . $this->pi_getLL('payment_costs') . '</label>
                <div class="col-md-2">
                ' . $payment_costs . '
                </div>
            </div>';
            $discount_content = '';
            $haveDiscountValue = false;
            // Check for discount amount for signed/unsigned value
            if ($orders['discount'] > 0) {
                $haveDiscountValue = true;
            } else {
                $discountAmount = str_replace('-', '', $orders['discount']);
                if ($discountAmount > 0) {
                    $haveDiscountValue = true;
                }
            }
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                $discount_content = '<div class="input-group pull-right" id="discount_amount_wrapper" style="width:140px;' . ($haveDiscountValue ? '' : ' display:none;') . '">';
                $discount_content .= '<span class="input-group-addon">' . mslib_fe::currency() . '</span><input name="edit_discount_value" id="edit_discount_value" class="form-control text-right priceInputDisplay" type="text" value="' . number_format($orders['discount'], 2, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], '') . '">';
                $discount_content .= '</div>';
                /*
                 * discount percentage
                 */
                $discount_percentage_value_selectbox = '<select name="edit_discount_percentage" id="edit_discount_percentage"'.($haveDiscountValue ? ' disabled="disabled"' : '').'><option value="">' . $this->pi_getLL('select_percentage') . '</option>';
                for ($i = 1; $i <= 100; $i++) {
                    if ($orders['discount_percentage'] == $i) {
                        $discount_percentage_value_selectbox .= '<option value="' . $i . '" selected="selected">' . $i . '%</option>';
                    } else {
                        $discount_percentage_value_selectbox .= '<option value="' . $i . '">' . $i . '%</option>';
                    }
                }
                $discount_percentage_value_selectbox .= '</select>';
                $discount_content .= '<div class="input-group pull-right" id="discount_percentage_wrapper" style="' . ($haveDiscountValue ? 'display:none; ' : '') . 'width:140px;">';
                $discount_content .= $discount_percentage_value_selectbox;
                $discount_content .= '</div>';
                $discount_content .= '<div class="input-group pull-right">';
                $discount_content .= '<a href="#" id="switch_to_discount_amount"' . ($haveDiscountValue ? ' style="display:none;"' : '') . '>' . $this->pi_getLL('switch_to_discount_amount') . '</a><a href="#" id="switch_to_discount_percentage"' . ($haveDiscountValue ? '' : ' style="display:none;"') . '>' . $this->pi_getLL('switch_to_discount_percentage') . '</a>';
                $discount_content .= '</div>';
            } else {
                if ($haveDiscountValue) {
                    $discount_content = mslib_fe::amount2Cents($orders['discount'], 0);
                }
            }
            if (!empty($discount_content)) {
                $coupon_code = '';
                if (!empty($orders['coupon_code'])) {
                    $coupon_code = ' (code: ' . $orders['coupon_code'] . ')';
                }
                $content_discount = '
                <div class="form-group" id="order_discount_wrapper">
                    <label class="control-label col-md-10">' . $this->pi_getLL('discount') . $coupon_code . '</label>
                    <div class="col-md-2">
                    ' . $discount_content . '
                    </div>
                </div>';
            }
            //print_r($orders_tax_data);
            $content_total = '
            <div class="form-group">
                <label class="control-label col-md-10">' . (!$orders_tax_data['total_orders_tax'] ? $this->pi_getLL('total_excl_vat') : $this->pi_getLL('total')) . '</label>
                <div class="col-md-2">
                <p class="form-control-static order_total_value grand_total">' . mslib_fe::amount2Cents($orders_tax_data['grand_total'], 0) . '</p>
                </div>
            </div>';
            // hook for adding new fieldsets into edit_order
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsPostProc'])) {
                // hook
                $params = array(
                    'content_shipping_costs' => &$content_shipping_costs,
                    'shipping_costs' => &$shipping_costs,
                    'content_payment_costs' => &$content_payment_costs,
                    'content_discount' => &$content_discount,
                    'content_total' => &$content_total,
                    'content_subtotal_tax' => &$content_subtotal_tax,
                    'orders' => &$orders
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersDetailsPostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
                // hook oef
            }
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                $tmpcontent .= $content_shipping_costs;
                $tmpcontent .= $content_payment_costs;
                $tmpcontent .= $content_discount;
                $tmpcontent .= $content_total;
                $tmpcontent .= $content_subtotal_tax;
            } else {
                if ($orders_tax_data['shipping_tax'] || $orders_tax_data['payment_tax']) {
                    $tmpcontent .= $content_shipping_costs;
                    $tmpcontent .= $content_payment_costs;
                    $tmpcontent .= $content_discount;
                    $tmpcontent .= $content_subtotal_tax;
                    $tmpcontent .= $content_total;
                } else {
                    $tmpcontent .= $content_subtotal_tax;
                    $tmpcontent .= $content_shipping_costs;
                    $tmpcontent .= $content_payment_costs;
                    $tmpcontent .= $content_discount;
                    $tmpcontent .= $content_total;
                }
            }
            $tmpcontent .= '</div>';
            $tmpcontent .= '</td></tr></tfoot></table>';
            if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
                $tmpcontent .= '<script type="text/javascript">';
                $tmpcontent .= '
                $(".qty_minus").click(function () {
                    var stepSize=parseFloat($(this).attr("data-stepSize"));
                    var minQty=parseFloat($(this).attr("data-minQty"));
                    var maxQty=parseFloat($(this).attr("data-maxQty"));
                    var new_val = 0;
                    var qty_id = "#" + $(this).attr("rel");
                    var qty = parseFloat($(qty_id).val());
                    new_val = parseFloat(qty - stepSize).toFixed(2).replace(\'.00\', \'\');
                    //if (qty > minQty) {
                    //    new_val = parseFloat(qty - stepSize).toFixed(2).replace(\'.00\', \'\');
                    //}
                    //if (parseFloat(new_val)==0) {
                    //    new_val=minQty;
                    //}
                    $(qty_id).val(new_val);
                });
                $(".qty_plus").click(function () {
                    var stepSize=parseFloat($(this).attr("data-stepSize"));
                    var minQty=parseFloat($(this).attr("data-minQty"));
                    var maxQty=parseFloat($(this).attr("data-maxQty"));
                    var qty_id = "#" + $(this).attr("rel");
                    var qty = parseFloat($(qty_id).val());
                    var new_val = 0;
                    if (maxQty>0) {
                        new_val=qty;
                        if (qty < maxQty) {
                            new_val = parseFloat(qty + stepSize).toFixed(2).replace(\'.00\', \'\');
                        }
                        if (new_val>maxQty) {
                            new_val=maxQty;
                        }
                    } else {
                        new_val = parseFloat(qty + stepSize).toFixed(2).replace(\'.00\', \'\');
                    }
                    $(qty_id).val(new_val);
                });
                // autocomplete for options val
                var select2_cn = function(selector_str, placeholder, dropdowncss, ajax_url) {
                    $(selector_str).select2({
                        placeholder: placeholder,
                        minimumInputLength: 0,
                        query: function(query) {
                            $.ajax(ajax_url, {
								data: {
									q: query.term
								},
								dataType: "json"
							}).done(function(data) {
								query.callback({results: data});
							});
                        },
                        initSelection: function(element, callback) {
                            var id=$(element).val();
                            if (id!=="") {
                                $.ajax(\'' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues') . '\', {
									data: {
										preselected_id: id
									},
									dataType: "json"
								}).done(function(data) {
									callback(data);
								});
                            }
                        },
                        formatResult: function(data){
                            if (data.text === undefined) {
                                $.each(data, function(i,val){
                                    return val.text;
                                });
                            } else {
                                return data.text;
                            }
                        },
                        formatSelection: function(data){
                            if (data.text === undefined) {
                                return data[0].text;
                            } else {
                                return data.text;
                            }
                        },
                        dropdownCssClass: dropdowncss,
                        escapeMarkup: function (m) { return m; }
                    });
                }
                var select2_pn = function(selector_str, placeholder, dropdowncss, ajax_url) {
                    $(selector_str).select2({
                        placeholder: placeholder,
                        
                        ' . (($this->ms['MODULES']['DISABLE_EDIT_ORDER_ADD_MANUAL_PRODUCT'] == '0') ? '
                        createSearchChoice:function(term, data) {
                            if ($(data).filter(function() {
							    return this.text.localeCompare(term)===0;
                            }).length===0) {
                                if (productsSearch[term] === undefined) {
                                    productsSearch[term]={id: term, text: term};
                                }
                                return {id:term, text:term};
                            }
                        },
                        ' : '') . '
                        minimumInputLength: ' . (!isset($this->ms['MODULES']['EDIT_ORDER_SELECT2_PRODUCT_MINIMUM_CHARACTER']) ? 0 : $this->ms['MODULES']['EDIT_ORDER_SELECT2_PRODUCT_MINIMUM_CHARACTER']) . ',
                        query: function(query) {
                            /*if (productsSearch[query.term] !== undefined) {
                                query.callback({results: productsSearch[query.term]});
                            } else {*/
                                $.ajax(ajax_url, {
                                    data: {
                                        q: query.term+ "||catid=" +  $("#categories_filter_id").select2("val")
                                    },
                                    dataType: "json"
                                }).done(function(data) {
                                    //productsSearch[query.term]=data;
                                    query.callback({results: data});
                                });
                            //}
                        },
                        initSelection: function(element, callback) {
                            var id=$(element).val();
                            if (id!=="") {
                                if (Products[id] !== undefined) {
                                    callback(Products[id]);
                                } else {
                                    $.ajax(ajax_url, {
                                        data: {
                                            preselected_id: id
                                        },
                                        dataType: "json"
                                    }).done(function(data) {
                                        Products[data.id]={id: data.id, text: data.text};
                                        callback(data);
                                    });
                                }
                            }
                        },
                        formatResult: function(data){
                            if (data.text === undefined) {
                                $.each(data, function(i,val){
                                    var product_name=val.text;
                                    if (val.products_model!=undefined && val.products_model!=\'\') {
                                        product_name += \' (MODEL: \' + val.products_model + \')\';
                                    }
                                    return product_name;
                                });
                            } else {
                                var product_name=data.text;
                                if (data.products_model!=undefined && data.products_model!=\'\') {
                                    product_name += \' (MODEL: \' + data.products_model + \')\';
                                }
                                return product_name;
                                //return data.text;
                            }
                        },
                        formatSelection: function(data){
                            if (data.text === undefined) {
                                return data[0].text;
                            } else {
                                return data.text;
                            }
                        },
                        dropdownCssClass: dropdowncss,
                        escapeMarkup: function (m) { return m; }
                    }).on("select2-selecting", function(e) {
                        if (e.object.id == e.object.text) {
                            if ($("#product_tax").length>0) {
                                //$("#product_tax").val("");
                                $("#display_name_including_vat").val("0" + decimal_sep + "00");
                                $("#display_name_excluding_vat").val("0" + decimal_sep + "00");
                                $("#product_price").val("0" + decimal_sep + "00");
                            } else {
                                //$("#manual_product_tax").val("");
                                $("#display_manual_name_including_vat").val("0" + decimal_sep + "00");
                                $("#display_manual_name_excluding_vat").val("0" + decimal_sep + "00");
                                $("#manual_product_price").val("0" + decimal_sep + "00");
                            }
                            ' . ($this->ms['MODULES']['ENABLE_MANUAL_ORDER_CUSTOM_ORDER_PRODUCTS_NAME'] ? '
                            $("#custom_manual_product_name_wrapper").hide();
                            $("#custom_manual_product_name").val("");
                            $("#custom_manual_product_name").prop("disabled", "disabled");
                            ' : '') . '
                        } else {
                            var product_id = e.object.id;
                            $("#edit_order_product_id").html(product_id);
                            jQuery.getJSON("' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=ajax_products_staffelprice_search&tx_multishop_pi1[type]=edit_order') . '",{pid: product_id, oid:' . $this->get['orders_id'] . ', qty: 1}, function(d){
                                if (d.tax_id!="" && d.tax_id!=undefined) {
                                    if ($("#product_tax").length>0) {
                                        if ($("#product_tax").children().length>0) {
                                        	$("#product_tax").val(d.tax_id);
                                        } else {
                                            d.price_include_vat=0;
                                        }
                                    } else {
                                        if ($("#manual_product_tax").length>0) {
                                        	$("#manual_product_tax").val(d.tax_id);
                                        } else {
                                            d.price_include_vat=0;
                                        }
                                    }
                                } else {
                                    if ($("#product_tax").length>0) {
                                        if ($("#product_tax").children().length>0) {
                                        	$("#product_tax").val("");
                                        }
                                    } else {
                                        if ($("#manual_product_tax").length>0) {
                                        	$("#manual_product_tax").val("");
                                        }
                                    }
                                }
                                /*if (!d.use_tax_id) {
                                	d.price_include_vat=0;
                                	if ($("#product_tax").length>0) {
                                        if ($("#product_tax").children().length>0) {
                                        	$("#product_tax").val("");
                                        }
                                    } else {
                                        if ($("#manual_product_tax").length>0) {
                                        	$("#manual_product_tax").val("");
                                        }
                                    }
                                }*/
                                if (d.price_include_vat>0) {
                                    if ($("#product_tax").length>0) {
                                        $("#display_name_including_vat").val(d.display_price_include_vat);
                                    } else {
                                        $("#display_manual_name_including_vat").val(d.display_price_include_vat);
                                    }
                                } else {
                                    if ($("#product_tax").length>0) {
                                        $("#display_name_including_vat").val(d.display_price);
                                    } else {
                                        $("#display_manual_name_including_vat").val(d.display_price);
                                    }
                                }
                                if ($("#product_tax").length>0) {
                                    $("#display_name_excluding_vat").val(d.display_price);
                                    $("#product_price").val(d.price);
                                } else {
                                    $("#display_manual_name_excluding_vat").val(d.display_price);
                                    $("#manual_product_price").val(d.price);
                                }
                            });
                            // get the pre-def attributes
                            $(\'.manual_new_attributes\').remove();
                            jQuery.getJSON("' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=ajax_products_attributes_search&tx_multishop_pi1[type]=edit_order&ajax_products_attributes_search[action]=get_options_values') . '",{pid: product_id, optid: 0}, function(optionsData){
                                if (optionsData.length==0) {
									if ($("#edit_add_attributes").length) {
										$("#edit_add_attributes").hide();
									} else {
										$("#add_attributes").hide();
									}
                                } else {
                                	if ($("#edit_add_attributes").length) {
										$("#edit_add_attributes").show();
									} else {
										$("#add_attributes").show();
									}
									$.each(optionsData, function(i, opt){
									    var valid = 0;
									    if (typeof opt.value != "undefined") { 
										    valid = opt.value.valid;
										}
										var price_data={values_price: opt.value.values_price, display_values_price: opt.value.display_values_price, display_values_price_including_vat: opt.value.display_values_price_including_vat, price_prefix: opt.value.price_prefix};
										var option_type = opt.opttype;
										add_new_attributes(product_id, opt.optid, valid, price_data, option_type);
									});
                                }
                            });
                            ' . ($this->ms['MODULES']['ENABLE_MANUAL_ORDER_CUSTOM_ORDER_PRODUCTS_NAME'] ? '
                            $("#custom_manual_product_name_wrapper").show();
                            $("#custom_manual_product_name").val("");
                            $("#custom_manual_product_name").prop("disabled", false);
                            ' : '') . '
                        }
                        $("#product_name").val(e.object.text);
                        $("#product_qty").val("1");
                    });
                }
                var select2_sb = function(selector_str, placeholder, dropdowncss, ajax_url) {
                    $(selector_str).select2({
                        placeholder: placeholder,
                        createSearchChoice:function(term, data) {
                            if ($(data).filter(function() {
							    return this.text.localeCompare(term)===0;
                            }).length===0) {
                                if (attributesOptions[term] === undefined) {
                                    attributesOptions[term]={id: term, text: term};
                                }
                                return {id:term, text:term};
                            }
                        },
                        minimumInputLength: 0,
                        query: function(query) {
                            if ($(".product_name").length) {
                            	var product_id=$(".product_name").select2("val");
                            } else {
                            	var product_id=$(".product_name_input").select2("val");
                            }
                            //if (attributesSearchOptions[query.term] !== undefined) {
                            //    query.callback({results: attributesSearchOptions[query.term]});
                            //} else {
                                $.ajax(ajax_url, {
                                    data: {
                                        q: query.term + "||pid=" +  product_id
                                    },
                                    dataType: "json"
                                }).done(function(data) {
                                    //attributesSearchOptions[query.term]=data;
                                    query.callback({results: data});
                                });
                            //}
                        },
                        initSelection: function(element, callback) {
                            var id=$(element).val();
                            if ($(".product_name").length) {
                            	var product_id=$(".product_name").select2("val");
                            } else {
                            	var product_id=$(".product_name_input").select2("val");
                            }
                            if (id!=="") {
                                if (attributesOptions[id] !== undefined) {
                                    callback(attributesOptions[id]);
                                } else {
                                    $.ajax(ajax_url, {
                                        data: {
                                            preselected_id: id,
                                            pid: product_id
                                        },
                                        dataType: "json"
                                    }).done(function(data) {
                                        attributesOptions[data.id]={id: data.id, text: data.text};
                                        callback(data);
                                    });
                                }
                            }
                        },
                        formatResult: function(data){
                            if (data.text === undefined) {
                                $.each(data, function(i,val){
                                    return val.text;
                                });
                            } else {
                                return data.text;
                            }
                        },
                        formatSelection: function(data){
                            if (data.text === undefined) {
                                return data[0].text;
                            } else {
                                return data.text;
                            }
                        },
                        dropdownCssClass: dropdowncss,
                        escapeMarkup: function (m) {
                            return m;
                        }
                    }).on("select2-selecting", function(e) {
                        if (e.object.id == e.object.text) {
                            $(this).next().val("1");
                        } else {
                            $(this).next().val("0");
                        }
                    });
                }
                var select2_values_sb = function(selector_str, placeholder, dropdowncss, ajax_url) {
                    $(selector_str).select2({
                        placeholder: placeholder,
                        createSearchChoice:function(term, data) {
                            if ($(data).filter(function() {
                                return this.text.localeCompare(term)===0;
                            }).length===0) {
                                if (attributesValues[term] === undefined) {
                                    attributesValues[term]={id: term, text: term};
                                }
                                return {id:term, text:term};
                            }
                        },
                        minimumInputLength: 0,
                        query: function(query) {
                            if ($(".product_name_input").length) {
                            	var product_id=$(".product_name_input").select2("val");
                            } else {
                            	var product_id=$(".product_name").select2("val");
                            }
                            var current_optid=$(selector_str).parent().prev().prev().children("input").val();
                            //if (attributesSearchValues[query.term + "||" + current_optid] !== undefined) {
                            //    query.callback({results: attributesSearchValues[query.term + "||" + current_optid]});
                            //} else {
                                $.ajax(ajax_url, {
                                    data: {
                                        q: query.term + "||pid=" +  product_id + "||optid=" +  current_optid
                                    },
                                    dataType: "json"
                                }).done(function(data) {
                                    attributesSearchValues[query.term]=data;
                                    query.callback({results: data});
                                });
                            //}
                        },
                        initSelection: function(element, callback) {
                            var product_id = "";
                            if ($(\'input[name="manual_products_id"]\').length) {
                            	product_id=$(\'input[name="manual_products_id"]\').select2("val");
                            	if (typeof product_id == "object") {
                            	    product_id=$(\'input[name="manual_products_id"]\').val();
                            	}
                            }
                            if (product_id=="" && $(\'input[name="products_id"]\').length) {
                                product_id=$(\'input[name="products_id"]\').select2("val");
                            }
                            if (product_id != "") {
                                var id=$(element).val();
                                var optid = $(selector_str).parent().prev().prev().children("input").val();    
                                if (id!=="") {
                                    $.ajax(ajax_url, {
                                        data: {
                                            preselected_id: id,
                                            option_id: optid,
                                            pid: product_id
                                        },
                                        dataType: "json"
                                    }).done(function(data) {
                                        attributesValues[data.id]={id: data.id, text: data.text};
                                        callback(data);
                                    });
                                }
                            }
                        },
                        formatResult: function(data){
                            var tmp_data=data.text.split("||");
                            return tmp_data[0];
                        },
                        formatSelection: function(data){
                            if (data.text === undefined) {
                                return data[0].text;
                            } else {
                                return data.text;
                            }
                        },
                        dropdownCssClass: dropdowncss,
                        escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
                    }).on("select2-selecting", function(e) {
                        if (e.object.id == e.object.text) {
                            $(this).next().val("1");
                            $(this).parent().parent().parent().parent().children().find(".msAttributesField").find("input").val("0.00");
                        } else {
                            var option_id=$(this).parent().prev().prev().find("input[type=hidden]").val();
                            var option_value_id=e.object.id;
                            var price_input_obj=$(this).parent().parent().parent().parent().children().find(".msAttributesField").find("input");
                            var tr_parent=$(this).parent().parent().parent().parent();
                            var tbody_parent=$(tr_parent).parent();
                            //
                            if ($(\'input[name="manual_products_id"]\').val() != ""){
                                var product_id=$(\'input[name="manual_products_id"]\').val();
                            } else if (typeof $(tbody_parent).attr("id")=="undefined") {
                                // add new product
                                var product_id=$(tbody_parent).children("tr:nth-child(2)").children().find("input.product_name").val();
                            } else {
                                // edit existing product
                                var product_id=$(tbody_parent).children().first("tr").children().find("input.product_name_input").val();
                            }
                            $(this).next().val("0");
                            //
                            jQuery.getJSON("' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=ajax_products_attributes_search&tx_multishop_pi1[type]=edit_order') . '",{pid: product_id, optid: option_id, valid: option_value_id}, function(k){
                                if (k.length>0) {
                                    jQuery.each(k, function(idx, optvalid) {
                                        optid=optvalid.optid;
                                        valid=optvalid.valid;
                                        var input_id="#edit_product_price" + optid
                                        var display_price_wrapper_id=".attributesPriceWrapper" + optid                                        
                                        price_data={values_price: optvalid.values_price, display_values_price: optvalid.display_values_price, display_values_price_including_vat: optvalid.display_values_price_including_vat, display_values_price_including_vat_formatted: optvalid.display_values_price_including_vat_formatted, price_prefix: optvalid.price_prefix};
                                        jQuery.each(jQuery(price_input_obj), function(i, v) {
                                            if ($(v).attr("id")=="display_manual_name_excluding_vat") {
                                                $(v).val(price_data.price_prefix + price_data.display_values_price);
                                            }
                                            if ($(v).attr("id")=="display_manual_name_including_vat") {
                                                $(v).val(price_data.price_prefix + price_data.display_values_price_including_vat);
                                            }
                                            if ($(v).attr("id")=="edit_manual_price" || $(v).attr("id")=="edit_product_price") {
                                                $(v).val(price_data.price_prefix + price_data.values_price);
                                            }
                                            if ($(v).attr("id")=="edit_manual_price" || $(v).attr("id")=="edit_product_price") {
                                                $(v).val(price_data.price_prefix + price_data.values_price);
                                            }
                                            if ($(v).attr("id")==input_id || $(v).attr("id")==input_id) {
                                                $(v).val(price_data.price_prefix + price_data.values_price);
                                            }
                                        });
                                        if ($(input_id).length > 0) {
                                            $(input_id).val(price_data.price_prefix + price_data.values_price);
                                        }
                                        if ($(display_price_wrapper_id).length > 0) {
                                            $(display_price_wrapper_id).html(price_data.display_values_price_including_vat_formatted);
                                        }
                                    });
                                } else {
                                    jQuery.each(jQuery(price_input_obj), function(i, v) {
                                        if ($(v).attr("id")=="display_manual_name_excluding_vat") {
                                            $(v).val("0" + decimal_sep + "00");
                                        }
                                        if ($(v).attr("id")=="display_manual_name_including_vat") {
                                            $(v).val("0" + decimal_sep + "00");
                                        }
                                        if ($(v).attr("id")=="edit_manual_price" || $(v).attr("id")=="edit_product_price") {
                                            $(v).val("0" + decimal_sep + "00");
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
                var select2_discount = function(selector_str) {
                    $(selector_str).select2().on("select2-selecting", function(e) {
                    	var product_price=0;
                    	var manual_product=false;
                    	var next_cell=$(selector_str).parentsUntil("td").parent().next();
                    	if ($("#product_price").length) {
                    		product_price=$("#product_price").val();
                    	} else if ($("#manual_product_price").length) {
                    		manual_product=true;
                    		product_price=$("#manual_product_price").val();
                    	}
						if (e.object.id!=\'\' && parseInt(product_price)>0) {
							var discount_percentage=parseInt(e.object.id);
							var current_price=parseFloat(product_price);
							var discount_amount=parseFloat((current_price/100)*discount_percentage);
							var price_after_discount=parseFloat(current_price-discount_amount);
							if (!manual_product) {
							    if (discount_percentage==100) {
							        $("#display_name_discount_excluding_vat").val($("#display_name_excluding_vat").val());
                                    $("#product_discount_amount").val(product_price);
							    } else {
                                    $("#display_name_discount_excluding_vat").val(discount_amount);
                                    $("#product_discount_amount").val(discount_amount);
                                }
                                priceEditRealtimeCalc(true, $("#display_name_discount_excluding_vat"), "#product_tax");
                                if (discount_percentage==100) {
                                    $("#display_name_discount_including_vat").val($("#display_name_including_vat").val());
                                }
                                if (SHOW_PRICES_INCLUDING_VAT) {
                                    var display_name_including_vat = parseFloat($("#display_name_including_vat").val());
                                    var price_discount_include_vat= parseFloat($("#display_name_discount_including_vat").val());
                                    var total_incl_vat=parseFloat(display_name_including_vat-price_discount_include_vat);
                                    var price_split=$.number(total_incl_vat, 2, ".", "").toString().split(".");
                                } else {
                                    var price_split=price_after_discount.toString().split(decimal_sep);
                                }
                                if (price_split[1]==undefined) {
                                    price_split[1]=\'-\';
                                }
                                $(next_cell).find("span.amountWrapper > span.amount").html(price_split[0] + decimal_sep);
                                $(next_cell).find("span.amountWrapper > span.amount_cents").html(price_split[1]);
                            } else {
                                if (!$(next_cell).hasClass("cellPrice")) {
                                    $(next_cell).addClass("cellPrice")
                                }
                                //$(next_cell).empty();
                                //$(next_cell).html(price_after_discount_format);
                                $("#manual_display_name_discount_excluding_vat").val(discount_amount);
                                $("#manual_product_discount_amount").val(discount_amount);
                                priceEditRealtimeCalc(true, $("#manual_display_name_discount_excluding_vat"), "#manual_product_tax");
                                if (SHOW_PRICES_INCLUDING_VAT) {
                                    var display_name_including_vat = parseFloat($("#display_manual_name_including_vat").val());
                                    var price_discount_include_vat= parseFloat($("#manual_display_name_discount_including_vat").val());
                                    var total_incl_vat=parseFloat(display_name_including_vat-price_discount_include_vat);
                                    var price_split=$.number(total_incl_vat, 2, ".", "").toString().split(".");
                                } else {
                                    var price_split=price_after_discount.toString().split(decimal_sep);
                                }
                                if (price_split[1]==undefined) {
                                    price_split[1]=\'-\';
                                }
                                if ($(next_cell).html()=="" || $(next_cell).html()=="&nbsp;") {
                                    $(next_cell).append($("td.cellPrice").html());
                                }
                                $(next_cell).find("span.amountWrapper > span.amount").html(price_split[0] + decimal_sep);
                                $(next_cell).find("span.amountWrapper > span.amount_cents").html(price_split[1]);
                            }
                            // plan for removal
							/*
							jQuery.getJSON("' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_product_discount_price') . '",{discount_percentage: e.object.id, current_price: product_price, qty: 1}, function(d){
								if (d.status==\'OK\') {
									if (!manual_product) {
										$(next_cell).empty();
										$(next_cell).html(d.price_after_discount_format);
										$("#display_name_discount_excluding_vat").val(d.discount_amount);
										$("#product_discount_amount").val(d.discount_amount);
									} else {
										if (!$(next_cell).hasClass("cellPrice")) {
											$(next_cell).addClass("cellPrice")
										}
										$(next_cell).empty();
										$(next_cell).html(d.price_after_discount_format);
										$("#manual_display_name_discount_excluding_vat").val(d.discount_amount);
										$("#manual_product_discount_amount").val(d.discount_amount);
									}
								}
                            });
                            */
						}
                    });
                }
                // eof autocomplete for option
                $("#edit_discount_percentage").select2({
                    width:"140px"
                });
                ' . (($this->get['action'] == 'edit_order' && isset($this->get['edit_product']) && $this->get['edit_product'] > 0) ? '
                select2_cn("#categories_filter_id", "categories", "categories_name_input", "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getTree') . '");
                select2_pn(".product_name_input", "product", "product_name_input", "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_products') . '");
                $.each($(".edit_product_manual_option"), function(i, v){
                    select2_sb("#" + $(v).attr("id"), "' . $this->pi_getLL('admin_label_option') . '", "edit_product_manual_option", "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_attributes_options') . '");
                });
                $.each($(".edit_product_manual_values"), function(i, v){
                    var select2_element_id="#" + $(v).attr("id");
                    select2_values_sb(select2_element_id, "' . $this->pi_getLL('admin_value') . '", "edit_product_manual_values_class", "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_attributes_values') . '");
                });
                select2_discount("#product_discount_percentage");
                ' : '') . '
                var add_new_attributes = function(product_id, optid_value, optvalid_value, price_data, option_type) {
                    var d = new Date();
                    var n = d.getTime();
                    var row_class=$(\'#last_edit_product_row\').prev("tr").attr("class");
                    var manual_attributes_selectbox = \'<div class="product_attributes_wrapper">\';
                    manual_attributes_selectbox += \'<span class="product_attributes_option">\';
                    if (optid_value != "") {
                        manual_attributes_selectbox += \'<input type="hidden" class="edit_product_manual_option\' + n + \' edit_manual_attributes_input" name="edit_manual_option[]" style="width:187px" value="\' +  optid_value + \'"/>\';
                    } else {
                        manual_attributes_selectbox += \'<input type="hidden" class="edit_product_manual_option\' + n + \' edit_manual_attributes_input" name="edit_manual_option[]" style="width:187px" value=""/>\';
                    }
                    manual_attributes_selectbox += \'<input type="hidden" name="is_manual_option[]"value="0"/>\';
                    manual_attributes_selectbox += \'</span>\';
                    manual_attributes_selectbox += \'<span> : </span>\';
                    manual_attributes_selectbox += \'<span class="product_attributes_values">\';
                    if (optvalid_value != "") {
                        manual_attributes_selectbox += \'<input type="hidden" class="edit_product_manual_values\' + n + \' edit_manual_attributes_input" name="edit_manual_values[]" style="width:187px" value="\' +  optvalid_value + \'"/>\';
                    } else {
                        manual_attributes_selectbox += \'<input type="hidden" class="edit_product_manual_values\' + n + \' edit_manual_attributes_input" name="edit_manual_values[]" style="width:187px"/>\';
                    }
                    manual_attributes_selectbox += \'<input type="hidden" name="is_manual_value[]"value="0"/>\';
                    manual_attributes_selectbox += \'</span>\';
                    manual_attributes_selectbox += \'</div>\';';
                $tmpcontent .= '
                    var number_class_display = "attributesPriceInputDisplay" + optid_value + optvalid_value;
                    var number_class_real = "attributesPriceInputReal" + optid_value + optvalid_value;
                    var manual_attributes_price = \'<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" id="display_manual_name_excluding_vat" name="display_name_excluding_vat" class="form-control msManualOrderProductPriceExcludingVat priceInputDisplay \' + number_class_display + \'" value="\' + decimalCrop(price_data.display_values_price) + \'" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('excluding_vat') . '</span></div></div>\';
                    manual_attributes_price += \'<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">' . mslib_fe::currency() . '</span><input type="text" name="display_name" id="display_manual_name_including_vat" class="form-control msManualOrderProductPriceIncludingVat priceInputDisplay \' + number_class_display + \'" value="\' + decimalCrop(price_data.display_values_price_including_vat) + \'" autocomplete="off"><span class="input-group-addon">' . $this->pi_getLL('including_vat') . '</span></div></div>\';
                    manual_attributes_price += \'<div class="msAttributesField hidden"><input class="priceInputReal text \' + number_class_real + \'" type="hidden" name="edit_manual_price[]" id="edit_product_price" value="\' + price_data.price_prefix + price_data.values_price + \'" /></div>\';';
                $tmpcontent .= '
                    var cloned_row=$(\'#last_edit_product_row\').clone();
                    cloned_row.removeAttr("id");
                    cloned_row.removeAttr("class");
                    cloned_row.addClass(row_class + " manual_new_attributes");
                    $.each(cloned_row.children(), function(i){
                        var current_class=$(this).prop("class");
                        if (current_class=="last_edit_product_row_pname_col") {
                            $(this).empty("");
                            $(this).append(manual_attributes_selectbox);
                        }
                        if (current_class=="last_edit_product_row_pprice_col") {
                            $(this).empty("");
                            $(this).append(manual_attributes_price);
                        }
                        if (current_class=="last_edit_product_row_paction_col") {
                            $(this).empty("");
                            $(this).append(\'<button type="button" class="btn btn-danger btn-sm remove_attributes" value=""><i class="fa fa-minus"></i></button>\');
                        }
                        $(this).removeAttr("style");
                        $(this).removeAttr("class");
                    });
                    $(\'#last_edit_product_row\').before(cloned_row);
                    $(\'input.\' + number_class_real).number(true, 2, \'.\', \'\');
			        $(\'input.\' + number_class_display).number(true, 2, "' . $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'] . '", "' . $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_thousands_point'] . '");

                    select2_sb(".edit_product_manual_option" + n, "' . $this->pi_getLL('admin_label_option') . '", "edit_product_manual_option", "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_attributes_options') . '");
                    select2_values_sb(".edit_product_manual_values" + n, "' . $this->pi_getLL('admin_value') . '", "edit_product_manual_values", "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_attributes_values') . '");
                }
                // manual function for removing the manual attributes
                jQuery(document).ready(function($) {';
                $tmpcontent .= '
                    $(document).on("click", \'#edit_add_attributes, #add_attributes\', function() {
                        add_new_attributes("", "", "", "", "");
                    });
                    $(document).on("click", ".remove_attributes", function(){
                        $(this).parent().parent().remove();
                    });
                });
                </script>';
            }
        }
        $tmpcontent .= '
        </div>
        </div>';
        $count_js_cache_options = count($js_select2_cache_options);
        $count_js_cache_values = count($js_select2_cache_values);
        if ($count_js_cache_options) {
            $js_select2_cache .= implode(";\n", $js_select2_cache_options);
        }
        if ($count_js_cache_values) {
            if ($count_js_cache_options) {
                $js_select2_cache .= ";\n";
            }
            $js_select2_cache .= implode(";\n", $js_select2_cache_values) . ";\n";
        }
        $js_select2_cache .= '</script>' . "\n";
        $GLOBALS['TSFE']->additionalHeaderData['js_select2_cache'] = $js_select2_cache;
        // order products eol
        $editOrderFormFieldset[] = $tmpcontent;
        $tmpcontent = '';
        // hook for adding new fieldsets into edit_order
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersFieldset'])) {
            // hook
            $params = array(
                    'editOrderFormFieldset' => &$editOrderFormFieldset,
                    'orders' => &$orders
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersFieldset'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
            // hook oef
        }
        $tabs['Order_Details'] = array(
                $this->pi_getLL('order_details'),
                implode('', $editOrderFormFieldset)
        );
        // order details tab eof
        // order memo/status tab
        $order_status_tab_content = array();
        $tmpcontent = '';
        $order_status_input = '';
        $all_orders_status = mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
        if (is_array($all_orders_status) and count($all_orders_status)) {
            $order_status_input .= '<div class="col-md-10"><select name="order_status" class="form-control">
            <option value="">' . $this->pi_getLL('choose') . '</option>
            ';
            foreach ($all_orders_status as $row) {
                if ($this->get['tx_multishop_pi1']['is_manual'] == '1' && $this->get['action'] == 'edit_order' && $orders['orders_status_id'] == 0) {
                    $order_status_input .= '<option value="' . $row['id'] . '" ' . (($row['default_status'] > 0) ? 'selected' : '') . '>' . $row['name'] . '</option>' . "\n";
                } else {
                    $order_status_input .= '<option value="' . $row['id'] . '" ' . (($orders['orders_status_id'] == $row['id']) ? 'selected' : '') . '>' . $row['name'] . '</option>' . "\n";
                }
            }
            $order_status_input .= '</select></div>';
        }
        if ($orders['expected_delivery_date']) {
            $format_locale = date("d-m-Y", $orders['expected_delivery_date']);
            $format_intl = date("Y-m-d", $orders['expected_delivery_date']);
            if ($this->ms['MODULES']['ADD_HOURS_TO_EDIT_ORDER_EXPECTED_DELIVERY_DATE'] == '1') {
                $format_locale = date("d-m-Y H:i:s", $orders['expected_delivery_date']);
                $format_intl = date("Y-m-d H:i:s", $orders['expected_delivery_date']);
            }
            $expected_delivery_date_local = $format_locale;
            $expected_date = $format_intl;
        }
        $order_status_tab_content['order_status'] = '<div class="form-group"><label for="order_status" class="control-label col-md-2">' . $this->pi_getLL('order_status') . '</label>' . $order_status_input . '</div>';
        $order_status_tab_content['expected_delivery_date'] = '<div class="form-group">
            <label for="expected_delivery_date" class="control-label col-md-2">' . $this->pi_getLL('expected_delivery_date') . '</label>
            <div class="col-md-10">
	            <input type="text" name="expected_delivery_date_local" class="form-control" id="expected_delivery_date_local" value="' . $expected_delivery_date_local . '" autocomplete="off" >
	            <input name="expected_delivery_date" id="expected_delivery_date" type="hidden" value="' . $expected_date . '" />
            </div>
        </div>';
        $order_status_tab_content['track_and_trace_code'] = '<div class="form-group">
            <label for="track_and_trace_code" class="control-label col-md-2">' . $this->pi_getLL('track_and_trace_code') . '</label>
            <div class="col-md-10">
            	<input class="form-control" name="track_and_trace_code" type="text" value="' . mslib_fe::RemoveXSS($orders['track_and_trace_code']) . '" />
            </div>
        </div>';
        if (!empty($orders['track_and_trace_link'])) {
            $order_status_tab_content['track_and_trace_code'] .= '<div class="form-group">
                <label for="track_and_trace_code" class="control-label col-md-2">' . $this->pi_getLL('track_and_trace_link') . '</label>
                <div class="col-md-10">
                    <p class="form-control-static"><a href="' . $orders['track_and_trace_link'] . '" target="_blank">' . $orders['track_and_trace_link'] . '</a></p>
                </div>
            </div>';
        }
        $order_status_tab_content['order_memo'] = '<div class="form-group">
            <label for="order_memo" class="control-label col-md-2">' . $this->pi_getLL('order_memo') . '</label>
            <div class="col-md-10">
            <textarea name="order_memo" id="order_memo" class="mceEditor" rows="4">' . mslib_fe::RemoveXSS($orders['order_memo']) . '</textarea>
            ' . ($orders['memo_crdate'] > 0 ? '<span class="memo_last_modified">' . $this->pi_getLL('order_memo_last_modified') . ': ' . strftime("%a. %x %X", $orders['memo_crdate']) . '</span>' : '') . '
            </div>
        </div>';
        $order_status_tab_content['customer_notified'] = '<div class="form-group">
            <label for="customer_notified" class="control-label col-md-2">' . $this->pi_getLL('send_email_to_customer') . '</label>
            <div class="col-md-10">
	            <div class="radio radio-success radio-inline">
	            	<input name="customer_notified" class="customer_notified_n" type="radio" id="value0" value="0" /><label for="value0">' . $this->pi_getLL('no') . '</label>
	            </div>
	            <div class="radio radio-success radio-inline">
		            <input name="customer_notified" class="customer_notified_y" id="customer_notified" type="radio" value="1" checked /><label for="customer_notified">' . $this->pi_getLL('yes') . '</label>
	            </div>
            </div>
        </div>';
        $order_status_tab_content['comments'] = '<div class="form-group">
            <label for="comments" class="control-label col-md-2">' . $this->pi_getLL('email_message') . '</label>
            <div class="col-md-10">
            <textarea name="comments" id="comments" class="mceEditor" rows="4"></textarea>
            </div>
        </div>';
        $GLOBALS['TSFE']->additionalHeaderData[] = '
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $(document).on("keydown keyup", "#shipping_method_costs", function(){
                    $("#shipping_costs_manual").val("1"); 
                });
                ' . ($this->ms['MODULES']['ADD_HOURS_TO_EDIT_ORDER_EXPECTED_DELIVERY_DATE'] == '1' ? '
                $("#expected_delivery_date_local").datetimepicker({
                    dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'dd/mm/yy') . '",
                    altField: "#expected_delivery_date",
                    altFormat: "yy-mm-dd",
                    changeMonth: true,
                    changeYear: true,
                    showOtherMonths: true,
                    yearRange: "' . (date("Y")) . ':' . (date("Y") + 2) . '",
                    timeFormat: \'HH:mm:ss\',
                    altFieldTimeOnly: false,
                    altTimeFormat: "HH:mm:ss"
                });
                ' : '
                $("#expected_delivery_date_local").datepicker({
                    dateFormat: "dd-mm-yy",
                    minDate: 0,
                    altField: "#expected_delivery_date",
                    altFormat: "yy-mm-dd",
                    changeMonth: true,
                    changeYear: true,
                    showOtherMonths: true,
                    yearRange: "' . (date("Y")) . ':' . (date("Y") + 2) . '"
                });
                ') . '
            });
         </script>
         ';
        // load the status history
        //$str="select * from tx_multishop_orders_status_history order by name";
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_orders_status_history', // FROM ...
                'orders_id=\'' . $orders['orders_id'] . '\'', // WHERE.
                '', // GROUP BY...
                'orders_status_history_id desc', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        $order_status_history_items = array();
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
            $order_status_history_items[] = $row;
        }
        $order_status_tab_content['order_history_table'] = '';
        if (count($order_status_history_items) > 0) {
            $order_status_tab_content['order_history_table'] .= '
            <div class="panel panel-default" id="order_status_history">
			<div class="panel-heading"><h3>' . $this->pi_getLL('order_status_history') . '</h3></div>
			<div class="panel-body">
            <table class="table table-striped table-bordered msadmin_border">
            <thead>
            <tr>
                <th>' . $this->pi_getLL('status') . '</th>
                <th>' . $this->pi_getLL('old_status') . '</th>
                <th>' . $this->pi_getLL('date') . '</th>
                <th>' . $this->pi_getLL('customer_notified') . '</th>
                <th>' . $this->pi_getLL('updated_by') . '</th>
                <th>' . $this->pi_getLL('requester_ip_address') . '</th>
            </tr>
            </thead>
            <tbody>
            ';
            foreach ($order_status_history_items as $row) {
                if (!$tr_type or $tr_type == 'even') {
                    $tr_type = 'odd';
                } else {
                    $tr_type = 'even';
                }
                $cr_user = mslib_fe::getUser($row['cruser_id']);
                $username = array();
                if ($cr_user['username']) {
                    $username[] = $cr_user['username'];
                }
                if ($cr_user['email'] && $cr_user['email'] != $cr_user['username']) {
                    if (count($username)) {
                        $username[] = '(' . $cr_user['email'] . ')';
                    } else {
                        $username[] = $cr_user['email'];
                    }
                }
                $old_status_name = $all_orders_status[$row['old_value']]['name'];
                if (!$old_status_name) {
                    $old_status_name = $this->pi_getLL('admin_label_unknown_order_status');
                }
                $status_name = $all_orders_status[$row['new_value']]['name'];
                if (!$status_name) {
                    $status_name = $this->pi_getLL('admin_label_unknown_order_status');
                }
                $order_status_tab_content['order_history_table'] .= '<tr class="odd">
                    <td><strong>' . $status_name . '</strong></td>
                    <td>' . $old_status_name . '</td>
                    <td>' . strftime("%a. %x %X", $row['crdate']) . '</td>
                    <td align="center">' . ($row['customer_notified'] ? $this->pi_getLL('yes') : $this->pi_getLL('no')) . '</td>
                    <td align="center">' . implode(' ', $username) . '</td>
                    <td align="center">' . $row['requester_ip_addr'] . '</td>
                </tr>
                ';
                if ($row['comments']) {
                    $order_status_tab_content['order_history_table'] .= '
                    <tr class="even">
                        <td colspan="6">' . $row['comments'] . '</td>
                    </tr>
                    ';
                }
            }
            $order_status_tab_content['order_history_table'] .= '
            </tbody>
            </table>
            </div></div>
            ';
            // hook for adding new tabs into edit_order
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersTabsOrderHistoryTablePostProc'])) {
                // hook
                $params = array(
                        'all_orders_status' => $all_orders_status,
                        'order_status_history_items' => $order_status_history_items,
                        'order_history_table' => &$order_status_tab_content['order_history_table']
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersTabsOrderHistoryTablePostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
                // hook oef
            }
        }
        if (count($order_status_tab_content)) {
            $tmpcontent .= implode("\n", $order_status_tab_content);
        }
        // load the status history eof
        $tabs['Order_Status'] = array(
                $this->pi_getLL('order_status'),
                $tmpcontent
        );
        // order status tab eof
        $page_title = $this->pi_getLL('order_details');
        // hook for adding new tabs into edit_order
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersTabs'])) {
            // hook
            $params = array(
                    'tabs' => &$tabs,
                    'order_status_tab_content' => &$order_status_tab_content,
                    'orders' => &$orders,
                    'page_title' => &$page_title,
                    'all_orders_status' => $all_orders_status,
                    'order_status_history_items' => $order_status_history_items,
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersTabs'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
            // hook oef
        }
        // Instantiate admin interface object
        $objRef = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj('EXT:multishop/pi1/classes/class.tx_mslib_admin_interface.php:&tx_mslib_admin_interface');
        $objRef->init($this);
        $objRef->setInterfaceKey('admin_edit_order');
        // Header buttons
        $headerButtons = array();
        if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE'] || $this->ms['MODULES']['PACKING_LIST_PRINT']) {
            if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
                $headingButton = array();
                $headingButton['btn_class'] = 'btn btn-primary';
                $headingButton['fa_class'] = 'fa fa-file';
                $headingButton['title'] = $this->pi_getLL('invoice');
                $headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $order['orders_id'] . '&action=edit_order&print=invoice');
                if ($this->ms['MODULES']['INVOICE_PDF_DIRECT_LINK_FROM_ORDERS_LISTING']) {
                    $headingButton['target'] = '_blank';
                }
                $headerButtons['header_invoice_btn'] = $headingButton;
            }
            if ($this->ms['MODULES']['PACKING_LIST_PRINT']) {
                $headingButton = array();
                $headingButton['btn_class'] = 'btn btn-primary';
                $headingButton['fa_class'] = 'fa fa-file';
                $headingButton['title'] = $this->pi_getLL('packing_list');
                if ($this->ms['MODULES']['PACKINGSLIP_PDF_DIRECT_LINK_FROM_ORDERS_LISTING']) {
                    $headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=download_packingslip&tx_multishop_pi1[order_id]=' . $order['orders_id']);
                    $headingButton['target'] = '_blank';
                } else {
                    $headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $order['orders_id'] . '&action=edit_order&print=packing');
                }
                $headerButtons['header_packingslip_btn'] = $headingButton;
            }
        }
        // create new order for same client as active order
        if ($this->ms['MODULES']['CREATE_NEW_ORDER_FROM_EDIT_ORDER']) {
            $headingButton = array();
            $headingButton['btn_class'] = 'btn btn-primary header_create_new_order_btn';
            $headingButton['fa_class'] = 'fa fa-check-circle';
            $headingButton['title'] = $this->pi_getLL('admin_label_create_order');
            $headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $order['orders_id'] . '&action=edit_order&tx_multishop_pi1[new_order]=true');
            $headingButton['attributes'] = '';
            $headingButton['target'] = '_blank';
            $headerButtons['header_create_new_order_btn'] = $headingButton;
        }
        $headingButton = array();
        $headingButton['btn_class'] = 'btn btn-success';
        $headingButton['fa_class'] = 'fa fa-check-circle';
        $headingButton['title'] = ($this->get['action'] == 'edit_order') ? $this->pi_getLL('update') : $this->pi_getLL('save');
        $headingButton['href'] = '#';
        $headingButton['attributes'] = 'id="topBtnSave" onclick="$(\'#btnSave\').click(); return false;"';
        $headerButtons['header_save_update_btn'] = $headingButton;
        //
        $headingButton = array();
        $headingButton['btn_class'] = 'btn btn-success';
        $headingButton['fa_class'] = 'fa fa-check-circle';
        $headingButton['title'] = ($this->get['action'] == 'edit_order') ? $this->pi_getLL('admin_update_close') : $this->pi_getLL('admin_save_close');
        $headingButton['href'] = '#';
        $headingButton['attributes'] = 'id="topBtnSaveClose" onclick="$(\'#btnSaveClose\').click(); return false;"';
        $headerButtons['header_save_close_btn'] = $headingButton;
        // Set header buttons through interface class so other plugins can adjust it
        $objRef->setHeaderButtons($headerButtons);
        // Get header buttons through interface class so we can render them
        $interfaceHeaderButtons = $objRef->renderHeaderButtons();
        $tmpcontent = '';
        if ($this->ms['MODULES']['ORDER_EDIT'] and $settings['enable_edit_orders_details']) {
            $new_manual_product_js = '
            $(document).on("click", "#button_manual_new_product", function(e) {
                e.preventDefault();
                if ($(".manual_add_new_product").is(":hidden")) {
                    $(".manual_add_new_product").show();
                    $("#button_manual_new_product").hide();
                } else {
                    $(".manual_add_new_product").hide();
                }
                $(".order_product_action").hide();
                select2_cn("#categories_filter_id", "categories", "categories_name_input", "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getTree') . '");
                select2_pn(".product_name", "product", "product_name", "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_products') . '");
                select2_discount("#manual_product_discount_percentage");
            });';
        }
        $content .= '
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            ' . $new_manual_product_js . '
            if ($(\'#shipping_method_sb\').val()!=\'\') {
                $(\'#shipping_cost_input_wrapper\').show();
            }
            if ($(\'#payment_method_sb\').val()!=\'\') {
                $(\'#payment_cost_input_wrapper\').show();
            }
            $(document).on(\'change\', \'#shipping_method_sb\', function(){
                if ($(this).val()!=\'\') {
                    $(\'#shipping_cost_input_wrapper\').show();
                } else {
                    $(\'#shipping_cost_input_wrapper\').hide();
                }
            });
            $(document).on(\'change\', \'#payment_method_sb\', function(){
                if ($(this).val()!=\'\') {
                    $(\'#payment_cost_input_wrapper\').show();
                } else {
                    $(\'#payment_cost_input_wrapper\').hide();
                }
            });
            $(\'.change_order_product_status\').change(function() {
                var order_pid = $(this).attr("rel");
                var orders_status_id = $("option:selected", this).val();
                var orders_status_label = $("option:selected", this).text();
                if (confirm("Do you want to change orders product id: " + order_pid + " to status: " + orders_status_label)) {
                    $.ajax({
                        type: "POST",
                        url: "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_update_order_product_status') . '",
                        dataType: \'json\',
                        data: "tx_multishop_pi1[orders_id]=' . $order['orders_id'] . '&tx_multishop_pi1[order_product_id]=" + order_pid + "&tx_multishop_pi1[orders_status_id]=" + orders_status_id,
                        success: function(msg) {}
                    });
                }
            });
            ' . (($this->ms['MODULES']['ORDER_EDIT'] && $settings['enable_edit_orders_details']) ? '
            var result = jQuery(".orders_products_listing").sortable({
                cursor:     "move",
                items: "tbody.sortbody",
                //axis:       "y",
                update: function(e, ui) {
                    href = "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=sort_orders_products') . '";
                    jQuery(this).sortable("refresh");
                    sorted = jQuery(this).sortable("serialize", "id");
                    jQuery.ajax({
                            type:   "POST",
                            url:    href,
                            data:   sorted,
                            success: function(msg) {
                                    //do something with the sorted data
                            }
                    });
                }
            });
            ' : '') . '
            var url_relatives = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_ajax_product_relatives') . '";
            var url = document.location.toString();
            if (url.match("#")) {
                $(\'.nav-tabs a[href="#\' + url.split("#")[1] + \'"]\').tab("show");
            } else {
                $(".nav-tabs a:first").tab("show");
            }
            // Change hash for page-reload
            $(".nav-tabs a").on("shown.bs.tab", function (e) {
                window.location.hash = e.target.hash;
                $("body,html,document").scrollTop(0);
            });

            $("#load").hide();
            $().ajaxStart(function() {
                $("#load").show();
                $("#has").hide();
            }).ajaxStop(function() {
                $("#load").hide();
                $("#has").show();
            });
            $("#filter").click(function(){
                if($("#key").val().length === 0 ){
                    var keywords = 2;
                    //alert("hore");
                } else {
                    var keywords = $("#key").val();
                }
                $.ajax({
                    type: "POST",
                    url: url_relatives,
                    data: {keypas:keywords,pid:"' . $_REQUEST['pid'] . '"},
                    success: function(data) {
                        jQuery("#has").html(data);
                    }
                });
            });
            $(document).on("keyup", ".msOrderManualProductPriceExcludingVat", function(e) {
            	if (e.keyCode!=9) {
                	priceEditRealtimeCalc(true, $(this), "#manual_product_tax");
                }
            });
            $(document).on("keyup", ".msOrderManualProductPriceIncludingVat", function(e) {
                if (e.keyCode!=9) {
                	priceEditRealtimeCalc(false, $(this), "#manual_product_tax");
                }
            });
            
            $(document).on("keyup", ".msOrderProductPriceExcludingVat", function(e) {
            	if (e.keyCode!=9) {
                	priceEditRealtimeCalc(true, $(this), "#product_tax");
                }
            });
            $(document).on("keyup", ".msOrderProductPriceIncludingVat", function(e) {
                if (e.keyCode!=9) {
                	priceEditRealtimeCalc(false, $(this), "#product_tax");
                }
            });
            $("#product_tax").change(function () {
                $(".msOrderProductPriceExcludingVat").each(function (i) {
                    priceEditRealtimeCalc(true, $(this), "#product_tax", "product_tax");
                });
                $(".msManualOrderProductPriceExcludingVat").each(function (i) {
                    priceEditRealtimeCalc(true, $(this), "#product_tax", "product_tax");
                });
            });
            $(document).on("keyup", ".msManualOrderProductPriceExcludingVat", function(e) {
            	if (e.keyCode!=9) {
                	priceEditRealtimeCalc(true, $(this), "#manual_product_tax");
                }
            });
            $(document).on("keyup", ".msManualOrderProductPriceIncludingVat", function(e) {
            	if (e.keyCode!=9) {
                	priceEditRealtimeCalc(false, $(this), "#manual_product_tax");
                }
            });
            $("#manual_product_tax").change(function () {
                $(".msManualOrderProductPriceExcludingVat").each(function (i) {
                    priceEditRealtimeCalc(true, $(this), "#manual_product_tax", "product_tax");
                });
            });
            $(document).on("click", "#switch_to_discount_amount", function(e){
                e.preventDefault();
                $(this).hide();
                $("#edit_discount_value").prop("disabled", false);
                $("#edit_discount_percentage").select2("destroy");
                $("#edit_discount_percentage").prop("disabled", true);
                $("#switch_to_discount_percentage").show();
                $("#discount_percentage_wrapper").hide();
                $("#discount_amount_wrapper").show();
            });
            $(document).on("click", "#switch_to_discount_percentage", function(e){
                e.preventDefault();
                $(this).hide();
                $("#edit_discount_value").prop("disabled", true);
                $("#edit_discount_percentage").prop("disabled", false);
                $("#edit_discount_percentage").select2({
                    width:"140px"
                });
                $("#switch_to_discount_amount").show();
                $("#discount_percentage_wrapper").show();
                $("#discount_amount_wrapper").hide();
            });
            $(document).on("change", "#edit_discount_value", function() {
                var discount_amount=parseFloat($(this).val());
                var subtotal=parseFloat($("#hidden_subtotal").val());
                var subtotal_excl_vat=parseFloat($("#hidden_subtotal_excl_vat").val());
                var shipping_tax=parseFloat($("#hidden_shipping_tax").val());
                var payment_tax=parseFloat($("#hidden_payment_tax").val());
                var total_tax=parseFloat($("#hidden_total_orders_tax").val());
                var product_tax=parseFloat($("#hidden_orders_products_tax").val());
                var percentage=parseInt((discount_amount/subtotal)*100);
                var shipping_costs=parseFloat($("#shipping_method_costs").val());
                var payment_costs=parseFloat($("#payment_method_costs").val());
                var new_grand_total=$.number(parseFloat((subtotal - discount_amount) + shipping_costs + payment_costs), 2, ".");
                var grand_total_split=new_grand_total.toString().split(".");
                // discount percentage
                $("#edit_discount_percentage").val("");
                $(".grand_total").find(".amount").empty().html(grand_total_split[0] + decimal_sep);
                $(".grand_total").find(".amount_cents").empty().html(grand_total_split[1]);
                // recalculate tax
                var sub_total_tax=parseFloat(((subtotal-subtotal_excl_vat)/100)*(100-percentage));
                var total_tax_split=parseFloat(sub_total_tax+shipping_tax+payment_tax);
                total_tax_split=$.number(total_tax_split, 2, ".").toString().split(".");
                // refill the tax value
                $(".total_orders_tax").find(".amount").empty().html(total_tax_split[0] + decimal_sep);
                $(".total_orders_tax").find(".amount_cents").empty().html(total_tax_split[1]);
            });
            $(document).on("change", "#edit_discount_percentage", function(){
                var percentage=parseInt($(this).val());
                var subtotal=parseFloat($("#hidden_subtotal").val());
                var subtotal_excl_vat=parseFloat($("#hidden_subtotal_excl_vat").val());
                var shipping_tax=parseFloat($("#hidden_shipping_tax").val());
                var payment_tax=parseFloat($("#hidden_payment_tax").val());
                var total_tax=parseFloat($("#hidden_total_orders_tax").val());
                var product_tax=parseFloat($("#hidden_orders_products_tax").val());
                var shipping_costs=parseFloat($("#shipping_method_costs").val());
                var payment_costs=parseFloat($("#payment_method_costs").val());
                var discount_amount=parseFloat((subtotal/100)*percentage);
                var new_grand_total=$.number(parseFloat((subtotal - discount_amount) + shipping_costs + payment_costs), 2, ".");
                var grand_total_split=new_grand_total.toString().split(".");
                // real discount value
                $("#edit_discount_value").val(discount_amount);
                $(".grand_total").find(".amount").empty().html(grand_total_split[0] + decimal_sep);
                $(".grand_total").find(".amount_cents").empty().html(grand_total_split[1]);
                // recalculate tax
                var sub_total_tax=parseFloat(((subtotal-subtotal_excl_vat)/100)*(100-percentage));
                var total_tax_split=parseFloat(sub_total_tax+shipping_tax+payment_tax);
                total_tax_split=$.number(total_tax_split, 2, ".").toString().split(".");
                // refill the tax value
                $(".total_orders_tax").find(".amount").empty().html(total_tax_split[0] + decimal_sep);
                $(".total_orders_tax").find(".amount_cents").empty().html(total_tax_split[1]);
            });
        });
        </script>
        <div class="panel panel-default">
        <div class="panel-heading">
        	<h3>' . htmlspecialchars($page_title) . '</h3>
        	' . $interfaceHeaderButtons . '
        </div>
        <div class="panel-body">
        <div id="tab-container" class="msAdminEditOrder">
            <ul class="nav nav-tabs" role="tablist">';
        foreach ($tabs as $key => $value) {
            $count++;
            $content .= '<li' . (($count == 1) ? '' : '') . ' role="presentation"><a href="#' . $key . '" aria-controls="profile" role="tab" data-toggle="tab">' . $value[0] . '</a></li>';
        }
        $content .= '</ul>
            <div class="tab-content">
            <form class="form-horizontal admin_product_edit blockSubmitForm" name="admin_edit_order_form" id="admin_edit_order_form" method="post" action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&action=edit_order&orders_id=' . $_REQUEST['orders_id']) . '" enctype="multipart/form-data">
            <input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="' . $subpartArray['###VALUE_REFERRER###'] . '" />';
        $count = 0;
        foreach ($tabs as $key => $value) {
            $count++;
            $content .= '
                <div role="tabpanel" id="' . $key . '" class="tab-pane">
                    ' . $value[1] . '
                </div>';
        }
        $content .= $save_block . '
                </form>
            </div>
        </div></div></div>';
    } else {
        $content .= '<div class="alert alert-danger"><h3>' . $this->pi_getLL('order_not_found') . '</h3></div>';
    }
}
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersMainPostProc'])) {
    // hook
    $params = array();
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_order.php']['adminEditOrdersMainPostProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
    // hook oef
}
?>
