<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Event;

use Laminas\EventManager\AbstractListenerAggregate;

abstract class AbstractAuthorizationEventListener extends AbstractListenerAggregate implements
    AuthorizationEventListenerInterface
{
    use AuthorizationEventListenerTrait;
}
