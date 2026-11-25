<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Factory\GuardsProviderFactory;
use Dot\Rbac\Route\Guard\Guard\Factory;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Dot\Rbac\Route\Guard\Provider\ArrayGuardsProvider;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;

class GuardsProviderFactoryTest extends TestCase
{
    public function testUsesGuardPluginManagerFromContainer(): void
    {
        $container     = new InMemoryContainer();
        $pluginManager = new GuardPluginManager($container);
        $container->set(GuardPluginManager::class, $pluginManager);

        $provider = (new GuardsProviderFactory())($container, ArrayGuardsProvider::class, ['guards' => []]);

        $this->assertInstanceOf(ArrayGuardsProvider::class, $provider);
        $this->assertSame($pluginManager, $provider->getGuardFactory()->getGuardPluginManager());
    }

    public function testWorksWithoutGuardPluginManagerInContainer(): void
    {
        $provider = (new GuardsProviderFactory())(new InMemoryContainer(), ArrayGuardsProvider::class);

        $this->assertInstanceOf(ArrayGuardsProvider::class, $provider);
    }

    public function testUsesNamedGuardFactory(): void
    {
        $container    = new InMemoryContainer();
        $guardFactory = new Factory($container);
        $container->set('custom.factory', $guardFactory);

        $provider = (new GuardsProviderFactory())($container, ArrayGuardsProvider::class, [
            'guard_factory' => 'custom.factory',
        ]);

        $this->assertInstanceOf(ArrayGuardsProvider::class, $provider);
        $this->assertSame($guardFactory, $provider->getGuardFactory());
    }

    public function testNamedServiceThatIsNotAGuardFactoryThrows(): void
    {
        $container = new InMemoryContainer(['stringTest' => false]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard factory is required and was not set');
        (new GuardsProviderFactory())($container, ArrayGuardsProvider::class, ['guard_factory' => 'stringTest']);
    }

    public function testUnknownClassThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guards provider class "Unknown\Provider" does not exist');
        (new GuardsProviderFactory())(new InMemoryContainer(), 'Unknown\Provider');
    }
}
