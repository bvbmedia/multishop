<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if(!$this->ms['image_paths']['products']['original']) {
	die('Protection. $ms image_paths products original variable is empty');
}
set_time_limit(86400);
ignore_user_abort(true);
$content.='<div class="main-heading"><h1>Orphan files checker</h1></div>';
switch($this->get['action']) {
	case 'import_product_images':
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orphan_files', '');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$content.='<strong>Adding files</strong><br />';
		$files=mslib_befe::listdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original']);
		sort($files, SORT_LOCALE_STRING);
		$tel=0;
		// we go to reconnect to the DB, because sometimes when scripts takes too long, the database connection is lost.
		$GLOBALS['TYPO3_DB']->connectDB();
		foreach($files as $f) {
			$tel++;
			$path_parts=pathinfo($f);
			$insertArray=array(
				'type'=>'products_image',
				'orphan'=>0,
				'path'=>$path_parts['dirname'],
				'file'=>$path_parts['basename'],
				'crdate'=>time());
			$str2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orphan_files', $insertArray);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		}
		if($tel) {
			$content.='We have added <strong>'.$tel.'</strong> files.';
		}
		break;
	case 'scan_for_orphan_files':
		$stats=array();
		$stats['orphan']=0;
		$stats['checked']=0;
		$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_multishop_orphan_files');
		foreach($datarows as $row) {
			if($row['type'] == 'products_image') {
				$array=array();
				$str2="SELECT * from tx_multishop_products where (products_image='".addslashes($row['file'])."' or products_image1='".addslashes($row['file'])."' or products_image2='".addslashes($row['file'])."' or products_image3='".addslashes($row['file'])."' or products_image4='".addslashes($row['file'])."')";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				if(!$GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
					$stats['orphan']++;
					$array['orphan']=1;
				} else {
					$array['orphan']=0;
				}
				$array['checked']=1;
				$stats['checked']++;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orphan_files', 'id=\''.$row['id'].'\'', $array);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		$content.='<strong>'.$stats['orphan'].'</strong> of <strong>'.$stats['checked'].'</strong> files were detected as orphan and could be deleted.';
		break;
	case 'delete_orphan_files':
		$stats=array();
		$stats['checked']=0;
		$stats['deleted']=0;
		$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_multishop_orphan_files', 'orphan=1 and checked=1', '', 'id asc');
		foreach($datarows as $datarow) {
			$stats['checked']++;
			$deleted=0;
			if($datarow['type'] == 'products_image' and $datarow['path'] and $datarow['file']) {
				foreach($this->ms['image_paths']['products'] as $key=>$path) {
					$path=str_replace('/original/', '/'.$key.'/', $datarow['path']);
					$path.='/'.$datarow['file'];
					if(unlink($path) or !file_exists($path)) {
						$deleted=1;
					}
				}
			}
			if($deleted) {
				$stats['deleted']++;
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orphan_files', 'id='.$datarow['id']);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		$content.='<strong>'.$stats['deleted'].'</strong> of <strong>'.$stats['checked'].'</strong> files were succesfully deleted.';
		break;
}
// load the found orphan records
$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_multishop_orphan_files', '', '', 'orphan desc', '100');
if(count($datarows) > 0) {
	$tr_type='even';
	$content.='<table class="msZebraTable msadmin_border" id="admin_modules_listing">';
	$content.='<tr><th><span title="Checked" alt="Checked">C</span></th><th><span title="Orphan" alt="Orphan">O</span></th><th><span title="Type" alt="Type">T</span></th><th>Path</th><th>File</th><th>Date</th><th>Action</th></tr>';
	foreach($datarows as $datarow) {
		if($tr_type == 'even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$content.='<tr class="'.$tr_type.'">';
		$content.='<td>'.($datarow['checked'] ? '1' : '0').'</td>';
		$content.='<td>'.($datarow['orphan'] ? '1' : '0').'</td>';
		$content.='<td>'.$datarow['type'].'</td>';
		$content.='<td>
			<a href="'.str_replace($this->DOCUMENT_ROOT, '', $datarow['path']).'/'.$datarow['file'].'" target="_blank" title="'.htmlspecialchars(str_replace($this->DOCUMENT_ROOT, '', $datarow['path'])).'" alt="'.htmlspecialchars(str_replace($this->DOCUMENT_ROOT, '', $datarow['path'])).'">'.substr(str_replace($this->DOCUMENT_ROOT, '', $datarow['path']), 0, 40).'..
			</a>
		</td>';
		$content.='<td>'.$datarow['file'].'</td>';
		$content.='<td>'.strftime("%x %X", $datarow['crdate']).'</td>';
		$content.='<td>delete</td>';
		$content.='</tr>';
	}
	$content.='</table>';
}
$content.='
<div class="hr"></div>
<ul>
	<li><a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=admin_system_orphan_files&action=import_product_images').'"><strong>Step 1: Store all product images inside a temporary table for further analyse</strong></a></li>
	<li><a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=admin_system_orphan_files&action=scan_for_orphan_files').'"><strong>Step 2: Start orphan file checker</strong></a></li>
	<li><a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=admin_system_orphan_files&action=delete_orphan_files').'" onClick="return CONFIRM(\'Are you sure you want to delete the orphan files?\')"><strong>Step 3: Delete found orphan files</strong></a></li>
</ul>
';
?>