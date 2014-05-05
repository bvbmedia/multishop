<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
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
class tx_mslib_catalog {
	function sortCatalog($sortItem, $sortByField, $orderBy='asc') {
		set_time_limit(86400);
		ignore_user_abort(true);
		switch ($sortItem) {
			case 'manufacturers':
				switch ($sortByField) {
					case 'manufacturers_name':
						$query_array=array();
						$query_array['select'][]='c.categories_id';
						$query_array['from'][]='tx_multishop_manufacturers m';
						$query_array['where'][]='m.status=1';
						$query_array['order_by'][]='SUBSTRING_INDEX(m.manufacturers_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(m.manufacturers_name, " ", -1) AS SIGNED) '.$orderBy;
						$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
							(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
							(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
							(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
							(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
							(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
						);
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$counter=0;
						$content.='<div class="main-heading"><h2>Sorting Manufacturers on alphabet '.$orderBy.' done</h2></div>';
						while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
							$updateArray=array();
							$updateArray['sort_order']=$counter;
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id='.$row['manufacturers_id'], $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							$counter++;
						}
						break;
				}
				break;
			case 'categories':
				switch ($sortByField) {
					case 'categories_name':
						$query_array=array();
						$query_array['select'][]='c.categories_id';
						$query_array['from'][]='tx_multishop_categories c, tx_multishop_categories_description cd';
						$query_array['where'][]='c.status=1 and c.parent_id=\'0\' and c.page_uid=\''.$this->showCatalogFromPage.'\' and c.categories_id=cd.categories_id';
						$query_array['order_by'][]='SUBSTRING_INDEX(cd.categories_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(cd.categories_name, " ", -1) AS SIGNED) '.$orderBy;
						$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
							(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
							(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
							(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
							(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
							(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
						);
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$counter=0;
						$content.='<div class="main-heading"><h2>Sorting categories on alphabet '.$orderby.' done</h2></div>';
						while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
							$updateArray=array();
							$updateArray['sort_order']=$counter;
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id='.$row['categories_id'], $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							$content.=$row['categories_id'].'<br />';
							$counter++;
						}
						$subcategories_array=array();
						mslib_fe::getSubcats($subcategories_array, 0);
						if (count($subcategories_array)) {
							foreach ($subcategories_array as $item) {
								// try to sort the subcats
								$content.=$item.'<br />';
								$query_array=array();
								$query_array['select'][]='c.categories_id';
								$query_array['from'][]='tx_multishop_categories c, tx_multishop_categories_description cd';
								$query_array['where'][]='c.status=1 and c.parent_id=\''.$item.'\' and c.page_uid=\''.$this->showCatalogFromPage.'\' and c.categories_id=cd.categories_id';
								$query_array['order_by'][]='SUBSTRING_INDEX(cd.categories_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(cd.categories_name, " ", -1) AS SIGNED) '.$orderBy;
								$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
									(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
									(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
									(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
									(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
									(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
								);
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$counter=0;
								while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
									$updateArray=array();
									$updateArray['sort_order']=$counter;
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id='.$row['categories_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									$counter++;
								}
							}
						}
						break;
				}
				break;
			case 'products':
				switch ($sortByField) {
					case 'products_price':
						$content.='<div class="main-heading"><h2>Sorting products price '.$orderBy.' done</h2></div>';
						mslib_fe::getSubcats($subcategories_array, 0);
						if (count($subcategories_array)) {
							foreach ($subcategories_array as $item) {
								// try to sort the subcats
								$content.=$item.'<br />';
								// try to find and sort the products
								$query_array=array();
								$query_array['select'][]='p2c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
								$query_array['from'][]='tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c';
								$query_array['where'][]='p.products_status=1 and p.page_uid=\''.$this->showCatalogFromPage.'\' and p.products_id=pd.products_id and p.products_id=p2c.products_id and p2c.categories_id=\''.$item.'\'';
								$query_array['order_by'][]='final_price '.$orderBy;
								$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
									(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
									(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
									(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
									(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
									(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
								);
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$counter=0;
								while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
									$updateArray=array();
									$updateArray['sort_order']=$counter;
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id='.$row['products_id'].' and categories_id='.$row['categories_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									$updateArray=array();
									$updateArray['sort_order']=$counter;
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id='.$row['products_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									$counter++;
								}
							}
						}
						break;
					case 'products_name':
						$content.='<div class="main-heading"><h2>Sorting products name on alphabet '.$orderBy.' done</h2></div>';
						$subcategories_array=array();
						mslib_fe::getSubcats($subcategories_array, 0);
						if (count($subcategories_array)) {
							foreach ($subcategories_array as $item) {
								// try to find and sort the products
								$query_array=array();
								$query_array['select'][]='p2c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
								$query_array['from'][]='tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c';
								$query_array['where'][]='p.products_status=1 and p.page_uid=\''.$this->showCatalogFromPage.'\' and p.products_id=pd.products_id and p2c.categories_id=\''.$item.'\' and p.products_id=p2c.products_id';
								$query_array['order_by'][]='SUBSTRING_INDEX(pd.products_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(pd.products_name, " ", -1) AS SIGNED) '.$orderBy;
								$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
									(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
									(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
									(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
									(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
									(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
								);
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$counter=0;
								while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
									$updateArray=array();
									$updateArray['sort_order']=$counter;
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id='.$row['products_id'].' and categories_id='.$row['categories_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									$updateArray=array();
									$updateArray['sort_order']=$counter;
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id='.$row['products_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									$counter++;
								}
							}
						}
						break;
					case 'products_date_added':
						$content.='<div class="main-heading"><h2>Sorting products date added '.$orderBy.' done</h2></div>';
						$subcategories_array=array();
						mslib_fe::getSubcats($subcategories_array, 0);
						if (count($subcategories_array)) {
							foreach ($subcategories_array as $item) {
								//$content.= $item.'<br />';		
								// try to find and sort the products
								$query_array=array();
								$query_array['select'][]='p2c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
								$query_array['from'][]='tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c';
								$query_array['where'][]='p.products_status=1 and p.page_uid=\''.$this->showCatalogFromPage.'\' and p.products_id=pd.products_id and p2c.categories_id=\''.$item.'\' and p.products_id=p2c.products_id';
								$query_array['order_by'][]='p.products_date_added '.$orderBy;
								$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
									(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
									(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
									(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
									(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
									(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
								);
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$no=time();
								while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
									$updateArray=array();
									$updateArray['sort_order']=$no;
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id='.$row['products_id'].' and categories_id='.$row['categories_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									$updateArray=array();
									$updateArray['sort_order']=$no;
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id='.$row['products_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									if ($this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION']=='desc') {
										$no--;
									} else {
										$no++;
									}
								}
							}
						}
						break;
				}
				break;
			case 'attribute_values':
				switch ($sortByField) {
					case 'products_options_values_name':
						// manually (naturally) sort all attribute values
						$str="select * from tx_multishop_products_options where language_id='0' order by sort_order";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
						if ($rows) {
							while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
								$query_array=array();
								$query_array['select'][]='*';
								$query_array['from'][]='tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov';
								$query_array['where'][]='povp.products_options_id=\''.$row['products_options_id'].'\' and pov.language_id=\'0\' and povp.products_options_values_id=pov.products_options_values_id';
								$query_array['order_by'][]='SUBSTRING_INDEX(pov.products_options_values_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(pov.products_options_values_name, " ", -1) AS SIGNED) '.$orderBy;
								$str2=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
									(is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
									(is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
									(is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
									(is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
									(is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
								);
								$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
								$counter=0;
								while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
									$counter++;
									$updateArray=array();
									$updateArray['sort_order']=$counter;
									$query3=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', 'products_options_id=\''.$row2['products_options_id'].'\' and products_options_values_id=\''.$row2['products_options_values_id'].'\'', $updateArray);
									$res3=$GLOBALS['TYPO3_DB']->sql_query($query3);
								}
								// update sort eof	
							}
						}
						$content.='Attribute value sorting completed';
						break;
				}
				break;
		}
		return $content;
	}
	// universal hook method for giving plugin information about update/insert action of the product 
	function productsUpdateNotifierForPlugin($data, $product_id=0) {
		// handle with care, the $data is just direct information injected from $this->post/$item
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['productsUpdateNotifierForPlugin'])) {
			$params=array(
				'data'=>&$data,
				'product_id'=>&$product_id
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['productsUpdateNotifierForPlugin'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_catalog.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_catalog.php"]);
}
?>