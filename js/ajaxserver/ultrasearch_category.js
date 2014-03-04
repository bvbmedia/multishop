var query_string = "";
var jum_checked = "";
var checked_category = jQuery(".category_products");
var content_middle = "#content_prod";

function filterproducts ()
		{
			
		query_string = "";
		jQuery(".option-attributes").each(
				function()
				{
					query_string += "&" + this.name + "="+ this.value;
					//alert();
					query_string += "&" + jQuery(this).attr("name")+ "d="+ jQuery('input:radio:checked').val();
				});
		jQuery(".radio-attributes").each(jQuery(this).find("input"),
				function()
				{
					query_string += "&" + jQuery(this).attr("name")+ "d="+ jQuery('input:radio:checked').val();
				});
		
		
		 query_string += "&min=" + jQuery("#Filter_5_Min").val();
		 query_string += "&max=" + jQuery("#Filter_5_Max").val();
		 //var query_string = jQuery(this).serialize();
		 jQuery("#product_listing").after('<div id="content_prod"></div>');
		 jQuery("#product_listing").remove();
			 	jQuery.ajax(
					{
						type: "POST",
					   	url: "index.php?eID=multishop&type=frontend_ultrasearch",
					   	cache :false,
					   	data: query_string,
					   	success: 
						    function(t) 
				          	{
				           		jQuery(content_middle).empty().append(t);
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
					updateComponent();
					
				});
				jQuery("#checkall").click(function(){
					jqCheckAll2("checkall","category_products");
					
				});
				
				function jqCheckAll2( id, name )
				{
				   jQuery("INPUT[@name=" + name + "][type='checkboikx']").attr('checked', jQuery('#' + id).is(':checked'));
				   filterproducts();
				}
			
				//start ajax pagging
				var pages = jQuery("#menu2 li");
				var loading = jQuery("#loading");
				var content = jQuery("#content_products");
				
				//show loading bar
				function showLoading(){
					loading
						.css({visibility:"visible"})
						.css({opacity:"1"})
						.css({display:"block"})
					;
				}
				//hide loading bar
				function hideLoading(){
					loading.fadeTo(1000, 0);
				};
				
			
				//Manage click events
				pages.click(function(){
					//show the loading bar
					showLoading();
					
					//Highlight current page number
					pages.css({"background-color" : ""});
					jQuery(this).css({"background-color" : "yellow"});
					
					//Load content
					var pageNum = this.id;
					var targetUrl = "index.php?eID=multishop&type=frontend_ultrasearch";
					query_string += "&min=" + jQuery("#Filter_5_Min").val();
					query_string += "&max=" + jQuery("#Filter_5_Max").val();
					//content.post(targetUrl, hideLoading);
					jQuery.ajax(
							{
		   						type: "POST",
							   	url: targetUrl,
								cache :false,
							   	data: "&page=" + pageNum + query_string,
							   	success: 
								    function(t) 
						          	{
						           		jQuery(content).empty().append(t);
						           		hideLoading();
						       	   	},
							   	error:
								   	function()
								   	{
								    	jQuery(content).append("An error occured during processing");
								   	}
							 });
					return false;
				});
				//ajax pagging EOF
				
			jQuery(function() { initslider(); });
			function initslider() { 
				jQuery("#slider_range").slider({
					orientation: "horizontal",
					range: true,
					min: 0,
					max: 1000,
					values: [0, 1000],
					change: function(event, ui) { filterproducts(); updateComponent() },
					slide: function(event, ui) {
							jQuery("#Filter_5_Min").val(ui.values[0]);
							jQuery("#Filter_5_Max").val(ui.values[1]);
							}
				});
				jQuery("#Filter_5_Min").val(jQuery("#slider_range").slider("values", 0));
				jQuery("#Filter_5_Max").val(jQuery("#slider_range").slider("values", 1));
			}
			//F5
			 jQuery('#button1').click(function() {
	                location.reload();
	            });
			
		});//end first load 
function updateComponent (value,name) {
	var  href = "index.php?eID=multishop&type=ajaxserver_json"; 
	value += query_string;
  	jQuery.ajax({
                type:   "POST",
                url:    href,
            	cache :false,
				dataType: "json",
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

	