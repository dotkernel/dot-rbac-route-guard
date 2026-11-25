<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Event;

use Dot\Rbac\Route\Guard\Event\AbstractAuthorizationEventListener;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use DotTest\Rbac\Route\Guard\Fixture\Listener;
use Laminas\EventManager\EventManager;
use PHPUnit\Framework\TestCase;

class AbstractAuthorizationEventListenerTest extends TestCase
{
    public function testAttachesToTheThreeEventsAndDetaches(): void
    {
        $eventManager = new EventManager();
        $listener     = new Listener();
        $listener->attach($eventManager);

        foreach (
            [
                AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION,
                AuthorizationEvent::EVENT_AFTER_AUTHORIZATION,
                AuthorizationEvent::EVENT_FORBIDDEN,
            ] as $name
        ) {
            $eventManager->triggerEvent(new AuthorizationEvent($name));
        }

        $this->assertSame(
            [
                AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION,
                AuthorizationEvent::EVENT_AFTER_AUTHORIZATION,
                AuthorizationEvent::EVENT_FORBIDDEN,
            ],
            $listener->events
        );

        $listener->detach($eventManager);
        $eventManager->triggerEvent(new AuthorizationEvent(AuthorizationEvent::EVENT_FORBIDDEN));
        $this->assertCount(3, $listener->events);
    }

    public function testDefaultHandlersDoNothing(): void
    {
        $listener = new class extends AbstractAuthorizationEventListener {
        };
        $event    = new AuthorizationEvent(AuthorizationEvent::EVENT_FORBIDDEN);

        $listener->onBeforeAuthorization($event);
        $listener->onAfterAuthorization($event);
        $listener->onForbidden($event);

        $this->assertSame([], $event->getParams());
    }
}
