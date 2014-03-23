<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// clearing cooluri
$content.='<h2>Clearing CoolURI cache</h2>';
$content.='<ul>';
$str="TRUNCATE link_cache";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$content.='<li>link_cache table truncated</li>';
$str="TRUNCATE link_oldlinks";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$content.='<li>link_oldlinks table truncated</li>';
$content.='</ul>';
// clearing cooluri eof
// Regenerate link of the shop so Cooluri works again
$link=mslib_fe::typolink('', '');
// if frontend caching is enabled also clear those cache files eof
require('admin_system_clear_multishop_cache.php');
?>