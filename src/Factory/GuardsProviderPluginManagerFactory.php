<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GuardsProviderPluginManagerFactory
{
    use PluginManagerConfigTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GuardsProviderPluginManager
    {
        return new GuardsProviderPluginManager(
            $container,
            $this->getPluginManagerConfig($container, 'guards_provider_manager')
        );
    }
}
