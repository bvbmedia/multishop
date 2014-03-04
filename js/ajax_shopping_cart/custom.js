jQuery(document).ready(function(){ 

	jQuery("#basketItemsWrap li:first").hide();

	jQuery(".productPriceWrapRight a img").click(function() {
		var productIDValSplitter 	= (this.id).split("_");
		var productIDVal 			= productIDValSplitter[1];
		
		var productX 		= jQuery("#productImageWrapID_" + productIDVal).offset().left;
		var productY 		= jQuery("#productImageWrapID_" + productIDVal).offset().top;
		
		if( jQuery("#productID_" + productIDVal).length > 0){
			var basketX 		= jQuery("#productID_" + productIDVal).offset().left;
			var basketY 		= jQuery("#productID_" + productIDVal).offset().top;			
		} else {
			var basketX 		= jQuery("#basketTitleWrap").offset().left;
			var basketY 		= jQuery("#basketTitleWrap").offset().top;
		}
		
		var gotoX 			= basketX - productX;
		var gotoY 			= basketY - productY;
		
		var newImageWidth 	= jQuery("#productImageWrapID_" + productIDVal).width() / 3;
		var newImageHeight	= jQuery("#productImageWrapID_" + productIDVal).height() / 3;
		
		jQuery("#productImageWrapID_" + productIDVal + " img")
		.clone()
		.prependTo("#productImageWrapID_" + productIDVal)
		.css({'position' : 'absolute'})
		.animate({opacity: 0.4}, 100 )
		.animate({opacity: 0.1, marginLeft: gotoX, marginTop: gotoY, width: newImageWidth, height: newImageHeight}, 1200, function() {
																																																																										  			jQuery(this).remove();
	
			jQuery("#notificationsLoader").html('<img src="images/loader.gif">');
		
			jQuery.ajax({  
				type: "post",  
				url: "index.php?eID=multishop&type=products_to_basket",  
				/*data: { productID: productIDVal, 
						action: "addToBasket",
						products_id:productIDVal,
						quantity:jQuery("#quantity").val(),
						price:jQuery("#price_default").val(),
						price:jQuery("input[name=attributes]").val()
						},  */
				data: jQuery("form[name=shopping_cart]").serialize(),  
				success: function(theResponse) {
					
					/*if( jQuery("#productID_" + productIDVal).length > 0){
						jQuery("#productID_" + productIDVal).animate({ opacity: 0 }, 500);
						jQuery("#productID_" + productIDVal).before(theResponse).remove();
						jQuery("#productID_" + productIDVal).animate({ opacity: 0 }, 500);
						jQuery("#productID_" + productIDVal).animate({ opacity: 1 }, 500);
						jQuery("#notificationsLoader").empty();
						
					} else {
						jQuery("#basketItemsWrap li:first").before(theResponse);
						jQuery("#basketItemsWrap li:first").hide();
						jQuery("#basketItemsWrap li:first").show("slow");  
						jQuery("#notificationsLoader").empty();			
					}*/
					jQuery("#content-basket").html(theResponse);
					
				}  
			});  
		
		});
		
	});
	
	
	
	jQuery("#basketItemsWrap li img").live("click", function(event) { 
		var productIDValSplitter 	= (this.id).split("_");
		var productIDVal 			= productIDValSplitter[1];	

		jQuery("#notificationsLoader").html('<img src="images/loader.gif">');
	
		jQuery.ajax({  
			type: "POST",  
			url: "inc/functions.php",  
			data: { productID: productIDVal, action: "deleteFromBasket"},  
			success: function(theResponse) {
				
				jQuery("#productID_" + productIDVal).hide("slow",  function() {jQuery(this).remove();});
				jQuery("#notificationsLoader").empty();
			
			}  
		});  
		
	});

});
