var adminPanelSearch = function () {
    jQuery("li.ms_admin_search > form#ms_admin_top_search > input#ms_admin_skeyword").select2({
        placeholder: MS_ADMIN_PANEL_AUTO_COMPLETE_LABEL,
        minimumInputLength: 1,
        formatResult: function (data) {
            if (data.is_children) {
                if (data.Product == true) {
                    var result_html = '<div class="ajax_products">';
                    result_html += data.Image;
                    result_html += '<div class="ajax_products_name"><a href="' + data.Link + '"><span>' + data.Title + '</span></a></div>';
                    result_html += data.Desc;
                    result_html += data.Price;
                    result_html += '</div>';
                } else {
                    var result_html = '<div class="ajax_items">';
                    if (data.Link) {
                        result_html += '<a href="' + data.Link + '"><span>' + data.Title + '</span></a>';
                    }
                    result_html += '</div>';
                }
                return result_html;
            } else {
                return data.text;
            }
        },
        formatSelection: function (data) {
            if (data.Link) {
                location.href = data.Link;
            }
        },
        context: function (data) {
            return data.page_marker.section
        },
        dropdownCssClass: "bigdrop",
        escapeMarkup: function (m) {
            return m;
        },
        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: MS_ADMIN_PANEL_AUTO_COMPLETE_URL,
            dataType: 'json',
            quietMillis: 100,
            context: 'sss',
            data: function (term, page, context) {
                return { q: term, context: context };
            },
            results: function (data, page) {
                var more = data.page_marker.context.next;
                return {results: data.products, more: more, context: data.page_marker.context};
            }
        }
    });
}
// auto complete eof