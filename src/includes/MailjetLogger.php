<?php
namespace MailjetPlugin\Includes;

use Analog\Analog;

class MailjetLogger
{

    public static function log($message, $level = null)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::log($message, $level);
    }

    public static function urgent($message)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::urgent($message);
    }

    public static function alert($message)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::alert($message);
    }

    public static function error($message)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::error($message);
    }

    public static function warning($message)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::warning($message);
    }

    public static function notice($message)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::notice($message);
    }

    public static function info($message)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::info($message);
    }

    public static function debug($message)
    {
        if (empty(get_option('mailjet_activate_logger')) || get_option('mailjet_activate_logger') != 1) {
            return;
        }
        return Analog::debug($message);
    }
}
