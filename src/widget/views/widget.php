<?php

namespace MailjetWp;

use MailjetWp\MailjetPlugin\Includes\Mailjeti18n;
?>

<!-- This file is used to markup the public-facing widget. -->
<div class="mailjet_widget_front_container">
    <?php

extract($args);
$locale = Mailjeti18n::getLocale();
// Check if selected locale checkbox is not set
if (!(isset($instance[$locale], $instance[$locale]['language_checkbox']) && $instance[$locale]['language_checkbox'])) {
    // Find other selected language locale
    $selectedLocales = \array_filter($instance, function ($localeInstance) {
        return isset($localeInstance['language_checkbox']) && 1 === $localeInstance['language_checkbox'];
    });
    if ($selectedLocales) {
        $locale = \array_keys($selectedLocales)[0];
    }
}
$language = Mailjeti18n::getCurrentUserLanguage($locale);
// Check the widget options
$title = isset($instance[$locale]['title']) ? apply_filters('widget_title', $instance[$locale]['title']) : '';
$emailLabel = !empty($instance[$locale]['language_mandatory_email']) ? apply_filters('widget_language_mandatory_email', $instance[$locale]['language_mandatory_email']) : Mailjeti18n::getTranslationsFromFile($locale, 'your@email.com');
$buttonLabel = !empty($instance[$locale]['language_mandatory_button']) ? apply_filters('widget_language_mandatory_button', $instance[$locale]['language_mandatory_button']) : Mailjeti18n::getTranslationsFromFile($locale, 'Subscribe');
?>

    <!--Widget title-->
    <div id="mailjet-widget-title-wrap">
            <?php 
do_action('before_title_widget_mailjet');
echo wp_kses_post($before_title . $title . $after_title);
do_action('after_title_widget_mailjet');
?>
    </div>
    <!--End Widget title-->

    <!--Widget form-->
    <form method="post" action="" id="mailjetSubscriptionForm" name="<?php 
echo esc_html($widget_id);
?>">

        <!--Subscription email input(mandatory)-->
        <div class="mailjet-widget-form-group">
            <input type="email" name="subscription_email" id="mailjet_widget_email" required="required" placeholder="* <?php 
echo esc_html($emailLabel);
?>">
            <input type="hidden" name="subscription_locale" id="mailjet_widget_locale" value="<?php 
echo esc_html($locale);
?>">
            <input type="hidden" name="action" value="send_mailjet_subscription_form">
        </div>
        <?php
            for ($i = 0; $i < 5; $i++) {
                if (!isset($instance[$locale])) {
                    continue;
                }
                // Property id - '0' there is no selected property
                $contactPropertyId = (int)$instance[$locale]['contactProperties' . $i];
                // Skip if this property is not added in admin part
                if (empty($contactPropertyId) || empty($this->propertyData[$contactPropertyId])) {
                    continue;
                }

                $propertyDataType = $this->propertyData[$contactPropertyId]['Datatype'];
                // Mailjet property type
                $labelValue = $instance[$locale][$language . 'Label' . $i];
                $propertyType = (int)$instance[$locale]['propertyDataType' . $i];
                // '0' - optional, '1' - mandatory, '2' - hidden
                $isHidden = $propertyType === 2;
                $isMandatory = $propertyType === 1;
                $inputProperties = $this->getInputProperties($propertyDataType, $labelValue, $isHidden, $isMandatory);
                if ('bool' === $inputProperties['type']) {
                    $required = isset($inputProperties['required']) ? $inputProperties['required'] : '';
                    ?>
                    <div class="mailjet-widget-form-group">
                        <input class="mj_form_property" type="checkbox" <?php
                        echo esc_html($required);
                        ?> name="properties[<?php
                        echo esc_html($contactPropertyId);
                        ?>]" id="mailjet_property_<?php
                        echo esc_html($i);
                        ?>"/>
                        <label for="mailjet_property_<?php
                        echo esc_html($i);
                        ?>" class="mailjet-widget-label">
                            <?php
                            echo esc_html($inputProperties['placeholder']);
                            ?>
                        </label>
                    </div>
                    <?php
                } else {
                    $inputPropertiesString = '';
                    foreach ($inputProperties as $propKey => $propValue) {
                        $inputPropertiesString .= "{$propKey}=\"{$propValue}\"";
                    }
                    $additionalDivClass = 'date' === $inputProperties['type'] ? 'mailjet-widget-form-date' : '';
                    ?>
                    <div class="mailjet-widget-form-group <?php
                    echo esc_html($additionalDivClass);
                    ?>">
                        <?php
                        if ('date' === $inputProperties['type']) {
                            ?>
                            <label for="mailjet_property_<?php
                            echo esc_html($i);
                            ?>" class="mailjet-widget-label mj-widget-label-date">
                                <?php
                                echo esc_attr($inputProperties['placeholder']);
                                ?>
                            </label>
                            <?php
                        }
                        ?>
                        <input class="mj_form_property" name="properties[<?php
                        echo esc_html($contactPropertyId);
                        ?>]" <?php
                        echo esc_html($inputPropertiesString);
                        ?>>
                    </div>
                    <?php
                }
            }
        ?>
        <input type="hidden" name="widget_id" value="<?php 
echo esc_html($widget_id);
?>">
        <input type="submit" value="<?php 
echo esc_html($buttonLabel);
?>">
    </form>
    <span class="mailjet_widget_form_message"></span>
</div>
<?php 
