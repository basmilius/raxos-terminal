<?php
declare(strict_types=1);

namespace Raxos\Terminal\Error;

use Raxos\Contract\Terminal\CommandExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class MissingOptionException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Error
 * @since 2.0.0
 */
final class MissingOptionException extends Exception implements CommandExceptionInterface
{

    /**
     * MissingOptionException constructor.
     *
     * @param string $name
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $name
    )
    {
        parent::__construct(
            'terminal_command_missing_option',
            "Command is missing required option {$this->name}."
        );
    }

}
