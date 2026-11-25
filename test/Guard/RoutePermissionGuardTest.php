<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\RoutePermissionGuard;
use DotTest\Rbac\Route\Guard\Fixture\AuthorizationService;
use DotTest\Rbac\Route\Guard\Fixture\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RoutePermissionGuardTest extends TestCase
{
    private AuthorizationService $authorization;

    protected function setUp(): void
    {
        $this->authorization = new AuthorizationService(['read', 'write']);
    }

    /**
     * @param array<array-key, mixed> $rules
     */
    private function guard(array $rules, string $policy = GuardInterface::POLICY_DENY): RoutePermissionGuard
    {
        return new RoutePermissionGuard([
            'authorization_service' => $this->authorization,
            'protection_policy'     => $policy,
            'rules'                 => $rules,
        ]);
    }

    public function testConstructorRequiresAuthorizationService(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Authorization service is required by this guard and was not set');
        new RoutePermissionGuard(['authorization_service' => 'not a service']);
    }

    public function testAccessors(): void
    {
        $subject = $this->guard([]);
        $this->assertSame($this->authorization, $subject->getAuthorizationService());
        $this->assertSame(RoutePermissionGuard::PRIORITY, $subject->getPriority());

        $other = new AuthorizationService();
        $subject->setAuthorizationService($other);
        $this->assertSame($other, $subject->getAuthorizationService());
    }

    public function testUnmatchedRequestUsesProtectionPolicy(): void
    {
        $this->assertFalse($this->guard([])->isGranted(Request::withoutRoute()));
        $this->assertTrue($this->guard([], GuardInterface::POLICY_ALLOW)->isGranted(Request::forRoute('other')));
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>, bool}>
     */
    public static function ruleProvider(): iterable
    {
        yield 'flat, all granted'               => [['read', 'write'], true];
        yield 'flat, one missing'               => [['read', 'delete'], false];
        yield 'flat wildcard'                   => [['*'], true];
        yield 'structured AND, all granted'     => [['permissions' => ['read', 'write']], true];
        yield 'structured AND, one missing'     => [['permissions' => ['read', 'delete'], 'condition' => 'AND'], false];
        yield 'structured OR, one granted'      => [['permissions' => ['delete', 'read'], 'condition' => 'OR'], true];
        yield 'structured OR, none granted'     => [['permissions' => ['delete', 'admin'], 'condition' => 'OR'], false];
        yield 'structured lower-case condition' => [['permissions' => ['delete', 'read'], 'condition' => 'or'], true];
        yield 'structured wildcard'             => [['permissions' => ['*'], 'condition' => 'AND'], true];
        yield 'structured empty permissions'    => [['permissions' => [], 'condition' => 'OR'], false];
        yield 'empty list'                      => [[], false];
    }

    /**
     * @param array<array-key, mixed> $rule
     */
    #[DataProvider('ruleProvider')]
    public function testIsGranted(array $rule, bool $expected): void
    {
        $this->assertSame($expected, $this->guard(['page' => $rule])->isGranted(Request::forRoute('page')));
    }

    public function testRouteListedWithoutPermissionsDeniesAccess(): void
    {
        $subject = $this->guard(['page'], GuardInterface::POLICY_ALLOW);
        $this->assertFalse($subject->isGranted(Request::forRoute('page')));
    }

    public function testAndConditionStopsAtFirstDeniedPermission(): void
    {
        $this->guard(['page' => ['delete', 'read']])->isGranted(Request::forRoute('page'));
        $this->assertSame(['delete'], $this->authorization->checked);
    }

    public function testInvalidConditionShowsTheValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition must be either "AND" or "OR", "XOR" given');
        $this->guard(['page' => ['permissions' => ['read'], 'condition' => 'XOR']])
            ->isGranted(Request::forRoute('page'));
    }

    public function testInvalidConditionTypeShowsTheType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition must be either "AND" or "OR", "array" given');
        $this->guard(['page' => ['permissions' => ['read'], 'condition' => []]])->isGranted(Request::forRoute('page'));
    }
}
