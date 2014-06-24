<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$compiledWidget['key']='searchKeywordsToplist';
$compiledWidget['defaultCol']=3;
$compiledWidget['title']=$this->pi_getLL('search_keywords_toplist', 'Gezochte termen');
$where=array();
$where[]='s.keyword <> \'\'';
switch ($this->dashboardArray['section']) {
	case 'admin_home':
		break;
	case 'admin_edit_customer':
		if ($this->get['tx_multishop_pi1']['cid'] && is_numeric($this->get['tx_multishop_pi1']['cid'])) {
			$where[]='(s.customer_id='.$this->get['tx_multishop_pi1']['cid'].')';
		}
		break;
}
$str=$GLOBALS['TYPO3_DB']->SELECTquery('s.keyword, count(s.keyword) as total, s.negative_results', // SELECT ...
	'tx_multishop_products_search_log s', // FROM ...
	'('.implode(" AND ", $where).')', // WHERE...
	's.keyword', // GROUP BY...
	'total desc', // ORDER BY...
	'10' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$data=array();
$data[]=array(
	$this->pi_getLL('keyword'),
	$this->pi_getLL('qty')
);
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	$data[$row['keyword']]=array(
		$row['keyword'],
		(int)$row['total']
	);
}
if (count($data)==1) {
	$compiledWidget['content']='<p>'.$this->pi_getLL('admin_label_data_not_available').'</p>';
} else {
//	$compiledWidget['content']='<p>Websites waarvandaan bestellingen tot stand zijn gekomen.</p>';
	$counter=0;
	$compiledWidget['content'].='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" >';
	foreach ($data as $host=>$item) {
		$counter++;
		$compiledWidget['content'].='<tr>';
		if ($counter==1) {
			$compiledWidget['content'].='
				<th>'.$item[0].'</th>
				<th>'.$item[1].'</th>		
			';
		} else {
			$compiledWidget['content'].='
				<td>'.$host.'</td>
				<td>'.number_format($item[1], 0, 3, '.').'</td>
			';
		}
		$compiledWidget['content'].='</tr>';
	}
	$compiledWidget['content'].='</table>';
}
?>