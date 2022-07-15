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

use Fidry\Console\DisplayNormalizer;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Tester\ApplicationTester as SymfonyAppTester;
use Symfony\Component\Console\Tester\CommandTester as SymfonyCommandTester;

final class OutputAssertions
{
    private function __construct()
    {
    }

    /**
     * @param AppTester|SymfonyAppTester|CommandTester|SymfonyCommandTester $actual
     * @param callable(string):string                                       $extraNormalizers
     */
    public static function assertSameOutput(
        string $expectedOutput,
        int $expectedStatusCode,
        $actual,
        callable ...$extraNormalizers
    ): void {
        $actualOutput = DisplayNormalizer::removeTrailingSpaces(
            $actual->getDisplay(),
            ...$extraNormalizers,
        );

        Assert::assertSame($expectedOutput, $actualOutput);
        Assert::assertSame($expectedStatusCode, $actual->getStatusCode());
    }
}
