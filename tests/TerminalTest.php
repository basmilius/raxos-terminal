<?php
declare(strict_types=1);

use Raxos\Terminal\Error\{DuplicateCommandException, InvalidCommandException};
use Raxos\Terminal\Terminal;
use RaxosTests\Terminal\{ExitSignal, SimpleCommand, UnitCommand, UnitTerminal};
use function RaxosTests\Terminal\{unitPrinter, withUnitArgs};

covers(Terminal::class);

it('registers valid commands fluently and rejects invalid or duplicate registration', function (): void {
    $terminal = new Terminal(unitPrinter()[0]);
    expect($terminal->register(UnitCommand::class))->toBe($terminal)->and($terminal->commands['unit'])->toBe(UnitCommand::class);
    expect(fn () => $terminal->register(UnitCommand::class))->toThrow(DuplicateCommandException::class);
    expect(fn () => $terminal->register(stdClass::class))->toThrow(InvalidCommandException::class);
});

it('runs injected middleware in declaration order around the instantiated command', function (): void {
    UnitCommand::$events = [];
    $terminal = new UnitTerminal(unitPrinter()[0])->register(UnitCommand::class);
    withUnitArgs(['unit', '7', '--enabled', '--trace=3'], $terminal->execute(...));
    expect(UnitCommand::$events)->toBe([['outer', 3, 'before'], ['inner', 3, 'before'], ['command', 7], ['inner', 3, 'after'], ['outer', 3, 'after']])
        ->and(UnitCommand::$last->active)->toBeTrue();
});

it('runs commands without middleware', function (): void {
    UnitCommand::$events = [];
    $terminal = new UnitTerminal(unitPrinter()[0])->register(SimpleCommand::class);
    withUnitArgs(['simple'], $terminal->execute(...));
    expect(UnitCommand::$events)->toBe([['simple']]);
});

it('shows contextual help for missing arguments and unknown commands', function (array $args, string $message): void {
    [$printer, $buffer] = unitPrinter();
    $terminal = new UnitTerminal($printer)->register(UnitCommand::class);
    try {
        withUnitArgs($args, $terminal->execute(...));
        test()->fail('The command must fail.');
    } catch (ExitSignal $signal) {
        expect($signal->getCode())->toBe(-2)->and($buffer->get())->toContain($message);
    }
})->with([[['unit'], 'count'], [['missing-unit'], 'missing-unit']]);

it('shows global help when no command is provided', function (): void {
    [$printer, $buffer] = unitPrinter();
    withUnitArgs([], new UnitTerminal($printer)->execute(...));
    expect($buffer->get())->toContain('help', 'Based on Raxos Terminal.');
});
