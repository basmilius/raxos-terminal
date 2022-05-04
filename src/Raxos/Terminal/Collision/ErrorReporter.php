<?php
declare(strict_types=1);

namespace Raxos\Terminal\Collision;

use Exception;
use NunoMaduro\Collision\Adapters\Laravel\Inspector;

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
        $errorReporting = self::collision();
        $errorReporting->getHandler()->setException($err);
        $errorReporting->getHandler()->setInspector(new Inspector($err));
        $errorReporting->getHandler()->handle();
    }

    /**
     * Gets the Collision instance.
     *
     * @return Collision
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    private static function collision(): Collision
    {
        static $errorReporter = null;

        $errorReporter ??= new Collision();

        return $errorReporter;
    }

}
