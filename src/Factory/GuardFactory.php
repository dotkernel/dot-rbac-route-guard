<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Rbac\Role\RoleServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GuardFactory
{
    use GuardOptionsTrait;

    /**
     * @param array<array-key, mixed>|null $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): object
    {
        $options                 = $options ?? [];
        $options['role_service'] = $this->getService($container, $options, 'role_service', RoleServiceInterface::class);

        return $this->createGuard($container, $requestedName, $options);
    }
}
