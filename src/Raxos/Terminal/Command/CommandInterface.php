<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use Exception;
use Raxos\Terminal\TerminalException;

/**
 * Interface CommandInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.0.17
 */
interface CommandInterface
{

    /**
     * Executes the command with the given arguments. If something goes wrong
     * an TerminalException is thrown.
     *
     * @param array $arguments
     * @param array $options
     *
     * @throws Exception
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function execute(array $arguments, array $options): void;

    /**
     * Gets the command spec.
     *
     * @return CommandSpec
     * @throws TerminalException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function spec(): CommandSpec;

}
