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
     * Gets all registered commands.
     *
     * @return array<string, class-string<AbstractCommand>>
     * @author Bas Milius <bas@mili.us>
     * @since 1.4.0
     */
    public array $commands {
        get;
    }

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
     * Executes based on the given arguments.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function execute(): void;

}
