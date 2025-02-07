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
    function getProductByName($products_name, $categories_name = '') {
        $filter = array();
        $filter[] = 'c.page_uid=\'' . $this->showCatalogFromPage . '\'';
        $filter[] = 'p.page_uid=\'' . $this->showCatalogFromPage . '\'';
        $filter[] = 'c.status = \'1\'';
        $filter[] = 'p.products_status = \'1\'';
        if ($categories_name) {
            $filter[] = 'cd.categories_name=\'' . addslashes($categories_name) . '\'';
        }
        if ($products_name) {
            $filter[] = 'pd.products_name=\'' . addslashes($products_name) . '\'';
        }
        $filter[] = 'pd.language_id=' . $this->sys_language_uid . '';
        $filter[] = 'cd.language_id=' . $this->sys_language_uid . '';
        $filter[] = 'p2c.is_deepest=1';
        $filter[] = 'c.categories_id=cd.categories_id';
        $filter[] = 'p2c.products_id=p.products_id';
        $filter[] = 'p.products_id=pd.products_id';
        $qry = $GLOBALS['TYPO3_DB']->SELECTquery('c.categories_id, cd.categories_name, p.products_id, pd.products_name', // SELECT ...
                'tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_categories c, tx_multishop_categories_description cd, tx_multishop_products_to_categories p2c', // FROM ...
                implode(' AND ', $filter), // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($qry);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            return $row;
        }
    }
    function createProduct($data) {
        // ADD PRODUCT
        $insertArray = array();
        $insertArray['products_date_added'] = time();
        $insertArray['products_status'] = 1;
        $insertArray['page_uid'] = $this->showCatalogFromPage;
        $insertArray['tax_id'] = $data['tax_id'];
        $insertArray['products_price'] = $data['products_price'];
        $insertArray['products_date_added'] = $data['products_date_added'];
        if (!$insertArray['products_date_added']) {
            $insertArray['products_date_added'] = time();
        }
	    $insertArray['products_last_modified'] = time();
        $insertArray['products_condition'] = $data['products_condition'];
        if (!$insertArray['products_condition']) {
            $insertArray['products_condition'] = 'new';
        }
        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products', $insertArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        $id = $GLOBALS['TYPO3_DB']->sql_insert_id();
        if ($id) {
            $insertArray = array();
            $insertArray['products_id'] = $id;
            $insertArray['products_name'] = $data['products_name'];
            $insertArray['language_id'] = $data['language_id'];
            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $insertArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            $insertArray = array();
            $insertArray['products_id'] = $id;
            $insertArray['categories_id'] = $data['categories_id'];
            $insertArray['sort_order'] = time();
            /*$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
            $res=$GLOBALS['TYPO3_DB']->sql_query($query);*/
            // create categories tree linking
            tx_mslib_catalog::linkCategoriesTreeToProduct($id, $data['categories_id'], $insertArray);
            return $id;
        }
    }
    function linkCategoriesTreeToProduct($pid, $deepest_cat_id, $dataArray = array()) {
        if (!is_numeric($pid)) {
            return false;
        }
        if (!is_numeric($deepest_cat_id)) {
            return false;
        }
        $level = 1;
        $cats = mslib_fe::globalCrumbarTree($deepest_cat_id);
        $cats = array_reverse($cats);
        //
        $crumbar_ident_string = '';
        $crumbar_ident_array = array();
        foreach ($cats as $item) {
            $crumbar_ident_array[] = $item['id'];
        }
        $crumbar_ident_string = implode(',', $crumbar_ident_array);
        $count_cats = count($cats);
        /*if ($count_cats>1) {
            // remove the deepest cat id record
            // disabled by bas
            //unset($cats[$count_cats-1]);
            //recount
            //$count_cats=count($cats);
        }*/
        if ($count_cats > 0) {
            foreach ($cats as $item) {
                if ($item['id']) {
                    $rec = tx_mslib_catalog::isProductToCategoryLinkingExist($pid, $item['id'], $crumbar_ident_string);
                    if (!$rec) {
                        $insertArray = array();
                        if (!is_array($dataArray) || (is_array($dataArray) && !count($dataArray))) {
                            $insertArray['categories_id'] = $deepest_cat_id;
                            $insertArray['products_id'] = $pid;
                            $insertArray['page_uid'] = $item['page_uid'];
                            $insertArray['sort_order'] = time();
                            $insertArray['related_to'] = 0;
                        } else {
                            foreach ($dataArray as $idx => $val) {
                                $insertArray[$idx] = $val;
                            }
                        }
                        $insertArray['node_id'] = $item['id'];
                        if ($item['id'] == $deepest_cat_id) {
                            $insertArray['is_deepest'] = 1;
                        } else {
                            $insertArray['is_deepest'] = 0;
                        }
                        $insertArray['crumbar_identifier'] = $crumbar_ident_string;
	                    $updateArray['crdate'] = time();
	                    $updateArray['last_updated_at'] = time();
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    } else {
                        $updateArray = array();
                        if ($item['id'] == $deepest_cat_id) {
                            $updateArray['is_deepest'] = 1;
                        } else {
                            $updateArray['is_deepest'] = 0;
                        }
						if ($dataArray['page_uid']) {
							$updateArray['page_uid'] = $dataArray['page_uid'];
						} else {
							$updateArray['page_uid'] = $item['page_uid'];
						}
                        $updateArray['crumbar_identifier'] = $crumbar_ident_string;
                        if (is_array($dataArray) && isset($dataArray['sort_order']) && is_numeric($dataArray['sort_order'])) {
                            $updateArray['sort_order'] = $dataArray['sort_order'];
                        }
	                    $updateArray['last_updated_at'] = time();
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_to_categories_id=\'' . $rec['products_to_categories_id'] . '\'', $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                    $level++;
                }
            }
            return true;
        }
    }
    function isProductToCategoryLinkingExist($pid, $node_id, $crumbar_string) {
        $cats_list = explode(',', $crumbar_string);
        $deepeset_cat_id = $cats_list[count($cats_list) - 1];
        //$rec=mslib_befe::getRecord($pid, 'tx_multishop_products_to_categories p2c', 'products_id', array('node_id=\''.$node_id.'\' and crumbar_identfier=\''.$crumbar_string.'\' and page_uid=\''.$this->shop_pid.'\''));
        $rec = mslib_befe::getRecord($pid, 'tx_multishop_products_to_categories p2c', 'products_id', array('node_id=\'' . $node_id . '\' and categories_id = \'' . $deepeset_cat_id . '\' and page_uid=\'' . $this->shop_pid . '\''));
        if (is_array($rec) && isset($rec['products_id']) && $rec['products_id'] > 0) {
            return $rec;
        } else {
            return false;
        }
    }
    function createAttributeOptionName($data) {
        // ADD PRODUCT ATTRIBUTE OPTION
        $filter = array();
        $filter[] = 'language_id=' . $data['language_id'];
        if (mslib_befe::ifExists($data['products_options_name'], 'tx_multishop_products_options', 'products_options_name', $filter)) {
            $record = mslib_befe::getRecord($data['products_options_name'], 'tx_multishop_products_options', 'products_options_name', $filter);
            return $record['products_options_id'];
        } else {
            // Insert option name
            $insertArray = array();
            $insertArray['language_id'] = $data['language_id'];
            $insertArray['products_options_name'] = $data['products_options_name'];
            $insertArray['listtype'] = 'pulldownmenu';
            $insertArray['attributes_values'] = '0';
            $insertArray['sort_order'] = time();
            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
            $GLOBALS['TYPO3_DB']->sql_query($query);
            return $GLOBALS['TYPO3_DB']->sql_insert_id();
        }
    }
    function createAttributeOptionValue($data) {
        if (!is_numeric($data['products_options_id'])) {
            return;
        }
        // ADD PRODUCT ATTRIBUTE OPTION
        $filter = array();
        //$filter[]='language_id='.$data['language_id'];
        $filter[] = 'povp.products_options_id=' . $data['products_options_id'];
        //$filter[]='pov.products_options_values_name=\''.addslashes($data['products_options_values_name']).'\'';
        $filter[] = 'povp.products_options_values_id=pov.products_options_values_id';
        $from = 'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov';
        //$str2="SELECT pov.products_options_values_id from tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov where
        // povp.products_options_id='".addslashes($products_options_id)."' and
        // pov.products_options_values_name='".addslashes($option_value)."' and
        // povp.products_options_values_id=pov.products_options_values_id";
        if (mslib_befe::ifExists($data['products_options_values_name'], $from, 'pov.products_options_values_name', $filter)) {
            $record = mslib_befe::getRecord($data['products_options_values_name'], $from, 'pov.products_options_values_name', $filter);
            $products_options_values_id = $record['products_options_values_id'];
        } else {
            $insertArray = array();
            $insertArray['language_id'] = $data['language_id'];
            $insertArray['products_options_values_name'] = $data['products_options_values_name'];
            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $insertArray);
            $GLOBALS['TYPO3_DB']->sql_query($query);
            $products_options_values_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
        }
        if ($data['products_options_id'] && $products_options_values_id) {
            $filter = array();
            $filter[] = 'products_options_id=' . $data['products_options_id'];
            if (!mslib_befe::ifExists($products_options_values_id, 'tx_multishop_products_options_values_to_products_options', 'products_options_values_id', $filter)) {
                $insertArray = array();
                $insertArray['products_options_id'] = $data['products_options_id'];
                $insertArray['products_options_values_id'] = $products_options_values_id;
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values_to_products_options', $insertArray);
                $GLOBALS['TYPO3_DB']->sql_query($query);
            }
            return $products_options_values_id;
        }
    }
    function sortCatalog($sortItem, $sortByField, $orderBy = 'asc', $primaryId='') {
        set_time_limit(86400);
        ignore_user_abort(true);
        switch ($sortItem) {
            case 'manufacturers':
                switch ($sortByField) {
                    case 'manufacturers_name':
                        $query_array = array();
                        $query_array['select'][] = 'm.manufacturers_id';
                        $query_array['from'][] = 'tx_multishop_manufacturers m';
                        $query_array['where'][] = 'm.status=1';
                        //$query_array['order_by'][]='SUBSTRING_INDEX(m.manufacturers_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(m.manufacturers_name, " ", -1) AS SIGNED) '.$orderBy;
                        $query_array['order_by'][] = 'm.manufacturers_name ' . $orderBy;
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                        );
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $counter = 0;
                        $content .= '<div class="main-heading"><h2>Sorting Manufacturers on alphabet ' . $orderBy . ' done</h2></div>';
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $updateArray = array();
                            $updateArray['sort_order'] = $counter;
	                        $updateArray['last_modified'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=' . $row['manufacturers_id'], $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            $counter++;
                        }
                        break;
                }
                break;
            case 'categories':
                switch ($sortByField) {
                    case 'categories_name':
                    case 'categories_name_natural':
                        $content .= '<div class="main-heading"><h2>Sorting categories on name ' . $orderBy . ' done</h2></div>';
                        $query_array = array();
                        $query_array['select'][] = 'c.categories_id,cd.categories_name';
                        $query_array['from'][] = 'tx_multishop_categories c, tx_multishop_categories_description cd';
                        //$query_array['where'][]='c.status=1 and c.parent_id=\''.$this->categoriesStartingPoint.'\' and c.page_uid=\''.$this->showCatalogFromPage.'\' and c.categories_id=cd.categories_id';
                        $query_array['where'][] = 'c.status=1 and c.page_uid=\'' . $this->showCatalogFromPage . '\' and c.categories_id=cd.categories_id';
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                        );
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $valuesArray = array();
                        while ($item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $values_name = $item['categories_name'];
                            // if the first char is not alphanumeric we cut it off, so we can sort much better
                            if ($values_name and !preg_match("/^[a-z0-9]/i", $values_name)) {
                                do {
                                    $values_name = substr($values_name, 1, strlen($values_name));
                                } while ($values_name and !preg_match("/^[a-z0-9]/i", $values_name));
                            }
                            // we now have a name that starts with alphanumeric
                            $valuesArray[$item['categories_id']] = $values_name;
                        }
                        // now let PHP sort the array
                        natcasesort($valuesArray);
                        switch ($orderBy) {
                            case 'desc':
                                $valuesArray = array_reverse($valuesArray);
                                break;
                        }
                        $sort = 1;
                        // iterate each value and save the new sort order number to DB
                        foreach ($valuesArray as $categories_id => $values_name) {
                            $updateArray = array();
                            $updateArray['sort_order'] = $sort;
	                        $updateArray['last_modified'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=' . $categories_id, $updateArray);
                            $GLOBALS['TYPO3_DB']->sql_query($query);
                            $sort++;
                        }
                        break;
                    case 'categories_name_old':
                        $query_array = array();
                        $query_array['select'][] = 'c.categories_id';
                        $query_array['from'][] = 'tx_multishop_categories c, tx_multishop_categories_description cd';
                        $query_array['where'][] = 'c.status=1 and c.parent_id=\'0\' and c.page_uid=\'' . $this->showCatalogFromPage . '\' and c.categories_id=cd.categories_id';
                        $query_array['order_by'][] = 'SUBSTRING_INDEX(cd.categories_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(cd.categories_name, " ", -1) AS SIGNED) ' . $orderBy;
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                        );
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $counter = 0;
                        $content .= '<div class="main-heading"><h2>Sorting categories on alphabet ' . $orderby . ' done</h2></div>';
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $updateArray = array();
                            $updateArray['sort_order'] = $counter;
	                        $updateArray['last_modified'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=' . $row['categories_id'], $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            $content .= $row['categories_id'] . '<br />';
                            $counter++;
                        }
                        $subcategories_array = array();
                        mslib_fe::getSubcats($subcategories_array, 0);
                        if (count($subcategories_array)) {
                            foreach ($subcategories_array as $item) {
                                // try to sort the subcats
                                $content .= $item . '<br />';
                                $query_array = array();
                                $query_array['select'][] = 'c.categories_id';
                                $query_array['from'][] = 'tx_multishop_categories c, tx_multishop_categories_description cd';
                                $query_array['where'][] = 'c.status=1 and c.parent_id=\'' . $item . '\' and c.page_uid=\'' . $this->showCatalogFromPage . '\' and c.categories_id=cd.categories_id';
                                $query_array['order_by'][] = 'SUBSTRING_INDEX(cd.categories_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(cd.categories_name, " ", -1) AS SIGNED) ' . $orderBy;
                                $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                        (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                        (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                        (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                        (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                        (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                                );
                                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                                $counter = 0;
                                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                                    $updateArray = array();
                                    $updateArray['sort_order'] = $counter;
	                                $updateArray['last_modified'] = time();
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=' . $row['categories_id'], $updateArray);
                                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                    $counter++;
                                    $query_array = array();
                                    $query_array['select'][] = 'c.categories_id';
                                    $query_array['from'][] = 'tx_multishop_categories c, tx_multishop_categories_description cd';
                                    $query_array['where'][] = 'c.status=1 and c.parent_id=\'' . $row['categories_id'] . '\' and c.page_uid=\'' . $this->showCatalogFromPage . '\' and c.categories_id=cd.categories_id';
                                    $query_array['order_by'][] = 'SUBSTRING_INDEX(cd.categories_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(cd.categories_name, " ", -1) AS SIGNED) ' . $orderBy;
                                    $str2 = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                            (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                            (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                            (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                            (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                            (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                                    );
                                    $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
                                    $counter = 0;
                                    while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) {
                                        $updateArray = array();
                                        $updateArray['sort_order'] = $counter;
	                                    $updateArray['last_modified'] = time();
                                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=' . $row2['categories_id'], $updateArray);
                                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                        $counter++;
                                    }
                                }
                            }
                        }
                        break;
                }
                break;
            case 'products':
                switch ($sortByField) {
                    case 'products_price':
                        $content .= '<div class="main-heading"><h2>Sorting products price ' . $orderBy . ' done</h2></div>';
                        // try to sort the subcats
                        $content .= $item . '<br />';
                        // try to find and sort the products
                        $query_array = array();
                        $query_array['select'][] = 'p2c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
                        $query_array['from'][] = 'tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c';
                        $query_array['where'][] = 'p.products_status=1 and p.page_uid=\'' . $this->showCatalogFromPage . '\' and p.products_id=pd.products_id and p.products_id=p2c.products_id';
                        $query_array['order_by'][] = 'final_price ' . $orderBy;
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                        );
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $counter = 0;
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $updateArray = array();
                            $updateArray['sort_order'] = $counter;
	                        $updateArray['last_updated_at'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id=' . $row['products_id'] . ' and categories_id=' . $row['categories_id'], $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
							// Update product
	                        $updateArray = array();
	                        $updateArray['sort_order'] = $counter;
	                        $updateArray['products_last_modified'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=' . $row['products_id'], $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            if ($this->ms['MODULES']['FLAT_DATABASE']) {
	                            unset($updateArray['products_last_modified']);
                                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_flat', 'products_id=' . $row['products_id'], $updateArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            }
                            $counter++;
                        }
                        // per category is not optimal when using wide products search
                        /*
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
                        */
                        break;
                    case 'products_name':
                        $content .= '<div class="main-heading"><h2>Sorting products name on alphabet ' . $orderBy . ' done</h2></div>';
                        $subcategories_array = array();
                        mslib_fe::getSubcats($subcategories_array, 0);
                        if (count($subcategories_array)) {
                            foreach ($subcategories_array as $item) {
                                // try to find and sort the products
                                $query_array = array();
                                $query_array['select'][] = 'pd.products_name,p2c.categories_id, p.products_id';
                                $query_array['from'][] = 'tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c';
                                $query_array['where'][] = 'p.products_status=1 and p.page_uid=\'' . $this->showCatalogFromPage . '\' and p.products_id=pd.products_id and p2c.categories_id=\'' . $item . '\' and p.products_id=p2c.products_id';
                                $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                        (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                        (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                        (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                        '', // ORDER BY...
                                        (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                                );
                                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                                $counter = 0;
                                $valuesArray = array();
                                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                                    $values_name = $row['products_name'];
                                    // if the first char is not alphanumeric we cut it off, so we can sort much better
                                    if ($values_name and !preg_match("/^[a-z0-9]/i", $values_name)) {
                                        do {
                                            $values_name = substr($values_name, 1, strlen($values_name));
                                        } while ($values_name and !preg_match("/^[a-z0-9]/i", $values_name));
                                    }
                                    // we now have a name that starts with alphanumeric
                                    $valuesArray[$row['products_id']] = $values_name;
                                }
                                // now let PHP sort the array
                                natcasesort($valuesArray);
                                switch ($orderBy) {
                                    case 'desc':
                                        $valuesArray = array_reverse($valuesArray);
                                        break;
                                }
                                $sort = 1;
                                // iterate each value and save the new sort order number to DB
                                foreach ($valuesArray as $products_id => $values_name) {
                                    $updateArray = array();
                                    $updateArray['sort_order'] = $sort;
	                                $updateArray['last_updated_at'] = time();
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id=' . $products_id, $updateArray);
                                    $GLOBALS['TYPO3_DB']->sql_query($query);
	                                // Update product
	                                $updateArray = array();
	                                $updateArray['sort_order'] = $sort;
	                                $updateArray['products_last_modified'] = time();
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=' . $products_id, $updateArray);
                                    $GLOBALS['TYPO3_DB']->sql_query($query);
                                    $sort++;
                                }
                            }
                        }
                        break;
                    case 'products_name_old':
                        $content .= '<div class="main-heading"><h2>Sorting products name on alphabet ' . $orderBy . ' done</h2></div>';
                        $subcategories_array = array();
                        mslib_fe::getSubcats($subcategories_array, 0);
                        if (count($subcategories_array)) {
                            foreach ($subcategories_array as $item) {
                                // try to find and sort the products
                                $query_array = array();
                                $query_array['select'][] = 'p2c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
                                $query_array['from'][] = 'tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c';
                                $query_array['where'][] = 'p.products_status=1 and p.page_uid=\'' . $this->showCatalogFromPage . '\' and p.products_id=pd.products_id and p2c.categories_id=\'' . $item . '\' and p.products_id=p2c.products_id';
                                $query_array['order_by'][] = 'SUBSTRING_INDEX(pd.products_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(pd.products_name, " ", -1) AS SIGNED) ' . $orderBy;
                                $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                        (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                        (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                        (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                        (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                        (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                                );
                                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                                $counter = 0;
                                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                                    $updateArray = array();
                                    $updateArray['sort_order'] = $counter;
	                                $updateArray['last_updated_at'] = time();
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id=' . $row['products_id'] . ' and categories_id=' . $row['categories_id'], $updateArray);
                                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                    $updateArray = array();
                                    $updateArray['sort_order'] = $counter;
	                                $updateArray['products_last_modified'] = time();
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=' . $row['products_id'], $updateArray);
                                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                    $counter++;
                                }
                            }
                        }
                        break;
                    case 'products_date_added':
                        $content .= '<div class="main-heading"><h2>Sorting products date added ' . $orderBy . ' done</h2></div>';
                        $subcategories_array = array();
                        mslib_fe::getSubcats($subcategories_array, 0);
                        if (count($subcategories_array)) {
                            foreach ($subcategories_array as $item) {
                                //$content.= $item.'<br />';
                                // try to find and sort the products
                                $query_array = array();
                                $query_array['select'][] = 'p2c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
                                $query_array['from'][] = 'tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c';
                                $query_array['where'][] = 'p.products_status=1 and p.page_uid=\'' . $this->showCatalogFromPage . '\' and p.products_id=pd.products_id and p2c.categories_id=\'' . $item . '\' and p.products_id=p2c.products_id';
                                $query_array['order_by'][] = 'p.products_date_added ' . $orderBy;
                                $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                        (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                        (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                        (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                        (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                        (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                                );
                                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                                $no = time();
                                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                                    $updateArray = array();
                                    $updateArray['sort_order'] = $no;
	                                $updateArray['last_updated_at'] = time();
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id=' . $row['products_id'] . ' and categories_id=' . $row['categories_id'], $updateArray);
                                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                    if ($this->conf['debugEnabled'] == '1') {
                                        $logString = 'Resort catalog (' . $sortByField . '). Query: ' . $query;
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
                                    }
                                    $updateArray = array();
                                    $updateArray['sort_order'] = $no;
	                                $updateArray['products_last_modified'] = time();
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=' . $row['products_id'], $updateArray);
                                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                    if ($this->conf['debugEnabled'] == '1') {
                                        $logString = 'Resort catalog (' . $sortByField . '). Query: ' . $query;
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
                                    }
                                    if ($this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'] == 'desc') {
                                        $no--;
                                    } else {
                                        $no++;
                                    }
                                }
                            }
                        }
                        break;
                    case 'products_main_categories':
                        $content .= '<div class="main-heading"><h2>Sorting products main categories ' . $orderBy . ' done</h2></div>';
                        //$content.= $item.'<br />';
                        // try to find and sort the products
                        $query_array = array();
                        $query_array['select'][] = 'c.sort_order, p2c.categories_id, p.products_id';
                        $query_array['from'][] = 'tx_multishop_products p, tx_multishop_products_to_categories p2c, tx_multishop_categories c';
                        $query_array['where'][] = 'p.products_status=1 and p.page_uid=\'' . $this->showCatalogFromPage . '\' and c.parent_id=\'0\' and p2c.page_uid=p.page_uid and p.products_id=p2c.products_id and p2c.node_id=c.categories_id';
                        $query_array['order_by'][] = 'c.sort_order ' . $orderBy;
                        $query_array['group_by'][] = 'p.products_id';
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                        );
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $updateArray = array();
                            $updateArray['sort_order'] = $row['sort_order'];
	                        $updateArray['last_updated_at'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id=' . $row['products_id'] . ' and page_uid=' . $this->showCatalogFromPage, $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            if ($this->conf['debugEnabled'] == '1') {
                                $logString = 'Resort catalog (' . $sortByField . '). Query: ' . $query;
                                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
                            }
                            $updateArray = array();
                            $updateArray['sort_order'] = $row['sort_order'];
	                        $updateArray['products_last_modified'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=' . $row['products_id'], $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            if ($this->conf['debugEnabled'] == '1') {
                                $logString = 'Resort catalog (' . $sortByField . '). Query: ' . $query;
                                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
                            }
                        }
                        break;
                    case 'products_deepest_categories':
                        $content .= '<div class="main-heading"><h2>Sorting products deepest categories ' . $orderBy . ' done</h2></div>';
                        //$content.= $item.'<br />';
                        // try to find and sort the products
                        $query_array = array();
                        $query_array['select'][] = 'c.sort_order, p2c.categories_id, p.products_id';
                        $query_array['from'][] = 'tx_multishop_products p, tx_multishop_products_to_categories p2c, tx_multishop_categories c';
                        $query_array['where'][] = 'p.products_status=1 and p.page_uid=\'' . $this->showCatalogFromPage . '\' and p2c.is_deepest=\'1\' and p2c.page_uid=p.page_uid p.products_id=p2c.products_id and p2c.categories_id=c.categories_id';
                        $query_array['order_by'][] = 'c.sort_order ' . $orderBy;
                        $query_array['group_by'][] = 'p.products_id';
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                        );
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $no = time();
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $updateArray = array();
                            $updateArray['sort_order'] = $row['sort_order'];
	                        $updateArray['last_updated_at'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_id=' . $row['products_id'] . ' and page_uid=' . $this->showCatalogFromPage, $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            if ($this->conf['debugEnabled'] == '1') {
                                $logString = 'Resort catalog (' . $sortByField . '). Query: ' . $query;
                                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
                            }
                            $updateArray = array();
                            $updateArray['sort_order'] = $no;
	                        $updateArray['products_last_modified'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=' . $row['products_id'], $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            if ($this->conf['debugEnabled'] == '1') {
                                $logString = 'Resort catalog (' . $sortByField . '). Query: ' . $query;
                                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
                            }
                        }
                        break;
                }
                break;
            case 'attribute_values':
                switch ($sortByField) {
                    case 'products_options_values_name':
                        // manually (naturally) sort all attribute values
                        $str = "select * from tx_multishop_products_options where language_id='0' order by sort_order";
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $rows = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);
                        if ($rows) {
                            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                                $query_array = array();
                                $query_array['select'][] = '*';
                                $query_array['from'][] = 'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov';
                                $query_array['where'][] = 'povp.products_options_id=\'' . $row['products_options_id'] . '\' and pov.language_id=\'0\' and povp.products_options_values_id=pov.products_options_values_id';
                                $query_array['order_by'][] = 'SUBSTRING_INDEX(pov.products_options_values_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(pov.products_options_values_name, " ", -1) AS SIGNED) ' . $orderBy;
                                $str2 = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                        (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                        (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                        (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                        (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                        (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                                );
                                $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
                                $counter = 0;
                                while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
                                    $counter++;
                                    $updateArray = array();
                                    $updateArray['sort_order'] = $counter;
                                    $query3 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', 'products_options_id=\'' . $row2['products_options_id'] . '\' and products_options_values_id=\'' . $row2['products_options_values_id'] . '\'', $updateArray);
                                    $res3 = $GLOBALS['TYPO3_DB']->sql_query($query3);
                                    // product level
                                    $where = "options_id = " . $row2['products_options_id'] . " and options_values_id=" . $row2['products_options_values_id'] . " and page_uid=" . $this->showCatalogFromPage;
                                    $updateArray = array();
                                    $updateArray = array(
                                            'sort_order_option_value' => $counter
                                    );
                                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', $where, $updateArray);
                                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                }
                                // update sort eof
                            }
                        }
                        $content .= 'Attribute value sorting completed';
                        break;
                    case 'products_options_values_name_natural':
                        // get all attribute options
                        $filter=array();
                        if (is_numeric($primaryId)) {
                            $filter[]='products_options_id='.$primaryId;
                        }
                        $options_ids = mslib_befe::getRecords('0', 'tx_multishop_products_options', 'language_id', $filter);
                        //$options_ids=array();
                        //test
                        //$options_ids[0]=array('products_options_id'=>'17');
                        foreach ($options_ids as $options_id) {
                            $valuesArray = array();
                            $values_id = array();
                            // iterate each attribute option and get the values
                            $sql = "select pov2po.*, pov.products_options_values_name, pov.products_options_values_id from tx_multishop_products_options_values_to_products_options pov2po, tx_multishop_products_options_values pov where pov2po.products_options_id = " . $options_id['products_options_id'] . " and pov.products_options_values_id = pov2po.products_options_values_id";
                            $qry = $GLOBALS['TYPO3_DB']->sql_query($sql);
                            $values_id = array();
                            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                                $values_name = $row['products_options_values_name'];
                                // if the first char is not alphanumeric we cut it off, so we can sort much better
                                if ($values_name and !preg_match("/^[a-z0-9]/i", $values_name)) {
                                    do {
                                        $values_name = substr($values_name, 1, strlen($values_name));
                                    } while ($values_name and !preg_match("/^[a-z0-9]/i", $values_name));
                                }
                                // we now have a name that starts with alphanumeric
                                $valuesArray[$row['products_options_values_to_products_options_id']] = $values_name;
                                $values_id[$values_name] = $row['products_options_values_id'];
                            }
                            // now let PHP sort the array
                            natcasesort($valuesArray);
                            switch ($orderBy) {
                                case 'desc':
                                    $valuesArray = array_reverse($valuesArray);
                                    break;
                            }
                            $sort = 1;
                            // iterate each value and save the new sort order number to DB
                            foreach ($valuesArray as $pov2po_row_id => $values_name) {
                                $updateArray = array();
                                $updateArray['sort_order'] = $sort;
                                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', "products_options_values_to_products_options_id = " . $pov2po_row_id . " and products_options_id = " . $options_id['products_options_id'], $updateArray);
                                $GLOBALS['TYPO3_DB']->sql_query($query);
                                // product level
                                $where = "options_id = " . $options_id['products_options_id'] . " and options_values_id=" . $values_id[$values_name] . " and page_uid=" . $this->showCatalogFromPage;
                                $updateArray = array();
                                $updateArray = array(
                                        'sort_order_option_value' => $sort
                                );
                                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', $where, $updateArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                //
                                $sort++;
                            }
                        }
                        $content .= 'Attribute value sorting (natural) completed';
                        break;
                }
                break;
            case 'attribute_names':
                switch ($sortByField) {
                    case 'products_options_name':
                        $query_array = array();
                        $query_array['select'][] = '*';
                        $query_array['from'][] = 'tx_multishop_products_options po';
                        $query_array['where'][] = 'po.language_id=\'0\'';
                        $query_array['order_by'][] = 'SUBSTRING_INDEX(po.products_options_name, " ", 1) ASC, CAST(SUBSTRING_INDEX(po.products_options_name, " ", -1) AS SIGNED) ' . $orderBy;
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($query_array['select']) ? implode(",", $query_array['select']) : ''), // SELECT ...
                                (is_array($query_array['from']) ? implode(",", $query_array['from']) : ''), // FROM ...
                                (is_array($query_array['where']) ? implode(" and ", $query_array['where']) : ''), // WHERE...
                                (is_array($query_array['group_by']) ? implode(",", $query_array['group_by']) : ''), // GROUP BY...
                                (is_array($query_array['order_by']) ? implode(",", $query_array['order_by']) : ''), // ORDER BY...
                                (is_array($query_array['limit']) ? implode(",", $query_array['limit']) : '') // LIMIT ...
                        );
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $counter = 0;
                        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                            $counter++;
                            $where = "products_options_id = " . $row['products_options_id'];
                            $updateArray = array(
                                    'sort_order' => $counter
                            );
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', $where, $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            // products level
                            $where = "options_id = " . $row['products_options_id'] . " and page_uid='" . $this->showCatalogFromPage . "'";
                            $updateArray = array();
                            $updateArray = array(
                                    'sort_order_option_name' => $counter
                            );
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', $where, $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                        $content .= 'Attribute name sorting completed';
                        break;
                    case 'products_options_name_natural':
                        $valuesArray = array();
                        // iterate each attribute option and get the values
                        $sql = "select po.* from tx_multishop_products_options po";
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($sql);
                        $values_id = array();
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $values_name = $row['products_options_name'];
                            // if the first char is not alphanumeric we cut it off, so we can sort much better
                            if ($values_name and !preg_match("/^[a-z0-9]/i", $values_name)) {
                                do {
                                    $values_name = substr($values_name, 1, strlen($values_name));
                                } while ($values_name and !preg_match("/^[a-z0-9]/i", $values_name));
                            }
                            // we now have a name that starts with alphanumeric
                            $valuesArray[$row['products_options_id']] = $values_name;
                        }
                        // now let PHP sort the array
                        natcasesort($valuesArray);
                        switch ($orderBy) {
                            case 'desc':
                                $valuesArray = array_reverse($valuesArray);
                                break;
                        }
                        $sort = 1;
                        // iterate each value and save the new sort order number to DB
                        foreach ($valuesArray as $pov2po_row_id => $values_name) {
                            $where = "products_options_id = " . $pov2po_row_id;
                            $updateArray = array(
                                    'sort_order' => $sort
                            );
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', $where, $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            // products level
                            $where = "options_id = " . $pov2po_row_id . " and page_uid='" . $this->showCatalogFromPage . "'";
                            $updateArray = array();
                            $updateArray = array(
                                    'sort_order_option_name' => $sort
                            );
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', $where, $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            $sort++;
                        }
                        $content .= 'Attribute name sorting (natural) completed';
                        break;
                }
                break;
        }
        return $content;
    }
    // universal hook method for giving plugin information about update/insert action of the product
    function productsUpdateNotifierForPlugin($data, $product_id = 0) {
        // handle with care, the $data is just direct information injected from $this->post/$item
        // custom hook that can be controlled by third-party plugin
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['productsUpdateNotifierForPlugin'])) {
            $params = array(
                    'data' => &$data,
                    'product_id' => &$product_id
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['productsUpdateNotifierForPlugin'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
    }
    function compareDatabaseAlterProductToCategoryLinking() {
        $p2c_records = mslib_befe::getRecords('', 'tx_multishop_products_to_categories', '', array(), '', '', '');
        foreach ($p2c_records as $p2c_record) {
            tx_mslib_catalog::linkCategoriesTreeToProduct($p2c_record['products_id'], $p2c_record['categories_id']);
        }
    }
    function compareDatabaseFixProductToCategoryLinking() {
        $messages = array();
        $str = "show indexes from `tx_multishop_products_to_categories` ";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $unique_indexes = array();
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
            while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                $unique_indexes[] = $rs['Key_name'];
            }
            if (!in_array('p2c_unique_key', $unique_indexes)) {
                // add unique p2c key
                $unique_key = 'ALTER IGNORE TABLE `tx_multishop_products_to_categories` ADD UNIQUE `p2c_unique_key` (`products_id`, `categories_id`, `page_uid`, `node_id`, `is_deepest`, `crumbar_identifier`)';
                $res = $GLOBALS['TYPO3_DB']->sql_query($unique_key);
                $messages[] = $unique_key;
            }
        }
        //
        $p2c_records = mslib_befe::getRecords('', 'tx_multishop_products_to_categories', '', array(), '', '', '');
        if (is_array($p2c_records) && count($p2c_records)) {
            foreach ($p2c_records as $p2c_record) {
                //if (!strlen($p2c_record['crumbar_identifier'])) {
                tx_mslib_catalog::compareDatabaseRebuildProductsToCategoryTree($p2c_record['products_id'], $p2c_record['categories_id']);
                //}
            }
        }
        // p2c fixer routine code for redundant records
        $query_p2c = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_products_to_categories', // FROM ...
                '', // WHERE.
                'products_id,node_id', // GROUP BY...
                'products_to_categories_id asc', // ORDER BY...
                '' // LIMIT ...
        );
        $res_p2c = $GLOBALS['TYPO3_DB']->sql_query($query_p2c);
        $delete_counter = 0;
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_p2c)) {
            while ($row_p2c = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_p2c)) {
                $query_p2c_del = $GLOBALS['TYPO3_DB']->SELECTquery('products_to_categories_id', // SELECT ...
                        'tx_multishop_products_to_categories', // FROM ...
                        //'products_id = \''.$row_p2c['products_id'].'\' and categories_id = \''.$row_p2c['categories_id'].'\' and node_id=\''.$row_p2c['node_id'].'\' and crumbar_identifier=\''.$row_p2c['crumbar_identifier'].'\' and is_deepest=\''.$row_p2c['is_deepest'].'\'', // WHERE.
                        'products_id = \'' . $row_p2c['products_id'] . '\' and categories_id = \'' . $row_p2c['categories_id'] . '\' and node_id=\'' . $row_p2c['node_id'] . '\' and is_deepest=\'' . $row_p2c['is_deepest'] . '\'', // WHERE.
                        '', // GROUP BY...
                        'products_to_categories_id asc', // ORDER BY...
                        '' // LIMIT ...
                );
                $res_p2c_del = $GLOBALS['TYPO3_DB']->sql_query($query_p2c_del);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_p2c_del)) {
                    while ($row_del = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_p2c_del)) {
                        if ($row_p2c['products_to_categories_id'] != $row_del['products_to_categories_id']) {
                            $del_p2c_query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_categories', 'products_to_categories_id=\'' . $row_del['products_to_categories_id'] . '\'');
                            $res = $GLOBALS['TYPO3_DB']->sql_query($del_p2c_query);
                            $delete_counter++;
                        }
                    }
                }
            }
            if ($delete_counter) {
                $messages[] = 'Delete ' . $delete_counter . ' redundant record(s) from tx_multishop_products_to_categories';
            }
        }
        // p2c fixer routine code for redundant records eol
        $message = implode("\n\n", $messages);
        if (count($messages)) {
            return $message;
        }
        return '';
    }
    function compareDatabaseRebuildProductsToCategoryTree($pid, $deepest_cat_id, $dataArray = array()) {
        if (!is_numeric($pid)) {
            return false;
        }
        if (!is_numeric($deepest_cat_id)) {
            return false;
        }
        $level = 1;
        $cats = mslib_fe::globalCrumbarTree($deepest_cat_id);
        $cats = array_reverse($cats);
        //
        $crumbar_ident_string = '';
        $crumbar_ident_array = array();
        foreach ($cats as $item) {
            $crumbar_ident_array[] = $item['id'];
        }
        $crumbar_ident_string = implode(',', $crumbar_ident_array);
        $count_cats = count($cats);
        /*if ($count_cats>1) {
            // remove the deepest cat id record
            // disabled by bas
            //unset($cats[$count_cats-1]);
            //recount
            //$count_cats=count($cats);
        }*/
        if ($count_cats > 0) {
            foreach ($cats as $item) {
                if ($item['id']) {
                    $rec = tx_mslib_catalog::isProductToCategoryLinkingExist($pid, $item['id'], $crumbar_ident_string);
                    if (!$rec) {
                        $insertArray = array();
                        if (!is_array($dataArray) || (is_array($dataArray) && !count($dataArray))) {
                            $insertArray['categories_id'] = $deepest_cat_id;
                            $insertArray['products_id'] = $pid;
                            $insertArray['page_uid'] = $item['page_uid'];
                            $insertArray['sort_order'] = time();
                            $insertArray['related_to'] = 0;
                        } else {
                            foreach ($dataArray as $idx => $val) {
                                $insertArray[$idx] = $val;
                            }
                        }
                        $insertArray['node_id'] = $item['id'];
                        if ($item['id'] == $deepest_cat_id) {
                            $insertArray['is_deepest'] = 1;
                        } else {
                            $insertArray['is_deepest'] = 0;
                        }
                        $insertArray['crumbar_identifier'] = $crumbar_ident_string;
	                    $insertArray['crdate'] = time();
	                    $insertArray['last_updated_at'] = time();
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    } else {
                        $updateArray = array();
                        if (!empty($rec['crumbar_identifier'])) {
                            $tmp_cid = explode(',', $rec['crumbar_identifier']);
                            $last_index = count($tmp_cid) - 1;
                            if ($tmp_cid[$last_index] == $deepest_cat_id) {
                                if ($item['id'] == $deepest_cat_id) {
                                    $updateArray['is_deepest'] = 1;
                                } else {
                                    $updateArray['is_deepest'] = 0;
                                }
                                $updateArray['crumbar_identifier'] = $crumbar_ident_string;
	                            $updateArray['last_updated_at'] = time();
                                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_to_categories_id=\'' . $rec['products_to_categories_id'] . '\'', $updateArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            }
                        } else {
                            if ($item['id'] == $deepest_cat_id) {
                                $updateArray['is_deepest'] = 1;
                            } else {
                                $updateArray['is_deepest'] = 0;
                            }
                            $updateArray['crumbar_identifier'] = $crumbar_ident_string;
	                        $updateArray['last_updated_at'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_to_categories_id=\'' . $rec['products_to_categories_id'] . '\'', $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                    }
                    $level++;
                    /*if ($pid==508) {
                        echo "<pre>";
                        var_dump($query);
                        echo "<br/>";
                    }*/
                }
            }
            //return true;
        }
    }
    function moveProductToCrum($product_id, $crum_paths = array(), $force = 0) {
        if (!is_numeric($product_id)) {
            return false;
        }
        if (!is_array($crum_paths)) {
            return false;
        }
        $path_count = count($crum_paths);
        if (is_array($crum_paths) && !$path_count) {
            return false;
        }
        $parent_id = 0;
        $create_category = false;
        $cat_ids = array();
        foreach ($crum_paths as $idx => $crum_path) {
            $category = tx_mslib_catalog::getCategoryByName($crum_path, $parent_id, false);
            if ($category['categories_id']) {
                $cat_ids[] = $category['categories_id'];
                $parent_id = $category['categories_id'];
            } else {
                $create_category = true;
                if ($force) {
                    $data = array();
                    $data['parent_id'] = $parent_id;
                    $data['categories_name'] = $crum_path;
                    $data['language_id'] = $GLOBALS['TSFE']->config['config']['sys_language_uid'];
                    $new_category_id = tx_mslib_catalog::createCategory($data);
                    if ($new_category_id) {
                        $cat_ids[] = $new_category_id;
                        $parent_id = $new_category_id;
                    }
                }
            }
        }
        if ($create_category && !$force) {
            return false;
        }
        $count_cat_ids = count($cat_ids);
        if ($count_cat_ids == $path_count) {
            // remove the linking from previous cat
            $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_categories', 'products_id=' . $product_id);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            // insert new linking
            $deepest_cat = $cat_ids[$count_cat_ids - 1];
            $insertArray = array();
            $insertArray['products_id'] = $product_id;
            $insertArray['categories_id'] = $deepest_cat;
            $insertArray['sort_order'] = time();
            // create categories tree linking
            tx_mslib_catalog::linkCategoriesTreeToProduct($product_id, $deepest_cat, $insertArray);
            return true;
        }
    }
    function getCategoryByName($categories_name = '', $parent_id = '', $enabled_category_only = true) {
        $filter = array();
        $filter[] = 'c.page_uid=\'' . $this->showCatalogFromPage . '\'';
        if ($enabled_category_only) {
            $filter[] = 'c.status = \'1\'';
        }
        if ($parent_id != '' && is_numeric($parent_id)) {
            $filter[] = 'c.parent_id=\'' . addslashes($parent_id) . '\'';
        }
        $filter[] = 'cd.language_id=' . $this->sys_language_uid . '';
        if ($categories_name) {
            $filter[] = 'cd.categories_name=\'' . addslashes($categories_name) . '\'';
        }
        $filter[] = 'c.categories_id=cd.categories_id';
        $qry = $GLOBALS['TYPO3_DB']->SELECTquery('c.categories_id, cd.categories_name', // SELECT ...
                'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
                implode(' AND ', $filter), // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($qry);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            return $row;
        }
    }
    function createCategory($data) {
        // ADD CATEGORY
        $insertArray = array();
        $insertArray['date_added'] = time();
	    $insertArray['last_modified'] = time();
        $insertArray['sort_order'] = time();
        $insertArray['status'] = 1;
        $insertArray['page_uid'] = $this->showCatalogFromPage;
        $insertArray['parent_id'] = $data['parent_id'];
        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories', $insertArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        $id = $GLOBALS['TYPO3_DB']->sql_insert_id();
        if ($id) {
            $insertArray = array();
            $insertArray['categories_id'] = $id;
            $insertArray['categories_name'] = $data['categories_name'];
            $insertArray['language_id'] = $data['language_id'];
	        $insertArray['last_updated_at'] = time();
            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $insertArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            return $id;
        }
    }
    function buildCategoryTree(array $elements, $parentId = 0) {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = tx_mslib_catalog::buildCategoryTree($elements, $element['categories_id']);
                if ($children) {
                    $element['hasProducts']=0;
                    $element['children'] = $children;
                } else {
                    $element['hasProducts']=1;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
    public function getCategoryTree($loadAllCategory = false) {
        $select=array();
        $select[]='c.categories_image';
        $select[]='c.categories_id';
        $select[]='cd.categories_name';
        $select[]='c.status';
        $select[]='c.page_uid';
        $select[]='c.parent_id';
        $select[]='cd.content as description';
        $select[]='cd.content_footer as description_bottom';
        $select[]='c.last_modified';
        $select[]='c.date_added';
	    $select[] = 'c.hide_in_footer_menu';
        $from=array();
        $from[]='tx_multishop_categories c';
        $from[]='tx_multishop_categories_description cd';
        $filter = array();
		if (!$loadAllCategory) {
			$filter[] = 'c.page_uid=\'' . $this->showCatalogFromPage . '\'';
		}
        $filter[] = 'c.hide_in_menu=0';
        $filter[] = 'c.status=1';
        $filter[] = 'cd.language_id=\'' . $this->sys_language_uid . '\'';
        $filter[] = 'c.categories_id=cd.categories_id';
        $orderBy = 'c.sort_order asc';
        $groupBy='';
        $limit=999;
        // Hook
        $query_elements = array();
        $query_elements['select'] =& $select;
        $query_elements['from'] =& $from;
        $query_elements['filter'] =& $filter;
        $query_elements['orderBy'] =& $orderBy;
        $query_elements['groupBy'] =& $groupBy;
        $query_elements['limit'] =& $limit;
        // custom hook that can be controlled by third-party plugin
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['getCategoryTreeItemQryPreProc'])) {
            $conf = array(
                    'query_elements' => &$query_elements,
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['getCategoryTreeItemQryPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $conf, $this);
            }
        }
        $records=mslib_befe::getRecords('',implode(', ',$from),'',$filter,$groupBy,$orderBy,$limit,$select);
        if (is_array($records)) {
            $categories=array();
            foreach ($records as $record) {
                // custom hook that can be controlled by third-party plugin
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['getCategoryTreeItemIteratorPreProc'])) {
                    $conf = array(
                            'record' => &$record,
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['pi1/classes/class.tx_mslib_catalog.php']['getCategoryTreeItemIteratorPreProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $conf, $this);
                    }
                }
                $categories[]=$record;
            }
            if (count($records)) {
                $tree = tx_mslib_catalog::buildCategoryTree($categories);
                return $tree;
            }
        }
    }
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_catalog.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_catalog.php"]);
}
