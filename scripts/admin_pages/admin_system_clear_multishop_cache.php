<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
/*
// clearing typo3 temp files
$string='					
cache_extensions
 cache_hash
 cache_imagesizes
 cache_md5params
 cache_pages
 cache_pagesection
 cache_treelist
 cache_typo3temp_log
 cachingframework_cache_hash
 cachingframework_cache_hash_tags
 cachingframework_cache_pages
 cachingframework_cache_pagesection
 cachingframework_cache_pagesection_tags
 cachingframework_cache_pages_tags
';
$tables = explode("\n",$string);
$content.='<h2>Clearing TYPO3 table cache</h2>';
$content.='<ul>';
foreach ($tables as $table)
{
	$table=trim($table);
	if ($table)
	{
		$str="TRUNCATE ".$table;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) $content.='<li>'.$table.' table truncated</li>';
	}
}
$content.='</ul>';
$content.='<h2>Clearing TYPO3 file cache</h2>';
$content.='<ul>';
if (($handle = opendir($this->DOCUMENT_ROOT.'typo3conf')) != false) {
	 while (false !== ($file = readdir($handle))) {
		  if ($file != "." && $file != "..") {
			  if (preg_match("/^temp_CACHED_/",$file))
			  {
				  if (unlink($this->DOCUMENT_ROOT.'typo3conf/'.$file))
				  {
					  $content.= '<li>'.$file.' file removed</li>';
				  }
				  else
				  {
					  $content.='<li>'.'<strong>'. $file.' file can\'t be removed</strong></li>';					  
				  }
			  }
		  }
	 }
	 closedir($handle);
}
$content.='</ul>';
// clearing typo3 temp files eof	
*/
// if frontend caching is enabled also clear those cache files
if($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END'] or $this->conf['cacheConfiguration']) {
	if($this->DOCUMENT_ROOT and !strstr($this->DOCUMENT_ROOT, '..')) {
		$command="rm -rf ".$this->DOCUMENT_ROOT."uploads/tx_multishop/tmp/cache/*";
		exec($command);
		$content.='<br /><p><strong>Multishop cache has been cleared.</strong></p>';
	} else {
		$content.='<br /><p><strong>Cache not cleared. Something is wrong with your configuration (DOCUMENT_ROOT is not set correctly).</strong></p>';
	}
}
?>