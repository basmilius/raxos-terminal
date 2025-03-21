<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use Raxos\Foundation\Option\Option;
use Raxos\Terminal\Attribute\Argument;

/**
 * Class ArgumentData
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.6.0
 */
final readonly class ArgumentData
{

    /**
     * ArgumentData constructor.
     *
     * @param Argument $argument
     * @param string $name
     * @param string[] $type
     * @param Option $defaultValue
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public Argument $argument,
        public string $name,
        public array $type,
        public Option $defaultValue
    ) {}

}
