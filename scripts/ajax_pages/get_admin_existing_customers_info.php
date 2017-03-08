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
                $return_data = $row;
            }
        }
    }
    echo json_encode($return_data, ENT_NOQUOTES);
}
exit();
?>