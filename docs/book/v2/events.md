# Authorization events

`RbacGuardMiddleware` and `ForbiddenHandler` trigger events.
Use the events to log decisions, to change the request, to change the decision, or to return your own response.

| Event constant | Value | Triggered by | Parameters |
| --- | --- | --- | --- |
| `AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION` | `event.beforeAuthorization` | `RbacGuardMiddleware`, before the guards | `request`, `authorizationService` |
| `AuthorizationEvent::EVENT_AFTER_AUTHORIZATION` | `event.afterAuthorization` | `RbacGuardMiddleware`, after the guards | `request`, `authorizationService`, `authorized` |
| `AuthorizationEvent::EVENT_FORBIDDEN` | `event.authorization.forbidden` | `ForbiddenHandler`, when it catches a `ForbiddenException` | `request`, `authorizationService`, `error` |

The class `AuthorizationEvent` is in the namespace `Dot\Rbac\Route\Guard\Event`.

## Write a listener

Extend `AbstractAuthorizationEventListener` and override the methods that you need:

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Dot\Rbac\Route\Guard\Event\AbstractAuthorizationEventListener;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;

class AuthorizationListener extends AbstractAuthorizationEventListener
{
    public function onAfterAuthorization(AuthorizationEvent $e): void
    {
        if ($e->getParam('authorized') === false) {
            // Log the denied request.
        }
    }
}
```

Register the listener in `dot_authorization.event_listeners`.
An entry is a service name, a class name, or an array with `type` and `priority`:

```php
'event_listeners' => [
    App\Listener\AuthorizationListener::class,
    [
        'type'     => App\Listener\OtherListener::class,
        'priority' => 10,
    ],
],
```

The package gets the listener from the container. If the container does not have it, the package creates the class without arguments.
A listener must implement `AuthorizationEventListenerInterface`, else a `RuntimeException` occurs.

## Change the request or the decision

A listener can set the `request` parameter to a new request. The next steps use the new request.
A listener of `EVENT_AFTER_AUTHORIZATION` can set `authorized` to `true` or `false` to change the decision.

## Return a response

The methods of `AuthorizationEventListenerInterface` return `void`, so a listener from `event_listeners` cannot return a response.
To return a response, attach a callable to the event manager of the middleware, for example in a delegator factory of your container.
The callable can return a `Psr\Http\Message\ResponseInterface`, for example a redirect to the login page.
Then the event stops, and the middleware returns that response. The guards and the next middleware do not run.

```php
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware;
use Laminas\Diactoros\Response\RedirectResponse;

/** @var RbacGuardMiddleware $middleware */
$middleware->getEventManager()->attach(
    AuthorizationEvent::EVENT_AFTER_AUTHORIZATION,
    static function (AuthorizationEvent $e): ?RedirectResponse {
        return $e->getParam('authorized') === false ? new RedirectResponse('/login') : null;
    }
);
```
