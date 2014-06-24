<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$compiledWidget['key']='referrerToplist';
$compiledWidget['defaultCol']=3;
$compiledWidget['title']=$this->pi_getLL('referrer_toplist', 'Verwijzende websites');
$where=array();
$where[]='f.http_referer <> \'\' and f.deleted=0';
switch ($this->dashboardArray['section']) {
	case 'admin_home':
		break;
	case 'admin_edit_customer':
		if ($this->get['tx_multishop_pi1']['cid'] && is_numeric($this->get['tx_multishop_pi1']['cid'])) {
			$where[]='(f.customer_id='.$this->get['tx_multishop_pi1']['cid'].')';
		}
		break;
}
$str=$GLOBALS['TYPO3_DB']->SELECTquery('f.http_referer, count(1) as total', // SELECT ...
	'tx_multishop_orders f', // FROM ...
	'('.implode(" AND ", $where).')', // WHERE...
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
	$compiledWidget['content']='<p>'.$this->pi_getLL('admin_label_orders_come_from_referer').'.</p>';
	$counter=0;
	$compiledWidget['content'].='<table width="100%" class="msZebraTable tblWidget" cellspacing="0" cellpadding="0" border="0" >';
	foreach ($data as $host=>$item) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
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