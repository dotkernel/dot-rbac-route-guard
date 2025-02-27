<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Factory\GuardsProviderFactory;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Dot\Rbac\Route\Guard\Provider\ArrayGuardsProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GuardsProviderFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWillNotCreateWithoutGuardFactory(): void
    {
        $container     = $this->createMock(ContainerInterface::class);
        $options       = [
            'guard_factory' => 'stringTest',
        ];
        $requestedName = ArrayGuardsProvider::class;

        $container->expects($this->once())
            ->method('get')
            ->with($options['guard_factory'])
            ->willReturn(false);
        $container->expects($this->once())
            ->method('has')
            ->with($options['guard_factory'])
            ->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard factory is required and was not set');
        (new GuardsProviderFactory())($container, $requestedName, $options);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testCanCreateService(): void
    {
        $container     = $this->createMock(ContainerInterface::class);
        $requestedName = ArrayGuardsProvider::class;

        $container->expects($this->once())
            ->method('get')
            ->with(GuardPluginManager::class)
            ->willReturn(new GuardPluginManager($container));

        $service = (new GuardsProviderFactory())($container, $requestedName);
        $this->assertInstanceOf(ArrayGuardsProvider::class, $service);
    }
}
