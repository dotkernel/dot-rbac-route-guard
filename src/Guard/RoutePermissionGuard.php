<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Guard;

use Dot\Authorization\AuthorizationInterface;
use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Psr\Http\Message\ServerRequestInterface;

use function array_filter;
use function get_debug_type;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use function sprintf;
use function strtoupper;

class RoutePermissionGuard extends AbstractGuard
{
    public const PRIORITY = 70;

    protected AuthorizationInterface $authorizationService;

    /**
     * @param array<array-key, mixed>|null $options
     */
    public function __construct(?array $options = null)
    {
        $options = $options ?? [];
        parent::__construct($options);

        if (
            ! isset($options['authorization_service'])
            || ! $options['authorization_service'] instanceof AuthorizationInterface
        ) {
            throw new RuntimeException('Authorization service is required by this guard and was not set');
        }

        $this->setAuthorizationService($options['authorization_service']);
    }

    public function isGranted(ServerRequestInterface $request): bool
    {
        $rule = $this->getMatchedRule($request);
        if ($rule === null) {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        $permissions = is_array($rule['permissions'] ?? null) ? $rule['permissions'] : $rule;
        $condition   = $rule['condition'] ?? GuardInterface::CONDITION_AND;
        unset($permissions['permissions'], $permissions['condition']);

        if (in_array('*', $permissions, true)) {
            return true;
        }

        $condition = is_string($condition) ? strtoupper($condition) : $condition;
        if ($condition !== GuardInterface::CONDITION_AND && $condition !== GuardInterface::CONDITION_OR) {
            throw new InvalidArgumentException(sprintf(
                'Condition must be either "AND" or "OR", "%s" given',
                is_scalar($condition) ? (string) $condition : get_debug_type($condition)
            ));
        }

        $permissions = array_filter($permissions, 'is_string');
        if ($permissions === []) {
            return false;
        }

        foreach ($permissions as $permission) {
            $granted = $this->authorizationService->isGranted($permission);
            if ($condition === GuardInterface::CONDITION_AND && ! $granted) {
                return false;
            }
            if ($condition === GuardInterface::CONDITION_OR && $granted) {
                return true;
            }
        }

        return $condition === GuardInterface::CONDITION_AND;
    }

    public function getAuthorizationService(): AuthorizationInterface
    {
        return $this->authorizationService;
    }

    public function setAuthorizationService(AuthorizationInterface $authorizationService): void
    {
        $this->authorizationService = $authorizationService;
    }
}
