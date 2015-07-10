<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (!$this->ms['image_paths']['products']['original']) {
	die('Protection. $ms image_paths products original variable is empty');
}
set_time_limit(86400);
ignore_user_abort(true);
$content.='<div class="main-heading"><h1>Orphan files checker</h1></div>';
switch ($this->get['action']) {
	case 'import_product_images':
		$importTypes=array();
		$importTypes[]='import_product_images';
		$importTypes[]='import_categories_images';
		$importTypes[]='import_manufacturers_images';
		foreach ($importTypes as $importType) {
			switch ($importType) {
				case 'import_product_images':
					$objectType='products_image';
					$objectFolderName='products';
					$objectFolders=$this->ms['image_paths']['products'];
					break;
				case 'import_categories_images':
					$objectType='categories_image';
					$objectFolderName='categories';
					$objectFolders=$this->ms['image_paths']['categories'];
					break;
				case 'import_manufacturers_images':
					$objectType='manufacturers_image';
					$objectFolderName='manufacturers';
					$objectFolders=$this->ms['image_paths']['manufacturers'];
					break;
			}
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_orphan_files', 'type=\''.addslashes($objectType).'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$content.='<strong>Adding files for queue: '.$objectFolderName.'</strong><br />';
			$filesToInsert=array();
			foreach ($objectFolders as $key=>$path) {
				$content.='Scanning: '.$key.' folder.<br/>';
				$files=mslib_befe::listdir($this->DOCUMENT_ROOT.$path);
				sort($files, SORT_LOCALE_STRING);
				// we go to reconnect to the DB, because sometimes when scripts takes too long, the database connection is lost.
				$GLOBALS['TYPO3_DB']->connectDB();
				foreach ($files as $f) {
					$path_parts=pathinfo($f);
					$path=str_replace('/images/'.$objectFolderName.'/'.$key.'/', '/images/'.$objectFolderName.'/original/', $path_parts['dirname']);
					if (!$filesToInsert[$path.'/'.$path_parts['basename']]) {
						$filesToInsert[$path.'/'.$path_parts['basename']]=array(
							'path'=>$path,
							'file'=>$path_parts['basename']
						);
					}
				}
			}
			// we go to reconnect to the DB, because sometimes when scripts takes too long, the database connection is lost.
			$GLOBALS['TYPO3_DB']->connectDB();
			$tel=0;
			foreach ($filesToInsert as $file) {
				$tel++;
				$insertArray=array(
					'type'=>$objectType,
					'orphan'=>0,
					'path'=>$file['path'],
					'file'=>$file['file'],
					'crdate'=>time()
				);
				$str2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orphan_files', $insertArray);
				$res2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			}
			if ($tel) {
				$content.='We have added <strong>'.$tel.'</strong> files.';
			} else {
				$content.='No files found.';
			}
			$content.='<br/>';
		}
		break;
	case 'scan_for_orphan_files':
		$stats=array();
		$stats['orphan']=0;
		$stats['checked']=0;
		$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_multishop_orphan_files');
		foreach ($datarows as $row) {
			switch ($row['type']) {
				case 'products_image':
					$array=array();
					$filter=array();
					$tmpOrFilter=array();
					if (!$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']) {
						$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']=5;
					}
					for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
						$i=$x;
						if ($i==0) {
							$i='';
						}
						$tmpOrFilter[]='products_image'.$i.'=\''.addslashes($row['file']).'\'';
					}
					$filter[]='('.implode(' OR ', $tmpOrFilter).')';
					$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
						'tx_multishop_products', // FROM ...
						implode(' AND ', $filter), // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
						$stats['orphan']++;
						$array['orphan']=1;
					} else {
						$array['orphan']=0;
					}
					$array['checked']=1;
					$stats['checked']++;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orphan_files', 'id=\''.$row['id'].'\'', $array);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					break;
				case 'categories_image':
					$array=array();
					$filter=array();
					$tmpOrFilter=array();
					$tmpOrFilter[]='categories_image=\''.addslashes($row['file']).'\'';
					$filter[]='('.implode(' OR ', $tmpOrFilter).')';
					$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
						'tx_multishop_categories', // FROM ...
						implode(' AND ', $filter), // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
						$stats['orphan']++;
						$array['orphan']=1;
					} else {
						$array['orphan']=0;
					}
					$array['checked']=1;
					$stats['checked']++;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orphan_files', 'id=\''.$row['id'].'\'', $array);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					break;
				case 'manufacturers_image':
					$array=array();
					$filter=array();
					$tmpOrFilter=array();
					$tmpOrFilter[]='manufacturers_image=\''.addslashes($row['file']).'\'';
					$filter[]='('.implode(' OR ', $tmpOrFilter).')';
					$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
						'tx_multishop_manufacturers', // FROM ...
						implode(' AND ', $filter), // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
						$stats['orphan']++;
						$array['orphan']=1;
					} else {
						$array['orphan']=0;
					}
					$array['checked']=1;
					$stats['checked']++;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orphan_files', 'id=\''.$row['id'].'\'', $array);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					break;
			}
		}
		$content.='<strong>'.$stats['orphan'].'</strong> of <strong>'.$stats['checked'].'</strong> files were detected as orphan and could be deleted.';
		break;
	case 'delete_orphan_files':
		$stats=array();
		$stats['checked']=0;
		$stats['deleted']=0;
		$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_multishop_orphan_files', 'orphan=1 and checked=1', '', 'id asc');
		foreach ($datarows as $datarow) {
			$stats['checked']++;
			$deleted=0;
			switch ($datarow['type']) {
				case 'products_image':
					$objectFolderName='products';
					$objectFolders=$this->ms['image_paths']['products'];
					break;
				case 'categories_image':
					$objectFolderName='categories';
					$objectFolders=$this->ms['image_paths']['categories'];
					break;
				case 'manufacturers_image':
					$objectFolderName='manufacturers';
					$objectFolders=$this->ms['image_paths']['manufacturers'];
					break;
				default:
					$objectFolderName='';
					continue(2);
					break;
			}
			if ($objectFolderName && $datarow['path'] and $datarow['file'] && is_array($objectFolders)) {
				foreach ($objectFolders as $key=>$path) {
					$path=str_replace('/images/'.$objectFolderName.'/original/', '/images/'.$objectFolderName.'/'.$key.'/', $datarow['path']);
					$path.='/'.$datarow['file'];
					if (unlink($path) or !file_exists($path)) {
						$deleted=1;
					}
				}
			}
			if ($deleted) {
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
if (count($datarows)>0) {
	$tr_type='even';
	$content.='<table class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">';
	$content.='<tr><th><span title="Checked" alt="Checked">C</span></th><th><span title="Orphan" alt="Orphan">O</span></th><th><span title="Type" alt="Type">T</span></th><th>Path</th><th>File</th><th>Date</th><th>Action</th></tr>';
	foreach ($datarows as $datarow) {
		if ($tr_type=='even') {
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
	<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_orphan_files&action=import_product_images').'"><strong>Step 1: Store all product images inside a temporary table for further analyse</strong></a></li>
	<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_orphan_files&action=scan_for_orphan_files').'"><strong>Step 2: Start orphan file checker</strong></a></li>
	<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_orphan_files&action=delete_orphan_files').'" onClick="return CONFIRM(\'Are you sure you want to delete the orphan files?\')"><strong>Step 3: Delete found orphan files</strong></a></li>
</ul>
';
?>