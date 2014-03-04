jQuery(document).ready(function(){
	//References
	var pages = jQuery("#menu li");
	var loading = jQuery("#loading");
	var content = jQuery("#product_listing");
	
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
		pages.css({'background-color' : ''});
		jQuery(this).css({'background-color' : 'yellow'});

		//Load content
		var pageNum = this.id;
		var targetUrl = "index.php?eID=multishop&type=ajaxserver_category_listing&page=" + pageNum + "&" + query_string;
		content.load(targetUrl, hideLoading);
	});
	
	//default - 1st page
	jQuery("#1").css({'background-color' : 'yellow'});
	var targetUrl = "index.php?eID=multishop&type=ajaxserver_category_listing&page=1&" + query_string;
	showLoading();
	content.load(targetUrl, hideLoading);
});