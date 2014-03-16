jQuery(document).ready(function($){
	// admin auto complete
	var sendData;
	$("#ms_admin_skeyword").bind("focus",function() {
		$("#ms_admin_us_page").val(0);
	});
	$(document).on("click", "#ms_admin_skeyword", function() {
		$("#ms_admin_skeyword").val("");
		$(".ui-autocomplete").attr("id", "ui-autocomplete-admin");
		$(".ui-autocomplete").css("zIndex","999");
	});
	$(document).on("keydown.autocomplete", "#ms_admin_skeyword", function(e) {
		// dont process special keys
		var skipKeys = [ 13,38,40,37,39,27,32,17,18,9,16,20,91,93,8,36,35,45,46,33,34,144,145,19 ];
		if (jQuery.inArray(e.keyCode, skipKeys) != -1) {
			sendData = false;
		} else {
			sendData = true;
		}
		$(".ui-autocomplete").attr("id", "ui-autocomplete-admin");
		$(this).autocomplete({
			minLength: 1,
			delay: 250,
			zIndex: 999,
			open: function(event, ui) {
				$(".ui-autocomplete").attr("id", "ui-autocomplete-admin");
				$(".ui-autocomplete li.ui-menu-item:odd").addClass("ui-menu-item-alternate");
				$(".ui-autocomplete").css("zIndex","999");
			},
			source: function( request, response ) {
				if (sendData){
					$.ajax({
						url: MS_ADMIN_PANEL_AUTO_COMPLETE_URL,
						dataType: "json",
						data: {
							q: $("#ms_admin_skeyword").val(), page: $("#ms_admin_us_page").val()
						},
						success: function( data ) {
							var index = 1;
							if(data.products != null){
								response( $.map( data.products, function( item ) {
									index = index + 1;
										if (item.Product == 'paging') {
											return {
												label: item.Title,
												value: item.skeyword,
												link: item.Link,
												ms_admin_skeyword: item.skeyword,
												page: item.Page,
												prod: item.Product
											}
				
										} else if (item.Product == false) {
											return {
												label: item.Title,
												value: item.Name,
												link: "",
												ms_admin_skeyword: item.skeyword,
												page: item.Page,
												prod: item.Product
											}
										} else if (item.SmallListing == true) {
											return {
												label: "<div class=\"single_row\">"+item.Title+"</div>",
												value: item.Name,
												link: item.Link,
												ms_admin_skeyword: item.skeyword,
												page: item.Page,
												prod: item.Product,
												EditIcons: item.EditIcons,
												title: item.Title
		
											}
										} else {
											return {
												label: "<div class=\"ajax_products_image_wrapper\">"+item.Image + "</div><div class=\"ajax_products_search_item\">" + item.Title  + item.Desc + item.Price + "</div>",
												value: item.Name,
												link: item.Link,
												ms_admin_skeyword: item.skeyword,
												page: item.Page,
												prod: item.Product,
												EditIcons: item.EditIcons,
												title: item.Title
											}
										}
									})
								);
							} //end if data
						}
					});
				} // and if sendData
			},
			select: function(event, ui ) {
				$("#ms_admin_skeyword").val(ui.item.ms_admin_skeyword);
				$("#ms_admin_us_page").val(ui.item.page);
				var link = MS_ADMIN_PANEL_FULL_URL + ui.item.link ;
				//alert(link);
				if (ui.item.prod == 'paging'){
					$("html,body").scrollTop(0);
					$("#ms_admin_skeyword").autocomplete("search");
				} else if (ui.item.prod == true){
					hs.htmlExpand(null, {
						objectType: 'iframe',
						width: 950,
						height: 750,
						src: link
					});
				} else {
					$("#ms_admin_skeyword").autocomplete("search");
				}
			},
			focus: function(event, ui) {
				$("#ms_admin_skeyword").val(ui.item.ms_admin_skeyword);
				$("#ms_admin_us_page").val(0);
				return false;
			}
		}).data('ui-autocomplete')._renderItem = function (ul, item) {
			if (item.link == false){
			   var objLi = jQuery("<li></li>").removeClass("ui-menu-item").removeClass("ui-menu-item");
			   return objLi.addClass("ui-category").data("item.autocomplete", item).append(item.label).appendTo(ul);
			} else {
				if (typeof item.EditIcons === 'undefined') {
					item.EditIcons="";
				}
				return jQuery("<li></li>").data("item.autocomplete", item).append(item.EditIcons).append(jQuery("<a></a>").attr("alt",item.value).html(item.label)).appendTo(ul);
			}
		};
	});
});
// auto complete eof