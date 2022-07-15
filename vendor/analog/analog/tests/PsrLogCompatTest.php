<?php

namespace MailjetWp;

\date_default_timezone_set('GMT');
require_once 'lib/Analog.php';
use MailjetWp\Analog\Logger;
use MailjetWp\Analog\Handler\Variable;
use MailjetWp\Psr\Log\Test\LoggerInterfaceTest;
if (!\class_exists('MailjetWp\\PHPUnit_Framework_TestCase')) {
    require_once 'tests/PHPUnit_Framework_TestCase.php';
}
class PsrLogCompatTest extends LoggerInterfaceTest
{
    private $log = '';
    public function getLogger()
    {
        $logger = new Logger();
        $logger->handler(Variable::init($this->log));
        $logger->format("%3\$d %4\$s\n");
        return $logger;
    }
    public function getLogs()
    {
        $logger = $this->getLogger();
        $logs = \explode("\n", \trim($this->log));
        foreach ($logs as $key => $line) {
            list($level, $msg) = \explode(' ', $line, 2);
            $logs[$key] = $logger->convert_log_level((int) $level, \true) . ' ' . $msg;
        }
        return $logs;
    }
}
\class_alias('MailjetWp\\PsrLogCompatTest', 'PsrLogCompatTest', \false);
