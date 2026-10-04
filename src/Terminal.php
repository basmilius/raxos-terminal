<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Closure;
use InvalidArgumentException;
use Raxos\Contract\Container\ContainerExceptionInterface;
use Raxos\Contract\Container\ContainerInterface;
use Raxos\Contract\Terminal\CommandExceptionInterface;
use Raxos\Contract\Terminal\CommandInterface;
use Raxos\Contract\Terminal\MiddlewareInterface;
use Raxos\Contract\Terminal\TerminalExceptionInterface;
use Raxos\Contract\Terminal\TerminalInterface;
use Raxos\Terminal\Collision\ErrorReporter;
use Raxos\Terminal\Command\HelpCommand;
use Raxos\Terminal\Error\CommandExitException;
use Raxos\Terminal\Error\CommandNotFoundException;
use Raxos\Terminal\Error\DuplicateCommandException;
use Raxos\Terminal\Error\InvalidCommandException;
use Raxos\Terminal\Internal\Data;
use Raxos\Terminal\Parser\Parser;
use Raxos\Terminal\Parser\ParserResult;
use Throwable;
use function is_subclass_of;

/**
 * Class Terminal
 *
 * Dispatches CLI commands through shared parsing, injection and middleware lifecycles.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal
 * @since 1.0.1
 */
class Terminal implements TerminalInterface
{

    /**
     * Keeps exit requests inside nested programmable runs from terminating the process.
     *
     * @var int
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    private int $runDepth = 0;

    /**
     * {@inheritdoc}
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.4.0
     */
    public private(set) array $commands = [
        'help' => HelpCommand::class
    ];

    /**
     * Terminal constructor.
     *
     * @param Printer $printer
     * @param ContainerInterface|null $container
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct(
        public readonly Printer $printer = new Printer(),
        public readonly ?ContainerInterface $container = null
    ) {}

    /**
     * {@inheritdoc}
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function execute(): void
    {
        $code = $this->run($GLOBALS['argv']);

        if ($code !== 0) {
            $this->exit($code);
        }
    }

    /**
     * Runs an argv vector including the executable name without terminating the process.
     *
     * @param list<string> $argv
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function run(array $argv): int
    {
        $result = null;
        ++$this->runDepth;

        try {
            $result = Parser::parseArgv($argv);

            if ($result === null || $result->command === null) {
                $this->runCommand(HelpCommand::class);
            } else {
                $commandClass = $this->commands[$result->command] ?? throw new CommandNotFoundException($result->command);
                $this->runCommand($commandClass, $result);
            }

            return 0;
        } catch (CommandExitException $exit) {
            return $exit->status;
        } catch (InvalidArgumentException $err) {
            $this->printer->incorrect($err->getMessage());

            return -1;
        } catch (CommandExceptionInterface $err) {
            $this->printer->incorrect($err->getMessage());

            try {
                $help = new HelpCommand($result?->command);
                $help->execute($this, $this->printer, false);
            } catch (Throwable) {
            }

            return -2;
        } catch (Throwable $err) {
            ErrorReporter::exception($err);

            return 9;
        } finally {
            --$this->runDepth;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function exit(int $code = 0): never
    {
        if ($this->runDepth > 0) {
            throw new CommandExitException($code);
        }

        exit($code);
    }

    /**
     * {@inheritdoc}
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function register(string $commandClass): static
    {
        if (!is_subclass_of($commandClass, CommandInterface::class)) {
            throw new InvalidCommandException($commandClass);
        }

        $data = Data::parseCommand($commandClass);

        if (isset($this->commands[$data->command->name])) {
            throw new DuplicateCommandException($commandClass);
        }

        $this->commands[$data->command->name] = $commandClass;

        return $this;
    }

    /**
     * Runs a command.
     *
     * @param string $commandClass
     * @param ParserResult|null $result
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws TerminalExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function runCommand(
        string $commandClass,
        ?ParserResult $result = null
    ): void
    {
        $data = Data::parseCommand($commandClass);
        $command = $data->instantiate($result?->arguments ?? [], $result?->options ?? [], $this->container);

        $this->closure($data->middlewares, $command, $result)();
    }

    /**
     * Returns a command execution stack.
     *
     * @param MiddlewareInterface[] $middlewares
     * @param CommandInterface $command
     * @param ParserResult|null $result
     *
     * @return Closure
     * @throws CommandExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function closure(
        array $middlewares,
        CommandInterface $command,
        ?ParserResult $result = null
    ): Closure
    {
        if (empty($middlewares)) {
            return fn() => $command->execute($this, $this->printer);
        }

        $middleware = array_shift($middlewares);
        $data = Data::parseMiddleware($middleware::class);
        $data->inject($middleware, options: $result?->options ?? []);

        $next = $this->closure($middlewares, $command, $result);

        return fn() => $middleware->handle($command, $this, $this->printer, $next);
    }

}
