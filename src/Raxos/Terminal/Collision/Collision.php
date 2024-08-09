<?php
declare(strict_types=1);

namespace Raxos\Terminal\Collision;

use NunoMaduro\Collision\Handler;
use Whoops\Run;

/**
 * Class Collision
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Collision
 * @since 1.0.1
 */
final readonly class Collision
{

    public Handler $handler;
    public Run $run;

    /**
     * Collision constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct()
    {
        $this->handler = new Handler();
        $this->run = new Run();
    }

    /**
     * Register the error handler.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 09-08-2024
     */
    public function register(): void
    {
        $this->run
            ->pushHandler($this->handler)
            ->register();
    }

}
