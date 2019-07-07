<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersMainPreProc'])) {
    $params = array();
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersMainPreProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
$content = '';
$all_orders_status = mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingAllOrdersStatusPreHook'])) {
    $params = array(
        'all_orders_status' => &$all_orders_status
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingAllOrdersStatusPreHook'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// hook
if ($this->post['tx_multishop_pi1']['edit_order'] == 1 and is_numeric($this->post['tx_multishop_pi1']['orders_id'])) {
    $url = $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=edit_order&orders_id=' . $this->post['tx_multishop_pi1']['orders_id'] . '&action=edit_order');
    header('Location: ' . $url);
    exit();
}
if (!$this->post['tx_multishop_pi1']['action'] && $this->get['tx_multishop_pi1']['action']) {
    $this->post['tx_multishop_pi1']['action'] = $this->get['tx_multishop_pi1']['action'];
}
if ($this->post) {
    foreach ($this->post as $post_idx => $post_val) {
        $this->get[$post_idx] = $post_val;
    }
}
if ($this->get) {
    foreach ($this->get as $get_idx => $get_val) {
        $this->post[$get_idx] = $get_val;
    }
}
$postErno = array();
switch ($this->post['tx_multishop_pi1']['action']) {
    case 'download_selected_orders_packingslips_in_one_pdf':
        if (is_array($this->post['selected_orders']) && count($this->post['selected_orders'])) {
            $attachments = array();
            foreach ($this->post['selected_orders'] as $order_id) {
                $order = mslib_fe::getOrder($order_id);
                $pdfFileName = 'packingslip_' . $order_id . '.pdf';
                $packingslip_path = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $pdfFileName;
                $packingslip_data = mslib_fe::file_get_contents($this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=download_packingslip&tx_multishop_pi1[order_hash]=' . $order['hash']));
                // write temporary to disk
                file_put_contents($packingslip_path, $packingslip_data);
                $attachments[] = $packingslip_path;
            }
            if (count($attachments)) {
                $combinedPdfFile = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . time() . '_' . uniqid() . '.pdf';
                $prog = \TYPO3\CMS\Core\Utility\CommandUtility::exec('which gs');
                //hook to let other plugins further manipulate the settings
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['overrideGhostScripPath'])) {
                    $params = array(
                            'prog' => &$prog
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['overrideGhostScripPath'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                if ($prog && is_file($prog)) {
                    $cmd = $prog . ' -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=' . $combinedPdfFile . ' ' . implode(' ', $attachments);
                    \TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd);
                    if (file_exists($combinedPdfFile)) {
                        header("Content-type:application/pdf");
                        readfile($combinedPdfFile);
                        // delete temporary invoice from disk
                        unlink($combinedPdfFile);
                        foreach ($attachments as $attachment) {
                            unlink($attachment);
                        }
                        exit();
                    }
                } else {
                    echo 'gs binary cannot be found. This is needed for merging multiple PDF files as one file.';
                    exit();
                }
            }
        }
        break;
    case 'export_selected_order_to_xls':
        if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
            $paths = array();
            $paths[] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service') . 'Classes/Service/PHPExcel.php';
            $paths[] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service') . 'Classes/PHPExcel.php';
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once($path);
                    break;
                }
            }
            require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/admin_pages/includes/orders/orders_xls_export.php');
        }
        break;
    case 'create_invoice_for_selected_orders':
        if (is_array($this->post['selected_orders']) && count($this->post['selected_orders'])) {
            foreach ($this->post['selected_orders'] as $orders_id) {
                $order = mslib_fe::getOrder($orders_id);
                if ($order['orders_id']) {
                    $returnStatus = mslib_fe::createOrderInvoice($order['orders_id']);
                    if ($returnStatus['erno']) {
                        $postErno[] = array(
                                'status' => 'error',
                                'message' => 'Failed to create invoice order: ' . $orders_id . '. Error(s): ' . implode('<br/>', $returnStatus['erno'])
                        );
                    } else {
                        $postErno[] = array(
                                'status' => 'info',
                                'message' => 'Created invoice ' . $returnStatus['invoice_id'] . ' for order: ' . $orders_id
                        );
                    }
                }
            }
        }
        break;
    case 'change_order_status_for_selected_orders':
        if (is_array($this->post['selected_orders']) and count($this->post['selected_orders']) and is_numeric($this->post['tx_multishop_pi1']['update_to_order_status'])) {
            foreach ($this->post['selected_orders'] as $orders_id) {
                if (is_numeric($orders_id)) {
                    $orders = mslib_fe::getOrder($orders_id);
                    if ($orders['orders_id'] and ($orders['status'] != $this->post['tx_multishop_pi1']['update_to_order_status'])) {
                        // mslib_befe::updateOrderStatus($orders['orders_id'],$this->post['tx_multishop_pi1']['update_to_order_status']);
                        if (mslib_befe::updateOrderStatus($orders['orders_id'], $this->post['tx_multishop_pi1']['update_to_order_status'], 1, 'change_order_status_for_selected_orders')) {
                            $postErno[] = array(
                                    'status' => 'info',
                                    'message' => 'Updated order status for orders is: ' . $orders['orders_id']
                            );
                        } else {
                            $postErno[] = array(
                                    'status' => 'error',
                                    'message' => 'Order status not updated for orders id: ' . $orders['orders_id']
                            );
                        }
                    }
                }
            }
            //hook to let other plugins further manipulate the settings
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersUpdateOrderStatusForSelectedOrdersPostProc'])) {
                $params = array('content' => &$content);
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersUpdateOrderStatusForSelectedOrdersPostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
        }
        break;
    case 'delete_selected_orders':
        if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
            foreach ($this->post['selected_orders'] as $orders_id) {
                if (is_numeric($orders_id)) {
                    $order = mslib_fe::getOrder($orders_id);
                    if ($order['orders_id']) {
                        $updateArray = array();
                        $updateArray['deleted'] = 1;
                        $updateArray['orders_last_modified'] = time();
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $orders_id . '\'', $updateArray);
                        if (!$res = $GLOBALS['TYPO3_DB']->sql_query($query)) {
                            $postErno[] = array(
                                    'status' => 'error',
                                    'message' => 'Failed to delete order: ' . $orders_id . '. Query error: ' . $GLOBALS['TYPO3_DB']->sql_error()
                            );
                        } else {
                            $postErno[] = array(
                                    'status' => 'info',
                                    'message' => 'Deleted order: ' . $orders_id
                            );
                        }
                    } else {
                        $postErno[] = array(
                                'status' => 'error',
                                'message' => 'Failed to delete order: ' . $orders_id . '. Order cannot be found'
                        );
                    }
                }
            }
        }
        break;
    case 'mail_selected_orders_to_customer':
    case 'mail_selected_orders_to_merchant':
        if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
            foreach ($this->post['selected_orders'] as $orders_id) {
                if (is_numeric($orders_id)) {
                    $order = mslib_fe::getOrder($orders_id);
                    if ($order['orders_id']) {
                        $mail_template = '';
                        if ($order['paid']) {
                            $mail_template = 'email_order_paid_letter';
                        }
                        $mailTo = '';
                        switch ($this->post['tx_multishop_pi1']['action']) {
                            case 'mail_selected_orders_to_customer':
                                // Empty to mail to the owner of the order
                                $mailTo = '';
                                break;
                            case 'mail_selected_orders_to_merchant':
                                $mailTo = $this->ms['MODULES']['STORE_EMAIL'];
                                break;
                        }
                        if ($mailTo) {
                            $printAddress = $mailTo;
                        } else {
                            $printAddress = $order['billing_email'];
                        }
                        if (mslib_fe::mailOrder($orders_id, 0, $mailTo, $mail_template)) {
                            $postErno[] = array(
                                    'status' => 'info',
                                    'message' => 'Order ' . $orders_id . ' has been mailed to: ' . $printAddress
                            );
                        } else {
                            $postErno[] = array(
                                    'status' => 'error',
                                    'message' => 'Failed to mail order ' . $orders_id . ' to: ' . $printAddress
                            );
                        }
                    } else {
                        $postErno[] = array(
                                'status' => 'error',
                                'message' => 'Failed to retrieve order: ' . $orders_id
                        );
                    }
                } else {
                    $postErno[] = array(
                            'status' => 'error',
                            'message' => 'Failed to retrieve order: ' . $orders_id
                    );
                }
            }
        }
        break;
    case 'update_selected_orders_to_paid':
    case 'update_selected_orders_to_not_paid':
        if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
            foreach ($this->post['selected_orders'] as $orders_id) {
                if (is_numeric($orders_id)) {
                    $order = mslib_fe::getOrder($orders_id);
                    if ($order['orders_id']) {
                        if ($this->post['tx_multishop_pi1']['action'] == 'update_selected_orders_to_paid') {
                            if (mslib_fe::updateOrderStatusToPaid($orders_id)) {
                                $postErno[] = array(
                                        'status' => 'info',
                                        'message' => 'Order ' . $orders_id . ' has been updated to paid.'
                                );
                            } else {
                                $postErno[] = array(
                                        'status' => 'error',
                                        'message' => 'Failed to update ' . $orders_id . ' to paid.'
                                );
                            }
                        } elseif ($this->post['tx_multishop_pi1']['action'] == 'update_selected_orders_to_not_paid') {
                            $updateArray = array('paid' => 0);
                            $updateArray['orders_last_modified'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=' . $orders_id, $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                    }
                }
            }
        }
        break;
    case 'mail_selected_orders_for_payment_reminder':
        if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
            foreach ($this->post['selected_orders'] as $orders_id) {
                $this->post['selected_order_id']=$orders_id;
                $tmpArray = mslib_fe::getOrder($orders_id); //=mslib_befe::getRecord($orders_id, 'tx_multishop_orders', 'orders_id');
                if ($tmpArray['paid'] == 0) {
                    // replacing the variables with dynamic values
                    $billing_address = '';
                    $delivery_address = '';
                    $full_customer_name = $tmpArray['billing_first_name'];
                    if ($tmpArray['billing_middle_name']) {
                        $full_customer_name .= ' ' . $tmpArray['billing_middle_name'];
                    }
                    if ($tmpArray['billing_last_name']) {
                        $full_customer_name .= ' ' . $tmpArray['billing_last_name'];
                    }
                    $delivery_full_customer_name = $tmpArray['delivery_first_name'];
                    if ($tmpArray['delivery_middle_name']) {
                        $delivery_full_customer_name .= ' ' . $tmpArray['delivery_middle_name'];
                    }
                    if ($order['delivery_last_name']) {
                        $delivery_full_customer_name .= ' ' . $tmpArray['delivery_last_name'];
                    }
                    $full_customer_name = preg_replace('/\s+/', ' ', $full_customer_name);
                    $delivery_full_customer_name = preg_replace('/\s+/', ' ', $delivery_full_customer_name);
                    if ($tmpArray['delivery_company']) {
                        $delivery_address = $tmpArray['delivery_company'] . "<br />";
                    }
                    if ($delivery_full_customer_name) {
                        $delivery_address .= $delivery_full_customer_name . "<br />";
                    }
                    if ($tmpArray['delivery_building']) {
                        $delivery_address .= $tmpArray['delivery_building'] . "<br />";
                    }
                    if ($tmpArray['delivery_address']) {
                        $delivery_address .= $tmpArray['delivery_address'] . "<br />";
                    }
                    if ($tmpArray['delivery_zip'] and $tmpArray['delivery_city']) {
                        $delivery_address .= $tmpArray['delivery_zip'] . " " . $tmpArray['delivery_city'];
                    }
                    if ($tmpArray['delivery_country'] && mslib_befe::strtolower($tmpArray['delivery_country']) != mslib_befe::strtolower($this->tta_shop_info['country'])) {
                        // ONLY PRINT COUNTRY IF THE COUNTRY OF THE CUSTOMER IS DIFFERENT THAN FROM THE SHOP
                        $delivery_address .= '<br />' . ucfirst(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $tmpArray['delivery_country']));
                    }
                    if ($tmpArray['billing_company']) {
                        $billing_address = $tmpArray['billing_company'] . "<br />";
                    }
                    if ($full_customer_name) {
                        $billing_address .= $full_customer_name . "<br />";
                    }
                    if ($tmpArray['billing_building']) {
                        $billing_address .= $tmpArray['billing_building'] . "<br />";
                    }
                    if ($tmpArray['billing_address']) {
                        $billing_address .= $tmpArray['billing_address'] . "<br />";
                    }
                    if ($tmpArray['billing_zip'] and $tmpArray['billing_city']) {
                        $billing_address .= $tmpArray['billing_zip'] . " " . $tmpArray['billing_city'];
                    }
                    if ($tmpArray['billing_country'] && mslib_befe::strtolower($tmpArray['billing_country']) != mslib_befe::strtolower($this->tta_shop_info['country'])) {
                        // ONLY PRINT COUNTRY IF THE COUNTRY OF THE CUSTOMER IS DIFFERENT THAN FROM THE SHOP
                        $billing_address .= '<br />' . ucfirst(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $tmpArray['billing_country']));
                    }
                    if (empty($tmpArray['hash'])) {
                        $hashcode = md5($orders_id + time());
                        $updateArray = array();
                        $updateArray['hash'] = $hashcode;
                        $updateArray['orders_last_modified'] = time();
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=' . $orders_id, $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    } else {
                        $hashcode = $tmpArray['hash'];
                    }
                    $array1 = array();
                    $array2 = array();
                    $array1[] = '###GENDER_SALUTATION###';
                    $array2[] = mslib_fe::genderSalutation($tmpArray['billing_gender']);
                    $array1[] = '###DELIVERY_FIRST_NAME###';
                    $array2[] = $tmpArray['delivery_first_name'];
                    $array1[] = '###DELIVERY_LAST_NAME###';
                    $array2[] = preg_replace('/\s+/', ' ', $tmpArray['delivery_middle_name'] . ' ' . $tmpArray['delivery_last_name']);
                    $array1[] = '###BILLING_FIRST_NAME###';
                    $array2[] = $order['billing_first_name'];
                    $array1[] = '###BILLING_LAST_NAME###';
                    $array2[] = preg_replace('/\s+/', ' ', $tmpArray['billing_middle_name'] . ' ' . $tmpArray['billing_last_name']);
                    $array1[] = '###BILLING_TELEPHONE###';
                    $array2[] = $tmpArray['billing_telephone'];
                    $array1[] = '###DELIVERY_TELEPHONE###';
                    $array2[] = $tmpArray['delivery_telephone'];
                    $array1[] = '###BILLING_MOBILE###';
                    $array2[] = $tmpArray['billing_mobile'];
                    $array1[] = '###DELIVERY_MOBILE###';
                    $array2[] = $tmpArray['delivery_mobile'];
                    $array1[] = '###FULL_NAME###';
                    $array2[] = $full_customer_name;
                    $array1[] = '###DELIVERY_FULL_NAME###';
                    $array2[] = $delivery_full_customer_name;
                    $array1[] = '###BILLING_NAME###';
                    $array2[] = $tmpArray['billing_name'];
                    $array1[] = '###BILLING_EMAIL###';
                    $array2[] = $tmpArray['billing_email'];
                    $array1[] = '###DELIVERY_EMAIL###';
                    $array2[] = $tmpArray['delivery_email'];
                    $array1[] = '###DELIVERY_NAME###';
                    $array2[] = $tmpArray['delivery_name'];
                    $array1[] = '###CUSTOMER_EMAIL###';
                    $array2[] = $tmpArray['billing_email'];
                    $array1[] = '###STORE_NAME###';
                    $array2[] = $this->ms['MODULES']['STORE_NAME'];
                    $array1[] = '###TOTAL_AMOUNT###';
                    $array2[] = mslib_fe::amount2Cents($tmpArray['total_amount']);
                    require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_order.php');
                    $mslib_order = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_order');
                    $mslib_order->init($this);
                    $ORDER_DETAILS = $mslib_order->printOrderDetailsTable($tmpArray, 'site');
                    $array1[] = '###ORDER_DETAILS###';
                    $array2[] = $ORDER_DETAILS;
                    $array1[] = '###BILLING_ADDRESS###';
                    $array2[] = $billing_address;
                    $array1[] = '###DELIVERY_ADDRESS###';
                    $array2[] = $delivery_address;
                    $array1[] = '###CUSTOMER_ID###';
                    $array2[] = $tmpArray['customer_id'];
                    $array1[] = '###SHIPPING_METHOD###';
                    $array2[] = $tmpArray['shipping_method_label'];
                    $array1[] = '###PAYMENT_METHOD###';
                    $array2[] = $tmpArray['payment_method_label'];
                    $array1[] = '###ORDERS_ID###';
                    $array2[] = $tmpArray['orders_id'];
                    $invoice = mslib_fe::getOrderInvoice($tmpArray['orders_id'], 0);
                    $invoice_id = '';
                    $invoice_link = '';
                    if (is_array($invoice)) {
                        $invoice_id = $invoice['invoice_id'];
                        $invoice_link = '<a href="' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]=' . $invoice['hash']) . '">' . $invoice['invoice_id'] . '</a>';
                    }
                    $array1[] = '###INVOICE_NUMBER###';
                    $array2[] = $invoice_id;
                    $array1[] = '###INVOICE_LINK###';
                    $array2[] = $invoice_link;
                    $time = $tmpArray['crdate'];
                    $long_date = strftime($this->pi_getLL('full_date_format'), $time);
                    $array1[] = '###ORDER_DATE_LONG###'; // ie woensdag 23 juni, 2010
                    $array2[] = $long_date;
                    // backwards compatibility
                    $array1[] = '###LONG_DATE###'; // ie woensdag 23 juni, 2010
                    $array2[] = $long_date;
                    $time = time();
                    $long_date = strftime($this->pi_getLL('full_date_format'), $time);
                    $array1[] = '###CURRENT_DATE_LONG###'; // ie woensdag 23 juni, 2010
                    $array2[] = $long_date;
                    $array1[] = '###CURRENT_DATE###'; // 21-12-2010 in localized format
                    $array2[] = strftime("%x", $time);
                    $array1[] = '###STORE_NAME###';
                    $array2[] = $this->ms['MODULES']['STORE_NAME'];
                    $array1[] = '###TOTAL_AMOUNT###';
                    $array2[] = mslib_fe::amount2Cents($tmpArray['total_amount']);
                    $array1[] = '###ORDER_NUMBER###';
                    $array2[] = $tmpArray['orders_id'];
                    $array1[] = '###ORDER_LINK###';
                    $array2[] = '';
                    $array1[] = '###CUSTOMER_ID###';
                    $array2[] = $tmpArray['customer_id'];
                    $link = $this->FULL_HTTP_URL . mslib_fe::typolink($tmpArray['page_uid'], 'tx_multishop_pi1[page_section]=payment_reminder_checkout&tx_multishop_pi1[hash]=' . $hashcode);
                    $array1[] = '###PAYMENT_PAGE_LINK###';
                    $array2[] = $link;
                    // psp email template
                    $psp_mail_template = array();
                    if ($tmpArray['payment_method']) {
                        $psp_data = mslib_fe::loadPaymentMethod($tmpArray['payment_method']);
                        $psp_vars = unserialize($psp_data['vars']);
                        if (isset($psp_vars['order_payment_reminder'])) {
                            $psp_mail_template['order_payment_reminder'] = '';
                            if ($psp_vars['order_payment_reminder'] > 0) {
                                $psp_mail_template['order_payment_reminder'] = mslib_fe::getCMSType($psp_vars['order_payment_reminder']);
                            }
                        }
                    }
                    if (isset($psp_mail_template['order_payment_reminder'])) {
                        $page = array();
                        if (!empty($psp_mail_template['order_payment_reminder'])) {
                            $page = mslib_fe::getCMScontent($psp_mail_template['order_payment_reminder'], $GLOBALS['TSFE']->sys_language_uid);
                        }
                    } else {
                        $cms_type = 'payment_reminder_email_templates_' . $tmpArray['payment_method'];
                        $page = mslib_fe::getCMScontent($cms_type, $GLOBALS['TSFE']->sys_language_uid);
                        if (!count($page[0])) {
                            $page = mslib_fe::getCMScontent('payment_reminder_email_templates', $GLOBALS['TSFE']->sys_language_uid);
                        }
                    }
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['ordersListingActionMailSelectedOrdersForPaymentReminder'])) {
                        $params = array(
                            'array1' => &$array1,
                            'array2' => &$array2,
                            'order' => $tmpArray
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['ordersListingActionMailSelectedOrdersForPaymentReminder'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                    if ($page[0]['name']) {
                        $reminder_cms_content = '';
                        if ($page[0]['name']) {
                            $page[0]['name'] = str_replace($array1, $array2, $page[0]['name']);
                            $reminder_cms_content .= '<div class="main-heading"><h2>' . $page[0]['name'] . '</h2></div>';
                        }
                        if ($page[0]['content']) {
                            $page[0]['content'] = str_replace($array1, $array2, $page[0]['content']);
                            $reminder_cms_content .= $page[0]['content'];
                        }
                        $full_customer_name = $tmpArray['billing_first_name'];
                        if ($order['billing_middle_name']) {
                            $full_customer_name .= ' ' . $tmpArray['billing_middle_name'];
                        }
                        if ($order['billing_last_name']) {
                            $full_customer_name .= ' ' . $tmpArray['billing_last_name'];
                        }
                        $user = array();
                        $user['name'] = $full_customer_name;
                        $user['email'] = $tmpArray['billing_email'];
                        $user['customer_id'] = $tmpArray['customer_id'];
                        if ($user['email']) {
                            mslib_fe::mailUser($user, $page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME']);
                            $postErno[] = array(
                                'status' => 'info',
                                'message' => 'Payment reminder e-mail has been sent to ' . $user['email'] . ' (Order ID: ' . $orders_id . ').'
                            );
                        } else {
                            $postErno[] = array(
                                    'status' => 'error',
                                    'message' => 'Failed to sent payment reminder e-mail to ' . $user['email'] . ' (Order ID: ' . $orders_id . ').'
                            );
                        }
                    } else {
                        $postErno[] = array(
                            'status' => 'error',
                            'message' => 'Failed to sent payment reminder e-mail to ' . $user['email'] . ' (Order ID: ' . $orders_id . '). reason: payment method has no link to payment reminder cms'
                        );
                    }
                }
            }
        }
        break;
    default:
        // post processing by third party plugins
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersPostHookProc'])) {
            $params = array(
                    'content' => &$content,
                    'postErno' => &$postErno
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersPostHookProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        break;
}
$sesPostsErno=$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_posterno');
if (count($sesPostsErno)) {
    foreach ($sesPostsErno as $sesPostErno) {
        $postErno[]=$sesPostErno;
    }
    $sesPostsErno=array();
    $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_posterno', $sesPostsErno);
    $GLOBALS['TSFE']->storeSessionData();
}
if (count($postErno)) {
    $returnMarkup = '
	<div style="display:none" id="msAdminPostMessage">
	<table class="table table-striped table-bordered">
	<thead>
	<tr>
		<th class="text-center">Status</th>
		<th>Message</th>
	</tr>
	</thead>
	<tbody>
	';
    foreach ($postErno as $item) {
        switch ($item['status']) {
            case 'error':
                $item['status'] = '<span class="fa-stack text-danger"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-thumbs-down fa-stack-1x fa-inverse"></i></span>';
                break;
            case 'info':
                $item['status'] = '<span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-thumbs-up fa-stack-1x fa-inverse"></i></span>';
                break;
        }
        $returnMarkup .= '<tr><td class="text-center">' . $item['status'] . '</td><td>' . $item['message'] . '</td></tr>' . "\n";
    }
    $returnMarkup .= '</tbody></table></div>';
    $content .= $returnMarkup;
    $GLOBALS['TSFE']->additionalHeaderData[] = '<script type="text/javascript" data-ignore="1">
	jQuery(document).ready(function ($) {
		$.confirm({
			title: \'\',
			content: $(\'#msAdminPostMessage\').html(),
			cancelButton: false // hides the cancel button.
		});
	});
	</script>
	';
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_orders_tmpl_path']) {
    $template = $this->cObj->fileResource($this->conf['admin_orders_tmpl_path']);
} else {
    $template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey) . 'templates/admin_orders.tmpl');
}
// Extract the subparts from the template
$subparts = array();
$subparts['template'] = $this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['orders_results'] = $this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['orders_listing'] = $this->cObj->getSubpart($subparts['orders_results'], '###ORDERS_LISTING###');
$subparts['orders_noresults'] = $this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
// search keys storage
$search_keys=array();
$search_keys[]='type_search';
$search_keys[]='usergroup';
$search_keys[]='country';
$search_keys[]='ordered_product';
$search_keys[]='payment_status';
$search_keys[]='orders_status_search';
$search_keys[]='order_date_from';
$search_keys[]='order_date_till';
$search_keys[]='order_expected_delivery_date_from';
$search_keys[]='order_expected_delivery_date_till';
$search_keys[]='payment_method';
$search_keys[]='shipping_method';
$search_keys[]='search_by_status_last_modified';
$search_keys[]='search_by_telephone_orders';
$search_keys[]='manufacturers_id';
foreach ($search_keys as $search_key) {
    if (isset($this->post[$search_key]) && $this->post[$search_key] != $this->cookie[$search_key]) {
        $this->cookie[$search_key] = $this->post[$search_key];
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
        $GLOBALS['TSFE']->storeSessionData();
    }
    if ($this->cookie[$search_key] && $this->conf['adminOrdersListingDisableAutoRememberFilters']=='0') {
        $this->post[$search_key] = $this->cookie[$search_key];
    }
}
if ($this->post['Search'] and ($this->post['tx_multishop_pi1']['excluding_vat']!=$this->cookie['excluding_vat'])) {
    $this->cookie['excluding_vat'] = $this->post['tx_multishop_pi1']['excluding_vat'];
    $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
    $GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['excluding_vat']) {
    $this->post['tx_multishop_pi1']['excluding_vat']= $this->cookie['excluding_vat'];
}
if ($this->post['Search'] and ($this->post['limit'] != $this->cookie['limit'])) {
    $this->cookie['limit'] = $this->post['limit'];
    $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
    $GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
    $this->post['limit'] = $this->cookie['limit'];
} else {
    $this->post['limit'] = 10;
}
/*
<label index="feed_exporter_fields_label_customer_delivery_address">Customer delivery address</label>
<label index="feed_exporter_fields_label_customer_delivery_company">Customer delivery company</label>
<label index="feed_exporter_fields_label_customer_delivery_city">Customer delivery city</label>
<label index="feed_exporter_fields_label_customer_delivery_country">Customer delivery country</label>
<label index="feed_exporter_fields_label_customer_delivery_email">Customer delivery e-mail</label>
<label index="feed_exporter_fields_label_customer_delivery_name">Customer delivery name</label>
<label index="feed_exporter_fields_label_customer_delivery_telephone">Customer delivery telephone</label>
<label index="feed_exporter_fields_label_customer_delivery_zip">Customer delivery zip</label>
 */
