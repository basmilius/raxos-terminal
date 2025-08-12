<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Closure;
use Exception;
use InvalidArgumentException;
use Raxos\Terminal\Collision\ErrorReporter;
use Raxos\Terminal\Command\{Data, HelpCommand};
use Raxos\Terminal\Contract\{CommandInterface, MiddlewareInterface, TerminalInterface};
use Raxos\Terminal\Error\CommandException;
use Raxos\Terminal\Parser\Parser;
use Raxos\Terminal\Parser\ParserResult;
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
                $commandClass = $this->commands[$result->command] ?? throw CommandException::notFound($result->command);
                $this->run($commandClass, $result);
            }
        } catch (InvalidArgumentException $err) {
            $this->printer->incorrect($err->getMessage());
            $this->exit(-1);
        } catch (CommandException $err) {
            $this->printer->incorrect($err->getMessage());

            try {
                $help = new HelpCommand($result->command);
                $help->execute($this, $this->printer, false);
            } catch (Exception) {
            }

            $this->exit(-2);
        } catch (Exception $err) {
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
            throw CommandException::invalid($commandClass);
        }

        $data = Data::parseCommand($commandClass);

        if (isset($this->commands[$data->command->name])) {
            throw CommandException::duplicate($commandClass);
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
     * @throws CommandException
     * @throws Error\TerminalException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function run(string $commandClass, ?ParserResult $result = null): void
    {
        $data = Data::parseCommand($commandClass);
        $args = $data->toArgs($result?->arguments ?? [], $result?->options ?? []);

        $command = new $commandClass(...$args);

        $this->closure($data->middlewares, $command)();
    }

    /**
     * Returns a command execution stack.
     *
     * @param MiddlewareInterface[] $middlewares
     * @param CommandInterface $command
     *
     * @return Closure
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function closure(array $middlewares, CommandInterface $command): Closure
    {
        if (empty($middlewares)) {
            return fn() => $command->execute($this, $this->printer);
        }

        $middleware = array_shift($middlewares);

        return fn() => $middleware->handle($command, $this, $this->printer, $this->closure($middlewares, $command));
    }

}
