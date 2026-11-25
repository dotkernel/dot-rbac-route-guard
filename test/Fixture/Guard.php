<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A guard with a fixed result and priority. It counts its calls.
 */
class Guard implements GuardInterface
{
    public int $calls = 0;

    public function __construct(private readonly bool $granted = true, private readonly int $priority = 1)
    {
    }

    public function isGranted(ServerRequestInterface $request): bool
    {
        $this->calls++;

        return $this->granted;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
