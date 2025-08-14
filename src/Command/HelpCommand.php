<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use Composer\InstalledVersions;
use Raxos\Foundation\Collection\ArrayList;
use Raxos\Terminal\Attribute\{Argument, Command, Option};
use Raxos\Terminal\Contract\{CommandInterface, TerminalInterface};
use Raxos\Terminal\Internal\Data;
use Raxos\Terminal\Printer;
use function array_values;
use function implode;
use function in_array;
use function sprintf;
use function str_pad;
use function strip_tags;
use function strlen;

/**
 * Class HelpCommand
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.0.1
 */
#[Command(
    name: 'help',
    description: 'Shows this help message.'
)]
final readonly class HelpCommand implements CommandInterface
{

    /**
     * HelpCommand constructor.
     *
     * @param string|null $command
     * @param bool $extended
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        #[Argument(description: 'Show the detailed help for a specific command.')]
        public ?string $command = null,

        #[Option(description: 'Always show the extended information.')]
        public bool $extended = false
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function execute(TerminalInterface $terminal, Printer $printer, bool $intro = true): void
    {
        global $argv;

        if ($intro) {
            $package = InstalledVersions::getRootPackage();

            $printer->cyan(sprintf('Terminal for %s version %s...', $package['name'], $package['pretty_version']));
            $printer->out('Based on Raxos Terminal.');
        }

        ArrayList::of(array_values($terminal->commands))
            ->map(static fn(string $command) => Data::parseCommand($command))
            ->sort(static fn(Data $a, Data $b) => $a->command->name <=> $b->command->name)
            ->filter(fn(Data $command) => $this->command === null || $command->command->name === $this->command)
            ->each(function (Data $command, int $index) use ($argv, $printer) {
                $title = ["<cyan>{$command->command->name}</cyan>"];

                foreach ($command->arguments as $argument) {
                    if (!$argument->defaultValue->isEmpty || in_array('null', $argument->type, true)) {
                        $title[] = "<cyan>({$argument->name})</cyan>";
                    } else {
                        $title[] = "<cyan>[{$argument->name}]</cyan>";
                    }
                }

                $title = implode(' ', $title);

                if ($this->command === null && !$this->extended) {
                    if ($index === 0) {
                        $printer->br();
                    }

                    $diff = strlen($title) - strlen(strip_tags($title));

                    $printer->columns([
                        str_pad($title, 36 + $diff),
                        $command->command->description
                    ], 2);
                } else {
                    $printer->br();
                    $printer->bold()->underline()->out($title);

                    if ($command->command->description !== null) {
                        $printer->out($command->command->description);
                    }

                    if ($command->command->usage !== null) {
                        $printer->darkGray('Example: ' . $argv[0] . ' ' . $command->command->usage);
                    }

                    if (!empty($command->arguments)) {
                        $printer->br();

                        foreach ($command->arguments as $index => $argument) {
                            $prefix = $index === 0 ? 'Arguments' : '';
                            $prefix = str_pad($prefix, 12);

                            $optional = !$argument->defaultValue->isEmpty || in_array('null', $argument->type, true);
                            $str = str_pad($argument->name, 16) . ' ' . $argument->argument->description . ($optional ? ' (optional)' : '');

                            $printer->out("<bold>{$prefix}</bold><dark_gray>{$str}</dark_gray>");
                        }
                    }

                    if (!empty($command->options) || !empty($command->middlewares)) {
                        $options = $command->options ?? [];

                        foreach ($command->middlewares as $middleware) {
                            $middlewareData = Data::parseMiddleware($middleware::class);

                            foreach ($middlewareData->options as $option) {
                                $options[] = $option;
                            }
                        }

                        if (!empty($options)) {
                            $printer->br();

                            foreach ($options as $index => $option) {
                                $prefix = $index === 0 ? 'Options' : '';
                                $prefix = str_pad($prefix, 12);

                                $str = str_pad("--{$option->name}", 16) . ' ' . $option->option->description;

                                $printer->out("<bold>{$prefix}</bold><dark_gray>{$str}</dark_gray>");
                            }
                        }
                    }

                    if (!empty($command->middlewares)) {
                        $printer->br();

                        foreach ($command->middlewares as $index => $middleware) {
                            $prefix = $index === 0 ? 'Middleware' : '';
                            $prefix = str_pad($prefix, 12);

                            $str = $middleware::class;

                            $printer->out("<bold>{$prefix}</bold><dark_gray>{$str}</dark_gray>");
                        }
                    }
                }
            });
    }

}
