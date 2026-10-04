<?php
declare(strict_types=1);

namespace Raxos\Terminal\Error;

use Raxos\Error\Exception;

/**
 * Class CommandExitException
 *
 * Carries an exit status back to Terminal::run without terminating the PHP process.
 *
 * @internal
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Error
 * @since 3.3.0
 */
final class CommandExitException extends Exception
{

    /**
     * Preserves the requested exit status for the programmable command entrypoint.
     *
     * @param int $status
     *
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function __construct(public readonly int $status)
    {
        parent::__construct('terminal_command_exit', 'Command execution stopped.');
    }

}
