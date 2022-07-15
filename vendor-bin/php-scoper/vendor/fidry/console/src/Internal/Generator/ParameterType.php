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

namespace Fidry\Console\Internal\Generator;

// TODO: switch to an enum in PHP8.1
/**
 * @private
 */
final class ParameterType
{
    public const ARGUMENT = 'ARGUMENT';
    public const OPTION = 'OPTION';

    public const ALL = [
        self::ARGUMENT,
        self::OPTION,
    ];

    private function __construct()
    {
    }
}