$this->ms['MODULES']['ORDERS_LISTING_LIMIT'] = $this->post['limit'];
$option_search = array(
        "orders_id" => $this->pi_getLL('admin_order_id'),
        "customer_id" => $this->pi_getLL('admin_customer_id'),
        "billing_email" => $this->pi_getLL('admin_customer_email'),
        "name" => $this->pi_getLL('admin_customer_name'),
        //"crdate" =>				$this->pi_getLL('admin_order_date'),
        "billing_zip" => $this->pi_getLL('admin_zip'),
        "billing_city" => $this->pi_getLL('admin_city'),
        "billing_address" => $this->pi_getLL('admin_address'),
        "billing_company" => $this->pi_getLL('admin_company'),
        //"shipping_method"=>$this->pi_getLL('admin_shipping_method'),
        //"payment_method"=>$this->pi_getLL('admin_payment_method'),
        "order_products" => $this->pi_getLL('admin_ordered_product'),
        /*"billing_country"=>ucfirst(strtolower($this->pi_getLL('admin_countries'))),*/
        "billing_telephone" => $this->pi_getLL('telephone'),
        "billing_mobile" => $this->pi_getLL('mobile'),
        "http_referer" => $this->pi_getLL('http_referer'),
        "delivery_email" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_email'),
        "delivery_name" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_name'),
        "delivery_zip" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_zip'),
        "delivery_city" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_city'),
        "delivery_address" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_address'),
        "delivery_company" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_company'),
        "delivery_telephone" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_telephone'),
        "delivery_mobile" => $this->pi_getLL('feed_exporter_fields_label_customer_delivery_mobile'),
        "foreign_orders_id" => $this->pi_getLL('feed_exporter_fields_label_foreign_orders_id'),
        "cruser_id" => $this->pi_getLL('feed_exporter_fields_label_ordered_by'),

);
asort($option_search);
$type_search = $this->post['type_search'];
if ($_REQUEST['skeyword']) {
    //  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
    $this->post['skeyword'] = $_REQUEST['skeyword'];
    $this->post['skeyword'] = trim($this->post['skeyword']);
    $this->post['skeyword'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($this->post['skeyword'], $GLOBALS['TSFE']->metaCharset);
    $this->post['skeyword'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->post['skeyword'], true);
    $this->post['skeyword'] = mslib_fe::RemoveXSS($this->post['skeyword']);
}
if (is_numeric($this->post['p'])) {
    $p = $this->post['p'];
}
if ($p > 0) {
    $offset = (((($p) * $this->ms['MODULES']['ORDERS_LISTING_LIMIT'])));
} else {
    $p = 0;
    $offset = 0;
}
// orders search
$option_item = '<select name="type_search" class="order_select2" id="type_search"><option value="all">' . $this->pi_getLL('all') . '</option>';
foreach ($option_search as $key => $val) {
    $option_item .= '<option value="' . $key . '" ' . ($this->post['type_search'] == $key ? "selected" : "") . '>' . $val . '</option>';
}
$option_item .= '</select>';
$orders_status_list = '<select name="orders_status_search" id="orders_status_search" class="order_select2"><option value="0" ' . ((!$order_status_search_selected) ? 'selected' : '') . '>' . $this->pi_getLL('all_orders_status', 'All orders status') . '</option>';
if (is_array($all_orders_status)) {
    $order_status_search_selected = false;
    foreach ($all_orders_status as $row) {
        $orders_status_list .= '<option value="' . $row['id'] . '" ' . (($this->post['orders_status_search'] == $row['id']) ? 'selected' : '') . '>' . $row['name'] . '</option>' . "\n";
        if ($this->post['orders_status_search'] == $row['id']) {
            $order_status_search_selected = true;
        }
    }
}
$orders_status_list .= '</select>';
$limit_selectbox = '<select name="limit" id="limit" class="form-control">';
$limits = array();
$limits[] = '10';
$limits[] = '15';
$limits[] = '20';
$limits[] = '25';
$limits[] = '30';
$limits[] = '40';
$limits[] = '48';
$limits[] = '50';
$limits[] = '100';
$limits[] = '150';
$limits[] = '200';
$limits[] = '250';
$limits[] = '300';
$limits[] = '350';
$limits[] = '400';
$limits[] = '450';
$limits[] = '500';
if (!in_array($this->get['limit'], $limits)) {
    $limits[] = $this->get['limit'];
}
foreach ($limits as $limit) {
    $limit_selectbox .= '<option value="' . $limit . '"' . ($limit == $this->post['limit'] ? ' selected' : '') . '>' . $limit . '</option>';
}
$limit_selectbox .= '</select>';
$filter = array();
$from = array();
$having = array();
$match = array();
$orderby = array();
$where = array();
$orderby = array();
$select = array();
if ($this->post['skeyword']) {
    switch ($type_search) {
        case 'all':
            $option_fields = $option_search;
            unset($option_fields['all']);
            unset($option_fields['crdate']);
            unset($option_fields['name']);
            unset($option_fields['delivery_name']);
            //print_r($option_fields);
            $items = array();
            foreach ($option_fields as $fields => $label) {
                switch ($fields) {
                    case 'orders_id':
                        $items[] = "o." . $fields . " LIKE '%" . addslashes($this->post['skeyword']) . "%'";
                        break;
                    case 'order_products':
                        //$items[]="(op.products_name LIKE '%".addslashes($this->post['skeyword'])."%' or op.products_description LIKE '%".addslashes($this->post['skeyword'])."%')";
                        $items[] = " orders_id IN (SELECT op.orders_id from tx_multishop_orders_products op where op.products_name LIKE '%" . addslashes($this->post['skeyword']) . "%' or op.products_description LIKE '%" . addslashes($this->post['skeyword']) . "%')";
                        break;
                    case 'cruser_id':
                        $subFilter=array();
                        $subFilter[]='fe.name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
                        $subFilter[]='fe.first_name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
                        $subFilter[]='fe.middle_name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
                        $subFilter[]='fe.last_name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
                        $subFilter[]='fe.email LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
                        $subFilter[]='fe.username LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
                        $items[] = "o.cruser_id in (select fe.uid from fe_users fe where (".implode(' OR ', $subFilter)."))";
                        break;
                    default:
                        $items[] = $fields . " LIKE '%" . addslashes($this->post['skeyword']) . "%'";
                        break;
                }
            }
            $search_name=str_replace(' ', '%', addslashes($this->post['skeyword']));
            $search_name=str_replace('%%', '%', $search_name);
            $items[] = "(billing_name LIKE '%" . $search_name . "%' or delivery_name LIKE '%" . $search_name . "%')";
            $filter['all'] = '(' . implode(" or ", $items) . ')';
            break;
        case 'orders_id':
            $filter[] = " o.orders_id='" . addslashes($this->post['skeyword']) . "'";
            break;
        case 'billing_email':
            $filter[] = " billing_email LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'delivery_email':
            $filter[] = " delivery_email LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'name':
            $search_name=str_replace(' ', '%', addslashes($this->post['skeyword']));
            $search_name=str_replace('%%', '%', $search_name);
            $filter[] = " (billing_name LIKE '%" . $search_name . "%')";
            break;
        case 'delivery_name':
            $search_name=str_replace(' ', '%', addslashes($this->post['skeyword']));
            $search_name=str_replace('%%', '%', $search_name);
            $filter[] = " (delivery_name LIKE '%" . $search_name . "%')";
            break;
        case 'billing_zip':
            $filter[] = " billing_zip LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'delivery_zip':
            $filter[] = " delivery_zip LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'billing_city':
            $filter[] = " billing_city LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'delivery_city':
            $filter[] = " delivery_city LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'billing_address':
            $filter[] = " billing_address LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'delivery_address':
            $filter[] = " delivery_address LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'billing_company':
            $filter[] = " billing_company LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'delivery_company':
            $filter[] = " delivery_company LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        /*case 'shipping_method':
            $filter[]=" (shipping_method_label '%".addslashes($this->post['skeyword'])."%' or shipping_method_label LIKE '%".addslashes($this->post['skeyword'])."%')";
            break;
        case 'payment_method':
            $filter[]=" (payment_method LIKE '%".addslashes($this->post['skeyword'])."%' or payment_method_label LIKE '%".addslashes($this->post['skeyword'])."%')";
            break;*/
        case 'customer_id':
            $filter[] = " customer_id='" . addslashes($this->post['skeyword']) . "'";
            break;
        case 'order_products':
            $filter[] = " orders_id IN (SELECT op.orders_id from tx_multishop_orders_products op where op.products_name LIKE '%" . addslashes($this->post['skeyword']) . "%' or op.products_description LIKE '%" . addslashes($this->post['skeyword']) . "%')";
            /*
            $filter[]=" (op.products_name LIKE '%".addslashes($this->post['skeyword'])."%' or op.products_description LIKE '%".addslashes($this->post['skeyword'])."%')";
            $from[]=' tx_multishop_orders_products op';
            $where[]=' o.orders_id=op.orders_id';
            */
            break;
        /*case 'billing_country':
            $filter[]=" billing_country LIKE '%".addslashes($this->post['skeyword'])."%'";
            break;*/
        case 'billing_telephone':
            $filter[] = " billing_telephone LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'billing_mobile':
            $filter[] = " billing_mobile LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'delivery_telephone':
            $filter[] = " delivery_telephone LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'delivery_mobile':
            $filter[] = " delivery_mobile LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'http_referer':
            $filter[] = " http_referer LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'foreign_orders_id':
            $filter[] = "o.foreign_orders_id LIKE '%" . addslashes($this->post['skeyword']) . "%'";
            break;
        case 'cruser_id':
            $subFilter=array();
            $subFilter[]='fe.name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
            $subFilter[]='fe.first_name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
            $subFilter[]='fe.middle_name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
            $subFilter[]='fe.last_name LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
            $subFilter[]='fe.email LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
            $subFilter[]='fe.username LIKE \'%'.addslashes($this->post['skeyword']).'%\'';
            $filter[] = "o.cruser_id in (select uid from fe_users fe where (".implode(' OR ', $subFilter)."))";
            break;
    }
}
if (!empty($this->post['order_date_from']) && !empty($this->post['order_date_till'])) {
    $start_time = strtotime($this->post['order_date_from']);
    $end_time = strtotime($this->post['order_date_till']);
    if ($this->post['search_by_status_last_modified']) {
        $column = 'o.status_last_modified';
    } else {
        $column = 'o.crdate';
    }
    $filter[] = $column . " BETWEEN '" . $start_time . "' and '" . $end_time . "'";
} else {
    if (!empty($this->post['order_date_from'])) {
        $start_time = strtotime($this->post['order_date_from']);
        if ($this->post['search_by_status_last_modified']) {
            $column = 'o.status_last_modified';
        } else {
            $column = 'o.crdate';
        }
        $filter[] = $column . " >= '" . $start_time . "'";
    }
    if (!empty($this->post['order_date_till'])) {
        $end_time = strtotime($this->post['order_date_till']);
        if ($this->post['search_by_status_last_modified']) {
            $column = 'o.status_last_modified';
        } else {
            $column = 'o.crdate';
        }
        $filter[] = $column . " <= '" . $end_time . "'";
    }
}
if (!empty($this->post['order_expected_delivery_date_from']) && !empty($this->post['order_expected_delivery_date_till'])) {
    $start_time = strtotime($this->post['order_expected_delivery_date_from']);
    $end_time = strtotime($this->post['order_expected_delivery_date_till']);
    $column = 'o.expected_delivery_date';
    $filter[] = $column . " BETWEEN '" . $start_time . "' and '" . $end_time . "'";
} else {
    if (!empty($this->post['order_expected_delivery_date_from'])) {
        $start_time = strtotime($this->post['order_expected_delivery_date_from']);
        $column = 'o.expected_delivery_date';
        $filter[] = $column . " >= '" . $start_time . "'";
    }
    if (!empty($this->post['order_expected_delivery_date_till'])) {
        $end_time = strtotime($this->post['order_expected_delivery_date_till']);
        $column = 'o.expected_delivery_date';
        $filter[] = $column . " <= '" . $end_time . "'";
    }
}
if ($this->post['search_by_telephone_orders']) {
    $filter[] = "o.by_phone=1";
}
//print_r($filter);
//print_r($this->post);
//die();
if ($this->post['orders_status_search']) {
    $filter[] = "(o.status='" . addslashes($this->post['orders_status_search']) . "')";
}
if (isset($this->post['payment_method']) && $this->post['payment_method'] != '' && $this->post['payment_method'] != 'all') {
    if ($this->post['payment_method'] == 'nopm') {
        $filter[] = "(o.payment_method is null)";
    } else {
        $filter[] = "(o.payment_method='" . addslashes($this->post['payment_method']) . "')";
    }
}
if (isset($this->post['shipping_method']) && $this->post['shipping_method'] != '' && $this->post['shipping_method'] != 'all') {
    if ($this->post['shipping_method'] == 'nosm') {
        $filter[] = "(o.shipping_method is null)";
    } else {
        $filter[] = "(o.shipping_method='" . addslashes($this->post['shipping_method']) . "')";
    }
}
if (isset($this->post['usergroup']) && $this->post['usergroup'] > 0) {
    $filter[] = ' o.customer_id IN (SELECT uid from fe_users where ' . $GLOBALS['TYPO3_DB']->listQuery('usergroup', $this->post['usergroup'], 'fe_users') . ')';
}
if ($this->post['payment_status'] == 'paid_only') {
    $filter[] = "(o.paid='1')";
} else {
    if ($this->post['payment_status'] == 'unpaid_only') {
        $filter[] = "(o.paid='0')";
    }
}
if (isset($this->post['tx_multishop_pi1']['search_in_shop']) && !empty($this->post['tx_multishop_pi1']['search_in_shop'])) {
    if ($this->post['tx_multishop_pi1']['search_in_shop']!='all') {
        $filter[] = 'o.page_uid=' . (int) $this->post['tx_multishop_pi1']['search_in_shop'];
    }
} else {
    if (!$this->masterShop) {
        $filter[] = 'o.page_uid=' . $this->shop_pid;
    }
}
//$orderby[]='orders_id desc';
$select[] = 'o.*';
//$select[]='o.*, osd.name as orders_status';
//$orderby[]='o.orders_id desc';
switch ($this->get['tx_multishop_pi1']['order_by']) {
    case 'billing_name':
        $order_by = 'o.billing_name';
        break;
    case 'crdate':
        $order_by = 'o.crdate';
        break;
    case 'grand_total':
        $order_by = 'o.grand_total';
        break;
    case 'shipping_method_label':
        $order_by = 'o.shipping_method_label';
        break;
    case 'payment_method_label':
        $order_by = 'o.payment_method_label';
        break;
    case 'status_last_modified':
        $order_by = 'o.status_last_modified';
        break;
    case 'custom_sort_by':
        $order_by = 'o.' . $this->get['tx_multishop_pi1']['custom_order_by'];
        break;
    case 'orders_id':
    default:
        $order_by = 'o.orders_id';
        break;

}
switch ($this->get['tx_multishop_pi1']['order']) {
    case 'a':
        $order = 'asc';
        $order_link = 'd';
        break;
    case 'd':
    default:
        $order = 'desc';
        $order_link = 'a';
        break;
}
$orderby[] = $order_by . ' ' . $order;
if ($this->post['tx_multishop_pi1']['by_phone']) {
    $filter[] = 'o.by_phone=1';
}
if (isset($this->post['country']) && !empty($this->post['country'])) {
    $filter[] = "o.billing_country='" . addslashes($this->post['country']) . "'";
}
if (isset($this->post['manufacturers_id']) && $this->post['manufacturers_id'] > 0) {
    $filter[] = "o.orders_id IN (SELECT op.orders_id from tx_multishop_orders_products op where op.manufacturers_id = " . addslashes($this->post['manufacturers_id']) . ")";
}
if (isset($this->get['ordered_category']) && !empty($this->get['ordered_category']) && $this->get['ordered_category'] != 99999) {
    $filter[] = "o.orders_id in (select op.orders_id from tx_multishop_orders_products op where (op.categories_id='" . addslashes($this->get['ordered_category']) . "' or op.categories_id_0='" . addslashes($this->get['ordered_category']) . "'))";
}
if (isset($this->get['ordered_product']) && !empty($this->get['ordered_product']) && $this->get['ordered_product'] != 99999) {
    $filter[] = "o.orders_id in (select op.orders_id from tx_multishop_orders_products op where op.products_id='" . addslashes($this->get['ordered_product']) . "')";
}
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersSearchFilterPreProc'])) {
    $params = array(
        'select' => &$select,
        'filter' => &$filter,
        'orderby' => &$orderby,
        'offset' => &$offset,
        'option_fields' => &$option_fields
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersSearchFilterPreProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// Use new method to retrieve records
$data = array();
$data['select'] = $select;
$data['where'] = $filter;
$data['where'][] = 'o.deleted=0';
$data['order_by'] = $orderby;
$data['limit'] = $this->ms['MODULES']['ORDERS_LISTING_LIMIT'];
$data['offset'] = $offset;
//$data['from'][]='tx_multishop_orders o left join tx_multishop_orders_status os on o.status=os.id left join tx_multishop_orders_status_description osd on (os.id=osd.orders_status_id AND o.language_id=osd.language_id)';
$data['from'][] = 'tx_multishop_orders o';
// Define section, so hooks can control the query
$data['section'] = 'admin_orders';
if ($this->get['tx_multishop_pi1']['group_by']) {
    $data['group_by'][] = addslashes($this->get['tx_multishop_pi1']['group_by']);
}
$pageset = mslib_fe::getRecordsPageSet($data);
$tmporders = $pageset['dataset'];
if ($pageset['total_rows'] > 0) {
    require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/admin_pages/includes/orders/orders_listing_table.php');
} else {
    $subpartArray = array();
    $subpartArray['###LABEL_NO_RESULTS###'] = $this->pi_getLL('no_orders_found') . '.';
    $no_results = $this->cObj->substituteMarkerArrayCached($subparts['orders_noresults'], array(), $subpartArray);
}
$payment_status_select = '<select name="payment_status" id="payment_status" class="order_select2">
<option value="">' . $this->pi_getLL('select_orders_payment_status') . '</option>';
$payment_status_select .= '<option value="paid_only"' . ($this->post['payment_status'] == 'paid_only' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('show_paid_orders_only') . '</option>';
$payment_status_select .= '<option value="unpaid_only"' . ($this->post['payment_status'] == 'unpaid_only' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('show_unpaid_orders_only') . '</option>';
$payment_status_select .= '</select>';
$groups = mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input = '';
$customer_groups_input .= '<select id="groups" class="order_select2" name="usergroup">' . "\n";
$customer_groups_input .= '<option value="0">' . $this->pi_getLL('all') . ' ' . $this->pi_getLL('usergroup') . '</option>' . "\n";
if (is_array($groups) and count($groups)) {
    foreach ($groups as $group) {
        $customer_groups_input .= '<option value="' . $group['uid'] . '"' . ($this->post['usergroup'] == $group['uid'] ? ' selected="selected"' : '') . '>' . $group['title'] . '</option>' . "\n";
    }
}
$customer_groups_input .= '</select>' . "\n";
// payment method
$payment_methods = array();
$payment_methods_label = array();
$shop_title = array();
$sql = $GLOBALS['TYPO3_DB']->SELECTquery('page_uid, payment_method, payment_method_label', // SELECT ...
    'tx_multishop_orders', // FROM ...
    'deleted=0'.((!$this->masterShop) ? ' and page_uid=\'' . $this->shop_pid . '\'' : ''), // WHERE...
    'payment_method', // GROUP BY...
    'payment_method_label asc', // ORDER BY...
    '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($sql);
while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
    $payment_method =array();
    if ($row['payment_method']) {
        $payment_method = mslib_fe::getPaymentMethod($row['payment_method'], 'p.code', 0, false);
    }
    if (empty($row['payment_method_label'])) {
        $row['payment_method'] = 'nopm';
        $row['payment_method_label'] = 'Empty payment method';
    }
    if ($this->masterShop) {
        $pageTitle=mslib_fe::getShopNameByPageUid($payment_method['page_uid'], 'All');
        $shop_title='';
        if (!empty($pageTitle)) {
            $shop_title = ' (' . $pageTitle . ')';
        }
        $row['payment_method_label'] = $row['payment_method_label'] . $shop_title;
    }
    $payment_methods[$row['payment_method']] = $row['payment_method_label'];
    $payment_methods_label[strtoupper($row['payment_method_label']). '_' . $row['payment_method']]=$row['payment_method'];

}
ksort($payment_methods_label);
$payment_method_input = '';
$payment_method_input .= '<select id="payment_method" class="order_select2" name="payment_method">' . "\n";
$payment_method_input .= '<option value="all">' . $this->pi_getLL('all_payment_methods') . '</option>' . "\n";
if (is_array($payment_methods_label) and count($payment_methods_label)) {
    foreach ($payment_methods_label as $payment_method_label => $payment_method_code) {
        $payment_method_input .= '<option value="' . $payment_method_code . '"' . ($this->post['payment_method'] == $payment_method_code ? ' selected="selected"' : '') . '>' . $payment_methods[$payment_method_code] . '</option>' . "\n";
    }
}
$payment_method_input .= '</select>' . "\n";
// shipping method
$shipping_methods = array();
$shipping_methods_label = array();
$sql = $GLOBALS['TYPO3_DB']->SELECTquery('page_uid, shipping_method, shipping_method_label', // SELECT ...
        'tx_multishop_orders', // FROM ...
        'deleted=0'.((!$this->masterShop) ? ' and page_uid=\'' . $this->shop_pid . '\'' : ''), // WHERE...
        'shipping_method', // GROUP BY...
        'shipping_method_label asc', // ORDER BY...
        '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($sql);
while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
    $shipping_method =array();
    if ($row['shipping_method']) {
        $shipping_method = mslib_fe::getShippingMethod($row['shipping_method'], 's.code', 0, false);
    }
    if (empty($row['shipping_method_label'])) {
        $row['shipping_method'] = 'nosm';
        $row['shipping_method_label'] = 'Empty shipping method';
    }
    if ($this->masterShop) {
        $pageTitle=mslib_fe::getShopNameByPageUid($shipping_method['page_uid'], 'All');
        $shop_title='';
        if (!empty($pageTitle)) {
            $shop_title = ' (' . $pageTitle . ')';
        }
        $row['shipping_method_label'] = $row['shipping_method_label'] . $shop_title;
    }
    $shipping_methods[$row['shipping_method']] = $row['shipping_method_label'];
    $shipping_methods_label[strtoupper($row['shipping_method_label'])] = $row['shipping_method'];
}
ksort($shipping_methods_label);
$shipping_method_input = '';
$shipping_method_input .= '<select id="shipping_method" class="order_select2" name="shipping_method">' . "\n";
$shipping_method_input .= '<option value="all">' . $this->pi_getLL('all_shipping_methods') . '</option>' . "\n";
if (is_array($shipping_methods_label) and count($shipping_methods_label)) {
    foreach ($shipping_methods_label as $shipping_method_label => $shipping_method_code) {
        $shipping_method_input .= '<option value="' . $shipping_method_code . '"' . ($this->post['shipping_method'] == $shipping_method_code ? ' selected="selected"' : '') . '>' . $shipping_methods[$shipping_method_code] . '</option>' . "\n";
    }
}
$shipping_method_input .= '</select>' . "\n";
// billing country
$order_countries = mslib_befe::getRecords('', 'tx_multishop_orders', '', array(), 'billing_country', 'billing_country asc');
$order_billing_country = array();
if (is_array($order_countries) && count($order_countries)) {
    foreach ($order_countries as $order_country) {
        $cn_localized_name = htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order_country['billing_country']));
        $order_billing_country[$cn_localized_name] = '<option value="' . mslib_befe::strtolower($order_country['billing_country']) . '" ' . ((mslib_befe::strtolower($this->post['country']) == strtolower($order_country['billing_country'])) ? 'selected' : '') . '>' . $cn_localized_name . '</option>';
    }
    ksort($order_billing_country);
}
$billing_countries_selectbox = '<select class="order_select2" name="country" id="country"><option value="">' . $this->pi_getLL('all_countries') . '</option>' . implode("\n", $order_billing_country) . '</select>';
$subpartArray = array();
$subpartArray['###AJAX_ADMIN_EDIT_ORDER_URL###'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=edit_order&action=edit_order');
$subpartArray['###FORM_SEARCH_ACTION_URL###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_orders');
$subpartArray['###SHOP_PID###'] = $this->shop_pid;
//
$subpartArray['###UNFOLD_SEARCH_BOX###'] = '';
if ((isset($this->get['type_search']) && !empty($this->get['type_search']) && $this->get['type_search'] != 'all') ||
    (isset($this->get['country']) && !empty($this->get['country'])) ||
    (isset($this->get['manufacturers_id']) && $this->get['manufacturers_id'] > 0) ||
    (isset($this->get['usergroup']) && $this->get['usergroup'] > 0) ||
    (isset($this->get['ordered_category']) && is_numeric($this->get['ordered_category'])) ||
    (isset($this->get['ordered_product']) && is_numeric($this->get['ordered_product'])) ||
    (isset($this->get['payment_status']) && !empty($this->get['payment_status'])) ||
    (isset($this->get['orders_status_search']) && $this->get['orders_status_search'] > 0) ||
    (isset($this->get['payment_method']) && !empty($this->get['payment_method']) && $this->get['payment_method'] != 'all') ||
    (isset($this->get['shipping_method']) && !empty($this->get['shipping_method']) && $this->get['shipping_method'] != 'all') ||
    (isset($this->get['order_date_from']) && !empty($this->get['order_date_from'])) ||
    (isset($this->get['order_date_till']) && !empty($this->get['order_date_till'])) ||
    (isset($this->get['order_expected_delivery_date_from']) && !empty($this->get['order_expected_delivery_date_from'])) ||
    (isset($this->get['order_expected_delivery_date_till']) && !empty($this->get['order_expected_delivery_date_till'])) ||
    (isset($this->get['search_by_status_last_modified']) && is_numeric($this->get['search_by_status_last_modified'])) ||
    (isset($this->get['search_by_telephone_orders']) && is_numeric($this->get['search_by_telephone_orders'])) ||
    ($this->ms['MODULES']['ALWAYS_OPEN_EXTEND_SEARCH_IN_ORDERS_LISTING']=='1')
) {
    $subpartArray['###UNFOLD_SEARCH_BOX###'] = ' in';
}
//
$subpartArray['###LABEL_KEYWORD###'] = $this->pi_getLL('keyword');
$subpartArray['###VALUE_KEYWORD###'] = ($this->post['skeyword'] ? $this->post['skeyword'] : "");
$subpartArray['###LABEL_SEARCH_ON###'] = $this->pi_getLL('search_by');
$subpartArray['###OPTION_ITEM_SELECTBOX###'] = $option_item;
$subpartArray['###LABEL_USERGROUP###'] = $this->pi_getLL('usergroup');
$subpartArray['###USERGROUP_SELECTBOX###'] = $customer_groups_input;
$subpartArray['###LABEL_PAYMENT_METHOD###'] = $this->pi_getLL('payment_method');
$subpartArray['###PAYMENT_METHOD_SELECTBOX###'] = $payment_method_input;
$subpartArray['###LABEL_SHIPPING_METHOD###'] = $this->pi_getLL('shipping_method');
$subpartArray['###SHIPPING_METHOD_SELECTBOX###'] = $shipping_method_input;
$subpartArray['###LABEL_ORDER_STATUS###'] = $this->pi_getLL('order_status');
$subpartArray['###ORDERS_STATUS_LIST_SELECTBOX###'] = $orders_status_list;
$subpartArray['###VALUE_SEARCH###'] = htmlspecialchars($this->pi_getLL('search'));
$subpartArray['###LABEL_DATE###'] = $this->pi_getLL('date');
$subpartArray['###LABEL_EXPECTED_DELIVERY_DATE###'] = $this->pi_getLL('expected_delivery_date');
$subpartArray['###LABEL_DATE_FROM###'] = $this->pi_getLL('from');
$subpartArray['###LABEL_DATE_TO###'] = $this->pi_getLL('to');
$subpartArray['###LABEL_EXPECTED_DELIVERY_DATE_FROM###'] = $this->pi_getLL('from');
$subpartArray['###LABEL_EXPECTED_DELIVERY_DATE_TO###'] = $this->pi_getLL('to');


$subpartArray['###VALUE_DATE_FROM###'] = '';
$subpartArray['###VALUE_DATE_FROM_VISUAL###'] = '';
if ($this->post['order_date_from']) {
    $subpartArray['###VALUE_DATE_FROM###'] = date('Y-m-d H:i:s', strtotime($this->post['order_date_from']));
    $subpartArray['###VALUE_DATE_FROM_VISUAL###'] = date($this->pi_getLL('locale_datetime_format'), strtotime($this->post['order_date_from']));
}
$subpartArray['###VALUE_DATE_TO###'] = '';
$subpartArray['###VALUE_DATE_TO_VISUAL###'] = '';
if ($this->post['order_date_till']) {
    $subpartArray['###VALUE_DATE_TO###'] = date('Y-m-d H:i:s', strtotime($this->post['order_date_till']));
    $subpartArray['###VALUE_DATE_TO_VISUAL###'] = date($this->pi_getLL('locale_datetime_format'), strtotime($this->post['order_date_till']));
}
$subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_FROM###'] = '';
$subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_FROM_VISUAL###'] = '';
if ($this->post['order_expected_delivery_date_from']) {
    $subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_FROM###'] = date('Y-m-d H:i:s', strtotime($this->post['order_expected_delivery_date_from']));
    $subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_FROM_VISUAL###'] = date($this->pi_getLL('locale_datetime_format'), strtotime($this->post['order_expected_delivery_date_from']));
}
$subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_TO###'] = '';
$subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_TO_VISUAL###'] = '';
if ($this->post['order_expected_delivery_date_from']) {
    $subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_TO###'] = date('Y-m-d H:i:s', strtotime($this->post['order_expected_delivery_date_till']));
    $subpartArray['###VALUE_EXPECTED_DELIVERY_DATE_TO_VISUAL###'] = date($this->pi_getLL('locale_datetime_format'), strtotime($this->post['order_expected_delivery_date_till']));
}

$subpartArray['###LABEL_FILTER_LAST_MODIFIED###'] = $this->pi_getLL('filter_by_date_status_last_modified', 'Filter by date status last modified');
$subpartArray['###LABEL_FILTER_TELEPHONE_ORDERS###'] = $this->pi_getLL('filter_by_telephone_orders', 'Filter by telephone orders');
$subpartArray['###FILTER_BY_LAST_MODIFIED_CHECKED###'] = ($this->post['search_by_status_last_modified'] ? ' checked' : '');
$subpartArray['###FILTER_BY_TELEPHONE_ORDERS_CHECKED###'] = ($this->post['search_by_telephone_orders'] ? ' checked' : '');
$subpartArray['###EXCLUDING_VAT_LABEL###'] = htmlspecialchars($this->pi_getLL('excluding_vat'));
$subpartArray['###EXCLUDING_VAT_CHECKED###'] = ($this->post['tx_multishop_pi1']['excluding_vat'] ? ' checked' : '');
$subpartArray['###LABEL_PAYMENT_STATUS###'] = $this->pi_getLL('order_payment_status');
$subpartArray['###PAYMENT_STATUS_SELECTBOX###'] = $payment_status_select;
$subpartArray['###LABEL_RESULTS_LIMIT_SELECTBOX###'] = $this->pi_getLL('limit_number_of_records_to');
$subpartArray['###LABEL_ADVANCED_SEARCH###'] = $this->pi_getLL('advanced_search');
$subpartArray['###LABEL_ORDERED_PRODUCT###'] = $this->pi_getLL('admin_ordered_product');
$subpartArray['###VALUE_ORDERED_PRODUCT###'] = $this->post['ordered_product'];
$subpartArray['###RESULTS_LIMIT_SELECTBOX###'] = $limit_selectbox;
$subpartArray['###RESULTS###'] = $order_results;
$subpartArray['###NORESULTS###'] = $no_results;
$subpartArray['###ADMIN_LABEL_TABS_ORDERS###'] = $this->pi_getLL('admin_label_tabs_orders');
$subpartArray['###LABEL_RESET_ADVANCED_SEARCH_FILTER###'] = $this->pi_getLL('reset_advanced_search_filter');
$subpartArray['###ADMIN_LABEL_YES###'] = $this->pi_getLL('yes');
$subpartArray['###ADMIN_LABEL_NO###'] = $this->pi_getLL('no');
$subpartArray['###UPDATE_ORDER_STATUS###'] = $this->pi_getLL('update_order_status');
$subpartArray['###ADMIN_AJAX_UPDATE_ORDER_STATUS_PRE_URL###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_update_orders_status_pre');
$subpartArray['###ADMIN_AJAX_UPDATE_ORDER_STATUS_URL###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_update_orders_status');
$subpartArray['###ADMIN_AJAX_UPDATE_ORDER_STATUS_URL2###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_update_orders_status');
$subpartArray['###LABEL_JS_DO_YOU_WANT_CHANGE_ORDERS_ID_X_TO_STATUS_X###']= $this->pi_getLL('admin_label_js_do_you_want_to_change_orders_id_x_to_status_x');
$subpartArray['###DATE_TIME_JS_FORMAT0###'] = $this->pi_getLL('locale_date_format_js');
$subpartArray['###DATE_TIME_JS_FORMAT1###'] = $this->pi_getLL('locale_date_format_js');
$subpartArray['###DATE_TIME_JS_FORMAT2###'] = $this->pi_getLL('locale_date_format_js');
$subpartArray['###DATE_TIME_JS_FORMAT3###'] = $this->pi_getLL('locale_date_format_js');
// search on shop
$subpartArray['###SEARCH_IN_SHOP_SELECTBOX###']='';
if ($this->conf['masterShop']) {
    $active_shop = mslib_fe::getActiveShop();
    if (count($active_shop) > 1) {
        $shop_select2=array();
        $shop_select2[]='<option value="all">'.$this->pi_getLL('all').'</option>';
        foreach ($active_shop as $pageinfo) {
            $pageTitle = $pageinfo['title'];
            if ($pageinfo['nav_title']) {
                $pageTitle = $pageinfo['nav_title'];
            }
            $shop_select2[]='<option value="'.$pageinfo['uid'].'"'.($this->post['tx_multishop_pi1']['search_in_shop']==$pageinfo['uid'] ? ' selected="selected"' : '').'>'.$pageTitle.'</option>';
        }
        $subpartArray['###SEARCH_IN_SHOP_SELECTBOX###']='<div class="form-group">
            <label for="search_in_shop" class="control-label">'.$this->pi_getLL('search_in_shop').'</label>
            '.(count($shop_select2)>1 ? '<select name="tx_multishop_pi1[search_in_shop]" id="search_in_shop" style="width: 200px;">'.implode('', $shop_select2).'</select>' : '').'
        </div>
        ';
    }

}
// Instantiate admin interface object
$objRef = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj('EXT:multishop/pi1/classes/class.tx_mslib_admin_interface.php:&tx_mslib_admin_interface');
$objRef->init($this);
$objRef->setInterfaceKey('admin_orders');
// Header buttons
$headerButtons = array();
$headingButton = array();
$headingButton['btn_class'] = 'btn btn-primary';
$headingButton['fa_class'] = 'fa fa-plus-circle';
$headingButton['title'] = $this->pi_getLL('admin_label_create_order');
$headingButton['key'] = 'admin_create_order';
$headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_new_order');
$headerButtons[] = $headingButton;
// Set header buttons through interface class so other plugins can adjust it
$objRef->setHeaderButtons($headerButtons);
// Get header buttons through interface class so we can render them
$subpartArray['###INTERFACE_HEADER_BUTTONS###'] = $objRef->renderHeaderButtons();
$subpartArray['###LABEL_COUNTRIES_SELECTBOX###'] = $this->pi_getLL('countries');
$subpartArray['###COUNTRIES_SELECTBOX###'] = $billing_countries_selectbox;
$subpartArray['###LABEL_MANUFACTURERS_SELECTBOX###'] = $this->pi_getLL('manufacturers');
$subpartArray['###VALUE_MANUFACTURERS_ID###'] = $this->post['manufacturers_id'];
$subpartArray['###BACK_BUTTON###'] = '<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="' . mslib_fe::typolink() . '"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> ' . $this->pi_getLL('admin_close_and_go_back_to_catalog') . '</a></div></div></div>';
$subpartArray['###LABEL_ORDERED_CATEGORY###'] = $this->pi_getLL('admin_ordered_category');
$subpartArray['###VALUE_ORDERED_CATEGORY###'] = $this->get['ordered_category'];

if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersMainTemplatePreProc'])) {
    $params = array(
        'subparts' => &$subparts,
        'subpartArray' => &$subpartArray,
        'headerButtons' => &$headerButtons
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersMainTemplatePreProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
$content .= $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
$GLOBALS['TSFE']->additionalHeaderData[] = '
<script type="text/javascript">
jQuery(document).ready(function($) {
    $(document).on("click", "#reset-advanced-search", function(e){
        location.href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_orders') . '";
    });
    '.(!empty($subpartArray['###SEARCH_IN_SHOP_SELECTBOX###']) ? '
    $("#search_in_shop").select2();
    ' : '').'
    $(\'#manufacturers_id\').select2({
		placeholder: \'' . $this->pi_getLL('admin_choose_manufacturer') . '\',
		dropdownCssClass: "", // apply css that makes the dropdown taller
		width:\'100%\',
		minimumInputLength: 0,
		multiple: false,
		//allowClear: true,
		query: function(query) {
			$.ajax(\'' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=getManufacturersList') . '\', {
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
				var split_id=id.split(",");
				var callback_data=[];
				$.ajax(\'' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=getManufacturersList') . '\', {
					data: {
						preselected_id: id
					},
					dataType: "json"
				}).done(function(data) {
					$.each(data, function(i,val){
						callback(val);
					});

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
				$.each(data, function(i,val){
					return val.text;
				});
			} else {
				return data.text;
			}
		},
		escapeMarkup: function (m) { return m; }
	});
	$(".order_select2").select2();
	$(".ordered_product").select2({
		placeholder: "' . $this->pi_getLL('all') . '",
		minimumInputLength: 0,
		query: function(query) {
			$.ajax("' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=get_ordered_products') . '", {
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
				$.ajax("' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=get_ordered_products') . '", {
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
		dropdownCssClass: "orderedProductsDropDownCss",
		escapeMarkup: function (m) { return m; }
	});
	var ordered_select2 = function (selector, ajax_url) {
        $(selector).select2({
            placeholder: "' . $this->pi_getLL('all') . '",
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
                    $.ajax(ajax_url, {
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
            dropdownCssClass: "orderedProductsDropDownCss",
            escapeMarkup: function (m) { return m; }
        });
    }
    ordered_select2(".ordered_category", "' . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=get_ordered_categories') . '");
});
</script>
';
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersMainPostProc'])) {
    $params = array(
        'content' => &$content
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersMainPostProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
?>