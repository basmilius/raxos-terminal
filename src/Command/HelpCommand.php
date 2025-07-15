<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use Composer\InstalledVersions;
use Raxos\Foundation\Collection\ArrayList;
use Raxos\Terminal\Attribute\{Argument, Command};
use Raxos\Terminal\Contract\{CommandInterface, TerminalInterface};
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
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        #[Argument(description: 'Show the detailed help for a specific command.')]
        public ?string $command = null
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

                if ($this->command === null) {
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
                    $printer->bold()->out($title);

                    if ($command->command->description !== null) {
                        $printer->out($command->command->description);
                    }

                    if ($command->command->usage !== null) {
                        $printer->darkGray('Example: ' . $argv[0] . ' ' . $command->command->usage);
                    }

                    if (!empty($command->arguments)) {
                        $printer->br();
                        $printer->tab()->bold('Arguments');

                        foreach ($command->arguments as $argument) {
                            $optional = !$argument->defaultValue->isEmpty || in_array('null', $argument->type, true);
                            $printer->tab()->darkGray(str_pad($argument->name, 16) . ' ' . $argument->argument->description . ($optional ? ' (optional)' : ''));
                        }
                    }

                    if (!empty($command->options)) {
                        $printer->br();
                        $printer->tab()->bold('Options');

                        foreach ($command->options as $option) {
                            $printer->tab()->darkGray(str_pad("--{$option->name}", 16) . ' ' . $option->option->description);
                        }
                    }
                }
            });
    }

}
