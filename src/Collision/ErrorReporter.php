<?php
declare(strict_types=1);

namespace Raxos\Terminal\Collision;

use Exception;
use NunoMaduro\Collision;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param Exception $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public static function exception(Exception $err): void
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
