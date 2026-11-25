<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\RouteGuard;
use DotTest\Rbac\Route\Guard\Fixture\Request;
use DotTest\Rbac\Route\Guard\Fixture\RoleService;
use PHPUnit\Framework\TestCase;

class RouteGuardTest extends TestCase
{
    /**
     * @param array<array-key, mixed> $rules
     */
    private function guard(array $rules = [], string $policy = GuardInterface::POLICY_DENY): RouteGuard
    {
        return new RouteGuard([
            'role_service'      => new RoleService(['user']),
            'protection_policy' => $policy,
            'rules'             => $rules,
        ]);
    }

    public function testConstructorRequiresRoleService(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('RoleService is required by this guard and was not set');
        new RouteGuard();
    }

    public function testConstructorRejectsInvalidProtectionPolicy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Protection policy must be "allow" or "deny", "ALLOW" given');
        new RouteGuard(['role_service' => new RoleService(), 'protection_policy' => 'ALLOW']);
    }

    public function testDefaultsAndAccessors(): void
    {
        $roleService = new RoleService();
        $subject     = new RouteGuard(['role_service' => $roleService]);

        $this->assertSame(GuardInterface::POLICY_DENY, $subject->getProtectionPolicy());
        $this->assertSame($roleService, $subject->getRoleService());
        $this->assertSame(RouteGuard::PRIORITY, $subject->getPriority());

        $other = new RoleService();
        $subject->setRoleService($other);
        $this->assertSame($other, $subject->getRoleService());
    }

    public function testSetRulesNormalizesRouteNames(): void
    {
        $subject = $this->guard(['Home' => 'user', 'Login', 'Account' => ['admin', 'user'], 10 => ['ignored']]);

        $this->assertSame(
            ['home' => ['user'], 'login' => [], 'account' => ['admin', 'user']],
            $subject->getRules()
        );
    }

    public function testUnmatchedRequestUsesProtectionPolicy(): void
    {
        $this->assertFalse($this->guard()->isGranted(Request::withoutRoute()));
        $this->assertFalse($this->guard()->isGranted(Request::forFailedRoute()));
        $this->assertTrue($this->guard([], GuardInterface::POLICY_ALLOW)->isGranted(Request::withoutRoute()));
    }

    public function testRouteWithoutRuleUsesProtectionPolicy(): void
    {
        $this->assertFalse($this->guard(['home' => ['user']])->isGranted(Request::forRoute('other')));
        $this->assertTrue(
            $this->guard(['home' => ['user']], GuardInterface::POLICY_ALLOW)->isGranted(Request::forRoute('other'))
        );
    }

    public function testWildcardGrantsEveryone(): void
    {
        $this->assertTrue($this->guard(['home' => ['*']])->isGranted(Request::forRoute('home')));
    }

    public function testMatchesRolesCaseInsensitiveRouteName(): void
    {
        $this->assertTrue($this->guard(['Account' => ['user']])->isGranted(Request::forRoute('account')));
        $this->assertFalse($this->guard(['account' => ['admin']])->isGranted(Request::forRoute('ACCOUNT')));
    }

    public function testRouteListedWithoutRolesDeniesAccess(): void
    {
        $this->assertFalse($this->guard(['home'], GuardInterface::POLICY_ALLOW)->isGranted(Request::forRoute('home')));
    }
}
