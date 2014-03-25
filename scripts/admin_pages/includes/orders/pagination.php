<?php
// orphan, we use 1 pagination for all now (admin_pagination.php
die();
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$total_pages=ceil(($pageset['total_rows']/$this->ms['MODULES']['ORDERS_LISTING_LIMIT']));
$tmp.='<div id="pagenav_container_list_wrapper">
<ul id="pagenav_container_list">
<li class="pagenav_first">';
if ($p>0) {
	$tmp.='<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003', ''.mslib_fe::tep_get_all_get_params(array(
				'p',
				'Submit',
				'tx_multishop_pi1[action]',
				'clearcache'
			))).'">'.$this->pi_getLL('first').'</a></div>';
} else {
	$tmp.='<span>&nbsp;</span>';
}
$tmp.='</li>';
$tmp.='<li class="pagenav_previous">';
if ($p>0) {
	if (($p-1)>0) {
		$tmp.='<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
					'p',
					'Submit',
					'tx_multishop_pi1[action]',
					'clearcache'
				))).'">'.$this->pi_getLL('previous').'</a></div>';
	} else {
		$tmp.='<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
					'p',
					'Submit',
					'tx_multishop_pi1[action]',
					'clearcache'
				))).'">'.$this->pi_getLL('previous').'</a></div>';
	}
} else {
	$tmp.='<span>&nbsp;</span>';
}
$tmp.='</li>';
if ($p==0 || $p<9) {
	$start_page_number=1;
	if ($total_pages<=10) {
		$end_page_number=$total_pages;
	} else {
		$end_page_number=10;
	}
} else {
	if ($p>=9) {
		$start_page_number=($p-5)+1;
		$end_page_number=($p+4)+1;
		if ($end_page_number>$total_pages) {
			$end_page_number=$total_pages;
		}
	}
}
$tmp.='<li class="pagenav_number">
<ul id="pagenav_number_wrapper">';
for ($x=$start_page_number; $x<=$end_page_number; $x++) {
	if (($p+1)==$x) {
		$tmp.='<li><div class="dyna_button"><span>'.$x.'</span></a></li>';
	} else {
		$tmp.='<li><div class="dyna_button"><a class="ajax_link pagination_button" href="'.mslib_fe::typolink(',2003', 'p='.($x-1).'&'.mslib_fe::tep_get_all_get_params(array(
					'p',
					'Submit',
					'page',
					'mini_foto',
					'clearcache'
				))).'">'.$x.'</a></div></li>';
	}
}
$tmp.='</ul>
</li>';
$tmp.='<li class="pagenav_next">';
if ((($p+1)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])<$pageset['total_rows']) {
	$tmp.='<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003', 'p='.($p+1).'&'.mslib_fe::tep_get_all_get_params(array(
				'p',
				'Submit',
				'tx_multishop_pi1[action]',
				'clearcache'
			))).'">'.$this->pi_getLL('next').'</a></div>';
} else {
	$tmp.='<span>&nbsp;</span>';
}
$tmp.='</li>';
$tmp.='<li class="pagenav_last">';
if ((($p+1)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])<$pageset['total_rows']) {
	$lastpage=floor(($pageset['total_rows']/$this->ms['MODULES']['ORDERS_LISTING_LIMIT']));
	if ($lastpage*$this->ms['MODULES']['ORDERS_LISTING_LIMIT']==$pageset['total_rows']) {
		$lastpage--;
	}
	$tmp.='<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003', 'p='.$lastpage.'&'.mslib_fe::tep_get_all_get_params(array(
				'p',
				'Submit',
				'tx_multishop_pi1[action]',
				'clearcache'
			))).'">'.$this->pi_getLL('last').'</a></div>';
} else {
	$tmp.='<span>&nbsp;</span>';
}
$tmp.='</li>';
$tmp.='</ul></div>';
?>