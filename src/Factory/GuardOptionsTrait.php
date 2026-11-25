<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function class_exists;
use function is_array;
use function is_string;
use function sprintf;

trait GuardOptionsTrait
{
    /**
     * Returns the service named in $options[$key] when the container has it, else the default service.
     *
     * @param array<array-key, mixed> $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getService(
        ContainerInterface $container,
        array $options,
        string $key,
        string $defaultService
    ): mixed {
        $name = $options[$key] ?? null;
        if (is_string($name) && $container->has($name)) {
            return $container->get($name);
        }

        return $container->get($defaultService);
    }

    /**
     * Sets the protection policy and the rules, then creates the guard.
     *
     * @param array<array-key, mixed> $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createGuard(ContainerInterface $container, string $requestedName, array $options): object
    {
        if (! isset($options['protection_policy'])) {
            $moduleOptions = $container->get(RbacGuardOptions::class);
            if (! $moduleOptions instanceof RbacGuardOptions) {
                throw new RuntimeException(sprintf('Service %s is not configured', RbacGuardOptions::class));
            }

            $options['protection_policy'] = $moduleOptions->getProtectionPolicy();
        }

        $options['rules'] = is_array($options['rules'] ?? null) ? $options['rules'] : [];

        if (! class_exists($requestedName)) {
            throw new RuntimeException(sprintf('Guard class "%s" does not exist', $requestedName));
        }

        return new $requestedName($options);
    }
}
