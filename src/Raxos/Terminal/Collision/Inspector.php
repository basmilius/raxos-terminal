<?php
declare(strict_types=1);

namespace Raxos\Terminal\Collision;

use Whoops\Exception\Inspector as BaseInspector;

/**
 * Class Inspector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Collision
 * @since 1.0.2
 */
final class Inspector extends BaseInspector
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.2
     */
    protected function getTrace($e): array
    {
        return $e->getTrace();
    }

}
