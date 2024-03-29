<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (!$this->cObj->data['header']) {
    $this->default_header = 1;
    $this->cObj->data['header'] = $this->pi_getLL('catalog');
}
$this->box_class = "multishop_catalog_box";
if (is_numeric($this->conf['parentID'])) {
    $this->parentID = $this->conf['parentID'];
} else {
    $this->parentID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'parentID', 's_listing');
}
if (is_numeric($this->conf['showIfsub'])) {
    $this->showIfsub = $this->conf['showIfsub'];
} else {
    $this->showIfsub = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showIfsub', 's_listing');
}
if (is_numeric($this->conf['maxDEPTH'])) {
    $this->maxDEPTH = $this->conf['maxDEPTH'];
} else {
    $this->maxDEPTH = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxDEPTH', 's_listing');
}
if (is_numeric($this->conf['maxDEPTHifSubs'])) {
    $this->maxDEPTHifSubs = $this->conf['maxDEPTHifSubs'];
} else {
    $this->maxDEPTHifSubs = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxDEPTHifSubs', 's_listing');
}
if (!is_numeric($this->maxDEPTHifSubs)) {
    $this->maxDEPTHifSubs = $this->maxDEPTH;
}
if (is_numeric($this->conf['hideHeader'])) {
    $this->hideHeader = $this->conf['hideHeader'];
} else {
    $this->hideHeader = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'hideHeader', 'sDEFAULT');
}
$this->expAll = 1;
if (is_numeric($this->conf['expAll'])) {
    $this->expAll = $this->conf['expAll'];
} else {
    // later
    //$this->expAll=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'expAll', 's_listing');
}
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
    if ($GLOBALS['TSFE']->fe_user->user['uid']) {
        $prefix = '1';
    } else {
        $prefix = '';
    }
    $string = $prefix . '_' . serialize($GLOBALS["TYPO3_CONF_VARS"]['tx_multishop_data']['user_crumbar']) . $this->cObj->data['uid'] . $this->lang . '_' . $this->server['REQUEST_URI'] . $this->server['QUERY_STRING'];
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content = $Cache_Lite->get($string)) {
    $this->ms['add_this_button'] = '';
    if ($this->showIfsub) {
        if (is_numeric($this->get['categories_id'])) {
            $user_crumbar = $GLOBALS["TYPO3_CONF_VARS"]['tx_multishop_data']['user_crumbar'];
            if (!is_array($user_crumbar)) {
                $user_crumbar = mslib_fe::Crumbar($this->get['categories_id']);
            }
            if (is_array($user_crumbar) and count($user_crumbar)) {
                $user_crumbar = array_reverse($user_crumbar);
            }
            $nested_level = 0;
            $catlist = mslib_fe::getSubcatsOnly($user_crumbar[$nested_level]['id']);
            $count_list = count($catlist);
            if (!$count_list) {
                $this->hideIfNoResults = 1;
                $this->no_database_results = 1;
            } else {
                if ($this->default_header) {
                    $this->cObj->data['header'] = $user_crumbar[$nested_level]['name'];
                }
                $content .= '<div id="multishop_catbox_' . $this->cObj->data['uid'] . '">
					<ul id="catalog_sortable_' . $this->cObj->data['uid'] . '">';
                $count_hidden_menu = 0;
                foreach ($catlist as $cat) {
                    if (!$cat['hide_in_menu']) {
                        // level 0
                        // get all cats to generate multilevel fake url
                        $nested_level = 1;
                        if ($cat['categories_external_url']) {
                            if (!preg_match('/^(http|https):\/\//', $cat['categories_external_url'])) {
                                $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                            }
                            $parsed_url = @parse_url($cat['categories_external_url']);
                            if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                $target = " target=\"_blank\"";
                            } else {
                                $target = '';
                            }
                            $link = $cat['categories_external_url'];
                        } else {
                            $target = "";
                            $level = 0;
                            $cats = mslib_fe::Crumbar($cat['categories_id']);
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
                            // get all cats to generate multilevel fake url eof
                            $link = mslib_fe::typolink($this->conf['products_listing_page_pid'], $where . '&tx_multishop_pi1[page_section]=products_listing');
                        }
                        $categories_name = htmlspecialchars($cat['categories_name']);
                        $meta_description = htmlspecialchars($cat['meta_description']);
                        $actifsub = 0;
                        $act = 0;
                        $hasChild = 0;
                        if ($user_crumbar[$nested_level]['id'] == $cat['categories_id']) {
                            if ($this->get['categories_id'] == $cat['categories_id'] or $this->maxDEPTH == $nested_level + 1) {
                                $act = 1;
                            }
                            if ($user_crumbar[($nested_level + 1)]) {
                                $actifsub = 1;
                            }
                        }
                        if ($actifsub or mslib_fe::hasCats($cat['categories_id'], 0)) {
                            $hasChild = 1;
                        }
                        $content .= '<li';
                        if ($this->ADMIN_USER) {
                            $content .= ' id="sortable_maincat_' . $cat['categories_id'] . '"';
                        }
                        $this->class = array();
                        if ($hasChild) {
                            $this->class[] = 'hasChild';
                        }
                        if ($act) {
                            $this->class[] = 'active';
                        }
                        if ($actifsub) {
                            $this->class[] = 'actifsub active';
                        }
                        $content .= ' class="' . implode(' ', $this->class) . '"><a href="' . $link . '" class="ajax_link" title="' . htmlspecialchars($meta_description) . '"' . $target . '><span>' . $categories_name . '</span></a>';
                        // level 0 eof
                        if (($this->maxDEPTH > $nested_level) or (($actifsub or $act) && $this->maxDEPTHifSubs > ($nested_level))) {
                            $catlist2 = mslib_fe::getSubcatsOnly($cat['categories_id']);
                            if (count($catlist2) > 0) {
                                // level 1
                                $content .= '<ul>';
                                foreach ($catlist2 as $cat) {
                                    if (!$cat['hide_in_menu']) {
                                        $nested_level = 2;
                                        if ($cat['categories_external_url']) {
                                            if (!preg_match('/^(http|https):\/\//', $cat['categories_external_url'])) {
                                                $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                                            }
                                            $parsed_url = @parse_url($cat['categories_external_url']);
                                            if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                                $target = " target=\"_blank\"";
                                            } else {
                                                $target = '';
                                            }
                                            $link = $cat['categories_external_url'];
                                        } else {
                                            $target = "";
                                            // get all cats to generate multilevel fake url
                                            $level = 0;
                                            $cats = mslib_fe::Crumbar($cat['categories_id']);
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
                                            // get all cats to generate multilevel fake url eof
                                            $link = mslib_fe::typolink($this->conf['products_listing_page_pid'], $where . '&tx_multishop_pi1[page_section]=products_listing');
                                        }
                                        $categories_name = htmlspecialchars($cat['categories_name']);
                                        $meta_description = htmlspecialchars($cat['meta_description']);
                                        $actifsub = 0;
                                        $act = 0;
                                        $hasChild = 0;
                                        if ($user_crumbar[$nested_level]['id'] == $cat['categories_id']) {
                                            if ($this->get['categories_id'] == $cat['categories_id'] or $this->maxDEPTH == $nested_level + 1) {
                                                $act = 1;
                                            }
                                            if ($user_crumbar[($nested_level + 1)]) {
                                                $actifsub = 1;
                                            }
                                        }
                                        if ($actifsub or mslib_fe::hasCats($cat['categories_id'], 0)) {
                                            $hasChild = 1;
                                        }
                                        $this->class = array();
                                        if ($hasChild) {
                                            $this->class[] = 'hasChild';
                                        }
                                        if ($act) {
                                            $this->class[] = 'active';
                                        }
                                        if ($actifsub) {
                                            $this->class[] = 'actifsub active';
                                        }
                                        $content .= '<li class="' . implode(' ', $this->class) . '">
										<a href="' . $link . '" class="ajax_link" title="' . htmlspecialchars($meta_description) . '"' . $target . '>
											<span>' . $categories_name . '</span>
										</a>';
                                        $content .= '</li>';
                                    } // hide in menu
                                }
                                $content .= '</ul>';
                                // level 1 eof
                            }
                        }
                        $content .= '</li>';
                    } else {
                        $count_hidden_menu++;
                    }
                    // hide in menu
                }
                $content .= '</ul></div>';
                if ($this->ADMIN_USER) {
                    $content .= '
					<script type="text/javascript">
					  jQuery(document).ready(function($) {
						var result = jQuery("#catalog_sortable_' . $this->cObj->data['uid'] . '").sortable({
						 cursor:     "move",
							//axis:       "y",
							update: function(e, ui) {
								href = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=menu') . '";
								jQuery(this).sortable("refresh");
								sorted = jQuery(this).sortable("serialize", "id");
								jQuery.ajax({
										type:   "POST",
										url:    href,
										data:   sorted,
										success: function(msg) {
												//do something with the sorted data
										}
								});
							}
						});
					  });
					  </script>
					';
                }
            }
        } else {
            $this->hideIfNoResults = 1;
            $this->no_database_results = 1;
        }
    } else {
        if ($this->maxDELIMITED) {
            $delimited_array = explode(",", $this->maxDELIMITED);
            if (count($delimited_array) > 0) {
                // multi row tabnavigation menu
                $user_crumbar = $GLOBALS["TYPO3_CONF_VARS"]['tx_multishop_data']['user_crumbar'];
                if (!is_array($user_crumbar)) {
                    $user_crumbar = mslib_fe::Crumbar($this->get['categories_id']);
                }
                if (is_array($user_crumbar) and count($user_crumbar)) {
                    $user_crumbar = array_reverse($user_crumbar);
                }
                $catlist = mslib_fe::getSubcatsOnly($this->categoriesStartingPoint);
                $count_list = count($catlist);
                if (!$count_list) {
                    $this->hideIfNoResults = 1;
                    $this->no_database_results = 1;
                } else {
                    $item_counter = 0;
                    $item_counter_accordion = 0;
                    $count_hidden_menu = 0;
                    foreach ($catlist as $cat) {
                        if (!$cat['hide_in_menu']) {
                            $tmpcontent = '';
                            $item_counter++;
                            // level 0
                            // get all cats to generate multilevel fake url
                            $nested_level = 0;
                            $level = 0;
                            if ($cat['categories_external_url']) {
                                if (!preg_match('/^(http|https):\/\//', $cat['categories_external_url'])) {
                                    $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                                }
                                $parsed_url = @parse_url($cat['categories_external_url']);
                                if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                    $target = " target=\"_blank\"";
                                } else {
                                    $target = '';
                                }
                                $link = $cat['categories_external_url'];
                            } else {
                                $target = "";
                                $cats = mslib_fe::Crumbar($cat['categories_id']);
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
                                // get all cats to generate multilevel fake url eof
                                $link = mslib_fe::typolink($this->conf['products_listing_page_pid'], $where . '&tx_multishop_pi1[page_section]=products_listing');
                            }
                            $categories_name = htmlspecialchars($cat['categories_name']);
                            $meta_description = htmlspecialchars($cat['meta_description']);
                            $actifsub = 0;
                            $act = 0;
                            $hasChild = 0;
                            if ($user_crumbar[$nested_level]['id'] == $cat['categories_id']) {
                                if ($this->get['categories_id'] == $cat['categories_id'] or $this->maxDEPTH == $nested_level + 1) {
                                    $act = 1;
                                }
                                if ($user_crumbar[($nested_level + 1)]) {
                                    $actifsub = 1;
                                }
                            }
                            if ($actifsub or mslib_fe::hasCats($cat['categories_id'], 0)) {
                                $hasChild = 1;
                            }
                            $tmpcontent .= '<li';
                            $class = '';
                            $class_h2 = '';
                            $catlist2 = mslib_fe::getSubcatsOnly($cat['categories_id']);
                            if (count($catlist2) > 0) {
                                $class_h2 = 'main';
                                $num = $item_counter_accordion;
                            } else {
                                $class_h2 = "main";
                            }
                            if ($this->ADMIN_USER) {
                                $tmpcontent .= ' id="sortable_maincat_' . $cat['categories_id'] . '"';
                            }
                            $class .= 'item_' . $item_counter . ' ';
                            $this->class = array();
                            if ($hasChild) {
                                $this->class[] = 'hasChild';
                            }
                            if ($act) {
                                $this->class[] = 'active';
                            }
                            if ($actifsub) {
                                $this->class[] = 'actifsub active';
                            }
                            $class .= ' ' . implode(' ', $this->class);
                            trim($class);
                            $label = '';
                            $tmpcontent .= (($class) ? ' class="' . trim($class) . '"' : '') . '><a href="' . $link . '" class="ajax_link" title="' . htmlspecialchars($meta_description) . '"' . $target . '><span>' . $label . '' . $categories_name . '</span></a>';
                            // level 0 eof
                            $tmpcontent .= '</li>';
                            $items[] = $tmpcontent;
                        } else {
                            $count_hidden_menu++;
                        } // hide in menu
                    }
                    $content .= '<div id="multishop_catbox_' . $this->cObj->data['uid'] . '">';
                    $del_num = 0;
                    $real_num = 0;
                    foreach ($delimited_array as $delimited) {
                        $del_num++;
                        $content .= '<div id="tabbertopnav_row_' . $del_num . '">
						<table cellpadding="0" cellspacing="0" border="0">
						<tr>
						<td>
						<ul class="tabberttopnav_row">' . "\n";
                        for ($i = 0; $i < $delimited; $i++) {
                            $content .= $items[$real_num] . "\n";
                            $real_num++;
                        }
                        $content .= '
						</ul>
						</td>
						</tr>
						</table>

						</div>' . "\n";
                    }
                    $content .= '</div>' . "\n";
                }
                // multi row tabnavigation menu eof
            }
        } else {
            // show default categories box
            $user_crumbar = $GLOBALS["TYPO3_CONF_VARS"]['tx_multishop_data']['user_crumbar'];
            if (!is_array($user_crumbar)) {
                $user_crumbar = mslib_fe::Crumbar($this->get['categories_id']);
            }
            if (count($user_crumbar)) {
                $user_crumbar = array_reverse($user_crumbar);
            }
            $catlist = mslib_fe::getSubcatsOnly($this->categoriesStartingPoint);
            $count_list = count($catlist);
            if (!$count_list) {
                $this->hideIfNoResults = 1;
                $this->no_database_results = 1;
            } else {
                $content .= '<div id="multishop_catbox_' . $this->cObj->data['uid'] . '">
					<ul id="vertical_container">';
                $item_counter = 0;
                $item_counter_accordion = 0;
                $count_hidden_menu = 0;
                foreach ($catlist as $cat) {
                    if (!$cat['hide_in_menu']) {
                        $item_counter++;
                        // level 0
                        // get all cats to generate multilevel fake url
                        $nested_level = 0;
                        $level = 0;
                        if ($cat['categories_external_url']) {
                            if (!preg_match('/^(http|https):\/\//', $cat['categories_external_url'])) {
                                $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                            }
                            $parsed_url = @parse_url($cat['categories_external_url']);
                            if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                $target = " target=\"_blank\"";
                            } else {
                                $target = '';
                            }
                            $link = $cat['categories_external_url'];
                        } else {
                            $target = "";
                            $cats = mslib_fe::Crumbar($cat['categories_id']);
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
                            // get all cats to generate multilevel fake url eof
                            $link = mslib_fe::typolink($this->conf['products_listing_page_pid'], $where . '&tx_multishop_pi1[page_section]=products_listing');
                        }
                        if ($cat['categories_external_url']) {
                            $link = $cat['categories_external_url'];
                        }
                        $actifsub = 0;
                        $act = 0;
                        $hasChild = 0;
                        $showNextLevel = 0;
                        if ($this->expAll) {
                            $showNextLevel = 1;
                        }
                        if (is_array($user_crumbar) && $user_crumbar[$nested_level]['id'] == $cat['categories_id']) {
                            if ($this->get['categories_id'] == $cat['categories_id'] or $this->maxDEPTH == $nested_level + 1) {
                                $act = 1;
                                $showNextLevel = 1;
                            }
                            if ($user_crumbar[($nested_level + 1)]) {
                                $actifsub = 1;
                            }
                        }
                        foreach ($user_crumbar as $item) {
                            if ($item['id'] == $cat['categories_id']) {
                                $act = 1;
                                $showNextLevel = 1;
                            }
                        }
                        if ($actifsub or mslib_fe::hasCats($cat['categories_id'], 0)) {
                            $hasChild = 1;
                        }
                        $categories_name = htmlspecialchars($cat['categories_name']);
                        $meta_description = htmlspecialchars($cat['meta_description']);
                        $content .= '<li ';
                        $class = '';
                        $class_h2 = '';
                        $catlist2 = array();
                        if ($showNextLevel) {
                            $catlist2 = mslib_fe::getSubcatsOnly($cat['categories_id']);
                        }
                        if (count($catlist2) > 0) {
                            $class_h2 = 'main';
                            $num = $item_counter_accordion;
                        } else {
                            $class_h2 = "main";
                        }
                        if ($this->ADMIN_USER) {
                            $content .= 'id="sortable_maincat_' . $cat['categories_id'] . '" ';
                        }
                        $class .= 'item_' . $item_counter . ' ';
                        $this->class = array();
                        if ($hasChild) {
                            $this->class[] = 'hasChild';
                        }
                        if ($act) {
                            $this->class[] = 'active';
                        }
                        if ($actifsub) {
                            $this->class[] = 'actifsub active';
                        }
                        $class = trim($class . ' ' . implode(' ', $this->class));
                        $content .= 'class="' . $class . '"><a href="' . $link . '" class="ajax_link" title="' . htmlspecialchars($meta_description) . '"' . $target . '><span>' . $categories_name . '</span></a>';
                        /*
                                            if ($actifsub or $act)  {
                                                $class.='active ';
                                                if (count($catlist2) > 0){
                                                    $active_accordion = 'bottomAccordion.activate($$("#vertical_container .accordion_toggle")['.$num.']);';
                                                }
                                            }
                                            $content.=(($class)?'class="'.trim($class).'"':'').'><a href="'.$link.'" class="ajax_link" title="'.htmlspecialchars($meta_description).'"'.$target.'><span>'.$categories_name.'</span></a>';
                        */
                        // level 0 eof
                        if ($this->maxDEPTH > $nested_level) {
                            if (count($catlist2) > 0) {
                                // level 1
                                $content .= '<ul>';
                                foreach ($catlist2 as $cat) {
                                    if (!$cat['hide_in_menu']) {
                                        $nested_level = 1;
                                        // get all cats to generate multilevel fake url
                                        $level = 0;
                                        if ($cat['categories_external_url']) {
                                            if (!preg_match('/^(http|https):\/\//', $cat['categories_external_url'])) {
                                                $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                                            }
                                            $parsed_url = @parse_url($cat['categories_external_url']);
                                            if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                                $target = " target=\"_blank\"";
                                            } else {
                                                $target = '';
                                            }
                                            $link = $cat['categories_external_url'];
                                        } else {
                                            $target = "";
                                            $cats = mslib_fe::Crumbar($cat['categories_id']);
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
                                            // get all cats to generate multilevel fake url eof
                                            $link = mslib_fe::typolink($this->conf['products_listing_page_pid'], $where . '&tx_multishop_pi1[page_section]=products_listing');
                                        }
                                        if ($cat['categories_external_url']) {
                                            if (!preg_match('/^(http|https):\/\//', $cat['categories_external_url'])) {
                                                $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                                            }
                                            $parsed_url = @parse_url($cat['categories_external_url']);
                                            if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                                $target = " target=\"_blank\"";
                                            } else {
                                                $target = '';
                                            }
                                            $link = $cat['categories_external_url'];
                                        }
                                        $categories_name = htmlspecialchars($cat['categories_name']);
                                        $meta_description = htmlspecialchars($cat['meta_description']);
                                        $actifsub = 0;
                                        $act = 0;
                                        $hasChild = 0;
                                        if ($user_crumbar[$nested_level]['id'] == $cat['categories_id']) {
                                            if ($this->get['categories_id'] == $cat['categories_id'] or $this->maxDEPTH == $nested_level + 1) {
                                                $act = 1;
                                            }
                                            if ($user_crumbar[($nested_level + 1)]) {
                                                $actifsub = 1;
                                            }
                                        }
                                        foreach ($user_crumbar as $item) {
                                            if ($item['id'] == $cat['categories_id']) {
                                                $act = 1;
                                            }
                                        }
                                        if ($actifsub or mslib_fe::hasCats($cat['categories_id'], 0)) {
                                            $hasChild = 1;
                                        }
                                        $catlist3 = mslib_fe::getSubcatsOnly($cat['categories_id']);
                                        //level submenu 2 start
                                        if (count($catlist3) > 0 and $this->maxDEPTH > 2) {
                                            $this->class = array();
                                            if ($hasChild) {
                                                $this->class[] = 'hasChild';
                                            }
                                            if ($act) {
                                                $this->class[] = 'active';
                                            }
                                            if ($actifsub) {
                                                $this->class[] = 'actifsub active';
                                            }
                                            $content .= '<li class="' . implode(' ', $this->class) . '">
											<a href="' . $link . '" title="' . htmlspecialchars($meta_description) . '"' . $target . '><span>' . $categories_name . '</span></a>
											<ul>';
                                            $cat_level_3 = "";
                                            foreach ($catlist3 as $cat) {
                                                if (!$cat['hide_in_menu']) {
                                                    $nested_level = 2;
                                                    // get all cats to generate multilevel fake url
                                                    $level = 0;
                                                    if ($cat['categories_external_url']) {
                                                        if (!preg_match('categories_external_url', $cat['categories_external_url'])) {
                                                            $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                                                        }
                                                        $parsed_url = @parse_url($cat['categories_external_url']);
                                                        if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                                            $target = " target=\"_blank\"";
                                                        } else {
                                                            $target = '';
                                                        }
                                                        $link = $cat['categories_external_url'];
                                                    } else {
                                                        $target = "";
                                                        $cats = mslib_fe::Crumbar($cat['categories_id']);
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
                                                        // get all cats to generate multilevel fake url eof
                                                        $link = mslib_fe::typolink($this->conf['products_listing_page_pid'], $where . '&tx_multishop_pi1[page_section]=products_listing');
                                                    }
                                                    if ($cat['categories_external_url']) {
                                                        if (!preg_match('/^(http|https):\/\//', $cat['categories_external_url'])) {
                                                            $cat['categories_external_url'] = 'http://' . $cat['categories_external_url'];
                                                        }
                                                        $parsed_url = @parse_url($cat['categories_external_url']);
                                                        if ($parsed_url['host'] and ($parsed_url['host'] <> $this->server['HTTP_HOST'])) {
                                                            $target = " target=\"_blank\"";
                                                        } else {
                                                            $target = '';
                                                        }
                                                        $link = $cat['categories_external_url'];
                                                    }
                                                    $categories_name = htmlspecialchars($cat['categories_name']);
                                                    $meta_description = htmlspecialchars($cat['meta_description']);
                                                    $actifsub = 0;
                                                    $act = 0;
                                                    $hasChild = 0;
                                                    if ($user_crumbar[$nested_level]['id'] == $cat['categories_id']) {
                                                        if ($this->get['categories_id'] == $cat['categories_id'] or $this->maxDEPTH == $nested_level + 1) {
                                                            $act = 1;
                                                        }
                                                        if ($user_crumbar[($nested_level + 1)]) {
                                                            $actifsub = 1;
                                                        }
                                                    }
                                                    if ($actifsub or mslib_fe::hasCats($cat['categories_id'], 0)) {
                                                        $hasChild = 1;
                                                    }
                                                    $this->class = array();
                                                    if ($hasChild) {
                                                        $this->class[] = 'hasChild';
                                                    }
                                                    if ($act) {
                                                        $this->class[] = 'active';
                                                    }
                                                    if ($actifsub) {
                                                        $this->class[] = 'actifsub active';
                                                    }
                                                    $cat_level_3 .= '<li class="' . implode(' ', $this->class) . '"><a href="' . $link . '" title="' . htmlspecialchars($meta_description) . '"' . $target . '><span>' . $categories_name . '</span></a></li>';
                                                } // hide in menu
                                            }
                                            $content .= $cat_level_3 . '</ul>';
                                        } else {
                                            $this->class = array();
                                            if ($hasChild) {
                                                $this->class[] = 'hasChild';
                                            }
                                            if ($act) {
                                                $this->class[] = 'active';
                                            }
                                            if ($actifsub) {
                                                $this->class[] = 'actifsub active';
                                            }
                                            $content .= '<li class="' . implode(' ', $this->class) . '"><a href="' . $link . '" title="' . htmlspecialchars($meta_description) . '"' . $target . '><span>' . $categories_name . '</span></a>';
                                        }
                                        //level submenu 2 eof
                                        $content .= '</li>';
                                    } // hide in menu
                                }
                                $content .= '</ul>';
                                // level 1 eof
                            }
                        }
                        $content .= '</li>';
                    } else {
                        $count_hidden_menu++;
                    }
                    // hide in menu
                }
                $content .= '</ul></div>';
                if ($this->ADMIN_USER) {
                    $content .= '
					<script type="text/javascript">
					  jQuery(document).ready(function($) {
						var result = jQuery("#vertical_container").sortable({
						 cursor:     "move",
							//axis:       "y",
							update: function(e, ui) {
								href = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=menu') . '";
								jQuery(this).sortable("refresh");
								sorted = jQuery(this).sortable("serialize", "id");
								jQuery.ajax({
										type:   "POST",
										url:    href,
										data:   sorted,
										success: function(msg) {
												//do something with the sorted data
										}
								});
							}
						});
					  });
					  </script>
					';
                }
            }
            // show default categories box eof
        }
    }
    if (!$content || ($count_hidden_menu == $count_list)) {
        // no content. lets hide it
        $this->hideIfNoResults = 1;
        $this->no_database_results = 1;
    }
    if ($this->ms['MODULES']['CACHE_FRONT_END']) {
        $Cache_Lite->save($content);
    }
}
