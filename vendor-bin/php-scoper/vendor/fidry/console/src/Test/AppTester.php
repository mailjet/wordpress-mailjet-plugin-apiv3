<?php

/*
 * This file is part of the Fidry\Console package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\Console\Test;

use Fidry\Console\Application\Application as ConsoleApplication;
use Fidry\Console\Application\SymfonyApplication;
use Fidry\Console\DisplayNormalizer;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class AppTester extends ApplicationTester
{
    public static function fromConsoleApp(ConsoleApplication $application): self
    {
        return new self(
            new SymfonyApplication($application),
        );
    }

    /**
     * @param callable(string):string $extraNormalizers
     */
    public function getNormalizedDisplay(
        callable ...$extraNormalizers
    ): string {
        return DisplayNormalizer::removeTrailingSpaces(
            $this->getDisplay(),
            ...$extraNormalizers,
        );
    }
}
