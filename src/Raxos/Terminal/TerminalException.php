<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use Raxos\Foundation\Error\RaxosException;

/**
 * Class TerminalException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal
 * @since 1.0.1
 */
final class TerminalException extends RaxosException
{

    public const int ERR_COMMAND_NOT_FOUND = 1;
    public const int ERR_COMMAND_FAILED = 2;
    public const int ERR_COMMAND_SYNTAX = 4;
    public const int ERR_ILLEGAL = 8;

}
