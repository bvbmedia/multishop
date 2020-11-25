<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data = array();
    $select = array();
    switch ($this->get['section']) {
        case 'subscriptions':
            $filter = array();
            if (isset($this->get['subscriptions_id']) && is_numeric($this->get['subscriptions_id']) && $this->get['subscriptions_id'] > 0) {
                if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] > 0) {
                    $filter[] = 'f.uid=' . $this->get['cid'];
                }
                $filter[] = 's.subscriptions_id=' . $this->get['subscriptions_id'];
                $filter[] = 'f.deleted=0';
                $filter[] = 'f.uid=s.customer_id';
                $from = array();
                $from[] = 'fe_users f, tx_multishop_subscriptions s';
                $select[] = 's.customer_id';
            }
            break;
        case 'invoices':
            $filter = array();
            if (isset($this->get['invoices_id']) && is_numeric($this->get['invoices_id']) && $this->get['invoices_id'] > 0) {
                if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] > 0) {
                    $filter[] = 'f.uid=' . $this->get['cid'];
                }
                $filter[] = 'i.invoice_id=' . $this->get['invoices_id'];
                $filter[] = 'f.deleted=0';
                $filter[] = 'f.uid=i.customer_id';
                $from = array();
                $from[] = 'fe_users f, tx_multishop_invoices i';
                $select[] = 'i.customer_id';
            }
            break;
        case 'projects':
            $filter = array();
            if (isset($this->get['projects_id']) && is_numeric($this->get['projects_id']) && $this->get['projects_id'] > 0) {
                if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] > 0) {
                    $filter[] = 'f.uid=' . $this->get['cid'];
                }
                $filter[] = 'p.projects_id=' . $this->get['projects_id'];
                $filter[] = 'f.deleted=0';
                $filter[] = 'f.uid=p.customer_id';
                $from = array();
                $from[] = 'fe_users f, tx_multishop_projects p';
                $select[] = 'p.customer_id';
            }
            break;
        case 'orders':
            $filter = array();
            if (isset($this->get['orders_id']) && is_numeric($this->get['orders_id']) && $this->get['orders_id'] > 0) {
                if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] > 0) {
                    $filter[] = 'f.uid=' . $this->get['cid'];
                }
                $filter[] = 'o.orders_id=' . $this->get['orders_id'];
                $filter[] = 'f.deleted=0';
                $filter[] = 'f.uid=o.customer_id';
                $from = array();
                $from[] = 'fe_users f, tx_multishop_orders o';
                $select[] = 'o.customer_id, o.billing_email';
            }
            break;
        case 'proposals':
            $filter = array();
            if (isset($this->get['proposals_id']) && is_numeric($this->get['proposals_id']) && $this->get['proposals_id'] > 0) {
                if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] > 0) {
                    $filter[] = 'f.uid=' . $this->get['cid'];
                }
                $filter[] = 'p.proposals_id=' . $this->get['proposals_id'];
                $filter[] = 'f.deleted=0';
                $filter[] = 'f.uid=p.customer_id';
                $from = array();
                $from[] = 'fe_users f, tx_multishop_proposals p';
                $select[] = 'p.customer_id, p.billing_email';
            }
            break;
        default:
            if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] > 0) {
                $filter = array();
                $filter[] = 'f.uid=' . $this->get['cid'];
                $filter[] = 'f.deleted=0';
                $from = array();
                $from[] = 'fe_users f';
            }
            break;
    }
    $select[] = 'f.address';
    $select[] = 'f.address_ext';
    $select[] = 'f.address_number';
    $select[] = 'f.building';
    $select[] = 'f.city';
    $select[] = 'f.company';
    $select[] = 'f.contact_email';
    $select[] = 'f.country';
    $select[] = 'f.deleted';
    $select[] = 'f.department';
    $select[] = 'f.email';
    $select[] = 'f.fax';
    $select[] = 'f.first_name';
    $select[] = 'f.gender';
    $select[] = 'f.image';
    $select[] = 'f.last_name';
    $select[] = 'f.middle_name';
    $select[] = 'f.mobile';
    $select[] = 'f.name';
    $select[] = 'f.street_name';
    $select[] = 'f.telephone';
    $select[] = 'f.title';
    $select[] = 'f.www';
    $select[] = 'f.zip';
    if (is_array($filter) && count($filter)) {
        $query = $GLOBALS['TYPO3_DB']->SELECTquery(implode(', ', $select), // SELECT ...
                implode(', ', $from), // FROM ...
                implode(' and ', $filter), // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $return_data = $row;
            }
        }
    }
    echo json_encode($return_data, ENT_NOQUOTES);
}
exit();
