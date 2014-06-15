<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$compiledWidget['key']='searchKeywordsToplist';
$compiledWidget['defaultCol']=3;
$compiledWidget['title']=$this->pi_getLL('search_keywords_toplist', 'Gezochte termen');
$str="SELECT s.keyword, count(s.keyword) as total, s.negative_results FROM tx_multishop_products_search_log s where s.keyword <> '' group by s.keyword order by total desc limit 10";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$data=array();
$data[]=array(
	'Zoekwoord',
	'Aantal'
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