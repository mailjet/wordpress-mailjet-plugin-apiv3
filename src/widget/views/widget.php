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
    <?php echo "From PO:";
    _e('Subscribe', 'mailjet') ?>
        <?php echo "<br>From DB:";
        echo $emailLabel ?>
        <div id="mailjet_widget_title_wrap">
            <span id="mailjet_widget_title">
                <?php echo $before_title . $title . $after_title ?>
            </span>
        </div>
        <form method="post" action=" <?php echo esc_url($_SERVER['REQUEST_URI']) ?>">
            <div class="form-group">
                <input type="email" name="subscription_email" id="mailjet_widget_email" placeholder="<?php echo $emailLabel ?>">
            </div>
            <?php
            for ($i = 0; $i < 5; $i++) {

                // Property id - '0' there is no selected property
                $contactProperties = (int)$instance[$locale]['contactProperties' . $i];

                // Skip if this property is not added in admin part
                if (empty($contactProperties)) {
                    continue;
                }

                // The value of the label
                $placeholder = $instance[$locale][$language . 'Label' . $i];

                // '0' - optional, '1' - mandatory, '2' - hidden
                $propertyType = (int) $instance[$locale]['propertyDataType' . $i];
                $display = 'block';
                $required = '';
                $value = '';
                switch ($propertyType) {
                    case 0:
                        break;
                    case 1:
                        $required = 'required';
                        break;
                    case 2:
                        $display = 'none';
                        $value = $placeholder;
                        break;
                    default:
                        break;
                }

                ?>
                <div class="form-group">
                    <input <?php echo $required ?> type="text" name="properties[<?php echo $contactProperties ?>]" value="<?php echo $value ?>" placeholder="<?php echo $placeholder ?>" style="display: <?php echo $display ?>">
                </div>
                <?php
            }
            ?>
            <input type="submit" value="<?php echo $buttonLabel ?>">
        </form>
    </div>
</div>