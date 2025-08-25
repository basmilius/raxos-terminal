<?php
declare(strict_types=1);

namespace Raxos\Terminal\Collision;

use NunoMaduro\Collision;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class ErrorReporter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Collision
 * @since 1.0.1
 */
final class ErrorReporter
{

    /**
     * Reports the given exception.
     *
     * @param Throwable $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public static function exception(Throwable $err): void
    {
        $provider = new Collision\Provider();
        $provider->register();

        $handler = $provider->getHandler();
        $handler->getWriter()->getOutput()->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $handler->setException($err);
        $handler->setInspector(new Inspector($err));
        $handler->handle();
    }

}
