var adminPanelSearch = function () {
    jQuery("li.ms_admin_search > form#ms_admin_top_search > input#ms_admin_skeyword").select2({
        placeholder:"Search",
        minimumInputLength:3,
        formatResult:function(data){
            if (data.is_children) {
                if (data.Product) {
                    var result_html='<div class="ajax_products">';
                    result_html+=data.Image;
                    result_html+='<div class="ajax_products_name"><a href="'+data.Link+'"><span>'+data.Title+'</span></a></div>';
                    result_html+=data.Desc;
                    result_html+=data.Price;
                    result_html+='</div>';
                } else {
                    var result_html='<div class="ajax_items">';
                    if (data.Link) {
                        result_html+='<a href="'+data.Link+'"><span>'+data.Title+'</span></a>';
                    }
                    result_html+='</div>';
                }
                return result_html;
            } else {
                return data.text;
            }
        },
        formatSelection:function(data){
            location.href=data.Link;
        },
        dropdownCssClass:"bigdrop",
        escapeMarkup: function (m) { return m; },
        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url:MS_ADMIN_PANEL_AUTO_COMPLETE_URL,
            dataType:'json',
            quietMillis: 100,
            data: function (term) {
                return { q:term };
            },
            results: function (data) {
                //console.log(data);
                return {results:data.products};
            }
        }
    });
}
// auto complete eof