<?php
declare(strict_types=1);

use Raxos\Terminal\Parser\ParserResult;

covers(ParserResult::class);

it('preserves raw input, nullable command and ordered positional and named values', function (): void {
    $result = new ParserResult('  raw  ', null, ['0', ''], ['flag' => true, 'empty' => '']);
    expect($result->raw)->toBe('  raw  ')->and($result->command)->toBeNull()
        ->and($result->arguments)->toBe(['0', ''])->and($result->options)->toBe(['flag' => true, 'empty' => '']);
});
