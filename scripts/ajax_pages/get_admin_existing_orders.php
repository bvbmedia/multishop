<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data = array();
    $orders = array();

    $orderby = 'orders_id';
    $limit = 50;
    $filter = array();
    if (is_numeric($this->get['preselected_id'])) {
        $filter[] = 'orders_id=' . $this->get['preselected_id'];
    }
    $customer_id=0;
    if (isset($this->get['q']) && !empty($this->get['q'])) {
        $limit = '';
        if (strpos($this->get['q'], '||customer_id=') !== false) {
            $tmp_value = explode('||customer_id=', $this->get['q']);
            $this->get['q'] = trim($tmp_value[0]);
            if (is_numeric($tmp_value[1]) && $tmp_value[1]>0) {
                $customer_id = $tmp_value[1];
            }
        } else {
            $this->get['q'] = trim($this->get['q']);
        }
        $this->get['q'] = addslashes($this->get['q']);
        $filter[] = 'orders_id like \'' . $this->get['q'] . '%\'';
    }
    if (is_numeric($customer_id) && $customer_id > 0) {
        $filter[] = 'customer_id=' . $customer_id;
    }
    if (!$this->masterShop) {
        $filter[] = 'page_uid=\'' . $this->shop_pid . '\'';
    }
    $filter[] = 'deleted=0';
    $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
            'tx_multishop_orders', // FROM ...
            implode(' and ', $filter), // WHERE...
            '', // GROUP BY...
            $orderby, // ORDER BY...
            $limit // LIMIT ...
    );
    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    $tel = 0;
    if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $orders[] = $row;
        }
    }
    foreach ($orders as $order) {
        if ($order['orders_id']) {
            $itemTitle = $order['orders_id'];
            $return_data[] = array(
                    'id' => $order['orders_id'],
                    'text' => $itemTitle
            );
        }
    }
    if (is_numeric($this->get['preselected_id']) && $this->get['preselected_id'] > 0 && count($orders) === 1) {
        $tmp_return_data = $return_data[0];
        $return_data = $tmp_return_data;
    } else {
        if ((!isset($this->get['preselected_id']) || !$this->get['preselected_id']) && empty($this->get['q'])) {
            $array_select_none = array(
                'id' => '',
                'text' => $this->pi_getLL('select_order')
            );
            array_unshift($return_data, $array_select_none);
        }
    }
    echo json_encode($return_data, ENT_NOQUOTES);
}
exit();
?>