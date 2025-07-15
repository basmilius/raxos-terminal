<?php
declare(strict_types=1);

namespace Raxos\Terminal\Attribute;

use Attribute;
use Raxos\Terminal\Contract\AttributeInterface;

/**
 * Class Command
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Attribute
 * @since 1.6.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Command implements AttributeInterface
{

    /**
     * Command constructor.
     *
     * @param string $name
     * @param string|null $description
     * @param string|null $usage
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $usage = null
    ) {}

}
