<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Closure;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Returns a "handled" response, or calls the given closure. It keeps the last request.
 */
class RequestHandler implements RequestHandlerInterface
{
    public ?ServerRequestInterface $request = null;

    public function __construct(private readonly ?Closure $handle = null)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        if ($this->handle !== null) {
            $response = ($this->handle)($request);
            if ($response instanceof ResponseInterface) {
                return $response;
            }
        }

        return new TextResponse('handled');
    }
}
