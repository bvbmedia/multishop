<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$output = array();

// now parse all the objects in the tmpl file
if ($this->conf['searchform_tmpl_path'])  	$template = $this->cObj->fileResource($this->conf['searchform_tmpl_path']);
elseif ($this->conf['searchform_tmpl'])  	$template = $this->cObj->fileResource($this->conf['searchform_tmpl']);
else										$template = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/searchform.tmpl');
// Extract the subparts from the template
$subparts=array();
$subparts['template'] 	= $this->cObj->getSubpart($template, '###TEMPLATE###');

if ($this->conf['includejAutocomplete'])
{						
	$GLOBALS['TSFE']->additionalHeaderData[]='<script type="text/javascript">
			  jQuery(document).ready(function($) {
				var sendData;														  
				jQuery("#skeyword").bind("focus",function(){
					jQuery("#page").val(0);
				})
				jQuery("#skeyword").bind("keydown.autocomplete",function(e){
					// dont process special keys
					var skipKeys = [ 13,38,40,37,39,27,32,17,18,9,16,20,91,93,8,36,35,45,46,33,34,144,145,19 ];
					if (jQuery.inArray(e.keyCode, skipKeys) != -1) sendData = false;
					else sendData = true;																													
				})							
				jQuery("#skeyword").autocomplete({
					//console.log(this);
					minLength: 1,
					delay: 400,
					open: function(event, ui){
						jQuery(".ui-autocomplete").attr("id", "ui-autocomplete-front");																										
						jQuery(".ui-autocomplete li.ui-menu-item:odd a").addClass("ui-menu-item-alternate");
					},										
					source: function( request, response ) {
							if (sendData){		
							jQuery.ajax({
								url: "'. mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=ajax_products_search') .'",
								dataType : "json",
								timeout : 10000,
								data: {
									q: jQuery("#skeyword").val(), page: jQuery("#page").val()
								},
								success: function( data ) {
									var index = 1;
									 if(data.products != null){
										response( jQuery.map( data.products, function( item ) {
											index = index + 1;
											 
												if (index == 6) {
													return {
														label: item.Title,
														value: item.skeyword,
														link: item.Link,
														skeyword: item.skeyword,
														page: item.Page,
														prod: item.Product
													}
												} else {
													return {
														label: "<div class=\"ajax_products_image_wrapper\">"+item.Image + "</div><div class=\"ajax_products_search_item\">" + item.Title  + item.Desc + item.Price + "</div>",
														value: item.Name,
														link: item.Link,
														skeyword: item.skeyword,
														page: item.Page,
														prod: item.Product
													}
												}
											 
											})
										);
									 } //end if data
									//alert(index);
								}
							});
							} // and if sendData
						},
					select: function(event, ui ) {
									 jQuery("#skeyword").val(ui.item.skeyword);
									 jQuery("#page").val(ui.item.page);
									//console.log(ui);
									//alert(ui.toSource());
									var link = "'.$this->FULL_HTTP_URL.'" + ui.item.link ; 
									//alert(link);
									if (ui.item.prod == true){
										open(link,\'_self\',\'resizable,location,menubar,toolbar,scrollbars,status\');
									} else {
										jQuery("#skeyword").autocomplete("search");
									}
								},
					focus: function(event, ui) {
						jQuery("#skeyword").val(ui.item.skeyword);
						jQuery("#page").val(0);
						return false;
					}	
				}).data(\'autocomplete\')._renderItem = function (ul, item) {
					return jQuery("<li></li>").data("item.autocomplete", item).append(jQuery("<a></a>").html(item.label)).appendTo(ul);
					};
			  });
		</script>';
}

// fill the row marker with the expanded rows
$subpartArray['###SEARCH_PAGE_PID###'] 		= $this->conf['search_page_pid'];
$subpartArray['###LABEL_KEYWORD###'] 			= $this->pi_getLL('keyword');
$subpartArray['###KEYWORD_VALUE###'] = htmlspecialchars(mslib_fe::RemoveXSS($this->get['skeyword']));
$subpartArray['###LABEL_SUBMIT_BUTTON###'] = htmlspecialchars($this->pi_getLL('search'));
// completed the template expansion by replacing the "item" marker in the template

$content = $this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
?>