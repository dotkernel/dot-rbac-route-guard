<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Dot\Rbac\Route\Guard\Provider\GuardsProviderInterface;

class GuardsProvider implements GuardsProviderInterface
{
    /**
     * @param array<mixed> $guards
     */
    public function __construct(private readonly array $guards = [])
    {
    }

    /**
     * @return array<mixed>
     */
    public function getGuards(): array
    {
        return $this->guards;
    }
}
