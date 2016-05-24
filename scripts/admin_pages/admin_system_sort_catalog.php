<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
switch ($this->get['tx_multishop_pi1']['sortItem']) {
	case 'manufacturers':
		switch ($this->get['tx_multishop_pi1']['sortByField']) {
			case 'name':
				$content.=tx_mslib_catalog::sortCatalog('manufacturers', 'manufacturers_name', $this->get['tx_multishop_pi1']['orderBy']);
				break;
		}
		break;
	case 'catalog':
		switch ($this->get['tx_multishop_pi1']['sortByField']) {
			case 'name':
				$content.=tx_mslib_catalog::sortCatalog('manufacturers', 'manufacturers_name', 'asc');
				$content.=tx_mslib_catalog::sortCatalog('categories', 'categories_name', 'asc');
				$content.=tx_mslib_catalog::sortCatalog('products', 'products_name', 'asc');
				break;
		}
		break;
	case 'categories':
		switch ($this->get['tx_multishop_pi1']['sortByField']) {
			case 'categories_name':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
		}
		break;
	case 'products':
		switch ($this->get['tx_multishop_pi1']['sortByField']) {
			case 'products_name':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
			case 'products_price':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
			case 'products_date_added':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
			case 'products_main_categories':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
			case 'products_deepest_categories':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
		}
		break;
	case 'attribute_values':
		switch ($this->get['tx_multishop_pi1']['sortByField']) {
			case 'products_options_values_name':
			case 'products_options_values_name_natural':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
		}
		break;
	case 'attribute_names':
		switch ($this->get['tx_multishop_pi1']['sortByField']) {
			case 'products_options_name':
			case 'products_options_name_natural':
				$content.=tx_mslib_catalog::sortCatalog($this->get['tx_multishop_pi1']['sortItem'], $this->get['tx_multishop_pi1']['sortByField'], $this->get['tx_multishop_pi1']['orderBy']);
				break;
		}
		break;
}
?>