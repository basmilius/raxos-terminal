<?php
declare(strict_types=1);

namespace Raxos\Terminal\Middleware;

use Attribute;
use Closure;
use Raxos\Terminal\Contract\{CommandInterface, MiddlewareInterface, TerminalInterface};
use Raxos\Terminal\Printer;
use function Raxos\Foundation\env;

/**
 * Class Caution
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Middleware
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Caution implements MiddlewareInterface
{

    /**
     * Caution constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $message = '⚠️ This command should generally not be used in production. Do you want to proceed?'
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function handle(CommandInterface $command, TerminalInterface $terminal, Printer $printer, Closure $next): void
    {
        if (env('MODE') !== 'production') {
            $next();

            return;
        }

        $confirm = $printer->confirm($this->message);

        if (!$confirm->confirmed()) {
            return;
        }

        $next();
    }

}
