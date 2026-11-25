<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Middleware;

use Dot\Authentication\AuthenticationInterface;
use Dot\Authentication\Exception\UnauthorizedException;
use Dot\Authorization\AuthorizationInterface;
use Dot\Authorization\Exception\ForbiddenException;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Event\AuthorizationEventListenerInterface;
use Dot\Rbac\Route\Guard\Event\AuthorizationEventListenerTrait;
use Dot\Rbac\Route\Guard\Event\DispatchAuthorizationEventTrait;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Options\MessagesOptions;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RbacGuardMiddleware implements MiddlewareInterface, AuthorizationEventListenerInterface
{
    use AuthorizationEventListenerTrait;
    use DispatchAuthorizationEventTrait;

    public function __construct(
        protected AuthorizationInterface $authorizationService,
        protected GuardsProviderInterface $guardsProvider,
        protected RbacGuardOptions $options,
        protected ?AuthenticationInterface $authentication = null
    ) {
    }

    /**
     * @throws ForbiddenException
     * @throws UnauthorizedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $event = $this->dispatchEvent(AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION, [
            'request'              => $request,
            'authorizationService' => $this->authorizationService,
        ]);
        if ($event instanceof ResponseInterface) {
            return $event;
        }

        $request = $this->getRequest($event, $request);
        $event->setParam('authorized', $this->isGranted($request));

        $event = $this->dispatchEvent(AuthorizationEvent::EVENT_AFTER_AUTHORIZATION, $event->getParams());
        if ($event instanceof ResponseInterface) {
            return $event;
        }

        $request = $this->getRequest($event, $request);
        if ($event->getParam('authorized', true) !== false) {
            return $handler->handle($request);
        }

        // A guest gets 401, so an unauthorized handler can process it. An identity gets 403.
        if ($this->authentication !== null && ! $this->authentication->hasIdentity()) {
            throw new UnauthorizedException(
                $this->options->getMessagesOptions()->getMessage(MessagesOptions::UNAUTHORIZED),
                401
            );
        }

        throw new ForbiddenException(
            $this->options->getMessagesOptions()->getMessage(MessagesOptions::FORBIDDEN),
            403
        );
    }

    /**
     * Grants access only when every guard grants it. Each guard applies its protection policy to unknown routes.
     * Without guards, the global protection policy decides.
     */
    protected function isGranted(ServerRequestInterface $request): bool
    {
        if (! $request->getAttribute(RouteResult::class) instanceof RouteResult) {
            return true;
        }

        $guards = $this->guardsProvider->getGuards();
        if ($guards === []) {
            return $this->options->getProtectionPolicy() === GuardInterface::POLICY_ALLOW;
        }

        foreach ($guards as $guard) {
            if (! $guard instanceof GuardInterface) {
                throw new RuntimeException('Guard is not an instance of ' . GuardInterface::class);
            }

            if (! $guard->isGranted($request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the request from the event, because a listener can replace it.
     */
    private function getRequest(AuthorizationEvent $event, ServerRequestInterface $default): ServerRequestInterface
    {
        $request = $event->getParam('request');

        return $request instanceof ServerRequestInterface ? $request : $default;
    }
}
