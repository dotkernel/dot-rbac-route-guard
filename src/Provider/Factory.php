<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

use function is_array;
use function is_string;

class Factory
{
    public function __construct(
        protected ContainerInterface $container,
        protected ?GuardsProviderPluginManager $guardsProviderPluginManager = null
    ) {
    }

    /**
     * @param array<array-key, mixed> $specs
     */
    public function create(array $specs): GuardsProviderInterface
    {
        $type = $specs['type'] ?? '';
        if (! is_string($type) || $type === '') {
            throw new RuntimeException('Guard provider type was not specified');
        }

        $options = $specs['options'] ?? null;

        return $this->getGuardsProviderPluginManager()->build($type, is_array($options) ? $options : null);
    }

    public function getGuardsProviderPluginManager(): GuardsProviderPluginManager
    {
        if ($this->guardsProviderPluginManager === null) {
            $this->guardsProviderPluginManager = new GuardsProviderPluginManager($this->container);
        }

        return $this->guardsProviderPluginManager;
    }
}
