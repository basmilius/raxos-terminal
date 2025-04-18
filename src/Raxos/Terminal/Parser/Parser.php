<?php
declare(strict_types=1);

namespace Raxos\Terminal\Parser;

use JetBrains\PhpStorm\ArrayShape;
use RuntimeException;
use function array_map;
use function array_shift;
use function implode;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Class Parser
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Parser
 * @since 1.0.1
 */
final class Parser
{

    private const string RE_ARG_KEY = '/^[\w-]+/';
    private const string RE_ARG_VALUE = '/^([\w@:\-.\\\]+)/';
    private const string RE_COMMAND = '/^[\w:-]+/';

    /**
     * Parses the given command.
     *
     * @param string $rawCommand
     *
     * @return array|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    #[ArrayShape([
        'raw' => 'string',
        'command' => 'string|null',
        'arguments' => 'string[]',
        'options' => 'array<string, string>'
    ])]
    public static function parse(string $rawCommand): ?array
    {
        if (empty($rawCommand)) {
            return null;
        }

        $cursor = new TextCursor($rawCommand);
        $result = [
            'raw' => $rawCommand,
            'command' => null,
            'arguments' => [],
            'options' => []
        ];

        do {
            if ($cursor->isSpace()) {
                $cursor->advance();
                continue;
            }

            if ($cursor->peek(2) === '--') {

                // --key
                // --key=value
                // --key value

                $cursor->advanceBy(2);
                $key = $cursor->match(self::RE_ARG_KEY);
                $value = null;

                if ($cursor->peek() === '=') {
                    // --key=value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } else {
                        $value = $cursor->match(self::RE_ARG_VALUE);
                    }
                } elseif ($cursor->peek() === ' ' || $cursor->peek() === '') {
                    // --key
                    // --key value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } elseif (($str = $cursor->match(self::RE_ARG_VALUE)) !== null) {
                        $value = $str;
                    } else {
                        $value = true;
                    }
                }

                $result['options'][$key] = $value;

            } elseif ($cursor->peek() === '-') {

                // -key
                // -key=value
                // -key value

                $cursor->advance();
                $key = $cursor->match(self::RE_ARG_KEY);
                $value = null;

                if ($cursor->peek() === '=') {
                    // -key=value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } else {
                        $value = $cursor->match(self::RE_ARG_VALUE);
                    }
                } elseif ($cursor->peek() === ' ' || $cursor->peek() === '') {
                    // -key
                    // -key value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } elseif (($str = $cursor->match(self::RE_ARG_VALUE)) !== null) {
                        $value = $str;
                    } else {
                        $value = true;
                    }
                }

                $result['options'][$key] = $value;

            } elseif ($cursor->peek() === '"' || $cursor->peek() === "'") {

                // "string"
                // 'string'

                $result['arguments'][] = $cursor->quotedString();

            } elseif ($result['command'] === null && $word = $cursor->match(self::RE_COMMAND)) {

                // string (Name of our command)

                $result['command'] = $word;

            } elseif ($word = $cursor->match(self::RE_ARG_VALUE)) {

                // string (As an argument)

                $result['arguments'][] = $word;

            } else {

                throw new RuntimeException(sprintf('Could not parse %s', $cursor->remainder()));

            }
        } while (!$cursor->atEnd());

        return $result;
    }

    /**
     * Parses the command from the command line.
     *
     * @return array|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    #[ArrayShape([
        'raw' => 'string',
        'command' => 'string|null',
        'arguments' => 'string[]',
        'options' => 'array<string, string>'
    ])]
    public static function parseFromArgs(): ?array
    {
        $arguments = $GLOBALS['argv'];
        array_shift($arguments);
        $arguments = array_map(static fn(string $arg) => str_contains($arg, ' ') ? '"' . str_replace('"', '\"', $arg) . '"' : $arg, $arguments);
        $command = implode(' ', $arguments);

        return self::parse($command);
    }

}
