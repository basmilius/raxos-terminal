<?php
declare(strict_types=1);

namespace Raxos\Terminal\Middleware;

use Attribute;
use Closure;
use Raxos\Terminal\Attribute\Option;
use Raxos\Terminal\Contract\{CommandInterface, MiddlewareInterface, TerminalInterface};
use Raxos\Terminal\Printer;

/**
 * Class Confirm
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Middleware
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Confirm implements MiddlewareInterface
{

    #[Option(description: 'Skip the confirmation.', default: false)]
    public bool $force;

    /**
     * Confirm constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $message = 'Are you sure you want to proceed?'
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function handle(CommandInterface $command, TerminalInterface $terminal, Printer $printer, Closure $next): void
    {
        if (!$this->force) {
            $confirm = $printer->confirm($this->message);

            if (!$confirm->confirmed()) {
                $terminal->exit(-1);
            }
        }

        $next();
    }

}
