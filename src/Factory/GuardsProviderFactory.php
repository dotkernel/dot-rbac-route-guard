<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\Factory;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function class_exists;
use function is_string;
use function sprintf;

class GuardsProviderFactory
{
    /**
     * @param array<array-key, mixed>|null $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): object
    {
        $options = $options ?? [];

        $guardFactory = $options['guard_factory'] ?? null;
        if (is_string($guardFactory) && $container->has($guardFactory)) {
            $options['guard_factory'] = $container->get($guardFactory);
        } else {
            $guardPluginManager = $container->has(GuardPluginManager::class)
                ? $container->get(GuardPluginManager::class)
                : null;

            $options['guard_factory'] = new Factory(
                $container,
                $guardPluginManager instanceof GuardPluginManager ? $guardPluginManager : null
            );
        }

        if (! class_exists($requestedName)) {
            throw new RuntimeException(sprintf('Guards provider class "%s" does not exist', $requestedName));
        }

        return new $requestedName($options);
    }
}
