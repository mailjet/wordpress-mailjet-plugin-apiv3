<!-- This file is used to markup the administration form of the widget. -->

<div class="mailjet_widget_admin_container">
    <?php
    // Set widget defaults
    $defaults = array(
        'title'    => '',
        'text'     => '',
        'textarea' => '',
        'checkbox' => '',
        'select'   => '',
    );

    // Parse current settings with defaults
    extract(wp_parse_args((array) $instance, $defaults)); ?>

    <?php // Widget Title ?>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Widget Title','mailjet'); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title ); ?>" />
    </p>

    <?php // Text Field ?>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('text')); ?>"><?php _e('Text:','mailjet'); ?></label>
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('text')); ?>" name="<?php echo esc_attr($this->get_field_name('text')); ?>" type="text" value="<?php echo esc_attr($text); ?>" />
    </p>

    <?php // Textarea Field ?>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('textarea')); ?>"><?php _e('Textarea:','mailjet'); ?></label>
        <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('textarea')); ?>" name="<?php echo esc_attr($this->get_field_name('textarea')); ?>"><?php echo wp_kses_post($textarea); ?></textarea>
    </p>

    <?php // Checkbox ?>
    <p>
        <input id="<?php echo esc_attr($this->get_field_id('checkbox')); ?>" name="<?php echo esc_attr($this->get_field_name('checkbox')); ?>" type="checkbox" value="1" <?php checked('1', $checkbox); ?> />
        <label for="<?php echo esc_attr($this->get_field_id('checkbox')); ?>"><?php _e('Checkbox','mailjet'); ?></label>
    </p>

    <?php // Dropdown ?>
    <p>
        <label for="<?php echo $this->get_field_id('select'); ?>"><?php _e('Select','mailjet'); ?></label>
        <select name="<?php echo $this->get_field_name('select'); ?>" id="<?php echo $this->get_field_id('select'); ?>" class="widefat">
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
                echo '<option value="' . esc_attr($key) . '" id="' . esc_attr($key) . '" '. selected($select, $key, false) . '>'. $name . '</option>';
            } ?>
        </select>
    </p>

</div>
