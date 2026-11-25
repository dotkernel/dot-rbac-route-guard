<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Creates server requests with a matched route, a failed route or no route result.
 */
final class Request
{
    public static function forRoute(string $routeName): ServerRequestInterface
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        return (new ServerRequest())->withAttribute(
            RouteResult::class,
            RouteResult::fromRoute(new Route('/' . $routeName, $middleware, null, $routeName))
        );
    }

    public static function forFailedRoute(): ServerRequestInterface
    {
        return (new ServerRequest())->withAttribute(RouteResult::class, RouteResult::fromRouteFailure(null));
    }

    public static function withoutRoute(): ServerRequestInterface
    {
        return new ServerRequest();
    }
}
