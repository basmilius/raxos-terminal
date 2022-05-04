<?php
/** @noinspection PhpInternalEntityUsedInspection */
declare(strict_types=1);

namespace Raxos\Terminal\Collision;

use NunoMaduro\Collision\Contracts\Provider;
use NunoMaduro\Collision\Handler;
use Whoops\Run;
use Whoops\RunInterface;

/**
 * Class Collision
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Collision
 * @since 1.0.1
 */
final class Collision implements Provider
{

    protected Handler $handler;
    protected RunInterface $run;

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
