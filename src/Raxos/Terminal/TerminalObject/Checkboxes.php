<?php
declare(strict_types=1);

namespace Raxos\Terminal\TerminalObject;

use League\CLImate\TerminalObject\Dynamic\Checkboxes as BaseCheckboxes;
use League\CLImate\Util\Reader\ReaderInterface;

/**
 * Class Checkboxes
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\TerminalObject
 * @since 1.6.0
 */
final class Checkboxes extends BaseCheckboxes
{

    /**
     * Checkboxes constructor.
     *
     * @param $prompt
     * @param array $options
     * @param array $selected
     * @param ReaderInterface|null $reader
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct($prompt, array $options, private array $selected = [], ?ReaderInterface $reader = null)
    {
        parent::__construct($prompt, $options, $reader);
    }

    /**
     * {@inheritdoc}
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function prompt(): array
    {
        return (array)parent::prompt();
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    protected function buildCheckboxes(array $options): CheckboxGroup
    {
        return new CheckboxGroup($options, $this->selected);
    }

}
