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
        $(content_middle).html(jQuery('<p />').attr("id", "msFrontUltraSearchPreLoader").html('<div></div><span class="msFrontOneMomentPlease"></span>'));

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
            listing_products += '<div class="product_listing_ultrasearch_wrapper"><div class="product_listing ui-sortable row">';
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
                var admin_icon = '';
                if (item.admin_edit_product_button) {
                    admin_icon += '<div class="admin_menu">';
                    admin_icon += '<a href="' + item.admin_edit_product + '" class="admin_menu_edit"><i class="fa fa-pencil"></i></a>';
                    admin_icon += '<a href="' + item.admin_delete_product + '" class="admin_menu_remove" title="Remove"><i class="fa fa-trash-o"></i></a>';
                    admin_icon += '</div>';
                }
                listing_products += '<strong><a class="ajax_link" href="' + item.link_detail + '">' + item.products_name + '</a>' + admin_icon + '</strong>';
                listing_products += '<div class="category"><a href="' + item.catlink + '" class="ajax_link">' + item.categories_name + '</a></div>';
                listing_products += '<div class="visible-lg msFrontAddToCartBtn"><a href="#" rel="' + item.products_id + '" class="add_cart_item_listing"><span></span></a></div>';
                listing_products += '<div class="visible-xs visible-sm visible-md msFrontAddToCartBtn"><a href="' + item.link_add_to_cart + '"><span>winkelwagen</span></a></div>';
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
                listing_products += '</div>';
                if (item.shipping_costs_popup==1) {
                    listing_products += '<div class="shipping_cost_popup_link_wrapper"><a href="#" class="show_shipping_cost_table" class="btn btn-primary" data-toggle="modal" data-target="#shippingCostsModal" data-productid="' + item.products_id + '"><span>' + data.resultSet.labels.shipping_costs + '</span></a></div>';
                }
                listing_products+='</div></div>';
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
            if (data.resultSet.pagination.page_number!=undefined) {
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
            }
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
            var content = '';
            if (data.resultSet.current_categories!=undefined) {
                if (data.resultSet.current_categories.name!="" && data.resultSet.current_categories.name!=null) {
                    content += '<h1>' + data.resultSet.current_categories.name + '</h1>';
                }
                if (data.resultSet.categories_description.header != "") {
                    content += '<div class="category_header_content ">' + data.resultSet.categories_description.header + '</div>';
                }
            }
            content += '<div class="tx-multishop-pi1"><div id="tx_multishop_pi1_core">' + listing_products + '</div></div>' + pagination_wrapper;
            if (data.resultSet.categories_description!=undefined) {
                if (data.resultSet.categories_description.footer != "") {
                    content += '<div class="category_footer_content ">' + data.resultSet.categories_description.footer + '</div>';
                }
            }
            if (shipping_costs_overview) {
                content += '<div class="modal" id="shippingCostsModal" tabindex="-1" role="dialog" aria-labelledby="shippingCostModalTitle" aria-hidden="true">';
                content += '<div class="modal-dialog">';
                content += '<div class="modal-content">';
                content += '<div class="modal-header">';
                content += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                content += '<h4 class="modal-title" id="shippingCostModalTitle">' + data.resultSet.labels.shipping_costs + '</h4>';
                content += '</div>';
                content += '<div class="modal-body"></div>';
                content += '<div class="modal-footer">';
                content += '<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>';
                content += '</div>';
                content += '</div>';
                content += '</div>';
                content += '</div>';
            }
            $(content_middle).append(ultrasearcch_resultset_header + content);
            if (shipping_costs_overview) {
                $('#shippingCostsModal').modal({
                    show:false,
                    backdrop:false
                });
                $('#shippingCostsModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget) // Button that triggered the modal
                    var product_id = button.data('productid') // Extract info from data-* attributes
                    var modalBox = $(this);
                    modalBox.find('.modal-body').empty();
                    if (modalBox.find('.modal-body').html()=='') {
                        jQuery.ajax({
                            url: ultrasearch_shipping_costs_review_url,
                            data: 'tx_multishop_pi1[pid]=' + product_id + '&tx_multishop_pi1[qty]=1',
                            type: 'post',
                            dataType: 'json',
                            success: function (j) {
                                if (j) {
                                    var shipping_cost_popup='<div class="product_shippingcost_popup_wrapper">';
                                    shipping_cost_popup+='<div class="product_shippingcost_popup_header">' + labels_product_shipping_and_handling_cost_overview + '</div>';
                                    shipping_cost_popup+='<div class="product_shippingcost_popup_table_wrapper">';
                                    shipping_cost_popup+='<table id="product_shippingcost_popup_table" class="table table-striped">';
                                    shipping_cost_popup+='<tr>';
                                    shipping_cost_popup+='<td colspan="3" class="product_shippingcost_popup_table_product_name">' + j.products_name + '</td>';
                                    shipping_cost_popup+='</tr>';
                                    shipping_cost_popup+='<tr>';
                                    shipping_cost_popup+='<td class="product_shippingcost_popup_table_left_col">' + labels_deliver_to + '</td>';
                                    shipping_cost_popup+='<td class="product_shippingcost_popup_table_center_col">' + labels_shipping_and_handling_cost_overview + '</td>';
                                    shipping_cost_popup+='<td class="product_shippingcost_popup_table_right_col">' + labels_deliver_by + '</td>';
                                    shipping_cost_popup+='</tr>';
                                    $.each(j.shipping_costs_display, function(shipping_method, shipping_data) {
                                        $.each(shipping_data, function(country_iso_nr, shipping_cost){
                                            shipping_cost_popup+='<tr>';
                                            shipping_cost_popup+='<td class="product_shippingcost_popup_table_left_col">' + j.deliver_to[shipping_method][country_iso_nr] + '</td>';
                                            shipping_cost_popup+='<td class="product_shippingcost_popup_table_center_col">' + shipping_cost + '</td>';
                                            shipping_cost_popup+='<td class="product_shippingcost_popup_table_right_col">' + j.deliver_by[shipping_method][country_iso_nr] + '</td>';
                                            shipping_cost_popup+='</tr>';
                                        });
                                    });
                                    /*$.each(j.shipping_costs_display, function(country_iso_nr, shipping_cost) {
                                        shipping_cost_popup+='<tr>';
                                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_left_col">' + j.deliver_to[country_iso_nr] + '</td>';
                                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_center_col">' + shipping_cost + '</td>';
                                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_right_col">' + j.deliver_by[country_iso_nr] + '</td>';
                                        shipping_cost_popup+='</tr>';
                                    });*/
                                    if (j.delivery_time!='e') {
                                        shipping_cost_popup+='<tr>';
                                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_left_col"><strong>' + labels_delivery_time + '</strong></td>';
                                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_left_col" colspan="2">' + j.delivery_time + '</td>';
                                        shipping_cost_popup+='</tr>';
                                    }
                                    shipping_cost_popup+='</table>';
                                    shipping_cost_popup+='</div>';
                                    shipping_cost_popup+='</div>';
                                    //modalBox.find(\'.modal-title\').html('.$this->pi_getLL('product_shipping_and_handling_cost_overview').');
                                    modalBox.find('.modal-body').html(shipping_cost_popup);
                                    //msDialog("'.$this->pi_getLL('shipping_costs').'", shipping_cost_popup, 650);
                                }
                            }
                        });
                    }
                });
            }
            if (typeof Cufon != "undefined") {
                //object exists
                Cufon.refresh();
            }
        }
