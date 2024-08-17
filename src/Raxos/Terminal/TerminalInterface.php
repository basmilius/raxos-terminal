<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Raxos\Terminal\Command\AbstractCommand;

/**
 * Interface TerminalInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal
 * @since 1.0.17
 */
interface TerminalInterface
{

    /**
     * Adds the given command to the terminal.
     *
     * @param string $commandClass
     *
     * @return self
     * @throws TerminalException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function addCommand(string $commandClass): self;

    /**
     * Gets all registered commands.
     *
     * @return class-string<AbstractCommand>[]
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function getCommands(): array;

    /**
     * Executes based on the given arguments.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function execute(): void;

}
