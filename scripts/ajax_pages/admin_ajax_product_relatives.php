<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$pid = (int)$this->post['pid'];
$json_data = array();
if ($this->post['req'] == 'init') {
    // pre-defined product relation
    $main_relations_data = array();
    $sub_relations_data = array();
    $relations_data = array();
    $where_relatives = '((products_id = ' . $pid . ') or (relative_product_id =  ' . $pid . ')) and relation_types=\'cross-sell\'';
    $query = $GLOBALS['TYPO3_DB']->SELECTquery('products_id, relative_product_id', // SELECT ...
            'tx_multishop_products_to_relative_products', // FROM ...
            $where_relatives, // WHERE.
            '', // GROUP BY...
            '', // ORDER BY...
            '' // LIMIT ...
    );
    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        if ($rows['relative_product_id'] != $pid && $rows['relative_product_id'] > 0) {
            $sub_relations_data[] = $rows['relative_product_id'];
            $relations_data[] = $rows['relative_product_id'];
        } else {
            if ($rows['products_id'] != $pid && $rows['products_id'] > 0) {
                $main_relations_data[] = $rows['products_id'];
                $relations_data[] = $rows['products_id'];
            }
        }
    }
    // pre-defined product relation
    if (is_array($relations_data) and count($relations_data)) {
        $where_A ='';
        $filter=array();
        if (isset($this->ms['MODULES']['CROSS_SHOP_PRODUCT_RELATION'])) {
            if ($this->ms['MODULES']['CROSS_SHOP_PRODUCT_RELATION']=='0') {
                $filter[] = "p.page_uid='" . $this->showCatalogFromPage . "'";
            }
        } else {
            $filter[] = "p.page_uid='" . $this->showCatalogFromPage . "'";
        }
        $filter[] = "p.products_id IN (" . implode(', ', $relations_data) . ")";
        $filter[] = "pd.products_id=p.products_id";
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('multishop_product_variations')) {
            $filter[] = 'p.is_hidden=0';
        }
        $filter[]='pd.language_id=' . $this->sys_language_uid;
        $filter[]='cd.language_id=' . $this->sys_language_uid;
        //die($where_A);
        $query = '
		SELECT pd.products_id,
			   pd.products_name,
			   p2c.categories_id,
			   cd.categories_name,
			   p.page_uid
		FROM tx_multishop_products p,
			 tx_multishop_products_description pd
		INNER JOIN tx_multishop_products_to_categories p2c ON pd.products_id = p2c.products_id
		INNER JOIN tx_multishop_categories_description cd ON p2c.categories_id = cd.categories_id
		WHERE ' . implode(' AND ', $filter) . '
		GROUP BY cd.categories_id ASC ORDER BY cd.categories_name';
        //	error_log($query);
        $pid_regs = array();
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                if ($row['categories_name']) {
                    $filter=array();
                    if (isset($this->ms['MODULES']['CROSS_SHOP_PRODUCT_RELATION'])) {
                        if ($this->ms['MODULES']['CROSS_SHOP_PRODUCT_RELATION']=='0') {
                            $filter[] = "p.page_uid='" . $this->showCatalogFromPage . "'";
                        }
                    } else {
                        $filter[] = "p.page_uid='" . $this->showCatalogFromPage . "'";
                    }
                    $filter[] = "p.products_id IN (" . implode(', ', $relations_data) . ")";
                    $filter[] = "pd.products_id=p.products_id";
                    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('multishop_product_variations')) {
                        $filter[] = 'p.is_hidden=0';
                    }
                    $filter[]='p2c.categories_id = ' . $row['categories_id'];
                    $filter[]='p2c.is_deepest=1';
                    $filter[]='pd.language_id=' . $this->sys_language_uid;
                    $filter[]='cd.language_id=' . $this->sys_language_uid;
                    $query2 = '
					SELECT pd.products_id,
						   pd.products_name,
						   p2c.categories_id,
						   cd.categories_name,
						   p.products_status,
						   p.page_uid,
						   p.products_model
					FROM tx_multishop_products p,
						 tx_multishop_products_description pd
					INNER JOIN tx_multishop_products_to_categories p2c ON pd.products_id = p2c.products_id
					INNER JOIN tx_multishop_categories_description cd ON p2c.categories_id = cd.categories_id
					WHERE ' . implode(' AND ', $filter) . '
					group by p.products_id ORDER BY pd.products_name ASC';
                    var_dump($query2);
                    $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
                    $cheking_check = 0;
                    if ($GLOBALS['TYPO3_DB']->sql_num_rows($res2) > 0) {
                        $level = 0;

                        $default_path = $row['categories_id'];
                        $product_path = mslib_befe::getRecord($row['products_id'], 'tx_multishop_products_to_categories', 'products_id', array('is_deepest=1 and default_path=1'));
                        if (is_array($product_path) && count($product_path)) {
                            $default_path = $product_path['node_id'];
                        }
                        if ($default_path) {
                            // get all cats to generate multilevel fake url
                            $level=0;
                            if ($row['page_uid']!=$this->shop_pid) {
                                $crum=mslib_fe::Crumbar($default_path, '', array(), $row['page_uid']);
                            } else {
                                $crum=mslib_fe::Crumbar($default_path);
                            }
                            $crum=array_reverse($crum);
                        }
                        $where = '';
                        if ($crum) {
                            $cats = array();
                            foreach ($crum as $item) {
                                $where .= "categories_id[" . $level . "]=" . $item['id'] . "&";
                                $cats[] = $item['name'];
                                $level++;
                            }
                        }
                        if (!empty($where)) {
                            $where = substr($where, 0, (strlen($where) - 1));
                            $where .= '&';
                        }
                        $json_data['related_product'][$row['categories_id']] = array();
                        $json_data['related_product'][$row['categories_id']]['categories_name'] = implode(' / ', $cats);
                        $product_counter = 0;
                        while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) != false) {
                            // custom hook that can be controlled by third-party plugin
                            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_product_relatives.php']['adminAjaxProductRelativesIteratorPreProc'])) {
                                $params = array(
                                        'row2' => &$row2
                                );
                                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_product_relatives.php']['adminAjaxProductRelativesIteratorPreProc'] as $funcRef) {
                                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                }
                            }
                            // custom hook that can be controlled by third-party plugin eof
                            $have_main_relation_type=false;
                            $have_sub_relation_type=false;
                            $have_both_relation_type=false;
                            if (in_array($row2['products_id'], $main_relations_data)) {
                                $have_main_relation_type=true;
                                $relation_type='main';
                            }
                            if (in_array($row2['products_id'], $sub_relations_data)) {
                                $have_sub_relation_type=true;
                                $relation_type='sub';
                            }
                            if ($have_main_relation_type && $have_sub_relation_type) {
                                $have_main_relation_type=false;
                                $have_sub_relation_type=false;
                                $have_both_relation_type=true;
                                $relation_type='both';
                            }
                            $relation_type_list=array();
                            $relation_type_list[]='main';
                            $relation_type_list[]='sub';
                            $relation_type_list[]='both';
                            foreach ($relation_type_list as $rtype) {
                                if (!is_array($pid_regs[$rtype])) {
                                    $pid_regs[$rtype]=array();
                                }
                                if (!in_array($row2['products_id'], $pid_regs[$rtype])) {
                                    if ($rtype=='main' && !$have_main_relation_type) {
                                        continue;
                                    }
                                    if ($rtype=='sub' && !$have_sub_relation_type) {
                                        continue;
                                    }
                                    if ($rtype=='both' && !$have_both_relation_type) {
                                        continue;
                                    }
                                    $json_data['related_product'][$row['categories_id']][$rtype]['products'][$product_counter]['id'] = $row2['products_id'];
                                    if ($row2['products_model']) {
                                        $row2['products_name'] = $row2['products_model'] . ': ' . $row2['products_name'];
                                    }
                                    $json_data['related_product'][$row['categories_id']][$rtype]['products'][$product_counter]['name'] = $row2['products_name'];

                                    $json_data['related_product'][$row['categories_id']][$rtype]['products'][$product_counter]['name'] .= ' (ID: ' . $row2['products_id'] . ')' . (!$row2['products_status'] ? ' (' . $this->pi_getLL('disabled') . ')' : '');
                                    $json_data['related_product'][$row['categories_id']][$rtype]['products'][$product_counter]['checked'] = 0;

                                    $product_link = '#';
                                    if (!empty($where)) {
                                        if ($row['page_uid']!=$this->shop_pid) {
                                            $product_link = mslib_fe::typolink($row['page_uid'], '&' . $where . '&products_id=' . $row['products_id'] . '&tx_multishop_pi1[page_section]=products_detail');
                                        } else {
                                            $product_link = mslib_fe::typolink($this->conf['products_detail_page_pid'], '&' . $where . '&products_id=' . $row['products_id'] . '&tx_multishop_pi1[page_section]=products_detail');
                                        }
                                    }
                                    $json_data['related_product'][$row['categories_id']][$rtype]['products'][$product_counter]['link'] = $product_link;
                                    $json_data['related_product'][$row['categories_id']][$rtype]['products'][$product_counter]['relation_type'] = $rtype;
                                    $pid_regs[$rtype][] = $row2['products_id'];
                                    $product_counter++;
                                }
                            }
                        }
                        if (!count($json_data['related_product'][$row['categories_id']][$relation_type]['products'])) {
                            unset($json_data['related_product'][$row['categories_id']]);
                        }
                    } else {
                        $json_data['related_product'] = 0;
                    }
                }
                if (isset($json_data['related_product'][$row['categories_id']])) {
                    $json_data['related_product'][$row['categories_id']]['have_main_relation_type']=$have_main_relation_type;
                    $json_data['related_product'][$row['categories_id']]['have_sub_relation_type']=$have_sub_relation_type;
                    $json_data['related_product'][$row['categories_id']]['have_both_relation_type']=$have_both_relation_type;
                }
            }
        }
    }
} else {
    switch ($this->post['req']) {
        case 'search':
            $relation_type='search';
            $filter = array();
            if (strlen($this->post['keypas']) > 1) {
                $subfilter=array();
                $subfilter[] = "pd.products_name LIKE '%" . addslashes(trim(mslib_befe::strtolower($this->post['keypas']))) . "%'";
                $subfilter[] = "p.products_model LIKE '%" . addslashes(trim(mslib_befe::strtolower($this->post['keypas']))) . "%'";
                $subfilter[] = "p.ean_code LIKE '%" . addslashes(trim(mslib_befe::strtolower($this->post['keypas']))) . "%'";
                $subfilter[] = "p.sku_code LIKE '%" . addslashes(trim(mslib_befe::strtolower($this->post['keypas']))) . "%'";
                $filter[]='('.implode(' OR ', $subfilter).')';
            }
            if (isset($this->ms['MODULES']['CROSS_SHOP_PRODUCT_RELATION'])) {
                if ($this->ms['MODULES']['CROSS_SHOP_PRODUCT_RELATION']=='0') {
                    $filter[] = "p.page_uid='" . $this->showCatalogFromPage . "'";
                }
            } else {
                $filter[] = "p.page_uid='" . $this->showCatalogFromPage . "'";
            }
            $filter[] = 'pd.products_id=p.products_id';
            if (is_array($relations_data) and count($relations_data)) {
                //$filter[] = 'pd.products_id NOT IN (' . implode(', ', $relations_data) . ')';
            }
            $subcat_query = '';
            if ($this->post['s_cid'] > 0) {
                $subcats = mslib_fe::get_subcategory_ids($this->post['s_cid'], $subcats);
                $subcat_queries[] = 'p2c.categories_id = ' . (int)$this->post['s_cid'];
                if (is_array($subcats)) {
                    foreach ($subcats as $subcat_id) {
                        $subcat_queries[] = 'p2c.categories_id = ' . $subcat_id;
                    }
                }
                $subcat_query = '(' . implode(' OR ', $subcat_queries) . ')';
                $filter[] = $subcat_query;
            }
            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('multishop_product_variations')) {
                $filter[] = 'p.is_hidden=0';
            }
            $filter[]='pd.language_id=' . $this->sys_language_uid . ' and cd.language_id=' . $this->sys_language_uid;
            //die($where);
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('p2c.categories_id,cd.categories_name', // SELECT ...
                    'tx_multishop_products p, tx_multishop_products_description pd INNER JOIN tx_multishop_products_to_categories p2c ON pd.products_id = p2c.products_id INNER JOIN tx_multishop_categories_description cd ON p2c.categories_id = cd.categories_id', // FROM ...
                    implode(" AND ", $filter), // WHERE...
                    'cd.categories_id', // GROUP BY...
                    'cd.categories_name ASC', // ORDER BY...
                    '' // LIMIT ...
            );
            //	error_log($query);
            //	error_log($query);
            $pid_regs = array();
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                    if ($row['categories_name']) {
                        $productFilter = $filter;
                        $productFilter[] = '(p2c.categories_id = ' . $row['categories_id'] . ' and p2c.is_deepest=1 and pd.products_id <> ' . (int)$this->post['pid'] . ')';
                        $query2 = $GLOBALS['TYPO3_DB']->SELECTquery('pd.products_id, pd.products_name, p2c.categories_id,cd.categories_name, p.products_status, p.products_model', // SELECT ...
                            'tx_multishop_products p, tx_multishop_products_description pd INNER JOIN tx_multishop_products_to_categories p2c ON pd.products_id = p2c.products_id INNER JOIN tx_multishop_categories_description cd ON p2c.categories_id = cd.categories_id', // FROM ...
                            implode(" AND ", $productFilter), // WHERE...
                            'p.products_id', // GROUP BY...
                            'pd.products_name ASC', // ORDER BY...
                            '' // LIMIT ...
                        );
                        //error_log($query2);
                        $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
                        $cheking_check = 0;
                        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res2) > 0) {
                            $level = 0;
                            $crum = mslib_fe::Crumbar($row['categories_id']);
                            $crum = array_reverse($crum);
                            $cats = array();
                            $where = '';
                            foreach ($crum as $item) {
                                $where .= "categories_id[" . $level . "]=" . $item['id'] . "&";
                                $cats[] = $item['name'];
                                $level++;
                            }
                            if (!empty($where)) {
                                $where = substr($where, 0, (strlen($where) - 1));
                                $where .= '&';
                            }
                            if (!isset($json_data['related_product'][$row['categories_id']]['categories_name'])) {
                                $json_data['related_product'][$row['categories_id']]['categories_name'] = implode(" / ", $cats);
                            }
                            if (!isset($json_data['related_product'][$row['categories_id']][$relation_type]['products'])) {
                                $json_data['related_product'][$row['categories_id']][$relation_type]['products'] = array();
                            }
                            $product_counter = 0;
                            while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) != false) {
                                // custom hook that can be controlled by third-party plugin
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_product_relatives.php']['adminAjaxProductRelativesIteratorPreProc'])) {
                                    $params = array(
                                            'row2' => &$row2
                                    );
                                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_product_relatives.php']['adminAjaxProductRelativesIteratorPreProc'] as $funcRef) {
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                    }
                                }
                                // custom hook that can be controlled by third-party plugin eof
                                //if (!in_array($row2['products_id'], $pid_regs)) {
                                $json_data['related_product'][$row['categories_id']][$relation_type]['products'][$product_counter]['id'] = $row2['products_id'];
                                if ($row2['products_model']) {
                                    $row2['products_name'] = $row2['products_model'] . ': ' . $row2['products_name'];
                                }
                                $json_data['related_product'][$row['categories_id']][$relation_type]['products'][$product_counter]['name'] = $row2['products_name'];
                                $json_data['related_product'][$row['categories_id']][$relation_type]['products'][$product_counter]['name'] .= ' (ID: ' . $row2['products_id'] . ')' . (!$row2['products_status'] ? ' (' . $this->pi_getLL('disabled') . ')' : '');
                                if (mslib_fe::isChecked($_REQUEST['pid'], $row2['products_id'])) {
                                    $json_data['related_product'][$row['categories_id']][$relation_type]['products'][$product_counter]['checked'] = 1;
                                } else {
                                    $json_data['related_product'][$row['categories_id']][$relation_type]['products'][$product_counter]['checked'] = 0;
                                }
                                $product_link = mslib_fe::typolink($this->conf['products_detail_page_pid'], $where . '&products_id=' . $row2['products_id'] . '&tx_multishop_pi1[page_section]=products_detail');
                                $json_data['related_product'][$row['categories_id']][$relation_type]['products'][$product_counter]['link'] = $product_link;
                                $json_data['related_product'][$row['categories_id']][$relation_type]['products'][$product_counter]['categories_name'] = $json_data['related_product'][$row['categories_id']]['categories_name'];
                                $pid_regs[] = $row2['products_id'];
                                $product_counter++;
                                //}
                            }
                            if (!count($json_data['related_product'][$row['categories_id']][$relation_type]['products'])) {
                                unset($json_data['related_product'][$row['categories_id']]);
                            }
                        } else {
                            if (!count($json_data['related_product'])) {
                                $json_data['related_product'] = 0;
                                $json_data['no_records_found'] = $this->pi_getLL('no_records_found');
                            }
                        }
                    }
                }
            } else {
                $json_data['related_product'] = 0;
                $json_data['no_records_found']=$this->pi_getLL('no_records_found');
            }
            break;
        case 'save':
            if (strpos($this->post['product_id'], '&') !== false || strpos($this->post['product_id'], '=') !== false) {
                $data_pids = array();
                $related_pid = explode("&", $this->post['product_id']);
                foreach ($related_pid as $multipid) {
                    list($var, $pid) = explode("=", $multipid);
                    $data_pids[] = $pid;
                }
                $main_product_id=$this->post['pid'];
                foreach ($data_pids as $data_pid) {
                    $sub_product_id=$data_pid;
                    if ($this->post['relation_type']=='main') {
                        $main_product_id=$data_pid;
                        $sub_product_id=$this->post['pid'];
                    }
                    $where_relatives = '((products_id = ' . $main_product_id . ' AND relative_product_id = ' . $sub_product_id . ') or (products_id = ' . $sub_product_id . ' AND relative_product_id = ' . $main_product_id . ')) and relation_types=\'cross-sell\'';
                    $query_checking = $GLOBALS['TYPO3_DB']->SELECTquery('count(*) as total', // SELECT ...
                            'tx_multishop_products_to_relative_products', // FROM ...
                            $where_relatives, // WHERE.
                            '', // GROUP BY...
                            '', // ORDER BY...
                            '' // LIMIT ...
                    );
                    $res_checking = $GLOBALS['TYPO3_DB']->sql_query($query_checking);
                    $row_check = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
                    //if empty on database => save products
                    if ($row_check['total'] == 0) {
                        //echo $row_check['jum'];
                        $updateArray = array();
                        $updateArray = array(
                                "products_id" => $main_product_id,
                                "relative_product_id" => $sub_product_id
                        );
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        if ($res) {
                            $json_data['saved'] = "OK";
                        }
                    }
                }
            } else {
                $main_product_id=$this->post['pid'];
                $sub_product_id=$this->post['product_id'];
                if ($this->post['relation_type']=='main') {
                    $main_product_id=$this->post['product_id'];
                    $sub_product_id=$this->post['pid'];
                }
                $where_relatives = '((products_id = ' . $main_product_id . ' AND relative_product_id = ' . $sub_product_id . ') or (products_id = ' . $sub_product_id . ' AND relative_product_id = ' . $main_product_id . ')) and relation_types=\'cross-sell\'';
                $query_checking = $GLOBALS['TYPO3_DB']->SELECTquery('count(*) as total', // SELECT ...
                        'tx_multishop_products_to_relative_products', // FROM ...
                        $where_relatives, // WHERE.
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $res_checking = $GLOBALS['TYPO3_DB']->sql_query($query_checking);
                $row_check = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
                //if empty on database => save products
                if ($row_check['total'] == 0) {
                    //echo $row_check['jum'];
                    $updateArray = array();
                    $updateArray = array(
                            "products_id" => $main_product_id,
                            "relative_product_id" => $sub_product_id
                    );
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    if ($res) {
                        $json_data['saved'] = "OK";
                    }
                }
            }
            break;
        case 'delete':
            $where_relatives = 'products_id = ' . $this->post['mpid'] . ' AND relative_product_id = ' . $this->post['spid'] . ' AND relation_types=\'cross-sell\'';
            $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_relative_products', $where_relatives);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($this->post['reltype']=='both') {
                $where_relatives = 'products_id = ' . $this->post['spid'] . ' AND relative_product_id = ' . $this->post['mpid'] . ' AND relation_types=\'cross-sell\'';
                $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_relative_products', $where_relatives);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
            if ($res) {
                $json_data['deleted'] = "OK";
            }
            break;
        case 'add_as_mpid':
        case 'add_as_spid':
            $main_product_id=$this->post['mpid'];
            $sub_product_id=$this->post['spid'];
            $where_relatives = '(products_id = ' . $main_product_id . ' AND relative_product_id = ' . $sub_product_id . ') and relation_types=\'cross-sell\'';
            $query_checking = $GLOBALS['TYPO3_DB']->SELECTquery('count(*) as total', // SELECT ...
                'tx_multishop_products_to_relative_products', // FROM ...
                $where_relatives, // WHERE.
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
            );
            $res_checking = $GLOBALS['TYPO3_DB']->sql_query($query_checking);
            $row_check = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
            //if empty on database => save products
            if ($row_check['total'] == 0) {
                $updateArray = array();
                $updateArray = array(
                    "products_id" => $main_product_id,
                    "relative_product_id" => $sub_product_id
                );
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                if ($res) {
                    $json_data['saved'] = "OK";
                }
            }
            break;
        case 'add_relation_as_both':
            $status=array();
            $relations_type=array();
            $relations_type[]='main';
            $relations_type[]='sub';
            foreach ($relations_type as $relation_type) {
                if ($relation_type=='main') {
                    $main_product_id = $this->post['mpid'];
                    $sub_product_id = $this->post['spid'];
                } else {
                    $main_product_id = $this->post['spid'];
                    $sub_product_id = $this->post['mpid'];
                }
                $where_relatives = '(products_id = ' . $main_product_id . ' AND relative_product_id = ' . $sub_product_id . ') and relation_types=\'cross-sell\'';
                $query_checking = $GLOBALS['TYPO3_DB']->SELECTquery('count(*) as total', // SELECT ...
                    'tx_multishop_products_to_relative_products', // FROM ...
                    $where_relatives, // WHERE.
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
                );
                $res_checking = $GLOBALS['TYPO3_DB']->sql_query($query_checking);
                $row_check = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
                //if empty on database => save products
                if ($row_check['total'] == 0) {
                    $updateArray = array();
                    $updateArray = array(
                            "products_id" => $main_product_id,
                            "relative_product_id" => $sub_product_id
                    );
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    if ($res) {
                        $status['both'] = "OK";
                    }
                }
            }
            $json_data['saved']=$status;
            break;
    }
}
header('Content-Type: application/json');
$data = json_encode($json_data);
echo $data;
exit();
?>