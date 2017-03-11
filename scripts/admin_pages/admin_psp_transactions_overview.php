<?php
$conf = array();
// TITLE PRINTED ON THE TAB
$conf['title'] = $this->pi_getLL('admin_psp_transactions_overview');
// DEFINE COLUMNS TO BE PRINTED IN THE TABLE
$conf['tableColumns'] = array();
$conf['tableColumns']['orders_id'] = array(
        'title' => $this->pi_getLL('orders_id'),
        'align' => '',
        'nowrap' => 1,
        'href' => 'index.php?id=' . $this->shop_pid . '&type=2003&tx_multishop_pi1[page_section]=edit_order&action=edit_customer&orders_id=###orders_id###'
);
$conf['tableColumns']['transaction_id'] = array(
        'title' => $this->pi_getLL('transaction_id'),
        'align' => '',
        'nowrap' => 1,
        'valueType' => ''
);
$conf['tableColumns']['code'] = array(
        'title' => $this->pi_getLL('payment_method'),
        'align' => 'center',
        'nowrap' => 1,
        'valueType' => ''
);
/*$conf['tableColumns']['provider']=array(
        'title'=>$this->pi_getLL('payment_method'),
        'align'=>'center',
        'nowrap'=>1,
        'valueType'=>''
);*/
$conf['tableColumns']['crdate'] = array(
        'title' => $this->pi_getLL('creation_date'),
        'align' => 'right',
        'nowrap' => 1,
        'valueType' => 'timestamp_to_date'
);
// ENABLE SEARCH FORM
$conf['settings']['enableKeywordSearch'] = 1;
//$conf['settings']['enableRowBasedCheckboxSelection']=1;
$conf['settings']['rowBasedCheckboxSelectionKey'] = 'orders_id';
$conf['settings']['enableActionSelectionForm'] = 1;
// DEFINE QUERY ELEMENTS
$conf['query']['select'] = array();
$conf['query']['select'][] = 'pt.orders_id, pt.transaction_id, pt.code, pm.provider, pt.crdate';
$conf['query']['from'] = 'tx_multishop_payment_transactions pt LEFT JOIN tx_multishop_orders o ON pt.orders_id=o.orders_id LEFT JOIN tx_multishop_payment_methods pm ON pt.code=pm.code';
//, tx_multishop_payment_methods_description pmd
// WHEN USING KEYWORD SEARCH INPUT THAN FILTER THE STRING ON THE FOLLOWING FIELDS
$conf['query']['keywordSearchByColumns'] = array();
$conf['query']['keywordSearchByColumns'][] = 'pt.orders_id';
$conf['query']['keywordSearchByColumns'][] = 'pt.transaction_id';
$conf['query']['keywordSearchByColumns'][] = 'pt.code';
// DEFAULT ORDER BY COLUMN
switch ($this->get['tx_multishop_pi1']['sortBy']) {
    default:
        $conf['query']['defaultOrderByColumns'][] = 'pt.orders_id';
        break;
}
// ASC OR DESC
if (!$this->get['limit']) {
    $this->get['limit'] = 15;
}
$conf['query']['defaultOrder'] = 'desc';
$conf['settings']['skipTabMarkup'] = '1';
$conf['settings']['limit'] = $this->get['limit'];
// HIDDEN FIELDS USED ON SEARCHFORM
$conf['searchForm']['hiddenFields']['id'] = $this->shop_pid;
$conf['searchForm']['hiddenFields']['do_search'] = 1;
$conf['searchForm']['hiddenFields']['type'] = 2003;
$conf['searchForm']['hiddenFields']['tx_multishop_pi1[page_section]'] = $this->get['tx_multishop_pi1']['page_section'];
// ACTION URL ON THE POSTFORM
$conf['postForm']['actionUrl'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $this->get['tx_multishop_pi1']['page_section']);
// GRAND TOTALS PRINTED BELOW THE TABLE
$filter = array();
$conf['summarizeData']['totalRecordsInTable'] = mslib_befe::getCount('', 'tx_multishop_payment_transactions', '', $filter);
// Instantiate admin interface object
$objRef = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj('EXT:multishop/pi1/classes/class.tx_mslib_admin_interface.php:&tx_mslib_admin_interface');
$objRef->setInterfaceKey('admin_psp_transactions_overview');
//$conf['msDebug']=1;
$content = $objRef->renderInterface($conf, $this);
?>