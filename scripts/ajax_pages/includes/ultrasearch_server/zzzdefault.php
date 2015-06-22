<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
	$this->ms['MODULES']['CACHE_FRONT_END']=0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']
	);
	$Cache_Lite=new Cache_Lite($options);
	$string=md5($this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'].print_r($this->get, 1).print_r($this->post, 1));
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string))) {
	// product search
	$filter=array();
	$having=array();
	$match=array();
	$orderby=array();
	$where=array();
	$orderby=array();
	$select_total_count=array();
	$select=array();
	if (strlen($this->get['q'])>2) {
		$array=explode(" ", $this->get['q']);
		$total=count($array);
		$oldsearch=0;
		foreach ($array as $item) {
			if (strlen($item)<$this->ms['MODULES']['FULLTEXT_SEARCH_MIN_CHARS']) {
				$oldsearch=1;
				break;
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
				$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."')";
			} else {
				if ($this->ms['MODULES']['REGULAR_SEARCH_MODE']=='keyword%') {
					// do normal indexed search
					$filter[]="(".$tbl."products_name like '".addslashes($this->get['q'])."%')";
				} else {
					// do normal indexed search
					$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."%')";
				}
			}
		} else {
			// do fulltext search
			$tmpstr=addslashes(mslib_befe::ms_implode(', ', $array, '"', '+', true));
			$select[]="MATCH (".$tbl."products_name) AGAINST ('".$tmpstr."' in boolean mode) AS score";
			//$where[]	="MATCH (".$tbl."products_name) AGAINST ('".$tmpstr."' in boolean mode)";
//			$key = 	$this->get['q'];
//			$where[]	=$tbl."products_name LIKE '%$key%' ";	
			$orderby[]='score desc';
		}
	}
	if (is_numeric($this->post['categories_id'])) {
		$parent_id=$this->post['categories_id'];
	}
	if (is_numeric($parent_id) and $parent_id>0) {
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$string='(';
			for ($i=0; $i<4; $i++) {
				if ($i>0) {
					$string.=" or ";
				}
				$string.="pf.categories_id_".$i." = '".$parent_id."'";
			}
			$string.=')';
			if ($string) {
				$filter[]=$string;
			}
			// 
		} else {
			$cats=mslib_fe::get_subcategory_ids($parent_id);
			$cats[]=$parent_id;
			if (is_array($this->post['categories_id_extra'])) {
				$cats=array();
				foreach ($this->post['categories_id_extra'] as $key_id=>$catid) {
					$cats_extra=mslib_fe::get_subcategory_ids($catid);
					$cats[]=$catid;
					$cats=array_merge($cats_extra, $cats);
				}
			}
			$filter[]="p2c.categories_id IN (".implode(",", $cats).")";
		}
	}
	if (is_numeric($this->post['min']) and is_numeric($this->post['max'])) {
		if (!$this->ms['MODULES']['FLAT_DATABASE']) {
			$having[]="(final_price BETWEEN '".$this->post['min']."' and '".$this->post['max']."')";
		} else {
			$filter[]="(pf.final_price BETWEEN '".$this->post['min']."' and '".$this->post['max']."')";
		}
	}
	$from=array();
	if (is_array($this->post['option']) and count($this->post['option'])) {
		// attributes
		if (!$this->ms['MODULES']['FLAT_DATABASE']) {
			$prefix='p.';
		} else {
			$prefix='pf.';
		}
		foreach ($this->post['option'] as $option_id=>$option_values_id) {
			if ($option_id=='or') {
				if (is_array($option_values_id) and count($option_values_id)) {
					foreach ($option_values_id as $key=>$val) {
						if ($key=='range') {
							foreach ($val as $option_id=>$ranges) {
								$from_value=$ranges[0];
								$till_value=$ranges[1];
								$options_name="option".$option_id;
								$options_name=str_replace(array(
									' ',
									'-'
								), '_', $options_name);
								$options_name=str_replace(array(
									'(',
									')',
									'[',
									']',
									"'",
									'"',
									':',
									';',
									'/',
									"\\"
								), '', $options_name);
								$options_name=str_replace('__', '_', $options_name);
								$options_name=addslashes($options_name);
								// this does not work nice with varchar. we need to convert the value to integer first
//								$between_field=$options_name.'_ov.products_options_values_name';
								$between_field='CONVERT(SUBSTRING('.$options_name.'_ov.products_options_values_name, LOCATE(\'-\', '.$options_name.'_ov.products_options_values_name) + 1), SIGNED INTEGER)';
								$subquery='SELECT '.$options_name.'.products_id from tx_multishop_products_attributes '.$options_name.', tx_multishop_products_options_values '.$options_name.'_ov where ('.$between_field.' BETWEEN \''.addslashes($from_value).'\' AND \''.addslashes($till_value).'\' and '.$options_name.'.options_id = "'.addslashes($option_id).'" and '.$options_name.'.options_values_id='.$options_name.'_ov.products_options_values_id) group by '.$options_name.'.products_id';
								$filter[]=$prefix.'products_id IN ('.$subquery.')';
							}
						} elseif (count($val)) {
							$ors=implode(",", $val);
							if ($ors) {
								$options_name="option".$key;
								$options_name=str_replace(array(
									' ',
									'-'
								), '_', $options_name);
								$options_name=str_replace(array(
									'(',
									')',
									'[',
									']',
									"'",
									'"',
									':',
									';',
									'/',
									"\\"
								), '', $options_name);
								$options_name=str_replace('__', '_', $options_name);
								$from[]='tx_multishop_products_attributes '.$options_name;
								$filter[]="(".$prefix."products_id = $options_name.products_id and $options_name.options_id = ".addslashes($key)." and $options_name.options_values_id IN (".$ors."))";
							}
						}
					}
				}
			} elseif (is_numeric($option_values_id) and $option_values_id) {
				$options_name="option".$option_id;
				//echo $options_name;
				$options_name=str_replace(array(
					' ',
					'-'
				), '_', $options_name);
				$options_name=str_replace(array(
					'(',
					')',
					'[',
					']',
					"'",
					'"',
					':',
					';',
					'/',
					"\\"
				), '', $options_name);
				$options_name=str_replace('__', '_', $options_name);
				$from[]='tx_multishop_products_attributes '.$options_name;
				$filter[]="(".$prefix."products_id = $options_name.products_id and $options_name.options_id = ".addslashes($option_id)." and $options_name.options_values_id = ".addslashes($option_values_id).")";
			}
		}
	}
	$limit=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
	if ($this->post['page']) {
		$p=$this->post['page'];
		$offset=$limit*($p-1);
	} else {
		$offset=0;
		$p=1;
	}
	$results=array();
	$results_products=array();
	if (!$this->ms['MODULES']['FLAT_DATABASE']) {
		$prefix='p.';
	} else {
		$prefix='pf.';
	}
	if (!empty($this->post['brands'])) {
		if (is_array($this->post['brands'])) {
			foreach ($this->post['brands'] as $key=>$value) {
				$this->post['brands'][$key]=addslashes($value);
			}
			$filter[]=$prefix."manufacturers_id IN (".implode(",", $this->post['brands']).")";
		} else {
			if (strpos($this->post['brands'], ',')===false) {
				$filter[]=$prefix.'manufacturers_id='.addslashes($this->post['brands']);
			} else {
				$filter[]=$prefix."manufacturers_id IN (".addslashes($this->post['brands']).")";
			}
		}
	}
