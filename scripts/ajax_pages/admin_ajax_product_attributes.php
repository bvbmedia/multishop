<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$content = '';
switch ($this->get['tx_multishop_pi1']['admin_ajax_product_attributes']) {
    case 'get_attributes_options':
        $where = array();
        $where[] = "po.language_id = '" . $this->sys_language_uid . "'";
        $skip_db = false;
        if (isset($this->get['q']) && !empty($this->get['q'])) {
            if (strpos($this->get['q'], 'newopt||') !== false) {
                $skip_db = true;
                $this->get['q'] = str_replace('newopt||', '', $this->get['q']);
                $data = array();
                $data[] = array(
                        'id' => $this->get['q'],
                        'text' => $this->get['q']
                );
            } else if (!is_numeric($this->get['q'])) {
                $where[] = "po.products_options_name like '%" . addslashes($this->get['q']) . "%'";
            } else {
                $where[] = "po.products_options_id = '" . addslashes($this->get['q']) . "'";
            }
        } else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
            $where[] = "po.products_options_id = '" . addslashes($this->get['preselected_id']) . "'";
        }
        if (isset($this->get['tx_multishop_pi1']['categories_id']) && is_numeric($this->get['tx_multishop_pi1']['categories_id']) && $this->get['tx_multishop_pi1']['categories_id'] > 0) {
            $where[] = 'p2c.node_id=' . (int)$this->get['tx_multishop_pi1']['categories_id'];
            $where[] = 'c.categories_id=p2c.node_id';
            $where[] = 'a.products_id=p2c.products_id';
            $where[] = 'a.options_id=po.products_options_id';
            $where[] = 'po.language_id=0';
            $str = $GLOBALS ['TYPO3_DB']->SELECTquery('po.products_options_id, po.products_options_name', // SELECT ...
                    'tx_multishop_products_attributes a, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_products_options po left join tx_multishop_attributes_options_groups_to_products_options og2po on po.products_options_id=og2po.products_options_id left join tx_multishop_attributes_options_groups og on og.attributes_options_groups_id=og2po.attributes_options_groups_id and og.language_id=0', // FROM ...
                    implode(' and ', $where), // WHERE.
                    'a.options_id', // GROUP BY...
                    'po.sort_order', // ORDER BY...
                    '' // LIMIT ...
            );
        } else {
            $str = $GLOBALS ['TYPO3_DB']->SELECTquery('po.products_options_id, po.products_options_name', // SELECT ...
                    'tx_multishop_products_options po', // FROM ...
                    implode(' and ', $where), // WHERE.
                    '', // GROUP BY...
                    'po.sort_order', // ORDER BY...
                    '' // LIMIT ...
            );
        }
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $data = array();
        $num_rows = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);
        if ($num_rows) {
            if ((isset($this->get['q']) && empty($this->get['q'])) || (!isset($this->get['preselected_id']))) {
                //$data[] = array(
                //'id' => '0',
                //'text' => $this->pi_getLL('admin_label_select_feature_highlights')
                //);
            }
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                if (isset($this->get['exclude_id']) && $this->get['exclude_id'] > 0) {
                    if ($row['products_options_id'] != $this->get['exclude_id']) {
                        $data[] = array(
                                'id' => $row['products_options_id'],
                                'text' => $row['products_options_name']
                        );
                    }
                } else {
                    $data[] = array(
                            'id' => $row['products_options_id'],
                            'text' => $row['products_options_name']
                    );
                }
            }
        } else {
            if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
                $data[] = array(
                        'id' => $this->get['preselected_id'],
                        'text' => $this->get['preselected_id']
                );
            } else {
                $data[] = array(
                        'id' => $this->get['q'],
                        'text' => $this->get['q']
                );
            }
        }
        $content = json_encode($data);
        break;
    case 'get_attributes_values':
        $where = array();
        $orderby = array();
        $where[] = "optval.language_id = '" . $this->sys_language_uid . "'";
        $skip_db = false;
        if (isset($this->get['q']) && !empty($this->get['q'])) {
            if (strpos($this->get['q'], '||optid') !== false) {
                list($search_term, $tmp_optid) = explode('||', $this->get['q']);
                $search_term = trim($search_term);
                if (!empty($search_term)) {
                    $where_str = '';
                    if (isset($tmp_optid) && !empty($tmp_optid)) {
                        list(, $optid) = explode('=', $tmp_optid);
                        if (is_numeric($optid)) {
                            $this->get['option_id'] = $optid;
                            $where_str = "optval2opt.products_options_id = '" . $optid . "'";
                        }
                    }
                    if (!empty($where_str)) {
                        $where[] = "(optval.products_options_values_name like '" . addslashes($search_term) . "%' and " . $where_str . ")";
                    } else {
                        $where[] = "optval.products_options_values_name like '" . addslashes($search_term) . "%'";
                    }
                    $orderby[] = "INSTR('optval.products_options_values_name', '" . $search_term . "')";
                } else {
                    if (isset($tmp_optid) && !empty($tmp_optid)) {
                        list(, $optid) = explode('=', $tmp_optid);
                        if (is_numeric($optid)) {
                            $this->get['option_id'] = $optid;
                            $where[] = "(optval2opt.products_options_id = '" . $optid . "')";
                        }
                    }
                }
            } else {
                $where[] = "optval.products_options_values_name like '%" . addslashes($this->get['q']) . "%'";
                $orderby[] = "INSTR('optval.products_options_values_name', '" . $this->get['q'] . "')";
            }
        } else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
            if (is_numeric($this->get['preselected_id'])) {
                $where[] = "optval2opt.products_options_values_id = '" . $this->get['preselected_id'] . "'";
            } else {
                $where[] = "optval.products_options_values_name like '%" . addslashes($this->get['preselected_id']) . "%'";
                $orderby[] = "INSTR('optval.products_options_values_name', '" . $this->get['preselected_id'] . "')";
            }
        }
        if (isset($this->get['option_id']) && is_numeric($this->get['option_id'])) {
            $where[] = "(optval2opt.products_options_id = '" . $this->get['option_id'] . "')";
        }
        $orderby[] = "optval.products_options_values_name asc";
        $str = $GLOBALS ['TYPO3_DB']->SELECTquery('optval.*, optval2opt.products_options_id', // SELECT ...
                'tx_multishop_products_options_values as optval left join tx_multishop_products_options_values_to_products_options as optval2opt on optval2opt.products_options_values_id = optval.products_options_values_id', // FROM ...
                implode(' and ', $where), // WHERE
                'optval.products_options_values_id', // GROUP BY...
                implode(', ', $orderby), // ORDER BY...
                '' // LIMIT ...
        );
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $data = array();
        $num_rows = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);
        if ($num_rows) {
            $show_skip = false;
            if (isset($this->get['option_id'])) {
                $show_skip = true;
            }
            if (!isset($this->get['preselected_id'])) {
                $show_skip = true;
            }
            if ($show_skip) {
                $data[0] = array(
                        'id' => 0,
                        'text' => $this->pi_getLL('skip')
                );
            }
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                if (isset($this->get['option_id'])) {
                    if ($this->get['option_id'] == $row['products_options_id']) {
                        $data[] = array(
                                'id' => $row['products_options_values_id'],
                                'text' => $row['products_options_values_name']
                        );
                    }
                } else {
                    $data[] = array(
                            'id' => $row['products_options_values_id'],
                            'text' => $row['products_options_values_name']
                    );
                }
            }
        } else {
            if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
                $data[] = array(
                        'id' => $this->get['preselected_id'],
                        'text' => $this->get['preselected_id']
                );
            }
        }

        $content = json_encode($data);
        break;
    case 'delete_product_attributes':
        if ($this->ADMIN_USER) {
            $pid = $this->get['pid'];
            $paid = $this->post['paid'];
            $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_attributes', "products_id='" . $pid . "' and products_attributes_id='" . $paid . "' and page_uid='" . $this->showCatalogFromPage . "'");
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_product_attributes.php']['deleteProductAttributes'])) {
                $params = array();
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_ajax_product_attributes.php']['deleteProductAttributes'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
        }
        exit();
        break;
    case 'sort_product_attributes_option':
        if ($this->ADMIN_USER) {
            $pid = $this->get['pid'];
            $no = 1;
            foreach ($this->post['products_attributes_item'] as $optid) {
                if (is_numeric($optid)) {
                    $where = "options_id = " . $optid . " and products_id=" . $pid . " and page_uid=" . $this->showCatalogFromPage;
                    $updateArray = array(
                            'sort_order_option_name' => $no
                    );
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', $where, $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    $no++;
                }
            }
        }
        exit();
        break;
    case 'sort_product_attributes_value':
        if ($this->ADMIN_USER) {
            $pid = $this->get['pid'];
            $no = 1;
            foreach ($this->post['item_product_attribute'] as $paid) {
                if (is_numeric($paid)) {
                    $where = "products_attributes_id = " . $paid . " and products_id=" . $pid . " and page_uid=" . $this->showCatalogFromPage;
                    $updateArray = array(
                            'sort_order_option_value' => $no
                    );
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', $where, $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    $no++;
                }
            }
        }
        exit();
        break;
}
echo $content;
exit();
