<?php
declare(strict_types=1);

use Raxos\Terminal\Error\DuplicateCommandException;
use Raxos\Terminal\Error\InvalidCommandException;
use Raxos\Terminal\Terminal;
use RaxosTests\Terminal\ExitSignal;
use RaxosTests\Terminal\ExplicitExitCommand;
use RaxosTests\Terminal\NestedRunCommand;
use RaxosTests\Terminal\SimpleCommand;
use RaxosTests\Terminal\UnitCommand;
use RaxosTests\Terminal\UnitTerminal;
use function RaxosTests\Terminal\unitPrinter;
use function RaxosTests\Terminal\withUnitArgs;

covers(Terminal::class);

it('registers valid commands fluently and rejects invalid or duplicate registration', function (): void {
    $terminal = new Terminal(unitPrinter()[0]);
    expect($terminal->register(UnitCommand::class))->toBe($terminal)->and($terminal->commands['unit'])->toBe(UnitCommand::class);
    expect(fn() => $terminal->register(UnitCommand::class))->toThrow(DuplicateCommandException::class);
    expect(fn() => $terminal->register(stdClass::class))->toThrow(InvalidCommandException::class);
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

it('runs an explicit argv vector without modifying global arguments or terminating', function (): void {
    [$printer, $buffer] = unitPrinter();
    $terminal = new Terminal($printer)->register(SimpleCommand::class)->register(UnitCommand::class);
    $original = $GLOBALS['argv'];
    expect($terminal->run(['tool', 'simple']))->toBe(0)->and($GLOBALS['argv'])->toBe($original);
    expect($terminal->run(['tool', 'missing']))->toBe(-2)->and($buffer->get())->toContain('missing');
    expect($terminal->run(['tool', 'unit']))->toBe(-2);
    expect($terminal->run(['tool', 'simple']))->toBe(0);
});

it('returns explicit command exits and can run again in the same process', function (): void {
    $terminal = new Terminal(unitPrinter()[0])->register(ExplicitExitCommand::class)->register(SimpleCommand::class);
    expect($terminal->run(['tool', 'explicit-exit', '7']))->toBe(7)
        ->and($terminal->run(['tool', 'explicit-exit', '0']))->toBe(0)
        ->and($terminal->run(['tool', 'simple']))->toBe(0);
});

it('keeps the outer programmable lifecycle active after an inner run exits', function (): void {
    $terminal = new Terminal(unitPrinter()[0])
        ->register(NestedRunCommand::class)
        ->register(ExplicitExitCommand::class)
        ->register(SimpleCommand::class);
    $originalArguments = $GLOBALS['argv'];

    expect($terminal->run(['tool', 'nested-run']))->toBe(8)
        ->and(NestedRunCommand::$innerStatus)->toBe(7)
        ->and($GLOBALS['argv'])->toBe($originalArguments)
        ->and($terminal->run(['tool', 'simple']))->toBe(0);
});
