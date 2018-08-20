# Mailjet iframe wrapper for PHP


[doc]: http://dev.mailjet.com/guides/?php#
[api_credential]: https://app.mailjet.com/account/api_keys
[mailjet]: http://www.mailjet.com

![alt text](http://cdn.appstorm.net/web.appstorm.net/files/2012/02/mailjet_logo_200x200.png "Mailjet")



[Mailjet][mailjet] Iframe wrapper.

Check out all the resources and all the PHP code examples on the official documentation: [Maijlet Documentation][doc]

## Requirements

`PHP >= 5.4`

## Installation

``` bash
composer require mailjet/Mailjet-iframe-v3
```

## Getting Started !

[grab][api_credential] your API credentials and use them to connect Mailjet API.

It's as easy as 1, 2, 3 !


## A basic example

``` php
<?php
require 'vendor/autoload.php';

use MailjetIframe\MailjetIframe;

/**
 *
 * Instantiate the iframe helper
 */
$mailjetIframe = new MailjetIframe(
    'YOUR-APIKEY',
    'YOUR-APISECRET'
);

/**
 *
 * Configure the iframe
 */
$mailjetIframe
    ->setCallback('')
    ->setTokenExpiration(600)
    ->setLocale('fr_FR')
    ->setTokenAccess(array(
        'campaigns',
        'contacts',
        'stats',
    ))
    ->turnDocumentationProperties(MailjetIframe::OFF)
    ->turnNewContactListCreation(MailjetIframe::OFF)
    ->turnMenu(MailjetIframe::ON)
    ->turnFooter(MailjetIframe::OFF)
    ->turnBar(MailjetIframe::ON)
    ->turnCreateCampaignButton(MailjetIframe::ON)
    ->turnSendingPolicy(MailjetIframe::OFF)
    ->setInitialPage(MailjetIframe::PAGE_STATS);

/**
 *
 * Show the iframe whereever you want
 */
echo $mailjetIframe->getHtml();
```

## Send a pull request

 - Fork the project.
 - Create a topic branch.
 - Implement your feature/bug fix.
 - Add documentation for your feature or bug fix.
 - Add specs for your feature or bug fix.
 - Commit and push your changes.
 - Submit a pull request.
