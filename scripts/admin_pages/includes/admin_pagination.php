<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$total_pages=ceil(($pageset['total_rows']/$this->ms['MODULES']['PAGESET_LIMIT']));
$tmp='';
$tmp.='<div id="pagenav_container_list_wrapper">
<ul class="pagination" id="admin_pagination_ul">';
if ($p>0) {
	$tmp.='<li class="pagenav_first"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', ''.mslib_fe::tep_get_all_get_params(array(
				'p',
				'Submit',
				'tx_multishop_pi1[action]',
				'clearcache'
			))).'"><i class="fa fa-angle-double-left"></i></a></li>';
} else {
	$tmp.='<li class="pagenav_first disabled"><span><i class="fa fa-angle-double-left"></i></span></li>';
}
if ($p>0) {
	if (($p-1)>0) {
		$tmp.='<li class="pagenav_previous"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
					'p',
					'Submit',
					'tx_multishop_pi1[action]',
					'clearcache'
				))).'"><i class="fa fa-angle-left"></i></a></li>';
	} else {
		$tmp.='<li class="pagenav_previous"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
					'p',
					'Submit',
					'tx_multishop_pi1[action]',
					'clearcache'
				))).'"><i class="fa fa-angle-left"></i></a></li>';
	}
} else {
	$tmp.='<li class="pagenav_previous disabled"><span><i class="fa fa-angle-left"></i></span></li>';
}
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
$tmp.='';
for ($x=$start_page_number; $x<=$end_page_number; $x++) {
	if (($p+1)==$x) {
		$tmp.='<li class="pagenav_number active"><span>'.$x.'</span></a></li>';
	} else {
		$tmp.='<li class="pagenav_number"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'p='.($x-1).'&'.mslib_fe::tep_get_all_get_params(array(
					'p',
					'Submit',
					'page',
					'tx_multishop_pi1[action]',
					'clearcache'
				))).'">'.$x.'</a></li>';
	}
}
if ((($p+1)*$this->ms['MODULES']['PAGESET_LIMIT'])<$pageset['total_rows']) {
	$tmp.='<li class="pagenav_next"><a class="pagination_button msBackendButton continueState arrowRight arrowPosLeft" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'p='.($p+1).'&'.mslib_fe::tep_get_all_get_params(array(
				'p',
				'Submit',
				'tx_multishop_pi1[action]',
				'clearcache'
			))).'"><span><i class="fa fa-angle-right"></i></span></a></li>';
} else {
	$tmp.='<li class="pagenav_next disabled"><span><i class="fa fa-angle-right"></i></span></li>';
}
if ((($p+1)*$this->ms['MODULES']['PAGESET_LIMIT'])<$pageset['total_rows']) {
	$lastpage=floor(($pageset['total_rows']/$this->ms['MODULES']['PAGESET_LIMIT']))-1;
	$tmp.='<li class="pagenav_last"><a class="pagination_button msBackendButton continueState arrowRight arrowPosLeft" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'p='.$lastpage.'&'.mslib_fe::tep_get_all_get_params(array(
											'p',
				'Submit',
				'tx_multishop_pi1[action]',
				'clearcache'
			))).'"><span><i class="fa fa-angle-double-right"></i></span></a></li>';
} else {
	$tmp.='<li class="pagenav_last disabled"><span><i class="fa fa-angle-double-right"></i></span></li>';
}
$tmp.='</ul>
</div>
';
?>