<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Rbac\Role\RoleServiceInterface;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Factory\GuardFactory;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\RouteGuard;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use DotTest\Rbac\Route\Guard\Fixture\RoleService;
use PHPUnit\Framework\TestCase;

class GuardFactoryTest extends TestCase
{
    private InMemoryContainer $container;
    private RoleService $roleService;

    protected function setUp(): void
    {
        $this->roleService = new RoleService();
        $this->container   = new InMemoryContainer([
            RoleServiceInterface::class => $this->roleService,
            RbacGuardOptions::class     => new RbacGuardOptions(['protection_policy' => GuardInterface::POLICY_DENY]),
        ]);
    }

    public function testUsesDefaultRoleServiceAndModuleProtectionPolicy(): void
    {
        $guard = (new GuardFactory())($this->container, RouteGuard::class, ['rules' => ['home' => ['*']]]);

        $this->assertInstanceOf(RouteGuard::class, $guard);
        $this->assertSame($this->roleService, $guard->getRoleService());
        $this->assertSame(GuardInterface::POLICY_DENY, $guard->getProtectionPolicy());
        $this->assertSame(['home' => ['*']], $guard->getRules());
    }

    public function testGuardProtectionPolicyOverridesModulePolicy(): void
    {
        $guard = (new GuardFactory())($this->container, RouteGuard::class, [
            'protection_policy' => GuardInterface::POLICY_ALLOW,
        ]);

        $this->assertInstanceOf(RouteGuard::class, $guard);
        $this->assertSame(GuardInterface::POLICY_ALLOW, $guard->getProtectionPolicy());
    }

    public function testUsesNamedRoleServiceFromContainer(): void
    {
        $custom = new RoleService(['admin']);
        $this->container->set('custom.roles', $custom);

        $guard = (new GuardFactory())($this->container, RouteGuard::class, ['role_service' => 'custom.roles']);

        $this->assertInstanceOf(RouteGuard::class, $guard);
        $this->assertSame($custom, $guard->getRoleService());
    }

    public function testNamedServiceThatIsNotARoleServiceThrows(): void
    {
        $this->container->set('noRoleService', []);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('RoleService is required by this guard and was not set');
        (new GuardFactory())($this->container, RouteGuard::class, ['role_service' => 'noRoleService']);
    }

    public function testMissingModuleOptionsThrows(): void
    {
        $container = new InMemoryContainer([
            RoleServiceInterface::class => $this->roleService,
            RbacGuardOptions::class     => 'not options',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service ' . RbacGuardOptions::class . ' is not configured');
        (new GuardFactory())($container, RouteGuard::class);
    }

    public function testUnknownClassThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard class "Unknown\Guard" does not exist');
        (new GuardFactory())($this->container, 'Unknown\Guard');
    }
}
