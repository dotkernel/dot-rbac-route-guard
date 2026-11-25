<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Dot\Rbac\Route\Guard\Event\AbstractAuthorizationEventListener;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;

/**
 * Records the names of the events it receives.
 */
class Listener extends AbstractAuthorizationEventListener
{
    /** @var list<string> Event names received by any instance */
    public static array $allEvents = [];

    /** @var list<string> */
    public array $events = [];

    public function onBeforeAuthorization(AuthorizationEvent $e): void
    {
        $this->events[]    = (string) $e->getName();
        self::$allEvents[] = (string) $e->getName();
    }

    public function onAfterAuthorization(AuthorizationEvent $e): void
    {
        $this->events[]    = (string) $e->getName();
        self::$allEvents[] = (string) $e->getName();
    }

    public function onForbidden(AuthorizationEvent $e): void
    {
        $this->events[]    = (string) $e->getName();
        self::$allEvents[] = (string) $e->getName();
    }
}
