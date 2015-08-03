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
function msDialog(textTitle, textBody, width,opacity) {
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
    $('.msBtnConfirm').click(function(e) {
        e.preventDefault();
        var linkTarget=$(this).attr("href");
        ifConfirm($(this).attr("data-dialog-title"),$(this).attr("data-dialog-body"),function() {
            window.location.href=linkTarget;
        });
    });
    $('.blockSubmitForm').submit(function(e) {
        msAdminBlockUi();
    });
    $('.blockAhrefLink').click(function(e) {
        msAdminBlockUi();
    });
});
