(function ($) {
    "use strict";
    $(function () {

        const widget = $('.mailjet_widget_form_message');
        if (1 === widget.length && widget[0] !== undefined) {
            const messageOffsetTop = widget[0].offsetTop + 100;
            $('html, body').animate({scrollTop: messageOffsetTop}, "fast");
        }

    });
}(jQuery));