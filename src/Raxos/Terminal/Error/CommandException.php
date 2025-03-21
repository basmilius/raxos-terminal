<?php
declare(strict_types=1);

namespace Raxos\Terminal\Error;

use Raxos\Foundation\Error\ExceptionId;
use ReflectionException;
use function sprintf;

/**
 * Class CommandException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Error
 * @since 1.6.0
 */
final class CommandException extends TerminalException
{

    /**
     * Returns the exception for when a command is already registered.
     *
     * @param string $command
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public static function duplicate(string $command): self
    {
        return new self(
            ExceptionId::guess(),
            'terminal_command_duplicate',
            sprintf('The command "%s" is already defined.', $command)
        );
    }

    /**
     * Returns the exception for when a command is invalid.
     *
     * @param string $commandClass
     * @param string|null $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public static function invalid(string $commandClass, ?string $message = null): self
    {
        return new self(
            ExceptionId::guess(),
            'terminal_command_invalid',
            $message !== null
                ? sprintf('The command "%s" is invalid: %s', $commandClass, $message)
                : sprintf('The command "%s" is invalid.', $commandClass)
        );
    }

    /**
     * Returns the exception for when a required argument is missing.
     *
     * @param string $argument
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public static function missingArgument(string $argument): self
    {
        return new self(
            ExceptionId::guess(),
            'terminal_command_missing_argument',
            sprintf('The command is missing the required argument "%s".', $argument)
        );
    }

    /**
     * Returns the exception for when a required option is missing.
     *
     * @param string $option
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public static function missingOption(string $option): self
    {
        return new self(
            ExceptionId::guess(),
            'terminal_command_missing_option',
            sprintf('The command is missing the required option "%s".', $option)
        );
    }

    /**
     * Returns the exception for when a command wasn't found.
     *
     * @param string $command
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public static function notFound(string $command): self
    {
        return new self(
            ExceptionId::guess(),
            'terminal_command_not_found',
            sprintf('The command "%s" could not be found.', $command)
        );
    }

    /**
     * Returns the exception for when a reflection call failed.
     *
     * @param string $commandClass
     * @param ReflectionException $err
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public static function reflectionFailed(string $commandClass, ReflectionException $err): self
    {
        return new self(
            ExceptionId::guess(),
            'terminal_command_reflection_failed',
            sprintf('The command "%s" could not be reflected.', $commandClass),
            $err
        );
    }

}
