// prepare the form when the DOM is ready
jQuery(document).ready(function ($) {
    var options = {
        target: '#output2',   // target element(s) to be updated with server response
        beforeSubmit: showRequest,  // pre-submit callback
        success: showResponse, // post-submit callback
        resetForm: true,
        clearForm: true,
        success: processMsFrontUltraSearchJson,
        // other available options:
        url: ultrasearch_resultset_server_path + "&tx_multishop_pi1[ultrasearch_hash]=" + ultrasearch_fields,         // override for form's 'action' attribute
        type: 'post', // 'get' or 'post', override for form's 'method' attribute
        dataType: 'json'        // 'xml', 'script', or 'json' (expected server response type)
        //clearForm: true        // clear all form fields after successful submit
        //resetForm: true        // reset the form after successful submit

        // $.ajax options can be used here too, for example:
        //timeout:   3000
    };

    $(document).on('click', '#usreset', function () {
        var urlloc = window.location;
        var redirect_url = urlloc.origin;
        if (urlloc.pathname != undefined) {
            redirect_url += urlloc.pathname;
        }
        if (urlloc.search != undefined) {
            redirect_url += urlloc.search;
        }
        location.href = redirect_url;
    });
    // bind to the form's submit event
    $('#msFrontUltrasearchForm').submit(function () {
        // inside event callbacks 'this' is the DOM element so we first
        // wrap it in a jQuery object and then invoke ajaxSubmit
        $(content_middle).html(jQuery('<p />').attr("id", "msFrontUltraSearchPreLoader").html('<div></div><span>One moment please...</span>'));

        $(this).ajaxSubmit(options);
        return false;
    });
    function processMsFrontUltraSearchJson(data) {
        $("#msFrontUltrasearchForm").html("");
        $("#msFrontUltrasearchForm").dform(data.formFields);
        // make selected checkboxes bold
//		$("#msFrontUltrasearchForm :checkbox:checked").next().find(".title").css("font-weight","bold");
        $("#msFrontUltrasearchForm :checkbox:checked").next().find(".title").css("font-weight", "bold");
        // make selected checkboxes bold
//		$("#msFrontUltrasearchForm :checkbox:not(:checked)").next().find(".title").css("font-weight","");
        $("#msFrontUltrasearchForm :checkbox:not(:checked)").next().find(".title").css("font-weight", "");
        // add wrappers
//		$('#msFrontUltrasearchForm .ui-dform-checkboxes input[type="checkbox"]').parent().wrapAll('<div></div>');
        // update resultset
        // first clear the page
        $(content_middle).empty();
        if (data.resultSet.products.length == 0) {
            // no results
            $(content_middle).append(ultrasearch_message_no_results);
        } else {
            //console.log(data);
            var listing_products = "";
            listing_products += '<div class="product_listing_ultrasearch_wrapper"><div class="product_listing" class="ui-sortable row">';
            var colCounter = 0;
            var colClass="";
            $.each(data.resultSet.products, function (i, item) {
                colCounter++;
                switch(colCounter) {
                    case 1:
                        colClass='leftItem';
                        break;
                    case 2:
                        colClass='middleItem';
                        break;
                    case 3:
                        colClass='rightItem';
                        colCounter=0;
                        break;
                }
                listing_products += '<div class="'+colClass+' col-sm-4"><div class="listing_item">';
                if (item.products_image) {
                    listing_products += '<div class="image"><a href="' + item.link_detail + '" title="' + item.products_name + '" class="ajax_link"><img src="' + item.products_image + '"></a></div>';
                } else {
                    listing_products += '<div class="image"><a href="' + item.link_detail + '" title="' + item.products_name + '" class="ajax_link"><div class="no_image"></div></a></div>';
                }
                listing_products += '<strong><a class="ajax_link" href="' + item.link_detail + '">' + item.products_name + '</a></strong>';
                listing_products += '<div class="category"><a href="' + item.catlink + '" class="ajax_link">' + item.categories_name + '</a></div>';
                listing_products += '<a href="#" rel="51" class="add_cart_item_listing"><span></span></a>';
                listing_products += '<div class="products_price">';
                if (item.price_excluding_vat) {
                    listing_products += '<div class="price_excluding_vat">' + item.price_excluding_vat + '</div>';
                }
                if (item.old_price) {
                    listing_products += '<div class="old_price">' + item.old_price + '</div><div class="specials_price">' + item.special_price + '</div>';
                }
                if (item.price) {
                    listing_products += '<div class="price">' + item.price + '</div>';
                }
                listing_products += '</div></div></div>';
            });
            listing_products += '</div>';
            // PAGINATION
            var pagination_wrapper = '<div id="pagenav_container_list_wrapper"><ul id="pagenav_container_list">';
            if (data.resultSet.pagination.prev) {
                pagination_wrapper += '<li class="pagenav_first"><div class="dyna_button"><a href="" id="1" class="ajax_link pagination_button">' + data.resultSet.pagination.firstText + '</a></div></li>';
            } else {
                pagination_wrapper += '<li class="pagenav_first"><div class="dyna_button"><span>' + data.resultSet.pagination.firstText + '</span></div></li>';
            }
            if (data.resultSet.pagination.prev) {
                pagination_wrapper += '<li class="pagenav_previous"><div class="dyna_button"><a href="" id="' + data.resultSet.pagination.prev + '" class="ajax_link pagination_button">' + data.resultSet.pagination.prevText + '</a></div></li>';
            } else {
                pagination_wrapper += '<li class="pagenav_previous"><div class="dyna_button"><span>' + data.resultSet.pagination.prevText + '</span></div></li>';
            }
            // ITERATE PAGE NUMBERS
            pagination_wrapper += '<li class="pagenav_number"><ul>';
            $.each(data.resultSet.pagination.page_number, function (idx, pn) {
                if (pn.link > 0) {
                    pagination_wrapper += '<li><div class="dyna_button"><a href="" id="' + pn.number + '" class="ajax_link pagination_button">' + pn.number + '</a></div></li>';
                } else {
                    pagination_wrapper += '<li><div class="dyna_button"><span>' + pn.number + '</span></div></li>';
                }
            });
            pagination_wrapper += '</ul></li>';
            // ITERATE PAGE NUMBERS EOF
            if (data.resultSet.pagination.next) {
                pagination_wrapper += '<li class="pagenav_next"><div class="dyna_button"><a href="" id="' + data.resultSet.pagination.next + '" class="ajax_link pagination_button">' + data.resultSet.pagination.nextText + '</a></div></li>';
            } else {
                pagination_wrapper += '<li class="pagenav_next"><div class="dyna_button"><span>' + data.resultSet.pagination.nextText + '</span></div></li>';
            }
            if (data.resultSet.pagination.current_p < data.resultSet.pagination.totpage) {
                pagination_wrapper += '<li class="pagenav_last"><div class="dyna_button"><a href="" id="' + data.resultSet.pagination.totpage + '" class="ajax_link pagination_button">' + data.resultSet.pagination.lastText + '</a></div></li>';
            } else {
                pagination_wrapper += '<li class="pagenav_last"><div class="dyna_button"><span>' + data.resultSet.pagination.lastText + '</span></div></li>';
            }
            pagination_wrapper += "</ul></div>";
            // PAGINATION EOF
            var content = '<div class="tx-multishop-pi1"><div id="tx_multishop_pi1_core">' + listing_products + '</div></div>' + pagination_wrapper;
            $(content_middle).append(ultrasearcch_resultset_header + content);
            if (typeof Cufon != "undefined") {
                //object exists
                Cufon.refresh();
            }
        }
//		$("body,html,document").scrollTop(0);
    }

    $(document).on('change', '#msFrontUltrasearchForm', function () {
        $('#msFrontUltrasearchForm #pageNum').val('0');
        $('#msFrontUltrasearchForm').submit();
    });
    $(document).on('click', '#pagenav_container_list a', function (e) {
        e.preventDefault();
        var pageNum = this.id;
        $('#msFrontUltrasearchForm #pageNum').val(pageNum);
        $('#msFrontUltrasearchForm').submit();
        $("body,html,document").scrollTop(0);
    });
    if (location.hash) {
        var hash = location.hash;
        if (hash) {
            // set the hash value to hidden field and use that to retrieve the post from the back button instead
            $('#locationHash').val(hash.replace(/^#/, ''));
        }
    }
    $('#msFrontUltrasearchForm').submit();
});

// pre-submit callback
function showRequest(formData, jqForm, options) {
    formData2 = formData;

    if (typeof(formData2[0]) != "undefined" && formData2[0]['name'] != 'locationHash') {
        // formData is an array; here we use $.param to convert it to a string to display it
        // but the form plugin does this for you automatically when it submits the data
        var queryString = jQuery.param(formData);
        var queryString2 = jQuery.param(formData2);
        if (queryString2 != '=undefined') {
            // set hash so users can press back button
            if (queryString2 != 'undefined') {
                location.hash = '#' + encodeURIComponent(queryString2);
            }
        }
    }
}

// post-submit callback
function showResponse(responseText, statusText, xhr, $form) {
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
