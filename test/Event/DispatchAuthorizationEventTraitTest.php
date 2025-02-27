<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Event;

use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Event\DispatchAuthorizationEventTrait;
use PHPUnit\Framework\TestCase;

class DispatchAuthorizationEventTraitTest extends TestCase
{
    use DispatchAuthorizationEventTrait;

    public function testDispatchEvent(): void
    {
        $name = 'name';

        $result = $this->dispatchEvent($name);
        $this->assertInstanceOf(AuthorizationEvent::class, $result);
    }
}
