<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Guard\Factory;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class FactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testCreateRuntimeException(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $subject   = new Factory($container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard type was not provided');
        $subject->create([]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testCreate(): void
    {
        $container           = $this->createMock(ContainerInterface::class);
        $guardsPluginManager = $this->createMock(GuardPluginManager::class);

        $guardsPluginManager->expects($this->once())
            ->method('build')
            ->with('testType', null)
            ->willReturn(new class implements GuardInterface {
                public function isGranted(ServerRequestInterface $request): bool
                {
                    return true;
                }

                public function getPriority(): int
                {
                    return 1;
                }
            });
        $subject = new Factory($container, $guardsPluginManager);

        $result = $subject->create(['type' => 'testType']);
        $this->assertContainsOnlyInstancesOf(GuardInterface::class, [$result]);
    }

    /**
     * @throws Exception
     */
    public function testGetGuardPluginManager(): void
    {
        $container           = $this->createMock(ContainerInterface::class);
        $guardsPluginManager = $this->createMock(GuardPluginManager::class);

        $subject = new Factory($container, $guardsPluginManager);

        $result = $subject->getGuardPluginManager();
        $this->assertContainsOnlyInstancesOf(GuardPluginManager::class, [$result]);
    }
}
