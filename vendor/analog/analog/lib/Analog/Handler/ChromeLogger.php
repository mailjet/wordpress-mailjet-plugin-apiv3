<?php

namespace MailjetWp\Analog\Handler;

require_once __DIR__ . '/../../ChromePhp.php';
/**
 * Log to the [Chrome Logger](http://craig.is/writing/chrome-logger).
 * Based on the [ChromePhp library](https://github.com/ccampbell/chromephp).
 *
 * Usage:
 *
 *     Analog::handler (Analog\Handler\ChromeLogger::init ());
 *     
 *     // send a debug message
 *     Analog::debug ($an_object);
 *
 *     // send an ordinary message
 *     Analog::info ('An error message');
 */
class ChromeLogger
{
    public static function init()
    {
        return function ($info) {
            switch ($info['level']) {
                case \MailjetWp\Analog\Analog::DEBUG:
                    \MailjetWp\ChromePhp::log($info['message']);
                    break;
                case \MailjetWp\Analog\Analog::INFO:
                case \MailjetWp\Analog\Analog::NOTICE:
                    \MailjetWp\ChromePhp::info($info['message']);
                    break;
                case \MailjetWp\Analog\Analog::WARNING:
                    \MailjetWp\ChromePhp::warn($info['message']);
                    break;
                case \MailjetWp\Analog\Analog::ERROR:
                case \MailjetWp\Analog\Analog::CRITICAL:
                case \MailjetWp\Analog\Analog::ALERT:
                case \MailjetWp\Analog\Analog::URGENT:
                    \MailjetWp\ChromePhp::error($info['message']);
                    break;
            }
        };
    }
}
