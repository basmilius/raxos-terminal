<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use Raxos\Foundation\Contract\OptionInterface;
use Raxos\Terminal\Attribute\Option;

/**
 * Class OptionData
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.6.0
 */
final readonly class OptionData
{

    /**
     * OptionData constructor.
     *
     * @param Option $option
     * @param string $name
     * @param string[] $type
     * @param OptionInterface $defaultValue
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public Option $option,
        public string $name,
        public array $type,
        public OptionInterface $defaultValue
    ) {}

}