//	error_log(print_r($post,1));
//	error_log(print_r($filter,1));	
	//JIRA 157 ultrasearch update eof 
//	error_log(print_r($this->post['sort_filter'],1));
	if (is_array($this->post['sort_filter']) and count($this->post['sort_filter'])>0) {
		$test_orderby=$this->post['sort_filter'][0];
	} elseif ($this->post['sort_filter']) {
		$test_orderby=$this->post['sort_filter'];
	}
	if ($test_orderby) {
		switch ($test_orderby) {
			case 'products_name ASC':
			case 'products_name DESC':
				if ($test_orderby=='products_name DESC') {
					$sort='desc';
				} else {
					$sort='asc';
				}
				if (!$this->ms['MODULES']['FLAT_DATABASE']) {
					$prefix='p.';
				} else {
					$prefix='pf.';
				}
				$orderby[]=$prefix.'products_name '.$sort;
				break;
			case 'final_price ASC':
			case 'final_price DESC':
				if ($test_orderby=='final_price DESC') {
					$sort='desc';
				} else {
					$sort='asc';
				}
				$orderby[]='final_price '.$sort;
				break;
		}
	}
	if (!$this->ms['MODULES']['FLAT_DATABASE']) {
		$prefix='pd.';
	} else {
		$prefix='pf.';
	}
	if (!empty($this->post['skeyword'])) {
		$filter[]=$prefix."products_name LIKE '%".addslashes($this->post['skeyword'])."%'";
	}
	$pageset=mslib_fe::getProductsPageSet($filter, $offset, $limit, $orderby, $having, $select, $where, 0, $from, array(), 'ajax_products_search', $select_total_count);
