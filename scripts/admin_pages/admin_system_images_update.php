<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<div class="main-heading"><h1>Updating Images</h1></div>';
// we go to reconnect to the DB, because sometimes when scripts takes too long, the database connection is lost.
// first the products
$files=mslib_befe::listdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original']);
sort($files, SORT_LOCALE_STRING);
$tel=0;
$GLOBALS['TYPO3_DB']->connectDB();
$updated=0;
if (count($files)>0) {
	foreach ($files as $f) {
		$path_parts=pathinfo($f);
		$filename=$path_parts['basename'];
		if ($filename) {
			// first check if its already subdirectory based
			$folder=mslib_befe::getImagePrefixFolder($filename);
			if (!file_exists($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.'/'.$filename)) {
				// we have to move this baby
				$content.=$filename.'<br />';
				foreach ($this->ms['image_paths']['products'] as $item) {
					$source=$this->DOCUMENT_ROOT.$item.'/'.$filename;
					$target=$this->DOCUMENT_ROOT.$item.'/'.$folder.'/'.$filename;
					if (!is_dir($this->DOCUMENT_ROOT.$item.'/'.$folder)) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($this->DOCUMENT_ROOT.$item.'/'.$folder);
					}
					exec("mv ".$source.' '.$target);
					$updated=1;
				}
			}
		}
	}
}
// next the categories
$files=mslib_befe::listdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original']);
sort($files, SORT_LOCALE_STRING);
$tel=0;
$GLOBALS['TYPO3_DB']->connectDB();
if (count($files)>0) {
	foreach ($files as $f) {
		$path_parts=pathinfo($f);
		$filename=$path_parts['basename'];
		if ($filename) {
			// first check if its already subdirectory based
			$folder=mslib_befe::getImagePrefixFolder($filename);
			if (!file_exists($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.'/'.$filename)) {
				// we have to move this baby
				$content.=$filename.'<br />';
				foreach ($this->ms['image_paths']['categories'] as $item) {
					$source=$this->DOCUMENT_ROOT.$item.'/'.$filename;
					$target=$this->DOCUMENT_ROOT.$item.'/'.$folder.'/'.$filename;
					if (!is_dir($this->DOCUMENT_ROOT.$item.'/'.$folder)) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($this->DOCUMENT_ROOT.$item.'/'.$folder);
					}
					exec("mv ".$source.' '.$target);
					$updated=1;
				}
			}
		}
	}
}
if (!$updated) {
	$content.='No files need to be repaired.';
}
?>