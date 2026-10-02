<?php
declare(strict_types=1);

use Raxos\Terminal\Collision\Inspector;

covers(Inspector::class);

it('preserves the original exception trace and message', function (): void {
    $error = new LogicException('inspect');
    $inspector = new Inspector($error);
    expect($inspector->getException())->toBe($error)->and($inspector->getException()->getTrace())->toBe($error->getTrace())
        ->and($inspector->getFrames()[0]->getFile())->toBe($error->getFile())
        ->and($inspector->getFrames()[0]->getLine())->toBe($error->getLine());
});
