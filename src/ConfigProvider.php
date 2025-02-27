<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard;

use Dot\Rbac\Route\Guard\Factory\ForbiddenHandlerFactory;
use Dot\Rbac\Route\Guard\Factory\GuardPluginManagerFactory;
use Dot\Rbac\Route\Guard\Factory\GuardsProviderPluginManagerFactory;
use Dot\Rbac\Route\Guard\Factory\RbacGuardMiddlewareFactory;
use Dot\Rbac\Route\Guard\Factory\RbacGuardOptionsFactory;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Dot\Rbac\Route\Guard\Middleware\ForbiddenHandler;
use Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'      => [
                'factories' => [
                    GuardPluginManager::class          => GuardPluginManagerFactory::class,
                    GuardsProviderPluginManager::class => GuardsProviderPluginManagerFactory::class,
                    RbacGuardOptions::class            => RbacGuardOptionsFactory::class,
                    RbacGuardMiddleware::class         => RbacGuardMiddlewareFactory::class,
                    ForbiddenHandler::class            => ForbiddenHandlerFactory::class,
                ],
            ],
            'dot_authorization' => [
                'protection_policy'       => GuardInterface::POLICY_ALLOW,
                'guards_provider_manager' => [],
                'guard_manager'           => [],
                'guards_provider'         => [],
                'messages_options'        => [],
            ],
        ];
    }
}
