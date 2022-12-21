<div class="bottom_links">
    <div class="needHelpDiv">
        <img src=" <?php

        echo plugin_dir_url(dirname(__FILE__, 2)) . '/admin/images/need_help.png';
        ?>" alt="<?php
        echo esc_textarea(__('Need help?', 'mailjet-for-wordpress'));
        ?>"/>
        <?php
        echo esc_textarea(__('Need help?', 'mailjet-for-wordpress'));
        ?>
    </div>
    <?php
    echo '<a target="_blank" href="' . get_query_var('userGuideLink') . '">' . __('Read our user guide', 'mailjet-for-wordpress') . '</a>';
    ?>
    <?php
    echo '<a target="_blank" href="' . get_query_var('supportLink') . '">' . __('Contact our support team', 'mailjet-for-wordpress') . '</a>';
    ?>
</div>
<div>
    <?php
    echo \sprintf(__('If you like Mailjet please support us with a %s rating on WordPress.org. Thank you', 'mailjet-for-wordpress'), '<a href="https://wordpress.org/support/plugin/mailjet-for-wordpress/reviews/?rate=5#new-post" target="_blank"> &#9733;&#9733;&#9733;&#9733;&#9733;</a>');
    ?>
</div><?php 
