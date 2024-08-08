<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use JetBrains\PhpStorm\Pure;
use Raxos\Terminal\{Printer, Terminal, TerminalException};

/**
 * Class AbstractCommand
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.0.1
 */
abstract class AbstractCommand
{

    protected readonly Printer $printer;

    /**
     * AbstractCommand constructor.
     *
     * @param Terminal $terminal
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    #[Pure]
    public function __construct(protected readonly Terminal $terminal)
    {
        $this->printer = $terminal->getPrinter();
    }

    /**
     * Executes the command with the given arguments. If something goes wrong
     * an TerminalException is thrown.
     *
     * @param array $arguments
     * @param array $options
     *
     * @throws TerminalException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public abstract function execute(array $arguments, array $options): void;

    /**
     * Gets the command spec.
     *
     * @return CommandSpec
     * @throws TerminalException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public static function spec(): CommandSpec
    {
        throw new TerminalException('Spec not implemented.', TerminalException::ERR_COMMAND_SYNTAX);
    }

}
