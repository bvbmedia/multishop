if (!price_subject_id) {
    var price_subject_id=0;
}
function priceEditRealtimeCalc(to_include_vat, o, type, trigger_element) {
    type = typeof type !== 'undefined' ? type : '';
    trigger_element = typeof trigger_element !== 'undefined' ? trigger_element : '';

    if (trigger_element=="product_tax") {
        var price_value=$(o).parent().parent().next().next().children().val();
        var original_val = price_value;
        var current_value = parseFloat(price_value);
    } else {
        var original_val = $(o).val();
        var current_value = parseFloat($(o).val());
    }
    if ($(o).attr("rel")!=undefined) {
        price_subject_id = $(o).attr("rel");
    }
    var tax_rate=0;
    if (typeof product_tax_rate_js!='undefined') {
        tax_rate=parseFloat(product_tax_rate_js[price_subject_id]);
    } else if (typeof product_tax_rate_list_js!='undefined') {
        var tax_id=$("#tax_id").val();
        if (type) {
            if (type=='rel') {
                // the tax id value is in other hidden input
                // $(o).attr('data-tax-id') contain input element id that hold the tax id
                if ($(o).attr('data-tax-id')!=undefined) {
                    // reference input
                    var reference_id=$(o).attr('data-tax-id');
                    tax_id = $(reference_id).val();
                } else {
                    tax_id = $(o).attr("rel");
                }
            } else {
                tax_id = $(type).val();
            }
        }
        tax_rate=parseFloat(product_tax_rate_list_js[tax_id]);
    }
    if (!$(o).hasClass('singlePriceInput')) {
        if (current_value > 0) {
            if (to_include_vat) {
                if (tax_rate > 0) {
                    var priceIncludeVat = parseFloat(current_value + (current_value * (tax_rate / 100)));
                    var incl_tax_crop = decimalCrop(priceIncludeVat);
                    $(o).parentsUntil('.msAttributesField').parent().next().children().find('input.form-control').val(incl_tax_crop);
                } else {
                    $(o).parentsUntil('.msAttributesField').parent().next().children().find('input.form-control').val(original_val);
                }
                // update the hidden excl vat
                $(o).parentsUntil('msAttributesField').next().next().first().children().val(current_value);
            } else {
                if (tax_rate > 0) {
                    var priceExcludeVat = parseFloat((current_value / (100 + tax_rate)) * 100);
                    var excl_tax_crop = decimalCrop(priceExcludeVat);
                    // update the excl. vat
                    $(o).parentsUntil('.msAttributesField').parent().prev().children().find('input.form-control').val(excl_tax_crop);
                    // update the hidden excl vat
                    $(o).parentsUntil('.msAttributesField').parent().next().first().children().val(priceExcludeVat);
                } else {
                    // update the excl. vat
                    $(o).parentsUntil('.msAttributesField').parent().prev().children().find('input.form-control').val(original_val);
                    // update the hidden excl vat
                    $(o).parentsUntil('.msAttributesField').parent().next().first().children().val(current_value);
                }
            }
        } else {
            if (to_include_vat) {
                // update the incl. vat
                $(o).parentsUntil('.msAttributesField').parent().next().children().find('input').val(0);
                // update the hidden excl vat
                $(o).parentsUntil('msAttributesField').next().next().first().children().val(0);
            } else {
                // update the excl. vat
                $(o).parentsUntil('.msAttributesField').parent().prev().children().find('input').val(0);
                // update the hidden excl vat
                $(o).parentsUntil('.msAttributesField').parent().next().first().children().val(0);
            }
        }
    }
}
function decimalCrop(float, precision, enable) {
    enable = typeof enable !== 'undefined' ? enable : false;
    precision = typeof precision !== 'undefined' ? precision : 2;
    if (enable) {
        var numbers = float.toString().split(".");
        var prime = numbers[0];
        if (numbers[1] > 0 && numbers[1] != "undefined") {
            var decimal = new String(numbers[1]);
        } else {
            var decimal = "00";
        }
        var number = prime + "." + decimal.substr(0, precision);
        return number;
    } else {
        return float;
    }
}
function CONFIRM(label) {
    if (confirm(label)) {
        return true;
    } else {
        return false;
    }
}
function isMobile() {
    try {
        document.createEvent("TouchEvent");
        return true;
    }
    catch (e) {
        return false;
    }
}
function ifConfirm(textTitle, textBody, yesFn, noFn) {
    return $.confirm({
        title: textTitle,
        content: textBody,
        confirm: yesFn,
        cancel: noFn
    });
}
function msDialog(textTitle, textBody, width, opacity) {
    return $.confirm({
        title: textTitle,
        content: textBody
    });
}
function msAdminBlockUi(onBlock) {
    $.blockUI({
        css: {
            width: '350',
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
        },
        message: '<ul class="multishop_block_message"><li>One moment please...</li></ul>',
        onBlock: onBlock
    });
}
jQuery(document).ready(function ($) {
    $('[data-toggle="tooltip"]').tooltip({html:true});
    $('.msBtnConfirm').click(function (e) {
        e.preventDefault();
        var linkTarget = $(this).attr("href");
        ifConfirm($(this).attr("data-dialog-title"), $(this).attr("data-dialog-body"), function () {
            window.location.href = linkTarget;
        });
    });
    $('.blockSubmitForm').submit(function (e) {
        msAdminBlockUi();
    });
    $('.blockAhrefLink').click(function (e) {
        msAdminBlockUi();
    });
    // plus minus
    $(document).on("click", '.btn-number', function(e) {
        e.preventDefault();
        fieldName = $(this).attr('data-field');
        type = $(this).attr('data-type');
        var input = $(this).parents('.input-number-wrapper').find('.input-number');
        var minValue = parseFloat(input.attr('min'));
        var maxValue = parseFloat(input.attr('max'));
        var currentVal = parseFloat(input.val());
        var stepSize = parseFloat(input.attr('data-step-size'));
        if (isNaN(stepSize)) {
            stepSize = 1;
        }
        if (isNaN(currentVal)) {
            currentVal = minValue;
        }
        if (!isNaN(currentVal)) {
            if (type == 'minus') {
                if (currentVal > minValue) {
                    var num=(currentVal - stepSize);
                    if (stepSize<1) {
                        input.val(num.toFixed(2)).change();
                    } else {
                        input.val(num).change();
                    }
                }
                if (currentVal == minValue) {
                    $(this).attr('disabled', true);
                }
            } else if (type == 'plus') {
                if (maxValue == '0' || currentVal < maxValue) {
                    var num=(currentVal + stepSize);
                    if (stepSize<1) {
                        input.val(num.toFixed(2)).change();
                    } else {
                        input.val(num).change();
                    }
                }
                if (maxValue == '0' && currentVal == maxValue) {
                    $(this).attr('disabled', true);
                }
            }
        } else {
            input.val(minValue);
        }
    });
    $(document).on("focusin", '.input-number', function(e) {
        $(this).data('oldValue', $(this).val());
    });
    $(document).on("change", '.input-number', function(e) {
        minValue = parseFloat($(this).attr('min'));
        maxValue = parseFloat($(this).attr('max'));
        valueCurrent = parseFloat($(this).val());
        name = $(this).attr('name');

        if (valueCurrent >= minValue) {
            $(this).parents('.input-number-wrapper').find('.btn-number[data-type=\'minus\']').removeAttr('disabled');
        } else {
            //alert('Sorry, the minimum value was reached');
            $(this).val($(this).data('oldValue'));
        }
        if (maxValue != '0' || valueCurrent <= maxValue) {
            $(this).parents('.input-number-wrapper').find('.btn-number[data-type=\'plus\']').removeAttr('disabled');
        } else if(maxValue != '0') {
            //alert('Sorry, the maximum value was reached');
            $(this).val($(this).data('oldValue'));
        }
    });
    $(document).on("keydown", '.input-number', function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    // plus minus eol
});