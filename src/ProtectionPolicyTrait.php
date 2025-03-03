<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard;

use Dot\Rbac\Route\Guard\Guard\GuardInterface;

trait ProtectionPolicyTrait
{
    protected string $protectionPolicy = GuardInterface::POLICY_DENY;

    public function getProtectionPolicy(): string
    {
        return $this->protectionPolicy;
    }

    public function setProtectionPolicy(string $protectionPolicy): void
    {
        $this->protectionPolicy = $protectionPolicy;
    }
}
