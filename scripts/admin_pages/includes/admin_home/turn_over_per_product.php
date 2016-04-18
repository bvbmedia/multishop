<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$compiledWidget['key']='turnoverPerProduct_B2U';
$compiledWidget['defaultCol']=1;
$compiledWidget['title']=$this->pi_getLL('sales_volume_by_month_per_product');
$categoriesTotal=array();
$allTotal=0;
$dates=array();
for ($i=3; $i>=0; $i--) {
	$time=strtotime(date('Y-m-01').' -'.$i.' MONTH');
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
	if ($i==3) {
		$start_time=$time;
	}
}
$end_time=$time;
$dates = array_reverse($dates);

$where=array();
$where[]='(o.deleted=0)';
$where[]='(i.crdate BETWEEN '.$start_time.' and '.$end_time.')';
$str=$GLOBALS['TYPO3_DB']->SELECTquery('i.crdate', // SELECT ...
	'tx_multishop_orders o, tx_multishop_invoices i, tx_multishop_orders_products op', // FROM ...
	'('.implode(" AND ", $where).') and o.orders_id=op.orders_id and (o.orders_id = i.orders_id)', // WHERE...
	'', // GROUP BY...
	'o.orders_id asc', // ORDER BY...
	'1' // LIMIT ...
);
$qry_year=$GLOBALS['TYPO3_DB']->sql_query($str);
$row_year=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);
if ($row_year['crdate']>0) {
	$oldest_year=date("Y", $row_year['crdate']);
} else {
	$oldest_year=date("Y");
}
$current_year=date("Y");
$categories=array();
$where=array();
$where[]='(o.deleted=0)';
$where[]='(i.crdate BETWEEN '.$start_time.' and '.$end_time.')';
$str=$GLOBALS['TYPO3_DB']->SELECTquery('op.categories_id', // SELECT ...
		'tx_multishop_orders o, tx_multishop_invoices i, tx_multishop_orders_products op', // FROM ...
		'('.implode(" AND ", $where).') and o.orders_id=op.orders_id and (o.orders_id = i.orders_id)', // WHERE...
		'op.categories_id', // GROUP BY...
		'o.orders_id asc', // ORDER BY...
		'' // LIMIT ...
);

$qry_categories=$GLOBALS['TYPO3_DB']->sql_query($str);
while ($row_categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_categories)) {
	$catname=mslib_fe::getCategoryName($row_categories['categories_id']);
	if (!$catname) {
		$catname='Onbekende groep (ID: '.$row_categories['categories_id'].')';
	}
	$categories[$row_categories['categories_id']]=$catname;
}

$compiledWidget['content'].='<div class="table-responsive"><table width="100%" class="table table-striped table-bordered" cellspacing="0" cellpadding="0" border="0" ><thead>';
$compiledWidget['content'].='<tr class="odd">';
$compiledWidget['content'].='<th align="left">'.$this->pi_getLL('month').'</th>';
foreach ($categories as $cid=>$categories_name) {
	$compiledWidget['content'].='<th align="right" nowrap>'.$categories_name.'</th>';
}
$compiledWidget['content'].='<th align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</th>';
$compiledWidget['content'].='</tr></thead><tbody>';
$row_ctr=0;
foreach ($dates as $key=>$value) {
	$total_amount=0;


	$compiledWidget['content'].='<tr class="'.$tr_type.'">';
	$compiledWidget['content'].='<td align="right" nowrap>'.ucfirst($key).'</td>';
	foreach ($categories as $cid => $category_name) {
		$total_price=0;
		if (!$tr_type or $tr_type=='odd') {
			$tr_type='even';
		} else {
			$tr_type='odd';
		}
		$start_time=strtotime($value."-01 00:00:00");
		//$end_time=strtotime($value."-31 23:59:59");
		$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
		$data_query['where']=array();
		$data_query['where'][]='(o.paid=1 or o.paid=0)';
		$data_query['where'][]='(o.deleted=0)';
		$data_query['where'][]='(i.crdate BETWEEN '.$start_time.' and '.$end_time.')';

		$data_query['where'][]='(o.orders_id = i.orders_id)';
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('sum(op.final_price*op.qty) as total_final_price', // SELECT ...
				'tx_multishop_orders o, tx_multishop_invoices i, tx_multishop_orders_products op', // FROM ...
				'('.implode(" AND ", $data_query['where']).') and op.categories_id=' . $cid . ' and o.orders_id=op.orders_id', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
		);

		// start time and edn time for search params
		$start_time_date=date('d-m-Y 00:00:00', $start_time);
		$end_time_date=date('d-m-Y 23:59:59', $end_time);

		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$total_price+=$row['total_final_price'];
			$compiledWidget['content'].='<td align="right"><a href="index.php?id='.$this->shop_pid.'&skeyword=&Search=Zoeken&limit=15&tx_multishop_pi1%5Bpage_section%5D=admin_invoices&type=2003&invoice_date_from='.$start_time_date.'&invoice_date_till='.$end_time_date.'&ordered_category='.$cid.'&type_search=all">'.mslib_fe::amount2Cents($row['total_final_price'], 0).'</a></td>';
		}
		//$compiledWidget['content'].='<td align="right">'.mslib_fe::amount2Cents($total_price, 0).'</td>';
		$total_amount=$total_amount+$total_price;
		$categoriesTotal[$cid]=($categoriesTotal[$cid]+$total_price);
		$allTotal=($allTotal+$total_price);
		$month=12;
		$dayOfTheYear=365;
		$currentDay=31;
		$currentYear=0;
		$currentMonth=0;
	}
	//$compiledWidget['content'].='<td align="right" nowrap>'.mslib_fe::amount2Cents($total_amount, 0).'</td>';
	$compiledWidget['content'].='<td align="right" nowrap><a href="index.php?id='.$this->shop_pid.'&skeyword=&Search=Zoeken&limit=15&tx_multishop_pi1%5Bpage_section%5D=admin_invoices&type=2003&invoice_date_from='.$start_time_date.'&invoice_date_till='.$end_time_date.'&type_search=all">'.mslib_fe::amount2Cents($total_amount, 0).'</a></td>';
	$compiledWidget['content'].='</tr>';
}
$compiledWidget['content'].='</tbody><tfoot><tr><th>Total</th>';
foreach ($categoriesTotal as $key => $val) {
	$compiledWidget['content'].='<th align="right" nowrap>'.mslib_fe::amount2Cents($val, 0).'</th>';
}
$compiledWidget['content'].='<th align="right" nowrap>'.mslib_fe::amount2Cents($allTotal, 0).'</th>';
$compiledWidget['content'].='</tr></tfoot>';
if (!$tr_type or $tr_type=='even') {
	$tr_type='odd';
} else {
	$tr_type='even';
}
$compiledWidget['content'].='
</table></div>';
?>