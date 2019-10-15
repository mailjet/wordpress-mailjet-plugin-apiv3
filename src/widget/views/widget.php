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

    <!--Widget title-->
    <div id="mailjet-widget-title-wrap">
            <?php
                do_action('before_title_widget_mailjet');
                echo $before_title . $title . $after_title;
                do_action('after_title_widget_mailjet');
            ?>
    </div>
    <!--End Widget title-->

    <!--Widget form-->
    <form method="post" action=" <?php echo esc_url($_SERVER['REQUEST_URI']) ?>" id="mjForm" name="<?php echo $widget_id ?>">

        <!--Subscription email input(mandatory)-->
        <div class="mailjet-widget-form-group">
            <input type="email" name="subscription_email" id="mailjet_widget_email" required="required" placeholder="* <?php echo $emailLabel ?>">
            <input type="hidden" name="subscription_locale" id="mailjet_widget_locale" value="<?php echo $locale ?>">
        </div>
        <?php

        // Check for the additional properties from the admin advanced settings
        for ($i = 0; $i < 5; $i++) {

            if(!isset($instance[$locale])) {
                continue;
            }

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
            if ('date' === $inputType) {
                $class = 'mjDate';
            }

            // Boolean type is checkbox
            if ('bool' === $inputType) {
                ?>
                <div class="mailjet-widget-form-group">
                    <input type="checkbox" <?php echo $required ?> name="properties[<?php echo $contactPropertyId ?>]" id="mailjet_property_<?php echo $i ?>" <?php echo $value ?> />
                    <label for="mailjet_property_<?php echo $i ?>" class="mailjet-widget-label">
                        <?php echo $requiredStar.$placeholder ?>
                    </label>
                </div>
                <?php
            } else {
            ?>
            <div class="mailjet-widget-form-group">
                <input <?php echo $required ?> type="text" class="mj_form_property <?php echo $class ?>" name="properties[<?php echo $contactPropertyId ?>]" <?php echo $value ?> placeholder="<?php
                echo $requiredStar;
                echo $placeholder
                ?>" style="display: <?php echo $display ?>">
            </div>
            <?php
            }
        }
        ?>
        <input type="hidden" name="widget_id" value="<?php echo $widget_id ?>">
        <input type="submit" value="<?php echo $buttonLabel ?>">
    </form>
    <?php
    if (!empty($form_message[$widget_id])){
        echo '<span class="mailjet_widget_form_message">'. $form_message[$widget_id] .'</span>';
    }
    ?>
</div>
