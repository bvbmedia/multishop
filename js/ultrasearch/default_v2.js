// prepare the form when the DOM is ready 
$(document).ready(function() { 
    var options = { 
        target:        '#output2',   // target element(s) to be updated with server response 
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse, // post-submit callback 
		resetForm: true,
 		clearForm: true,
		success:   		processMsFrontUltraSearchJson,
        // other available options: 
        url:       ultrasearch_resultset_server_path+"&tx_multishop_pi1[ultrasearch_hash]="+ultrasearch_fields,         // override for form's 'action' attribute 
        type:      'post', // 'get' or 'post', override for form's 'method' attribute 
        dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true        // clear all form fields after successful submit 
        //resetForm: true        // reset the form after successful submit 
 
        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    }; 
 
    // bind to the form's submit event 
    $('#msFrontUltrasearchForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
		$(content_middle).html('<p class="loader" id="loading_mask_loader"><div></div>Een moment geduld aub...</p>');
        return false; 
    }); 
	function processMsFrontUltraSearchJson(data) { 
		$("#msFrontUltrasearchForm").html("");
		$("#msFrontUltrasearchForm").dform(data.formFields);
		// make selected checkboxes bold
//		$("#msFrontUltrasearchForm :checkbox:checked").next().find(".title").css("font-weight","bold");
		$("#msFrontUltrasearchForm :checkbox:checked").next().find(".title").css("font-weight","bold");
		// make selected checkboxes bold
//		$("#msFrontUltrasearchForm :checkbox:not(:checked)").next().find(".title").css("font-weight","");		
		$("#msFrontUltrasearchForm :checkbox:not(:checked)").next().find(".title").css("font-weight","");		
		// add wrappers
//		$('#msFrontUltrasearchForm .ui-dform-checkboxes input[type="checkbox"]').parent().wrapAll('<div></div>');
		// update resultset
		// first clear the page
		$(content_middle).empty();				
		if (data.resultSet.products.length==0)
		{
			// no results
			$(content_middle).append(ultrasearch_message_no_results);
		}
		else
		{
		   //console.log(data);
		   var listing_products = "";
		   listing_products += '<ul id="product_listing" class="ui-sortable">';
		   $.each(data.resultSet.products, function(i,item){
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
		   if (data.resultSet.pagination.first){
			   pagination_wrapper += '<td class="pagenav_first"><table><tr><td><div id="pagenav_first"><div class="dyna_button"><a href="" id="1" class="ajax_link pagination_button">' + data.resultSet.pagination.first + '</a></div></div></td></tr></table></td>'; 
		   } else {
			   pagination_wrapper += '<td class="pagenav_first"><table><tr><td>&nbsp</td></tr></table></td>'; 
		   }
		   if (data.resultSet.pagination.prev){
			   pagination_wrapper += '<td><table><tr><td><div id="pagenav_prev"><div class="dyna_button"><a href="" id="'+ data.resultSet.pagination.prev +'" class="ajax_link pagination_button">' + data.resultSet.pagination.prevText + '</a></div></div></td></tr></table></td>'; 
		   } else {
			   pagination_wrapper += '<td><table><tr><td>&nbsp</td></tr></table></td>'; 
		   }
		   
		   if (data.resultSet.pagination.next){
			   pagination_wrapper += '<td><table><tr><td><div id="pagenav_next"><div class="dyna_button"><a href="" id="'+ data.resultSet.pagination.next +'" class="ajax_link pagination_button">' + data.resultSet.pagination.nextText + '</a></div></div></td></tr></table></td>'; 
		   }else {
			   pagination_wrapper += '<td><table><tr><td class="pagenav_next">&nbsp;</td></tr></table></td>';
		   }
		   if (data.resultSet.pagination.last){
			   pagination_wrapper += '<td><table><tr><td><div id="pagenav_last"><div class="dyna_button"><a href=""  id="'+ data.resultSet.pagination.totpage +'" class="ajax_link pagination_button">' + data.resultSet.pagination.last + '</a></div></div></td></tr></table></td>'; 
		   } else {
			   pagination_wrapper += '<td><table><tr><td class="pagenav_last">&nbsp;</td></tr></table></td>'; 
		   }
		   pagination_wrapper += "</tr></table></div>";
		   //eof paginations
		   var content='<div class="tx-multishop-pi1"><div id="tx_multishop_pi1_core">'+ listing_products +'</div></div>'+ pagination_wrapper ;
		   $(content_middle).append(ultrasearcch_resultset_header+content);
			if (typeof Cufon != "undefined") {
				//object exists
				Cufon.refresh();
			}				   
		}
		$("body,html,document").scrollTop(0);
	}
	$('#msFrontUltrasearchForm').change(function(){
	   $('#msFrontUltrasearchForm').submit();
	});
	$('#ajax_pagination #pagenav_container  a').on("click", "selector", function(e) {
		e.preventDefault();
		var pageNum = this.id;
		$('#msFrontUltrasearchForm #pageNum').val(pageNum);
		$('#msFrontUltrasearchForm').submit();
	});		
   $('#msFrontUltrasearchForm').submit();
}); 
 
// pre-submit callback 
function showRequest(formData, jqForm, options) { 
    // formData is an array; here we use $.param to convert it to a string to display it 
    // but the form plugin does this for you automatically when it submits the data 
    var queryString = $.param(formData); 
 
    // jqForm is a jQuery object encapsulating the form element.  To access the 
    // DOM element for the form do this: 
    // var formElement = jqForm[0]; 
 
//    alert('About to submit: \n\n' + queryString); 
 
    // here we could return false to prevent the form from being submitted; 
    // returning anything other than false will allow the form submit to continue 
    return true; 
} 
 
// post-submit callback 
function showResponse(responseText, statusText, xhr, $form)  { 
    // for normal html responses, the first argument to the success callback 
    // is the XMLHttpRequest object's responseText property 
 
    // if the ajaxSubmit method was passed an Options Object with the dataType 
    // property set to 'xml' then the first argument to the success callback 
    // is the XMLHttpRequest object's responseXML property 
 
    // if the ajaxSubmit method was passed an Options Object with the dataType 
    // property set to 'json' then the first argument to the success callback 
    // is the json data object returned by the server 
 
//    alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +   '\n\nThe output div should have already been updated with the responseText.'); 
} 
