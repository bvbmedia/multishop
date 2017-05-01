var shift_key_pressed=false;
$(document).on('keydown', '#ms_admin_skeyword', function(e){
    console.log(e);
});
var adminPanelSearch = function () {
    var select2=jQuery("form#ms_admin_top_search > input#ms_admin_skeyword").select2({
        placeholder: MS_ADMIN_PANEL_AUTO_COMPLETE_LABEL,
        minimumInputLength: 1,
        formatResult: function (data) {
            if (data.is_children) {
                if (data.Product == true) {
                    var result_html = '<div class="ajax_products">';
                    result_html += data.Image;
                    result_html += '<div class="ajax_products_name"><a href="' + data.Link + '" class="linkItem"><span>' + data.Title + '</span></a></div>';
                    result_html += data.Desc;
                    result_html += data.Price;
                    result_html += '</div>';
                } else {
                    var result_html = '<div class="ajax_items">';
                    if (data.HTMLRES!=undefined) {
                        result_html += data.HTMLRES;
                    } else {
                        if (data.Link) {
                            result_html += '<a href="' + data.Link + '" class="linkItem"><span>' + data.Title + '</span></a>';
                        }
                    }
                    result_html += '</div>';
                }
                return result_html;
            } else {
                return data.text;
            }
        },
        /*formatSelection: function (data, container) {
            return false;
            /!*
            //object.preventDefault();
            console.log(object);
            console.log(container);
            jQuery(document).on('mouseup', '#contact_tel', function() {
                alert('aaa');
            });
            //console.log(data);
            //console.log(object);
            //console.log(container);
            //console.log(d);
            *!/
            console.log();
            jQuery('a.contact_tel').mouseup(function() {
                alert('aaa');
            });
            if (data.Link) {
                //location.href = data.Link;
            }
        },*/
        context: function (data) {
            return data.page_marker.section
        },
        dropdownCssClass: "adminpanel-search-bigdrop",
        escapeMarkup: function (m) {
            return m;
        },
        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: MS_ADMIN_PANEL_AUTO_COMPLETE_URL,
            dataType: 'json',
            quietMillis: 100,
            context: '',
            data: function (term, page, context) {
                return { q: term, context: context };
            },
            results: function (data, page) {
                var more = data.page_marker.context.next;
                return {results: data.products, more: more, context: data.page_marker.context};
            }
        }
    }).on("select2-opening", function(){
        $.ajax(MS_ADMIN_PANEL_AUTO_COMPLETE_URL, {
            data: {
                clear_session: true
            },
            dataType: "json"
        });
    }).on("select2-selecting", function(data, options){
        var elem=$(data.srcEvent[0].srcElement);
        var shift_key_pressed=data.keyPress.shiftKey;
        //location.href=$(elem).attr('href');
        var href_elem=$(elem).parent();
        if (!$(href_elem).hasClass('linkItem')) {
            href_elem=$(elem).parentsUntil('.linkItem').parent();
        }
        if ($(href_elem).hasClass('linkItem')) {
            if ($(href_elem).attr('data-require-confirmation')!=undefined) {
                ifConfirm('', $(href_elem).attr('data-require-confirmation') + ' ?', (function(){
                    if (shift_key_pressed) {
                        var win = window.open($(href_elem).attr('href'), '_blank');
                        win.location;
                    } else {
                        location.href = $(href_elem).attr('href');
                    }
                }), (function(){}));
            } else {
                if (shift_key_pressed) {
                    var win = window.open($(href_elem).attr('href'), '_blank');
                    win.location;
                } else {
                    location.href = $(href_elem).attr('href');
                }
            }
        } else {
            var href_elem=$(elem).parentsUntil('.ajaxItem').parent();
            var link_item=$(href_elem).find('a.linkItem');
            if ($(link_item).hasClass('linkItem')) {
                if ($(link_item).attr('data-require-confirmation')!=undefined) {
                    ifConfirm('', $(link_item).attr('data-require-confirmation') + ' ?', (function(){
                        if (shift_key_pressed) {
                            var win = window.open($(href_elem).attr('href'), '_blank');
                            win.location;
                        } else {
                            location.href = $(href_elem).attr('href');
                        }
                    }), (function(){}));
                } else {
                    if (shift_key_pressed) {
                        var win = window.open($(href_elem).attr('href'), '_blank');
                        win.location;
                    } else {
                        location.href = $(link_item).attr('href');
                    }
                }
            }
        }
    });
}
// auto complete eof
