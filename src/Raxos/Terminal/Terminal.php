<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Exception;
use Raxos\Terminal\Collision\ErrorReporter;
use Raxos\Terminal\Command\{AbstractCommand, HelpCommand};
use Raxos\Terminal\Parser\Parser;
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

    private array $commands = [HelpCommand::class];

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
    public function addCommand(string $commandClass): self
    {
        if (!is_subclass_of($commandClass, AbstractCommand::class)) {
            throw TerminalException::invalidCommand($commandClass);
        }

        $spec = $commandClass::spec();
        $name = $spec->getName();

        if (isset($this->commands[$name])) {
            throw TerminalException::duplicateCommand($commandClass);
        }

        $this->commands[$name] = $commandClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function execute(): void
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
                    throw TerminalException::commandNotFound($cl['command']);
                }

                /** @var AbstractCommand $command */
                $command = new $commandClass($this);
                $command->execute($cl['arguments'], $cl['options']);
            }
        } catch (Exception $err) {
            ErrorReporter::exception($err);
        }
    }

}
