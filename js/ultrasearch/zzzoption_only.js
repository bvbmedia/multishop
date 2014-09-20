var query_string = "";
var jum_checked = "";
var content_middle = "#content";
var url_json = "index.php?eID=multishop&type=ajaxserver_json"; 
var url_products = "index.php?eID=multishop&type=option_only";

function updateComponent (value,name) {
	jQuery("#content-special").remove();
	var  href = url_json;
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
			          			//jQuery("select#" + b).attr("disabled", "disabled");
			          		} else {
			          			//jQuery("select#" + b).removeAttr("disabled");
			          		}
			          });
			        }
	        });
  }

function filterproducts ()
{
	query_string = "";
	jQuery(".option-attributes").each(
		function()
		{
			query_string += "&" + this.name + "="+ this.value;
		});

 //var query_string = jQuery(this).serialize();
	 	jQuery.ajax(
			{
				type: "POST",
			   	url: url_products,
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

	