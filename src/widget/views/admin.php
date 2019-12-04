<?php

use MailjetPlugin\Includes\Mailjeti18n;

$activeLanguages = array();
foreach ($languages as $language => $locale) {
    if (isset($instance[$locale]) && isset($instance[$locale]['language_checkbox']) && $instance[$locale]['language_checkbox']) {
        $activeLanguages[$language] = $locale;
    }
}

$numberActiveLanguages = count($activeLanguages);
//$pxPerLang = 136;
//$totalWidth = $numberActiveLanguages * $pxPerLang;
//$percent = $totalWidth/$numberActiveLanguages.'px';
$maxWidth = 60;
if($numberActiveLanguages == 1) {
    $percent = 30;
} else {
    $percent = $numberActiveLanguages > 0 ? $maxWidth / $numberActiveLanguages : $maxWidth;
}
?>
<!-- This file is used to markup the administration form of the widget. -->
<div id="chooseLangLabelWrap"><span id="chooseLangLabel"><?php _e('Choose the languages supported by your subscription form:', 'mailjet-for-wordpress'); ?></span></div>
<div class="mailjet_widget_admin_container">
    <?php
    // Set widget defaults
    $defaults = array(
        'language_checkbox' => '',
        'title' => '',
        'list' => '',
    );

    $this->registerCustomLanguageTranslations();
    foreach ($languages as $language => $locale) {
        $pass_args_data = isset($instance[$locale]) ? $instance[$locale]: array();
        extract(wp_parse_args($pass_args_data, $defaults));
        ?>
        <div class="language-wrap">
            <input id="<?php echo esc_attr($this->get_field_id($locale . '[language_checkbox]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale . '[language_checkbox]')); ?>" type="checkbox" class="language_checkbox" <?php checked('1', $language_checkbox); ?> />
            <label class="language-label" for="<?php echo esc_attr($this->get_field_id($locale . '[language_checkbox]')); ?>"><?php _e($language, 'mailjet-for-wordpress'); ?></label>

            <div class="hidden_default" id="hidden_<?php echo esc_attr($this->get_field_id($locale . '[language_checkbox]')); ?>">
                <hr>
                <p>
                    <label class="language-title-label" for="<?php echo esc_attr($this->get_field_id($locale . '[title]')); ?>"><?php _e('Title of the form:', 'mailjet-for-wordpress'); ?></label>
                    <input class="widefat title-input" id="<?php echo esc_attr($this->get_field_id($locale . '[title]')); ?>" name="<?php echo esc_attr($this->get_field_name($locale . '[title]')); ?>" type="text" placeholder="<?php _e('Sign up to receive the newsletter', 'mailjet-for-wordpress'); ?>" value="<?php echo esc_attr($title); ?>"/>
                </p>
                <p>
                    <label class="language-title-label" for="<?php echo $this->get_field_id($locale . '[list]'); ?>"><?php _e('Add email addresses to:', 'mailjet-for-wordpress'); ?></label>
                    <select name="<?php echo $this->get_field_name($locale . '[list]'); ?>" id="<?php echo $this->get_field_id($locale . '[list]'); ?>" class="widefat dropdown-list language-select-list">
                        <?php
                        $options = array(
                            0 => __('Choose a list', 'mailjet-for-wordpress'),
                        );
                        if (is_array($contactLists) && !empty($contactLists)) {
                            foreach ($contactLists as $contactList) {
                                $options[$contactList['ID']] = $contactList['Name'] . ' (' . $contactList['SubscriberCount'] . ')';
                            }
                        }
                        // Loop through options and add each one to the select dropdown
                        foreach ($options as $key => $name) {
                                echo '<option  value="' . esc_attr($key) . '" ' . selected($list, $key, false) . '>' . $name . '</option>';
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
        $advancedFormDefaults['contactProperties' . $i] = '';
        $advancedFormDefaults['propertyDataType' . $i] = '';
        $advancedFormDefaults['EnglishLabel' . $i] = '';
        $advancedFormDefaults['FrenchLabel' . $i] = '';
        $advancedFormDefaults['GermanLabel' . $i] = '';
        $advancedFormDefaults['SpanishLabel' . $i] = '';
        $advancedFormDefaults['ItalianLabel' . $i] = '';
    }
    $advancedFormDefaults['language_mandatory_email'] = '';
    $advancedFormDefaults['language_mandatory_button'] = '';
    $advancedFormDefaults['thank_you'] = '';

    $pass_args_data = isset($instance[$admin_locale]) ? $instance[$admin_locale]: array();
    extract(wp_parse_args($pass_args_data, $advancedFormDefaults));
    $defaultPlaceholder = 'Field label in ';
    $hiddenPlaceholder = 'Value for ';

    $hideAdvancedLinkClass = $numberActiveLanguages === 0 ? 'hidden_default': '';
    $hideShortCodeSectionClass = $numberActiveLanguages === 0 ? 'hidden_default': '';
    ?>
    <div class="some-space advanced-form-link-wrap <?php echo $hideAdvancedLinkClass ?>">
        <p>
            <span id="advanced-form-link" data-toggle="modal" data-target=".<?php echo $this->id ?>"><?php _e('Advanced form customization', 'mailjet-for-wordpress') ?></span>
            <span id="advanced-form-link-info" data-toggle="tooltip" data-placement="bottom" title="<?php _e('Add more fields to your form (ex: First name, Last name, Birthday...) and customize the labels, error messages and confirmation email wordings.', 'mailjet-for-wordpress'); ?>">
            <svg viewBox="0 0 16 16" style="height: 12px;"><path d="M8 0C3.589 0 0 3.59 0 8c0 4.412 3.589 8 8 8s8-3.588 8-8c0-4.41-3.589-8-8-8zm0 13a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm.75-3.875V10h-1.5V7.667H8c.828 0 1.5-.698 1.5-1.556 0-.859-.672-1.555-1.5-1.555s-1.5.696-1.5 1.555H5C5 4.396 6.346 3 8 3s3 1.396 3 3.111c0 1.448-.958 2.667-2.25 3.014z"/></svg>
            </span>
        </p>
    </div>
    <div class="some-space disabled-advanced-link hidden_default" title="<?php _e('Save changes first', 'mailjet-for-wordpress'); ?>">
        <span><?php _e('Advanced form customization', 'mailjet-for-wordpress') ?></span>
    </div>

    <div class="modal fade <?php echo $this->id ?>" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" style="margin: 35px 0 0 165px!important;">
        <div class="modal-dialog modal-lg modal-mailjet-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel"><?php _e('Customize your subscription form', 'mailjet-for-wordpress') ?></h4>
                </div>

                <div>
                    <!-- Nav tabs -->
                    <ul id="advanced-form-navs" class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href=".advanced-form-fields" aria-controls="advanced-form-fields" role="tab" data-toggle="tab"><?php _e('Form fields', 'mailjet-for-wordpress') ?></a></li>
                        <li role="presentation"><a href=".advanced-form-validation-messages" aria-controls="advanced-form-validation-messages" role="tab" data-toggle="tab"><?php _e('Form validation messages', 'mailjet-for-wordpress') ?></a></li>
                        <li role="presentation"><a href=".advanced-form-confirmation-email-content" aria-controls="advanced-form-confirmation-email-content" role="tab" data-toggle="tab"><?php _e('Confirmation email content', 'mailjet-for-wordpress') ?></a></li>
                        <li role="presentation"><a href=".advanced-form-thank-you-page-tab" aria-controls="advanced-form-thank-you-page-tab" role="tab" data-toggle="tab"><?php _e('Thank you page', 'mailjet-for-wordpress') ?></a></li>
                    </ul>

                    <!--Tab panes--> 
                    <div id="advanced-form-tabs" class="tab-content">

                        <!-- TAB 1 - Form fields -->
                        <div role="tabpanel" class="tab-pane advanced-form-fields active container-fluid" >
                            <p id="properties-info" class="propertiesInfo"><span><?php _e('You can add up to 5 contact properties to collect additional data', 'mailjet-for-wordpress') ?></span></p>
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
                                    $display = 'none';
                                }

                                // There is no default select shown
                                if (!$contactPropertiesN && !$opened) {
                                    $opened++;
                                }
                                ?>
                                <div class="property" style="display: <?php echo $display ?>">
                                        <?php 
                                            $setLabelStyle = '';
                                            if($row===0) {
                                                $setLabelStyle = 'padding-top: 22px;';
                                            } 
                                            ?>
                                        <span class="floatLeft propertyLabel" style="<?php echo $setLabelStyle ?>"><?php _e('Property', 'mailjet-for-wordpress') ?> #<?php echo $row + 1 ?></span>
                                    <!--Select property-->
                                    <div class="propertySelect floatLeft form-group">
                                        <?php if($row==0) { ?>
                                        <label for="<?php echo esc_attr($this->get_field_id($admin_locale . '[contactProperties' . $row . ']')); ?>"><?php _e('Properties', 'mailjet-for-wordpress') ?></label>
                                        <?php } ?>
                                        <select class="selectProperty mjProperties form-control" name="<?php echo $this->get_field_name($admin_locale . '[contactProperties' . $row . ']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[contactProperties' . $row . ']'); ?>">
                                            <option disabled selected value="0"><?php _e('Select a property', 'mailjet-for-wordpress') ?></option>
                                            <option value="newProperty">+ <?php _e('Create new', 'mailjet-for-wordpress') ?></option>
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
                                        <div class="newPropertyName floatLeft form-group">
                                            <?php if($row === 0) { ?>
                                                <label for="<?php echo esc_attr($this->get_field_id('[newPropertyName' . $row . ']')); ?>"><?php _e('Name your property (no spaces)', 'mailjet-for-wordpress') ?></label>
                                            <?php } ?>
                                                <input type="text" class="form-conrol" id="<?php echo esc_attr($this->get_field_id('[newPropertyName' . $row . ']')); ?>" placeholder="<?php _e('Forbidden characters: space , * + - / &quot &#39; : [ ] ( ) &gt; &lt; = ; $ ', 'mailjet-for-wordpress') ?>"/>
                                        </div>
                                        <div class="newPropertyType floatLeft form-group col-md-2">
                                            <?php if($row === 0) { ?>
                                                <label for="<?php echo esc_attr($this->get_field_id('[newPropertyType' . $row . ']')); ?>"><?php _e('Property type', 'mailjet-for-wordpress') ?></label>
                                            <?php } ?>
                                            <select class="form-conrol" id="<?php echo esc_attr($this->get_field_id('[newPropertyType' . $row . ']')); ?>">
                                                <option value="str">String</option>
                                                <option value="int">Integer</option>
                                                <option value="float">Decimal</option>
                                                <option value="datetime">Date</option>
                                                <option value="bool">Boolean</option>
                                            </select>
                                        </div>
                                        <?php 
                                            $styleSaveCancel = '';
                                        if($row == 0 ) {
                                            $styleSaveCancel = 'margin-top: 20px;';
                                        } ?>
                                        <span class="btn btn-default floatLeft saveNewPropertyButton" style="<?php echo $styleSaveCancel ?>" ><?php _e('Save', 'mailjet-for-wordpress') ?></span>
                                        <span class="btn floatLeft cancelNewPropertyButton" style="<?php echo $styleSaveCancel ?>"><?php _e('Cancel', 'mailjet-for-wordpress') ?></span>
                                    </div>
                                    <!--Display only if there is a selected option-->
                                    <div class="hiddenProperties" style="display: <?php echo $contactPropertiesN ? 'block' : 'none' ?>">
                                        <!--Select property DataType-->
                                        <div class="typeSelect floatLeft form-group">
                                             <?php if($row==0) { ?>
                                            <label for="<?php echo $this->get_field_id($admin_locale . '[propertyDataType' . $row . ']'); ?>"><?php _e('Type', 'mailjet-for-wordpress') ?></label>
                                             <?php } ?>
                                            <select class="propertyDataType form-control" name="<?php echo $this->get_field_name($admin_locale . '[propertyDataType' . $row . ']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[propertyDataType' . $row . ']'); ?>">
                                                <?php
                                                $dataTypeOptions = array(
                                                    __('Optional', 'mailjet-for-wordpress'),
                                                    __('Mandatory', 'mailjet-for-wordpress'),
                                                    __('Hidden', 'mailjet-for-wordpress')
                                                );
                                                foreach ($dataTypeOptions as $key => $name) {
                                                    echo '<option value="' . esc_attr($key) . '" id="mjPropertyDataType_' . esc_attr($key) . '" ' . selected($propertyDataTypeN, $key, false) . '>' . $name . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <?php
                                        foreach ($activeLanguages as $language => $locale) {
                                            ${$language . 'LabelN'} = ${$language . 'Label' . $row}; ?>
                                            <!--Languages label-->
                                            <div class="languageInput floatLeft" style="width: <?php echo $percent . '%' ?>">
                                                <?php if($row==0) { ?>
                                                    <label for="<?php echo $this->get_field_id($admin_locale . '[' . $language . 'Label' . $row . ']'); ?>"><?php _e($language, 'mailjet-for-wordpress') ?></label>
                                                <?php } ?>
                                                <input type="text" value="<?php echo ${$language . 'LabelN'} ?>"  name="<?php echo $this->get_field_name($admin_locale . '[' . $language . 'Label' . $row . ']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[' . $language . 'Label' . $row . ']'); ?>" placeholder="<?php echo $propertyDataTypeN != 2 ? $defaultPlaceholder . $language : $hiddenPlaceholder . $language ?>" />
                                            </div>
                                        <?php }
                                            $setDeleteLabelStyle = '';
                                            if ($row === 0) {
                                                $setDeleteLabelStyle = 'padding-top:25px;';
                                            }
                                            ?>
                                        <div class="deleteProperty floatLeft" style="display: <?php echo $displayDelete.';'. $setDeleteLabelStyle ?>">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true" id=""></span>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <p class="customize-mandatory-email propertiesInfo"><span><?php _e('Customize the placeholder text for the email address field:', 'mailjet-for-wordpress') ?></span></p>
                            <div id="mandatory-wrap">
                                <span class="floatLeft mandatoryEmailLabel"><?php _e('Email address field placeholder text', 'mailjet-for-wordpress'); ?></span>
                                <?php
                                foreach ($activeLanguages as $language => $locale) {
                                    $yourEmailTranslation = Mailjeti18n::getTranslationsFromFile($locale, 'your@email.com');
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    ?>
                                    <!--Languages label-->
                                    <div class="mandatoryEmailLanguageInput floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <label for="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_email]')); ?>"><?php _e($language, 'mailjet-for-wordpress') ?></label>
                                        <input class="form-control" type="text" value="<?php echo $language_mandatory_email ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[language_mandatory_email]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_email]')); ?>" placeholder="<?php echo $yourEmailTranslation ?>" />
                                    </div>
                                <?php } ?>
                                <p class="customize-mandatory-button propertiesInfo"><span><?php _e('Customize the submission button label:', 'mailjet-for-wordpress') ?></span></p>        
                                <span class="floatLeft mandatoryEmailLabel"><?php _e('Button label', 'mailjet-for-wordpress'); ?></span>
                                <?php
                                foreach ($activeLanguages as $language => $locale) {
                                    $subscribeTranslation = Mailjeti18n::getTranslationsFromFile($locale, 'Subscribe');
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    ?>
                                    <!--Languages label-->
                                    <div class="mandatoryButtonLanguage floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <label for="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_button]')); ?>"><?php _e($language, 'mailjet-for-wordpress') ?></label>
                                        <input class="form-control" type="text" title=""  value="<?php echo $language_mandatory_button ?>"  name="<?php echo esc_attr($this->get_field_name($locale . '[language_mandatory_button]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[language_mandatory_button]')); ?>" placeholder="<?php echo $subscribeTranslation ?>" />
                                    </div>
                                <?php }
                                ?>
                            </div>
                        </div>

                        <!--Tab 2 - Form validation messages-->
                        <div role="tabpanel" class="tab-pane advanced-form-validation-messages" style="overflow-y: scroll;height: 502px;">
                            <p class="tab-info propertiesInfo"><span><?php _e('You can customize error and success messages displayed to your users as they interact with the subscription form. Leave empty fields to use the default values.', 'mailjet-for-wordpress') ?></span></p>

                            <div class="validation_messages_wrap">
                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <label for="<?php echo esc_attr($this->get_field_id('form_success_label')); ?>"><?php _e('Description', 'mailjet-for-wordpress') ?></label>
                                        <div class="form-control validation_messages_labels" id="<?php echo esc_attr($this->get_field_id('form_success_label')); ?>"><?php _e('Form successfully submitted', 'mailjet-for-wordpress') ?></div>
                                    </div>
                                    <?php
                                    $n = 0;
                                    $marginLeftByDefault = 'margin-left: 10px;';
                                    foreach ($activeLanguages as $language => $locale) {
                                        $n++;
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        $subscriptionConfirmationEmailSent = Mailjeti18n::getTranslationsFromFile($locale, 'Subscription confirmation email sent. Please check your inbox and confirm your subscription.');
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <label for="<?php echo esc_attr($this->get_field_id($locale . '[confirmation_email_message_input]')); ?>"><?php _e($language, 'mailjet-for-wordpress') ?></label>
                                            <textarea class="form-control form-validation-successfully-submited" name="<?php echo esc_attr($this->get_field_name($locale . '[confirmation_email_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[confirmation_email_message_input]')); ?>" placeholder="<?php echo $subscriptionConfirmationEmailSent ?>"><?php echo esc_attr($confirmation_email_message_input); ?></textarea>
                                        </div>
                                    <?php } ?>
                                </div>

