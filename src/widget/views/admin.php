<!-- This file is used to markup the administration form of the widget. -->

<div class="mailjet_widget_admin_container">
    <?php
    // Set widget defaults
    $defaults = array(
        'title' => '',
        'input1' => '',
        'input2' => '',
        'input3' => '',
        'input4' => '',
    );

    // Parse current settings with defaults
    extract(wp_parse_args((array) $instance[$locale], $defaults)); ?>
    <input class="widefat" id="<?php echo esc_attr($this->get_field_id($locale.'[hidden]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale.'[hidden]')); ?>" type="hidden" value="TEST TITLE HIDDEN" />
    <?php // Widget Title ?>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id($locale.'[title]')); ?>"><?=$locale?> - <?php _e('Widget Title','mailjet'); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id($locale.'[title]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale.'[title]')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>

    <?php // Text Field ?>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id($locale.'[input1]')); ?>"><?php _e('Text:','mailjet'); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id($locale.'[input1]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale.'[input1]')); ?>" type="text" value="<?php echo esc_attr($input1); ?>" />
    </p>

    <?php // Textarea Field ?>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id($locale.'[input2]')); ?>"><?php _e('Textarea:','mailjet'); ?></label>
        <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id($locale.'[input2]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale.'[input2]')); ?>"><?php echo wp_kses_post($input2); ?></textarea>
    </p>

    <?php // Checkbox ?>
    <p>
        <input id="<?php echo esc_attr($this->get_field_id($locale.'[input3]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale.'[input3]')); ?>" type="checkbox" value="1" <?php checked('1', $input3); ?> />
        <label for="<?php echo esc_attr($this->get_field_id($locale.'[input3]')); ?>"><?php _e('Checkbox','mailjet'); ?></label>
    </p>

    <?php // Dropdown ?>
    <p>
        <label for="<?php echo $this->get_field_id($locale.'[input4]'); ?>"><?php _e('Select','mailjet'); ?></label>
        <select name="<?php echo $this->get_field_name($locale.'[input4]'); ?>" id="<?php echo $this->get_field_id($locale.'[input4]'); ?>" class="widefat">
            <?php
            // Your options array
            $options = array(
                ''        => __('Select', 'mailjet'),
                'option_1' => __('Option 1', 'mailjet'),
                'option_2' => __('Option 2', 'mailjet'),
                'option_3' => __('Option 3', 'mailjet'),
            );

            // Loop through options and add each one to the select dropdown
            foreach ($options as $key => $name) {
                echo '<option value="' . esc_attr($key) . '" id="' . esc_attr($key) . '" '. selected($input4, $key, false) . '>'. $name . '</option>';
            } ?>
        </select>
    </p>

</div>
