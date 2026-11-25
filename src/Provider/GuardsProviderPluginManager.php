<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Factory\GuardsProviderFactory;
use Dot\Rbac\Route\Guard\Plugin\AbstractPluginManager;

/**
 * @extends AbstractPluginManager<GuardsProviderInterface>
 */
class GuardsProviderPluginManager extends AbstractPluginManager
{
    /** @var array<string, string|callable> */
    protected array $factories = [
        ArrayGuardsProvider::class => GuardsProviderFactory::class,
    ];

    /** @var array<string, string> */
    protected array $aliases = [
        'arrayguardsprovider' => ArrayGuardsProvider::class,
        'arrayGuardsProvider' => ArrayGuardsProvider::class,
        'ArrayGuardsProvider' => ArrayGuardsProvider::class,
        'arrayguards'         => ArrayGuardsProvider::class,
        'arrayGuards'         => ArrayGuardsProvider::class,
        'ArrayGuards'         => ArrayGuardsProvider::class,
    ];

    protected function getInstanceOf(): string
    {
        return GuardsProviderInterface::class;
    }
}
