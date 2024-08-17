<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use JetBrains\PhpStorm\Pure;
use Raxos\Terminal\{Printer, Terminal};

/**
 * Class AbstractCommand
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.0.1
 */
abstract class AbstractCommand implements CommandInterface
{

    public readonly Printer $printer;

    /**
     * AbstractCommand constructor.
     *
     * @param Terminal $terminal
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    #[Pure]
    public function __construct(
        public readonly Terminal $terminal
    )
    {
        $this->printer = $terminal->printer;
    }

}
