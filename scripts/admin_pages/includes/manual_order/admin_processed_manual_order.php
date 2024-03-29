<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
//add order
if ($this->post['proceed_order']) {
    $unique_id = $this->post['email'];
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderFEUsersPreProc'])) {
        // hook
        $params = array(
            'unique_id' => &$unique_id
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderFEUsersPreProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
        // hook eof
    }
    if ($this->post['customer_id']) {
        $user = mslib_fe::getUser($this->post['customer_id']);
        if ($user['uid']) {
            $customer_id = $user['uid'];
            $this->post = array_merge($this->post, $user);
            $this->post['tx_multishop_pi1']['telephone'] = $this->post['telephone'];
        }
    } else {
        $str = "SELECT uid from fe_users where (username='" . addslashes($unique_id) . "')";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
            // use current account
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
            $customer_id = $row['uid'];
        }
    }
    if (!$customer_id) {
        $username = '';
        if (!empty($this->post['tx_multishop_pi1']['telephone'])) {
            $username = $this->post['tx_multishop_pi1']['telephone'];
        }
        if (!empty($this->post['email'])) {
            $username = $this->post['email'];
        }
        $insertArray = array();
        $billing_gender = '0';
        if (!$this->post['gender'] || $this->post['gender'] == 'm') {
            $billing_gender = '0';
        } else if ($this->post['gender'] == 'f') {
            $billing_gender = '1';
        }
        $insertArray['gender'] = $billing_gender;
        $insertArray['page_uid'] = $this->shop_pid;
        $insertArray['company'] = $this->post['company'];
        $insertArray['name'] = $this->post['first_name'] . ' ' . $this->post['middle_name'] . ' ' . $this->post['last_name'];
        $insertArray['name'] = preg_replace('/\s+/', ' ', $insertArray['name']);
        $insertArray['name'] = str_replace('  ', ' ', $insertArray['name']);
        $insertArray['first_name'] = $this->post['first_name'];
        $insertArray['middle_name'] = $this->post['middle_name'];
        $insertArray['last_name'] = $this->post['last_name'];
        $insertArray['username'] = $unique_id;
        $insertArray['email'] = $this->post['email'];
        $insertArray['building'] = $this->post['building'];
        $insertArray['department'] = ($this->post['department'] ? $this->post['department'] : '');
        $insertArray['street_name'] = $this->post['street_name'];
        $insertArray['address_number'] = $this->post['address_number'];
        $insertArray['address_ext'] = $this->post['address_ext'];
        $insertArray['address'] = $insertArray['street_name'] . ' ' . $insertArray['address_number'] . $insertArray['address_ext'];
        $insertArray['address'] = preg_replace('/\s+/', ' ', $insertArray['address']);
        $insertArray['address'] = str_replace('  ', ' ', $insertArray['address']);
        $insertArray['zip'] = $this->post['zip'];
        $insertArray['telephone'] = $this->post['tx_multishop_pi1']['telephone'];
        $insertArray['city'] = $this->post['city'];
        $insertArray['country'] = $this->post['country'];
        $insertArray['password'] = $insertArray['crdate'] = time();
        $insertArray['usergroup'] = $this->conf['fe_customer_usergroup'];
        $insertArray['pid'] = $this->conf['fe_customer_pid'];
        $insertArray['password'] = mslib_befe::getHashedPassword(rand(1000000, 9000000));
        $insertArray['tx_multishop_vat_id'] = $this->post['tx_multishop_vat_id'];
        $insertArray['tx_multishop_coc_id'] = $this->post['tx_multishop_coc_id'];
        $insertArray['tx_multishop_newsletter'] = (!$this->post['tx_multishop_newsletter_manual'] ? 0 : 1);
        $insertArray['crdate'] = time();
        $insertArray['tstamp'] = time();
        $insertArray['last_updated_at'] = time();
        $insertArray = mslib_befe::rmNullValuedKeys($insertArray);
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderFEUsersPreHook'])) {
            // hook
            $params = array(
                    'insertArray' => &$insertArray
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderFEUsersPreHook'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
            // hook eof
        }
        $query = $GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $insertArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($res) {
            $customer_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
        }
    }
    //add to orders
    if ($customer_id) {
        $billing_gender = '';
        if (!is_numeric($this->post['gender'])) {
            $billing_gender = $this->post['gender'];
        } else {
            if (!$this->post['gender'] || $this->post['gender'] == '0') {
                $billing_gender = 'm';
            } else if ($this->post['gender'] == '1') {
                $billing_gender = 'f';
            }
        }
        $order_language_id = 0;
        if (!$user) {
            $user = mslib_fe::getUser($customer_id);
        }
        if ($user['tx_multishop_language']) {
            foreach ($this->languages as $key => $language) {
                $language['lg_iso_2'] = strtolower($language['lg_iso_2']);
                if (strtolower($user['tx_multishop_language']) == $language['lg_iso_2']) {
                    $order_language_id = $language['uid'];
                    break;
                }
            }
        }
        // now add the order
        $insertArray = array();
        $insertArray['customer_id'] = $customer_id;
        $insertArray['language_id'] = $order_language_id;
        $insertArray['page_uid'] = $this->shop_pid;
        $insertArray['status'] = 1;
        $insertArray['billing_company'] = $this->post['company'];
        $insertArray['billing_first_name'] = $this->post['first_name'];
        $insertArray['billing_middle_name'] = $this->post['middle_name'];
        $insertArray['billing_last_name'] = $this->post['last_name'];
        $insertArray['billing_name'] = $this->post['first_name'] . ' ' . $this->post['middle_name'] . ' ' . $this->post['last_name'];
        $insertArray['billing_name'] = preg_replace('/\s+/', ' ', $insertArray['billing_name']);
        $insertArray['billing_name'] = str_replace('  ', ' ', $insertArray['billing_name']);
        $insertArray['billing_email'] = $this->post['email'];
        $insertArray['billing_gender'] = $billing_gender;
        $insertArray['billing_birthday'] = $this->post['birthday'];
        $insertArray['billing_building'] = $this->post['building'];
        $insertArray['billing_department'] = $this->post['department'];
        $insertArray['billing_street_name'] = $this->post['street_name'];
        $insertArray['billing_address_number'] = $this->post['address_number'];
        $insertArray['billing_address_ext'] = $this->post['address_ext'];
        $insertArray['billing_address'] = $insertArray['billing_street_name'] . ' ' . $insertArray['billing_address_number'] . ' ' . $insertArray['billing_address_ext'];
        $insertArray['billing_address'] = preg_replace('/\s+/', ' ', $insertArray['billing_address']);
        $insertArray['billing_address'] = str_replace('  ', ' ', $insertArray['billing_address']);
        $insertArray['billing_room'] = '';
        $insertArray['billing_city'] = $this->post['city'];
        $insertArray['billing_zip'] = $this->post['zip'];
        $insertArray['billing_region'] = '';
        $insertArray['billing_country'] = $this->post['country'];
        $insertArray['billing_telephone'] = $this->post['tx_multishop_pi1']['telephone'];
        $insertArray['billing_mobile'] = $this->post['mobile'];
        $insertArray['billing_fax'] = '';
        $insertArray['billing_vat_id'] = $this->post['tx_multishop_vat_id'];
        $insertArray['billing_coc_id'] = $this->post['tx_multishop_coc_id'];
        $insertArray['delivery_company'] = $this->post['delivery_company'];
        $insertArray['delivery_first_name'] = $this->post['delivery_first_name'];
        $insertArray['delivery_middle_name'] = $this->post['delivery_middle_name'];
        $insertArray['delivery_last_name'] = $this->post['delivery_last_name'];
        $insertArray['delivery_name'] = $this->post['delivery_first_name'] . ' ' . $this->post['delivery_middle_name'] . ' ' . $this->post['delivery_last_name'];
        $insertArray['delivery_name'] = preg_replace('/\s+/', ' ', $insertArray['delivery_name']);
        $insertArray['delivery_name'] = str_replace('  ', ' ', $insertArray['delivery_name']);
        $insertArray['delivery_email'] = $this->post['delivery_email'];
        $insertArray['delivery_gender'] = $this->post['delivery_gender'];
        $insertArray['delivery_street_name'] = $this->post['delivery_street_name'];
        $insertArray['delivery_building'] = $this->post['delivery_building'];
        $insertArray['delivery_department'] = $this->post['delivery_department'];
        $insertArray['delivery_address_number'] = $this->post['delivery_address_number'];
        $insertArray['delivery_address'] = $insertArray['delivery_street_name'] . ' ' . $insertArray['delivery_address_number'] . ' ' . $insertArray['delivery_address_ext'];
        $insertArray['delivery_address'] = preg_replace('/\s+/', ' ', $insertArray['delivery_address']);
        $insertArray['delivery_address'] = str_replace('  ', ' ', $insertArray['delivery_address']);
        $insertArray['delivery_city'] = $this->post['delivery_city'];
        $insertArray['delivery_zip'] = $this->post['delivery_zip'];
        $insertArray['delivery_address_ext'] = $this->post['delivery_address_ext'];
        $insertArray['delivery_room'] = '';
        $insertArray['delivery_region'] = '';
        $insertArray['delivery_country'] = $this->post['delivery_country'];
        $insertArray['delivery_telephone'] = $this->post['delivery_telephone'];
        $insertArray['delivery_mobile'] = $this->post['delivery_mobile'];
        $insertArray['delivery_fax'] = '';
        $insertArray['delivery_vat_id'] = '';
        $insertArray['bill'] = 1;
        $insertArray['crdate'] = time();
        $insertArray['shipping_method'] = $this->post['shipping_method'];
        $insertArray['payment_method'] = $this->post['payment_method'];
        $insertArray['shipping_method_costs'] = $this->post['shipping_method_costs'];
        $insertArray['payment_method_costs'] = $this->post['payment_method_costs'];
        $insertArray['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
        $insertArray['status'] = '';
        // get default orders status
        $status = mslib_fe::getDefaultOrdersStatus($this->sys_language_uid);
        if (is_array($status) && isset($status['id']) && $status['id'] > 0) {
            $insertArray['status'] = $status['id'];
        }
        if (!$this->post['different_delivery_address']) {
            $delivery_address = mslib_fe::getFeUserTTaddressDetails($customer_id, 'delivery');
            if ($delivery_address) {
                if (!$delivery_address['name']) {
                    $delivery_address['name'] = $delivery_address['first_name'] . ' ' . $delivery_address['middle_name'] . ' ' . $delivery_address['last_name'];
                    $delivery_address['name'] = preg_replace('/\s+/', ' ', $delivery_address['name']);
                    $delivery_address['name'] = str_replace('  ', ' ', $delivery_address['name']);
                }
                if (!$delivery_address['address']) {
                    $delivery_address['address'] = $delivery_address['street_name'] . ' ' . $delivery_address['address_number'] . ' ' . $delivery_address['address_ext'];
                    $delivery_address['address'] = preg_replace('/\s+/', ' ', $delivery_address['address']);
                    $delivery_address['address'] = str_replace('  ', ' ', $delivery_address['address']);
                }
                $insertArray['delivery_email'] = $delivery_address['email'];
                $insertArray['delivery_company'] = $delivery_address['company'];
                $insertArray['delivery_first_name'] = $delivery_address['first_name'];
                $insertArray['delivery_middle_name'] = $delivery_address['middle_name'];
                $insertArray['delivery_last_name'] = $delivery_address['last_name'];
                $insertArray['delivery_mobile'] = $delivery_address['mobile'];
                $insertArray['delivery_gender'] = $delivery_address['gender'];
                $insertArray['delivery_building'] = $delivery_address['building'];
                $insertArray['delivery_department'] = $delivery_address['department'];
                $insertArray['delivery_street_name'] = $delivery_address['street_name'];
                $insertArray['delivery_address'] = $delivery_address['address'];
                $insertArray['delivery_address_number'] = $delivery_address['address_number'];
                $insertArray['delivery_address_ext'] = $delivery_address['address_ext'];
                $insertArray['delivery_zip'] = $delivery_address['zip'];
                $insertArray['delivery_city'] = $delivery_address['city'];
                $insertArray['delivery_country'] = $delivery_address['country'];
                $insertArray['delivery_telephone'] = $delivery_address['phone'];
                $insertArray['delivery_name'] = $delivery_address['name'];
            } else {
                $insertArray['delivery_email'] = $insertArray['billing_email'];
                $insertArray['delivery_company'] = $insertArray['billing_company'];
                $insertArray['delivery_first_name'] = $insertArray['billing_first_name'];
                $insertArray['delivery_middle_name'] = $insertArray['billing_middle_name'];
                $insertArray['delivery_last_name'] = $insertArray['billing_last_name'];
                $insertArray['delivery_telephone'] = $insertArray['billing_telephone'];
                $insertArray['delivery_mobile'] = $insertArray['billing_mobile'];
                $insertArray['delivery_gender'] = $insertArray['billing_gender'];
                $insertArray['delivery_building'] = $insertArray['billing_building'];
                $insertArray['delivery_department'] = $insertArray['billing_department'];
                $insertArray['delivery_street_name'] = $insertArray['billing_street_name'];
                $insertArray['delivery_address'] = $insertArray['billing_address'];
                $insertArray['delivery_address_number'] = $insertArray['billing_address_number'];
                $insertArray['delivery_address_ext'] = $insertArray['billing_address_ext'];
                $insertArray['delivery_zip'] = $insertArray['billing_zip'];
                $insertArray['delivery_city'] = $insertArray['billing_city'];
                $insertArray['delivery_country'] = $insertArray['billing_country'];
                $insertArray['delivery_telephone'] = $insertArray['billing_telephone'];
                $insertArray['delivery_region'] = $insertArray['billing_region'];
                $insertArray['delivery_name'] = $insertArray['billing_name'];
            }
        } else if ($this->post['different_delivery_address']) {
            $this->post['different_delivery_address'] = true;
            $insertArray['delivery_email'] = $this->post['delivery_email'];
            $insertArray['delivery_company'] = $this->post['delivery_company'];
            $insertArray['delivery_first_name'] = $this->post['delivery_first_name'];
            $insertArray['delivery_middle_name'] = $this->post['delivery_middle_name'];
            $insertArray['delivery_last_name'] = $this->post['delivery_last_name'];
            $insertArray['delivery_telephone'] = $this->post['delivery_telephone'];
            $insertArray['delivery_mobile'] = $this->post['delivery_mobile'];
            $insertArray['delivery_gender'] = $this->post['delivery_gender'];
            $insertArray['delivery_street_name'] = $this->post['delivery_street_name'];
            $insertArray['delivery_address_number'] = $this->post['delivery_address_number'];
            $insertArray['delivery_address_ext'] = $this->post['delivery_address_ext'];
            $insertArray['delivery_address'] = preg_replace('/ +/', ' ', $this->post['delivery_street_name'] . ' ' . $this->post['delivery_address_number'] . ' ' . $this->post['delivery_address_ext']);
            $insertArray['delivery_zip'] = $this->post['delivery_zip'];
            $insertArray['delivery_city'] = $this->post['delivery_city'];
            $insertArray['delivery_country'] = $this->post['delivery_country'];
            $insertArray['delivery_email'] = $this->post['delivery_email'];
            $insertArray['delivery_telephone'] = $this->post['delivery_telephone'];
            $insertArray['delivery_state'] = $this->post['delivery_state'];
            $insertArray['delivery_name'] = preg_replace('/ +/', ' ', $this->post['delivery_first_name'] . ' ' . $this->post['delivery_middle_name'] . ' ' . $this->post['delivery_last_name']);
        }
        $insertArray['payment_condition'] = $this->ms['MODULES']['DEFAULT_PAYMENT_CONDITION_VALUE'];
        if (is_numeric($this->post['tx_multishop_payment_condition']) && $this->post['tx_multishop_payment_condition'] > 0) {
            $insertArray['payment_condition'] = $this->post['tx_multishop_payment_condition'];
        }
        if ($this->post['tx_multishop_pi1']['is_proposal']) {
            $insertArray['is_proposal'] = 1;
        } else {
            $insertArray['by_phone'] = 1;
        }
        $insertArray['hash'] = md5(uniqid('', true));
        // geo data
        $addresstypes = array();
        $addresstypes[] = 'billing';
        $addresstypes[] = 'delivery';
        foreach ($addresstypes as $addresstype) {
            $str2 = 'select st.* from static_countries sc, static_territories st where sc.cn_short_en=\'' . addslashes($insertArray[$addresstype . '_country']) . '\' and st.tr_iso_nr=sc.cn_parent_tr_iso_nr';
            $query2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $rows2 = $GLOBALS['TYPO3_DB']->sql_num_rows($query2);
            if ($rows2) {
                $row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query2);
                $insertArray[$addresstype . '_tr_iso_nr'] = $row2['tr_iso_nr'];
                $insertArray[$addresstype . '_tr_name_en'] = $row2['tr_name_en'];
                $str2 = 'select * from static_territories where tr_iso_nr=' . $row2['tr_parent_iso_nr'];
                $query2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
                $rows2 = $GLOBALS['TYPO3_DB']->sql_num_rows($query2);
                if ($rows2) {
                    $row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query2);
                    $insertArray[$addresstype . '_tr_parent_iso_nr'] = $row2['tr_iso_nr'];
                    $insertArray[$addresstype . '_tr_parent_name_en'] = $row2['tr_name_en'];
                }
            }
        }
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderPreHook'])) {
            // hook
            $params = array(
                    'insertArray' => &$insertArray
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderPreHook'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
            // hook eof
        }
        $insertArray['orders_last_modified'] = time();
        $insertArray = mslib_befe::rmNullValuedKeys($insertArray);
        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders', $insertArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        // now add the order eof
        $orders_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
        // redirect back to orders and let highslide open it
        $url = $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $orders_id . '&tx_multishop_pi1[is_manual]=1&action=edit_order&tx_multishop_pi1[is_proposal]=' . $this->post['tx_multishop_pi1']['is_proposal'], 1);
        header('Location: ' . $url);
        exit();
    } //add to orders eof
}
?>
