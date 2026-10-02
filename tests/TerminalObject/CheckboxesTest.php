<?php
declare(strict_types=1);

use League\CLImate\Util\Reader\Stream;
use League\CLImate\Util\System\System;
use League\CLImate\Util\UtilFactory;
use Raxos\Terminal\TerminalObject\Checkboxes;
use function RaxosTests\Terminal\unitPrinter;

covers(Checkboxes::class);

it('returns the selected values as an array after confirming through an injected reader', function (): void {
    [$printer, $buffer] = unitPrinter();
    $system = test()->createStub(System::class);
    $system->method('exec')->willReturn('');
    $printer->setUtil(new UtilFactory($system));
    $reader = test()->createMock(Stream::class);
    $reader->expects(test()->once())->method('char')->with(1)->willReturn("\n");
    $checkboxes = $printer->checkboxes('Select', ['a' => 'A', 'b' => 'B'], ['b'], $reader);
    expect($checkboxes->prompt())->toBe(['b'])->and($buffer->get())->toContain('Select', 'A', 'B');
});
