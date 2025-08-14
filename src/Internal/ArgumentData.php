<?php
declare(strict_types=1);

namespace Raxos\Terminal\Internal;

use Raxos\Foundation\Contract\OptionInterface;
use Raxos\Terminal\Attribute\Argument;

/**
 * Class ArgumentData
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Internal
 * @since 1.6.0
 * @internal
 * @private
 */
final readonly class ArgumentData
{

    /**
     * ArgumentData constructor.
     *
     * @param Argument $argument
     * @param string $name
     * @param string[] $type
     * @param OptionInterface $defaultValue
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public Argument $argument,
        public string $name,
        public array $type,
        public OptionInterface $defaultValue
    ) {}

}
