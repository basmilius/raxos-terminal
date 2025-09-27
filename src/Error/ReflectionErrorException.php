<?php
declare(strict_types=1);

namespace Raxos\Terminal\Error;

use Raxos\Contract\Reflection\ReflectionFailedExceptionInterface;
use Raxos\Contract\Terminal\CommandExceptionInterface;
use Raxos\Error\Exception;
use ReflectionException;

/**
 * Class ReflectionErrorException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Error
 * @since 2.0.0
 */
final class ReflectionErrorException extends Exception implements CommandExceptionInterface, ReflectionFailedExceptionInterface
{

    /**
     * ReflectionErrorException constructor.
     *
     * @param string $class
     * @param ReflectionException $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $class,
        public readonly ReflectionException $err
    )
    {
        parent::__construct(
            'terminal_command_reflection_error',
            "Command or middleware {$this->class} had a reflection error.",
            previous: $err
        );
    }

}
