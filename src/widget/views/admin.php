<!-- This file is used to markup the administration form of the widget. -->
<div class="mailjet_widget_admin_container">
    <?php
    // Set widget defaults
    $defaults = array(
        'language_checkbox' => '',
        'title' => '',
        'list' => ''
    );

    foreach ($languages as $language => $locale) {
        extract(wp_parse_args((array) $instance[$locale], $defaults));
    ?>
    <div class="language-wrap">
        <input id="<?php echo esc_attr($this->get_field_id($locale . '[language_checkbox]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale . '[language_checkbox]')); ?>" type="checkbox" class="language_checkbox" <?php checked('1', $language_checkbox); ?> />
        <label class="language-label" for="<?php echo esc_attr($this->get_field_id($locale . '[language_checkbox]')); ?>"><?php _e($language, 'mailjet'); ?></label>

        <div class="hidden_default" id="hidden_<?php echo esc_attr($this->get_field_id($locale . '[language_checkbox]')); ?>">
            <hr>
            <p>
                <label class="language-title-label" for="<?php echo esc_attr($this->get_field_id($locale . '[title]')); ?>"><?php _e('Title of the form:', 'mailjet'); ?></label>
                <input class="widefat title-input" id="<?php echo esc_attr($this->get_field_id($locale . '[title]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale . '[title]')); ?>" type="text" placeholder="<?php _e('Sign up to receive the newsletter', 'mailjet'); ?>" value="<?php echo esc_attr($title); ?>"/>
            </p>
            <p>
                <label class="language-title-label" for="<?php echo $this->get_field_id($locale . '[list]'); ?>"><?php _e('Add email addresses to:', 'mailjet'); ?></label>
                <select name="<?php echo $this->get_field_name($locale . '[list]'); ?>" id="<?php echo $this->get_field_id($locale . '[list]'); ?>" class="widefat dropdown-list">
                    <?php
                    $options = array(
                        '' => __('Choose a list', 'mailjet'),
                    );
                    if(is_array($contactLists) && !empty($contactLists)) {
                        foreach($contactLists as $contactList) {
//                            if($contactList['IsDeleted'] == 'false') {
                                $options[$contactList['ID']] = $contactList['Name'] . ' (' . $contactList['SubscriberCount'].')';
//                            }
                        }
                    }
                    // Loop through options and add each one to the select dropdown
                    foreach ($options as $key => $name) {
                        echo '<option value="' . esc_attr($key) . '" id="' . esc_attr($key) . '" ' . selected($list, $key, false) . '>' . $name . '</option>';
                    }
                    ?>
                </select>
            </p>
        </div>
    </div>
    <?php } ?>
    <div>
        <p>
            <span id="advanced-form-link"><?php _e('Advanced form customization', 'mailjet') ?></span>
            <span id="advanced-form-link-info" title="<?php _e('Add more fields to your form (ex: First name, Last name, Birthday...) and customize the labels, error messages and confirmation email wordings.', 'mailjet'); ?>">(?)</span>
        </p>
    </div>
</div>