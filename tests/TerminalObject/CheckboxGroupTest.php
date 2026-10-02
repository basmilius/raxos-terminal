<?php
declare(strict_types=1);

use Raxos\Terminal\TerminalObject\CheckboxGroup;

covers(CheckboxGroup::class);

it('preselects exact values and retains interactive changes', function (): void {
    $group = new CheckboxGroup(['first' => 'First', 'second' => 'Second', 1 => 'Numeric'], ['second', '1']);
    expect($group->getCheckedValues())->toBe(['second'])->and($group->count())->toBe(3);
    $group->toggleCurrent();
    expect($group->getCheckedValues())->toBe(['first', 'second']);
    $group->setCurrent('next');
    $group->toggleCurrent();
    expect($group->getCheckedValues())->toBe(['first']);
});
