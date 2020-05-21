<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data = array();
    $limit = 100;
    $filter = array();
    if (isset($this->get['products_model']) && $this->get['products_model'] != 'all') {
		if ($this->get['products_model'] == 'Blank') {
			$this->get['products_model'] = '';
		}
		$filter[] = '(products_model like \'%' . $this->get['products_model'] . '%\')';
    }
    if (!empty($this->get['q']) && $this->get['q'] != 'all') {
		if (empty($this->get['q'] == 'Blank')) {
			$this->get['q'] = '';
		}
        $filter[] = '(products_model like \'%' . $this->get['q'] . '%\')';
        $limit = '';
    }
    $counter = 0;
    if (count($filter) || (isset($this->get['q']) && empty($this->get['q']))) {
		$return_data[$counter]['text'] = htmlentities($this->pi_getLL('all'));
		$return_data[$counter]['id'] = 'all';
		$counter++;
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('products_model', // SELECT ...
                'tx_multishop_products', // FROM ...
                implode(' and ', $filter), // WHERE...
                'products_model', // GROUP BY...
                'products_model asc', // ORDER BY...
                $limit // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            	if (empty($row['products_model'])) {
					$row['products_model'] = 'Blank';
				}
                $return_data[$counter]['text'] = htmlentities($row['products_model']);
                $return_data[$counter]['id'] = $row['products_model'];
                $counter++;
            }
        }
    }
	if (isset($this->get['products_model'])) {
		if ($this->get['products_model'] == 'all') {
			$return_data = array();
			$return_data[0]['text'] = htmlentities($this->pi_getLL('all'));
			$return_data[0]['id'] = 'all';
		}
		if ($this->get['products_model'] == '') {
			$return_data = array();
			$return_data[0]['text'] = 'Blank';
			$return_data[0]['id'] = 'Blank';
		}
	}
    echo json_encode($return_data);
    exit;
}
exit();
?>