<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Raxos\Foundation\Error\{ExceptionId, RaxosException};
use Raxos\Terminal\Command\AbstractCommand;
use function sprintf;

/**
 * Class TerminalException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal
 * @since 1.0.17
 */
final class TerminalException extends RaxosException
{

    /**
     * Returns a command not found exception.
     *
     * @param string $command
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function commandNotFound(string $command): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'terminal_command_not_found',
            sprintf('Command "%s" not found.', $command)
        );
    }

    /**
     * Returns a duplicate command exception.
     *
     * @param string $commandClass
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function duplicateCommand(string $commandClass): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'terminal_duplicate_command',
            sprintf('Command "%s" is already defined.', $commandClass)
        );
    }

    /**
     * Returns an invalid command exception.
     *
     * @param string $commandClass
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function invalidCommand(string $commandClass): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'terminal_duplicate_command',
            sprintf('Command "%s" is invalid. Commands should extend from "%s" and have a spec.', $commandClass, AbstractCommand::class)
        );
    }

}
