<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
