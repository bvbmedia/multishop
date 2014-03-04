<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');
$str="SELECT s.keyword, count(s.keyword) as total, s.negative_results FROM tx_multishop_products_search_log s where s.keyword <> '' group by s.keyword order by total desc limit 10";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);

$data=array();
$data[]=array('Zoekwoord','Aantal');
while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	$data[$row['keyword']]=array($row['keyword'],(int) $row['total']);
}
if (count($data)==1) {
	$libaryWidgets['searchKeywordsToplist']['content']='<p>Nog geen data beschikbaar.</p>';
} else {
//	$libaryWidgets['searchKeywordsToplist']['content']='<p>Websites waarvandaan bestellingen tot stand zijn gekomen.</p>';
	
	$counter=0;
	$libaryWidgets['searchKeywordsToplist']['content'].='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" >';
	foreach ($data as $host => $item) {
		$counter++;
		$libaryWidgets['searchKeywordsToplist']['content'].='<tr>';
		if ($counter==1) {
			$libaryWidgets['searchKeywordsToplist']['content'].='
				<th>'.$item[0].'</th>
				<th>'.$item[1].'</th>		
			';
		} else {		
			$libaryWidgets['searchKeywordsToplist']['content'].='
				<td>'.$host.'</td>
				<td>'.number_format($item[1],0,3,'.').'</td>		
			';
		}
		$libaryWidgets['searchKeywordsToplist']['content'].='</tr>';
	}
	$libaryWidgets['searchKeywordsToplist']['content'].='</table>';
}
?>