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

    protected AuthorizationInterface $authorizationService;

    protected RbacGuardOptions $options;

    protected GuardsProviderInterface $guardsProvider;

    protected ?AuthenticationInterface $authentication;

    public function __construct(
        AuthorizationInterface $authorizationService,
        GuardsProviderInterface $guardsProvider,
        RbacGuardOptions $options,
        ?AuthenticationInterface $authentication = null
    ) {
        $this->authorizationService = $authorizationService;
        $this->guardsProvider       = $guardsProvider;
        $this->options              = $options;
        $this->authentication       = $authentication;
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

        $request     = $event->getParam('request');
        $routeResult = $request->getAttribute(RouteResult::class, null);
        if ($routeResult instanceof RouteResult) {
            $guards = $this->guardsProvider->getGuards();

            //iterate over guards, which are sorted by priority
            //break on the first one that does not grant access

            $isGranted = $this->options->getProtectionPolicy() === GuardInterface::POLICY_ALLOW;
            foreach ($guards as $guard) {
                if (! $guard instanceof GuardInterface) {
                    throw new RuntimeException("Guard is not an instance of " . GuardInterface::class);
                }

                //according to the policy, we whitelist or blacklist matched routes
                $r = $guard->isGranted($request);
                if ($r !== $isGranted) {
                    $isGranted = $r;
                    break;
                }
            }
            $event->setParam('authorized', $isGranted);
        } else {
            $event->setParam('authorized', true);
        }

        $params = $event->getParams();
        $event  = $this->dispatchEvent(AuthorizationEvent::EVENT_AFTER_AUTHORIZATION, $params);
        if ($event instanceof ResponseInterface) {
            return $event;
        }

        $request   = $event->getParam('request');
        $isGranted = $event->getParam('authorized', true);
        if (! $isGranted) {
            if ($this->authentication) {
                //we throw a 401 if is guest, and let unauthorized exception handlers process it
                //403 otherwise, resulting in a final handler or redirect, whatever you register as the error handler
                if (! $this->authentication->hasIdentity()) {
                    throw new UnauthorizedException(
                        $this->options->getMessagesOptions()->getMessage(MessagesOptions::UNAUTHORIZED),
                        401
                    );
                }
            }

            throw new ForbiddenException(
                $this->options->getMessagesOptions()->getMessage(MessagesOptions::FORBIDDEN),
                403
            );
        }

        return $handler->handle($request);
    }
}
