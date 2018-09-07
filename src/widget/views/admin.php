<!-- This file is used to markup the administration form of the widget. -->
<div class="mailjet_widget_admin_container">

    <?php
    // Set widget defaults
    $defaults = array(
        'language_checkbox' => '',
        'title' => '',
        'list' => '',
    );
//echo "<pre>First";
//print_r($instance);
//echo "</pre>";
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
                        if (is_array($contactLists) && !empty($contactLists)) {
                            foreach ($contactLists as $contactList) {
//                            if($contactList['IsDeleted'] == 'false') {
                                $options[$contactList['ID']] = $contactList['Name'] . ' (' . $contactList['SubscriberCount'] . ')';
//                            }
                            }
                        }
                        // Loop through options and add each one to the select dropdown
                        foreach ($options as $key => $name) {
                            echo '<option value="' . esc_attr($key) . '" id="mjContactList_' . esc_attr($key) . '" ' . selected($list, $key, false) . '>' . $name . '</option>';
                        }
                        ?>
                    </select>
                </p>
            </div>
        </div>
        <?php
    }
    $advancedFormDefaults = array();
    for ($i = 0; $i <= 4; $i++) {
        // Original
        $advancedFormDefaults[] = 'contactProperties' . $i;
        $advancedFormDefaults[] = 'propertyDataType' . $i;
        $advancedFormDefaults[] = 'EnglishLabel' . $i;
        $advancedFormDefaults[] = 'FrenchLabel' . $i;
        $advancedFormDefaults[] = 'GermanLabel' . $i;
        $advancedFormDefaults[] = 'SpanishLabel' . $i;

        // Test
//        $advancedFormDefaults['contactProperties' . $i] = '';
//        $advancedFormDefaults['propertyDataType' . $i] = '';
//        $advancedFormDefaults['EnglishLabel' . $i]  = '';
//        $advancedFormDefaults['FrenchLabel' . $i]  = '';
//        $advancedFormDefaults['GermanLabel' . $i]  = '';
//        $advancedFormDefaults['SpanishLabel' . $i]  = '';
    }
//    array_push($advancedFormDefaults, 'language_mandatory_email');
    $advancedFormDefaults['language_mandatory_email'] = '';
    $advancedFormDefaults['language_mandatory_button'] = '';
//    $advancedFormDefaults['someTest'] = '';
//    array_push($advancedFormDefaults, 'language_mandatory_button');
//echo "<pre>Second";
//print_r($instance);
//echo "</pre>";
    extract(wp_parse_args((array) $instance[$admin_locale], $advancedFormDefaults));
    $defaultPlaceholder = 'Field label in ';
    $hiddenPlaceholder = 'Value for ';
    ?>

    <div id="advanced-form-link-wrap">
        <p>
            <span id="advanced-form-link" data-toggle="modal" data-target=".advanced-form-popup"><?php _e('Advanced form customization', 'mailjet') ?></span>
            <span id="advanced-form-link-info" data-toggle="tooltip" data-placement="bottom" title="<?php _e('Add more fields to your form (ex: First name, Last name, Birthday...) and customize the labels, error messages and confirmation email wordings.', 'mailjet'); ?>">(?)</span>
        </p>
    </div>

    <div class="modal fade advanced-form-popup" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg modal-mailjet-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel"><?php _e('Customize your subscription form', 'mailjet') ?></h4>
                </div>

                <div>
                    <!-- Nav tabs -->
                    <ul id="advanced-form-navs" class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href=".advanced-form-fields" aria-controls="advanced-form-fields" role="tab" data-toggle="tab"><?php _e('Form fields', 'mailjet') ?></a></li>
                        <li role="presentation"><a href=".advanced-form-validation-messages" aria-controls="advanced-form-validation-messages" role="tab" data-toggle="tab"><?php _e('Form validation messages', 'mailjet') ?></a></li>
                        <li role="presentation"><a href=".advanced-form-confirmation-email-content" aria-controls="advanced-form-confirmation-email-content" role="tab" data-toggle="tab"><?php _e('Confirmation email content', 'mailjet') ?></a></li>
                    </ul>

                    <!--Tab panes--> 
                    <div id="advanced-form-tabs" class="tab-content">
                        <!-- Form fields -->
                        <div role="tabpanel" class="tab-pane advanced-form-fields active container-fluid">
                            <p id="properties-info"><span><?php _e('You can add up to 5 contact properties to collect additional data', 'mailjet') ?></span></p>

                            <?php
                            $numberActiveLanguages = 0;
                            foreach ($languages as $language => $locale) {
                                if ($instance[$locale]['language_checkbox']) {
                                    $activeLanguages[] = $language;
                                }
                                $numberActiveLanguages += $instance[$locale]['language_checkbox'];
                            }
                            $maxWidth = 60;
                            $percent = $numberActiveLanguages > 0 ? $maxWidth / $numberActiveLanguages : $maxWidth;
                            ?>

