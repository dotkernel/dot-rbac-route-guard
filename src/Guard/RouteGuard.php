<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Guard;

use Dot\Rbac\Role\RoleServiceInterface;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Psr\Http\Message\ServerRequestInterface;

use function in_array;

class RouteGuard extends AbstractGuard
{
    public const PRIORITY = 100;

    protected RoleServiceInterface $roleService;

    /**
     * @param array<array-key, mixed>|null $options
     */
    public function __construct(?array $options = null)
    {
        $options = $options ?? [];
        parent::__construct($options);

        if (! isset($options['role_service']) || ! $options['role_service'] instanceof RoleServiceInterface) {
            throw new RuntimeException('RoleService is required by this guard and was not set');
        }

        $this->setRoleService($options['role_service']);
    }

    public function getRoleService(): RoleServiceInterface
    {
        return $this->roleService;
    }

    public function setRoleService(RoleServiceInterface $roleService): void
    {
        $this->roleService = $roleService;
    }

    public function isGranted(ServerRequestInterface $request): bool
    {
        $allowedRoles = $this->getMatchedRule($request);
        if ($allowedRoles === null) {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        if (in_array('*', $allowedRoles, true)) {
            return true;
        }

        if ($allowedRoles === []) {
            return false;
        }

        return $this->roleService->matchIdentityRoles($allowedRoles);
    }
}
