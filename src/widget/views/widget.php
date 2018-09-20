<!-- This file is used to markup the public-facing widget. -->

<div class="mailjet_widget_front_container">
    <?php
    extract($args);

    $locale = \MailjetPlugin\Includes\Mailjeti18n::getLocale();
    $language = \MailjetPlugin\Includes\Mailjeti18n::getCurrentUserLanguage();

    // Check the widget options
    $title = isset($instance[$locale]['title']) ? apply_filters('widget_title', $instance[$locale]['title']) : '';
    $emailLabel = !empty($instance[$locale]['language_mandatory_email']) ? apply_filters('widget_language_mandatory_email', $instance[$locale]['language_mandatory_email']) : \MailjetPlugin\Includes\Mailjeti18n::getTranslationsFromFile($locale, 'your@email.com');
    $buttonLabel = !empty($instance[$locale]['language_mandatory_button']) ? apply_filters('widget_language_mandatory_button', $instance[$locale]['language_mandatory_button']) : \MailjetPlugin\Includes\Mailjeti18n::getTranslationsFromFile($locale, 'Subscribe');
    ?>
    <div class="widget-text wp_widget_plugin_box">
    <?php
//    echo "From PO:";
//    _e('Subscribe', 'mailjet') ;
//    echo "<br>From DB:";
//    echo $emailLabel 
    ?>
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

                // Mailjet property type
                $propertyDataType = $this->propertyData[$contactPropertyId]['Datatype'];

                // Map mailjet property type to valid input type
                $inputType = $this->getInputType($propertyDataType);

                // Skip if this property is not added in admin part
                if (empty($contactPropertyId)) {
                    continue;
                }

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

                ?>
                <div class="form-group">
                    <input <?php echo $required ?> class="mj_form_property" type="<?php echo $inputType ?>" name="properties[<?php echo $contactPropertyId ?>]" <?php echo $value ?> placeholder="<?php echo $requiredStar; echo $placeholder ?>" style="display: <?php echo $display ?>">
                </div>
                <?php
            }
            ?>
            <input type="submit" value="<?php echo $buttonLabel ?>" onclick="mjSubmitWidgetForm(event)">
        </form>
        <span><?php echo $form_message; ?></span>
    </div>
</div>