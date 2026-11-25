<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Dot\Rbac\Role\RoleServiceInterface;

use function array_intersect;
use function array_values;

/**
 * Matches the role names given to it. The resolveRoles() method keeps the fixture compatible with dot-rbac 5.
 */
class RoleService implements RoleServiceInterface
{
    /**
     * @param list<string> $identityRoles
     */
    public function __construct(private readonly array $identityRoles = [])
    {
    }

    public function getIdentity(): mixed
    {
        return null;
    }

    public function getGuestRole(): string
    {
        return 'guest';
    }

    /**
     * @return array<mixed>
     */
    public function getIdentityRoles(): array
    {
        return [];
    }

    /**
     * @param array<mixed> $roles
     */
    public function matchIdentityRoles(array $roles): bool
    {
        return array_intersect($roles, $this->identityRoles) !== [];
    }

    /**
     * @param array<mixed> $roles
     * @return array<mixed>
     */
    public function resolveRoles(array $roles): array
    {
        return array_values($roles);
    }
}
