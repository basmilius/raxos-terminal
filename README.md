<a href="https://bas.dev">
    <img src="https://bmcdn.nl/assets/branding/logo.svg" alt="Bas Milius" height="48" />
</a>

---

# Raxos Terminal

Build command-line applications with command attributes, typed input and console output.

[Documentation](https://raxos.dev/terminal/) | [Packagist](https://packagist.org/packages/raxos/terminal) | [Raxos](https://github.com/basmilius/raxos)

- Named commands with positional arguments and options.
- Generated help, middleware and optional container injection.
- A printer for messages, tables and interactive input.
- Argument parsing that preserves shell token boundaries and the `--` separator.

## Installation

Requires PHP 8.5 or later. Enable the `mbstring` PHP extension. Composer checks the remaining package and extension dependencies declared in [composer.json](composer.json).

```sh
composer require "raxos/terminal:^3.2"
```

## Usage

```php
<?php
declare(strict_types=1);

use Raxos\Contract\Terminal\CommandInterface;
use Raxos\Contract\Terminal\TerminalInterface;
use Raxos\Terminal\Attribute\Argument;
use Raxos\Terminal\Attribute\Command;
use Raxos\Terminal\Printer;
use Raxos\Terminal\Terminal;

require __DIR__ . '/vendor/autoload.php';

#[Command(name: 'greet', description: 'Print a greeting.')]
final readonly class GreetCommand implements CommandInterface
{
    public function __construct(#[Argument] public string $name = 'World') {}

    public function execute(TerminalInterface $terminal, Printer $printer): void
    {
        $printer->out("Hello {$this->name}");
    }
}

$terminal = new Terminal();
$terminal->register(GreetCommand::class);
$terminal->execute();
```

Save the example as `console.php` in your application root and run `php console.php greet Ada`. Run `php console.php help` to list commands. Declare positional arguments before options. Class or interface constructor dependencies require a container passed to the terminal.

## Documentation

- [Commands](https://raxos.dev/terminal/commands)
- [Middleware](https://raxos.dev/terminal/middleware)
- [The Printer](https://raxos.dev/terminal/printer)
- [Errors and reporting](https://raxos.dev/terminal/errors)

## Testing

Run this library's Pest suite from the Raxos workspace:

```sh
git clone --recurse-submodules https://github.com/basmilius/raxos.git
cd raxos
composer install
vendor/bin/pest --testsuite=terminal
```

See [Testing Raxos](https://github.com/basmilius/raxos/blob/main/TESTING.md) for PHP extensions, integration services and coverage commands. The library's [Tests workflow](.github/workflows/tests.yml) also runs in GitHub Actions.

## License

[MIT](LICENSE). Copyright (c) 2017 - present Bas Milius.
