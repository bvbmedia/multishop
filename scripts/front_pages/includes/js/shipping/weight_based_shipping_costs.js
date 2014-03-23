function UpdateWeightPrice(nextrow, zone_id, cur_weight) {
    // index keys for both array are from 0 ... 38
    var weight_list_value = ['0.02', '0.05', '0.1', '0.25', '0.35', '0.5', '0.75', '1', '1.25', '1.5', '1.75', '2', '2.25', '2.5', '2.75', '3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5', '6', '7', '8', '9', '10', '15', '20', '25', '30', '35', '40', '45', '50', '100', '101'];
    var weight_list_text = ['0,02', '0,05', '0,1', '0,25', '0,35', '0,5', '0,75', '1', '1,25', '1,5', '1,75', '2', '2,25', '2,5', '2,75', '3', '3,25', '3,5', '3,75', '4', '4,25', '4,5', '4,75', '5', '6', '7', '8', '9', '10', '15', '20', '25', '30', '35', '40', '45', '50', '100', 'End'];

    var next_row_id = '#' + zone_id + '_Row_' + nextrow;
    var next_begin_weight_label_id = '#' + zone_id + '_BeginWeightLevel' + nextrow;
    var next_weight_list_id = '#' + zone_id + '_EndWeightLevel' + nextrow;
    var cur_weight_list_id = '#' + zone_id + '_EndWeightLevel' + parseInt(nextrow - 1);
    var next_begin_weight_label = '';
    var next_weight_list_selected = '';
    if (cur_weight == 101 && $(next_row_id).is(':visible')) {
        jQuery(next_begin_weight_label_id).text('0 KG');
        jQuery(next_row_id).hide();
    } else {
        var option_elem = '';
        $(cur_weight_list_id + ' > option').each(function (idx, obj) {
            if ($(obj).val() == cur_weight) {
                next_begin_weight_label = $(obj).text();
                next_weight_list_selected = weight_list_value.indexOf(jQuery(obj).val());
                if (parseInt(next_weight_list_selected) > 38) {
                    next_weight_list_selected = 38;
                }
            }
        });
        $(weight_list_value).each(function (idx, val) {
            if (parseInt(idx) > parseInt(next_weight_list_selected)) {
                if (idx == 38) {
                    option_elem += '<option value="' + val + '" selected="selected">' + weight_list_text[idx] + '</option>';
                } else {
                    option_elem += '<option value="' + val + '">' + weight_list_text[idx] + '</option>';
                }
            }
        });
        $(next_weight_list_id).html(option_elem);
        jQuery(next_begin_weight_label_id).text(next_begin_weight_label + ' KG');
        jQuery(next_row_id).show();
    }
}