<?php
switch ($_REQUEST['action']) {
	case 'migrate_shop_data':
		if ($GLOBALS['TYPO3_CONF_VARS']['DB']['database']) {
			$db=$GLOBALS['TYPO3_CONF_VARS']['DB']['database'];
			$tables=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('page_uid', 'tx_multishop_products', '', 'page_uid');
			$str="SHOW TABLES where tables_in_".$db." like 'tx_multishop_%'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$array=explode('_',$_REQUEST['source_target_string']);
			if (is_array($array) && count($array) == 2) {
				while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
					$str2="UPDATE ".$row['Tables_in_'.$db]." SET page_uid='".$array[0]."' where page_uid='".$array[1]."'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					echo $str2.'<br/>';
				}
			}
		}
		break;
}
$sourceShops=array();
$targetShops=array();
$shopPids=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('page_uid', 'tx_multishop_products', '', 'page_uid');
$multishop_content_objects=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', 'deleted=0 and hidden=0 and module = \'mscore\'', '');
if (is_array($multishop_content_objects) && count($multishop_content_objects)>0) {
	foreach ($multishop_content_objects as $content_object) {
		$pageinfo=t3lib_BEfunc::readPageAccess($content_object['uid'], '');
		if (is_numeric($pageinfo['uid'])) {
			$sourceShops[$pageinfo['uid']]='PID: '.$pageinfo['uid'].' ('.$pageinfo['_thePathFull'].')';
			$targetShops[$pageinfo['uid']]='PID: '.$pageinfo['uid'].' ('.$pageinfo['_thePathFull'].')';
		}
	}
}
if (count($shopPids)) {
	foreach ($shopPids as $page_uid) {
		if (!array_key_exists($page_uid, $sourceShops)) {
			$sourceShops[$page_uid]='PID: '.$page_uid.' (Unknown)';
		}
	}
}
if (count($sourceShops) && count($targetShops)) {
	$options='';
	foreach ($sourceShops as $sourcePageUid => $sourcePageTitle) {
		foreach ($targetShops as $targetPageUid => $targetPageTitle) {
			if ($sourcePageUid!=$targetPageUid) {
				$options.='<option value="'.$sourcePageUid.'_'.$targetPageUid.'">Move ['.$sourcePageTitle.'] to ['.$targetPageTitle.']</option>'."\n";
			}
		}
	}
	if ($options) {
		$typoLink=$t3lib_BEfuncAlias::getModuleUrl('web_txmultishopM1');
		$tmpcontent='';
		$tmpcontent.='<select name="source_target_string"><option value="">Choose</option>'."\n".$options."\n".'</select>'."\n";
		$content.='
		<fieldset><legend>Local Shop Migration Tool</legend>
		<form action="'.$typoLink.'" method="post" enctype="multipart/form-data">
		<input name="action" type="hidden" value="migrate_shop_data" />
		'.$tmpcontent.'
			<table>
				<tr>
					<td>
						<input name="Submit" type="submit" value="Migrate data" onClick="return CONFIRM(\'Are you sure you want to brutally move the data? Make sure you create a backup first, because this cannot be reverted!\')" />
					</td>
				</tr>
			</table>
		</form>
		</fieldset>		
		';
	}
}
$this->content.=$this->doc->section('Multishop Administration', $content, 0, 1);
?>