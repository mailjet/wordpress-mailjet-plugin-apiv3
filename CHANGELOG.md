## Changelog

##### 5.4.1
* Added new translations for notice message
* Fix deactivate function to not throw an error

##### 5.4.0
* New widget for Form Builder

##### 5.3.7
* Added notice about updating plugin in the future (new Form Builder widget)

##### 5.3.6
* Fixed error with Double opt-in and CF7 plugin

##### 5.3.5
* Fixed error when new user in WP was unsubscribed in the contact list

##### 5.3.4
* Fixed issues with uncaught exception and wrong sanitizing for <option> in the widget

##### 5.3.3
* Fixed bug with showing html in the widget title.

##### 5.3.2
* Fixed security issues according to plugin scanning. Sanitized some input args.

##### 5.3.1
* Fixed security issues according to plugin scanning. Added escaping for shown strings.

##### 5.3
* Fixed security issues according to plugin scanning. Added escaping for shown strings. Added sanitizing for input vars

##### 5.2.25
* Removed var_dump from the code because off error

* ##### 5.2.24
* Fix bug with widget for WP 6+ version

##### 5.2.23
* Fix issue with php8.1 and classes namespace

##### 5.2.21
* Fix issue with guzzle namespace conflict

##### 5.2.20
* Moved Iframe library from own repo to main plugin repo
* Small code cleanup

##### 5.2.19
* Fixed issue with not translated string. Some code did not respect the domain of translations

##### 5.2.16
* Fixed issue with sending subscribed contact to selected mail list

##### 5.2.15
* Removed not needed requests when initiating widget

##### 5.2.14
* Fixed showing modal after saving widget

##### 5.2.13
* Dont add scripts if widget not added

##### 5.2.12
* Validation improved

##### 5.2.11
* Validation improved

##### 5.2.10
* Missing translations fix
* Saved section fix

##### 5.2.9
* Fixed PHP 7 issue

##### 5.2.8
* Fixed PHP 7 issue

##### 5.2.7
* Fixed various things

##### 5.2.6
* Updated assets

##### 5.2.5
* Ensured compatibility with WP version 5.6
* Removed Bootstrap dependency

##### 5.2.4
* Ensured compatibility with WP version 5.5 and previous versions
* Fixed issue with WooCommerce order status
* Security improvements

##### 5.2.3
* Reverted to 5.2.1

##### 5.2.2
* Ensure compatibility with WP version 5.5

##### 5.2.1
* Fixed compatibility with the Theme Customizer
* Added compatibility with custom roles
* Send subscription widget through Ajax
* Tweaks and optimizations

##### 5.2
* Added full WooCommerce integration
* Added widget shortcode support
* Fixed compatibility bug with Astra theme
* Fixed W3C validation issue with the widget code

##### 5.1.3
* Allow use of filter to replace the subscription confirmation email for Contact Form 7, WooCommerce and comment authors subscriptions

##### 5.1.2
* Allow use of TLS for ports 587 / 588
* Show the default thank you page when subscribing via Contact Form 7
* Translations fixes

##### 5.1.1
* Fixed a problem when saving the WooCommerce integration settings
* Fixed translations in the subscription confirmation email for Contact Form 7

##### 5.1.0
* New integration with Contact Form 7 has been added
* Added support for custom translation files
* Multiple widgets can be added on the same page
* Various bug fixes and improvements

##### 5.0.12
* User can upload custom language translation files
* Added Contact Form 7 integration

##### 5.0.11
* Fix confirmation link
* Create contact properties
* Change Connected to name
* Fix user access

##### 5.0.10
* Fix subscription confirmation URL for custom thank you page

##### 5.0.9
* Remove css and js public files to reduce public page speed loading
* WordPress widget is now working on wordpress version < 4.4
* Prevent fatal error on api timeout
* Add missing translations

##### 5.0.8
* Improve advanced options popup style
* Boolean type property is now checkbox
* Check requirements on activation
* Simplify onboarding UX
* Improve settings
* Advanced customisation link is always visible
* Added link to "Setup account" inside the Plugins section
* Improve compatibility with WP versions < 5.7

##### 5.0.7
* Fixed 'Thank you' page url when moving the blog across domains

##### 5.0.6
* Allow logged in WooCommerce customers to subscribe to the newsletter during checkout.
* Fixed css issues in the WP admin
* Small bugfixes and improvements

##### 5.0.5
* Fix subscription widget issues for multilanguage sites
* Fix fatal error for WooCommerce integration

##### 5.0.4
* Fix widget contact properties to be compatible with Polylang

##### 5.0.3
* Fix fatal error for php 5.5

##### 5.0.2
* Small bugfixes

##### 5.0.1
* Plugin redesign and major improvements
