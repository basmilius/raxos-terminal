<?php
declare(strict_types=1);

use Raxos\Terminal\Command\HelpCommand;
use RaxosTests\Terminal\{UnitCommand, UnitTerminal};
use function RaxosTests\Terminal\{unitPrinter, withUnitArgs};

covers(HelpCommand::class);

it('renders required and optional arguments, option aliases and middleware options', function (): void {
    [$printer, $buffer] = unitPrinter();
    $terminal = new UnitTerminal($printer)->register(UnitCommand::class);
    withUnitArgs([], fn() => new HelpCommand('unit')->execute($terminal, $printer, false));
    expect($buffer->get())->toContain('unit [count] (name)', 'Number of items.', '(optional)', '--enabled', '--trace', 'TraceMiddleware', 'Example: unit.php unit');
});

it('lists descriptions in compact help and filters unknown command names', function (): void {
    [$printer, $buffer] = unitPrinter();
    $terminal = new UnitTerminal($printer)->register(UnitCommand::class);
    new HelpCommand()->execute($terminal, $printer, false);
    expect($buffer->get())->toContain('unit', 'A test command.', 'help');
    $buffer->clean();
    new HelpCommand('unknown')->execute($terminal, $printer, false);
    expect($buffer->get())->toBe('');
});
