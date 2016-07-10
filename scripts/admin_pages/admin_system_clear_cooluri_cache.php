<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
// Clear CoolURI Cache
$str = "TRUNCATE link_cache";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$str = "TRUNCATE link_oldlinks";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$content .= '<h2>Cleared CoolURI cache</h2>';
// Clear TYPO3 cache
$content .= '<h2>Cleared TYPO3 cache</h2>';
$tableString = 'cache_md5params cache_treelist cf_cache_hash cf_cache_hash_tags cf_cache_imagesizes cf_cache_imagesizes_tags cf_cache_pages cf_cache_pagesection cf_cache_pagesection_tags cf_cache_pages_tags cf_cache_rootline cf_cache_rootline_tags cf_extbase_datamapfactory_datamap cf_extbase_datamapfactory_datamap_tags cf_extbase_object cf_extbase_object_tags cf_extbase_reflection cf_extbase_reflection_tags cf_extbase_typo3dbbackend_queries cf_extbase_typo3dbbackend_queries_tags cf_extbase_typo3dbbackend_tablecolumns cf_extbase_typo3dbbackend_tablecolumns_tags cf_fluidcontent cf_fluidcontent_tags cf_flux cf_flux_tags cf_schemaker cf_schemaker_tags cf_vhs_main cf_vhs_main_tags cf_vhs_markdown cf_vhs_markdown_tags';
$tables = explode(' ', $tableString);
foreach ($tables as $table) {
    $str = 'TRUNCATE ' . $table . ';';
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
}
$command = 'rm -rf ' . $this->DOCUMENT_ROOT . 'typo3conf/temp_CACHED_*';
exec($command);
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('nc_staticfilecache', 0) && is_dir($this->DOCUMENT_ROOT . 'typo3conf/tx_ncstaticfilecache')) {
    $command = 'rm -rf ' . $this->DOCUMENT_ROOT . 'typo3conf/tx_ncstaticfilecache/*';
    exec($command);
}
//$command='chmod 2775 '.$this->DOCUMENT_ROOT.'typo3temp';
//exec($command);
$command = 'rm -f ' . $this->DOCUMENT_ROOT . 'typo3temp/*';
exec($command);
//$command='mkdir '.$this->DOCUMENT_ROOT.'typo3temp/Cache';
//exec($command);
//$command='mkdir '.$this->DOCUMENT_ROOT.'typo3temp/_processed_';
//exec($command);
$command = \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($this->DOCUMENT_ROOT . 'typo3temp/_processed_');
exec($command);
// Regenerate link of the shop so Cooluri works again
$link = mslib_fe::typolink('', '');
// if frontend caching is enabled also clear those cache files eof
require('admin_system_clear_multishop_cache.php');

$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_sitemap_generator').'">Click here to run the sitemap generator to repair all deeplinks</a>';
?>