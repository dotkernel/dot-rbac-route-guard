<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Event\AuthorizationEventListenerInterface;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Laminas\EventManager\EventManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function class_exists;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

trait AttachAuthorizationEventListenersTrait
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function attachListeners(ContainerInterface $container, EventManagerInterface $eventManager): void
    {
        foreach ($this->getRbacGuardOptions($container)->getEventListeners() as $listener) {
            $priority = 1;
            if (is_array($listener)) {
                $priority = is_int($listener['priority'] ?? null) ? $listener['priority'] : 1;
                $listener = $listener['type'] ?? '';
            }

            if (! is_string($listener)) {
                throw new RuntimeException(
                    'Authorization event listener must be a service name, a class name or an array with a "type" key'
                );
            }

            $this->getListenerObject($container, $listener)->attach($eventManager, $priority);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getListenerObject(
        ContainerInterface $container,
        string $listener
    ): AuthorizationEventListenerInterface {
        $object = null;
        if ($container->has($listener)) {
            $object = $container->get($listener);
        } elseif (class_exists($listener)) {
            $object = new $listener();
        }

        if (! $object instanceof AuthorizationEventListenerInterface) {
            throw new RuntimeException(sprintf(
                'Authorization event listener "%s" is not an instance of %s',
                $listener,
                AuthorizationEventListenerInterface::class
            ));
        }

        return $object;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRbacGuardOptions(ContainerInterface $container): RbacGuardOptions
    {
        $options = $container->get(RbacGuardOptions::class);
        if (! $options instanceof RbacGuardOptions) {
            throw new RuntimeException(sprintf('Service %s is not configured', RbacGuardOptions::class));
        }

        return $options;
    }
}
