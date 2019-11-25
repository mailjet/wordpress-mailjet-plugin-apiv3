<p>
    <?php
    if ($validApiCredentials == false) {
       _e('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys" target="_blank">https://app.mailjet.com/account/api_keys</a>', 'mailjet-for-wordpress');
    } else {
        _e('Could not connect to Mailjet API, please try again later!', 'mailjet-for-wordpress');
    }
    ?>
</p>