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

use Fidry\Console\InputAssert;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @template TypedValue
 * @implements InputType<non-empty-list<TypedValue>>
 */
final class NonEmptyListType implements InputType
{
    /**
     * @var InputType<TypedValue>
     */
    private InputType $innerType;

    /**
     * @param InputType<TypedValue> $innerType
     */
    public function __construct(InputType $innerType)
    {
        $this->innerType = $innerType;
    }

    public function coerceValue($value, string $label): array
    {
        $list = (new ListType($this->innerType))->coerceValue($value, $label);

        /** @psalm-suppress MissingClosureReturnType */
        InputAssert::castThrowException(
            static fn () => Assert::minCount($list, 1),
            $label,
        );

        return $list;
    }

    public function getTypeClassNames(): array
    {
        return [
            self::class,
            ...$this->innerType->getTypeClassNames(),
        ];
    }

    /** @psalm-suppress MoreSpecificReturnType */
    public function getPsalmTypeDeclaration(): string
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return sprintf(
            'non-empty-list<%s>',
            $this->innerType->getPsalmTypeDeclaration(),
        );
    }

    public function getPhpTypeDeclaration(): ?string
    {
        return 'array';
    }
}
