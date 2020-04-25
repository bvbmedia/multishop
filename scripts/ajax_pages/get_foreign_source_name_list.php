<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data = array();
    $limit = 100;
    $filter = array();
    if (isset($this->get['foreign_source_name'])) {
		if (empty($this->get['foreign_source_name'] == 'blank value')) {
			$this->get['foreign_source_name'] = '';
		}
		$filter[] = '(foreign_source_name like \'%' . $this->get['foreign_source_name'] . '%\')';
    }
    if (!empty($this->get['q'])) {
		if (empty($this->get['q'] == 'blank value')) {
			$this->get['q'] = '';
		}
        $filter[] = '(foreign_source_name like \'%' . $this->get['q'] . '%\')';
        $limit = '';
    }
    $counter = 0;
    if (count($filter) || (isset($this->get['q']) && empty($this->get['q']))) {
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
					$row['foreign_source_name'] = 'blank value';
				}
                $return_data[$counter]['text'] = htmlentities($row['foreign_source_name']);
                $return_data[$counter]['id'] = $row['foreign_source_name'];
                $counter++;
            }
        }
    }
    echo json_encode($return_data);
    exit;
}
exit();
?>