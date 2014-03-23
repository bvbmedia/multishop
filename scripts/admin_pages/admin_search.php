<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if($this->ADMIN_USER) {
	if($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
		$this->ms['MODULES']['CACHE_FRONT_END']=0;
	}
	if($this->ms['MODULES']['CACHE_FRONT_END']) {
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']);
		$Cache_Lite=new Cache_Lite($options);
		$string=md5('admin_search_'.$this->showCatalogFromPage.'_'.$this->get['ms_admin_skeyword'].'_'.$this->get['page']);
	}
	if(!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string))) {
		$data=array();
		if($this->get['ms_admin_skeyword']) {
			$this->get['q']=$this->get['ms_admin_skeyword'];
			$this->get['q']=trim($this->get['q']);
			$this->get['q']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['q'], $GLOBALS['TSFE']->metaCharset);
			$this->get['q']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['q'], TRUE);
			$this->get['q']=mslib_fe::RemoveXSS($this->get['q']);
		}
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
		 * @param       string $text Haystack - The text to search
		 * @param       array|string $needle Needle - The string to highlight
		 * @param       bool $options Bitwise set of options
		 * @param       array $highlight Replacement string
		 * @return      Text with needle highlighted
		 */
		function str_highlight($text, $needle='', $options=null, $highlight=null) {
			if(!$needle) {
				return $text;
			}
			// Default highlighting
			if($highlight === null) {
				$highlight='<strong class="highlight">\1</strong>';
			}
			// Select pattern to use
			if($options & STR_HIGHLIGHT_SIMPLE) {
				$pattern='#(%s)#';
			} else {
				$pattern='#(?!<.*?)(%s)(?![^<>]*?>)#';
				$sl_pattern='#<a\s(?:.*?)>(%s)</a>#';
			}
			$pattern.='i';
			$sl_pattern.='i';
			$needle=(array)$needle;
			foreach($needle as $needle_s) {
				$needle_s=preg_quote($needle_s);
				// Escape needle with optional whole word check
				if($options & STR_HIGHLIGHT_WHOLEWD) {
					$needle_s='\b'.$needle_s.'\b';
				}
				// Strip links
				if($options & STR_HIGHLIGHT_STRIPLINKS) {
					$sl_regex=sprintf($sl_pattern, $needle_s);
					$text=preg_replace($sl_regex, '\1', $text);
				}
				$regex=sprintf($pattern, $needle_s);
				$text=preg_replace($regex, $highlight, $text);
			}
			return $text;
		}

		$p=!$this->get['page'] ? 0 : $this->get['page'];
		if(!is_numeric($p)) {
			$p=0;
		}
		$limit=25;
		$offset=$p*$limit;
		$this->get['limit']=$limit;
		$global_max_page=0;
		$modules=array();
		$orders_filter=array();
		$products_filter=array();
		$customers_filter=array();
		// global paging indicator
		$have_paging=false;
		$results_counter=0;
		if(is_numeric($this->get['q'])) {
			// numeric so first find orders and customer ids
			$modules['orders']=1;
			$modules['invoices']=1;
			$modules['customers']=1;
			$modules['products']=1;
			$modules['categories']=1;
			$orders_filter[]='o.orders_id like "'.addslashes($this->get['q']).'%"';
			$invoices_filter[]='i.invoice_id like "'.addslashes($this->get['q']).'%"';
			$customers_filter[]='f.uid like "'.addslashes($this->get['q']).'%"';
			$categories_filter[]='c.products_id like "'.addslashes($this->get['q']).'%"';
		} else {
			// string
			$modules['products']=1;
			$modules['categories']=1;
			$modules['orders']=1;
			$modules['customers']=1;
			if($this->ADMIN_USER) {
				$modules['admin_cms']=1;
				$modules['admin_settings']=1;
			}
		}
		// cms search
		if($modules['admin_cms']) {
			$filter=array();
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			if(strlen($this->get['q']) > 0) {
				$items=array();
				$items[]="cd.name LIKE '%".addslashes($this->get['q'])."%'";
				$filter[]='('.implode(" or ", $items).')';
			}
			//if (!$this->masterShop) $filter[]='page_uid='.$this->shop_pid;
			$select[]='cd.id, cd.name';
			$orderby[]='cd.name';
			$pageset=mslib_fe::getCMSPageSet($filter, $offset, $this->get['limit'], $orderby, $having, $select, $where, $from);
			$resultset['admin_cms']=$pageset;
			$max_page=ceil($pageset['total_rows']/$limit);
			if($max_page > $global_max_page) {
				$global_max_page=$max_page;
			}
			if($pageset['total_rows'] > $limit && ($p+1) < $max_page) {
				$have_paging=true;
			}
			if($pageset['total_rows'] > 0) {
				$results_counter++;
			}
		}
		// cms search eof		
		// admin_settings search
		if($modules['admin_settings']) {
			$filter=array();
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			if(strlen($this->get['q']) > 0) {
				$items=array();
				$items[]="c.configuration_title LIKE '%".addslashes($this->get['q'])."%'";
				$items[]="c.configuration_key LIKE '%".addslashes($this->get['q'])."%'";
				$items[]="c.configuration_value LIKE '%".addslashes($this->get['q'])."%'";
				$items[]="cv.configuration_value LIKE '%".addslashes($this->get['q'])."%'";
				$filter[]='('.implode(" or ", $items).')';
				$filter[]="(cv.page_uid is NULL or cv.page_uid='".$this->showCatalogFromPage."')";
			}
			$select[]='c.id, c.configuration_title';
			$orderby[]='c.configuration_title';
			$pageset=mslib_fe::getAdminSettingsPageSet($filter, $offset, $this->get['limit'], $orderby, $having, $select, $where, $from);
			$resultset['admin_settings']=$pageset;
			$max_page=ceil($pageset['total_rows']/$limit);
			if($max_page > $global_max_page) {
				$global_max_page=$max_page;
			}
			if($pageset['total_rows'] > $limit && ($p+1) < $max_page) {
				$have_paging=true;
			}
			if($pageset['total_rows'] > 0) {
				$results_counter++;
			}
		}
		// admin_settings search eof	
		// categories search
		if($modules['categories']) {
			$filter=$categories_filter;
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			if(strlen($this->get['q']) > 0) {
				$items=array();
				$items[]="cd.categories_name LIKE '%".addslashes($this->get['q'])."%'";
				$filter[]='('.implode(" or ", $items).')';
			}
			if(!$this->masterShop) {
				$filter[]='c.page_uid='.$this->showCatalogFromPage;
			}
			$select[]='c.categories_url, cd.categories_name, c.categories_id';
			$orderby[]='cd.categories_name';
			$pageset=mslib_fe::getCategoriesPageSet($filter, $offset, $this->get['limit'], $orderby, $having, $select, $where, $from);
			$resultset['categories']=$pageset;
			$max_page=ceil($pageset['total_rows']/$limit);
			if($max_page > $global_max_page) {
				$global_max_page=$max_page;
			}
			if($pageset['total_rows'] > $limit && ($p+1) < $max_page) {
				$have_paging=true;
			}
			if($pageset['total_rows'] > 0) {
				$results_counter++;
			}
		}
		// categories search eof
		// orders search
		if($modules['orders']) {
			$filter=$orders_filter;
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			if(strlen($this->get['q']) > 0) {
				$items=array();
				$items[]="orders_id='".addslashes($this->get['q'])."'";
				$items[]="customer_id LIKE '%".addslashes($this->get['q'])."%'";
				$filter[]='('.implode(" or ", $items).')';
			}
			if(!$this->masterShop) {
				$filter[]='o.page_uid='.$this->showCatalogFromPage;
			}
			$select[]='o.*, osd.name as orders_status';
			$orderby[]='o.orders_id desc';
			$pageset=mslib_fe::getOrdersPageSet($filter, $offset, $this->get['limit'], $orderby, $having, $select, $where, $from);
			$resultset['orders']=$pageset;
			$max_page=ceil($pageset['total_rows']/$limit);
			if($max_page > $global_max_page) {
				$global_max_page=$max_page;
			}
			if($pageset['total_rows'] > $limit && ($p+1) < $max_page) {
				$have_paging=true;
			}
			if($pageset['total_rows'] > 0) {
				$results_counter++;
			}
		}
		// orders search eof
		// invoices search
		if($modules['invoices']) {
			$filter=$invoices_filter;
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			if(!$this->masterShop) {
				$filter[]='i.page_uid='.$this->showCatalogFromPage;
			}
			$select[]='i.invoice_id,i.hash';
			$orderby[]='i.id desc';
			$pageset=mslib_fe::getInvoicesPageSet($filter, $offset, $this->get['limit'], $orderby, $having, $select, $where, $from);
			$resultset['invoices']=$pageset;
			$max_page=ceil($pageset['total_rows']/$limit);
			if($max_page > $global_max_page) {
				$global_max_page=$max_page;
			}
			if($pageset['total_rows'] > $limit && ($p+1) < $max_page) {
				$have_paging=true;
			}
			if($pageset['total_rows'] > 0) {
				$results_counter++;
			}
		}
		// invoices eof	
		// customer search
		if($modules['customers']) {
			$filter=$customers_filter;
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			if(strlen($this->get['q']) > 0) {
				$items=array();
				$items[]="f.company like '".addslashes($this->get['q'])."%'";
				$items[]="f.name like '".addslashes($this->get['q'])."%'";
				$items[]="f.email like '".addslashes($this->get['q'])."%'";
				$items[]="f.username like '".addslashes($this->get['q'])."%'";
				$filter[]='('.implode(" or ", $items).')';
				$filter[]='(f.disable=0 and f.deleted=0)';
			}
			$pageset=mslib_fe::getCustomersPageSet($filter, $offset, 0, $orderby, $having, $select, $where);
			$resultset['customers']=$pageset;
			$max_page=ceil($pageset['total_rows']/$limit);
			if($max_page > $global_max_page) {
				$global_max_page=$max_page;
			}
			if($pageset['total_rows'] > $limit && ($p+1) < $max_page) {
				$have_paging=true;
			}
			if($pageset['total_rows'] > 0) {
				$results_counter++;
			}
		}
		// customer search eof
		// product search
		if($modules['products'] and $this->get['q']) {
			$this->ms['MODULES']['FLAT_DATABASE']=0;
			$filter=array();
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			if(strlen($this->get['q']) > 1) {
				$array=explode(" ", $this->get['q']);
				$total=count($array);
				$oldsearch=0;
				foreach($array as $item) {
					if(strlen($item) < $this->ms['MODULES']['FULLTEXT_SEARCH_MIN_CHARS']) {
						$oldsearch=1;
						break;
					}
				}
				if($this->ms['MODULES']['FLAT_DATABASE']) {
					$tbl='';
				} else {
					$tbl='pd.';
				}
				$filter[]=$tbl.'products_id like "'.addslashes($this->get['q']).'%"';
				if($this->ms['MODULES']['REGULAR_SEARCH_MODE'] == '%keyword') {
					// do normal indexed search
					$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."')";
				} else {
					if($this->ms['MODULES']['REGULAR_SEARCH_MODE'] == 'keyword%') {
						// do normal indexed search
						$filter[]="(".$tbl."products_name like '".addslashes($this->get['q'])."%')";
					} else {
						// do normal indexed search
						$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."%')";
					}
				}
				if($this->ms['MODULES']['FLAT_DATABASE']) {
					$tbl='pf.';
				} else {
					$tbl='p.';
				}
				$filter[]="(".$tbl."products_model like '%".addslashes($this->get['q'])."%')";
				$filter[]="(".$tbl."sku_code like '%".addslashes($this->get['q'])."%')";
				$filter[]="(".$tbl."ean_code like '%".addslashes($this->get['q'])."%')";
				$filter[]="(".$tbl."vendor_code like '%".addslashes($this->get['q'])."%')";
				$filter='('.implode(" OR ", $filter).')';
			}
			if(is_numeric($parent_id) and $parent_id > 0) {
				if($this->ms['MODULES']['FLAT_DATABASE']) {
					$string='(';
					for($i=0; $i < 4; $i++) {
						if($i > 0) {
							$string.=" or ";
						}
						$string.="categories_id_".$i." = '".$parent_id."'";
					}
					$string.=')';
					if($string) {
						$filter[]=$string;
					}
				} else {
					$cats=mslib_fe::get_subcategory_ids($parent_id);
					$cats[]=$parent_id;
					$filter[]="p2c.categories_id IN (".implode(",", $cats).")";
				}
			}
			//error_log(print_r($filter,1));
			$pageset=mslib_fe::getProductsPageSet($filter, $offset, $limit, $orderby, $having, $select, $where, 0, array(), array(), 'admin_ajax_products_search');
			$resultset['products']=$pageset;
			$max_page=ceil($pageset['total_rows']/$limit);
			if($pageset['total_rows'] > $limit && ($p+1) < $max_page) {
				$have_paging=true;
			}
			if($pageset['total_rows'] > 0) {
				$results_counter++;
			}
		}
		$content='';
		$tmp_listing='';
		// product search eof
		// now build up the listing
		// admin cms
		if(count($resultset['admin_cms']['admin_cms'])) {
			$tmp_listing.='<li class="ui-category"><span class="admin_ajax_res_header">Admin CMS</span></li>';
			foreach($resultset['admin_cms']['admin_cms'] as $category) {
				$tmp_listing.='<li class="ui-menu-item ui-menu-item-alternate" role="menuitem">
					<a alt="'.substr($category['name'], 0, 50).'" class="ui-corner-all" tabindex="-1" href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cmt_id='.$category['id']).'&action=edit_cms" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">
						<div class="single_row">'.substr($category['name'], 0, 50).'</div>
					</a>
				</li>';
			}
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_footer">Total number: '.$resultset['admin_cms']['total_rows'].'</span></li>';
		}
		// end admin cms
		// admin settings
		if(count($resultset['admin_settings']['admin_settings'])) {
			$tmp_listing.='<li class="ui-category"><span class="admin_ajax_res_header">Admin Settings</span></li>';
			foreach($resultset['admin_settings']['admin_settings'] as $category) {
				$tmp_listing.='<li class="ui-menu-item ui-menu-item-alternate" role="menuitem">
					<a alt="'.substr($category['configuration_title'], 0, 50).'" class="ui-corner-all" tabindex="-1" href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&module_id='.$category['id']).'&action=edit_module" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">
						<div class="single_row">'.substr($category['configuration_title'], 0, 50).'</div>
					</a>
				</li>';
			}
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_footer">Total number: '.$resultset['admin_settings']['total_rows'].'</span></li>';
		}
		// end admin settings
		// categories
		if(count($resultset['categories']['categories'])) {
			$tmp_listing.='<li class="ui-category"><span class="admin_ajax_res_header">Categories</span></li>';
			foreach($resultset['categories']['categories'] as $category) {
				$tmp_listing.='<li class="ui-menu-item ui-menu-item-alternate" role="menuitem">
					<a alt="'.substr($category['categories_name'], 0, 50).'" class="ui-corner-all" tabindex="-1" href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id'].'&action=edit_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">
						<div class="single_row">'.substr($category['categories_name'], 0, 50).'</div>
					</a>
				</li>';
			}
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_footer">Total number: '.$resultset['categories']['total_rows'].'</span></li>';
		}
		// end categories
		// orders
		if(count($resultset['orders']['orders'])) {
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_header">Orders</span></li>';
			foreach($resultset['orders']['orders'] as $order) {
				$tmp_listing.='<li class="ui-menu-item ui-menu-item-alternate" role="menuitem">
					<a alt="'.substr($order['orders_id'], 0, 50).'" class="ui-corner-all" tabindex="-1" href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id']).'&action=edit_order" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">
						<div class="single_row">'.substr($order['orders_id'], 0, 50).'</div>
					</a>
				</li>';
			}
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_footer">Total number: '.$resultset['orders']['total_rows'].'</span></li>';
		}
		// end orders
		// invoices
		if(count($resultset['invoices']['invoices'])) {
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_header">Invoices</span></li>';
			foreach($resultset['invoices']['invoices'] as $invoice) {
				$tmp_listing.='<li class="ui-menu-item ui-menu-item-alternate" role="menuitem">
					<a alt="'.substr($invoice['invoice_id'], 0, 50).'" class="ui-corner-all" tabindex="-1" href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">
						<div class="single_row">'.substr($invoice['invoice_id'], 0, 50).'</div>
					</a>
				</li>';
			}
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_footer">Total number: '.$resultset['orders']['total_rows'].'</span></li>';
		}
		// end invoices
		// customers
		if(count($resultset['customers']['customers'])) {
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_header">Customers</span></li>';
			foreach($resultset['customers']['customers'] as $customer) {
				if(!$customer['company']) {
					$customer['company']='N/A';
				}
				if(!$customer['name']) {
					$customer['name']=$customer['username'];
				}
				$tmp_listing.='<li class="ui-menu-item ui-menu-item-alternate" role="menuitem">
					<a alt="'.substr($customer['name'], 0, 50).'" class="ui-corner-all" tabindex="-1" href="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&tx_multishop_pi1[cid]='.$customer['uid'].'&action=edit_customer').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">
						<div class="single_row">'.substr($customer['name'], 0, 50).'</div>
					</a>
				</li>';
			}
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_footer">Total number: '.$resultset['customers']['total_rows'].'</span></li>';
		}
		// end customers	
		// products
		if(count($resultset['products']['products'])) {
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_header">Products</span></li>';
			foreach($resultset['products']['products'] as $product) {
				$prod_link=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$product['categories_id'].'&pid='.$product['products_id'].'&action=edit_product');
				if($product['products_image']) {
					$prod_image='<div class="ajax_products_image">'.'<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '50').'">'.'</div>';
				} else {
					$prod_image='<div class="ajax_products_image"><div class="no_image_50"></div>
					</div>';
				}
				$prod_name='<div class="ajax_products_name">'.substr($product['products_name'], 0, 50).'</div>';
				$prod_desc='<div class="ajax_products_shortdescription">'.str_highlight(substr($product['products_shortdescription'], 0, 75), $this->get['q']).'</div>';
				if($product['products_price'] <> $product['final_price']) {
					$prod_price='<div class="ajax_old_price">'.mslib_fe::amount2Cents($product['products_price'], 0).'</div><div class="ajax_specials_price">'.mslib_fe::amount2Cents($product['final_price'], 0).'</div>';
				} else {
					$prod_price='<div class="ajax_products_price">'.mslib_fe::amount2Cents($product['products_price'], 0).'</div>';
				}
				$tmp_listing.='<li class="ui-menu-item ui-menu-item-alternate" role="menuitem">
					<a alt="test product" class="ui-corner-all" tabindex="-1" href="'.$prod_link.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">
						<div class="ajax_products_image_wrapper">
							'.$prod_image.'
						</div>
						<div class="ajax_products_search_item">
							'.$prod_name.'
							'.$prod_desc.'
							'.$prod_price.'
						</div>
					</a>
				</li>';
			}
			$tmp_listing.='<li class="ui-category"><span id="admin_ajax_res_footer">Total number: '.$resultset['products']['total_rows'].'</span></li>';
		}
		// start pagination
		if($have_paging || $this->get['page'] > 0) {
			$total_pages=$global_max_page;
			$tmp_pagination='';
			$tmp_pagination.='<div id="pagenav_container_list_wrapper">
			<ul id="pagenav_container_list">
			<li class="pagenav_first">';
			if($p > 0) {
				$tmp_pagination.='<a alt="'.urlencode($this->get['q']).'" tabindex="-1" class="pagination_button msBackendButton backState arrowLeft arrowPosLeft ui-corner-all" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_search&page=0&ms_admin_skeyword='.urlencode($this->get['q'])).'"><span>'.$this->pi_getLL('first').'</span></a>';
			} else {
				$tmp_pagination.='<span class="pagination_button msBackendButton backState arrowLeft arrowPosLeft disabled"><span>'.$this->pi_getLL('first').'</span></span>';
			}
			$tmp_pagination.='</li>';
			$tmp_pagination.='<li class="pagenav_previous">';
			if($p > 0) {
				if(($p-1) > 0) {
					$tmp_pagination.='<a alt="'.urlencode($this->get['q']).'" tabindex="-1" class="pagination_button msBackendButton backState arrowLeft arrowPosLeft ui-corner-all" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_search&page='.($this->get['page']-1).'&ms_admin_skeyword='.urlencode($this->get['q'])).'"><span>'.$this->pi_getLL('previous').'</span></a>';
				} else {
					$tmp_pagination.='<a alt="'.urlencode($this->get['q']).'" tabindex="-1" class="pagination_button msBackendButton backState arrowLeft arrowPosLeft ui-corner-all" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_search&page='.($this->get['page']-1).'&ms_admin_skeyword='.urlencode($this->get['q'])).'"><span>'.$this->pi_getLL('previous').'</span></a>';
				}
			} else {
				$tmp_pagination.='<span class="pagination_button msBackendButton backState arrowLeft arrowPosLeft disabled"><span>'.$this->pi_getLL('previous').'</span></span>';
			}
			$tmp_pagination.='</li>';
			if($p == 0 || $p < 9) {
				$start_page_number=1;
				if($total_pages <= 10) {
					$end_page_number=$total_pages;
				} else {
					$end_page_number=10;
				}
			} else {
				if($p >= 9) {
					$start_page_number=($p-5)+1;
					$end_page_number=($p+4)+1;
					if($end_page_number > $total_pages) {
						$end_page_number=$total_pages;
					}
				}
			}
			$tmp_pagination.='<li class="pagenav_number">
			<ul id="pagenav_number_wrapper">';
			for($x=$start_page_number; $x <= $end_page_number; $x++) {
				if(($p+1) == $x) {
					$tmp_pagination.='<li><span>'.$x.'</span></a></li>';
				} else {
					$tmp_pagination.='<li><a alt="'.urlencode($this->get['q']).'" tabindex="-1" class="ajax_link pagination_button ui-corner-all" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_search&page='.($x-1).'&ms_admin_skeyword='.urlencode($this->get['q'])).'">'.$x.'</a></li>';
				}
			}
			$tmp_pagination.='</ul>
			</li>';
			$tmp_pagination.='<li class="pagenav_next">';
			if($this->get['page'] < ($global_max_page-1)) {
				$tmp_pagination.='<a alt="'.urlencode($this->get['q']).'" tabindex="-1" class="pagination_button msBackendButton continueState arrowRight arrowPosLeft ui-corner-all" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_search&page='.($this->get['page']+1).'&ms_admin_skeyword='.urlencode($this->get['q'])).'"><span>'.$this->pi_getLL('next').'</span></a>';
			} else {
				$tmp_pagination.='<span class="pagination_button msBackendButton continueState arrowRight arrowPosLeft disabled"><span>'.$this->pi_getLL('next').'</span></span>';
			}
			$tmp_pagination.='</li>';
			$tmp_pagination.='<li class="pagenav_last">';
			if($this->get['page'] < ($global_max_page-1)) {
				$lastpage=$global_max_page-1;
				$tmp_pagination.='<a alt="'.urlencode($this->get['q']).'" tabindex="-1" class="pagination_button msBackendButton continueState arrowRight arrowPosLeft ui-corner-all" href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_search&page='.($lastpage).'&ms_admin_skeyword='.urlencode($this->get['q'])).'"><span>'.$this->pi_getLL('last').'</span></a>';
			} else {
				$tmp_pagination.='<span class="pagination_button msBackendButton continueState arrowRight arrowPosLeft disabled"><span>'.$this->pi_getLL('last').'</span></span>';
			}
			$tmp_pagination.='</li>';
			$tmp_pagination.='</ul>
			</div>';
		}
		// eol pagination
		if($have_paging) {
			$tmp_listing.='<li class="ui-menu-item" role="menuitem">'.$tmp_pagination.'</li>';
		} else {
			$prod=array();
			if($results_counter > 0) {
				$tmp_listing.='<li class="ui-menu-item" role="menuitem">'.$tmp_pagination.'</li>';
			} else {
				$tmp_listing.='<li class="ui-menu-item" role="menuitem">
					<a alt="'.urlencode($this->get['q']).'" class="ui-corner-all" tabindex="-1">
						<span id="more-results">No result</span>
					</a>
				</li>';
			}
		}
		if(!empty($tmp_listing)) {
			$content.='<ul class="admin_search_result">'.$tmp_listing.'</ul>';
		}
		// now build up the listing eof
	}
}
?>