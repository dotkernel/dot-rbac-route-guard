<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Provider\ArrayGuardsProvider;
use Dot\Rbac\Route\Guard\Provider\Factory;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;

class GuardsProviderFactoryTest extends TestCase
{
    public function testCreateRequiresType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard provider type was not specified');
        (new Factory(new InMemoryContainer()))->create([]);
    }

    public function testCreateBuildsProvider(): void
    {
        $provider = (new Factory(new InMemoryContainer()))->create([
            'type'    => 'ArrayGuards',
            'options' => ['guards' => [['type' => 'Route']]],
        ]);

        $this->assertInstanceOf(ArrayGuardsProvider::class, $provider);
        $this->assertSame([['type' => 'Route']], $provider->getGuardsConfig());
    }

    public function testCreateWithUnknownTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Factory(new InMemoryContainer()))->create(['type' => 'Unknown']);
    }

    public function testPluginManagerAccessor(): void
    {
        $container     = new InMemoryContainer();
        $pluginManager = new GuardsProviderPluginManager($container);

        $this->assertSame($pluginManager, (new Factory($container, $pluginManager))->getGuardsProviderPluginManager());

        $subject = new Factory($container);
        $this->assertSame($subject->getGuardsProviderPluginManager(), $subject->getGuardsProviderPluginManager());
    }
}
