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

    /**
     * Parses the given command.
     *
     * @param string $rawCommand
     *
     * @return array|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     * @noinspection DuplicatedCode
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
                $key = $cursor->match('/^\w+/');
                $value = null;

                if ($cursor->peek() === '=') {
                    // --key=value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } else {
                        $value = $cursor->match('/^([a-zA-Z\d.\\\]+)/');
                    }
                } else if ($cursor->peek() === ' ' || $cursor->peek() === '') {
                    // --key
                    // --key value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } else if (($str = $cursor->match('/^([a-zA-Z\d.\\\]+)/')) !== null) {
                        $value = $str;
                    } else {
                        $value = true;
                    }
                }

                $result['options'][$key] = $value;

            } else if ($cursor->peek() === '"' || $cursor->peek() === "'") {

                // "string"
                // 'string'

                $result['arguments'][] = $cursor->quotedString();

            } else if ($result['command'] === null && $word = $cursor->match('/^\w+/')) {

                // string (Name of our command)

                $result['command'] = $word;

            } else if ($word = $cursor->match('/^([\w.\\\]+)/')) {

                // string (As an argument)

                $result['arguments'][] = $word;

            } else if ($cursor->peek() === '-') {

                // -key
                // -key=value
                // -key value

                $cursor->advance();
                $key = $cursor->match('/^\w+/');
                $value = null;

                if ($cursor->peek() === '=') {
                    // -key=value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } else {
                        $value = $cursor->match('/^([a-zA-Z\d.\\\]+)/');
                    }
                } else if ($cursor->peek() === ' ' || $cursor->peek() === '') {
                    // -key
                    // -key value

                    $cursor->advance();

                    if ($cursor->peek() === '"' || $cursor->peek() === "'") {
                        $value = $cursor->quotedString();
                    } else if (($str = $cursor->match('/^([a-zA-Z\d.\\\]+)/')) !== null) {
                        $value = $str;
                    } else {
                        $value = true;
                    }
                }

                $result['options'][$key] = $value;

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
        $arguments = array_map(fn(string $arg) => str_contains($arg, ' ') ? '"' . str_replace('"', '\"', $arg) . '"' : $arg, $arguments);
        $command = implode(' ', $arguments);

        return self::parse($command);
    }

}
