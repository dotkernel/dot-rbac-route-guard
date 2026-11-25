<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Factory\AttachAuthorizationEventListenersTrait;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use DotTest\Rbac\Route\Guard\Fixture\Listener;
use Laminas\EventManager\EventManager;
use PHPUnit\Framework\TestCase;
use stdClass;

class AttachAuthorizationEventListenersTraitTest extends TestCase
{
    use AttachAuthorizationEventListenersTrait;

    /**
     * @param array<array-key, mixed> $listeners
     * @param array<string, mixed> $services
     */
    private function container(array $listeners, array $services = []): InMemoryContainer
    {
        return new InMemoryContainer(
            [RbacGuardOptions::class => new RbacGuardOptions(['event_listeners' => $listeners])] + $services
        );
    }

    private function trigger(EventManager $eventManager): void
    {
        $eventManager->triggerEvent(new AuthorizationEvent(AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION));
    }

    public function testAttachesListenerFromContainerWithPriority(): void
    {
        $listener     = new Listener();
        $eventManager = new EventManager();

        $this->attachListeners(
            $this->container([['type' => 'my.listener', 'priority' => 10]], ['my.listener' => $listener]),
            $eventManager
        );
        $this->trigger($eventManager);

        $this->assertSame([AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION], $listener->events);
    }

    public function testCreatesListenerFromClassName(): void
    {
        Listener::$allEvents = [];
        $eventManager        = new EventManager();

        $this->attachListeners($this->container([Listener::class]), $eventManager);
        $this->trigger($eventManager);

        $this->assertSame([AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION], Listener::$allEvents);
    }

    public function testNoListeners(): void
    {
        Listener::$allEvents = [];
        $eventManager        = new EventManager();

        $this->attachListeners($this->container([]), $eventManager);
        $this->trigger($eventManager);

        $this->assertSame([], Listener::$allEvents);
    }

    public function testInvalidListenerThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Authorization event listener "invalid" is not an instance of');
        $this->attachListeners($this->container(['invalid'], ['invalid' => new stdClass()]), new EventManager());
    }

    public function testArrayListenerWithoutTypeThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Authorization event listener "" is not an instance of');
        $this->attachListeners($this->container([['priority' => 1]]), new EventManager());
    }

    public function testNonStringListenerThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be a service name, a class name or an array with a "type" key');
        $this->attachListeners($this->container([5]), new EventManager());
    }

    public function testMissingOptionsServiceThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->attachListeners(new InMemoryContainer([RbacGuardOptions::class => null]), new EventManager());
    }
}
