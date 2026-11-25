<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Factory\GuardFactory;
use Dot\Rbac\Route\Guard\Factory\PermissionGuardFactory;
use Dot\Rbac\Route\Guard\Plugin\AbstractPluginManager;

/**
 * @extends AbstractPluginManager<GuardInterface>
 */
class GuardPluginManager extends AbstractPluginManager
{
    /** @var array<string, string|callable> */
    protected array $factories = [
        RouteGuard::class           => GuardFactory::class,
        RoutePermissionGuard::class => PermissionGuardFactory::class,
    ];

    /** @var array<string, string> */
    protected array $aliases = [
        'routeguard'           => RouteGuard::class,
        'routeGuard'           => RouteGuard::class,
        'RouteGuard'           => RouteGuard::class,
        'route'                => RouteGuard::class,
        'Route'                => RouteGuard::class,
        'routepermissionguard' => RoutePermissionGuard::class,
        'routePermissionGuard' => RoutePermissionGuard::class,
        'RoutePermissionGuard' => RoutePermissionGuard::class,
        'routepermission'      => RoutePermissionGuard::class,
        'routePermission'      => RoutePermissionGuard::class,
        'RoutePermission'      => RoutePermissionGuard::class,
    ];

    protected function getInstanceOf(): string
    {
        return GuardInterface::class;
    }
}
