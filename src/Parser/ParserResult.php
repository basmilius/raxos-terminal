<?php
declare(strict_types=1);

namespace Raxos\Terminal\Parser;

/**
 * Class ParserResult
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Parser
 * @since 2.0.0
 */
final readonly class ParserResult
{

    /**
     * ParserResult constructor.
     *
     * @param string $raw
     * @param string|null $command
     * @param string[] $arguments
     * @param array<string, string> $options
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $raw,
        public ?string $command,
        public array $arguments,
        public array $options
    ) {}

}
