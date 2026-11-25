<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
