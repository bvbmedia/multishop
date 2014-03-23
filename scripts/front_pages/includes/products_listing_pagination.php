<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tmp='';
$tmp.='<table id="pagenav_container">
<tr>
 <td class="pagenav_first"><table><tr><td>';
if($p > 0) {
	$tmp.=mslib_fe::flexibutton('<a class="ajax_link pagination_button" href="'.mslib_fe::typolink('', ''.mslib_fe::tep_get_all_get_params(array(
			'p',
			'Submit',
			'page',
			'mini_foto',
			'clearcache'))).'">'.$this->pi_getLL('first').'</a>');
} else {
	$tmp.='&nbsp;';
}
$tmp.='</td></tr></table></td><td class="pagenav_previous"><table><tr><td>';
if($p > 0) {
	if(($p-1) > 0) {
		$tmp.=mslib_fe::flexibutton('<a class="ajax_link pagination_button" href="'.mslib_fe::typolink('', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
				'p',
				'Submit',
				'page',
				'mini_foto',
				'clearcache'))).'">'.$this->pi_getLL('previous').'</a>', 'pagenav_previous');
	} else {
		$tmp.=mslib_fe::flexibutton('<a class="ajax_link pagination_button" href="'.mslib_fe::typolink('', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
				'p',
				'Submit',
				'page',
				'mini_foto',
				'clearcache'))).'">'.$this->pi_getLL('previous').'</a>', 'pagenav_previous');
	}
} else {
	$tmp.='&nbsp;';
}
$tmp.='</td></tr></table></td><td class="pagenav_next"><table><tr><td>';
if((($p+1)*$limit_per_page) < $pageset['total_rows']) {
	$tmp.=mslib_fe::flexibutton('<a class="ajax_link pagination_button" href="'.mslib_fe::typolink('', 'p='.($p+1).'&'.mslib_fe::tep_get_all_get_params(array(
			'p',
			'Submit',
			'page',
			'mini_foto',
			'clearcache'))).'">'.$this->pi_getLL('next').'</a>', 'pagenav_next');
} else {
	$tmp.='&nbsp;';
}
$tmp.='</td></tr></table></td><td class="pagenav_last"><table><tr><td>';
if((($p+1)*$limit_per_page) < $pageset['total_rows']) {
	$times=($pageset['total_rows']/$limit_per_page);
	$lastpage=floor($times);
	if($lastpage == $times) {
		$lastpage--;
	}
	$tmp.=mslib_fe::flexibutton('<a class="ajax_link pagination_button" href="'.mslib_fe::typolink('', 'p='.$lastpage.'&'.mslib_fe::tep_get_all_get_params(array(
			'p',
			'Submit',
			'page',
			'mini_foto',
			'clearcache'))).'">'.$this->pi_getLL('last').'</a>', 'pagenav_last');
} else {
	$tmp.='&nbsp;';
}
$tmp.='</td></tr></table></td></tr>
</table>';
$content.=$tmp;
?>