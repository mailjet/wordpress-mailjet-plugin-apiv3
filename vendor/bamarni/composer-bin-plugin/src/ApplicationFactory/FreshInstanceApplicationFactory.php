<?php

declare (strict_types=1);
namespace MailjetWp\Bamarni\Composer\Bin\ApplicationFactory;

use MailjetWp\Composer\Console\Application;
final class FreshInstanceApplicationFactory implements NamespaceApplicationFactory
{
    public function create(Application $existingApplication) : Application
    {
        return new Application();
    }
}
