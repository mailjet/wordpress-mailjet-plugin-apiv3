<?php

namespace MailjetWp\Analog\Handler;

/**
 * Translates log level codes to their names
 *
 *
 * Usage:
 *
 *     // The log level (3rd value) must be formatted as a string
 *     Analog::$format = "%s - %s - %s - %s\n";
 * 
 *     Analog::handler (Analog\Handler\LevelName::init (
 *         Analog\Handler\File::init ($file)
 *     ));
 */
class LevelName
{
    /**
     * Translation list for log levels.
     */
    private static $log_levels = array(\MailjetWp\Analog\Analog::DEBUG => 'DEBUG', \MailjetWp\Analog\Analog::INFO => 'INFO', \MailjetWp\Analog\Analog::NOTICE => 'NOTICE', \MailjetWp\Analog\Analog::WARNING => 'WARNING', \MailjetWp\Analog\Analog::ERROR => 'ERROR', \MailjetWp\Analog\Analog::CRITICAL => 'CRITICAL', \MailjetWp\Analog\Analog::ALERT => 'ALERT', \MailjetWp\Analog\Analog::URGENT => 'URGENT');
    /**
     * This contains the handler to send to
     */
    public static $handler;
    public static function init($handler)
    {
        self::$handler = $handler;
        return function ($info) {
            if (isset(self::$log_levels[$info['level']])) {
                $info['level'] = self::$log_levels[$info['level']];
            }
            $handler = LevelName::$handler;
            $handler($info);
        };
    }
}
