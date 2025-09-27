<?php
declare(strict_types=1);

namespace Raxos\Terminal\Error;

use Raxos\Contract\Terminal\CommandExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class DuplicateCommandException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Error
 * @since 2.0.0
 */
final class DuplicateCommandException extends Exception implements CommandExceptionInterface
{

    /**
     * DuplicateCommandException constructor.
     *
     * @param string $command
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $command
    )
    {
        parent::__construct(
            'terminal_command_duplicate',
            "Command {$this->command} is already defined."
        );
    }

}
