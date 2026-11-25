<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function is_array;

class RbacGuardOptionsFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RbacGuardOptions
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = is_array($config) ? $config['dot_authorization'] ?? [] : [];

        return new RbacGuardOptions(is_array($config) ? $config : []);
    }
}
