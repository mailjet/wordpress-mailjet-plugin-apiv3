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

namespace Fidry\Console\Internal\Type;

/**
 * @psalm-import-type ArgumentInput from \Fidry\Console\InputAssert
 * @psalm-import-type OptionInput from \Fidry\Console\InputAssert
 * @implements InputType<ArgumentInput|OptionInput>
 */
final class RawType implements InputType
{
    public function coerceValue($value, string $label)
    {
        /** @psalm-suppress NullableReturnStatement */
        return $value;
    }

    public function getTypeClassNames(): array
    {
        return [self::class];
    }

    public function getPsalmTypeDeclaration(): string
    {
        return 'null|bool|string|list<string>';
    }

    public function getPhpTypeDeclaration(): ?string
    {
        // TODO: make it null|bool|string|array in PHP8.1
        return null;
    }
}
