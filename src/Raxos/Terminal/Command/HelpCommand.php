<?php
declare(strict_types=1);

namespace Raxos\Terminal\Command;

use Composer\InstalledVersions;
use function array_keys;
use function implode;
use function sprintf;
use function str_pad;

/**
 * Class HelpCommand
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Terminal\Command
 * @since 1.0.1
 */
final class HelpCommand extends AbstractCommand
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public static function spec(): CommandSpec
    {
        return CommandSpec::make('help')
            ->description('Displays the help screen.');
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function execute(array $arguments, array $options): void
    {
        global $argv;

        $package = InstalledVersions::getRootPackage();

        $this->printer->cyan(sprintf('Running terminal for %s version %s...', $package['name'], $package['pretty_version']));
        $this->printer->br();
        $this->printer->out('This terminal is based on raxos/terminal.');

        /** @var AbstractCommand $command */
        foreach ($this->terminal->getCommands() as $command) {
            $spec = $command::spec();
            $arguments = $spec->getArguments();
            $options = $spec->getOptions();
            $title = [$spec->getName()];

            foreach (array_keys($arguments) as $argumentKey) {
                $title[] = "<light_gray>[{$argumentKey}]</light_gray>";
            }

            $title = implode(' ', $title);

            $this->printer->br();
            $this->printer->out($title);
            $this->printer->tab()->darkGray($spec->getDescription());

            if ($spec->getExample() !== null) {
                $this->printer->tab()->darkGray('Example: ' . $argv[0] . ' ' . $spec->getExample());
            }

            foreach ($options as $option => [, $description]) {
                $this->printer->tab(2)->darkGray(str_pad("--{$option}", 16) . ' ' . $description);
            }
        }
    }

}
