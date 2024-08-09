<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Exception;
use Raxos\Terminal\Collision\ErrorReporter;
use Raxos\Terminal\Command\{AbstractCommand, HelpCommand};
use Raxos\Terminal\Parser\Parser;
use function is_subclass_of;
use function sprintf;

/**
 * Class Terminal
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal
 * @since 1.0.1
 */
class Terminal
{

    private array $commands = [HelpCommand::class];
    private readonly Printer $printer;

    /**
     * Terminal constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct()
    {
        $this->printer = new Printer();
    }

    /**
     * Gets all registered commands.
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Gets the printer instance.
     *
     * @return Printer
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getPrinter(): Printer
    {
        return $this->printer;
    }

    /**
     * Adds the given command to the terminal.
     *
     * @param string $name
     * @param string $commandClass
     *
     * @return $this
     * @throws TerminalException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function addCommand(string $name, string $commandClass): self
    {
        if (!is_subclass_of($commandClass, AbstractCommand::class)) {
            throw new TerminalException(sprintf('Command class "%s" should extend from "%s".', $commandClass, AbstractCommand::class), TerminalException::ERR_ILLEGAL);
        }

        if (isset($this->commands[$name])) {
            throw new TerminalException(sprintf('Command "%s" is already added to the terminal.', $name), TerminalException::ERR_ILLEGAL);
        }

        $this->commands[$name] = $commandClass;

        return $this;
    }

    /**
     * Executes based on the given arguments.
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function execute(): self
    {
        $cl = Parser::parseFromArgs();

        try {
            if ($cl === null || $cl['command'] === null) {
                $command = new HelpCommand($this);
                $command->execute([], []);
            } else {
                /** @var AbstractCommand|null $commandClass */
                $commandClass = null;

                /** @var AbstractCommand $command */
                foreach ($this->commands as $command) {
                    $spec = $command::spec();

                    if ($spec->getName() === $cl['command']) {
                        $commandClass = $command;
                        break;
                    }
                }

                if ($commandClass === null) {
                    throw new TerminalException(sprintf('Command "%s" not found.', $cl['command']), TerminalException::ERR_COMMAND_NOT_FOUND);
                }

                /** @var AbstractCommand $command */
                $command = new $commandClass($this);
                $command->execute($cl['arguments'], $cl['options']);
            }
        } catch (Exception $err) {
            ErrorReporter::exception($err);
        }

        return $this;
    }

}
