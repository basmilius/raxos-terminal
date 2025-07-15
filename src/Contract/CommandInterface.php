<?php
declare(strict_types=1);

namespace Raxos\Terminal\Contract;

use Exception;
use Raxos\Terminal\Printer;

/**
 * Interface CommandInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Contract
 * @since 1.6.0
 */
interface CommandInterface
{

    /**
     * Executes the command with the given arguments. If something goes wrong, a
     * TerminalException is thrown.
     *
     * @param TerminalInterface $terminal
     * @param Printer $printer
     *
     * @throws Exception
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function execute(TerminalInterface $terminal, Printer $printer): void;

}
