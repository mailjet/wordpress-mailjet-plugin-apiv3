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
    <form method="post" action="" id="mailjetSubscriptionForm" name="<?php echo $widget_id ?>">

        <!--Subscription email input(mandatory)-->
        <div class="mailjet-widget-form-group">
            <input type="email" name="subscription_email" id="mailjet_widget_email" required="required" placeholder="* <?php echo $emailLabel ?>">
            <input type="hidden" name="subscription_locale" id="mailjet_widget_locale" value="<?php echo $locale ?>">
            <input type="hidden" name="action" value="send_mailjet_subscription_form">
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
            if (empty($contactPropertyId) || empty($this->propertyData[$contactPropertyId])) {
                continue;
            }

            $propertyDataType = $this->propertyData[$contactPropertyId]['Datatype']; // Mailjet property type
            $labelValue = $instance[$locale][$language . 'Label' . $i];
            $propertyType = (int) $instance[$locale]['propertyDataType' . $i]; // '0' - optional, '1' - mandatory, '2' - hidden
            $isHidden = $propertyType === 2;
            $isMandatory = $propertyType === 1;
            $inputProperties = $this->getInputProperties($propertyDataType, $labelValue, $isHidden, $isMandatory);

            if ('bool' === $inputProperties['type']) {
                $required = isset($inputProperties['required']) ? $inputProperties['required'] : '';
                ?>
                <div class="mailjet-widget-form-group">
                    <input type="checkbox" <?php echo $required ?> name="properties[<?php echo $contactPropertyId ?>]" id="mailjet_property_<?php echo $i ?>" />
                    <label for="mailjet_property_<?php echo $i ?>" class="mailjet-widget-label">
                        <?php echo $inputProperties['placeholder'] ?>
                    </label>
                </div>
                <?php
            } else {
                $inputPropertiesString = '';
                foreach ($inputProperties as $propKey => $propValue) {
                    $inputPropertiesString .= "$propKey=\"$propValue\"";
                }
                $additionalDivClass = 'date' === $inputProperties['type'] ? 'mailjet-widget-form-date' : '';
                ?>
                <div class="mailjet-widget-form-group <?php echo $additionalDivClass ?>">
                    <?php if ('date' === $inputProperties['type']) { ?>
                    <label for="mailjet_property_<?php echo $i ?>" class="mailjet-widget-label mj-widget-label-date">
                        <?php echo $inputProperties['placeholder'] ?>
                    </label>
                    <?php } ?>
                    <input class="mj_form_property" name="properties[<?php echo $contactPropertyId ?>]" <?php echo $inputPropertiesString ?>>
                </div>
                <?php
            }
        }
        ?>
        <input type="hidden" name="widget_id" value="<?php echo $widget_id ?>">
        <input type="submit" value="<?php echo $buttonLabel ?>">
    </form>
    <span class="mailjet_widget_form_message"></span>
</div>
