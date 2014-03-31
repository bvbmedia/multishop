<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
/***************************************************************
 *  Copyright notice
 *  (c) 2010 BVB Media BV - Bas van Beek <bvbmedia@gmail.com>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 * Hint: use extdeveval to insert/update function index above.
 */
class mslib_fe {
	public function init($ref) {
		$this->ref=$ref;
		$this->DOCUMENT_ROOT=$ref->DOCUMENT_ROOT;
		$this->HTTP_HOST=$ref->HTTP_HOST;
		$this->FULL_HTTP_URL=$ref->FULL_HTTP_URL;
		$this->DOCUMENT_ROOT_MULTISHOP=$ref->DOCUMENT_ROOT_MULTISHOP;
		$this->FULL_HTTP_URL_MULTISHOP=$ref->FULL_HTTP_URL_MULTISHOP;
		$this->get= &$ref->get;
		$this->post= &$ref->post;
		$this->conf= &$ref->conf;
		$this->ms= &$ref->ms;
		$this->cObj= &$ref->cObj;
		$this->extKey= &$ref->extKey;
		$this->server= &$ref->server;
		$this->cart_page_uid= &$ref->cart_page_uid;
		$this->shop_pid= &$ref->shop_pid;
		$this->showCatalogFromPage= &$ref->showCatalogFromPage;
		$this->sys_language_uid= &$ref->sys_language_uid;
		$this->ADMIN_USER=$ref->ADMIN_USER;
		$this->ROOTADMIN_USER=$ref->ROOTADMIN_USER;
		$this->tta_user_info=&$ref->tta_user_info;
		$this->tta_shop_info=&$ref->tta_shop_info;
		$this->sys_language_uid=&$ref->sys_language_uid;
		$this->lang=&$ref->lang;
		$this->LLkey=&$ref->LLkey;
		$this->LOCAL_LANG=&$ref->LOCAL_LANG;
		$this->excluded_userGroups=&$ref->excluded_userGroups;
		$this->categoriesStartingPoint=&$ref->categoriesStartingPoint;
		$this->REMOTE_ADDR=&$ref->REMOTE_ADDR;
		$this->cookie=&$ref->cookie;
		$this->initLanguage($ref->LOCAL_LANG);
	}
	/**
	 * @param test admin panel $ms_menu
	 * @param type of the menu $type
	 * @return string
	 */
	public function createAdminPanel($ms_menu, $type='header') {
		$admin_content='';
		$total_tabs=count($ms_menu);
		$tab_counter=0;
		foreach ($ms_menu as $tablevel1_key=>$tablevel1) {
			$tab_counter++;
			$admin_content.='<li class="'.$tablevel1_key.'">';
			if (!$tablevel1['label'] and $tablevel1['description']) {
				$admin_content.=$tablevel1['description'];
			} else {
				if (!is_array($tablevel1['subs'])) {
					if ($tablevel1['link']) {
						$admin_content.='<a href="'.$tablevel1['link'].'"'.$tablevel1['link_params'].'>'.$tablevel1['label'].'</a>';
					} else {
						$admin_content.=$tablevel1['label'];
					}
				} else {
					$total_tablevel2=count($tablevel1['subs']);
					$counter_tablevel2=0;
					if ($tablevel1['link']) {
						$admin_content.='<a href="'.$tablevel1['link'].'"'.$tablevel1['link_params'].'>'.$tablevel1['label'].'</a>';
					} else {
						$admin_content.='<span>'.$tablevel1['label'].'</span>';
					}
					$admin_content.='<ul>';
					foreach ($tablevel1['subs'] as $tablevel2_key=>$tablevel2) {
						$counter_tablevel2++;
						if ($type=='header' and ($counter_tablevel2==$total_tablevel2)) {
							$tablevel2_params='dropdown_bottom';
						} else if ($type=='footer' and ($counter_tablevel2==1)) {
							$tablevel2_params='dropdown_top';
						} else {
							$tablevel2_params='';
						}
						if ($tablevel2['divider']) {
							$admin_content.='<li class="ms_admin_divider"></li>';
							continue;
						}
						if (!is_array($tablevel2['subs'])) {
							$admin_content.='<li class="'.$tablevel2_params.'"><a href="'.$tablevel2['link'].'"'.$tablevel2['link_params'].'>'.$tablevel2['label'].'<span class="ms_admin_menu_item_description">'.$tablevel2['description'].'</span></a></li>';
						} else {
							$admin_content.='<li class="'.$tablevel2_params.' ms_admin_has_subs">';
							$admin_content.='<span>'.$tablevel2['label'].'<span class="ms_admin_menu_item_description">'.$tablevel2['description'].'</span></span>';
							$admin_content.='<ul>';
							$total_tablevel3=count($tablevel2['subs']);
							$counter_tablevel3=0;
							foreach ($tablevel2['subs'] as $tablevel3_key=>$tablevel3) {
								$counter_tablevel3++;
								if ($type=='header' and ($counter_tablevel3==$total_tablevel3)) {
									$tablevel3_params='dropdown_bottom';
								} else if ($type=='footer' and ($counter_tablevel3==1)) {
									$tablevel3_params='dropdown_top';
								} else {
									$tablevel3_params='';
								}
								$admin_content.='<li class="'.$tablevel3_key.'">';
								if ($tablevel3['link']) {
									$admin_content.='<a href="'.$tablevel3['link'].'"'.$tablevel3['link_params'].'>'.$tablevel3['label'].'<span class="ms_admin_menu_item_description">'.$tablevel3['description'].'</span></a>';
								} else {
									$admin_content.='<span>'.$tablevel3['label'].'<span class="ms_admin_menu_item_description">'.$tablevel3['description'].'</span></span>';
								}
								$admin_content.='</li>';
							}
							$admin_content.='</ul></li>';
						}
					}
					$admin_content.='</ul>';
				}
			}
			$admin_content.='</li>';
		}
		return $admin_content;
	}
	public function getProductRelativesBox($product, $type='relatives', $limit=20) {
		$product['products_id']=(int)$product['products_id'];
		$product['categories_id']=(int)$product['categories_id'];
		$filter=array();
		$having=array();
		$match=array();
		$orderby=array();
		$where=array();
		$select=array();
		if ($this->ms['MODULES']['SHOW_PRODUCTS_WITH_IMAGE_FIRST']) {
			if (!$this->ms['MODULES']['FLAT_DATABASE']) {
				$prefix='p.';
			} else {
				$prefix='pf.';
			}
			$tmp_orderby=array();
			$tmp_orderby[]=$prefix.'contains_image desc';
			$tmp_orderby=array_merge($tmp_orderby, $orderby);
			$orderby[]=$tmp_orderby;
		}
		switch ($type) {
			case 'customers_also_bought':
				$product_ids=array();
				$orders=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('orders_id', 'tx_multishop_orders_products', "products_id = '".$product['products_id']."'", 'orders_id');
				foreach ($orders as $order) {
					$products=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('products_id', 'tx_multishop_orders_products', "orders_id = '".$order['orders_id']."' and products_id !='".$product['products_id']."'", '', $limit);
					foreach ($products as $product) {
						$product_ids[]=$product['products_id'];
						if (count($product_ids)==$limit) {
							break;
						}
					}
					if (count($product_ids)==$limit) {
						break;
					}
				}
				$product_ids=array_unique($product_ids);
				if (!count($product_ids)) {
					return false;
				}
				if (!$this->ms['MODULES']['FLAT_DATABASE']) {
					$prefix='p.';
				} else {
					$prefix='';
				}
				$filter[]=$prefix."products_id IN (".implode(',', $product_ids).")";
				break;
			case 'relatives':
				$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('products_id,relative_product_id', 'tx_multishop_products_to_relative_products', "(products_id = '".$product['products_id']."' or relative_product_id = '".$product['products_id']."')", '', $limit);
				$product_ids=array();
				foreach ($data as $item) {
					if ($product['products_id']==$item['relative_product_id']) {
						$product_ids[]=$item['products_id'];
					} else {
						$product_ids[]=$item['relative_product_id'];
					}
					if (count($product_ids)==$limit) {
						break;
					}
				}
				if (!count($product_ids)) {
					return false;
				}
				if (!$this->ms['MODULES']['FLAT_DATABASE']) {
					$prefix='p.';
				} else {
					$prefix='';
				}
				$filter[]=$prefix."products_id IN (".implode(',', $product_ids).")";
				break;
			case 'categories_id':
				if ($this->ms['MODULES']['FLAT_DATABASE']) {
					$filter[]='pf.categories_id='.$product['categories_id'];
				} else {
					$filter[]='c.categories_id='.$product['categories_id'];
				}
				break;
			case 'specials':
				if ($this->ms['MODULES']['FLAT_DATABASE']) {
					$filter[]='pf.sstatus=1';
				} else {
					$filter[]='s.status=1';
				}
				break;
			case 'products_model':
				if (strlen($product['products_model'])>2) {
					$array=explode(" ", $product['products_model']);
					$total=count($array);
					$oldsearch=0;
					foreach ($array as $item) {
						if (strlen($item)<2) {
							$oldsearch=1;
							break;
						}
					}
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						$tbl='pf.';
					} else {
						$tbl='p.';
					}
					if ($oldsearch) {
						// do normal indexed search	
						$filter[]="(".$tbl."products_model like '".addslashes($product['products_model'])."%')";
					} else {
						// do fulltext search
						$tmpstr=addslashes(mslib_befe::ms_implode(', ', $array, '"', '+', true));
						$select[]="MATCH (".$tbl."products_model) AGAINST ('".$tmpstr."' in boolean mode) AS score";
						$where[]="MATCH (".$tbl."products_model) AGAINST ('".$tmpstr."' in boolean mode)";
						$orderby[]='score desc';
					}
				}
				break;
		}
		if (is_numeric($this->get['manufacturers_id'])) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p.';
			}
			$filter[]="(".$tbl."manufacturers_id='".addslashes($this->get['manufacturers_id'])."')";
		}
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$tbl='pf.';
			if ($this->ms['MODULES']['FLAT_DATABASE_ORDER_PRODUCTS_BY_SORT_ORDER']) {
				$orderby[]='pf.sort_order';
			}
		} else {
			$tbl='p.';
		}
		$filter[]="(".$tbl."products_id <> '".$product['products_id']."')";
		if ($this->ms['MODULES']['FLAT_DATABASE'] and count($having)) {
			$filter[]=$having[0];
			unset($having);
		}
		$pageset=mslib_fe::getProductsPageSet($filter, $offset, $limit, $orderby, $having, $select, $where);
		$products=$pageset['products'];
		if ($pageset['total_rows']>0) {
			$content='';
			if ($pageset['total_rows']) {
				if (!$this->ms['MODULES']['PRODUCTS_RELATIVES_TYPE']) {
					$this->ms['MODULES']['PRODUCTS_RELATIVES_TYPE']='default';
				}
				if (strstr($this->ms['MODULES']['PRODUCTS_RELATIVES_TYPE'], "..")) {
					die('error in PRODUCTS_RELATIVES_TYPE value');
				} else {
					if (strstr($this->ms['MODULES']['PRODUCTS_RELATIVES_TYPE'], "/")) {
						require($this->DOCUMENT_ROOT.$this->ms['MODULES']['PRODUCTS_RELATIVES_TYPE'].'.php');
					} else {
						require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_relatives/'.$this->ms['MODULES']['PRODUCTS_RELATIVES_TYPE'].'.php');
					}
				}
			}
		}
		return $content;
	}
	public function final_products_price($product, $quantity=1, $add_currency=1, $ignore_minimum_quantity=0) {
		if (!$ignore_minimum_quantity) {
			if ($quantity and $product['minimum_quantity']>$quantity) {
				// check if the product has a minimum quantity
				$quantity=$product['minimum_quantity'];
				// sum cause we dont want to show the individual price
				$sum=1;
			}
		}
		// hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['finalPriceCalc'])) {
			$params=array(
				'product'=>&$product
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['finalPriceCalc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// hook eof		
		if ($product['staffel_price']) {
			$final_price=(mslib_fe::calculateStaffelPrice($product['staffel_price'], $quantity)/$quantity);
		} else {
			$final_price=($product['final_price']);
		}
		if ($sum and $product['final_price']>0) {
			$final_price=($product['final_price']*$quantity);
		}
		if ($product['tax_rate'] and ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] || $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT'])) {
			// in this mode the stored prices in the tx_multishop_products are excluding VAT and we have to add it manually
			if ($product['country_tax_rate'] && $product['region_tax_rate']) {
				$country_tax_rate=mslib_fe::taxDecimalCrop($final_price*($product['country_tax_rate']));
				$region_tax_rate=mslib_fe::taxDecimalCrop($final_price*($product['region_tax_rate']));
				$final_price=$final_price+($country_tax_rate+$region_tax_rate);
			} else {
				$tax_rate=mslib_fe::taxDecimalCrop($final_price*($product['tax_rate']));
				$final_price=$final_price+$tax_rate;
			}
		}
		if ($add_currency) {
			return mslib_fe::amount2Cents2($final_price);
		} else {
			return $final_price;
		}
	}
	public function final_attributes_price($product, $attributes, $quantity=1, $add_currency=0, $ignore_minimum_quantity=0) {
		$final_price=0;
		foreach ($attributes as $opt_id=>$val_id) {
			$sql="select options_values_price, price_prefix from tx_multishop_products_attributes where options_id = ".$opt_id." and options_values_id = ".$val_id;
			$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
			$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			if ($rs['price_prefix']=='-') {
				$final_price+='-'.$rs['options_values_price'];
			} else {
				$final_price+=$rs['options_values_price'];
			}
		}
		if ($final_price>0) {
			$final_price=($final_price*$quantity);
		}
		if ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT']) {
				// in this mode the stored prices in the tx_multishop_products are excluding VAT and we have to add it manually
				$final_price=$final_price*(1+$product['tax_rate']);
			}
		}
		if ($add_currency) {
			return mslib_fe::amount2Cents2($final_price);
		} else {
			return $final_price;
		}
	}
	public function url_exists($url) {
		$handle=curl_init($url);
		if (false===$handle) {
			return false;
		}
		curl_setopt($handle, CURLOPT_HEADER, false);
		curl_setopt($handle, CURLOPT_FAILONERROR, true); // this works
		curl_setopt($handle, CURLOPT_NOBODY, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
		$connectable=curl_exec($handle);
		curl_close($handle);
		return $connectable;
	}
	public function xml2array($contents, $get_attributes=0) {
		if (!$contents) {
			return array();
		}
		if (!function_exists('xml_parser_create')) {
			//print "'xml_parser_create()' function not found!";
			return array();
		}
		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser=xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $contents, $xml_values);
		xml_parser_free($parser);
		if (!$xml_values) {
			return;
		}
		//Initializations
		$xml_array=array();
		$parents=array();
		$opened_tags=array();
		$arr=array();
		$current= &$xml_array;
		//Go through the tags.
		foreach ($xml_values as $data) {
			unset($attributes, $value); //Remove existing values, or there will be trouble
			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data); //We could use the array by itself, but this cooler.
			$result='';
			if ($get_attributes) { //The second argument of the function decides this.
				$result=array();
				if (isset($value)) {
					$result['value']=$value;
				}
				//Set the attributes too.
				if (isset($attributes)) {
					foreach ($attributes as $attr=>$val) {
						if ($get_attributes==1) {
							$result['attr'][$attr]=$val;
						} //Set all the attributes in a array called 'attr'
						/**  :TODO: should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too */
					}
				}
			} else if (isset($value)) {
				$result=$value;
			}
			//See tag status and do the needed.
			if ($type=="open") { //The starting of the tag '<tag>'
				$parent[$level-1]= &$current;
				if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag]=$result;
					$current= &$current[$tag];
				} else { //There was another element with the same tag name
					if (isset($current[$tag][0])) {
						array_push($current[$tag], $result);
					} else {
						$current[$tag]=array(
							$current[$tag],
							$result
						);
					}
					$last=count($current[$tag])-1;
					$current= &$current[$tag][$last];
				}
			} elseif ($type=="complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if (!isset($current[$tag])) { //New Key
					$current[$tag]=$result;
				} else { //If taken, put all things inside a list(array)
					if ((is_array($current[$tag]) and $get_attributes==0) or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes==1)) {
						array_push($current[$tag], $result); // ...push the new element into that array.
					} else { //If it is not an array...
						$current[$tag]=array(
							$current[$tag],
							$result
						); //...Make it an array using using the existing value and the new value
					}
				}
			} elseif ($type=='close') { //End of tag '</tag>'
				$current= &$parent[$level-1];
			}
		}
		return ($xml_array);
	}
	public function String2Array($var) {
		$ms=array();
		if ($var) {
			$records=explode("\n", trim($var));
			foreach ($records as $record) {
				$record=trim($record);
				preg_match("/^(.*?)\=(.*?)$/", $record, $tmpitem);
				$item=array();
				$item[0]=trim($tmpitem[1]);
				$item[1]=trim($tmpitem[2]);
				if ($item[0]!='keyword' and $item[0]!='label') {
					if (strstr($item[1], ",")) {
						$selected=explode(",", $item[1]);
						$this->ms['parameters'][$item[0]]=array();
						if (count($selected)>1) {
							foreach ($selected as $select) {
								$this->ms['parameters'][$item[0]][]=$select;
							}
						} else {
							$this->ms['parameters'][$item[0]]=$item[1];
						}
					} else {
						$this->ms['parameters'][$item[0]]=$item[1];
					}
				} else {
					$this->ms['parameters'][$item[0]]=$item[1];
				}
			}
		}
		return $this->ms['parameters'];
	}
	public function image_type_to_extension($imagetype, $include_dot=false) {
		if (empty($imagetype)) {
			return false;
		}
		$dot=$include_dot ? $dot.'' : '';
		switch ($imagetype) {
			case IMAGETYPE_GIF        :
				return $dot.'gif';
			case IMAGETYPE_JPEG        :
				return $dot.'jpg';
			case IMAGETYPE_PNG        :
				return $dot.'png';
			case IMAGETYPE_SWF        :
				return $dot.'swf';
			case IMAGETYPE_PSD        :
				return $dot.'psd';
			case IMAGETYPE_WBMP        :
				return $dot.'wbmp';
			case IMAGETYPE_XBM        :
				return $dot.'xbm';
			case IMAGETYPE_TIFF_II    :
				return $dot.'tiff';
			case IMAGETYPE_TIFF_MM    :
				return $dot.'tiff';
			case IMAGETYPE_IFF        :
				return $dot.'aiff';
			case IMAGETYPE_JB2        :
				return $dot.'jb2';
			case IMAGETYPE_JPC        :
				return $dot.'jpc';
			case IMAGETYPE_JP2        :
				return $dot.'jp2';
			case IMAGETYPE_JPX        :
				return $dot.'jpf';
			case IMAGETYPE_SWC        :
				return $dot.'swc';
			case 1    :
				return $dot.'gif';
			case 2    :
				return $dot.'jpg';
			case 3    :
				return $dot.'png';
			case 4    :
				return $dot.'swf';
			case 5    :
				return $dot.'psd';
			case 6    :
				return $dot.'jpg';
			case 7    :
				return $dot.'tiff';
			case 8    :
				return $dot.'tiff';
			case 9    :
				return $dot.'jpc';
			case 10    :
				return $dot.'jp2';
			case 11    :
				return $dot.'jpx';
			case 12    :
				return $dot.'jb2';
			case 13    :
				return $dot.'swc';
			case 14    :
				return $dot.'aiff';
			case 15    :
				return $dot.'wbmp';
			case 16    :
				return $dot.'xbm';
			default    :
				return false;
		}
	}

	////
	// wrapper to in_array() for PHP3 compatibility
	// Checks if the lookup value exists in the lookup array
	public function tep_in_array($lookup_value, $lookup_array) {
		if (function_exists('in_array')) {
			if (in_array($lookup_value, $lookup_array)) {
				return true;
			}
		} else {
			reset($lookup_array);
			while (list($key, $value)=each($lookup_array)) {
				if ($value==$lookup_value) {
					return true;
				}
			}
		}
		return false;
	}
	////
	// Return all HTTP GET variables, except those passed as a parameter
	public function tep_get_all_get_params($exclude_array='', $hidden_fields=0) {
		if ($exclude_array=='') {
			$exclude_array=array();
		}
		// always exclude id and type, cause this is reserved to TYPO3
		$exclude_array[]='id';
		$exclude_array[]='type';
		$get_url='';
		if (is_array($this->get)) {
			$get=$this->get;
			if (!is_array($get['categories_id']) and is_array($_GET['categories_id'])) {
				// dirty bug fixer, cause in application_top_always it resets the aray to deepest categories id, so the pagination url is having broken categories tree
				$get['categories_id']=$_GET['categories_id'];
			}
			reset($get);
			while (list($key, $value)=each($get)) {
				if (!mslib_fe::tep_in_array($key, $exclude_array)) {
					if (!is_array($value)) {
						if ((strlen($value)>0) && ($key!=session_name()) && ($key!='error') && (!mslib_fe::tep_in_array($key, $exclude_array))) {
							if ($hidden_fields) {
								$get_url.='<input name="'.$key.'" type="hidden" value="'.rawurlencode($value).'">'."\n";
							} else {
								$get_url.=$key.'='.rawurlencode(htmlentities($value)).'&';
							}
						}
					} else {
						foreach ($value as $$key=>$$value) {
							if (!is_array($$value)) {
								if ((strlen($$value)>0) && ($key!=session_name()) && ($key!='error')) {
									$string=$key.'['.$$key.']';
									if (!mslib_fe::tep_in_array($string, $exclude_array)) {
										if ($hidden_fields) {
											$get_url.='<input name="'.$key.rawurlencode('['.$$key.']').'" type="hidden" value="'.rawurlencode($$value).'">'."\n";
										} else {
											$get_url.=$key.rawurlencode('['.$$key.']').'='.rawurlencode(htmlentities($$value)).'&';
										}
									}
								}
							} else {
								foreach ($$value as $k=>$v) {
									if (is_array($v)) {
										foreach ($v as $final_key=>$final_value) {
											$string=$key.'['.$$key.']['.$k.']';
											if (!mslib_fe::tep_in_array($string, $exclude_array)) {
												$get_url.=$key.rawurlencode('['.$$key.']').rawurlencode('['.$k.']['.$final_key.']').'='.rawurlencode(htmlentities($final_value)).'&';
											}
										}
									} else {
										if ((strlen($v)>0) && ($key!=session_name()) && ($key!='error')) {
											$string=$key.'['.$$key.']['.$k.']';
											if (!mslib_fe::tep_in_array($string, $exclude_array)) {
												if ($hidden_fields) {
													$get_url.='<input name="'.$key.rawurlencode('['.$$$key.'][]').'" type="hidden" value="'.rawurlencode($v).'">'."\n";
												} else {
													$get_url.=$key.rawurlencode('['.$$key.']['.$k.']').'='.rawurlencode(htmlentities($v)).'&';
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $get_url;
	}
	public function getUser($value, $field='uid') {
		if ($value) {
			if ($field=='code') {
				$field='tx_multishop_code';
			}
			if ($field) {
				$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'fe_users', // FROM ...
					$field.'="'.addslashes($value).'"', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
			} else {
				return 0;
			}
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$tel=0;
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row;
			}
		} else {
			return 0;
		}
	}
	public function getManufacturer($value, $type='manufacturers_id') {
		if ($value) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_manufacturers', // FROM ...
				$type.'="'.$value.'"', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$tel=0;
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row;
			}
		} else {
			return 0;
		}
	}
	public function getGroup($value, $type='title') {
		if ($value==$this->conf['fe_customer_usergroup'] or $value==$this->conf['fe_admin_usergroup'] or $value==$this->conf['fe_rootadmin_usergroup']) {
			return false;
		}
		if ($value) {
			if ($type=='uid') {
				$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'fe_groups', // FROM ...
					'uid="'.$value.'"', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
			} else {
				$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'fe_groups', // FROM ...
					'title="'.$value.'"', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
			}
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$tel=0;
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row;
			}
		} else {
			return 0;
		}
	}
	public function getUsers($groupid, $orderby='company', $include_disabled=0) {
		if (is_numeric($groupid) and $groupid>0) {
			$filter=array();
			if (!$this->masterShop) {
				$filter[]="page_uid='".$this->shop_pid."'";
			}
			$filter[]=$GLOBALS['TYPO3_DB']->listQuery('usergroup', $groupid, 'fe_users');
			if (!$include_disabled) {
				$filter[]="disable=0";
			}
			$filter[]="deleted=0";
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'fe_users', // FROM ...
				implode(" and ", $filter), // WHERE...
				'', // GROUP BY...
				$orderby, // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$tel=0;
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$array=array();
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array[]=$row;
				}
				return $array;
			}
		} else {
			return 0;
		}
	}
	public function getSignedInUsers($groupid='', $orderby='company') {
		$time=(time()-350);
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'fe_users', // FROM ...
			'is_online >= '.$time, // WHERE...
			'', // GROUP BY...
			$orderby, // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$tel=0;
		$array=array();
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$array[]=$row;
			}
		}
		return $array;
	}
	public function ifRootAdmin($uid, $usergroup) {
		if (is_numeric($usergroup)) {
			$admin_group=$usergroup;
		} else {
			die("no admin group defined yet. Please add the admin usergroup to the constants field in the TYPO3 template.");
		}
		if (is_numeric($uid) and $uid>0) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'fe_users', // FROM ...
				'uid=\''.$uid.'\' and '.$GLOBALS['TYPO3_DB']->listQuery('usergroup', $admin_group, 'fe_users'), // WHERE...
				'', // GROUP BY...
				'company', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$tel=0;
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				return 1;
			}
		} else {
			return 0;
		}
	}
	public function rewritenamein($input, $id='', $replaceDashByUnderscore=0) {
		$input=self::normaliza($input);
		if (mb_detect_encoding($input, 'UTF-8', true)=='UTF-8') {
			$input=utf8_decode($input);
		}
		$input=t3lib_div::strtolower($input);
		$input=strip_tags($input);
		$input=str_replace("+", "-", $input);
		$input=preg_replace("/[^[:alnum:]+]/i", "-", $input);
		$input=str_replace("\\", "-", $input);
		$input=preg_replace('/-+/', '-', $input);
		$input=trim($input, '_');
		$input=trim($input, '/');
		$input=trim($input);
		if ($id) {
			$final_file=$input.'-'.$id;
		} else {
			$final_file=$input;
		}
		$final_file=rtrim($final_file, '-');
		if ($replaceDashByUnderscore) {
			$final_file=str_replace('-', '_', $final_file);
		}
		return urlencode($final_file);
	}
	public function normaliza($string) {
		$normalizeChars=array(
			'Š'=>'S',
			'š'=>'s',
			'Ð'=>'Dj',
			'Ž'=>'Z',
			'ž'=>'z',
			'À'=>'A',
			'Á'=>'A',
			'Â'=>'A',
			'Ã'=>'A',
			'Ä'=>'A',
			'Å'=>'A',
			'Æ'=>'A',
			'Ç'=>'C',
			'È'=>'E',
			'É'=>'E',
			'Ê'=>'E',
			'Ë'=>'E',
			'Ì'=>'I',
			'Í'=>'I',
			'Î'=>'I',
			'Ï'=>'I',
			'Ñ'=>'N',
			'Ò'=>'O',
			'Ó'=>'O',
			'Ô'=>'O',
			'Õ'=>'O',
			'Ö'=>'O',
			'Ø'=>'O',
			'Ù'=>'U',
			'Ú'=>'U',
			'Û'=>'U',
			'Ü'=>'U',
			'Ý'=>'Y',
			'Þ'=>'B',
			'ß'=>'Ss',
			'à'=>'a',
			'á'=>'a',
			'â'=>'a',
			'ã'=>'a',
			'ä'=>'a',
			'å'=>'a',
			'æ'=>'a',
			'ç'=>'c',
			'è'=>'e',
			'é'=>'e',
			'ê'=>'e',
			'ë'=>'e',
			'ì'=>'i',
			'í'=>'i',
			'î'=>'i',
			'ï'=>'i',
			'ð'=>'o',
			'ñ'=>'n',
			'ò'=>'o',
			'ó'=>'o',
			'ô'=>'o',
			'õ'=>'o',
			'ö'=>'o',
			'ø'=>'o',
			'ù'=>'u',
			'ú'=>'u',
			'û'=>'u',
			'ý'=>'y',
			'ý'=>'y',
			'þ'=>'b',
			'ÿ'=>'y',
			'ƒ'=>'f',
			'ü'=>'u',
			'š'=>'s',
			'd'=>'d',
			'c'=>'c',
			'c'=>'c',
			'ž'=>'z',
			'Š'=>'s',
			'Ð'=>'d',
			'C'=>'c',
			'C'=>'c',
			'Ž'=>'z'
		);
		$string=strtr($string, $normalizeChars);
		return $string;
	}
	public function SqlDate($sqldatetime) {
		$user_date=date("Y-m-d", strtotime($sqldatetime));
		return $user_date;
	}
	public function orderhasitems($orders_id) {
		$str="select * from orders_products where orders_id='".$orders_id."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			return 1;
		} else {
			return 0;
		}
	}
	public function Money2DutchString($input, $add_currency=1) {
		if (is_numeric($input)) {
			if ($add_currency) {
				$content.=$this->ms['MODULES']['CURRENCY']." ";
			}
			$content.=number_format($input, 2, $this->ms['MODULES']['CURRENCY_ARRAY']['cu_decimal_point'], $this->ms['MODULES']['CURRENCY_ARRAY']['cu_thousands_point']);
			return $content;
		}
	}
	public function Money2PDFDutchString($input, $add_currency=1) {
		if (is_numeric($input)) {
			if ($add_currency) {
				if ($this->ms['MODULES']['CURRENCY_ARRAY']['cu_iso_3']=='EUR') {
					$currency_char=chr(128);
				} else {
					$currency_char=$this->ms['MODULES']['CURRENCY_ARRAY']['cu_symbol_left'];
				}
				$content.=$currency_char." ";
			}
			$content.=number_format($input, 2, ',', '.');
			return $content;
		}
	}
	public function Datetime2Time($indate) {
		//YYYY-MM-DD HH:mm:ss.splits
		$indate=explode(" ", $indate);
		$dateArr=explode("-", $indate[0]);
		$timeArr=explode(":", $indate[1]);
		$timeArr[2]=substr($timeArr[2], 0, strpos($timeArr[2], "."));
		$outdate=mktime($timeArr[0], $timeArr[1], $timeArr[2], $dateArr[1], $dateArr[2], $dateArr[0]);
		return $outdate;
	}
	public function Date2Time($indate) {
		//YYYY-MM-DD HH:mm:ss.splits
		$indate=explode(" ", $indate);
		$dateArr=explode("-", $indate[0]);
		$outdate=mktime('0', '0', '0', $dateArr[1], $dateArr[2], $dateArr[0]);
		return $outdate;
	}
	public function Time2Datetime($sqldatetime) {
		$user_date=date($this->pi_getLL('date_format')." H:i:s", $sqldatetime);
		return $user_date;
	}
	public function Time2DutchDate($sqldatetime) {
		$user_date=strftime("%x %X", $sqldatetime);
		return $user_date;
	}
	public function Time2Date($sqldatetime) {
		$user_date=strftime("%x %X", $sqldatetime);
		return $user_date;
	}
	public function Time2DutchDatetime($sqldatetime) {
		$user_date=date($this->pi_getLL('date_format')." H:i:s", $sqldatetime);
		return $user_date;
	}
	public function DutchDate($sqldatetime) {
		$user_date=strftime("%x %X", strtotime($sqldatetime));
		return $user_date;
	}
	public function DutchTime($sqldatetime) {
		$user_date=strtotime($sqldatetime);
		return $user_date;
	}
	public function getTax($customer_landen_id, $bu_landen_id) {
		if ($customer_landen_id) {
			$str="select * from static_countries where id='".$customer_landen_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$customer_landnaam=$row['naam'];
		}
		if ($bu_landen_id) {
			$str="SELECT * FROM `static_taxes` WHERE `tx_country_iso_nr` ='".$bu_landen_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$tax=$row['tx_rate']*100;
		}
		return $tax;
	}
	public function getTaxById($id) {
		$str="SELECT * FROM `static_taxes` WHERE `uid` ='".$id."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$tax=($row['tx_rate']*100);
		return $tax;
	}
	public function getTaxByPercentage($value) {
		//TODO: needs v3 update
		$str="SELECT * FROM `static_taxes` WHERE `tx_country_iso_nr` ='".addslashes($this->ms['MODULES']['COUNTRY_ISO_NR'])."' and tx_rate='".($value/100)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			return $row['uid'];
		}
	}
	public function getTaxGroupByName($string) {
		$str="SELECT * FROM `tx_multishop_tax_rule_groups` WHERE `name` ='".addslashes($string)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			return $row;
		}
	}
	public function getTaxGroupById($string) {
		$str="SELECT * FROM `tx_multishop_tax_rule_groups` WHERE `rules_group_id` ='".addslashes($string)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			return $row;
		}
	}
	public function getCountryByIso($cn_iso_nr) {
		$str="select * from static_countries where cn_iso_nr='".addslashes($cn_iso_nr)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row;
	}
	public function getCountryByName($english_name) {
		$str="select * from static_countries where cn_short_en='".addslashes($english_name)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row;
	}
	public function getCountryCnIsoByEnglishName($english_name) {
		// returns NL, BE, DE etc
		$str="select cn_iso_2 from static_countries where cn_short_en='".addslashes($english_name)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row['cn_iso_2'];
	}
	public function getCountryName($cn_iso_nr) {
		$str="select * from static_countries where cn_iso_nr='".addslashes($cn_iso_nr)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row['cn_iso_2'];
	}
	public function getCountryByCode($cn_iso_2) {
		//cn_iso_2
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'static_countries', // FROM ...
			'cn_iso_2=\''.addslashes($cn_iso_2).'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['cn_short_en'];
	}
	/*
		Example input:
		language_code=de
		english_name=Germany
		Output:
		Deutschland (if static_info_tables_de is configured properly)
		Fallback output: Germany (if static_info_tables_de is not installed)
	*/
	public function getTranslatedCountryNameByEnglishName($language_code, $english_name) {
		$str="select * from static_countries where cn_short_en='".addslashes($english_name)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if ($row['cn_short_'.$language_code]) {
			return $row['cn_short_'.$language_code];
		} else {
			return $row['cn_short_en'];
		}
	}
	public function getEnglishCountryNameByTranslatedName($language_code, $translated_name) {
		$str="select * from static_countries where cn_short_".$language_code."='".addslashes($translated_name)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row['cn_short_en'];
	}
	public function getCityName($id) {
		$str="select * from plaatsen where id='".$id."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row['naam'];
	}
	public function getCityId($name) {
		$str="select * from plaatsen where naam='".$name."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row['id'];
	}
	public function getRegionByName($english_name) {
		$str="select * from static_country_zones where zn_name_local='".addslashes($english_name)."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row;
	}
	public function replace_uri($str) {
		$pattern='#(^|[^\"=]{1})(http://|ftp://|mailto:|news:)([^\s<>]+)([\s\n<>]|$)#sm';
		return preg_replace($pattern, "\\1<a href=\"\\2\\3\" target=\"_blank\"><u>\\2\\3</u></a>\\4", $str);
	}
	public function typolink($page_id='', $vars='', $manual_link=0) {
		if ($vars and preg_match("/^&/", $vars)) {
			$vars=substr($vars, 1, strlen($vars));
		}
		$conf=array();
		if ($page_id and strstr($page_id, ",")) {
			$array=explode(",", $page_id);
			if (!$array[0]) {
				$page_id=$GLOBALS["TSFE"]->id.','.$array[1];
			}
			$conf['type']=$array[1];
		} else if (!$page_id) {
			$page_id=$GLOBALS["TSFE"]->id;
		}
		$conf['parameter']=$page_id;
//		$conf['useCacheHash']=1; // dont use
		if ($vars) {
			// xss protection
			//$vars='';
			//$vars=mslib_fe::RemoveXSS($vars);
			$conf['additionalParams']='&'.$vars;
		}
		$conf['returnLast']='url'; // get it as URL
		if ($manual_link) {
			// dont use cObj typolink method (which makes realurl/cooluri version of the link), but instead make manual link
			if (strstr($page_id, ',')) {
				$array=explode(",", $page_id);
				$url='index.php?id='.$array[0].'&type='.$array[1].$conf['additionalParams'];
			} else {
				$url='index.php?id='.$page_id.$conf['additionalParams'];
			}
		} else {
			//	$url = htmlspecialchars($GLOBALS["TSFE"]->cObj->typolink(NULL, $conf));	
			$url=$GLOBALS["TSFE"]->cObj->typolink(NULL, $conf);
		}
		return $url;
	}
	public function loggedin() {
		if ($GLOBALS['TSFE']->fe_user->user['uid']) {
			return 1;
		} else {
			return 0;
		}
	}
	public function getTypoContent($pid='', $uid='') {
		$filter=array();
		if (is_numeric($uid)) {
			$filter[]='t.uid=\''.$uid.'\'';
		}
		if (is_numeric($pid)) {
			$filter[]='t.pid=\''.$pid.'\'';
		}
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('t.*', 'tt_content t', implode(' AND ', $filter), '', '', '');
		if ($res) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		}
		//	return $this->div->parseFunc($row['title'], $this->cObj, $this->conf['label.']['parse']); // return title
		return mslib_fe::renderTypoContent($row['bodytext']);
	}
	public function string2url($input) {
		$input=preg_replace("`((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"http\\3://\\5\\6\\8\\9\" target=\"_blank\" title=\"\\0\">\\5\\6</a>", $input);
		return $input;
	}
	public function renderTypoContent($content) {
		$parseHTML=new t3lib_parsehtml_proc();
		$message=$parseHTML->TS_links_rte($content);
		return $message;
	}
	public function mailFeUser($user, $subject, $content, $from_address='noreply@typo3multishop.com', $from_name='TYPO3 Multishop') {
		if ($user['email']) {
			$mail=new PHPMailer();
			$mail->CharSet='UTF-8';
			$mail->Encoding='base64';
			if ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport']=='smtp') {
				$mail->IsSMTP();
				$mail->Host=$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'];
				if (isset($GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_username'])) {
					$mail->SMTPAuth=true;
					$mail->Username=$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_username'];
					$mail->Password=$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_password'];
				}
			}
			if ($this->conf['email_tmpl_path']) {
				$template=$this->cObj->fileResource($this->conf['email_tmpl_path']);
			} else {
				$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath('multishop').'templates/email_template.tmpl');
			}
			$markerArray=array();
			$markerArray['###BODY###']=$content;
			$body=$this->cObj->substituteMarkerArray($template, $markerArray);
			$mail->SetFrom($from_address, $from_name);
			$mail->AddAddress($user['email'], $user['username']);
			$mail->Subject=$subject;
			$mail->AltBody="To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			self::MsgHTMLwithEmbedImages($mail, $body);
//			$mail->MsgHTML($body,$this->DOCUMENT_ROOT);
			if (!$mail->Send()) {
				return 0;
			} else {
				return 1;
			}
		} else {
			return 0;
		}
	}
	public function getUsersByGroup($group_id) {
		if (is_numeric($group_id)) {
			$additional_where=array();
			$additional_where[]='FIND_IN_SET(\''.$group_id.'\',usergroup) > 0';
			$users=mslib_befe::getRecords(0, 'fe_users', 'disable', $additional_where);
			return $users;
		}
	}
	public function mailFeGroup($group_id, $subject, $body, $from_address='noreply@mysite.com', $from_name='TYPO3 Multishop') {
		if (!is_numeric($group_id)) {
			return false;
		}
		$users=mslib_befe::getUsersByGroup($group_id);
		if (is_array($users) and count($users)) {
			foreach ($users as $user) {
				mslib_fe::mailUser($user, $subject, $body, $from_address, $from_name);
			}
		}
	}
	public function mailUser($user, $subject, $content, $from_address='noreply@mysite.com', $from_name='TYPO3 Multishop', $attachments=array()) {
		if ($user['email']) {
			$mail=new PHPMailer();
			$mail->CharSet='UTF-8';
			$mail->Encoding='base64';
			if ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport']=='smtp') {
				$mail->IsSMTP();
				$mail->Host=$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'];
				if (isset($GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_username'])) {
					$mail->SMTPAuth=true;
					$mail->Username=$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_username'];
					$mail->Password=$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_password'];
				}
			}
			//		$mail->IsSendmail(); // telling the class to use SendMail transport
			if ($this->conf['email_tmpl_path']) {
				$template=$this->cObj->fileResource($this->conf['email_tmpl_path']);
			} else {
				$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath('multishop').'templates/email_template.tmpl');
			}
			$markerArray=array();
			$markerArray['###BODY###']=$content;
			$body=$this->cObj->substituteMarkerArray($template, $markerArray);
			// try to change URL images to embedded		
			$mail->SetFrom($from_address, $from_name);
			if (count($attachments)) {
				foreach ($attachments as $path) {
					if ($path and is_file($path)) {
						$mail->AddAttachment($path);
					}
				}
			}
			$mail->AddAddress($user['email'], $user['username']);
			$mail->Subject=$subject;
			$mail->AltBody="To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			self::MsgHTMLwithEmbedImages($mail, $body);
//			$mail->MsgHTML($body,$this->DOCUMENT_ROOT);
			if (!$mail->Send()) {
				return 0;
			} else {
				return 1;
			}
		} else {
			return 0;
		}
	}
	public function MsgHTMLwithEmbedImages(&$mail, $body) {
		// get all img tags
		preg_match_all('/<img.*?>/', $body, $matches);
		if (!isset($matches[0])) {
			return;
		}
		// foreach tag, create the cid and embed image
		$i=1;
		foreach ($matches[0] as $img) {
			// make cid
			$id='img'.($i++);
			// replace image web path with local path
			preg_match('/src="(.*?)"/', $img, $m);
			if (!isset($m[1])) {
				continue;
			}
			$arr=parse_url($m[1]);
			if ($arr['host']==$this->HTTP_HOST and (!isset($arr['host']) || !isset($arr['path']))) {
				continue;
			}
			// add
			$mail->AddEmbeddedImage($this->DOCUMENT_ROOT.preg_replace("/^\//", "", $arr['path']), $id, 'attachment', 'base64', 'image/jpeg');
			$body=str_replace($img, '<img alt="" src="cid:'.$id.'" style="border: none;" />', $body);
		}
		$mail->MsgHTML($body, $this->DOCUMENT_ROOT);
	}
	public function convertTime($dformat, $sformat, $ts) {
		extract(strptime($ts, $sformat));
		return strftime($dformat, mktime(intval($tm_hour), intval($tm_min), intval($tm_sec), intval($tm_mon)+1, intval($tm_mday), intval($tm_year)+1900));
	}
	public function ifMobile($USER_AGENT) {
		$types=array();
		$types[]='DoCoMo';
		$types[]='J-PHONE';
		$types[]='KDDI';
		$types[]='UP.Browser';
		$types[]='DDIPOCKET';
		$types[]='SymbianOS';
		$types[]='iPhone';
		$types[]='IEMobile';
		foreach ($types as $type) {
			if (strstr($USER_AGENT, $type)) {
				return 1;
			}
		}
	}
	public function getCountry($id) {
		if (!is_numeric($id)) {
			return false;
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'static_countries', // FROM ...
			'id="'.$id.'"', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$tel=0;
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row;
		} else {
			return 0;
		}
	}
	public function Crumbar($c, $languages_id='', $output=array()) {
		if (is_numeric($c)) {
			if ($this->ms['MODULES']['CACHE_FRONT_END']) {
				if (!isset($this->ms['MODULES']['CACHE_TIME_OUT_CRUM'])) {
					$this->ms['MODULES']['CACHE_TIME_OUT_CRUM']=$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES'];
				}
				if (!count($output) && $this->ms['MODULES']['CACHE_TIME_OUT_CRUM']) {
					$CACHE_FRONT_END=1;
				} else {
					$CACHE_FRONT_END=0;
				}
			} else {
				$CACHE_FRONT_END=0;
			}
			if ($CACHE_FRONT_END) {
				$this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_CRUM'];
				$options=array(
					'caching'=>true,
					'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
					'lifeTime'=>$this->cacheLifeTime
				);
				$Cache_Lite=new Cache_Lite($options);
				$string=$this->cObj->data['uid'].'_crum_'.$c.'_'.$languages_id.'_'.md5(serialize($output));
			}
			if (!$CACHE_FRONT_END || ($CACHE_FRONT_END && !$content=$Cache_Lite->get($string))) {
				$sql="select c.status, c.custom_settings, c.categories_id, c.parent_id, cd.categories_name, cd.meta_title, cd.meta_description from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id = ".$c." and cd.language_id='".$this->sys_language_uid."' and c.categories_id = cd.categories_id";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
					$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					if ($data['categories_name']) {
						$output[]=array(
							'name'=>$data['categories_name'],
							'url'=>mslib_fe::rewritenamein($data['categories_name'], 'cat', $data['categories_id']),
							'id'=>$data['categories_id'],
							'custom_settings'=>$data['custom_settings'],
							'meta_title'=>$data['meta_title'],
							'meta_description'=>$data['meta_description'],
							'status'=>$data['status']
						);
					}
					if ($data['parent_id']>0 && $data['parent_id']<>$this->categoriesStartingPoint) {
						if ($data['categories_id']==$data['parent_id']) {
							echo 'crumbar is looping.';
							die();
						} else {
							$output=mslib_fe::Crumbar($data['parent_id'], '', $output);
						}
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($qry);
				}
				if ($CACHE_FRONT_END) {
					$Cache_Lite->save(serialize($output));
				}
			} else {
				$output=unserialize($content);
			}
		}
		return $output;
	}
	public function showAttributes($products_id, $add_tax_rate='', $sessionData=array(), $readonly=0, $hide_prices=0, $returnAsArray=0) {
		if (!is_numeric($products_id)) {
			return false;
		}
		//hook to let other plugins further manipulate the query
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionNamesPreProc'])) {
			$params=array(
				'this'=>&$this,
				'products_id'=>&$products_id,
				'readonly'=>&$readonly,
				'hide_prices'=>&$hide_prices,
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionNamesPreProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$required_formfields=array();
		if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$add_tax_rate='';
		}
		if (!$sessionData) {
			$sessionData=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
			$sessionData=$sessionData['products'][$this->get['tx_multishop_pi1']['cart_item']];
		}
		//and popt.language_id = '" . $languages_id . "' 
		$option_value_counter=0;
		$json_attributes='{';
		$query_array=array();
		$query_array['select'][]='popt.required';
		$query_array['select'][]='popt.products_options_id';
		$query_array['select'][]='popt.products_options_name';
		$query_array['select'][]='popt.listtype';
		$query_array['select'][]='popt.listtype';
		$query_array['from'][]='tx_multishop_products_options popt';
		$query_array['from'][]='tx_multishop_products_attributes patrib';
		$query_array['where'][]='patrib.products_id=\''.(int)$products_id.'\'';
		$query_array['where'][]='popt.language_id = \''.$this->sys_language_uid.'\'';
		$query_array['where'][]='patrib.options_id = popt.products_options_id';
		$query_array['group_by'][]='popt.products_options_id';
		$query_array['order_by'][]='popt.sort_order';
		//hook to let other plugins further manipulate the query
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionNamesQuery'])) {
			$params=array(
				'this'=>&$this,
				'products_id'=>$products_id,
				'query_array'=>&$query_array
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionNamesQuery'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
			(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
			(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
			(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
			(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
			(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$total_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($returnAsArray) {
			$returnAsArrayData=array();
			if ($total_rows>0) {
				while ($options=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					$returnAsArrayData[$options['products_options_id']]=$options;
					// now get the values
					$str="select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.options_values_id, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pa.products_id = '".(int)$products_id."' and pa.options_id = '".$options['products_options_id']."' and pov.language_id = '".$this->sys_language_uid."' and pa.options_values_id = pov.products_options_values_id and povp.products_options_id='".$options['products_options_id']."' and povp.products_options_values_id=pov.products_options_values_id order by povp.sort_order";
					$products_options=$GLOBALS['TYPO3_DB']->sql_query($str);
					$total_values=$GLOBALS['TYPO3_DB']->sql_num_rows($products_options);
					if ($total_values) {
						while ($products_options_values=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_options)) {
							$returnAsArrayData[$options['products_options_id']]['values'][]=$products_options_values;
						}
					}
				}
			}
			return $returnAsArrayData;
		}
		if ($total_rows>0) {
			$output='';
			$output_html=array();
			$next_index=0;
			while ($options=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$load_default=0;
				switch ($options['listtype']) {
					case 'divider':
						$output_html[$options['products_options_id']].='<div class="opties-field-attribute'.$options['products_options_id'].' opties-field-radio opties-field-divider" id="attribute_item_wrapper_'.$options['products_options_id'].'">
						<label></label>
						<div class="hr"></div>
						</div>';
						$load_default=0;
						break;
					case 'input':
						$output_html[$options['products_options_id']].='<div class="opties-field-attribute'.$options['products_options_id'].' opties-field-radio opties-field-input" id="attribute_item_wrapper_'.$options['products_options_id'].'">
						<label>'.$options['products_options_name'].':</label>
						<input type="text" name="attributes['.$options['products_options_id'].']" id="attributes'.$options['products_options_id'].'" value="'.$sessionData['attributes'][$options['products_options_id']]['products_options_values_name'].'" '.($options['required'] ? 'required="required"' : '').' />
						</div>';
						$load_default=0;
						break;
					case 'textarea':
						$output_html[$options['products_options_id']].='<div class="opties-field-attribute'.$options['products_options_id'].' opties-field-radio opties-field-textarea" id="attribute_item_wrapper_'.$options['products_options_id'].'">
						<label>'.$options['products_options_name'].':</label>
						<textarea name="attributes['.$options['products_options_id'].']" id="attributes'.$options['products_options_id'].'" '.($options['required'] ? 'required="required"' : '').'>'.htmlspecialchars($sessionData['attributes'][$options['products_options_id']]['products_options_values_name']).'</textarea>
						</div>';
						$load_default=0;
						break;
					case 'hidden_field':
						$output_html[$options['products_options_id']].='<div class="opties-field-attribute'.$options['products_options_id'].' opties-field-radio opties-field-textarea" id="attribute_item_wrapper_'.$options['products_options_id'].'">
						<input type="hidden" name="attributes['.$options['products_options_id'].']" id="attributes'.$options['products_options_id'].'" value="'.$sessionData['attributes'][$options['products_options_id']]['products_options_values_name'].'" />						
						</div>';
						$load_default=0;
						break;
					case 'file':
						$output_html[$options['products_options_id']].='<div class="opties-field-attribute'.$options['products_options_id'].' opties-field-radio opties-field-input" id="attribute_item_wrapper_'.$options['products_options_id'].'">
						<label>'.$options['products_options_name'].':</label>
						<div id="attribute_item_wrapper">
						<input type="file" name="attributes['.$options['products_options_id'].']" id="attributes'.$options['products_options_id'].'" '.($options['required'] ? 'required="required"' : '').' />
						</div>
						</div>';
						$load_default=0;
						break;
					case 'radio':
						$class='opties-field-attribute'.$options['products_options_id'].' opties-field-radio';
						$load_default=1;
						break;
					case 'checkbox':
						$class='opties-field-attribute'.$options['products_options_id'].' opties-field-radio opties-field-checkbox';
						$load_default=1;
						break;
					default:
						$load_default=1;
						//hook to let other plugins further manipulate the listypes
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionNameItemHook'])) {
							$params=array(
								'load_default'=>&$load_default,
								'products_id'=>$products_id,
								'options'=>&$options,
								'class'=>&$class,
								'output_html'=>&$output_html
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionNameItemHook'] as $funcRef) {
								t3lib_div::callUserFunction($funcRef, $params, $this);
							}
						}
						if ($load_default) {
							$load_default=1;
							$class='opties-field-attribute'.$options['products_options_id'].' opties-field-radio';
						}
						break;
				}
				if ($load_default) {
					if ($readonly) {
						$output_html[$options['products_options_id']].='<ul>';
					}
					// now get the values
					$str="select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.options_values_id, pa.price_prefix from tx_multishop_products_attributes pa, tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp where pa.products_id = '".(int)$products_id."' and pa.options_id = '".$options['products_options_id']."' and pov.language_id = '".$this->sys_language_uid."' and pa.options_values_id = pov.products_options_values_id and povp.products_options_id='".$options['products_options_id']."' and povp.products_options_values_id=pov.products_options_values_id order by povp.sort_order";
					//echo $str;
					//die();
					$products_options=$GLOBALS['TYPO3_DB']->sql_query($str);
					$total_values=$GLOBALS['TYPO3_DB']->sql_num_rows($products_options);
					if (!$readonly) {
						$output_html[$options['products_options_id']].='<div class="'.$class.'" id="attribute_item_wrapper_'.$options['products_options_id'].'"><label>'.$options['products_options_name'].':</label><div id="attribute_item_wrapper">';
					} else {
						$output_html[$options['products_options_id']].='<li><label>'.$options['products_options_name'].':</label> ';
					}
					// SHOW_ATTRIBUTE_DESCRIPTION
					if (SHOW_ATTRIBUTE_DESCRIPTION && !empty($products_options_name_values['description'])) {
						$output_html[$options['products_options_id']].=$products_options_name_values['description']."<br/>";
					}
					$opt=0;
					$json_attributes.=($next_index>0 ? "," : "").$options['products_options_id'].':{';
					$next_index++;
					$next_index2=0;
					$items='';
					$options_values=array();
					while ($products_options_values=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_options)) {
						$options_values[]=$products_options_values;
						// hook for manipulating the $products_options_values array
						// hook to let other plugins further manipulate the option values display
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['attributesArray'])) {
							$params=array(
								'options'=>&$options,
								'products_options_values'=>&$products_options_values,
								'products_id'=>&$products_id
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['attributesArray'] as $funcRef) {
								t3lib_div::callUserFunction($funcRef, $params, $this);
							}
						}
						// hook
						if ($hide_prices) {
							$products_options_values['options_values_price']=0;
						}
						$option_value_counter++;
						if ($add_tax_rate) {
							// add vat
							$products_options_values['options_values_price']=round($products_options_values['options_values_price']*(1+$add_tax_rate), 2);
						}
						// print_r($products_options_values);
						if (!$readonly) {
							if ($total_values<2 and $options['listtype']!='checkbox') {
								$label='<span class="attributes-values">'.$products_options_values['products_options_values_name'].'</span>';
								if ($products_options_values['options_values_price']!='0') {
									$label.=' ('.$products_options_values['price_prefix'].' '.mslib_fe::currency().mslib_fe::amount2Cents2($products_options_values['options_values_price']).')';
								}
								$output_html[$options['products_options_id']].="\n".$label.'<input name="attributes['.$options['products_options_id'].']" id="attributes'.$options['products_options_id'].'" type="hidden" value="'.$products_options_values['products_options_values_id'].'" /></li>';
							} else {
								switch ($options['listtype']) {
									case 'radio':
										$items.="\n".'
										<div class="attribute_item" id="attribute_item_wrapper_'.$options['products_options_id'].'_'.$products_options_values['products_options_values_id'].'">
										<label for="attributes'.$options['products_options_id'].'_'.$option_value_counter.'"><span class="attribute_value_label">'.$products_options_values['products_options_values_name'].'</span></label>										
										<input name="attributes['.$options['products_options_id'].'][]" id="attributes'.$options['products_options_id'].'_'.$option_value_counter.'" type="radio" value="'.$products_options_values['products_options_values_id'].'"';
										if (count($sessionData['attributes'][$options['products_options_id']])) {
											foreach ($sessionData['attributes'][$options['products_options_id']] as $item) {
												if ($item['products_options_values_id']==$products_options_values['products_options_values_id']) {
													$items.=' checked';
												}
											}
										}
										$items.=' class="PrettyInput" '.($options['required'] ? 'required="required"' : '').' />
										<div class="attribute_item_price">';
										if ($products_options_values['options_values_price']!='0') {
											$items.=$products_options_values['price_prefix'].' '.mslib_fe::currency().mslib_fe::amount2Cents2($products_options_values['options_values_price']);
										}
										$items.='</div></div>';
										break;
									case 'checkbox':
										$items.="\n".'
										<div class="attribute_item" id="attribute_item_wrapper_'.$options['products_options_id'].'_'.$products_options_values['products_options_values_id'].'">
										<label for="attributes'.$options['products_options_id'].'_'.$option_value_counter.'">'.$products_options_values['products_options_values_name'].'</label>										
										<input name="attributes['.$options['products_options_id'].'][]" id="attributes'.$options['products_options_id'].'_'.$option_value_counter.'" type="checkbox" value="'.$products_options_values['products_options_values_id'].'"';
										if (count($sessionData['attributes'][$options['products_options_id']])) {
											foreach ($sessionData['attributes'][$options['products_options_id']] as $item) {
												if ($item['products_options_values_id']==$products_options_values['products_options_values_id']) {
													$items.=' checked';
												}
											}
										}
										$items.=' class="PrettyInput" '.($options['required'] ? 'required="required"' : '').' />
										<div class="attribute_item_price">';
										if ($products_options_values['options_values_price']!='0') {
											$items.=$products_options_values['price_prefix'].' '.mslib_fe::currency().mslib_fe::amount2Cents2($products_options_values['options_values_price']);
										}
										$items.='</div></div>';
										break;
									default:
										$items.="\n".'<option value="'.$products_options_values['products_options_values_id'].'" ';
										if (($sessionData['attributes'][$options['products_options_id']]['products_options_values_id']==$products_options_values['products_options_values_id'])) {
											$selected=1;
											$items.=' SELECTED';
										}
										$aantal=strlen($products_options_values['products_options_values_name']);
										$t="";
										if ($products_options_values['options_values_price']!='0') {
											$t=' ('.$products_options_values['price_prefix'].' '.mslib_fe::currency().mslib_fe::amount2Cents2($products_options_values['options_values_price']).')&nbsp';
											$aantal=$aantal+strlen($t);
										}
										$x=62-$aantal;
										$x=INFO_SELECTBOX_OPTIONS_SPACE-$aantal;
										$spaces='';
										for ($i=0; $i<=$x; $i++) {
											$spaces.='&nbsp;';
										}
										$items.='>'.$products_options_values['products_options_values_name'].$spaces.$t;
										$items.='</option>';
										break;
								}
							}
							$json_attributes.=($next_index2>0 ? "," : "").$products_options_values['products_options_values_id'].':{"prefix":"'.$products_options_values['price_prefix'].'","val":'.$products_options_values['options_values_price'].'}';
						} else {
							if (($sessionData['attributes'][$options['products_options_id']]==$products_options_values['products_options_values_id'])) {
								$items.=$products_options_values['products_options_values_name'].'</li>';
							}
						}
						$next_index2++;
					}
					$json_attributes.='}';
					if ($total_values>0) {
						if (!$readonly) {
							switch ($options['listtype']) {
								case 'input':
								case 'checkbox':
								case 'radio':
									break;
								default:
									if ($total_values>1) {
										$html='';
										$html.='<select name="attributes['.$options['products_options_id'].']" id="attributes'.$options['products_options_id'].'" '.($options['required'] ? 'required="required"' : '').'>';
										if ($options['required']) {
											//$required_formfields[]='attributes'.$options['products_options_id'];
											$html.='<option value="">'.$this->pi_getLL('choose_selection').'</option>';
										}
										$items=$html.$items.'</select>'."\n";
									}
									break;
							}
							$output_html[$options['products_options_id']].=$items;
						}
					}
					if (!$readonly) {
						$output_html[$options['products_options_id']].='</div></div>'."\n";
					}
					if ($readonly) {
						$output_html[$options['products_options_id']].='</ul>';
					}
					// hook to let other plugins further manipulate the option values display
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['ShowAttributesLoadDefaultOutputHTML'])) {
						$params=array(
							'load_default'=>&$load_default,
							'products_id'=>$products_id,
							'options'=>&$options,
							'readonly'=>$readonly,
							'options_values'=>&$options_values,
							'output'=>&$output,
							'output_html'=>&$output_html
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['ShowAttributesLoadDefaultOutputHTML'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					// hook
				} else {
					// hook to let other plugins further manipulate the option values display
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionValuesHook'])) {
						$params=array(
							'load_default'=>&$load_default,
							'products_id'=>$products_id,
							'options'=>&$options,
							'readonly'=>$readonly,
							'output'=>&$output,
							'output_html'=>&$output_html
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['showAttributesOptionValuesHook'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					// hook	
				}
			}
			$output.='<div class="products_attributes"><h2>'.$this->pi_getLL('product_options').'</h2>';
			$output.=implode("\n", $output_html);
			$output.='</div>';
			/* if (count($required_formfields)) {
				$output.='			
				<script type="text/javascript">
				jQuery(function($){';
				//foreach ($required_formfields as $formfield) {
					$output.='
					//$("#'.$formfield.'").validate({
					 // expression: "if (!isNaN(VAL) && VAL) return true; else return false;",
						message: "'.$this->pi_getLL('telephone_is_required').'"
					//});';
				//}
				$output.='
				});			
				</script>
				';
			} */
		} else {
			$json_attributes.="'0':'yes'";
		}
		$json_attributes.='}';
		//$output .= $json_attributes;
		return $output;
	}
	public function ProductHasAttributes($products_id) {
		if (!is_numeric($products_id)) {
			return false;
		}
		$str="select popt.products_options_id, popt.products_options_name from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='".(int)$products_id."' and popt.hide_in_cart=0 and patrib.options_id = popt.products_options_id group by popt.products_options_id order by popt.products_options_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			return true;
		} else {
			return false;
		}
	}
	public function categoryHasSubs($categories_id) {
		if (is_numeric($categories_id)) {
			$str="select categories_id tx_multishop_categories where parent_id='".$categories_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function getTaxRate($tax_id) {
		if (!is_numeric($tax_id)) {
			return false;
		}
		$str="SELECT * from static_taxes where uid='".$tax_id."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $row['tx_rate'];
	}
	public function getProduct($products_id, $categories_id='', $extra_fields='', $include_disabled_products=0, $skipFlatDatabase=0) {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (!empty($categories_id)) {
			$categories_id=(int)$categories_id;
		}
		if ($skipFlatDatabase || (!$this->ms['MODULES']['FLAT_DATABASE'] || $include_disabled_products)) {
			$select=array();
			$select[]='*';
			$select[]='p.staffel_price as staffel_price';
			$select[]='s.specials_new_products_price';
			$select[]='s.start_date as special_start_date';
			$select[]='s.expires_date as special_expired_date';
			$select[]='IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
			$select[]='oud.name as order_unit_name';
			if ($extra_fields) {
				$select[]=$extra_fields;
			}
			$from=array();
			$from[]='tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id= m.manufacturers_id left join tx_multishop_order_units_description oud on p.order_unit_id=oud.order_unit_id and oud.language_id='.$this->sys_language_uid;
			$from[]='tx_multishop_products_description pd';
			$from[]='tx_multishop_products_to_categories p2c';
			$from[]='tx_multishop_categories c';
			$from[]='tx_multishop_categories_description cd';
			$where=array();
			if (!$include_disabled_products) {
				$where[]='p.products_status=1';
			}
			$where[]='p.products_id=\''.$products_id.'\'';
			$where[]='pd.language_id=\''.$this->sys_language_uid.'\'';
			$where[]='cd.language_id=pd.language_id';
			$where[]='p.products_id=pd.products_id';
			$where[]='p.products_id=p2c.products_id';
			$where[]='p2c.categories_id=c.categories_id';
			$where[]='p2c.categories_id=cd.categories_id';
			if ($categories_id) {
				$where[]='p2c.categories_id=\''.$categories_id.'\'';
			}
		} else {
			$select=array();
			$select[]='*';
			$from=array();
			$from[]='tx_multishop_products_flat';
			$where=array();
			$where[]='products_id=\''.$products_id.'\'';
			$where[]='language_id=\''.$this->sys_language_uid.'\'';
		}
		$query_elements=array();
		$query_elements['select']= &$select;
		$query_elements['from']= &$from;
		$query_elements['where']= &$where;
		$params=array(
			'query_elements'=>&$query_elements,
			'skipFlatDatabase'=>&$skipFlatDatabase,
			'include_disabled_products'=>&$include_disabled_products,
		);
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductPreProc'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductPreProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_elements['select']) ? implode(",", $query_elements['select']) : ''), // SELECT ...
			(is_array($query_elements['from']) ? implode(",", $query_elements['from']) : ''), // FROM ...
			(is_array($query_elements['where']) ? implode(" and ", $query_elements['where']) : ''), // WHERE...
			(is_array($query_elements['group_by']) ? implode(",", $query_elements['group_by']) : ''), // GROUP BY...
			(is_array($query_elements['order_by']) ? implode(",", $query_elements['order_by']) : ''), // ORDER BY...
			(is_array($query_elements['limit']) ? implode(",", $query_elements['limit']) : '') // LIMIT ...
		);
//		error_log($str);
//		echo $str;
//		die();
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if ($product['specials_new_products_price']>0) {
			if ($product['special_start_date']>0) {
				if ($product['special_start_date']>time()) {
					$product['specials_new_products_price']=0;
					$product['final_price']=$product['products_price'];
				}
			}
			if ($product['special_expired_date']>0) {
				if ($product['special_expired_date']<time()) {
					$product['specials_new_products_price']=0;
					$product['final_price']=$product['products_price'];
				}
			}
		}
		if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			// when shop is running excluding vat then change prices to 2 decimals to prevent bugs
			if ($product['final_price']) {
				$product['final_price']=round($product['final_price'], 2);
			}
			if ($product['products_price']) {
				$product['products_price']=round($product['products_price'], 2);
			}
			if ($product['specials_price']) {
				$product['specials_price']=round($product['specials_price'], 2);
			}
		}
		if ($product['products_id']) {
			// check every cat status
			$disable_product=false;
			if ($product['categories_id']) {
				// get all cats to generate multilevel fake url
				$level=0;
				$cats=mslib_fe::Crumbar($product['categories_id']);
				$cats=array_reverse($cats);
				$product_crumbar_tree=array();
				if (count($cats)>0) {
					foreach ($cats as $cat) {
						if ($cat['status']==0) {
							$disable_product=true;
						}
						$product_crumbar_tree[$level]['id']=$cat['id'];
						$product_crumbar_tree[$level]['name']=$cat['name'];
						$product_crumbar_tree[$level]['url']=$cat['url'];
						$level++;
					}
				}
				// get all cats to generate multilevel fake url eof
				if (count($product_crumbar_tree)) {
					$product['categories_crumbar']=$product_crumbar_tree;
				}
			}
			// hook
			if ($disable_product && !$include_disabled_products) {
				return false;
			}
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductArray'])) {
				$params=array(
					'product'=>&$product
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductArray'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			// hook eof
			$tax_ruleset=self::getTaxRuleSet($product['tax_id'], 0);
			$product['tax_rate']=($tax_ruleset['total_tax_rate']/100);
			$product['region_tax_rate']=($tax_ruleset['state_tax_rate']/100);
			$product['country_tax_rate']=($tax_ruleset['country_tax_rate']/100);
			return $product;
		}
	}
	public function getOrdersProductName($pid) {
		if (!is_numeric($pid)) {
			return false;
		}
		$sql="select products_name from tx_multishop_orders_products where products_id = ".$pid;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
		$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		return $rs['products_name'];
	}
	public function getNextPreviousProduct($products_id, $categories_id='') {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (!empty($categories_id)) {
			$categories_id=(int)$categories_id;
		}
		//hook to let other plugins further manipulate the query
		$query_elements=array();
		if (!$this->ms['MODULES']['FLAT_DATABASE']) {
			$query_elements['select'][]='pd.products_name, p.products_id, c.categories_id';
			$query_elements['from'][]='tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd';
			$query_elements['filter'][]="p.products_status=1 and c.categories_id='".$categories_id."' and pd.language_id='".$this->sys_language_uid."'";
			$query_elements['where'][]='cd.language_id=pd.language_id and p.products_id=pd.products_id and p.products_id=p2c.products_id and c.categories_id=p2c.categories_id and c.categories_id=cd.categories_id';
			$query_elements['orderby'][]="p2c.sort_order ".$this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'];
		} else {
			$query_elements['select'][]='pf.products_name, pf.products_id, pf.categories_id';
			$query_elements['from'][]='tx_multishop_products_flat pf';
			$query_elements['filter'][]="pf.categories_id='".$categories_id."'";
			$query_elements['orderby'][]="pf.sort_order ".$this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'];
		}
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getNextPreviousProduct'])) {
			$params=array(
				'query_elements'=>&$query_elements
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getNextPreviousProduct'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$str=$GLOBALS['TYPO3_DB']->SELECTquery(implode(',', $query_elements['select']), // SELECT ...
			implode(',', $query_elements['from']), // FROM ...
			(count($query_elements['filter']) ? implode(' AND ', $query_elements['filter']) : '').(count($query_elements['where']) ? ' AND '.implode(' AND ', $query_elements['where']) : ''), // WHERE...
			(count($query_elements['groupby']) ? implode(' AND ', $query_elements['groupby']) : ''), // GROUP BY...
			(count($query_elements['orderby']) ? implode(' AND ', $query_elements['orderby']) : ''), // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$count=0;
		$products=array();
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
			$products[]=$row['products_id'];
		}
		$total=count($products);
		$pagination_items=array();
		$trans=array_flip($products);
		$internal=$trans[$products_id];
		if ($internal==0) {
			$pagination_items['next_item']=$products[1];
		} else {
			$pagination_items['previous_item']=$products[($internal-1)];
			$pagination_items['next_item']=$products[($internal+1)];
		}
		foreach ($pagination_items as $key=>$item) {
			if ($item) {
				// get all cats to generate multilevel fake url
				$level=0;
				$cats=mslib_fe::Crumbar($categories_id);
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
				$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$item.'&tx_multishop_pi1[page_section]=products_detail');
				$pagination_items[$key]=$link;
			}
		}
		$pagination_items['internal']=$internal;
		$pagination_items['total']=$total;
		return $pagination_items;
	}
	/*
		this method is used to request the products page set
		$filter can be an string or (multiple) array:	
		string example: p2c.categories_id=12
		array example:  $filter[]='p2c.categories_id=12'	
	*/
	public function getProductsPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array(), $redirect_if_one_product=0, $extra_from=array(), $groupby=array(), $search_section='products_search', $select_total_count='', $returnTotalCountOnly=0, $enableFetchTaxRate=1, $extra_join=array()) {
		if (!is_array($filter) and $filter) {
			$filter=array($filter);
		}
		if ($this->ms['MODULES']['SHOW_PRODUCTS_WITH_IMAGE_FIRST']) {
			if (!$this->ms['MODULES']['FLAT_DATABASE']) {
				$prefix='p.';
			} else {
				$prefix='pf.';
			}
			$tmp_orderby=array();
			$tmp_orderby[]=$prefix.'contains_image desc';
			$tmp_orderby[]=$prefix.'sort_order '.$this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'];
			if (!is_array($orderby) and $orderby) {
				$tmp_orderby[]=$orderby;
			} else {
				$tmp_orderby=array_merge($tmp_orderby, $orderby);
			}
			$orderby=$tmp_orderby;
		}
		if (!$limit) {
			$limit=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		if (!count($groupby)) {
			if (!$this->ms['MODULES']['FLAT_DATABASE']) {
				$prefix='p.';
			} else {
				$prefix='pf.';
			}
			$groupby[]=$prefix.'products_id';
		}
		if (!$this->ms['MODULES']['FLAT_DATABASE']) {
			// do normal search (join the seperate tables)					
			$required_cols='p.products_status,p.products_id,p.minimum_quantity,s.specials_new_products_price,s.start_date as special_start_date,s.expires_date as special_expired_date,pd.products_viewed,pd.products_url,p.products_image,p.products_image1,p.products_date_added,p.products_model,p.products_quantity,p.products_price,p.staffel_price as staffel_price,IF(s.status, s.specials_new_products_price, p.products_price) as final_price,p.products_date_available,p.tax_id,p.manufacturers_id,pd.products_name,pd.products_shortdescription,c.categories_id,cd.categories_name,pd.products_meta_title,pd.products_meta_keywords,pd.products_meta_description,pd.products_description,m.manufacturers_name';
			$select=array_merge(explode(',', $required_cols), $select);
			if ($this->ms['MODULES']['INCLUDE_PRODUCTS_DESCRIPTION_DB_FIELD_IN_PRODUCTS_LISTING']) {
				$required_cols.=',pd.products_description';
			}
			//hook to let other plugins further manipulate the query
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductsPageSet'])) {
				$query_elements=array();
				$query_elements['filter']=&$filter;
				$query_elements['offset']=&$offset;
				$query_elements['limit']=&$limit;
				$query_elements['orderby']=&$orderby;
				$query_elements['having']=&$having;
				$query_elements['select']=&$select;
				$query_elements['select_total_count']=&$select_total_count;
				$query_elements['where']=&$where;
				$query_elements['groupby']=&$groupby;
				$query_elements['redirect_if_one_product']=&$redirect_if_one_product;
				$query_elements['extra_from']=&$extra_from;
				$query_elements['search_section']=&$search_section;
				$query_elements['extra_join']=&$extra_join;
				$params=array(
					'query_elements'=>&$query_elements,
					'enableFetchTaxRate'=>&$enableFetchTaxRate
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductsPageSet'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			$from_clause.="tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id = m.manufacturers_id";
			if (count($extra_join)) {
				$from_clause.=" ";
				$from_clause.=implode(" ", $extra_join);
			}
			$from_clause.=", tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd ";
			//hook to let other plugins further manipulate the query eof					
			if (count($extra_from)) {
				$from_clause.=", ";
				$from_clause.=implode(",", $extra_from);
			}
			if ($this->ADMIN_USER) {
				$where_clause.=" 1 ";
			} else {
				$where_clause.=" p.products_status=1 ";
			}
			if (!$this->masterShop) {
				$where_clause.=" and p.page_uid='".$this->showCatalogFromPage."' ";
			}
			$where_clause.=" and pd.language_id='".$this->sys_language_uid."' ";
			if (is_array($where) and count($where)>0) {
				$where_clause.='and ';
				$where_clause.=implode(" and ", $where);
			}
			$where_clause.=' and ';
			if (is_array($filter) and count($filter)>0) {
				$where_clause.=implode(" and ", $filter)." and ";
			} else if ($filter) {
				$where_clause.=$filter." and ";
			}
			$where_clause.=" pd.language_id=cd.language_id and p.products_id=p2c.products_id and p.products_id=pd.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id ";
			if (count($having)>0) {
				$having_clause.=" having ";
				foreach ($having as $item) {
					$having_clause.=$item;
				}
			}
			if (is_array($orderby) and count($orderby)>0) {
				$str_order_by.=implode(",", $orderby);
			} elseif ($orderby) {
				$str_order_by.=$orderby;
			} else {
				$str_order_by.='p2c.sort_order '.$this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'];
			}
			if ($str_order_by) {
				$orderby_clause.=$str_order_by;
			}
		} else {
			// flat mode database mode. This module is used on LARGE catalogs, so the joins of individual tables are minimized
			// do the flat search (without having to join the seperate tables)                      
			// temporary fix, cause hot products sometimes show products double
			if (!$orderby) {
				$orderby[]='NULL';
			}
			$required_cols='pf.products_multiplication,pf.maximum_quantity,pf.minimum_quantity,pf.products_viewed,pf.products_url,pf.products_id,pf.products_image,pf.products_image1,pf.products_model,pf.products_quantity,pf.products_price,pf.staffel_price,pf.final_price,pf.products_date_added,pf.products_date_available,pf.tax_id,pf.manufacturers_id,pf.manufacturers_name,pf.products_name,pf.products_shortdescription,pf.categories_id,pf.categories_name,pf.categories_name_0,pf.categories_name_1,pf.categories_name_2,pf.categories_name_3,pf.products_meta_title,pf.products_meta_keywords,pf.products_meta_description,pf.order_unit_code,pf.order_unit_name,pf.ean_code,pf.sku_code';
			$select=array_merge(explode(',', $required_cols), $select);
			if ($this->ms['MODULES']['INCLUDE_PRODUCTS_DESCRIPTION_DB_FIELD_IN_PRODUCTS_LISTING']) {
				$select[]='pf.products_description';
			}
			if ($this->ms['MODULES']['FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS'] and is_array($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'])) {
				foreach ($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'] as $option_id=>$array) {
					if ($array[0] and $array[1]) {
						$select[]="pf.".$array[0];
					}
				}
			}
			if (!$this->masterShop) {
				$where[]="pf.page_uid='".$this->showCatalogFromPage."'";
			}
			$from_clause.="tx_multishop_products_flat pf ";
			if (count($extra_join)) {
				$from_clause.=" ";
				$from_clause.=implode(" ", $extra_join);
			}
			//hook to let other plugins further manipulate the query
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductsPageSet'])) {
				$query_elements=array();
				$query_elements['filter']=&$filter;
				$query_elements['offset']=&$offset;
				$query_elements['limit']=&$limit;
				$query_elements['orderby']=&$orderby;
				$query_elements['having']=&$having;
				$query_elements['select']=&$select;
				$query_elements['select_total_count']=&$select_total_count;
				$query_elements['where']=&$where;
				$query_elements['groupby']=&$groupby;
				$query_elements['redirect_if_one_product']=&$redirect_if_one_product;
				$query_elements['extra_from']=&$extra_from;
				$query_elements['search_section']=&$search_section;
				$params=array(
					'query_elements'=>&$query_elements,
					'enableFetchTaxRate'=>&$enableFetchTaxRate
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getProductsPageSet'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			//hook to let other plugins further manipulate the query eof					
			if (count($extra_from)) {
				$from_clause.=", ";
				$from_clause.=implode(",", $extra_from);
			}
			if (is_array($where) and count($where)>0) {
				$where_clause.=implode(" and ", $where);
			}
			if (is_array($filter) and count($filter)>0) {
				if ($where_clause) {
					$where_clause.=' and ';
				}
				$where_clause.=implode(" and ", $filter);
			} else if ($filter) {
				if ($where_clause) {
					$where_clause.=' and ';
				}
				$where_clause.=$filter;
			}
			if (count($having)>0) {
				$having_clause.=" having ";
				foreach ($having as $item) {
					$having_clause.=$item;
				}
			}
			if (is_array($orderby) and count($orderby)>0) {
				$str_order_by.=implode(",", $orderby);
			} else if ($orderby) {
				$str_order_by.=$orderby;
			}
			if ($str_order_by) {
				$orderby_clause.=$str_order_by;
			}
		}
		$limit_clause.=$offset.','.$limit;
		// error_log(print_r($groupby,1));
		// die();
		$array=array();
		if (!$this->ms['MODULES']['FLAT_DATABASE']) {
			if (count($having)) {
				// since this query is using HAVING we need to calculate the total records on a different way                           
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('p.products_id,IF(s.status, s.specials_new_products_price, p.products_price) as final_price', // SELECT ...
					$from_clause, // FROM ...
					$where_clause, // WHERE...
					implode($groupby, ",").$having_clause, // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				//echo $str;
				//die();                                
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$array['total_rows']=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
			} else {
				// the select count(1) is buggy when working with group by and 1-n relations (1 product to many categories). therefore we temporary counting through sql_num_rows
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('p.products_id ', // SELECT ...
					$from_clause, // FROM ...
					$where_clause, // WHERE...
					implode($groupby, ","), // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				//echo $str;
				//die();                                        
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$array['total_rows']=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
			}
		} else {
			$prefix='pf.';
			if (!$select_total_count) {
				$select_total_count='count(DISTINCT('.$prefix.'products_id)) as total';
			}
			$str=$GLOBALS['TYPO3_DB']->SELECTquery(//'count(1) as total',         // SELECT ...
				$select_total_count, // SELECT ...
				$from_clause, // FROM ...
				$where_clause, // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$array['total_rows']=$row['total'];
		}
		if ($this->msDebug) {
			$this->msDebugInfo.=$str."\n\n";
		}
		//echo $str.'<br>';
		//die();
		// error_log($str);
		// die();
		if ($returnTotalCountOnly) {
			return $array['total_rows'];
		}
		// now do the real query including the order by and the limit
		$str=$GLOBALS['TYPO3_DB']->SELECTquery(implode($select, ","), // SELECT ...
			$from_clause, // FROM ...
			$where_clause, // WHERE...
			implode($groupby, ",").$having_clause, // GROUP BY...
			$orderby_clause, // ORDER BY...
			$limit_clause // LIMIT ...
		);
		//echo $str.'<br>';
		//error_log($str);
		//die();
		if ($this->msDebug) {
			$this->msDebugInfo.=$str."\n\n";
		}
		// error_log($str);
		// echo $str."\n\n";
		// die();
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			$tax_ruleset=array();
			while ($product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				if ($product['specials_new_products_price']>0) {
					if ($product['special_start_date']>0) {
						if ($product['special_start_date']>time()) {
							$product['specials_new_products_price']=0;
							$product['final_price']=$product['products_price'];
						}
					}
					if ($product['special_expired_date']>0) {
						if ($product['special_expired_date']<time()) {
							$product['specials_new_products_price']=0;
							$product['final_price']=$product['products_price'];
						}
					}
				}
				if (!$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
					// when shop is running excluding vat then change prices to 2 decimals to prevent bugs
					if ($product['final_price']) {
						$product['final_price']=round($product['final_price'], 2);
					}
					if ($product['products_price']) {
						$product['products_price']=round($product['products_price'], 2);
					}
					if ($product['specials_price']) {
						$product['specials_price']=round($product['specials_price'], 2);
					}
				}
				if ($enableFetchTaxRate) {
					if (!isset($tax_ruleset[$product['tax_id']])) {
						$tax_ruleset[$product['tax_id']]=self::getTaxRuleSet($product['tax_id'], 0);
					}
					$product['tax_rate']=($tax_ruleset[$product['tax_id']]['total_tax_rate']/100);
					$product['country_tax_rate']=($tax_ruleset[$product['tax_id']]['country_tax_rate']/100);
					$product['region_tax_rate']=($tax_ruleset[$product['tax_id']]['state_tax_rate']/100);
				}
				$array['products'][]=$product;
			}
			if (count($array['products'])==1 and $redirect_if_one_product) {
				$where='';
				$product=$array['products'][0];
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
				if ($product['products_url'] and $this->ms['MODULES']['AFFILIATE_SHOP']) {
					$link=$product['products_url'];
				} else {
					$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
				}
				if ($link) {
					header("Location: ".$this->FULL_HTTP_URL.$link);
					exit();
				}
			}
		}
		return $array;
	}
	public function getOptionsPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array()) {
		if (!is_numeric($offset)) {
			$offset=0;
		}
		$groupby_clause='';
		// do normal search (join the seperate tables)
		$required_cols='op.products_options_name, op.products_options_id';
		$select_clause="SELECT ".$required_cols;
		$from_clause.=" from tx_multishop_products_options op ";
		$where_clause.=" where op.language_id='".$this->sys_language_uid."' ";
		$where_clause.=' and ';
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=implode(" and ", $filter)." ";
		} else if ($filter) {
			$where_clause.=$filter." ";
		}
		$orderby_clause.=" order by op.products_options_name asc";
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
		$str="select count(1) as total ".$from_clause.$where_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		// now do the real query including the order by and the limit
		$str=$select_clause.$from_clause.$where_clause.$groupby_clause.$having_clause.$orderby_clause.$limit_clause;
		//echo $str;
		/*
			//$GLOBALS['TSFE']->fe_user->user['username']=='base'
		if ($this->ROOTADMIN_USER)
		{
		echo $str;
		die();
		}
		*/
		//		error_log($str);
		//		echo $str."\n\n";
		//		error_log($array['total_rows']);
		//		error_log($str);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($options=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['options'][]=$options;
			}
		}
		return $array;
	}
	public function getOptionsValuesPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array()) {
		if (!is_numeric($offset)) {
			$offset=0;
		}
		$groupby_clause='';
		// do normal search (join the seperate tables)
		$required_cols='opv.products_options_values_name';
		$select_clause="SELECT ".$required_cols;
		$from_clause.=" from tx_multishop_products_options op left join tx_multishop_products_options_values_to_products_options op2v on op.products_options_id = op2v.products_options_id left join tx_multishop_products_options_values opv on opv.products_options_values_id = op2v.products_options_values_id ";
		$where_clause.=" where opv.language_id='".$this->sys_language_uid."' ";
		$where_clause.=' and ';
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=implode(" and ", $filter)." ";
		} else if ($filter) {
			$where_clause.=$filter." ";
		}
		$orderby_clause.=" order by opv.products_options_values_name asc";
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
		$str="select count(1) as total ".$from_clause.$where_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		// now do the real query including the order by and the limit
		$str=$select_clause.$from_clause.$where_clause.$groupby_clause.$having_clause.$orderby_clause.$limit_clause;
		//echo $str;
		/*
		 //$GLOBALS['TSFE']->fe_user->user['username']=='base'
		if ($this->ROOTADMIN_USER)
		{
		echo $str;
		die();
		}
		*/
		//		error_log($str);
		//		echo $str."\n\n";
		//		error_log($array['total_rows']);
		//		error_log($str);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($options=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['options'][]=$options;
			}
		}
		return $array;
	}
	/*
		this method is used to request the admin customers page set
		$filter can be an string or (multiple) array:	
		string example: p2c.categories_id=12
		array example:  $filter[]='p2c.categories_id=12'	
	*/
	public function getCustomersPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array()) {
		if (!$limit) {
			$limit=30;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$required_cols='f.*';
		$select_clause="SELECT ".$required_cols;
		if (count($select)>0) {
			$select_clause.=', ';
			$select_clause.=implode(",", $select);
		}
		$from_clause.="	from fe_users f ";
		$where_clause.=" where ";
		if (count($where)>0) {
			$where_clause.=implode(",", $where);
			$where_clause.=' and ';
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=implode(" and ", $filter)." and ";
		} else if ($filter) {
			$where_clause.=$filter." and ";
		}
		$where_clause.=" f.username !=''";
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode(",", $orderby);
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
//		$array['total_rows']=$row2[0];
		// retrieve the total number of records
//		$str=$select_clause.", count(1) as total ".$from_clause.$where_clause.$having_clause;
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		// now do the query
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
//		$qry2=$GLOBALS['TYPO3_DB']->sql_query("SELECT FOUND_ROWS();");
//		$row2=$GLOBALS['TYPO3_DB']->sql_fetch_row($qry2);
		if ($rows>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['customers'][]=$row;
			}
		}
		return $array;
	}
	public function getCustomersList($filter=array(), $orderby=array(), $having=array(), $select=array(), $where=array()) {
		// do normal search (join the seperate tables)
		$required_cols='f.*';
		$select_clause="SELECT ".$required_cols;
		if (count($select)>0) {
			$select_clause.=', ';
			$select_clause.=implode(",", $select);
		}
		$from_clause.="	from fe_users f ";
		$where_clause.=" where ";
		if (count($where)>0) {
			$where_clause.=implode(",", $where);
			$where_clause.=' and ';
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=implode(" and ", $filter)." and ";
		} else if ($filter) {
			$where_clause.=$filter." and ";
		}
		$where_clause.=" f.username !=''";
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode(",", $orderby);
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$array=array();
		//		$array['total_rows']=$row2[0];
		// retrieve the total number of records
		//		$str=$select_clause.", count(1) as total ".$from_clause.$where_clause.$having_clause;
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		// now do the query
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		//		$qry2=$GLOBALS['TYPO3_DB']->sql_query("SELECT FOUND_ROWS();");
		//		$row2=$GLOBALS['TYPO3_DB']->sql_fetch_row($qry2);
		if ($rows>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['customers'][]=$row;
			}
		}
		return $array;
	}
	public function getCustomerGroupsPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array()) {
		if (!$limit) {
			$limit=30;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$required_cols='f.*';
		$select_clause="SELECT ".$required_cols;
		if (count($select)>0) {
			$select_clause.=', ';
			$select_clause.=implode(",", $select);
		}
		$from_clause.="	from fe_groups f ";
		$where_clause.=" where ";
		if (count($where)>0) {
			$where_clause.=implode(",", $where);
			$where_clause.=' and ';
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=implode(" and ", $filter)." and ";
		} else if ($filter) {
			$where_clause.=$filter." and ";
		}
		$where_clause.=" f.title !=''";
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode(",", $orderby);
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
//		$array['total_rows']=$row2[0];
		// retrieve the total number of records
//		$str=$select_clause.", count(1) as total ".$from_clause.$where_clause.$having_clause;
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		// now do the query
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
//		$qry2=$GLOBALS['TYPO3_DB']->sql_query("SELECT FOUND_ROWS();");
//		$row2=$GLOBALS['TYPO3_DB']->sql_fetch_row($qry2);
		if ($rows>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['groups'][]=$row;
			}
		}
		return $array;
	}
	/*
		this method is used to request the stores page set
		$filter can be an string or (multiple)
	*/
	public function getStoresPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array()) {
		if (!$limit) {
			$limit=30;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$required_cols='mss.address,mss.zip,mss.city,mss.telephone,mss.fax, mss.id, mss.name, mssd.description';
		$select_clause="SELECT ".$required_cols;
		if (count($select)>0) {
			$select_clause.=', ';
			$select_clause.=implode(",", $select);
		}
		$from_clause.="	from tx_multishop_stores mss, tx_multishop_stores_description mssd ";
		$where_clause.=" where ";
		if (count($where)>0) {
			$where_clause.=implode(",", $where);
			$where_clause.=' and ';
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=implode(" and ", $filter)." and ";
		} else if ($filter) {
			$where_clause.=$filter." and ";
		}
		$where_clause.=" mss.status ='1' and mssd.language_id='".$this->sys_language_uid."' and mss.id=mssd.id ";
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode(",", $orderby);
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
//		$array['total_rows']=$row2[0];
		// retrieve the total number of records
//		$str=$select_clause.", count(1) as total ".$from_clause.$where_clause.$having_clause;
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		// now do the query
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
//		$qry2=$GLOBALS['TYPO3_DB']->sql_query("SELECT FOUND_ROWS();");
//		$row2=$GLOBALS['TYPO3_DB']->sql_fetch_row($qry2);
		if ($rows>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['stores'][]=$row;
			}
		}
		return $array;
	}
	public function getSubcats(&$subcategories_array, $parent_id=0) {
		$subcategories_query=$GLOBALS['TYPO3_DB']->sql_query("select categories_id from tx_multishop_categories where page_uid='".$this->showCatalogFromPage."' and status = '1' and parent_id = '".$parent_id."'");
		while ($subcategories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($subcategories_query)) {
			$subcategories_array[sizeof($subcategories_array)]=$subcategories['categories_id'];
			if ($subcategories['categories_id']!=$parent_id) {
				mslib_fe::getSubcats($subcategories_array, $subcategories['categories_id']);
			}
		}
	}
	/*
	limit				number of products
	page_uid			the pid of the core shop page
	content_uid		the uid of the content object for unique css id naming
	*/
	public function SpecialsBox($contentType, $limit=5, $page_uid='', $content_uid='') {
		switch ($contentType) {
			case 'home':
				if ($this->ms['MODULES']['HOME_SPECIALS_BOX']) {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/products_specials.php');
				}
				break;
			case 'single_special':
				require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/products_specials.php');
				break;
			case 'specials_listing_page':
				require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/products_specials.php');
				break;
			case 'products_search':
			case 'specials_section':
			default:
				require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/products_specials.php');
				break;
		}
		return $content;
	}
	public function getProductMappedMethods($pids=array(), $type='', $user_country='0') {
		if (is_array($pids) and count($pids)) {
			switch ($type) {
				case 'payment':
					// first we load all options
					$allmethods=mslib_fe::loadPaymentMethods($user_country);
					foreach ($pids as $pid) {
						$str="SELECT s.code from tx_multishop_products_method_mappings pmm, tx_multishop_payment_methods s where s.status=1 and pmm.type='".$type."' and pmm.products_id = ".$pid." and pmm.method_id=s.id";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$array=array();
						while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
							$array[]=$row['code'];
						}
						foreach ($allmethods as $key=>$value) {
							if (!in_array($key, $array)) {
								unset($allmethods[$key]);
							}
						}
					}
					break;
				case 'shipping':
					// first we load all options
					/*					
					$allmethods=mslib_fe::loadShippingMethods();
					foreach ($pids as $pid)
					{
						$str="SELECT s.code from tx_multishop_products_method_mappings pmm, tx_multishop_shipping_methods s where pmm.type='".$type."' and pmm.products_id = ".$pid." and pmm.method_id=s.id";	
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$array=array();
						while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))
						{
							$array[]=$row['code'];
						}
						foreach ($allmethods as $key => $value)
						{
							if (!in_array($key,$array))
							{
								unset($allmethods[$key]);
							}
						}
					}
*/
					$allmethods=array();
					foreach ($pids as $pid) {
						$str="SELECT s.*,d.description, d.name from tx_multishop_products_method_mappings pmm, tx_multishop_shipping_methods s, tx_multishop_shipping_methods_description d where s.status=1 and pmm.type='".$type."' and pmm.products_id = ".$pid." and pmm.method_id=s.id and d.language_id='".$this->sys_language_uid."' and s.id=d.id order by s.sort_order";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
							$allmethods[$row['code']]=$row;
						}
					}
					break;
			}
			return $allmethods;
		}
	}
	public function loadShippingMethods($include_hidden_items=0, $user_country=0) {
		if (is_numeric($user_country) && $user_country>0) {
			$str="SELECT c2z.id as c2z_id, s.*, d.* from tx_multishop_shipping_methods s, tx_multishop_shipping_methods_description d, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_methods_to_zones p2z where ";
			if (!$include_hidden_items) {
				$str.="s.status=1 and ";
			}
			$str.="c2z.cn_iso_nr = ".$user_country." and d.language_id='".$this->sys_language_uid."' and (s.page_uid = ".$this->shop_pid." or s.page_uid = 0) and c2z.zone_id = p2z.zone_id and p2z.shipping_method_id = s.id and s.id=d.id order by s.sort_order";
		} else {
			$str="SELECT * from tx_multishop_shipping_methods s, tx_multishop_shipping_methods_description d where ";
			if (!$include_hidden_items) {
				$str.="s.status=1 and ";
			}
			$str.="d.language_id='".$this->sys_language_uid."' and (s.page_uid = ".$this->shop_pid." or s.page_uid = 0) and s.id=d.id order by s.sort_order";
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$array=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array[$row['code']]=$row;
			}
			return $array;
		}
	}
	public function loadShippingMethod($code) {
		$str="SELECT * from tx_multishop_shipping_methods s, tx_multishop_shipping_methods_description d where s.code='".addslashes($code)."' and (s.page_uid = ".$this->shop_pid." or s.page_uid = 0) and d.language_id='".$this->sys_language_uid."' and s.id=d.id order by s.sort_order";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$array=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				return $row;
			}
		}
	}
	public function loadPaymentMethods($include_hidden_items=0, $user_country=0) {
		if (is_numeric($user_country) && $user_country>0) {
			$str="SELECT c2z.id as c2z_id, s.*, d.* from tx_multishop_payment_methods s, tx_multishop_payment_methods_description d, tx_multishop_countries_to_zones c2z, tx_multishop_payment_methods_to_zones p2z where ";
			if (!$include_hidden_items) {
				$str.="s.status=1 and ";
			}
			$str.="c2z.cn_iso_nr = ".$user_country." and d.language_id='".$this->sys_language_uid."' and (s.page_uid = ".$this->shop_pid." or s.page_uid = 0) and c2z.zone_id = p2z.zone_id and p2z.payment_method_id = s.id and s.id=d.id order by s.sort_order";
		} else {
			$str="SELECT * from tx_multishop_payment_methods s, tx_multishop_payment_methods_description d where ";
			if (!$include_hidden_items) {
				$str.="s.status=1 and ";
			}
			$str.="d.language_id='".$this->sys_language_uid."' and (s.page_uid = ".$this->shop_pid." or s.page_uid = 0) and s.id=d.id order by s.sort_order";
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$array=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array[$row['code']]=$row;
			}
			return $array;
		}
	}
	public function loadPaymentMethod($code) {
		$str="SELECT * from tx_multishop_payment_methods s, tx_multishop_payment_methods_description d where s.code='".addslashes($code)."' and (s.page_uid = ".$this->shop_pid." or s.page_uid = 0) and d.language_id='".$this->sys_language_uid."' and s.id=d.id order by s.sort_order";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$array=array();
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				return $row;
			}
		}
	}
	public function loadAllCountriesZones() {
		$str="SELECT * from tx_multishop_zones";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$array=array();
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['zone_id'][]=$row['id'];
				$array['zone_name'][]=$row['name'];
				$sql_countries="select c2z.cn_iso_nr, sc.cn_short_en from tx_multishop_countries_to_zones c2z, static_countries sc where c2z.cn_iso_nr = sc.cn_iso_nr and c2z.zone_id = ".$row['id'];
				$qry_countries=$GLOBALS['TYPO3_DB']->sql_query($sql_countries);
				while ($row_country=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_countries)) {
					$array['countries'][$row['id']][]=$row_country['cn_short_en'];
				}
			}
			return $array;
		}
	}
	public function loadAllShippingMethods() {
		$shipping_methods=array();
		$shipping_methods['generic']=array(
			'name'=>'Generic'
		);
		$shipping_methods['gls']=array(
			'name'=>'GLS'
		);
		$shipping_methods['hermes']=array(
			'name'=>'Hermes'
		);
		$shipping_methods['dhl']=array(
			'name'=>'DHL'
		);
		$shipping_methods['ups']=array(
			'name'=>'UPS'
		);
		$shipping_methods['fedex']=array(
			'name'=>'FEDEX'
		);
		$shipping_methods['tnt']=array(
			'name'=>'TNT'
		);
		$shipping_methods['gpl']=array(
			'name'=>'GPL'
		);
		return $shipping_methods;
	}
	public function loadAllPaymentMethods() {
		$payment_methods=array();
		// GENERIC
		$payment_methods['generic']=array(
			'name'=>'Generic',
			'country'=>'int'
		);
		// GENERIC EOF
		// ON ACCOUNT
		$payment_methods['on_account']=array(
			'name'=>'On Account',
			'country'=>'int'
		);
		// ON ACCOUNT EOF
		// PAYMENT IN STORE
		$payment_methods['payment_in_store']=array(
			'name'=>'Payment in Store',
			'country'=>'int'
		);
		// PAYMENT IN STORE EOF
		return $payment_methods;
	}
	public function parsePaymentMethodEditForm($psp, $selected_values='', $readonly=0) {
		foreach ($psp as $key=>$value) {
			switch ($key) {
				case 'name':
					$name=$value;
					break;
				case 'image':
					$image=$value;
					break;
				case 'vars':
					foreach ($value as $field_key=>$vars) {
						$content.='<div class="account-field"><label for="radio">'.$field_key.'</label>';
						switch ($vars['type']) {
							case 'input':
								$content.='<input name="'.$field_key.'" id="'.$field_key.'" type="text" value="'.(isset($selected_values[$field_key]) ? htmlspecialchars($selected_values[$field_key]) : '').'" />';
								break;
							case 'radio':
								if (count($vars['options'])>0) {
									foreach ($vars['options'] as $radio_option) {
										$content.=' <input name="'.$field_key.'" id="'.$field_key.'" type="radio" value="'.$radio_option.'" '.(($selected_values[$field_key]==$radio_option) ? 'checked' : '').' /><span>'.$radio_option.'</span>';
									}
								}
								break;
							case 'order_status':
								$all_orders_status=mslib_fe::getAllOrderStatus();
								if (is_array($all_orders_status) and count($all_orders_status)) {
									$content.='<select name="'.$field_key.'" id="'.$field_key.'">';
									$content.='<option value="0">-- order status --</option>'."\n";
									foreach ($all_orders_status as $row) {
										$content.='<option value="'.$row['id'].'" '.(($selected_values[$field_key]==$row['id']) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
									}
									$content.='</select>';
								}
								break;
						}
						$content.='</div>';
					}
					break;
			}
		}
		if (count($psp['additional_info'])>0) {
			$content.='
			<div class="account-field">						
				<strong>Parameters that are needed for configuring this PSP</strong>
			</div>
			';
			foreach ($psp['additional_info'] as $item) {
				$content.='
				<div class="account-field">
					<strong>'.$item['label'].'</strong><br />'.$item['value'].'</div>
				';
			}
		}
		return $content;
	}
	public function parseShippingMethodEditForm($psp, $selected_values='', $readonly=0) {
		foreach ($psp as $key=>$value) {
			switch ($key) {
				case 'name':
					$name=$value;
					break;
				case 'image':
					$image=$value;
					break;
				case 'vars':
					foreach ($value as $field_key=>$vars) {
						$content.='
						<div class="account-field">
							<label for="radio">'.$field_key.'</label>';
						switch ($vars['type']) {
							case 'input':
								$content.='<input name="'.$field_key.'" id="'.$field_key.'" type="text" value="'.(isset($selected_values[$field_key]) ? htmlspecialchars($selected_values[$field_key]) : '').'" />';
								break;
							case 'radio':
								if (count($vars['options'])>0) {
									foreach ($vars['options'] as $radio_option) {
										$content.='<input name="'.$field_key.'" id="'.$field_key.'" type="radio" value="'.$radio_option.'" '.(($selected_values[$field_key]==$radio_option) ? 'checked' : '').' /><span>'.$radio_option.'</span>';
									}
								}
								break;
						}
						$content.='</div>';
					}
					break;
			}
		}
		return $content;
	}
	public function returnBoxedHTML($title='', $content='') {
		$output='
		 <div class="shaded_box">
			<div class="shaded_box_lt">
			<div class="shaded_box_rt">
			<div class="shaded_box_lb">
			<div class="shaded_box_rb">
						<div class="title"><h2>'.$title.'</h2></div>
					  <div class="content">'.$content.'</div>
			</div></div></div></div>
		</div>	
		';
		/*
		<div class="shaded_box">
			<div class="title"><h2>'.$title.'</h2></div>
			<div class="content">'.$content.'</div>
		</div>
	*/
		return $output;
	}
	public function amount2Cents($amount, $customer_currency=1, $include_currency_symbol=1) {
		$cu_thousands_point=$this->ms['MODULES']['CURRENCY_ARRAY']['cu_thousands_point'];
		$cu_decimal_point=$this->ms['MODULES']['CURRENCY_ARRAY']['cu_decimal_point'];
		if ($this->cookie['currency_rate'] and $customer_currency) {
			$amount=$amount*$this->cookie['currency_rate'];
			$cu_thousands_point=$this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_thousands_point'];
			$cu_decimal_point=$this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'];
		}
		$amount=number_format($amount, 2, '.', '');
		$array=explode('.', $amount);
		$array[0]=number_format($array[0], 0, '', $cu_thousands_point);
		$output='<span class="amount">';
		if ($include_currency_symbol) {
			$output.=mslib_fe::currency(1, $customer_currency);
		}
		if ($array[1]=='00') {
			$array[1]='-';
		}
		if ($array[1]==',00') {
			$array[1]=',-';
		}
		$output.=$array[0].$cu_decimal_point.'</span><span class="amount_cents">'.$array[1].'</span>';
		return $output;
	}
	public function taxDecimalCrop($float, $precision=2, $disable=true) {
		if ($disable) {
			return $float;
		}
		$numbers=explode('.', $float);
		$prime=$numbers[0];
		$decimal=substr($numbers[1], 0, $precision);
		if (!$prime && !$decimal) {
			return '0.00';
		}
		if (!empty($decimal)) {
			$float=$prime.'.'.$decimal;
		} else {
			$float=$prime.'.00';
		}
		return $float;
	}
	public function amount2Cents2($money) {
		$money=number_format($money, 2, '.', '');
		return $money;
	}
	public function currency($html=1, $customer_currency=0) {
		$currency_symbol=$this->ms['MODULES']['CURRENCY'];
		if ($this->cookie['currency_rate'] and $customer_currency) {
			$currency_symbol=$this->ms['MODULES']['CUSTOMER_CURRENCY'];
		}
		return $currency_symbol;
	}
	public function get_subcategory_ids($parent_id, &$array=array()) {
		if (!is_numeric($parent_id)) {
			return false;
		}
		$categories_query=$GLOBALS['TYPO3_DB']->sql_query("select c.categories_id, cd.categories_name, c.parent_id from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '".$parent_id."' and c.page_uid='".$this->showCatalogFromPage."' order by c.sort_order, cd.categories_name");
		while ($categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($categories_query)) {
			if (is_numeric($categories['categories_id'])) {
				$array[]=$categories['categories_id'];
			}
			mslib_fe::get_subcategory_ids($categories['categories_id'], $array);
		}
		return $array;
	}
	public function tx_multishop_get_category_tree($parent_id='0', $spacing='', $exclude='', $category_tree_array='', $include_itself=false, $exclude_not_active=false, $default_label='') {
		if (!$parent_id and $this->categoriesStartingPoint) {
			$parent_id=$this->categoriesStartingPoint;
		}
		if (!$default_label) {
			$default_label=$this->pi_getLL('admin_main_category');
		}
		if (!is_array($category_tree_array)) {
			$category_tree_array=array();
		}
		if ((sizeof($category_tree_array)<1) && ($exclude!='0')) {
			$category_tree_array[]=array(
				'id'=>$this->categoriesStartingPoint,
				'text'=>$default_label
			);
		}
		if ($include_itself) {
			if ($exclude_not_active) {
				$category_query=$GLOBALS['TYPO3_DB']->sql_query("select cd.categories_name from tx_multishop_categories_description cd where cd.categories_id = '".$parent_id."' and c.status = 1 and cd.language_id='".$this->sys_language_uid."' order by c.sort_order, cd.categories_name");
			} else {
				$category_query=$GLOBALS['TYPO3_DB']->sql_query("select cd.categories_name from tx_multishop_categories_description cd where cd.categories_id = '".$parent_id."' and cd.language_id='".$this->sys_language_uid."'");
			}
			$category=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($category_query);
			$category_tree_array[]=array(
				'id'=>$parent_id,
				'text'=>$category['categories_name']
			);
		}
		if ($exclude_not_active) {
			$categories_query=$GLOBALS['TYPO3_DB']->sql_query("select c.categories_id, cd.categories_name, c.parent_id from tx_multishop_categories c, tx_multishop_categories_description cd where c.parent_id = '".$parent_id."' and c.status = 1 and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$this->sys_language_uid."' and c.categories_id = cd.categories_id  order by c.sort_order");
		} else {
			$categories_query=$GLOBALS['TYPO3_DB']->sql_query("select c.categories_id, cd.categories_name, c.parent_id from tx_multishop_categories c, tx_multishop_categories_description cd where c.parent_id = '".$parent_id."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$this->sys_language_uid."' and c.categories_id = cd.categories_id order by c.sort_order");
		}
		while ($categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($categories_query)) {
			if (is_array($exclude) and count($exclude)) {
				if (!in_array($categories['categories_id'], $exclude)) {
					$category_tree_array[]=array(
						'id'=>$categories['categories_id'],
						'text'=>$spacing.$categories['categories_name']
					);
					$category_tree_array=mslib_fe::tx_multishop_get_category_tree($categories['categories_id'], $spacing.'---', $exclude, $category_tree_array);
				}
			} else {
				if ($exclude!=$categories['categories_id']) {
					$category_tree_array[]=array(
						'id'=>$categories['categories_id'],
						'text'=>$spacing.$categories['categories_name']
					);
				}
				$category_tree_array=mslib_fe::tx_multishop_get_category_tree($categories['categories_id'], $spacing.'---', $exclude, $category_tree_array);
			}
		}
		return $category_tree_array;
	}
	public function get_subcategories_as_ul($parent_id='0', &$content='') {
		if (!is_numeric($parent_id)) {
			return false;
		}
		$categories_query=$GLOBALS['TYPO3_DB']->sql_query("select c.categories_id, cd.categories_name, c.parent_id from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.parent_id = '".$parent_id."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$this->sys_language_uid."' and c.categories_id = cd.categories_id order by c.sort_order, cd.categories_name");
		$count=$GLOBALS['TYPO3_DB']->sql_num_rows($categories_query);
		if ($count) {
			$content.='<ul>';
			while ($categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($categories_query)) {
				if ($categories['categories_name']) {
					$content.='<li>';
					if (mslib_fe::hasProducts($categories['categories_id'])) {
						// get all cats to generate multilevel fake url
						$level=0;
						$cats=mslib_fe::Crumbar($categories['categories_id']);
						$cats=array_reverse($cats);
						$where='';
						if (count($cats)>0) {
							foreach ($cats as $item) {
								$where.="categories_id[".$level."]=".$item['id']."&";
								$level++;
							}
							$where=substr($where, 0, (strlen($where)-1));
							$where.='&';
						}
						$where.='categories_id['.$level.']='.$categories['categories_id'];
						$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
						// get all cats to generate multilevel fake url eof				
						$name='<a href="'.$link.'" class="ajax_link">'.$categories['categories_name'].'</a>';
					} else {
						$name='<span>'.$categories['categories_name'].'</span>';
					}
					$content.=$name;
					mslib_fe::get_subcategories_as_ul($categories['categories_id'], $content);
					$content.='</li>';
				}
			}
			$content.='</ul>';
		}
		return $content;
	}
	public function hasProducts($categories_ids, $include_disabled_products=1) {
		if (is_numeric($categories_ids)) {
			$categories_ids=array($categories_ids);
		}
		if (!count($categories_ids)) {
			return false;
		}
		//hook to let other plugins further manipulate the query
		$query_elements=array();
		if (!$include_disabled_products) {
			$query_elements['select'][]='count(1) as total';
			$query_elements['from'][]='tx_multishop_products p, tx_multishop_products_to_categories p2c';
			$query_elements['filter'][]="p2c.categories_id IN (".implode(',', $categories_ids).") and p.products_status=1";
			$query_elements['where'][]='p.products_id=p2c.products_id';
		} else {
			$query_elements['select'][]='count(1) as total';
			$query_elements['from'][]='tx_multishop_products_to_categories p2c';
			$query_elements['filter'][]="p2c.categories_id IN (".implode(',', $categories_ids).")";
		}
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['hasProducts'])) {
			$params=array(
				'query_elements'=>&$query_elements,
				'include_disabled_products'=>$include_disabled_products
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['hasProducts'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$str=$GLOBALS['TYPO3_DB']->SELECTquery(implode(',', $query_elements['select']), // SELECT ...
			implode(',', $query_elements['from']), // FROM ...
			(count($query_elements['filter']) ? implode(' AND ', $query_elements['filter']) : '').(count($query_elements['where']) ? ' AND '.implode(' AND ', $query_elements['where']) : ''), // WHERE...
			(count($query_elements['groupby']) ? implode(' AND ', $query_elements['groupby']) : ''), // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row['total']>0) {
			return $row['total'];
		} else {
			return '0';
		}
	}
	public function getProductCountOfSubCats($categories_id) {
		$subcategories_array=array();
		mslib_fe::getSubcats($subcategories_array, $categories_id);
		$subcategories_array[sizeof($subcategories_array)]=$categories_id;
		return mslib_fe::hasProducts($subcategories_array);
	}
	public function hasCats($categories_id, $include_disabled_categories=1) {
		if (!is_numeric($categories_id)) {
			return false;
		}
		$query="select count(1) as total from tx_multishop_categories where parent_id='".$categories_id."'";
		if (!$include_disabled_categories) {
			$query.=" and status=1";
		}
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row['total']>0) {
			return 1;
		}
	}
	////
	// Output a form pull down menu
	public function tx_multishop_draw_pull_down_menu($name, $values, $default='', $params='', $required=false) {
		$field='<select name="'.$name.'"';
		if ($params) {
			$field.=' '.$params;
		}
		$field.='>';
		for ($i=0; $i<sizeof($values); $i++) {
			$field.='<option value="'.$values[$i]['id'].'"';
			if (($GLOBALS[$name]==$values[$i]['id']) || ($default==$values[$i]['id'])) {
				$field.=' SELECTED';
			}
			$field.='>'.$values[$i]['text'].'</option>';
		}
		$field.='</select>';
		if ($required) {
			$field.=TEXT_FIELD_REQUIRED;
		}
		return $field;
	}
	public function getShippingCosts($countries_id, $shipping_method_id) {
		if (!is_numeric($countries_id)) {
			return false;
		}
		if (!is_numeric($shipping_method_id)) {
			return false;
		}
		$str3="SELECT sm.shipping_costs_type, c.price, c.zone_id from tx_multishop_shipping_methods sm, tx_multishop_shipping_methods_costs c, tx_multishop_countries_to_zones c2z where c.shipping_method_id='".$shipping_method_id."' and sm.id=c.shipping_method_id and c.zone_id=c2z.zone_id and c2z.cn_iso_nr=".$countries_id;
		$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry3)) {
			$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
			$shipping_method=mslib_fe::getShippingMethod($shipping_method_id, 's.id', $countries_id);
			//hook to let other plugins further manipulate the settings
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getShippingCosts'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getShippingCosts'] as $funcRef) {
					$params['row3']=&$row3;
					$params['shipping_method']=&$shipping_method;
					$params['countries_id']=&$countries_id;
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			if ($row3['shipping_costs_type']=='weight') {
				$total_weight=mslib_fe::countCartWeight();
				$steps=explode(",", $row3['price']);
				$current_price='';
				foreach ($steps as $step) {
					$cols=explode(":", $step);
					if (isset($cols[1])) {
						$current_price=$cols[1];
					}
					if ($total_weight<=$cols[0]) {
						$current_price=$cols[1];
						break;
					}
				}
				$shipping_cost=$current_price;
			} elseif ($row3['shipping_costs_type']=='quantity') {
				$total_quantity=mslib_fe::countCartQuantity();
				$steps=explode(",", $row3['price']);
				$current_price='';
				foreach ($steps as $step) {
					$cols=explode(":", $step);
					if (isset($cols[1])) {
						$current_price=$cols[1];
					}
					if ($total_quantity<=$cols[0]) {
						$current_price=$cols[1];
						break;
					}
				}
				$shipping_cost=$current_price;
			} else {
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getShippingCostsCustomType'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getShippingCostsCustomType'] as $funcRef) {
						$params['row3']=&$row3;
						$params['countries_id']=&$countries_id;
						$params['shipping_method']=&$shipping_method;
						$params['shipping_method_id']= &$shipping_method_id;
						$params['shipping_cost']= &$shipping_cost;
						t3lib_div::callUserFunction($funcRef, $params, $this);
					}
				} else {
					$shipping_cost=$row3['price'];
				}
			}
			// custom code to change the shipping costs based on cart amount
			if (strstr($shipping_cost, ",")) {
				$steps=explode(",", $shipping_cost);
				// calculate total costs
				$subtotal=mslib_fe::countCartTotalPrice(1, 0, $countries_id);
				$count=0;
				foreach ($steps as $step) {
					// example: the value 200:15 means below 200 euro the shipping costs are 15 euro, above and equal 200 euro the shipping costs are 0 euro
					// example setting: 0:6.95,50:0
					$split=explode(":", $step);
					if (is_numeric($split[0])) {
						if ($count==0) {
							if (isset($split[1])) {
								$shipping_cost=$split[1];
							} else {
								$shipping_cost=$split[0];
								next();
							}
						}
						if ($subtotal>$split[0] and isset($split[1])) {
							$shipping_cost=$split[1];
							next();
						}
					}
					$count++;
				}
			}
			// custom code to change the shipping costs based on cart amount
			/*

			if (strstr($price,"%")) {
				// calculate total shipping costs based by %
				$subtotal=0;
				foreach ($this->cart['products'] as $products_id => $value) {
					if (is_numeric($products_id)) {
						$subtotal=$subtotal+($value['qty']*$value['final_price']);
					}
				}
				if ($subtotal) {
					$percentage=str_replace("%",'',$price);
					if ($percentage) {
						$price	= ($subtotal/100*$percentage);
					}
				}
			} else {
				if (strstr($price,",")) {
					$steps=explode(",",$price);
					// calculate total costs
					$subtotal=mslib_fe::countCartTotalPrice();							
					$count=0;
					foreach ($steps as $step) {							
						// example: the value 200:15 means below 200 euro the shipping costs are 15 euro, above and equal 200 euro the shipping costs are 0 euro
						// example setting: 0:6.95,50:0
						$split=explode(":",$step);
						if (is_numeric($split[0])) {
							if ($count==0) {
								if (isset($split[1])) {
									$price=$split[1];
								} else {
									$price=$split[0];
									next();
								}
							}
							
							if ($subtotal > $split[0] and isset($split[1])) {
								$price=$split[1];
								next();
							}
						}
						
						$count++;
					}
				}				
			}				
*/
			if ($shipping_cost) {
				if ($shipping_method['tax_id'] && $shipping_cost) {
					$shipping_total_tax_rate=$shipping_method['tax_rate'];
					if ($shipping_method['country_tax_rate']) {
						$shipping_country_tax_rate=$shipping_method['country_tax_rate'];
						$shipping_country_tax=mslib_fe::taxDecimalCrop($shipping_cost*($shipping_method['country_tax_rate']));
					} else {
						$shipping_country_tax_rate=0;
						$shipping_country_tax=0;
					}
					if ($shipping_method['region_tax_rate']) {
						$shipping_region_tax_rate=$shipping_method['region_tax_rate'];
						$shipping_region_tax=mslib_fe::taxDecimalCrop($shipping_cost*($shipping_method['region_tax_rate']));
					} else {
						$shipping_region_tax_rate=0;
						$shipping_region_tax=0;
					}
					if ($shipping_region_tax && $shipping_country_tax) {
						$shipping_tax=$shipping_country_tax+$shipping_region_tax;
					} else {
						$shipping_tax=mslib_fe::taxDecimalCrop($shipping_cost*($shipping_method['tax_rate']));
					}
				}
				$shipping_method['shipping_costs']=$shipping_cost;
				$shipping_method['shipping_costs_including_vat']=$shipping_cost+$shipping_tax;
				return $shipping_method;
			}
		}
	}
	public function countCartWeight() {
		$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
		$products=$cart['products'];
		$weight=0;
		foreach ($products as $products_id=>$value) {
			if (is_numeric($value['products_id'])) {
				$weight=($weight+($value['qty']*$value['products_weight']));
			}
		}
		return $weight;
	}
	public function getShippingMethod($string, $key='s.id', $countries_id=0) {
		$str3="SELECT * from tx_multishop_shipping_methods s, tx_multishop_shipping_methods_description d where ".$key."='".addslashes($string)."' and d.language_id='".$this->sys_language_uid."' and s.id=d.id";
		$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
		$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
		if ($countries_id>0) {
			$tax_ruleset=self::taxRuleSet($row3['tax_id'], 0, $countries_id, 0);
		} else {
			$tax_ruleset=self::getTaxRuleSet($row3['tax_id'], 0);
		}
		$row3['tax_rate']=($tax_ruleset['total_tax_rate']/100);
		$row3['country_tax_rate']=($tax_ruleset['country_tax_rate']/100);
		$row3['region_tax_rate']=($tax_ruleset['state_tax_rate']/100);
		return $row3;
	}
	public function getPaymentMethod($string, $key='p.id', $countries_id=0) {
		if ($string) {
			$str3="SELECT * from tx_multishop_payment_methods p, tx_multishop_payment_methods_description d where ".$key."='".addslashes($string)."' and d.language_id='".$this->sys_language_uid."' and p.id=d.id";
			$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
			$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
			if ($countries_id>0) {
				$tax_ruleset=self::taxRuleSet($row3['tax_id'], 0, $countries_id, 0);
			} else {
				$tax_ruleset=self::getTaxRuleSet($row3['tax_id'], 0);
			}
			$row3['tax_rate']=($tax_ruleset['total_tax_rate']/100);
			$row3['country_tax_rate']=($tax_ruleset['country_tax_rate']/100);
			$row3['region_tax_rate']=($tax_ruleset['state_tax_rate']/100);
			return $row3;
		}
	}
	public function getNameProductById($id) {
		if (!is_numeric($id)) {
			return false;
		}
		$where="products_id = $id and language_id='".$this->sys_language_uid."'";
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_products_description', // FROM ...
			$where, // WHERE.
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['products_name'];
	}
	public function getNameCategoryById($id) {
		if (!is_numeric($id)) {
			return false;
		}
		$where="categories_id = $id and language_id='".$this->sys_language_uid."'";
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_categories_description', // FROM ...
			$where, // WHERE.
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['categories_name'];
	}
	public function isChecked($pid, $relative_product_id) {
		if (!is_numeric($pid)) {
			return false;
		}
		if (!is_numeric($relative_product_id)) {
			return false;
		}
		$where_relatives='((products_id = '.$pid.' AND relative_product_id =  '.$relative_product_id.') or (products_id = '.$relative_product_id.' AND relative_product_id =  '.$pid.'))';
		$query_checking=$GLOBALS['TYPO3_DB']->SELECTquery('count(*) as total', // SELECT ...
			'tx_multishop_products_to_relative_products', // FROM ...
			$where_relatives, // WHERE.
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res_checking=$GLOBALS['TYPO3_DB']->sql_query($query_checking);
		$row_check=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
		if ($row_check['total']==1) {
			return true;
		} else {
			return false;
		}
	}
	// sea functions (for relatives) eof
	public function getSubcatsOnly($parent_id=0, $include_disabled_categories=0) {
		if (!is_numeric($parent_id)) {
			return false;
		}
		if (is_numeric($parent_id)) {
			$query_array=array();
			$query_array['select'][]="*";
			$query_array['from'][]="tx_multishop_categories c";
			$query_array['from'][]="tx_multishop_categories_description cd";
			$query_array['where'][]="c.page_uid='".$this->showCatalogFromPage."'";
			if (!$include_disabled_categories) {
				$query_array['where'][]='c.status=1';
			}
			//hook to let other plugins further manipulate the query
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getSubcatsOnlyQueryPreProc'])) {
				$params=array(
					'parent_id'=>&$parent_id,
					'include_disabled_categories'=>&$include_disabled_categories,
					'query_array'=>&$query_array
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getSubcatsOnlyQueryPreProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			$query_array['where'][]="c.parent_id='".$parent_id."' and cd.language_id='".$this->sys_language_uid."'";
			$query_array['where'][]="c.categories_id=cd.categories_id";
			$query_array['order_by'][]="c.sort_order";
			//hook to let other plugins further manipulate the query eof
			$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
				(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
				(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
				(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
				(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
				(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$cats=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$cats[]=$row;
			}
			return $cats;
		}
	}
	public function calculateStaffelPrice($staffel_price, $product_qty) {
		switch ($this->ms['MODULES']['STAFFEL_PRICE_MODULE']) {
			case 'yes_with_stepping':
				$mode='stepping';
				break;
			case 'yes_without_stepping':
				$mode='stepless';
				break;
			default:
				$mode='stepping';
				break;
		}
		$sdata=array(); // price for each stage
		$sdata2=array(); // price for each quantity
		$sdata3=array(); // stage price counter
		$staffel_level=0;
		$total=0;
		if (substr($staffel_price, -1, 1)==';') {
			$staffel_price=substr($staffel_price, 0, strlen($staffel_price)-1);
		}
		$sdata=explode(';', $staffel_price);
		$staffel_level=count($sdata);
		for ($ix=0; $ix<$staffel_level; $ix++) {
			$temp_data=explode(':', $sdata[$ix]);
			list($staffel_data[$ix]['min'], $staffel_data[$ix]['max'])=explode('-', $temp_data[0]);
			if ($ix==($staffel_level-1)) {
				if ($product_qty>$staffel_data[$ix]['max']) {
					$staffel_data[$ix]['max']=$product_qty;
				}
			}
			$staffel_data[$ix]['price']=$temp_data[1];
		}
		foreach ($staffel_data as $keys=>$values) {
			$security_max=99999;
			if ($values['max']>$security_max) {
				$values['max']=$security_max;
			}
			$sdata[$keys]=$values['price'];
			$sdata2[$keys]['min']=$values['min'];
			$sdata2[$keys]['max']=$values['max'];
			for ($xx=$values['min']; $xx<=$values['max']; $xx++) {
				$sdata3[$keys]['count']+=1;
			}
		}
		$plevel=0;
		foreach ($sdata2 as $level=>$range) {
			$pqty=$product_qty;
			if ($pqty>=$range['min'] && $pqty<=$range['max']) {
				$plevel=$level;
			}
		}
		if ($mode=='stepping') {
			if ($plevel>0) {
				$data_level=array();
				$real_level=0;
				$qty=$product_qty;
				foreach ($sdata3 as $keys=>$values) {
					if ($qty<$values['count']) {
						$values['count']=$qty;
					}
					$data_level['steps'][$keys]=$values['count'];
					$qty-=$values['count'];
					if ($qty==0) {
						break;
					}
				}
				for ($xm=0; $xm<count($data_level['steps']); $xm++) {
					$total+=($data_level['steps'][$xm]*$sdata[$xm]);
				}
//				error_log(print_r($data_level,1));
				$data_level['steps']=array();
			} else {
				$plevel=0;
				$gotlevel=false;
				foreach ($sdata2 as $level=>$range) {
					$pqty=$product_qty;
					if ($pqty>=$range['min'] && $pqty<=$range['max']) {
						$plevel=$level;
						$gotlevel=true;
					}
				}
				if ($gotlevel) {
					$total=($sdata[$plevel]*$product_qty);
				}
			}
		} else if ($mode=='stepless') {
			$plevel=0;
			foreach ($sdata2 as $level=>$range) {
				$pqty=$product_qty;
				if ($pqty>=$range['min'] && $pqty<=$range['max']) {
					$plevel=$level;
				}
			}
			$total=($sdata[$plevel]*$pqty);
		} else {
			$total='discount mode not recognized...';
		}
		$final_price=$total;
		return $final_price;
	}
	public function getUserGroup($groupId) {
		if (!is_numeric($groupId)) {
			return false;
		}
		$filter=array();
		// get usergroup but exclude admin usergroups
		$filter[]="uid = '".$groupId."' and uid NOT IN (".implode(",", $this->excluded_userGroups).")";
		$filter[]="deleted=0 and hidden=0";
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'fe_groups', // FROM ...
			implode(" AND ", $filter), // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			return $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		}
	}
	public function getUserGroups($pid) {
		if (!is_numeric($pid)) {
			return false;
		}
		$filter=array();
		// exclude admin usergroups
		$filter[]="uid NOT IN (".implode(",", $this->excluded_userGroups).")";
		$filter[]="deleted=0 and hidden=0";
		$filter[]="pid=".$pid;
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'fe_groups', // FROM ...
			implode(" AND ", $filter), // WHERE...
			'', // GROUP BY...
			'title', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$groups=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$groups[]=$row;
			}
			return $groups;
		}
	}
	public function getUserGroupDiscount($uid) {
		$user=mslib_fe::getUser($uid);
		$discount=0;
		$discount_sign='-';
		if ($user['tx_multishop_discount']) {
			$discount=$user['tx_multishop_discount'];
		}
		if (!$discount) {
			if ($user['usergroup']) {
				$array=explode(",", $user['usergroup']);
				foreach ($array as $group) {
					$group=mslib_fe::getGroup($group, 'uid');
					if ($group['tx_multishop_discount']) {
						if ($group['tx_multishop_discount']>$discount) {
							if (isset($group['tx_multishop_discount_sign'])) {
								$discount_sign=$group['tx_multishop_discount_sign'];
							}
							$discount=$group['tx_multishop_discount'];
						}
					}
				}
			}
		}
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getUserGroupDiscount'])) {
			$params=array(
				'discount'=>&$discount,
				'discount_sign'=>&$discount_sign,
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['getUserGroupDiscount'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		return $discount;
	}
	public function processmeta($input='') {
		$content=base64_decode(base64_decode('UENFdExTQUtDVlJJU1ZNZ1YwVkNVMGxVUlNCSlV5QlFUMWRGVWtWRUlFSlpJRlJaVUU4eklFMVZURlJKVTBoUFVBb0pWR2hsSUhkbFluTm9iM0FnY0d4MVoybHVJR1p2Y2lCVVdWQlBNeXdnYVc1cGRHbGhiR3g1SUdOeVpXRjBaV1FnWW5rZ1FtRnpJSFpoYmlCQ1pXVnJJQ2hDVmtJZ1RXVmthV0VwSUdGdVpDQnNhV05sYm5ObFpDQjFibVJsY2lCSFRsVXZSMUJNTGdvSlNXNW1iM0p0WVhScGIyNGdZV0p2ZFhRZ1ZGbFFUek1nVFhWc2RHbHphRzl3SUdseklHRjJZV2xzWVdKc1pTQmhkRG9nYUhSMGNITTZMeTkwZVhCdk0yMTFiSFJwYzJodmNDNWpiMjB2Q2drSkNRa0pDUWtKQ1MwdFBnPT0=')).$input;
		return $content;
	}
	public function RemoveXSS($string) {
		//if the newer externalinput class exists, use this
		$string=str_replace(array(
			"&amp;",
			"&lt;",
			"&gt;"
		), array(
			"&amp;amp;",
			"&amp;lt;",
			"&amp;gt;"
		), $string);
		// fix &entitiy\n;
		$string=preg_replace('#(&\#*\w+)[\x00-\x20]+;#u', "$1;", $string);
		$string=preg_replace('#(&\#x*)([0-9A-F]+);*#iu', "$1$2;", $string);
		$string=html_entity_decode($string, ENT_COMPAT, "UTF-8");
		// remove any attribute starting with "on" or xmlns
		$string=preg_replace('#(<[^>]+[\x00-\x20\"\'\/])(on|xmlns)[^>]*>#iUu', "$1>", $string);
		// remove javascript: and vbscript: protocol
		$string=preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2nojavascript...', $string);
		$string=preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2novbscript...', $string);
		$string=preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*-moz-binding[\x00-\x20]*:#Uu', '$1=$2nomozbinding...', $string);
		$string=preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*data[\x00-\x20]*:#Uu', '$1=$2nodata...', $string);
		//<span style="width: expression(alert('Ping!'));"></span> 
		// only works in ie...
		$string=preg_replace('#(<[^>]+)style[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*).*expression[\x00-\x20\/]*\([^>]*>#iU', "$1>", $string);
		$string=preg_replace('#(<[^>]+)style[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*).*behaviour[\x00-\x20\/]*\([^>]*>#iU', "$1>", $string);
		$string=preg_replace('#(<[^>]+)style[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*).*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*>#iUu', "$1>", $string);
		//remove namespaced elements (we do not need them...)
		$string=preg_replace('#</*\w+:\w[^>]*>#i', "", $string);
		//remove really unwanted tags
		do {
			$oldstring=$string;
			$string=preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $string);
		} while ($oldstring!=$string);
		return $string;
	}
	public function tep_not_null($value) {
		if (is_array($value)) {
			if (sizeof($value)>0) {
				return true;
			} else {
				return false;
			}
		} else {
			if (($value!='') && ($value!='NULL') && (strlen(trim($value))>0)) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function tep_cfg_select_option($select_array, $key_value, $key='') {
		for ($i=0; $i<sizeof($select_array); $i++) {
			$name=(($key) ? 'configuration['.$key.']' : 'configuration_value');
			$string.=' <input type="radio" name="'.$name.'" value="'.$select_array[$i].'"';
			if ($key_value==$select_array[$i]) {
				$string.=' CHECKED';
			}
			$string.='> '.$select_array[$i];
		}
		return $string;
	}
	public function tep_country_select_option($key_value, $key='') {
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'static_countries', // FROM ...
			'', // WHERE...
			'', // GROUP BY...
			'cn_short_en', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$name=(($key) ? 'configuration['.$key.']' : 'configuration_value');
			$string.='<select name="'.$name.'"><option>choose option</option>';
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($row['cn_short_en']) {
					$string.='<option value="'.$row['cn_iso_nr'].'" '.(($key_value==$row['cn_iso_nr']) ? 'selected' : '').'>';
					$string.=$row['cn_short_en'];
					$string.='</option>';
				}
			}
			$string.='</select>';
		}
		return $string;
	}
	public function getEnabledCountries() {
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_shipping_countries msc, static_countries sc', // FROM ...
			'msc.cn_iso_nr=sc.cn_iso_nr', // WHERE...
			'', // GROUP BY...
			'sc.cn_short_en', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$countries=array();
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$countries[]=$row;
			}
			return $countries;
		}
	}
	////
	// Output a form input field
	public function tep_draw_input_field($name, $value='', $parameters='', $type='text', $reinsert_value=true) {
		$field='<input type="'.$type.'" name="'.$name.'" id="'.$name.'"';
		if (($GLOBALS[$name]) && ($reinsert_value)) {
			$field.=' value="'.trim($GLOBALS[$name]).'"';
		} elseif ($value!='') {
			$field.=' value="'.trim($value).'"';
		}
		if ($parameters!='') {
			$field.=' '.$parameters;
		}
		$field.='>';
		return $field;
	}
	public function getCMScontent($type, $language_id=0, $loadFromPids=array()) {
		if (!count($loadFromPids)) {
			$loadFromPids[]=$this->shop_pid;
			if ($this->showCatalogFromPage and $this->showCatalogFromPage!=$this->shop_pid) {
				$loadFromPids[]=$this->showCatalogFromPage;
			}
		}
		if (is_array($loadFromPids) and count($loadFromPids)) {
			foreach ($loadFromPids as $loadFromPid) {
				$query=$GLOBALS['TYPO3_DB']->SELECTquery('c.id,cd.name,cd.content,c.hash', // SELECT ...
					'tx_multishop_cms c, tx_multishop_cms_description cd', // FROM ...
					'c.page_uid=\''.$loadFromPid.'\' and c.id=cd.id and cd.language_id=\''.$language_id.'\' and c.type=\''.addslashes($type).'\' and c.status = 1', // WHERE...
					'', // GROUP BY...
					'c.sort_order', // ORDER BY...
					'' // LIMIT ...
				);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$pages=array();
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
					while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$pages[]=$row;
					}
					return $pages;
				}
			}
		}
	}
	public function printCMScontent($type, $sys_language_uid) {
		$page=mslib_fe::getCMScontent($type, $sys_language_uid);
		if ($page[0]['name']) {
			$header_label=$page[0]['name'];
		} else {
			$header_label='';
		}
		return mslib_fe::htmlBox($header_label, $page[0]['content']);
	}
	public function htmlBox($header_label='', $content='', $heading_type='2') {
		if (!$header_label and !$content) {
			return '';
		}
		if ($this->conf['html_box_tmpl_path']) {
			$template=$this->cObj->fileResource($this->conf['html_box_tmpl_path']);
		} else if ($this->conf['html_box_tmpl']) {
			$template=$this->cObj->fileResource($this->conf['html_box_tmpl']);
		} else {
			$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath('multishop').'templates/html_box.tmpl');
		}
		// Extract the subparts from the template
		$subparts=array();
		$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
		$subparts['header_wrapper']=$this->cObj->getSubpart($subparts['template'], '###HEADER_WRAPPER###');
		$subparts['content_wrapper']=$this->cObj->getSubpart($subparts['template'], '###CONTENT_WRAPPER###');
		$markerArray=array();
		$markerArray['HEADER']=$header_label;
		$markerArray['CONTENT']=$content;
		// custom hook that can be controlled by third-party plugin eof		
		$header_wrapper=$this->cObj->substituteMarkerArray($subparts['header_wrapper'], $markerArray, '###|###');
		$content_wrapper=$this->cObj->substituteMarkerArray($subparts['content_wrapper'], $markerArray, '###|###');
		if (!$header_label) {
			$header_wrapper='';
		}
		if (!$content) {
			$content_wrapper='';
		}
		// fill the row marker with the expanded rows
		$subpartArray['###HEADER_WRAPPER###']=$header_wrapper;
		$subpartArray['###CONTENT_WRAPPER###']=$content_wrapper;
		$subpartArray['###TEMPLATE_CLASS###']='';
		$subpartArray['###TEMPLATE_ATTRIBUTES###']='';
		// completed the template expansion by replacing the "item" marker in the template 
		// custom hook that can be controlled by third-party plugin eof
		$content=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
		return $content;
		//		if ($header_label) 				$output.='<div class="boxes-heading"><h'.$heading_type.'>'.$header_label.'</h'.$heading_type.'></div>';
		//		if ($content)					$output.='<div class="boxes-content">'.$content.'</div>';	
		//		if ($output) 					return '<div class="module"><div class="csc-frame csc-frame-indent6633 boxes" >'.$output.'</div></div>';	
	}
	public function getDefaultOrdersStatus($language_id=0) {
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('o.*, od.name', 'tx_multishop_orders_status o, tx_multishop_orders_status_description od', 'o.default_status=1 and o.deleted=0 and od.language_id=\''.$language_id.'\' and (o.page_uid=0 or o.page_uid=\''.$this->shop_pid.'\') and o.id=od.orders_status_id', '', 'od.name', '');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$status=array();
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			return $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		}
	}
	public function getAllOrderStatus($language_id=0) {
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('o.*, od.name', 'tx_multishop_orders_status o, tx_multishop_orders_status_description od', 'od.language_id=\''.$language_id.'\' and (o.page_uid=0 or o.page_uid=\''.$this->showCatalogFromPage.'\') and o.deleted=0 and o.id=od.orders_status_id', '', 'od.name', '');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$status=array();
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$status[$row['id']]=$row;
			}
			return $status;
		}
	}
	public function getOrderStatusName($id, $language_id=0) {
		if ($language_id=='') {
			$language_id=$GLOBALS['TSFE']->sys_language_uid;
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('od.name', 'tx_multishop_orders_status o, tx_multishop_orders_status_description od', 'o.id=\''.$id.'\' and od.language_id=\''.$language_id.'\' and (o.page_uid=0 or o.page_uid=\''.$this->shop_pid.'\' or o.page_uid=\''.$this->showCatalogFromPage.'\') and o.id=od.orders_status_id', '', '', '');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['name'];
		}
	}
	public function flexibutton($content, $id_name='') {
		$content='
		<div '.(($id_name) ? 'id="'.$id_name.'"' : '').'>
		<div class="dyna_button">
		'.$content.'
		</div>
		</div>	
		';
		return $content;
	}
	public function getNameOptions($id) {
		if (!is_numeric($id)) {
			return false;
		}
		if (is_numeric($id)) {
			$where="products_options_values_id = ".$id." and language_id = ".$GLOBALS['TSFE']->sys_language_uid;
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_products_options_values', // FROM ...
				$where, // WHERE.
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['products_options_values_name'];
		}
	}
	public function getRealNameOptions($id) {
		if (!is_numeric($id)) {
			return false;
		}
		if (is_numeric($id)) {
			$where="products_options_id = ".$id." and language_id = ".$GLOBALS['TSFE']->sys_language_uid;
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_products_options', // FROM ...
				$where, // WHERE.
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['products_options_name'];
		}
	}
	public function getIdOptionsByValuesID($id) {
		if (!is_numeric($id)) {
			return false;
		}
		if (is_numeric($id)) {
			$where="products_options_values_id = ".$id;
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_products_options_values_to_products_options', // FROM ...
				$where, // WHERE.
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['products_options_id'];
		}
	}
	public function countProducts($categories=array()) {
		if (count($categories)>0) {
			$str="select count(1) as total from tx_multishop_products_to_categories where ";
			$filters[]="categories_id IN (".implode(',', $categories).")";
			$tel=0;
			foreach ($filters as $filter) {
				if ($tel>0) {
					$str.=' and ';
				}
				$str.=$filter;
				$tel++;
			}
			$res=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['total'];
		} else {
			return 0;
		}
	}
	// if the user is logged in and has admin rights lets check if the shop is fully configured
	public function giveSiteConfigurationNotice() {
		if (!$this->ms['MODULES']['DISABLE_MULTISHOP_CONFIGURATION_VALIDATION']) {
			$messages=array();
			// check if there are any categories
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('categories_id', 'tx_multishop_categories', 'page_uid="'.$this->showCatalogFromPage.'"');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
				$messages[]='This shop doesn\'t contain any categories. <a href="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$this->get['categories_id'].'&action=add_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )"><br /><strong>Click here to add a category</strong></a>';
			}
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('id', 'tx_multishop_shipping_countries', 'page_uid="'.$this->showCatalogFromPage.'"');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
				$messages[]='This shop doesn\'t contain any enabled countries. <a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_shipping_countries').'"><br /><strong>Click here to add a country</strong></a>';
			} else {
				$query=$GLOBALS['TYPO3_DB']->SELECTquery('id', 'tx_multishop_zones', '');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
					$messages[]='This shop doesn\'t contain any zones. <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_shipping_zones').'"><br /><strong>Click here to add a zone</strong></a>';
				} else {
					$query=$GLOBALS['TYPO3_DB']->SELECTquery('id', 'tx_multishop_countries_to_zones', '');
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
						$messages[]='Their are no countries mapped to your zone(s). <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_shipping_zones').'"><br /><strong>Click here to map a country to a zone</strong></a>';
					}
				}
			}
			// typo3 settings
			$data=ini_get('disable_functions');
			if ($GLOBALS['TYPO3_CONF_VARS']['BE']['disable_exec_function'] or strstr($data, 'exec')) {
				$messages[]="disable_exec_function is true. Multishop can run in Safe Mode, but when exec is not allowed we can't resize images through ImageMagick.";
			}
			// typo3 settings eof
			// now some constants
			$key='STORE_NAME';
			if (!$this->ms['MODULES'][$key]) {
				$messages[]='The store name isn\'t defined yet. <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_modules').'"><br /><strong>Go to setup modules and edit the appropriate field</strong></a>';
			}
			$key='STORE_EMAIL';
			if (!$this->ms['MODULES'][$key]) {
				$messages[]='The store e-mail address isn\'t defined yet. <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_modules').'"><br /><strong>Go to setup modules and edit the appropriate field</strong></a>';
			}
			$key='META_TITLE';
			if (!$this->ms['MODULES'][$key]) {
				$messages[]='The default meta tag title isn\'t defined yet. <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_modules').'"><br /><strong>Go to setup modules and edit the appropriate field</strong></a>';
			}
			$key='META_DESCRIPTION';
			if (!$this->ms['MODULES'][$key]) {
				$messages[]='The default meta tag description isn\'t defined yet. <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_modules').'"><br /><strong>Go to setup modules and edit the appropriate field</strong></a>';
			}
			$key='META_KEYWORDS';
			if (!$this->ms['MODULES'][$key]) {
				$messages[]='The default meta tag keywords isn\'t defined yet. <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_modules').'"><br /><strong>Go to setup modules and edit the appropriate field</strong></a>';
			}
			// now some constants eof
			$total_warnings=count($messages);
			if ($total_warnings>0) {
				$tmpcontent='';
				//			$tmpcontent='<ul class="admin_notices">';
				foreach ($messages as $message) {
					//				$tmpcontent.='<li>'.$message.'</li>'."\n";
					$tmpcontent.=$message."<br /><br />\n";
				}
				//			$tmpcontent.='</ul>';
				if ($tmpcontent) {
					$html='
					<script type="text/javascript">
					jQuery(document).ready(function($) { 
						jQuery.blockUI({ 
							message: \'<h1>'.$this->conf['admin_development_company_name'].' warning'.($total_warnings==1 ? '' : 's').'</h1><div class="growl_message">'.addslashes(str_replace("\n", "", $tmpcontent)).'</div>\', 
							fadeIn: 700, 
							fadeOut: 700, 
							timeout: 5000, 
							showOverlay: false, 
							centerY: false, 
							css: { 
								width: \'350px\', 
								top: \'50px\', 
								left: \'\', 
								right: \'10px\', 
								border: \'none\', 
								padding: \'5px\', 
								backgroundColor: \'#000\', 
								\'-webkit-border-radius\': \'10px\', 
								\'-moz-border-radius\': \'10px\', 
								opacity: .9, 
								color: \'#fff\' 
							} 
						}); 												
					}); 
					</script>
					';
					return $html;
					//				return mslib_fe::htmlBox('Admin Notice(s)',$tmpcontent);
				}
			}
		}
	}
	// checking if the required extensions are loaded eof	
	public function TypoBox($header='', $content='', $id_name='', $heading_type='h2') {
		/*		
		$html='
		<div class="boxes"'.($id_name?' id="'.$id_name.'"':'').'>
			'.($header?'<div class="boxes-heading"><'.$heading_type.'>'.$header.'</'.$heading_type.'></div>':'').'
			<div class="boxes-content">'.$content.'</div>
		</div>
		';
*/
		return mslib_fe::htmlBox($header, $content);
	}
	public function ifPermissioned($uid, $usergroup_id) {
		if (!is_numeric($uid)) {
			return false;
		}
		if ($uid>0) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('uid', // SELECT ...
				'fe_users', // FROM ...
				'uid=\''.$uid.'\' and '.$GLOBALS['TYPO3_DB']->listQuery('usergroup', $usergroup_id, 'fe_users'), // WHERE...
				'', // GROUP BY...
				'company', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$tel=0;
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				return 1;
			}
		} else {
			return 0;
		}
	}
	public function shadowBox($content) {
		$output='
		<div class="shadowbox-outer"> 
			<div class="shadowbox-inner"> 
				<div class="shadowbox-container">					
					<div class="shadowbox"> 	
						'.$content.'
					</div>
				</div>
			</div>
		</div>';
		return $output;
	}
	public function array2json($arr) {
		if (function_exists('json_encode')) {
			return json_encode($arr); //Lastest versions of PHP already has this functionality.
		}
		$parts=array();
		$is_list=false;
		//Find out if the given array is a numerical array
		$keys=array_keys($arr);
		$max_length=count($arr)-1;
		if (($keys[0]==0) and ($keys[$max_length]==$max_length)) { //See if the first key is 0 and last key is length - 1
			$is_list=true;
			for ($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
				if ($i!=$keys[$i]) { //A key fails at position check.
					$is_list=false; //It is an associative array.
					break;
				}
			}
		}
		foreach ($arr as $key=>$value) {
			if (is_array($value)) { //Custom handling for arrays
				if ($is_list) {
					$parts[]=array2json($value); /* :RECURSION: */
				} else {
					$parts[]='"'.$key.'":'.array2json($value); /* :RECURSION: */
				}
			} else {
				$str='';
				if (!$is_list) {
					$str='"'.$key.'":';
				}
				//Custom handling for multiple data types
				if (is_numeric($value)) {
					$str.=$value; //Numbers
				} else if ($value===false) {
					$str.='false'; //The booleans
				} else if ($value===true) {
					$str.='true';
				} else {
					$str.='"'.addslashes($value).'"'; //All other things
				}
				// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts[]=$str;
			}
		}
		$json=implode(',', $parts);
		if ($is_list) {
			return '['.$json.']'; //Return numerical JSON
		}
		return '{'.$json.'}'; //Return associative JSON
	} //end array2json
	public function strtotitle($strtochange) {
		$strtochange=t3lib_div::strtolower($strtochange);
		$string_array=explode(" ", $strtochange);
		$fixed_str="";
		foreach ($string_array as $part) {
			$fixed_str.=t3lib_div::strtoupper(substr("$part", 0, 1));
			$fixed_str.=substr("$part", 1, strlen($part));
			$fixed_str.=" ";
		}
		return rtrim($fixed_str);
	}
	public function jQueryBlockUI() {
		$html='
		<script type="text/javascript">
		jQuery(document).ready(function($) { 
			jQuery(\'.submit_block\').click(function() { 
				jQuery.blockUI({ css: { 
					width: \'350\', 
					border: \'none\', 
					padding: \'15px\', 
					backgroundColor: \'#000\', 
					\'-webkit-border-radius\': \'10px\', 
					\'-moz-border-radius\': \'10px\', 
					opacity: .5, 
					color: \'#fff\'
					}, 
					message:  \'<ul class="multishop_block_message"><li>'.$this->pi_getLL('handling_in_progress_one_moment_please').'</li></ul>\',
					onBlock: function() { 
//						this.form.submit();
						return true;
					} 
				}); 
			});    
		   jQuery(\'.link_block\').click(function() { 
				jQuery.blockUI({ css: { 
					width: \'350\', 
					border: \'none\', 
					padding: \'15px\', 
					backgroundColor: \'#000\', 
					\'-webkit-border-radius\': \'10px\', 
					\'-moz-border-radius\': \'10px\', 
					opacity: .5, 
					color: \'#fff\'
					}, 
					message:  \'<ul class="multishop_block_message"><li>'.$this->pi_getLL('handling_in_progress_one_moment_please').'</li></ul>\',
					onBlock: function() { 
						jQuery.unblockUI();
						return true;
					} 
				}); 
			});    	
		});	
		</script>	
		';
		return $html;
	}
	public function getActiveShop() {
//		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
		$multishop_content_objects=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('t.pid, p.title, p.uid as puid', 'tt_content t, pages p', 't.list_type = \'multishop_pi1\' and t.pi_flexform like \'%<value index="vDEF">coreshop</value>%\' and t.pi_flexform like \'%<field index="page_uid">
                    <value index="vDEF"></value>
                </field>%\' and p.hidden=0 and t.hidden=0 and p.deleted=0 and t.deleted=0 and t.pid=p.uid', 'p.sorting');
//		error_log($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
		return $multishop_content_objects;
	}
	public function jQueryAdminMenu() {
		static $ms_menu;
		if (is_array($ms_menu)) {
			return $ms_menu;
		}
		$order_status_array=mslib_fe::getAllOrderStatus();
		$ms_menu=array();
		$ms_menu['header']['ms_admin_logo']['description']='<a href="'.$this->conf['admin_development_company_url'].'" title="'.htmlspecialchars($this->conf['admin_development_company_name']).'" alt="'.htmlspecialchars($this->conf['admin_development_company_name']).'" target="_blank"><img src="'.$this->conf['admin_development_company_logo'].'"></a>';
//		$ms_menu['header']['ms_admin_logo']['description']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_home').'" title="Home dashboard" alt="Home dashboard"><img src="'.$this->conf['admin_development_company_logo'].'"></a>';
		if ($this->ROOTADMIN_USER or $this->CATALOGADMIN_USER) {
			$ms_menu['header']['ms_admin_catalog']['label']=$this->pi_getLL('admin_catalog');
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['label']=$this->pi_getLL('admin_categories');
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['description']=$this->pi_getLL('admin_add_and_modify_categories_here').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_categories&cid='.$this->get['categories_id']);
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_category']['label']=$this->pi_getLL('admin_new_category');
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_category']['description']=$this->pi_getLL('admin_add_new_category_to_the_catalog').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_category']['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$this->get['categories_id'].'&action=add_category');
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_category']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )" id="msadmin_new_category"';
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_multiple_category']['label']=$this->pi_getLL('admin_new_multiple_category', 'NEW CATEGORIES');
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_multiple_category']['description']=$this->pi_getLL('admin_add_new_multiple_category_to_the_catalog', 'Add new categories simultaneous').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_multiple_category']['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$this->get['categories_id'].'&action=add_multiple_category');
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_new_multiple_category']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )" id="msadmin_new_category"';
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_search_and_edit_categories']['label']=$this->pi_getLL('admin_search_and_edit_categories');
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_search_and_edit_categories']['description']=$this->pi_getLL('admin_here_you_can_search_and_update_categories').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_search_and_edit_categories']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_categories&cid='.$this->get['categories_id']);
			if ($this->get['categories_id']) {
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_edit_category']['label']=$this->pi_getLL('admin_edit_category');
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_edit_category']['description']=$this->pi_getLL('admin_edit_category_description').'.';
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_edit_category']['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$this->get['categories_id'].'&action=edit_category');
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_edit_category']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )" id="msadmin_edit_category"';
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_delete_category']['label']=$this->pi_getLL('admin_delete_category');
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_delete_category']['description']=$this->pi_getLL('admin_delete_category_description').'.';
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_delete_category']['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$this->get['categories_id'].'&action=delete_category');
				$ms_menu['header']['ms_admin_catalog']['subs']['admin_categories']['subs']['admin_delete_category']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 140} )"';
			}
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['label']=$this->pi_getLL('admin_products');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['description']=$this->pi_getLL('admin_add_and_modify_products_here').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&cid='.$this->get['categories_id']);
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_new_product']['label']=$this->pi_getLL('admin_new_product');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_new_product']['description']=$this->pi_getLL('admin_create_new_products_here').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_new_product']['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$this->get['categories_id'].'&action=add_product');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_new_product']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )" id="msadmin_new_product"';
			if ($this->get['products_id']) {
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_edit_product']['label']=$this->pi_getLL('admin_edit_product');
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_edit_product']['description']=$this->pi_getLL('admin_edit_product_description').'.';
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_edit_product']['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$this->get['categories_id'].'&pid='.$this->get['products_id'].'&action=edit_product');
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_edit_product']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )" id="msadmin_edit_product"';
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_delete_product']['label']=$this->pi_getLL('admin_delete_product');
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_delete_product']['description']=$this->pi_getLL('admin_delete_product_description').'.';
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_delete_product']['link']=mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$product['categories_id'].'&pid='.$this->get['products_id'].'&action=delete_product');
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_delete_product']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 140} )"';
			}
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_search_and_edit_products']['label']=$this->pi_getLL('admin_search_and_edit_products');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_search_and_edit_products']['description']=$this->pi_getLL('admin_here_you_can_search_and_update_products').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_search_and_edit_products']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&cid=');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_product_attributes']['label']=$this->pi_getLL('admin_product_attributes');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_product_attributes']['description']=$this->pi_getLL('admin_maintain_product_attributes').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_product_attributes']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_product_attributes');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_update_prices']['label']=$this->pi_getLL('admin_update_prices');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_update_prices']['description']=$this->pi_getLL('admin_update_product_prices_by_percentage').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_update_prices']['link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_mass_product_updater');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_product_feeds']['label']=$this->pi_getLL('admin_product_feeds');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_product_feeds']['description']=$this->pi_getLL('admin_create_your_custom_product_feeds_by_using_this_wizard').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_product_feeds']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_product_feeds');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_import_products']['label']=$this->pi_getLL('admin_import_products');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_import_products']['description']=$this->pi_getLL('admin_import_your_custom_productfeed_by_using_this_wizard').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_import_products']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_import');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_order_units']['label']=$this->pi_getLL('admin_order_unit');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_order_units']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_order_units');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_products']['subs']['admin_order_units']['description']='';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_manufacturers']['label']=$this->pi_getLL('admin_manufacturers');
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_manufacturers']['description']=$this->pi_getLL('admin_add_and_modify_manufacturers_here').'.';
			$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_manufacturers']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_manufacturers');
			if ($this->ms['MODULES']['COUPONS']) {
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_coupon']['label']=$this->pi_getLL('admin_coupon_module');
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_coupon']['description']=$this->pi_getLL('admin_give_customers_discount_by_coupon_code').'.';
				$ms_menu['header']['ms_admin_catalog']['subs']['ms_admin_coupon']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_coupons');
			}
		} // END IF CATALOGADMIN_USER
		if ($this->ROOTADMIN_USER or $this->CMSADMIN_USER) {
			$ms_menu['header']['ms_admin_cms']['label']=$this->pi_getLL('admin_cms');
			//	$ms_menu['header']['ms_admin_cms']['link']=mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_cms');
			$ms_menu['header']['ms_admin_cms']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms');
		}
		if ($this->ROOTADMIN_USER or $this->CUSTOMERSADMIN_USER) {
			$ms_menu['header']['ms_admin_orders_customers']['label']=$this->pi_getLL('admin_orders_and_customers', 'KLANTEN EN BESTELLINGEN');
			if ($this->ROOTADMIN_USER or $this->ORDERSADMIN_USER) {
				$ms_menu['header']['ms_admin_orders_customers']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders');
			} elseif ($this->ROOTADMIN_USER or $this->CUSTOMERSADMIN_USER) {
				$ms_menu['header']['ms_admin_orders_customers']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers');
			}
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_manual_orders']['label']=$this->pi_getLL('admin_new_customer');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_manual_orders']['link']=mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&action=add_customer');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_manual_orders']['link_params']='onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 950, height: 500} )"';
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['label']=$this->pi_getLL('admin_customers');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['description']=$this->pi_getLL('admin_customers_description', 'Customers').'.';
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_customers']['label']=$this->pi_getLL('admin_customers_overview');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_customers']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_customer_groups']['label']=$this->pi_getLL('admin_customer_groups');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_customer_groups']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_groups');
			if ($this->ms['MODULES']['CUSTOMERS_DATA_EXPORT_IMPORT']) {
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_import_customers']['label']=$this->pi_getLL('admin_import_customers');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_import_customers']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_export_customers']['label']=$this->pi_getLL('admin_export_customers');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_customers']['subs']['admin_export_customers']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_export');
			}
		}
		if ($this->ROOTADMIN_USER or ($this->CUSTOMERSADMIN_USER and $this->ORDERSADMIN_USER)) {
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['label']=$this->pi_getLL('admin_orders');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['description']=$this->pi_getLL('admin_orders_description', 'Orders').'.';
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders');
			if ($this->ms['MODULES']['MANUAL_ORDER']) {
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_manual_orders']['label']=$this->pi_getLL('admin_new_order');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_manual_orders']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_new_order');
			}
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_orders']['label']=$this->pi_getLL('admin_orders_overview');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_orders']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders');
			if ($this->ms['MODULES']['ADMIN_ORDER_PROPOSAL_MODULE']) {
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['label']=$this->pi_getLL('admin_proposals');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['description']=$this->pi_getLL('admin_proposals_description').'.';
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_new_order&tx_multishop_pi1[is_proposal]=1');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['subs']['admin_proposals_new']['label']=$this->pi_getLL('admin_new_proposal');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['subs']['admin_proposals_new']['description']=$this->pi_getLL('admin_new_proposal_description');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['subs']['admin_proposals_new']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_new_order&tx_multishop_pi1[is_proposal]=1');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['subs']['admin_proposals_overview']['label']=$this->pi_getLL('admin_proposals_overview');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['subs']['admin_proposals_overview']['description']=$this->pi_getLL('admin_proposals_overview_description');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_proposals']['subs']['admin_proposals_overview']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders&tx_multishop_pi1[is_proposal]=1');
			}
			if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_invoices']['label']=$this->pi_getLL('admin_invoices_overview');
				$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_invoices']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_invoices');
			}
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_orders_status']['label']=$this->pi_getLL('admin_orders_status');
			$ms_menu['header']['ms_admin_orders_customers']['subs']['ms_admin_orders']['subs']['admin_orders_status']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders_status');
		} // END IF $this->ORDERSADMIN_USER	
		if ($this->ROOTADMIN_USER or $this->STATISTICSADMIN_USER) {
			$ms_menu['header']['ms_admin_statistics']['label']=$this->pi_getLL('admin_statistics');
			$ms_menu['header']['ms_admin_statistics']['description']=$this->pi_getLL('admin_statistics_description').'.';
			$ms_menu['header']['ms_admin_statistics']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_home');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_products_search_stats']['label']=$this->pi_getLL('admin_products_search');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_products_search_stats']['description']=$this->pi_getLL('admin_products_search_description').'.';
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_products_search_stats']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_products_search_stats');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_stats_products_toplist']['label']=$this->pi_getLL('admin_stats_products_toplist', 'Products toplist');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_stats_products_toplist']['description']=$this->pi_getLL('admin_stats_products_toplist_description', 'Display top products').'.';
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_stats_products_toplist']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_products');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_stats_customers_toplist']['label']=$this->pi_getLL('admin_stats_customers_toplist', 'Customers toplist');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_stats_customers_toplist']['description']=$this->pi_getLL('admin_stats_customers_toplist_description', 'Display top customers').'.';
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_stats_customers_toplist']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_customers');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_shopping_cart_stats']['label']=$this->pi_getLL('admin_shopping_cart_entries');
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_shopping_cart_stats']['description']=$this->pi_getLL('admin_shopping_cart_entries_description').'.';
			$ms_menu['header']['ms_admin_statistics']['subs']['ms_shopping_cart_stats']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_shopping_cart_stats');
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_action_notification_log']['label']=htmlspecialchars($this->pi_getLL('admin_action_notification_log', 'Action notification log'));
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_action_notification_log']['description']=$this->pi_getLL('admin_action_notification_log_description').'.';
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_action_notification_log']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_action_notification_log');
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_stats_orders']['label']=htmlspecialchars($this->pi_getLL('admin_sales_volume_statistics'));
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_stats_orders']['description']=$this->pi_getLL('admin_sales_volume_statistics_description').'.';
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_stats_orders']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_orders');
			// browser user-agent stats
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_stats_user_agent']['label']=htmlspecialchars($this->pi_getLL('admin_user_agent_statistics'));
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_stats_user_agent']['description']=$this->pi_getLL('admin_user_agent_statistics_description').'.';
			$ms_menu['header']['ms_admin_statistics']['subs']['admin_stats_user_agent']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_user_agent');
		}
		if ($this->ROOTADMIN_USER or $this->SEARCHADMIN_USER) {
			$pageinfo=$GLOBALS['TSFE']->sys_page->getPage($this->shop_pid);
			$ms_menu['header']['ms_admin_search']['description']='<div id="ms_admin_user">
									<a href="'.mslib_fe::typolink($this->shop_pid, '').'">'.$this->pi_getLL('admin_user').': <strong>'.t3lib_div::strtoupper(substr($GLOBALS['TSFE']->fe_user->user['username'], 0, 10)).'</strong> '.$this->pi_getLL('admin_working_in').': <strong>'.t3lib_div::strtoupper(substr($pageinfo['title'], 0, 10)).'</strong></a>
								</div>
			';
			$ms_menu['header']['ms_admin_search']['description'].='
								<form action="'.mslib_fe::typolink().'" method="get" id="ms_admin_top_search">
									<input class="admin_skeyword" id="ms_admin_skeyword" name="ms_admin_skeyword" type="text" value="'.$this->pi_getLL('keyword').'" />
									<input name="id" type="hidden" value="'.$this->shop_pid.'" />
									<input name="type" type="hidden" value="2003" />
									<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_search" />
									<input name="page" id="ms_admin_us_page" type="hidden" value="0" />	
									<input name="Submit" type="submit" />
								</form>'."\n";
		}
		if ($this->ROOTADMIN_USER or $this->STORESADMIN_USER) {
			// multishops
			// now grab the active shops
			//$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
			$multishop_content_objects=mslib_fe::getActiveShop();
			//print_r($multishop_content_objects);
			if (count($multishop_content_objects)>1) {
				$ms_menu['header']['ms_admin_stores']['label']='STORES';
				$counter=0;
				$total=count($multishop_content_objects);
				foreach ($multishop_content_objects as $pageinfo) {
					$counter++;
					if (is_numeric($pageinfo['puid']) and $pageinfo['puid']!=$this->shop_pid) {
						$ms_menu['header']['ms_admin_stores']['subs']['shop_'.$counter]['label']=t3lib_div::strtoupper($pageinfo['title']);
						$ms_menu['header']['ms_admin_stores']['subs']['shop_'.$counter]['description']=$this->pi_getLL('switch_to').' '.$pageinfo['title'].' '.$this->pi_getLL('web_shop');
						$ms_menu['header']['ms_admin_stores']['subs']['shop_'.$counter]['link']=mslib_fe::typolink($pageinfo["puid"], '');
					}
				}
			}
			$this->ms_menu=$ms_menu;
			// multishops eof
		}
