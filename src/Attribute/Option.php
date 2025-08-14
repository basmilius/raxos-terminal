<?php
declare(strict_types=1);

namespace Raxos\Terminal\Attribute;

use Attribute;
use Raxos\Foundation\Option\None;
use Raxos\Terminal\Contract\AttributeInterface;

/**
 * Class Option
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Attribute
 * @since 1.6.0
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class Option implements AttributeInterface
{

    /**
     * Option constructor.
     *
     * @param string|null $name
     * @param string|null $description
     * @param string|null $example
     * @param mixed $default
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $example = null,
        public mixed $default = None::class
    ) {}

}
