<?php
declare(strict_types=1);

namespace Raxos\Terminal\Error;

use Raxos\Contract\Terminal\CommandExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class InvalidCommandException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Error
 * @since 2.0.0
 */
final class InvalidCommandException extends Exception implements CommandExceptionInterface
{

    /**
     * InvalidCommandException constructor.
     *
     * @param string $commandClass
     * @param string|null $msg
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $commandClass,
        public ?string $msg = null
    )
    {
        parent::__construct(
            'terminal_command_invalid',
            $this->msg !== null
                ? "Command {$this->commandClass} is invalid: {$this->msg}"
                : "Command {$this->commandClass} is invalid."
        );
    }

}
