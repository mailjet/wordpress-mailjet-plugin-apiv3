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
    for($i=0;$i<=4;$i++) {
        $advancedFormDefaults[] = 'contactProperties'.$i;
        $advancedFormDefaults[] = 'propertyDataType'.$i;
        $advancedFormDefaults[] = 'EnglishLabel'.$i;
        $advancedFormDefaults[] = 'FrenchLabel'.$i;
        $advancedFormDefaults[] = 'GermanLabel'.$i;
        $advancedFormDefaults[] = 'SpanishLabel'.$i;
    }
//        'propertyDataType',
//        'englishLabel'
    extract(wp_parse_args((array) $instance[$admin_locale], $advancedFormDefaults));
//    echo "<pre>";print_r($instance);
//    for($i=0;$i<=4;$i++) {
//        echo ${'contactProperties'.$i};
//    }
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
                            $opened = 0;
                            $display = 'block';
                            for($row=0;$row<=4;$row++) {
                                $contactPropertiesN = ${'contactProperties'.$row};
                                $propertyDataTypeN = ${'propertyDataType'.$row};

                                // The selected properties and one more default select is shown
                                // We do not need more default selects
                                if(!$contactPropertiesN && $opened) {
                                    
                                    //Todo Or hide next rows
                                    // depends on js
                                    $display = 'none';
//                                    break;
                                }

                                // There is no default select shown
                                if(!$contactPropertiesN && !$opened) {
                                    $opened++;
                                }
                                
                            ?>
                            <div class="property" style="display: <?php echo $display ?>">
                                <span class="floatLeft propertyLabel">Property#<?php echo $row ?></span>

                                <!--Select property-->
                                <div class="propertySelect floatLeft">
                                    <select class="selectProperty mjProperties" name="<?php echo $this->get_field_name($admin_locale . '[contactProperties'.$row.']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[contactProperties'.$row.']'); ?>">
                                    <!--<option disabled selected value>Select a property</option>-->
                                    <option value>Select a property</option>
                                    <?php
                                    $options = array(
                                        'newProperty' => __('Create new', 'mailjet'),
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
                                <!--Display only if there is a selected option-->
                                <div class="hiddenProperties" style="display: <?php echo $contactPropertiesN ? 'block': 'none' ?>">
                                    <!--Select property DataType-->
                                    <div class="typeSelect floatLeft">
                                        <select class="propertyDataType" name="<?php echo $this->get_field_name($admin_locale . '[propertyDataType'.$row.']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '[propertyDataType'.$row.']'); ?>">
                                            <?php 
                                            $dataTypeOptions = array('Optional', 'Mandatory', 'Hidden');
                                            foreach ($dataTypeOptions as $key => $name) {
                                                echo '<option value="' . esc_attr($key) . '" id="mjPropertyDataType_' . esc_attr($key) . '" ' . selected($propertyDataTypeN, $key, false) . '>' . $name . '</option>';
                                            } ?>
                                        </select>
                                    </div>
                                    <?php
                                    $numberActiveLanguages = 0;
                                    foreach ($languages as $language => $locale) {
                                        $numberActiveLanguages+= $instance[$locale]['language_checkbox'];
                                    }
                                    $maxWidth = 60;
                                    $percent = $numberActiveLanguages > 0 ? $maxWidth/$numberActiveLanguages : $maxWidth;
    //                                echo "<pre>";var_dump($instance);

                                    foreach ($languages as $language => $locale) {
                                        ${$language.'LabelN'} = ${$language.'Label'.$row};

                                        if($instance[$locale]['language_checkbox'] != 1) {
                                            continue;
                                        }
                                    ?>
                                    <!--Languages label-->
                                    <div class="languageInput floatLeft" style="width: <?php echo $percent.'%' ?>">
                                        <input type="text" value="<?php echo ${$language.'LabelN'} ?>"  name="<?php echo $this->get_field_name($admin_locale . '['. $language.'Label'.$row.']'); ?>" id="<?php echo $this->get_field_id($admin_locale . '['. $language.'Label'.$row.']'); ?>"/>
                                    </div>
                                    <?php } ?>
                                    <div class="deleteProperty floatLeft">
                                        <span class="glyphicon glyphicon-trash" aria-hidden="true" id=""></span>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <!--Form validation messages-->
                        <div role="tabpanel" class="tab-pane advanced-form-validation-messages">2</div>
                        <!--Confirmation email content-->
                        <div role="tabpanel" class="tab-pane advanced-form-confirmation-email-content">3</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-backdrop="false">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAdvancedForm">Send message</button>
                </div>
            </div>
        </div>
    </div>

</div>