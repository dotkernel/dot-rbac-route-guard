<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Guard\GuardInterface;

interface GuardsProviderInterface
{
    /**
     * @return array<GuardInterface>
     */
    public function getGuards(): array;
}
