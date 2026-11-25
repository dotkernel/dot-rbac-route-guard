<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;

use function is_array;
use function usort;

class ArrayGuardsProvider extends AbstractGuardsProvider
{
    /** @var array<array-key, mixed> */
    protected array $guardsConfig = [];

    /** @var list<GuardInterface> */
    protected array $guards = [];

    /**
     * @param array<array-key, mixed>|null $options
     */
    public function __construct(?array $options = null)
    {
        $options = $options ?? [];
        parent::__construct($options);

        if (isset($options['guards']) && is_array($options['guards'])) {
            $this->setGuardsConfig($options['guards']);
        }
    }

    /**
     * Returns the cached guards, or creates them from the config, sorted by priority (highest first).
     *
     * @return list<GuardInterface>
     */
    public function getGuards(): array
    {
        if ($this->guards !== [] || $this->guardsConfig === []) {
            return $this->guards;
        }

        $guards = [];
        foreach ($this->guardsConfig as $guardConfig) {
            if (! is_array($guardConfig)) {
                throw new RuntimeException('Each guard config must be an array with a "type" key');
            }
            $guards[] = $this->guardFactory->create($guardConfig);
        }

        usort(
            $guards,
            static fn (GuardInterface $a, GuardInterface $b): int => $b->getPriority() <=> $a->getPriority()
        );

        return $this->guards = $guards;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getGuardsConfig(): array
    {
        return $this->guardsConfig;
    }

    /**
     * @param array<array-key, mixed> $guardsConfig
     */
    public function setGuardsConfig(array $guardsConfig): void
    {
        $this->guardsConfig = $guardsConfig;
        $this->guards       = [];
    }
}
