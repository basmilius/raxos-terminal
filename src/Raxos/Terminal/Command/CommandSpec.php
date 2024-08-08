<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

/**
 * Class CommandSpec
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.0.1
 */
final class CommandSpec
{

    private ?string $description = null;
    private ?string $example = null;
    private array $arguments = [];
    private array $options = [];

    /**
     * CommandSpec constructor.
     *
     * @param string $name
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct(private readonly string $name)
    {
    }

    /**
     * Gets the description.
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Gets the example.
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getExample(): ?string
    {
        return $this->example;
    }

    /**
     * Gets the name.
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Gets the arguments.
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Gets the options.
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Sets the example.
     *
     * @param string $example
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function example(string $example): self
    {
        $this->example = $example;

        return $this;
    }

    /**
     * Adds an argument.
     *
     * @param string $name
     * @param string $type
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function argument(string $name, string $type): self
    {
        $this->arguments[$name] = $type;

        return $this;
    }

    /**
     * Adds an option.
     *
     * @param string $name
     * @param string $type
     * @param string $description
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function option(string $name, string $type, string $description): self
    {
        $this->options[$name] = [$type, $description];

        return $this;
    }

    /**
     * Creates a new command spec instance.
     *
     * @param string $name
     *
     * @return static
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public static function make(string $name): self
    {
        return new self($name);
    }

}
