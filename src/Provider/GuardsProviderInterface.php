<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Psr\Container\ContainerExceptionInterface;

interface GuardsProviderInterface
{
    /**
     * @return GuardInterface[]
     * @throws ContainerExceptionInterface
     */
    public function getGuards(): array;
}
