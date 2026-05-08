<?php
declare(strict_types=1);

namespace Raxos\Terminal\Middleware;

use Attribute;
use Closure;
use Raxos\Contract\Terminal\{CommandInterface, MiddlewareInterface, TerminalInterface};
use Raxos\Terminal\Printer;
use function Raxos\Foundation\env;

/**
 * Class Environment
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Middleware
 * @since 2.2.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Environment implements MiddlewareInterface
{

    /**
     * Environment constructor.
     *
     * @param string $environment
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function __construct(
        public string $environment = 'production'
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function handle(CommandInterface $command, TerminalInterface $terminal, Printer $printer, Closure $next): void
    {
        if (env('MODE') === $this->environment) {
            $next();

            return;
        }

        $printer->error("This command is only available in the {$this->environment} environment.");
        $terminal->exit(-1);
    }

}
