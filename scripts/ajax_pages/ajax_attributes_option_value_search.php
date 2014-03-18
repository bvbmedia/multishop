<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if ($this->ADMIN_USER) {
	header("Content-Type:application/json; charset=UTF-8");
	if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) $this->ms['MODULES']['CACHE_FRONT_END']=0;
	if ($this->ms['MODULES']['CACHE_FRONT_END'])
	{
		$options = array(
			'caching' => true,
			'cacheDir' => $this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime' => $this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']
		);
		$Cache_Lite = new Cache_Lite($options);
		$string=md5('ajax_attributes_option_value_search_'.$this->shop_pid.'_'.$_REQUEST['q'].'_'.$this->get['page']);
	}
	if ($_REQUEST['q'])
	{
		$this->get['q'] = $_REQUEST['q'];
		$this->get['q'] = trim($this->get['q']);
		$this->get['q'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['q'], $GLOBALS['TSFE']->metaCharset);
		$this->get['q'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['q'],TRUE);
		$this->get['q'] = mslib_fe::RemoveXSS($this->get['q']);
	}
	if ($_REQUEST['q'] and strlen($_REQUEST['q']) < 1) exit();
	
	$this->get['ftype'] = $_REQUEST['ftype'];
	$this->get['optid'] = $_REQUEST['optid'];
	
	/**
	 * Perform a simple text replace
	 * This should be used when the string does not contain HTML
	 * (off by default)
	 */
	define('STR_HIGHLIGHT_SIMPLE', 1);
	
	/**
	 * Only match whole words in the string
	 * (off by default)
	 */
	define('STR_HIGHLIGHT_WHOLEWD', 2);
	
	/**
	 * Case sensitive matching
	 * (on by default)
	 */
	define('STR_HIGHLIGHT_CASESENS', 0);
	
	/**
	 * Overwrite links if matched
	 * This should be used when the replacement string is a link
	 * (off by default)
	 */
	define('STR_HIGHLIGHT_STRIPLINKS', 8);
	
	/**
	 * Highlight a string in text without corrupting HTML tags
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     3.1.0
	 * @param       string          $text           Haystack - The text to search
	 * @param       array|string    $needle         Needle - The string to highlight
	 * @param       bool            $options        Bitwise set of options
	 * @param       array           $highlight      Replacement string
	 * @return      Text with needle highlighted
	 */
	function str_highlight($text, $needle='', $options = null, $highlight = null)
	{
		if (!$needle) return $text;
		// Default highlighting
		if ($highlight === null) {
			$highlight = '<strong class="highlight">\1</strong>';
		}
	
		// Select pattern to use
		if ($options & STR_HIGHLIGHT_SIMPLE) {
			$pattern = '#(%s)#';
		} else {
			$pattern = '#(?!<.*?)(%s)(?![^<>]*?>)#';
			$sl_pattern = '#<a\s(?:.*?)>(%s)</a>#';
		}
	
		// Case sensitivity
		/*
			if ($options ^ STR_HIGHLIGHT_CASESENS) {
	
		$pattern .= 'i';
		$sl_pattern .= 'i';
		}
		*/
		$pattern .= 'i';
		$sl_pattern .= 'i';
	
		$needle = (array) $needle;
		foreach ($needle as $needle_s) {
			$needle_s = preg_quote($needle_s);
	
			// Escape needle with optional whole word check
			if ($options & STR_HIGHLIGHT_WHOLEWD) {
				$needle_s = '\b' . $needle_s . '\b';
			}
	
			// Strip links
			if ($options & STR_HIGHLIGHT_STRIPLINKS) {
				$sl_regex = sprintf($sl_pattern, $needle_s);
				$text = preg_replace($sl_regex, '\1', $text);
			}
	
			$regex = sprintf($pattern, $needle_s);
			$text = preg_replace($regex, $highlight, $text);
		}
	
		return $text;
	}
	
	$p = !$this->get['page'] ? 0 : $this->get['page'];
	if (!is_numeric($p)) $p=0;
	
	$limit = 4;
	$offset = $p * $limit;
	
	// product search
	$filter		=array();
	$having		=array();
	$match		=array();
	$orderby	=array();
	$where		=array();
	$orderby	=array();
	$select		=array();
	if (strlen($this->get['q']) >0)
	{
		$array=explode(" ",$this->get['q']);
		$total=count($array);
		$oldsearch=1;
	
		if ($this->get['ftype'] == 'option') {
			$filter[]="(op.products_options_name like '".addslashes($this->get['q'])."%')";
		} else {
			$filter[]="(opv.products_options_values_name like '".addslashes($this->get['q'])."%')";
			$filter[]="op2v.products_options_id = " . $this->get['optid'];
		}
	}
	
	if ($this->get['ftype'] == 'option') {
		$pageset=mslib_fe::getOptionsPageSet($filter,$offset,$limit,$orderby,$having,$select,$where);
	} else {
		$filter[]="op2v.products_options_id = " . $this->get['optid'];
		$pageset=mslib_fe::getOptionsValuesPageSet($filter,$offset,$limit,$orderby,$having,$select,$where);
	}
	
	$options=$pageset['options'];
	
	if ($pageset['total_rows'] > 0)
	{
			
		$prod = array();
		foreach ($options as $option)
		{
			if (!$tr_type or $tr_type=='even') 	$tr_type='odd';
			else								$tr_type='even';
				
				
			if ($this->get['ftype'] == 'option') {
				$prod['Name'] = substr($option['products_options_name'],0,50);
				$prod['Title'] = '<div class="ajax_options_name">'. substr($option['products_options_name'],0,50) .'</div>';
				$prod['Title']  = $prod['Title'];
				$prod['Link']  = '';
				$prod['skeyword'] = $this->get['q'];
				$prod['Page'] = $pages;
				$prod['products_options_id'] = $option['products_options_id'];
				$data[] = $prod;
					
			} else {
				$prod['Name'] = substr($option['products_options_values_name'],0,50);
				$prod['Title'] = '<div class="ajax_options_name">'. substr($option['products_options_values_name'],0,50) .'</div>';
				$prod['Title']  = $prod['Title'];
				$prod['Link']  = '';
				$prod['skeyword'] = $this->get['q'];
				$prod['Page'] = $pages;
				$prod['products_options_id'] = 0;
				$data[] = $prod;
			}
			
			$prod['Product'] = true;
				
		}
		 
		$totpage = ceil($pageset['total_rows'] / $limit);
		//echo $totpage;
		 
		$pages = !$this->get['page'] ? 0 : $this->get['page'];
	
		if ($pages > $totpage) {
			$this->get['page'] = $totpage;
		}
		if ($pages < $totpage){
			$pages = $pages + 1;
		} else {
			$pages = 0;
		}
		 
		if (isset($p)) {
			if ($totpage > 1) {
				//echo $totpage;
				if ($pages != $totpage){
					$prod = array();
					$prod['Name'] = $this->pi_getLL('more_results');
					
					if ($this->get['optid'] > 0) {
						$prod['Link'] = mslib_fe::typolink($this->shop_pid,'&type=2002&tx_multishop_pi1[page_section]=ajax_attributes_option_value_search&optid='.$this->get['optid'].'&page='.$pages.'&ftype=values&q='.urlencode($this->get['q']));
					} else {
						$prod['Link'] = mslib_fe::typolink($this->shop_pid,'&type=2002&tx_multishop_pi1[page_section]=ajax_attributes_option_value_search&page='.$pages.'&q='.urlencode($this->get['q']));
					}
					
					$prod['Title'] = '<span id="more-results">'.htmlspecialchars($this->pi_getLL('more_results')).'</span>';
					$prod['skeyword'] = $this->get['q'];
					$prod['Page'] = $pages;
					$prod['Product'] = false;
					$data[] = $prod;
				}
			}
		}
		$content = array("options"=>$data);
	}
	else
	{
		$content = array("options"=>array());
	}
	$content=json_encode($content, ENT_NOQUOTES);
	if ($this->ms['MODULES']['CACHE_FRONT_END'])	$Cache_Lite->save($content);
	
	echo $content;
	exit;
}
?>