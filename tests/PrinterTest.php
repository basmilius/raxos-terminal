<?php
declare(strict_types=1);

use Raxos\Terminal\Printer;
use Raxos\Terminal\TerminalObject\Checkboxes;
use function RaxosTests\Terminal\unitPrinter;

covers(Printer::class);

it('writes success and error symbols to the selected writers fluently', function (): void {
    [$printer, $buffer] = unitPrinter();
    expect($printer->correct('saved'))->toBe($printer)->and($printer->incorrect('failed'))->toBe($printer)
        ->and($printer->to('out'))->toBe($printer);
    $printer->out('next');
    expect($buffer->get())->toBe("✔ saved\n✘ failed\nnext\n");
});

it('registers the custom checkbox extension with initial selections', function (): void {
    [$printer] = unitPrinter();
    expect($printer->checkboxes('Select', ['a', 'b'], ['a']))->toBeInstanceOf(Checkboxes::class);
});
