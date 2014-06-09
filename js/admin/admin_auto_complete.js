function format(products) {
    var result_listing_html='<ul>';
    $.each(products.headers, function(i, val){
        result_listing_html+='<li class="ui-category">';
        result_listing_html+=val.Title;
        result_listing_html+='</li>';
        switch (i) {
            case 'admin_settings':
                if (products.listing.admin_settings.length > 0) {
                    $.each(products.listing.admin_settings, function(x, lval){
                        if (!lval.Link) {
                            result_listing_html+='<li class="ui-category">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</li>';
                        } else {
                            result_listing_html+='<li class="ui-menu-item">';
                            result_listing_html+='<a href="' + lval.Link + '"><div class="single_row">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</div></a>';
                            result_listing_html+='</li>';
                        }
                    });
                }
                break;
            case 'categories':
                if (products.listing.categories.length > 0) {
                    $.each(products.listing.categories, function(x, lval){
                        if (!lval.Link) {
                            result_listing_html+='<li class="ui-category">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</li>';
                        } else {
                            result_listing_html+='<li class="ui-menu-item">';
                            result_listing_html+='<a href="' + lval.Link + '"><div class="single_row">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</div></a>';
                            result_listing_html+='</li>';
                        }
                    });
                }
                break;
            case 'cms':
                if (products.listing.cms.length > 0) {
                    $.each(products.listing.cms, function(x, lval){
                        if (!lval.Link) {
                            result_listing_html+='<li class="ui-category">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</li>';
                        } else {
                            result_listing_html+='<li class="ui-menu-item">';
                            result_listing_html+='<a href="' + lval.Link + '"><div class="single_row">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</div></a>';
                            result_listing_html+='</li>';
                        }
                    });
                }
                break;
            case 'products':
                if (products.listing.products.length > 0) {
                    $.each(products.listing.products, function(x, lval){
                        if (!lval.Link) {
                            result_listing_html+='<li class="ui-category">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</li>';
                        } else {
                            result_listing_html+='<li class="ui-menu-item">';
                            result_listing_html+='<a href="' + lval.Link + '"><div class="single_row">';
                            result_listing_html+=lval.Title;
                            result_listing_html+='</div></a>';
                            result_listing_html+='</li>';
                        }
                    });
                }
                break;
        }
    });
    result_listing_html+='</ul>';
    //console.log(products);
    return result_listing_html;
}
jQuery(document).ready(function($){
    var sendData;
    $("#ms_admin_skeyword").bind("focus", function () {
        $("#ms_admin_us_page").val(0);
    });
    $(document).on("click", "#ms_admin_skeyword", function () {
        $("#ms_admin_skeyword").val("");
    });
    $(document).on("keydown", "#ms_admin_skeyword", function (e) {
        // dont process special keys
        var skipKeys = [ 13, 38, 40, 37, 39, 27, 32, 17, 18, 9, 16, 20, 91, 93, 8, 36, 35, 45, 46, 33, 34, 144, 145, 19 ];
        if ($.inArray(e.keyCode, skipKeys) != -1) {
            sendData = false;
        } else {
            sendData = true;
        }
        if (sendData) {
            $("#ms_admin_skeyword").select2({
                minimumInputLength: 1,
                ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                    url: MS_ADMIN_PANEL_AUTO_COMPLETE_URL,
                    type: 'post',
                    dataType: 'json',
                    quietMillis: 50,
                    allowClear: true,
                    data: function (term, page) {
                        console.log(term);
                        return { q: term, page: $("#ms_admin_us_page").val() };
                    },
                    results: function (data) {
                        console.log(data);
                        return {results: data.products, more: false};
                    }
                },
                initSelection: function(element, callback) {
                    // the input tag has a value attribute preloaded that points to a preselected movie's id
                    // this function resolves that id attribute to an object that select2 can render
                    // using its formatResult renderer - that way the movie name is shown preselected
                    var term=$(element).val();
                    if (term!=="") {
                        $.ajax(MS_ADMIN_PANEL_AUTO_COMPLETE_URL, {
                            data: {
                                q: term, page: $("#ms_admin_us_page").val()
                            },
                            dataType: "json"
                        }).done(function(data) { callback(data.products); });
                    }
                },
                formatSelection: format,
                formatResult: format,
                dropdownCssClass: "bigdrop",
                escapeMarkup: function (m) { return m; }
            });
        }
    });
});
// auto complete eof