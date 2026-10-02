<?php
declare(strict_types=1);

namespace Raxos\Terminal\Parser;

use RuntimeException;
use function array_shift;
use function count;
use function implode;
use function preg_match;
use function sprintf;
use function str_starts_with;

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
    private const string RE_ARG_VALUE = '/^([\w@:\-.\\\\\/]+)/';
    private const string RE_COMMAND = '/^[\w:-]+/';

    /**
     * Parses the given command.
     *
     * @param string $rawCommand
     *
     * @return ParserResult|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public static function parse(string $rawCommand): ?ParserResult
    {
        if (empty($rawCommand)) {
            return null;
        }

        $cursor = new TextCursor($rawCommand);
        $command = null;
        $arguments = [];
        $options = [];

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

                $options[$key] = $value;

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

                $options[$key] = $value;

            } elseif ($cursor->peek() === '"' || $cursor->peek() === "'") {

                // "string"
                // 'string'

                $arguments[] = $cursor->quotedString();

            } elseif ($command === null && $word = $cursor->match(self::RE_COMMAND)) {

                // string (Name of our command)

                $command = $word;

            } elseif ($word = $cursor->match(self::RE_ARG_VALUE)) {

                // string (As an argument)

                $arguments[] = $word;

            } else {

                throw new RuntimeException(sprintf('Could not parse %s', $cursor->remainder()));

            }
        } while (!$cursor->atEnd());

        return new ParserResult(
            $rawCommand,
            $command,
            $arguments,
            $options
        );
    }

    /**
     * Parses the command from the command line.
     *
     * @return ParserResult|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public static function parseFromArgs(): ?ParserResult
    {
        $tokens = $GLOBALS['argv'];
        array_shift($tokens);

        if ($tokens === []) {
            return null;
        }

        $command = array_shift($tokens);
        $arguments = [];
        $options = [];
        $positional = false;

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (!$positional && $token === '--') {
                $positional = true;
                continue;
            }

            if (!$positional && preg_match('/^--?([\w-]+)(?:=(.*))?$/sD', $token, $matches)) {
                $value = $matches[2] ?? true;

                if (!isset($matches[2]) && isset($tokens[$i + 1]) && !str_starts_with($tokens[$i + 1], '-')) {
                    $value = $tokens[++$i];
                }

                $options[$matches[1]] = $value;
            } else {
                $arguments[] = $token;
            }
        }

        return new ParserResult(implode(' ', [$command, ...$tokens]), $command, $arguments, $options);
    }

}
