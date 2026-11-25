<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Options;

use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\ProtectionPolicyTrait;
use Laminas\Stdlib\AbstractOptions;
use Laminas\Stdlib\ParameterObjectInterface;

/**
 * @extends AbstractOptions<ParameterObjectInterface>
 */
class RbacGuardOptions extends AbstractOptions
{
    use ProtectionPolicyTrait;

    /** @var array<array-key, mixed> */
    protected array $eventListeners = [];

    /** @var array<array-key, mixed> */
    protected array $guardsProvider = [];

    protected ?MessagesOptions $messagesOptions = null;

    /**
     * @param iterable<string, mixed>|null $options
     */
    public function __construct(?iterable $options = null)
    {
        $this->__strictMode__   = false;
        $this->protectionPolicy = GuardInterface::POLICY_ALLOW;
        parent::__construct($options);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getGuardsProvider(): array
    {
        return $this->guardsProvider;
    }

    /**
     * @param array<array-key, mixed> $guardsProvider
     */
    public function setGuardsProvider(array $guardsProvider): void
    {
        $this->guardsProvider = $guardsProvider;
    }

    public function getMessagesOptions(): MessagesOptions
    {
        if ($this->messagesOptions === null) {
            $this->messagesOptions = new MessagesOptions();
        }

        return $this->messagesOptions;
    }

    /**
     * @param array<string, mixed> $messagesOptions
     */
    public function setMessagesOptions(array $messagesOptions): void
    {
        $this->messagesOptions = new MessagesOptions($messagesOptions);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }

    /**
     * @param array<array-key, mixed> $eventListeners
     */
    public function setEventListeners(array $eventListeners): void
    {
        $this->eventListeners = $eventListeners;
    }
}
