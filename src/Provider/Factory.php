<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class Factory
{
    protected ContainerInterface $container;

    protected ?GuardsProviderPluginManager $guardsProviderPluginManager;

    public function __construct(
        ContainerInterface $container,
        ?GuardsProviderPluginManager $guardsProviderPluginManager = null
    ) {
        $this->container                   = $container;
        $this->guardsProviderPluginManager = $guardsProviderPluginManager;
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function create(array $specs): GuardsProviderInterface
    {
        $type = $specs['type'] ?? '';
        if (empty($type)) {
            throw new RuntimeException('Guard provider type was not specified');
        }

        return $this->getGuardsProviderPluginManager()->build($type, $specs['options'] ?? null);
    }

    public function getGuardsProviderPluginManager(): GuardsProviderPluginManager
    {
        if (! $this->guardsProviderPluginManager) {
            $this->guardsProviderPluginManager = new GuardsProviderPluginManager($this->container, []);
        }

        return $this->guardsProviderPluginManager;
    }
}
