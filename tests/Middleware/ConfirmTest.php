<?php
declare(strict_types=1);

use Raxos\Terminal\Internal\Data;
use Raxos\Terminal\Middleware\Confirm;
use RaxosTests\Terminal\{Confirmation, ExitSignal, SimpleCommand, UnitTerminal};
use function RaxosTests\Terminal\unitPrinter;

covers(Confirm::class);

it('continues for forced and confirmed operations', function (bool $force, bool $answer): void {
    [$printer] = unitPrinter();
    $middleware = new Confirm('Proceed?');
    Data::parseMiddleware(Confirm::class)->inject($middleware, options: ['force' => $force]);
    Confirmation::$answer = $answer;
    $called = 0;
    $middleware->handle(new SimpleCommand(), new UnitTerminal($printer), $printer, static function () use (&$called): void {
        ++$called;
    });
    expect($called)->toBe(1);
})->with([[true, false], [false, true]]);

it('exits before the command when confirmation is declined', function (): void {
    [$printer] = unitPrinter();
    $middleware = new Confirm();
    Data::parseMiddleware(Confirm::class)->inject($middleware);
    Confirmation::$answer = false;
    $called = false;
    try {
        $middleware->handle(new SimpleCommand(), new UnitTerminal($printer), $printer, static function () use (&$called): void {
            $called = true;
        });
        test()->fail('Declined operations must exit.');
    } catch (ExitSignal $error) {
        expect($error->getCode())->toBe(-1)->and($called)->toBeFalse();
    }
});
