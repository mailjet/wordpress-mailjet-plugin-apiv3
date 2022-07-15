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

namespace Fidry\Console;

use function array_is_list;
use Fidry\Console\Input\InvalidInputValueType;
use function get_debug_type;
use function is_array;
use function is_bool;
use function is_string;
use function sprintf;
use function var_export;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException as AssertInvalidArgumentException;

/**
 * TODO: move this under Internal.
 *
 * @private
 * @psalm-type ArgumentInput = null|string|list<string>
 * @psalm-type OptionInput = null|bool|string|list<string>
 */
final class InputAssert
{
    private function __construct()
    {
    }

    /**
     * @param mixed $argument
     *
     * @psalm-assert ArgumentInput $argument
     */
    public static function assertIsValidArgumentType($argument, string $name): void
    {
        if (null === $argument || is_string($argument)) {
            return;
        }

        if (!is_array($argument) || !array_is_list($argument)) {
            throw new InvalidInputValueType(
                sprintf(
                    'Expected an argument value type to be "null|string|list<string>". Got "%s" for the argument "%s".',
                    get_debug_type($argument),
                    $name,
                ),
            );
        }

        foreach ($argument as $item) {
            self::assertIsValidArgumentType($item, $name);
        }
    }

    /**
     * @param mixed $option
     *
     * @psalm-assert OptionInput $option
     */
    public static function assertIsValidOptionType($option, string $name): void
    {
        if (null === $option || is_bool($option) || is_string($option)) {
            return;
        }

        if (!is_array($option) || !array_is_list($option)) {
            throw new InvalidInputValueType(
                sprintf(
                    'Expected an option value type to be "null|bool|string|list<string>". Got "%s" for the option "%s".',
                    get_debug_type($option),
                    $name,
                ),
            );
        }

        foreach ($option as $item) {
            self::assertIsValidOptionType($item, $name);
        }
    }

    /**
     * @param ArgumentInput|OptionInput $value
     * @param non-empty-string          $label
     *
     * @psalm-assert scalar|null $value
     */
    public static function assertIsScalar($value, string $label): void
    {
        self::castThrowException(
            static function () use ($value): void {
                if (null === $value) {
                    return;
                }

                Assert::scalar(
                    $value,
                    sprintf(
                        'Expected a null or scalar value. Got the value: "%s"',
                        self::castType($value),
                    ),
                );
            },
            $label,
        );
    }

    /**
     * @param ArgumentInput|OptionInput $value
     * @param non-empty-string          $label
     *
     * @psalm-assert list $value
     */
    public static function assertIsList($value, string $label): void
    {
        self::castThrowException(
            static function () use ($value): void {
                Assert::isArray(
                    $value,
                    sprintf(
                        'Cannot cast a non-array input argument into an array. Got "%s"',
                        self::castType($value),
                    ),
                );
                /** @psalm-suppress RedundantConditionGivenDocblockType */
                Assert::isList(
                    $value,
                    sprintf(
                        'Expected array to be a list. Got "%s"',
                        self::castType($value),
                    ),
                );
            },
            $label,
        );
    }

    /**
     * @param ArgumentInput|OptionInput $value
     * @param non-empty-string          $label
     *
     * @psalm-assert numeric $value
     */
    public static function numericString($value, string $label): void
    {
        self::castThrowException(
            static function () use ($value, $label): void {
                self::assertIsScalar($value, $label);
                Assert::string(
                    $value,
                    sprintf(
                        'Expected a numeric string. Got "%s"',
                        self::castType($value),
                    ),
                );
                Assert::numeric(
                    $value,
                    sprintf(
                        'Expected a numeric string. Got "%s"',
                        self::castType($value),
                    ),
                );
            },
            $label,
        );
    }

    /**
     * @param ArgumentInput|OptionInput $value
     * @param non-empty-string          $label
     *
     * @psalm-assert string $value
     */
    public static function integerString($value, string $label): void
    {
        self::castThrowException(
            static function () use ($value, $label): void {
                self::assertIsScalar($value, $label);
                Assert::string(
                    $value,
                    sprintf(
                        'Expected an integer string. Got "%s"',
                        self::castType($value),
                    ),
                );
                Assert::digits(
                    $value,
                    sprintf(
                        'Expected an integer string. Got "%s"',
                        self::castType($value),
                    ),
                );
            },
            $label,
        );
    }

    /**
     * @param ArgumentInput|OptionInput $value
     * @param non-empty-string          $label
     *
     * @psalm-assert string $value
     */
    public static function string($value, string $label): void
    {
        self::castThrowException(
            static function () use ($value, $label): void {
                self::assertIsScalar($value, $label);
                Assert::string(
                    $value,
                    sprintf(
                        'Expected a string. Got "%s"',
                        self::castType($value),
                    ),
                );
            },
            $label,
        );
    }

    /**
     * @param callable(): void $callable
     * @param non-empty-string $label
     */
    public static function castThrowException(callable $callable, string $label): void
    {
        try {
            $callable();
        } catch (AssertInvalidArgumentException $exception) {
            throw InvalidInputValueType::fromAssert($exception, $label);
        }
    }

    /**
     * @param ArgumentInput|OptionInput $value
     */
    private static function castType($value): string
    {
        return var_export($value, true);
    }
}
