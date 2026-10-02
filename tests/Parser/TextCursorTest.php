<?php
declare(strict_types=1);

use Raxos\Terminal\Parser\TextCursor;

covers(TextCursor::class);

it('tracks Unicode characters rather than byte offsets', function (): void {
    $cursor = new TextCursor('é 😀!');
    expect($cursor->maxLength)->toBe(4)->and($cursor->peek(2))->toBe('é ')->and($cursor->character())->toBe('é');
    $cursor->advance();
    expect($cursor->isSpace())->toBeTrue()->and($cursor->position)->toBe(1);
    $cursor->advanceBy(2);
    expect($cursor->remainder())->toBe('!')->and($cursor->atEnd())->toBeFalse();
    $cursor->advance();
    expect($cursor->peek())->toBe('')->and($cursor->atEnd())->toBeTrue();
});

it('advances only after a successful match', function (): void {
    $cursor = new TextCursor('hello world');
    expect($cursor->match('/^\d+/'))->toBeNull()->and($cursor->position)->toBe(0)
        ->and($cursor->match('/^\w+/'))->toBe('hello')->and($cursor->position)->toBe(5);
});

it('unescapes matching quotes and backslashes while keeping other escapes', function (string $raw, string $expected): void {
    $cursor = new TextCursor($raw . ' tail');
    expect($cursor->quotedString())->toBe($expected)->and($cursor->remainder())->toBe(' tail');
})->with([['""', ''], ["'héllo'", 'héllo'], ['"a\\"b"', 'a"b'], ["'a\\\\b'", 'a\\b'], ['"a\\nb"', 'a\\nb']]);

it('rejects an unterminated quoted string at the end of input', function (): void {
    expect(fn () => new TextCursor('"unterminated')->quotedString())->toThrow(RuntimeException::class, 'Unterminated');
});
