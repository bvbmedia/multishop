<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $jsonData = array();
    if (is_numeric($this->post['tx_multishop_pi1']['customer_id'])) {
        $customer = mslib_fe::getUser($this->post['tx_multishop_pi1']['customer_id']);
        if ($customer['uid']) {
            $actionButtons = array();
            if ($customer['email']) {
                $actionLink = 'mailto:' . $customer['email'];
                $actionButtons['email'] = '<a href="' . $actionLink . '" class="btn btn-xs btn-default"><i class="fa fa-envelope-o"></i> ' . $this->pi_getLL('email') . '</a>';
            }
            if ($customer['telephone']) {
                $actionLink = 'callto:' . $customer['telephone'];
                $actionButtons['call'] = '<a href="' . $actionLink . '" class="btn btn-xs btn-default"><i class="fa fa-phone-square"></i> ' . $this->pi_getLL('call') . '</a>';
            }
            $address = array();
            $address[] = rawurlencode($customer['address']);
            $address[] = rawurlencode($customer['zip']);
            $address[] = rawurlencode($customer['city']);
            $address[] = rawurlencode($customer['country']);
            $actionLink = 'http://maps.google.com/maps?daddr=' . implode('+', $address);
            $actionButtons['travel_guide'] = '<a href="' . $actionLink . '" rel="nofollow" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-map-marker"></i> ' . $this->pi_getLL('travel_guide') . '</a>';
            $jsonData['html'] = '';
            $htmlArray = array();
            if ($customer['company']) {
                $htmlArray['company'] = '<h1>' . $customer['company'] . '</h1>';
            }
            if ($customer['name']) {
                $htmlArray['name'] = '<h1>' . $customer['name'] . '</h1>';
            }
            $htmlArray['address'] = $customer['address'] . '<br />';
            $htmlArray['zip_city'] = $customer['zip'] . ' ' . $customer['city'] . ' <br />';
            $htmlArray['country'] = mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $customer['country']) . '<br /><br />';
            if ($customer['email']) {
                $htmlArray['email'] = $this->pi_getLL('email') . ': <a href="mailto:' . $customer['email'] . '">' . $customer['email'] . '</a><br />';
            }
            if ($customer['telephone']) {
                $htmlArray['telephone'] = $this->pi_getLL('telephone') . ': ' . $customer['telephone'] . '<br />';
            }
            if ($customer['mobile']) {
                $htmlArray['mobile'] = $this->pi_getLL('mobile') . ': ' . $customer['mobile'] . '<br />';
            }
            if ($customer['fax']) {
                $htmlArray['fax'] = $this->pi_getLL('fax') . ': ' . $customer['fax'] . '<br />';
            }
            // custom page hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_admin_customers_listing_details.php']['actionButtonsAdminCustomersTooltipPreProc'])) {
                $params = array(
                        'actionButtons' => &$actionButtons,
                        'customer' => &$customer,
                        'htmlArray' => &$htmlArray
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_admin_customers_listing_details.php']['actionButtonsAdminCustomersTooltipPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            $jsonData['html'] .= implode('', $htmlArray);
            // custom page hook that can be controlled by third-party plugin eol
            if (count($actionButtons)) {
                $jsonData['html'] .= '<div class="btn-group">';
                foreach ($actionButtons as $actionButton) {
                    $jsonData['html'] .= $actionButton;
                }
                $jsonData['html'] .= '</div>';
            }
        } else {
            $jsonData['html'] = 'No data.';
        }
    }
    echo json_encode($jsonData, ENT_NOQUOTES);
}
exit();
?>