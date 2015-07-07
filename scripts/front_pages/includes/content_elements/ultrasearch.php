<?php
//print_r($this->get);
// if there are no Ultrasearch fields defined through the Multishop configuration system, lets check if the Ultrasearch fields are defined through FlexForms.
// setting coming from typoscript or from flexform
if ($this->conf['ultrasearch_fields']) {
	$this->ultrasearch_fields=$this->conf['ultrasearch_fields'];
} else {
	$this->ultrasearch_fields=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_fields', 's_search');
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
		$this->filterCategoriesFormByCategoriesIdGetParam=$this->conf['filterCategoriesFormByCategoriesIdGetParam'];
	} else {
		$this->filterCategoriesFormByCategoriesIdGetParam=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_filtered_by_current_category', 's_search');
	}
	// setting coming from typoscript or from flexform
	if (is_numeric($this->conf['ultrasearch_exclude_negative_filter_values'])) {
		$this->ultrasearch_exclude_negative_filter_values=$this->conf['ultrasearch_exclude_negative_filter_values'];
	} else {
		$this->ultrasearch_exclude_negative_filter_values=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_exclude_negative_filter_values', 's_search');
	}
	// setting coming from typoscript or from flexform
	if ($this->conf['ultrasearch_target_element']) {
		$this->ultrasearch_target_element=$this->conf['ultrasearch_target_element'];
	} else {
		$this->ultrasearch_target_element=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_target_element', 's_search');
	}
	// setting coming from typoscript or from flexform
	if ($this->conf['ultrasearch_javascript_client_file']) {
		$this->ultrasearch_javascript_client_file=$this->conf['ultrasearch_javascript_client_file'];
	} else {
		$this->ultrasearch_javascript_client_file=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_javascript_client_file', 's_search');
	}
	if (!$this->ultrasearch_javascript_client_file or $this->ultrasearch_javascript_client_file=='default.js') {
		$this->ultrasearch_javascript_client_file=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/ultrasearch/default.js';
	} else if ($this->ultrasearch_javascript_client_file) {
		if (strstr($this->ultrasearch_javascript_client_file, "/")) {
			$this->ultrasearch_javascript_client_file=$this->ultrasearch_javascript_client_file;
		} else if ($this->ultrasearch_javascript_client_file) {
			$this->ultrasearch_javascript_client_file=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/ultrasearch/'.$this->ultrasearch_javascript_client_file;
		} else {
			$this->ultrasearch_javascript_client_file=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/ultrasearch/default.js';
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
	var ultrasearch_resultset_server_path=\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=ultrasearch_server&manufacturers_id='.$this->get['manufacturers_id'].'&categories_id='.$this->get['categories_id'].'&ultrasearch_exclude_negative_filter_values='.$this->ultrasearch_exclude_negative_filter_values.'&filterCategoriesFormByCategoriesIdGetParam='.$this->filterCategoriesFormByCategoriesIdGetParam, 1).'\';
	var shipping_costs_overview=false;'."\n";
	if ($this->ms['MODULES']['DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE']) {
		$headers.='
		var ultrasearch_shipping_costs_review_url=\''.mslib_fe::typolink('', 'type=2002&tx_multishop_pi1[page_section]=get_product_shippingcost_overview').'\';
		var labels_shipping_costs = \''.$this->pi_getLL('shipping_costs').'\';
		var labels_product_shipping_and_handling_cost_overview = \''.$this->pi_getLL('product_shipping_and_handling_cost_overview').'\';
		var labels_deliver_to = \''.$this->pi_getLL('deliver_to').'\';
		var labels_shipping_and_handling_cost_overview = \''.$this->pi_getLL('shipping_and_handling_cost_overview').'\';
		var labels_deliver_by = \''.$this->pi_getLL('deliver_by').'\';
		var labels_delivery_time=\''.$this->pi_getLL('admin_delivery_time').'\';
		var shipping_costs_overview=true;
		'."\n";
	}
	if ($this->hideHeader) {
		$headers.='var ultrasearcch_resultset_header=\'\';';
	} else {
		$cmsDescriptionArray=array();
		if (isset($this->get['manufacturers_id']) && is_numeric($this->get['manufacturers_id'])) {
			$strCms=$GLOBALS ['TYPO3_DB']->SELECTquery('m.manufacturers_id, mc.content, mc.content_footer, m.manufacturers_name', // SELECT ...
				'tx_multishop_manufacturers m, tx_multishop_manufacturers_cms mc', // FROM ...
				"m.manufacturers_id='".$this->get['manufacturers_id']."' AND m.status=1 and mc.language_id='".$this->sys_language_uid."' and m.manufacturers_id=mc.manufacturers_id", // WHERE.
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qryCms=$GLOBALS['TYPO3_DB']->sql_query($strCms);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qryCms)) {
				$rowCms=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qryCms);
				$cmsDescriptionArray['header_title']=$rowCms['manufacturers_name'];
				$cmsDescriptionArray['content']=$rowCms['content'];
				$cmsDescriptionArray['content_footer']=$rowCms['content_footer'];
			}
		} elseif (isset($this->get['categories_id']) && is_numeric($this->get['categories_id'])) {
			$strCms=$GLOBALS ['TYPO3_DB']->SELECTquery('c.categories_id, cd.content, cd.content_footer, cd.categories_name', // SELECT ...
				'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
				"c.categories_id='".$this->get['categories_id']."' AND c.status=1 and cd.language_id='".$this->sys_language_uid."' and c.page_uid='".$this->showCatalogFromPage."' and c.categories_id=cd.categories_id", // WHERE.
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qryCms=$GLOBALS['TYPO3_DB']->sql_query($strCms);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qryCms)) {
				$rowCms=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qryCms);
				$cmsDescriptionArray['header_title']=$rowCms['categories_name'];
				$cmsDescriptionArray['content']=$rowCms['content'];
				$cmsDescriptionArray['content_footer']=$rowCms['content_footer'];
			}
		}
		if ($cmsDescriptionArray['header_title']) {
			$headers.='var ultrasearcch_resultset_header=\'<div class="main-heading"><h1>'.htmlspecialchars($cmsDescriptionArray['header_title']).'</h1></div>\';';
		} else {
			$headers.='var ultrasearcch_resultset_header=\'<div class="main-heading"><h1>'.$this->pi_getLL('search').'</h1></div>\';';
		}
	}
	$headers.='var ultrasearch_message_no_results=\'<div id="msFrontUltrasearchNoResults"><div class="main-heading"><h1>'.addslashes($this->pi_getLL('no_products_found_heading')).'</h1></div><p>'.addslashes($this->pi_getLL('no_products_found_description')).'</p></div>\';
	</script>
	<script type="text/javascript" src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/jquery-hashchange-master/jquery.ba-hashchange.min.js"></script>
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
	<script type="text/javascript" src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/jquery.form.js"></script>
	<script type="text/javascript" src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/jquery.dform-1.1.0.min.js"></script>
	<script type="text/javascript" src="'.$this->ultrasearch_javascript_client_file.'"></script>';
	$GLOBALS['TSFE']->additionalHeaderData[]=$headers;
	$content='<form method="get" action="" id="msFrontUltrasearchForm">
	<input name="locationHash" type="hidden" value="" id="locationHash" />
	</form>';
}
?>