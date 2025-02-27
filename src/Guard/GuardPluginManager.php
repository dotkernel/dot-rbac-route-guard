<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Factory\GuardFactory;
use Dot\Rbac\Route\Guard\Factory\PermissionGuardFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

use function gettype;
use function is_object;
use function sprintf;

/**
 * @template T
 * @extends AbstractPluginManager<T>
 */
class GuardPluginManager extends AbstractPluginManager
{
    protected string $instanceOf = GuardInterface::class;

    protected array $factories = [
        RouteGuard::class           => GuardFactory::class,
        RoutePermissionGuard::class => PermissionGuardFactory::class,
    ];

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

    public function validate(mixed $instance): void
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                static::class,
                $this->instanceOf,
                is_object($instance) ? $instance::class : gettype($instance)
            ));
        }
    }
}
