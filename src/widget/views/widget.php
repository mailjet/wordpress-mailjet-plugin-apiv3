<?php

use MailjetPlugin\Includes\Mailjeti18n;

?>

<!-- This file is used to markup the public-facing widget. -->
<div class="mailjet_widget_front_container">
    <?php
    extract($args);

    $locale = Mailjeti18n::getLocale();
    $language = Mailjeti18n::getCurrentUserLanguage();

    // Check the widget options
    $title = isset($instance[$locale]['title']) ? apply_filters('widget_title', $instance[$locale]['title']) : '';
    $emailLabel = !empty($instance[$locale]['language_mandatory_email']) ? apply_filters('widget_language_mandatory_email', $instance[$locale]['language_mandatory_email']) : Mailjeti18n::getTranslationsFromFile($locale, 'your@email.com');
    $buttonLabel = !empty($instance[$locale]['language_mandatory_button']) ? apply_filters('widget_language_mandatory_button', $instance[$locale]['language_mandatory_button']) : Mailjeti18n::getTranslationsFromFile($locale, 'Subscribe');
    ?>
    <div class="widget-text wp_widget_plugin_box">

        <!--Widget title-->
        <div id="mailjet_widget_title_wrap">
            <span id="mailjet_widget_title">
                <?php echo $before_title . $title . $after_title ?>
            </span>
        </div>
        <!--End Widget title-->
        
        <!--Widget form-->
        <form method="post" action=" <?php echo esc_url($_SERVER['REQUEST_URI']) ?>" id="mjForm" name="mjForm">
            
            <!--Subscription email input(mandatory)-->
            <div class="form-group">
                <input type="email" name="subscription_email" id="mailjet_widget_email" required="required" placeholder="* <?php echo $emailLabel ?>">
            </div>
            <?php

            // Check for the additional properties from the admin advanced settings
            for ($i = 0; $i < 5; $i++) {

                // Property id - '0' there is no selected property
                $contactPropertyId = (int)$instance[$locale]['contactProperties' . $i];
                
                // Skip if this property is not added in admin part
                if (empty($contactPropertyId)) {
                    continue;
                }

                if(empty($this->propertyData[$contactPropertyId])) {
                   continue;
                }

                // Mailjet property type
                $propertyDataType = $this->propertyData[$contactPropertyId]['Datatype'];

                // Map mailjet property type to valid input type
                $inputType = $this->getInputType($propertyDataType);
               

                // The value of the label
                $placeholder = $instance[$locale][$language . 'Label' . $i];

                // '0' - optional
                // '1' - mandatory
                // '2' - hidden
                $propertyType = (int) $instance[$locale]['propertyDataType' . $i];

                // Display block by default
                $display = 'block';

                // Not required by default
                $required = '';

                // Used on hidden properties
                $value = '';

                $requiredStar = '';

                // Set required, display and value depends on the current property type
                switch ($propertyType) {
                    // Optional input
                    case 0:
                        // Display: block
                        // No value, only placeholder
                        // Not Required
                        break;
                    // Mandatory input
                    case 1:
                        // Display: block
                        // No value, only placeholder

                        // Required
                        $required = 'required';

                        // Add * to placeholder to indicate that the input is required
                        $requiredStar = '* ';
                        break;
                    // Hidden input
                    case 2:
                        // Display: none
                        $display = 'none';

                        // Value is given from admin advanced settings
                        $value = 'value="'.$placeholder.'"';
                        break;
                }
                $class = '';
                if ($inputType == 'date') {
                    $class = 'mjDate';
                }
                ?>
                <div class="form-group">
                    <input <?php echo $required ?> type="text" class="mj_form_property <?php echo $class ?>" name="properties[<?php echo $contactPropertyId ?>]" <?php echo $value ?> placeholder="<?php
                    echo $requiredStar;
                    echo $placeholder
                    ?>" style="display: <?php echo $display ?>">
                </div>
                <?php
            }
            ?>
            <input type="submit" value="<?php echo $buttonLabel ?>" onclick="mjSubmitWidgetForm(event)">
        </form>
        <span><?php echo $form_message; ?></span>
    </div>
</div>
<script>
    (function ($) {
//        "use strict";
        $(function () {
            $('#mjForm').on('submit', function (e) {
                var dates = $('.mjDate');
                $.each(dates, function (index, value) {
                    var dateInputValue = $(value).val();
                    if (dateInputValue !== "") {
                        var isOkDate = validatedate(dateInputValue);
                        if (isOkDate === false) {
                            $(value).addClass('mjWrongDateInput');
                            e.preventDefault();
                            return false;
                        }
                    }
                });
                return true;
            });

            function validatedate(dateText) {
                if (dateText) {
                    var splitComponents = dateText.split('/');
                    if (splitComponents.length !== 3) {
                        return false;
                    }
                    var day = parseInt(splitComponents[0]);
                    var month = parseInt(splitComponents[1]);
                    var year = parseInt(splitComponents[2]);
                    if (isNaN(day) || isNaN(month) || isNaN(year)) {
                        return false;
                    }
                    var now = new Date;
                    var theYear = now.getYear();
                    if (theYear < 1900) {
                        theYear = theYear + 1900;
                    }
                    if (day <= 0 || month <= 0 || year <= 0 || year <= 1900 || year > theYear) {
                        return false;
                    }
                    if (month > 12) {
                        return false;
                    }
                    // assuming no leap year by default
                    var daysPerMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                    if (year % 4 === 0) {
                        // current year is a leap year
                        daysPerMonth[1] = 29;
                    }
                    if (day > daysPerMonth[month - 1]) {
                        return false;
                    }
                }
                return true;
            }

        });
    }(jQuery));

</script>