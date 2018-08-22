<!-- This file is used to markup the public-facing widget. -->

<div class="mailjet_widget_front_container">
    <?php
    extract($args);

    $locale = \MailjetPlugin\Includes\Mailjeti18n::getLocale();

    // Check the widget options
    $title = isset($instance[$locale]['title']) ? apply_filters('widget_title', $instance[$locale]['title']) : '';
    $list = isset($instance[$locale]['list']) ? $instance[$locale]['list'] : '';
//    $input2 = isset($instance[$locale]['input2']) ? $instance[$locale]['input2'] : '';
//    $input3 = isset($instance[$locale]['input3']) ? $instance[$locale]['input3'] : '';
//    $input4 = !empty($instance[$locale]['input4']) ? $instance[$locale]['input4'] : false;

//    'language_checkbox' => '',
//        'title' => '',
//        'list' => ''


    // Display the widget
    ?>
    <div class="widget-text wp_widget_plugin_box">
        <div id="mailjet_widget_title_wrap"><span id="mailjet_widget_title"><?php echo $before_title . $title . $after_title ?></span></div>
        <form method="post" action=" <?php echo esc_url( $_SERVER['REQUEST_URI'] ) ?>">
            <label for="front_email" id="mailjet_widget_email_label"><?php _e('Email', 'mailjet') ?></label>
            <input type="email" name="subscription_email" id="mailjet_widget_email" placeholder="<?php _e('Email', 'mailjet') ?>">

            <input type="hidden" name="mailjet_widget_form_submited">
            <input type="submit" value="<?php _e('Subscribe', 'mailjet') ?>">
        </form>
    </div>
</div>