//	error_log($pageset['total_rows']);
//	error_log($this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']);
	if ($pageset['total_rows']>0) {
		$products=$pageset['products'];
		if (count($products)) {
			if ($this->post['skeyword']) {
				mslib_befe::storeProductsKeywordSearch($this->post['skeyword']);
			}
			$totpage=ceil($pageset['total_rows']/$limit);
			foreach ($products as $index=>$product) {
				if ($product['categories_id']) {
					// get all cats to generate multilevel fake url
					$level=0;
					$cats=mslib_fe::Crumbar($product['categories_id']);
					$cats=array_reverse($cats);
					$where='';
					if (count($cats)>0) {
						foreach ($cats as $cat) {
							$where.="categories_id[".$level."]=".$cat['id']."&";
							$level++;
						}
						$where=substr($where, 0, (strlen($where)-1));
						$where.='&';
					}
					// get all cats to generate multilevel fake url eof
				}
				$temp_var_products=array();
				$link_detail=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
				$catlink=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
				$final_price=mslib_fe::final_products_price($product);
				if ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT']) {
					$price_excluding_vat=$this->pi_getLL('excluding_vat').' '.mslib_fe::amount2Cents($product['final_price']);
				} else {
					$price_excluding_vat=false;
				}
				if ($product['products_price']<>$product['final_price']) {
					if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
						$old_price=$product['products_price']*(1+$product['tax_rate']);
					} else {
						$old_price=$product['products_price'];
					}
					$old_price=mslib_fe::amount2Cents($old_price);
					$specials_price=mslib_fe::amount2Cents($final_price);
					$price=false;
				} else {
					$old_price=false;
					$specials_price=false;
					$price=mslib_fe::amount2Cents($final_price);
				}
				$temp_var_products['products_id']=$product['products_id'];
				$temp_var_products['products_shortdescription']=$product['products_shortdescription'];
				$temp_var_products['products_name']=$product['products_name'];
				$temp_var_products['products_model']=$product['products_model'];
				$temp_var_products['categories_name']=$product['categories_name'];
				if ($this->ms['MODULES']['FLAT_DATABASE']) {
					$temp_var_products['categories_name_1']=$product['categories_name_1'];
					$temp_var_products['categories_name_2']=$product['categories_name_2'];
					$temp_var_products['categories_name_3']=$product['categories_name_3'];
				}
				$temp_var_products['link_detail']=$link_detail;
				$temp_var_products['link_add_to_cart']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=shopping_cart&products_id='.$product['products_id']);
				$temp_var_products['add_to_basket']=$this->pi_getLL('add_to_basket');
				$temp_var_products['catlink']=$catlink;
				if ($product['products_image']) {
					$temp_var_products['products_image']=mslib_befe::getImagePath($product['products_image'], 'products', '100');
				}
				if ($product['products_image1']) {
					$temp_var_products['products_image1']=mslib_befe::getImagePath($product['products_image1'], 'products', '100');
				}
				$temp_var_products['manufacturers_name']=$product['manufacturers_name'];
				$temp_var_products['price_excluding_vat']=$price_excluding_vat;
				$temp_var_products['old_price']=$old_price;
				$temp_var_products['special_price']=$specials_price;
				$temp_var_products['price']=$price;
				foreach ($product as $key=>$val) {
					if (strstr($key, "a_")) {
						$temp_var_products[$key]=$val;
					}
				}
				$results_products[]=$temp_var_products;
			}
		} else {
			// no results
			if ($this->post['skeyword']) {
				mslib_befe::storeProductsKeywordSearch($this->post['skeyword'], '1');
			}
		}
	}
	$results['products']=$results_products;
	$results['total_rows']=$pageset['total_rows'];
	$results['pagination']['offset']=$offset;
	$results['pagination']['limit']=$limit;
	$results['pagination']['totpage']=$totpage;
	if ($p==1) {
		$results['pagination']['prev']=false;
		$results['pagination']['first']=false;
	} else {
		$results['pagination']['prev']=$p-1;
		$results['pagination']['prevText']=mslib_befe::strtoupper($this->pi_getLL('previous'));
		$results['pagination']['first']=mslib_befe::strtoupper($this->pi_getLL('first'));
	}
	$results['pagination']['curent_p']=$p;
	if ($totpage==$p) {
		$results['pagination']['next']=false;
		$results['pagination']['last']=false;
	} else {
		$results['pagination']['next']=$p+1;
		$results['pagination']['nextText']=mslib_befe::strtoupper($this->pi_getLL('next'));
		$results['pagination']['last']=mslib_befe::strtoupper($this->pi_getLL('last'));
	}
	$content=json_encode($results, ENT_NOQUOTES);
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($content);
	}
}
header('Content-Type: application/json');
echo $content;
exit();
?>