<!--                            <span style="margin-left: 21%;">Type</span>
<span>Label of value in English</span>
<span>Label of value in French</span>
<span>Label of value in German</span>
<span>Label of value in Spanish</span>-->
                            <?php
                            $opened = 0;
                            $display = 'block';
                            for ($row = 0; $row <= 4; $row++) {
                                $nextRow = $row + 1;
                                $displayDelete = !empty(${'contactProperties' . $nextRow}) ? 'none' : 'block';
                                $contactPropertiesN = ${'contactProperties' . $row};
                                $propertyDataTypeN = ${'propertyDataType' . $row};

                                // The selected properties and one more default select is shown
                                // We do not need more default selects
                                if (!$contactPropertiesN && $opened) {

                                    //Todo Or hide next rows
                                    // depends on js
                                    $display = 'none';
//                                    break;
                                }

                                // There is no default select shown
                                if (!$contactPropertiesN && !$opened) {
                                    $opened++;
                                }
                                ?>
                                <div class="property" style="display: <?php echo $display ?>">
                                    <span class="floatLeft propertyLabel">Property#<?php echo $row + 1 ?></span>

                                    <!--Select property-->
                                    <div class="propertySelect floatLeft">
                                        <select class="selectProperty mjProperties" name="<?php echo $this->get_field_name($admin_locale . '[contactProperties' . $row . ']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[contactProperties' . $row . ']'); ?>">
                                            <option disabled selected value="0">Select a property</option>
                                            <option value="newProperty">Create new</option>
                                            <option disabled value="0"><?php echo str_repeat('-', 16) ?></option>
                                            <?php
                                            $options = array(
                                            );

                                            if (is_array($mailjetContactProperties) && !empty($mailjetContactProperties)) {
                                                foreach ($mailjetContactProperties as $key => $mailjetContactProperty) {
                                                    $options[$key] = $mailjetContactProperty;
                                                }
                                            }
                                            // Loop through options and add each one to the select dropdown
                                            foreach ($options as $key => $name) {
                                                echo '<option value="' . esc_attr($key) . '" id="mjContactProperty_' . esc_attr($key) . '" ' . selected($contactPropertiesN, $key, false) . '>' . $name . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="createNewProperties">
                                        <div class="newPropertyName floatLeft">
                                            <input type="text"  />
                                        </div>
                                        <div class="newPropertyType floatLeft">
                                            <select >
                                                <option>Text</option>
                                                <option>Int</option>
                                                <option>Date</option>
                                                <option>Bool</option>
                                            </select>
                                        </div>
                                        <span class="btn btn-default floatLeft saveNewPropertyButton" >Save</span>
                                        <span class="btn floatLeft cancelNewPropertyButton">Cancel</span>
                                    </div>
                                    <!--Display only if there is a selected option-->
                                    <div class="hiddenProperties" style="display: <?php echo $contactPropertiesN ? 'block' : 'none' ?>">
                                        <!--Select property DataType-->
                                        <div class="typeSelect floatLeft">
                                            <select class="propertyDataType" name="<?php echo $this->get_field_name($admin_locale . '[propertyDataType' . $row . ']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[propertyDataType' . $row . ']'); ?>">
                                                <?php
                                                $dataTypeOptions = array('Optional', 'Mandatory', 'Hidden');
                                                foreach ($dataTypeOptions as $key => $name) {
                                                    echo '<option value="' . esc_attr($key) . '" id="mjPropertyDataType_' . esc_attr($key) . '" ' . selected($propertyDataTypeN, $key, false) . '>' . $name . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <?php
                                        foreach ($languages as $language => $locale) {
                                            ${$language . 'LabelN'} = ${$language . 'Label' . $row};

                                            if ($instance[$locale]['language_checkbox'] != 1) {
                                                continue;
                                            }
                                            ?>
                                            <!--Languages label-->
                                            <div class="languageInput floatLeft" style="width: <?php echo $percent . '%' ?>">
                                                <input type="text" value="<?php echo ${$language . 'LabelN'} ?>"  name="<?php echo $this->get_field_name($admin_locale . '[' . $language . 'Label' . $row . ']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[' . $language . 'Label' . $row . ']'); ?>" placeholder="<?php echo $propertyDataTypeN != 2 ? $defaultPlaceholder . $language : $hiddenPlaceholder . $language ?>" />
                                            </div>
                                        <?php } ?>
                                        <div class="deleteProperty floatLeft" style="display: <?php echo $displayDelete ?>">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true" id=""></span>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <p class="customize-mandatory-email"><span><?php _e('Customize the placeholder text for the email address field:', 'mailjet') ?></span></p>
                            <div id="mandatory-wrap">
                                <span class="floatLeft mandatoryEmailLabel"><?php _e('Email address field placeholder text', 'mailjet'); ?></span>
                                <?php
                                foreach ($languages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    if ($instance[$locale]['language_checkbox'] != 1) {
                                        continue;
                                    }
                                    ?>
                                    <!--Languages label-->
                                    <div class="mandatoryEmailLanguageInput floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <label for="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_email]')); ?>"><?php echo $language ?></label>
                                        <input class="form-control" type="text" value="<?php if(esc_attr($language_mandatory_email)){ echo esc_attr($language_mandatory_email); }else { _e('your@email.com', 'mailjet');} ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[language_mandatory_email]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_email]')); ?>" placeholder="" />
                                    </div>
                                <?php } ?>
                                <p class="customize-mandatory-button"><span><?php _e('Customize the submission button label:', 'mailjet') ?></span></p>        
                                <span class="floatLeft mandatoryEmailLabel"><?php _e('For button label', 'mailjet'); ?></span>
                                <?php
                                foreach ($languages as $language => $locale) {
//                                    if($locale !== $admin_locale) {
//                                        echo "<pre>";
//                                        echo $locale."->";
//                                        var_dump(switch_to_locale( $locale ));
//                                        var_dump(get_locale());
//                                        echo "</pre>";
//                                    }
                                    if ($instance[$locale]['language_checkbox'] != 1) {
                                        continue;
                                    }
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    ?>
                                    <!--Languages label-->
                                    <div class="mandatoryButtonLanguage floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <label for="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_button]')); ?>"><?php echo $language ?></label>
                                        <input class="form-control" type="text" title="<?php if(esc_attr($language_mandatory_button)){ echo esc_attr($language_mandatory_button); }else { _e('Subscribe', 'mailjet');echo ';';} ?>"  value="<?php if(esc_attr($language_mandatory_button)){ echo esc_attr($language_mandatory_button); }else { _e('Subscribe', 'mailjet');} ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[language_mandatory_button]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_button]')); ?>" placeholder="" />
                                    </div>
                                <?php } ?>

                            </div>

                        </div>
                    <!--Tab 2-->
                        <!--Form validation messages-->
                        <div role="tabpanel" class="tab-pane advanced-form-validation-messages">
                            <p><span>You can customize error and success messages displayed to your users as they interact with the subscription form. Leave empty fields to use the default values.</span></p>

                            <div class="validation_messages_wrap">
                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <label for="<?php echo esc_attr($this->get_field_id('form_success_label')); ?>">Description</label>
                                        <div class="form-control validation_messages_labels" id="<?php echo esc_attr($this->get_field_id('form_success_label')); ?>">Form successfully submitted</div>
                                    </div>
                                    <?php
                                    foreach ($languages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        if ($instance[$locale]['language_checkbox'] != 1) {
                                            continue;
                                        }
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <label for="<?php echo esc_attr($this->get_field_id($locale . '[confirmation_email_message_input]')); ?>"><?php echo $language ?></label>
                                            <input class="form-control" type="text"  value="<?php echo esc_attr($confirmation_email_message_input); ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[confirmation_email_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[confirmation_email_message_input]')); ?>" placeholder="" />
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels">Subscription confirmed (displayed after the user has clicked the confirmation email)</div>
                                    </div>
                                    <?php
                                    foreach ($languages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        if ($instance[$locale]['language_checkbox'] != 1) {
                                            continue;
                                        }
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <input class="form-control" type="text"  value="<?php echo esc_attr($subscription_confirmed_message_input); ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[subscription_confirmed_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[subscription_confirmed_message_input]')); ?>" placeholder="" />
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels">Error: email field is empty</div>
                                    </div>
                                    <?php
                                    foreach ($languages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        if ($instance[$locale]['language_checkbox'] != 1) {
                                            continue;
                                        }
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <input class="form-control" type="text"  value="<?php echo esc_attr($empty_email_message_input); ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[empty_email_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[empty_email_message_input]')); ?>" placeholder="" />
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels">Error: the email address is already subscribed</div>
                                    </div>
                                    <?php
                                    foreach ($languages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        if ($instance[$locale]['language_checkbox'] != 1) {
                                            continue;
                                        }
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <input class="form-control" type="text"  value="<?php echo esc_attr($already_subscribed_message_input); ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[already_subscribed_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[already_subscribed_message_input]')); ?>" placeholder="" />
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels">Error: Invalid data format (this applies only for numbers and dates)</div>
                                    </div>
                                    <?php
                                    foreach ($languages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        if ($instance[$locale]['language_checkbox'] != 1) {
                                            continue;
                                        }
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <input class="form-control" type="text"  value="<?php echo esc_attr($invalid_data_format_message_input); ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[invalid_data_format_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[invalid_data_format_message_input]')); ?>" placeholder="" />
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels">Generic technical error message</div>
                                    </div>
                                    <?php
                                    foreach ($languages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        if ($instance[$locale]['language_checkbox'] != 1) {
                                            continue;
                                        }
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <input class="form-control" type="text"  value="<?php echo esc_attr($generic_technical_error_message_input); ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[generic_technical_error_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[generic_technical_error_message_input]')); ?>" placeholder="" />
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>
                        </div>
                        
                        <!--Confirmation email content-->
                        <div role="tabpanel" class="tab-pane advanced-form-confirmation-email-content">
                            <p><span>When a user fills in the form, they will receive an email containing a button they need to click on to confirm their subscription. You can customize the text of the confirmation email if you wish. Leave empty fields to use the default values.</span></p>

                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <label for="<?php echo esc_attr($this->get_field_id('email_subject_description')); ?>">Description</label>
                                    <div class="form-control validation_messages_labels" id="<?php echo esc_attr($this->get_field_id('email_subject_description')); ?>">Email subject</div>
                                </div>
                                <?php
                                foreach ($languages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    if ($instance[$locale]['language_checkbox'] != 1) {
                                        continue;
                                    }
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <label for="<?php echo esc_attr($this->get_field_id($locale . '[email_subject]')); ?>"><?php echo $language ?></label>
                                        <!--<input class="form-control" type="text"  value="<?php echo esc_attr($email_subject); ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[email_subject]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_subject]')); ?>" placeholder="" />-->
                                        <textarea class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_subject]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_subject]')); ?>"><?php echo esc_attr($email_subject); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels">Email content: title</div>
                                </div>
                                <?php
                                foreach ($languages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    if ($instance[$locale]['language_checkbox'] != 1) {
                                        continue;
                                    }
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_title]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_title]')); ?>"><?php echo esc_attr($email_content_title); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels">Email content: main text </div>
                                </div>
                                <?php
                                foreach ($languages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    if ($instance[$locale]['language_checkbox'] != 1) {
                                        continue;
                                    }
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_main_text]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_main_text]')); ?>"><?php echo esc_attr($email_content_main_text); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels">Email content: confirmation button label</div>
                                </div>
                                <?php
                                foreach ($languages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    if ($instance[$locale]['language_checkbox'] != 1) {
                                        continue;
                                    }
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_confirm_button]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_confirm_button]')); ?>"><?php echo esc_attr($email_content_confirm_button); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels">Email content: text after the button</div>
                                </div>
                                <?php
                                foreach ($languages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    if ($instance[$locale]['language_checkbox'] != 1) {
                                        continue;
                                    }
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_after_button]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_after_button]')); ?>"><?php echo esc_attr($email_content_after_button); ?></textarea>
                                    </div>
                                <?php } 
                                switch_to_locale( $admin_locale );
                                ?>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-backdrop="false">Cancel</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-backdrop="false" id="saveAdvancedForm">Save&Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
