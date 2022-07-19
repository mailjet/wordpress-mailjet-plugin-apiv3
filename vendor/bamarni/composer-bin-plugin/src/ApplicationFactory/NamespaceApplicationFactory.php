<?php

declare (strict_types=1);
namespace MailjetWp\Bamarni\Composer\Bin\ApplicationFactory;

use MailjetWp\Composer\Console\Application;
interface NamespaceApplicationFactory
{
    public function create(Application $existingApplication) : Application;
}
