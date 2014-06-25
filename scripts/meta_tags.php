<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// check Multishop version
if (!$this->ms['MODULES']['GLOBAL_MODULES']['MULTISHOP_VERSION']) {
	$this->runUpdate=1;
} else {
	$info=mslib_befe::getExtensionInfo($this->DOCUMENT_ROOT_MS, 'multishop');
	$current_version=class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger($this->ms['MODULES']['GLOBAL_MODULES']['MULTISHOP_VERSION']) : t3lib_div::int_from_ver($this->ms['MODULES']['GLOBAL_MODULES']['MULTISHOP_VERSION']);
	$new_version=class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger($info['version']) : t3lib_div::int_from_ver($info['version']);
	if ($current_version<$new_version) {
		// update current_version
		$array=array();
		$array['configuration_value']=$info['version'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_configuration', 'configuration_key=\'MULTISHOP_VERSION\'', $array);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		//TODO later we will execute the update method from here, instead of outside this if statement
		$this->runUpdate=1;
	}
}
if ($this->runUpdate) {
	mslib_befe::RunMultishopUpdate();
}
//mslib_befe::RunMultishopUpdate();
// temporary we compare the database for reach request, so the developer doesnt need to press manual button Compare database within the admin panel.
//mslib_befe::RunMultishopUpdate();
// application top things that are only runned the first time when the plugin is initiated
if ($this->get['categories_id'] or $this->get['products_id']) {
	if (strstr($this->ms['MODULES']['CRUMBAR_TYPE'], "/")) {
		require($this->DOCUMENT_ROOT.$this->ms['MODULES']['CRUMBAR_TYPE'].'.php');
	} elseif ($this->ms['MODULES']['CRUMBAR_TYPE']) {
		require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/crumbar/'.$this->ms['MODULES']['CRUMBAR_TYPE'].'.php');
	} else {
		require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/crumbar/default.php');
	}
	if ($crum) {
		$GLOBALS["TYPO3_CONF_VARS"]["tx_multishop"]['crumbar_html']=$crum;
	}
}
if (!$GLOBALS["TYPO3_CONF_VARS"]["tx_multishop_started"]) {
	$GLOBALS["TYPO3_CONF_VARS"]["tx_multishop_started"]=1;
	// hook for pre-processing product before inserted to cart
	if ($this->get['tx_multishop_pi1']['page_section']=='shopping_cart' and is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scripts/meta_tags.php']['insertToCart'])) {
		$params=array();
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scripts/meta_tags.php']['insertToCart'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scripts/meta_tags.php']['insertToCart'] as $funcRef) {
				$content.=t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
	} else {
		$updateCart=0;
		if (is_numeric($this->get['products_id']) and $this->get['tx_multishop_pi1']['action']=='add_to_cart') {
			$updateCart=1;
		} else {
			if ((($this->post['products_id'] or $this->get['delete_products_id'] or $this->post['qty'] or $this->get['add_products_id']) and $this->get['tx_multishop_pi1']['page_section']=='shopping_cart') and !$GLOBALS['dont_update_cart']) {
				$updateCart=1;
			}
		}
		if ($updateCart) {
			require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_cart.php');
			$mslib_cart=t3lib_div::makeInstance('tx_mslib_cart');
			$mslib_cart->init($this);
			$mslib_cart->updateCart();
			$link=mslib_fe::typolink($this->shoppingcart_page_pid, '&tx_multishop_pi1[page_section]=shopping_cart', 1);
			if ($link) {
				header("Location: ".$this->FULL_HTTP_URL.$link);
				exit();
			}
		}
	}
	if ($this->get['categories_id']) {
		$categories_id=$this->get['categories_id'];
	} elseif ($product['categories_id']) {
		$categories_id=$product['categories_id'];
	}
	if ($categories_id) {
		$GLOBALS["TYPO3_CONF_VARS"]['tx_multishop_data']['user_crumbar']=mslib_fe::Crumbar($categories_id);
	}
}
// application top things that are only runned the first time when the plugin is initiated eof
$meta_tags=array();
if ($this->ADMIN_USER) {
	// bind shortkeys
	$meta_tags['1_jquery'].='
	<script type="text/javascript">
	jQuery(document).ready(function($) {
			';
	if ($this->get['products_id']) {
		$meta_tags['1_jquery'].='msAdminShortcutFunc(\'product\');'."\n";
	} elseif ($this->get['categories_id']) {
		$meta_tags['1_jquery'].='msAdminShortcutFunc(\'category\');'."\n";
	} else {
		$meta_tags['1_jquery'].='msAdminShortcutFunc();'."\n";
	}
	$meta_tags['1_jquery'].='
	});
	</script>
	';
}
if ($this->ADMIN_USER) {
	$admin_menu_panel=mslib_fe::jQueryAdminMenu();
	// admin stats
	if ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END']) {
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>180
		);
		$Cache_Lite=new Cache_Lite($options);
		$string=md5('admin_stats_'.$this->shop_pid);
	}
	if (!$this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END'] and !$html=$Cache_Lite->get($string))) {
		$html='
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var intervalID;
			// messages
		';
		$messages=array();
		// total customers
		$str="SELECT count(1) as total from fe_users where disable=0";
		if (!$this->masterShop) {
			$str.=" and page_uid='".$this->shop_pid."'";
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if ($row['total']>0) {
			if ($row['total']==1) {
				$string=sprintf($this->pi_getLL('there_is_one_customer_registered'), '<strong>'.$row['total'].'</strong>');
			} else {
				$string=sprintf($this->pi_getLL('there_are_s_customers_registered'), '<strong>'.$row['total'].'</strong>');
			}
			$messages[]='"<a href=\"'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers').'\">'.$string.'</a>"';
		}
		// total customers eof
		// orders today
		$from=strtotime(date("Y-m-d").' 00:00:00');
		$till=time();
		$str="SELECT count(1) as total from tx_multishop_orders where deleted=0 and crdate BETWEEN ".$from." and ".$till;
		if (!$this->masterShop) {
			$str.=" and page_uid='".$this->shop_pid."'";
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if ($row['total']>0) {
			if ($row['total']==1) {
				$string=sprintf($this->pi_getLL('today_there_is_one_order_created'), '<strong>'.$row['total'].'</strong>');
			} else {
				$string=sprintf($this->pi_getLL('today_there_are_s_orders_created'), '<strong>'.$row['total'].'</strong>');
			}
			$messages[]='"<a href=\"'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders').'\">'.$string.'</a>"';
		}
		// orders today eof
		// orders this week
		$days=mslib_befe::Week((date("W")+1));
		$from=$days[0];
		$till=$days[5];
		$str="SELECT count(1) as total from tx_multishop_orders where deleted=0 and crdate BETWEEN ".$from." and ".$till;
		if (!$this->masterShop) {
			$str.=" and page_uid='".$this->shop_pid."'";
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if ($row['total']>0) {
			if ($row['total']==1) {
				$string=sprintf($this->pi_getLL('this_s_there_is_one_order_created'), t3lib_div::strtoupper($this->pi_getLL('week')), '<strong>'.$row['total'].'</strong>');
			} else {
				$string=sprintf($this->pi_getLL('this_s_there_are_s_orders_created'), t3lib_div::strtoupper($this->pi_getLL('week')), '<strong>'.$row['total'].'</strong>');
			}
			$messages[]='"<a href=\"'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders').'\">'.$string.'</a>"';
		}
		// orders this week eof
		// orders this month
		$from=strtotime(date("Y-m-1 00:00:00"));
		$till=strtotime("+1 MONTH -1 DAY ".date("Y-m-1 23:59:59"));
		$str="SELECT count(1) as total from tx_multishop_orders where deleted=0 and crdate BETWEEN ".$from." and ".$till;
		if (!$this->masterShop) {
			$str.=" and page_uid='".$this->shop_pid."'";
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if ($row['total']>0) {
			if ($row['total']==1) {
				$string=sprintf($this->pi_getLL('this_s_there_is_one_order_created'), t3lib_div::strtoupper($this->pi_getLL('month')), '<strong>'.$row['total'].'</strong>');
			} else {
				$string=sprintf($this->pi_getLL('this_s_there_are_s_orders_created'), t3lib_div::strtoupper($this->pi_getLL('month')), '<strong>'.$row['total'].'</strong>');
			}
			$messages[]='"<a href=\"'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders').'\">'.$string.'</a>"';
		}
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/meta_tags.php']['adminPanelMessages'])) {
			$params=array('messages'=>&$messages);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/meta_tags.php']['adminPanelMessages'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// orders this month eof
		if (count($messages)) {
			shuffle($messages);
			$html.='
					var messages=['.implode(", ", $messages).'];
					var countMessage = messages.length * 4;
					var secondInterval = countMessage + 1 + "000";
					function changeText() {
					  intervalID = setInterval(multishop_admin_scroller, secondInterval);
					}
					function multishop_admin_scroller() {
						jQuery.each(messages, function(index, value) {
							setTimeout(function() {
								jQuery("#tx_multishop_admin_footer .ms_admin_scroller").hide().html(value).fadeIn(600);
							},index*4000);
						});
					}
					';
			$html.='
					//scroll messages
					multishop_admin_scroller();
					changeText();
			';
		}
		$html.='
		});
		</script>
		';
		if ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END']) {
			$Cache_Lite->save($html);
		}
	}
	if ($this->get['tx_multishop_pi1']['page_section']=='admin_home') {
		$this->ms['MODULES']['DISABLE_ADMIN_PANEL']=1;
	}
	// admin stats eof
	$html.='
			<script type="text/javascript">
			var MS_ADMIN_PANEL_AUTO_COMPLETE_URL=\''.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_panel_ajax_search').'\';
			var MS_ADMIN_PANEL_AUTO_COMPLETE_LABEL=\''.$this->pi_getLL('keyword').'\';
			var MS_ADMIN_PANEL_FULL_URL=\''.$this->FULL_HTTP_URL.'\';
			jQuery(document).ready(function($) {
				$(document).on("click", ".ms_admin_minimize", function(e) {
					e.preventDefault();
					$("li.ms_admin_search > form#ms_admin_top_search > input#ms_admin_skeyword").select2("close");
					$.cookie("hide_admin_panel", "1", { expires: 7, path: \'/\', domain: \''.$this->server['HTTP_HOST'].'\'});
					$("#tx_multishop_admin_header_bg").slideToggle("slow");
					$("#tx_multishop_admin_footer_wrapper").slideToggle("slow");
					$("#ms_admin_minimaxi_wrapper").html(\'<ul id="ms_admin_maximize"><li><a href="#" class="ms_admin_maximize">'.$this->pi_getLL('maximize').'</a></li></ul>\');
				});
				$(document).on("click", ".ms_admin_maximize", function(e) {
					e.preventDefault();
					$.cookie("hide_admin_panel", "0", { expires: 7, path: \'/\', domain: \''.$this->server['HTTP_HOST'].'\'});
					$("#tx_multishop_admin_header_bg").slideToggle("slow");
					$("#tx_multishop_admin_footer_wrapper").slideToggle("slow");
					$("#ms_admin_minimaxi_wrapper").html(\'<ul id="ms_admin_minimize"><li><a href="#" class="ms_admin_minimize">'.$this->pi_getLL('minimize').'</a></li></ul>\');
				});
				$(document).on("change", "#ms_admin_simulate_language", function() {
					$("#multishop_admin_language_form").submit();
				});
				// if (isMobile()) {
				//	return false;
				// }
				$.ajax({
					url: \''.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_panel&tx_multishop_pi1[categories_id]='.$this->get['categories_id'].'&tx_multishop_pi1[products_id]='.$this->get['products_id']).'\',
					data: \'\',
					type: \'post\',
					dataType: \'json\',
					success: function (j){
						if (j) {
							//var json_data = $.parseJSON(j);
							var json_data = j;
							
							// top admin menu
							var admin_menu_header = \'<div id="tx_multishop_admin_header_wrapper">\';
							admin_menu_header += \'<div id="tx_multishop_admin_header_bg"><ul id="tx_multishop_admin_header">\';
							var admin_menu_header_html = renderAdminMenu(json_data.header, \'header\', 1);
							admin_menu_header += admin_menu_header_html;
							admin_menu_header += \'</ul></div>\';
							admin_menu_header += \'<div id="ms_admin_minimaxi_wrapper"><ul id="ms_admin_minimize"><li><a href="#" class="ms_admin_minimize">'.$this->pi_getLL('minimize').'</a></li></ul></div>\';
							admin_menu_header += \'</div>\';
						
							// bottom admin menu
							var admin_menu_footer = \'<div id="tx_multishop_admin_footer_wrapper"><ul id="tx_multishop_admin_footer">\';
							var admin_menu_footer_html = renderAdminMenu(json_data.footer, \'footer\', 1);
							admin_menu_footer += admin_menu_footer_html;
							admin_menu_footer += \'</ul></div>\';

							var admin_menu= admin_menu_header + admin_menu_footer;
							'.(!$this->ms['MODULES']['DISABLE_ADMIN_PANEL'] ? '$("body").prepend(admin_menu);' : '').'
							
							// load partial menu items and add them to the footer
							if ($(".footer_content").length > 0) {
								$("#footer_content_cols").hide();
								$("#footer_content_cols #footer_content1").html("");
								$("#footer_content_cols #footer_content2").html("");
								$("#footer_content_cols #footer_content3").html("");
								$("#footer_content_cols #footer_content4").html("");
								//$("#footer_content_cols #footer_content5").html("");
								if (json_data.header.ms_admin_catalog != undefined) {
									var admin_menu_catalog_html=renderAdminMenu(json_data.header.ms_admin_catalog, \'header\', 0);
									$("#footer_content_cols #footer_content1").append(\'<ul>\'+admin_menu_catalog_html+\'</ul>\');
								}
								if (json_data.header.ms_admin_cms != undefined) {
									var admin_menu_cms_html=renderAdminMenu(json_data.header.ms_admin_cms, \'header\', 0);
									$("#footer_content_cols #footer_content1").append(\'<ul>\'+admin_menu_cms_html+\'</ul>\');
								}
								if (json_data.header.ms_admin_orders_customers != undefined) {
									var admin_menu_orders_html=renderAdminMenu(json_data.header.ms_admin_orders_customers, \'header\', 0);
									$("#footer_content_cols #footer_content2").append(\'<ul>\'+admin_menu_orders_html+\'</ul>\');
								}
								if (json_data.header.ms_admin_statistics != undefined) {
									var admin_menu_statistics_html=renderAdminMenu(json_data.header.ms_admin_statistics, \'header\', 0);
									$("#footer_content_cols #footer_content3").append(\'<ul>\'+admin_menu_statistics_html+\'</ul>\');
								}
								if (json_data.footer.ms_admin_system != undefined) {
									var admin_menu_system_html=renderAdminMenu(json_data.footer.ms_admin_system, \'footer\', 0);
									$("#footer_content_cols #footer_content4").append(\'<ul>\'+admin_menu_system_html+\'</ul>\');
								}																
								$("#footer_content_cols").slideToggle("500");
							}
							';
	if ($_COOKIE['hide_admin_panel']) {
		$html.='
									$("#tx_multishop_admin_header_bg").hide();
									$("#tx_multishop_admin_footer_wrapper").hide();
									$("#ms_admin_minimaxi_wrapper").html(\'<ul id="ms_admin_maximize"><li><a href="#" class="ms_admin_maximize">'.$this->pi_getLL('maximize').'</a></li></ul>\');
								';
	}
	$html.='}
					}
				});
											
				';
	$html.='
$(document).on("click", "#multishop_update_button", function(e) {
	e.preventDefault();
	if (CONFIRM(\''.addslashes($this->pi_getLL('admin_label_are_you_sure_you_want_to_run_the_multishop_update_script')).'?\')) {
			$.blockUI({ css: {
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
					$.ajax({
					  url: \''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=update_multishop').'\',
					  data: \'\',
					  type: \'post\',
					  dataType: \'json\',
					  success: function (j){
						$.unblockUI();
						var string=j.html;
						if (string) {
							$.blockUI({
								message: \'<h1>Multishop Update</h1><div class="growl_message">\'+string+\'</div>\',
								fadeIn: 700,
								fadeOut: 700,
								timeout: 10000,
								showOverlay: false,
								centerY: true,
								css: {
									width: \'350px\',
									height: \'350px\',
									top: \'250px\',
									left: \'\',
									right: \'50%\',
									border: \'none\',
									padding: \'5px\',
									backgroundColor: \'#000\',
									\'-webkit-border-radius\': \'10px\',
									\'-moz-border-radius\': \'10px\',
									opacity: .9,
									color: \'#fff\'
								}
							});
						} else {
							$.blockUI({
								message: \'<h1>Multishop Update</h1><div class="growl_message">We are sorry, but the update failed</div>\',
								fadeIn: 700,
								fadeOut: 700,
								timeout: 10000,
								showOverlay: false,
								centerY: true,
								css: {
									width: \'350px\',
									height: \'350px\',
									top: \'250px\',
									left: \'\',
									right: \'50%\',
									border: \'none\',
									padding: \'5px\',
									backgroundColor: \'#000\',
									\'-webkit-border-radius\': \'10px\',
									\'-moz-border-radius\': \'10px\',
									opacity: .9,
									color: \'#fff\'
								}
							});
						}
					  }
					});
				}
			});
	}
});
';
	$html.='
});
</script>';
	$meta_tags['tx_multishop_pi1_admin_menu']=$html;
	if ($this->ms['MODULES']['DISPLAY_REALTIME_NOTIFICATION_MESSAGES']) {
		$meta_tags['tx_multishop_pi1_admin_menu'].=mslib_fe::displayAdminNotificationPopup();
	}
}
?>