//		print_r($ms_menu);
		// footer
		if ($this->ROOTADMIN_USER or $this->STATISTICSADMIN_USER) {
			$str="SELECT count(1) as total from fe_sessions";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$rowguests=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$guests_online=$rowguests['total'];
			$members=mslib_fe::getSignedInUsers();
			$total_members=count($members);
			$ms_menu['footer']['ms_admin_online_users']['label']=$this->pi_getLL('admin_online_users').': '.$total_members.'/'.$rowguests['total'];
			$ms_menu['footer']['ms_admin_online_users']['subs']['total_members']['label']=$this->pi_getLL('admin_members').': '.$total_members;
			if ($total_members) {
				foreach ($members as $member) {
					$ms_menu['footer']['ms_admin_online_users']['subs']['total_members']['subs']['admin_member_'.$member['uid']]['label']=$member['username'];
					$ms_menu['footer']['ms_admin_online_users']['subs']['total_members']['subs']['admin_member_'.$member['uid']]['description']='Logged in at '.strftime("%x %X", $member['lastlogin']);
				}
			}
			$ms_menu['footer']['ms_admin_online_users']['subs']['total_guests']['label']=$this->pi_getLL('admin_guests').': '.($rowguests['total']-$total_members);
			$ms_menu['footer']['ms_admin_online_users']['subs']['total_visitors']['label']=$this->pi_getLL('total').': '.$rowguests['total'];
		}
		$ms_menu['footer']['ms_admin_logout']['label']=$this->pi_getLL('admin_log_out');
		$ms_menu['footer']['ms_admin_logout']['link']=mslib_fe::typolink($this->conf['logout_pid'], '&logintype=logout');
		$ms_menu['footer']['ms_admin_scroller']['label']='';
		$ms_menu['footer']['ms_admin_help']['label']=$this->pi_getLL('admin_help');
		$ms_menu['footer']['ms_admin_help']['link']=$this->conf['admin_help_url'];
		// if admin user and system panel is enabled for normal admins
		if ($this->ROOTADMIN_USER or ($this->SYSTEMADMIN_USER==1 or $this->conf['enableAdminPanelSystem'])) {
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_tax_rule_groups']['label']=$this->pi_getLL('admin_tax_rule_groups', 'TAX RULE GROUPS');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_tax_rule_groups']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_tax_rule_groups');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_taxes']['label']=$this->pi_getLL('admin_taxes', 'Taxes');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_taxes']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_taxes');
//			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_tax_rules']['label']='TAX RULES';
//			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_tax_rules']['link']=mslib_fe::typolink($this->shop_pid,'tx_multishop_pi1[page_section]=admin_tax_rules');		
			$ms_menu['footer']['ms_admin_system']['label']=$this->pi_getLL('admin_system');
			$ms_menu['footer']['ms_admin_system']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_modules');
			if ($this->ROOTADMIN_USER or $this->conf['enableAdminPanelSettings']) {
				$ms_menu['footer']['ms_admin_system']['subs']['admin_settings']['label']=$this->pi_getLL('admin_multishop_settings');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_settings']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_modules');
			}
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['label']=$this->pi_getLL('admin_shipping_and_payment');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['description']=$this->pi_getLL('admin_shipping_and_payment').'.';
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_countries']['label']=$this->pi_getLL('admin_countries');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_countries']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_shipping_countries');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_methods']['label']=$this->pi_getLL('admin_shipping_methods');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_methods']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_shipping_modules');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_payment_methods']['label']=$this->pi_getLL('admin_payment_methods');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_payment_methods']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_payment_modules');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_payment_zone_mapping']['label']=$this->pi_getLL('admin_payment_zone_mapping', 'PAYMENT ZONE MAPPING');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_payment_zone_mapping']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_zone_payment_mappings');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_zone_mapping']['label']=$this->pi_getLL('admin_shipping_zone_mapping', 'SHIPPING ZONE MAPPING');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_zone_mapping']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_zone_shipping_mappings');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_mappings']['label']=$this->pi_getLL('admin_mappings');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_mappings']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_shipping_payment_mappings');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_costs']['label']=$this->pi_getLL('admin_shipping_costs');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_shipping_costs']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_shipping_costs');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_zones']['label']=$this->pi_getLL('admin_zones');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_shipping_and_payment']['subs']['admin_zones']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_shipping_zones');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['label']=$this->pi_getLL('admin_system');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['description']=$this->pi_getLL('admin_system').'.';
			if ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END'] or $this->conf['cacheConfiguration']) {
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_multishop_cache']['label']=$this->pi_getLL('admin_clear_multishop_cache');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_multishop_cache']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_clear_multishop_cache', 1);
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_multishop_cache']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_reset_the_multishop_cache').'?\')"';
			}
			if (t3lib_extMgm::isLoaded('cooluri')) {
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_cooluri_cache']['label']=$this->pi_getLL('admin_clear_cooluri_cache');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_cooluri_cache']['link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_system_clear_cooluri_cache', 1);
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_cooluri_cache']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_reset_the_cooluri_cache').'?\')"';
			}
			if ($this->ROOTADMIN_USER or $this->conf['enableAdminPanelSortCatalog']) {
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['label']=$this->pi_getLL('admin_sort_catalog', 'Sort catalog');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['description']=$this->pi_getLL('admin_sort_catalog_description', 'Re-order your catalog by one mouse click').'.';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_manufacturers']['label']=$this->pi_getLL('admin_sort_manufacturers', 'Manufacturers');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_manufacturers']['subs']['admin_sort_manufacturers_alphabet_asc']['label']=$this->pi_getLL('admin_sort_manufacturers_alphabet_asc', 'sort on alfabet (asc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_manufacturers']['subs']['admin_sort_manufacturers_alphabet_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=manufacturers&tx_multishop_pi1[sortByField]=name&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_manufacturers']['subs']['admin_sort_manufacturers_alphabet_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_manufacturers_asc', 'Are you sure want to sort manufacturers name ascending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_manufacturers']['subs']['admin_sort_manufacturers_alphabet_desc']['label']=$this->pi_getLL('admin_sort_manufacturers_alphabet_desc', 'sort on alfabet (desc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_manufacturers']['subs']['admin_sort_manufacturers_alphabet_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=manufacturers&tx_multishop_pi1[sortByField]=name&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_manufacturers']['subs']['admin_sort_manufacturers_alphabet_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_manufacturers_desc', 'Are you sure want to sort manufacturers name descending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_categories']['label']=$this->pi_getLL('admin_sort_categories', 'Categories');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_categories']['subs']['admin_sort_categories_alphabet_asc']['label']=$this->pi_getLL('admin_sort_categories_alphabet_asc', 'sort on alfabet (asc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_categories']['subs']['admin_sort_categories_alphabet_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=categories&tx_multishop_pi1[sortByField]=categories_name&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_categories']['subs']['admin_sort_categories_alphabet_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_categories_asc', 'Are you sure want to sort categories name ascending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_categories']['subs']['admin_sort_categories_alphabet_desc']['label']=$this->pi_getLL('admin_sort_categories_alphabet_desc', 'sort on alfabet (desc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_categories']['subs']['admin_sort_categories_alphabet_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=categories&tx_multishop_pi1[sortByField]=categories_name&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_categories']['subs']['admin_sort_categories_alphabet_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_categories_desc', 'Are you sure want to sort categories name descending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['label']=$this->pi_getLL('admin_sort_products', 'Products');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_alphabet_asc']['label']=$this->pi_getLL('admin_sort_products_alphabet_asc', 'sort on alfabet (asc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_alphabet_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_name&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_alphabet_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_asc', 'Are you sure want to sort products name ascending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_alphabet_desc']['label']=$this->pi_getLL('admin_sort_products_alphabet_desc', 'sort on alfabet (desc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_alphabet_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_name&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_alphabet_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_desc', 'Are you sure want to sort products name descending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_price_asc']['label']=$this->pi_getLL('admin_sort_products_price_asc', 'sort on price (asc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_price_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_price&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_price_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_price_asc', 'Are you sure want to sort products price ascending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_price_desc']['label']=$this->pi_getLL('admin_sort_products_price_desc', 'sort on price (desc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_price_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_price&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_price_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_price_desc', 'Are you sure want to sort products price descending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_date_available_asc']['label']=$this->pi_getLL('admin_sort_by_date_ascending', 'sort on date available (asc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_date_available_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_date_added&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_date_available_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_date_asc', 'Are you sure want to sort products date added ascending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_date_available_desc']['label']=$this->pi_getLL('admin_sort_by_date_descending', 'sort on date available (desc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_date_available_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_date_added&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products']['subs']['admin_sort_products_date_available_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_date_desc', 'Are you sure want to sort products date added descending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['label']=$this->pi_getLL('admin_sort_products_attributes', 'Products attributes');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_alphabet_asc']['label']=$this->pi_getLL('admin_sort_products_attributes_alphabet_asc', 'sort on alfabet (asc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_alphabet_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=attribute_values&tx_multishop_pi1[sortByField]=products_options_values_name&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_alphabet_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_asc', 'Are you sure want to sort products attributes ascending').'?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_alphabet_desc']['label']=$this->pi_getLL('admin_sort_products_attributes_alphabet_desc', 'sort on alfabet (desc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_alphabet_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=attribute_values&tx_multishop_pi1[sortByField]=products_options_values_name&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_alphabet_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_desc', 'Are you sure want to sort products attributes descending').'?\')"';
				/*
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_price_asc']['label']=$this->pi_getLL('admin_sort_products_attributes_price_asc', 'sort on price (asc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_price_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=attribute_values&tx_multishop_pi1[sortByField]=products_price&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_price_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_price_asc', 'Are you sure want to sort products attributes price ascending').'?\')"';	
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_price_desc']['label']=$this->pi_getLL('admin_sort_products_attributes_price_desc', 'sort on price (desc)');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_price_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=attribute_values&tx_multishop_pi1[sortByField]=products_price&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_products_attributes']['subs']['admin_sort_products_attributes_price_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_products_price_desc', 'Are you sure want to sort products attributes price descending').'?\')"';
*/
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_complete']['label']=$this->pi_getLL('admin_sort_complete', 'Sort all on alphabet');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_complete']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=catalog&tx_multishop_pi1[sortByField]=name&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_complete']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_catalog').'?\')"';
				/*
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_alphabet']['label']=$this->pi_getLL('admin_sort_by_alphabet');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_alphabet']['link']=mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=catalog&tx_multishop_pi1[sortByField]=name&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_alphabet']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_catalog').'?\')"';	
		
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_date_asc']['label']=$this->pi_getLL('admin_sort_by_date_ascending');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_date_asc']['link']=mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_date_added&tx_multishop_pi1[orderBy]=asc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_date_asc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_catalog').'?\')"';	
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_date_desc']['label']=$this->pi_getLL('admin_sort_by_date_descending');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_date_desc']['link']=mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_system_sort_catalog&tx_multishop_pi1[sortItem]=products&tx_multishop_pi1[sortByField]=products_date_added&tx_multishop_pi1[orderBy]=desc');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_sort']['subs']['admin_sort_on_date_desc']['link_params']='onClick="return CONFIRM(\''.$this->pi_getLL('admin_are_you_sure_you_want_to_sort_catalog').'?\')"';	
				*/
			}
			if ($this->ROOTADMIN_USER) {
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_compare_database']['label']=$this->pi_getLL('admin_compare_database');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_compare_database']['link']='#';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_compare_database']['link_params']='id="multishop_update_button"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_consistency_checker']['label']=$this->pi_getLL('admin_consistency_checker');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_consistency_checker']['link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_system_consistency_checker');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_consistency_checker']['link_params']='onClick="return CONFIRM(\'Are you sure you want to run the consistency checker?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_delete_disabled_products']['label']=$this->pi_getLL('admin_delete_disabled_products');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_delete_disabled_products']['link']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_system_delete_disabled_products');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_delete_disabled_products']['link_params']='onClick="return CONFIRM(\'Are you sure you want to delete the disabled products?\')"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_whole_database']['label']=$this->pi_getLL('admin_clear_whole_database');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_whole_database']['link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_system_clear_database');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_clear_whole_database']['link_params']='onClick="return CONFIRM(\'This will destroy the whole catalog and cannot be restored. Are you sure you want to start all over again?\');"';
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_scan_for_orphan_files']['label']=$this->pi_getLL('admin_scan_for_orphan_files');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_scan_for_orphan_files']['link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_system_orphan_files');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_scan_for_orphan_files']['link_params']='';
			}
			if ($this->ms['MODULES']['FLAT_DATABASE'] and ($this->ROOTADMIN_USER or $this->conf['enableAdminPanelRebuildFlatDatabase'])) {
				$ms_menu['footer']['ms_admin_system']['subs']['admin_rebuild_flat_database']['label']=$this->pi_getLL('admin_rebuild_flat_database');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_rebuild_flat_database']['link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_system_rebuild_flat_database');
				$ms_menu['footer']['ms_admin_system']['subs']['admin_rebuild_flat_database']['link_params']='onClick="return CONFIRM(\'Are you sure you want to rebuild the flat databases?\')"';
			}
			$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_sitemap_generator']['label']=$this->pi_getLL('admin_sitemap_generator');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_sitemap_generator']['link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=admin_sitemap_generator');
			$ms_menu['footer']['ms_admin_system']['subs']['admin_system']['subs']['admin_sitemap_generator']['link_params']='onClick="return CONFIRM(\'Are you sure you want to start this?\')"';
			// footer eof
		} // end if enableAdminPanelSystem
		// hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['adminPanel'])) {
			$params=array(
				'this'=>&$this,
				'ms_menu'=>&$ms_menu
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['adminPanel'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$this->linkVars=$GLOBALS['TSFE']->linkVars;
		$useSysLanguageTitle=trim($this->conf['useSysLanguageTitle']) ? trim($this->conf['useSysLanguageTitle']) : 0;
		$useIsoLanguageCountryCode=trim($this->conf['useIsoLanguageCountryCode']) ? trim($this->conf['useIsoLanguageCountryCode']) : 0;
		$useIsoLanguageCountryCode=$useSysLanguageTitle ? 0 : $useIsoLanguageCountryCode;
		$useSelfLanguageTitle=trim($this->conf['useSelfLanguageTitle']) ? trim($this->conf['useSelfLanguageTitle']) : 0;
		$useSelfLanguageTitle=($useSysLanguageTitle || $useIsoLanguageCountryCode) ? 0 : $useSelfLanguageTitle;
		$tableA='sys_language';
		$tableB='static_languages';
		$languagesUidsList=trim($this->cObj->data['tx_srlanguagemenu_languages']) ? trim($this->cObj->data['tx_srlanguagemenu_languages']) : trim($this->conf['languagesUidsList']);
		$languages=array();
		$languagesLabels=array();
		// Set default language
		$defaultLanguageISOCode=trim($this->conf['defaultLanguageISOCode']) ? t3lib_div::strtoupper(trim($this->conf['defaultLanguageISOCode'])) : 'EN';
		$this->ms['MODULES']['COUNTRY_ISO_NR']=trim($this->conf['defaultCountryISOCode']) ? t3lib_div::strtoupper(trim($this->conf['defaultCountryISOCode'])) : '';
		$languages[]=t3lib_div::strtolower($defaultLanguageISOCode).($this->ms['MODULES']['COUNTRY_ISO_NR'] ? '_'.$this->ms['MODULES']['COUNTRY_ISO_NR'] : '');
		$this->languagesUids[]='0';
		// Get the language codes and labels for the languages set in the plugin list
		$selectFields=$tableA.'.uid, '.$tableA.'.title, '.$tableB.'.*';
		$table=$tableA.' LEFT JOIN '.$tableB.' ON '.$tableA.'.flag='.$tableB.'.cn_iso_2';
		// Ignore IN clause if language list is empty. This means that all languages found in the sys_language table will be used
		if (!empty($languagesUidsList)) {
			$whereClause=$tableA.'.uid IN ('.$languagesUidsList.') ';
		} else {
			$whereClause='1=1 ';
		}
		$whereClause.=$this->cObj->enableFields($tableA);
		$whereClause.=$this->cObj->enableFields($tableB);
		//	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields, $table, $whereClause);
		// If $languagesUidsList is not empty, the languages will be sorted in the order it specifies
		$languagesUidsArray=t3lib_div::trimExplode(',', $languagesUidsList, 1);
		$index=0;
		$str="select * from sys_language where hidden=0 order by title";
		$res=$GLOBALS['TYPO3_DB']->sql_query($str);
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$key++;
			$languages[$key]=$row['uid'];
			$languagesLabels[$key]['key']=$row['uid'];
			$languagesLabels[$key]['flag']=$row['flag'];
			if ($row['flag']) {
				if ($this->cookie['multishop_admin_language']==$row['uid']) {
					$this->cookie['multishop_admin_language']=$row['flag'];
				}
			}
			$languagesLabels[$key]['value']=$row['title'];
			$this->languagesUids[$key]=$row['uid'];
		}
		if (is_array($languagesLabels) and count($languagesLabels)) {
			$ms_menu['footer']['ms_admin_language']['description']='
			<form action="'.mslib_fe::typolink().'" method="post" id="multishop_admin_language_form">
				<select name="multishop_admin_language" id="ms_admin_simulate_language">
				<option value="default"'.($this->cookie['multishop_admin_language']=='' ? ' selected' : '').'>'.t3lib_div::strtoupper($this->pi_getLL('default_language')).'</option>
				';
			foreach ($languagesLabels as $key=>$language) {
				if ($language['key']) {
					$ms_menu['footer']['ms_admin_language']['description'].='<option value="'.$language['flag'].'"'.($this->cookie['multishop_admin_language']==$language['flag'] ? ' selected' : '').'>'.t3lib_div::strtoupper($language['value']).'</option>'."\n";
				}
			}
			$ms_menu['footer']['ms_admin_language']['description'].='
				</select>
			</form>			
			';
		}
		return $ms_menu;
	}
	public function getInvoice($value, $key='hash') {
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', 'tx_multishop_invoices', $key.'=\''.addslashes($value).'\'', '', 'id', '');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row;
		}
	}
	public function getProductsOptionName($option_id) {
		if (!is_numeric($option_id)) {
			return false;
		}
		if (is_numeric($option_id)) {
			//language_id=\''.$GLOBALS['TSFE']->sys_language_uid.'\'
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_name', // SELECT ...
				'tx_multishop_products_options', // FROM ...
				'products_options_id=\''.$option_id.'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row['products_options_name'];
			}
		}
	}
	public function getProductsOptionValues($option_id, $products_id='') {
		if (!is_numeric($option_id)) {
			return false;
		}
		if (is_numeric($option_id)) {
			//language_id=\''.$GLOBALS['TSFE']->sys_language_uid.'\'
			$str="select pa.options_values_id, pov.products_options_values_name from  tx_multishop_products_attributes pa, tx_multishop_products_options_values pov where ";
			if (is_numeric($products_id)) {
				$str.="pa.products_id='".$products_id."' and ";
			}
			$str.="pa.options_id='".$option_id."' and pa.options_values_id=pov.products_options_values_id";
			$res=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$array=array();
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array[]=$row;
				}
				return $array;
			}
		}
	}
	public function getCategoryName($categories_id) {
		if (!is_numeric($categories_id)) {
			return false;
		}
		if (is_numeric($categories_id)) {
			//language_id=\''.$GLOBALS['TSFE']->sys_language_uid.'\'
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('categories_name', // SELECT ...
				'tx_multishop_categories_description', // FROM ...
				'categories_id=\''.$categories_id.'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row['categories_name'];
			}
		}
	}
	public function tep_get_categories_edit($categories_id='', $aid='') {
		if ($categories_id) {
			$parent_categories_query=$GLOBALS['TYPO3_DB']->sql_query("SELECT cd.categories_name as name, cd.categories_id as id, c.parent_id as parent from tx_multishop_categories c, tx_multishop_categories_description cd where c.parent_id='".$categories_id."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$GLOBALS['TSFE']->sys_language_uid."' and c.status=1 and c.categories_id=cd.categories_id order by c.sort_order, cd.categories_name");
		} else {
			$parent_categories_query=$GLOBALS['TYPO3_DB']->sql_query("SELECT cd.categories_name as name, cd.categories_id as id, c.parent_id as parent from tx_multishop_categories c, tx_multishop_categories_description cd where c.parent_id='0' and c.status=1 and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$GLOBALS['TSFE']->sys_language_uid."' and c.categories_id=cd.categories_id order by c.sort_order, cd.categories_name");
		}
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($parent_categories_query);
		if ($rows) {
			$html.='<ul>';
		}
		while ($parent_categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($parent_categories_query)) {
			$html.='<li><div class="float-right-bold"><a href="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$parent_categories['id']).'&action=delete_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 140} )" alt="Remove" class="admin_menu_remove" title="Remove"></a>';
			$strchk="select * from tx_multishop_categories where parent_id='".$parent_categories['id']."'";
			$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
				$html.=' <a href="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$parent_categories['id'].'&action=add_product').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )" class="admin_menu_add" title="Add Product"></a>';
			}
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
				$html.=' <a href="#" cid="'.$parent_categories['id'].'" class="admin_menu_upload_productfeed" title="Upload Productfeed"></a>';
			}
			$html.='</div><strong><a href="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$parent_categories['id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 890, height: 500} )">'.$parent_categories['name'].'</a></strong>';
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
				$html.=mslib_fe::tep_get_categories_edit($parent_categories['id'], $aid);
			} else {
				$html.='<div class="hide" id="productfeed_form_container_'.$parent_categories['id'].'"></div>';
			}
			$html.='</li>';
		}
		if ($rows) {
			$html.='</ul>';
		}
		return $html;
	}
	public function updateCustomSettings($customsettings) {
		// this is for overwriting the Multishop module settings with the inserted values in the advanced tab of the Multishop content element settings.
		if (strstr($customsettings, "\r\n")) {
			$data=explode("\r\n", $customsettings);
		} else if (strstr($customsettings, "\n")) {
			$data=explode("\n", $customsettings);
		} else {
			$data=array($customsettings);
		}
		if (count($data)) {
			foreach ($data as $item) {
				$item=trim($item);
				$var=explode("=", $item);
				switch ($var[0]) {
					default:
						// lets overwrite the parameters (content element custom parameters always overrules
						$this->ms['MODULES'][$var[0]]=$var[1];
						// later we remove $ms
						$this->ms['MODULES'][$var[0]]=$var[1];
						break;
				}
			}
		}
	}
	public function loadInherentCustomSettingsByProduct($products_id, $categories_id='') {
		$product=mslib_fe::getProduct($products_id, $categories_id, 'p.custom_settings', 1);
		$cats=mslib_fe::Crumbar($product['categories_id']);
		$cats=array_reverse($cats);
		$settings=array();
		foreach ($cats as $cat) {
			$settings[]=$cat['custom_settings'];
		}
		if ($product['custom_settings']) {
			$settings[]=$product['custom_settings'];
		}
		if (count($settings)) {
			return mslib_fe::processInherentCustomSettings($settings);
		}
	}
	public function loadInherentCustomSettingsByCategory($categories_id) {
		$cats=mslib_fe::Crumbar($categories_id);
		$cats=array_reverse($cats);
		$settings=array();
		foreach ($cats as $cat) {
			$settings[]=$cat['custom_settings'];
		}
		if (count($settings)) {
			return mslib_fe::processInherentCustomSettings($settings);
		}
	}
	public function processInherentCustomSettings($settings) {
		if (count($settings)) {
			$modules=array();
			foreach ($settings as $customsettings) {
				if (strstr($customsettings, "\r\n")) {
					$data=explode("\r\n", $customsettings);
				} else if (strstr($customsettings, "\n")) {
					$data=explode("\n", $customsettings);
				} else {
					$data=array($customsettings);
				}
				if (count($data)) {
					foreach ($data as $item) {
						$var=explode("=", $item);
						switch ($var[0]) {
							default:
								// lets overwrite the parameters (content element custom parameters always overrules
								$modules[$var[0]]=$var[1];
								break;
						}
					}
				}
			}
			return $modules;
		}
	}
	public function inUserGroup($uid, $usergroup_string) {
		$groups=explode(",", $usergroup_string);
		if (in_array($uid, $groups)) {
			return 1;
		} else {
			return 0;
		}
	}
	public function updateFeUserGroup($uid, $add_usergroup, $remove_usergroup='') {
		if (!is_numeric($uid)) {
			return false;
		}
		if (is_numeric($uid)) {
			$user=mslib_fe::getUser($uid);
			if ($user['uid']) {
				$string=$user['usergroup'];
				$groups=explode(",", $string);
				$new_groups=array();
				foreach ($groups as $group) {
					$group=trim($group);
					if ($group) {
						if (is_array($remove_usergroup) or $remove_usergroup) {
							if (is_array($remove_usergroup)) {
								if (!in_array($group, $remove_usergroup)) {
									$new_groups[]=$group;
								}
							} else if ($group!=$remove_usergroup) {
								$new_groups[]=$group;
							}
						} else {
							$new_groups[]=$group;
						}
					}
				}
				if (is_array($add_usergroup)) {
					foreach ($add_usergroup as $item) {
						if ($item) {
							$new_groups[]=$item;
						}
					}
				} else if ($add_usergroup) {
					$new_groups[]=$add_usergroup;
				}
				$new_groups=array_unique($new_groups);
				$new_string=implode(",", $new_groups);
				$updateArray=array('usergroup'=>$new_string);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid='.$uid, $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				return $new_string;
			}
		}
	}
	public function generateInvoiceId() {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['generateInvoiceId'])) {
			$invoice_id='';
			// hook
			$params=array(
				'ms'=>$ms,
				'invoice_id'=>&$invoice_id
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['generateInvoiceId'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
			// hook oef
			return $invoice_id;
		} else {
			//$sql	= "select invoice_id from tx_multishop_invoices where page_uid='".$this->shop_pid."' order by id desc limit 1";
			$sql="select invoice_id from tx_multishop_invoices where page_uid='".$this->showCatalogFromPage."' order by id desc limit 1";
			$query=$GLOBALS['TYPO3_DB']->sql_query($sql);
			$rs_inv=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
			$prefix=$this->ms['MODULES']['INVOICE_PREFIX'].date("Y");
			if (preg_match("/^".$prefix."/", $rs_inv['invoice_id'])) {
				//$invoice_id = ((int) $rs_inv['invoice_id'] + 1);
				if ($this->ms['MODULES']['INVOICE_PREFIX']) {
					$rs_inv['invoice_id']=str_replace($this->ms['MODULES']['INVOICE_PREFIX'], '', $rs_inv['invoice_id']);
				}
				// if prefix not empty, the (int) will convert the whole invoice id to 1
				$invoice_id=((int)$rs_inv['invoice_id']+1);
				if ($this->ms['MODULES']['INVOICE_PREFIX']) {
					$invoice_id=$this->ms['MODULES']['INVOICE_PREFIX'].$invoice_id;
				}
			} else {
				$invoice_id=$this->ms['MODULES']['INVOICE_PREFIX'].date("Y").'00001';
			}
			return $invoice_id;
		}
	}
	public function createOrderInvoice($orders_id, $force=0) {
		if (!is_numeric($orders_id)) {
			return false;
		}
		if (is_numeric($orders_id)) {
			$order=mslib_fe::getOrder($orders_id);
			if ($order['total_amount']==0) {
				// it does not make sense to create an invoice without an amount
				return false;
			}
			if (($order['orders_id'] and $order['bill']) or ($order['orders_id'] and $force)) {
				$invoice_id=mslib_fe::generateInvoiceId();
				if ($invoice_id) {
					$hash=md5(uniqid('', TRUE));
					$insertArray=array();
					$insertArray['invoice_id']=$invoice_id;
					$insertArray['customer_id']=$order['customer_id'];
					$insertArray['paid']=$order['paid'];
					$insertArray['orders_id']=$orders_id;
					$insertArray['crdate']=time();
					$insertArray['status']=1;
					$insertArray['page_uid']=$this->shop_pid;
					$insertArray['hash']=$hash;
					$insertArray['amount']=mslib_fe::getOrderTotalPrice($orders_id);
					$insertArray['discount']=$order['discount'];
					if ($order['billing_company']) {
						$name=$order['billing_company'];
					} else {
						$name=$order['billing_name'];
					}
					$insertArray['ordered_by']=$name;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_invoices', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					if ($res) {
						// update order to billed and lock the order so nobody can adjust it
						if ($this->ms['MODULES']['LOCK_ORDER_AFTER_CREATING_INVOICE']) {
							$lock_order=1;
						} else {
							$lock_order=0;
						}
						$updateArray=array(
							'bill'=>0,
							'is_locked'=>$lock_order
						);
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id='.$order['orders_id'], $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						$array=array();
						$array['hash']=$hash;
						$array['invoice_id']=$invoice_id;
						return $array;
					}
				}
			}
		}
	}
	public function getOrderInvoice($orders_id, $create_if_not_exists=1) {
		if (!is_numeric($orders_id)) {
			return false;
		}
		$sql="select invoice_id,hash from tx_multishop_invoices where orders_id = ".$orders_id;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)==0) {
			if ($create_if_not_exists) {
				$data=mslib_fe::createOrderInvoice($orders_id, 1);
			}
		} else {
			$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		}
		if ($data['invoice_id']) {
			$invoice=array();
			$invoice['invoice_id']=$data['invoice_id'];
			$invoice['hash']=$data['hash'];
			return $invoice;
		}
	}
	public function generateReversalInvoice($id) {
		if (!is_numeric($id)) {
			return false;
		}
		if (is_numeric($id)) {
			// check if this invoice hasnt be reversed already
			$sql="select count(1) as total from tx_multishop_invoices where reversal_related_id='".$id."'";
			$query=$GLOBALS['TYPO3_DB']->sql_query($sql);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
			if ($row['total']==0) {
				$sql="select * from tx_multishop_invoices where id='".$id."'";
				$query=$GLOBALS['TYPO3_DB']->sql_query($sql);
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
				if (!$invoice['reversal_invoice']) {
					$new_invoice_id=mslib_fe::generateInvoiceId();
					if ($new_invoice_id) {
						unset($row['id']);
						$row['reversal_related_id']=$id;
						$row['reversal_invoice']=1;
						$row['crdate']=time();
						$row['paid']=1;
						$row['invoice_id']=$new_invoice_id;
						$row['hash']=md5(uniqid('', TRUE));
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_invoices', $row);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						// update old invoice to paid so its gone from the unpaid list						
						$updateArray=array('paid'=>1);
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id='.$id, $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
		}
	}
	public function updateOrderStatusToPaid($orders_id) {
		if (!is_numeric($orders_id)) {
			return false;
		}
		$order=mslib_fe::getOrder($orders_id);
		if (!$order['paid']) {
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['updateOrderStatusToPaidPreProc'])) {
				$params=array(
					'order'=>&$order
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['updateOrderStatusToPaidPreProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			$updateArray=array('paid'=>1);
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id='.$orders_id, $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
				// create invoice
				$invoice=mslib_fe::getOrderInvoice($orders_id);
				$updateArray=array('paid'=>1);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'hash=\''.$invoice['hash'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$continue=1;
			foreach ($order['products'] as $product) {
				$module_settings=mslib_fe::loadInherentCustomSettingsByProduct($product['products_id']);
				if ($module_settings['ORDERS_PAID_CUSTOM_SCRIPT']) {
					if (!strstr($module_settings['ORDERS_PAID_CUSTOM_SCRIPT'], "..")) {
						if (strstr($module_settings['ORDERS_PAID_CUSTOM_SCRIPT'], "/")) {
							$continue=0;
							require(PATH_site.$module_settings['ORDERS_PAID_CUSTOM_SCRIPT'].'.php');
						}
					}
				}
			}
			if ($this->ms['MODULES']['ORDERS_PAID_CUSTOM_SCRIPT'] and $continue) {
				if (!strstr($module_settings['ORDERS_PAID_CUSTOM_SCRIPT'], "..")) {
					if (strstr($module_settings['ORDERS_PAID_CUSTOM_SCRIPT'], "/")) {
						require(PATH_site.$module_settings['ORDERS_PAID_CUSTOM_SCRIPT'].'.php');
					}
				}
			}
			$mailOrder=1;
			//hook to let other plugins further manipulate the replacers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['updateOrderStatusToPaidPostProc'])) {
				$params=array(
					'order'=>&$order,
					'mailOrder'=>&$mailOrder
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_fe.php']['updateOrderStatusToPaidPostProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			if ($mailOrder) {
				$tmp=mslib_fe::mailOrder($order['orders_id'], 1, '', 'email_order_paid_letter');
			}
			return true;
		} else {
			return false;
		}
	}
	public function fullwidthDiv($content) {
		return '<div class="fullwidth_div">'.$content.'</div>';
	}
	/*
		this method is used to request the categories page set
		$filter can be an string or (multiple) array:	
		string example: o.orders_id=12
		array example:  $filter[]='o.orders_id=12'	
	*/
	public function getCMSPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array(), $from=array()) {
		if (!$limit) {
			$limit=20;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$select_clause="SELECT ";
		if (count($select)>0) {
			$select_clause.=implode(",", $select);
		}
		$from_clause.="	from tx_multishop_cms c, tx_multishop_cms_description cd";
		if (count($from)>0) {
			$from_clause.=", ";
			$from_clause.=implode(",", $from);
		}
		$where_clause.=" where c.id=cd.id and cd.language_id='".$this->sys_language_uid."'";
		if (count($where)>0) {
			$where_clause.='and ';
			$where_clause.=implode($where, ",");
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=' and ('.implode($filter, " and ").')';
		} else if ($filter) {
			$where_clause.=' and ('.$filter.')';
		}
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode($orderby, ",");
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($category=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['admin_cms'][]=$category;
			}
		}
		return $array;
	}
	public function getRecordsPageSet($data=array()) {
		if (is_array($data) and count($data) and count($data['from'])) {
			$results=array();
			$results['dataset']=array();
			if (!is_array($data['select'])) {
				$data['select']=array('*');
			}
			if (!is_array($data['where'])) {
				$data['where']=array();
			}
			if (!is_array($data['group_by'])) {
				$data['group_by']=array();
			}
			if (!is_array($data['order_by'])) {
				$data['order_by']=array();
			}
			if (!$data['limit']) {
				$data['limit']='10';
			}
			if (!$data['offset']) {
				$data['offset']='0';
			}
			// get the total results
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
				implode(',', $data['from']), // FROM ...
				implode(' AND ', $data['where']), // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			if ($this->msDebug) {
				$this->msDebugInfo.=$query."\n\n";
			}
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$results['total_rows']=$row['total'];
			if ($results['total_rows']) {
				$query=$GLOBALS['TYPO3_DB']->SELECTquery(implode(',', $data['select']), // SELECT ...
					implode(',', $data['from']), // FROM ...
					implode(' AND ', $data['where']), // WHERE...
					implode(',', $data['group_by']), // GROUP BY...
					implode(',', $data['order_by']), // ORDER BY...
					$data['offset'].','.$data['limit'] // LIMIT ...
				);
				if ($this->msDebug) {
					$this->msDebugInfo.=$query."\n\n";
				}
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
					while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$results['dataset'][]=$row;
					}
				}
			}
			return $results;
		}
	}
	/*
		this method is used to request the categories page set
		$filter can be an string or (multiple) array:	
		string example: o.orders_id=12
		array example:  $filter[]='o.orders_id=12'	
	*/
	public function getAdminSettingsPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array(), $from=array()) {
		if (!$limit) {
			$limit=20;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$select_clause="SELECT ";
		if (count($select)>0) {
			$select_clause.=implode(",", $select);
		}
		$from_clause.="	from tx_multishop_configuration c left join tx_multishop_configuration_values cv on c.configuration_key=cv.configuration_key ";
		if (count($from)>0) {
			$from_clause.=", ";
			$from_clause.=implode(",", $from);
		}
		$where_clause.=" where 1 ";
		if (count($where)>0) {
			$where_clause.='and ';
			$where_clause.=implode($where, ",");
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=' and ('.implode($filter, " and ").')';
		} else if ($filter) {
			$where_clause.=' and ('.$filter.')';
		}
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode($orderby, ",");
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($category=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['admin_settings'][]=$category;
			}
		}
		return $array;
	}
	/*
		this method is used to request the categories page set
		$filter can be an string or (multiple) array:	
		string example: o.orders_id=12
		array example:  $filter[]='o.orders_id=12'	
	*/
	public function getCategoriesPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array(), $from=array()) {
		if (!$limit) {
			$limit=20;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$select_clause="SELECT ";
		if (count($select)>0) {
			$select_clause.=implode(",", $select);
		}
		$from_clause.="	from tx_multishop_categories c, tx_multishop_categories_description cd";
		if (count($from)>0) {
			$from_clause.=", ";
			$from_clause.=implode(",", $from);
		}
		$where_clause.=" where c.status=1 and cd.language_id='".$this->sys_language_uid."' and c.categories_id=cd.categories_id";
		if (count($where)>0) {
			$where_clause.='and ';
			$where_clause.=implode($where, ",");
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=' and ('.implode($filter, " and ").')';
		} else if ($filter) {
			$where_clause.=' and ('.$filter.')';
		}
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode($orderby, ",");
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($category=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['categories'][]=$category;
			}
		}
		return $array;
	}
	/*
		this method is used to request the orders page set
		$filter can be an string or (multiple) array:	
		string example: o.orders_id=12
		array example:  $filter[]='o.orders_id=12'	
	*/
	public function getOrdersPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array(), $from=array()) {
		if (!$limit) {
			$limit=20;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$select_clause="SELECT ";
		if (count($select)>0) {
			$select_clause.=implode(",", $select);
		}
//		$from_clause.="	from tx_multishop_orders o left join tx_multishop_orders_status os on o.status=os.id ";
		$from_clause.="	from tx_multishop_orders o left join tx_multishop_orders_status os on o.status=os.id left join tx_multishop_orders_status_description osd on (os.id=osd.orders_status_id AND o.language_id=osd.language_id) ";
		if (count($from)>0) {
			$from_clause.=", ";
			$from_clause.=implode(",", $from);
		}
		$where_clause.=" where o.deleted=0";
		if (count($where)>0) {
			$where_clause.='and ';
			$where_clause.=implode($where, ",");
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=' and ('.implode($filter, " and ").')';
		} else if ($filter) {
			$where_clause.=' and ('.$filter.')';
		}
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode($orderby, ",");
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
//		error_log($str);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($order=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['orders'][]=$order;
			}
		}
		return $array;
	}
	/*
		this method is used to request the orders page set
		$filter can be an string or (multiple) array:	
		string example: o.orders_id=12
		array example:  $filter[]='o.orders_id=12'	
	*/
	public function getInvoicesPageSet($filter=array(), $offset=0, $limit=0, $orderby=array(), $having=array(), $select=array(), $where=array(), $from=array()) {
		if (!$limit) {
			$limit=20;
		}
		if (!is_numeric($offset)) {
			$offset=0;
		}
		// do normal search (join the seperate tables)
		$select_clause="SELECT ";
		if (count($select)>0) {
			$select_clause.=implode(",", $select);
		}
		$from_clause.=",i.crdate,i.paid from tx_multishop_invoices i left join tx_multishop_orders o on o.orders_id=i.orders_id";
		if (count($from)>0) {
			$from_clause.=", ";
			$from_clause.=implode(",", $from);
		}
		$where_clause.=" where 1 ";
		if (count($where)>0) {
			$where_clause.='and ';
			$where_clause.=implode($where, ",");
		}
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=' and ('.implode($filter, " and ").')';
		} else if ($filter) {
			$where_clause.=' and ('.$filter.')';
		}
		if (count($having)>0) {
			$having_clause.=" having ";
			foreach ($having as $item) {
				$having_clause.=$item;
			}
		}
		if (is_array($orderby) and count($orderby)>0) {
			$str_order_by.=implode($orderby, ",");
		} else if ($orderby) {
			$str_order_by.=$orderby;
		} else {
			$str_order_by.="";
		}
		if ($str_order_by) {
			$orderby_clause.=" order by ".$str_order_by;
		}
		$limit_clause.=' LIMIT '.$offset.','.$limit;
		$array=array();
		$str='SELECT count(1) as total '.$from_clause.$where_clause.$having_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$array['total_rows']=$row['total'];
		$str=$select_clause.$from_clause.$where_clause.$having_clause.$orderby_clause.$limit_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['invoices'][]=$row;
			}
		}
		return $array;
	}
	public function getShopByPageUid($page_uid) {
		if (!is_numeric($page_id)) {
			return false;
		}
		if (is_numeric($page_uid)) {
			$shop=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('t.pid, p.title, p.uid as puid', 'tt_content t, pages p', 'p.uid=\''.$page_uid.'\' and p.hidden=0 and t.hidden=0 and p.deleted=0 and t.deleted=0 and t.list_type = \'multishop_pi1\' and t.pi_flexform like \'%<value index="vDEF">coreshop</value>%\' and t.pid=p.uid', 'p.sorting');
			return $shop[0];
		}
	}
	public function getShopNameByPageUid($page_uid) {
		if (!is_numeric($page_uid)) {
			return false;
		} else {
			$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=1;
			$shop=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('t.pid, p.title, p.uid as puid', 'tt_content t, pages p', 'p.uid=\''.$page_uid.'\' and p.hidden=0 and t.hidden=0 and p.deleted=0 and t.deleted=0 and t.pid=p.uid', '');
			return $shop[0]['title'];
		}
	}
	public function getOrdersIdByTransactionId($transaction_id, $psp='') {
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_payment_transactions', // FROM ...
			'transaction_id=\''.addslashes($transaction_id).'\' and psp=\''.addslashes($psp).'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['orders_id'];
		}
	}
	public function getTransactionIdByOrderId($order_id, $psp='') {
		if (!is_numeric($order_id)) {
			return false;
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_payment_transactions', // FROM ...
			'orders_id=\''.addslashes($order_id).'\' and psp=\''.addslashes($psp).'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['transaction_id'];
		}
	}
	public function createPaymentTransactionId($orders_id, $psp='', $code='', $security_type='md5', $transid='') {
		if (!is_numeric($orders_id)) {
			return false;
		}
		$array=array();
		$array['orders_id']=$orders_id;
		switch ($security_type) {
			case 'sha512':
				$array['transaction_id']=hash('sha512', uniqid($orders_id.'-'.$psp, TRUE));
				break;
			case 'sha1':
				$array['transaction_id']=sha1(uniqid($orders_id.'-'.$psp, TRUE));
				break;
			case 'manual':
				$array['transaction_id']=$transid;
				break;
			case 'crc32':
				$array['transaction_id']=hash('crc32', uniqid($orders_id.'-'.$psp, TRUE));
				break;
			case 'short_md5':
			case 'md5':
			default:
				$array['transaction_id']=md5(uniqid($orders_id.'-'.$psp, TRUE));
				break;
		}
		$array['psp']=$psp;
		$array['code']=$code;
		$array['crdate']=time();
		$array['status']=0;
		if ($array['transaction_id']) {
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_transactions', $array);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($res) {
				return $array['transaction_id'];
			}
		}
	}
	public function getProducts($products_id='', $categories_id='') {
		if (!empty($products_id) && !is_numeric($products_id)) {
			return false;
		}
		if (!empty($categories_id) && !is_numeric($categories_id)) {
			return false;
		}
		if (!$this->ms['MODULES']['FLAT_DATABASE']) {
			//pd.products_meta_title, pd.products_shortdescription, pd.products_meta_keywords,
			if ($products_id) {
				$str="SELECT *,p.staffel_price as staffel_price, tr.tx_rate as tax_rate, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join static_taxes tr on p.tax_id = tr.uid, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p.products_status=1 and pd.language_id='".$this->sys_language_uid."' and cd.language_id='".$this->sys_language_uid."' and p.products_id='".$products_id."' and p.products_id=pd.products_id and  p.products_id=p2c.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id ";
				if ($categories_id) {
					$str.=" and p2c.categories_id='".$categories_id."'";
				}
				$str.=" order by p2c.sort_order";
			} else if ($categories_id) {
				$str="SELECT *,p.staffel_price as staffel_price, tr.tx_rate as tax_rate, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join static_taxes tr on p.tax_id = tr.uid, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p.products_status=1 and p2c.categories_id='".$categories_id."' and pd.language_id='".$this->sys_language_uid."' and cd.language_id='".$this->sys_language_uid."' and p.products_id=pd.products_id and  p.products_id=p2c.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id order by p2c.sort_order";
			}
		} else {
			if ($products_id) {
				$str="SELECT * from tx_multishop_products_flat where products_id='".$products_id."' and language_id='".$this->sys_language_uid."'";
			} else if ($categories_id) {
				$str="SELECT * from tx_multishop_products_flat where categories_id='".$categories_id."' and language_id='".$this->sys_language_uid."'";
			}
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$products=array();
		while ($product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
			$products[$product['products_id']]=$product;
		}
		return $products;
	}
	/*
		method used for the ultrasearch searchform
	*/
	public function categories_ultrasearch_as_ul($parent_id='0', &$content='') {
		$content.='<ul id="cat_'.$parent_id.'">';
		$categories_query=$GLOBALS['TYPO3_DB']->sql_query("select c.categories_id, cd.categories_name, c.parent_id from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '".$parent_id."' and c.page_uid='".$this->showCatalogFromPage."' order by c.sort_order, cd.categories_name");
		while ($categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($categories_query)) {
			if ($categories['categories_name']) {
				$content.='<li><input type="checkbox" name="category_products" class="category_products" value="'.$categories['categories_id'].'"> ';
				if (mslib_fe::hasProducts($categories['categories_id'])) {
					// get all cats to generate multilevel fake url
					$level=0;
					$cats=mslib_fe::Crumbar($categories['categories_id']);
					$cats=array_reverse($cats);
					$where='';
					if (count($cats)>0) {
						foreach ($cats as $item) {
							$where.="categories_id[".$level."]=".$item['id']."&";
							$level++;
						}
						$where=substr($where, 0, (strlen($where)-1));
						$where.='&';
					}
					$where.='categories_id['.$level.']='.$categories['categories_id'];
					$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
					// get all cats to generate multilevel fake url eof				
					$name='<a href="'.$link.'" class="ajax_link">'.$categories['categories_name'].'</a>';
				} else {
					$name='<span>'.$categories['categories_name'].'</span>';
				}
				$content.=$name;
				categories_ultrasearch_as_ul($categories['categories_id'], $content);
				$content.='</li>';
			}
		}
		$content.='</ul>';
		return $content;
	}
	/*
		loads all options ids plus option values ids that are mapped to a specific product
	*/
	public function getProductOptions($products_id) {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (is_numeric($products_id)) {
			$str="SELECT options_id,options_values_id from tx_multishop_products_attributes where products_id='".$products_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$options=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$options[$row['options_id']][]=$row['options_values_id'];
			}
			return $options;
		}
	}
	public function getProductFeed($string, $type='id') {
		if ($string) {
			switch ($type) {
				case 'code':
				case 'id':
					$str="SELECT * from tx_multishop_product_feeds where ".$type."='".addslashes($string)."'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					return $row;
					break;
				default:
					return false;
					break;
			}
		}
	}
	public function file_get_contents($filename, $force_gz=0) {
		if ($filename) {
			if (!preg_match("/^\//", $filename) and strstr($filename, ' ')) {
				// if filename is not a local path and it contains a space, then encode it
				$parts=parse_url($filename);
				$path_parts=array_map('rawurldecode', explode('/', $parts['path']));
				$filename=$parts['scheme'].'://'.$parts['host'].implode('/', array_map('rawurlencode', $path_parts));
			}
			if (preg_match("/\.gz$/", $filename) or $force_gz) {
				// get contents of a gz-file into a string
				$zd=gzopen($filename, "r");
				$file_content=gzread($zd, 999999999);
				gzclose($zd);
			} else {
				if (preg_match("/^\//", $filename)) {
					// local path
					$file_content=file_get_contents($filename);
				} else {
					$path=@parse_url($filename);
					if ($path['scheme']) {
						// we try to use Curl, so we don't need PHP allow_url_fopen to be on
						$ch=curl_init($filename);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_POST, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // does not work when safe mode is activated or open_base restriction has been set. Below we bypass the redirect problem
						//curl_setopt($ch, CURLOPT_MAXREDIRS, 10); /* Max redirection to follow */      
						$file_content=curl_exec($ch);
						$http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
						if ($http_code==301 || $http_code==302) {
							// redirect. lets download it manually
							$file_content=file_get_contents($filename);
						}
					}
				}
			}
			return $file_content;
		}
	}
	public function convertXMLtoPHPObject($xml) {
		$xmlstr=urldecode(rawurldecode($xml));
//		$template_obj = new SimpleXMLElement($xmlstr);
		$template_obj=simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA|LIBXML_NOBLANKS);
		return $template_obj;
	}
	public function convertXMLtoPHPArray($xml) {
		$xmlstr=urldecode(rawurldecode($xml));
//		$template_obj = new SimpleXMLElement($xmlstr);				
		$template_obj=simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA|LIBXML_NOBLANKS);
		$json=json_encode($template_obj);
		$template_array=json_decode($json, TRUE);
		return $template_array;
	}
	public function convertPHPArraytoXML($array, $root='root') {
		$xml=new SimpleXMLElement('<'.$root.'/>');
		array_walk_recursive($array, array(
			$xml,
			'addChild'
		));
		return $xml;
	}
	public function getSitemap($categories_id, $array=array(), $include_disabled_categories=0, $include_products=1) {
		$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.page_uid='".$this->showCatalogFromPage."' ";
		if (!$include_disabled_categories) {
			$str.="and c.status=1 ";
		}
		$str.="and c.parent_id='".$categories_id."' and cd.language_id='".$this->sys_language_uid."' and c.categories_id=cd.categories_id order by c.sort_order";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			// cats
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['subs'][]=mslib_fe::getSitemap($row['categories_id'], $row, $include_disabled_categories, $include_products);
			}
		} else if ($include_products) {
			// products
			$products=mslib_fe::getProducts('', $categories_id);
			if (is_array($products)) {
				if (!count($array)) {
					// starting point doesnt have subcats, but products instead.
					$array['subs'][0]['products']=$products;
				} else {
					$array['products']=$products;
				}
			}
		}
		return $array;
	}
	public function displaySitemap($dataArray, $display_products=1, $admin_mode=0, $display_fields=array()) {
		if (!count($display_fields)) {
			$display_fields[]='products_image';
			$display_fields[]='products_shortdescription';
		}
		// cats
		if (count($dataArray['subs'])) {
//			$content.='<ul>';	
			foreach ($dataArray['subs'] as $item) {
				if (!count($item['products'])) {
					// cats
					$content.='<li class="category'.(!$item['status'] ? ' disabled' : '').'">';
					if ($this->ADMIN_USER and $admin_mode) {
						// get all cats to generate multilevel fake url
						$level=0;
						$cats=mslib_fe::Crumbar($item['categories_id']);
						$cats=array_reverse($cats);
						$where='';
						if (count($cats)>0) {
							foreach ($cats as $tmp) {
								$where.="categories_id[".$level."]=".$tmp['id']."&";
								$level++;
							}
							$where=substr($where, 0, (strlen($where)-1));
//							$where.='&';
						}
						$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
//						$where.='categories_id['.$level.']='.$category['categories_id'];
						// get all cats to generate multilevel fake url eof
						$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$item['categories_name'].'</a>';
						$content.='<div class="action_icons">
						<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="msadmin_edit_icon"><span>edit</span></a>
						<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id'].'&action=delete_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 140} )" class="msadmin_delete_icon" alt="Remove"><span>delete</span></a>
						<a href="'.$link.'" target="_blank" class="msadmin_view"><span>view</span></a>
						</div>';
//						$content.='<span class="msadmin_cat_edit_icons"><a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="msadmin_edit">edit</a> | <a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id'].'&action=delete_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 140} )" class="msadmin_delete" alt="Remove">delete</a></span> | <span class="msadmin_cat_view_icons"><a href="'.$link.'" target="_blank" class="msadmin_view">view</a>';
						// | <a href="#" class="msadmin_status">enabled</a>
					} else {
						if ($display_products) {
							$content.=$item['categories_name'];
						} else {
							// get all cats to generate multilevel fake url
							$level=0;
							$cats=mslib_fe::Crumbar($item['categories_id']);
							$cats=array_reverse($cats);
							$where='';
							if (count($cats)>0) {
								foreach ($cats as $tmp) {
									$where.="categories_id[".$level."]=".$tmp['id']."&";
									$level++;
								}
								$where=substr($where, 0, (strlen($where)-1));
//								$where.='&';
							}
//							$where.='categories_id['.$level.']='.$item['categories_id'];
							$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
							// get all cats to generate multilevel fake url eof											
							//							$content.=$item['categories_name'];
							if ($link) {
								$content.='<a href="'.$link.'" class="ajax_link"'.$target.'>';
							}
							$content.=$item['categories_name'];
							if ($link) {
								$content.='</a>';
							}
						}
					}
					$sub_content=mslib_fe::displaySitemap($item, $display_products, $admin_mode, $display_fields);
					if ($sub_content) {
						$content.='<ul>'.$sub_content.'</ul>';
					}
					$content.='</li>';
				} else if ($display_products and count($item['products'])) {
					// products
					foreach ($item['products'] as $product) {
						if ($product['products_image']) {
							$image='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', 50).'" alt="'.htmlspecialchars($product['products_name']).'">';
						} else {
							$image='<div class="no_image"></div>';
						}
						$where='';
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
//								$where.='&';
							}
							// get all cats to generate multilevel fake url eof
						}
						if ($product['products_url'] and $this->ms['MODULES']['AFFILIATE_SHOP']) {
							$link=$product['products_url'];
						} else {
							$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
						}
						$content.='<li class="product">';
						$content.='<span class="products_name"><a href="'.$link.'" class="ajax_link" alt="'.$product['products_meta_description'].'" title="'.$product['products_meta_description'].'">'.$product['products_name'].'</a></span>';
						if (in_array('products_image', $display_fields)) {
							$content.='<span class="products_image"><a href="'.$link.'" class="ajax_link" alt="'.$product['products_meta_description'].'" title="'.$product['products_meta_description'].'">'.$image.'</a></span>';
						}
						if (in_array('products_shortdescription', $display_fields)) {
							$content.='<span class="products_description">'.$product['products_shortdescription'].'</span>';
						}
						$content.='</li>';
					}
				}
			}
