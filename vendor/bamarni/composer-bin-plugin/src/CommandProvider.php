<?php

declare (strict_types=1);
namespace MailjetWp\Bamarni\Composer\Bin;

use MailjetWp\Bamarni\Composer\Bin\Command\BinCommand;
use MailjetWp\Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
/**
 * @final Will be final in 2.x.
 */
class CommandProvider implements CommandProviderCapability
{
    public function getCommands() : array
    {
        return [new BinCommand()];
    }
}
