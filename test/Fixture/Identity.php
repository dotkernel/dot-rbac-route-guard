<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Dot\Authentication\Identity\IdentityInterface;

class Identity implements IdentityInterface
{
    public function getId(): mixed
    {
        return 1;
    }

    public function getName(): string
    {
        return 'user';
    }
}
