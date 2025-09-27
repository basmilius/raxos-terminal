<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Closure;
use InvalidArgumentException;
use Raxos\Contract\Terminal\{CommandExceptionInterface, CommandInterface, MiddlewareInterface, TerminalExceptionInterface, TerminalInterface};
use Raxos\Terminal\Collision\ErrorReporter;
use Raxos\Terminal\Command\HelpCommand;
use Raxos\Terminal\Error\{CommandNotFoundException, DuplicateCommandException, InvalidCommandException};
use Raxos\Terminal\Internal\Data;
use Raxos\Terminal\Parser\{Parser, ParserResult};
use Throwable;
use function is_subclass_of;

/**
 * Class Terminal
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal
 * @since 1.0.1
 */
class Terminal implements TerminalInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.4.0
     */
    public private(set) array $commands = [
        'help' => HelpCommand::class
    ];

    /**
     * Terminal constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct(
        public readonly Printer $printer = new Printer()
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function execute(): void
    {
        $result = Parser::parseFromArgs();

        try {
            if ($result === null || $result->command === null) {
                $this->run(HelpCommand::class);
            } else {
                $commandClass = $this->commands[$result->command] ?? throw new CommandNotFoundException($result->command);
                $this->run($commandClass, $result);
            }
        } catch (InvalidArgumentException $err) {
            $this->printer->incorrect($err->getMessage());
            $this->exit(-1);
        } catch (CommandExceptionInterface $err) {
            $this->printer->incorrect($err->getMessage());

            try {
                $help = new HelpCommand($result->command);
                $help->execute($this, $this->printer, false);
            } catch (Throwable) {
            }

            $this->exit(-2);
        } catch (Throwable $err) {
            ErrorReporter::exception($err);
            $this->exit(9);
        }
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function exit(int $code = 0): never
    {
        exit($code);
    }

    /**
     * {@inheritdoc}
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
     * @throws TerminalExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function run(string $commandClass, ?ParserResult $result = null): void
    {
        $data = Data::parseCommand($commandClass);
        $command = $data->instantiate($result?->arguments ?? [], $result?->options ?? []);

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
    private function closure(array $middlewares, CommandInterface $command, ?ParserResult $result = null): Closure
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
