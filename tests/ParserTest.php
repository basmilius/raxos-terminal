<?php
declare(strict_types=1);

use Raxos\Terminal\Parser\Parser;

covers(Parser::class);

it('parses empty quoted positional and option values', function (): void {
    $result = Parser::parse('send "" --name=""');
    expect($result->command)->toBe('send');
    expect($result->arguments)->toBe(['']);
    expect($result->options)->toBe(['name' => '']);
});

it('rejects unterminated quotes immediately', function (): void {
    expect(fn() => Parser::parse('send "unclosed'))->toThrow(RuntimeException::class, 'Unterminated');
});

it('preserves Unicode and escaped quotes', function (): void {
    expect(Parser::parse('send "héllo \\"world\\""')->arguments)->toBe(['héllo "world"']);
});

it('preserves argv tokens without reparsing shell quoting', function (): void {
    $previous = $GLOBALS['argv'];
    try {
        $GLOBALS['argv'] = ['app.php', 'send', '', 'héllo "world"', '--url=https://example.org/?x=1&y=2', '--flag', '--', '-literal'];
        $result = Parser::parseFromArgs();
        expect($result->arguments)->toBe(['', 'héllo "world"', '-literal']);
        expect($result->options)->toBe(['url' => 'https://example.org/?x=1&y=2', 'flag' => true]);
    } finally {
        $GLOBALS['argv'] = $previous;
    }
});

it('keeps argv short options, negative positionals and repeated options intact', function (): void {
    $previous = $GLOBALS['argv'];
    try {
        $GLOBALS['argv'] = ['app.php', 'send', '-n', 'name', '--limit=0', '--flag', '--limit=2', '--', '-7', '--literal'];
        $result = Parser::parseFromArgs();
        expect($result->command)->toBe('send')
            ->and($result->options)->toBe(['n' => 'name', 'limit' => '2', 'flag' => true])
            ->and($result->arguments)->toBe(['-7', '--literal']);
    } finally {
        $GLOBALS['argv'] = $previous;
    }
});

it('returns no command for an empty argv', function (): void {
    $previous = $GLOBALS['argv'];
    try {
        $GLOBALS['argv'] = ['app.php'];
        expect(Parser::parseFromArgs())->toBeNull();
    } finally {
        $GLOBALS['argv'] = $previous;
    }
});

it('parses raw boolean flags and quoted option values', function (string $input, array $options): void {
    expect(Parser::parse($input)->options)->toBe($options);
})->with([['send --flag', ['flag' => true]], ['send --name="héllo world"', ['name' => 'héllo world']], ['send -n=0', ['n' => '0']]]);

it('keeps a zero positional argument and distinguishes successive flags', function (): void {
    $result = Parser::parse('send 0 --flag --other=1');
    expect($result->arguments)->toBe(['0'])->and($result->options)->toBe(['flag' => true, 'other' => '1']);
});

it('rejects option markers without a name', function (string $raw): void {
    expect(fn() => Parser::parse($raw))->toThrow(RuntimeException::class);
})->with(['send --', 'send -=value', 'send --=value']);

it('accepts no input and parses both short and long options with separate values', function (): void {
    expect(Parser::parse(''))->toBeNull()->and(Parser::parse('send -n name --limit 25')->options)->toBe(['n' => 'name', 'limit' => '25']);
});
