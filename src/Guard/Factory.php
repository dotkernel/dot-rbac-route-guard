<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

use function is_array;
use function is_string;

class Factory
{
    public function __construct(
        protected ContainerInterface $container,
        protected ?GuardPluginManager $guardPluginManager = null
    ) {
    }

    /**
     * @param array<array-key, mixed> $specs
     */
    public function create(array $specs): GuardInterface
    {
        $type = $specs['type'] ?? '';
        if (! is_string($type) || $type === '') {
            throw new RuntimeException('Guard type was not provided');
        }

        $options = $specs['options'] ?? null;

        return $this->getGuardPluginManager()->build($type, is_array($options) ? $options : null);
    }

    public function getGuardPluginManager(): GuardPluginManager
    {
        if ($this->guardPluginManager === null) {
            $this->guardPluginManager = new GuardPluginManager($this->container);
        }

        return $this->guardPluginManager;
    }
}
