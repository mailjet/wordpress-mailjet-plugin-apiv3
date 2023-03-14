<?php

namespace MailjetWp;

extract(wp_parse_args($instance, $defaults), EXTR_OVERWRITE);
?>


<div class="mailjet_widget_admin_container">

    <div>
        <label class="language-title-label">
            <?php _e('Insert you Form code here', 'mailjet-for-wordpress'); ?>
        </label>
        <div>
            <textarea
                    placeholder="Form Builder HTML code"
                    class="form_builder_text_area"
                    name="<?php echo $this->get_field_name('form_builder_code'); ?>"><?php echo esc_attr( $form_builder_code ); ?></textarea>
        </div>
    </div>
    <!--SHORTCODE-->
    <div class="some-space" style="background: #fbfbfb;border: 1px solid #c8d1d4;border-radius: 4px;">
    <span class="span_mailjet_subscribe_shortcode">
        <?php _e('Add the following shortcode anywhere in your Posts or Pages to display the widget', 'mailjet-for-wordpress'); ?>
    </span>
        <div class="mj-copy-wrapper mailjet_subscribe_shortcode-copy-wrapper">
            <input
                    class="mailjet_subscribe_shortcode"
                    name="mailjet_form_builder_shortcode_<?php echo esc_attr($this->id);?>"
                    id="mailjet_form_builder_shortcode_<?php echo esc_attr($this->id);?>"
                    value='[mailjet_form_builder widget_id="<?php echo esc_attr(substr($this->id, strpos($this->id, '-') + 1)); ?>"]'
                    disabled="disabled"
            />
            <i class="copy_mailjet_shortcode fa fa-copy mj-copy-icon"
               data-input_id="mailjet_form_builder_shortcode_<?php echo esc_attr($this->id); ?>"
               id="copy_mailjet_shortcode_<?php echo esc_attr($this->id);?>"
               style="width:12px;">

            </i>
        </div>
    </div>

</div>


<?php 
