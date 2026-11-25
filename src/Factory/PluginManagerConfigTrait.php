<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function is_array;
use function is_callable;
use function is_string;

trait PluginManagerConfigTrait
{
    /**
     * Reads the "aliases" and "factories" of a plugin manager from the "dot_authorization" config.
     *
     * @return array{aliases: array<string, string>, factories: array<string, string|callable>}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getPluginManagerConfig(ContainerInterface $container, string $key): array
    {
        $result = ['aliases' => [], 'factories' => []];

        $config = $container->has('config') ? $container->get('config') : [];
        $config = is_array($config) ? $config['dot_authorization'][$key] ?? [] : [];
        if (! is_array($config)) {
            return $result;
        }

        foreach (is_array($config['aliases'] ?? null) ? $config['aliases'] : [] as $alias => $target) {
            if (is_string($alias) && is_string($target)) {
                $result['aliases'][$alias] = $target;
            }
        }

        foreach (is_array($config['factories'] ?? null) ? $config['factories'] : [] as $name => $factory) {
            if (is_string($name) && (is_string($factory) || is_callable($factory))) {
                $result['factories'][$name] = $factory;
            }
        }

        return $result;
    }
}
