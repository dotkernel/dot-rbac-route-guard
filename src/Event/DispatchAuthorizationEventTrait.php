<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Event;

use Laminas\EventManager\EventManagerAwareTrait;
use Psr\Http\Message\ResponseInterface;

trait DispatchAuthorizationEventTrait
{
    use EventManagerAwareTrait;

    /**
     * Triggers the event. A listener can stop the event by returning a response, which is then returned.
     *
     * @param array<string, mixed> $params
     */
    public function dispatchEvent(
        string $name,
        array $params = [],
        object|string|null $target = null
    ): AuthorizationEvent|ResponseInterface {
        $event  = new AuthorizationEvent($name, $target ?? $this, $params);
        $result = $this->getEventManager()->triggerEventUntil(
            static fn (mixed $response): bool => $response instanceof ResponseInterface,
            $event
        );

        $last = $result->last();
        if ($result->stopped() && $last instanceof ResponseInterface) {
            return $last;
        }

        return $event;
    }
}
