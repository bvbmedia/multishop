<?php
//print_r($this->get);
// if there are no Ultrasearch fields defined through the Multishop configuration system, lets check if the Ultrasearch fields are defined through FlexForms.

// setting coming from typoscript or from flexform
if ($this->conf['ultrasearch_fields']) {
	$this->ultrasearch_fields = $this->conf['ultrasearch_fields'];
} else {
	$this->ultrasearch_fields = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_fields', 's_search');
}
if (!$this->ms['MODULES']['ULTRASEARCH_FIELDS']) {
	if ($this->ultrasearch_fields) {
		$this->ms['MODULES']['ULTRASEARCH_FIELDS']=$this->ultrasearch_fields;
	}
} 
if (!$this->ms['MODULES']['ULTRASEARCH_FIELDS']) {
	$this->no_database_results=1;
} else {
	// setting coming from typoscript or from flexform
	if (is_numeric($this->conf['filterCategoriesFormByCategoriesIdGetParam'])) {
		$this->filterCategoriesFormByCategoriesIdGetParam = $this->conf['filterCategoriesFormByCategoriesIdGetParam'];
	} else {
		$this->filterCategoriesFormByCategoriesIdGetParam = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_filtered_by_current_category', 's_search');
	}
	// setting coming from typoscript or from flexform
	if (is_numeric($this->conf['ultrasearch_exclude_negative_filter_values'])) {
		$this->ultrasearch_exclude_negative_filter_values = $this->conf['ultrasearch_exclude_negative_filter_values'];
	} else {
		$this->ultrasearch_exclude_negative_filter_values = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_exclude_negative_filter_values', 's_search');
	}

	// setting coming from typoscript or from flexform
	if ($this->conf['ultrasearch_target_element']) {
		$this->ultrasearch_target_element = $this->conf['ultrasearch_target_element'];
	} else {
		$this->ultrasearch_target_element = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_target_element', 's_search');
	}
	// setting coming from typoscript or from flexform
	if ($this->conf['ultrasearch_javascript_client_file']) {
		$this->ultrasearch_javascript_client_file = $this->conf['ultrasearch_javascript_client_file'];
	} else {
		$this->ultrasearch_javascript_client_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_javascript_client_file', 's_search');
	}
	if (!$this->ultrasearch_javascript_client_file or $this->ultrasearch_javascript_client_file=='default.js') {
		$this->ultrasearch_javascript_client_file=t3lib_extMgm::siteRelPath('multishop').'js/ultrasearch/default.js';
	} else if ($this->ultrasearch_javascript_client_file) {
		if (strstr($this->ultrasearch_javascript_client_file,"/")) {
			$this->ultrasearch_javascript_client_file=$this->ultrasearch_javascript_client_file;
		} else if ($this->ultrasearch_javascript_client_file) {
			$this->ultrasearch_javascript_client_file=t3lib_extMgm::siteRelPath('multishop').'js/ultrasearch/'.$this->ultrasearch_javascript_client_file;
		} else {
			$this->ultrasearch_javascript_client_file=t3lib_extMgm::siteRelPath('multishop').'js/ultrasearch/default.js';
		}
	}
	if (!$this->ultrasearch_target_element) {
		$this->ultrasearch_target_element='#content';
	}
	$headers='
	<script type="text/javascript">
	var content_middle = "'.$this->ultrasearch_target_element.'";
	var ultrasearch_categories_id;
	var ultrasearch_exclude_negative_filter_values;
	var ultrasearch_fields=\''.base64_encode(($this->ultrasearch_fields)).'\';';
	if ($this->filterCategoriesFormByCategoriesIdGetParam and is_numeric($this->get['categories_id'])) {
		//$headers.='ultrasearch_categories_id=\''.$this->get['categories_id'].'\';';
		$headers.='filterCategoriesFormByCategoriesIdGetParam=\'1\';';
	}
	if ($this->ultrasearch_exclude_negative_filter_values) {
		$headers.='ultrasearch_exclude_negative_filter_values=\'1\';';
	}
	$headers.='// location of the ultrasearch server
	var ultrasearch_resultset_server_path=\''.mslib_fe::typolink($this->shop_pid.',2002','&tx_multishop_pi1[page_section]=ultrasearch_server&categories_id='.$this->get['categories_id']).'&ultrasearch_exclude_negative_filter_values='.$this->ultrasearch_exclude_negative_filter_values.'&filterCategoriesFormByCategoriesIdGetParam='.$this->filterCategoriesFormByCategoriesIdGetParam.'\';';
	if ($this->hideHeader) {
		$headers.='var ultrasearcch_resultset_header=\'\';';
	} else {
		$headers.='var ultrasearcch_resultset_header=\'<div class="main-heading"><h2>'.$this->pi_getLL('search').'</h2></div>\';';		
	}
	$headers.='var ultrasearch_message_no_results=\'<div id="msFrontUltrasearchNoResults"><div class="main-heading"><h2>'.addslashes($this->pi_getLL('no_products_found_heading')).'</h2></div><p>'.addslashes($this->pi_getLL('no_products_found_description')).'</p></div>\';
	</script>
	<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('multishop').'js/jquery-hashchange-master/jquery.ba-hashchange.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
		  // Bind an event to window.onhashchange that, when the hash changes, gets the
		  // hash and adds the class "selected" to any matching nav link.
		  $(window).hashchange( function(){
			var hash = location.hash;
			if (hash) {
				$(\'#locationHash\').val(hash.replace( /^#/, \'\' ));
			}
		  })
		  // $(window).hashchange();
		});	
	</script>	
	<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('multishop').'js/jquery.form.js"></script> 	
	<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('multishop').'js/jquery.dform-1.1.0.min.js"></script> 		
	<script type="text/javascript" src="'.$this->ultrasearch_javascript_client_file.'"></script>';
	$GLOBALS['TSFE']->additionalHeaderData[] =$headers;
	$content = '<form method="get" action="" id="msFrontUltrasearchForm">
	<input name="locationHash" type="hidden" value="" id="locationHash" />
	</form>';
}
?>