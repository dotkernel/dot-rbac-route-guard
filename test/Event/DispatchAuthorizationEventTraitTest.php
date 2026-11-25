<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Event;

use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Event\DispatchAuthorizationEventTrait;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;

class DispatchAuthorizationEventTraitTest extends TestCase
{
    use DispatchAuthorizationEventTrait;

    public function testReturnsTheEventWithParamsAndTarget(): void
    {
        $result = $this->dispatchEvent('name', ['key' => 'value']);

        $this->assertInstanceOf(AuthorizationEvent::class, $result);
        $this->assertSame('name', $result->getName());
        $this->assertSame($this, $result->getTarget());
        $this->assertSame('value', $result->getParam('key'));
    }

    public function testUsesGivenTarget(): void
    {
        $result = $this->dispatchEvent('name', [], 'target');

        $this->assertInstanceOf(AuthorizationEvent::class, $result);
        $this->assertSame('target', $result->getTarget());
    }

    public function testReturnsResponseFromListener(): void
    {
        $response = new TextResponse('stop');
        $this->getEventManager()->attach('name', static fn (): TextResponse => $response);

        $this->assertSame($response, $this->dispatchEvent('name'));
    }

    public function testIgnoresNonResponseListenerResults(): void
    {
        $this->getEventManager()->attach('name', static fn (): string => 'not a response');

        $this->assertInstanceOf(AuthorizationEvent::class, $this->dispatchEvent('name'));
    }
}
