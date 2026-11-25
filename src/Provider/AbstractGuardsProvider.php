<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\Factory;

abstract class AbstractGuardsProvider implements GuardsProviderInterface
{
    protected Factory $guardFactory;

    /**
     * @param array<array-key, mixed> $options
     */
    public function __construct(array $options = [])
    {
        if (! isset($options['guard_factory']) || ! $options['guard_factory'] instanceof Factory) {
            throw new RuntimeException('Guard factory is required and was not set');
        }

        $this->setGuardFactory($options['guard_factory']);
    }

    public function getGuardFactory(): Factory
    {
        return $this->guardFactory;
    }

    public function setGuardFactory(Factory $guardFactory): void
    {
        $this->guardFactory = $guardFactory;
    }
}
