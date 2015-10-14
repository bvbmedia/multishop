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
    $('.btn-number').click(function (e) {
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
        if (!isNaN(currentVal)) {
            if (type == 'minus') {
                if (currentVal > minValue) {
                    input.val(currentVal - stepSize).change();
                }
                if (currentVal == minValue) {
                    $(this).attr('disabled', true);
                }
            } else if (type == 'plus') {
                if (maxValue == '' || currentVal < maxValue) {
                    input.val(currentVal + stepSize).change();
                }
                if (maxValue == '' && currentVal == maxValue) {
                    $(this).attr('disabled', true);
                }
            }
        } else {
            input.val(0);
        }
    });
    $('.input-number').focusin(function () {
        $(this).data('oldValue', $(this).val());
    });
    $('.input-number').change(function () {
        minValue = parseFloat($(this).attr('min'));
        maxValue = parseFloat($(this).attr('max'));
        valueCurrent = parseFloat($(this).val());

        name = $(this).attr('name');

        if (valueCurrent >= minValue) {
            //$(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
            $(this).parents('.input-number-wrapper').find('.btn-number[data-type=\\\'minus\\\']').removeAttr('disabled');
        } else {
            //alert('Sorry, the minimum value was reached');
            $(this).val($(this).data('oldValue'));
        }
        if (maxValue != '' || valueCurrent <= maxValue) {
            //$(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
            $(this).parents('.input-number-wrapper').find('.btn-number[data-type=\\\'plus\\\']').removeAttr('disabled');
        } else {
            //alert('Sorry, the maximum value was reached');
            $(this).val($(this).data('oldValue'));
        }
    });
    $(".input-number").keydown(function (e) {
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