<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$compiledWidget['key']='referrerToplist';
$compiledWidget['defaultCol']=3;
$compiledWidget['title']=$this->pi_getLL('referrer_toplist', 'Verwijzende websites');
$data_query=array();
$data_query['where'][]='f.http_referer <> \'\' and f.deleted=0';
switch ($this->dashboardArray['section']) {
	case 'admin_home':
		break;
	case 'admin_edit_customer':
		if ($this->get['tx_multishop_pi1']['cid'] && is_numeric($this->get['tx_multishop_pi1']['cid'])) {
			$data_query['where'][]='(f.customer_id='.$this->get['tx_multishop_pi1']['cid'].')';
		}
		break;
}
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_home/referrerToplist.php']['referrerToplistHomeStatsQueryHookPreProc'])) {
	$params=array(
		'data_query'=>&$data_query
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_home/referrerToplist.php']['referrerToplistHomeStatsQueryHookPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$str=$GLOBALS['TYPO3_DB']->SELECTquery('f.http_referer, count(1) as total', // SELECT ...
	'tx_multishop_orders f', // FROM ...
	'('.implode(" AND ", $data_query['where']).')', // WHERE...
	'f.http_referer', // GROUP BY...
	'total desc', // ORDER BY...
	'10' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$data=array();
$data[]=array(
	$this->pi_getLL('referrer'),
	$this->pi_getLL('qty')
);
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	$domain=parse_url($row['http_referer']);
	$data[$domain['host']]=array(
		$row['http_referer'],
		(int)$row['total']+$data[$domain['host']][1]
	);
}
if (count($data)==1) {
	$compiledWidget['content']='<p>'.$this->pi_getLL('admin_label_data_not_available').'</p>';
} else {
	$counter=0;
	$compiledWidget['content'].='<table class="table table-striped table-bordered tblWidget">';
	if (count($data)) {
		$compiledWidget['content'].='
		<thead>
			<tr>
				<th>'.$data[0][0].'</th>
				<th class="text-right">'.$data[0][1].'</th>
			</tr>
		</thead>
		';
		$compiledWidget['content'].='<tbody>';
		foreach ($data as $host=>$item) {
			$counter++;
			if ($counter>1) {
				$compiledWidget['content'].='
				<tr>
					<td>'.$host.'</td>
					<td class="text-right">'.number_format($item[1], 0, '', '.').'</td>
				</tr>
				';
			}
		}
		$compiledWidget['content'].='</tbody>';
	}
	$compiledWidget['content'].='</table>';
}
?>