<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Terminal\Attribute\{Argument, Command, Option};
use Raxos\Terminal\Contract\{AttributeInterface, CommandInterface};
use Raxos\Terminal\Error\{CommandException, TerminalException};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use function in_array;

/**
 * Class Data
 *
 * @template TCommand of CommandInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.6.0
 */
final readonly class Data
{

    /**
     * Data constructor.
     *
     * @param class-string<TCommand> $class
     * @param Command $command
     * @param ArgumentData[] $arguments
     * @param OptionData[] $options
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public string $class,
        public Command $command,
        public array $arguments,
        public array $options
    ) {}

    /**
     * Converts the arguments and options to an array of arguments.
     *
     * @param array $arguments
     * @param array $options
     *
     * @return array
     * @throws CommandException
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function toArgs(array $arguments, array $options): array
    {
        $args = [];

        foreach ($this->arguments as $index => $argument) {
            $arg = $arguments[$index] ?? null;

            if ($arg !== null) {
                // todo(Bas): types.
                $args[] = $arg;
                continue;
            }

            if (!$argument->defaultValue->isEmpty) {
                $args[] = $argument->defaultValue->getOrThrow(CommandException::missingArgument($argument->name));
                continue;
            }

            if (in_array('null', $argument->type, true)) {
                $args[] = null;
                continue;
            }

            throw CommandException::missingArgument($argument->name);
        }

        foreach ($this->options as $option) {
            $arg = $options[$option->name] ?? null;

            if ($arg !== null) {
                // todo(Bas): types.
                $args[] = $arg;
                continue;
            }

            if (!$option->defaultValue->isEmpty) {
                $args[] = $option->defaultValue->getOrThrow(CommandException::missingOption($option->name));
                continue;
            }

            if (in_array('null', $option->type, true)) {
                $args[] = null;
                continue;
            }

            throw CommandException::missingOption($option->name);
        }

        return $args;
    }

    /**
     * Parses the given command.
     *
     * @param class-string<TCommand> $commandClass
     *
     * @return self<TCommand>
     * @throws TerminalException
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public static function parseCommand(string $commandClass): self
    {
        static $cache = [];

        if (isset($cache[$commandClass])) {
            return $cache[$commandClass];
        }

        try {
            $classRef = new ReflectionClass($commandClass);

            /** @var ReflectionAttribute<Command> $command */
            $command = $classRef->getAttributes(Command::class)[0];
            $command = $command?->newInstance() ?? throw CommandException::invalid($commandClass, 'The command is missing a Command attribute.');

            $arguments = [];
            $options = [];

            $constructorRef = $classRef->getConstructor();

            if ($constructorRef !== null) {
                $parameters = $constructorRef->getParameters();

                foreach ($parameters as $parameterRef) {
                    $attributes = $parameterRef->getAttributes(AttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);
                    $attribute = $attributes[0] ?? throw CommandException::invalid($commandClass, 'One of the parameters is missing an Argument or Option attribute.');
                    $attribute = $attribute->newInstance();

                    $name = $attribute->name ?? $parameterRef->name;
                    $types = ReflectionUtil::getTypes($parameterRef->getType());
                    $defaultValue = $parameterRef->isDefaultValueAvailable()
                        ? \Raxos\Foundation\Option\Option::some($parameterRef->getDefaultValue())
                        : \Raxos\Foundation\Option\Option::none();

                    if ($attribute instanceof Argument && !empty($options)) {
                        throw CommandException::invalid($commandClass, 'An argument cannot be after an option.');
                    }

                    match (true) {
                        $attribute instanceof Argument => $arguments[] = new ArgumentData(
                            $attribute,
                            $name,
                            $types,
                            $defaultValue
                        ),
                        $attribute instanceof Option => $options[] = new OptionData(
                            $attribute,
                            $name,
                            $types,
                            $defaultValue
                        ),
                        default => throw CommandException::invalid($commandClass)
                    };
                }
            }

            return new self($commandClass, $command, $arguments, $options);
        } catch (ReflectionException $err) {
            throw CommandException::reflectionFailed($commandClass, $err);
        }
    }

}
