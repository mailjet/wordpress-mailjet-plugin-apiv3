<?php

declare (strict_types=1);
namespace MailjetWp\Bamarni\Composer\Bin;

use MailjetWp\Composer\IO\IOInterface;
final class Logger
{
    /**
     * @var IOInterface
     */
    private $io;
    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }
    public function logStandard(string $message) : void
    {
        $this->log($message, \false);
    }
    public function logDebug(string $message) : void
    {
        $this->log($message, \true);
    }
    private function log(string $message, bool $debug) : void
    {
        $verbosity = $debug ? IOInterface::VERBOSE : IOInterface::NORMAL;
        $this->io->writeError('[bamarni-bin] ' . $message, \true, $verbosity);
    }
}
