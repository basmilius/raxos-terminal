<?php
declare(strict_types=1);

namespace Raxos\Terminal\Parser;

use JetBrains\PhpStorm\Pure;
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

    private int $maxLength;
    private int $position = 0;

    /**
     * TextCursor constructor.
     *
     * @param string $text
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function __construct(private readonly string $text)
    {
        $this->maxLength = mb_strlen($this->text);
    }

    /**
     * Gets the current position.
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public final function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Gets the text.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public final function getText(): string
    {
        return $this->text;
    }

    /**
     * Advance by one.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public final function advance(): void
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
    public final function advanceBy(int $num): void
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
    public final function atEnd(): bool
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
    public final function character(): ?string
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
    #[Pure]
    public final function isSpace(): bool
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
    public final function match(string $pattern): ?string
    {
        $result = preg_match($pattern, $this->remainder(), $match);

        if ($result === false || ($match[0] ?? null) === null) {
            return null;
        }

        $this->advanceBy(mb_strlen($match[0]));

        return $match[0];
    }

    /**
     * Peek for the given amount of characters.
     *
     * @param int $length
     *
     * @return string|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public final function peek(int $length = 1): ?string
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
    public final function quotedString(): ?string
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
    public final function remainder(): string
    {
        return mb_substr($this->text, $this->position);
    }

}
