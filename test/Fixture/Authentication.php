<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Dot\Authentication\AuthenticationInterface;
use Dot\Authentication\AuthenticationResult;
use Dot\Authentication\Identity\IdentityInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Authentication implements AuthenticationInterface
{
    public function __construct(private ?IdentityInterface $identity = null)
    {
    }

    public function challenge(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(401);
    }

    public function authenticate(ServerRequestInterface $request): AuthenticationResult
    {
        throw new LogicException('Not used in tests');
    }

    public function hasIdentity(): bool
    {
        return $this->identity !== null;
    }

    public function getIdentity(): ?IdentityInterface
    {
        return $this->identity;
    }

    public function setIdentity(IdentityInterface $identity): void
    {
        $this->identity = $identity;
    }

    public function clearIdentity(): void
    {
        $this->identity = null;
    }
}
