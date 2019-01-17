<p>
    <?php
    if ($validApiCredentials == false) {
        _e('Please make sure that you are using the correct API key and Secret key associated to your Mailjet account: <a href="https://app.mailjet.com/account/api_keys">https://app.mailjet.com/account/api_keys</a>', 'wp-mailjet');
    } else {
        _e('Could not connect to mailjet api, please try again later!', 'wp-mailjet');
    }
    ?>
</p>