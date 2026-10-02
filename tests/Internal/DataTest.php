<?php
declare(strict_types=1);

use Raxos\Contract\Container\ContainerInterface;
use Raxos\Terminal\Error\{InvalidCommandException, MissingArgumentException, MissingOptionException, ReflectionErrorException};
use Raxos\Terminal\Internal\Data;
use RaxosTests\Terminal\{BadOrderCommand, BadScalarCommand, DependencyCommand, RequiredOptions, SimpleCommand, UnitCommand, UnitService};

covers(Data::class);

it('caches parsed command metadata and resolves aliases, defaults and middleware order', function (): void {
    $data = Data::parseCommand(UnitCommand::class);
    expect(Data::parseCommand(UnitCommand::class))->toBe($data)->and($data->command->name)->toBe('unit')
        ->and($data->arguments[0]->realName)->toBe('number')->and($data->arguments[0]->name)->toBe('count')
        ->and($data->options[0]->realName)->toBe('active')->and($data->options[0]->name)->toBe('enabled')
        ->and(array_column($data->middlewares, 'name'))->toBe(['outer', 'inner']);
    $instance = $data->instantiate(['12'], ['enabled' => 'true', 'weight' => '2.25']);
    expect($instance->number)->toBe(12)->and($instance->name)->toBeNull()->and($instance->active)->toBeTrue()->and($instance->weight)->toBe(2.25);
    expect($data->instantiate(['0'], [])->active)->toBeFalse();
});

it('rejects missing arguments before constructing a command', function (): void {
    expect(fn () => Data::parseCommand(UnitCommand::class)->instantiate([], []))->toThrow(MissingArgumentException::class, 'count');
});

it('requires a container only for commands with dependencies', function (): void {
    $data = Data::parseCommand(DependencyCommand::class);
    expect(fn () => $data->instantiate([], []))->toThrow(InvalidCommandException::class, 'container');
    $service = new UnitService();
    $container = test()->createMock(ContainerInterface::class);
    $container->expects(test()->once())->method('get')->with(UnitService::class)->willReturn($service);
    expect($data->instantiate([], [], $container)->service)->toBe($service);
    expect(Data::parseCommand(SimpleCommand::class)->instantiate([], []))->toBeInstanceOf(SimpleCommand::class);
});

it('rejects invalid command metadata and missing classes', function (string $class, string $error): void {
    expect(fn () => Data::parseCommand($class))->toThrow($error);
})->with([[BadOrderCommand::class, InvalidCommandException::class], [BadScalarCommand::class, InvalidCommandException::class], [RequiredOptions::class, InvalidCommandException::class], ['MissingUnitCommand', ReflectionErrorException::class]]);

it('injects options with attribute defaults taking precedence over property defaults', function (): void {
    $data = Data::parseMiddleware(RequiredOptions::class);
    expect(Data::parseMiddleware(RequiredOptions::class))->toBe($data)->and(count($data->options))->toBe(4);
    expect(fn () => $data->inject(new RequiredOptions()))->toThrow(MissingOptionException::class, 'required');
    $middleware = new RequiredOptions();
    $data->inject($middleware, options: ['required' => 'false']);
    expect($middleware->required)->toBeFalse()->and($middleware->nullable)->toBeNull()
        ->and($middleware->default)->toBe('property')->and($middleware->overridden)->toBe('attribute')->and($middleware->ignored)->toBe('ignored');
    $data->inject($middleware, options: ['required' => true, 'default' => 'explicit']);
    expect($middleware->required)->toBeTrue()->and($middleware->default)->toBe('explicit');
});

it('wraps missing middleware classes with their reflection cause', function (): void {
    expect(fn () => Data::parseMiddleware('MissingUnitMiddleware'))->toThrow(ReflectionErrorException::class);
});
