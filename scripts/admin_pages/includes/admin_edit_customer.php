<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
if ($this->post && $this->post['email']) {
    $this->post['email'] = mslib_fe::RemoveXSS($this->post['email']);
    $erno = array();
    if (is_numeric($this->post['tx_multishop_pi1']['cid'])) {
        $edit_mode = 1;
        $user = mslib_fe::getUser($this->post['tx_multishop_pi1']['cid']);
        if ($user['email'] <> $this->post['email']) {
            if (!$this->ms['MODULES']['ADMIN_ALLOW_DUPLICATE_CUSTOMERS_EMAIL_ADDRESS']) {
                // check if the emailaddress is not already in use
                $usercheck = mslib_fe::getUser($this->post['email'], 'email');
                if ($usercheck['uid']) {
                    $edit_customer_link = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]=' . $usercheck['uid'] . '&action=edit_customer', 1);
                    $erno[] = 'Email address is already in use by customer ID: <a href="' . $edit_customer_link . '">' . $usercheck['uid'] . '</a>';
                }
            }
        }
        if ($user['username'] <> $this->post['username']) {
            // check if the emailaddress is not already in use
            $usercheck = mslib_fe::getUser($this->post['username'], 'username');
            if ($usercheck['uid']) {
                $edit_customer_link = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]=' . $usercheck['uid'] . '&action=edit_customer', 1);
                $erno[] = 'Username ' . $usercheck['username'] . ' is in use by customer ID: <a href="' . $edit_customer_link . '">' . $usercheck['uid'] . '</a>';
            }
        }
    } else {
        if (!$this->ms['MODULES']['ADMIN_ALLOW_DUPLICATE_CUSTOMERS_EMAIL_ADDRESS']) {
            // check if the emailaddress is not already in use
            $usercheck = mslib_fe::getUser($this->post['email'], 'email');
            if ($usercheck['uid']) {
                $edit_customer_link = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]=' . $usercheck['uid'] . '&action=edit_customer', 1);
                $erno[] = 'Email address is already in use by customer ID: <a href="' . $edit_customer_link . '">' . $usercheck['uid'] . '</a>';
            }
        }
        // check if the emailaddress is not already in use
        $usercheck = mslib_fe::getUser($this->post['username'], 'username');
        if ($usercheck['uid']) {
            $edit_customer_link = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]=' . $usercheck['uid'] . '&action=edit_customer', 1);
            $erno[] = 'Username ' . $usercheck['username'] . ' is in use by customer ID: <a href="' . $edit_customer_link . '">' . $usercheck['uid'] . '</a>';
        }
    }
    if (count($erno)) {
        $this->get['tx_multishop_pi1']['cid'] = $this->post['tx_multishop_pi1']['cid'];
        $continue = 0;
    } else {
        $continue = 1;
    }
    if ($continue) {
        $updateArray = array();
        if (isset($this->post['tx_multishop_language'])) {
            $updateArray['tx_multishop_language'] = $this->post['tx_multishop_language'];
        }
        $updateArray['username'] = $this->post['username'];
        if ($this->post['birthday']) {
            $updateArray['date_of_birth'] = strtotime($this->post['birthday']);
        }
        $updateArray['first_name'] = trim($this->post['first_name']);
        $updateArray['middle_name'] = trim($this->post['middle_name']);
        $updateArray['last_name'] = trim($this->post['last_name']);
        $updateArray['name'] = $updateArray['first_name'] . ' ' . $updateArray['middle_name'] . ' ' . $updateArray['last_name'];
        $updateArray['name'] = preg_replace('/\s+/', ' ', $updateArray['name']);
        $updateArray['gender'] = $this->post['gender'];
        $updateArray['company'] = trim($this->post['company']);
        $updateArray['building'] = trim($this->post['building']);
        $updateArray['street_name'] = trim($this->post['street_name']);
        $updateArray['address_number'] = trim($this->post['address_number']);
        $updateArray['address_ext'] = trim($this->post['address_ext']);
        $updateArray['address'] = $updateArray['street_name'] . ' ' . $updateArray['address_number'] . $updateArray['address_ext'];
        $updateArray['address'] = preg_replace('/\s+/', ' ', $updateArray['address']);
        $updateArray['zip'] = trim($this->post['zip']);
        $updateArray['city'] = trim($this->post['city']);
        $updateArray['country'] = trim($this->post['country']);
        $updateArray['email'] = trim($this->post['email']);
        if (isset($this->post['contact_email'])) {
            $updateArray['contact_email'] = trim($this->post['contact_email']);
        }
        $updateArray['www'] = trim($this->post['www']);
        $updateArray['telephone'] = trim($this->post['telephone']);
        $updateArray['mobile'] = trim($this->post['mobile']);
        $updateArray['tx_multishop_discount'] = $this->post['tx_multishop_discount'];
        $updateArray['tx_multishop_payment_condition'] = $this->post['tx_multishop_payment_condition'];
        $updateArray['foreign_customer_id'] = $this->post['foreign_customer_id'];
        if ($this->post['password']) {
            $updateArray['password'] = mslib_befe::getHashedPassword($this->post['password']);
        }
        if ($this->post['tx_multishop_pi1']['image']) {
            $updateArray['image'] = $this->post['tx_multishop_pi1']['image'];
        }
        if (isset($this->post['tx_multishop_vat_id'])) {
            if (!empty($this->post['tx_multishop_vat_id'])) {
                $updateArray['tx_multishop_vat_id'] = trim($this->post['tx_multishop_vat_id']);
            } else {
                $updateArray['tx_multishop_vat_id'] = '';
            }
        }
        if (isset($this->post['tx_multishop_coc_id'])) {
            if (!empty($this->post['tx_multishop_coc_id'])) {
                $updateArray['tx_multishop_coc_id'] = trim($this->post['tx_multishop_coc_id']);
            } else {
                $updateArray['tx_multishop_coc_id'] = '';
            }
        }
        if ($this->post['page_uid'] and $this->masterShop) {
            $updateArray['page_uid'] = $this->post['page_uid'];
        }
        if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
            if (isset($this->post['department'])) {
                $updateArray['department'] = trim($this->post['department']);
            }
        }
        $updateArray['tx_multishop_newsletter'] = 0;
        if (isset($this->post['tx_multishop_newsletter'])) {
            $updateArray['tx_multishop_newsletter'] = $this->post['tx_multishop_newsletter'];
        }
        if (is_numeric($this->post['tx_multishop_pi1']['cid'])) {
            $postedUsergroup = array();
            $originalGroups = array();
            if (!empty($this->post['tx_multishop_pi1']['groups'])) {
                $postedUsergroup = explode(',', $this->post['tx_multishop_pi1']['groups']);
            }
            if (!empty($this->post['tx_multishop_pi1']['original_groups'])) {
                $originalGroups = explode(',', $this->post['tx_multishop_pi1']['original_groups']);
            }
            $removedGroups = array();
            if (is_array($originalGroups) && count($originalGroups)) {
                foreach ($originalGroups as $originalGroup) {
                    if (!in_array($originalGroup, $postedUsergroup)) {
                        $removedGroups[] = $originalGroup;
                    }
                }
            }
            $customer_id = $this->post['tx_multishop_pi1']['cid'];
            // update mode
            if (!empty($this->post['tx_multishop_pi1']['groups'])) {
                $selectedGroups = array();
                foreach ($postedUsergroup as $postedGroup) {
                    $selectedGroups[] = $postedGroup;
                }
                if (isset($user['usergroup'])) {
                    // first get old usergroup data, cause maybe the user is also member of excluded usergroups that we should remain
                    $currentUserGroup = explode(",", $user['usergroup']);
                    foreach ($currentUserGroup as $currentGroup) {
                        if (!in_array($currentGroup, $removedGroups)) {
                            $selectedGroups[] = $currentGroup;
                        }
                    }
                    foreach ($this->excluded_userGroups as $usergroup) {
                        if (in_array($usergroup, $selectedGroups) && !in_array($usergroup, $removedGroups)) {
                            $selectedGroups[] = $usergroup;
                        }
                    }
                }
                $selectedGroups = array_unique($selectedGroups);
                $updateArray['usergroup'] = implode(',', $selectedGroups);
            } else {
                if (isset($user['usergroup'])) {
                    // first get old usergroup data, cause maybe the user is also member of excluded usergroups that we should remain
                    $selectedGroups = explode(",", $user['usergroup']);
                    $currentGroups = array();
                    foreach ($selectedGroups as $selectedGroup) {
                        if (!in_array($selectedGroup, $removedGroups)) {
                            $currentGroups[] = $selectedGroup;
                        }
                    }
                    foreach ($this->excluded_userGroups as $usergroup) {
                        if (in_array($usergroup, $selectedGroups) && !in_array($usergroup, $removedGroups)) {
                            $currentGroups[] = $usergroup;
                        }
                    }
                    $currentGroups = array_unique($currentGroups);
                    $updateArray['usergroup'] = implode(',', $currentGroups);
                }
            }
            //
            // custom hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPreProc'])) {
                $params = array(
                        'uid' => $this->post['tx_multishop_pi1']['cid'],
                        'updateArray' => &$updateArray,
                        'user' => $user,
                        'erno' => $erno
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            if (count($erno)) {
                $this->get['tx_multishop_pi1']['cid'] = $this->post['tx_multishop_pi1']['cid'];
                $continue = 0;
            } else {
                $continue = 1;
            }
            if ($continue) {
                $updateArray['last_updated_at'] = time();
                // custom hook that can be controlled by third-party plugin eof
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=' . $this->post['tx_multishop_pi1']['cid'], $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                //update the tt_address billing
                $updateTTAddressArray = array();
                $updateTTAddressArray['tstamp'] = time();
                $updateTTAddressArray['company'] = $updateArray['company'];
                $updateTTAddressArray['name'] = $updateArray['first_name'] . ' ' . $updateArray['middle_name'] . ' ' . $updateArray['last_name'];
                $updateTTAddressArray['name'] = preg_replace('/\s+/', ' ', $updateTTAddressArray['name']);
                $updateTTAddressArray['first_name'] = $updateArray['first_name'];
                $updateTTAddressArray['middle_name'] = $updateArray['middle_name'];
                $updateTTAddressArray['last_name'] = $updateArray['last_name'];
                $updateTTAddressArray['email'] = $updateArray['email'];
                if (!$updateArray['street_name']) {
                    // fallback for old custom checkouts
                    $updateTTAddressArray['building'] = $updateArray['building'];
                    $updateTTAddressArray['street_name'] = $updateArray['address'];
                    $updateTTAddressArray['address_number'] = $updateArray['address_number'];
                    $updateTTAddressArray['address_ext'] = $updateArray['address_ext'];
                    $updateTTAddressArray['address'] = $updateTTAddressArray['street_name'] . ' ' . $updateTTAddressArray['address_number'] . ($insertArray['address_ext'] ? '-' . $updateTTAddressArray['address_ext'] : '');
                    $updateTTAddressArray['address'] = preg_replace('/\s+/', ' ', $updateTTAddressArray['address']);
                } else {
                    $updateTTAddressArray['building'] = $updateArray['building'];
                    $updateTTAddressArray['street_name'] = $updateArray['street_name'];
                    $updateTTAddressArray['address_number'] = $updateArray['address_number'];
                    $updateTTAddressArray['address_ext'] = $updateArray['address_ext'];
                    $updateTTAddressArray['address'] = $updateArray['address'];
                }
                $updateTTAddressArray['zip'] = $updateArray['zip'];
                $updateTTAddressArray['phone'] = $updateArray['telephone'];
                $updateTTAddressArray['mobile'] = $updateArray['mobile'];
                $updateTTAddressArray['city'] = $updateArray['city'];
                $updateTTAddressArray['country'] = $updateArray['country'];
                if ($updateArray['gender'] == '0' || $updateArray['gender'] == '1') {
                    $updateTTAddressArray['gender'] = ($updateArray['gender'] == '0' ? 'm' : 'f');
                } else {
                    $updateTTAddressArray['gender'] = $updateArray['gender'];
                }
                $updateTTAddressArray['birthday'] = strtotime($updateArray['birthday']);
                if ($updateTTAddressArray['gender'] == 'm') {
                    $updateTTAddressArray['title'] = 'Mr.';
                } else {
                    if ($updateTTAddressArray['gender'] == 'f') {
                        $updateTTAddressArray['title'] = 'Mrs.';
                    }
                }
                $updateTTAddressArray['region'] = $updateArray['state'];
                $updateTTAddressArray['pid'] = $this->conf['fe_customer_pid'];
                $updateTTAddressArray['page_uid'] = $this->shop_pid;
                $updateTTAddressArray['tstamp'] = time();
                $updateTTAddressArray['tx_multishop_address_type'] = 'billing';
                $updateTTAddressArray['tx_multishop_default'] = 1;
                $updateTTAddressArray['tx_multishop_customer_id'] = $customer_id;
                if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                    if (isset($this->post['department'])) {
                        $updateTTAddressArray['department'] = $this->post['department'];
                    }
                }
                $updateTTAddressArray['is_delivery_same'] = 0;
                if (!$this->post['different_delivery_address']) {
                    $updateTTAddressArray['is_delivery_same'] = 1;
                } else {
                    // Recheck if data all the same or not
                    // Billing details
                    $md5_list = array();
                    $md5_list[] = ($this->post['gender'] == '0' ? 'm' : 'f');
                    $md5_list[] = $this->post['first_name'];
                    $md5_list[] = $this->post['middle_name'];
                    $md5_list[] = $this->post['last_name'];
                    $md5_list[] = $this->post['company'];
                    $md5_list[] = $this->post['street_name'];
                    $md5_list[] = $this->post['address_number'];
                    $md5_list[] = $this->post['address_ext'];
                    $md5_list[] = $this->post['zip'];
                    $md5_list[] = $this->post['city'];
                    $md5_list[] = $this->post['telephone'];
                    $md5_list[] = $this->post['email'];
                    $billing_address_md5 = md5(implode("", $md5_list));
                    // Delivery details
                    $md5_list = array();
                    $md5_list[] = ($this->post['delivery_gender'] == '0' ? 'm' : 'f');
                    $md5_list[] = $this->post['delivery_first_name'];
                    $md5_list[] = $this->post['delivery_middle_name'];
                    $md5_list[] = $this->post['delivery_last_name'];
                    $md5_list[] = $this->post['delivery_company'];
                    $md5_list[] = $this->post['delivery_street_name'];
                    $md5_list[] = $this->post['delivery_address_number'];
                    $md5_list[] = $this->post['delivery_address_ext'];
                    $md5_list[] = $this->post['delivery_zip'];
                    $md5_list[] = $this->post['delivery_city'];
                    $md5_list[] = $this->post['delivery_telephone'];
                    $md5_list[] = $this->post['delivery_email'];
                    $delivery_address_md5 = md5(implode("", $md5_list));
                    if ($billing_address_md5 == $delivery_address_md5) {
                        $updateTTAddressArray['is_delivery_same'] = 1;
                    }
                }
                if (!mslib_fe::getFeUserTTaddressDetails($customer_id, 'billing')) {
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $updateTTAddressArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                } else {
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'tx_multishop_customer_id=' . $customer_id . ' and tx_multishop_address_type=\'billing\'', $updateTTAddressArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                }
                if (!$this->post['different_delivery_address']) {
                    $updateTTAddressArray['tx_multishop_address_type'] = 'delivery';
                    $updateTTAddressArray['tx_multishop_default'] = 0;
                    if (!mslib_fe::getFeUserTTaddressDetails($customer_id, 'delivery')) {
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $updateTTAddressArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    } else {
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'tx_multishop_customer_id=' . $customer_id . ' and tx_multishop_address_type=\'delivery\'', $updateTTAddressArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                } else {
                    // ADD TT_ADDRESS RECORD
                    $updateTTAddressArray = array();
                    $updateTTAddressArray['tstamp'] = time();
                    $updateTTAddressArray['company'] = $this->post['delivery_company'];
                    $updateTTAddressArray['name'] = $this->post['delivery_first_name'] . ' ' . $this->post['delivery_middle_name'] . ' ' . $this->post['delivery_last_name'];
                    $updateTTAddressArray['name'] = preg_replace('/\s+/', ' ', $updateTTAddressArray['name']);
                    $updateTTAddressArray['first_name'] = $this->post['delivery_first_name'];
                    $updateTTAddressArray['middle_name'] = $this->post['delivery_middle_name'];
                    $updateTTAddressArray['last_name'] = $this->post['delivery_last_name'];
                    $updateTTAddressArray['email'] = $this->post['delivery_email'];
                    if (!$this->post['delivery_street_name']) {
                        // fallback for old custom checkouts
                        $updateTTAddressArray['building'] = $this->post['delivery_building'];
                        $updateTTAddressArray['street_name'] = $this->post['delivery_address'];
                        $updateTTAddressArray['address_number'] = $this->post['delivery_address_number'];
                        $updateTTAddressArray['address_ext'] = $this->post['delivery_address_ext'];
                        $updateTTAddressArray['address'] = $updateTTAddressArray['street_name'] . ' ' . $updateTTAddressArray['address_number'] . ($insertArray['address_ext'] ? '-' . $updateTTAddressArray['address_ext'] : '');
                        $updateTTAddressArray['address'] = preg_replace('/\s+/', ' ', $updateTTAddressArray['address']);
                    } else {
                        $updateTTAddressArray['building'] = $this->post['delivery_building'];
                        $updateTTAddressArray['street_name'] = $this->post['delivery_street_name'];
                        $updateTTAddressArray['address_number'] = $this->post['delivery_address_number'];
                        $updateTTAddressArray['address_ext'] = $this->post['delivery_address_ext'];
                        $updateTTAddressArray['address'] = $updateTTAddressArray['street_name'] . ' ' . $updateTTAddressArray['address_number'] . ($insertArray['address_ext'] ? '-' . $updateTTAddressArray['address_ext'] : '');
                        $updateTTAddressArray['address'] = preg_replace('/\s+/', ' ', $updateTTAddressArray['address']);
                    }
                    $updateTTAddressArray['zip'] = $this->post['delivery_zip'];
                    $updateTTAddressArray['phone'] = $this->post['delivery_telephone'];
                    $updateTTAddressArray['mobile'] = $this->post['delivery_mobile'];
                    $updateTTAddressArray['city'] = $this->post['delivery_city'];
                    $updateTTAddressArray['country'] = $this->post['delivery_country'];
                    if ($this->post['delivery_gender'] == '0' || $this->post['delivery_gender'] == '1') {
                        $updateTTAddressArray['gender'] = ($this->post['delivery_gender'] == '0' ? 'm' : 'f');
                    } else {
                        $updateTTAddressArray['gender'] = $this->post['delivery_gender'];
                    }
                    if ($updateTTAddressArray['gender'] == 'm') {
                        $updateTTAddressArray['title'] = 'Mr.';
                    } else {
                        if ($updateTTAddressArray['gender'] == 'f') {
                            $updateTTAddressArray['title'] = 'Mrs.';
                        }
                    }
                    $updateTTAddressArray['region'] = $this->post['delivery_state'];
                    $updateTTAddressArray['pid'] = $this->conf['fe_customer_pid'];
                    $updateTTAddressArray['page_uid'] = $this->shop_pid;
                    $updateTTAddressArray['tstamp'] = time();
                    $updateTTAddressArray['tx_multishop_address_type'] = 'delivery';
                    $updateTTAddressArray['tx_multishop_default'] = 0;
                    $updateTTAddressArray['tx_multishop_customer_id'] = $customer_id;
                    if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                        if (isset($this->post['delivery_department'])) {
                            $updateTTAddressArray['department'] = $this->post['delivery_department'];
                        }
                    }
                    $updateTTAddressArray['is_delivery_same'] = 0;
                    if ($billing_address_md5 == $delivery_address_md5) {
                        $updateTTAddressArray['is_delivery_same'] = 1;
                    }
                    if (!mslib_fe::getFeUserTTaddressDetails($customer_id, 'delivery')) {
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $updateTTAddressArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    } else {
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'tx_multishop_customer_id=' . $customer_id . ' and tx_multishop_address_type=\'delivery\'', $updateTTAddressArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                }
                // custom hook that can be controlled by third-party plugin
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPostProc'])) {
                    $params = array(
                            'uid' => $this->post['tx_multishop_pi1']['cid']
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPostProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
            }
            // custom hook that can be controlled by third-party plugin eof
            // customer shipping/payment method mapping
            if ($customer_id && $this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
                $payment_methods = mslib_fe::loadPaymentMethods();
                $shipping_methods = mslib_fe::loadShippingMethods();

                // shipping/payment methods
                $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_customers_method_mappings', 'customers_id=\'' . $customer_id . '\'');
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                if (count($payment_methods)) {
                    foreach ($payment_methods as $code => $item) {
                        // Only set the negate value when setting is differ from global setting
                        if (isset($this->post['payment_method'][$item['id']])) {
                            $negateValue = 0;
                        } else {
                            if ($item['status'] > 0) {
                                $negateValue = 1;
                            }
                            if ($item['enable_on_default']) {
                                $negateValue = 1;
                            }
                        }
                        // Only insert when $negateValue var is set otherwise the setting value same as the global
                        if (isset($negateValue)) {
                            $updateArray = array();
                            $updateArray['customers_id'] = $customer_id;
                            $updateArray['method_id'] = $item['id'];
                            $updateArray['type'] = 'payment';
                            $updateArray['negate'] = $negateValue;
                            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                        // Keep clean after use
                        unset($negateValue);
                    }
                }
                if (count($shipping_methods)) {
                    foreach ($shipping_methods as $code => $item) {
                        // Only set the negate value when setting is differ from global setting
                        if (isset($this->post['shipping_method'][$item['id']])) {
                            if (!$item['status']) {
                                $negateValue = 0;
                            }
                        } else {
                            if ($item['status'] > 0) {
                                $negateValue = 1;
                            }
                        }
                        // Only insert when $negateValue var is set otherwise the setting value same as the global
                        if (isset($negateValue)) {
                            $updateArray = array();
                            $updateArray['customers_id'] = $customer_id;
                            $updateArray['method_id'] = $item['id'];
                            $updateArray['type'] = 'shipping';
                            $updateArray['negate'] = $negateValue;
                            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                        // Keep clean after use
                        unset($negateValue);
                    }
                }
                // shipping/payment methods eof
            }
        } else {
            // insert mode
            if (isset($this->post['tx_multishop_language'])) {
                $updateArray['tx_multishop_language'] = $this->post['tx_multishop_language'];
            }
            if (!empty($this->post['tx_multishop_pi1']['groups'])) {
                if (!empty($this->conf['fe_customer_usergroup'])) {
                    $this->post['tx_multishop_pi1']['groups'] .= ',' . $this->conf['fe_customer_usergroup'];
                }
                $updateArray['usergroup'] = $this->post['tx_multishop_pi1']['groups'];
            } else {
                $updateArray['usergroup'] = $this->conf['fe_customer_usergroup'];
            }
            $updateArray['pid'] = $this->conf['fe_customer_pid'];
            $updateArray['tx_multishop_code'] = md5(uniqid('', true));
            $updateArray['tstamp'] = time();
            $updateArray['crdate'] = time();
            $updateArray['last_updated_at'] = time();
            if ($this->post['password']) {
                $updateArray['password'] = mslib_befe::getHashedPassword($this->post['password']);
            } else {
                $string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789-=~!@#$%^&*()_+,./?;:[]{}\|';
                $updateArray['password'] = mslib_befe::getHashedPassword(mslib_befe::generateRandomPassword(12, $string, 'unpronounceable'));
            }
            if ($this->post['page_uid'] and $this->masterShop) {
                $updateArray['page_uid'] = $this->post['page_uid'];
            } else {
                $updateArray['page_uid'] = $this->shop_pid;
            }
            if (isset($this->post['tx_multishop_vat_id'])) {
                if (!empty($this->post['tx_multishop_vat_id'])) {
                    $updateArray['tx_multishop_vat_id'] = $this->post['tx_multishop_vat_id'];
                } else {
                    $updateArray['tx_multishop_vat_id'] = '';
                }
            }
            if (isset($this->post['tx_multishop_coc_id'])) {
                if (!empty($this->post['tx_multishop_coc_id'])) {
                    $updateArray['tx_multishop_coc_id'] = $this->post['tx_multishop_coc_id'];
                } else {
                    $updateArray['tx_multishop_coc_id'] = '';
                }
            }
            if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                if (isset($this->post['department'])) {
                    $updateArray['department'] = $this->post['department'];
                }
            }
            $updateArray['tx_multishop_newsletter'] = 0;
            if (isset($this->post['tx_multishop_newsletter'])) {
                $updateArray['tx_multishop_newsletter'] = $this->post['tx_multishop_newsletter'];
            }
            $updateArray['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
            // custom hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPreProc'])) {
                $params = array(
                        'uid' => $this->post['tx_multishop_pi1']['cid'],
                        'updateArray' => &$updateArray,
                        'erno' => &$erno
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            if (!count($erno)) {
                // custom hook that can be controlled by third-party plugin eof
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                if (!$res) {
                    $erno[] = $GLOBALS['TYPO3_DB']->sql_error();
                } else {
                    $customer_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
                    // ADD TT_ADDRESS RECORD
                    $insertArray = array();
                    $insertArray['tstamp'] = time();
                    $insertArray['company'] = $updateArray['company'];
                    $insertArray['name'] = $updateArray['first_name'] . ' ' . $updateArray['middle_name'] . ' ' . $updateArray['last_name'];
                    $insertArray['name'] = preg_replace('/\s+/', ' ', $insertArray['name']);
                    $insertArray['first_name'] = $updateArray['first_name'];
                    $insertArray['middle_name'] = $updateArray['middle_name'];
                    $insertArray['last_name'] = $updateArray['last_name'];
                    $insertArray['email'] = $updateArray['email'];
                    if (!$updateArray['street_name']) {
                        // fallback for old custom checkouts
                        $insertArray['building'] = $updateArray['building'];
                        $insertArray['street_name'] = $updateArray['address'];
                        $insertArray['address_number'] = $updateArray['address_number'];
                        $insertArray['address_ext'] = $updateArray['address_ext'];
                        $insertArray['address'] = $insertArray['street_name'] . ' ' . $insertArray['address_number'] . ($insertArray['address_ext'] ? '-' . $insertArray['address_ext'] : '');
                        $insertArray['address'] = preg_replace('/\s+/', ' ', $insertArray['address']);
                    } else {
                        $insertArray['building'] = $updateArray['building'];
                        $insertArray['street_name'] = $updateArray['street_name'];
                        $insertArray['address_number'] = $updateArray['address_number'];
                        $insertArray['address_ext'] = $updateArray['address_ext'];
                        $insertArray['address'] = $updateArray['address'];
                    }
                    $insertArray['zip'] = $updateArray['zip'];
                    $insertArray['phone'] = $updateArray['telephone'];
                    $insertArray['mobile'] = $updateArray['mobile'];
                    $insertArray['city'] = $updateArray['city'];
                    $insertArray['country'] = $updateArray['country'];
                    if ($updateArray['gender'] == '0' || $updateArray['gender'] == '1') {
                        $insertArray['gender'] = ($updateArray['gender'] == '0' ? 'm' : 'f');
                    } else {
                        $insertArray['gender'] = $updateArray['gender'];
                    }
                    $insertArray['birthday'] = strtotime($updateArray['birthday']);
                    if ($insertArray['gender'] == 'm') {
                        $insertArray['title'] = 'Mr.';
                    } else {
                        if ($insertArray['gender'] == 'f') {
                            $insertArray['title'] = 'Mrs.';
                        }
                    }
                    $insertArray['region'] = $updateArray['state'];
                    $insertArray['pid'] = $this->conf['fe_customer_pid'];
                    $insertArray['page_uid'] = $this->shop_pid;
                    $insertArray['tstamp'] = time();
                    $insertArray['tx_multishop_address_type'] = 'billing';
                    $insertArray['tx_multishop_default'] = 1;
                    $insertArray['tx_multishop_customer_id'] = $customer_id;
                    if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                        if (isset($this->post['department'])) {
                            $insertArray['department'] = $this->post['department'];
                        }
                    }
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    if (!$this->post['different_delivery_address']) {
                        $insertArray['tx_multishop_address_type'] = 'delivery';
                        $insertArray['tx_multishop_default'] = 0;
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    } else {
                        // ADD TT_ADDRESS RECORD
                        $insertArray = array();
                        $insertArray['tstamp'] = time();
                        $insertArray['company'] = $this->post['delivery_company'];
                        $insertArray['name'] = $this->post['delivery_first_name'] . ' ' . $this->post['delivery_middle_name'] . ' ' . $this->post['delivery_last_name'];
                        $insertArray['name'] = preg_replace('/\s+/', ' ', $insertArray['name']);
                        $insertArray['first_name'] = $this->post['delivery_first_name'];
                        $insertArray['middle_name'] = $this->post['delivery_middle_name'];
                        $insertArray['last_name'] = $this->post['delivery_last_name'];
                        $insertArray['email'] = $this->post['delivery_email'];
                        if (!$this->post['delivery_street_name']) {
                            // fallback for old custom checkouts
                            $insertArray['building'] = $this->post['delivery_building'];
                            $insertArray['street_name'] = $this->post['delivery_address'];
                            $insertArray['address_number'] = $this->post['delivery_address_number'];
                            $insertArray['address_ext'] = $this->post['delivery_address_ext'];
                            $insertArray['address'] = $insertArray['street_name'] . ' ' . $insertArray['address_number'] . ($insertArray['address_ext'] ? '-' . $insertArray['address_ext'] : '');
                            $insertArray['address'] = preg_replace('/\s+/', ' ', $insertArray['address']);
                        } else {
                            $insertArray['building'] = $this->post['delivery_building'];
                            $insertArray['street_name'] = $this->post['delivery_street_name'];
                            $insertArray['address_number'] = $this->post['delivery_address_number'];
                            $insertArray['address_ext'] = $this->post['delivery_address_ext'];
                            $insertArray['address'] = $this->post['delivery_address'];
                        }
                        $insertArray['zip'] = $this->post['delivery_zip'];
                        $insertArray['phone'] = $this->post['delivery_telephone'];
                        $insertArray['mobile'] = $this->post['delivery_mobile'];
                        $insertArray['city'] = $this->post['delivery_city'];
                        $insertArray['country'] = $this->post['delivery_country'];
                        if ($this->post['delivery_gender'] == '0' || $this->post['delivery_gender'] == '1') {
                            $insertArray['gender'] = ($this->post['delivery_gender'] == '0' ? 'm' : 'f');
                        } else {
                            $insertArray['gender'] = $this->post['delivery_gender'];
                        }
                        $insertArray['birthday'] = strtotime($this->post['delivery_birthday']);
                        if ($this->post['delivery_gender'] == 'm') {
                            $insertArray['title'] = 'Mr.';
                        } else {
                            if ($this->post['delivery_gender'] == 'f') {
                                $insertArray['title'] = 'Mrs.';
                            }
                        }
                        $insertArray['region'] = $this->post['delivery_state'];
                        $insertArray['pid'] = $this->conf['fe_customer_pid'];
                        $insertArray['page_uid'] = $this->shop_pid;
                        $insertArray['tstamp'] = time();
                        $insertArray['tx_multishop_address_type'] = 'delivery';
                        $insertArray['tx_multishop_default'] = 0;
                        $insertArray['tx_multishop_customer_id'] = $customer_id;
                        if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                            if (isset($this->post['delivery_department'])) {
                                $insertArray['department'] = $this->post['delivery_department'];
                            }
                        }
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                    // customer shipping/payment method mapping
                    if ($customer_id && $this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
                        // shipping/payment methods
                        $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_customers_method_mappings', 'customers_id=\'' . $customer_id . '\'');
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        if (is_array($this->post['payment_method']) and count($this->post['payment_method'])) {
                            foreach ($this->post['payment_method'] as $payment_method_id => $value) {
                                $updateArray = array();
                                $updateArray['customers_id'] = $customer_id;
                                $updateArray['method_id'] = $payment_method_id;
                                $updateArray['type'] = 'payment';
                                $updateArray['negate'] = $value;
                                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            }
                        }
                        if (is_array($this->post['shipping_method']) and count($this->post['shipping_method'])) {
                            foreach ($this->post['shipping_method'] as $shipping_method_id => $value) {
                                $updateArray = array();
                                $updateArray['customers_id'] = $customer_id;
                                $updateArray['method_id'] = $shipping_method_id;
                                $updateArray['type'] = 'shipping';
                                $updateArray['negate'] = $value;
                                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            }
                        }
                        // shipping/payment methods eof
                    }
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPostProc'])) {
                        $params = array(
                                'uid' => $customer_id
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPostProc'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                }
            }
        }
        if (!count($erno)) {
            if (isset($this->post['SaveClose'])) {
                if (strpos($this->post['tx_multishop_pi1']['referrer'], 'action=edit_customer') === false && strpos($this->post['tx_multishop_pi1']['referrer'], 'action=add_customer') === false && $this->post['tx_multishop_pi1']['referrer']) {
                    header("Location: " . $this->post['tx_multishop_pi1']['referrer']);
                    exit();
                } else {
                    header("Location: " . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_customers', 1));
                    exit();
                }
            } else if (isset($this->post['Submit'])) {
                header("Location: " . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=edit_customer', 1) . '&tx_multishop_pi1[cid]=' . $customer_id . '&action=edit_customer#edit_customer');
                exit();
            }
        }
    }
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_edit_customer_tmpl_path']) {
    $template = $this->cObj->fileResource($this->conf['admin_edit_customer_tmpl_path']);
} else {
    $template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey) . 'templates/admin_edit_customer.tmpl');
}
// Extract the subparts from the template
$subparts = array();
$subparts['template'] = $this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['details'] = $this->cObj->getSubpart($subparts['template'], '###DETAILS###');
$subparts['birthdate'] = $this->cObj->getSubpart($subparts['template'], '###BIRTHDATE_BLOCK###');
// remove block
$subpartsTemplateWrapperRemove = array();
if ($this->ms['MODULES']['DISABLE_BIRTHDATE_IN_ADMIN_CUSTOMER_FORM']) {
    $subpartsTemplateWrapperRemove['###BIRTHDATE_BLOCK###'] = '';
}
$subparts['template'] = $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartsTemplateWrapperRemove);
// load enabled countries to array
$str2 = "SELECT * from static_countries sc, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_countries c where c.page_uid='" . $this->showCatalogFromPage . "' and sc.cn_iso_nr=c.cn_iso_nr and c2z.cn_iso_nr=sc.cn_iso_nr group by c.cn_iso_nr order by sc.cn_short_en";
//$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
$qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
$enabled_countries = array();
while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
    $enabled_countries[] = $row2;
}
$regex = "/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
$regex_for_character = "/[^0-9]$/";
if (!$this->post && is_numeric($this->get['tx_multishop_pi1']['cid'])) {
    $user = mslib_fe::getUser($this->get['tx_multishop_pi1']['cid']);
    $this->post = $user;
    // custom hook that can be controlled by third-party plugin
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerPreloadData'])) {
        $params = array(
                'user' => $user
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerPreloadData'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
}
$head = '';
$head .= '
<script type="text/javascript">
	jQuery(document).ready(function($) {
		jQuery.h5Validate.addPatterns({
			email: /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
		});
		var validate=jQuery(\'#admin_interface_form\').h5Validate();

		$("#birthday_visitor").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'm/d/Y') . '",
			altField: "#birthday",
			altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . (date("Y") - 100) . ':' . date("Y") . '"
			});
		$("#delivery_birthday_visitor").datepicker({
			dateFormat: "' . $this->pi_getLL('locale_date_format', 'm/d/Y') . '",
			altField: "#delivery_birthday",
			altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "' . (date("Y") - 100) . ':' . date("Y") . '"
		});
		var originalLeave = $.fn.popover.Constructor.prototype.leave;
		$.fn.popover.Constructor.prototype.leave = function(obj){
		  var self = obj instanceof this.constructor ? obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data(\'bs.\' + this.type)
		  var container, timeout;
		  originalLeave.call(this, obj);
		  if(obj.currentTarget) {
			container = $(obj.currentTarget).siblings(\'.popover\')
			timeout = self.timeout;
			container.one(\'mouseenter\', function(){
			  //We entered the actual popover – call off the dogs
			  clearTimeout(timeout);
			  //Let\'s monitor popover content instead
			  container.one(\'mouseleave\', function(){
				  $.fn.popover.Constructor.prototype.leave.call(self, self);
				  $(".popover-link").popover("hide");
			  });
			})
		  }
		};
		$(".popover-link").popover({
			position: "down",
			placement: \'bottom\',
			html: true,
			trigger:"hover",
			delay: {show: 20, hide: 200}
		});
		var tooltip_is_shown=\'\';
		$(\'.popover-link\').on(\'show.bs.popover, mouseover\', function () {
			var that=$(this);
			//$(".popover").remove();
			//$(".popover-link").popover(\'hide\');
			var orders_id=$(this).attr(\'rel\');
			//if (tooltip_is_shown != orders_id) {
				tooltip_is_shown=orders_id;
				$.ajax({
					type:   "POST",
					url:    \'' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=getAdminOrdersListingDetails&') . '\',
					data:   \'tx_multishop_pi1[orders_id]=\'+orders_id,
					dataType: "json",
					success: function(data) {
						if (data.content!="") {
							that.next().html(\'<div class="arrow"></div>\' + data.title + data.content);
							//that.next().popover("show");
							//$(that).popover(\'show\');
						} else {
							$(".popover").remove();
						}
					}
				});
			//}
		});
	}); //end of first load
