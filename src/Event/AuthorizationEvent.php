<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Event;

use Dot\Event\Event;

/**
 * @extends Event<object|string|null, array<string, mixed>>
 */
class AuthorizationEvent extends Event
{
    public const EVENT_BEFORE_AUTHORIZATION = 'event.beforeAuthorization';
    public const EVENT_AFTER_AUTHORIZATION  = 'event.afterAuthorization';

    public const EVENT_FORBIDDEN = 'event.authorization.forbidden';
}
