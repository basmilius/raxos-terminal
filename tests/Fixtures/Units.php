<?php
declare(strict_types=1);

namespace RaxosTests\Terminal;

use Attribute;
use Closure;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Confirm as ClimateConfirm;
use League\CLImate\Util\Output;
use League\CLImate\Util\Writer\Buffer;
use Raxos\Contract\Terminal\CommandInterface;
use Raxos\Contract\Terminal\MiddlewareInterface;
use Raxos\Contract\Terminal\TerminalInterface;
use Raxos\Terminal\Attribute\Argument;
use Raxos\Terminal\Attribute\Command;
use Raxos\Terminal\Attribute\Option;
use Raxos\Terminal\Printer;
use Raxos\Terminal\Terminal;
use RuntimeException;

final class ExitSignal extends RuntimeException
{
}

final class UnitTerminal extends Terminal
{
    public function exit(int $code = 0): never
    {
        throw new ExitSignal('exit', $code);
    }
}

final class Confirmation extends ClimateConfirm
{
    public static bool $answer = true;

    public function confirmed(): bool
    {
        return self::$answer;
    }
}

final class UnitService
{
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class TraceMiddleware implements MiddlewareInterface
{
    #[Option(name: 'trace', default: 0)]
    public int $trace;

    public function __construct(public string $name = 'outer')
    {
    }

    public function handle(CommandInterface $command, TerminalInterface $terminal, Printer $printer, Closure $next): void
    {
        UnitCommand::$events[] = [$this->name, $this->trace, 'before'];
        $next();
        UnitCommand::$events[] = [$this->name, $this->trace, 'after'];
    }
}

#[Command('unit', 'A test command.', 'unit [count] --enabled=true')]
#[TraceMiddleware('outer')]
#[TraceMiddleware('inner')]
final class UnitCommand implements CommandInterface
{
    public static array $events = [];

    public static ?self $last = null;

    public function __construct(
        #[Argument('count', 'Number of items.')]
        public int $number,
        #[Argument]
        public ?string $name = null,
        #[Option('enabled')]
        public bool $active = false,
        #[Option]
        public float $weight = 1.5,
    ) {
    }

    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
        self::$last = $this;
        self::$events[] = ['command', $this->number];
    }
}

#[Command('simple')]
final class SimpleCommand implements CommandInterface
{
    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
        UnitCommand::$events[] = ['simple'];
    }
}

#[Command('dependency')]
final class DependencyCommand implements CommandInterface
{
    public function __construct(public UnitService $service)
    {
    }

    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
    }
}

#[Command('bad-order')]
final class BadOrderCommand implements CommandInterface
{
    public function __construct(#[Option] bool $option, #[Argument] string $argument)
    {
    }

    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
    }
}

#[Command('bad-scalar')]
final class BadScalarCommand implements CommandInterface
{
    public function __construct(string $unannotated)
    {
    }

    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
    }
}

final class RequiredOptions implements MiddlewareInterface
{
    #[Option]
    public bool $required;

    #[Option]
    public ?string $nullable;

    #[Option]
    public string $default = 'property';

    #[Option(default: 'attribute')]
    public string $overridden = 'property';

    public string $ignored = 'ignored';

    public function handle(CommandInterface $command, TerminalInterface $terminal, Printer $printer, Closure $next): void
    {
    }
}

function unitPrinter(): array
{
    $buffer = new Buffer();
    $output = new Output();
    $output->add('out', $buffer)->add('error', $buffer);
    $output->defaultTo('out');
    $printer = new Printer();
    $printer->setOutput($output);
    $printer->forceAnsiOff();
    new \ReflectionProperty(CLImate::class, 'router')->getValue($printer)->addExtension('confirm', Confirmation::class);

    return [$printer, $buffer];
}

function withUnitArgs(array $args, callable $run): void
{
    $previous = $GLOBALS['argv'];
    $GLOBALS['argv'] = ['unit.php', ...$args];

    try {
        $run();
    } finally {
        $GLOBALS['argv'] = $previous;
    }
}

#[Command('explicit-exit')]
final class ExplicitExitCommand implements CommandInterface
{
    public function __construct(#[Argument] public int $status)
    {
    }

    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
        $terminal->exit($this->status);
    }
}

#[Command('nested-run')]
final class NestedRunCommand implements CommandInterface
{
    public static ?int $innerStatus = null;

    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
        self::$innerStatus = $terminal->run(['tool', 'explicit-exit', '7']);
        $terminal->exit(self::$innerStatus + 1);
    }
}
