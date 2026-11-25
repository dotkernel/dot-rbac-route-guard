<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GuardPluginManagerFactory
{
    use PluginManagerConfigTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GuardPluginManager
    {
        return new GuardPluginManager($container, $this->getPluginManagerConfig($container, 'guard_manager'));
    }
}
