<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// cms info page
//$page=mslib_fe::getCMScontent($this->get['tx_multishop_pi1']['cms_hash'],$GLOBALS['TSFE']->sys_language_uid,'c.hash');	
$query=$GLOBALS['TYPO3_DB']->SELECTquery('cd.content, cd.name, c.type', // SELECT ...
	'tx_multishop_cms c, tx_multishop_cms_description cd', // FROM ...
	'c.page_uid=\''.$this->shop_pid.'\' and c.id=cd.id and cd.language_id=\''.$GLOBALS['TSFE']->sys_language_uid.'\' and c.hash=\''.$this->get['tx_multishop_pi1']['cms_hash'].'\'', // WHERE...
	'', // GROUP BY...
	'', // ORDER BY...
	'' // LIMIT ...
);
$res=$GLOBALS['TYPO3_DB']->sql_query($query);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
	$page=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	//hook to let other plugins further manipulate the cms content
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/info.php']['infoCMSContent'])) {
		$params=array(
			'page'=>&$page,
			'cms_type'=>$page['type'],
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/info.php']['infoCMSContent'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	if ($page['name']) {
		$this->ms['title']=$page['name'];
	}
	if (!$this->conf['disableMetatags']) {
		$output_array['meta']['title']='<title>'.htmlspecialchars($this->ms['title']).$this->ms['MODULES']['PAGE_TITLE_DELIMETER'].$this->ms['MODULES']['STORE_NAME'].'</title>';
		if (is_array($output_array['meta']) and count($output_array['meta'])) {
			$GLOBALS['TSFE']->additionalHeaderData=array_merge($GLOBALS['TSFE']->additionalHeaderData, $output_array['meta']);
		}
	}
	$content.='<div class="main-heading"><h2>'.$page['name'].'</h2></div>
	<div class="content">'.$page['content'].'</div>';
}
?>