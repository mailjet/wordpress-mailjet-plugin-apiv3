<?php

namespace MailjetWp;

extract($args);
?>

<div class="mailjet_widget_form_builder_container">
    <div class="">
        <?php echo wp_kses($instance['form_builder_code'] ?? '', [
            'iframe' => [
                'align' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'name' => true,
                'src' => true,
                'id' => true,
                'class' => true,
                'style' => true,
                'scrolling' => true,
                'marginwidth' => true,
                'marginheight' => true,
                'data' => true,
                'data-w-type' => true,
                'data-w-token' => true,
            ],
            'script' => [
                'type' => true,
                'src' => true,
                'height' => true,
                'width' => true,
            ]
        ]); ?>
    </div>
</div>
<?php
