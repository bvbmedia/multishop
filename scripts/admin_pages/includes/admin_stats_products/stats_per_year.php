<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$filter=array();
$filter[]='o.crdate BETWEEN '.strtotime(date('Y-01-01 00:00:00')).' and '.time();
$filter[]='o.orders_id=op.orders_id';
$str=$GLOBALS['TYPO3_DB']->SELECTquery('sum(op.qty) as total, op.products_name, op.products_id, op.categories_id', // SELECT ...
		'tx_multishop_orders o, tx_multishop_orders_products op', // FROM ...
		implode(' AND ',$filter), // WHERE...
		'op.products_name having total > 0', // GROUP BY...
		'total desc', // ORDER BY...
		'' // LIMIT ...
);

$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
	$content .= '
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="table table-striped table-bordered">
		<thead>
		<tr class="' . $tr_type . '">
			<th valign="top" class="text-right">Qty</td>
			<th valign="top">Product</td>
		</tr>
		</thead><tbody>
	';
	while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		if ($row['categories_id']) {
			// get all cats to generate multilevel fake url
			$level=0;
			$cats=mslib_fe::Crumbar($row['categories_id']);
			$cats=array_reverse($cats);
			$where='';
			if (count($cats)>0) {
				foreach ($cats as $cat) {
					$where.="categories_id[".$level."]=".$cat['id']."&";
					$level++;
				}
				$where=substr($where, 0, (strlen($where)-1));
			}
			// get all cats to generate multilevel fake url eof
			$productLink=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$row['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
		} else {
			$productLink='';
		}

		$content.='<tr>';
		$content.='<td class="text-right">'.number_format(round($row['total'],1),0,'','.').'</td>';
		$content.='<td><a href="'.$productLink.'" target="_blank">'.htmlspecialchars($row['products_name']).'</a></td>';
		$content.='</tr>';
	}
	$content.='</tbody></table>';
}
$content='<div class="panel panel-default"><div class="panel-body">'.mslib_fe::shadowBox($content).'</div></div>';
?>