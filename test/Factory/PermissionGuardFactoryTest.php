<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Authorization\AuthorizationInterface;
use Dot\Rbac\Route\Guard\Factory\PermissionGuardFactory;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\RoutePermissionGuard;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use DotTest\Rbac\Route\Guard\Fixture\AuthorizationService;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;

class PermissionGuardFactoryTest extends TestCase
{
    public function testUsesDefaultAuthorizationService(): void
    {
        $authorization = new AuthorizationService();
        $container     = new InMemoryContainer([
            AuthorizationInterface::class => $authorization,
            RbacGuardOptions::class       => new RbacGuardOptions(),
        ]);

        $guard = (new PermissionGuardFactory())($container, RoutePermissionGuard::class);

        $this->assertInstanceOf(RoutePermissionGuard::class, $guard);
        $this->assertSame($authorization, $guard->getAuthorizationService());
        $this->assertSame(GuardInterface::POLICY_ALLOW, $guard->getProtectionPolicy());
        $this->assertSame([], $guard->getRules());
    }

    public function testUsesNamedAuthorizationService(): void
    {
        $custom    = new AuthorizationService(['read']);
        $container = new InMemoryContainer([
            AuthorizationInterface::class => new AuthorizationService(),
            'custom.authorization'        => $custom,
            RbacGuardOptions::class       => new RbacGuardOptions(),
        ]);

        $guard = (new PermissionGuardFactory())($container, RoutePermissionGuard::class, [
            'authorization_service' => 'custom.authorization',
            'protection_policy'     => GuardInterface::POLICY_DENY,
            'rules'                 => 'not an array',
        ]);

        $this->assertInstanceOf(RoutePermissionGuard::class, $guard);
        $this->assertSame($custom, $guard->getAuthorizationService());
        $this->assertSame(GuardInterface::POLICY_DENY, $guard->getProtectionPolicy());
        $this->assertSame([], $guard->getRules());
    }
}
