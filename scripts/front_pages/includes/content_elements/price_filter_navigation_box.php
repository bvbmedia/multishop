<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ms['MODULES']['PRICE_FILTER_BOX_STEPPINGS']) {
    $this->box_class = "multishop_price_filter_box";
    if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU']) {
        $this->ms['MODULES']['CACHE_FRONT_END'] = 0;
    }
    if ($this->ms['MODULES']['CACHE_FRONT_END']) {
        $options = array(
                'caching' => true,
                'cacheDir' => PATH_site . 'uploads/tx_multishop/tmp/cache/',
                'lifeTime' => $this->ms['MODULES']['CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU']
        );
        $Cache_Lite = new Cache_Lite($options);
        $string = 'price_filter_' . $this->server['REQUEST_URI'] . $this->server['QUERY_STRING'] . $this->cObj->data['uid'];
    }
    if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content = $Cache_Lite->get($string)) {
        $array = explode(";", $this->ms['MODULES']['PRICE_FILTER_BOX_STEPPINGS']);
        $array[] = $this->pi_getLL('show_all');
        $content .= '<ul>';
        if ($this->ms['MODULES']['PRICE_FILTER_WITHOUT_CATEGORY_QUERY_STRING']) {
            $where = '';
        } else {
            if (count($GLOBALS["TYPO3_CONF_VARS"]['tx_multishop_data']['user_crumbar']) > 0) {
                // get all cats to generate multilevel fake url
                $level = 0;
                $cats = $GLOBALS["TYPO3_CONF_VARS"]['tx_multishop_data']['user_crumbar'];
                if (is_array($cats) and count($cats)) {
                    $cats = array_reverse($cats);
                }
                $where = '';
                if (count($cats) > 0) {
                    foreach ($cats as $cat) {
                        $where .= "categories_id[" . $level . "]=" . $cat['id'] . "&";
                        $level++;
                    }
                    $where = substr($where, 0, (strlen($where) - 1));
                    $where .= '&';
                }
                // get all cats to generate multilevel fake url eof
            } else {
                $where = '';
            }
        }
        $this->get['skeyword'] = $_REQUEST['skeyword'];
        $this->get['skeyword'] = trim($this->get['skeyword']);
        $this->get['skeyword'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['skeyword'], $GLOBALS['TSFE']->metaCharset);
        $this->get['skeyword'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['skeyword'], true);
        $this->get['skeyword'] = mslib_fe::RemoveXSS($this->get['skeyword']);
        if ($this->get['skeyword']) {
            $where .= '&skeyword=' . urlencode($this->get['skeyword']) . '&';
        }
        $steps = count($array);
        $stepCounter = 0;
        foreach ($array as $item) {
            $stepCounter++;
            if ($item != $this->pi_getLL('show_all')) {
                $price_range = str_replace('.', ',', $item);
                $label = $price_range . ' euro';
            } else {
                $label = $this->pi_getLL('show_all');
            }
            //hook to let other plugins further manipulate the replacers
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/content_elements/price_filter_navigation_box.php']['priceFilterSteppingIteratorPreProc'])) {
                $params = array(
                        'item' => &$item,
                        'label' => &$label,
                        'steps' => $steps,
                        'stepCounter' => $stepCounter,
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/content_elements/price_filter_navigation_box.php']['priceFilterSteppingIteratorPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            $content .= '<li' . ($this->get['price_filter'] == $item ? ' class="active"' : '') . '><a href="' . mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=products_search&' . $where . 'price_filter=' . urlencode($item)) . '" class="ajax_link">' . $label . '</a></li>' . "\n";
        }
        $content .= '
		</ul>
		';
        if ($this->ms['MODULES']['CACHE_FRONT_END']) {
            $Cache_Lite->save($content);
        }
    }
}
