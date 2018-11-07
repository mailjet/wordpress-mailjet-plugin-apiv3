(function ($) {
    "use strict";
    $(function () {

        $(document).on('widget-updated', function (event, widget) {
            var widget_id = $(widget).attr('id');
//            alert('On widget-updated: ' + widget_id);

            showCheckedLanguages();
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

        // Toggle(show/hide) hidden language elements(title, contactList)
        $(document).on('change', '.language_checkbox', function () {

            // Show/Colapse
            var languageHiddenElementClass = getLanguageHiddenElements(this);
            $('#' + languageHiddenElementClass).toggle("slow");

            // Uncheck the language checkbox
            var removeLanguage = !$(this).prop('checked');
            if (removeLanguage) {
                // Reset Title and List ?
                $(this).parent().find('.title-input').val('');
                $(this).parent().find('.language-select-list').val(0);
            } else {
                // Add specific language
                var languageListId = $(this).parent().find('.language-select-list').val();
                if (languageListId === "0") {
                    $(this).parent().find('.language-select-list').css({'border': '1px solid red'});
                }
            }


            // Hide advanced form, changes must be saved
//            $("div#advanced-form-link-wrap").hide();
        });

        $(document).on('change', '.language-select-list', function (event) {
            var languageListId = event.target.value;
            if (languageListId === "0") {
                // No contact list is selected
                $(this).parent().find('.language-select-list').css({'border': '1px solid red'});
            } else {
                // A contact list is selected
                $(this).parent().find('.language-select-list').css({'border': '1px solid green'});
            }
        });

        $(document).on('click', '.saveNewPropertyButton', function () {

            var element = $(this);

            // Get new property name value
            var newPropertyName = element.parent().parent().find('.newPropertyName input').val();
            // Get new property type
            var newPropertyType = element.parent().parent().find('.newPropertyType select').val();

            var selectProperty = element.parent().parent().find('.selectProperty');

            // ajax request to create the new property
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: myAjax.ajaxurl,
                data: {action: "mailjet_add_contact_property", propertyName: newPropertyName, propertyType: newPropertyType},
                success: function (response) {
                    if (response !== null && response[0] !== undefined && response[0].ID !== undefined) {

                        // Reset elements
                        resetHiddenPropertiesInputs(element);
                        resetAddingNewPropertyInputs(element, 'Text');

                        // Hide new property inputs
                        element.parent().parent().find('.createNewProperties').hide();

                        // Show datatype and languages inputs/values and delete
                        element.parent().parent().find('.hiddenProperties').show();

                        // Show next default property select
                        element.parent().parent().next('.property').show();

                        // Remove the delete icon for the previous row
                        element.parent().parent().prev().find('.deleteProperty').hide();

                        // Remove class as the input value is ok
                        element.parent().parent().find('.newPropertyName input').removeClass('redInput');

                        // Add the new property and select it
                        selectProperty.append('<option value="' + response[0].ID + '" selected="selected">' + newPropertyName + '</option>');
                    } else {
                        element.parent().parent().find('.newPropertyName input').addClass('redInput');
                    }
                }
            });

        });

        // Select a contact property
        $(document).on('change', '.selectProperty', function (event) {
            var optionValue = event.target.value;

            // Create new property
            if (optionValue === 'newProperty') {
                // Hide select property inputs
                $(this).parent().parent().find('.hiddenProperties').hide();

                // Show new property inputs
                $(this).parent().parent().find('.createNewProperties').show();

                // Do not delete 
                // Hide all rows that are not setup
//                $('.selectProperty').each(function (env) {
//                    if ($(this).val() === null) {
//                        $(this).parent().parent().hide();
//                    }
//                });

                // Show next default property select
//                $(this).parent().parent().next('.property').hide();
            } else {
                // Show datatype and languages inputs/values and delete
                $(this).parent().parent().find('.hiddenProperties').show();

                // Hide inputs of adding new property
                $(this).parent().parent().find('.createNewProperties').hide();

                // Reset inputs for adding new property
                resetAddingNewPropertyInputs($(this));

                // Show next default property select
                $(this).parent().parent().next('.property').show();

                // Remove the delete icon for the previous row
                $(this).parent().parent().prev().find('.deleteProperty').hide();
            }
        });

        // Delete a property
        $(document).on('click', '.deleteProperty', function (event) {

            // Hide row properties and only property select left
            $(this).parent().parent().find('.hiddenProperties').hide();

            // Hide next row as the deleted stays the current
            $(this).parent().parent().next('.property').hide();

            // Show delete icon for the last row
            $(this).parent().parent().prev().find('.deleteProperty').show();

            resetHiddenPropertiesInputs($(this));
        });

        function resetHiddenPropertiesInputs(element) {
            // Reset selects
            element.parent().parent().find('.selectProperty').val(0);
            element.parent().parent().find('.propertyDataType').val(0);

            // Reset language inputs
            element.parent().parent().find('.languageInput input').val('');

            // Remove class as the input value is ok
            element.parent().parent().find('.newPropertyName input').removeClass('redInput');
        }

        function resetAddingNewPropertyInputs(element, resetElementValue = 0) {
            // Reset new property name input
            element.parent().parent().find('.newPropertyName input').val('');

            // Reset new property datatype select
            element.parent().parent().find('.newPropertyType input').val(resetElementValue);
        }

        $(document).on('click', '#saveAdvancedForm', function () {
            $("input[name='savewidget']").click();
            $(this).text("Saving...").prop('disabled', true);
        });

        // Show/hide advnaced form link depends on Save button
        $(document).on('change', $("input[name='savewidget']"), function () {
            var isSaveButtonDisabled = $(this).is(":disabled");
            if (isSaveButtonDisabled) {
                $("div#advanced-form-link-wrap").show();
            } else {
                $("div#advanced-form-link-wrap").hide();
            }
//           $("div#advanced-form-link-wrap").hide();
//           $("span#advanced-form-link").hide();
        });

        // Fires when property value is changed
        $(document).on('change', '.propertyDataType', function () {
            var optionValue = event.target.value;
            var defaultPlaceholder = 'Field label in';
            var hiddenPlaceholder = 'Value for';
            if (optionValue === '2') {
                // When 'hidden' option is selected
                replaceLanguagePlaceholder($(this), defaultPlaceholder, hiddenPlaceholder);
            } else {
                replaceLanguagePlaceholder($(this), hiddenPlaceholder, defaultPlaceholder);
            }
        });

        $(document).on('click', '.cancelNewPropertyButton', function () {
            // Hide new property inputs
            $(this).parent().parent().find('.createNewProperties').hide();

            // Reset inputs
            resetHiddenPropertiesInputs($(this));

            // Show select property inputs
//            $(this).parent().parent().find('.hiddenProperties').show();

            // Show next default property select
//            $(this).parent().parent().next('.property').show();

        });

        function replaceLanguagePlaceholder(element, searchStr, replaceStr) {
            element.parent().parent().parent().find('.languageInput input').each(function (env) {
                if (!$(this).val()) {
                    var str = $(this).attr("placeholder");
                    var res = str.replace(searchStr, replaceStr);
                    $(this).attr("placeholder", res);
                }
            });
        }

        init();

        /**
         * Show checked languages
         * Activate tooltips
         * Activate tabs
         * @returns {undefined}
         */
        function init() {
            showCheckedLanguages();
            $('[data-toggle="tooltip"]').tooltip();
//            showPropertySelect(1);
        }

//        function showPropertySelect(n) {
//            var selectProperties = $('.selectProperty');
//            var opened = 0;
//            selectProperties.each(function (index, value) {
//                if (opened >= n) {
//                    return false;
//                }
//                $(this).parent().parent().find('.hiddenSelectProperties').show();
//                $(this).parent().parent().parent().find('.hiddenSelectProperties').show();
//                $(this).parent().find('.hiddenSelectProperties').show();
//                $(this).parent().parent().find('.hiddenSelectProperties').css({'display':'block'});
//                opened = opened + 1;
//            });
//        }

        /**
         * Show the hidden elements of the checked languages
         * @returns {undefined}
         */
        function showCheckedLanguages() {
            $('.language_checkbox').each(function (index, value) {
                if (value.checked === true) {
                    var languageHiddenElementClass = getLanguageHiddenElements(value);
                    $('#' + languageHiddenElementClass).show();
                }
            });
        }

        /**
         * Get the id of the hidden elements of a specific language checkbox
         * @param {type} element
         * @returns {String}
         */
        function getLanguageHiddenElements(element) {
            var language = (element);
            var languageId = language.getAttribute('id');
            return 'hidden_' + languageId;
        }

    });
}(jQuery));