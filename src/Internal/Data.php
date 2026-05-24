<?php
declare(strict_types=1);

namespace Raxos\Terminal\Internal;

use Raxos\Contract\Container\ContainerExceptionInterface;
use Raxos\Contract\Container\ContainerInterface;
use Raxos\Contract\Terminal\{AttributeInterface, CommandExceptionInterface, CommandInterface, MiddlewareInterface, TerminalExceptionInterface};
use Raxos\Foundation\Option\{None, Option as ValueOption};
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Terminal\Attribute\{Argument, Command, Option};
use Raxos\Terminal\Error\{InvalidCommandException, MissingArgumentException, MissingOptionException, ReflectionErrorException};
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
     * @param DependencyData[] $dependencies
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public string $class,
        public ?Command $command = null,
        public array $arguments = [],
        public array $middlewares = [],
        public array $options = [],
        public array $dependencies = []
    ) {}

    /**
     * Inject options into the middleware.
     *
     * @param MiddlewareInterface $middleware
     * @param array $arguments
     * @param array $options
     *
     * @return void
     * @throws CommandExceptionInterface
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
            throw new ReflectionErrorException($this->class, $err);
        }
    }

    /**
     * Instantiates the command.
     *
     * @param string[] $arguments
     * @param array<string, string> $options
     * @param ContainerInterface|null $container
     *
     * @return CommandInterface
     * @throws CommandExceptionInterface
     * @throws ContainerExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function instantiate(array $arguments, array $options, ?ContainerInterface $container = null): CommandInterface
    {
        $args = $this->getNamedArgumentsAndOptions($arguments, $options);

        if ($this->dependencies !== []) {
            if ($container === null) {
                throw new InvalidCommandException($this->class, 'A container is required to instantiate a command with dependencies.');
            }

            foreach ($this->dependencies as $dependency) {
                $args[$dependency->name] = $container->get($dependency->type);
            }
        }

        return new $this->class(...$args);
    }

    /**
     * Converts the arguments and options to an array of arguments.
     *
     * @param string[] $arguments
     * @param array<string, string> $options
     *
     * @return array
     * @throws CommandExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    private function getNamedArgumentsAndOptions(array $arguments, array $options): array
    {
        $args = [];

        foreach ($this->arguments as $index => $argument) {
            $arg = $arguments[$index] ?? null;

            if ($arg !== null) {
                $arg = match ($argument->type[0]) {
                    'int' => (int)$arg,
                    'float' => (float)$arg,
                    'bool' => $arg === 'true' || $arg === '1',
                    default => $arg
                };

                $args[$argument->realName] = $arg;
                continue;
            }

            if (!$argument->defaultValue->isEmpty) {
                $args[$argument->realName] = $argument->defaultValue->getOrThrow(new MissingArgumentException($argument->name));
                continue;
            }

            if (in_array('null', $argument->type, true)) {
                $args[$argument->realName] = null;
                continue;
            }

            throw new MissingArgumentException($argument->name);
        }

        foreach ($this->options as $option) {
            $arg = $options[$option->name] ?? null;

            if ($arg !== null) {
                $arg = match ($option->type[0]) {
                    'int' => (int)$arg,
                    'float' => (float)$arg,
                    'bool' => $arg === true || $arg === 'true' || $arg === '1',
                    default => $arg
                };

                $args[$option->realName] = $arg;
                continue;
            }

            if (!$option->defaultValue->isEmpty) {
                $args[$option->realName] = $option->defaultValue->getOrThrow(new MissingOptionException($option->name));
                continue;
            }

            if (in_array('null', $option->type, true)) {
                $args[$option->realName] = null;
                continue;
            }

            throw new MissingOptionException($option->name);
        }

        return $args;
    }

    /**
     * Returns the data structure for a command class.
     *
     * @param class-string<TCommand> $commandClass
     *
     * @return self<TCommand>
     * @throws TerminalExceptionInterface
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
            $command = $classRef->getAttributes(Command::class)[0] ?? null;
            $command = $command?->newInstance() ?? throw new InvalidCommandException($commandClass, 'The command is missing a Command attribute.');

            $arguments = [];
            $options = [];
            $dependencies = [];

            $middlewares = $classRef->getAttributes(MiddlewareInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            $middlewares = array_map(static fn(ReflectionAttribute $attribute) => $attribute->newInstance(), $middlewares);

            $constructorRef = $classRef->getConstructor();

            if ($constructorRef !== null) {
                $parameters = $constructorRef->getParameters();

                foreach ($parameters as $parameterRef) {
                    $attributes = $parameterRef->getAttributes(AttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);
                    $attribute = $attributes[0] ?? null;

                    if ($attribute === null) {
                        $parameterType = $parameterRef->getType();

                        if ($parameterType === null) {
                            throw new InvalidCommandException($commandClass, "Parameter '{$parameterRef->name}' has no type and no Argument or Option attribute.");
                        }

                        $types = ReflectionUtil::getTypes($parameterType);
                        $resolvable = null;

                        foreach ($types ?? [] as $candidate) {
                            if ($candidate === 'null' || in_array($candidate, ['int', 'float', 'string', 'bool', 'array', 'mixed', 'iterable', 'object', 'callable', 'self', 'static', 'parent', 'void', 'never'], true)) {
                                continue;
                            }

                            $resolvable = $candidate;
                            break;
                        }

                        if ($resolvable === null) {
                            throw new InvalidCommandException($commandClass, "Parameter '{$parameterRef->name}' must be typed with a class or interface to be resolved via the container.");
                        }

                        $dependencies[] = new DependencyData($parameterRef->name, $resolvable);
                        continue;
                    }

                    $attribute = $attribute->newInstance();

                    $name = $attribute->name ?? $parameterRef->name;
                    $types = ReflectionUtil::getTypes($parameterRef->getType());
                    $defaultValue = $parameterRef->isDefaultValueAvailable()
                        ? ValueOption::some($parameterRef->getDefaultValue())
                        : ValueOption::none();

                    if ($attribute instanceof Argument && !empty($options)) {
                        throw new InvalidCommandException($commandClass, 'An argument cannot be after an option.');
                    }

                    match (true) {
                        $attribute instanceof Argument => $arguments[] = new ArgumentData(
                            $attribute,
                            $parameterRef->name,
                            $name,
                            $types,
                            $defaultValue
                        ),
                        $attribute instanceof Option => $options[] = new OptionData(
                            $attribute,
                            $parameterRef->name,
                            $name,
                            $types,
                            $defaultValue
                        ),
                        default => throw new InvalidCommandException($commandClass)
                    };
                }
            }

            return $cache[$commandClass] = new self($commandClass, $command, $arguments, $middlewares, $options, $dependencies);
        } catch (ReflectionException $err) {
            throw new ReflectionErrorException($commandClass, $err);
        }
    }

    /**
     * Returns the data structure for a middleware class.
     *
     * @param string $middlewareClass
     *
     * @return self
     * @throws CommandExceptionInterface
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
                    $propertyRef->name,
                    $name,
                    $types,
                    $defaultValue
                );
            }

            return $cache[$middlewareClass] = new self($middlewareClass, options: $options);
        } catch (ReflectionException $err) {
            throw new ReflectionErrorException($middlewareClass, $err);
        }
    }

}
