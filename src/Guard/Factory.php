<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class Factory
{
    protected ContainerInterface $container;

    protected ?GuardPluginManager $guardPluginManager;

    public function __construct(ContainerInterface $container, ?GuardPluginManager $guardPluginManager = null)
    {
        $this->container          = $container;
        $this->guardPluginManager = $guardPluginManager;
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function create(array $specs): GuardInterface
    {
        $type = $specs['type'] ?? '';
        if (empty($type)) {
            throw new RuntimeException('Guard type was not provided');
        }

        $guardPluginManager = $this->getGuardPluginManager();
        return $guardPluginManager->build($type, $specs['options'] ?? null);
    }

    public function getGuardPluginManager(): GuardPluginManager
    {
        if (! $this->guardPluginManager) {
            $this->guardPluginManager = new GuardPluginManager($this->container, []);
        }

        return $this->guardPluginManager;
    }
}
