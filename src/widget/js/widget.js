(function ($) {
    "use strict";
    $(function () {

        $(document).on('widget-updated', function (event, widget) {
            showCheckedLanguages();
        });

        $(document).on('widget-added', function (event, widget) {
            showCheckedLanguages();
        });

        // Toggle(show/hide) hidden language elements(title, contactList)
        $(document).on('change', '.language_checkbox', function () {
            var removeLanguage = !$(this).prop('checked');
            // Uncheck the language checkbox
            if (removeLanguage) {
                mjHide($(this).parent().find('div.mj-modal-language-config')[0]);
            } else {
                mjShow($(this).parent().find('div.mj-modal-language-config')[0]);
                // Add specific language
                var languageListId = $(this).parent().find('.language-select-list').val();
                if (languageListId === "0") {
                    $(this).parent().find('.language-select-list').css({'border': '1px solid red'});
                }
            }

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

        $(document).on('click', '.saveNewPropertyButton', function (event) {
            event.preventDefault();
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
                        mjHide(element.parent().parent().find('.createNewProperties')[0]);

                        // Show datatype and languages inputs/values and delete
                        mjShow(element.parent().parent().find('.hiddenProperties')[0]);

                        // Show next default property select
                        mjShow(element.parent().parent().next('.property')[0]);

                        // Remove the delete icon for the previous row
                        mjHide(element.parent().parent().prev().find('.deleteProperty')[0]);

                        // Remove class as the input value is ok
                        element.parent().parent().find('.newPropertyName input').removeClass('redInput');

                        // Add the new property and select it
                        selectProperty.append('<option value="' + response[0].ID + '" selected="selected">' + newPropertyName + '</option>');
                    } else {
                        element.parent().parent().find('.newPropertyName input').addClass('redInput');
                        element.parent().parent().find('.newPropertyName input').val('');
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
                mjHide($(this).parent().parent().find('.hiddenProperties')[0]);

                // Show new property inputs
                mjShow($(this).parent().parent().find('.createNewProperties')[0]);

            } else {
                // Show datatype and languages inputs/values and delete
                mjShow($(this).parent().parent().find('.hiddenProperties')[0]);

                // Hide inputs of adding new property
                mjHide($(this).parent().parent().find('.createNewProperties')[0]);

                // Reset inputs for adding new property
                resetAddingNewPropertyInputs($(this));

                // Show next default property select
                const nextProperty = $(this).parent().parent().next('.property')[0];
                if (nextProperty) {
                    mjShow(nextProperty);
                }

                // Remove the delete icon for the previous row
                mjHide($(this).parent().parent().prev().find('.deleteProperty')[0]);
            }
        });

        // Delete a property
        $(document).on('click', '.deleteProperty', function (event) {

            // Hide row properties and only property select left
            mjHide($(this).parent().parent().find('.hiddenProperties')[0]);

            // Hide next row as the deleted stays the current
            mjHide($(this).parent().parent().next()[0]);

            // Show delete icon for the last row
            const previousDeleteProperty = $(this).parent().parent().prev().find('.deleteProperty')[0];
            if (previousDeleteProperty) {
                mjShow(previousDeleteProperty);
            }

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

        $(document).on('click', '.saveMailjetAdvancedForm', function () {
            $(this).parents('.widget').find("[name='savewidget']").click();
            $(this).text("Saving...").prop('disabled', true);
        });

        // Show/hide advnaced form link depends on Save button
        $(document).on('change', $("input[name='savewidget']"), function () {
            var isSaveButtonDisabled = $(this).is(":disabled");
            if (isSaveButtonDisabled) {
                $(".disabled-advanced-link").addClass('hidden_default');
                $(".advanced-form-link-wrap").removeClass('hidden_default');
            } else {
                $(".disabled-advanced-link").removeClass('hidden_default');
                $(".advanced-form-link-wrap").addClass('hidden_default');
            }
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

        $(document).on('click', '.cancelNewPropertyButton', function (event) {
            event.preventDefault();
            // Hide new property inputs
            mjHide($(this).parent().parent().find('.createNewProperties')[0]);

            // Reset inputs
            resetHiddenPropertiesInputs($(this));

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
            eventOpenAdvancedFormModal();
            eventCloseAdvancedFormModal();
            eventChangeAdvancedFormTab();
        }

        /**
         * Show the hidden elements of the checked languages
         * @returns {undefined}
         */
        function showCheckedLanguages() {
            $('.language_checkbox').each(function (index, value) {
                if (value.checked === true) {
					mjShow($(this).parent().find('div.mj-modal-language-config')[0]);
                }
            });
        }

        function eventOpenAdvancedFormModal() {
            const modalTriggers = document.querySelectorAll('.advanced-form-link');
            modalTriggers.forEach(function(trigger) {
                trigger.addEventListener('click', function () {
                    const widgetId = this.getAttribute('data-target');
                    const modalWindow = document.getElementById('modal-' + widgetId);
                    modalWindow.classList.add('open');
                });
            });
        }

        function eventCloseAdvancedFormModal() {
            const modals = document.querySelectorAll('.mj-modal');
            modals.forEach(function(modal) {
                const closeModalTriggers = modal.querySelectorAll('.mj-modal-header .close, .cancelMailjetAdvancedForm');
                closeModalTriggers.forEach(function(trigger) {
                    trigger.addEventListener('click', function () {
                        modal.classList.remove('open');
                    });
                });
            });
        }

        function eventChangeAdvancedFormTab() {
            const tabsTriggersContainers = document.querySelectorAll('.mj-nav-tabs');
            tabsTriggersContainers.forEach(function(tabsTriggersContainer) {
                const modalId = '#modal-' + tabsTriggersContainer.getAttribute('data-target');
                const tabsTriggers = tabsTriggersContainer.querySelectorAll('.mj-tab');
                tabsTriggers.forEach(function(trigger) {
                    trigger.addEventListener('click', function (event) {
                        const currentActiveTrigger = document.querySelector(modalId + ' .mj-tab.active');
                        if (currentActiveTrigger !== null) {
                            currentActiveTrigger.classList.remove('active');
                        }
                        this.classList.add('active');
                        const targetTabId = this.getAttribute('data-tab');
                        const currentActiveTab = document.querySelector(modalId + ' .mj-tab-panel.active');
                        if (currentActiveTab !== null) {
                            currentActiveTab.classList.remove('active');
                        }
                        const newActiveTab = document.querySelector(modalId + ' #' + targetTabId);
                        if (newActiveTab !== null) {
                            newActiveTab.classList.add('active');
                        }
                    });
                });
            });
        }
    });
}(jQuery));