</script>';
$GLOBALS['TSFE']->additionalHeaderData[] = $head;
$head = '';
if (is_array($erno) and count($erno) > 0) {
    $content .= '<div class="alert alert-danger">';
    $content .= '<h3>' . $this->pi_getLL('the_following_errors_occurred') . '</h3><ul class="ul-display-error">';
    $content .= '<li class="item-error" style="display:none"></li>';
    foreach ($erno as $item) {
        $content .= '<li class="item-error">' . $item . '</li>';
    }
    $content .= '</ul>';
    $content .= '</div>';
} else {
    $content .= '<div class="alert alert-danger" style="display:none">';
    $content .= '<h3>' . $this->pi_getLL('the_following_errors_occurred') . '</h3><ul class="ul-display-error">';
    //$content.='<li class="item-error" style="display:none"></li>';
    $content .= '</ul></div>';
}
// load countries
$countries_input = '';
$delivery_countries_input = '';
if (count($enabled_countries) == 1) {
    $countries_input = '<input name="country" type="hidden" value="' . mslib_befe::strtolower($enabled_countries[0]['cn_short_en']) . '" />';
    $delivery_countries_input = '<input name="delivery_country" type="hidden" value="' . mslib_befe::strtolower($enabled_countries[0]['cn_short_en']) . '" />';
} else {
    $billing_countries_option = array();
    $delivery_countries_option = array();
    foreach ($enabled_countries as $country) {
        $cn_localized_name = htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']));
        if ($this->get['action'] == 'add_customer') {
            $billing_countries_option[$cn_localized_name] = '<option value="' . mslib_befe::strtolower($country['cn_short_en']) . '" ' . ((mslib_befe::strtolower($this->tta_shop_info['country']) == mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '') . '>' . $cn_localized_name . '</option>';
        } else {
            $billing_countries_option[$cn_localized_name] = '<option value="' . mslib_befe::strtolower($country['cn_short_en']) . '" ' . ((mslib_befe::strtolower($this->post['country']) == mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '') . '>' . $cn_localized_name . '</option>';
        }
        $delivery_countries_option[$cn_localized_name] = '<option value="' . mslib_befe::strtolower($country['cn_short_en']) . '" ' . (($this->post['delivery_country'] == mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '') . '>' . $cn_localized_name . '</option>';
    }
    ksort($billing_countries_option);
    ksort($delivery_countries_option);
    $tmpcontent_con = implode("\n", $billing_countries_option);
    $tmpcontent_con_delivery = implode("\n", $delivery_countries_option);
    if ($tmpcontent_con) {
        $countries_input = '
		<label for="country" id="account-country">' . ucfirst($this->pi_getLL('country')) . '<span class="text-danger">*</span></label>
		<select name="country" id="country" class="country" required="required" data-h5-errorid="invalid-country" title="' . $this->pi_getLL('country_is_required') . '" autocomplete="off">
		<option value="">' . ucfirst($this->pi_getLL('choose_country')) . '</option>
		' . $tmpcontent_con . '
		</select>
		<div id="invalid-country" class="error-space" style="display:none"></div>';
    }
    if ($tmpcontent_con_delivery) {
        $delivery_countries_input = '
		<label for="delivery_country" id="account-delivery_country">' . ucfirst($this->pi_getLL('country')) . '</label>
		<select name="delivery_country" id="delivery_country" class="country" autocomplete="off">
		<option value="">' . ucfirst($this->pi_getLL('choose_country')) . '</option>
		' . $tmpcontent_con . '
		</select>';
    }
    // custom hook that can be controlled by third-party plugin
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['editCustomerCountries'])) {
        $params = array(
                'enabled_countries' => &$enabled_countries,
                'countries_input' => &$countries_input,
                'delivery_countries_input' => &$delivery_countries_input
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['editCustomerCountries'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    // custom hook that can be controlled by third-party plugin eof
}
// country eof
// fe_user image
$images_tab_block = '';
$images_tab_block .= '
<div class="account-field" id="msEditProductInputImage">
	<label for="products_image" class="width-fw">' . $this->pi_getLL('admin_image') . '</label>
	<div id="fe_user_image">
		<noscript>
			<input name="fe_user_image" type="file" />
		</noscript>
	</div>
';
if ($this->post['image']) {
    $temp_file = $this->DOCUMENT_ROOT . 'uploads/pics/' . $this->post['image'];
    $size = getimagesize($temp_file);
    if ($size[0] > 150) {
        $size[0] = 150;
    }
    $images_tab_block .= '
	<div class="fe_user_image">
		<img src="uploads/pics/' . $this->post['image'] . '" width="' . $size[0] . '" />
	</div>
	';
}
$images_tab_block .= '

	<input name="tx_multishop_pi1[image]" id="ajax_fe_user_image" type="hidden" value="" />';
// todo: question from Bas: what is edit_product code doing in edit_customer
if ($_REQUEST['action'] == 'edit_product' and $this->post['image']) {
    $images_tab_block .= '<img src="' . mslib_befe::getImagePath($this->post['image'], 'products', '50') . '">';
    $images_tab_block .= ' <a href="' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_ajax&cid=' . $_REQUEST['cid'] . '&pid=' . $_REQUEST['pid'] . '&action=edit_product&delete_image=products_image') . '" onclick="return confirm(\'' . addslashes($this->pi_getLL('admin_label_js_are_you_sure')) . '\')"><img src="' . $this->FULL_HTTP_URL_MS . 'templates/images/icons/delete2.png" border="0" alt="' . $this->pi_getLL('admin_delete_image') . '"></a>';
}
$images_tab_block .= '</div>';
$images_tab_block .= '
<script>
jQuery(document).ready(function($) {
	var uploader = new qq.FileUploader({
		element: document.getElementById(\'fe_user_image' . '\'),
		action: \'' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_ajax_upload&tx_multishop_pi1[uid]=' . $user['uid']) . '\',
		params: {
			file_type: \'fe_user_image' . '\'
		},
		template: \'<div class="qq-uploader">\' +
				  \'<div class="qq-upload-drop-area"><span>' . addslashes(htmlspecialchars($this->pi_getLL('admin_label_drop_files_here_to_upload'))) . '</span></div>\' +
				  \'<div class="btn btn-primary btn-sm qq-upload-button">' . addslashes(htmlspecialchars($this->pi_getLL('choose_image'))) . '</div>\' +
				  \'<ul class="qq-upload-list"></ul>\' +
				  \'</div>\',
		onComplete: function(id, fileName, responseJSON){
			var filenameServer = responseJSON[\'filename\'];
			$("#ajax_fe_user_image").val(filenameServer);
		},
		debug: false
	});
	$("div#edit_customer").find("select").select2();
});
</script>';
// now lets load the users
$customer_groups_input .= '<div class="form-group multiselect_horizontal"><label>' . $this->pi_getLL('member_of') . '</label>' . "\n";
if ($erno) {
    //$this->post['usergroup']=$this->post['tx_multishop_pi1']['groups'];
}
/*$customer_groups_input.='<select id="groups" class="multiselect" multiple="multiple" name="tx_multishop_pi1[groups][]">';
foreach ($groups as $group) {
    $customer_groups_input.='<option value="'.$group['uid'].'"'.(mslib_fe::inUserGroup($group['uid'], $this->post['usergroup']) ? ' selected="selected"' : '').'>'.$group['title'].'</option>'."\n";
}
$customer_groups_input.='</select>';*/
$selected_groups = array();
if ($this->post['tx_multishop_pi1']['groups']) {
    $this->post['usergroup'] = $this->post['tx_multishop_pi1']['groups'];
}
$userGroupUids = explode(',', $this->post['usergroup']);
if (is_array($userGroupUids) && count($userGroupUids)) {
    foreach ($userGroupUids as $userGroupUid) {
        $usergroup = mslib_fe::getUserGroup($userGroupUid);
        if (is_array($usergroup) && $usergroup['title']) {
            $selected_groups[] = $userGroupUid;
            //$userGroupMarkupArray[]='<span class="badge">'.htmlspecialchars($usergroup['title']).'</span>';
        }
    }
}
$selected_group_str = '';
if (count($selected_groups)) {
    $selected_group_str = implode(',', $selected_groups);
}
$customer_groups_input .= '<input type="hidden" id="groups" name="tx_multishop_pi1[groups]" value="' . $selected_group_str . '" />' . "\n";
$customer_groups_input .= '<input type="hidden" name="tx_multishop_pi1[original_groups]" value="' . $selected_group_str . '" /></div>' . "\n";
//
/*$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input='';
if (is_array($groups) and count($groups)) {



	$selected_groups=array();
	foreach ($groups as $group) {
		$selected_groups[]=(in_array($group['uid'], $this->post['usergroup']) ? $group['uid'] : '');
	}

}*/
$login_as_this_user_link = '';
if ($this->get['tx_multishop_pi1']['cid']) {
    $login_as_this_user_link = '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_customers&login_as_customer=1&customer_id=' . $this->get['tx_multishop_pi1']['cid']) . '" target="_parent" class="btn btn-success">' . $this->pi_getLL('login_as_user') . '</a>';
}
$subpartArray = array();
$subpartArray['###VALUE_REFERRER###'] = '';
if ($this->post['tx_multishop_pi1']['referrer']) {
    $subpartArray['###VALUE_REFERRER###'] = $this->post['tx_multishop_pi1']['referrer'];
} else {
    $subpartArray['###VALUE_REFERRER###'] = $_SERVER['HTTP_REFERER'];
}
// global fields
// VAT ID
if ($this->ms['MODULES']['ADMIN_VAT_ID_FIELD_REQUIRED']) {
    $subpartArray['###LABEL_INPUT_VAT_ID###'] = ucfirst($this->pi_getLL('vat_id', 'VAT ID')) . '<span class="text-danger">*</span>';
    $vat_input_block = '<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">' . ucfirst($this->pi_getLL('vat_id', 'VAT ID')) . '<span class="text-danger">*</span></label>
	<input type="text" name="tx_multishop_vat_id" class="form-control tx_multishop_vat_id" id="tx_multishop_vat_id" required="required" value="' . mslib_fe::RemoveXSS($this->post['tx_multishop_vat_id']) . '"/>';
} else {
    $subpartArray['###LABEL_INPUT_VAT_ID###'] = ucfirst($this->pi_getLL('vat_id', 'VAT ID'));
    $vat_input_block = '<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">' . ucfirst($this->pi_getLL('vat_id', 'VAT ID')) . '</label>
<input type="text" name="tx_multishop_vat_id" class="form-control tx_multishop_vat_id" id="tx_multishop_vat_id" value="' . mslib_fe::RemoveXSS($this->post['tx_multishop_vat_id']) . '"/>';
}
//COC ID
if ($this->ms['MODULES']['ADMIN_COC_ID_FIELD_REQUIRED']) {
    $coc_input_block = '<label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">' . ucfirst($this->pi_getLL('coc_id', 'KvK ID')) . '<span class="text-danger">*</span></label>';
    $coc_input_block .= '<input type="text" name="tx_multishop_coc_id" class="form-control tx_multishop_coc_id" id="tx_multishop_coc_id" required="required" value="' . mslib_fe::RemoveXSS($this->post['tx_multishop_coc_id']) . '"/>';
} else {
    $coc_input_block = '<label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">' . ucfirst($this->pi_getLL('coc_id', 'KvK ID')) . '</label>';
    $coc_input_block .= '<input type="text" name="tx_multishop_coc_id" class="form-control tx_multishop_coc_id" id="tx_multishop_coc_id" value="' . mslib_fe::RemoveXSS($this->post['tx_multishop_coc_id']) . '"/>';
}
$subpartArray['###VALUE_INPUT_VAT_ID###'] = mslib_fe::RemoveXSS($this->post['tx_multishop_vat_id']);
$subpartArray['###INPUT_VAT_ID###'] = $vat_input_block;
$subpartArray['###INPUT_COC_ID###'] = $coc_input_block;
$subpartArray['###LABEL_IMAGE###'] = ucfirst($this->pi_getLL('image'));
$subpartArray['###VALUE_IMAGE###'] = $images_tab_block;
$subpartArray['###CUSTOM_MARKER_ABOVE_PAYMENT_CONDITION_FORM_FIELD###'] = '';
$subpartArray['###CUSTOM_MARKER_BELOW_IMAGE_FORM_FIELD###'] = '';
$subpartArray['###LABEL_BUTTON_ADMIN_CANCEL###'] = $this->pi_getLL('admin_cancel');
$subpartArray['###LINK_BUTTON_CANCEL###'] = $subpartArray['###VALUE_REFERRER###'];
$subpartArray['###LABEL_BUTTON_ADMIN_SAVE###'] = $this->pi_getLL('admin_save');
$subpartArray['###LABEL_BUTTON_ADMIN_SAVE_CLOSE###'] = ($this->get['action'] == 'edit_customer') ? $this->pi_getLL('admin_update_close') : $this->pi_getLL('admin_save_close');
$subpartArray['###CUSTOMER_FORM_HEADING###'] = $this->pi_getLL('admin_label_tabs_edit_customer');
$subpartArray['###MASTER_SHOP###'] = '';
$subpartArray['###LABEL_BILLING_ADDRESS###'] = $this->pi_getLL('admin_label_billing_address');
$subpartArray['###LABEL_DELIVERY_ADDRESS###'] = $this->pi_getLL('admin_label_delivery_address');
$subpartArray['###CUSTOM_MARKER_ABOVE_USERNAME_FIELD###'] = '';
$subpartArray['###CUSTOM_MARKER_BELOW_USERNAME_FIELD###'] = '';
if ($_GET['action'] == 'add_customer') {
    $subpartArray['###CUSTOMER_EDIT_FORM_URL###'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $_REQUEST['action'] . '&action=' . $_REQUEST['action']);
} else {
    $subpartArray['###CUSTOMER_EDIT_FORM_URL###'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $_REQUEST['action'] . '&action=' . $_REQUEST['action'] . '&tx_multishop_pi1[cid]=' . $_REQUEST['tx_multishop_pi1']['cid']);
}
// customer to shipping/payment method mapping
$shipping_payment_method = '';
if ($this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
    $payment_methods = mslib_fe::loadPaymentMethods();
    // loading shipping methods eof
    $shipping_methods = mslib_fe::loadShippingMethods();
    if (count($payment_methods) or count($shipping_methods)) {
        // the value is are the negate value
        // negate 1 mean the shipping/payment are excluded
        $shipping_payment_method .= '
<div class="form-horizontal" id="payment-shipping-methods-mapping-wrapper">
						<div class="div_products_mappings toggle_advanced_option" id="msEditCustomerInputPaymentMethod">
							<label class="control-label col-md-2">' . $this->pi_getLL('admin_mapped_methods') . '</label>
							<div class="col-md-10">
							<div class="innerbox_methods">
								<div class="innerbox_payment_methods">
									<p class="form-control-static"><strong>' . $this->pi_getLL('admin_payment_methods') . '</strong></p>
									';
        // load mapped ids
        $method_mappings = array();
        if ($this->get['tx_multishop_pi1']['cid']) {
            $method_mappings = mslib_befe::getMethodsByCustomer($this->get['tx_multishop_pi1']['cid']);
        }
        $tr_type = '';
        if (count($payment_methods)) {
            foreach ($payment_methods as $code => $item) {
                if (!$tr_type or $tr_type == 'even') {
                    $tr_type = 'odd';
                } else {
                    $tr_type = 'even';
                }
                $count++;
                $shipping_payment_method .= '<div class="form-group" id="multishop_payment_method_' . $item['id'] . '" style="margin-bottom:20px">
                    <label class="control-label col-md-4">' . $item['name'] . '</label>
                    <div class="col-md-8">';
                $paymentSettingSetFrom = 'Disabled in global settings';
                $paymentChecked = ' data-setting-from="global-disable"';
                // Payment global setting
                if ($item['status'] > 0) {
                    $paymentSettingSetFrom = 'Enabled in global settings';
                    $paymentChecked = ' checked="checked" data-setting-from="global-enable"';
                    if (!$item['enable_on_default']) {
                        $paymentChecked = ' data-setting-from="global-enable"';
                        $paymentSettingSetFrom .= ' / Hidden in frontend';
                    }
                }
                // Checked for local setting
                if (is_array($method_mappings['payment']) && in_array($item['id'], $method_mappings['payment'])) {
                    // Checked
                    if (!$method_mappings['payment']['method_data'][$item['id']]['negate']) {
                        $paymentChecked = ' checked="checked" data-setting-from="local-enable"';
                        //$paymentSettingSetFrom = '';
                    }
                    // Unchecked
                    if ($method_mappings['payment']['method_data'][$item['id']]['negate'] > 0) {
                        $paymentChecked = ' data-setting-from="local-disable"';
                        //$paymentSettingSetFrom = '';
                    }
                }
                if ($_GET['action'] == 'add_customer') {
                    $paymentChecked = ' data-setting-from="add-new-customer-local-disable"';
                }
                $shipping_payment_method .= '
                <div class="toggleButton">
                    <input type="checkbox" class="payment_method_cb" id="payment_method'.$item['id'].'" name="payment_method[' . mslib_fe::RemoveXSS($item['id']) . ']" value="1"'.$paymentChecked.'>
                    <label for="payment_method'.$item['id'].'" style="width:60px">
                        <span class="toggleButtonTextEnable"></span>
                        <span class="toggleButtonTextDisable"></span>
                        <span class="toggleButtonHandler"></span>
                    </label>
                    <span style="vertical-align: middle; margin-left:6px">'.(!empty($paymentSettingSetFrom) ? '('.$paymentSettingSetFrom.')' : '').'</span>
                </div>';
                $shipping_payment_method .= '</div>
                </div>';
            }
        }
        $shipping_payment_method .= '
								</div>
								</div>
								<div class="innerbox_shipping_methods" id="msEditCustomerInputShippingMethod">
									<p class="form-control-static"><strong>' . $this->pi_getLL('admin_shipping_methods') . '</strong></p>
							 		';
        $count = 0;
        $tr_type = '';
        if (count($shipping_methods)) {
            foreach ($shipping_methods as $code => $item) {
                $count++;
                $shipping_payment_method .= '<div class="form-group" id="multishop_shipping_method">
                    <label class="control-label col-md-4">' . $item['name'] . '</label>
                <div class="col-md-8">';
                $shippingSettingSetFrom = 'Disabled in global settings';
                $shippingChecked = ' data-setting-from="global-disable"';
                // Payment global setting
                if ($item['status'] > 0) {
                    $shippingSettingSetFrom = 'Enabled in global settings';
                    $shippingChecked = ' checked="checked" data-setting-from="global-enable"';
                }
                // Checked for local setting
                if (is_array($method_mappings['shipping']) && in_array($item['id'], $method_mappings['shipping'])) {
                    // Checked
                    if (!$method_mappings['shipping']['method_data'][$item['id']]['negate']) {
                        //$shippingSettingSetFrom = '';
                        $shippingChecked = ' checked="checked" data-setting-from="local-enable"';
                    }
                    // Unchecked
                    if ($method_mappings['shipping']['method_data'][$item['id']]['negate'] > 0) {
                        //$shippingSettingSetFrom = '';
                        $shippingChecked = ' data-setting-from="local-disable"';
                    }
                }
                if ($_GET['action'] == 'add_customer') {
                    $shippingChecked = ' data-setting-from="add-new-customer-local-disable"';
                }
                $shipping_payment_method .= '
                <div class="toggleButton">
                    <input type="checkbox" class="shipping_method_cb" id="shipping_method'.$item['id'].'" name="shipping_method[' . mslib_fe::RemoveXSS($item['id']) . ']" value="1"'.$shippingChecked.'>
                    <label for="shipping_method'.$item['id'].'" style="width:60px">
                        <span class="toggleButtonTextEnable"></span>
                        <span class="toggleButtonTextDisable"></span>
                        <span class="toggleButtonHandler"></span>
                    </label>
                    <span style="vertical-align: middle; font-weight: bold; margin-left:6px">'.(!empty($shippingSettingSetFrom) ? '('.$shippingSettingSetFrom.')' : '').'</span>
                </div>';
                $shipping_payment_method .= '</div>
                </div>';
            }
        }
        $shipping_payment_method .= '

								</div>
							</div>
						</div></div>';
    }
}
// delivery_address default value
$subpartArray['###LABEL_DIFFERENT_DELIVERY_ADDRESS###'] = $this->pi_getLL('click_here_if_your_delivery_address_is_different_from_your_billing_address');
$subpartArray['###LABEL_DELIVERY_GENDER###'] = ucfirst($this->pi_getLL('title'));
$subpartArray['###LABEL_DELIVERY_GENDER_MR###'] = ucfirst($this->pi_getLL('mr'));
$subpartArray['###LABEL_DELIVERY_GENDER_MRS###'] = ucfirst($this->pi_getLL('mrs'));
$subpartArray['###LABEL_DELIVERY_FIRSTNAME###'] = ucfirst($this->pi_getLL('first_name'));
$subpartArray['###LABEL_DELIVERY_MIDDLENAME###'] = ucfirst($this->pi_getLL('middle_name'));
$subpartArray['###LABEL_DELIVERY_LASTNAME###'] = ucfirst($this->pi_getLL('last_name'));
$subpartArray['###LABEL_DELIVERY_COMPANY###'] = ucfirst($this->pi_getLL('company'));
$subpartArray['###LABEL_DELIVERY_BUILDING###'] = ucfirst($this->pi_getLL('building'));
$subpartArray['###LABEL_DELIVERY_STREET_ADDRESS###'] = ucfirst($this->pi_getLL('street_address'));
$subpartArray['###LABEL_DELIVERY_STREET_ADDRESS_NUMBER###'] = ucfirst($this->pi_getLL('street_address_number'));
$subpartArray['###LABEL_DELIVERY_ADDRESS_EXTENTION###'] = ucfirst($this->pi_getLL('address_extension'));
$subpartArray['###LABEL_DELIVERY_POSTCODE###'] = ucfirst($this->pi_getLL('zip'));
$subpartArray['###LABEL_DELIVERY_CITY###'] = ucfirst($this->pi_getLL('city'));
$subpartArray['###LABEL_DELIVERY_EMAIL###'] = ucfirst($this->pi_getLL('e-mail_address'));
$subpartArray['###LABEL_DELIVERY_TELEPHONE###'] = ucfirst($this->pi_getLL('telephone'));//.($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'] ? '<span class="text-danger">*</span>' : '');
$subpartArray['###LABEL_DELIVERY_MOBILE###'] = ucfirst($this->pi_getLL('mobile'));
$subpartArray['###LABEL_DELIVERY_BIRTHDATE###'] = ucfirst($this->pi_getLL('birthday'));
$subpartArray['###COUNTRIES_DELIVERY_INPUT###'] = $delivery_countries_input;
$subpartArray['###DIFFERENT_DELIVERY_ADDRESS_CHECKED###'] = '';
$subpartArray['###DELIVERY_GENDER_MR_CHECKED###'] = '';
$subpartArray['###DELIVERY_GENDER_MRS_CHECKED###'] = '';
$subpartArray['###VALUE_DELIVERY_FIRSTNAME###'] = '';
$subpartArray['###VALUE_DELIVERY_MIDDLENAME###'] = '';
$subpartArray['###VALUE_DELIVERY_LASTNAME###'] = '';
//
$subpartArray['###VALUE_DELIVERY_COMPANY###'] = '';
//
$subpartArray['###VALUE_DELIVERY_BUILDING###'] = '';
$subpartArray['###VALUE_DELIVERY_STREET_ADDRESS###'] = '';
$subpartArray['###VALUE_DELIVERY_STREET_ADDRESS_NUMBER###'] = '';
$subpartArray['###VALUE_DELIVERY_ADDRESS_EXTENTION###'] = '';
$subpartArray['###VALUE_DELIVERY_POSTCODE###'] = '';
$subpartArray['###VALUE_DELIVERY_CITY###'] = '';
$subpartArray['###VALUE_DELIVERY_EMAIL###'] = '';
$subpartArray['###VALUE_DELIVERY_TELEPHONE###'] = '';
$subpartArray['###VALUE_DELIVERY_MOBILE###'] = '';
$subpartArray['###VALUE_DELIVERY_VISIBLE_BIRTHDATE###'] = '';
$subpartArray['###VALUE_DELIVERY_HIDDEN_BIRTHDATE###'] = '';
// delivery address default value EOL
switch ($_REQUEST['action']) {
    case 'edit_customer':
        if (is_numeric($user['uid']) && $user['uid'] > 0) {
            foreach ($user as $userIdxKey => $userValue) {
                $user[$userIdxKey] = mslib_fe::RemoveXSS($userValue);
            }
            $subpartArray['###LABEL_USERNAME###'] = ucfirst($this->pi_getLL('username')) . '<span class="text-danger">*</span>';
            if ($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY'] > 0 || !isset($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY'])) {
                $subpartArray['###USERNAME_READONLY###'] = (($this->get['action'] == 'edit_customer' && $this->get['tx_multishop_pi1']['cid'] > 0) ? 'readonly="readonly"' : '');
            } else {
                $subpartArray['###USERNAME_READONLY###'] = '';
            }
            $subpartArray['###EDIT_CUSTOMER_HEADER###'] = htmlspecialchars($this->pi_getLL('admin_label_tabs_edit_customer'));
            $subpartArray['###VALUE_USERNAME###'] = mslib_fe::RemoveXSS($this->post['username']);
            $subpartArray['###LABEL_PASSWORD###'] = ucfirst($this->pi_getLL('password'));
            if ($this->masterShop) {
                $multishop_content_objects = mslib_fe::getActiveShop();
                if (count($multishop_content_objects) > 1) {
                    $counter = 0;
                    $total = count($multishop_content_objects);
                    $selectContent .= '<select name="page_uid"><option value="">' . ucfirst($this->pi_getLL('choose')) . '</option>' . "\n";
                    foreach ($multishop_content_objects as $pageinfo) {
                        $selectContent .= '<option value="' . $pageinfo['uid'] . '"' . ($pageinfo['uid'] == $this->post['page_uid'] ? ' selected' : '') . '>' . mslib_fe::RemoveXSS($pageinfo['title']) . '</option>';
                        $counter++;
                    }
                    $selectContent .= '</select>' . "\n";
                    if ($selectContent) {
                        $subpartArray['###MASTER_SHOP###'] = '
						<div class="form-group">
							<label for="store" id="account-store">' . $this->pi_getLL('store') . '</label>
							' . $selectContent . '
						</div>
					';
                    }
                }
            }
            $subpartArray['###VALUE_PASSWORD###'] = '';
            $subpartArray['###HIDE_PASSWORD###'] = '';
            if ($this->ms['MODULES']['HIDE_PASSWORD_FIELD_IN_EDIT_CUSTOMER'] == '1') {
                $subpartArray['###HIDE_PASSWORD###'] = ' style="display:none"';
            }
            $subpartArray['###LABEL_GENDER###'] = ucfirst($this->pi_getLL('title'));
            $subpartArray['###GENDER_MR_CHECKED###'] = (($this->post['gender'] == '0') ? 'checked="checked"' : '');
            $subpartArray['###LABEL_GENDER_MR###'] = ucfirst($this->pi_getLL('mr'));
            $subpartArray['###GENDER_MRS_CHECKED###'] = (($this->post['gender'] == '1') ? 'checked="checked"' : '');
            $subpartArray['###LABEL_NEWSLETTER###'] = ucfirst($this->pi_getLL('newsletter'));
            $subpartArray['###NEWSLETTER_CHECKED###'] = (($this->post['tx_multishop_newsletter'] == '1') ? 'checked="checked"' : '');
            $subpartArray['###LABEL_GENDER_MRS###'] = ucfirst($this->pi_getLL('mrs'));
            $subpartArray['###LABEL_FIRSTNAME###'] = ucfirst($this->pi_getLL('first_name'));
            $subpartArray['###VALUE_FIRSTNAME###'] = mslib_fe::RemoveXSS($this->post['first_name']);
            $subpartArray['###LABEL_MIDDLENAME###'] = ucfirst($this->pi_getLL('middle_name'));
            $subpartArray['###VALUE_MIDDLENAME###'] = mslib_fe::RemoveXSS($this->post['middle_name']);
            $subpartArray['###LABEL_LASTNAME###'] = ucfirst($this->pi_getLL('last_name'));
            $subpartArray['###VALUE_LASTNAME###'] = mslib_fe::RemoveXSS($this->post['last_name']);
            //
            $company_validation = '';
            $subpartArray['###LABEL_COMPANY###'] = ucfirst($this->pi_getLL('company'));
            if ($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY']) {
                $subpartArray['###LABEL_COMPANY###'] .= '*';
                $company_validation = ' required="required" data-h5-errorid="invalid-company" title="' . $this->pi_getLL('company_is_required') . '"';
            }
            $subpartArray['###COMPANY_VALIDATION###'] = $company_validation;
            $subpartArray['###COMPANY_COL_SIZE###'] = 12;
            $subpartArray['###DELIVERY_COMPANY_COL_SIZE###'] = 12;
            $subpartArray['###VALUE_COMPANY###'] = mslib_fe::RemoveXSS($this->post['company']);
            // department input
            $subpartArray['###DEPARTMENT_INPUT_FIELD###'] = '';
            $subpartArray['###COMPANY_COL_SIZE###'] = 12;
            if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                $subpartArray['###COMPANY_COL_SIZE###'] = 6;
                $subpartArray['###DEPARTMENT_INPUT_FIELD###'] = '<div class="col-md-6">
                    <label for="department" id="account-department">' . $this->pi_getLL('department') . '</label>
                    <input type="text" name="department" class="form-control department" id="department" value="' . mslib_fe::RemoveXSS($this->post['department']) . '" />
                </div>';
            }
            $md5_list = array();
            $md5_list[] = ($this->post['gender'] == '0' ? 'm' : 'f');
            $md5_list[] = $this->post['first_name'];
            $md5_list[] = $this->post['middle_name'];
            $md5_list[] = $this->post['last_name'];
            $md5_list[] = $this->post['company'];
            $md5_list[] = $this->post['street_name'];
            $md5_list[] = $this->post['address_number'];
            $md5_list[] = $this->post['address_ext'];
            $md5_list[] = $this->post['zip'];
            $md5_list[] = $this->post['city'];
            $md5_list[] = $this->post['telephone'];
            $md5_list[] = $this->post['email'];
            $billing_address_md5 = md5(implode("", $md5_list));
            //
            $subpartArray['###LABEL_BUILDING###'] = ucfirst($this->pi_getLL('building'));
            $subpartArray['###VALUE_BUILDING###'] = mslib_fe::RemoveXSS($this->post['building']);
            $subpartArray['###LABEL_STREET_ADDRESS###'] = ucfirst($this->pi_getLL('street_address'));
            $subpartArray['###VALUE_STREET_ADDRESS###'] = mslib_fe::RemoveXSS($this->post['street_name']);
            $subpartArray['###LABEL_STREET_ADDRESS_NUMBER###'] = ucfirst($this->pi_getLL('street_address_number'));
            $subpartArray['###VALUE_STREET_ADDRESS_NUMBER###'] = mslib_fe::RemoveXSS($this->post['address_number']);
            $subpartArray['###LABEL_ADDRESS_EXTENTION###'] = ucfirst($this->pi_getLL('address_extension'));
            $subpartArray['###VALUE_ADDRESS_EXTENTION###'] = mslib_fe::RemoveXSS($this->post['address_ext']);
            $subpartArray['###LABEL_POSTCODE###'] = ucfirst($this->pi_getLL('zip'));
            $subpartArray['###VALUE_POSTCODE###'] = mslib_fe::RemoveXSS($this->post['zip']);
            $subpartArray['###LABEL_CITY###'] = ucfirst($this->pi_getLL('city'));
            $subpartArray['###VALUE_CITY###'] = mslib_fe::RemoveXSS($this->post['city']);
            $subpartArray['###COUNTRIES_INPUT###'] = $countries_input;
            $subpartArray['###LABEL_EMAIL###'] = ucfirst($this->pi_getLL('e-mail_address'));
            $subpartArray['###VALUE_EMAIL###'] = mslib_fe::RemoveXSS($this->post['email']);
            $subpartArray['###LABEL_WEBSITE###'] = ucfirst($this->pi_getLL('website'));
            $subpartArray['###VALUE_WEBSITE###'] = mslib_fe::RemoveXSS($this->post['www']);
            $subpartArray['###LABEL_TELEPHONE###'] = ucfirst($this->pi_getLL('telephone'));//.($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'] ? '<span class="text-danger">*</span>' : '');
            $subpartArray['###VALUE_TELEPHONE###'] = mslib_fe::RemoveXSS($this->post['telephone']);
            $subpartArray['###LABEL_MOBILE###'] = ucfirst($this->pi_getLL('mobile'));
            $subpartArray['###VALUE_MOBILE###'] = mslib_fe::RemoveXSS($this->post['mobile']);
            $subpartArray['###LABEL_BIRTHDATE###'] = ucfirst($this->pi_getLL('birthday'));
            $subpartArray['###VALUE_VISIBLE_BIRTHDATE###'] = ($this->post['date_of_birth'] ? htmlspecialchars(strftime("%x", $this->post['date_of_birth'])) : '');
            $subpartArray['###VALUE_HIDDEN_BIRTHDATE###'] = ($this->post['date_of_birth'] ? htmlspecialchars(strftime("%F", $this->post['date_of_birth'])) : '');
            $subpartArray['###DELIVERY_COMPANY_COL_SIZE###'] = 12;
            $subpartArray['###DELIVERY_DEPARTMENT_INPUT_FIELD###'] = '';
            if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                $subpartArray['###DELIVERY_COMPANY_COL_SIZE###'] = 6;
                $subpartArray['###DELIVERY_DEPARTMENT_INPUT_FIELD###'] = '<div class="col-md-6">
                    <label for="delivery_department" id="account-delivery_department">' . $this->pi_getLL('department') . '</label>
                    <input type="text" name="delivery_department" class="form-control delivery_department" id="delivery_department" value="" />
                </div>';
            }
            // delivery address
            $delivery_address = mslib_fe::getFeUserTTaddressDetails($user['uid'], 'delivery');
            if ($delivery_address) {
                $md5_list = array();
                $md5_list[] = $delivery_address['gender'];
                $md5_list[] = $delivery_address['first_name'];
                $md5_list[] = $delivery_address['middle_name'];
                $md5_list[] = $delivery_address['last_name'];
                $md5_list[] = $delivery_address['company'];
                $md5_list[] = $delivery_address['street_name'];
                $md5_list[] = $delivery_address['address_number'];
                $md5_list[] = $delivery_address['address_ext'];
                $md5_list[] = $delivery_address['zip'];
                $md5_list[] = $delivery_address['city'];
                $md5_list[] = $delivery_address['phone'];
                $md5_list[] = $delivery_address['email'];
                $delivery_address_md5 = md5(implode("", $md5_list));
                if ($billing_address_md5 != $delivery_address_md5) {
                    $subpartArray['###DIFFERENT_DELIVERY_ADDRESS_CHECKED###'] = ' checked="checked"';
                }
                $subpartArray['###DELIVERY_GENDER_MR_CHECKED###'] = (($delivery_address['gender'] == 'm') ? 'checked="checked"' : '');
                $subpartArray['###DELIVERY_GENDER_MRS_CHECKED###'] = (($delivery_address['gender'] == 'f') ? 'checked="checked"' : '');
                $subpartArray['###VALUE_DELIVERY_FIRSTNAME###'] = mslib_fe::RemoveXSS($delivery_address['first_name']);
                $subpartArray['###VALUE_DELIVERY_MIDDLENAME###'] = mslib_fe::RemoveXSS($delivery_address['middle_name']);
                $subpartArray['###VALUE_DELIVERY_LASTNAME###'] = mslib_fe::RemoveXSS($delivery_address['last_name']);
                //
                $subpartArray['###VALUE_DELIVERY_COMPANY###'] = mslib_fe::RemoveXSS($delivery_address['company']);
                // department input
                if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
                    $subpartArray['###DELIVERY_DEPARTMENT_INPUT_FIELD###'] = '<div class="col-md-6">
                        <label for="delivery_department" id="account-delivery_department">' . $this->pi_getLL('department') . '</label>
                        <input type="text" name="delivery_department" class="form-control delivery_department" id="delivery_department" value="' . mslib_fe::RemoveXSS($delivery_address['department']) . '" />
                    </div>';
                }
                //
                $subpartArray['###VALUE_DELIVERY_BUILDING###'] = mslib_fe::RemoveXSS($delivery_address['building']);
                $subpartArray['###VALUE_DELIVERY_STREET_ADDRESS###'] = mslib_fe::RemoveXSS($delivery_address['street_name']);
                $subpartArray['###VALUE_DELIVERY_STREET_ADDRESS_NUMBER###'] = mslib_fe::RemoveXSS($delivery_address['address_number']);
                $subpartArray['###VALUE_DELIVERY_ADDRESS_EXTENTION###'] = mslib_fe::RemoveXSS($delivery_address['address_ext']);
                $subpartArray['###VALUE_DELIVERY_POSTCODE###'] = mslib_fe::RemoveXSS($delivery_address['zip']);
                $subpartArray['###VALUE_DELIVERY_CITY###'] = mslib_fe::RemoveXSS($delivery_address['city']);
                $subpartArray['###VALUE_DELIVERY_EMAIL###'] = mslib_fe::RemoveXSS($delivery_address['email']);
                $subpartArray['###VALUE_DELIVERY_TELEPHONE###'] = mslib_fe::RemoveXSS($delivery_address['phone']);
                $subpartArray['###VALUE_DELIVERY_MOBILE###'] = mslib_fe::RemoveXSS($delivery_address['mobile']);
                $subpartArray['###VALUE_DELIVERY_VISIBLE_BIRTHDATE###'] = ($delivery_address['date_of_birth'] ? htmlspecialchars(strftime("%x", $delivery_address['date_of_birth'])) : '');
                $subpartArray['###VALUE_DELIVERY_HIDDEN_BIRTHDATE###'] = ($delivery_address['date_of_birth'] ? htmlspecialchars(strftime("%F", $delivery_address['date_of_birth'])) : '');
            }
            $subpartArray['###LABEL_DISCOUNT###'] = ucfirst($this->pi_getLL('discount'));
            $subpartArray['###VALUE_DISCOUNT###'] = ($this->post['tx_multishop_discount'] > 0 ? mslib_fe::RemoveXSS($this->post['tx_multishop_discount']) : '');
            $subpartArray['###LABEL_PAYMENT_CONDITION###'] = ucfirst($this->pi_getLL('payment_condition'));
            $subpartArray['###VALUE_PAYMENT_CONDITION###'] = (isset($this->post['tx_multishop_payment_condition']) ? mslib_fe::RemoveXSS($this->post['tx_multishop_payment_condition']) : '');
            $subpartArray['###LABEL_FOREIGN_CUSTOMER_ID###'] = ucfirst($this->pi_getLL('foreign_customer_id'));
            $subpartArray['###VALUE_FOREIGN_CUSTOMER_ID###'] = ($this->post['foreign_customer_id'] > 0 ? mslib_fe::RemoveXSS($this->post['foreign_customer_id']) : '');
            $subpartArray['###CUSTOMER_GROUPS_INPUT###'] = $customer_groups_input;
            $subpartArray['###VALUE_CUSTOMER_ID###'] = $this->get['tx_multishop_pi1']['cid'];
            if ($_GET['action'] == 'edit_customer') {
                $subpartArray['###LABEL_BUTTON_SAVE###'] = ucfirst($this->pi_getLL('update_account'));
            } else {
                $subpartArray['###LABEL_BUTTON_SAVE###'] = ucfirst($this->pi_getLL('save'));
            }
            $subpartArray['###LOGIN_AS_THIS_USER_LINK###'] = $login_as_this_user_link;
            $customer_details = '';
            $markerArray = array();
            if ($this->post['image']) {
                $markerArray['CUSTOMER_IMAGE'] = '<div class="msAdminFeUserImage"><img src="uploads/pics/' . $this->post['image'] . '" width="' . $size[0] . '" /></div>';
            } else {
                $markerArray['CUSTOMER_IMAGE'] = '';
            }
            $customer_billing_address = mslib_fe::getFeUserTTaddressDetails($this->get['tx_multishop_pi1']['cid']);
            $customer_delivery_address = mslib_fe::getFeUserTTaddressDetails($this->get['tx_multishop_pi1']['cid'], 'delivery');
            // Sanitize it
            foreach ($customer_billing_address as $billingIdxKey => $billingValue) {
                $customer_billing_address[$billingIdxKey] = mslib_fe::RemoveXSS($billingValue);
            }
            foreach ($customer_delivery_address as $deliveryIdxKey => $deliveryValue) {
                $customer_delivery_address[$deliveryIdxKey] = mslib_fe::RemoveXSS($deliveryValue);
            }
            if (!$customer_billing_address['address']) {
                $customer_billing_address['address'] = preg_replace('/\s+/', ' ', $customer_billing_address['street_name'] . ' ' . $customer_billing_address['address_number'] . ' ' . $customer_billing_address['address_ext']);
            }
            if (!$customer_delivery_address['address']) {
                $customer_delivery_address['address'] = preg_replace('/\s+/', ' ', $customer_delivery_address['street_name'] . ' ' . $customer_delivery_address['address_number'] . ' ' . $customer_delivery_address['address_ext']);
            }
            if ($customer_billing_address['name'] && $customer_billing_address['phone'] && $customer_billing_address['email']) {
                $fullname = $customer_billing_address['name'];
                $telephone = $customer_billing_address['phone'];
                $email_address = $customer_billing_address['email'];
            } else {
                $fullname = $this->post['name'];
                $telephone = $this->post['telephone'];
                $email_address = $this->post['email'];
            }
            $company_name = '';
            if ($customer_billing_address['address'] && $customer_billing_address['zip'] && $customer_billing_address['city']) {
                $billing_street_address = $customer_billing_address['address'];
                $billing_postcode = $customer_billing_address['zip'] . ' ' . $customer_billing_address['city'];
                $billing_country = ucwords(mslib_befe::strtolower($customer_billing_address['country']));
            } else {
                $billing_street_address = $user['address'];
                $billing_postcode = $user['zip'] . ' ' . $user['city'];
                $billing_country = ucwords(mslib_befe::strtolower($user['country']));
            }
            if ($user['company']) {
                $company_name = $user['company'];
            }
            if (!$company_name && $customer_billing_address['company']) {
                $company_name = $customer_billing_address['company'];
            }
            if ($customer_delivery_address['address'] && $customer_delivery_address['zip'] && $customer_delivery_address['city']) {
                $delivery_street_address = $customer_delivery_address['address'];
                $delivery_postcode = $customer_delivery_address['zip'] . ' ' . $customer_delivery_address['city'];
                $delivery_country = ucwords(mslib_befe::strtolower($customer_delivery_address['country']));
            } else {
                $delivery_street_address = $user['address'];
                $delivery_postcode = $user['zip'] . ' ' . $user['city'];
                $delivery_country = ucwords(mslib_befe::strtolower($user['country']));
            }
            $markerArray['DETAILS_COMPANY_NAME'] = $company_name;
            $actionButtons = array();
            if (!$markerArray['DETAILS_COMPANY_NAME']) {
                $markerArray['DETAILS_COMPANY_NAME'] = $fullname;
            }
            $markerArray['BILLING_COMPANY'] = '';
            if ($company_name) {
                $markerArray['BILLING_COMPANY'] = $company_name . '<br/>';
            }
            $markerArray['BILLING_FULLNAME'] = $fullname . '<br/>';
            $markerArray['BILLING_TELEPHONE'] = '';
            if ($telephone) {
                $markerArray['BILLING_TELEPHONE'] .= ucfirst($this->pi_getLL('telephone')) . ': ' . $telephone . '<br/>';
                $actionLink = 'callto:' . $telephone;
                $actionButtons['call'] = '<a href="' . $actionLink . '" class="btn btn-xs btn-default"><i class="fa fa-phone-square"></i> ' . $this->pi_getLL('call') . '</a>';
            }
            $markerArray['BILLING_EMAIL'] = '';
            if ($email_address) {
                $markerArray['BILLING_EMAIL'] .= ucfirst($this->pi_getLL('e-mail_address')) . ': ' . $email_address . '<br/>';
                $actionLink = 'mailto:' . $email_address;
                $actionButtons['email'] = '<a href="' . $actionLink . '" class="btn btn-xs btn-default"><i class="fa fa-envelope-o"></i> ' . $this->pi_getLL('email') . '</a>';
            }
            $address = array();
            $address[] = rawurlencode($user['address']);
            $address[] = rawurlencode($user['zip']);
            $address[] = rawurlencode($user['city']);
            $address[] = rawurlencode($user['country']);
            $actionLink = 'http://maps.google.com/maps?daddr=' . implode('+', $address);
            $actionButtons['travel_guide'] = '<a href="' . $actionLink . '" rel="nofollow" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-map-marker"></i> ' . $this->pi_getLL('travel_guide') . '</a>';
            $markerArray['BILLING_COMPANY_ACTION_NAV'] = '';
            // custom page hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['actionButtonsBillingCompanyBoxPreProc'])) {
                $params = array(
                        'actionButtons' => &$actionButtons,
                        'customer' => &$user,
                        'markerArray' => &$markerArray
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['actionButtonsBillingCompanyBoxPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom page hook that can be controlled by third-party plugin eol
            $userGroupMarkupArray = array();
            $userGroupUids = explode(',', $user['usergroup']);
            if (is_array($userGroupUids) && count($userGroupUids)) {
                foreach ($userGroupUids as $userGroupUid) {
                    $usergroup = mslib_fe::getUserGroup($userGroupUid);
                    if (is_array($usergroup) && $usergroup['title']) {
                        $userGroupMarkupArray[] = '<span class="badge">' . htmlspecialchars($usergroup['title']) . '</span>';
                    }
                }
            }
            if (count($userGroupMarkupArray)) {
                $markerArray['BILLING_FULLNAME'] .= '<div class="group_badges">' . implode(' ', $userGroupMarkupArray) . '</div>';
            }
            if (count($actionButtons)) {
                $markerArray['BILLING_COMPANY_ACTION_NAV'] = '<div class="btn-group">';
                foreach ($actionButtons as $actionButton) {
                    $markerArray['BILLING_COMPANY_ACTION_NAV'] .= $actionButton;
                }
                $markerArray['BILLING_COMPANY_ACTION_NAV'] .= '</div>';
            }
            $markerArray['CUSTOMER_ID'] = $this->pi_getLL('admin_customer_id') . ': ' . $user['uid'] . '<br/>';
            if ($user['crdate'] > 0) {
                $user['crdate'] = strftime("%a. %x %X", $user['crdate']);
            } else {
                $user['crdate'] = '';
            }
            $markerArray['REGISTERED_DATE'] = $this->pi_getLL('created') . ': ' . $user['crdate'] . '<br/>';
            if ($user['lastlogin']) {
                $user['lastlogin'] = strftime("%a. %x %X", $user['lastlogin']);
            } else {
                $user['lastlogin'] = '-';
            }
            $markerArray['LAST_LOGIN'] = $this->pi_getLL('latest_login') . ': ' . $user['lastlogin'] . '<br/>';
            $markerArray['BILLING_BUILDING'] = '';
            if ($customer_billing_address['building']) {
                $markerArray['BILLING_BUILDING'] = $customer_billing_address['building'] . '<br/>';
            }
            $markerArray['BILLING_ADDRESS'] = $billing_street_address . '<br/>' . $billing_postcode . '<br/>' . htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $billing_country));
            $markerArray['DELIVERY_BUILDING'] = '';
            if ($customer_delivery_address['building']) {
                $markerArray['DELIVERY_BUILDING'] = $customer_delivery_address['building'] . '<br/>';
            }
            $markerArray['DELIVERY_ADDRESS'] = $delivery_street_address . '<br/>' . $delivery_postcode . '<br/>' . htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $delivery_country));
            $markerArray['GOOGLE_MAPS_URL_QUERY'] = '//maps.google.com/maps?f=q&amp;source=s_q&amp;&amp;geocode=&amp;q=' . rawurlencode($billing_street_address) . ',' . rawurlencode($billing_postcode) . ',' . rawurlencode($billing_country) . '&amp;z=14&amp;iwloc=A&amp;output=embed&amp;iwloc=';
            $markerArray['ADMIN_LABEL_CONTACT_INFO'] = $this->pi_getLL('admin_label_contact_info');
            $markerArray['ADMIN_LABEL_BILLING_ADDRESS'] = $this->pi_getLL('admin_label_billing_address');
            $markerArray['ADMIN_LABEL_DELIVERY_ADDRESS'] = $this->pi_getLL('admin_label_delivery_address');
            // customers related orders listings
            $filter = array();
            $from = array();
            $having = array();
            $match = array();
            $orderby = array();
            $where = array();
            $select = array();
            $select[] = 'o.*';
            $filter[] = 'o.customer_id=' . $user['uid'];
            if (!$this->masterShop) {
                $filter[] = 'o.page_uid=' . $this->shop_pid;
            }
            $orders_pageset = mslib_fe::getOrdersPageSet($filter, 0, 10000, array('orders_id desc'), $having, $select, $where, $from);
            $order_listing = $this->pi_getLL('no_orders_found');
            if ($orders_pageset['total_rows'] > 0) {
                $all_orders_status = mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
                $order_listing = '<div class="table-responsive">
				<table id="product_import_table" class="table table-striped table-bordered no-mb msadmin_orders_listing">
					<thead><tr>
						<th width="50" align="right" class="cellID">' . $this->pi_getLL('orders_id') . '</th>
						<th width="110" class="cellDate">' . $this->pi_getLL('order_date') . '</th>
						<th width="50" class="cellPrice">' . $this->pi_getLL('amount') . '</th>
						<th width="50" class="cell_shipping_method">' . $this->pi_getLL('shipping_method') . '</th>
						<th width="50" class="cell_payment_method">' . $this->pi_getLL('payment_method') . '</th>
						<th width="100" class="cell_status">' . $this->pi_getLL('order_status') . '</th>
						<th width="110" class="cellDate">' . $this->pi_getLL('modified_on', 'Modified on') . '</th>
						<th width="50" class="cellStatus">' . $this->pi_getLL('admin_paid') . '</th>
					</tr></thead><tbody>';
                $tr_type = 'odd';
                foreach ($orders_pageset['orders'] as $order) {
                    if (!isset($tr_type) || $tr_type == 'odd') {
                        $tr_type = 'even';
                    } else {
                        $tr_type = 'odd';
                    }
                    if ($order['is_proposal'] > 0) {
                        $order_edit_url = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $order['orders_id'] . '&action=edit_order&tx_multishop_pi1[is_proposal]=1');
                    } else {
                        $order_edit_url = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $order['orders_id'] . '&action=edit_order');
                    }
                    $paid_status = '';
                    if (!$order['paid']) {
                        $paid_status .= '<span class="admin_status_red" alt="' . $this->pi_getLL('has_not_been_paid') . '" title="' . $this->pi_getLL('has_not_been_paid') . '"></span> ';
                        $paid_status .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[action]=update_selected_orders_to_paid&selected_orders[]=' . $order['orders_id']) . '" class="update_to_paid" data-order-id="' . $order['orders_id'] . '"><span class="admin_status_green disabled" alt="' . $this->pi_getLL('change_to_paid') . '" title="' . $this->pi_getLL('change_to_paid') . '"></span></a>';
                    } else {
                        $paid_status .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[action]=update_selected_orders_to_not_paid&selected_orders[]=' . $order['orders_id']) . '" class="update_to_unpaid" data-order-id="' . $order['orders_id'] . '"><span class="admin_status_red disabled" alt="' . $this->pi_getLL('change_to_not_paid') . '" title="' . $this->pi_getLL('change_to_not_paid') . '"></span></a> ';
                        $paid_status .= '<span class="admin_status_green" alt="' . $this->pi_getLL('has_been_paid') . '" title="' . $this->pi_getLL('has_been_paid') . '"></span>';
                    }
                    $order_listing .= '<tr>
							<td class="cellID"><a href="' . $order_edit_url . '" title="' . htmlspecialchars($this->pi_getLL('loading')) . '" title="Loading" class="popover-link" rel="' . $order['orders_id'] . '">' . $order['orders_id'] . '</a></td>
							<td class="cellDate">' . strftime("%a. %x %X", $order['crdate']) . '</td>
							<td class="cellPrice">' . mslib_fe::amount2Cents($order['grand_total'], 0) . '</td>
							<td nowrap class="cell_shipping_method">' . $order['shipping_method_label'] . '</td>
							<td nowrap class="cell_payment_method">' . $order['payment_method_label'] . '</td>
							<td align="left" nowrap class="cell_status">' . $all_orders_status[$order['status']]['name'] . '</td>
							<td class="cellDate">' . ($order['status_last_modified'] ? strftime("%a. %x %X", $order['status_last_modified']) : '') . '</td>
							<td class="cellStatus">' . $paid_status . '</td>
						</tr>';
                }
                $order_listing .= '</tbody><tfoot><tr>
						<th width="50" class="cellID">' . $this->pi_getLL('orders_id') . '</th>
						<th width="110" class="cellDate">' . $this->pi_getLL('order_date') . '</th>
						<th width="50" class="cellPrice">' . $this->pi_getLL('amount') . '</th>
						<th width="50" class="cell_shipping_method">' . $this->pi_getLL('shipping_method') . '</th>
						<th width="50" class="cell_payment_method">' . $this->pi_getLL('payment_method') . '</th>
						<th width="100" class="cell_status">' . $this->pi_getLL('order_status') . '</th>
						<th width="110" class="cellDate">' . $this->pi_getLL('modified_on', 'Modified on') . '</th>
						<th width="50" class="cellStatus">' . $this->pi_getLL('admin_paid') . '</th>
					</tr></tfoot>
				</table>
			</div>';
            }
            $customer_related_orders_listing = '<div id="orders_details">';
            $customer_related_orders_listing .= '<div class="panel panel-default">';
            $customer_related_orders_listing .= '<div class="panel-heading"><h3>' . $this->pi_getLL('orders') . '</h3></div>';
            $customer_related_orders_listing .= '<div class="panel-body"><fieldset>';
            $customer_related_orders_listing .= $order_listing;
            $customer_related_orders_listing .= '</fieldset></div>';
            $customer_related_orders_listing .= '</div></div>';
            $markerArray['CUSTOMER_RELATED_ORDERS_LISTING'] = $customer_related_orders_listing;
            // custom page hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerDashBoardMainTabPreProc'])) {
                $params = array(
                        'orders' => $orders_pageset,
                        'markerArray' => &$markerArray,
                        'user' => &$user
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerDashBoardMainTabPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom page hook that can be controlled by third-party plugin eof
            $customer_details .= $this->cObj->substituteMarkerArray($subparts['details'], $markerArray, '###|###');
            $subpartArray['###DETAILS_TAB###'] = '<li role="presentation"><a href="#view_customer" aria-controls="profile" role="tab" data-toggle="tab">' . $this->pi_getLL('admin_label_tabs_details') . '</a></li>';
            $subpartArray['###DETAILS###'] = $customer_details;
            $subpartArray['###INPUT_EDIT_SHIPPING_AND_PAYMENT_METHOD###'] = $shipping_payment_method;
            $headerData = '';
            $headerData .= '
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $(document).on("click", ".update_to_paid", function(e){
                        e.preventDefault();
                        var link=$(this).attr("href");
                        var order_id=$(this).attr("data-order-id");
                        var tthis=$(this).parent();
                        jQuery.ajax({
                            type: "POST",
                            url: "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_order_payment_methods') . '",
                            dataType: \'json\',
                            data: "tx_multishop_pi1[order_id]=" + order_id,
                            success: function(d) {
                                var tmp_confirm_content =\'' . addslashes(sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_been_paid'), '%order_id%')) . '\';
                                var confirm_content = \'<div><h3 class="panel-title">\' + tmp_confirm_content . replace(\'%order_id%\', order_id) + \'</h3></div><div class="form-group" id="popup_order_wrapper_listing">\' + d.payment_method_date_purchased + \'</div>\';
                                var confirm_box=jQuery.confirm({
                                    title: \'\',
                                    content: confirm_content,
                                    columnClass: \'col-md-6 col-md-offset-4 \',
                                    confirm: function(){
                                        var payment_id=this.$b.find("#payment_method_sb_listing").val();
                                        var date_paid=this.$b.find("#orders_paid_timestamp").val();
                                        var send_paid_letter=this.$b.find("#send_payment_received_email").prop("checked") ? 1 : 0;
                                        //
                                        jQuery.ajax({
                                            type: "POST",
                                            url: "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=update_paid_status_save_popup_value') . '",
                                            dataType: \'json\',
                                            data: "tx_multishop_pi1[payment_id]=" + payment_id + "&tx_multishop_pi1[date_paid]=" + date_paid + "&tx_multishop_pi1[order_id]=" + order_id + "&tx_multishop_pi1[send_paid_letter]=" + send_paid_letter + "&tx_multishop_pi1[action]=update_selected_orders_to_paid",
                                            success: function(d) {
                                                if (d.status=="OK") {
                                                    var return_string = \'<a href="#" class="update_to_unpaid" data-order-id="\' + order_id + \'"><span class="admin_status_red disabled" alt="' . $this->pi_getLL('admin_label_disable') . '"></span></a><span class="admin_status_green" alt="' . $this->pi_getLL('admin_label_enable') . '"></span>\';
                                                    tthis.html(return_string);
                                                }
                                            }
                                        });
                                        //window.location =link;
                                    },
                                    cancel: function(){},
                                    confirmButton: \'' . $this->pi_getLL('yes') . '\',
                                    cancelButton: \'' . $this->pi_getLL('no') . '\',
                                    backgroundDismiss: false
                                });
                                confirm_box.$b.find("#orders_paid_timestamp_visual").datepicker({
                                    dateFormat: "' . $this->pi_getLL('locale_date_format_js', 'yy/mm/dd') . '",
                                    altField: "#orders_paid_timestamp",
                                    altFormat: "yy-mm-dd",
                                    changeMonth: true,
                                    changeYear: true,
                                    showOtherMonths: true,
                                    yearRange: "' . (date("Y") - 15) . ':' . (date("Y") + 2) . '"
                                });
                            }
                        });
                    });
                    $(document).on("click", ".update_to_unpaid", function(e){
                        e.preventDefault();
                        var link=$(this).attr("href");
                        var order_id=$(this).attr("data-order-id");
                        var tthis=$(this).parent();
                        var tmp_confirm_content =\'' . addslashes(sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_not_been_paid'), '%order_id%')) . '\';
                        var confirm_content=\'<div class="confirm_to_unpaid_status">\' + tmp_confirm_content.replace(\'%order_id%\', order_id) + \'</div>\';
                        //
                        $.confirm({
                            title: \'\',
                            content: confirm_content,
                            columnClass: \'col-md-6 col-md-offset-4 \',
                            confirm: function(){
                                jQuery.ajax({
                                    type: "POST",
                                    url: "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=update_paid_status_save_popup_value') . '",
                                    dataType: \'json\',
                                    data: "tx_multishop_pi1[order_id]=" + order_id + "&tx_multishop_pi1[action]=update_selected_orders_to_not_paid",
                                    success: function(d) {
                                        if (d.status=="OK") {
                                            var return_string = \'<span class="admin_status_red" alt="' . $this->pi_getLL('admin_label_disable') . '"></span><a href="#" class="update_to_paid" data-order-id="\' + order_id + \'"><span class="admin_status_green disabled" alt="' . $this->pi_getLL('admin_label_enable') . '"></span></a>\';
                                            tthis.html(return_string);
                                        }
                                    }
                                });
                            },
                            cancel: function(){},
                            confirmButton: \'Yes\',
                            cancelButton: \'NO\'
                        });
                    });
                });
             </script>';
            $GLOBALS['TSFE']->additionalHeaderData[] = $headerData;
            $headerData = '';
        }
        break;
    case 'add_customer':
    default:
        if ($this->post['gender'] == '1') {
            $mr_checked = '';
            $mrs_checked = 'checked="checked"';
        } else {
            $mr_checked = 'checked="checked"';
            $mrs_checked = '';
        }
        $subpartArray['###EDIT_CUSTOMER_HEADER###'] = htmlspecialchars($this->pi_getLL('admin_new_customer'));
        $subpartArray['###LABEL_USERNAME###'] = ucfirst($this->pi_getLL('username')) . '<span class="text-danger">*</span>';
        if ($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY'] > 0 || !isset($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY'])) {
            $subpartArray['###USERNAME_READONLY###'] = ($this->get['action'] == 'edit_customer' ? 'readonly="readonly"' : '');
        } else {
            $subpartArray['###USERNAME_READONLY###'] = '';
        }
        $subpartArray['###VALUE_USERNAME###'] = mslib_fe::RemoveXSS($this->post['username']);
        //if (empty($this->post['password']) || !isset($this->post['password'])) {
        //	$this->post['password']=substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789-=~!@#$%^&*()_+,./<>?;:[]{}\|') , 0 , 10 );
        //}
        $subpartArray['###VALUE_PASSWORD###'] = mslib_fe::RemoveXSS($this->post['password']);
        $subpartArray['###HIDE_PASSWORD###'] = '';
        if ($this->ms['MODULES']['HIDE_PASSWORD_FIELD_IN_EDIT_CUSTOMER'] == '1') {
            $subpartArray['###HIDE_PASSWORD###'] = ' style="display:none"';
        }
        $subpartArray['###LABEL_PASSWORD###'] = ucfirst($this->pi_getLL('password'));
        $subpartArray['###LABEL_GENDER###'] = ucfirst($this->pi_getLL('title'));
        $subpartArray['###GENDER_MR_CHECKED###'] = $mr_checked;
        $subpartArray['###LABEL_GENDER_MR###'] = ucfirst($this->pi_getLL('mr'));
        $subpartArray['###GENDER_MRS_CHECKED###'] = $mrs_checked;
        $subpartArray['###LABEL_NEWSLETTER###'] = ucfirst($this->pi_getLL('newsletter'));
        $subpartArray['###NEWSLETTER_CHECKED###'] = (($this->post['tx_multishop_newsletter'] == '1') ? 'checked="checked"' : '');
        $subpartArray['###LABEL_GENDER_MRS###'] = ucfirst($this->pi_getLL('mrs'));
        $subpartArray['###LABEL_FIRSTNAME###'] = ucfirst($this->pi_getLL('first_name'));
        $subpartArray['###VALUE_FIRSTNAME###'] = mslib_fe::RemoveXSS($this->post['first_name']);
        $subpartArray['###LABEL_MIDDLENAME###'] = ucfirst($this->pi_getLL('middle_name'));
        $subpartArray['###VALUE_MIDDLENAME###'] = mslib_fe::RemoveXSS($this->post['middle_name']);
        $subpartArray['###LABEL_LASTNAME###'] = ucfirst($this->pi_getLL('last_name'));
        $subpartArray['###VALUE_LASTNAME###'] = mslib_fe::RemoveXSS($this->post['last_name']);
        //
        $company_validation = '';
        $subpartArray['###LABEL_COMPANY###'] = ucfirst($this->pi_getLL('company'));
        if ($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY']) {
            //$subpartArray['###LABEL_COMPANY###'].='<span class="text-danger">*</span>';
            $company_validation = ' required="required" data-h5-errorid="invalid-company" title="' . $this->pi_getLL('company_is_required') . '"';
        }
        $subpartArray['###COMPANY_VALIDATION###'] = $company_validation;
        $subpartArray['###VALUE_COMPANY###'] = mslib_fe::RemoveXSS($this->post['company']);
        // department input
        $subpartArray['###DEPARTMENT_INPUT_FIELD###'] = '';
        $subpartArray['###COMPANY_COL_SIZE###'] = 12;
        if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
            $subpartArray['###COMPANY_COL_SIZE###'] = 6;
            $subpartArray['###DEPARTMENT_INPUT_FIELD###'] = '<div class="col-md-6">
                <label for="department" id="account-department">' . $this->pi_getLL('department') . '</label>
                <input type="text" name="department" class="form-control department" id="department" value="' . mslib_fe::RemoveXSS($this->post['department']) . '" />
            </div>';
        }
        // department input
        $subpartArray['###DELIVERY_DEPARTMENT_INPUT_FIELD###'] = '';
        $subpartArray['###DELIVERY_COMPANY_COL_SIZE###'] = 12;
        if ($this->ms['MODULES']['SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER']) {
            $subpartArray['###DELIVERY_COMPANY_COL_SIZE###'] = 6;
            $subpartArray['###DELIVERY_DEPARTMENT_INPUT_FIELD###'] = '<div class="col-md-6">
                <label for="delivery_department" id="account-delivery_department">' . $this->pi_getLL('department') . '</label>
                <input type="text" name="delivery_department" class="form-control delivery_department" id="delivery_department" value="' . mslib_fe::RemoveXSS($this->post['delivery_department']) . '" />
            </div>';
        }
        //
        $subpartArray['###LABEL_BUILDING###'] = ucfirst($this->pi_getLL('building'));
        $subpartArray['###VALUE_BUILDING###'] = mslib_fe::RemoveXSS($this->post['building']);
        $subpartArray['###LABEL_STREET_ADDRESS###'] = ucfirst($this->pi_getLL('street_address'));
        $subpartArray['###VALUE_STREET_ADDRESS###'] = mslib_fe::RemoveXSS($this->post['street_name']);
        $subpartArray['###LABEL_STREET_ADDRESS_NUMBER###'] = ucfirst($this->pi_getLL('street_address_number'));
        $subpartArray['###VALUE_STREET_ADDRESS_NUMBER###'] = mslib_fe::RemoveXSS($this->post['address_number']);
        $subpartArray['###LABEL_ADDRESS_EXTENTION###'] = ucfirst($this->pi_getLL('address_extension'));
        $subpartArray['###VALUE_ADDRESS_EXTENTION###'] = mslib_fe::RemoveXSS($this->post['address_ext']);
        $subpartArray['###LABEL_POSTCODE###'] = ucfirst($this->pi_getLL('zip'));
        $subpartArray['###VALUE_POSTCODE###'] = mslib_fe::RemoveXSS($this->post['zip']);
        $subpartArray['###LABEL_CITY###'] = ucfirst($this->pi_getLL('city'));
        $subpartArray['###VALUE_CITY###'] = mslib_fe::RemoveXSS($this->post['city']);
        $subpartArray['###COUNTRIES_INPUT###'] = $countries_input;
        $subpartArray['###LABEL_EMAIL###'] = ucfirst($this->pi_getLL('e-mail_address'));
        $subpartArray['###VALUE_EMAIL###'] = mslib_fe::RemoveXSS($this->post['email']);
        $subpartArray['###LABEL_WEBSITE###'] = ucfirst($this->pi_getLL('website'));
        $subpartArray['###VALUE_WEBSITE###'] = mslib_fe::RemoveXSS($this->post['www']);
        $subpartArray['###LABEL_TELEPHONE###'] = ucfirst($this->pi_getLL('telephone'));//.($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'] ? '<span class="text-danger">*</span>' : '');
        $subpartArray['###VALUE_TELEPHONE###'] = mslib_fe::RemoveXSS($this->post['telephone']);
        $subpartArray['###LABEL_MOBILE###'] = ucfirst($this->pi_getLL('mobile'));
        $subpartArray['###VALUE_MOBILE###'] = mslib_fe::RemoveXSS($this->post['mobile']);
        $subpartArray['###LABEL_BIRTHDATE###'] = ucfirst($this->pi_getLL('birthday'));
        $subpartArray['###VALUE_VISIBLE_BIRTHDATE###'] = ($this->post['date_of_birth'] ? htmlspecialchars(strftime("%x", $this->post['date_of_birth'])) : $this->post['birthday_visitor']);
        $subpartArray['###VALUE_HIDDEN_BIRTHDATE###'] = ($this->post['date_of_birth'] ? htmlspecialchars(strftime("%F", $this->post['date_of_birth'])) : $this->post['birthday']);
        $subpartArray['###LABEL_DISCOUNT###'] = ucfirst($this->pi_getLL('discount'));
        $subpartArray['###VALUE_DISCOUNT###'] = ($this->post['tx_multishop_discount'] > 0 ? mslib_fe::RemoveXSS($this->post['tx_multishop_discount']) : '');
        $subpartArray['###LABEL_PAYMENT_CONDITION###'] = ucfirst($this->pi_getLL('payment_condition'));
        $subpartArray['###VALUE_PAYMENT_CONDITION###'] = $this->ms['MODULES']['DEFAULT_PAYMENT_CONDITION_VALUE'];
        $subpartArray['###CUSTOMER_GROUPS_INPUT###'] = $customer_groups_input;
        $subpartArray['###VALUE_CUSTOMER_ID###'] = '';
        if ($_GET['action'] == 'edit_customer') {
            $subpartArray['###LABEL_BUTTON_SAVE###'] = ucfirst($this->pi_getLL('update_account'));
        } else {
            $subpartArray['###LABEL_BUTTON_SAVE###'] = ucfirst($this->pi_getLL('save'));
        }
        $subpartArray['###LOGIN_AS_THIS_USER_LINK###'] = '';
        $subpartArray['###DETAILS_TAB###'] = '';
        $subpartArray['###DETAILS###'] = '';
        $subpartArray['###INPUT_EDIT_SHIPPING_AND_PAYMENT_METHOD###'] = $shipping_payment_method;
        $subpartArray['###LABEL_PAYMENT_CONDITION###'] = ucfirst($this->pi_getLL('payment_condition'));
        $subpartArray['###VALUE_PAYMENT_CONDITION###'] = (isset($this->post['tx_multishop_payment_condition']) ? mslib_fe::RemoveXSS($this->post['tx_multishop_payment_condition']) : 14);
        $subpartArray['###LABEL_FOREIGN_CUSTOMER_ID###'] = ucfirst($this->pi_getLL('foreign_customer_id'));
        $subpartArray['###VALUE_FOREIGN_CUSTOMER_ID###'] = mslib_fe::RemoveXSS($this->post['foreign_customer_id']);
        break;
}
// language input
$language_selectbox = '';
foreach ($this->languages as $key => $language) {
    $language['lg_iso_2'] = strtolower($language['lg_iso_2']);
    if (empty($user['tx_multishop_language']) && $language['uid'] === 0) {
        $language_selectbox .= '<option value="' . $language['lg_iso_2'] . '" selected="selected">' . $language['title'] . '</option>';
    } else {
        if (strtolower($user['tx_multishop_language']) == $language['lg_iso_2']) {
            $language_selectbox .= '<option value="' . $language['lg_iso_2'] . '" selected="selected">' . $language['title'] . '</option>';
        } else {
            $language_selectbox .= '<option value="' . $language['lg_iso_2'] . '">' . $language['title'] . '</option>';
        }
    }
}
if (!empty($language_selectbox)) {
    $language_selectbox = '<select name="tx_multishop_language">' . $language_selectbox . '</select>';
}
$subpartArray['###LABEL_LANGUAGE###'] = $this->pi_getLL('language');
$subpartArray['###LANGUAGE_SELECTBOX###'] = $language_selectbox;
// language eol
// h5validate message
$subpartArray['###INVALID_FIRSTNAME_MESSAGE###'] = $this->pi_getLL('first_name_required');
$subpartArray['###INVALID_LASTNAME_MESSAGE###'] = $this->pi_getLL('surname_is_required');
$subpartArray['###INVALID_ADDRESS_MESSAGE###'] = $this->pi_getLL('street_address_is_required');
$subpartArray['###INVALID_ADDRESSNUMBER_MESSAGE###'] = $this->pi_getLL('street_number_is_required');
$subpartArray['###INVALID_ZIP_MESSAGE###'] = $this->pi_getLL('zip_is_required');
$subpartArray['###INVALID_CITY_MESSAGE###'] = $this->pi_getLL('city_is_required');
$subpartArray['###INVALID_EMAIL_MESSAGE###'] = $this->pi_getLL('email_is_required');
$subpartArray['###INVALID_USERNAME_MESSAGE###'] = $this->pi_getLL('username_is_required');
$subpartArray['###INVALID_PASSWORD_MESSAGE###'] = $this->pi_getLL('password_is_required');
$telephone_validation = '';
if ($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE']) {
    if (!$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER']) {
        $telephone_validation = ' required="required" data-h5-errorid="invalid-telephone" title="' . $this->pi_getLL('telephone_is_required') . '"';
    } else {
        $telephone_validation = ' required="required" data-h5-errorid="invalid-telephone" title="' . $this->pi_getLL('telephone_is_required') . '" pattern=".{' . $this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'] . '}"';
    }
}
$subpartArray['###TELEPHONE_VALIDATION###'] = $telephone_validation;
// plugin marker place holder
if (!$this->ms['MODULES']['FIRSTNAME_AND_LASTNAME_UNREQUIRED_IN_ADMIN_CUSTOMER_PAGE']) {
    $subpartArray['###LABEL_FIRSTNAME###'] = ucfirst($this->pi_getLL('first_name'));//.'<span class="text-danger">*</span>';
    $subpartArray['###LABEL_LASTNAME###'] = ucfirst($this->pi_getLL('last_name'));//.'<span class="text-danger">*</span>';
    $subpartArray['###FIRSTNAME_VALIDATION###'] = ' required="required" data-h5-errorid="invalid-first_name" title="' . $this->pi_getLL('first_name_required') . '"';
    $subpartArray['###LASTNAME_VALIDATION###'] = ' required="required" data-h5-errorid="invalid-last_name" title="' . $this->pi_getLL('last_name_required') . '"';
} else {
    $subpartArray['###LABEL_FIRSTNAME###'] = ucfirst($this->pi_getLL('first_name'));
    $subpartArray['###LABEL_LASTNAME###'] = ucfirst($this->pi_getLL('last_name'));
}
$plugins_extra_tab = array();
$js_extra = array();
$plugins_extra_tab['tabs_header'] = array();
$plugins_extra_tab['tabs_content'] = array();
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerTmplPreProc'])) {
    $params = array(
            'subpartArray' => &$subpartArray,
            'user' => &$user,
            'plugins_extra_tab' => &$plugins_extra_tab,
            'js_extra' => &$js_extra
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerTmplPreProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// custom page hook that can be controlled by third-party plugin eof
if (!count($plugins_extra_tab['tabs_header']) && !count($plugins_extra_tab['tabs_content'])) {
    $subpartArray['###LABEL_EXTRA_PLUGIN_TABS###'] = '';
    $subpartArray['###CONTENT_EXTRA_PLUGIN_TABS###'] = '';
} else {
    $subpartArray['###LABEL_EXTRA_PLUGIN_TABS###'] = implode("\n", $plugins_extra_tab['tabs_header']);
    $subpartArray['###CONTENT_EXTRA_PLUGIN_TABS###'] = implode("\n", $plugins_extra_tab['tabs_content']);
}
if (!count($js_extra['functions'])) {
    $subpartArray['###JS_FUNCTIONS_EXTRA###'] = '';
} else {
    $subpartArray['###JS_FUNCTIONS_EXTRA###'] = implode("\n", $js_extra['functions']);
}
if (!count($js_extra['triggers'])) {
    $subpartArray['###JS_TRIGGERS_EXTRA###'] = '';
} else {
    $subpartArray['###JS_TRIGGERS_EXTRA###'] = implode("\n", $js_extra['triggers']);
}
if (isset($this->get['tx_multishop_pi1']['cid']) && $this->get['tx_multishop_pi1']['cid'] > 0) {
    if (!empty($this->post['company'])) {
        $subpartArray['###HEADING_TITLE###'] = mslib_fe::RemoveXSS($this->post['company']) . ' (ID: ' . $this->get['tx_multishop_pi1']['cid'] . ')';
    } else if (!empty($this->post['name'])) {
        $subpartArray['###HEADING_TITLE###'] = mslib_fe::RemoveXSS($this->post['name']) . ' (ID: ' . $this->get['tx_multishop_pi1']['cid'] . ')';
    } else {
        $subpartArray['###HEADING_TITLE###'] = $this->pi_getLL('admin_label_tabs_edit_customer') . ' (ID: ' . $this->get['tx_multishop_pi1']['cid'] . ')';
    }
    $subpartArray['###ADMIN_LABEL_TABS_EDIT_CUSTOMER###'] = $this->pi_getLL('admin_label_tabs_edit_customer');
} else {
    $subpartArray['###HEADING_TITLE###'] = $this->pi_getLL('admin_new_customer');
    $subpartArray['###ADMIN_LABEL_TABS_EDIT_CUSTOMER###'] = $this->pi_getLL('admin_new_customer');
}
$subpartArray['###AJAX_URL_GET_USERGROUPS0###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_usergroups');
$subpartArray['###AJAX_URL_GET_USERGROUPS1###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_usergroups');
$subpartArray['###CUSTOMER_GROUPS_PLACEHOLDER###'] = $this->pi_getLL('select_customer_groups');
// Instantiate admin interface object
$objRef = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj('EXT:multishop/pi1/classes/class.tx_mslib_admin_interface.php:&tx_mslib_admin_interface');
$objRef->init($this);
$objRef->setInterfaceKey('admin_edit_customer');
// Header buttons
$headerButtons = array();
if (is_array($user) && $user['uid']) {
    $headingButton = array();
    $headingButton['btn_class'] = 'btn btn-primary';
    $headingButton['fa_class'] = 'fa fa-sign-in';
    $headingButton['title'] = $this->pi_getLL('login');
    $headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_customers&login_as_customer=1&customer_id=' . $user['uid']);
    $headingButton['sort'] = 50;
    $headerButtons['impersonateLoginAs'] = $headingButton;
}
$headingButton = array();
$headingButton['btn_class'] = 'btn btn-primary';
$headingButton['fa_class'] = 'fa fa-book';
$headingButton['title'] = $this->pi_getLL('admin_label_create_order');
$headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_new_order&tx_multishop_pi1[customer_id]=' . $user['uid']);
$headingButton['sort'] = 55;
$headerButtons['create_order'] = $headingButton;
$headingButton = array();
$headingButton['btn_class'] = 'btn btn-success';
$headingButton['fa_class'] = 'fa fa-check-circle';
$headingButton['title'] = ($this->get['action'] == 'edit_customer') ? $this->pi_getLL('update') : $this->pi_getLL('save');
$headingButton['href'] = '#';
$headingButton['attributes'] = 'onclick="$(\'#btnSave\').click(); return false;"';
$headingButton['sort'] = 60;
$headerButtons['save'] = $headingButton;
$headingButton = array();
$headingButton['btn_class'] = 'btn btn-success';
$headingButton['fa_class'] = 'fa fa-check-circle';
$headingButton['title'] = ($this->get['action'] == 'edit_customer') ? $this->pi_getLL('admin_update_close') : $this->pi_getLL('admin_save_close');
$headingButton['href'] = '#';
$headingButton['attributes'] = 'onclick="$(\'#btnSaveClose\').click(); return false;"';
$headingButton['sort'] = 65;
$headerButtons['save_and_close'] = $headingButton;
// Set header buttons through interface class so other plugins can adjust it
$objRef->setHeaderButtons($headerButtons);
// Get header buttons through interface class so we can render them
$interfaceHeaderButtons = $objRef->renderHeaderButtons();
// Get header buttons through interface class so we can render them
$subpartArray['###INTERFACE_HEADER_BUTTONS###'] = $objRef->renderHeaderButtons();
$content .= $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
if (!count($erno)) {
    if ($this->get['tx_multishop_pi1']['cid'] > 0 && !is_numeric($user['uid'])) {
        $content = $this->pi_getLL('customer_not_found');
    } else {
        if (isset($this->get['tx_multishop_pi1']['cid'])) {
            if (!$this->get['tx_multishop_pi1']['cid'] || !is_numeric($this->get['tx_multishop_pi1']['cid']) || !$user) {
                $content = $this->pi_getLL('customer_not_found');
            }
        }
    }
}
/*
if ($customer_id) {
	require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_dashboard.php');
	$mslib_dashboard=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_dashboard');
	$mslib_dashboard->init($this);
	$mslib_dashboard->setSection('admin_edit_customer');
	$mslib_dashboard->renderWidgets();
	$content.=$mslib_dashboard->displayDashboard();
}
*/
?>
