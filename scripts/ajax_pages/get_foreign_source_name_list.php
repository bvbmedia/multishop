<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data = array();
    $limit = 100;
    $filter = array();
    if (isset($this->get['foreign_source_name']) && $this->get['foreign_source_name'] != 'all') {
		if ($this->get['foreign_source_name'] == 'Blank') {
			$this->get['foreign_source_name'] = '';
		}
		$filter[] = '(foreign_source_name like \'%' . $this->get['foreign_source_name'] . '%\')';
    }
    if (!empty($this->get['q']) && $this->get['q'] != 'all') {
		if (empty($this->get['q'] == 'Blank')) {
			$this->get['q'] = '';
		}
        $filter[] = '(foreign_source_name like \'%' . $this->get['q'] . '%\')';
        $limit = '';
    }
    $counter = 0;
    if (count($filter) || (isset($this->get['q']) && empty($this->get['q']))) {
		$return_data[$counter]['text'] = htmlentities($this->pi_getLL('all'));
		$return_data[$counter]['id'] = 'all';
		$counter++;
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('foreign_source_name', // SELECT ...
                'tx_multishop_products', // FROM ...
                implode(' and ', $filter), // WHERE...
                'foreign_source_name', // GROUP BY...
                'foreign_source_name asc', // ORDER BY...
                $limit // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            	if (empty($row['foreign_source_name'])) {
					$row['foreign_source_name'] = 'Blank';
				}
                $return_data[$counter]['text'] = htmlentities($row['foreign_source_name']);
                $return_data[$counter]['id'] = $row['foreign_source_name'];
                $counter++;
            }
        }
    }
	if (isset($this->get['foreign_source_name'])) {
		if ($this->get['foreign_source_name'] == 'all') {
			$return_data = array();
			$return_data[0]['text'] = htmlentities($this->pi_getLL('all'));
			$return_data[0]['id'] = 'all';
		}
		if ($this->get['foreign_source_name'] == '') {
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