//			$content.='</ul>';					
		}
		return $content;
	}
	public function displayAdminCategories($dataArray, $selectbox=false, $level=0, $parent_id=0, $admin_mode=1) {
		// cats
		if (count($dataArray['subs'])) {
			foreach ($dataArray['subs'] as $item) {
				// cats
				if (!$selectbox) {
					$content.='<li class="category'.(!$item['status'] ? ' disabled' : '').'">';
					$content.='<input type="checkbox" class="movecats" name="movecats[]" value="'.$item['categories_id'].'" id="cb-cat_'.$parent_id.'_'.$item['categories_id'].'" rel="'.$parent_id.'_'.$item['categories_id'].'">&nbsp;';
					if ($this->ADMIN_USER and $admin_mode) {
						// get all cats to generate multilevel fake url
						$level=0;
						$cats=mslib_fe::Crumbar($item['categories_id']);
						$cats=array_reverse($cats);
						$where='';
						if (count($cats)>0) {
							foreach ($cats as $tmp) {
								$where.="categories_id[".$level."]=".$tmp['id']."&";
								$level++;
							}
							$where=substr($where, 0, (strlen($where)-1));
							//							$where.='&';
						}
						$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
						//						$where.='categories_id['.$level.']='.$category['categories_id'];
						// get all cats to generate multilevel fake url eof
						$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$item['categories_name'].'</a>';
						$content.='<div class="action_icons">
							<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="msadmin_edit_icon"><span>edit</span></a>
							<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id'].'&action=delete_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 140} )" class="msadmin_delete_icon" alt="Remove"><span>delete</span></a>
							<a href="'.$link.'" target="_blank" class="msadmin_view"><span>view</span></a>
						</div>';
						//						$content.='<span class="msadmin_cat_edit_icons"><a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="msadmin_edit">edit</a> | <a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$item['categories_id'].'&action=delete_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 140} )" class="msadmin_delete" alt="Remove">delete</a></span> | <span class="msadmin_cat_view_icons"><a href="'.$link.'" target="_blank" class="msadmin_view">view</a>';
						// | <a href="#" class="msadmin_status">enabled</a>
					} else {
						// get all cats to generate multilevel fake url
						$level=0;
						$cats=mslib_fe::Crumbar($item['categories_id']);
						$cats=array_reverse($cats);
						$where='';
						if (count($cats)>0) {
							foreach ($cats as $tmp) {
								$where.="categories_id[".$level."]=".$tmp['id']."&";
								$level++;
							}
							$where=substr($where, 0, (strlen($where)-1));
							//								$where.='&';
						}
						//							$where.='categories_id['.$level.']='.$item['categories_id'];
						$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
						// get all cats to generate multilevel fake url eof
						//							$content.=$item['categories_name'];
						if ($link) {
							$content.='<a href="'.$link.'" class="ajax_link"'.$target.'>';
						}
						$content.=$item['categories_name'];
						if ($link) {
							$content.='</a>';
						}
					}
					$sub_content=mslib_fe::displayAdminCategories($item, $selectbox, 0, $item['categories_id']);
					if ($sub_content) {
						$content.='<ul>'.$sub_content.'</ul>';
					}
					$content.='</li>';
				} else {
					$content.='<option value="'.$item['categories_id'].'" id="sl-cat_'.$parent_id.'_'.$item['categories_id'].'">+-'.str_repeat('--', $level).' ['.$item['categories_name'].'</option>';
					$sub_content=mslib_fe::displayAdminCategories($item, $selectbox, $level+1, $item['categories_id']);
					if ($sub_content) {
						$content.=$sub_content;
					}
				}
			}
		}
		return $content;
	}
	public function currencyConverter($from_Currency, $to_Currency, $amount) {
		// add static so the rate is only requested one time, while processing the PHP script
		static $currencyArray;
		if (!is_array($currencyArray) or !isset($currencyArray[$from_Currency][$to_Currency])) {
			// fetch currency
			$amount=urlencode($amount);
			$from_Currency=urlencode($from_Currency);
			$to_Currency=urlencode($to_Currency);
			$url='http://www.google.com/finance/converter?a=1&from='.strtoupper($from_Currency).'&to='.strtoupper($to_Currency);
			$ch=curl_init();
			$timeout=0;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$rawdata=curl_exec($ch);
			curl_close($ch);
			$pattern='/<span class=bld>(.*)<\/span>/isUm';
			preg_match_all($pattern, $rawdata, $matches);
			$rate='';
			if (isset($matches[1][0]) && !empty($matches[1][0])) {
				$rate=str_replace(' '.strtoupper($to_Currency), '', $matches[1][0]);
			}
			$currencyArray[$from_Currency][$to_Currency]=$rate;
		}
		return round(($amount*$currencyArray[$from_Currency][$to_Currency]), 3);
	}
	public function setCookie($name, $value, $lifetime, $path='/', $domain='', $secure=0) {
		setcookie($name, $value, $lifetime, $path, $domain, $secure);
	}
	public function displayAdminNotificationPopup() {
		$content.='<script language="javascript" type="text/javascript">
			function displayAdminNotificationMessage() 
			{ 
				jQuery.ajax({
				  url: \''.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=retrieveAdminNotificationMessage').'\',
				  data: \'\',
				  dataType: \'json\',
				  type: \'post\',
				  success: function (j) {
					  if (j.length > 0)
					  {
							if (jQuery(\'.msadmin_balloon_wrapper\').length != 0) jQuery(\'.msadmin_balloon_wrapper\').remove();							
						    var content=\'\';
							content+=\'<div class="msadmin_balloon_wrapper">\';
							jQuery.each(j, function(i, val) {
								var title=\'\';
								var message=\'\';
								if (val.title) title=\'<h2>\'+val.title+\'</h2><i class="crdate">\'+val.crdate+\'</i>\';
								if (val.message) message=\'<p>\'+val.message+\'</p>\';																
								content+=\'<div class="msadmin_balloon_item"><div class="msadmin_balloon">\'+title+message+\'<em></em></div></div>\';
							});
							content+=\'</div>\';
							jQuery("body").append(content);
							jQuery(\'.msadmin_balloon_wrapper\').hide().delay(500).slideDown(\'slow\').delay(8000).slideUp();
					  }
				  }
				});
				setTimeout("displayAdminNotificationMessage()", 45000);	
			}
			jQuery(document).ready(function($){			
				displayAdminNotificationMessage();
			});
		</script>';
		return $content;
	}

	// attributes stock front view 
	/*
	 * this method is used to request the products based on attributes stock
	*/
	public function getProductsAttributesStockGroup($filter) {
		// do normal search (join the seperate tables)
		$required_cols='asg.attributes_stock, asg.group_id as as_group_id, p.minimum_quantity, pd.products_viewed,pd.products_url,p.products_id,p.products_image,p.products_image1,p.products_date_added,p.products_model,p.products_quantity,p.products_price,p.staffel_price as staffel_price,IF(s.status, s.specials_new_products_price, p.products_price) as final_price,p.products_date_available,p.tax_id,tr.tx_rate as tax_rate,p.manufacturers_id,pd.products_name,pd.products_shortdescription,c.categories_id,cd.categories_name';
		if ($this->ms['MODULES']['INCLUDE_PRODUCTS_DESCRIPTION_DB_FIELD_IN_PRODUCTS_LISTING']) {
			$required_cols.=',pd.products_description';
		}
		$select_clause="SELECT ".$required_cols;
		if (count($select)>0) {
			$select_clause.=', ';
			$select_clause.=implode(",", $select);
		}
		$from_clause.=" from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join static_taxes tr on p.tax_id = tr.uid, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd, tx_multishop_products_attributes_stock_group asg ";
		$where_clause.=" where p.products_status=1 ";
		if (!$this->masterShop) {
			$where_clause.=" and p.page_uid='".$this->showCatalogFromPage."' ";
		}
		$where_clause.=" and pd.language_id='".$this->sys_language_uid."' ";
		if (is_array($where) and count($where)>0) {
			$where_clause.='and ';
			$where_clause.=implode(",", $where);
		}
		$where_clause.=' and ';
		if (is_array($filter) and count($filter)>0) {
			$where_clause.=implode(" and ", $filter)." and ";
		} else if ($filter) {
			$where_clause.=$filter." and ";
		}
		$where_clause.=" pd.language_id=cd.language_id and p.products_id=p2c.products_id and p.products_id=pd.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id and p.products_id = asg.products_id and asg.not_allowed = 0 ";
		$array=array();
		// now do the real query including the order by and the limit
		$str=$select_clause.$from_clause.$where_clause;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows>0) {
			while ($product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array['products'][]=$product;
			}
		}
		return $array;
	}
	public function getProductAttributesStockGroupBox($product) {
		$filter='p.products_id = '.$product['products_id'];
		$pageset=mslib_fe::getProductsAttributesStockGroup($filter);
		$products=$pageset['products'];
		$products_count=count($products);
		if ($products_count>0) {
			$content='';
			if ($products_count) {
				if (!$this->ms['MODULES']['PRODUCTS_ATTRIBUTES_PAIR_TYPE']) {
					$this->ms['MODULES']['PRODUCTS_ATTRIBUTES_PAIR_TYPE']='default';
				}
				if (strstr($this->ms['MODULES']['PRODUCTS_ATTRIBUTES_PAIR_TYPE'], "..")) {
					die('error in PRODUCTS_ATTRIBUTES_PAIR_TYPE value');
				} else {
					if (strstr($this->ms['MODULES']['PRODUCTS_ATTRIBUTES_PAIR_TYPE'], "/")) {
						require($this->DOCUMENT_ROOT.$this->ms['MODULES']['PRODUCTS_ATTRIBUTES_PAIR_TYPE'].'.php');
					} else {
						require('includes/products_attributes_pair/'.$this->ms['MODULES']['PRODUCTS_ATTRIBUTES_PAIR_TYPE'].'.php');
					}
				}
			}
		}
		return $content;
	}
	// attributes stock
	public function getTaxes($tax_id) {
		if (!is_numeric($tax_id)) {
			return false;
		}
		if ($tax_id) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_taxes', // FROM ...
				'tax_id="'.addslashes($tax_id).'"', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row;
			}
		} else {
			return 0;
		}
	}
	public function getTaxRulesGroup($rules_group_id) {
		if (!is_numeric($rules_group_id)) {
			return false;
		}
		if ($rules_group_id) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_tax_rule_groups', // FROM ...
				'rules_group_id="'.addslashes($rules_group_id).'"', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row;
			}
		} else {
			return 0;
		}
	}
	public function getTaxRule($rule_id) {
		if (!is_numeric($rule_id)) {
			return false;
		}
		if ($rule_id) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_tax_rules', // FROM ...
				'rule_id="'.addslashes($rule_id).'"', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				return $row;
			}
		} else {
			return 0;
		}
	}
	public function taxRuleSet($tax_group_id, $current_price, $cn_iso_nr=0, $zn_country_iso_nr=0) {
		if (!$zn_country_iso_nr) {
			if (mslib_fe::loggedin()) {
				if (!$this->ADMIN_USER) {
					if (!$this->tta_user_info) {
						$row_shop_address=$this->tta_shop_info;
					} else {
						$row_shop_address=$this->tta_user_info['default'];
					}
				} else {
					$row_shop_address=$this->tta_shop_info;
				}
			} else {
				$row_shop_address=$this->tta_shop_info;
			}
			if (isset($row_shop_address['region']) && !empty($row_shop_address['region'])) {
				$zone_id=mslib_fe::getRegionByName($row_shop_address['region']);
				$zn_country_iso_nr=$zone_id['uid'];
			}
		}
		if ($tax_group_id) {
			$sql_local_tax_rate=$GLOBALS['TYPO3_DB']->SELECTquery('mt.rate as tax_rate,mt_c.rate as country_tax_rate,sc.cn_iso_nr as country_id,sc.cn_short_en as country_name,scz.uid as state_id,scz.zn_name_local as state_name,mtr.state_modus', // SELECT ...
				'tx_multishop_taxes mt LEFT JOIN tx_multishop_tax_rules mtr on mtr.tax_id = mt.tax_id LEFT JOIN tx_multishop_taxes mt_c on mtr.country_tax_id = mt_c.tax_id LEFT JOIN static_countries sc on sc.cn_iso_nr = mtr.cn_iso_nr LEFT JOIN static_country_zones scz on mtr.zn_country_iso_nr = scz.uid', // FROM ...
				'mtr.status = 1 and mtr.cn_iso_nr = '.addslashes($cn_iso_nr)." and mtr.zn_country_iso_nr = ".addslashes($zn_country_iso_nr)." and mtr.rules_group_id = ".addslashes($tax_group_id), // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry_local_tax_rate=$GLOBALS['TYPO3_DB']->sql_query($sql_local_tax_rate);
			// retry to get the tax ruleset only for the country
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry_local_tax_rate) && $zn_country_iso_nr>0) {
				$sql_local_tax_rate=$GLOBALS['TYPO3_DB']->SELECTquery('mt.rate as tax_rate,mt_c.rate as country_tax_rate,sc.cn_iso_nr as country_id,sc.cn_short_en as country_name,scz.uid as state_id,scz.zn_name_local as state_name,mtr.state_modus', // SELECT ...
					'tx_multishop_taxes mt left join tx_multishop_tax_rules mtr on mtr.tax_id = mt.tax_id left join tx_multishop_taxes mt_c on mtr.country_tax_id = mt_c.tax_id left join static_countries sc on sc.cn_iso_nr = mtr.cn_iso_nr left join static_country_zones scz on mtr.zn_country_iso_nr = scz.uid', // FROM ...
					'mtr.status = 1 and mtr.cn_iso_nr = '.addslashes($cn_iso_nr)." and mtr.zn_country_iso_nr = 0 and mtr.rules_group_id = ".addslashes($tax_group_id), // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry_local_tax_rate=$GLOBALS['TYPO3_DB']->sql_query($sql_local_tax_rate);
			}
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry_local_tax_rate)) {
				return false;
			}
			$tax_data=array();
			while ($row_local_tax_rate=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_local_tax_rate)) {
				$tax_data['local']=$row_local_tax_rate;
			}
			if ($tax_data['local']['state_modus']==2) {
				$state_tax_rate=$tax_data['local']['tax_rate'];
				$country_tax_rate=$tax_data['local']['country_tax_rate'];
				$total_tax_rate=$state_tax_rate+$country_tax_rate;
				$data['state_tax']=$state_tax;
				$data['country_tax']=$country_tax;
				$data['state_tax_rate']=$state_tax_rate;
				$data['country_tax_rate']=$country_tax_rate;
				$data['total_tax_rate']=$total_tax_rate;
			} else {
				$tax_rate=$tax_data['local']['tax_rate'];
				$total_tax_rate=$tax_rate;
				$data['tax']=$tax;
				$data['tax_rate']=$tax_rate;
				$data['total_tax_rate']=$total_tax_rate;
			}
			return $data;
		}
		return false;
	}
	public function getTaxRuleSet($tax_group_id, $current_price, $to_tax_include='true') {
		if (is_numeric($tax_group_id)) {
			if (mslib_fe::loggedin()) {
				if (!$this->ADMIN_USER) {
					if (!$this->tta_user_info) {
						$row_shop_address=$this->tta_shop_info;
					} else {
						$row_shop_address=$this->tta_user_info['default'];
					}
				} else {
					$row_shop_address=$this->tta_shop_info;
				}
			} else {
				$row_shop_address=$this->tta_shop_info;
			}
			$sql_local_tax_rate=$GLOBALS['TYPO3_DB']->SELECTquery('mt.rate as tax_rate,mt_c.rate as country_tax_rate,sc.cn_iso_nr as country_id,sc.cn_short_en as country_name,scz.uid as state_id,scz.zn_name_local as state_name,mtr.state_modus', // SELECT ...
				'tx_multishop_taxes mt left join tx_multishop_tax_rules mtr on mtr.tax_id = mt.tax_id left join tx_multishop_taxes mt_c on mtr.country_tax_id = mt_c.tax_id left join static_countries sc on sc.cn_iso_nr = mtr.cn_iso_nr left join static_country_zones scz on mtr.zn_country_iso_nr = scz.uid', // FROM ...
				'mtr.status = 1 and mtr.rules_group_id = '.addslashes($tax_group_id), // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry_local_tax_rate=$GLOBALS['TYPO3_DB']->sql_query($sql_local_tax_rate);
			$tax_data=array();
			while ($row_local_tax_rate=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_local_tax_rate)) {
				$row_local_tax_rate['tax_rate']=($row_local_tax_rate['tax_rate']/100)*100;
				$row_local_tax_rate['country_tax_rate']=($row_local_tax_rate['country_tax_rate']/100)*100;
				$tax_data['global'][]=$row_local_tax_rate;
				if (!empty($row_shop_address['region'])) {
					if (strtolower($row_shop_address['region'])==strtolower($row_local_tax_rate['state_name']) && strtolower($row_shop_address['country'])==strtolower($row_local_tax_rate['country_name'])) {
						$tax_data['local']=$row_local_tax_rate;
					}
					if (!count($tax_data['local']) && empty($row_local_tax_rate['state_name']) && strtolower($row_shop_address['country'])==strtolower($row_local_tax_rate['country_name'])) {
						$tax_data['local']=$row_local_tax_rate;
					}
				} else {
					if (empty($row_local_tax_rate['state_name']) && strtolower($row_shop_address['country'])==strtolower($row_local_tax_rate['country_name'])) {
						$tax_data['local']=$row_local_tax_rate;
					}
				}
			}
			if ($tax_data['local']['state_modus']==2) {
				$state_tax_rate=$tax_data['local']['tax_rate'];
				$country_tax_rate=$tax_data['local']['country_tax_rate'];
				$total_tax_rate=$state_tax_rate+$country_tax_rate;
				if ($to_tax_include=='true') {
					$state_tax=mslib_fe::taxDecimalCrop(($current_price*$total_tax_rate)/100);
					$country_tax=mslib_fe::taxDecimalCrop(($current_price*$country_tax_rate)/100);
					//$tmp_total_tax_rate 			= (($state_tax + $country_tax) / $current_price) * 100;
					//$total_tax_rate 				= $tmp_total_tax_rate;
					$data['price_including_tax']=$current_price+($state_tax);
					$data['state_tax']=$state_tax;
					$data['country_tax']=$country_tax;
					$data['state_tax_rate']=$state_tax_rate;
					$data['country_tax_rate']=$country_tax_rate;
					$data['total_tax_rate']=$total_tax_rate;
				} else {
					//reverse convert
					// number_format is needed otherwise PHP limits the decimals to 12, but we need 14 to bypass cents problems					
					$price_excluding_tax=number_format(($current_price/(100+$total_tax_rate))*100, 14);
					$data['price_excluding_tax']=$price_excluding_tax;
					$data['total_tax']=$state_tax;
					$data['state_tax_rate']=$state_tax_rate;
					$data['country_tax_rate']=$country_tax_rate;
					$data['total_tax_rate']=$total_tax_rate;
				}
			} else {
				$tax_rate=$tax_data['local']['tax_rate'];
				$total_tax_rate=$tax_rate;
				if ($to_tax_include=='true') {
					$tax=mslib_fe::taxDecimalCrop(($current_price*$total_tax_rate)/100);
					//$tmp_total_tax_rate 			= (($state_tax) / $current_price) * 100;
					//$total_tax_rate 				= $tmp_total_tax_rate;
					$data['price_including_tax']=$current_price+($tax);
					$data['tax']=$tax;
					$data['tax_rate']=$tax_rate;
					$data['total_tax_rate']=$total_tax_rate;
				} else {
					//reverse convert
					// number_format is needed otherwise PHP limits the decimals to 12, but we need 14 to bypass cents problems
					$price_excluding_tax=number_format(($current_price/(100+$total_tax_rate))*100, 14);
					$data['price_excluding_tax']=$price_excluding_tax;
					$data['tax']=$tax;
					$data['tax_rate']=$tax_rate;
					$data['total_tax_rate']=$total_tax_rate;
				}
			}
			return $data;
		}
		return false;
	}
	public function getAddressInfo($type='shop', $customer_id=0) {
		if ($this->conf['cacheConfiguration']) {
			$CACHE_FRONT_END=1;
		} else {
			$CACHE_FRONT_END=0;
		}
		if ($CACHE_FRONT_END) {
			$this->cacheLifeTime=2592000;
			$options=array(
				'caching'=>true,
				'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
				'lifeTime'=>$this->cacheLifeTime
			);
			$Cache_Lite=new Cache_Lite($options);
			$string=$this->cObj->data['uid'].'_ADDRESS_'.$type.'_'.$customer_id.'_'.$this->conf['tt_address_record_id_store'].'_'.$this->conf['fe_customer_pid'];
		}
		if (!$CACHE_FRONT_END or ($CACHE_FRONT_END and !$data=$Cache_Lite->get($string))) {
			if ($type=='shop') {
				$sql_tt_address="select *, sc.cn_iso_nr from tt_address tta, static_countries sc where tta.uid='".addslashes($this->conf['tt_address_record_id_store'])."' and tta.country=sc.cn_short_en";
				$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address)>0) {
					$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
				} else {
					if ($this->ADMIN_USER) {
						echo '<h1>Admin Error Message</h1>';
						$sql_tt_address="select * from tt_address where uid='".addslashes($this->conf['tt_address_record_id_store'])."'";
						$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address)>0) {
							// tt_address record has been found, but the country field does not contain a valid english country name
							echo 'System could fetch the store tt address record, but it contains an invalid country name (that is not found in static_countries table). Make sure you define the country field in tt address record in English. I.e. Netherlands, Germany, Austria etc.';
							exit();
						}
						echo 'System could not fetch the store tt address record id. Maybe the value of tt_address_record_id_store is incorrect?<br />Query: '.$sql_tt_address;
						$sql_tt_address="select * from tt_address where tx_multishop_address_type='store' and tx_multishop_customer_id=0 and page_uid='".$this->showCatalogFromPage."' and pid='".$this->conf['fe_customer_pid']."'";
						$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
						if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address)>0) {
							$item=array();
							$item['html']=mslib_befe::RunMultishopUpdate();
							$json=mslib_befe::array2json($item);
						}
						$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address)>0) {
							$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
							$row_tt_address=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
							$recordId=$row_tt_address['uid'];
							echo '<br /><br />Add the following line to your TYPO3 template (constants field):<br />plugin.multishop.tt_address_record_id_store='.$recordId;
						}
						exit();
					} else {
						// old fallback mode to bypass bugs
						$sql_tt_address="select * from tt_address where tx_multishop_address_type='store' and tx_multishop_customer_id=0 and page_uid='".$this->showCatalogFromPage."' and pid='".$this->conf['fe_customer_pid']."'";
						$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
						$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
					}
				}
			} elseif ($type=='customer') {
				$sql_tt_address="select * from tt_address where tx_multishop_customer_id=".$customer_id." and page_uid='".$this->showCatalogFromPage."' and pid='".$this->conf['fe_customer_pid']."'";
				$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
				while ($row_tt_address=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address)) {
					if ($row_tt_address['tx_multishop_default']==1) {
						$data['default']=$row_tt_address;
					} else {
						$data['delivery'][]=$row_tt_address;
					}
				}
			}
			if ($CACHE_FRONT_END) {
				$Cache_Lite->save(serialize($data));
			}
			return $data;
		} else {
			return unserialize($data);
		}
		return false;
	}
	public function getAddressUidInfo($uid) {
		if (is_numeric($uid)) {
			$sql_tt_address="select * from tt_address where uid='".$uid."'";
			$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
			return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
		}
	}
	public function getFeUserTTaddressDetails($customer_id, $tx_multishop_address_type='billing') {
		if (is_numeric($customer_id)) {
			$sql_tt_address="select * from tt_address where tx_multishop_address_type = '".$tx_multishop_address_type."' and tx_multishop_customer_id=".$customer_id." and page_uid='".$this->showCatalogFromPage."' and pid='".$this->conf ['fe_customer_pid']."'";
//			$sql_tt_address = "select * from tt_address where tx_multishop_address_type = '" . $tx_multishop_address_type . "' and tx_multishop_customer_id=" . $customer_id . " and pid='" . $this->conf ['fe_customer_pid'] . "'";
			$qry_tt_address=$GLOBALS ['TYPO3_DB']->sql_query($sql_tt_address);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address)>0) {
				return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
			}
			return false;
		}
		return false;
	}
	public function loadEnabledCountries() {
		// load enabled countries to array
		$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		$enabled_countries=array();
		while ($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) {
			$enabled_countries[]=$row2;
		}
		return $enabled_countries;
	}
	// deprecated methods
	// alias for old v2 client side scripts
	public function Money2Cents($amount, $customer_currency=1) {
		return mslib_fe::amount2Cents($amount, $customer_currency);
	}
	public function updateCart() {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
		$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
		$mslib_cart->init($this);
		$mslib_cart->updateCart();
	}
	public function countCartQuantity() {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
		$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
		$mslib_cart->init($this);
		return $mslib_cart->countCartQuantity();
	}
	public function countCartTotalPrice($subtract_discount=1, $include_vat=0, $country_id=0) {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
		$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
		$mslib_cart->init($this);
		return $mslib_cart->countCartTotalPrice($subtract_discount, $include_vat, $country_id);
	}
	public function countCartTotalTax($country_id=0) {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
		$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
		$mslib_cart->init($this);
		return $mslib_cart->countCartTotalTax($country_id);
	}
	public function mailOrder($orders_id, $copy_to_merchant=1, $custom_email_address='', $mail_template='') {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
		$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
		$mslib_order->init($this);
		return $mslib_order->mailOrder($orders_id, $copy_to_merchant, $custom_email_address, $mail_template);
	}
	public function getOrderTotalPrice($orders_id, $skip_method_costs=0) {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
		$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
		$mslib_order->init($this);
		return $mslib_order->getOrderTotalPrice($orders_id, $skip_method_costs);
	}
	public function getOrder($string, $field='orders_id') {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
		$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
		$mslib_order->init($this);
		return $mslib_order->getOrder($string, $field);
	}
	public function createOrder($address) {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
		$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
		$mslib_order->init($this);
		return $mslib_order->createOrder($address);
	}
	public function createOrdersProduct($orders_id, $orders_product=array()) {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
		$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
		$mslib_order->init($this);
		return $mslib_order->createOrdersProduct($orders_id, $orders_product);
	}
	public function printOrderDetailsTable($order, $template_type='site') {
		require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
		$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
		$mslib_order->init($this);
		return $mslib_order->printOrderDetailsTable($order, $template_type);
	}
	// deprecated methods eof
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_fe.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_fe.php"]);
}
?>