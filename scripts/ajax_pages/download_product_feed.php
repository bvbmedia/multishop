<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->get['feed_hash']) {
	set_time_limit(86400);
	ignore_user_abort(true);
	$feed=mslib_fe::getProductFeed($this->get['feed_hash'], 'code');
	$lifetime=7200;
	if ($this->ADMIN_USER) {
		$lifetime=0;
	}
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$lifetime
	);
	$Cache_Lite=new Cache_Lite($options);
	$string='productfeed_'.$this->shop_pid.'_'.serialize($feed).'-'.md5($this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING']);
	if ($this->ADMIN_USER and $this->get['clear_cache']) {
		$Cache_Lite->remove($string);
	}
	if (!$content=$Cache_Lite->get($string)) {
		// preload attibute option names
		$attributes=array();
		$str="SELECT * FROM `tx_multishop_products_options` where language_id='".$GLOBALS['TSFE']->sys_language_uid."' order by products_options_id asc";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$attributes['attribute_option_name_'.$row['products_options_id']]=$row['products_options_name'];
			$attributes['attribute_option_name_'.$row['products_options_id'].'_including_prices']=$row['products_options_name'];
			$attributes['attribute_option_name_'.$row['products_options_id'].'_including_prices_including_vat']=$row['products_options_name'];
		}
		// preload attibute option names eof
		// custom page hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['feedTypesProc'])) {
			$params=array(
				'feed'=>&$feed
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['feedTypesProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom page hook that can be controlled by third-party plugin eof
		$fields=unserialize($feed['fields']);
		$post_data=unserialize($feed['post_data']);
		$fields_headers=$post_data['fields_headers'];
		$fields_values=$post_data['fields_values'];
		if ($feed['include_header']) {
			$total=count($fields);
			$rowCount=0;
			if ($this->get['format']=='excel') {
				$excelHeaderCols=array();
			}
			foreach ($fields as $counter=>$field) {
				$tmpcontent='';
				$rowCount++;
				$enableCustomHeaders=0;
				//hook to let other plugins further manipulate the settings
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['productFeedHeaderIteratorProc'])) {
					$params=array(
						'row'=>&$row,
						'fields'=>&$fields,
						'counter'=>&$counter,
						'field'=>&$field,
						'tmpcontent'=>&$tmpcontent,
						'fields_headers'=>&$fields_headers,
						'fields_values'=>&$fields_values,
						'post_data'=>&$post_data,
						'enableCustomHeaders'=>&$enableCustomHeaders
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['productFeedHeaderIteratorProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				if (!$enableCustomHeaders) {
					switch ($field) {
						case 'custom_field':
							$tmpcontent.=$fields_headers[$counter];
							break;
						default:
							// if key name is attribute option, print the option name. else print key name
							if ($attributes[$field]) {
								$tmpcontent.=$attributes[$field];
							} else {
								$tmpcontent.=$field;
							}
							break;
					}
				}
				if ($this->get['format']=='excel') {
					$excelHeaderCols[]=$tmpcontent;
				} else {
					if ($this->get['format']=='csv') {
						$content.='"';
					}
					$content.=$tmpcontent;
					if ($this->get['format']=='csv') {
						$content.='"';
					}
				}
				if ($rowCount<$total) {
					if ($this->get['format']=='csv') {
						$content.=';';
					} else {
						// add delimiter
						switch ($feed['delimiter']) {
							case 'dash':
								$feed['delimiter_char']='|';
								$content.=$feed['delimiter_char'];
								break;
							case 'dotcomma':
								$feed['delimiter_char']=';';
								$content.=$feed['delimiter_char'];
								break;
							case 'tab':
								$feed['delimiter_char']="\t";
								$content.=$feed['delimiter_char'];
								break;
						}
					}
				}
			}
			$content.="\r\n";
		}
		$mode='products';
		if (in_array('products_id', $fields) or in_array('products_name', $fields)) {
			// retrieve products
			$mode='products';
		} else {
			if (in_array('categories_id', $fields) || in_array('category_link', $fields)) {
				$mode='categories';
			} else {
				if (in_array('manufacturers_id', $fields)) {
					$mode='manufacturers';
				}
			}
		}
		$records=array();
		switch ($mode) {
			case 'products':
				// product search
				$filter=array();
				$having=array();
				$match=array();
				$where=array();
				$orderby=array();
				$select=array();
				if (is_numeric($this->get['products_id'])) {
					$filter[]="p.products_id='".$this->get['products_id']."'";
				}
				if (is_numeric($this->get['categories_id'])) {
					$parent_id=$this->get['categories_id'];
				}
				if (is_numeric($this->get['manufacturers_id'])) {
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						$tbl='pf.';
					} else {
						$tbl='p.';
					}
					$filter[]="(".$tbl."manufacturers_id='".addslashes($this->get['manufacturers_id'])."')";
				}
				if (strlen($this->get['skeyword'])>2) {
					$extra_columns='';
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_ID']) {
						if ($this->ms['MODULES']['FLAT_DATABASE']) {
							$tbl='pf.';
						} else {
							$tbl='p.';
						}
						$extra_columns.=" or ".$tbl."products_id ='".addslashes($this->get['skeyword'])."'";
					}
					$array=explode(" ", $this->get['skeyword']);
					$total=count($array);
					$oldsearch=0;
					if (!$this->ms['MODULES']['ENABLE_FULLTEXT_SEARCH_IN_PRODUCTS_SEARCH']) {
						$oldsearch=1;
					} else {
						foreach ($array as $item) {
							if (strlen($item)<$this->ms['MODULES']['FULLTEXT_SEARCH_MIN_CHARS']) {
								$oldsearch=1;
								break;
							}
						}
					}
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						$tbl='pf.';
					} else {
						$tbl='pd.';
					}
					if ($oldsearch) {
						if ($this->ms['MODULES']['REGULAR_SEARCH_MODE']=='%keyword') {
							// do normal indexed search
							if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
								$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."' or ".$tbl."products_description like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
							} else {
								$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."' ".$extra_columns.")";
							}
						} else {
							if ($this->ms['MODULES']['REGULAR_SEARCH_MODE']=='keyword%') {
								// do normal indexed search
								if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
									$filter[]="(".$tbl."products_name like '".addslashes($this->get['skeyword'])."%' or ".$tbl."products_description like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
								} else {
									$filter[]="(".$tbl."products_name like '".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
								}
							} else {
								// do normal indexed search
								if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
									$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."%' or ".$tbl."products_description like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
								} else {
									$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
								}
							}
						}
					} else {
						// do fulltext search
						$tmpstr=addslashes(mslib_befe::ms_implode(', ', $array, '"', '+', true));
						$fields=$tbl."products_name";
						if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
							$fields.=",".$tbl."products_description";
						}
						if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_ID']) {
							if ($this->ms['MODULES']['FLAT_DATABASE']) {
								$tbl='pf.';
							} else {
								$tbl='p.';
							}
							$fields.=",".$tbl."products_id";
						}
						$select[]="MATCH (".$fields.") AGAINST ('".$tmpstr."' in boolean mode) AS score";
						$where[]="MATCH (".$fields.") AGAINST ('".$tmpstr."' in boolean mode)";
						$orderby[]='score desc';
					}
				}
				if (is_numeric($parent_id) and $parent_id>0) {
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						$string='(';
						for ($i=0; $i<4; $i++) {
							if ($i>0) {
								$string.=" or ";
							}
							$string.="categories_id_".$i." = '".$parent_id."'";
						}
						$string.=')';
						if ($string) {
							$filter[]=$string;
						}
						//
					} else {
						$cats=mslib_fe::get_subcategory_ids($parent_id);
						$cats[]=$parent_id;
						$filter[]="p2c.categories_id IN (".implode(",", $cats).")";
					}
				}
				if ($this->ms['MODULES']['FLAT_DATABASE'] and count($having)) {
					$filter[]=$having[0];
					unset($having);
				}
				if (!$this->ms['MODULES']['FLAT_DATABASE']) {
					$select[]='cd.content as categories_content_top';
					$select[]='cd.content_footer as categories_content_bottom';
				} else {
					// grab it for flat database by subquery
					$select[]='(select cd.content from tx_multishop_categories_description cd where cd.language_id=pf.language_id and cd.categories_id=pf.categories_id) as categories_content_top';
					$select[]='(select cd.content_footer from tx_multishop_categories_description cd where cd.language_id=pf.language_id and cd.categories_id=pf.categories_id) as categories_content_bottom';
				}
				if ($feed['include_disabled']) {
					$includeDisabled=1;
				} else {
					$includeDisabled=0;
				}
				$pageset=mslib_fe::getProductsPageSet($filter, $offset, 999999, $orderby, $having, $select, $where, 0, array(), array(), 'products_feeds', '', 0, 1, array(), $includeDisabled);
				$products=$pageset['products'];
				if ($pageset['total_rows']>0) {
					foreach ($pageset['products'] as $row) {
						$fetchExtraDataFromProducts=1;
						//hook to let other plugins further manipulate the settings
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['productFeedIteratorPreProc'])) {
							$params=array(
								'row'=>&$row,
								'fetchExtraDataFromProducts'=>&$fetchExtraDataFromProducts
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['productFeedIteratorPreProc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						if (!$fetchExtraDataFromProducts) {
							$records[]=$row;
						} else {
							$product=mslib_fe::getProduct($row['products_id'], '', '', $includeDisabled);
							if ($product['products_id']) {
								// TEMPORARY DISABLE THIS IF CONDITION, CAUSE PRODUCTFEED WAS MISSING ATTRIBUTE VALUES IN FLAT ENABLED SHOP
								//if (!$this->ms['MODULES']['FLAT_DATABASE']) {
								// fetch the attributes manually
								$loadAttributeValues=0;
								foreach ($fields as $field) {
									if (strstr($field, 'attribute_option_name')) {
										$loadAttributeValues=1;
									}
								}
								if ($loadAttributeValues) {
									$attributes_data=array();
									//$sql_attributes = "select pa.options_id, pa.options_values_id, pov.products_options_values_name from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov where pa.options_values_id = pov.products_options_values_id and pov.language_id = '".$this->sys_language_uid."' and pa.products_id = " . $product['products_id'];
									$sql_attributes="select * from tx_multishop_products_attributes pa, tx_multishop_products_options po, tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pa.options_id=povp.products_options_id and pa.options_values_id=povp.products_options_values_id and pa.options_id=po.products_options_id and po.language_id = '".$this->sys_language_uid."' and pov.language_id = '".$this->sys_language_uid."' and pa.products_id = ".$product['products_id']." and pa.page_uid=".$this->showCatalogFromPage." and pa.options_values_id = pov.products_options_values_id order by po.sort_order, povp.sort_order";
									$qry_attributes=$GLOBALS['TYPO3_DB']->sql_query($sql_attributes);
									while ($row_attributes=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_attributes)) {
										$attributes_data['attribute_option_name_'.$row_attributes['options_id']]['values'][]=$row_attributes['products_options_values_name'];
										$attributes_data['attribute_option_name_'.$row_attributes['options_id']]['array'][]=$row_attributes;
									}
									foreach ($attributes_data as $attribute_key=>$attribute_val) {
										$row[$attribute_key]=implode(', ', $attributes_data[$attribute_key]['values']);
										// now with prices
										$itemsWithPrice=array();
										$itemsWithPriceIncludingVat=array();
										foreach ($attributes_data[$attribute_key]['array'] as $valueArray) {
											// excluding vat
											$final_price=number_format($valueArray['options_values_price'], 2);
											// store value with corresponding price, divided with double ;
											$itemsWithPrice[]=$valueArray['products_options_values_name'].'::'.$final_price;
											// including vat
											if ($row['tax_rate'] and ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT'])) {
												$final_price=$valueArray['options_values_price'];
												// in this mode the stored prices in the tx_multishop_products are excluding VAT and we have to add it manually
												if ($row['country_tax_rate'] && $row['region_tax_rate']) {
													$country_tax_rate=mslib_fe::taxDecimalCrop($final_price*($row['country_tax_rate']));
													$region_tax_rate=mslib_fe::taxDecimalCrop($final_price*($row['region_tax_rate']));
													$final_price=$final_price+($country_tax_rate+$region_tax_rate);
												} else {
													$tax_rate=mslib_fe::taxDecimalCrop($final_price*($row['tax_rate']));
													$final_price=$final_price+$tax_rate;
												}
											}
											$final_price=number_format($final_price, 2);
											// store value with corresponding price, divided with double ;
											$itemsWithPriceIncludingVat[]=$valueArray['products_options_values_name'].'::'.$final_price;
										}
										if (count($itemsWithPrice)) {
											// add all values with prices excluding VAT as one big string, divided with double pipe
											$row[$attribute_key.'_including_prices']=implode('||', $itemsWithPrice);
										}
										if (count($itemsWithPriceIncludingVat)) {
											// add all values with prices including VAT as one big string, divided with double pipe
											$row[$attribute_key.'_including_prices_including_vat']=implode('||', $itemsWithPriceIncludingVat);
										}
									}
								}
								//}
								$cats=mslib_fe::Crumbar($product['categories_id']);
								$cats=array_reverse($cats);
								$product['categories_crum']=$cats;
								// some parts are not available in flat table and vice versa so lets merge them
								/*
								 * exclude products from feeds
								 */
								$feed_id=$feed['id'];
								$in_feed_exclude_list=false;
								$in_feed_stock_exclude_list=false;
								if (mslib_fe::isItemInFeedsExcludeList($feed_id, $product['products_id'])) {
									$in_feed_exclude_list=true;
								}
								if (!$in_feed_exclude_list) {
									if (mslib_fe::isItemInFeedsExcludeList($feed_id, $product['categories_id'], 'categories')) {
										$in_feed_exclude_list=true;
									}
								}
								if (!$in_feed_exclude_list) {
									if (mslib_fe::isItemInFeedsStockExcludeList($feed_id, $product['products_id'])) {
										$in_feed_stock_exclude_list=true;
									}
									if (!$in_feed_stock_exclude_list) {
										if (mslib_fe::isItemInFeedsStockExcludeList($feed_id, $product['categories_id'], 'categories')) {
											$in_feed_stock_exclude_list=true;
										}
									}
									if ($in_feed_stock_exclude_list) {
										if (isset($product['products_quantity'])) {
											$product['products_quantity']='';
										}
										if (isset($row['products_quantity'])) {
											$row['products_quantity']='';
										}
									}
									$records[]=array_merge($product, $row);
								}
							}
						}
					}
					//hook to let other plugins further manipulate the settings
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['productFeedRecordsPreProc'])) {
						$params=array(
							'records'=>&$records
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['productFeedRecordsPreProc'] as $funcRef) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
						}
					}
				}
				break;
			case 'categories':
				$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.page_uid='".$this->showCatalogFromPage."' and c.status=1 and c.categories_id=cd.categories_id");
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					if ($row['categories_id']) {
						$cats=mslib_fe::Crumbar($row['categories_id']);
						$cats=array_reverse($cats);
						$row['categories_crum']=$cats;
						// get all cats to generate multilevel fake url
						$level=0;
						$where='';
						if (count($cats)>0) {
							foreach ($cats as $item) {
								$where.="categories_id[".$level."]=".$item['id']."&";
								$level++;
							}
							$where=substr($where, 0, (strlen($where)-1));
							$where.='&';
						}
//						$where.='categories_id['.$level.']='.$row['categories_id'];
						// get all cats to generate multilevel fake url eof
						if ($row['categories_url']) {
							$link=$row['categories_url'];
						} else {
							$target="";
							$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
						}
						$row['category_link']=$this->FULL_HTTP_URL.$link;
						$records[]=$row;
					}
				}
				break;
			case 'manufacturers':
				$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_manufacturers m where m.status=1");
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					if ($row['manufacturers_id']) {
						$records[]=$row;
					}
				}
				break;
		}
		// load all products
		if ($this->get['format']=='excel') {
			$excelRows=array();
			if ($excelHeaderCols) {
				$excelRows[]=$excelHeaderCols;
			}
		}
		foreach ($records as $row) {
			if ($this->get['format']=='excel') {
				$excelCols=array();
			}
			$total=count($fields);
			$count=0;
			foreach ($fields as $counter=>$field) {
				$count++;
				if ($this->get['format']=='csv') {
					$content.='"';
				}
				$tmpcontent='';
				switch ($field) {
					case 'categories_id':
						$tmpcontent.=$row['categories_id'];
						break;
					case 'categories_name':
						$tmpcontent.=$row['categories_name'];
						break;
					case 'categories_content_top':
						if ($row['content']) {
							$string=$row['content'];
							if (!$this->get['format']=='excel') {
								$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
							}
							$tmpcontent.=$string;
						}
						break;
					case 'categories_content_bottom':
						if ($row['content_footer']) {
							$string=$row['content_footer'];
							if (!$this->get['format']=='excel') {
								$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
							}
							$tmpcontent.=$string;
						}
						break;
					case 'products_vat_rate':
						$tmpcontent.=($row['tax_rate']*100);
						break;
					case 'categories_meta_title':
						$tmpcontent.=$row['meta_title'];
						break;
					case 'categories_meta_keywords':
						$tmpcontent.=$row['meta_keywords'];
						break;
					case 'categories_meta_description':
						$tmpcontent.=$row['meta_description'];
						break;
					case 'categories_meta_title_1':
					case 'categories_meta_title_2':
					case 'categories_meta_title_3':
					case 'categories_meta_title_4':
					case 'categories_meta_title_5':
						$level=str_replace("categories_meta_title_", '', $field);
						if ($row['categories_crum'][($level-1)]['id']) {
							$filter=array();
							$filter[]='language_id='.$GLOBALS['TSFE']->sys_language_uid;
							$row2=mslib_befe::getRecord($row['categories_crum'][($level-1)]['id'], 'tx_multishop_categories_keywords', 'categories_id', $filter);
							$tmpcontent.=$row2['meta_title'];
						}
						break;
					case 'categories_meta_keywords_1':
					case 'categories_meta_keywords_2':
					case 'categories_meta_keywords_3':
					case 'categories_meta_keywords_4':
					case 'categories_meta_keywords_5':
						$level=str_replace("categories_meta_keywords_", '', $field);
						if ($row['categories_crum'][($level-1)]['id']) {
							$filter=array();
							$filter[]='language_id='.$GLOBALS['TSFE']->sys_language_uid;
							$row2=mslib_befe::getRecord($row['categories_crum'][($level-1)]['id'], 'tx_multishop_categories_keywords', 'categories_id', $filter);
							$tmpcontent.=$row2['meta_keywords'];
						}
						break;
					case 'categories_meta_description_1':
					case 'categories_meta_description_2':
					case 'categories_meta_description_3':
					case 'categories_meta_description_4':
					case 'categories_meta_description_5':
						$level=str_replace("categories_meta_description_", '', $field);
						if ($row['categories_crum'][($level-1)]['id']) {
							$filter=array();
							$filter[]='language_id='.$GLOBALS['TSFE']->sys_language_uid;
							$row2=mslib_befe::getRecord($row['categories_crum'][($level-1)]['id'], 'tx_multishop_categories_description', 'categories_id', $filter);
							$tmpcontent.=$row2['meta_description'];
						}
						break;
					case 'categories_content_top_1':
					case 'categories_content_top_2':
					case 'categories_content_top_3':
					case 'categories_content_top_4':
					case 'categories_content_top_5':
						$level=str_replace("categories_content_top_", '', $field);
						if ($row['categories_crum'][($level-1)]['id']) {
							$filter=array();
							$filter[]='language_id='.$GLOBALS['TSFE']->sys_language_uid;
							$row2=mslib_befe::getRecord($row['categories_crum'][($level-1)]['id'], 'tx_multishop_categories_description', 'categories_id', $filter);
							if ($row2['content']) {
								$string=$row2['content'];
								if (!$this->get['format']=='excel') {
									$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
								}
								$tmpcontent.=$string;
							}
						}
						break;
					case 'categories_name_1':
					case 'categories_name_2':
					case 'categories_name_3':
					case 'categories_name_4':
					case 'categories_name_5':
						$level=str_replace("categories_name_", '', $field);
						if ($row['categories_crum'][($level-1)]['id']) {
							$filter=array();
							$filter[]='language_id='.$GLOBALS['TSFE']->sys_language_uid;
							$row2=mslib_befe::getRecord($row['categories_crum'][($level-1)]['id'], 'tx_multishop_categories_description', 'categories_id', $filter);
							if ($row2['categories_name']) {
								$string=$row2['categories_name'];
								if ($this->get['format']!='excel') {
									$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
								}
								$tmpcontent.=$string;
							}
						}
						break;
					case 'categories_image':
					case 'categories_image_1':
					case 'categories_image_2':
					case 'categories_image_3':
					case 'categories_image_4':
					case 'categories_image_5':
						$level=str_replace("categories_image_", '', $field);
						if ($row['categories_crum'][($level-1)]['id']) {
							$filter=array();
							if ($level) {
								$row2=mslib_befe::getRecord($row['categories_crum'][($level-1)]['id'], 'tx_multishop_categories', 'categories_id', $filter);
							} else {
								$row2=mslib_befe::getRecord($row['categories_id'], 'tx_multishop_categories', 'categories_id', $filter);
							}
							if ($row2['categories_image']) {
								$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row2['categories_image'], 'categories', 'original');
							}
						}
						break;
					case 'categories_content_bottom_1':
					case 'categories_content_bottom_2':
					case 'categories_content_bottom_3':
					case 'categories_content_bottom_4':
					case 'categories_content_bottom_5':
						$level=str_replace("categories_content_bottom_", '', $field);
						if ($row['categories_crum'][($level-1)]['id']) {
							$filter=array();
							$filter[]='language_id='.$GLOBALS['TSFE']->sys_language_uid;
							$row2=mslib_befe::getRecord($row['categories_crum'][($level-1)]['id'], 'tx_multishop_categories_description', 'categories_id', $filter);
							if ($row2['content_footer']) {
								$string=$row2['content_footer'];
								if (!$this->get['format']=='excel') {
									$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
								}
								$tmpcontent.=$string;
							}
						}
						break;
					case 'products_condition':
						$tmpcontent.=$row['products_condition'];
						break;
					case 'products_id':
						$tmpcontent.=$row['products_id'];
						break;
					case 'products_weight':
						$tmpcontent.=$row['products_weight'];
						break;
					case 'manufacturers_advice_price':
						$tmpcontent.=$row['manufacturers_advice_price'];
						break;
					case 'custom_field':
						$tmpcontent.=$fields_values[$counter];
						break;
					case 'products_name':
						$tmpcontent.=$row['products_name'];
						break;
					case 'products_status':
						if ($this->ms['MODULES']['FLAT_DATABASE']) {
							$tmpcontent.='1';
						} else {
							$tmpcontent.=$row['products_status'];
						}
						break;
					case 'products_model':
						$tmpcontent.=$row['products_model'];
						break;
					case 'products_old_price_excluding_vat':
						//$tmpcontent .= $row['products_price'];
						$tmpcontent.=round($row['products_price'], 14);
						break;
					case 'products_old_price':
						$final_price=mslib_fe::final_products_price($row);
						$old_product_price=$row['products_price'];
						if ($row['tax_rate'] and ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT'])) {
							// in this mode the stored prices in the tx_multishop_products are excluding VAT and we have to add it manually
							if ($row['country_tax_rate'] && $row['region_tax_rate']) {
								$country_tax_rate=mslib_fe::taxDecimalCrop($final_price*($row['country_tax_rate']));
								$region_tax_rate=mslib_fe::taxDecimalCrop($final_price*($row['region_tax_rate']));
								$old_product_price=$old_product_price+($country_tax_rate+$region_tax_rate);
							} else {
								$tax_rate=mslib_fe::taxDecimalCrop($row['products_price']*($row['tax_rate']));
								$old_product_price=$old_product_price+$tax_rate;
							}
						}
						if ($old_product_price!=$final_price) {
							//$tmpcontent .= round($old_product_price,14);
							$tmpcontent.=round($old_product_price, 2);
						} else {
							$tmpcontent.='';
						}
						break;
					case 'products_price_excluding_vat':
						$tmpcontent.=round($row['final_price'], 14);
						break;
					case 'products_price':
						$tmpcontent.=mslib_fe::final_products_price($row);
						break;
					case 'product_capital_price':
						if ($this->ms['MODULES']['FLAT_DATABASE']) {
							$row2=mslib_befe::getRecord($row['products_id'], 'tx_multishop_products', 'products_id');
							$tmpcontent.=$row2['product_capital_price'];
						} else {
							$tmpcontent.=$row['product_capital_price'];
						}
						break;
					case 'manufacturers_id':
						$tmpcontent.=$row['manufacturers_id'];
						break;
					case 'manufacturers_name':
						if ($row['manufacturers_name']) {
							$tmpcontent.=$row['manufacturers_name'];
						} elseif (!$row['manufacturers_name'] and $row['manufacturers_id']) {
							$manufacturer=mslib_fe::getManufacturer($row['manufacturers_id']);
							if ($manufacturer['manufacturers_name']) {
								$tmpcontent.=$manufacturer['manufacturers_name'];
							}
						}
						break;
					case 'category_crum_path':
						$tmpcontent.=$row['categories_crum'][0]['name'];
						for ($i=1; $i<6; $i++) {
							if ($row['categories_crum'][$i]['name']) {
								$tmpcontent.=" > ".$row['categories_crum'][$i]['name'];
							}
						}
						break;
					case 'category_link':
						if ($row['category_link']) {
							$tmpcontent.=$row['category_link'];
						}
						break;
					case 'category_level_1':
						if ($row['categories_crum'][0]['name']) {
							$tmpcontent.=$row['categories_crum'][0]['name'];
						}
						break;
					case 'category_level_2':
						if ($row['categories_crum'][1]['name']) {
							$tmpcontent.=$row['categories_crum'][1]['name'];
						}
						break;
					case 'category_level_3':
						if ($row['categories_crum'][2]['name']) {
							$tmpcontent.=$row['categories_crum'][2]['name'];
						}
						break;
					case 'delivery_time':
						$tmpcontent.=$row['delivery_time'];
						break;
					case 'products_shortdescription':
						$string=$row['products_shortdescription'];
						if (!$this->get['format']=='excel') {
							$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
						}
						if ($string) {
							$string=preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}
						break;
					case 'products_description':
						$string=$row['products_description'];
						if (!$this->get['format']=='excel') {
							$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
						}
						if ($string) {
							$string=preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}
						break;
					case 'products_description_encoded':
						$string=$row['products_description'];
						if (!$this->get['format']=='excel') {
							$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
						}
						$string=htmlentities($string);
						if ($string) {
							$string=preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}
						break;
					case 'products_description_strip_tags':
						$string=strip_tags($row['products_description']);
						if (!$this->get['format']=='excel') {
							$string=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $string);
						}
						if ($string) {
							$string=preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}
						break;
					case 'products_external_url':
						if ($row['products_url']) {
							$tmpcontent.=$row['products_url'];
						}
						break;
					case 'products_image_50':
						if ($row['products_image']) {
							$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'], 'products', '50');
						}
						break;
					case 'products_image_100':
						if ($row['products_image']) {
							$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'], 'products', '100');
						}
						break;
					case 'products_image_200':
						if ($row['products_image']) {
							$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'], 'products', '200');
						}
						break;
					case 'products_image_normal':
						if ($row['products_image']) {
							$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'], 'products', 'normal');
						}
						break;
					case 'products_image_original':
						if ($row['products_image']) {
							$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'], 'products', 'original');
						}
						break;
					case 'products_ean':
						$tmpcontent.=$row['ean_code'];
						break;
					case 'products_sku':
						$tmpcontent.=$row['sku_code'];
						break;
					case 'products_quantity':
						$tmpcontent.=$row['products_quantity'];
						break;
					case 'order_unit_name':
						$tmpcontent.=$row['order_unit_name'];
						break;
					case 'products_multiplication':
						$tmpcontent.=$row['products_multiplication'];
						break;
					case 'minimum_quantity':
						$tmpcontent.=$row['minimum_quantity'];
						break;
					case 'maximum_quantity':
						$tmpcontent.=$row['maximum_quantity'];
						break;
					case 'manufacturers_products_id':
						$tmpcontent.=$row['vendor_code'];
						break;
					case 'foreign_products_id':
						if ($this->ms['MODULES']['FLAT_DATABASE']) {
							$row2=mslib_befe::getRecord($row['products_id'], 'tx_multishop_products', 'products_id');
							$row['foreign_products_id']=$row2['foreign_products_id'];
						}
						$tmpcontent.=$row['foreign_products_id'];
						break;
					case 'products_url':
						$where='';
						if ($row['categories_id']) {
							// get all cats to generate multilevel fake url
							$level=0;
							if (count($row['categories_crum'])>0) {
								foreach ($row['categories_crum'] as $cat) {
									$where.="categories_id[".$level."]=".$cat['id']."&";
									$level++;
								}
								$where=substr($where, 0, (strlen($where)-1));
								$where.='&';
							}
							// get all cats to generate multilevel fake url eof
						}
						$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], $where.'&products_id='.$row['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
						$tmpcontent.=$this->FULL_HTTP_URL.$link;
						break;
					case 'products_meta_title':
						$tmpcontent.=$row['products_meta_title'];
						break;
					case 'products_meta_keywords':
						$tmpcontent.=$row['products_meta_keywords'];
						break;
					case 'products_meta_description':
						$tmpcontent.=$row['products_meta_description'];
						break;
					default:
						if ($field) {
							// COMPARE FIELD WITH PRODUCT_IMAGES OR ATTRIBUTES
							$imageKeys=array();
							for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
								if (!$x) {
									$s='';
								} else {
									$s='_'.($x+1);
								}
								$imageKeys[]='products_image_50'.$s;
								$imageKeys[]='products_image_100'.$s;
								$imageKeys[]='products_image_200'.$s;
								$imageKeys[]='products_image_normal'.$s;
								$imageKeys[]='products_image_original'.$s;
							}
							if (count($imageKeys) && in_array($field, $imageKeys)) {
								// we need to print the products image url
								for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
									if (!$x) {
										$s='';
										$y='';
									} else {
										$s='_'.($x+1);
										$y=$x;
									}
									if ($row['products_image'.$y]) {
										if ($field=='products_image_50'.$s) {
											$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'.$y], 'products', '50');
										} elseif ($field=='products_image_100'.$s) {
											$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'.$y], 'products', '100');
										} elseif ($field=='products_image_200'.$s) {
											$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'.$y], 'products', '200');
										} elseif ($field=='products_image_normal'.$s) {
											$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'.$y], 'products', 'normal');
										} elseif ($field=='products_image_original'.$s) {
											$tmpcontent.=$this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'.$y], 'products', 'original');
										}
									}
								}
							} else {
								if ($attributes[$field]) {
									// print it from flat table
									if (!$this->ms['MODULES']['FLAT_DATABASE']) {
										$field_name=$field;
									} else {
										$field_name="a_".str_replace("-", "_", mslib_fe::rewritenamein($attributes[$field]));
										if (!$row[$field_name]) {
											$field_name=$field;
										}
									}
									$tmpcontent.=$row[$field_name];
								}
							}
						}
						break;
				}
				// custom page hook that can be controlled by third-party plugin
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['iterateItemFieldProc'])) {
					$output=$tmpcontent;
					$params=array(
						'mode'=>$mode,
						'field'=>$field,
						'row'=>&$row,
						'output'=>&$output
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['iterateItemFieldProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
					if ($output) {
						$tmpcontent=$output;
					}
				}
				// custom page hook that can be controlled by third-party plugin eof
				if ($this->get['format']!='excel') {
					$tmpcontent=str_replace("\"", "", $tmpcontent);
					if ($feed['plain_text']=='1') {
						$tmpcontent=strip_tags($tmpcontent);
						$tmpcontent=html_entity_decode($tmpcontent);
						$tmpcontent=str_replace(array(
							'&nbsp;',
							'&amp;',
							'&euro;',
							'&amp;quot;',
							'&quot;'
						), array(
							' ',
							'&',
							'EUR',
							"'",
							"'"
						), $tmpcontent);
					}
					// test extra delimiter strip
					if ($feed['delimiter_char']) {
						$tmpcontent=preg_replace("/\r\n|\n|\\".$feed['delimiter_char']."/", " ", $tmpcontent);
					}
				}
				if ($this->get['format']=='excel') {
					$excelCols[]=$tmpcontent;
				}
				$content.=$tmpcontent;
				if ($this->get['format']=='csv') {
					$content.='"';
				}
				if ($count<$total) {
					if ($this->get['format']=='csv') {
						$content.=';';
					} else {
						// add delimiter
						switch ($feed['delimiter']) {
							case 'dash':
								$feed['delimiter_char']='|';
								$content.=$feed['delimiter_char'];
								break;
							case 'dotcomma':
								$feed['delimiter_char']=';';
								$content.=$feed['delimiter_char'];
								break;
							case 'tab':
								$feed['delimiter_char']="\t";
								$content.=$feed['delimiter_char'];
								break;
						}
					}
				}
			}
			// new line
			$content.="\r\n";
			if ($this->get['format']=='excel') {
				$excelRows[]=$excelCols;
			}
		}
		if ($this->get['format']=='excel') {
			require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel.php');
			$objPHPExcel=new PHPExcel();
			$objPHPExcel->getSheet(0)->setTitle('Productfeed');
			$objPHPExcel->getActiveSheet()->fromArray($excelRows);
			$ExcelWriter=new PHPExcel_Writer_Excel2007($objPHPExcel);
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="productfeed.xlsx"');
			$ExcelWriter->save('php://output');
			exit();
		}
		$Cache_Lite->save($content);
	}
	header("Content-Type: text/plain");
	echo $content;
	exit();
}
exit();
?>