//		$("body,html,document").scrollTop(0);
    }

    /*$(document).on("click", ".show_shipping_cost_table", function(e) {
        e.preventDefault();
        var pid=jQuery(this).attr("rel");
        jQuery.ajax({
            url: ultrasearch_shipping_costs_review_url,
            data: 'tx_multishop_pi1[pid]=' + pid + '&tx_multishop_pi1[qty]=1',
            type: 'post',
            dataType: 'json',
            success: function (j) {
                if (j) {
                    var shipping_cost_popup='<div class="product_shippingcost_popup_wrapper">';
                    shipping_cost_popup+='<div class="product_shippingcost_popup_header">' + labels_product_shipping_and_handling_cost_overview + '</div>';
                    shipping_cost_popup+='<div class="product_shippingcost_popup_table_wrapper">';
                    shipping_cost_popup+='<table id="product_shippingcost_popup_table">';
                    shipping_cost_popup+='<tr>';
                    shipping_cost_popup+='<td colspan="3" class="product_shippingcost_popup_table_product_name">' + j.products_name + '</td>';
                    shipping_cost_popup+='</tr>';
                    shipping_cost_popup+='<tr>';
                    shipping_cost_popup+='<td class="product_shippingcost_popup_table_left_col">' + labels_deliver_to + '</td>';
                    shipping_cost_popup+='<td class="product_shippingcost_popup_table_center_col">' + labels_shipping_and_handling_cost_overview + '</td>';
                    shipping_cost_popup+='<td class="product_shippingcost_popup_table_right_col">' + labels_deliver_by + '</td>';
                    shipping_cost_popup+='</tr>';
                    $.each(j.shipping_costs_display, function(country_iso_nr, shipping_cost) {
                        shipping_cost_popup+='<tr>';
                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_left_col">' + j.deliver_to[country_iso_nr] + '</td>';
                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_center_col">' + shipping_cost + '</td>';
                        shipping_cost_popup+='<td class="product_shippingcost_popup_table_right_col">' + j.deliver_by[country_iso_nr] + '</td>';
                        shipping_cost_popup+='</tr>';
                    });
                    shipping_cost_popup+='</table>';
                    shipping_cost_popup+='</div>';
                    shipping_cost_popup+='</div>';
                    msDialog(labels_shipping_costs, shipping_cost_popup, 650);
                }
            }
        });
    });*/
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
