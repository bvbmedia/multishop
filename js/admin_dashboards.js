jQuery(document).ready(function($){
    $(document).on('click', '#addWidget', function() {
        var dashboard_id=$('#dashboardWrapper').attr('data-dashboard-id');
        $('#dashboardModal').find('.modal-title').html(admin_label_add_widget);
        var widgets_list={};
        widgets_list=[
            {'key': 'customersPerMonth', 'title':'customersPerMonth'},
            {'key': 'google_chart_carts', 'title':'google_chart_carts'},
            {'key': 'google_chart_customers', 'title':'google_chart_customers'},
            {'key': 'google_chart_orders', 'title':'google_chart_orders'},
            {'key': 'ordersLatest', 'title':'ordersLatest'},
            {'key': 'ordersPerMonth', 'title':'ordersPerMonth'},
            {'key': 'referrerToplist', 'title':'referrerToplist'},
            {'key': 'searchKeywordsToplist', 'title':'searchKeywordsToplist'},
            {'key': 'turnoverPerMonth', 'title':'turnoverPerMonth'},
            {'key': 'turnoverPerProduct', 'title':'turnoverPerProduct'},
            {'key': 'turnoverPerYear', 'title':'turnoverPerYear'}
        ];
        var add_widget='<ul class="widgetsListWrapper">';
        $(widgets_list).each(function (index, widget) {
            add_widget+='<li class="widgetItem"><div class="radio radio-inline radio-success"><input type="radio" class="dashboard_widget" name="dashboard_widget" id="widget_' + widget.key + '" value="' + widget.key + '" /><label for="widget_' + widget.key + '">' + widget.key + '</label></div></li>';
        });
        add_widget+='</ul>';
        $('#dashboardModal').find('.modal-body').empty();
        $('#dashboardModal').find('.modal-body').append(add_widget);
    });
    $(document).on('click', '#changeLayout', function() {
        var dashboard_id=$('#dashboardWrapper').attr('data-dashboard-id');
        var dashboard_layout=$('#dashboardWrapper').attr('data-dashboard-layout');
        $('#dashboardModal').find('.modal-title').html(admin_label_change_layout);
        var layout_list='<ul class="layoutListWrapper">';
        layout_list+='<li class="layoutItem"><div class="radio radio-inline radio-success"><input type="radio" class="dashboard_layout" name="dashboard_layout" id="layout_layout1big1small" value="layout1big1small"' + (dashboard_layout=='layout1big1small' ? ' checked="checked"' : '') + ' /><label for="layout_layout1big1small">1 big 1 small (2 cols)</label></div></li>';
        layout_list+='<li class="layoutItem"><div class="radio radio-inline radio-success"><input type="radio" class="dashboard_layout" name="dashboard_layout" id="layout_layout1small1big" value="layout1small1big"' + (dashboard_layout=='layout1small1big' ? ' checked="checked"' : '') + ' /><label for="layout_layout1small1big">1 small 1 big (2 cols)</label></div></li>';
        layout_list+='<li class="layoutItem"><div class="radio radio-inline radio-success"><input type="radio" class="dashboard_layout" name="dashboard_layout" id="layout_layout1col" value="layout1col"' + (dashboard_layout=='layout1col' ? ' checked="checked"' : '') + ' /><label for="layout_layout1col">1 col</label></div></li>';
        layout_list+='<li class="layoutItem"><div class="radio radio-inline radio-success"><input type="radio" class="dashboard_layout" name="dashboard_layout" id="layout_layout2cols" value="layout2cols"' + (dashboard_layout=='layout2cols' ? ' checked="checked"' : '') + ' /><label for="layout_layout2cols">2 cols</label></div></li>';
        layout_list+='<li class="layoutItem"><div class="radio radio-inline radio-success"><input type="radio" class="dashboard_layout" name="dashboard_layout" id="layout_layout3cols" value="layout3cols"' + (dashboard_layout=='layout3cols' ? ' checked="checked"' : '') + ' /><label for="layout_layout3cols">3 cols</label></div></li>';
        layout_list+='<li class="layoutItem"><div class="radio radio-inline radio-success"><input type="radio" class="dashboard_layout" name="dashboard_layout" id="layout_layout4cols" value="layout4cols"' + (dashboard_layout=='layout4cols' ? ' checked="checked"' : '') + ' /><label for="layout_layout4cols">4 cols</label></div></li>';
        layout_list+='</ul>';
        $('#dashboardModal').find('.modal-body').empty();
        $('#dashboardModal').find('.modal-body').append(layout_list);
    });
    $(document).on('click', '#saveChanges', function() {
        var dashboard_id=$('#dashboardWrapper').attr('data-dashboard-id');
        if($('.dashboard_layout').length) {
            jQuery.ajax({
                type: 'POST',
                url: saveNewLayoutAjaxURL,
                cache :false,
                dataType: 'json',
                data: 'tx_multishop_pi1[new_layout]=' + $('.dashboard_layout:checked').val() + '&tx_multishop_pi1[dashboard_id]=' + dashboard_id,
                success:
                    function(d) {
                        if (d.status=='OK') {
                            location.reload();
                        } else {
                            $('#dashboardModal').modal('hide')
                        }
                    },
                error:
                    function() {

                    }
            });
        } else if ($('.dashboard_widget').length) {
            jQuery.ajax({
                type: 'POST',
                url: addWidgetAjaxURL,
                cache :false,
                dataType: 'json',
                data: 'tx_multishop_pi1[widget_key]=' + $('.dashboard_widget:checked').val() + '&tx_multishop_pi1[dashboard_id]=' + dashboard_id,
                success:
                    function(d) {
                        if (d.status=='OK') {
                            location.reload();
                        } else {
                            $('#dashboardModal').modal('hide');
                            alert(d.reason);
                        }
                    },
                error:
                    function() {

                    }
            });
        }
    });
});
