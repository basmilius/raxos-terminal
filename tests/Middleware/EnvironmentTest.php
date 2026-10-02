<?php
declare(strict_types=1);

use Raxos\Terminal\Middleware\Environment;
use RaxosTests\Terminal\{ExitSignal, SimpleCommand, UnitTerminal};
use function RaxosTests\Terminal\unitPrinter;

covers(Environment::class);

it('runs only in the requested environment and explains rejection', function (string $mode, bool $allowed): void {
    $old = getenv('MODE');
    putenv('MODE=' . $mode);
    [$printer, $buffer] = unitPrinter();
    $called = false;
    try {
        new Environment('unit')->handle(new SimpleCommand(), new UnitTerminal($printer), $printer, static function () use (&$called): void {
            $called = true;
        });
        expect($allowed)->toBeTrue()->and($called)->toBeTrue();
    } catch (ExitSignal $error) {
        expect($allowed)->toBeFalse()->and($called)->toBeFalse()->and($error->getCode())->toBe(-1)
            ->and($buffer->get())->toContain('only available in the unit environment');
    } finally {
        putenv($old === false ? 'MODE' : 'MODE=' . $old);
    }
})->with([['unit', true], ['development', false]]);
