<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Factory\GuardsProviderPluginManagerFactory;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GuardsProviderPluginManagerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCanCreateService(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config    = [
            'dot_authorization' => [
                'guards_provider_manager' => [],
            ],
        ];

        $container->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $service = (new GuardsProviderPluginManagerFactory())($container);
        $this->assertSame(GuardsProviderPluginManager::class, $service::class);
    }
}
