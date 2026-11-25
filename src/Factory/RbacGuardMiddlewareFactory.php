<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Authentication\AuthenticationInterface;
use Dot\Authorization\AuthorizationInterface;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware;
use Dot\Rbac\Route\Guard\Provider\Factory;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function is_a;
use function sprintf;

class RbacGuardMiddlewareFactory
{
    use AttachAuthorizationEventListenersTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName = RbacGuardMiddleware::class
    ): RbacGuardMiddleware {
        if (! is_a($requestedName, RbacGuardMiddleware::class, true)) {
            throw new RuntimeException(sprintf('%s can only create %s', self::class, RbacGuardMiddleware::class));
        }

        $options = $this->getRbacGuardOptions($container);

        $pluginManager         = $container->has(GuardsProviderPluginManager::class)
            ? $container->get(GuardsProviderPluginManager::class)
            : null;
        $guardsProviderFactory = new Factory(
            $container,
            $pluginManager instanceof GuardsProviderPluginManager ? $pluginManager : null
        );
        $guardsProvider        = $guardsProviderFactory->create($options->getGuardsProvider());

        $authorizationService = $container->get(AuthorizationInterface::class);
        if (! $authorizationService instanceof AuthorizationInterface) {
            throw new RuntimeException(sprintf('Service %s is not configured', AuthorizationInterface::class));
        }

        $authentication = $container->has(AuthenticationInterface::class)
            ? $container->get(AuthenticationInterface::class)
            : null;

        $middleware = new $requestedName(
            $authorizationService,
            $guardsProvider,
            $options,
            $authentication instanceof AuthenticationInterface ? $authentication : null
        );

        $middleware->attach($middleware->getEventManager(), 1000);
        $this->attachListeners($container, $middleware->getEventManager());

        return $middleware;
    }
}
