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

/*
 * This file is part of the box project.
 *
 * (c) Kevin Herrera <kevin@herrera.io>
 *     Théo Fidry <theo.fidry@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Fidry\Console\Input;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @final
 */
class IO extends SymfonyStyle
{
    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);

        $this->input = $input;
        $this->output = $output;
    }

    public static function createDefault(): self
    {
        return new self(
            new ArgvInput(),
            new ConsoleOutput(),
        );
    }

    public static function createNull(): self
    {
        return new self(
            new StringInput(''),
            new NullOutput()
        );
    }

    public function withInput(InputInterface $input): self
    {
        return new self($input, $this->output);
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    public function withOutput(OutputInterface $output): self
    {
        return new self($this->input, $output);
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param non-empty-string $name
     */
    public function getArgument(string $name): TypedInput
    {
        return TypedInput::fromArgument(
            $this->input->getArgument($name),
            $name,
        );
    }

    /**
     * @param non-empty-string $name
     */
    public function getOption(string $name): TypedInput
    {
        return TypedInput::fromOption(
            $this->input->getOption($name),
            $name,
        );
    }

    /**
     * @param non-empty-string $name
     */
    public function hasOption(string $name, bool $onlyRealParams = false): bool
    {
        return $this->input->hasParameterOption($name, $onlyRealParams);
    }
}
