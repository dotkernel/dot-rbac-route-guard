<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Factory\GuardFactory;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\RouteGuard;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GuardFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWillNotCreateWithoutRoleService(): void
    {
        $options       = [
            'role_service'      => 'noRoleService',
            'protection_policy' => GuardInterface::POLICY_ALLOW,
            'rules'             => [],
        ];
        $requestedName = RouteGuard::class;
        $container     = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('has')
            ->with($options['role_service'])
            ->willReturn(true);
        $container->method('get')->willReturnMap([
            [$options['role_service'], []],
            [RbacGuardOptions::class, new RbacGuardOptions(null)],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('RoleService is required by this guard and was not set');
        (new GuardFactory())($container, $requestedName, $options);
    }
}
