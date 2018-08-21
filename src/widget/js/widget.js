(function ($) {
    "use strict";
    $(function () {

        $(document).on('widget-updated', function (event, widget) {
            var widget_id = $(widget).attr('id');
//            alert('On widget-updated: ' + widget_id);
            // any code that needs to be run when a widget gets updated goes here
            // widget_id holds the ID of the actual widget that got updated
            // be sure to only run the code if one of your widgets got updated
            // otherwise the code will be run when any widget is updated
        });

        $(document).on('widget-added', function (event, widget) {
            var widget_id = $(widget).attr('id');
//            alert('On widget-added: ' + widget_id);
            // any code that needs to be run when a new widget gets added goes here
            // widget_id holds the ID of the actual widget that got added
            // be sure to only run the code if one of your widgets got added
            // otherwise the code will be run when any widget is added
        });

        $(document).on('change', '.language_checkbox', function() {
            var language = (this);
            var languageId = language.getAttribute('id');
            var languageHiddenElementClass = 'hidden_' + languageId;
            $('#' + languageHiddenElementClass).toggle("slow");
        });

    });
}(jQuery));