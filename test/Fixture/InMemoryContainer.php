<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Psr\Container\ContainerInterface;

use function array_key_exists;
use function sprintf;

/**
 * A minimal PSR-11 container that returns the services given to it.
 */
class InMemoryContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(private array $services = [])
    {
    }

    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): mixed
    {
        if (! array_key_exists($id, $this->services)) {
            throw new NotFoundException(sprintf('Service "%s" was not found', $id));
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}
