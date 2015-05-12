<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->get['Search'] and ($this->get['negative_keywords_only']!=$this->cookie['negative_keywords_only'])) {
	$this->cookie['negative_keywords_only']=$this->get['negative_keywords_only'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
$content.='
<form method="get" action="index.php" id="search_log_form" class="float_right">
<input name="id" type="hidden" value="'.$this->shop_pid.'" />
<input name="Search" type="hidden" value="1" />
<input name="type" type="hidden" value="2003" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="'.$this->ms['page'].'" />
<input id="checkbox_negative_keywords_only" name="negative_keywords_only" type="checkbox" value="1" '.($this->cookie['negative_keywords_only'] ? 'checked' : '').' /> '.$this->pi_getLL('display_negative_keywords_only').'
</form>
';
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript" language="JavaScript">
	jQuery(document).ready(function($) {
		$("#checkbox_negative_keywords_only").click(function(e) {
			$("#search_log_form").submit();
		 });

		$(".is_not_checkout").css("opacity", "0.5");
	});
</script>
';
$dates=array();
$content.='<h2>'.htmlspecialchars($this->pi_getLL('month')).'</h2>';
for ($i=1; $i<13; $i++) {
	$time=strtotime(date("Y-".$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
}
$content.='<table width="100%" cellspacing="0" cellpadding="0" border="0" class="msZebraTable" id="product_import_table">';
$content.='<tr class="odd">';
foreach ($dates as $key=>$value) {
	$content.='<td align="center">'.ucfirst($key).'</td>';
}
$content.='<tr class="even">';
$total=0;
$keywords_data=array();
foreach ($dates as $key=>$value) {
	$total_price=0;
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	$where=array();
	if ($this->cookie['negative_keywords_only']) {
		$where[]='(s.negative_results=1)';
	} else {
		$where[]='(s.negative_results=0 or s.negative_results=1)';
	}
	$content.='<td align="left" valign="top">';
	$content.='<table width="100%" cellspacing="0" cellpadding="0" border="0" class="msZebraTable" id="product_import_table">';
	$str="SELECT s.keyword, count(s.keyword) as total, s.negative_results FROM tx_multishop_products_search_log s WHERE (".implode(" AND ", $where).") and (s.crdate BETWEEN ".$start_time." and ".$end_time.") group by s.keyword order by total desc limit 10";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$search_amount=0;
	while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$search_amount+=$row['total'];
		$content.='<tr class="'.$tr_type.'">
		<td class="text_right" nowrap width="40">'.$row['total'].'</td>
		<td width="10">';
		if ($row['negative_results']) {
			$content.='<span class="negative_icon">x</span>';
		} else {
			$content.='<span class="positive_icon">+</span>';
		}
		$content.='</td>
		<td><a href="'.mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=products_search&skeyword='.urlencode($row['keyword'])).'" target="_blank">'.htmlspecialchars($row['keyword']).'</a></td>
		</tr>';
	}
	if ($search_amount>0) {
		$content.='<tr class="'.$tr_type.'">
		<td class="text_right" nowrap width="40">'.$search_amount.'</td>
		<td width="10" colspan="2">'.$this->pi_getLL('total').'</td>';
	}
	$content.='</table>';
	$content.='</td>';
}
$tr_type=false;
$content.='</tr>';
if (!$tr_type or $tr_type=='even') {
	$tr_type='odd';
} else {
	$tr_type='even';
}
$content.='
</table>';
// LAST MONTHS EOF
$tr_type='even';
$dates=array();
$content.='<h2>'.htmlspecialchars($this->pi_getLL('day')).'</h2>';
for ($i=0; $i<31; $i++) {
	$time=strtotime("-".$i." day");
	$dates[strftime("%x", $time)]=$time;
}
$content.='<table width="100%" cellpadding="0" cellspacing="0" border="0" class="msZebraTable" id="product_import_table">
<tr>
	<th width="100" align="right">'.htmlspecialchars($this->pi_getLL('day')).'</th>
	<th>'.htmlspecialchars($this->pi_getLL('keyword')).'</th>
</tr>
';
foreach ($dates as $key=>$value) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$content.='<tr class="'.$tr_type.'">';
	$content.='<td align="right" valign="top">'.$key.'</td>';
	$total_price=0;
	$system_date=date("Y-m-d", $value);
	$start_time=strtotime($system_date." 00:00:00");
	$end_time=strtotime($system_date." 23:59:59");
	$where=array();
	if ($this->cookie['negative_keywords_only']) {
		$where[]='(s.negative_results=1)';
	} else {
		$where[]='(s.negative_results=0 or s.negative_results=1)';
	}
	$str="SELECT s.keyword, count(s.keyword) as total, s.negative_results FROM  tx_multishop_products_search_log s WHERE (".implode(" AND ", $where).") and (s.crdate BETWEEN ".$start_time." and ".$end_time.") group by s.keyword order by total desc limit 5";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$key_data=array();
	while ($rows=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
		$key_data[]=$rows;
	}
	/*
		$content.='<td align="right">';
		$content.='<table width="100%" cellspacing="0" cellpadding="0" border="0" class="msZebraTable" id="product_import_table">';
		foreach ($key_data as $idx => $row) {
			$content.='<tr><td class="text_right" nowrap width="50">'.$row['total'].'</td></tr>';
		}
		$content.= '</table>';
		$content.= '</td>';
	*/
	$content.='<td>';
	$content.='<table width="100%" cellspacing="0" cellpadding="0" border="0" id="product_import_table">';
	foreach ($key_data as $idx=>$row) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$content.='<tr class="'.$tr_type.'">
		<td class="text_right" nowrap width="40">'.$row['total'].'</td>
		<td width="10">';
		if ($row['negative_results']) {
			$content.='<span class="negative_icon">x</span>';
		} else {
			$content.='<span class="positive_icon">+</span>';
		}
		$content.='</td>
		<td><a href="'.mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=products_search&skeyword='.$row['keyword']).'" target="_blank">'.$row['keyword'].'</a></td></tr>';
	}
	$content.='</table>
	</td>';
	$content.='</tr>';
}
$content.='</table>';
// LAST MONTHS EOF
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>