<!--                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels"><?php _e('Subscription confirmed (displayed after the user has clicked the confirmation email)', 'mailjet-for-wordpress'); ?></div>
                                    </div>
                                    <?php
                                    foreach ($activeLanguages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        $subscriptionConfirmed = Mailjeti18n::getTranslationsFromFile($locale, 'Your subscription was successfully confirmed.');
                                        ?>
                                        Languages label
                                        <div class="floatLeft form-group"  style="width: <?php echo $percent . '%' ?>">
                                            <textarea class="form-control form-validation-confirmed" name="<?php echo esc_attr($this->get_field_name($locale . '[subscription_confirmed_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[subscription_confirmed_message_input]')); ?>" placeholder="<?php echo $subscriptionConfirmed ?>"><?php echo esc_attr($subscription_confirmed_message_input); ?></textarea>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels"><?php _e('Error: email field is empty', 'mailjet-for-wordpress') ?></div>
                                    </div>
                                    <?php
                                    foreach ($activeLanguages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        $provideEmail = Mailjeti18n::getTranslationsFromFile($locale, 'Please provide an email address');
                                        ?>
                                        Languages label
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <textarea class="form-control form-validation-email-empty"  name="<?php echo esc_attr($this->get_field_name($locale . '[empty_email_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[empty_email_message_input]')); ?>" placeholder="<?php echo $provideEmail ?>"><?php echo esc_attr($empty_email_message_input); ?></textarea>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels"><?php _e('Error: the email address is already subscribed', 'mailjet-for-wordpress') ?></div>
                                    </div>
                                    <?php
                                    foreach ($activeLanguages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        $emailAlreadySubscribed = Mailjeti18n::getTranslationsFromFile($locale, 'This email address has already been subscribed.');
                                        ?>
                                        Languages label
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <textarea class="form-control form-validation-email-already-subscribed" name="<?php echo esc_attr($this->get_field_name($locale . '[already_subscribed_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[already_subscribed_message_input]')); ?>" placeholder="<?php echo $emailAlreadySubscribed ?>"><?php echo esc_attr($already_subscribed_message_input); ?></textarea>
                                        </div>
                                    <?php } ?>
                                </div>-->

                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels"><?php _e('Error: Invalid data format (this applies only for numbers and dates)', 'mailjet-for-wordpress') ?></div>
                                    </div>
                                    <?php
                                    foreach ($activeLanguages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        $incorectValue = Mailjeti18n::getTranslationsFromFile($locale, 'The value you entered is not in the correct format.');
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <textarea class="form-control form-validation-invalid-data" name="<?php echo esc_attr($this->get_field_name($locale . '[invalid_data_format_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[invalid_data_format_message_input]')); ?>" placeholder="<?php echo $incorectValue ?>"><?php echo esc_attr($invalid_data_format_message_input); ?></textarea>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="validation_message_row">
                                    <div class="floatLeft">
                                        <div class="form-control validation_messages_labels"><?php _e('Generic technical error message', 'mailjet-for-wordpress') ?></div>
                                    </div>
                                    <?php
                                    foreach ($activeLanguages as $language => $locale) {
                                        extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                        $technicalIssue = Mailjeti18n::getTranslationsFromFile($locale, 'A technical issue has prevented your subscription. Please try again later.');
                                        ?>
                                        <!--Languages label-->
                                        <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                            <textarea class="form-control form-validation-generic-error" name="<?php echo esc_attr($this->get_field_name($locale . '[generic_technical_error_message_input]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[generic_technical_error_message_input]')); ?>" placeholder="<?php echo $technicalIssue ?>"><?php echo esc_attr($generic_technical_error_message_input); ?></textarea>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>
                        </div>

                        <!--TAB 3 - Confirmation email content-->
                        <div role="tabpanel" class="tab-pane advanced-form-confirmation-email-content" style="height: 502px;">
                            <p class="tab-info propertiesInfo"><span><?php _e('When a user fills in the form, they will receive an email containing a button they need to click on to confirm their subscription. You can customize the text of the confirmation email if you wish. Leave empty fields to use the default values.', 'mailjet-for-wordpress') ?></span></p>
                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <label for="<?php echo esc_attr($this->get_field_id('email_subject_description')); ?>"><?php _e('Description', 'mailjet-for-wordpress') ?></label>
                                    <div class="form-control validation_messages_labels" id="<?php echo esc_attr($this->get_field_id('email_subject_description')); ?>"><?php _e('Email subject', 'mailjet-for-wordpress') ?></div>
                                </div>
                                <?php
                                foreach ($activeLanguages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    $subscriptionConfirmation = Mailjeti18n::getTranslationsFromFile($locale, 'Subscription Confirmation');
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <label for="<?php echo esc_attr($this->get_field_id($locale . '[email_subject]')); ?>"><?php _e($language, 'mailjet-for-wordpress') ?></label>
                                        <textarea class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_subject]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_subject]')); ?>" placeholder="<?php echo $subscriptionConfirmation ?>"><?php echo esc_attr($email_subject); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels"><?php _e('Email content: title', 'mailjet-for-wordpress') ?></div>
                                </div>
                                <?php
                                foreach ($activeLanguages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    $confirmYourSubscription = Mailjeti18n::getTranslationsFromFile($locale, 'Please confirm your subscription');
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea placeholder="<?php echo $confirmYourSubscription ?>" class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_title]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_title]')); ?>"><?php echo esc_attr($email_content_title); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels"><?php _e('Email content: main text', 'mailjet-for-wordpress') ?></div>
                                </div>
                                <?php
                                foreach ($activeLanguages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    $toReceiveNewslettersFrom = Mailjeti18n::getTranslationsFromFile($locale, 'To receive newsletters from %s please confirm your subscription by clicking the following button:');
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea placeholder="<?php echo $toReceiveNewslettersFrom ?>" class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_main_text]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_main_text]')); ?>"><?php echo esc_attr($email_content_main_text); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels"><?php _e('Email content: confirmation button label', 'mailjet-for-wordpress') ?></div>
                                </div>
                                <?php
                                foreach ($activeLanguages as $language => $locale) {
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    $yesSubscribeMe = Mailjeti18n::getTranslationsFromFile($locale, 'Yes, subscribe me to this list');
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea placeholder="<?php echo $yesSubscribeMe ?>" class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_confirm_button]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_confirm_button]')); ?>"><?php echo esc_attr($email_content_confirm_button); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="confirmation_email_row">
                                <div class="floatLeft">
                                    <div class="form-control validation_messages_labels"><?php _e('Email content: text after the button', 'mailjet-for-wordpress') ?></div>
                                </div>
                                <?php
                                foreach ($activeLanguages as $language => $locale) {
                                    $ignoreMessage = Mailjeti18n::getTranslationsFromFile($locale, "If you received this email by mistake or don't wish to subscribe anymore, simply ignore this message.");
                                    extract(wp_parse_args((array) $instance[$locale], $advancedFormDefaults));
                                    ?>
                                    <!--Languages label-->
                                    <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                        <textarea placeholder="<?php echo $ignoreMessage ?>" class="form-control" name="<?php echo esc_attr($this->get_field_name($locale . '[email_content_after_button]')); ?>" id="<?php echo esc_attr($this->get_field_id($locale . '[email_content_after_button]')); ?>"><?php echo esc_attr($email_content_after_button); ?></textarea>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        
                        <!--TAB - 4 THANK YOU PAGE-->
                        <div role="tabpanel" class="tab-pane advanced-form-thank-you-page-tab" style="height: 240px;">
                            <p class="tab-info propertiesInfo">
                                <span><?php _e('Select a page from your WordPress site to show after successful subscription confirmation or leave empty to use the default "Thank you" page', 'mailjet-for-wordpress') ?></span>
                            </p>   
                            <?php
                            foreach ($activeLanguages as $language => $locale) {
                                extract(wp_parse_args((array) $instance[$language], $advancedFormDefaults));
                                ?>
                                <div class="floatLeft form-group" style="width: <?php echo $percent . '%' ?>">
                                    <label for="<?php echo esc_attr($this->get_field_id($language . '[thank_you]')); ?>"><?php _e($language, 'mailjet-for-wordpress') ?></label>
                                    <select class="thankYou_select form-control" id="<?php echo esc_attr($this->get_field_id($language . '[thank_you]')); ?>" name="<?php echo $this->get_field_name($language . '[thank_you]'); ?>">
                                        <option value="0"><?php _e('Default page', 'mailjet-for-wordpress') ?></option>
                                        <?php
                                        foreach ($pages as $page) { ?>
                                            <option value="<?php echo $page->ID ?>" id="thankYouOption_<?php echo $page->ID ?>" <?php echo selected($thank_you, $page->ID, false) ?> > <?php echo $page->post_title ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-backdrop="false"><?php _e('Cancel', 'mailjet-for-wordpress') ?></button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-backdrop="false" id="saveAdvancedForm"><?php _e('Save', 'mailjet-for-wordpress') ?></button>
                </div>
            </div>
        </div>
    </div>

    <!--SHORTCODE-->
    <div class="some-space <?php echo $hideShortCodeSectionClass ?>" style="background: #fbfbfb;border: 1px solid #c8d1d4;border-radius: 4px;">
        <span class="span_mailjet_subscribe_shortcode"><?php _e('Add the following shortcode anywhere in your Posts or Pages to display the widget', 'mailjet-for-wordpress') ?></span>
        <div class="mj-copy-wrapper mailjet_subscribe_shortcode-copy-wrapper">
            <input class="mailjet_subscribe_shortcode" name="mailjet_subscribe_shortcode_<?=$this->id?>" id="mailjet_subscribe_shortcode_<?=$this->id?>" value='[mailjet_subscribe widget_id="<?=substr($this->id, strpos($this->id, '-') + 1)?>"]' class="widefat" disabled="disabled"/>
            <i class="copy_mailjet_shortcode fa fa-copy mj-copy-icon" data-input_id="mailjet_subscribe_shortcode_<?=$this->id?>" id="copy_mailjet_shortcode_<?=$this->id?>" style="width:12px;" ></i>
        </div>
    </div>

</div>


