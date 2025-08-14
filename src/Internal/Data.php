<?php
declare(strict_types=1);

namespace Raxos\Terminal\Internal;

use Raxos\Foundation\Option\{None, Option as ValueOption};
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Terminal\Attribute\{Argument, Command, Option};
use Raxos\Terminal\Contract\{AttributeInterface, CommandInterface, MiddlewareInterface};
use Raxos\Terminal\Error\{CommandException, TerminalException};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use function array_map;
use function in_array;

/**
 * Class Data
 *
 * @template TCommand of CommandInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Internal
 * @since 1.6.0
 * @internal
 * @private
 */
final readonly class Data
{

    /**
     * Data constructor.
     *
     * @param class-string<TCommand> $class
     * @param Command|null $command
     * @param ArgumentData[] $arguments
     * @param MiddlewareInterface[] $middlewares
     * @param OptionData[] $options
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public string $class,
        public ?Command $command = null,
        public array $arguments = [],
        public array $middlewares = [],
        public array $options = []
    ) {}

    /**
     * Inject options into the middleware.
     *
     * @param MiddlewareInterface $middleware
     * @param array $arguments
     * @param array $options
     *
     * @return void
     * @throws CommandException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function inject(MiddlewareInterface $middleware, array $arguments = [], array $options = []): void
    {
        try {
            $options = $this->getNamedArgumentsAndOptions($arguments, $options);
            $classRef = new ReflectionClass($middleware);

            foreach ($options as $name => $value) {
                $propertyRef = $classRef->getProperty($name);
                $propertyRef->setValue($middleware, $value);
            }
        } catch (ReflectionException $err) {
            throw CommandException::reflectionFailed($this->class, $err);
        }
    }

    /**
     * Instantiates the command.
     *
     * @param string[] $arguments
     * @param array<string, string> $options
     *
     * @return CommandInterface
     * @throws CommandException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function instantiate(array $arguments, array $options): CommandInterface
    {
        return new $this->class(...$this->getNamedArgumentsAndOptions($arguments, $options));
    }

    /**
     * Converts the arguments and options to an array of arguments.
     *
     * @param string[] $arguments
     * @param array<string, string> $options
     *
     * @return array
     * @throws CommandException
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    private function getNamedArgumentsAndOptions(array $arguments, array $options): array
    {
        $args = [];

        foreach ($this->arguments as $index => $argument) {
            $arg = $arguments[$index] ?? null;

            if ($arg !== null) {
                // todo(Bas): types.
                $args[$argument->name] = $arg;
                continue;
            }

            if (!$argument->defaultValue->isEmpty) {
                $args[$argument->name] = $argument->defaultValue->getOrThrow(CommandException::missingArgument($argument->name));
                continue;
            }

            if (in_array('null', $argument->type, true)) {
                $args[$argument->name] = null;
                continue;
            }

            throw CommandException::missingArgument($argument->name);
        }

        foreach ($this->options as $option) {
            $arg = $options[$option->name] ?? null;

            if ($arg !== null) {
                // todo(Bas): types.
                $args[$option->name] = $arg;
                continue;
            }

            if (!$option->defaultValue->isEmpty) {
                $args[$option->name] = $option->defaultValue->getOrThrow(CommandException::missingOption($option->name));
                continue;
            }

            if (in_array('null', $option->type, true)) {
                $args[$option->name] = null;
                continue;
            }

            throw CommandException::missingOption($option->name);
        }

        return $args;
    }

    /**
     * Returns the data structure for a command class.
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

            $middlewares = $classRef->getAttributes(MiddlewareInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            $middlewares = array_map(static fn(ReflectionAttribute $attribute) => $attribute->newInstance(), $middlewares);

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
                        ? ValueOption::some($parameterRef->getDefaultValue())
                        : ValueOption::none();

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

            return new self($commandClass, $command, $arguments, $middlewares, $options);
        } catch (ReflectionException $err) {
            throw CommandException::reflectionFailed($commandClass, $err);
        }
    }

    /**
     * Returns the data structure for a middleware class.
     *
     * @param string $middlewareClass
     *
     * @return self
     * @throws CommandException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function parseMiddleware(string $middlewareClass): self
    {
        static $cache = [];

        if (isset($cache[$middlewareClass])) {
            return $cache[$middlewareClass];
        }

        try {
            $classRef = new ReflectionClass($middlewareClass);
            $propertyRefs = $classRef->getProperties(ReflectionProperty::IS_PUBLIC);

            $options = [];

            foreach ($propertyRefs as $propertyRef) {
                $attributes = $propertyRef->getAttributes(Option::class);

                if (empty($attributes)) {
                    continue;
                }

                /** @var Option $attribute */
                $attribute = $attributes[0]->newInstance();

                $name = $attribute->name ?? $propertyRef->name;
                $types = ReflectionUtil::getTypes($propertyRef->getType());

                if ($attribute->default !== None::class) {
                    $defaultValue = ValueOption::some($attribute->default);
                } else {
                    $defaultValue = $propertyRef->hasDefaultValue()
                        ? ValueOption::some($propertyRef->getDefaultValue())
                        : ValueOption::none();
                }

                $options[] = new OptionData(
                    $attribute,
                    $name,
                    $types,
                    $defaultValue
                );
            }

            return new self($middlewareClass, options: $options);
        } catch (ReflectionException $err) {
            throw CommandException::reflectionFailed($middlewareClass, $err);
        }
    }

}
