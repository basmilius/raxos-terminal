<?php
declare(strict_types=1);

namespace Raxos\Terminal\Collision;

use NunoMaduro\Collision\Contracts\Provider;
use NunoMaduro\Collision\Handler;
use Whoops\Run;

/**
 * Class Collision
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Collision
 * @since 1.0.1
 */
final readonly class Collision implements Provider
{

    /**
     * Collision constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct(
        public Handler $handler = new Handler(),
        public Run $run = new Run()
    )
    {
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function getHandler(): Handler
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function register(): Provider
    {
        $this->run
            ->pushHandler($this->handler)
            ->register();

        return $this;
    }

}
