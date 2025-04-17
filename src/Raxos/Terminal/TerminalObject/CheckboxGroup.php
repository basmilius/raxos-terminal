<?php
declare(strict_types=1);

namespace Raxos\Terminal\TerminalObject;

use League\CLImate\TerminalObject\Dynamic\Checkbox\Checkbox;
use League\CLImate\TerminalObject\Dynamic\Checkbox\CheckboxGroup as BaseCheckboxGroup;
use function in_array;

/**
 * Class CheckboxGroup
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\TerminalObject
 * @since 1.6.0
 */
final class CheckboxGroup extends BaseCheckboxGroup
{

    /**
     * CheckboxGroup constructor.
     *
     * @param array $options
     * @param array $selected
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(array $options, array $selected = [])
    {
        parent::__construct($options);

        /** @var Checkbox $checkbox */
        foreach ($this->checkboxes as $checkbox) {
            if (!in_array($checkbox->getValue(), $selected, true)) {
                continue;
            }

            $checkbox->setChecked();
        }
    }

}
