var query_string = "";
var jum_checked = "";
var checked_category = jQuery(".category_products");
function filterproducts (page)
{
	query_string = "";
	jQuery(".category_products").each(
		function()
		{
			if(this.checked)
			{
				query_string += "&category_products[]=" + this.value;
				//updateComponent(this.value);
			}
		});
	jQuery(".option-attributes").each(
			function()
			{
				if (jQuery(this).is(':radio') || jQuery(this).is(':checkbox'))
				{
					if(jQuery(this).is(':checked'))
					{
						query_string += "&" + this.name  + "="+ this.value;
					}
				}
				else if (jQuery(this).is('select'))
				{
					var variable_name=this.name+"[]";
					$("select[name='"+this.name+"'] option:selected").each(function () {
						if (this.value) query_string += "&" + variable_name + "="+ this.value;
					});		
				}				
				else
				{
					if (this.value) query_string += "&" + this.name + "="+ this.value;
				}
			});
		jQuery(".slider_value_left").each(
			function()
			{
				if (this.value) query_string += "&" + this.name + "="+ this.value;
			});		
		jQuery(".slider_value_right").each(
			function()
			{
				if (this.value) query_string += "&" + this.name + "="+ this.value;
			});		
	 query_string += "&min=" + jQuery("#Filter_5_Min").val();
	 query_string += "&max=" + jQuery("#Filter_5_Max").val();
	 
	 //JIRA 157 ultrasearch update
	 var brands_val = jQuery("#brands").val(); //for list and list_multiple
	 if (brands_val != null){
		 query_string += "&brands=" + brands_val;
	 }
	 var categories_id = jQuery("#categories_id_extra").val(); //for list and list_multiple
	 if (categories_id != null){
		 query_string += "&categories_id=" + categories_id;
	 }	 	 
	
	 
	 jQuery(".brands-click").each(
			
		function()
		{
			if (jQuery(this).is(':radio') || jQuery(this).is(':checkbox'))
				{
					if(jQuery(this).is(':checked'))
					{
						query_string += "&" + this.name  + "="+ this.value;
					}
				}
			
		});
	 //JIRA 157 ultrasearch update eof
	 
	 if (ultrasearch_categories_id != null){
		query_string += "&categories_id=" + ultrasearch_categories_id;  
	 }
	 if (page != null){
		query_string += "&page=" + page;  
	 }
	 //var query_string = jQuery(this).serialize();
	jQuery.ajax({
		type: "POST",
		dataType: "json",
		url: ultrasearch_resultset_server_path,
		cache :false,
		data: query_string,
		success: 
			function(data) 
			{
				// first clear the page
				jQuery(content_middle).empty();				
				if (data.products.length==0)
				{
					// no results
					jQuery(content_middle).append(ultrasearch_message_no_results);
				}
				else
				{
				   //console.log(data);
				   var listing_products = "";
				   listing_products += '<ul id="product_listing" class="ui-sortable">';
				   jQuery.each(data.products, function(i,item){
					  listing_products += '<li>';
					  listing_products += '<h2><a class="ajax_link" href="'+ item.link_detail +'">'+ item.products_name +'</a></h2>';
					  if (item.products_image){
						listing_products += '<div class="image"><a href="'+ item.link_detail  +'" title="' + item.products_name + '" class="ajax_link"><img src="'+ item.products_image +'"></a></div>';  
					  } else {
						listing_products += '<div class="image"><a href="'+ item.link_detail  +'" title="' + item.products_name + '" class="ajax_link"><div class="no_image"></div></a></div>';
					  }
					  
					  listing_products += '<div class="category"><a href="'+ item.catlink +'" class="ajax_link">'+ item.categories_name +'</a></div>';
					  if (item.price_excluding_vat) {
						listing_products += '<div class="price_excluding_vat">'+ item.price_excluding_vat +'</div>';  
					  }
					  if (item.old_price){
						listing_products += '<div class="old_price">'+ item.old_price +'</div><div class="specials_price">'+ item.special_price +'</div>';
					  }
					  if (item.price) {
						listing_products += '<div class="price">'+ item.price +'</div>';
					  }
					  
					  listing_products += '</li>';
				   });
				   listing_products += '</ul>';
				   
				   //start paginations
				   var pagination_wrapper = '<div id="ajax_pagination"><table id="pagenav_container"><tr>';
				   if (data.pagination.first){
					   pagination_wrapper += '<td class="pagenav_first"><table><tr><td><div id="pagenav_first"><div class="dyna_button"><a href="" id="1" class="ajax_link pagination_button">' + data.pagination.first + '</a></div></div></td></tr></table></td>'; 
				   } else {
					   pagination_wrapper += '<td class="pagenav_first"><table><tr><td>&nbsp</td></tr></table></td>'; 
				   }
				   if (data.pagination.prev){
					   pagination_wrapper += '<td><table><tr><td><div id="pagenav_prev"><div class="dyna_button"><a href="" id="'+ data.pagination.prev +'" class="ajax_link pagination_button">' + data.pagination.prevText + '</a></div></div></td></tr></table></td>'; 
				   } else {
					   pagination_wrapper += '<td><table><tr><td>&nbsp</td></tr></table></td>'; 
				   }
				   
				   if (data.pagination.next){
					   pagination_wrapper += '<td><table><tr><td><div id="pagenav_next"><div class="dyna_button"><a href="" id="'+ data.pagination.next +'" class="ajax_link pagination_button">' + data.pagination.nextText + '</a></div></div></td></tr></table></td>'; 
				   }else {
					   pagination_wrapper += '<td><table><tr><td class="pagenav_next">&nbsp;</td></tr></table></td>';
				   }
				   if (data.pagination.last){
					   pagination_wrapper += '<td><table><tr><td><div id="pagenav_last"><div class="dyna_button"><a href=""  id="'+ data.pagination.totpage +'" class="ajax_link pagination_button">' + data.pagination.last + '</a></div></div></td></tr></table></td>'; 
				   } else {
					   pagination_wrapper += '<td><table><tr><td class="pagenav_last">&nbsp;</td></tr></table></td>'; 
				   }
				   pagination_wrapper += "</tr></table></div>";
				   //eof paginations
				   var content='<div class="tx-multishop-pi1"><div id="tx_multishop_pi1_core">'+ listing_products +'</div></div>'+ pagination_wrapper ;
				   jQuery(content_middle).append(ultrasearcch_resultset_header+content);
					if (typeof Cufon != "undefined") {
						//object exists
						Cufon.refresh();
					}				   
				}
				jQuery("body,html,document").scrollTop(0);
			},
		error:
			function()
			{
				jQuery(content_middle).append("An error occured during processing");
			}
	 });	
	//return false;
} //end function filterproducts() 		


