<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
/* old not working anymore
$arrOutput = mslib_fe::xml2array($str,0);
$i=0;
$s=0;
$rows=array();
$offset=$arrOutput['m4n']['data']['record'];
foreach ($offset as $item)
{
	foreach ($item['column'] as $col)
	{
		$rows[$i][$s] = $col;
		$s++;
	}
	$i++;
	$s=0;
}
*/
// converts the source file to a PHP array
$arrOutput=mslib_fe::xml2array($str, 0);
$i=0;
$s=0;
$rows=array();
$offset=$arrOutput['m4n']['data']['record'];
$table_cols=array();
foreach($offset as $item) {
	foreach($item as $key=>$col) {
		if($i == 0) {
			$table_cols[$s]=$key;
		}
		$rows[$i][$s]=$col;
		$s++;
	}
	$i++;
	$s=0;
}

?>