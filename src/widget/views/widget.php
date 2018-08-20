<!-- This file is used to markup the public-facing widget. -->

<div class="mailjet_widget_front_container">
    <?php
    extract($args);

    $locale = \MailjetPlugin\Includes\Mailjeti18n::getLocale();

    // Check the widget options
    $title = isset($instance[$locale]['title']) ? apply_filters('widget_title', $instance[$locale]['title']) : '';
    $input1 = isset($instance[$locale]['input1']) ? $instance[$locale]['input1'] : '';
    $input2 = isset($instance[$locale]['input2']) ? $instance[$locale]['input2'] : '';
    $input3 = isset($instance[$locale]['input3']) ? $instance[$locale]['input3'] : '';
    $input4 = !empty($instance[$locale]['input4']) ? $instance[$locale]['input4'] : false;



    // Display the widget
    echo '<div class="widget-text wp_widget_plugin_box">';

        // Display widget title if defined
        if ($title) {
            echo $before_title . $title . $after_title;
        }

        // Display text field
        if ($input1) {
            echo '<p>' . $input1 . '</p>';
        }

        // Display textarea field
        if ($input2) {
            echo '<p>' . $input2 . '</p>';
        }

        // Display something if checkbox is true
        if ($input3) {
            echo __('<p>Something awesome</p>', 'mailjet');
        }

        // Display select field
        if ($input4) {
            echo '<p>' . $input4 . '</p>';
        }

    echo '</div>';

    ?>
</div>