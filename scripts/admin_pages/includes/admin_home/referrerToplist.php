<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$sql="select http_referer, count(1) as total from tx_multishop_orders where http_referer <> '' and deleted=0 group by http_referer order by total desc limit 10";
$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
$data=array();
$data[]=array(
	'Referrer',
	'Aantal'
);
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	$domain=parse_url($row['http_referer']);
	$data[$domain['host']]=array(
		$row['http_referer'],
		(int)$row['total']+$data[$domain['host']][1]
	);
}
if (count($data)==1) {
	$libaryWidgets['referrerToplist']['content']='<p>'.$this->pi_getLL('admin_label_data_not_available').'</p>';
} else {
	$libaryWidgets['referrerToplist']['content']='<p>'.$this->pi_getLL('admin_label_orders_come_from_referer').'.</p>';
	$counter=0;
	$libaryWidgets['referrerToplist']['content'].='<table width="100%" class="msZebraTable tblWidget" cellspacing="0" cellpadding="0" border="0" >';
	foreach ($data as $host=>$item) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$counter++;
		$libaryWidgets['referrerToplist']['content'].='<tr>';
		if ($counter==1) {
			$libaryWidgets['referrerToplist']['content'].='
				<th>'.$item[0].'</th>
				<th>'.$item[1].'</th>		
			';
		} else {
			$libaryWidgets['referrerToplist']['content'].='
				<td>'.$host.'</td>
				<td>'.number_format($item[1], 0, 3, '.').'</td>
			';
		}
		$libaryWidgets['referrerToplist']['content'].='</tr>';
	}
	$libaryWidgets['referrerToplist']['content'].='</table>';
}
?>