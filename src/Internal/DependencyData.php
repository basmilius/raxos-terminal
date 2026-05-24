<?php
declare(strict_types=1);

namespace Raxos\Terminal\Internal;

/**
 * Class DependencyData
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Internal
 * @since 2.1.0
 * @internal
 * @private
 */
final readonly class DependencyData
{

    /**
     * DependencyData constructor.
     *
     * @param string $name
     * @param string $type
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    public function __construct(
        public string $name,
        public string $type
    ) {}

}
