<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
set_time_limit(7200);
ignore_user_abort(true);
$server_host_suffix = mslib_fe::rewritenamein($this->HTTP_HOST);
$sitemap_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_' . $server_host_suffix . '.txt';
$sitemap_file_web_path = 'uploads/tx_multishop/sitemap_' . $server_host_suffix . '.txt';
$sitemap_xml_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_' . $server_host_suffix . '.xml';
$sitemap_xml_file_web_path = 'uploads/tx_multishop/sitemap_' . $server_host_suffix . '.xml';
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorPreProc'])) {
    $params = array(
            'sitemap_file' => &$sitemap_file,
            'sitemap_file_web_path' => &$sitemap_file_web_path,
            'sitemap_xml_file' => &$sitemap_xml_file,
            'sitemap_xml_file_web_path' => &$sitemap_xml_file_web_path,
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorPreProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// hook eof
$prefix_domain = $this->FULL_HTTP_URL;
$content = '';
//
$log_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_tmp_' . $server_host_suffix . '.txt';
// file counter
$logs_file_reg = 1;
// links line counter
$logs_lines_reg = 0;
$max_lines_per_file = 50000; // google sitemap max lines per file
//
$log_file_reg_cache = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/log_file_reg_cache_' . $server_host_suffix;
$previous_log_file_reg_cache = file_get_contents($log_file_reg_cache);
if ($previous_log_file_reg_cache > 1) {
    $sitemap_file_path_parts = pathinfo($sitemap_file);
    $sitemap_file_fn = $sitemap_file_path_parts['filename'];
    $sitemap_file_ext = $sitemap_file_path_parts['extension'];
    for ($lfrc = 1; $lfrc <= $previous_log_file_reg_cache; $lfrc++) {
        $suffix = '_' . $server_host_suffix;
        if ($lfrc > 1) {
            $suffix = '_' . $server_host_suffix . '-' . $lfrc;
        }
        $log_file_fn = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_tmp' . $suffix . '.txt';
        $sitemap_file_iterate = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $sitemap_file_fn . $suffix . '.' . $sitemap_file_ext;
        // clean it before write
        @unlink($sitemap_file_iterate);
        @unlink($log_file_fn);
    }
} else {
    @unlink($log_file);
}
// XML
$log_xml_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_xml_tmp_' . $server_host_suffix . '.xml';
// file counter
$logs_xml_file_reg = 1;
// links line counter
$logs_xml_lines_reg = 0;
$prefix_domain = $this->FULL_HTTP_URL;
$log_xml_file_reg_cache = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/log_xml_file_reg_cache_' . $server_host_suffix;
$previous_xml_log_file_reg_cache = file_get_contents($log_xml_file_reg_cache);
if ($previous_xml_log_file_reg_cache > 1) {
    $sitemap_xml_file_path_parts = pathinfo($sitemap_xml_file);
    $sitemap_xml_file_fn = $sitemap_xml_file_path_parts['filename'];
    $sitemap_xml_file_ext = $sitemap_xml_file_path_parts['extension'];
    for ($lfrc = 1; $lfrc <= $previous_xml_log_file_reg_cache; $lfrc++) {
        $suffix = '_' . $server_host_suffix;
        if ($lfrc > 1) {
            $suffix = '_' . $server_host_suffix . '-' . $lfrc;
        }
        $log_xml_file_fn = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_xml_tmp' . $suffix . '.xml';
        $sitemap_xml_file_iterate = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $sitemap_xml_file_fn . $suffix . '.' . $sitemap_xml_file_ext;
        // clean it before write
        @unlink($sitemap_xml_file_iterate);
        @unlink($log_xml_file_fn);
    }
} else {
    @unlink($log_xml_file);
}
$tmpContent = '';
$tmpXMLContentHeader = array();
$tmpXMLContentHeader[] = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
$tmpXMLContentHeader[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
file_put_contents($log_xml_file, $tmpContent, FILE_APPEND | LOCK_EX);
$link = $prefix_domain . mslib_fe::typolink($this->shop_pid);
// TXT
$tmpContent = $link . "\n";
file_put_contents($log_file, $tmpContent, FILE_APPEND | LOCK_EX);
$logs_lines_reg++;
// XML
$tmpXMLContentHeader[] = '<url>' . "\n";
$tmpXMLContentHeader[] = "\t" . '<loc><![CDATA[' . $link . ']]></loc>' . "\n";
$tmpXMLContentHeader[] = "\t" . '<lastmod>' . date('c') . '</lastmod>' . "\n";
$tmpXMLContentHeader[] = "\t" . '<changefreq>daily</changefreq>' . "\n";
$tmpXMLContentHeader[] = "\t" . '<priority>0.5</priority>' . "\n";
$tmpXMLContentHeader[] = '</url>' . "\n";
//file_put_contents($log_xml_file, $tmpContent, FILE_APPEND | LOCK_EX);
$tmpContent = '';
if (!$this->get['skip_categories']) {
    $qry = $GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id=cd.categories_id and c.status=1 and c.page_uid='" . $this->showCatalogFromPage . "' and cd.language_id=" . $this->sys_language_uid);
    while (($categories = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        if (trim($categories['categories_external_url']) != '') {
            // Skip external URLs
            continue;
        }
        $level = 0;
        $cats = mslib_fe::Crumbar($categories['categories_id']);
        $cats = array_reverse($cats);
        $where = '';
        if (count($cats) > 0) {
            foreach ($cats as $item) {
                $where .= "categories_id[" . $level . "]=" . $item['id'] . "&";
                $level++;
            }
            $where = substr($where, 0, (strlen($where) - 1));
            $where .= '&';
        }
        $link = $prefix_domain . mslib_fe::typolink($this->conf['products_listing_page_pid'], '' . $where . '&tx_multishop_pi1[page_section]=products_listing');
        if (trim($categories['categories_external_url']) != '') {
            $link = $categories['categories_external_url'];
        }
        // hook
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorCategoryUrlsPreProc'])) {
            $params = array(
                    'link' => &$link
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorCategoryUrlsPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        // hook eof
        // TXT
        if ($logs_lines_reg == $max_lines_per_file) {
            $logs_file_reg++;
            $log_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_tmp_' . $server_host_suffix . '-' . $logs_file_reg . '.txt';
            $logs_lines_reg = 0;
        }
        $tmpContent = $link . "\n";
        file_put_contents($log_file, $tmpContent, FILE_APPEND | LOCK_EX);
        $logs_lines_reg++;
        // XML
        $tmpContent = '<url>' . "\n";
        $tmpContent .= "\t" . '<loc><![CDATA[' . $link . ']]></loc>' . "\n";
        if ($categories['last_modified']) {
            $tmpContent .= "\t" . '<lastmod>' . ($categories['last_modified'] > 0 ? date('c', $categories['last_modified']) : '') . '</lastmod>' . "\n";
        }
        $tmpContent .= "\t" . '<changefreq>daily</changefreq>' . "\n";
        $tmpContent .= "\t" . '<priority>0.5</priority>' . "\n";
        $tmpContent .= '</url>' . "\n";
        if ($logs_xml_lines_reg == $max_lines_per_file) {
            $logs_xml_file_reg++;
            $log_xml_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_xml_tmp_' . $server_host_suffix . '-' . $logs_xml_file_reg . '.xml';
            $logs_xml_lines_reg = 0;
        }
        $logs_xml_lines_reg++;
        file_put_contents($log_xml_file, $tmpContent, FILE_APPEND | LOCK_EX);
    }
}
// lets create the products sitemap
if (!$this->get['skip_products']) {
    $filterProducts = array();
    $filterProducts[] = 'products_status=1';
    $filterProducts[] = 'page_uid=' . $this->showCatalogFromPage;
    // hook
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorProductsQueryFilter'])) {
        $params = array(
                'filterProducts' => &$filterProducts
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorProductsQueryFilter'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    // hook eof
    $qry = $GLOBALS['TYPO3_DB']->sql_query("SELECT products_id from tx_multishop_products where " . implode(" and ", $filterProducts));
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $product = mslib_fe::getProduct($row['products_id']);
        $product_paths = mslib_befe::getRecords($row['products_id'], 'tx_multishop_products_to_categories p2c, tx_multishop_categories c', 'p2c.products_id', array('p2c.is_deepest=1 and p2c.categories_id=c.categories_id and c.status=1'));
        if (is_array($product_paths) && count($product_paths)) {
            foreach ($product_paths as $product_path) {
                $where = '';
                if ($product_path['categories_id']) {
                    // get all cats to generate multilevel fake url
                    $level = 0;
                    $cats = mslib_fe::Crumbar($product_path['categories_id']);
                    $cats = array_reverse($cats);
                    if (count($cats) > 0) {
                        foreach ($cats as $cat) {
                            $where .= "categories_id[" . $level . "]=" . $cat['id'] . "&";
                            $level++;
                        }
                        $where = substr($where, 0, (strlen($where) - 1));
                        $where .= '&';
                    }
                    // get all cats to generate multilevel fake url eof
                }
                $link = '';
                if (!empty($where)) {
                    $link = $prefix_domain . mslib_fe::typolink($this->conf['products_detail_page_pid'], '&' . $where . '&products_id=' . $product['products_id'] . '&tx_multishop_pi1[page_section]=products_detail');
                    // TXT
                    if ($logs_lines_reg == $max_lines_per_file) {
                        $logs_file_reg++;
                        $log_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_tmp_' . $server_host_suffix . '-' . $logs_file_reg . '.txt';
                        $logs_lines_reg = 0;
                    }
                    $tmpContent = $link . "\n";
                    file_put_contents($log_file, $tmpContent, FILE_APPEND | LOCK_EX);
                    $logs_lines_reg++;
                    // XML
                    $tmpContent = '<url>' . "\n";
                    $tmpContent .= "\t" . '<loc><![CDATA[' . $link . ']]></loc>' . "\n";
                    if ($product['products_last_modified']) {
                        $tmpContent .= "\t" . '<lastmod>' . ($product['products_last_modified'] > 0 ? date('c', $product['products_last_modified']) : '') . '</lastmod>' . "\n";
                    }
                    $tmpContent .= "\t" . '<changefreq>daily</changefreq>' . "\n";
                    $tmpContent .= "\t" . '<priority>0.5</priority>' . "\n";
                    $tmpContent .= '</url>' . "\n";
                    if ($logs_xml_lines_reg == $max_lines_per_file) {
                        $logs_xml_file_reg++;
                        $log_xml_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_xml_tmp_' . $server_host_suffix . '-' . $logs_xml_file_reg . '.xml';
                        $logs_xml_lines_reg = 0;
                    }
                    $logs_xml_lines_reg++;
                    file_put_contents($log_xml_file, $tmpContent, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}
if (!$this->get['skip_manufacturers']) {
    // MANUFACTURERS
    $qry = $GLOBALS['TYPO3_DB']->sql_query("SELECT manufacturers_id from tx_multishop_manufacturers m where m.status=1");
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $link = $prefix_domain . mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id=' . $row['manufacturers_id']);
        if ($link) {
            if ($logs_lines_reg == $max_lines_per_file) {
                $logs_file_reg++;
                $log_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_tmp_' . $server_host_suffix . '-' . $logs_file_reg . '.txt';
                $logs_lines_reg = 0;
            }
            // TXT
            $tmpContent = $link . "\n";
            file_put_contents($log_file, $tmpContent, FILE_APPEND | LOCK_EX);
            $logs_lines_reg++;
            // XML
            $tmpContent = '<url>' . "\n";
            $tmpContent .= "\t" . '<loc><![CDATA[' . $link . ']]></loc>' . "\n";
            if ($row['last_modified']) {
                $tmpContent .= "\t" . '<lastmod>' . ($row['last_modified'] > 0 ? date('c', $row['last_modified']) : '') . '</lastmod>' . "\n";
            }
            $tmpContent .= "\t" . '<changefreq>daily</changefreq>' . "\n";
            $tmpContent .= "\t" . '<priority>0.5</priority>' . "\n";
            $tmpContent .= '</url>' . "\n";
            if ($logs_xml_lines_reg == $max_lines_per_file) {
                $logs_xml_file_reg++;
                $log_xml_file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/sitemap_xml_tmp_' . $server_host_suffix . '-' . $logs_xml_file_reg . '.xml';
                $logs_xml_lines_reg = 0;
            }
            $logs_xml_lines_reg++;
            file_put_contents($log_xml_file, $tmpContent, FILE_APPEND | LOCK_EX);
        }
    }
}
// XML
$tmpXMLContentFooter = array();
$tmpXMLContentFooter[] = '</urlset>';
//file_put_contents($log_xml_file, $tmpContent, FILE_APPEND | LOCK_EX);
$tmpContent = '';
if ($logs_file_reg > 1) {
    $sitemap_file_web_path_list = array();
    $log_file_fn = 'sitemap_tmp';
    $sitemap_file_path_parts = pathinfo($sitemap_file);
    $sitemap_file_fn = $sitemap_file_path_parts['filename'];
    $sitemap_file_ext = $sitemap_file_path_parts['extension'];
    for ($lfr = 1; $lfr <= $logs_file_reg; $lfr++) {
        $suffix = '_' . $server_host_suffix;
        if ($lfr > 1) {
            $suffix = '_' . $server_host_suffix . '-' . $lfr;
        }
        $log_file_iterate = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $log_file_fn . $suffix . '.txt';
        $sitemap_file_iterate = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $sitemap_file_fn . $suffix . '.' . $sitemap_file_ext;
        $sitemap_file_web_path_list[] = 'uploads/tx_multishop/' . $sitemap_file_fn . $suffix . '.' . $sitemap_file_ext;
        // clean it before write
        @unlink($sitemap_file_iterate);
        // write content
        @copy($log_file_iterate, $sitemap_file_iterate);
    }
} else {
    @unlink($sitemap_file);
    @copy($log_file, $sitemap_file);
}
if ($logs_xml_file_reg > 1) {
    $sitemap_xml_file_web_path_list = array();
    $log_xml_file_fn = 'sitemap_xml_tmp';
    $sitemap_xml_file_path_parts = pathinfo($sitemap_xml_file);
    $sitemap_xml_file_fn = $sitemap_xml_file_path_parts['filename'];
    $sitemap_xml_file_ext = $sitemap_xml_file_path_parts['extension'];
    for ($lfr = 1; $lfr <= $logs_xml_file_reg; $lfr++) {
        $suffix = '_' . $server_host_suffix;
        if ($lfr > 1) {
            $suffix = '_' . $server_host_suffix . '-' . $lfr;
        }
        $log_xml_file_iterate = file_get_contents($this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $log_xml_file_fn . $suffix . '.xml');
        $sitemap_xml_file_iterate = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/' . $sitemap_xml_file_fn . $suffix . '.' . $sitemap_xml_file_ext;
        $sitemap_xml_file_web_path_list[] = 'uploads/tx_multishop/' . $sitemap_xml_file_fn . $suffix . '.' . $sitemap_xml_file_ext;
        // clean it before write
        @unlink($sitemap_xml_file_iterate);
        // append & prepend XML Header and Footer
        $XMLContent = implode('', $tmpXMLContentHeader);
        $XMLContent .= $log_xml_file_iterate;
        $XMLContent .= implode('', $tmpXMLContentFooter);
        // write content
        file_put_contents($sitemap_xml_file_iterate, $XMLContent, LOCK_EX);
    }
} else {
    @unlink($sitemap_xml_file);
    $log_xml_file_iterate = file_get_contents($log_xml_file);
    // append & prepend XML Header and Footer
    $XMLContent = implode('', $tmpXMLContentHeader);
    $XMLContent .= $log_xml_file_iterate;
    $XMLContent .= implode('', $tmpXMLContentFooter);
    // write content
    file_put_contents($sitemap_xml_file, $XMLContent, LOCK_EX);
}
// txt cache file
@unlink($log_file_reg_cache);
file_put_contents($log_file_reg_cache, $logs_file_reg, FILE_APPEND | LOCK_EX);
// xml cache file
@unlink($log_xml_file_reg_cache);
file_put_contents($log_xml_file_reg_cache, $logs_xml_file_reg, FILE_APPEND | LOCK_EX);
$content .= '<div class="main-heading"><h1>' . $this->pi_getLL('admin_label_sitemap_creator') . '</h1></div>';
$content .= '<p>' . $this->pi_getLL('admin_label_your_sitemap_has_been_created') . '</p>' . $this->pi_getLL('admin_label_you_can_download_it_here') . ':<br/>';
if (is_array($sitemap_file_web_path_list) && count($sitemap_file_web_path_list) > 0) {
    foreach ($sitemap_file_web_path_list as $web_path) {
        $content .= '
TXT: <a href="' . $web_path . '" target="_blank">' . $web_path . '</a><br/>
';
    }
} else {
    $content .= '
TXT: <a href="' . $sitemap_file_web_path . '" target="_blank">' . $sitemap_file_web_path . '</a><br/>
';
}
if (is_array($sitemap_xml_file_web_path_list) && count($sitemap_xml_file_web_path_list) > 0) {
    foreach ($sitemap_xml_file_web_path_list as $web_xml_path) {
        $content .= '
XML: <a href="' . $web_xml_path . '" target="_blank">' . $web_xml_path . '</a><br/>
';
    }
} else {
    $content .= '
XML: <a href="' . $sitemap_xml_file_web_path . '" target="_blank">' . $sitemap_xml_file_web_path . '</a><br/>
';
}