jQuery(document).ready(function()
{
	//count of checked
	function countChecked() {
	  var n = jQuery(".category_products:checked").length;
	  jum_checked = n ;
	  if(n > 0){
		  jQuery("#checkall").attr('checked',true);
	  } else {
		  jQuery("#checkall").attr('checked',false);
	  }
	}
	//ajax checkbox	
	jQuery(".category_products").click(function(){
		filterproducts();
		countChecked();
//		updateComponent();
	});
	jQuery("#checkall").click(function(){
		jqCheckAll2("checkall","category_products");
		
	});	
	function jqCheckAll2( id, name )
	{
	   jQuery("INPUT[@name=" + name + "][type='checkbox']").attr('checked', jQuery('#' + id).is(':checked'));
	   filterproducts();
	}
	//start ajax pagging
	 //Manage click events
	jQuery('#ajax_pagination #pagenav_container  a').on("click", "selector", function(e) {
		e.preventDefault();
		var pageNum = this.id;
		filterproducts(pageNum);
	});
	//ajax pagging EOF
	jQuery(function() { initslider(); });
	function initslider() { 
		var min_slider=jQuery("#Filter_5_Min").val();
		var max_slider=jQuery("#Filter_5_Max").val();
		jQuery("#slider_range").slider({
			orientation: "horizontal",
			range: true,
			min: min_slider,
			max: max_slider,
			values: [min_slider, max_slider],
//			change: function(event, ui) { filterproducts(); updateComponent() },
			change: function(event, ui) { filterproducts(); },
			slide: function(event, ui) {
					jQuery("#Filter_5_Min").val(ui.values[0]);
					jQuery("#Filter_5_Max").val(ui.values[1]);
					}
		});
		jQuery("#Filter_5_Min").val(jQuery("#slider_range").slider("values", 0));
		jQuery("#Filter_5_Max").val(jQuery("#slider_range").slider("values", 1));
		// product attributes slider
		$('.option_slider_range').each(function(){
			var min_value=jQuery(this).parent().next().find(".slider_value_left").val();
			var max_value=jQuery(this).parent().next().next().find(".slider_value_right").val();
			jQuery(this).slider({
				orientation: "horizontal",
				range: true,
				min: 0,
				max: max_value,
				values: [0, max_value],
	//			change: function(event, ui) { filterproducts(); updateComponent() },
				change: function(event, ui) { filterproducts(); },
				slide: function(event, ui) {
						jQuery(this).parent().next().find(".slider_value_left").val(ui.values[0]);
						jQuery(this).parent().next().next().find(".slider_value_right").val(ui.values[1]);
	//					jQuery(this).parent().find(".slider_amount_left").val(ui.values[0]);
	//					jQuery(this).parent().find(".slider_amount_right").val(ui.values[1]);
				}
			});			
		});
	}
	//F5
	 jQuery('#button1').click(function() {
			location.reload();
		});
	 //JIRA 157 ultrasearch update
	 jQuery('.brands-change').bind("change",function() {
		 	filterproducts();
		});
	 jQuery('.slider_value').bind("change",function() {
		 	filterproducts();
		});
	 jQuery('.brands-click').bind("click",function() {
		 	filterproducts();
		}); 
	 jQuery('.options-attributes').bind("",function() {
		 	filterproducts();
		}); 
	//JIRA 157 ultrasearch update eof 
});//end first load 
function updateComponent (value,name) {
	var  href = ultrasearch_formcomponent_server_path; 
	value += query_string;
  	jQuery.ajax({
		type:   "POST",
		url:    href,
		dataType : "jsonp",
	    timeout : 10000,
		cache :false,
		data:"&oneoption_" + name + "=" + value,
		success: function(data){
				//alert("ok");
			  jQuery.each(data.items, function(i,item){
				 //alert(i);
					if (i != data.option){
						if (jQuery("select#" + i).val() == 0){
							jQuery("select#" + i).find("option").remove().end().append("<option value=\"0\">Select Option</option>");
							jQuery.each(data.items[i],function(key,val){
								jQuery("select#" + i).append("<option value="+key+">" + val + "</option>");
								//alert(val);
							});
						}
						
					}
			  });
			  jQuery.each(data.alloption,function(a,b){
					var tes = data.items[b];
					if (data.items[b]==undefined || data.items==undefined){
						jQuery("select#" + b).find("option").remove().end().append("<option value=\"0\">Select Option</option>");	
						jQuery("select#" + b).attr("disabled", "disabled");
					} else {
						jQuery("select#" + b).removeAttr("disabled");
					}
			  });
			}
	});
}

function clear_form_elements(ele) {
    jQuery(ele).find(':input').each(function() {
        switch(this.type) {
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                jQuery(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });  
    location.href=window.location;
}