<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Provider\Factory;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderInterface;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class FactoryTest extends TestCase
{
    protected ContainerInterface|MockObject $container;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testCreateRuntimeException(): void
    {
        $subject = new Factory($this->container);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard provider type was not specified');
        $subject->create([]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testCreate(): void
    {
        $type                        = 'arrayGuardsProvider';
        $guardsProviderPluginManager = $this->createMock(GuardsProviderPluginManager::class);
        $guardsProviderPluginManager->expects($this->once())
            ->method('build')
            ->with($type, null)
            ->willReturn(new class implements GuardsProviderInterface {
                public function getGuards(): array
                {
                    return [];
                }
            });
        $subject = new Factory($this->container, $guardsProviderPluginManager);

        $result = $subject->create(
            [
                'type' => $type,
            ]
        );

        $this->assertContainsOnlyInstancesOf(GuardsProviderInterface::class, [$result]);
    }

    public function testGetGuardsProviderPluginManager(): void
    {
        $guardsProviderPluginManager = new GuardsProviderPluginManager($this->container);
        $subject                     = new Factory($this->container, $guardsProviderPluginManager);

        $result = $subject->getGuardsProviderPluginManager();
        $this->assertSame(GuardsProviderPluginManager::class, $result::class);
    }
}
