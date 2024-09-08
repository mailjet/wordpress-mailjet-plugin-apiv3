<?php

namespace MailjetWp\MailjetPlugin\Includes;

use MailjetWp\Analog\Analog;

class MailjetLogger
{
    public static function log($message, $level = null)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::log($message, $level);
    }
    public static function urgent($message)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::urgent($message);
    }
    public static function alert($message)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::alert($message);
    }
    public static function error($message)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::error($message);
    }
    public static function warning($message)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::warning($message);
    }
    public static function notice($message)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::notice($message);
    }
    public static function info($message)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::info($message);
    }
    public static function debug($message)
    {
        $mailjetActivateLogger = Mailjet::getOption('mailjet_activate_logger');
        if (empty($mailjetActivateLogger) || $mailjetActivateLogger != 1) {
            return '';
        }
        return Analog::debug($message);
    }
}
