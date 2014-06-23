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
    var dialog = $('<div/>', {
        id: 'dialog',
        title: textTitle
    });
    dialog.append(textBody);
    dialog.dialog({
        width: 900,
        modal: true,
        body: "",
        resizable: false,
        open: function () {
            // right button (save button) must be the default button when user presses enter key
            $(this).siblings('.ui-dialog-buttonpane').find('.continueState').focus();
        },
        buttons: {
            "no": {
                text: "No",
                class: 'msCancelButton msBackendButton prevState arrowLeft arrowPosLeft',
                click: function () {
                    $(this).dialog("close");
                    $(this).hide();
                }
            },
            "yes": {
                text: "Yes",
                class: 'msOkButton msBackendButton continueState arrowRight arrowPosLeft',
                click: yesFn
            }
        }
    });
}
function msDialog(textTitle, textBody) {
    var dialog = $('<div/>', {
        id: 'dialog',
        title: textTitle
    });
    dialog.append(textBody);
    dialog.dialog({
        width: 450,
        modal: true,
        body: "",
        resizable: false,
        open: function () {
            // right button (OK button) must be the default button when user presses enter key
            $(this).siblings('.ui-dialog-buttonpane').find('.continueState').focus();
        },
        buttons: {
            "ok": {
                text: "OK",
                class: 'msOkButton msBackendButton continueState arrowRight arrowPosLeft',
                click: function () {
                    $(this).dialog("close");
                    $(this).hide();
                }
            }
        }
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
    $('.blockSubmitForm').submit(function(e) {
        msAdminBlockUi();
    });
    $('.blockAhrefLink').click(function(e) {
        msAdminBlockUi();
    });
});
