<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data = array();
    if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] >0) {

        $filter = array();
        $filter[] = 'uid=' . $this->get['cid'];
        $filter[] = 'deleted=0';
        $select=array();
        $select[]='address';
        $select[]='address_ext';
        $select[]='address_number';
        $select[]='building';
        $select[]='city';
        $select[]='company';
        $select[]='contact_email';
        $select[]='country';
        $select[]='deleted';
        $select[]='department';
        $select[]='email';
        $select[]='fax';
        $select[]='first_name';
        $select[]='gender';
        $select[]='image';
        $select[]='last_name';
        $select[]='middle_name';
        $select[]='mobile';
        $select[]='name';
        $select[]='street_name';
        $select[]='telephone';
        $select[]='title';
        $select[]='www';
        $select[]='zip';
        $query = $GLOBALS['TYPO3_DB']->SELECTquery(implode(', ', $select), // SELECT ...
            'fe_users', // FROM ...
            implode(' and ', $filter), // WHERE...
            '', // GROUP BY...
            '', // ORDER BY...
            '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                unset($row['TSconfig']);
                unset($row['bic_account_number']);
                unset($row['cruser_id']);
                unset($row['disable']);
                unset($row['disable_jira_worklog_notification_mail']);
                unset($row['fe_cruser_id']);
                unset($row['felogin_forgotHash']);
                unset($row['felogin_redirectPid']);
                unset($row['http_referer']);
                unset($row['iban_account_number']);
                unset($row['ip_address']);
                unset($row['is_online']);
                unset($row['jira_worklog_notification_email']);
                unset($row['lastlogin']);
                unset($row['lockToDomain']);
                unset($row['page_uid']);
                unset($row['password']);
                unset($row['payment_method']);
                unset($row['pid']);
                unset($row['region']);
                unset($row['starttime']);
                unset($row['status']);
                unset($row['tstamp']);
                unset($row['tx_extbase_type']);
                unset($row['tx_multishop_changed']);
                unset($row['tx_multishop_check_subscription']);
                unset($row['tx_multishop_coc_id']);
                unset($row['tx_multishop_code']);
                unset($row['tx_multishop_customer_id']);
                unset($row['tx_multishop_discount']);
                unset($row['tx_multishop_geo_lat']);
                unset($row['tx_multishop_geo_lng']);
                unset($row['tx_multishop_language']);
                unset($row['tx_multishop_newsletter']);
                unset($row['tx_multishop_optin_crdate']);
                unset($row['tx_multishop_optin_ip']);
                unset($row['tx_multishop_payment_condition']);
                unset($row['tx_multishop_quick_checkout']);
                unset($row['tx_multishop_show_on_googlemap']);
                unset($row['tx_multishop_source_id']);
                unset($row['tx_multishop_vat_id']);
                unset($row['uc']);
                unset($row['uid']);
                unset($row['usergroup']);
                unset($row['username']);
                $return_data = $row;
            }
        }
    }
    echo json_encode($return_data, ENT_NOQUOTES);
}
exit();
?>