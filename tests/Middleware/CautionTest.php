<?php
declare(strict_types=1);

use Raxos\Terminal\Middleware\Caution;
use RaxosTests\Terminal\{Confirmation, ExitSignal, SimpleCommand, UnitTerminal};
use function RaxosTests\Terminal\unitPrinter;

covers(Caution::class);

it('requires confirmation in production and continues directly elsewhere', function (string $mode, bool $answer, bool $allowed): void {
    $old = getenv('MODE');
    putenv('MODE=' . $mode);
    Confirmation::$answer = $answer;
    [$printer] = unitPrinter();
    $called = false;
    try {
        new Caution()->handle(new SimpleCommand(), new UnitTerminal($printer), $printer, static function () use (&$called): void {
            $called = true;
        });
        expect($allowed)->toBeTrue()->and($called)->toBeTrue();
    } catch (ExitSignal $error) {
        expect($allowed)->toBeFalse()->and($called)->toBeFalse()->and($error->getCode())->toBe(-1);
    } finally {
        putenv($old === false ? 'MODE' : 'MODE=' . $old);
    }
})->with([['development', false, true], ['production', true, true], ['production', false, false]]);
