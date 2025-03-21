<?php
declare(strict_types=1);

namespace Raxos\Terminal;

use JetBrains\PhpStorm\ExpectedValues;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Basic\{Columns};
use League\CLImate\TerminalObject\Dynamic\{Animation, Confirm, Input, Padding, Password, Progress, Radio, Spinner};
use League\CLImate\TerminalObject\Helper\Sleeper;
use League\CLImate\Util\Reader\ReaderInterface;
use Raxos\Terminal\TerminalObject\Checkboxes;
use function sprintf;

/**
 * Class Printer
 *
 * @method self black(string $str = null)
 * @method self red(string $str = null)
 * @method self green(string $str = null)
 * @method self yellow(string $str = null)
 * @method self blue(string $str = null)
 * @method self magenta(string $str = null)
 * @method self cyan(string $str = null)
 * @method self lightGray(string $str = null)
 * @method self darkGray(string $str = null)
 * @method self lightRed(string $str = null)
 * @method self lightGreen(string $str = null)
 * @method self lightYellow(string $str = null)
 * @method self lightBlue(string $str = null)
 * @method self lightMagenta(string $str = null)
 * @method self lightCyan(string $str = null)
 * @method self white(string $str = null)
 *
 * @method self backgroundBlack(string $str = null)
 * @method self backgroundRed(string $str = null)
 * @method self backgroundGreen(string $str = null)
 * @method self backgroundYellow(string $str = null)
 * @method self backgroundBlue(string $str = null)
 * @method self backgroundMagenta(string $str = null)
 * @method self backgroundCyan(string $str = null)
 * @method self backgroundLightGray(string $str = null)
 * @method self backgroundDarkGray(string $str = null)
 * @method self backgroundLightRed(string $str = null)
 * @method self backgroundLightGreen(string $str = null)
 * @method self backgroundLightYellow(string $str = null)
 * @method self backgroundLightBlue(string $str = null)
 * @method self backgroundLightMagenta(string $str = null)
 * @method self backgroundLightCyan(string $str = null)
 * @method self backgroundWhite(string $str = null)
 *
 * @method self bold(string $str = null)
 * @method self dim(string $str = null)
 * @method self underline(string $str = null)
 * @method self blink(string $str = null)
 * @method self invert(string $str = null)
 * @method self hidden(string $str = null)
 *
 * @method self info(string $str = null)
 * @method self comment(string $str = null)
 * @method self whisper(string $str = null)
 * @method self shout(string $str = null)
 * @method self error(string $str = null)
 *
 * @method self out(string $str)
 * @method self inline(string $str)
 * @method self table(array $data)
 * @method self json(mixed $var)
 * @method self br($count = 1)
 * @method self tab($count = 1)
 * @method self draw(string $art)
 * @method self border(string $char = null, integer $length = null)
 * @method self dump(mixed $var)
 * @method self flank(string $output, string $char = null, integer $length = null)
 *
 * @method Progress progress(integer $total = null)
 * @method Spinner spinner(string $label = null, string ...$characters = null)
 * @method Padding padding(integer $length = 0, string $char = '.')
 * @method Input input(string $prompt, ReaderInterface $reader = null)
 * @method Confirm confirm(string $prompt, ReaderInterface $reader = null)
 * @method Password password(string $prompt, ReaderInterface $reader = null)
 * @method Checkboxes checkboxes(string $prompt, array $options, array $selected = [], ReaderInterface $reader = null)
 * @method Radio radio(string $prompt, array $options, ReaderInterface $reader = null)
 * @method Animation animation(string $art, Sleeper $sleeper = null)
 * @method Columns columns(array $data, $column_count = null)
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal
 * @since 1.0.1
 */
final class Printer extends CLImate
{

    public const string CORRECT = '✔';
    public const string INCORRECT = '✘';

    /**
     * Printer constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->router->addExtension('checkboxes', Checkboxes::class);
    }

    /**
     * Write that something is correct.
     *
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function correct(string $message): self
    {
        return $this->green(sprintf("%s %s", self::CORRECT, $message));
    }

    /**
     * Write that something is incorrect.
     *
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function incorrect(string $message): self
    {
        return $this->to('error')->error(sprintf("%s %s", self::INCORRECT, $message));
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function to(#[ExpectedValues(['error', 'out'])] $writer): self
    {
        return parent::to($writer);
    }

}
