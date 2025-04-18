<?php
declare(strict_types=1);

namespace Raxos\Terminal\Parser;

use function mb_strlen;
use function mb_substr;
use function preg_match;
use function stripslashes;

/**
 * Class TextCursor
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Parser
 * @since 1.0.1
 */
final class TextCursor
{

    public readonly int $maxLength;
    public private(set) int $position = 0;

    /**
     * TextCursor constructor.
     *
     * @param string $text
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct(
        public readonly string $text
    )
    {
        $this->maxLength = mb_strlen($this->text);
    }

    /**
     * Advance by one.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function advance(): void
    {
        ++$this->position;
    }

    /**
     * Advance by the given amount.
     *
     * @param int $num
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function advanceBy(int $num): void
    {
        $this->position += $num;
    }

    /**
     * Returns TRUE if we're at the end.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function atEnd(): bool
    {
        return $this->position >= $this->maxLength;
    }

    /**
     * Gets the next character.
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function character(): ?string
    {
        return mb_substr($this->text, $this->position, 1);
    }

    /**
     * Returns TRUE if the next character is a space.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function isSpace(): bool
    {
        return $this->peek() === ' ';
    }

    /**
     * Matches with the given pattern and returns the result. This will
     * also advance the cursor to the end of the result.
     *
     * @param string $pattern
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function match(string $pattern): ?string
    {
        $result = preg_match($pattern, $this->remainder(), $match);

        if ($result === false || ($match[0] ?? null) === null) {
            return null;
        }

        $this->advanceBy(mb_strlen($match[0]));

        return $match[0];
    }

    /**
     * Peek for the given number of characters.
     *
     * @param int $length
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function peek(int $length = 1): ?string
    {
        return mb_substr($this->text, $this->position, $length);
    }

    /**
     * Parses a quoted string and returns it without the quotes.
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function quotedString(): ?string
    {
        $str = $this->match("/^(?:\"(?:\"|[^\"])+\"|'(?:'|[^'])+')/i");

        if ($str !== null) {
            return stripslashes(mb_substr($str, 1, -1));
        }

        return null;
    }

    /**
     * Gets the remaining text.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function remainder(): string
    {
        return mb_substr($this->text, $this->position);
    }

}
